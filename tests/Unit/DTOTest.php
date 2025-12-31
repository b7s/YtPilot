<?php

declare(strict_types=1);

use YtPilot\DTO\DownloadResult;
use YtPilot\DTO\FormatItem;
use YtPilot\DTO\SubtitleList;

test('DownloadResult can be created as success', function () {
    $result = DownloadResult::success(
        output: 'test output',
        downloadedFiles: ['file1.mp4', 'file2.mp3'],
        videoPath: 'file1.mp4',
        audioPath: 'file2.mp3'
    );

    expect($result->success)->toBeTrue()
        ->and($result->output)->toBe('test output')
        ->and($result->downloadedFiles)->toHaveCount(2)
        ->and($result->videoPath)->toBe('file1.mp4')
        ->and($result->audioPath)->toBe('file2.mp3')
        ->and($result->exitCode)->toBe(0);
});

test('DownloadResult can be created as failure', function () {
    $result = DownloadResult::failure('error output', 1);

    expect($result->success)->toBeFalse()
        ->and($result->output)->toBe('error output')
        ->and($result->exitCode)->toBe(1)
        ->and($result->downloadedFiles)->toBeEmpty();
});

test('FormatItem can be created from parsed data', function () {
    $data = [
        'id' => '137',
        'ext' => 'mp4',
        'resolution' => '1920x1080',
        'fps' => '30',
        'vcodec' => 'avc1',
        'acodec' => 'none',
    ];

    $format = FormatItem::fromParsed($data);

    expect($format->id)->toBe('137')
        ->and($format->ext)->toBe('mp4')
        ->and($format->resolution)->toBe('1920x1080')
        ->and($format->fps)->toBe(30)
        ->and($format->vcodec)->toBe('avc1')
        ->and($format->isVideoOnly)->toBeTrue()
        ->and($format->isAudioOnly)->toBeFalse();
});

test('FormatItem detects audio only format', function () {
    $data = [
        'id' => '140',
        'ext' => 'm4a',
        'resolution' => 'audio only',
        'acodec' => 'mp4a',
        'vcodec' => 'none',
    ];

    $format = FormatItem::fromParsed($data);

    expect($format->isAudioOnly)->toBeTrue()
        ->and($format->isVideoOnly)->toBeFalse()
        ->and($format->resolution)->toBeNull();
});

test('SubtitleList can be created', function () {
    $manual = ['en' => ['srt', 'vtt'], 'es' => ['srt']];
    $automatic = ['pt' => ['srt'], 'fr' => ['vtt']];

    $subtitles = SubtitleList::fromParsed($manual, $automatic);

    expect($subtitles->manual)->toBe($manual)
        ->and($subtitles->automatic)->toBe($automatic)
        ->and($subtitles->getLanguages())->toHaveCount(4)
        ->and($subtitles->hasLanguage('en'))->toBeTrue()
        ->and($subtitles->hasLanguage('pt'))->toBeTrue()
        ->and($subtitles->hasLanguage('de'))->toBeFalse();
});
