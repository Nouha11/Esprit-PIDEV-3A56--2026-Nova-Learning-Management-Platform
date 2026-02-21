<?php

namespace App\Service\StudySession;

/**
 * Data model for Wikipedia article search results
 */
class WikipediaArticle
{
    public string $title;
    public string $summary;
    public string $url;
    public ?string $thumbnailUrl;

    /**
     * Get the full Wikipedia article URL
     */
    public function getUrl(): string
    {
        return $this->url;
    }

    /**
     * Check if the article has a thumbnail
     */
    public function hasThumbnail(): bool
    {
        return $this->thumbnailUrl !== null && $this->thumbnailUrl !== '';
    }
}
