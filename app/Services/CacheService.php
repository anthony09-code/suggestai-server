<?php
namespace App\Services;

use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Redis;

class CacheService
{
    /**
     * @param string $key
     * @param int $ttl
     * @param callable $callback
     */
    public function remember(string $key, int $ttl, callable $callback): mixed
    {
        return Cache::remember($key, $ttl, $callback);
    }

    /**
     * @param string $key
     */
    public function forget(string $key): void
    {
        Cache::forget($key);
    }

    /**
     * @param array<int, string> $keys
     */
    public function forget_many(array $keys): void
    {
        foreach ($keys as $key) {
            Cache::forget($key);
        }
    }

    /**
     * @param string $pattern
     */
    public function forget_pattern(string $pattern): void
    {
        $keys = Redis::keys("*{$pattern}*");

        foreach ($keys as $key) {
            $stripped = preg_replace("/^[^:]+:/", "", $key);
            Cache::forget($stripped);
        }
    }

    /**
     * @param string $key
     */
    public function has(string $key): bool
    {
        return Cache::has($key);
    }

    public function flush(): void
    {
        Cache::flush();
    }
}
