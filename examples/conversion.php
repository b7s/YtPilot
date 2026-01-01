<?php

declare(strict_types=1);

require __DIR__.'/../vendor/autoload.php';

use YtPilot\YtPilot;

// Example 1: Download video and convert to MP4 (simplified - uses downloaded file automatically)
echo "Example 1: Download and convert to MP4 (auto input/output)\n";
echo str_repeat('-', 50)."\n";

$ytpilot = YtPilot::make()
    ->url('https://www.youtube.com/watch?v=dQw4w9WgXcQ')
    ->video()
    ->onDownloading(function (int $percentage, float $downloaded, float $total): void {
        echo "\rDownloading: {$percentage}% ";
    })
    ->onConverting(function (int $percentage, float $current, float $duration): void {
        echo "\rConverting: {$percentage}% ";
    });

$result = $ytpilot->download();

if ($result->success && $result->videoPath !== null) {
    echo "\n✓ Downloaded: {$result->videoPath}\n";
    echo "Converting to MP4 (auto-deletes original)...\n";

    // No parameters needed - uses downloaded video, saves to same folder with .mp4 extension
    // Original file is deleted after successful conversion (default behavior)
    $ytpilot->convertVideoToMp4();

    echo "✓ Converted!\n";
}

echo "\n";

// Example 2: Download audio and convert to MP3 (keep original)
echo "Example 2: Download and convert to MP3 (keep original)\n";
echo str_repeat('-', 50)."\n";

$ytpilot = YtPilot::make()
    ->url('https://www.youtube.com/watch?v=dQw4w9WgXcQ')
    ->audioOnly()
    ->onDownloading(function (int $percentage): void {
        echo "\rDownloading: {$percentage}% ";
    })
    ->onConverting(function (int $percentage): void {
        echo "\rConverting: {$percentage}% ";
    });

$result = $ytpilot->download();

if ($result->success && $result->audioPath !== null) {
    echo "\n✓ Downloaded: {$result->audioPath}\n";
    echo "Converting to MP3 (keeping original)...\n";

    // Pass deleteOriginalAfterConvert: false to keep the original file
    $ytpilot->convertAudioToMp3(deleteOriginalAfterConvert: false);

    echo "✓ Converted! Original file preserved.\n";
}

echo "\n";

// Example 3: Download and convert with custom output path
echo "Example 3: Download and convert with custom output\n";
echo str_repeat('-', 50)."\n";

$ytpilot = YtPilot::make()
    ->url('https://www.youtube.com/watch?v=dQw4w9WgXcQ')
    ->video()
    ->onDownloading(function (int $percentage): void {
        echo "\rDownloading: {$percentage}% ";
    });

$result = $ytpilot->download();

if ($result->success && $result->videoPath !== null) {
    echo "\n✓ Downloaded: {$result->videoPath}\n";

    // Custom output path, keep original
    $customOutput = '/tmp/my-converted-video.mp4';
    echo "Converting to: {$customOutput}\n";

    $ytpilot->convertVideoToMp4(outputPath: $customOutput, deleteOriginalAfterConvert: false);

    echo "✓ Converted to custom path!\n";
}

echo "\n";

// Example 4: Convert existing video to different formats (explicit input)
echo "Example 4: Convert existing video to multiple formats\n";
echo str_repeat('-', 50)."\n";

$inputVideo = 'video.mp4';

if (file_exists($inputVideo)) {
    $ytpilot = YtPilot::make();

    // Convert to WebM (explicit input, auto output, keep original)
    echo "Converting to WebM...\n";
    $ytpilot->convertVideoToWebm($inputVideo, deleteOriginalAfterConvert: false);
    echo "✓ Converted to WebM\n";

    // Convert to MKV with custom output
    echo "Converting to MKV...\n";
    $ytpilot->convertVideoToMkv($inputVideo, 'custom-name.mkv', deleteOriginalAfterConvert: false);
    echo "✓ Converted to MKV\n";

    // Convert to AVI (delete original after)
    echo "Converting to AVI (will delete original)...\n";
    $ytpilot->convertVideoToAvi($inputVideo);
    echo "✓ Converted to AVI\n";
} else {
    echo "⚠ Input file not found: {$inputVideo}\n";
}

echo "\n";

// Example 5: Convert audio to different formats
echo "Example 5: Convert audio to multiple formats\n";
echo str_repeat('-', 50)."\n";

$inputAudio = 'audio.m4a';

if (file_exists($inputAudio)) {
    $ytpilot = YtPilot::make();

    // Convert to MP3 (keep original)
    echo "Converting to MP3...\n";
    $ytpilot->convertAudioToMp3($inputAudio, deleteOriginalAfterConvert: false);
    echo "✓ Converted to MP3\n";

    // Convert to Opus
    echo "Converting to Opus...\n";
    $ytpilot->convertAudioToOpus($inputAudio, deleteOriginalAfterConvert: false);
    echo "✓ Converted to Opus\n";

    // Convert to WAV
    echo "Converting to WAV...\n";
    $ytpilot->convertAudioToWav($inputAudio, deleteOriginalAfterConvert: false);
    echo "✓ Converted to WAV\n";

    // Convert to FLAC
    echo "Converting to FLAC...\n";
    $ytpilot->convertAudioToFlac($inputAudio, deleteOriginalAfterConvert: false);
    echo "✓ Converted to FLAC\n";
} else {
    echo "⚠ Input file not found: {$inputAudio}\n";
}

echo "\n";

// Example 6: Custom format conversion with progress tracking
echo "Example 6: Custom conversion with detailed progress\n";
echo str_repeat('-', 50)."\n";

$inputFile = 'video.mp4';

if (file_exists($inputFile)) {
    $lastPercentage = -1;

    YtPilot::make()
        ->onConverting(function (int $percentage, float $current, float $duration) use (&$lastPercentage): void {
            if ($percentage !== $lastPercentage) {
                $currentFormatted = gmdate('H:i:s', (int) $current);
                $durationFormatted = gmdate('H:i:s', (int) $duration);
                echo "\rConverting: {$percentage}% ({$currentFormatted} / {$durationFormatted}) ";
                $lastPercentage = $percentage;
            }
        })
        ->convertVideoTo($inputFile, format: 'webm', deleteOriginalAfterConvert: false);

    echo "\n✓ Conversion complete!\n";
} else {
    echo "⚠ Input file not found: {$inputFile}\n";
}
