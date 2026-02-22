<?php

namespace App\Service\Forum;

use Symfony\Contracts\HttpClient\HttpClientInterface;
use Symfony\Component\DomCrawler\Crawler;
use Psr\Log\LoggerInterface;

class OpenGraphFetcher
{
    public function __construct(
        private HttpClientInterface $httpClient,
        private LoggerInterface $logger
    ) {}

    public function fetch(string $url): ?array
    {
        // Ensure standard URL format
        if (!preg_match("~^(?:f|ht)tps?://~i", $url)) {
            $url = "https://" . $url;
        }

        try {
            // Disguise the scraper as a standard Google Chrome browser
            $response = $this->httpClient->request('GET', $url, [
                'timeout' => 5,
                'max_redirects' => 5,
                'verify_peer' => false,
                'verify_host' => false,
                'headers' => [
                    'User-Agent' => 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/120.0.0.0 Safari/537.36',
                    'Accept' => 'text/html,application/xhtml+xml,application/xml;q=0.9,image/avif,image/webp,*/*;q=0.8',
                    'Accept-Language' => 'en-US,en;q=0.5'
                ]
            ]);

            if ($response->getStatusCode() >= 400) {
                return null;
            }

            $content = $response->getContent();
            $crawler = new Crawler($content);

            // Safely extract Title
            $title = null;
            if ($crawler->filter('meta[property="og:title"]')->count()) {
                $title = $crawler->filter('meta[property="og:title"]')->attr('content');
            } elseif ($crawler->filter('meta[name="twitter:title"]')->count()) {
                $title = $crawler->filter('meta[name="twitter:title"]')->attr('content');
            } elseif ($crawler->filter('title')->count()) {
                $title = $crawler->filter('title')->text();
            }

            // Safely extract Description
            $description = null;
            if ($crawler->filter('meta[property="og:description"]')->count()) {
                $description = $crawler->filter('meta[property="og:description"]')->attr('content');
            } elseif ($crawler->filter('meta[name="twitter:description"]')->count()) {
                $description = $crawler->filter('meta[name="twitter:description"]')->attr('content');
            } elseif ($crawler->filter('meta[name="description"]')->count()) {
                $description = $crawler->filter('meta[name="description"]')->attr('content');
            }

            // Safely extract Image
            $image = null;
            if ($crawler->filter('meta[property="og:image"]')->count()) {
                $image = $crawler->filter('meta[property="og:image"]')->attr('content');
            } elseif ($crawler->filter('meta[name="twitter:image"]')->count()) {
                $image = $crawler->filter('meta[name="twitter:image"]')->attr('content');
            }

            return [
                'title' => $title ? trim($title) : 'External Link',
                'description' => $description ? trim($description) : null,
                'image' => $image,
            ];

        } catch (\Exception $e) {
            $this->logger->error('OpenGraph Fetch Exception: ' . $e->getMessage());
            return null;
        }
    }
}