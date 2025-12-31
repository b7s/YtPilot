<?php

declare(strict_types=1);

namespace YtPilot\Services\Console;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;
use YtPilot\Services\Binary\FfmpegBinaryService;
use YtPilot\Services\Binary\YtDlpBinaryService;
use YtPilot\Services\Binary\BinaryLocatorService;
use YtPilot\Services\Binary\ManifestService;
use YtPilot\Services\Binary\ReleaseResolverService;
use YtPilot\Services\Filesystem\PathService;
use YtPilot\Services\Http\DownloaderService;
use YtPilot\Services\Platform\PlatformService;
use YtPilot\Services\Process\ProcessRunnerService;

final class InstallCommand extends Command
{
    /** @var string */
    protected static $defaultName = 'install';
    /** @var string */
    protected static $defaultDescription = 'Install yt-dlp and ffmpeg binaries';

    protected function configure(): void
    {
        $this
            ->setName('install')
            ->setDescription('Install yt-dlp and ffmpeg binaries')
            ->addOption('skip-ffmpeg', null, InputOption::VALUE_NONE, 'Skip ffmpeg installation')
            ->addOption('force', 'f', InputOption::VALUE_NONE, 'Force reinstall even if binaries exist');
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);
        $io->title('YtPilot Binary Installer');

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

        $force = $input->getOption('force');
        $skipFfmpeg = $input->getOption('skip-ffmpeg');

        $io->section('Installing yt-dlp');

        if (!$force && $ytDlpService->isInstalled()) {
            $io->success('yt-dlp is already installed: ' . $ytDlpService->getPath());
        } else {
            $io->text('Downloading yt-dlp...');

            try {
                $path = $ytDlpService->install(function ($downloaded, $total) use ($io): void {
                    $percent = $total > 0 ? round(($downloaded / $total) * 100) : 0;
                    $io->write("\r  Progress: {$percent}%");
                });
                $io->newLine();
                $io->success("yt-dlp installed: {$path}");
            } catch (\Throwable $e) {
                $io->error('Failed to install yt-dlp: ' . $e->getMessage());

                return Command::FAILURE;
            }
        }

        if (!$skipFfmpeg) {
            $io->section('Installing ffmpeg');

            if (!$force && $ffmpegService->isInstalled()) {
                $paths = $ffmpegService->getPaths();
                $io->success('ffmpeg is already installed: ' . ($paths['ffmpeg'] ?? 'unknown'));
            } else {
                $io->text('Downloading ffmpeg (this may take a while)...');

                try {
                    $paths = $ffmpegService->install(function ($downloaded, $total) use ($io): void {
                        $percent = $total > 0 ? round(($downloaded / $total) * 100) : 0;
                        $io->write("\r  Progress: {$percent}%");
                    });
                    $io->newLine();
                    $io->success("ffmpeg installed: {$paths['ffmpeg']}");
                    $io->success("ffprobe installed: {$paths['ffprobe']}");
                } catch (\Throwable $e) {
                    $io->warning('Failed to install ffmpeg: ' . $e->getMessage());
                    $io->text('You can install ffmpeg manually or use system ffmpeg.');
                }
            }
        }

        $io->success('Installation complete!');

        return Command::SUCCESS;
    }
}
