<?php

declare(strict_types=1);

namespace YtPilot\Services\Binary;

use YtPilot\Config;
use YtPilot\Exceptions\BinaryValidationException;
use YtPilot\Services\Filesystem\PathService;

final class BinaryLocatorService
{
    public function __construct(
        private readonly PathService $pathService,
    ) {}

    public function locateYtDlp(?string $runtimePath = null): ?string
    {
        if ($runtimePath !== null && $this->isValidBinary($runtimePath)) {
            return $runtimePath;
        }

        $configPath = Config::get('yt_dlp.path');
        if ($configPath !== null && $this->isValidBinary($configPath)) {
            return $configPath;
        }

        $systemPath = $this->pathService->findInPath('yt-dlp');
        if ($systemPath !== null) {
            return $systemPath;
        }

        $localPath = $this->pathService->getYtDlpPath();
        if ($this->isValidBinary($localPath)) {
            return $localPath;
        }

        return null;
    }

    public function locateFfmpeg(?string $runtimePath = null): ?string
    {
        if ($runtimePath !== null && $this->isValidBinary($runtimePath)) {
            return $runtimePath;
        }

        $configPath = Config::get('ffmpeg.path');
        if ($configPath !== null && $this->isValidBinary($configPath)) {
            return $configPath;
        }

        if (Config::get('ffmpeg.prefer_global', true)) {
            $systemPath = $this->pathService->findInPath('ffmpeg');
            if ($systemPath !== null) {
                return $systemPath;
            }
        }

        $localPath = $this->pathService->getFfmpegPath();
        if ($this->isValidBinary($localPath)) {
            return $localPath;
        }

        if (!Config::get('ffmpeg.prefer_global', true)) {
            $systemPath = $this->pathService->findInPath('ffmpeg');
            if ($systemPath !== null) {
                return $systemPath;
            }
        }

        return null;
    }

    public function locateFfprobe(?string $runtimePath = null): ?string
    {
        if ($runtimePath !== null && $this->isValidBinary($runtimePath)) {
            return $runtimePath;
        }

        $configPath = Config::get('ffmpeg.probe_path');
        if ($configPath !== null && $this->isValidBinary($configPath)) {
            return $configPath;
        }

        if (Config::get('ffmpeg.prefer_global', true)) {
            $systemPath = $this->pathService->findInPath('ffprobe');
            if ($systemPath !== null) {
                return $systemPath;
            }
        }

        $localPath = $this->pathService->getFfprobePath();
        if ($this->isValidBinary($localPath)) {
            return $localPath;
        }

        if (!Config::get('ffmpeg.prefer_global', true)) {
            $systemPath = $this->pathService->findInPath('ffprobe');
            if ($systemPath !== null) {
                return $systemPath;
            }
        }

        return null;
    }

    public function requireYtDlp(?string $runtimePath = null): string
    {
        $path = $this->locateYtDlp($runtimePath);

        if ($path === null) {
            throw BinaryValidationException::notFound('yt-dlp');
        }

        return $path;
    }

    public function requireFfmpeg(?string $runtimePath = null): string
    {
        $path = $this->locateFfmpeg($runtimePath);

        if ($path === null) {
            throw BinaryValidationException::notFound('ffmpeg');
        }

        return $path;
    }

    public function requireFfprobe(?string $runtimePath = null): string
    {
        $path = $this->locateFfprobe($runtimePath);

        if ($path === null) {
            throw BinaryValidationException::notFound('ffprobe');
        }

        return $path;
    }

    public function isValidBinary(string $path): bool
    {
        return is_file($path) && is_executable($path);
    }
}
