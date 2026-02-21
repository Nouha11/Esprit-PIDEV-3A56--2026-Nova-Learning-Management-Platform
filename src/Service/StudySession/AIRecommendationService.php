<?php

namespace App\Service\StudySession;

use App\Entity\users\User;
use Psr\Log\LoggerInterface;
use Symfony\Contracts\Cache\CacheInterface;
use Symfony\Contracts\Cache\ItemInterface;
use Symfony\Contracts\HttpClient\HttpClientInterface;
use Symfony\Contracts\HttpClient\Exception\TransportExceptionInterface;
use Symfony\Contracts\HttpClient\Exception\ClientExceptionInterface;
use Symfony\Contracts\HttpClient\Exception\ServerExceptionInterface;

/**
 * AI-powered recommendation service using OpenAI API with Gemini fallback
 * 
 * Provides:
 * - Study recommendations based on session data
 * - Note summarization
 * - Quiz generation from content
 * 
 * Supports both OpenAI and Google Gemini APIs with automatic fallback
 */
class AIRecommendationService
{
    private const API_NAME = 'ai';
    private const CACHE_TTL = 3600; // 1 hour
    private const OPENAI_BASE_URL = 'https://api.openai.com/v1';
    private const GEMINI_BASE_URL = 'https://generativelanguage.googleapis.com/v1beta';
    private const OPENAI_MODEL = 'gpt-3.5-turbo';
    private const GEMINI_MODEL = 'gemini-pro';
    
    private HttpClientInterface $httpClient;
    private LoggerInterface $logger;
    private ApiErrorHandler $errorHandler;
    private CacheInterface $cache;
    private string $openaiApiKey;
    private string $geminiApiKey;

    public function __construct(
        HttpClientInterface $httpClient,
        LoggerInterface $logger,
        ApiErrorHandler $errorHandler,
        CacheInterface $cache,
        string $openaiApiKey,
        string $geminiApiKey
    ) {
        $this->httpClient = $httpClient;
        $this->logger = $logger;
        $this->errorHandler = $errorHandler;
        $this->cache = $cache;
        $this->openaiApiKey = $openaiApiKey;
        $this->geminiApiKey = $geminiApiKey;
        
        // Log API key status (without exposing the keys)
        $hasOpenAI = !empty($this->openaiApiKey) && $this->openaiApiKey !== 'your_openai_api_key_here';
        $hasGemini = !empty($this->geminiApiKey) && $this->geminiApiKey !== 'your_gemini_api_key_here';
        
        if (!$hasOpenAI && !$hasGemini) {
            $this->logger->warning('AIRecommendationService: No AI API keys configured');
        } elseif (!$hasOpenAI) {
            $this->logger->info('AIRecommendationService: Using Gemini API only');
        } elseif (!$hasGemini) {
            $this->logger->info('AIRecommendationService: Using OpenAI API only');
        } else {
            $this->logger->info('AIRecommendationService: Both OpenAI and Gemini APIs available');
        }
    }

    /**
     * Generate personalized study recommendations based on recent session data
     *
     * @param User $user The user to generate recommendations for
     * @param array $recentSessions Array of recent StudySession entities
     * @return array Array of AIRecommendation objects
     */
    public function generateStudyRecommendations(User $user, array $recentSessions): array
    {
        // Check if any API key is configured
        if (!$this->hasValidApiKey()) {
            $this->logger->warning('AIRecommendationService: No valid API keys configured, returning generic recommendations');
            return $this->getGenericRecommendations();
        }

        // Check circuit breaker
        if ($this->errorHandler->isCircuitOpen(self::API_NAME)) {
            $this->logger->warning('AIRecommendationService: Circuit breaker is open, returning cached/generic recommendations');
            return $this->getGenericRecommendations();
        }

        // Generate cache key based on user and session data
        $cacheKey = $this->generateRecommendationsCacheKey($user, $recentSessions);

        try {
            return $this->cache->get($cacheKey, function (ItemInterface $item) use ($user, $recentSessions) {
                $item->expiresAfter(self::CACHE_TTL);
                
                // Analyze session data
                $sessionData = $this->analyzeSessionData($recentSessions);
                
                // Build prompt for AI
                $prompt = $this->buildRecommendationsPrompt($sessionData);
                
                // Call AI API (tries OpenAI first, then Gemini)
                $response = $this->callAI($prompt);
                
                if ($response === null) {
                    // API call failed, return generic recommendations
                    return $this->getGenericRecommendations();
                }
                
                // Parse AI response into recommendations
                $recommendations = $this->parseRecommendations($response, $sessionData);
                
                $this->errorHandler->recordSuccess(self::API_NAME);
                
                return $recommendations;
            });
        } catch (\Exception $e) {
            $this->logger->error('AIRecommendationService: Failed to generate recommendations', [
                'userId' => $user->getId(),
                'error' => $e->getMessage()
            ]);
            return $this->getGenericRecommendations();
        }
    }

    /**
     * Summarize note content using AI
     *
     * @param string $noteContent The note content to summarize
     * @return string Summarized content
     */
    public function summarizeNotes(string $noteContent): string
    {
        // Check if any API key is configured
        if (!$this->hasValidApiKey()) {
            $this->logger->warning('AIRecommendationService: No valid API keys configured');
            return 'AI service is not configured. Please set up your OpenAI or Gemini API key in the .env file.';
        }

        // Check if notes are empty or too short
        if (empty(trim($noteContent)) || strlen($noteContent) < 50) {
            return 'Note content is too short to summarize. Please provide at least 50 characters.';
        }

        // Check circuit breaker
        if ($this->errorHandler->isCircuitOpen(self::API_NAME)) {
            $this->logger->warning('AIRecommendationService: Circuit breaker is open, cannot summarize notes');
            return 'AI service is temporarily unavailable due to repeated failures. Please try again in a few minutes.';
        }

        // Generate cache key
        $cacheKey = 'ai_summary_' . md5($noteContent);

        try {
            return $this->cache->get($cacheKey, function (ItemInterface $item) use ($noteContent) {
                $item->expiresAfter(self::CACHE_TTL);
                
                // Build prompt for summarization
                $prompt = $this->buildSummarizationPrompt($noteContent);
                
                // Call AI API (tries OpenAI first, then Gemini)
                $response = $this->callAI($prompt);
                
                if ($response === null) {
                    return 'Failed to generate summary. The API may be unavailable or your API key may be invalid. Please check your configuration.';
                }
                
                $this->errorHandler->recordSuccess(self::API_NAME);
                
                return $response;
            });
        } catch (\Exception $e) {
            $this->logger->error('AIRecommendationService: Failed to summarize notes', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            return 'Failed to generate summary: ' . $e->getMessage();
        }
    }

    /**
     * Generate quiz questions from content
     *
     * @param string $content The content to generate quiz from
     * @param int $questionCount Number of questions to generate (5-10)
     * @return array Array of QuizQuestion objects
     */
    public function generateQuiz(string $content, int $questionCount = 5): array
    {
        // Check if any API key is configured
        if (!$this->hasValidApiKey()) {
            $this->logger->warning('AIRecommendationService: No valid API keys configured');
            return [];
        }

        // Validate question count (5-10)
        $questionCount = max(5, min(10, $questionCount));

        // Check if content is empty or too short
        if (empty(trim($content)) || strlen($content) < 100) {
            $this->logger->warning('AIRecommendationService: Content too short for quiz generation');
            return [];
        }

        // Check circuit breaker
        if ($this->errorHandler->isCircuitOpen(self::API_NAME)) {
            $this->logger->warning('AIRecommendationService: Circuit breaker is open, cannot generate quiz');
            return [];
        }

        // Generate cache key
        $cacheKey = 'ai_quiz_' . md5($content . $questionCount);

        try {
            return $this->cache->get($cacheKey, function (ItemInterface $item) use ($content, $questionCount) {
                $item->expiresAfter(self::CACHE_TTL);
                
                // Build prompt for quiz generation
                $prompt = $this->buildQuizPrompt($content, $questionCount);
                
                // Call AI API (tries OpenAI first, then Gemini)
                $response = $this->callAI($prompt);
                
                if ($response === null) {
                    return [];
                }
                
                // Parse quiz questions from response
                $questions = $this->parseQuizQuestions($response);
                
                $this->errorHandler->recordSuccess(self::API_NAME);
                
                return $questions;
            });
        } catch (\Exception $e) {
            $this->logger->error('AIRecommendationService: Failed to generate quiz', [
                'error' => $e->getMessage()
            ]);
            return [];
        }
    }

    /**
     * Call AI API with a prompt (tries OpenAI first, then Gemini as fallback)
     *
     * @param string $prompt The prompt to send
     * @return string|null The AI response or null on failure
     */
    private function callAI(string $prompt): ?string
    {
        // Try OpenAI first if available
        if ($this->hasOpenAI()) {
            $this->logger->info('AIRecommendationService: Trying OpenAI API');
            $response = $this->callOpenAI($prompt);
            if ($response !== null) {
                return $response;
            }
            $this->logger->warning('AIRecommendationService: OpenAI failed, trying Gemini fallback');
        }
        
        // Try Gemini as fallback if available
        if ($this->hasGemini()) {
            $this->logger->info('AIRecommendationService: Trying Gemini API');
            return $this->callGemini($prompt);
        }
        
        $this->logger->error('AIRecommendationService: All AI APIs failed or unavailable');
        return null;
    }

    /**
     * Check if any valid API key is configured
     *
     * @return bool
     */
    private function hasValidApiKey(): bool
    {
        return $this->hasOpenAI() || $this->hasGemini();
    }

    /**
     * Check if OpenAI API key is configured
     *
     * @return bool
     */
    private function hasOpenAI(): bool
    {
        return !empty($this->openaiApiKey) && $this->openaiApiKey !== 'your_openai_api_key_here';
    }

    /**
     * Check if Gemini API key is configured
     *
     * @return bool
     */
    private function hasGemini(): bool
    {
        return !empty($this->geminiApiKey) && $this->geminiApiKey !== 'your_gemini_api_key_here';
    }

    /**
     * Call OpenAI API with a prompt
     *
     * @param string $prompt The prompt to send
     * @return string|null The AI response or null on failure
     */
    private function callOpenAI(string $prompt): ?string
    {
        try {
            $this->logger->info('AIRecommendationService: Calling OpenAI API');

            $response = $this->httpClient->request('POST', self::OPENAI_BASE_URL . '/chat/completions', [
                'timeout' => $this->errorHandler->getTimeout(),
                'headers' => [
                    'Authorization' => 'Bearer ' . $this->openaiApiKey,
                    'Content-Type' => 'application/json',
                ],
                'json' => [
                    'model' => self::OPENAI_MODEL,
                    'messages' => [
                        [
                            'role' => 'system',
                            'content' => 'You are a helpful study assistant that provides concise, actionable advice.'
                        ],
                        [
                            'role' => 'user',
                            'content' => $prompt
                        ]
                    ],
                    'temperature' => 0.7,
                    'max_tokens' => 500,
                ]
            ]);

            $statusCode = $response->getStatusCode();

            if ($statusCode !== 200) {
                $this->logger->error('AIRecommendationService: OpenAI non-200 status code', [
                    'statusCode' => $statusCode
                ]);
                $this->errorHandler->recordFailure(self::API_NAME);
                return null;
            }

            $data = $response->toArray();

            if (!isset($data['choices'][0]['message']['content'])) {
                $this->logger->error('AIRecommendationService: OpenAI invalid response structure');
                $this->errorHandler->recordFailure(self::API_NAME);
                return null;
            }

            $content = trim($data['choices'][0]['message']['content']);

            $this->logger->info('AIRecommendationService: OpenAI API call successful');

            return $content;

        } catch (TransportExceptionInterface $e) {
            $this->logger->error('AIRecommendationService: OpenAI transport error', [
                'error' => $e->getMessage()
            ]);
            $this->errorHandler->handleException($e, self::API_NAME, ['action' => 'callOpenAI']);
            return null;

        } catch (ClientExceptionInterface | ServerExceptionInterface $e) {
            $this->logger->error('AIRecommendationService: OpenAI HTTP error', [
                'error' => $e->getMessage(),
                'response' => method_exists($e, 'getResponse') ? $e->getResponse()->getContent(false) : 'N/A'
            ]);
            $this->errorHandler->handleException($e, self::API_NAME, ['action' => 'callOpenAI']);
            return null;

        } catch (\Exception $e) {
            $this->logger->error('AIRecommendationService: OpenAI unexpected error', [
                'error' => $e->getMessage()
            ]);
            $this->errorHandler->recordFailure(self::API_NAME);
            return null;
        }
    }

    /**
     * Call Gemini API with a prompt
     *
     * @param string $prompt The prompt to send
     * @return string|null The AI response or null on failure
     */
    private function callGemini(string $prompt): ?string
    {
        try {
            $this->logger->info('AIRecommendationService: Calling Gemini API');

            $response = $this->httpClient->request('POST', self::GEMINI_BASE_URL . '/models/' . self::GEMINI_MODEL . ':generateContent', [
                'timeout' => $this->errorHandler->getTimeout(),
                'headers' => [
                    'Content-Type' => 'application/json',
                ],
                'query' => [
                    'key' => $this->geminiApiKey
                ],
                'json' => [
                    'contents' => [
                        [
                            'parts' => [
                                [
                                    'text' => 'You are a helpful study assistant that provides concise, actionable advice. ' . $prompt
                                ]
                            ]
                        ]
                    ],
                    'generationConfig' => [
                        'temperature' => 0.7,
                        'maxOutputTokens' => 500,
                    ]
                ]
            ]);

            $statusCode = $response->getStatusCode();

            if ($statusCode !== 200) {
                $this->logger->error('AIRecommendationService: Gemini non-200 status code', [
                    'statusCode' => $statusCode
                ]);
                $this->errorHandler->recordFailure(self::API_NAME);
                return null;
            }

            $data = $response->toArray();

            if (!isset($data['candidates'][0]['content']['parts'][0]['text'])) {
                $this->logger->error('AIRecommendationService: Gemini invalid response structure', [
                    'response' => json_encode($data)
                ]);
                $this->errorHandler->recordFailure(self::API_NAME);
                return null;
            }

            $content = trim($data['candidates'][0]['content']['parts'][0]['text']);

            $this->logger->info('AIRecommendationService: Gemini API call successful');

            return $content;

        } catch (TransportExceptionInterface $e) {
            $this->logger->error('AIRecommendationService: Gemini transport error', [
                'error' => $e->getMessage()
            ]);
            $this->errorHandler->handleException($e, self::API_NAME, ['action' => 'callGemini']);
            return null;

        } catch (ClientExceptionInterface | ServerExceptionInterface $e) {
            $this->logger->error('AIRecommendationService: Gemini HTTP error', [
                'error' => $e->getMessage(),
                'response' => method_exists($e, 'getResponse') ? $e->getResponse()->getContent(false) : 'N/A'
            ]);
            $this->errorHandler->handleException($e, self::API_NAME, ['action' => 'callGemini']);
            return null;

        } catch (\Exception $e) {
            $this->logger->error('AIRecommendationService: Gemini unexpected error', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            $this->errorHandler->recordFailure(self::API_NAME);
            return null;
        }
    }

    /**
     * Analyze session data to extract patterns
     *
     * @param array $sessions Array of StudySession entities
     * @return array Analyzed data
     */
    private function analyzeSessionData(array $sessions): array
    {
        if (empty($sessions)) {
            return [
                'totalSessions' => 0,
                'avgDuration' => 0,
                'avgXP' => 0,
                'completionRate' => 0,
                'lowEnergyCount' => 0,
                'energyPattern' => 'unknown',
                'moodPattern' => 'unknown',
            ];
        }

        $totalDuration = 0;
        $totalXP = 0;
        $completedCount = 0;
        $lowEnergyCount = 0;
        $energyLevels = [];
        $moods = [];

        foreach ($sessions as $session) {
            $totalDuration += $session->getDuration() ?? 0;
            $totalXP += $session->getXpEarned() ?? 0;
            
            if ($session->getCompletedAt() !== null) {
                $completedCount++;
            }

            $energyLevel = $session->getEnergyLevel();
            if ($energyLevel === 'low') {
                $lowEnergyCount++;
            }
            if ($energyLevel) {
                $energyLevels[] = $energyLevel;
            }

            $mood = $session->getMood();
            if ($mood) {
                $moods[] = $mood;
            }
        }

        $sessionCount = count($sessions);
        $avgDuration = $sessionCount > 0 ? round($totalDuration / $sessionCount) : 0;
        $avgXP = $sessionCount > 0 ? round($totalXP / $sessionCount) : 0;
        $completionRate = $sessionCount > 0 ? round(($completedCount / $sessionCount) * 100) : 0;

        // Determine energy pattern
        $lowEnergyPercentage = $sessionCount > 0 ? ($lowEnergyCount / $sessionCount) * 100 : 0;
        $energyPattern = $lowEnergyPercentage > 70 ? 'consistently_low' : 
                        ($lowEnergyPercentage > 30 ? 'variable' : 'good');

        // Determine mood pattern
        $negativeMoodCount = count(array_filter($moods, fn($m) => $m === 'negative'));
        $negativeMoodPercentage = count($moods) > 0 ? ($negativeMoodCount / count($moods)) * 100 : 0;
        $moodPattern = $negativeMoodPercentage > 50 ? 'negative' : 
                      ($negativeMoodPercentage > 20 ? 'mixed' : 'positive');

        return [
            'totalSessions' => $sessionCount,
            'avgDuration' => $avgDuration,
            'avgXP' => $avgXP,
            'completionRate' => $completionRate,
            'lowEnergyCount' => $lowEnergyCount,
            'lowEnergyPercentage' => round($lowEnergyPercentage),
            'energyPattern' => $energyPattern,
            'moodPattern' => $moodPattern,
        ];
    }

    /**
     * Build prompt for study recommendations
     *
     * @param array $sessionData Analyzed session data
     * @return string The prompt
     */
    private function buildRecommendationsPrompt(array $sessionData): string
    {
        return sprintf(
            "Based on the following study session data, provide 3-5 specific, actionable recommendations to improve study effectiveness:\n\n" .
            "- Total sessions: %d\n" .
            "- Average duration: %d minutes\n" .
            "- Average XP earned: %d\n" .
            "- Completion rate: %d%%\n" .
            "- Energy pattern: %s\n" .
            "- Mood pattern: %s\n\n" .
            "Format each recommendation as: [TYPE] Message\n" .
            "Where TYPE is one of: DURATION, TIMING, BREAK, FOCUS\n" .
            "Keep each recommendation under 100 characters.",
            $sessionData['totalSessions'],
            $sessionData['avgDuration'],
            $sessionData['avgXP'],
            $sessionData['completionRate'],
            $sessionData['energyPattern'],
            $sessionData['moodPattern']
        );
    }

    /**
     * Build prompt for note summarization
     *
     * @param string $noteContent The note content
     * @return string The prompt
     */
    private function buildSummarizationPrompt(string $noteContent): string
    {
        return sprintf(
            "Summarize the following study notes into key points. Keep the summary concise (under 200 words) while preserving important concepts:\n\n%s",
            $noteContent
        );
    }

    /**
     * Build prompt for quiz generation
     *
     * @param string $content The content to generate quiz from
     * @param int $questionCount Number of questions
     * @return string The prompt
     */
    private function buildQuizPrompt(string $content, int $questionCount): string
    {
        return sprintf(
            "Generate %d multiple-choice quiz questions based on the following content. " .
            "Format each question as:\n" .
            "Q: [question]\n" .
            "A) [option]\n" .
            "B) [option]\n" .
            "C) [option]\n" .
            "D) [option]\n" .
            "CORRECT: [A/B/C/D]\n" .
            "EXPLANATION: [brief explanation]\n\n" .
            "Content:\n%s",
            $questionCount,
            $content
        );
    }

    /**
     * Parse AI response into recommendation objects
     *
     * @param string $response The AI response
     * @param array $sessionData Session data for context
     * @return array Array of AIRecommendation objects
     */
    private function parseRecommendations(string $response, array $sessionData): array
    {
        $recommendations = [];
        $lines = explode("\n", $response);

        foreach ($lines as $line) {
            $line = trim($line);
            if (empty($line)) {
                continue;
            }

            // Try to parse format: [TYPE] Message
            if (preg_match('/^\[(\w+)\]\s*(.+)$/i', $line, $matches)) {
                $type = strtolower($matches[1]);
                $message = trim($matches[2]);
                
                $recommendations[] = new AIRecommendation($type, $message, $sessionData);
            } else {
                // If format doesn't match, treat as general recommendation
                $recommendations[] = new AIRecommendation('general', $line, $sessionData);
            }
        }

        // If no recommendations were parsed, create a generic one
        if (empty($recommendations)) {
            $recommendations[] = new AIRecommendation(
                'general',
                'Continue maintaining consistent study habits and track your progress.',
                $sessionData
            );
        }

        return $recommendations;
    }

    /**
     * Parse quiz questions from AI response
     *
     * @param string $response The AI response
     * @return array Array of QuizQuestion objects
     */
    private function parseQuizQuestions(string $response): array
    {
        $questions = [];
        $lines = explode("\n", $response);
        
        $currentQuestion = null;
        $currentOptions = [];
        $currentCorrect = null;
        $currentExplanation = null;

        foreach ($lines as $line) {
            $line = trim($line);
            
            if (preg_match('/^Q:\s*(.+)$/i', $line, $matches)) {
                // Save previous question if exists
                if ($currentQuestion && !empty($currentOptions) && $currentCorrect) {
                    $questions[] = new QuizQuestion(
                        $currentQuestion,
                        $currentOptions,
                        $currentCorrect,
                        'multiple_choice',
                        $currentExplanation
                    );
                }
                
                // Start new question
                $currentQuestion = trim($matches[1]);
                $currentOptions = [];
                $currentCorrect = null;
                $currentExplanation = null;
            } elseif (preg_match('/^([A-D])\)\s*(.+)$/i', $line, $matches)) {
                // Option line
                $currentOptions[$matches[1]] = trim($matches[2]);
            } elseif (preg_match('/^CORRECT:\s*([A-D])$/i', $line, $matches)) {
                // Correct answer
                $currentCorrect = strtoupper($matches[1]);
            } elseif (preg_match('/^EXPLANATION:\s*(.+)$/i', $line, $matches)) {
                // Explanation
                $currentExplanation = trim($matches[1]);
            }
        }

        // Save last question
        if ($currentQuestion && !empty($currentOptions) && $currentCorrect) {
            $questions[] = new QuizQuestion(
                $currentQuestion,
                $currentOptions,
                $currentCorrect,
                'multiple_choice',
                $currentExplanation
            );
        }

        return $questions;
    }

    /**
     * Get generic recommendations when AI is unavailable
     *
     * @return array Array of AIRecommendation objects
     */
    private function getGenericRecommendations(): array
    {
        return [
            new AIRecommendation(
                'duration',
                'Try studying in 25-minute focused sessions (Pomodoro technique) with 5-minute breaks.'
            ),
            new AIRecommendation(
                'timing',
                'Schedule study sessions during your peak energy hours for better retention.'
            ),
            new AIRecommendation(
                'break',
                'Take regular breaks to prevent burnout and maintain focus throughout your session.'
            ),
            new AIRecommendation(
                'focus',
                'Minimize distractions by turning off notifications and using focus mode.'
            ),
        ];
    }

    /**
     * Generate cache key for recommendations
     *
     * @param User $user The user
     * @param array $sessions Recent sessions
     * @return string Cache key
     */
    private function generateRecommendationsCacheKey(User $user, array $sessions): string
    {
        $sessionIds = array_map(fn($s) => $s->getId(), $sessions);
        sort($sessionIds);
        
        return sprintf(
            'ai_recommendations_%d_%s',
            $user->getId(),
            md5(implode('_', $sessionIds))
        );
    }
}
