<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20260207140329 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE student_game_progress (id INT AUTO_INCREMENT NOT NULL, times_played INT NOT NULL, times_won INT NOT NULL, total_xpearned INT NOT NULL, total_tokens_earned INT NOT NULL, last_played_at DATETIME DEFAULT NULL, created_at DATETIME NOT NULL, updated_at DATETIME NOT NULL, student_id INT NOT NULL, game_id INT NOT NULL, INDEX IDX_DCD9F5FECB944F1A (student_id), INDEX IDX_DCD9F5FEE48FD905 (game_id), PRIMARY KEY (id)) DEFAULT CHARACTER SET utf8mb4');
        $this->addSql('CREATE TABLE student_reward (id INT AUTO_INCREMENT NOT NULL, earned_at DATETIME NOT NULL, is_viewed TINYINT NOT NULL, student_id INT NOT NULL, reward_id INT NOT NULL, earned_from_game_id INT DEFAULT NULL, INDEX IDX_C0E7AAD3CB944F1A (student_id), INDEX IDX_C0E7AAD3E466ACA1 (reward_id), INDEX IDX_C0E7AAD3FDEA601B (earned_from_game_id), PRIMARY KEY (id)) DEFAULT CHARACTER SET utf8mb4');
        $this->addSql('ALTER TABLE student_game_progress ADD CONSTRAINT FK_DCD9F5FECB944F1A FOREIGN KEY (student_id) REFERENCES student_profile (id)');
        $this->addSql('ALTER TABLE student_game_progress ADD CONSTRAINT FK_DCD9F5FEE48FD905 FOREIGN KEY (game_id) REFERENCES game (id)');
        $this->addSql('ALTER TABLE student_reward ADD CONSTRAINT FK_C0E7AAD3CB944F1A FOREIGN KEY (student_id) REFERENCES student_profile (id)');
        $this->addSql('ALTER TABLE student_reward ADD CONSTRAINT FK_C0E7AAD3E466ACA1 FOREIGN KEY (reward_id) REFERENCES reward (id)');
        $this->addSql('ALTER TABLE student_reward ADD CONSTRAINT FK_C0E7AAD3FDEA601B FOREIGN KEY (earned_from_game_id) REFERENCES game (id)');
        $this->addSql('ALTER TABLE course CHANGE description description VARCHAR(255) DEFAULT NULL');
        $this->addSql('ALTER TABLE libraries CHANGE address address VARCHAR(255) DEFAULT NULL');
        $this->addSql('ALTER TABLE loans CHANGE end_at end_at DATETIME DEFAULT NULL');
        $this->addSql('ALTER TABLE reward CHANGE icon icon VARCHAR(255) DEFAULT NULL');
        $this->addSql('ALTER TABLE student_profile ADD total_xp INT NOT NULL, ADD total_tokens INT NOT NULL, ADD level INT NOT NULL, CHANGE university university VARCHAR(100) DEFAULT NULL, CHANGE major major VARCHAR(100) DEFAULT NULL, CHANGE academic_level academic_level VARCHAR(50) DEFAULT NULL, CHANGE profile_picture profile_picture VARCHAR(255) DEFAULT NULL, CHANGE interests interests JSON DEFAULT NULL');
        $this->addSql('ALTER TABLE study_session CHANGE ended_at ended_at DATETIME DEFAULT NULL');
        $this->addSql('ALTER TABLE tutor_profile CHANGE expertise expertise JSON DEFAULT NULL, CHANGE hourly_rate hourly_rate NUMERIC(10, 2) DEFAULT NULL, CHANGE profile_picture profile_picture VARCHAR(255) DEFAULT NULL');
        $this->addSql('ALTER TABLE user ADD created_at DATETIME NOT NULL, ADD updated_at DATETIME NOT NULL, ADD student_profile_id INT DEFAULT NULL, ADD tutor_profile_id INT DEFAULT NULL');
        $this->addSql('ALTER TABLE user ADD CONSTRAINT FK_8D93D6492125FF59 FOREIGN KEY (student_profile_id) REFERENCES student_profile (id)');
        $this->addSql('ALTER TABLE user ADD CONSTRAINT FK_8D93D649430AF9E FOREIGN KEY (tutor_profile_id) REFERENCES tutor_profile (id)');
        $this->addSql('CREATE UNIQUE INDEX UNIQ_8D93D6492125FF59 ON user (student_profile_id)');
        $this->addSql('CREATE UNIQUE INDEX UNIQ_8D93D649430AF9E ON user (tutor_profile_id)');
        $this->addSql('ALTER TABLE messenger_messages CHANGE delivered_at delivered_at DATETIME DEFAULT NULL');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE student_game_progress DROP FOREIGN KEY FK_DCD9F5FECB944F1A');
        $this->addSql('ALTER TABLE student_game_progress DROP FOREIGN KEY FK_DCD9F5FEE48FD905');
        $this->addSql('ALTER TABLE student_reward DROP FOREIGN KEY FK_C0E7AAD3CB944F1A');
        $this->addSql('ALTER TABLE student_reward DROP FOREIGN KEY FK_C0E7AAD3E466ACA1');
        $this->addSql('ALTER TABLE student_reward DROP FOREIGN KEY FK_C0E7AAD3FDEA601B');
        $this->addSql('DROP TABLE student_game_progress');
        $this->addSql('DROP TABLE student_reward');
        $this->addSql('ALTER TABLE course CHANGE description description VARCHAR(255) DEFAULT \'NULL\'');
        $this->addSql('ALTER TABLE libraries CHANGE address address VARCHAR(255) DEFAULT \'NULL\'');
        $this->addSql('ALTER TABLE loans CHANGE end_at end_at DATETIME DEFAULT \'NULL\'');
        $this->addSql('ALTER TABLE messenger_messages CHANGE delivered_at delivered_at DATETIME DEFAULT \'NULL\'');
        $this->addSql('ALTER TABLE reward CHANGE icon icon VARCHAR(255) DEFAULT \'NULL\'');
        $this->addSql('ALTER TABLE student_profile DROP total_xp, DROP total_tokens, DROP level, CHANGE university university VARCHAR(100) NOT NULL, CHANGE major major VARCHAR(100) DEFAULT \'NULL\', CHANGE academic_level academic_level VARCHAR(50) DEFAULT \'NULL\', CHANGE profile_picture profile_picture VARCHAR(255) DEFAULT \'NULL\', CHANGE interests interests LONGTEXT DEFAULT NULL');
        $this->addSql('ALTER TABLE study_session CHANGE ended_at ended_at DATETIME DEFAULT \'NULL\'');
        $this->addSql('ALTER TABLE tutor_profile CHANGE expertise expertise LONGTEXT DEFAULT NULL, CHANGE hourly_rate hourly_rate NUMERIC(10, 2) DEFAULT \'NULL\', CHANGE profile_picture profile_picture VARCHAR(255) DEFAULT \'NULL\'');
        $this->addSql('ALTER TABLE user DROP FOREIGN KEY FK_8D93D6492125FF59');
        $this->addSql('ALTER TABLE user DROP FOREIGN KEY FK_8D93D649430AF9E');
        $this->addSql('DROP INDEX UNIQ_8D93D6492125FF59 ON user');
        $this->addSql('DROP INDEX UNIQ_8D93D649430AF9E ON user');
        $this->addSql('ALTER TABLE user DROP created_at, DROP updated_at, DROP student_profile_id, DROP tutor_profile_id');
    }
}
