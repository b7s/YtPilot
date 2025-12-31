<?php

declare(strict_types=1);

namespace YtPilot\Exceptions;

use RuntimeException;

final class MissingUrlException extends RuntimeException
{
    public static function required(): self
    {
        return new self('URL is required. Use ->url($url) before calling this method.');
    }
}
