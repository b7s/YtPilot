<?php

declare(strict_types=1);

namespace YtPilot\Services\Platform;

final class PlatformService
{
    public const string OS_LINUX = 'linux';
    public const string OS_WINDOWS = 'windows';
    public const string OS_MACOS = 'darwin';

    public const string ARCH_X64 = 'x64';
    public const string ARCH_ARM64 = 'arm64';
    public const string ARCH_X86 = 'x86';

    public function getOs(): string
    {
        return match (PHP_OS_FAMILY) {
            'Windows' => self::OS_WINDOWS,
            'Darwin' => self::OS_MACOS,
            default => self::OS_LINUX,
        };
    }

    public function getArch(): string
    {
        $arch = php_uname('m');

        return match (true) {
            str_contains($arch, 'arm64'), str_contains($arch, 'aarch64') => self::ARCH_ARM64,
            str_contains($arch, '64') => self::ARCH_X64,
            default => self::ARCH_X86,
        };
    }

    public function isWindows(): bool
    {
        return $this->getOs() === self::OS_WINDOWS;
    }

    public function isLinux(): bool
    {
        return $this->getOs() === self::OS_LINUX;
    }

    public function isMacOs(): bool
    {
        return $this->getOs() === self::OS_MACOS;
    }

    public function isMusl(): bool
    {
        if (!$this->isLinux()) {
            return false;
        }

        $lddOutput = @shell_exec('ldd --version 2>&1') ?? '';

        return str_contains(strtolower($lddOutput), 'musl');
    }

    public function getExecutableExtension(): string
    {
        return $this->isWindows() ? '.exe' : '';
    }

    public function getPlatformIdentifier(): string
    {
        return sprintf('%s-%s%s',
            $this->getOs(),
            $this->getArch(),
            $this->isMusl() ? '-musl' : ''
        );
    }
}
