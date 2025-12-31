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
        $arch = strtolower(php_uname('m'));

        return match (true) {
            // Check ARM variants first (before checking for '64')
            str_contains($arch, 'arm64'), 
            str_contains($arch, 'aarch64'),
            str_contains($arch, 'armv8') => self::ARCH_ARM64,
            
            // Check x64 variants
            str_contains($arch, 'x86_64'),
            str_contains($arch, 'amd64'),
            str_contains($arch, 'x64') => self::ARCH_X64,
            
            // Check x86 variants
            str_contains($arch, 'i386'),
            str_contains($arch, 'i686'),
            str_contains($arch, 'x86') => self::ARCH_X86,
            
            // Fallback: if contains '64' but not matched above
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

        // Method 1: Check ldd version
        $lddOutput = @shell_exec('ldd --version 2>&1') ?? '';
        if (str_contains(strtolower($lddOutput), 'musl')) {
            return true;
        }

        // Method 2: Check if musl libc exists
        if (file_exists('/lib/ld-musl-x86_64.so.1') || 
            file_exists('/lib/ld-musl-aarch64.so.1') ||
            file_exists('/lib/libc.musl-x86_64.so.1')) {
            return true;
        }

        // Method 3: Check getconf (more reliable on Alpine)
        $getconfOutput = @shell_exec('getconf GNU_LIBC_VERSION 2>&1') ?? '';
        if (str_contains($getconfOutput, 'not valid') || $getconfOutput === '') {
            // If getconf doesn't recognize GNU_LIBC_VERSION, likely musl
            $lddPath = @shell_exec('which ldd 2>/dev/null') ?? '';
            if ($lddPath !== '') {
                return true; // Has ldd but no GNU libc version = likely musl
            }
        }

        return false;
    }

    public function getExecutableExtension(): string
    {
        return $this->isWindows() ? '.exe' : '';
    }

    public function getPlatformIdentifier(): string
    {
        return \sprintf('%s-%s%s',
            $this->getOs(),
            $this->getArch(),
            $this->isMusl() ? '-musl' : ''
        );
    }
}
