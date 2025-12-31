<?php

declare(strict_types=1);

require __DIR__ . '/../vendor/autoload.php';

use YtPilot\YtPilot;

// Example 1: Download video and convert to MP4
echo "Example 1: Download and convert to MP4\n";
echo str_repeat('-', 50) . "\n";

$ytpilot = YtPilot::make()
    ->url('https://www.youtube.com/watch?v=dQw4w9WgXcQ')
    ->video()
    ->onDownloading(function (int $percentage, float $downloaded, float $total): void {
        echo "\rDownloading: {$percentage}% ";
    })
    ->download();

if ($ytpilot->success && $ytpilot->videoPath !== null) {
    echo "\n✓ Downloaded: {$ytpilot->videoPath}\n";
    
    $outputPath = str_replace(pathinfo($ytpilot->videoPath, PATHINFO_EXTENSION), 'mp4', $ytpilot->videoPath);
    
    echo "Converting to MP4...\n";
    YtPilot::make()
        ->onConverting(function (int $percentage, float $current, float $duration): void {
            echo "\rConverting: {$percentage}% ";
        })
        ->convertVideoToMp4($ytpilot->videoPath, $outputPath);
    
    echo "\n✓ Converted: {$outputPath}\n";
}

echo "\n";

// Example 2: Download audio and convert to MP3
echo "Example 2: Download and convert to MP3\n";
echo str_repeat('-', 50) . "\n";

$ytpilot = YtPilot::make()
    ->url('https://www.youtube.com/watch?v=dQw4w9WgXcQ')
    ->audioOnly()
    ->onDownloading(function (int $percentage): void {
        echo "\rDownloading: {$percentage}% ";
    })
    ->download();

if ($ytpilot->success && $ytpilot->audioPath !== null) {
    echo "\n✓ Downloaded: {$ytpilot->audioPath}\n";
    
    $outputPath = str_replace(pathinfo($ytpilot->audioPath, PATHINFO_EXTENSION), 'mp3', $ytpilot->audioPath);
    
    echo "Converting to MP3...\n";
    YtPilot::make()
        ->onConverting(function (int $percentage): void {
            echo "\rConverting: {$percentage}% ";
        })
        ->convertAudioToMp3($ytpilot->audioPath, $outputPath);
    
    echo "\n✓ Converted: {$outputPath}\n";
}

echo "\n";

// Example 3: Convert existing video to different formats
echo "Example 3: Convert video to multiple formats\n";
echo str_repeat('-', 50) . "\n";

$inputVideo = 'video.mp4';

if (file_exists($inputVideo)) {
    $ytpilot = YtPilot::make();
    
    // Convert to WebM
    echo "Converting to WebM...\n";
    $ytpilot->convertVideoToWebm($inputVideo, 'video.webm');
    echo "✓ Converted to WebM\n";
    
    // Convert to MKV
    echo "Converting to MKV...\n";
    $ytpilot->convertVideoToMkv($inputVideo, 'video.mkv');
    echo "✓ Converted to MKV\n";
    
    // Convert to AVI
    echo "Converting to AVI...\n";
    $ytpilot->convertVideoToAvi($inputVideo, 'video.avi');
    echo "✓ Converted to AVI\n";
} else {
    echo "⚠ Input file not found: {$inputVideo}\n";
}

echo "\n";

// Example 4: Convert audio to different formats
echo "Example 4: Convert audio to multiple formats\n";
echo str_repeat('-', 50) . "\n";

$inputAudio = 'audio.m4a';

if (file_exists($inputAudio)) {
    $ytpilot = YtPilot::make();
    
    // Convert to MP3
    echo "Converting to MP3...\n";
    $ytpilot->convertAudioToMp3($inputAudio, 'audio.mp3');
    echo "✓ Converted to MP3\n";
    
    // Convert to Opus
    echo "Converting to Opus...\n";
    $ytpilot->convertAudioToOpus($inputAudio, 'audio.opus');
    echo "✓ Converted to Opus\n";
    
    // Convert to WAV
    echo "Converting to WAV...\n";
    $ytpilot->convertAudioToWav($inputAudio, 'audio.wav');
    echo "✓ Converted to WAV\n";
    
    // Convert to FLAC
    echo "Converting to FLAC...\n";
    $ytpilot->convertAudioToFlac($inputAudio, 'audio.flac');
    echo "✓ Converted to FLAC\n";
} else {
    echo "⚠ Input file not found: {$inputAudio}\n";
}

echo "\n";

// Example 5: Custom format conversion with progress tracking
echo "Example 5: Custom conversion with detailed progress\n";
echo str_repeat('-', 50) . "\n";

$inputFile = 'video.mp4';
$outputFile = 'output.webm';

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
        ->convertVideoTo($inputFile, $outputFile, 'webm');
    
    echo "\n✓ Conversion complete: {$outputFile}\n";
} else {
    echo "⚠ Input file not found: {$inputFile}\n";
}
