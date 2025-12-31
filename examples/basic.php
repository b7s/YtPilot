<?php

declare(strict_types=1);

require __DIR__ . '/../vendor/autoload.php';

use YtPilot\YtPilot;

// Example 1: Simple video download
echo "Example 1: Simple video download\n";
$result = YtPilot::make()
    ->url('https://www.youtube.com/watch?v=dQw4w9WgXcQ')
    ->best()
    ->download();

if ($result->success) {
    echo "✓ Downloaded: {$result->videoPath}\n\n";
} else {
    echo "✗ Failed: {$result->output}\n\n";
}

// Example 2: Audio only download
echo "Example 2: Audio only download\n";
$result = YtPilot::make()
    ->url('https://www.youtube.com/watch?v=dQw4w9WgXcQ')
    ->audioOnly()
    ->audioFormat('mp3')
    ->audioQuality('320K')
    ->download();

if ($result->success) {
    echo "✓ Downloaded: {$result->audioPath}\n\n";
}

// Example 3: Download with subtitles
echo "Example 3: Download with subtitles\n";
$result = YtPilot::make()
    ->url('https://www.youtube.com/watch?v=dQw4w9WgXcQ')
    ->video()
    ->audio()
    ->subtitles()
    ->subtitleLanguages(['en'])
    ->subtitleAsSrt()
    ->download();

if ($result->success) {
    echo "✓ Video: {$result->videoPath}\n";
    echo "✓ Subtitles: " . implode(', ', $result->subtitlePaths) . "\n\n";
}

// Example 4: Download everything
echo "Example 4: Download everything\n";
$result = YtPilot::make()
    ->url('https://www.youtube.com/watch?v=dQw4w9WgXcQ')
    ->video()
    ->audio()
    ->subtitles()
    ->metadata()
    ->thumbnail()
    ->download();

if ($result->success) {
    echo "✓ Video: {$result->videoPath}\n";
    echo "✓ Thumbnail: {$result->thumbnailPath}\n";
    echo "✓ Metadata: {$result->metadataPath}\n";
    echo "✓ All files: " . count($result->downloadedFiles) . "\n\n";
}

// Example 5: Query available formats
echo "Example 5: Query available formats\n";
$pilot = YtPilot::make()
    ->url('https://www.youtube.com/watch?v=dQw4w9WgXcQ');

$resolutions = $pilot->getAvailableResolutions();
echo "Available resolutions: " . implode(', ', $resolutions) . "\n";

$codecs = $pilot->getAvailableVideoCodecs();
echo "Available video codecs: " . implode(', ', array_slice($codecs, 0, 5)) . "...\n";

$subtitles = $pilot->getAvailableSubtitles();
echo "Available subtitle languages: " . implode(', ', array_slice($subtitles->getLanguages(), 0, 10)) . "...\n\n";

// Example 6: Use presets
echo "Example 6: Cinema preset (high quality)\n";
$result = YtPilot::make()
    ->url('https://www.youtube.com/watch?v=dQw4w9WgXcQ')
    ->cinema()
    ->download();

if ($result->success) {
    echo "✓ Downloaded with cinema preset\n\n";
}

echo "Example 7: Mobile preset (small size)\n";
$result = YtPilot::make()
    ->url('https://www.youtube.com/watch?v=dQw4w9WgXcQ')
    ->mobile()
    ->download();

if ($result->success) {
    echo "✓ Downloaded with mobile preset\n\n";
}

// Example 8: Custom output path
echo "Example 8: Custom output path\n";
$result = YtPilot::make()
    ->url('https://www.youtube.com/watch?v=dQw4w9WgXcQ')
    ->outputPath('./downloads')
    ->output('%(title)s-%(id)s.%(ext)s')
    ->download();

if ($result->success) {
    echo "✓ Downloaded to custom path\n\n";
}

echo "All examples completed!\n";
