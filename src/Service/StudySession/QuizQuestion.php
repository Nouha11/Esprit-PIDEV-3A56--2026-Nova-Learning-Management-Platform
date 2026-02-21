<?php

namespace App\Service\StudySession;

/**
 * Data model for AI-generated quiz questions
 */
class QuizQuestion
{
    public string $question;
    public array $options; // For multiple choice questions
    public string $correctAnswer;
    public string $type; // 'multiple_choice' or 'short_answer'
    public ?string $explanation = null;

    public function __construct(
        string $question,
        array $options,
        string $correctAnswer,
        string $type = 'multiple_choice',
        ?string $explanation = null
    ) {
        $this->question = $question;
        $this->options = $options;
        $this->correctAnswer = $correctAnswer;
        $this->type = $type;
        $this->explanation = $explanation;
    }
}
