<?php

declare(strict_types=1);

namespace YtPilot\Services\Console;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
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

final class VersionCommand extends Command
{
    /** @var string */
    protected static $defaultName = 'version';
    /** @var string */
    protected static $defaultDescription = 'Show versions of YtPilot and installed binaries';

    protected function configure(): void
    {
        $this
            ->setName('version')
            ->setDescription('Show versions of YtPilot and installed binaries');
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);

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

        $io->title('YtPilot Version Information');

        $io->definitionList(
            ['YtPilot' => '1.0.0'],
            ['PHP' => PHP_VERSION],
            ['Platform' => $platform->getPlatformIdentifier()],
        );

        $io->section('Binary Versions');

        $ytDlpVersion = 'not installed';
        $ytDlpPath = 'N/A';
        if ($ytDlpService->isInstalled()) {
            $ytDlpVersion = $ytDlpService->getVersion();
            $ytDlpPath = $ytDlpService->getPath() ?? 'N/A';
        }

        $ffmpegVersion = $ffmpegService->getVersion();
        $ffmpegPaths = $ffmpegService->getPaths();

        $io->table(
            ['Binary', 'Version', 'Path'],
            [
                ['yt-dlp', $ytDlpVersion, $ytDlpPath],
                ['ffmpeg', $ffmpegVersion, $ffmpegPaths['ffmpeg'] ?? 'N/A'],
                ['ffprobe', $ffmpegVersion, $ffmpegPaths['ffprobe'] ?? 'N/A'],
            ]
        );

        return Command::SUCCESS;
    }
}
