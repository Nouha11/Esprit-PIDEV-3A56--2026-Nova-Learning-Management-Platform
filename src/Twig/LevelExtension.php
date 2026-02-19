<?php

namespace App\Twig;

use App\Service\game\LevelCalculatorService;
use Twig\Extension\AbstractExtension;
use Twig\TwigFunction;

class LevelExtension extends AbstractExtension
{
    public function __construct(
        private LevelCalculatorService $levelCalculator
    ) {
    }

    public function getFunctions(): array
    {
        return [
            new TwigFunction('calculate_level', [$this->levelCalculator, 'calculateLevel']),
            new TwigFunction('get_level_name', [$this->levelCalculator, 'getLevelName']),
            new TwigFunction('get_level_badge_color', [$this->levelCalculator, 'getLevelBadgeColor']),
            new TwigFunction('get_level_icon', [$this->levelCalculator, 'getLevelIcon']),
        ];
    }
}
