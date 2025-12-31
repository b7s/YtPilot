<?php

declare(strict_types=1);

return [
    'bin_path' => '.ytpilot/bin',

    'yt_dlp' => [
        'path' => null,
    ],

    'ffmpeg' => [
        'path' => null,
        'probe_path' => null,
        'prefer_global' => true,
        'enabled' => true,
    ],

    'timeout' => 300,

    // Default download directory (null = current working directory)
    'download_path' => null,
];
