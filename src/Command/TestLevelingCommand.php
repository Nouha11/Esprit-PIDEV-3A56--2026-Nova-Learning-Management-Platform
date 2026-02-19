<?php

namespace App\Command;

use App\Service\game\LevelCalculatorService;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

#[AsCommand(
    name: 'app:test-leveling',
    description: 'Test the leveling algorithm with different XP values',
)]
class TestLevelingCommand extends Command
{
    public function __construct(
        private LevelCalculatorService $levelCalculator
    ) {
        parent::__construct();
    }

    protected function configure(): void
    {
        $this
            ->addArgument('xp', InputArgument::OPTIONAL, 'XP value to test (leave empty to test all thresholds)')
            ->setHelp('This command tests the leveling algorithm with different XP values.');
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);
        $xp = $input->getArgument('xp');

        if ($xp !== null) {
            // Test specific XP value
            $this->testSingleXP($io, (int)$xp);
        } else {
            // Test all level thresholds
            $this->testAllLevels($io);
        }

        return Command::SUCCESS;
    }

    private function testSingleXP(SymfonyStyle $io, int $xp): void
    {
        $io->title("Testing XP: $xp");
        
        $result = $this->levelCalculator->calculateLevel($xp);
        
        $io->table(
            ['Property', 'Value'],
            [
                ['Level', $result['level']],
                ['Level Name', $result['name']],
                ['Progress to Next Level', $result['progress'] . '%'],
                ['Current Level Min XP', $result['currentLevelMin']],
                ['Next Level Min XP', $result['nextLevelMin']],
                ['XP in Current Level', $result['xpInCurrentLevel']],
                ['XP Needed for Next Level', $result['xpNeededForNextLevel']],
                ['Badge Color', $this->levelCalculator->getLevelBadgeColor($result['level'])],
                ['Icon', $this->levelCalculator->getLevelIcon($result['level'])],
            ]
        );
    }

    private function testAllLevels(SymfonyStyle $io): void
    {
        $io->title('Leveling Algorithm Test');
        
        // Test values at boundaries and middle points
        $testValues = [
            0, 50, 99,           // Level 1
            100, 175, 249,       // Level 2
            250, 375, 499,       // Level 3
            500, 750, 999,       // Level 4
            1000, 1500, 2000,    // Level 5
        ];

        $tableData = [];
        foreach ($testValues as $xp) {
            $result = $this->levelCalculator->calculateLevel($xp);
            $tableData[] = [
                $xp,
                $result['level'],
                $result['name'],
                $result['progress'] . '%',
                $result['xpNeededForNextLevel'],
            ];
        }

        $io->table(
            ['XP', 'Level', 'Name', 'Progress', 'XP to Next'],
            $tableData
        );

        // Show level thresholds
        $io->section('Level Thresholds');
        $thresholds = $this->levelCalculator->getLevelThresholds();
        $thresholdData = [];
        foreach ($thresholds as $level => $data) {
            $thresholdData[] = [
                $level,
                $data['name'],
                $data['min'] . ' - ' . ($data['max'] === PHP_INT_MAX ? '∞' : $data['max']),
                $this->levelCalculator->getLevelBadgeColor($level),
                $this->levelCalculator->getLevelIcon($level),
            ];
        }

        $io->table(
            ['Level', 'Name', 'XP Range', 'Badge Color', 'Icon'],
            $thresholdData
        );

        $io->success('Leveling algorithm test completed!');
    }
}
