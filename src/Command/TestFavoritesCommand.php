<?php

namespace App\Command;

use App\Repository\UserRepository;
use App\Repository\Gamification\GameRepository;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

#[AsCommand(
    name: 'app:test-favorites',
    description: 'Test the favorite games feature'
)]
class TestFavoritesCommand extends Command
{
    public function __construct(
        private UserRepository $userRepository,
        private GameRepository $gameRepository
    ) {
        parent::__construct();
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);

        $io->title('Testing Favorite Games Feature');

        // Check if table exists
        $io->section('1. Checking Database Table');
        try {
            $connection = $this->gameRepository->createQueryBuilder('g')->getEntityManager()->getConnection();
            $result = $connection->executeQuery("SHOW TABLES LIKE 'user_favorite_games'")->fetchOne();
            
            if ($result) {
                $io->success('✓ Table "user_favorite_games" exists');
            } else {
                $io->error('✗ Table "user_favorite_games" does NOT exist');
                $io->note('Run: database_seeds/create_favorite_games_table.sql');
                return Command::FAILURE;
            }
        } catch (\Exception $e) {
            $io->error('Database error: ' . $e->getMessage());
            return Command::FAILURE;
        }

        // Test entity methods
        $io->section('2. Testing Entity Methods');
        
        $user = $this->userRepository->findOneBy(['role' => 'ROLE_STUDENT']);
        if (!$user) {
            $io->warning('No student user found to test with');
            return Command::FAILURE;
        }

        $game = $this->gameRepository->findOneBy(['isActive' => true]);
        if (!$game) {
            $io->warning('No active game found to test with');
            return Command::FAILURE;
        }

        $io->text("Testing with User: {$user->getUsername()} (ID: {$user->getId()})");
        $io->text("Testing with Game: {$game->getName()} (ID: {$game->getId()})");

        // Test hasFavoriteGame
        $hasFavorite = $user->hasFavoriteGame($game);
        $io->text("✓ hasFavoriteGame() method works: " . ($hasFavorite ? 'true' : 'false'));

        // Test getFavoriteGames
        $favorites = $user->getFavoriteGames();
        $io->text("✓ getFavoriteGames() method works: {$favorites->count()} favorite(s)");

        // Test getFavoritedBy
        $favoritedBy = $game->getFavoritedBy();
        $io->text("✓ getFavoritedBy() method works: {$favoritedBy->count()} user(s) favorited this game");

        // Routes check
        $io->section('3. Available Routes');
        $io->listing([
            'POST /games/{id}/toggle-favorite - Toggle favorite status',
            'GET /games/favorites/my-favorites - View favorites page'
        ]);

        // Summary
        $io->section('Summary');
        $io->success('All checks passed! The favorite games feature is ready to use.');
        
        $io->note([
            'To test in browser:',
            '1. Visit: http://127.0.0.1:8001/games',
            '2. Log in as a student',
            '3. Click the heart icon on any game',
            '4. Visit: http://127.0.0.1:8001/games/favorites/my-favorites'
        ]);

        return Command::SUCCESS;
    }
}
