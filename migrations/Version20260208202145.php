<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20260208202145 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE book_library (book_id INT NOT NULL, library_id INT NOT NULL, INDEX IDX_32A0B02A16A2B381 (book_id), INDEX IDX_32A0B02AFE2541D7 (library_id), PRIMARY KEY (book_id, library_id)) DEFAULT CHARACTER SET utf8mb4');
        $this->addSql('ALTER TABLE book_library ADD CONSTRAINT FK_32A0B02A16A2B381 FOREIGN KEY (book_id) REFERENCES books (id)');
        $this->addSql('ALTER TABLE book_library ADD CONSTRAINT FK_32A0B02AFE2541D7 FOREIGN KEY (library_id) REFERENCES libraries (id)');
        $this->addSql('ALTER TABLE student_game_progress DROP FOREIGN KEY `FK_DCD9F5FECB944F1A`');
        $this->addSql('ALTER TABLE student_game_progress DROP FOREIGN KEY `FK_DCD9F5FEE48FD905`');
        $this->addSql('ALTER TABLE student_reward DROP FOREIGN KEY `FK_C0E7AAD3CB944F1A`');
        $this->addSql('ALTER TABLE student_reward DROP FOREIGN KEY `FK_C0E7AAD3E466ACA1`');
        $this->addSql('ALTER TABLE student_reward DROP FOREIGN KEY `FK_C0E7AAD3FDEA601B`');
        $this->addSql('DROP TABLE student_game_progress');
        $this->addSql('DROP TABLE student_reward');
        $this->addSql('ALTER TABLE books ADD price NUMERIC(10, 2) DEFAULT NULL, ADD cover_image VARCHAR(255) DEFAULT NULL, ADD author VARCHAR(255) DEFAULT NULL, ADD isbn VARCHAR(20) DEFAULT NULL, ADD published_at DATETIME DEFAULT NULL, ADD uploader_id INT DEFAULT NULL, ADD created_at DATETIME NOT NULL, ADD updated_at DATETIME DEFAULT NULL');
        $this->addSql('ALTER TABLE course ADD created_by_id INT DEFAULT NULL, CHANGE description description VARCHAR(255) DEFAULT NULL');
        $this->addSql('ALTER TABLE course ADD CONSTRAINT FK_169E6FB9B03A8386 FOREIGN KEY (created_by_id) REFERENCES user (id)');
        $this->addSql('CREATE INDEX IDX_169E6FB9B03A8386 ON course (created_by_id)');
        $this->addSql('ALTER TABLE game ADD user_id INT DEFAULT NULL');
        $this->addSql('ALTER TABLE game ADD CONSTRAINT FK_232B318CA76ED395 FOREIGN KEY (user_id) REFERENCES user (id)');
        $this->addSql('CREATE INDEX IDX_232B318CA76ED395 ON game (user_id)');
        $this->addSql('ALTER TABLE libraries CHANGE address address VARCHAR(255) DEFAULT NULL');
        $this->addSql('ALTER TABLE loans CHANGE end_at end_at DATETIME DEFAULT NULL');
        $this->addSql('ALTER TABLE question ADD user_id INT DEFAULT NULL');
        $this->addSql('ALTER TABLE question ADD CONSTRAINT FK_B6F7494EA76ED395 FOREIGN KEY (user_id) REFERENCES user (id)');
        $this->addSql('CREATE INDEX IDX_B6F7494EA76ED395 ON question (user_id)');
        $this->addSql('ALTER TABLE reward CHANGE icon icon VARCHAR(255) DEFAULT NULL');
        $this->addSql('ALTER TABLE student_profile CHANGE university university VARCHAR(100) DEFAULT NULL, CHANGE major major VARCHAR(100) DEFAULT NULL, CHANGE academic_level academic_level VARCHAR(50) DEFAULT NULL, CHANGE profile_picture profile_picture VARCHAR(255) DEFAULT NULL, CHANGE interests interests JSON DEFAULT NULL');
        $this->addSql('ALTER TABLE study_session ADD actual_duration INT DEFAULT NULL, ADD completed_at DATETIME DEFAULT NULL, CHANGE ended_at ended_at DATETIME DEFAULT NULL');
        $this->addSql('ALTER TABLE tutor_profile CHANGE expertise expertise JSON DEFAULT NULL, CHANGE hourly_rate hourly_rate NUMERIC(10, 2) DEFAULT NULL, CHANGE profile_picture profile_picture VARCHAR(255) DEFAULT NULL');
        $this->addSql('CREATE UNIQUE INDEX UNIQ_8D93D649E7927C74 ON user (email)');
        $this->addSql('CREATE UNIQUE INDEX UNIQ_8D93D649F85E0677 ON user (username)');
        $this->addSql('ALTER TABLE messenger_messages CHANGE delivered_at delivered_at DATETIME DEFAULT NULL');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE student_game_progress (id INT AUTO_INCREMENT NOT NULL, times_played INT NOT NULL, times_won INT NOT NULL, total_xpearned INT NOT NULL, total_tokens_earned INT NOT NULL, last_played_at DATETIME DEFAULT \'NULL\', created_at DATETIME NOT NULL, updated_at DATETIME NOT NULL, student_id INT NOT NULL, game_id INT NOT NULL, INDEX IDX_DCD9F5FECB944F1A (student_id), INDEX IDX_DCD9F5FEE48FD905 (game_id), PRIMARY KEY (id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_general_ci` ENGINE = InnoDB COMMENT = \'\' ');
        $this->addSql('CREATE TABLE student_reward (id INT AUTO_INCREMENT NOT NULL, earned_at DATETIME NOT NULL, is_viewed TINYINT NOT NULL, student_id INT NOT NULL, reward_id INT NOT NULL, earned_from_game_id INT DEFAULT NULL, INDEX IDX_C0E7AAD3E466ACA1 (reward_id), INDEX IDX_C0E7AAD3FDEA601B (earned_from_game_id), INDEX IDX_C0E7AAD3CB944F1A (student_id), PRIMARY KEY (id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_general_ci` ENGINE = InnoDB COMMENT = \'\' ');
        $this->addSql('ALTER TABLE student_game_progress ADD CONSTRAINT `FK_DCD9F5FECB944F1A` FOREIGN KEY (student_id) REFERENCES student_profile (id)');
        $this->addSql('ALTER TABLE student_game_progress ADD CONSTRAINT `FK_DCD9F5FEE48FD905` FOREIGN KEY (game_id) REFERENCES game (id)');
        $this->addSql('ALTER TABLE student_reward ADD CONSTRAINT `FK_C0E7AAD3CB944F1A` FOREIGN KEY (student_id) REFERENCES student_profile (id)');
        $this->addSql('ALTER TABLE student_reward ADD CONSTRAINT `FK_C0E7AAD3E466ACA1` FOREIGN KEY (reward_id) REFERENCES reward (id)');
        $this->addSql('ALTER TABLE student_reward ADD CONSTRAINT `FK_C0E7AAD3FDEA601B` FOREIGN KEY (earned_from_game_id) REFERENCES game (id)');
        $this->addSql('ALTER TABLE book_library DROP FOREIGN KEY FK_32A0B02A16A2B381');
        $this->addSql('ALTER TABLE book_library DROP FOREIGN KEY FK_32A0B02AFE2541D7');
        $this->addSql('DROP TABLE book_library');
        $this->addSql('ALTER TABLE books DROP price, DROP cover_image, DROP author, DROP isbn, DROP published_at, DROP uploader_id, DROP created_at, DROP updated_at');
        $this->addSql('ALTER TABLE course DROP FOREIGN KEY FK_169E6FB9B03A8386');
        $this->addSql('DROP INDEX IDX_169E6FB9B03A8386 ON course');
        $this->addSql('ALTER TABLE course DROP created_by_id, CHANGE description description VARCHAR(255) DEFAULT \'NULL\'');
        $this->addSql('ALTER TABLE game DROP FOREIGN KEY FK_232B318CA76ED395');
        $this->addSql('DROP INDEX IDX_232B318CA76ED395 ON game');
        $this->addSql('ALTER TABLE game DROP user_id');
        $this->addSql('ALTER TABLE libraries CHANGE address address VARCHAR(255) DEFAULT \'NULL\'');
        $this->addSql('ALTER TABLE loans CHANGE end_at end_at DATETIME DEFAULT \'NULL\'');
        $this->addSql('ALTER TABLE messenger_messages CHANGE delivered_at delivered_at DATETIME DEFAULT \'NULL\'');
        $this->addSql('ALTER TABLE question DROP FOREIGN KEY FK_B6F7494EA76ED395');
        $this->addSql('DROP INDEX IDX_B6F7494EA76ED395 ON question');
        $this->addSql('ALTER TABLE question DROP user_id');
        $this->addSql('ALTER TABLE reward CHANGE icon icon VARCHAR(255) DEFAULT \'NULL\'');
        $this->addSql('ALTER TABLE student_profile CHANGE university university VARCHAR(100) DEFAULT \'NULL\', CHANGE major major VARCHAR(100) DEFAULT \'NULL\', CHANGE academic_level academic_level VARCHAR(50) DEFAULT \'NULL\', CHANGE profile_picture profile_picture VARCHAR(255) DEFAULT \'NULL\', CHANGE interests interests LONGTEXT DEFAULT NULL COLLATE `utf8mb4_bin`');
        $this->addSql('ALTER TABLE study_session DROP actual_duration, DROP completed_at, CHANGE ended_at ended_at DATETIME DEFAULT \'NULL\'');
        $this->addSql('ALTER TABLE tutor_profile CHANGE expertise expertise LONGTEXT DEFAULT NULL COLLATE `utf8mb4_bin`, CHANGE hourly_rate hourly_rate NUMERIC(10, 2) DEFAULT \'NULL\', CHANGE profile_picture profile_picture VARCHAR(255) DEFAULT \'NULL\'');
        $this->addSql('DROP INDEX UNIQ_8D93D649E7927C74 ON user');
        $this->addSql('DROP INDEX UNIQ_8D93D649F85E0677 ON user');
    }
}
