<?php

namespace App\Service\StudySession;

use Psr\Log\LoggerInterface;
use Symfony\Contracts\Cache\CacheInterface;
use Symfony\Contracts\Cache\ItemInterface;
use Symfony\Contracts\HttpClient\Exception\TransportExceptionInterface;
use Symfony\Contracts\HttpClient\Exception\ClientExceptionInterface;
use Symfony\Contracts\HttpClient\Exception\ServerExceptionInterface;
use Symfony\Contracts\HttpClient\Exception\HttpExceptionInterface;

/**
 * Centralized API error handling service
 * 
 * Provides:
 * - Timeout handling (10 seconds)
 * - Error response parsing
 * - Circuit breaker pattern (disable after 3 consecutive failures)
 * - Logging for all API errors
 */
class ApiErrorHandler
{
    private const TIMEOUT = 10; // 10 seconds
    private const CIRCUIT_BREAKER_THRESHOLD = 3; // Number of consecutive failures before circuit opens
    private const CIRCUIT_BREAKER_TTL = 300; // 5 minutes before circuit breaker resets
    
    private LoggerInterface $logger;
    private CacheInterface $cache;

    public function __construct(
        LoggerInterface $logger,
        CacheInterface $cache
    ) {
        $this->logger = $logger;
        $this->cache = $cache;
    }

    /**
     * Get the timeout value for API requests
     *
     * @return int Timeout in seconds
     */
    public function getTimeout(): int
    {
        return self::TIMEOUT;
    }

    /**
     * Check if circuit breaker is open for a given API
     *
     * @param string $apiName Name of the API (e.g., 'youtube', 'wikipedia', 'weather', 'ai')
     * @return bool True if circuit is open (API is disabled), false otherwise
     */
    public function isCircuitOpen(string $apiName): bool
    {
        $cacheKey = $this->getCircuitBreakerCacheKey($apiName);
        
        try {
            $failureCount = $this->cache->get($cacheKey, function (ItemInterface $item) {
                $item->expiresAfter(self::CIRCUIT_BREAKER_TTL);
                return 0;
            });

            return $failureCount >= self::CIRCUIT_BREAKER_THRESHOLD;
        } catch (\Exception $e) {
            // If cache fails, assume circuit is closed to allow API calls
            $this->logger->warning('ApiErrorHandler: Failed to check circuit breaker', [
                'apiName' => $apiName,
                'error' => $e->getMessage()
            ]);
            return false;
        }
    }

    /**
     * Record a successful API call (resets circuit breaker)
     *
     * @param string $apiName Name of the API
     */
    public function recordSuccess(string $apiName): void
    {
        $cacheKey = $this->getCircuitBreakerCacheKey($apiName);
        
        try {
            $this->cache->delete($cacheKey);
            
            $this->logger->info('ApiErrorHandler: API call successful, circuit breaker reset', [
                'apiName' => $apiName
            ]);
        } catch (\Exception $e) {
            $this->logger->warning('ApiErrorHandler: Failed to reset circuit breaker', [
                'apiName' => $apiName,
                'error' => $e->getMessage()
            ]);
        }
    }

    /**
     * Record a failed API call (increments circuit breaker counter)
     *
     * @param string $apiName Name of the API
     */
    public function recordFailure(string $apiName): void
    {
        $cacheKey = $this->getCircuitBreakerCacheKey($apiName);
        
        try {
            $failureCount = $this->cache->get($cacheKey, function (ItemInterface $item) {
                $item->expiresAfter(self::CIRCUIT_BREAKER_TTL);
                return 0;
            });

            $failureCount++;

            $this->cache->get($cacheKey, function (ItemInterface $item) use ($failureCount) {
                $item->expiresAfter(self::CIRCUIT_BREAKER_TTL);
                return $failureCount;
            });

            if ($failureCount >= self::CIRCUIT_BREAKER_THRESHOLD) {
                $this->logger->error('ApiErrorHandler: Circuit breaker opened (threshold reached)', [
                    'apiName' => $apiName,
                    'failureCount' => $failureCount,
                    'threshold' => self::CIRCUIT_BREAKER_THRESHOLD
                ]);
            } else {
                $this->logger->warning('ApiErrorHandler: API failure recorded', [
                    'apiName' => $apiName,
                    'failureCount' => $failureCount,
                    'threshold' => self::CIRCUIT_BREAKER_THRESHOLD
                ]);
            }
        } catch (\Exception $e) {
            $this->logger->warning('ApiErrorHandler: Failed to record failure', [
                'apiName' => $apiName,
                'error' => $e->getMessage()
            ]);
        }
    }

    /**
     * Handle an exception from an API call
     *
     * @param \Exception $exception The exception to handle
     * @param string $apiName Name of the API
     * @param array $context Additional context for logging
     * @return array Parsed error information
     */
    public function handleException(\Exception $exception, string $apiName, array $context = []): array
    {
        $errorInfo = $this->parseException($exception);
        
        // Log the error with full context
        $this->logger->error('ApiErrorHandler: API call failed', array_merge([
            'apiName' => $apiName,
            'errorType' => $errorInfo['type'],
            'errorMessage' => $errorInfo['message'],
            'userMessage' => $errorInfo['userMessage'],
        ], $context));

        // Record failure for circuit breaker
        $this->recordFailure($apiName);

        return $errorInfo;
    }

    /**
     * Parse an exception into structured error information
     *
     * @param \Exception $exception The exception to parse
     * @return array Structured error information with keys: type, message, userMessage, statusCode
     */
    public function parseException(\Exception $exception): array
    {
        $errorInfo = [
            'type' => 'unknown',
            'message' => $exception->getMessage(),
            'userMessage' => 'An unexpected error occurred. Please try again later.',
            'statusCode' => null,
        ];

        if ($exception instanceof TransportExceptionInterface) {
            // Timeout or network error
            $errorInfo['type'] = 'transport';
            $errorInfo['userMessage'] = 'The service is currently unavailable or taking too long to respond. Please try again later.';
            
            // Check if it's specifically a timeout
            if (strpos($exception->getMessage(), 'timeout') !== false || 
                strpos($exception->getMessage(), 'timed out') !== false) {
                $errorInfo['type'] = 'timeout';
                $errorInfo['userMessage'] = 'The request timed out. Please try again.';
            }
        } elseif ($exception instanceof ClientExceptionInterface) {
            // 4xx error (client error)
            $errorInfo['type'] = 'client_error';
            $errorInfo['userMessage'] = 'Invalid request. Please check your input and try again.';
            
            if ($exception instanceof HttpExceptionInterface) {
                $statusCode = $exception->getResponse()->getStatusCode();
                $errorInfo['statusCode'] = $statusCode;
                
                // Provide more specific messages for common status codes
                switch ($statusCode) {
                    case 400:
                        $errorInfo['userMessage'] = 'Bad request. Please check your input.';
                        break;
                    case 401:
                        $errorInfo['userMessage'] = 'Authentication failed. Please check API credentials.';
                        break;
                    case 403:
                        $errorInfo['userMessage'] = 'Access forbidden. You may not have permission to access this resource.';
                        break;
                    case 404:
                        $errorInfo['userMessage'] = 'Resource not found.';
                        break;
                    case 429:
                        $errorInfo['type'] = 'rate_limit';
                        $errorInfo['userMessage'] = 'Rate limit exceeded. Please try again later.';
                        break;
                }
            }
        } elseif ($exception instanceof ServerExceptionInterface) {
            // 5xx error (server error)
            $errorInfo['type'] = 'server_error';
            $errorInfo['userMessage'] = 'The service is experiencing issues. Please try again later.';
            
            if ($exception instanceof HttpExceptionInterface) {
                $errorInfo['statusCode'] = $exception->getResponse()->getStatusCode();
            }
        }

        return $errorInfo;
    }

    /**
     * Get a user-friendly error message for display
     *
     * @param string $apiName Name of the API
     * @return string User-friendly error message
     */
    public function getCircuitOpenMessage(string $apiName): string
    {
        return sprintf(
            'The %s service is temporarily unavailable due to repeated failures. Please try again later.',
            ucfirst($apiName)
        );
    }

    /**
     * Get the cache key for circuit breaker
     *
     * @param string $apiName Name of the API
     * @return string Cache key
     */
    private function getCircuitBreakerCacheKey(string $apiName): string
    {
        return sprintf('api_circuit_breaker_%s', $apiName);
    }
}
