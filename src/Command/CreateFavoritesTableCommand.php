<?php

namespace App\Command;

use Doctrine\DBAL\Connection;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

#[AsCommand(
    name: 'app:create-favorites-table',
    description: 'Create the user_favorite_games table'
)]
class CreateFavoritesTableCommand extends Command
{
    public function __construct(
        private Connection $connection
    ) {
        parent::__construct();
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);

        $io->title('Creating user_favorite_games Table');

        $sql = "CREATE TABLE IF NOT EXISTS user_favorite_games (
            user_id INT NOT NULL,
            game_id INT NOT NULL,
            PRIMARY KEY (user_id, game_id),
            INDEX idx_user (user_id),
            INDEX idx_game (game_id),
            CONSTRAINT fk_favorite_user FOREIGN KEY (user_id) REFERENCES user(id) ON DELETE CASCADE,
            CONSTRAINT fk_favorite_game FOREIGN KEY (game_id) REFERENCES game(id) ON DELETE CASCADE
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci";

        try {
            $this->connection->executeStatement($sql);
            $io->success('Table "user_favorite_games" created successfully!');
            
            // Verify table exists
            $result = $this->connection->fetchOne("SHOW TABLES LIKE 'user_favorite_games'");
            if ($result) {
                $io->success('✓ Table verified - exists in database');
                
                // Show table structure
                $io->section('Table Structure');
                $columns = $this->connection->fetchAllAssociative("DESCRIBE user_favorite_games");
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
