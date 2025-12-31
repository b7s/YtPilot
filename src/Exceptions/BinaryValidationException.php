<?php

declare(strict_types=1);

namespace YtPilot\Exceptions;

use RuntimeException;

final class BinaryValidationException extends RuntimeException
{
    public static function notFound(string $binary): self
    {
        return new self("Binary '{$binary}' not found and could not be resolved");
    }

    public static function notExecutable(string $path): self
    {
        return new self("Binary at '{$path}' is not executable");
    }

    public static function versionCheckFailed(string $binary, string $output): self
    {
        return new self("Version check failed for '{$binary}': {$output}");
    }

    public static function unsupportedPlatform(string $os, string $arch): self
    {
        return new self("Unsupported platform: {$os} ({$arch})");
    }
}
