<?php

namespace App\Service\StudySession;

/**
 * Data model for AI-generated study recommendations
 */
class AIRecommendation
{
    public string $type; // 'duration', 'timing', 'break', 'focus'
    public string $message;
    public ?array $data = null; // additional structured data

    public function __construct(string $type, string $message, ?array $data = null)
    {
        $this->type = $type;
        $this->message = $message;
        $this->data = $data;
    }
}
