<?php

declare(strict_types=1);

namespace YtPilot\DTO;

final readonly class FormatItem
{
    public function __construct(
        public string $id,
        public string $ext,
        public ?string $resolution,
        public ?int $fps,
        public ?string $filesize,
        public ?string $tbr,
        public ?string $protocol,
        public ?string $vcodec,
        public ?string $vbr,
        public ?string $acodec,
        public ?string $abr,
        public bool $isAudioOnly,
        public bool $isVideoOnly,
    ) {}

    /** @param array<string, mixed> $data */
    public static function fromParsed(array $data): self
    {
        $resolution = $data['resolution'] ?? null;
        $isAudioOnly = $resolution === 'audio only' || str_contains($resolution ?? '', 'audio');

        return new self(
            id: $data['id'] ?? '',
            ext: $data['ext'] ?? '',
            resolution: $isAudioOnly ? null : $resolution,
            fps: isset($data['fps']) ? (int) $data['fps'] : null,
            filesize: $data['filesize'] ?? null,
            tbr: $data['tbr'] ?? null,
            protocol: $data['proto'] ?? null,
            vcodec: ($data['vcodec'] ?? 'none') !== 'none' ? $data['vcodec'] : null,
            vbr: $data['vbr'] ?? null,
            acodec: ($data['acodec'] ?? 'none') !== 'none' ? $data['acodec'] : null,
            abr: $data['abr'] ?? null,
            isAudioOnly: $isAudioOnly,
            isVideoOnly: ($data['acodec'] ?? 'none') === 'none' && !$isAudioOnly,
        );
    }

    /** @return array<string, mixed> */
    public function toArray(): array
    {
        return [
            'id' => $this->id,
            'ext' => $this->ext,
            'resolution' => $this->resolution,
            'fps' => $this->fps,
            'filesize' => $this->filesize,
            'tbr' => $this->tbr,
            'protocol' => $this->protocol,
            'vcodec' => $this->vcodec,
            'vbr' => $this->vbr,
            'acodec' => $this->acodec,
            'abr' => $this->abr,
            'is_audio_only' => $this->isAudioOnly,
            'is_video_only' => $this->isVideoOnly,
        ];
    }
}
