<?php

declare(strict_types=1);

namespace YtPilot\Exceptions;

use RuntimeException;

final class ProcessFailedException extends RuntimeException
{
    public function __construct(
        string $message,
        private readonly string $command = '',
        private readonly string $output = '',
        private readonly string $errorOutput = '',
        private readonly int $exitCode = 1,
    ) {
        parent::__construct($message, $exitCode);
    }

    public static function fromProcess(string $command, string $output, string $errorOutput, int $exitCode): self
    {
        $message = "Process failed with exit code {$exitCode}";

        if ($errorOutput !== '') {
            $message .= ": {$errorOutput}";
        }

        return new self($message, $command, $output, $errorOutput, $exitCode);
    }

    public static function timeout(string $command, int $timeout): self
    {
        return new self("Process timed out after {$timeout} seconds", $command);
    }

    public function getCommand(): string
    {
        return $this->command;
    }

    public function getOutput(): string
    {
        return $this->output;
    }

    public function getErrorOutput(): string
    {
        return $this->errorOutput;
    }

    public function getExitCode(): int
    {
        return $this->exitCode;
    }
}
