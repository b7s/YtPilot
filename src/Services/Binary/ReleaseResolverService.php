<?php

declare(strict_types=1);

namespace YtPilot\Services\Binary;

use YtPilot\Exceptions\BinaryValidationException;
use YtPilot\Services\Platform\PlatformService;

final class ReleaseResolverService
{
    private const string YTDLP_BASE_URL = 'https://github.com/yt-dlp/yt-dlp/releases/latest/download';
    private const string FFMPEG_LINUX_URL = 'https://github.com/yt-dlp/FFmpeg-Builds/releases/download/latest/ffmpeg-master-latest-linux64-gpl.tar.xz';
    private const string FFMPEG_WINDOWS_URL = 'https://github.com/yt-dlp/FFmpeg-Builds/releases/download/latest/ffmpeg-master-latest-win64-gpl.zip';
    private const string FFMPEG_MACOS_URL = 'https://evermeet.cx/ffmpeg/getrelease/zip';

    public function __construct(
        private readonly PlatformService $platform,
    ) {}

    public function getYtDlpDownloadUrl(): string
    {
        return match ($this->platform->getOs()) {
            PlatformService::OS_WINDOWS => self::YTDLP_BASE_URL . '/yt-dlp.exe',
            PlatformService::OS_MACOS => self::YTDLP_BASE_URL . '/yt-dlp_macos',
            PlatformService::OS_LINUX => self::YTDLP_BASE_URL . '/yt-dlp',
            default => throw BinaryValidationException::unsupportedPlatform(
                $this->platform->getOs(),
                $this->platform->getArch()
            ),
        };
    }

    public function getYtDlpBinaryName(): string
    {
        return match ($this->platform->getOs()) {
            PlatformService::OS_WINDOWS => 'yt-dlp.exe',
            PlatformService::OS_MACOS => 'yt-dlp_macos',
            default => 'yt-dlp',
        };
    }

    public function getFfmpegDownloadUrl(): string
    {
        return match ($this->platform->getOs()) {
            PlatformService::OS_WINDOWS => self::FFMPEG_WINDOWS_URL,
            PlatformService::OS_MACOS => self::FFMPEG_MACOS_URL,
            PlatformService::OS_LINUX => self::FFMPEG_LINUX_URL,
            default => throw BinaryValidationException::unsupportedPlatform(
                $this->platform->getOs(),
                $this->platform->getArch()
            ),
        };
    }

    public function getFfmpegArchiveType(): string
    {
        return match ($this->platform->getOs()) {
            PlatformService::OS_WINDOWS => 'zip',
            PlatformService::OS_MACOS => 'zip',
            PlatformService::OS_LINUX => 'tar.xz',
            default => 'tar.xz',
        };
    }
}
