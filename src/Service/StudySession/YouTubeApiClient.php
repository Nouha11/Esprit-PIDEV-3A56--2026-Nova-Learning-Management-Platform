<?php

namespace App\Service\StudySession;

use Psr\Log\LoggerInterface;
use Symfony\Contracts\HttpClient\HttpClientInterface;
use Symfony\Contracts\HttpClient\Exception\TransportExceptionInterface;
use Symfony\Contracts\HttpClient\Exception\ClientExceptionInterface;
use Symfony\Contracts\HttpClient\Exception\ServerExceptionInterface;

class YouTubeApiClient
{
    private const API_BASE_URL = 'https://www.googleapis.com/youtube/v3';
    private const TIMEOUT = 10; // 10 seconds
    private const MAX_RESULTS = 10;

    private HttpClientInterface $httpClient;
    private string $apiKey;
    private LoggerInterface $logger;

    public function __construct(
        HttpClientInterface $httpClient,
        LoggerInterface $logger,
        string $youtubeApiKey
    ) {
        $this->httpClient = $httpClient;
        $this->logger = $logger;
        $this->apiKey = $youtubeApiKey;
    }

    /**
     * Search for videos on YouTube
     *
     * @param string $query Search term
     * @param int $maxResults Maximum number of results (default 10)
     * @return array Array of YouTubeVideo objects
     */
    public function searchVideos(string $query, int $maxResults = self::MAX_RESULTS): array
    {
        // Limit results to maximum of 10
        $maxResults = min($maxResults, self::MAX_RESULTS);

        try {
            $this->logger->info('YouTube API: Searching videos', [
                'query' => $query,
                'maxResults' => $maxResults
            ]);

            $response = $this->httpClient->request('GET', self::API_BASE_URL . '/search', [
                'timeout' => self::TIMEOUT,
                'query' => [
                    'part' => 'snippet',
                    'q' => $query,
                    'type' => 'video',
                    'maxResults' => $maxResults,
                    'key' => $this->apiKey,
                ]
            ]);

            $statusCode = $response->getStatusCode();

            if ($statusCode !== 200) {
                $this->logger->error('YouTube API: Non-200 status code', [
                    'statusCode' => $statusCode,
                    'query' => $query
                ]);
                return [];
            }

            $data = $response->toArray();

            if (!isset($data['items']) || !is_array($data['items'])) {
                $this->logger->warning('YouTube API: No items in response', [
                    'query' => $query
                ]);
                return [];
            }

            // Parse response into YouTubeVideo objects
            $videos = [];
            foreach ($data['items'] as $item) {
                $video = $this->parseVideoItem($item);
                if ($video !== null) {
                    $videos[] = $video;
                }
            }

            $this->logger->info('YouTube API: Search successful', [
                'query' => $query,
                'resultCount' => count($videos)
            ]);

            return $videos;

        } catch (TransportExceptionInterface $e) {
            // Timeout or network error
            $this->logger->error('YouTube API: Transport error (timeout or network issue)', [
                'query' => $query,
                'error' => $e->getMessage()
            ]);
            return [];

        } catch (ClientExceptionInterface | ServerExceptionInterface $e) {
            // 4xx or 5xx error
            $this->logger->error('YouTube API: HTTP error', [
                'query' => $query,
                'error' => $e->getMessage()
            ]);
            return [];

        } catch (\Exception $e) {
            // Any other error
            $this->logger->error('YouTube API: Unexpected error', [
                'query' => $query,
                'error' => $e->getMessage()
            ]);
            return [];
        }
    }

    /**
     * Parse a video item from YouTube API response
     *
     * @param array $item Video item from API response
     * @return YouTubeVideo|null Parsed video object or null if parsing fails
     */
    private function parseVideoItem(array $item): ?YouTubeVideo
    {
        try {
            $videoId = $item['id']['videoId'] ?? null;
            $snippet = $item['snippet'] ?? [];

            if (!$videoId) {
                return null;
            }

            $video = new YouTubeVideo();
            $video->videoId = $videoId;
            $video->title = $snippet['title'] ?? 'Untitled';
            $video->channelName = $snippet['channelTitle'] ?? 'Unknown Channel';
            $video->thumbnailUrl = $snippet['thumbnails']['medium']['url'] ?? 
                                   $snippet['thumbnails']['default']['url'] ?? '';
            $video->publishedAt = $snippet['publishedAt'] ?? '';

            // Note: View count requires a separate API call to videos endpoint
            // For now, we'll set it to 0 as it's not critical for the search functionality
            $video->viewCount = 0;

            return $video;

        } catch (\Exception $e) {
            $this->logger->warning('YouTube API: Failed to parse video item', [
                'error' => $e->getMessage()
            ]);
            return null;
        }
    }
}
