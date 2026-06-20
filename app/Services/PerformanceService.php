<?php

namespace App\Services;

use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;

class PerformanceService
{
    // How long to cache the QoL score result 
    private const QOL_CACHE_SECONDS = 10;

    // Cache key for the QoL score
    private const QOL_CACHE_KEY = 'qol_score_cache';

    
      //Get the cached QoL score, or calculate and cache it fresh
      //This prevents recalculating on every request//

    public static function getCachedQolScore(): array
    {
        return Cache::remember(self::QOL_CACHE_KEY, self::QOL_CACHE_SECONDS, function () {
            return (new QolScoreService())->calculate();
        });
    }

    //Clear the QoL score cache so the next request gets fresh data.
    public static function clearQolCache(): void
    {
        Cache::forget(self::QOL_CACHE_KEY);
    }

    public static function measure(string $label, callable $fn): mixed
    {
        $start = microtime(true);
        $result = $fn();
        $ms = round((microtime(true) - $start) * 1000, 2);

        // Logs directly to storage/logs/laravel.log
        Log::info("QA.2 Performance Measure [{$label}] took {$ms}ms");

        return $result;
    }
}