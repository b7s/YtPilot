<?php

declare(strict_types=1);

require __DIR__ . '/../vendor/autoload.php';

use YtPilot\YtPilot;

// Advanced Example 1: Custom format selector with specific codec
echo "Advanced Example 1: Custom format with AV1 codec\n";
$result = YtPilot::make()
    ->url('https://www.youtube.com/watch?v=dQw4w9WgXcQ')
    ->format('bestvideo[vcodec^=av01][height<=1080]+bestaudio/best')
    ->download();

if ($result->success) {
    echo "✓ Downloaded with AV1 codec\n\n";
}

// Advanced Example 2: Download with specific resolution and FPS
echo "Advanced Example 2: 1080p 60fps\n";
$result = YtPilot::make()
    ->url('https://www.youtube.com/watch?v=dQw4w9WgXcQ')
    ->resolution('1080p')
    ->fps(60)
    ->download();

if ($result->success) {
    echo "✓ Downloaded 1080p 60fps\n\n";
}

// Advanced Example 3: HDR content
echo "Advanced Example 3: HDR content\n";
$result = YtPilot::make()
    ->url('https://www.youtube.com/watch?v=dQw4w9WgXcQ')
    ->hdr()
    ->download();

if ($result->success) {
    echo "✓ Downloaded HDR content\n\n";
}

// Advanced Example 4: Multiple subtitle languages
echo "Advanced Example 4: Multiple subtitle languages\n";
$result = YtPilot::make()
    ->url('https://www.youtube.com/watch?v=dQw4w9WgXcQ')
    ->video()
    ->audio()
    ->subtitles()
    ->autoSubtitles()
    ->subtitleLanguages(['en', 'es', 'fr', 'de', 'pt'])
    ->subtitleFormat('srt')
    ->download();

if ($result->success) {
    echo "✓ Downloaded with multiple subtitles: " . count($result->subtitlePaths) . " files\n\n";
}

// Advanced Example 5: Inspect formats before downloading
echo "Advanced Example 5: Inspect and choose format\n";
$pilot = YtPilot::make()
    ->url('https://www.youtube.com/watch?v=dQw4w9WgXcQ');

$formats = $pilot->getAvailableFormats();

echo "Available formats:\n";
foreach (array_slice($formats, 0, 5) as $format) {
    echo sprintf(
        "  - ID: %s, Ext: %s, Resolution: %s, Codec: %s/%s\n",
        $format->id,
        $format->ext,
        $format->resolution ?? 'audio only',
        $format->vcodec ?? 'none',
        $format->acodec ?? 'none'
    );
}
echo "\n";

// Advanced Example 6: Archive quality with metadata
echo "Advanced Example 6: Archive quality with all metadata\n";
$result = YtPilot::make()
    ->url('https://www.youtube.com/watch?v=dQw4w9WgXcQ')
    ->archive()
    ->video()
    ->audio()
    ->metadata()
    ->thumbnail()
    ->subtitles()
    ->download();

if ($result->success) {
    echo "✓ Archived with all metadata\n";
    echo "  Files: " . implode(', ', array_map('basename', $result->downloadedFiles)) . "\n\n";
}

// Advanced Example 7: Custom binary paths
echo "Advanced Example 7: Using custom binary paths\n";
$result = YtPilot::make()
    ->withYtDlpPath('/usr/local/bin/yt-dlp')
    ->withFfmpegPath('/usr/local/bin/ffmpeg')
    ->url('https://www.youtube.com/watch?v=dQw4w9WgXcQ')
    ->best()
    ->download();

if ($result->success) {
    echo "✓ Downloaded with custom binaries\n\n";
}

// Advanced Example 8: Timeout and error handling
echo "Advanced Example 8: With timeout\n";
try {
    $result = YtPilot::make()
        
        ->url('https://www.youtube.com/watch?v=dQw4w9WgXcQ')
        ->timeout(60)
        ->best()
        ->download();

    if ($result->success) {
        echo "✓ Downloaded within timeout\n\n";
    } else {
        echo "✗ Download failed: {$result->output}\n\n";
    }
} catch (\Exception $e) {
    echo "✗ Error: {$e->getMessage()}\n\n";
}

// Advanced Example 9: Simulate download (no actual download)
echo "Advanced Example 9: Simulate download\n";
$result = YtPilot::make()
    ->url('https://www.youtube.com/watch?v=dQw4w9WgXcQ')
    ->simulate()
    ->download();

if ($result->success) {
    echo "✓ Simulation completed\n\n";
}

// Advanced Example 10: Query all available information
echo "Advanced Example 10: Complete media information\n";
$pilot = YtPilot::make()
    ->url('https://www.youtube.com/watch?v=dQw4w9WgXcQ');

echo "Resolutions: " . implode(', ', $pilot->getAvailableResolutions()) . "\n";
echo "Frame rates: " . implode(', ', $pilot->getAvailableFrameRates()) . "\n";
echo "Video codecs: " . implode(', ', array_slice($pilot->getAvailableVideoCodecs(), 0, 5)) . "...\n";
echo "Audio codecs: " . implode(', ', $pilot->getAvailableAudioCodecs()) . "\n";
echo "Containers: " . implode(', ', $pilot->getAvailableContainers()) . "\n";
echo "Dynamic ranges: " . implode(', ', $pilot->getAvailableDynamicRanges()) . "\n";

$subtitles = $pilot->getAvailableSubtitles();
echo "Manual subtitles: " . count($subtitles->manual) . " languages\n";
echo "Auto subtitles: " . count($subtitles->automatic) . " languages\n\n";

echo "All advanced examples completed!\n";
