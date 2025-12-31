<?php

declare(strict_types=1);

use YtPilot\Services\Conversion\ConversionService;

it('throws exception when input file does not exist', function (): void {
    $conversionService = new ConversionService(
        new YtPilot\Services\Process\ProcessRunnerService(),
        new YtPilot\Services\Binary\BinaryLocatorService(
            new YtPilot\Services\Filesystem\PathService(
                new YtPilot\Services\Platform\PlatformService()
            )
        )
    );

    $conversionService->convert(
        '/nonexistent/file.mp4',
        '/output/file.mp4',
        'mp4'
    );
})->throws(InvalidArgumentException::class, 'Input file not found');

it('ConversionResult can be created', function (): void {
    $result = new YtPilot\Services\Conversion\ConversionResult(
        success: true,
        outputPath: '/path/to/output.mp4',
        format: 'mp4',
        output: 'Conversion complete'
    );

    expect($result->success)->toBeTrue()
        ->and($result->outputPath)->toBe('/path/to/output.mp4')
        ->and($result->format)->toBe('mp4')
        ->and($result->output)->toBe('Conversion complete');
});
