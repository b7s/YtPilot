<?php

declare(strict_types=1);

namespace YtPilot\Services\Parsing;

use YtPilot\DTO\FormatItem;

final class FormatsParserService
{
    private const string FORMAT_REGEX = '/^(?<id>\S+)\s+(?<ext>\S+)\s+(?<resolution>[\dx]+|audio only)\s*(?<fps>\d+)?\s*[│|]\s*(?<filesize>[\d.]+(?:MiB|GiB|KiB|B))?\s*~?\s*(?<tbr>[\d.]+[kKMG]?i?b?\/s|[\d.]+k)?\s*(?<proto>\S+)?\s*[│|]?\s*(?<vcodec>\S+)?\s*(?<vbr>[\d.]+k)?\s*(?<acodec>\S+)?\s*(?<abr>[\d.]+k)?/m';

    /** @return list<FormatItem> */
    public function parse(string $output): array
    {
        $formats = [];
        $lines = explode("\n", $output);
        $inFormatSection = false;

        foreach ($lines as $line) {
            $line = trim($line);

            if (str_contains($line, 'ID') && str_contains($line, 'EXT')) {
                $inFormatSection = true;

                continue;
            }

            if (str_starts_with($line, '---') || str_starts_with($line, '───')) {
                continue;
            }

            if (!$inFormatSection || $line === '') {
                continue;
            }

            $format = $this->parseLine($line);

            if ($format !== null) {
                $formats[] = $format;
            }
        }

        return $formats;
    }

    public function parseLine(string $line): ?FormatItem
    {
        if (preg_match(self::FORMAT_REGEX, $line, $matches)) {
            return FormatItem::fromParsed($matches);
        }

        $parts = preg_split('/\s+/', $line, -1, PREG_SPLIT_NO_EMPTY);

        if (count($parts) < 3) {
            return null;
        }

        return FormatItem::fromParsed([
            'id' => $parts[0],
            'ext' => $parts[1],
            'resolution' => $parts[2] ?? null,
            'fps' => $parts[3] ?? null,
        ]);
    }

    /**
     * @param list<FormatItem> $formats
     * @return list<string>
     */
    public function extractResolutions(array $formats): array
    {
        $resolutions = [];

        foreach ($formats as $format) {
            if ($format->resolution !== null && !$format->isAudioOnly) {
                $resolutions[] = $format->resolution;
            }
        }

        return array_values(array_unique($resolutions));
    }

    /**
     * @param list<FormatItem> $formats
     * @return list<int>
     */
    public function extractFrameRates(array $formats): array
    {
        $fps = [];

        foreach ($formats as $format) {
            if ($format->fps !== null) {
                $fps[] = $format->fps;
            }
        }

        return array_values(array_unique($fps));
    }

    /**
     * @param list<FormatItem> $formats
     * @return list<string>
     */
    public function extractVideoCodecs(array $formats): array
    {
        $codecs = [];

        foreach ($formats as $format) {
            if ($format->vcodec !== null) {
                $codecs[] = $format->vcodec;
            }
        }

        return array_values(array_unique($codecs));
    }

    /**
     * @param list<FormatItem> $formats
     * @return list<string>
     */
    public function extractAudioCodecs(array $formats): array
    {
        $codecs = [];

        foreach ($formats as $format) {
            if ($format->acodec !== null) {
                $codecs[] = $format->acodec;
            }
        }

        return array_values(array_unique($codecs));
    }

    /**
     * @param list<FormatItem> $formats
     * @return list<string>
     */
    public function extractContainers(array $formats): array
    {
        $containers = [];

        foreach ($formats as $format) {
            if ($format->ext !== null && $format->ext !== '') {
                $containers[] = $format->ext;
            }
        }

        return array_values(array_unique($containers));
    }

    /**
     * @param list<FormatItem> $formats
     * @return list<string>
     */
    public function extractDynamicRanges(array $formats): array
    {
        $ranges = [];

        foreach ($formats as $format) {
            if ($format->vcodec !== null) {
                if (str_contains(strtolower($format->vcodec), 'hdr') ||
                    str_contains(strtolower($format->vcodec), 'hev') ||
                    str_contains(strtolower($format->vcodec), 'vp9.2')) {
                    $ranges[] = 'HDR';
                } else {
                    $ranges[] = 'SDR';
                }
            }
        }

        return array_values(array_unique($ranges));
    }
}
