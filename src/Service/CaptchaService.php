<?php

namespace App\Service;

use Symfony\Component\HttpFoundation\RequestStack;

class CaptchaService
{
    private const CAPTCHA_SESSION_KEY = 'captcha_answer';
    private const CAPTCHA_QUESTION_KEY = 'captcha_question';

    public function __construct(
        private RequestStack $requestStack
    ) {}

    private function getSession()
    {
        return $this->requestStack->getSession();
    }

    /**
     * Generate a new CAPTCHA challenge
     */
    public function generateCaptcha(): array
    {
        $challenges = [
            // Math challenges
            [
                'type' => 'math',
                'question' => 'What is 5 + 3?',
                'answer' => '8',
            ],
            [
                'type' => 'math',
                'question' => 'What is 12 - 7?',
                'answer' => '5',
            ],
            [
                'type' => 'math',
                'question' => 'What is 4 × 3?',
                'answer' => '12',
            ],
            [
                'type' => 'math',
                'question' => 'What is 15 ÷ 3?',
                'answer' => '5',
            ],
            [
                'type' => 'math',
                'question' => 'What is 9 + 6?',
                'answer' => '15',
            ],
            
            // Educational questions
            [
                'type' => 'education',
                'question' => 'How many days are in a week?',
                'answer' => '7',
            ],
            [
                'type' => 'education',
                'question' => 'How many months are in a year?',
                'answer' => '12',
            ],
            [
                'type' => 'education',
                'question' => 'What color is the sky on a clear day?',
                'answer' => 'blue',
            ],
            [
                'type' => 'education',
                'question' => 'How many letters are in the word "NOVA"?',
                'answer' => '4',
            ],
            [
                'type' => 'education',
                'question' => 'What is the first letter of the alphabet?',
                'answer' => 'a',
            ],
            
            // Pattern recognition
            [
                'type' => 'pattern',
                'question' => 'Complete the sequence: 2, 4, 6, 8, __',
                'answer' => '10',
            ],
            [
                'type' => 'pattern',
                'question' => 'Complete the sequence: 5, 10, 15, 20, __',
                'answer' => '25',
            ],
            
            // Logic questions
            [
                'type' => 'logic',
                'question' => 'If today is Monday, what day is tomorrow?',
                'answer' => 'tuesday',
            ],
            [
                'type' => 'logic',
                'question' => 'What comes after "one, two, three"?',
                'answer' => 'four',
            ],
        ];

        // Select a random challenge
        $challenge = $challenges[array_rand($challenges)];

        // Store the answer in session
        $session = $this->getSession();
        $session->set(self::CAPTCHA_SESSION_KEY, strtolower(trim($challenge['answer'])));
        $session->set(self::CAPTCHA_QUESTION_KEY, $challenge['question']);

        return $challenge;
    }

    /**
     * Verify the CAPTCHA answer
     */
    public function verifyCaptcha(string $userAnswer): bool
    {
        $session = $this->getSession();
        $correctAnswer = $session->get(self::CAPTCHA_SESSION_KEY);
        
        if (!$correctAnswer) {
            return false;
        }

        $userAnswer = strtolower(trim($userAnswer));
        $isCorrect = $userAnswer === $correctAnswer;

        // Clear the CAPTCHA from session after verification
        if ($isCorrect) {
            $session->remove(self::CAPTCHA_SESSION_KEY);
            $session->remove(self::CAPTCHA_QUESTION_KEY);
        }

        return $isCorrect;
    }

    /**
     * Get the current CAPTCHA question
     */
    public function getCurrentQuestion(): ?string
    {
        return $this->getSession()->get(self::CAPTCHA_QUESTION_KEY);
    }

    /**
     * Check if a CAPTCHA is currently active
     */
    public function hasCaptcha(): bool
    {
        return $this->getSession()->has(self::CAPTCHA_SESSION_KEY);
    }

    /**
     * Clear the current CAPTCHA
     */
    public function clearCaptcha(): void
    {
        $session = $this->getSession();
        $session->remove(self::CAPTCHA_SESSION_KEY);
        $session->remove(self::CAPTCHA_QUESTION_KEY);
    }

    /**
     * Generate a visual CAPTCHA image (SVG-based)
     */
    public function generateVisualCaptcha(): array
    {
        $code = $this->generateRandomCode(6);
        $this->getSession()->set(self::CAPTCHA_SESSION_KEY, strtolower($code));

        return [
            'code' => $code,
            'svg' => $this->createSvgCaptcha($code),
        ];
    }

    /**
     * Generate random alphanumeric code
     */
    private function generateRandomCode(int $length = 6): string
    {
        $characters = 'ABCDEFGHJKLMNPQRSTUVWXYZ23456789'; // Exclude confusing characters
        $code = '';
        
        for ($i = 0; $i < $length; $i++) {
            $code .= $characters[random_int(0, strlen($characters) - 1)];
        }
        
        return $code;
    }

    /**
     * Create SVG CAPTCHA image
     */
    private function createSvgCaptcha(string $code): string
    {
        $width = 200;
        $height = 60;
        $fontSize = 24;
        
        // Random colors
        $bgColor = sprintf('hsl(%d, 70%%, 95%%)', random_int(0, 360));
        $textColor = sprintf('hsl(%d, 70%%, 30%%)', random_int(0, 360));
        $lineColor = sprintf('hsl(%d, 50%%, 60%%)', random_int(0, 360));

        $svg = '<svg width="' . $width . '" height="' . $height . '" xmlns="http://www.w3.org/2000/svg">';
        
        // Background
        $svg .= '<rect width="100%" height="100%" fill="' . $bgColor . '"/>';
        
        // Add noise lines
        for ($i = 0; $i < 5; $i++) {
            $x1 = random_int(0, $width);
            $y1 = random_int(0, $height);
            $x2 = random_int(0, $width);
            $y2 = random_int(0, $height);
            $svg .= '<line x1="' . $x1 . '" y1="' . $y1 . '" x2="' . $x2 . '" y2="' . $y2 . '" stroke="' . $lineColor . '" stroke-width="1" opacity="0.3"/>';
        }
        
        // Add text with random positioning and rotation
        $x = 20;
        $chars = str_split($code);
        foreach ($chars as $char) {
            $y = random_int(35, 45);
            $rotation = random_int(-15, 15);
            $svg .= '<text x="' . $x . '" y="' . $y . '" font-family="Arial, sans-serif" font-size="' . $fontSize . '" font-weight="bold" fill="' . $textColor . '" transform="rotate(' . $rotation . ' ' . $x . ' ' . $y . ')">' . $char . '</text>';
            $x += 28;
        }
        
        // Add noise dots
        for ($i = 0; $i < 50; $i++) {
            $cx = random_int(0, $width);
            $cy = random_int(0, $height);
            $svg .= '<circle cx="' . $cx . '" cy="' . $cy . '" r="1" fill="' . $lineColor . '" opacity="0.3"/>';
        }
        
        $svg .= '</svg>';
        
        return $svg;
    }
}
