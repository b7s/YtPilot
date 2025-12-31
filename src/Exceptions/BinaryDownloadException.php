<?php

declare(strict_types=1);

namespace YtPilot\Exceptions;

use RuntimeException;

final class BinaryDownloadException extends RuntimeException
{
    public static function failedToDownload(string $binary, string $url, string $reason = ''): self
    {
        $message = "Failed to download {$binary} from {$url}";

        if ($reason !== '') {
            $message .= ": {$reason}";
        }

        return new self($message);
    }

    public static function invalidChecksum(string $binary): self
    {
        return new self("Downloaded {$binary} binary has invalid checksum");
    }

    public static function unableToWrite(string $path): self
    {
        return new self("Unable to write binary to {$path}");
    }
}
