<?php

namespace App\Service\StudySession;

/**
 * Data model for YouTube video search results
 */
class YouTubeVideo
{
    public string $videoId;
    public string $title;
    public string $channelName;
    public string $thumbnailUrl;
    public int $viewCount;
    public string $publishedAt;

    /**
     * Get the full YouTube video URL
     */
    public function getUrl(): string
    {
        return 'https://www.youtube.com/watch?v=' . $this->videoId;
    }

    /**
     * Get the embed URL for the video
     */
    public function getEmbedUrl(): string
    {
        return 'https://www.youtube.com/embed/' . $this->videoId;
    }
}
