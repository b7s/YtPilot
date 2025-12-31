<?php

declare(strict_types=1);

namespace YtPilot\Services\Filesystem;

use YtPilot\Config;
use YtPilot\Services\Platform\PlatformService;

final class PathService
{
    public function __construct(
        private readonly PlatformService $platform,
    ) {}

    public function getBinDirectory(): string
    {
        $binPath = Config::get('bin_path', '.ytpilot/bin');

        if (!str_starts_with($binPath, '/') && !str_starts_with($binPath, '\\')) {
            $binPath = getcwd() . DIRECTORY_SEPARATOR . $binPath;
        }

        return rtrim($binPath, DIRECTORY_SEPARATOR);
    }

    public function ensureBinDirectory(): string
    {
        $binDir = $this->getBinDirectory();

        if (!is_dir($binDir)) {
            mkdir($binDir, 0755, true);
        }

        return $binDir;
    }

    public function getYtDlpPath(): string
    {
        $filename = $this->platform->isWindows() ? 'yt-dlp.exe' : 'yt-dlp';

        return $this->getBinDirectory() . DIRECTORY_SEPARATOR . $filename;
    }

    public function getFfmpegPath(): string
    {
        $filename = $this->platform->isWindows() ? 'ffmpeg.exe' : 'ffmpeg';

        return $this->getBinDirectory() . DIRECTORY_SEPARATOR . $filename;
    }

    public function getFfprobePath(): string
    {
        $filename = $this->platform->isWindows() ? 'ffprobe.exe' : 'ffprobe';

        return $this->getBinDirectory() . DIRECTORY_SEPARATOR . $filename;
    }

    public function getManifestPath(): string
    {
        return $this->getBinDirectory() . DIRECTORY_SEPARATOR . 'manifest.json';
    }

    public function findInPath(string $binary): ?string
    {
        $pathEnv = getenv('PATH') ?: '';
        $separator = $this->platform->isWindows() ? ';' : ':';
        $extension = $this->platform->getExecutableExtension();

        foreach (explode($separator, $pathEnv) as $dir) {
            $fullPath = rtrim($dir, DIRECTORY_SEPARATOR) . DIRECTORY_SEPARATOR . $binary . $extension;

            if (is_file($fullPath) && is_executable($fullPath)) {
                return $fullPath;
            }
        }

        $which = $this->platform->isWindows() ? 'where' : 'which';
        $result = @shell_exec("{$which} {$binary} 2>/dev/null");

        if ($result !== null && $result !== '') {
            $path = trim(explode("\n", $result)[0]);

            if (is_file($path) && is_executable($path)) {
                return $path;
            }
        }

        return null;
    }

    public function makeExecutable(string $path): void
    {
        if (!$this->platform->isWindows() && is_file($path)) {
            chmod($path, 0755);
        }
    }
}
