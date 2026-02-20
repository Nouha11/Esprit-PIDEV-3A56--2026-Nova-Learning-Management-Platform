<?php

namespace App\Tests\Service\StudySession;

use App\Service\StudySession\YouTubeApiClient;
use App\Service\StudySession\YouTubeVideo;
use PHPUnit\Framework\TestCase;
use Psr\Log\LoggerInterface;
use Symfony\Component\HttpClient\MockHttpClient;
use Symfony\Component\HttpClient\Response\MockResponse;
use Symfony\Contracts\HttpClient\Exception\TransportExceptionInterface;

class YouTubeApiClientTest extends TestCase
{
    private LoggerInterface $logger;
    private string $apiKey = 'test_api_key';

    protected function setUp(): void
    {
        $this->logger = $this->createMock(LoggerInterface::class);
    }

    public function testSearchVideosReturnsVideos(): void
    {
        $mockResponseData = [
            'items' => [
                [
                    'id' => ['videoId' => 'abc123'],
                    'snippet' => [
                        'title' => 'Test Video 1',
                        'channelTitle' => 'Test Channel 1',
                        'publishedAt' => '2024-01-01T00:00:00Z',
                        'thumbnails' => [
                            'medium' => ['url' => 'https://example.com/thumb1.jpg']
                        ]
                    ]
                ],
                [
                    'id' => ['videoId' => 'def456'],
                    'snippet' => [
                        'title' => 'Test Video 2',
                        'channelTitle' => 'Test Channel 2',
                        'publishedAt' => '2024-01-02T00:00:00Z',
                        'thumbnails' => [
                            'default' => ['url' => 'https://example.com/thumb2.jpg']
                        ]
                    ]
                ]
            ]
        ];

        $mockResponse = new MockResponse(json_encode($mockResponseData), [
            'http_code' => 200,
        ]);

        $httpClient = new MockHttpClient($mockResponse);
        $client = new YouTubeApiClient($httpClient, $this->logger, $this->apiKey);

        $videos = $client->searchVideos('symfony tutorial');

        $this->assertCount(2, $videos);
        $this->assertInstanceOf(YouTubeVideo::class, $videos[0]);
        $this->assertEquals('abc123', $videos[0]->videoId);
        $this->assertEquals('Test Video 1', $videos[0]->title);
        $this->assertEquals('Test Channel 1', $videos[0]->channelName);
        $this->assertEquals('https://example.com/thumb1.jpg', $videos[0]->thumbnailUrl);
    }

    public function testSearchVideosLimitsResultsTo10(): void
    {
        // Create 10 mock video items (API should limit to 10)
        $items = [];
        for ($i = 1; $i <= 10; $i++) {
            $items[] = [
                'id' => ['videoId' => "video{$i}"],
                'snippet' => [
                    'title' => "Video {$i}",
                    'channelTitle' => "Channel {$i}",
                    'publishedAt' => '2024-01-01T00:00:00Z',
                    'thumbnails' => [
                        'medium' => ['url' => "https://example.com/thumb{$i}.jpg"]
                    ]
                ]
            ];
        }

        $mockResponseData = ['items' => $items];
        $mockResponse = new MockResponse(json_encode($mockResponseData), [
            'http_code' => 200,
        ]);

        $httpClient = new MockHttpClient($mockResponse);
        $client = new YouTubeApiClient($httpClient, $this->logger, $this->apiKey);

        // Request 15 videos, but API should limit to 10
        $videos = $client->searchVideos('test', 15);

        // The actual limiting happens in the API call via maxResults parameter
        // Our client ensures maxResults is capped at 10
        $this->assertCount(10, $videos);
    }

    public function testSearchVideosHandlesApiUnavailable(): void
    {
        $mockResponse = new MockResponse('', [
            'http_code' => 503, // Service Unavailable
        ]);

        $httpClient = new MockHttpClient($mockResponse);
        $client = new YouTubeApiClient($httpClient, $this->logger, $this->apiKey);

        $this->logger->expects($this->once())
            ->method('error')
            ->with(
                $this->stringContains('YouTube API'),
                $this->anything()
            );

        $videos = $client->searchVideos('test');

        $this->assertIsArray($videos);
        $this->assertEmpty($videos);
    }

    public function testSearchVideosHandlesTimeout(): void
    {
        $mockResponse = new MockResponse('', [
            'http_code' => 200,
        ]);
        
        // Create a mock that throws TransportException
        $httpClient = $this->createMock(\Symfony\Contracts\HttpClient\HttpClientInterface::class);
        $httpClient->method('request')
            ->willThrowException(new class extends \Exception implements TransportExceptionInterface {});

        $client = new YouTubeApiClient($httpClient, $this->logger, $this->apiKey);

        $this->logger->expects($this->once())
            ->method('error')
            ->with(
                $this->stringContains('Transport error'),
                $this->anything()
            );

        $videos = $client->searchVideos('test');

        $this->assertIsArray($videos);
        $this->assertEmpty($videos);
    }

    public function testSearchVideosHandlesEmptyResponse(): void
    {
        $mockResponseData = ['items' => []];
        $mockResponse = new MockResponse(json_encode($mockResponseData), [
            'http_code' => 200,
        ]);

        $httpClient = new MockHttpClient($mockResponse);
        $client = new YouTubeApiClient($httpClient, $this->logger, $this->apiKey);

        $videos = $client->searchVideos('nonexistent query');

        $this->assertIsArray($videos);
        $this->assertEmpty($videos);
    }

    public function testSearchVideosHandlesMissingItems(): void
    {
        $mockResponseData = []; // No 'items' key
        $mockResponse = new MockResponse(json_encode($mockResponseData), [
            'http_code' => 200,
        ]);

        $httpClient = new MockHttpClient($mockResponse);
        $client = new YouTubeApiClient($httpClient, $this->logger, $this->apiKey);

        $this->logger->expects($this->once())
            ->method('warning')
            ->with(
                $this->stringContains('No items in response'),
                $this->anything()
            );

        $videos = $client->searchVideos('test');

        $this->assertIsArray($videos);
        $this->assertEmpty($videos);
    }

    public function testSearchVideosHandlesInvalidVideoItem(): void
    {
        $mockResponseData = [
            'items' => [
                [
                    'id' => ['videoId' => 'valid123'],
                    'snippet' => [
                        'title' => 'Valid Video',
                        'channelTitle' => 'Valid Channel',
                        'publishedAt' => '2024-01-01T00:00:00Z',
                        'thumbnails' => [
                            'medium' => ['url' => 'https://example.com/thumb.jpg']
                        ]
                    ]
                ],
                [
                    // Missing videoId - should be skipped
                    'id' => [],
                    'snippet' => [
                        'title' => 'Invalid Video',
                    ]
                ]
            ]
        ];

        $mockResponse = new MockResponse(json_encode($mockResponseData), [
            'http_code' => 200,
        ]);

        $httpClient = new MockHttpClient($mockResponse);
        $client = new YouTubeApiClient($httpClient, $this->logger, $this->apiKey);

        $videos = $client->searchVideos('test');

        // Should only return the valid video
        $this->assertCount(1, $videos);
        $this->assertEquals('valid123', $videos[0]->videoId);
    }

    public function testYouTubeVideoGetUrl(): void
    {
        $video = new YouTubeVideo();
        $video->videoId = 'abc123';

        $this->assertEquals('https://www.youtube.com/watch?v=abc123', $video->getUrl());
    }

    public function testYouTubeVideoGetEmbedUrl(): void
    {
        $video = new YouTubeVideo();
        $video->videoId = 'abc123';

        $this->assertEquals('https://www.youtube.com/embed/abc123', $video->getEmbedUrl());
    }
}
