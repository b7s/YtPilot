<?php

declare(strict_types=1);

namespace YtPilot\Services\Http;

use YtPilot\Exceptions\BinaryDownloadException;
use YtPilot\Services\Filesystem\PathService;

final class DownloaderService
{
    public function __construct(
        private readonly PathService $pathService,
    ) {}

    public function download(string $url, string $destination): void
    {
        $this->pathService->ensureBinDirectory();

        $context = stream_context_create([
            'http' => [
                'method' => 'GET',
                'header' => [
                    'User-Agent: YtPilot/1.0',
                    'Accept: application/octet-stream',
                ],
                'follow_location' => true,
                'timeout' => 300,
            ],
            'ssl' => [
                'verify_peer' => true,
                'verify_peer_name' => true,
            ],
        ]);

        $content = @file_get_contents($url, false, $context);

        if ($content === false) {
            $error = error_get_last();
            throw BinaryDownloadException::failedToDownload(
                basename($destination),
                $url,
                $error['message'] ?? 'Unknown error'
            );
        }

        $written = @file_put_contents($destination, $content);

        if ($written === false) {
            throw BinaryDownloadException::unableToWrite($destination);
        }

        $this->pathService->makeExecutable($destination);
    }

    public function downloadWithProgress(string $url, string $destination, ?callable $progressCallback = null): void
    {
        $this->pathService->ensureBinDirectory();

        $ch = curl_init($url);

        if ($ch === false) {
            $this->download($url, $destination);

            return;
        }

        $fp = fopen($destination, 'wb');

        if ($fp === false) {
            curl_close($ch);
            throw BinaryDownloadException::unableToWrite($destination);
        }

        curl_setopt_array($ch, [
            CURLOPT_FILE => $fp,
            CURLOPT_FOLLOWLOCATION => true,
            CURLOPT_USERAGENT => 'YtPilot/1.0',
            CURLOPT_TIMEOUT => 300,
            CURLOPT_SSL_VERIFYPEER => true,
            CURLOPT_NOPROGRESS => $progressCallback === null,
        ]);

        if ($progressCallback !== null) {
            curl_setopt($ch, CURLOPT_PROGRESSFUNCTION, function ($ch, $downloadSize, $downloaded) use ($progressCallback): int {
                if ($downloadSize > 0) {
                    $progressCallback($downloaded, $downloadSize);
                }

                return 0;
            });
        }

        $success = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $error = curl_error($ch);

        curl_close($ch);
        fclose($fp);

        if (!$success || $httpCode >= 400) {
            @unlink($destination);
            throw BinaryDownloadException::failedToDownload(
                basename($destination),
                $url,
                $error ?: "HTTP {$httpCode}"
            );
        }

        $this->pathService->makeExecutable($destination);
    }
}
