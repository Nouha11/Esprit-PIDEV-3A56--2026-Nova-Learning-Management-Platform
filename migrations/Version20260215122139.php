<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20260215122139 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE game_rewards (game_id INT NOT NULL, reward_id INT NOT NULL, INDEX IDX_7CE06118E48FD905 (game_id), INDEX IDX_7CE06118E466ACA1 (reward_id), PRIMARY KEY (game_id, reward_id)) DEFAULT CHARACTER SET utf8mb4');
        $this->addSql('CREATE TABLE post_upvoters (post_id INT NOT NULL, user_id INT NOT NULL, INDEX IDX_6A4A12A54B89032C (post_id), INDEX IDX_6A4A12A5A76ED395 (user_id), PRIMARY KEY (post_id, user_id)) DEFAULT CHARACTER SET utf8mb4');
        $this->addSql('CREATE TABLE student_earned_rewards (student_profile_id INT NOT NULL, reward_id INT NOT NULL, INDEX IDX_199824092125FF59 (student_profile_id), INDEX IDX_19982409E466ACA1 (reward_id), PRIMARY KEY (student_profile_id, reward_id)) DEFAULT CHARACTER SET utf8mb4');
        $this->addSql('ALTER TABLE game_rewards ADD CONSTRAINT FK_7CE06118E48FD905 FOREIGN KEY (game_id) REFERENCES game (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE game_rewards ADD CONSTRAINT FK_7CE06118E466ACA1 FOREIGN KEY (reward_id) REFERENCES reward (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE post_upvoters ADD CONSTRAINT FK_6A4A12A54B89032C FOREIGN KEY (post_id) REFERENCES post (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE post_upvoters ADD CONSTRAINT FK_6A4A12A5A76ED395 FOREIGN KEY (user_id) REFERENCES user (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE student_earned_rewards ADD CONSTRAINT FK_199824092125FF59 FOREIGN KEY (student_profile_id) REFERENCES student_profile (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE student_earned_rewards ADD CONSTRAINT FK_19982409E466ACA1 FOREIGN KEY (reward_id) REFERENCES reward (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE books ADD user_id INT DEFAULT NULL, CHANGE price price NUMERIC(10, 2) DEFAULT NULL, CHANGE cover_image cover_image VARCHAR(255) DEFAULT NULL, CHANGE author author VARCHAR(255) DEFAULT NULL, CHANGE isbn isbn VARCHAR(20) DEFAULT NULL, CHANGE published_at published_at DATETIME DEFAULT NULL, CHANGE updated_at updated_at DATETIME DEFAULT NULL');
        $this->addSql('ALTER TABLE books ADD CONSTRAINT FK_4A1B2A92A76ED395 FOREIGN KEY (user_id) REFERENCES user (id)');
        $this->addSql('CREATE INDEX IDX_4A1B2A92A76ED395 ON books (user_id)');
        $this->addSql('ALTER TABLE course CHANGE description description VARCHAR(255) DEFAULT NULL');
        $this->addSql('ALTER TABLE libraries CHANGE address address VARCHAR(255) DEFAULT NULL');
        $this->addSql('ALTER TABLE loans CHANGE end_at end_at DATETIME DEFAULT NULL');
        $this->addSql('ALTER TABLE post ADD is_locked TINYINT NOT NULL');
        $this->addSql('ALTER TABLE reward CHANGE icon icon VARCHAR(255) DEFAULT NULL');
        $this->addSql('ALTER TABLE student_profile CHANGE university university VARCHAR(100) DEFAULT NULL, CHANGE major major VARCHAR(100) DEFAULT NULL, CHANGE academic_level academic_level VARCHAR(50) DEFAULT NULL, CHANGE profile_picture profile_picture VARCHAR(255) DEFAULT NULL, CHANGE interests interests JSON DEFAULT NULL');
        $this->addSql('ALTER TABLE study_session CHANGE ended_at ended_at DATETIME DEFAULT NULL, CHANGE completed_at completed_at DATETIME DEFAULT NULL');
        $this->addSql('ALTER TABLE tutor_profile CHANGE expertise expertise JSON DEFAULT NULL, CHANGE hourly_rate hourly_rate NUMERIC(10, 2) DEFAULT NULL, CHANGE profile_picture profile_picture VARCHAR(255) DEFAULT NULL');
        $this->addSql('ALTER TABLE messenger_messages CHANGE delivered_at delivered_at DATETIME DEFAULT NULL');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE game_rewards DROP FOREIGN KEY FK_7CE06118E48FD905');
        $this->addSql('ALTER TABLE game_rewards DROP FOREIGN KEY FK_7CE06118E466ACA1');
        $this->addSql('ALTER TABLE post_upvoters DROP FOREIGN KEY FK_6A4A12A54B89032C');
        $this->addSql('ALTER TABLE post_upvoters DROP FOREIGN KEY FK_6A4A12A5A76ED395');
        $this->addSql('ALTER TABLE student_earned_rewards DROP FOREIGN KEY FK_199824092125FF59');
        $this->addSql('ALTER TABLE student_earned_rewards DROP FOREIGN KEY FK_19982409E466ACA1');
        $this->addSql('DROP TABLE game_rewards');
        $this->addSql('DROP TABLE post_upvoters');
        $this->addSql('DROP TABLE student_earned_rewards');
        $this->addSql('ALTER TABLE books DROP FOREIGN KEY FK_4A1B2A92A76ED395');
        $this->addSql('DROP INDEX IDX_4A1B2A92A76ED395 ON books');
        $this->addSql('ALTER TABLE books DROP user_id, CHANGE price price NUMERIC(10, 2) DEFAULT \'NULL\', CHANGE cover_image cover_image VARCHAR(255) DEFAULT \'NULL\', CHANGE author author VARCHAR(255) DEFAULT \'NULL\', CHANGE isbn isbn VARCHAR(20) DEFAULT \'NULL\', CHANGE published_at published_at DATETIME DEFAULT \'NULL\', CHANGE updated_at updated_at DATETIME DEFAULT \'NULL\'');
        $this->addSql('ALTER TABLE course CHANGE description description VARCHAR(255) DEFAULT \'NULL\'');
        $this->addSql('ALTER TABLE libraries CHANGE address address VARCHAR(255) DEFAULT \'NULL\'');
        $this->addSql('ALTER TABLE loans CHANGE end_at end_at DATETIME DEFAULT \'NULL\'');
        $this->addSql('ALTER TABLE messenger_messages CHANGE delivered_at delivered_at DATETIME DEFAULT \'NULL\'');
        $this->addSql('ALTER TABLE post DROP is_locked');
        $this->addSql('ALTER TABLE reward CHANGE icon icon VARCHAR(255) DEFAULT \'NULL\'');
        $this->addSql('ALTER TABLE student_profile CHANGE university university VARCHAR(100) DEFAULT \'NULL\', CHANGE major major VARCHAR(100) DEFAULT \'NULL\', CHANGE academic_level academic_level VARCHAR(50) DEFAULT \'NULL\', CHANGE profile_picture profile_picture VARCHAR(255) DEFAULT \'NULL\', CHANGE interests interests LONGTEXT DEFAULT NULL COLLATE `utf8mb4_bin`');
        $this->addSql('ALTER TABLE study_session CHANGE ended_at ended_at DATETIME DEFAULT \'NULL\', CHANGE completed_at completed_at DATETIME DEFAULT \'NULL\'');
        $this->addSql('ALTER TABLE tutor_profile CHANGE expertise expertise LONGTEXT DEFAULT NULL COLLATE `utf8mb4_bin`, CHANGE hourly_rate hourly_rate NUMERIC(10, 2) DEFAULT \'NULL\', CHANGE profile_picture profile_picture VARCHAR(255) DEFAULT \'NULL\'');
    }
}
