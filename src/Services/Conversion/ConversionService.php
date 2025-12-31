<?php

declare(strict_types=1);

namespace YtPilot\Services\Conversion;

use YtPilot\Services\Binary\BinaryLocatorService;
use YtPilot\Services\Process\ProcessRunnerService;
use YtPilot\Exceptions\ProcessFailedException;

final class ConversionService
{
    public function __construct(
        private readonly ProcessRunnerService $processRunner,
        private readonly BinaryLocatorService $locator,
    ) {}

    public function convert(
        string $inputPath,
        string $outputPath,
        string $format,
        ?string $ffmpegPath = null,
        ?callable $progressCallback = null,
    ): ConversionResult {
        if (!is_file($inputPath)) {
            throw new \InvalidArgumentException("Input file not found: {$inputPath}");
        }

        $ffmpeg = $this->locator->requireFfmpeg($ffmpegPath);
        $command = $this->buildConversionCommand($ffmpeg, $inputPath, $outputPath, $format);

        $outputBuffer = '';
        $duration = null;

        $callback = null;
        if ($progressCallback !== null) {
            $callback = function (string $type, string $buffer) use (&$outputBuffer, &$duration, $progressCallback): void {
                $outputBuffer .= $buffer;

                if ($duration === null && preg_match('/Duration: (\d{2}):(\d{2}):(\d{2}\.\d{2})/', $buffer, $matches)) {
                    $hours = (int) $matches[1];
                    $minutes = (int) $matches[2];
                    $seconds = (float) $matches[3];
                    $duration = ($hours * 3600) + ($minutes * 60) + $seconds;
                }

                if ($duration !== null && preg_match('/time=(\d{2}):(\d{2}):(\d{2}\.\d{2})/', $buffer, $matches)) {
                    $hours = (int) $matches[1];
                    $minutes = (int) $matches[2];
                    $seconds = (float) $matches[3];
                    $currentTime = ($hours * 3600) + ($minutes * 60) + $seconds;

                    $percentage = min(100, (int) round(($currentTime / $duration) * 100));
                    $progressCallback($percentage, $currentTime, $duration);
                }
            };
        }

        $result = $this->processRunner->run($command, null, null, $callback);

        if (!$result->success) {
            throw ProcessFailedException::fromProcess(
                $result->command,
                $result->output,
                $result->errorOutput,
                $result->exitCode,
            );
        }

        return new ConversionResult(
            success: true,
            outputPath: $outputPath,
            format: $format,
            output: $outputBuffer ?: $result->output,
        );
    }

    /** @return list<string> */
    private function buildConversionCommand(string $ffmpeg, string $input, string $output, string $format): array
    {
        $command = [
            $ffmpeg,
            '-i', $input,
            '-y',
            '-progress', 'pipe:1',
        ];

        $command = match (strtolower($format)) {
            'mp4' => [...$command, '-c:v', 'libx264', '-c:a', 'aac', '-movflags', '+faststart', $output],
            'mkv' => [...$command, '-c:v', 'copy', '-c:a', 'copy', $output],
            'webm' => [...$command, '-c:v', 'libvpx-vp9', '-c:a', 'libopus', $output],
            'avi' => [...$command, '-c:v', 'libx264', '-c:a', 'mp3', $output],
            'mp3' => [...$command, '-vn', '-c:a', 'libmp3lame', '-q:a', '2', $output],
            'm4a' => [...$command, '-vn', '-c:a', 'aac', '-b:a', '192k', $output],
            'opus' => [...$command, '-vn', '-c:a', 'libopus', '-b:a', '128k', $output],
            'ogg' => [...$command, '-vn', '-c:a', 'libvorbis', '-q:a', '5', $output],
            'wav' => [...$command, '-vn', '-c:a', 'pcm_s16le', $output],
            'flac' => [...$command, '-vn', '-c:a', 'flac', $output],
            default => [...$command, $output],
        };

        return $command;
    }
}

final readonly class ConversionResult
{
    public function __construct(
        public bool $success,
        public string $outputPath,
        public string $format,
        public string $output,
    ) {}
}
