<?php

namespace App\Service\StudySession;

use Psr\Log\LoggerInterface;
use Symfony\Contracts\HttpClient\HttpClientInterface;
use Symfony\Contracts\HttpClient\Exception\TransportExceptionInterface;
use Symfony\Contracts\HttpClient\Exception\ClientExceptionInterface;
use Symfony\Contracts\HttpClient\Exception\ServerExceptionInterface;

class WikipediaApiClient
{
    private const API_BASE_URL = 'https://en.wikipedia.org/w/api.php';
    private const TIMEOUT = 10; // 10 seconds

    private HttpClientInterface $httpClient;
    private LoggerInterface $logger;

    public function __construct(
        HttpClientInterface $httpClient,
        LoggerInterface $logger
    ) {
        $this->httpClient = $httpClient;
        $this->logger = $logger;
    }

    /**
     * Search for articles on Wikipedia
     *
     * @param string $query Search term
     * @return array Array of WikipediaArticle objects
     */
    public function searchArticles(string $query): array
    {
        try {
            $this->logger->info('Wikipedia API: Searching articles', [
                'query' => $query
            ]);

            $response = $this->httpClient->request('GET', self::API_BASE_URL, [
                'timeout' => self::TIMEOUT,
                'query' => [
                    'action' => 'query',
                    'format' => 'json',
                    'list' => 'search',
                    'srsearch' => $query,
                    'utf8' => 1,
                    'formatversion' => 2,
                ]
            ]);

            $statusCode = $response->getStatusCode();

            if ($statusCode !== 200) {
                $this->logger->error('Wikipedia API: Non-200 status code', [
                    'statusCode' => $statusCode,
                    'query' => $query
                ]);
                return [];
            }

            $data = $response->toArray();

            if (!isset($data['query']['search']) || !is_array($data['query']['search'])) {
                $this->logger->warning('Wikipedia API: No search results in response', [
                    'query' => $query
                ]);
                return [];
            }

            // Parse response into WikipediaArticle objects
            $articles = [];
            foreach ($data['query']['search'] as $item) {
                $article = $this->parseArticleItem($item);
                if ($article !== null) {
                    $articles[] = $article;
                }
            }

            $this->logger->info('Wikipedia API: Search successful', [
                'query' => $query,
                'resultCount' => count($articles)
            ]);

            return $articles;

        } catch (TransportExceptionInterface $e) {
            // Timeout or network error
            $this->logger->error('Wikipedia API: Transport error (timeout or network issue)', [
                'query' => $query,
                'error' => $e->getMessage()
            ]);
            return [];

        } catch (ClientExceptionInterface | ServerExceptionInterface $e) {
            // 4xx or 5xx error
            $this->logger->error('Wikipedia API: HTTP error', [
                'query' => $query,
                'error' => $e->getMessage()
            ]);
            return [];

        } catch (\Exception $e) {
            // Any other error
            $this->logger->error('Wikipedia API: Unexpected error', [
                'query' => $query,
                'error' => $e->getMessage()
            ]);
            return [];
        }
    }

    /**
     * Get article summary for a specific title
     *
     * @param string $title Article title
     * @return string|null Article summary or null if not found
     */
    public function getArticleSummary(string $title): ?string
    {
        try {
            $this->logger->info('Wikipedia API: Getting article summary', [
                'title' => $title
            ]);

            $response = $this->httpClient->request('GET', self::API_BASE_URL, [
                'timeout' => self::TIMEOUT,
                'query' => [
                    'action' => 'query',
                    'format' => 'json',
                    'prop' => 'extracts',
                    'exintro' => true,
                    'explaintext' => true,
                    'titles' => $title,
                    'utf8' => 1,
                    'formatversion' => 2,
                ]
            ]);

            $statusCode = $response->getStatusCode();

            if ($statusCode !== 200) {
                $this->logger->error('Wikipedia API: Non-200 status code for summary', [
                    'statusCode' => $statusCode,
                    'title' => $title
                ]);
                return null;
            }

            $data = $response->toArray();

            if (!isset($data['query']['pages'][0]['extract'])) {
                $this->logger->warning('Wikipedia API: No extract found for article', [
                    'title' => $title
                ]);
                return null;
            }

            $summary = $data['query']['pages'][0]['extract'];

            $this->logger->info('Wikipedia API: Summary retrieved successfully', [
                'title' => $title,
                'summaryLength' => strlen($summary)
            ]);

            return $summary;

        } catch (TransportExceptionInterface $e) {
            // Timeout or network error
            $this->logger->error('Wikipedia API: Transport error getting summary', [
                'title' => $title,
                'error' => $e->getMessage()
            ]);
            return null;

        } catch (ClientExceptionInterface | ServerExceptionInterface $e) {
            // 4xx or 5xx error
            $this->logger->error('Wikipedia API: HTTP error getting summary', [
                'title' => $title,
                'error' => $e->getMessage()
            ]);
            return null;

        } catch (\Exception $e) {
            // Any other error
            $this->logger->error('Wikipedia API: Unexpected error getting summary', [
                'title' => $title,
                'error' => $e->getMessage()
            ]);
            return null;
        }
    }

    /**
     * Parse an article item from Wikipedia API response
     *
     * @param array $item Article item from API response
     * @return WikipediaArticle|null Parsed article object or null if parsing fails
     */
    private function parseArticleItem(array $item): ?WikipediaArticle
    {
        try {
            $title = $item['title'] ?? null;

            if (!$title) {
                return null;
            }

            $article = new WikipediaArticle();
            $article->title = $title;
            
            // Extract snippet and clean HTML tags
            $snippet = $item['snippet'] ?? '';
            $article->summary = strip_tags($snippet);
            
            // Build Wikipedia URL
            $article->url = 'https://en.wikipedia.org/wiki/' . urlencode(str_replace(' ', '_', $title));
            
            // Thumbnail is not available in search results, would require separate API call
            $article->thumbnailUrl = null;

            return $article;

        } catch (\Exception $e) {
            $this->logger->warning('Wikipedia API: Failed to parse article item', [
                'error' => $e->getMessage()
            ]);
            return null;
        }
    }
}
