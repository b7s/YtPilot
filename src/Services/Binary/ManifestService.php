<?php

declare(strict_types=1);

namespace YtPilot\Services\Binary;

use YtPilot\Services\Filesystem\PathService;

final class ManifestService
{
    public function __construct(
        private readonly PathService $pathService,
    ) {}

    /** @return array<string, mixed> */
    public function read(): array
    {
        $path = $this->pathService->getManifestPath();

        if (!file_exists($path)) {
            return [];
        }

        $content = file_get_contents($path);

        if ($content === false) {
            return [];
        }

        $data = json_decode($content, true);

        return is_array($data) ? $data : [];
    }

    /** @param array<string, mixed> $data */
    public function write(array $data): void
    {
        $this->pathService->ensureBinDirectory();
        $path = $this->pathService->getManifestPath();

        file_put_contents($path, json_encode($data, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES));
    }

    /** @param array<string, mixed> $value */
    public function update(string $key, array $value): void
    {
        $manifest = $this->read();
        $manifest[$key] = array_merge($value, ['updated_at' => date('c')]);
        $this->write($manifest);
    }

    /** @return array<string, mixed>|null */
    public function get(string $key): ?array
    {
        $manifest = $this->read();

        return $manifest[$key] ?? null;
    }

    /** @return array<string, mixed>|null */
    public function getBinaryInfo(string $binary): ?array
    {
        return $this->get($binary);
    }

    public function setBinaryInfo(string $binary, string $path, string $version, string $source): void
    {
        $this->update($binary, [
            'path' => $path,
            'version' => $version,
            'source' => $source,
        ]);
    }
}
