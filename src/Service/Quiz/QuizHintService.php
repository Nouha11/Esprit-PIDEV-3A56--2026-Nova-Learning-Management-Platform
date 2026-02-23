<?php

namespace App\Service\Quiz;

use App\Entity\Quiz\Question;
use Symfony\Contracts\HttpClient\HttpClientInterface;
use Psr\Log\LoggerInterface;

class QuizHintService
{
    public function __construct(
        private HttpClientInterface $httpClient,
        private string $geminiApiKey,
        private LoggerInterface $logger
    ) {
    }

    /**
     * Generate a hint for a quiz question using Google Gemini
     * 
     * @param Question $question
     * @return string The generated hint
     * @throws \Exception If hint generation fails
     */
    public function generateHint(Question $question): string
    {
        try {
            $prompt = $this->buildPrompt($question);
            
            // Using Gemini 2.5 Flash API for text generation
            $response = $this->httpClient->request('POST', 'https://generativelanguage.googleapis.com/v1beta/models/gemini-2.5-flash:generateContent', [
                'headers' => [
                    'Content-Type' => 'application/json',
                    'X-goog-api-key' => $this->geminiApiKey,
                ],
                'json' => [
                    'contents' => [
                        [
                            'parts' => [
                                ['text' => $prompt]
                            ]
                        ]
                    ],
                    'generationConfig' => [
                        'temperature' => 0.7,
                        'topK' => 40,
                        'topP' => 0.95,
                        'maxOutputTokens' => 150,
                    ],
                    'safetySettings' => [
                        [
                            'category' => 'HARM_CATEGORY_HARASSMENT',
                            'threshold' => 'BLOCK_NONE'
                        ],
                        [
                            'category' => 'HARM_CATEGORY_HATE_SPEECH',
                            'threshold' => 'BLOCK_NONE'
                        ],
                        [
                            'category' => 'HARM_CATEGORY_SEXUALLY_EXPLICIT',
                            'threshold' => 'BLOCK_NONE'
                        ],
                        [
                            'category' => 'HARM_CATEGORY_DANGEROUS_CONTENT',
                            'threshold' => 'BLOCK_NONE'
                        ]
                    ]
                ],
                'timeout' => 15,
            ]);

            $data = $response->toArray();
            
            // Gemini returns response in candidates array
            if (isset($data['candidates'][0]['content']['parts'][0]['text'])) {
                $hint = trim($data['candidates'][0]['content']['parts'][0]['text']);
                // Clean up the hint
                $hint = $this->cleanHintText($hint);
                return $hint;
            }
            
            throw new \Exception('Invalid response from Gemini API');
            
        } catch (\Exception $e) {
            $this->logger->error('Failed to generate quiz hint', [
                'question_id' => $question->getId(),
                'error' => $e->getMessage()
            ]);
            
            // Return a fallback hint
            return $this->getFallbackHint($question);
        }
    }

    /**
     * Build the prompt for Gemini based on the question
     */
    private function buildPrompt(Question $question): string
    {
        $questionText = $question->getText();
        $difficulty = $question->getDifficulty();
        
        // Get the choices (without revealing which is correct)
        $choices = [];
        foreach ($question->getChoices() as $choice) {
            $choices[] = $choice->getContent();
        }
        $choicesText = implode(', ', $choices);
        
        // Format prompt for Gemini
        return sprintf(
            "You are a helpful tutor. Provide a subtle hint (2-3 sentences max) for this %s difficulty quiz question. Guide the student without revealing the answer directly.\n\nQuestion: %s\nChoices: %s\n\nProvide only the hint, nothing else:",
            strtolower($difficulty),
            $questionText,
            $choicesText
        );
    }

    /**
     * Clean the generated hint text
     */
    private function cleanHintText(string $hint): string
    {
        // Remove common artifacts from generation
        $hint = preg_replace('/\[INST\].*?\[\/INST\]/s', '', $hint);
        $hint = preg_replace('/^(Hint:|Answer:|Here\'s a hint:)/i', '', $hint);
        $hint = trim($hint);
        
        // Limit to reasonable length (first 3 sentences)
        $sentences = preg_split('/(?<=[.!?])\s+/', $hint, 4);
        if (count($sentences) > 3) {
            $hint = implode(' ', array_slice($sentences, 0, 3));
        }
        
        return $hint;
    }

    /**
     * Provide a fallback hint if AI fails
     */
    private function getFallbackHint(Question $question): string
    {
        $difficulty = $question->getDifficulty();
        
        $fallbackHints = [
            'Easy' => 'Think carefully about the basics. The answer is often simpler than you think!',
            'Medium' => 'Consider what you know about this topic. Try to eliminate obviously wrong answers first.',
            'Hard' => 'This is a challenging question. Break it down into smaller parts and think about each component.'
        ];
        
        return $fallbackHints[$difficulty] ?? 'Take your time and think through each option carefully.';
    }

    /**
     * Calculate XP with hint penalty (50% reduction)
     */
    public function calculateXpWithHintPenalty(int $originalXp): int
    {
        return (int) ceil($originalXp / 2);
    }
}
