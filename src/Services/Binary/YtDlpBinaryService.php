<?php

declare(strict_types=1);

namespace YtPilot\Services\Binary;

use YtPilot\Exceptions\BinaryValidationException;
use YtPilot\Services\Filesystem\PathService;
use YtPilot\Services\Http\DownloaderService;
use YtPilot\Services\Process\ProcessRunnerService;

final class YtDlpBinaryService
{
    public function __construct(
        private readonly PathService $pathService,
        private readonly DownloaderService $downloaderService,
        private readonly ReleaseResolverService $releaseResolver,
        private readonly BinaryLocatorService $locator,
        private readonly ManifestService $manifestService,
        private readonly ProcessRunnerService $processRunner,
    ) {}

    public function install(?callable $progressCallback = null): string
    {
        $url = $this->releaseResolver->getYtDlpDownloadUrl();
        $destination = $this->pathService->getYtDlpPath();

        if ($progressCallback !== null) {
            $this->downloaderService->downloadWithProgress($url, $destination, $progressCallback);
        } else {
            $this->downloaderService->download($url, $destination);
        }

        $this->validate($destination);

        $version = $this->getVersion($destination);
        $this->manifestService->setBinaryInfo('yt-dlp', $destination, $version, 'downloaded');

        return $destination;
    }

    public function update(?callable $progressCallback = null): string
    {
        return $this->install($progressCallback);
    }

    public function ensureInstalled(?string $runtimePath = null): string
    {
        $existing = $this->locator->locateYtDlp($runtimePath);

        if ($existing !== null) {
            return $existing;
        }

        return $this->install();
    }

    public function validate(string $path): void
    {
        if (!is_file($path)) {
            throw BinaryValidationException::notFound($path);
        }

        if (!is_executable($path)) {
            throw BinaryValidationException::notExecutable($path);
        }

        $result = $this->processRunner->run([$path, '--version'], timeout: 10);

        if (!$result->success) {
            throw BinaryValidationException::versionCheckFailed('yt-dlp', $result->errorOutput);
        }
    }

    public function getVersion(?string $path = null): string
    {
        $binaryPath = $path ?? $this->locator->requireYtDlp();
        $result = $this->processRunner->run([$binaryPath, '--version'], timeout: 10);

        return trim($result->output);
    }

    public function isInstalled(?string $runtimePath = null): bool
    {
        return $this->locator->locateYtDlp($runtimePath) !== null;
    }

    public function getPath(?string $runtimePath = null): ?string
    {
        return $this->locator->locateYtDlp($runtimePath);
    }
}
