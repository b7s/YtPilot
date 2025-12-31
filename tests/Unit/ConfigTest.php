<?php

declare(strict_types=1);

use YtPilot\Config;

beforeEach(function () {
    Config::reset();
});

test('can get config value', function () {
    $binPath = Config::get('bin_path');

    expect($binPath)->toBe('.ytpilot/bin');
});

test('returns default value when key not found', function () {
    $value = Config::get('non.existent.key', 'default');

    expect($value)->toBe('default');
});

test('can set config value', function () {
    Config::set('test.key', 'test-value');

    expect(Config::get('test.key'))->toBe('test-value');
});

test('can get nested config value', function () {
    $ytDlpPath = Config::get('yt_dlp.path');

    expect($ytDlpPath)->toBeNull();
});

test('can get all config', function () {
    $config = Config::all();

    expect($config)->toBeArray()
        ->and($config)->toHaveKey('bin_path')
        ->and($config)->toHaveKey('yt_dlp')
        ->and($config)->toHaveKey('ffmpeg')
        ->and($config)->toHaveKey('timeout');
});
