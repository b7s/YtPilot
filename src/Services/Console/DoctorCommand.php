<?php

declare(strict_types=1);

namespace YtPilot\Services\Console;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;
use YtPilot\Config;
use YtPilot\Services\Binary\BinaryLocatorService;
use YtPilot\Services\Binary\FfmpegBinaryService;
use YtPilot\Services\Binary\ManifestService;
use YtPilot\Services\Binary\ReleaseResolverService;
use YtPilot\Services\Binary\YtDlpBinaryService;
use YtPilot\Services\Filesystem\PathService;
use YtPilot\Services\Http\DownloaderService;
use YtPilot\Services\Platform\PlatformService;
use YtPilot\Services\Process\ProcessRunnerService;

final class DoctorCommand extends Command
{
    /** @var string */
    protected static $defaultName = 'doctor';
    /** @var string */
    protected static $defaultDescription = 'Diagnose YtPilot installation and configuration';

    protected function configure(): void
    {
        $this
            ->setName('doctor')
            ->setDescription('Diagnose YtPilot installation and configuration');
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);
        $io->title('YtPilot Doctor');

        $platform = new PlatformService();
        $pathService = new PathService($platform);
        $processRunner = new ProcessRunnerService();
        $downloader = new DownloaderService($pathService);
        $releaseResolver = new ReleaseResolverService($platform);
        $manifestService = new ManifestService($pathService);
        $locator = new BinaryLocatorService($pathService);

        $ytDlpService = new YtDlpBinaryService(
            $pathService,
            $downloader,
            $releaseResolver,
            $locator,
            $manifestService,
            $processRunner,
        );

        $ffmpegService = new FfmpegBinaryService(
            $pathService,
            $downloader,
            $releaseResolver,
            $locator,
            $manifestService,
            $processRunner,
            $platform,
        );

        $issues = [];

        $io->section('System Information');
        $io->table(
            ['Property', 'Value'],
            [
                ['Operating System', $platform->getOs()],
                ['Architecture', $platform->getArch()],
                ['Platform ID', $platform->getPlatformIdentifier()],
                ['PHP Version', PHP_VERSION],
                ['Bin Directory', $pathService->getBinDirectory()],
            ]
        );

        $io->section('Binary Status');

        $ytDlpPath = $locator->locateYtDlp();
        $ytDlpStatus = $ytDlpPath !== null ? '✓ Installed' : '✗ Not found';
        $ytDlpSource = $this->determineBinarySource($ytDlpPath, $pathService);

        $ffmpegPath = $locator->locateFfmpeg();
        $ffmpegStatus = $ffmpegPath !== null ? '✓ Installed' : '✗ Not found';
        $ffmpegSource = $this->determineBinarySource($ffmpegPath, $pathService);

        $ffprobePath = $locator->locateFfprobe();
        $ffprobeStatus = $ffprobePath !== null ? '✓ Installed' : '✗ Not found';
        $ffprobeSource = $this->determineBinarySource($ffprobePath, $pathService);

        $io->table(
            ['Binary', 'Status', 'Source', 'Path'],
            [
                ['yt-dlp', $ytDlpStatus, $ytDlpSource, $ytDlpPath ?? 'N/A'],
                ['ffmpeg', $ffmpegStatus, $ffmpegSource, $ffmpegPath ?? 'N/A'],
                ['ffprobe', $ffprobeStatus, $ffprobeSource, $ffprobePath ?? 'N/A'],
            ]
        );

        if ($ytDlpPath === null) {
            $issues[] = 'yt-dlp is not installed. Run: ytpilot install';
        }

        if ($ffmpegPath === null) {
            $issues[] = 'ffmpeg is not installed. Run: ytpilot install';
        }

        if ($ffprobePath === null) {
            $issues[] = 'ffprobe is not installed. Run: ytpilot install';
        }

        $io->section('Permissions');

        $binDir = $pathService->getBinDirectory();
        $binDirExists = is_dir($binDir);
        $binDirWritable = $binDirExists && is_writable($binDir);

        $io->table(
            ['Path', 'Exists', 'Writable'],
            [
                [$binDir, $binDirExists ? '✓' : '✗', $binDirWritable ? '✓' : '✗'],
            ]
        );

        if (!$binDirWritable && !$binDirExists) {
            $parentDir = dirname($binDir);
            if (!is_writable($parentDir)) {
                $issues[] = "Cannot create bin directory: {$binDir}";
            }
        }

        $io->section('Version Check');

        if ($ytDlpPath !== null) {
            try {
                $version = $ytDlpService->getVersion();
                $io->text("yt-dlp version: {$version}");
            } catch (\Throwable $e) {
                $issues[] = 'yt-dlp version check failed: ' . $e->getMessage();
                $io->text('yt-dlp version: ERROR');
            }
        }

        if ($ffmpegPath !== null) {
            try {
                $version = $ffmpegService->getVersion();
                $io->text("ffmpeg version: {$version}");
            } catch (\Throwable $e) {
                $issues[] = 'ffmpeg version check failed: ' . $e->getMessage();
                $io->text('ffmpeg version: ERROR');
            }
        }

        $io->section('Configuration');

        $io->table(
            ['Setting', 'Value'],
            [
                ['bin_path', Config::get('bin_path', '.ytpilot/bin')],
                ['yt_dlp.path', Config::get('yt_dlp.path') ?? '(auto)'],
                ['ffmpeg.path', Config::get('ffmpeg.path') ?? '(auto)'],
                ['ffmpeg.probe_path', Config::get('ffmpeg.probe_path') ?? '(auto)'],
                ['ffmpeg.prefer_global', Config::get('ffmpeg.prefer_global', true) ? 'true' : 'false'],
                ['ffmpeg.enabled', Config::get('ffmpeg.enabled', true) ? 'true' : 'false'],
                ['timeout', (string) Config::get('timeout', 300)],
            ]
        );

        $io->section('Diagnosis Result');

        if ($issues === []) {
            $io->success('All checks passed! YtPilot is ready to use.');

            return Command::SUCCESS;
        }

        $io->warning('Issues found:');
        $io->listing($issues);

        return Command::FAILURE;
    }

    private function determineBinarySource(?string $path, PathService $pathService): string
    {
        if ($path === null) {
            return 'N/A';
        }

        $binDir = $pathService->getBinDirectory();

        if (str_starts_with($path, $binDir)) {
            return 'Local (downloaded)';
        }

        return 'System (global)';
    }
}
