<?php

declare(strict_types=1);

require __DIR__ . '/../vendor/autoload.php';

use YtPilot\Services\Platform\PlatformService;

echo "=== YtPilot Platform Detection ===\n\n";

$platform = new PlatformService();

// System Information
echo "📊 System Information:\n";
echo "  PHP_OS_FAMILY: " . PHP_OS_FAMILY . "\n";
echo "  php_uname('s'): " . php_uname('s') . "\n";
echo "  php_uname('m'): " . php_uname('m') . "\n";
echo "  PHP_OS: " . PHP_OS . "\n\n";

// Detected Platform
echo "🔍 Detected Platform:\n";
echo "  Operating System: " . $platform->getOs() . "\n";
echo "  Architecture: " . $platform->getArch() . "\n";
echo "  Platform ID: " . $platform->getPlatformIdentifier() . "\n";
echo "  Executable Extension: " . ($platform->getExecutableExtension() ?: '(none)') . "\n\n";

// Platform Checks
echo "✅ Platform Checks:\n";
echo "  Is Windows: " . ($platform->isWindows() ? 'Yes' : 'No') . "\n";
echo "  Is Linux: " . ($platform->isLinux() ? 'Yes' : 'No') . "\n";
echo "  Is macOS: " . ($platform->isMacOs() ? 'Yes' : 'No') . "\n";
echo "  Is musl: " . ($platform->isMusl() ? 'Yes' : 'No') . "\n\n";

// Expected Binary Names
echo "📦 Expected Binary Names:\n";
echo "  yt-dlp: yt-dlp" . $platform->getExecutableExtension() . "\n";
echo "  ffmpeg: ffmpeg" . $platform->getExecutableExtension() . "\n";
echo "  ffprobe: ffprobe" . $platform->getExecutableExtension() . "\n\n";

// Validation
echo "🧪 Validation:\n";

$expectedResults = [
    'Windows' => [
        'os' => 'windows',
        'extension' => '.exe',
        'isWindows' => true,
        'isLinux' => false,
        'isMacOs' => false,
    ],
    'Linux' => [
        'os' => 'linux',
        'extension' => '',
        'isWindows' => false,
        'isLinux' => true,
        'isMacOs' => false,
    ],
    'Darwin' => [
        'os' => 'darwin',
        'extension' => '',
        'isWindows' => false,
        'isLinux' => false,
        'isMacOs' => true,
    ],
];

$currentFamily = PHP_OS_FAMILY;
if (isset($expectedResults[$currentFamily])) {
    $expected = $expectedResults[$currentFamily];
    $allCorrect = true;

    if ($platform->getOs() !== $expected['os']) {
        echo "  ❌ OS detection failed: expected '{$expected['os']}', got '{$platform->getOs()}'\n";
        $allCorrect = false;
    }

    if ($platform->getExecutableExtension() !== $expected['extension']) {
        echo "  ❌ Extension detection failed: expected '{$expected['extension']}', got '{$platform->getExecutableExtension()}'\n";
        $allCorrect = false;
    }

    if ($platform->isWindows() !== $expected['isWindows']) {
        echo "  ❌ isWindows() failed\n";
        $allCorrect = false;
    }

    if ($platform->isLinux() !== $expected['isLinux']) {
        echo "  ❌ isLinux() failed\n";
        $allCorrect = false;
    }

    if ($platform->isMacOs() !== $expected['isMacOs']) {
        echo "  ❌ isMacOs() failed\n";
        $allCorrect = false;
    }

    if ($allCorrect) {
        echo "  ✅ All platform detection checks passed!\n";
    }
} else {
    echo "  ⚠️  Unknown OS family: {$currentFamily}\n";
}

// Architecture Validation
echo "\n🏗️  Architecture Detection:\n";
$arch = strtolower(php_uname('m'));
$detectedArch = $platform->getArch();

$knownArchitectures = [
    'x86_64' => 'x64',
    'amd64' => 'x64',
    'x64' => 'x64',
    'arm64' => 'arm64',
    'aarch64' => 'arm64',
    'armv8' => 'arm64',
    'i386' => 'x86',
    'i686' => 'x86',
    'x86' => 'x86',
];

if (isset($knownArchitectures[$arch])) {
    $expectedArch = $knownArchitectures[$arch];
    if ($detectedArch === $expectedArch) {
        echo "  ✅ Architecture correctly detected as '{$detectedArch}'\n";
    } else {
        echo "  ❌ Architecture detection failed: expected '{$expectedArch}', got '{$detectedArch}'\n";
    }
} else {
    echo "  ⚠️  Unknown architecture: {$arch} (detected as: {$detectedArch})\n";
}

echo "\n=== Platform Detection Complete ===\n";
