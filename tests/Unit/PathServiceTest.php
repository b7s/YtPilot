<?php

declare(strict_types=1);

use YtPilot\Services\Filesystem\PathService;
use YtPilot\Services\Platform\PlatformService;

test('returns bin directory path', function () {
    $platform = new PlatformService();
    $pathService = new PathService($platform);

    $binDir = $pathService->getBinDirectory();

    expect($binDir)->toBeString()
        ->and($binDir)->toContain('.ytpilot/bin');
});

test('returns correct yt-dlp path', function () {
    $platform = new PlatformService();
    $pathService = new PathService($platform);

    $ytDlpPath = $pathService->getYtDlpPath();

    expect($ytDlpPath)->toBeString()
        ->and($ytDlpPath)->toContain('yt-dlp');

    if ($platform->isWindows()) {
        expect($ytDlpPath)->toEndWith('.exe');
    }
});

test('returns correct ffmpeg path', function () {
    $platform = new PlatformService();
    $pathService = new PathService($platform);

    $ffmpegPath = $pathService->getFfmpegPath();

    expect($ffmpegPath)->toBeString()
        ->and($ffmpegPath)->toContain('ffmpeg');

    if ($platform->isWindows()) {
        expect($ffmpegPath)->toEndWith('.exe');
    }
});

test('returns correct ffprobe path', function () {
    $platform = new PlatformService();
    $pathService = new PathService($platform);

    $ffprobePath = $pathService->getFfprobePath();

    expect($ffprobePath)->toBeString()
        ->and($ffprobePath)->toContain('ffprobe');

    if ($platform->isWindows()) {
        expect($ffprobePath)->toEndWith('.exe');
    }
});

test('returns manifest path', function () {
    $platform = new PlatformService();
    $pathService = new PathService($platform);

    $manifestPath = $pathService->getManifestPath();

    expect($manifestPath)->toBeString()
        ->and($manifestPath)->toEndWith('manifest.json');
});
