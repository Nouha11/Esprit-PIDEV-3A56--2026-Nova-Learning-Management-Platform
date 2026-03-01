<?php

namespace App\Command;

use App\Repository\Gamification\GameRepository;
use App\Repository\StudentProfileRepository;
use App\Service\game\TokenService;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

#[AsCommand(
    name: 'app:check-token-system',
    description: 'Check if the token system is ready for testing',
)]
class CheckTokenSystemCommand extends Command
{
    public function __construct(
        private StudentProfileRepository $studentRepository,
        private GameRepository $gameRepository,
        private TokenService $tokenService
    ) {
        parent::__construct();
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);
        
        $io->title('Token System Readiness Check');

        // Check 1: Students exist
        $students = $this->studentRepository->findAll();
        if (empty($students)) {
            $io->error('No students found! Create at least one student account.');
            return Command::FAILURE;
        }
        $io->success(sprintf('✓ Found %d student(s)', count($students)));

        // Check 2: Games exist
        $games = $this->gameRepository->findAll();
        if (empty($games)) {
            $io->error('No games found! Create at least one game.');
            return Command::FAILURE;
        }
        $io->success(sprintf('✓ Found %d game(s)', count($games)));

        // Check 3: Show student balances
        $io->section('Student Token Balances');
        $studentData = [];
        foreach ($students as $student) {
            $studentData[] = [
                $student->getId(),
                $student->getFirstName() . ' ' . $student->getLastName(),
                $student->getTotalTokens(),
            ];
        }
        $io->table(['ID', 'Name', 'Tokens'], $studentData);

        // Check 4: Show game costs
        $io->section('Game Token Costs');
        $gameData = [];
        foreach ($games as $game) {
            $isFree = $this->tokenService->isFreeGame($game);
            $gameData[] = [
                $game->getId(),
                $game->getName(),
                $game->getTokenCost(),
                $game->isActive() ? '✓' : '✗',
                $isFree ? 'FREE' : 'PAID',
            ];
        }
        $io->table(['ID', 'Name', 'Cost', 'Active', 'Type'], $gameData);

        // Check 5: Test scenarios
        $io->section('Test Scenarios');
        
        // FIXED: Removed redundant empty() checks since the code already returned FAILURE above if these were empty
        $student = $students[0];
        $game = $games[0];
        
        $validation = $this->tokenService->validateTransaction($student, $game);
        
        $io->writeln(sprintf(
            'Student: %s (ID: %d) with %d tokens',
            $student->getFirstName() . ' ' . $student->getLastName(),
            $student->getId(),
            $student->getTotalTokens()
        ));
        $io->writeln(sprintf(
            'Game: %s (ID: %d) costs %d tokens',
            $game->getName(),
            $game->getId(),
            $game->getTokenCost()
        ));
        $io->writeln('');
        
        if ($validation['valid']) {
            $io->success('✓ Student CAN afford this game');
        } else {
            $io->warning(sprintf(
                '✗ Student CANNOT afford this game (needs %d more tokens)',
                $validation['missing']
            ));
        }

        // Check 6: Recommendations
        $io->section('Testing Recommendations');
        
        $hasInsufficientScenario = false;
        $hasSufficientScenario = false;
        $hasFreeGame = false;
        
        foreach ($students as $studentItem) {
            foreach ($games as $gameItem) {
                if ($this->tokenService->isFreeGame($gameItem)) {
                    $hasFreeGame = true;
                }
                if ($this->tokenService->hasEnoughTokens($studentItem, $gameItem)) {
                    $hasSufficientScenario = true;
                }
                if (!$this->tokenService->hasEnoughTokens($studentItem, $gameItem) && !$this->tokenService->isFreeGame($gameItem)) {
                    $hasInsufficientScenario = true;
                }
            }
        }

        if ($hasSufficientScenario) {
            $io->writeln('✓ You have scenarios to test SUFFICIENT tokens');
        } else {
            $io->writeln('✗ Add tokens to students or reduce game costs to test SUFFICIENT tokens');
        }

        if ($hasInsufficientScenario) {
            $io->writeln('✓ You have scenarios to test INSUFFICIENT tokens');
        } else {
            $io->writeln('✗ Reduce student tokens or increase game costs to test INSUFFICIENT tokens');
        }

        if ($hasFreeGame) {
            $io->writeln('✓ You have FREE games to test');
        } else {
            $io->writeln('✗ Set a game cost to 0 to test FREE games');
        }

        // Final recommendations
        $io->section('Quick Setup Commands');
        
        // FIXED: Removed redundant empty() checks
        $studentId = $students[0]->getId();
        $gameId = $games[0]->getId();
        
        $io->writeln('Run these SQL commands to set up test scenarios:');
        $io->writeln('');
        $io->writeln(sprintf('-- Give student %d enough tokens', $studentId));
        $io->writeln(sprintf('UPDATE student_profile SET total_tokens = 50 WHERE id = %d;', $studentId));
        $io->writeln('');
        $io->writeln(sprintf('-- Make game %d affordable (20 tokens)', $gameId));
        $io->writeln(sprintf('UPDATE game SET token_cost = 20 WHERE id = %d;', $gameId));
        $io->writeln('');
        $io->writeln('-- Or use the setup script:');
        $io->writeln('mysql -u root nova_db < database_seeds/setup_token_testing.sql');

        $io->success('Token system check complete!');
        $io->note('See QUICK_START_TOKEN_TESTING.md for detailed testing instructions');

        return Command::SUCCESS;
    }
}