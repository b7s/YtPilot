<?php

declare(strict_types=1);

namespace YtPilot\Services\Parsing;

use YtPilot\DTO\SubtitleList;

final class SubtitlesParserService
{
    private const string SUBTITLE_REGEX = '/^(?<lang>[a-zA-Z]{2,3}(?:-[a-zA-Z0-9]+)?)\s+(?<formats>.+)$/m';

    public function parse(string $output): SubtitleList
    {
        $manual = [];
        $automatic = [];
        $currentSection = null;

        $lines = explode("\n", $output);

        foreach ($lines as $line) {
            $line = trim($line);

            if ($line === '' || str_starts_with($line, '[')) {
                continue;
            }

            if (str_contains(strtolower($line), 'available subtitles') ||
                str_contains(strtolower($line), 'manual subtitles')) {
                $currentSection = 'manual';

                continue;
            }

            if (str_contains(strtolower($line), 'automatic captions') ||
                str_contains(strtolower($line), 'auto-generated')) {
                $currentSection = 'automatic';

                continue;
            }

            if (str_contains($line, 'Language') || str_starts_with($line, '---')) {
                continue;
            }

            $parsed = $this->parseLine($line);

            if ($parsed === null) {
                continue;
            }

            if ($currentSection === 'automatic') {
                $automatic[$parsed['lang']] = $parsed['formats'];
            } else {
                $manual[$parsed['lang']] = $parsed['formats'];
            }
        }

        return SubtitleList::fromParsed($manual, $automatic);
    }

    /** @return array{lang: string, formats: list<string>}|null */
    public function parseLine(string $line): ?array
    {
        if (preg_match(self::SUBTITLE_REGEX, $line, $matches)) {
            $formats = array_map('trim', preg_split('/[,\s]+/', $matches['formats']));
            $formats = array_filter($formats, fn ($f) => $f !== '');

            return [
                'lang' => $matches['lang'],
                'formats' => array_values($formats),
            ];
        }

        $parts = preg_split('/\s+/', $line, 2);

        if (count($parts) >= 1 && preg_match('/^[a-zA-Z]{2,3}(-[a-zA-Z0-9]+)?$/', $parts[0])) {
            return [
                'lang' => $parts[0],
                'formats' => isset($parts[1]) ? array_map('trim', explode(',', $parts[1])) : [],
            ];
        }

        return null;
    }

    /** @return list<string> */
    public function extractLanguages(SubtitleList $subtitles): array
    {
        return $subtitles->getLanguages();
    }
}
