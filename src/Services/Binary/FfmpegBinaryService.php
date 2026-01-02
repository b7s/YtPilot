<?php

declare(strict_types=1);

namespace YtPilot\Services\Binary;

use YtPilot\Config;
use YtPilot\Exceptions\BinaryDownloadException;
use YtPilot\Exceptions\BinaryValidationException;
use YtPilot\Services\Filesystem\PathService;
use YtPilot\Services\Http\DownloaderService;
use YtPilot\Services\Platform\PlatformService;
use YtPilot\Services\Process\ProcessRunnerService;

use function in_array;

final class FfmpegBinaryService
{
    public function __construct(
        private readonly PathService $pathService,
        private readonly DownloaderService $downloaderService,
        private readonly ReleaseResolverService $releaseResolver,
        private readonly BinaryLocatorService $locator,
        private readonly ManifestService $manifestService,
        private readonly ProcessRunnerService $processRunner,
        private readonly PlatformService $platform,
    ) {}

    /** @return array{ffmpeg: string, ffprobe: string} */
    public function install(?callable $progressCallback = null): array
    {
        $url = $this->releaseResolver->getFfmpegDownloadUrl();
        $archiveType = $this->releaseResolver->getFfmpegArchiveType();
        $binDir = $this->pathService->ensureBinDirectory();

        $archivePath = $binDir.DIRECTORY_SEPARATOR.'ffmpeg-archive.'.$archiveType;

        if ($progressCallback !== null) {
            $this->downloaderService->downloadWithProgress($url, $archivePath, $progressCallback);
        } else {
            $this->downloaderService->download($url, $archivePath);
        }

        $paths = $this->extractBinaries($archivePath, $binDir);

        @unlink($archivePath);

        $this->validate($paths['ffmpeg']);

        $ffmpegVersion = $this->getVersion($paths['ffmpeg']);
        $this->manifestService->setBinaryInfo('ffmpeg', $paths['ffmpeg'], $ffmpegVersion, 'downloaded');
        $this->manifestService->setBinaryInfo('ffprobe', $paths['ffprobe'], $ffmpegVersion, 'downloaded');

        return $paths;
    }

    /** @return array{ffmpeg: string, ffprobe: string} */
    public function update(?callable $progressCallback = null): array
    {
        return $this->install($progressCallback);
    }

    /** @return array{ffmpeg: string, ffprobe: string} */
    public function ensureInstalled(?string $ffmpegPath = null, ?string $ffprobePath = null): array
    {
        $ffmpeg = $this->locator->locateFfmpeg($ffmpegPath);
        $ffprobe = $this->locator->locateFfprobe($ffprobePath);

        if ($ffmpeg !== null && $ffprobe !== null) {
            return ['ffmpeg' => $ffmpeg, 'ffprobe' => $ffprobe];
        }

        if (Config::get('ffmpeg.prefer_global', true)) {
            $globalFfmpeg = $this->pathService->findInPath('ffmpeg');
            $globalFfprobe = $this->pathService->findInPath('ffprobe');

            if ($globalFfmpeg !== null && $globalFfprobe !== null) {
                return ['ffmpeg' => $globalFfmpeg, 'ffprobe' => $globalFfprobe];
            }
        }

        return $this->install();
    }

    public function validate(string $path): void
    {
        if (! is_file($path)) {
            throw BinaryValidationException::notFound($path);
        }

        if (! is_executable($path)) {
            throw BinaryValidationException::notExecutable($path);
        }

        $result = $this->processRunner->run([$path, '-version'], timeout: 10);

        if (! $result->success) {
            throw BinaryValidationException::versionCheckFailed('ffmpeg', $result->errorOutput);
        }
    }

    public function getVersion(?string $path = null): string
    {
        $binaryPath = $path ?? $this->locator->locateFfmpeg();

        if ($binaryPath === null) {
            return 'not installed';
        }

        $result = $this->processRunner->run([$binaryPath, '-version'], timeout: 10);

        if ($result->output === '') {
            return 'unknown';
        }

        $lines = explode("\n", $result->output);
        $firstLine = array_shift($lines);

        if (in_array($firstLine, [0, null, ''], true)) {
            return 'unknown';
        }

        if (preg_match('/ffmpeg version (\S+)/', $firstLine, $matches)) {
            return $matches[1];
        }

        return trim($firstLine);
    }

    public function isInstalled(?string $ffmpegPath = null, ?string $ffprobePath = null): bool
    {
        return $this->locator->locateFfmpeg($ffmpegPath) !== null
            && $this->locator->locateFfprobe($ffprobePath) !== null;
    }

    /** @return array{ffmpeg: ?string, ffprobe: ?string} */
    public function getPaths(?string $ffmpegPath = null, ?string $ffprobePath = null): array
    {
        return [
            'ffmpeg' => $this->locator->locateFfmpeg($ffmpegPath),
            'ffprobe' => $this->locator->locateFfprobe($ffprobePath),
        ];
    }

    /** @return array{ffmpeg: string, ffprobe: string} */
    private function extractBinaries(string $archivePath, string $binDir): array
    {
        $archiveType = $this->releaseResolver->getFfmpegArchiveType();
        $ext = $this->platform->getExecutableExtension();

        $ffmpegDest = $binDir.DIRECTORY_SEPARATOR.'ffmpeg'.$ext;
        $ffprobeDest = $binDir.DIRECTORY_SEPARATOR.'ffprobe'.$ext;

        if ($archiveType === 'zip') {
            $this->extractFromZip($archivePath, $binDir, $ffmpegDest, $ffprobeDest);
        } else {
            $this->extractFromTarXz($archivePath, $binDir, $ffmpegDest, $ffprobeDest);
        }

        return [
            'ffmpeg' => $ffmpegDest,
            'ffprobe' => $ffprobeDest,
        ];
    }

    private function extractFromZip(string $archivePath, string $binDir, string $ffmpegDest, string $ffprobeDest): void
    {
        $zip = new \ZipArchive;

        if ($zip->open($archivePath) !== true) {
            throw BinaryDownloadException::failedToDownload('ffmpeg', $archivePath, 'Failed to open archive');
        }

        $ext = $this->platform->getExecutableExtension();
        $ffmpegFound = false;
        $ffprobeFound = false;

        for ($i = 0; $i < $zip->numFiles; $i++) {
            $name = $zip->getNameIndex($i);

            if ($name === false) {
                continue;
            }

            $basename = basename($name);

            if ($basename === 'ffmpeg'.$ext || $basename === 'ffmpeg') {
                $content = $zip->getFromIndex($i);
                if ($content !== false) {
                    file_put_contents($ffmpegDest, $content);
                    $this->pathService->makeExecutable($ffmpegDest);
                    $ffmpegFound = true;
                }
            }

            if ($basename === 'ffprobe'.$ext || $basename === 'ffprobe') {
                $content = $zip->getFromIndex($i);
                if ($content !== false) {
                    file_put_contents($ffprobeDest, $content);
                    $this->pathService->makeExecutable($ffprobeDest);
                    $ffprobeFound = true;
                }
            }
        }

        $zip->close();

        if (! $ffmpegFound) {
            throw BinaryDownloadException::failedToDownload('ffmpeg', $archivePath, 'ffmpeg binary not found in archive');
        }
    }

    private function extractFromTarXz(string $archivePath, string $binDir, string $ffmpegDest, string $ffprobeDest): void
    {
        $tempDir = $binDir.DIRECTORY_SEPARATOR.'ffmpeg-extract-'.uniqid();
        mkdir($tempDir, 0755, true);

        $result = $this->processRunner->run([
            'tar', '-xf', $archivePath, '-C', $tempDir,
        ], timeout: 120);

        if (! $result->success) {
            $this->cleanupDir($tempDir);
            throw BinaryDownloadException::failedToDownload('ffmpeg', $archivePath, 'Failed to extract archive');
        }

        $ffmpegSource = $this->findBinaryInDir($tempDir, 'ffmpeg');
        $ffprobeSource = $this->findBinaryInDir($tempDir, 'ffprobe');

        if ($ffmpegSource !== null) {
            copy($ffmpegSource, $ffmpegDest);
            $this->pathService->makeExecutable($ffmpegDest);
        }

        if ($ffprobeSource !== null) {
            copy($ffprobeSource, $ffprobeDest);
            $this->pathService->makeExecutable($ffprobeDest);
        }

        $this->cleanupDir($tempDir);

        if ($ffmpegSource === null) {
            throw BinaryDownloadException::failedToDownload('ffmpeg', $archivePath, 'ffmpeg binary not found in archive');
        }
    }

    private function findBinaryInDir(string $dir, string $name): ?string
    {
        $iterator = new \RecursiveIteratorIterator(
            new \RecursiveDirectoryIterator($dir, \RecursiveDirectoryIterator::SKIP_DOTS)
        );

        foreach ($iterator as $file) {
            if ($file->isFile() && $file->getFilename() === $name) {
                return $file->getPathname();
            }
        }

        return null;
    }

    private function cleanupDir(string $dir): void
    {
        if (! is_dir($dir)) {
            return;
        }

        $iterator = new \RecursiveIteratorIterator(
            new \RecursiveDirectoryIterator($dir, \RecursiveDirectoryIterator::SKIP_DOTS),
            \RecursiveIteratorIterator::CHILD_FIRST
        );

        foreach ($iterator as $file) {
            if ($file->isDir()) {
                rmdir($file->getPathname());
            } else {
                unlink($file->getPathname());
            }
        }

        rmdir($dir);
    }
}
