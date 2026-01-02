<?php

declare(strict_types=1);

namespace YtPilot\DTO;

final readonly class PlatformConfig
{
    /**
     * @param  array<string, string>  $extractorArgs
     */
    public function __construct(
        public string $platform,
        public ?string $username = null,
        public ?string $password = null,
        public ?string $sessionId = null,
        public array $extractorArgs = [],
    ) {}

    /**
     * @return list<string>
     */
    public function toCommandArgs(): array
    {
        $args = [];

        if ($this->username !== null) {
            $args[] = '--username';
            $args[] = $this->username;
        }

        if ($this->password !== null) {
            $args[] = '--password';
            $args[] = $this->password;
        }

        foreach ($this->extractorArgs as $key => $value) {
            $args[] = '--extractor-args';
            $args[] = "{$this->platform}:{$key}={$value}";
        }

        return $args;
    }
}
