<?php

namespace App\Service\StudySession;

use App\Entity\users\User;
use Symfony\Contracts\Cache\CacheInterface;
use Symfony\Contracts\Cache\ItemInterface;

class CacheService
{
    private const ANALYTICS_TTL = 300; // 5 minutes
    private const API_RESPONSE_TTL = 3600; // 1 hour

    public function __construct(
        private CacheInterface $cache
    ) {
    }

    /**
     * Cache analytics data with 5-minute TTL
     *
     * @param string $key Cache key
     * @param callable $callback Function to execute if cache miss
     * @return mixed Cached or fresh data
     */
    public function cacheAnalytics(string $key, callable $callback): mixed
    {
        return $this->cache->get($key, function (ItemInterface $item) use ($callback) {
            $item->expiresAfter(self::ANALYTICS_TTL);
            return $callback();
        });
    }

    /**
     * Cache API response with 1-hour TTL
     *
     * @param string $key Cache key
     * @param callable $callback Function to execute if cache miss
     * @return mixed Cached or fresh data
     */
    public function cacheApiResponse(string $key, callable $callback): mixed
    {
        return $this->cache->get($key, function (ItemInterface $item) use ($callback) {
            $item->expiresAfter(self::API_RESPONSE_TTL);
            return $callback();
        });
    }

    /**
     * Invalidate cache entries related to a user's analytics
     *
     * @param User $user
     * @return void
     */
    public function invalidateUserAnalytics(User $user): void
    {
        $userId = $user->getId();
        
        // Invalidate all analytics cache keys for this user
        $patterns = [
            "analytics.total_study_time.{$userId}",
            "analytics.total_xp.{$userId}",
            "analytics.completion_rate.{$userId}",
            "analytics.study_time_by_course.{$userId}",
            "analytics.xp_over_time.{$userId}",
            "analytics.energy_patterns.{$userId}",
        ];

        foreach ($patterns as $pattern) {
            // Delete all keys matching the pattern (with any time range suffix)
            $this->cache->delete($pattern);
            
            // Also delete with common time range suffixes
            $this->cache->delete($pattern . '.week');
            $this->cache->delete($pattern . '.month');
            $this->cache->delete($pattern . '.year');
        }
    }

    /**
     * Invalidate a specific cache key
     *
     * @param string $key
     * @return void
     */
    public function invalidate(string $key): void
    {
        $this->cache->delete($key);
    }

    /**
     * Generate cache key for analytics data
     *
     * @param string $metric Metric name (e.g., 'total_study_time')
     * @param User $user
     * @param \DateTimeInterface $start
     * @param \DateTimeInterface $end
     * @return string
     */
    public function generateAnalyticsCacheKey(
        string $metric,
        User $user,
        \DateTimeInterface $start,
        \DateTimeInterface $end
    ): string {
        $userId = $user->getId();
        $startStr = $start->format('Y-m-d');
        $endStr = $end->format('Y-m-d');
        
        return "analytics.{$metric}.{$userId}.{$startStr}.{$endStr}";
    }

    /**
     * Generate cache key for API responses
     *
     * @param string $apiName API name (e.g., 'youtube', 'wikipedia', 'weather')
     * @param string $query Query or identifier
     * @param array $params Additional parameters
     * @return string
     */
    public function generateApiCacheKey(string $apiName, string $query, array $params = []): string
    {
        $paramsStr = !empty($params) ? '.' . md5(serialize($params)) : '';
        $queryHash = md5($query);
        
        return "api.{$apiName}.{$queryHash}{$paramsStr}";
    }

    /**
     * Clear all cache entries
     *
     * @return void
     */
    public function clearAll(): void
    {
        $this->cache->clear();
    }
}
