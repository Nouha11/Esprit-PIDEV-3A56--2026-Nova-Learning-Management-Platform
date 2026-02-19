<?php
// Simple script to create the user_favorite_games table

require __DIR__.'/vendor/autoload.php';

use Doctrine\DBAL\DriverManager;

// Load .env file
$dotenv = Dotenv\Dotenv::createImmutable(__DIR__);
$dotenv->load();

// Get database URL from environment
$databaseUrl = $_ENV['DATABASE_URL'];

// Create connection
$connectionParams = ['url' => $databaseUrl];
$conn = DriverManager::getConnection($connectionParams);

// SQL to create table
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
    $conn->executeStatement($sql);
    echo "✓ Table 'user_favorite_games' created successfully!\n";
    
    // Verify table exists
    $result = $conn->fetchOne("SHOW TABLES LIKE 'user_favorite_games'");
    if ($result) {
        echo "✓ Table verified - exists in database\n";
    } else {
        echo "✗ Table creation failed\n";
    }
} catch (\Exception $e) {
    echo "✗ Error: " . $e->getMessage() . "\n";
}
