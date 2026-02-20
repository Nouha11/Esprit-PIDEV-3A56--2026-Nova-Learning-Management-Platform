<?php

namespace App\Tests\Service\StudySession;

use App\Entity\users\User;
use App\Service\StudySession\CacheService;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Cache\Adapter\ArrayAdapter;

class CacheServiceTest extends TestCase
{
    private CacheService $cacheService;
    private ArrayAdapter $cache;

    protected function setUp(): void
    {
        $this->cache = new ArrayAdapter();
        $this->cacheService = new CacheService($this->cache);
    }

    public function testCacheAnalyticsStoresAndRetrievesData(): void
    {
        $key = 'test.analytics.key';
        $expectedValue = ['total_time' => 120, 'total_xp' => 500];
        
        $callCount = 0;
        $callback = function() use ($expectedValue, &$callCount) {
            $callCount++;
            return $expectedValue;
        };

        // First call should execute callback
        $result1 = $this->cacheService->cacheAnalytics($key, $callback);
        $this->assertEquals($expectedValue, $result1);
        $this->assertEquals(1, $callCount);

        // Second call should return cached value without executing callback
        $result2 = $this->cacheService->cacheAnalytics($key, $callback);
        $this->assertEquals($expectedValue, $result2);
        $this->assertEquals(1, $callCount, 'Callback should not be called again for cached data');
    }

    public function testCacheApiResponseStoresAndRetrievesData(): void
    {
        $key = 'test.api.key';
        $expectedValue = ['videos' => ['video1', 'video2']];
        
        $callCount = 0;
        $callback = function() use ($expectedValue, &$callCount) {
            $callCount++;
            return $expectedValue;
        };

        // First call should execute callback
        $result1 = $this->cacheService->cacheApiResponse($key, $callback);
        $this->assertEquals($expectedValue, $result1);
        $this->assertEquals(1, $callCount);

        // Second call should return cached value without executing callback
        $result2 = $this->cacheService->cacheApiResponse($key, $callback);
        $this->assertEquals($expectedValue, $result2);
        $this->assertEquals(1, $callCount, 'Callback should not be called again for cached data');
    }

    public function testInvalidateRemovesCacheEntry(): void
    {
        $key = 'test.invalidate.key';
        $value = 'test value';
        
        $callCount = 0;
        $callback = function() use ($value, &$callCount) {
            $callCount++;
            return $value;
        };

        // Cache the value
        $this->cacheService->cacheAnalytics($key, $callback);
        $this->assertEquals(1, $callCount);

        // Invalidate the cache
        $this->cacheService->invalidate($key);

        // Next call should execute callback again
        $this->cacheService->cacheAnalytics($key, $callback);
        $this->assertEquals(2, $callCount, 'Callback should be called again after invalidation');
    }

    public function testInvalidateUserAnalyticsRemovesUserCacheEntries(): void
    {
        $user = $this->createMock(User::class);
        $user->method('getId')->willReturn(123);

        // Cache some analytics data for the user
        $key1 = 'analytics.total_study_time.123';
        $key2 = 'analytics.total_xp.123.week';
        
        $this->cacheService->cacheAnalytics($key1, fn() => 100);
        $this->cacheService->cacheAnalytics($key2, fn() => 500);

        // Invalidate user analytics
        $this->cacheService->invalidateUserAnalytics($user);

        // Verify cache entries are removed by checking if callback is called again
        $callCount = 0;
        $this->cacheService->cacheAnalytics($key1, function() use (&$callCount) {
            $callCount++;
            return 100;
        });
        
        $this->assertEquals(1, $callCount, 'Cache should be invalidated and callback called');
    }

    public function testGenerateAnalyticsCacheKeyCreatesConsistentKeys(): void
    {
        $user = $this->createMock(User::class);
        $user->method('getId')->willReturn(456);

        $start = new \DateTime('2024-01-01');
        $end = new \DateTime('2024-01-31');

        $key1 = $this->cacheService->generateAnalyticsCacheKey('total_study_time', $user, $start, $end);
        $key2 = $this->cacheService->generateAnalyticsCacheKey('total_study_time', $user, $start, $end);

        $this->assertEquals($key1, $key2, 'Same parameters should generate same cache key');
        $this->assertStringContainsString('analytics.total_study_time.456', $key1);
        $this->assertStringContainsString('2024-01-01', $key1);
        $this->assertStringContainsString('2024-01-31', $key1);
    }

    public function testGenerateApiCacheKeyCreatesConsistentKeys(): void
    {
        $key1 = $this->cacheService->generateApiCacheKey('youtube', 'symfony tutorial');
        $key2 = $this->cacheService->generateApiCacheKey('youtube', 'symfony tutorial');

        $this->assertEquals($key1, $key2, 'Same parameters should generate same cache key');
        $this->assertStringContainsString('api.youtube', $key1);
    }

    public function testGenerateApiCacheKeyWithParametersCreatesUniqueKeys(): void
    {
        $key1 = $this->cacheService->generateApiCacheKey('youtube', 'symfony', ['maxResults' => 10]);
        $key2 = $this->cacheService->generateApiCacheKey('youtube', 'symfony', ['maxResults' => 20]);

        $this->assertNotEquals($key1, $key2, 'Different parameters should generate different cache keys');
    }

    public function testClearAllRemovesAllCacheEntries(): void
    {
        // Cache multiple entries
        $this->cacheService->cacheAnalytics('key1', fn() => 'value1');
        $this->cacheService->cacheAnalytics('key2', fn() => 'value2');
        $this->cacheService->cacheApiResponse('key3', fn() => 'value3');

        // Clear all cache
        $this->cacheService->clearAll();

        // Verify all entries are removed by checking if callbacks are called again
        $callCount = 0;
        $this->cacheService->cacheAnalytics('key1', function() use (&$callCount) {
            $callCount++;
            return 'value1';
        });
        
        $this->assertEquals(1, $callCount, 'Cache should be cleared and callback called');
    }
}
