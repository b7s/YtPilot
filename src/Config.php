<?php

declare(strict_types=1);

namespace YtPilot;

final class Config
{
    /** @var array<string, mixed>|null */
    private static ?array $config = null;

    public static function get(string $key, mixed $default = null): mixed
    {
        self::load();

        $keys = explode('.', $key);
        $value = self::$config;

        foreach ($keys as $segment) {
            if (!is_array($value) || !array_key_exists($segment, $value)) {
                return $default;
            }
            $value = $value[$segment];
        }

        return $value;
    }

    public static function set(string $key, mixed $value): void
    {
        self::load();

        $keys = explode('.', $key);
        $config = &self::$config;

        foreach ($keys as $i => $segment) {
            if ($i === count($keys) - 1) {
                $config[$segment] = $value;
            } else {
                if (!isset($config[$segment]) || !is_array($config[$segment])) {
                    $config[$segment] = [];
                }
                $config = &$config[$segment];
            }
        }
    }

    /** @return array<string, mixed> */
    public static function all(): array
    {
        self::load();

        return self::$config;
    }

    public static function reset(): void
    {
        self::$config = null;
    }

    private static function load(): void
    {
        if (self::$config !== null) {
            return;
        }

        $configPath = dirname(__DIR__) . '/config/ytpilot.php';

        self::$config = file_exists($configPath) ? require $configPath : [];
    }
}
