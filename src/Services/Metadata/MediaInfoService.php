<?php

declare(strict_types=1);

namespace YtPilot\Services\Metadata;

use YtPilot\DTO\FormatItem;
use YtPilot\DTO\SubtitleList;
use YtPilot\Exceptions\MissingUrlException;
use YtPilot\Services\Binary\BinaryLocatorService;
use YtPilot\Services\Parsing\FormatsParserService;
use YtPilot\Services\Parsing\SubtitlesParserService;
use YtPilot\Services\Process\ProcessRunnerService;

final class MediaInfoService
{
    public function __construct(
        private readonly ProcessRunnerService $processRunner,
        private readonly BinaryLocatorService $locator,
        private readonly FormatsParserService $formatsParser,
        private readonly SubtitlesParserService $subtitlesParser,
    ) {}

    /**
     * @return FormatItem[]
     */
    public function getAvailableFormats(string $url, ?string $ytDlpPath = null): array
    {
        $this->requireUrl($url);

        $binary = $this->locator->requireYtDlp($ytDlpPath);
        $result = $this->processRunner->runOrFail([
            $binary, '-F', '--no-warnings', $url,
        ]);

        return $this->formatsParser->parse($result->output);
    }

    /** @return list<string> */
    public function getAvailableResolutions(string $url, ?string $ytDlpPath = null): array
    {
        $formats = $this->getAvailableFormats($url, $ytDlpPath);

        return $this->formatsParser->extractResolutions($formats);
    }

    /** @return list<int> */
    public function getAvailableFrameRates(string $url, ?string $ytDlpPath = null): array
    {
        $formats = $this->getAvailableFormats($url, $ytDlpPath);

        return $this->formatsParser->extractFrameRates($formats);
    }

    /** @return list<string> */
    public function getAvailableVideoCodecs(string $url, ?string $ytDlpPath = null): array
    {
        $formats = $this->getAvailableFormats($url, $ytDlpPath);

        return $this->formatsParser->extractVideoCodecs($formats);
    }

    /** @return list<string> */
    public function getAvailableAudioCodecs(string $url, ?string $ytDlpPath = null): array
    {
        $formats = $this->getAvailableFormats($url, $ytDlpPath);

        return $this->formatsParser->extractAudioCodecs($formats);
    }

    /** @return list<string> */
    public function getAvailableContainers(string $url, ?string $ytDlpPath = null): array
    {
        $formats = $this->getAvailableFormats($url, $ytDlpPath);

        return $this->formatsParser->extractContainers($formats);
    }

    /** @return list<string> */
    public function getAvailableDynamicRanges(string $url, ?string $ytDlpPath = null): array
    {
        $formats = $this->getAvailableFormats($url, $ytDlpPath);

        return $this->formatsParser->extractDynamicRanges($formats);
    }

    public function getAvailableSubtitles(string $url, ?string $ytDlpPath = null): SubtitleList
    {
        $this->requireUrl($url);

        $binary = $this->locator->requireYtDlp($ytDlpPath);
        $result = $this->processRunner->runOrFail([
            $binary, '--list-subs', '--no-warnings', $url,
        ]);

        return $this->subtitlesParser->parse($result->output);
    }

    /** @return array<string, mixed> */
    public function getMetadata(string $url, ?string $ytDlpPath = null): array
    {
        $this->requireUrl($url);

        $binary = $this->locator->requireYtDlp($ytDlpPath);
        $result = $this->processRunner->runOrFail([
            $binary, '-j', '--no-warnings', $url,
        ]);

        $data = json_decode($result->output, true);

        return is_array($data) ? $data : [];
    }

    private function requireUrl(string $url): void
    {
        if ($url === '') {
            throw MissingUrlException::required();
        }
    }
}
