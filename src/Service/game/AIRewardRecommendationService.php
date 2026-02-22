<?php

namespace App\Service\game;

use App\Entity\users\StudentProfile;
use App\Repository\Gamification\RewardRepository;
use App\Repository\Gamification\GameRepository;
use Symfony\Contracts\HttpClient\HttpClientInterface;
use Psr\Log\LoggerInterface;

class AIRewardRecommendationService
{
    private const API_URL = 'https://router.huggingface.co/novita/v3/openai/chat/completions';
    
    public function __construct(
        private HttpClientInterface $httpClient,
        private LoggerInterface $logger,
        private string $huggingFaceApiKey,
        private RewardRepository $rewardRepository,
        private GameRepository $gameRepository
    ) {
    }

    /**
     * Generate personalized reward recommendation for a student
     */
    public function generateRecommendation(StudentProfile $student): array
    {
        if (empty($this->huggingFaceApiKey)) {
            return [
                'success' => false,
                'message' => 'AI service is not configured.'
            ];
        }

        try {
            // Collect student data
            $studentData = $this->collectStudentData($student);
            
            // Build prompt
            $prompt = $this->buildRecommendationPrompt($studentData);
            
            // Call AI API
            $response = $this->httpClient->request('POST', self::API_URL, [
                'headers' => [
                    'Authorization' => 'Bearer ' . $this->huggingFaceApiKey,
                    'Content-Type' => 'application/json',
                ],
                'json' => [
                    'model' => 'qwen/qwen2.5-7b-instruct',
                    'messages' => [
                        ['role' => 'user', 'content' => $prompt]
                    ],
                    'max_tokens' => 150,
                    'temperature' => 0.3,
                ],
                'timeout' => 15,
            ]);

            if ($response->getStatusCode() !== 200) {
                throw new \Exception('AI API returned status: ' . $response->getStatusCode());
            }

            $data = $response->toArray();
            $message = $data['choices'][0]['message']['content'] ?? '';
            
            if (empty($message)) {
                throw new \Exception('No response from AI');
            }

            return [
                'success' => true,
                'message' => trim($message),
                'studentData' => $studentData
            ];

        } catch (\Exception $e) {
            $this->logger->error('AI Reward Recommendation failed', [
                'error' => $e->getMessage(),
                'student_id' => $student->getId()
            ]);
            
            return [
                'success' => false,
                'message' => 'Unable to generate recommendation at this time. Please try again later.'
            ];
        }
    }

    /**
     * Generate response to student question
     */
    public function generateChatResponse(StudentProfile $student, string $question): array
    {
        if (empty($this->huggingFaceApiKey)) {
            return [
                'success' => false,
                'message' => 'AI service is not configured.'
            ];
        }

        try {
            $studentData = $this->collectStudentData($student);
            $studentName = $student->getFirstName() ?? 'there';
            $prompt = $this->buildChatPrompt($studentData, $question, $studentName);
            
            $response = $this->httpClient->request('POST', self::API_URL, [
                'headers' => [
                    'Authorization' => 'Bearer ' . $this->huggingFaceApiKey,
                    'Content-Type' => 'application/json',
                ],
                'json' => [
                    'model' => 'qwen/qwen2.5-7b-instruct',
                    'messages' => [
                        ['role' => 'user', 'content' => $prompt]
                    ],
                    'max_tokens' => 150,
                    'temperature' => 0.3,
                ],
                'timeout' => 20,
            ]);

            if ($response->getStatusCode() !== 200) {
                $errorContent = $response->getContent(false);
                $this->logger->error('AI API returned non-200 status', [
                    'status' => $response->getStatusCode(),
                    'response' => $errorContent
                ]);
                
                // Try to parse error message
                $errorData = json_decode($errorContent, true);
                $errorMsg = $errorData['error'] ?? 'API returned status: ' . $response->getStatusCode();
                
                if ($response->getStatusCode() === 401) {
                    throw new \Exception('Hugging Face API key is invalid or expired. Please contact administrator.');
                }
                
                throw new \Exception('AI service error: ' . $errorMsg);
            }

            $data = $response->toArray();
            $message = $data['choices'][0]['message']['content'] ?? '';
            
            if (empty($message)) {
                $this->logger->error('Empty AI response', ['data' => $data]);
                throw new \Exception('No response from AI');
            }
            
            // Clean up response
            $message = $this->cleanResponse($message);

            return [
                'success' => true,
                'message' => trim($message)
            ];

        } catch (\Exception $e) {
            $this->logger->error('AI Chat Response failed', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
                'student_id' => $student->getId(),
                'question' => $question
            ]);
            
            return [
                'success' => false,
                'message' => 'Sorry, I couldn\'t process your question. Please try again.'
            ];
        }
    }

    /**
     * Collect student data for AI context
     */
    private function collectStudentData(StudentProfile $student): array
    {
        // Get all rewards
        $allRewards = $this->rewardRepository->findBy(['isActive' => true]);
        
        // Get student's unlocked rewards
        $unlockedRewards = $student->getEarnedRewards()->toArray();
        $unlockedIds = array_map(fn($r) => $r->getId(), $unlockedRewards);
        
        // Get available (not yet unlocked) rewards
        $availableRewards = array_filter($allRewards, fn($r) => !in_array($r->getId(), $unlockedIds));
        
        // Get game statistics
        $gameStats = $this->getGameStatistics();
        
        return [
            'level' => $student->getLevel(),
            'xp' => $student->getTotalXP(),
            'tokens' => $student->getTotalTokens(),
            'unlockedCount' => count($unlockedRewards),
            'unlockedRewards' => array_map(fn($r) => [
                'name' => $r->getName(),
                'type' => $r->getType()
            ], array_slice($unlockedRewards, 0, 5)), // Limit to 5 for context
            'availableRewards' => array_map(fn($r) => [
                'name' => $r->getName(),
                'type' => $r->getType(),
                'requirement' => $r->getRequirement(),
                'requiredLevel' => $r->getRequiredLevel(), // Add required level for milestones
                'value' => $r->getValue()
            ], array_slice($availableRewards, 0, 15)), // Increased to 15 for better context
            'gameStats' => $gameStats
        ];
    }

    /**
     * Get game statistics from database
     */
    private function getGameStatistics(): array
    {
        $games = $this->gameRepository->findBy(['isActive' => true]);
        
        $stats = [
            'total' => count($games),
            'byType' => [],
            'byDifficulty' => [],
            'byCategory' => [],
            'allGames' => []
        ];
        
        foreach ($games as $game) {
            // Count by type
            $type = $game->getType();
            $stats['byType'][$type] = ($stats['byType'][$type] ?? 0) + 1;
            
            // Count by difficulty
            $difficulty = $game->getDifficulty();
            if ($difficulty) {
                $stats['byDifficulty'][$difficulty] = ($stats['byDifficulty'][$difficulty] ?? 0) + 1;
            }
            
            // Count by category
            $category = $game->getCategory();
            $stats['byCategory'][$category] = ($stats['byCategory'][$category] ?? 0) + 1;
            
            // Collect ALL games with full details
            $stats['allGames'][] = [
                'name' => $game->getName(),
                'type' => $type,
                'difficulty' => $difficulty,
                'category' => $category,
                'tokenCost' => $game->getTokenCost(),
                'rewardXP' => $game->getRewardXP(),
                'rewardTokens' => $game->getRewardTokens()
            ];
        }
        
        return $stats;
    }

    /**
     * Build prompt for reward recommendation
     */
    private function buildRecommendationPrompt(array $studentData): string
    {
        $unlockedList = empty($studentData['unlockedRewards']) 
            ? 'None yet' 
            : implode(', ', array_map(fn($r) => "{$r['name']} ({$r['type']})", $studentData['unlockedRewards']));
        
        // Build detailed available rewards with full context
        $availableList = empty($studentData['availableRewards'])
            ? 'None available'
            : implode("\n", array_map(function($r) use ($studentData) {
                $valueInfo = '';
                if ($r['type'] === 'BONUS_TOKENS' || $r['type'] === 'TOKEN') {
                    $valueInfo = " → Gives {$r['value']} tokens";
                } elseif ($r['type'] === 'BONUS_XP') {
                    $valueInfo = " → Gives {$r['value']} XP";
                } elseif ($r['type'] === 'LEVEL_MILESTONE' && $r['requiredLevel']) {
                    $valueInfo = " → Reach Level {$r['requiredLevel']} (gives {$r['value']} tokens)";
                }
                
                $reqText = $r['requirement'] ?: 'See level requirement';
                return "  • '{$r['name']}' ({$r['type']}): {$reqText}{$valueInfo}";
            }, array_slice($studentData['availableRewards'], 0, 10)));

        return <<<PROMPT
You are a gaming coach. Recommend ONE achievable reward based on EXACT data below.

=== STUDENT CURRENT STATUS ===
Level: {$studentData['level']} | XP: {$studentData['xp']} | Tokens: {$studentData['tokens']}
Already Unlocked: {$unlockedList}

=== AVAILABLE REWARDS (NOT YET UNLOCKED) ===
{$availableList}

REWARD TYPE RULES:
1. LEVEL_MILESTONE: Requires reaching a specific level (e.g., "Reach Level 15"). Student is currently Level {$studentData['level']}.
2. BONUS_TOKENS/BONUS_XP: Requires XP amount (e.g., "Earn 5000 XP"). Student currently has {$studentData['xp']} XP.
3. ACHIEVEMENT: Requires completing specific tasks (e.g., "Complete 10 games").

TASK: Recommend the MOST ACHIEVABLE reward from the list above.

CRITICAL CALCULATION RULES:
- For LEVEL_MILESTONE: Calculate levels needed (e.g., Level 12 → Level 15 = need 3 more levels)
- For XP-based: Calculate XP gap (e.g., has 4080 XP, needs 5000 XP = need 920 more XP)
- Choose reward with SMALLEST gap

FORMAT:
- Level milestone: "Level {level}, {xp} XP. Get '{reward name}' ({tokens} tokens) - need {X} more levels (reach Level {target})."
- XP-based: "Level {level}, {xp} XP. Get '{reward name}' ({tokens} tokens) - need {X} more XP."
- Achievement: "Level {level}, {xp} XP. Get '{reward name}' - {requirement}."

EXAMPLES:
- Student Level 12, 4080 XP. Next milestone "Adept Achievement" at Level 15: "Level 12, 4080 XP. Get 'Adept Achievement' (150 tokens) - need 3 more levels (reach Level 15)."
- Student Level 12, 4080 XP. "Experience Legend" needs 5000 XP: "Level 12, 4080 XP. Get 'Experience Legend' (1000 tokens) - need 920 more XP."

YOUR RECOMMENDATION (1-2 sentences):
PROMPT;
    }

    /**
     * Build prompt for chat response
     */
    private function buildChatPrompt(array $studentData, string $question, string $studentName): string
    {
        // Build detailed rewards list
        $availableRewardsList = empty($studentData['availableRewards'])
            ? 'None available'
            : implode("\n", array_map(fn($r) => 
                "  • {$r['name']} ({$r['type']}): {$r['requirement']}" . 
                ($r['value'] > 0 ? " → Reward: {$r['value']} tokens" : ""), 
                $studentData['availableRewards']
            ));

        // Build unlocked rewards list
        $unlockedRewardsList = empty($studentData['unlockedRewards'])
            ? 'None yet'
            : implode(', ', array_map(fn($r) => "{$r['name']} ({$r['type']})", $studentData['unlockedRewards']));

        // Build game statistics summary
        $gameStats = $studentData['gameStats'];
        $gamesSummary = "Total: {$gameStats['total']} games\n";
        
        // Games by type
        $gamesSummary .= "By Type: ";
        $typeCounts = [];
        foreach ($gameStats['byType'] as $type => $count) {
            $typeCounts[] = "{$count} {$type}";
        }
        $gamesSummary .= implode(", ", $typeCounts) . "\n";
        
        // Games by difficulty
        if (!empty($gameStats['byDifficulty'])) {
            $gamesSummary .= "By Difficulty: ";
            $diffCounts = [];
            foreach ($gameStats['byDifficulty'] as $difficulty => $count) {
                $diffCounts[] = "{$count} {$difficulty}";
            }
            $gamesSummary .= implode(", ", $diffCounts);
        }
        
        // ALL games list with full details
        $allGamesList = "\nCOMPLETE GAMES LIST:\n";
        foreach ($gameStats['allGames'] as $game) {
            $cost = $game['tokenCost'] > 0 ? "{$game['tokenCost']} tokens" : "FREE";
            $rewards = [];
            if ($game['rewardXP']) $rewards[] = "{$game['rewardXP']} XP";
            if ($game['rewardTokens']) $rewards[] = "{$game['rewardTokens']} tokens";
            $rewardStr = !empty($rewards) ? " → " . implode(", ", $rewards) : "";
            $allGamesList .= "• '{$game['name']}' - {$game['type']}, {$game['difficulty']}, {$game['category']}, Cost: {$cost}{$rewardStr}\n";
        }

        return <<<PROMPT
You are NOVA's gaming assistant. Answer using ONLY the exact data below. Include actual game/reward names when relevant.

=== STUDENT STATUS ===
Name: {$studentName}
Level: {$studentData['level']} | XP: {$studentData['xp']} | Tokens: {$studentData['tokens']}
Unlocked Rewards ({$studentData['unlockedCount']}): {$unlockedRewardsList}

=== AVAILABLE REWARDS TO UNLOCK ===
{$availableRewardsList}

=== ACTUAL GAMES DATA ===
{$gamesSummary}
{$allGamesList}

PLATFORM KNOWLEDGE:
• GAME TYPES: TRIVIA (quiz), PUZZLE (word scramble), MEMORY (card match), ARCADE (reaction/typing)
• DIFFICULTY: EASY (5 questions, base XP), MEDIUM (7 questions, +30% XP), HARD (8 questions, +60% XP)
• CATEGORY: FULL_GAME (costs tokens, gives XP+tokens), MINI_GAME (free, restores energy)
• REWARD TYPES: BADGE (achievement), TOKEN (bonus tokens), ACHIEVEMENT (special accomplishment), BONUS_XP (extra XP), LEVEL_MILESTONE (level rewards)

HOW TO EARN:
• XP: Complete games (60%+ to pass), play HARD difficulty, get perfect scores
• TOKENS: Complete FULL_GAME category, unlock TOKEN rewards, perfect scores

QUESTION: {$question}

ANSWER RULES:
1. Address the student as {$studentName} when appropriate
2. Use relevant emojis (🎮 🏆 ⭐ 💎 🎯 💪 🔥 ✨) to make responses engaging
3. Answer in 2-3 sentences max (NO numbered lists)
4. Include exact numbers (XP, tokens, costs)
5. Say "HARD difficulty" not "HARD category"
6. When listing games, mention actual names like 'GAMES QUIZ', 'Word Scramble', etc.
7. Be concise and direct - get straight to the point
8. Maximum 60 words total

EXAMPLE GOOD RESPONSES:
Q: "Tell me about my progress"
A: "Hey {$studentName}! 🎮 You're at Level {$studentData['level']} with {$studentData['xp']} XP and {$studentData['tokens']} tokens. You've unlocked {$studentData['unlockedCount']} rewards so far - keep it up! 🌟"

Q: "How do I earn more XP?"
A: "{$studentName}, play HARD difficulty games for 60% more XP! 🔥 Try 'GAMES QUIZ' or 'Word Scramble' and aim for perfect scores. Each completed game gives you XP based on your performance ⭐"

ANSWER:
PROMPT;
    }

    /**
     * Clean AI response by removing markdown and limiting length
     */
    private function cleanResponse(string $response): string
    {
        // Remove markdown headers (###, ##, #)
        $response = preg_replace('/^#{1,6}\s+/m', '', $response);
        
        // Remove bold/italic markdown (**, *, __)
        $response = preg_replace('/(\*\*|__)(.*?)\1/', '$2', $response);
        $response = preg_replace('/(\*|_)(.*?)\1/', '$2', $response);
        
        // Remove numbered lists (1., 2., etc.)
        $response = preg_replace('/^\d+\.\s+/m', '', $response);
        
        // Remove bullet points (-, *, •)
        $response = preg_replace('/^[\-\*•]\s+/m', '', $response);
        
        // Split into sentences
        $sentences = preg_split('/(?<=[.!?])\s+/', trim($response), -1, PREG_SPLIT_NO_EMPTY);
        
        // Keep only first 3 sentences
        if (count($sentences) > 3) {
            $sentences = array_slice($sentences, 0, 3);
        }
        
        // Join and clean up extra whitespace
        $result = implode(' ', $sentences);
        $result = preg_replace('/\s+/', ' ', $result);
        
        return trim($result);
    }
}
