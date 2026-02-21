<?php

namespace App\Service\game;

class GameTemplateService
{
    /**
     * Get all available game templates
     */
    public function getTemplates(): array
    {
        return [
            'full_games' => [
                'word_scramble' => [
                    'name' => 'Word Scramble',
                    'type' => 'PUZZLE',
                    'category' => 'FULL_GAME',
                    'description' => 'Unscramble words within the time limit',
                    'engine' => 'word_scramble',
                    'difficulty_settings' => [
                        'EASY' => ['time' => 60, 'words' => 5, 'tokens' => 10, 'xp' => 20],
                        'MEDIUM' => ['time' => 45, 'words' => 8, 'tokens' => 20, 'xp' => 40],
                        'HARD' => ['time' => 30, 'words' => 12, 'tokens' => 30, 'xp' => 60],
                    ],
                ],
                'memory_match' => [
                    'name' => 'Memory Match',
                    'type' => 'MEMORY',
                    'category' => 'FULL_GAME',
                    'description' => 'Match pairs of cards in the shortest time',
                    'engine' => 'memory_match',
                    'difficulty_settings' => [
                        'EASY' => ['pairs' => 6, 'time' => 90, 'tokens' => 10, 'xp' => 20],
                        'MEDIUM' => ['pairs' => 10, 'time' => 120, 'tokens' => 20, 'xp' => 40],
                        'HARD' => ['pairs' => 15, 'time' => 150, 'tokens' => 30, 'xp' => 60],
                    ],
                ],
                'quick_quiz' => [
                    'name' => 'Quick Quiz',
                    'type' => 'TRIVIA',
                    'category' => 'FULL_GAME',
                    'description' => 'Answer multiple choice questions correctly',
                    'engine' => 'quick_quiz',
                    'difficulty_settings' => [
                        'EASY' => ['questions' => 5, 'time_per_q' => 15, 'tokens' => 10, 'xp' => 20],
                        'MEDIUM' => ['questions' => 8, 'time_per_q' => 12, 'tokens' => 20, 'xp' => 40],
                        'HARD' => ['questions' => 10, 'time_per_q' => 10, 'tokens' => 30, 'xp' => 60],
                    ],
                ],
                'reaction_clicker' => [
                    'name' => 'Reaction Clicker',
                    'type' => 'ARCADE',
                    'category' => 'FULL_GAME',
                    'description' => 'Click targets before they disappear',
                    'engine' => 'reaction_clicker',
                    'difficulty_settings' => [
                        'EASY' => ['targets' => 10, 'speed' => 2000, 'tokens' => 10, 'xp' => 20],
                        'MEDIUM' => ['targets' => 15, 'speed' => 1500, 'tokens' => 20, 'xp' => 40],
                        'HARD' => ['targets' => 20, 'speed' => 1000, 'tokens' => 30, 'xp' => 60],
                    ],
                ],
            ],
            'mini_games' => [
                'breathing_exercise' => [
                    'name' => 'Breathing Exercise',
                    'type' => 'ARCADE',
                    'category' => 'MINI_GAME',
                    'description' => 'Calm breathing exercise for relaxation',
                    'engine' => 'breathing',
                    'energy_points' => 5,
                ],
                'quick_stretch' => [
                    'name' => 'Quick Stretch',
                    'type' => 'ARCADE',
                    'category' => 'MINI_GAME',
                    'description' => 'Simple stretching exercises',
                    'engine' => 'stretch',
                    'energy_points' => 5,
                ],
                'eye_rest' => [
                    'name' => 'Eye Rest - 20-20-20',
                    'type' => 'ARCADE',
                    'category' => 'MINI_GAME',
                    'description' => 'Look away for 20 seconds every 20 minutes',
                    'engine' => 'eye_rest',
                    'energy_points' => 3,
                ],
                'hydration_break' => [
                    'name' => 'Hydration Break',
                    'type' => 'ARCADE',
                    'category' => 'MINI_GAME',
                    'description' => 'Take a water break',
                    'engine' => 'hydration',
                    'energy_points' => 3,
                ],
            ],
        ];
    }

    /**
     * Get template by key
     */
    public function getTemplate(string $category, string $key): ?array
    {
        $templates = $this->getTemplates();
        return $templates[$category][$key] ?? null;
    }

    /**
     * Get template configuration for game creation
     */
    public function getTemplateConfig(string $category, string $key, string $difficulty = 'MEDIUM'): array
    {
        $template = $this->getTemplate($category, $key);
        
        if (!$template) {
            return [];
        }

        $config = [
            'name' => $template['name'],
            'description' => $template['description'],
            'type' => $template['type'],
            'category' => $template['category'],
            'engine' => $template['engine'],
        ];

        if ($template['category'] === 'FULL_GAME') {
            $settings = $template['difficulty_settings'][$difficulty] ?? $template['difficulty_settings']['MEDIUM'];
            $config['difficulty'] = $difficulty;
            $config['rewardTokens'] = $settings['tokens'];
            $config['rewardXP'] = $settings['xp'];
            $config['tokenCost'] = 0;
            $config['settings'] = $settings;
        } else {
            $config['energyPoints'] = $template['energy_points'];
            $config['tokenCost'] = 0;
            $config['rewardTokens'] = 0;
            $config['rewardXP'] = 0;
        }

        return $config;
    }
}
