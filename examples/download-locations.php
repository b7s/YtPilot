<?php

declare(strict_types=1);

require __DIR__ . '/../vendor/autoload.php';

use YtPilot\YtPilot;

echo "=== YtPilot Download Locations Examples ===\n\n";

// Example 1: Default location (current working directory)
echo "Example 1: Default location (current directory)\n";
$result = YtPilot::make()
    ->url('https://www.youtube.com/watch?v=dQw4w9WgXcQ')
    ->best()
    ->simulate() // Simulate to avoid actual download
    ->download();

if ($result->success) {
    echo "✓ Would download to: " . getcwd() . "\n\n";
}

// Example 2: Custom output path
echo "Example 2: Custom output path\n";
$result = YtPilot::make()
    ->url('https://www.youtube.com/watch?v=dQw4w9WgXcQ')
    ->outputPath('./downloads')
    ->best()
    ->simulate()
    ->download();

if ($result->success) {
    echo "✓ Would download to: ./downloads\n\n";
}

// Example 3: Custom output path with filename template
echo "Example 3: Custom path + filename template\n";
$result = YtPilot::make()
    ->url('https://www.youtube.com/watch?v=dQw4w9WgXcQ')
    ->outputPath('./my-videos')
    ->output('%(title)s-%(id)s.%(ext)s')
    ->best()
    ->simulate()
    ->download();

if ($result->success) {
    echo "✓ Would download to: ./my-videos/[title]-[id].[ext]\n\n";
}

// Example 4: Organized by date
echo "Example 4: Organized by date\n";
$result = YtPilot::make()
    ->url('https://www.youtube.com/watch?v=dQw4w9WgXcQ')
    ->outputPath('./downloads/' . date('Y-m-d'))
    ->output('%(title)s.%(ext)s')
    ->best()
    ->simulate()
    ->download();

if ($result->success) {
    echo "✓ Would download to: ./downloads/" . date('Y-m-d') . "/[title].[ext]\n\n";
}

// Example 5: Organized by channel
echo "Example 5: Organized by uploader\n";
$result = YtPilot::make()
    ->url('https://www.youtube.com/watch?v=dQw4w9WgXcQ')
    ->outputPath('./downloads')
    ->output('%(uploader)s/%(title)s.%(ext)s')
    ->best()
    ->simulate()
    ->download();

if ($result->success) {
    echo "✓ Would download to: ./downloads/[uploader]/[title].[ext]\n\n";
}

// Example 6: Different paths for different content types
echo "Example 6: Separate directories for video and audio\n";

// Video
$videoResult = YtPilot::make()
    ->url('https://www.youtube.com/watch?v=dQw4w9WgXcQ')
    ->outputPath('./downloads/videos')
    ->video()
    ->audio()
    ->best()
    ->simulate()
    ->download();

if ($videoResult->success) {
    echo "✓ Video would download to: ./downloads/videos/\n";
}

// Audio only
$audioResult = YtPilot::make()
    ->url('https://www.youtube.com/watch?v=dQw4w9WgXcQ')
    ->outputPath('./downloads/music')
    ->audioOnly()
    ->audioFormat('mp3')
    ->simulate()
    ->download();

if ($audioResult->success) {
    echo "✓ Audio would download to: ./downloads/music/\n\n";
}

echo "All examples completed!\n";
echo "\nNote: All examples used ->simulate() to avoid actual downloads.\n";
echo "Remove ->simulate() to perform real downloads.\n";
