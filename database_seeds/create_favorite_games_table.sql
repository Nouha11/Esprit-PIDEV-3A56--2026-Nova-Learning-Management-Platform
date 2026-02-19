-- Create user_favorite_games junction table for many-to-many relationship
CREATE TABLE IF NOT EXISTS user_favorite_games (
    user_id INT NOT NULL,
    game_id INT NOT NULL,
    PRIMARY KEY (user_id, game_id),
    INDEX IDX_USER (user_id),
    INDEX IDX_GAME (game_id),
    CONSTRAINT FK_FAVORITE_USER FOREIGN KEY (user_id) REFERENCES user (id) ON DELETE CASCADE,
    CONSTRAINT FK_FAVORITE_GAME FOREIGN KEY (game_id) REFERENCES game (id) ON DELETE CASCADE
) DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci ENGINE = InnoDB;
