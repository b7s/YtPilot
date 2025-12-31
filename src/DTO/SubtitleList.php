<?php

declare(strict_types=1);

namespace YtPilot\DTO;

final readonly class SubtitleList
{
    /**
     * @param array<string, list<string>> $manual
     * @param array<string, list<string>> $automatic
     */
    public function __construct(
        public array $manual,
        public array $automatic,
    ) {}

    /**
     * @param array<string, list<string>> $manual
     * @param array<string, list<string>> $automatic
     */
    public static function fromParsed(array $manual, array $automatic): self
    {
        return new self(
            manual: $manual,
            automatic: $automatic,
        );
    }

    /** @return list<string> */
    public function getLanguages(): array
    {
        return array_unique(array_merge(
            array_keys($this->manual),
            array_keys($this->automatic),
        ));
    }

    public function hasLanguage(string $lang): bool
    {
        return isset($this->manual[$lang]) || isset($this->automatic[$lang]);
    }

    /** @return array<string, array<string, list<string>>> */
    public function toArray(): array
    {
        return [
            'manual' => $this->manual,
            'automatic' => $this->automatic,
        ];
    }
}
