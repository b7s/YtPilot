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

final class UpdateCommand extends Command
{
    /** @var string */
    protected static $defaultName = 'update';
    /** @var string */
    protected static $defaultDescription = 'Update yt-dlp and ffmpeg binaries to latest versions';

    protected function configure(): void
    {
        $this
            ->setName('update')
            ->setDescription('Update yt-dlp and ffmpeg binaries to latest versions')
            ->addOption('skip-ffmpeg', null, InputOption::VALUE_NONE, 'Skip ffmpeg update');
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);
        $io->title('YtPilot Binary Updater');

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

        $skipFfmpeg = $input->getOption('skip-ffmpeg');

        $io->section('Updating yt-dlp');

        $currentVersion = 'not installed';
        if ($ytDlpService->isInstalled()) {
            $currentVersion = $ytDlpService->getVersion();
        }
        $io->text("Current version: {$currentVersion}");

        try {
            $io->text('Downloading latest yt-dlp...');
            $path = $ytDlpService->update(function ($downloaded, $total) use ($io): void {
                $percent = $total > 0 ? round(($downloaded / $total) * 100) : 0;
                $io->write("\r  Progress: {$percent}%");
            });
            $io->newLine();

            $newVersion = $ytDlpService->getVersion();
            $io->success("yt-dlp updated to {$newVersion}");
        } catch (\Throwable $e) {
            $io->error('Failed to update yt-dlp: ' . $e->getMessage());

            return Command::FAILURE;
        }

        if (!$skipFfmpeg) {
            $io->section('Updating ffmpeg');

            $currentFfmpegVersion = $ffmpegService->getVersion();
            $io->text("Current version: {$currentFfmpegVersion}");

            try {
                $io->text('Downloading latest ffmpeg...');
                $paths = $ffmpegService->update(function ($downloaded, $total) use ($io): void {
                    $percent = $total > 0 ? round(($downloaded / $total) * 100) : 0;
                    $io->write("\r  Progress: {$percent}%");
                });
                $io->newLine();

                $newVersion = $ffmpegService->getVersion();
                $io->success("ffmpeg updated to {$newVersion}");
            } catch (\Throwable $e) {
                $io->warning('Failed to update ffmpeg: ' . $e->getMessage());
            }
        }

        $io->success('Update complete!');

        return Command::SUCCESS;
    }
}
