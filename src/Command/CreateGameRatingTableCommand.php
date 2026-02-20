<?php

namespace App\Command;

use Doctrine\DBAL\Connection;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

#[AsCommand(
    name: 'app:create-game-rating-table',
    description: 'Create the game_rating table'
)]
class CreateGameRatingTableCommand extends Command
{
    public function __construct(
        private Connection $connection
    ) {
        parent::__construct();
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);

        $io->title('Creating game_rating Table');

        $sql = "CREATE TABLE IF NOT EXISTS game_rating (
            id INT AUTO_INCREMENT PRIMARY KEY,
            game_id INT NOT NULL,
            user_id INT NOT NULL,
            rating INT NOT NULL CHECK (rating >= 1 AND rating <= 5),
            created_at DATETIME NOT NULL,
            updated_at DATETIME DEFAULT NULL,
            UNIQUE KEY unique_user_game_rating (game_id, user_id),
            CONSTRAINT fk_rating_game FOREIGN KEY (game_id) REFERENCES game(id) ON DELETE CASCADE,
            CONSTRAINT fk_rating_user FOREIGN KEY (user_id) REFERENCES user(id) ON DELETE CASCADE,
            INDEX idx_game_rating (game_id),
            INDEX idx_user_rating (user_id)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci";

        try {
            $this->connection->executeStatement($sql);
            $io->success('Table "game_rating" created successfully!');
            
            // Verify table exists
            $result = $this->connection->fetchOne("SHOW TABLES LIKE 'game_rating'");
            if ($result) {
                $io->success('✓ Table verified - exists in database');
                
                // Show table structure
                $io->section('Table Structure');
                $columns = $this->connection->fetchAllAssociative("DESCRIBE game_rating");
                $io->table(
                    ['Field', 'Type', 'Null', 'Key', 'Default', 'Extra'],
                    array_map(fn($col) => array_values($col), $columns)
                );
                
                return Command::SUCCESS;
            } else {
                $io->error('✗ Table creation failed - table not found');
                return Command::FAILURE;
            }
        } catch (\Exception $e) {
            $io->error('Error: ' . $e->getMessage());
            return Command::FAILURE;
        }
    }
}
