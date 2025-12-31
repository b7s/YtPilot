<?php

declare(strict_types=1);

namespace YtPilot\DTO;

final readonly class DownloadResult
{
    /**
     * @param list<string> $downloadedFiles
     * @param list<string> $subtitlePaths
     */
    public function __construct(
        public bool $success,
        public string $output,
        public array $downloadedFiles,
        public ?string $videoPath = null,
        public ?string $audioPath = null,
        public ?string $thumbnailPath = null,
        public ?string $metadataPath = null,
        public array $subtitlePaths = [],
        public int $exitCode = 0,
    ) {}

    /**
     * @param list<string> $downloadedFiles
     * @param list<string> $subtitlePaths
     */
    public static function success(
        string $output,
        array $downloadedFiles = [],
        ?string $videoPath = null,
        ?string $audioPath = null,
        ?string $thumbnailPath = null,
        ?string $metadataPath = null,
        array $subtitlePaths = [],
    ): self {
        return new self(
            success: true,
            output: $output,
            downloadedFiles: $downloadedFiles,
            videoPath: $videoPath,
            audioPath: $audioPath,
            thumbnailPath: $thumbnailPath,
            metadataPath: $metadataPath,
            subtitlePaths: $subtitlePaths,
            exitCode: 0,
        );
    }

    public static function failure(string $output, int $exitCode): self
    {
        return new self(
            success: false,
            output: $output,
            downloadedFiles: [],
            exitCode: $exitCode,
        );
    }
}
