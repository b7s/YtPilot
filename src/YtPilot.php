<?php

declare(strict_types=1);

namespace YtPilot;

use YtPilot\DTO\DownloadResult;
use YtPilot\DTO\FormatItem;
use YtPilot\DTO\SubtitleList;
use YtPilot\Exceptions\MissingUrlException;
use YtPilot\Services\Binary\BinaryLocatorService;
use YtPilot\Services\Binary\FfmpegBinaryService;
use YtPilot\Services\Binary\ManifestService;
use YtPilot\Services\Binary\ReleaseResolverService;
use YtPilot\Services\Binary\YtDlpBinaryService;
use YtPilot\Services\Filesystem\PathService;
use YtPilot\Services\Http\DownloaderService;
use YtPilot\Services\Metadata\MediaInfoService;
use YtPilot\Services\Parsing\FormatsParserService;
use YtPilot\Services\Parsing\SubtitlesParserService;
use YtPilot\Services\Platform\PlatformService;
use YtPilot\Services\Process\ProcessRunnerService;

final class YtPilot
{
    private ?string $url = null;
    private ?string $outputTemplate = null;
    private ?string $outputPath = null;
    private ?string $formatSelector = null;
    private ?int $timeout = null;

    private ?string $ytDlpPath = null;
    private ?string $ffmpegPath = null;
    private ?string $ffprobePath = null;

    private bool $downloadVideo = false;
    private bool $downloadAudio = false;
    private bool $downloadSubtitles = false;
    private bool $downloadAutoSubtitles = false;
    private bool $downloadMetadata = false;
    private bool $downloadThumbnail = false;

    private bool $audioOnly = false;
    private ?string $audioFormat = null;
    private ?string $audioQuality = null;

    /** @var list<string> */
    private array $subtitleLanguages = [];
    private ?string $subtitleFormat = null;

    private bool $skipDownload = false;
    private bool $simulate = false;
    private bool $overwrite = false;

    private PlatformService $platform;
    private PathService $pathService;
    private ProcessRunnerService $processRunner;
    private DownloaderService $downloader;
    private ReleaseResolverService $releaseResolver;
    private ManifestService $manifestService;
    private BinaryLocatorService $locator;
    private YtDlpBinaryService $ytDlpService;
    private FfmpegBinaryService $ffmpegService;
    private FormatsParserService $formatsParser;
    private SubtitlesParserService $subtitlesParser;
    private MediaInfoService $mediaInfo;

    private function __construct()
    {
        $this->initializeServices();
        $this->ensureInstalled();
    }

    public static function make(): self
    {
        return new self();
    }

    public function ensureInstalled(): self
    {
        $this->ytDlpService->ensureInstalled($this->ytDlpPath);

        if (Config::get('ffmpeg.enabled', true)) {
            $this->ffmpegService->ensureInstalled($this->ffmpegPath, $this->ffprobePath);
        }

        return $this;
    }

    public function url(string $url): self
    {
        $this->url = $url;

        return $this;
    }

    public function outputPath(string $directory): self
    {
        $this->outputPath = rtrim($directory, DIRECTORY_SEPARATOR);

        return $this;
    }

    public function output(string $template): self
    {
        $this->outputTemplate = $template;

        return $this;
    }

    public function format(string $selector): self
    {
        $this->formatSelector = $selector;

        return $this;
    }

    public function best(): self
    {
        $this->formatSelector = 'bestvideo+bestaudio/best';

        return $this;
    }

    public function worst(): self
    {
        $this->formatSelector = 'worstvideo+worstaudio/worst';

        return $this;
    }

    public function resolution(string $label): self
    {
        $height = match (strtolower($label)) {
            '4k', '2160p' => 2160,
            '1440p', '2k' => 1440,
            '1080p', 'fhd' => 1080,
            '720p', 'hd' => 720,
            '480p' => 480,
            '360p' => 360,
            '240p' => 240,
            '144p' => 144,
            default => (int) preg_replace('/\D/', '', $label),
        };

        $this->formatSelector = "bestvideo[height<={$height}]+bestaudio/best[height<={$height}]";

        return $this;
    }

    public function fps(int $fps): self
    {
        $current = $this->formatSelector ?? 'bestvideo';
        $this->formatSelector = str_replace('bestvideo', "bestvideo[fps<={$fps}]", $current);

        return $this;
    }

    public function videoCodec(string $codec): self
    {
        $current = $this->formatSelector ?? 'bestvideo+bestaudio/best';
        $this->formatSelector = str_replace('bestvideo', "bestvideo[vcodec^={$codec}]", $current);

        return $this;
    }

    public function audioCodec(string $codec): self
    {
        $current = $this->formatSelector ?? 'bestvideo+bestaudio/best';
        $this->formatSelector = str_replace('bestaudio', "bestaudio[acodec^={$codec}]", $current);

        return $this;
    }

    public function container(string $ext): self
    {
        $current = $this->formatSelector ?? 'bestvideo+bestaudio/best';
        $this->formatSelector = str_replace('bestvideo', "bestvideo[ext={$ext}]", $current);

        return $this;
    }

    public function hdr(): self
    {
        $this->formatSelector = 'bestvideo[vcodec^=vp9.2]+bestaudio/bestvideo[dynamic_range=HDR]+bestaudio/best';

        return $this;
    }

    public function sdr(): self
    {
        $this->formatSelector = 'bestvideo[dynamic_range=SDR]+bestaudio/best';

        return $this;
    }

    public function audioOnly(): self
    {
        $this->audioOnly = true;
        $this->downloadVideo = false;
        $this->downloadAudio = true;

        return $this;
    }

    public function audioFormat(string $format): self
    {
        $this->audioFormat = $format;

        return $this;
    }

    public function audioQuality(string $quality): self
    {
        $this->audioQuality = $quality;

        return $this;
    }

    /** @param list<string> $langs */
    public function subtitleLanguages(array $langs): self
    {
        $this->subtitleLanguages = $langs;

        return $this;
    }

    public function subtitleFormat(string $format): self
    {
        $this->subtitleFormat = $format;

        return $this;
    }

    public function subtitleAsSrt(): self
    {
        $this->subtitleFormat = 'srt';

        return $this;
    }

    public function subtitleAsVtt(): self
    {
        $this->subtitleFormat = 'vtt';

        return $this;
    }

    public function subtitleAsAss(): self
    {
        $this->subtitleFormat = 'ass';

        return $this;
    }

    public function subtitleAsSsa(): self
    {
        $this->subtitleFormat = 'ssa';

        return $this;
    }

    public function subtitleAsLrc(): self
    {
        $this->subtitleFormat = 'lrc';

        return $this;
    }

    public function subtitleAsSrv1(): self
    {
        $this->subtitleFormat = 'srv1';

        return $this;
    }

    public function subtitleAsSrv2(): self
    {
        $this->subtitleFormat = 'srv2';

        return $this;
    }

    public function subtitleAsSrv3(): self
    {
        $this->subtitleFormat = 'srv3';

        return $this;
    }

    public function subtitleAsTtml(): self
    {
        $this->subtitleFormat = 'ttml';

        return $this;
    }

    public function subtitleAsJson3(): self
    {
        $this->subtitleFormat = 'json3';

        return $this;
    }

    public function cinema(): self
    {
        $this->formatSelector = 'bestvideo[height>=1080][vcodec^=av01]/bestvideo[height>=1080][vcodec^=vp9]/bestvideo[height>=1080][vcodec^=avc1]/bestvideo[height>=1080]+bestaudio/best';

        return $this;
    }

    public function mobile(): self
    {
        $this->formatSelector = 'bestvideo[height<=720][vcodec^=avc1]+bestaudio[abr<=128]/best[height<=720]';

        return $this;
    }

    public function archive(): self
    {
        $this->formatSelector = 'bestvideo[vcodec^=av01]/bestvideo[vcodec^=vp9]/bestvideo+bestaudio/best';

        return $this;
    }

    public function video(): self
    {
        $this->downloadVideo = true;

        return $this;
    }

    public function audio(): self
    {
        $this->downloadAudio = true;

        return $this;
    }

    public function subtitles(): self
    {
        $this->downloadSubtitles = true;

        return $this;
    }

    public function autoSubtitles(): self
    {
        $this->downloadAutoSubtitles = true;

        return $this;
    }

    public function metadata(): self
    {
        $this->downloadMetadata = true;

        return $this;
    }

    public function thumbnail(): self
    {
        $this->downloadThumbnail = true;

        return $this;
    }

    public function skipDownload(): self
    {
        $this->skipDownload = true;

        return $this;
    }

    public function simulate(): self
    {
        $this->simulate = true;

        return $this;
    }

    public function overwrite(): self
    {
        $this->overwrite = true;

        return $this;
    }

    public function timeout(int $seconds): self
    {
        $this->timeout = $seconds;

        return $this;
    }

    public function withYtDlpPath(?string $path): self
    {
        $this->ytDlpPath = $path;

        return $this;
    }

    public function withFfmpegPath(?string $path): self
    {
        $this->ffmpegPath = $path;

        return $this;
    }

    public function withFfprobePath(?string $path): self
    {
        $this->ffprobePath = $path;

        return $this;
    }

    public function download(): DownloadResult
    {
        $this->requireUrl();

        $command = $this->buildCommand();
        $timeout = $this->timeout ?? Config::get('timeout', 300);
        
        // Use configured default download path if not set
        $workingDir = $this->outputPath ?? Config::get('download_path');

        $result = $this->processRunner->run($command, $workingDir, $timeout);

        if (!$result->success) {
            return DownloadResult::failure($result->errorOutput ?: $result->output, $result->exitCode);
        }

        $downloadedFiles = $this->parseDownloadedFiles($result->output);

        return DownloadResult::success(
            output: $result->output,
            downloadedFiles: $downloadedFiles,
            videoPath: $this->findFileByType($downloadedFiles, ['mp4', 'mkv', 'webm', 'avi']),
            audioPath: $this->findFileByType($downloadedFiles, ['mp3', 'm4a', 'opus', 'ogg', 'wav']),
            thumbnailPath: $this->findFileByType($downloadedFiles, ['jpg', 'jpeg', 'png', 'webp']),
            metadataPath: $this->findFileByType($downloadedFiles, ['json', 'info.json']),
            subtitlePaths: $this->findFilesByType($downloadedFiles, ['srt', 'vtt', 'ass']),
        );
    }

    /**
     * @return FormatItem[]
     */
    public function getAvailableFormats(): array
    {
        $this->requireUrl();

        return $this->mediaInfo->getAvailableFormats($this->url, $this->ytDlpPath);
    }

    /** @return list<string> */
    public function getAvailableResolutions(): array
    {
        $this->requireUrl();

        return $this->mediaInfo->getAvailableResolutions($this->url, $this->ytDlpPath);
    }

    /** @return list<int> */
    public function getAvailableFrameRates(): array
    {
        $this->requireUrl();

        return $this->mediaInfo->getAvailableFrameRates($this->url, $this->ytDlpPath);
    }

    /** @return list<string> */
    public function getAvailableVideoCodecs(): array
    {
        $this->requireUrl();

        return $this->mediaInfo->getAvailableVideoCodecs($this->url, $this->ytDlpPath);
    }

    /** @return list<string> */
    public function getAvailableAudioCodecs(): array
    {
        $this->requireUrl();

        return $this->mediaInfo->getAvailableAudioCodecs($this->url, $this->ytDlpPath);
    }

    /** @return list<string> */
    public function getAvailableContainers(): array
    {
        $this->requireUrl();

        return $this->mediaInfo->getAvailableContainers($this->url, $this->ytDlpPath);
    }

    /** @return list<string> */
    public function getAvailableDynamicRanges(): array
    {
        $this->requireUrl();

        return $this->mediaInfo->getAvailableDynamicRanges($this->url, $this->ytDlpPath);
    }

    public function getAvailableSubtitles(): SubtitleList
    {
        $this->requireUrl();

        return $this->mediaInfo->getAvailableSubtitles($this->url, $this->ytDlpPath);
    }

    /** @return list<string> */
    private function buildCommand(): array
    {
        $binary = $this->locator->requireYtDlp($this->ytDlpPath);
        $command = [$binary];

        if (!$this->hasAnyTarget()) {
            $this->downloadVideo = true;
            $this->downloadAudio = true;
        }

        if ($this->formatSelector !== null) {
            $command[] = '-f';
            $command[] = $this->formatSelector;
        }

        if ($this->outputTemplate !== null) {
            $command[] = '-o';
            $command[] = $this->outputTemplate;
        }

        if ($this->audioOnly) {
            $command[] = '-x';

            if ($this->audioFormat !== null) {
                $command[] = '--audio-format';
                $command[] = $this->audioFormat;
            }

            if ($this->audioQuality !== null) {
                $command[] = '--audio-quality';
                $command[] = $this->audioQuality;
            }
        }

        if ($this->downloadSubtitles) {
            $command[] = '--write-subs';

            if ($this->subtitleLanguages !== []) {
                $command[] = '--sub-langs';
                $command[] = implode(',', $this->subtitleLanguages);
            }

            if ($this->subtitleFormat !== null) {
                $command[] = '--sub-format';
                $command[] = $this->subtitleFormat;
            }
        }

        if ($this->downloadAutoSubtitles) {
            $command[] = '--write-auto-subs';

            if ($this->subtitleLanguages !== []) {
                $command[] = '--sub-langs';
                $command[] = implode(',', $this->subtitleLanguages);
            }
        }

        if ($this->downloadMetadata) {
            $command[] = '--write-info-json';
        }

        if ($this->downloadThumbnail) {
            $command[] = '--write-thumbnail';
        }

        if ($this->skipDownload) {
            $command[] = '--skip-download';
        }

        if ($this->simulate) {
            $command[] = '--simulate';
        }

        if ($this->overwrite) {
            $command[] = '--force-overwrites';
        }

        $ffmpegLocation = $this->resolveFfmpegLocation();
        if ($ffmpegLocation !== null) {
            $command[] = '--ffmpeg-location';
            $command[] = $ffmpegLocation;
        }

        $command[] = '--no-warnings';
        $command[] = '--newline';
        $command[] = $this->url;

        return $command;
    }

    private function hasAnyTarget(): bool
    {
        return $this->downloadVideo
            || $this->downloadAudio
            || $this->downloadSubtitles
            || $this->downloadAutoSubtitles
            || $this->downloadMetadata
            || $this->downloadThumbnail
            || $this->audioOnly;
    }

    private function resolveFfmpegLocation(): ?string
    {
        if (!Config::get('ffmpeg.enabled', true)) {
            return null;
        }

        $ffmpeg = $this->locator->locateFfmpeg($this->ffmpegPath);

        if ($ffmpeg === null) {
            return null;
        }

        return dirname($ffmpeg);
    }

    private function requireUrl(): void
    {
        if ($this->url === null || $this->url === '') {
            throw MissingUrlException::required();
        }
    }

    /** @return list<string> */
    private function parseDownloadedFiles(string $output): array
    {
        $files = [];
        $patterns = [
            '/\[download\] Destination: (.+)$/m',
            '/\[Merger\] Merging formats into "(.+)"$/m',
            '/\[ExtractAudio\] Destination: (.+)$/m',
            '/\[ThumbnailsConvertor\] Converting thumbnail "(.+)"$/m',
            '/\[info\] Writing video metadata as JSON to: (.+)$/m',
            '/Already downloaded: (.+)$/m',
        ];

        foreach ($patterns as $pattern) {
            if (preg_match_all($pattern, $output, $matches)) {
                foreach ($matches[1] as $file) {
                    $files[] = trim($file);
                }
            }
        }

        return array_unique($files);
    }

    /**
     * @param list<string> $files
     * @param list<string> $extensions
     */
    private function findFileByType(array $files, array $extensions): ?string
    {
        foreach ($files as $file) {
            $ext = strtolower(pathinfo($file, PATHINFO_EXTENSION));

            if (in_array($ext, $extensions, true)) {
                return $file;
            }
        }

        return null;
    }

    /**
     * @param list<string> $files
     * @param list<string> $extensions
     * @return list<string>
     */
    private function findFilesByType(array $files, array $extensions): array
    {
        $found = [];

        foreach ($files as $file) {
            $ext = strtolower(pathinfo($file, PATHINFO_EXTENSION));

            if (in_array($ext, $extensions, true)) {
                $found[] = $file;
            }
        }

        return $found;
    }

    private function initializeServices(): void
    {
        $this->platform = new PlatformService();
        $this->pathService = new PathService($this->platform);
        $this->processRunner = new ProcessRunnerService();
        $this->downloader = new DownloaderService($this->pathService);
        $this->releaseResolver = new ReleaseResolverService($this->platform);
        $this->manifestService = new ManifestService($this->pathService);
        $this->locator = new BinaryLocatorService($this->pathService);

        $this->ytDlpService = new YtDlpBinaryService(
            $this->pathService,
            $this->downloader,
            $this->releaseResolver,
            $this->locator,
            $this->manifestService,
            $this->processRunner,
        );

        $this->ffmpegService = new FfmpegBinaryService(
            $this->pathService,
            $this->downloader,
            $this->releaseResolver,
            $this->locator,
            $this->manifestService,
            $this->processRunner,
            $this->platform,
        );

        $this->formatsParser = new FormatsParserService();
        $this->subtitlesParser = new SubtitlesParserService();

        $this->mediaInfo = new MediaInfoService(
            $this->processRunner,
            $this->locator,
            $this->formatsParser,
            $this->subtitlesParser,
        );
    }
}
