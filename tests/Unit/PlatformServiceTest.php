<?php

declare(strict_types=1);

use YtPilot\Services\Platform\PlatformService;

test('detects operating system', function () {
    $platform = new PlatformService();
    $os = $platform->getOs();

    expect($os)->toBeIn(['linux', 'windows', 'darwin']);
});

test('detects architecture', function () {
    $platform = new PlatformService();
    $arch = $platform->getArch();

    expect($arch)->toBeIn(['x64', 'arm64', 'x86']);
});

test('returns correct executable extension', function () {
    $platform = new PlatformService();
    $extension = $platform->getExecutableExtension();

    if ($platform->isWindows()) {
        expect($extension)->toBe('.exe');
    } else {
        expect($extension)->toBe('');
    }
});

test('platform identifier has correct format', function () {
    $platform = new PlatformService();
    $identifier = $platform->getPlatformIdentifier();

    expect($identifier)->toMatch('/^(linux|windows|darwin)-(x64|arm64|x86)(-musl)?$/');
});

test('only one OS check returns true', function () {
    $platform = new PlatformService();

    $checks = [
        $platform->isWindows(),
        $platform->isLinux(),
        $platform->isMacOs(),
    ];

    $trueCount = count(array_filter($checks));

    expect($trueCount)->toBe(1);
});

test('musl detection only returns true on linux', function () {
    $platform = new PlatformService();

    if (!$platform->isLinux()) {
        expect($platform->isMusl())->toBeFalse();
    } else {
        // On Linux, musl detection should return a boolean
        expect($platform->isMusl())->toBeBool();
    }
});
