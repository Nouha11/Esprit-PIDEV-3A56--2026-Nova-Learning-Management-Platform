<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20260222122148 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE login_history (id INT AUTO_INCREMENT NOT NULL, status VARCHAR(20) NOT NULL, ip_address VARCHAR(45) DEFAULT NULL, user_agent LONGTEXT DEFAULT NULL, browser VARCHAR(100) DEFAULT NULL, platform VARCHAR(100) DEFAULT NULL, device VARCHAR(100) DEFAULT NULL, location VARCHAR(100) DEFAULT NULL, failure_reason VARCHAR(255) DEFAULT NULL, is2fa_used TINYINT DEFAULT 0 NOT NULL, created_at DATETIME NOT NULL, user_id INT NOT NULL, INDEX idx_user_id (user_id), INDEX idx_created_at (created_at), INDEX idx_status (status), PRIMARY KEY (id)) DEFAULT CHARACTER SET utf8mb4');
        $this->addSql('CREATE TABLE space (id INT AUTO_INCREMENT NOT NULL, name VARCHAR(255) NOT NULL, description LONGTEXT NOT NULL, PRIMARY KEY (id)) DEFAULT CHARACTER SET utf8mb4');
        $this->addSql('CREATE TABLE user_bookmarked_posts (user_id INT NOT NULL, post_id INT NOT NULL, INDEX IDX_55F2261EA76ED395 (user_id), INDEX IDX_55F2261E4B89032C (post_id), PRIMARY KEY (user_id, post_id)) DEFAULT CHARACTER SET utf8mb4');
        $this->addSql('ALTER TABLE login_history ADD CONSTRAINT FK_37976E36A76ED395 FOREIGN KEY (user_id) REFERENCES user (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE user_bookmarked_posts ADD CONSTRAINT FK_55F2261EA76ED395 FOREIGN KEY (user_id) REFERENCES user (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE user_bookmarked_posts ADD CONSTRAINT FK_55F2261E4B89032C FOREIGN KEY (post_id) REFERENCES post (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE books CHANGE price price NUMERIC(10, 2) DEFAULT NULL, CHANGE cover_image cover_image VARCHAR(255) DEFAULT NULL, CHANGE author author VARCHAR(255) DEFAULT NULL, CHANGE isbn isbn VARCHAR(20) DEFAULT NULL, CHANGE published_at published_at DATETIME DEFAULT NULL, CHANGE updated_at updated_at DATETIME DEFAULT NULL');
        $this->addSql('ALTER TABLE course CHANGE description description VARCHAR(255) DEFAULT NULL');
        $this->addSql('ALTER TABLE game_content CHANGE data data JSON DEFAULT NULL');
        $this->addSql('ALTER TABLE game_rating CHANGE updated_at updated_at DATETIME DEFAULT NULL');
        $this->addSql('ALTER TABLE libraries CHANGE address address VARCHAR(255) DEFAULT NULL');
        $this->addSql('ALTER TABLE loans CHANGE end_at end_at DATETIME DEFAULT NULL');
        $this->addSql('ALTER TABLE post ADD link_title VARCHAR(255) DEFAULT NULL, ADD link_description LONGTEXT DEFAULT NULL, ADD link_image LONGTEXT DEFAULT NULL, ADD space_id INT DEFAULT NULL, CHANGE image_name image_name VARCHAR(255) DEFAULT NULL, CHANGE updated_at updated_at DATETIME DEFAULT NULL, CHANGE link link VARCHAR(255) DEFAULT NULL');
        $this->addSql('ALTER TABLE post ADD CONSTRAINT FK_5A8A6C8D23575340 FOREIGN KEY (space_id) REFERENCES space (id)');
        $this->addSql('CREATE INDEX IDX_5A8A6C8D23575340 ON post (space_id)');
        $this->addSql('ALTER TABLE question ADD image_name VARCHAR(255) DEFAULT NULL, ADD updated_at DATETIME DEFAULT NULL');
        $this->addSql('ALTER TABLE quiz_report CHANGE resolved_at resolved_at DATETIME DEFAULT NULL');
        $this->addSql('ALTER TABLE reward CHANGE icon icon VARCHAR(255) DEFAULT NULL');
        $this->addSql('ALTER TABLE student_profile ADD updated_at DATETIME DEFAULT NULL, CHANGE university university VARCHAR(100) DEFAULT NULL, CHANGE major major VARCHAR(100) DEFAULT NULL, CHANGE academic_level academic_level VARCHAR(50) DEFAULT NULL, CHANGE profile_picture profile_picture VARCHAR(255) DEFAULT NULL, CHANGE interests interests JSON DEFAULT NULL, CHANGE email email VARCHAR(180) DEFAULT NULL');
        $this->addSql('ALTER TABLE study_session CHANGE ended_at ended_at DATETIME DEFAULT NULL, CHANGE completed_at completed_at DATETIME DEFAULT NULL, CHANGE mood mood VARCHAR(20) DEFAULT NULL, CHANGE energy_level energy_level VARCHAR(20) DEFAULT NULL');
        $this->addSql('ALTER TABLE study_streak CHANGE last_study_date last_study_date DATE DEFAULT NULL');
        $this->addSql('ALTER TABLE tutor_profile ADD updated_at DATETIME DEFAULT NULL, CHANGE expertise expertise JSON DEFAULT NULL, CHANGE hourly_rate hourly_rate NUMERIC(10, 2) DEFAULT NULL, CHANGE profile_picture profile_picture VARCHAR(255) DEFAULT NULL');
        $this->addSql('ALTER TABLE user CHANGE verification_token verification_token VARCHAR(255) DEFAULT NULL, CHANGE verification_token_expires_at verification_token_expires_at DATETIME DEFAULT NULL, CHANGE reset_password_token reset_password_token VARCHAR(255) DEFAULT NULL, CHANGE reset_password_token_expires_at reset_password_token_expires_at DATETIME DEFAULT NULL, CHANGE banned_at banned_at DATETIME DEFAULT NULL, CHANGE totp_secret totp_secret VARCHAR(255) DEFAULT NULL');
        $this->addSql('ALTER TABLE messenger_messages CHANGE delivered_at delivered_at DATETIME DEFAULT NULL');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE login_history DROP FOREIGN KEY FK_37976E36A76ED395');
        $this->addSql('ALTER TABLE user_bookmarked_posts DROP FOREIGN KEY FK_55F2261EA76ED395');
        $this->addSql('ALTER TABLE user_bookmarked_posts DROP FOREIGN KEY FK_55F2261E4B89032C');
        $this->addSql('DROP TABLE login_history');
        $this->addSql('DROP TABLE space');
        $this->addSql('DROP TABLE user_bookmarked_posts');
        $this->addSql('ALTER TABLE books CHANGE price price NUMERIC(10, 2) DEFAULT \'NULL\', CHANGE cover_image cover_image VARCHAR(255) DEFAULT \'NULL\', CHANGE author author VARCHAR(255) DEFAULT \'NULL\', CHANGE isbn isbn VARCHAR(20) DEFAULT \'NULL\', CHANGE published_at published_at DATETIME DEFAULT \'NULL\', CHANGE updated_at updated_at DATETIME DEFAULT \'NULL\'');
        $this->addSql('ALTER TABLE course CHANGE description description VARCHAR(255) DEFAULT \'NULL\'');
        $this->addSql('ALTER TABLE game_content CHANGE data data LONGTEXT DEFAULT NULL COLLATE `utf8mb4_bin`');
        $this->addSql('ALTER TABLE game_rating CHANGE updated_at updated_at DATETIME DEFAULT \'NULL\'');
        $this->addSql('ALTER TABLE libraries CHANGE address address VARCHAR(255) DEFAULT \'NULL\'');
        $this->addSql('ALTER TABLE loans CHANGE end_at end_at DATETIME DEFAULT \'NULL\'');
        $this->addSql('ALTER TABLE messenger_messages CHANGE delivered_at delivered_at DATETIME DEFAULT \'NULL\'');
        $this->addSql('ALTER TABLE post DROP FOREIGN KEY FK_5A8A6C8D23575340');
        $this->addSql('DROP INDEX IDX_5A8A6C8D23575340 ON post');
        $this->addSql('ALTER TABLE post DROP link_title, DROP link_description, DROP link_image, DROP space_id, CHANGE image_name image_name VARCHAR(255) DEFAULT \'NULL\', CHANGE updated_at updated_at DATETIME DEFAULT \'NULL\', CHANGE link link VARCHAR(255) DEFAULT \'NULL\'');
        $this->addSql('ALTER TABLE question DROP image_name, DROP updated_at');
        $this->addSql('ALTER TABLE quiz_report CHANGE resolved_at resolved_at DATETIME DEFAULT \'NULL\'');
        $this->addSql('ALTER TABLE reward CHANGE icon icon VARCHAR(255) DEFAULT \'NULL\'');
        $this->addSql('ALTER TABLE student_profile DROP updated_at, CHANGE email email VARCHAR(180) DEFAULT \'NULL\', CHANGE university university VARCHAR(100) DEFAULT \'NULL\', CHANGE major major VARCHAR(100) DEFAULT \'NULL\', CHANGE academic_level academic_level VARCHAR(50) DEFAULT \'NULL\', CHANGE profile_picture profile_picture VARCHAR(255) DEFAULT \'NULL\', CHANGE interests interests LONGTEXT DEFAULT NULL COLLATE `utf8mb4_bin`');
        $this->addSql('ALTER TABLE study_session CHANGE ended_at ended_at DATETIME DEFAULT \'NULL\', CHANGE completed_at completed_at DATETIME DEFAULT \'NULL\', CHANGE mood mood VARCHAR(20) DEFAULT \'NULL\', CHANGE energy_level energy_level VARCHAR(20) DEFAULT \'NULL\'');
        $this->addSql('ALTER TABLE study_streak CHANGE last_study_date last_study_date DATE DEFAULT \'NULL\'');
        $this->addSql('ALTER TABLE tutor_profile DROP updated_at, CHANGE expertise expertise LONGTEXT DEFAULT NULL COLLATE `utf8mb4_bin`, CHANGE hourly_rate hourly_rate NUMERIC(10, 2) DEFAULT \'NULL\', CHANGE profile_picture profile_picture VARCHAR(255) DEFAULT \'NULL\'');
        $this->addSql('ALTER TABLE user CHANGE verification_token verification_token VARCHAR(255) DEFAULT \'NULL\', CHANGE verification_token_expires_at verification_token_expires_at DATETIME DEFAULT \'NULL\', CHANGE reset_password_token reset_password_token VARCHAR(255) DEFAULT \'NULL\', CHANGE reset_password_token_expires_at reset_password_token_expires_at DATETIME DEFAULT \'NULL\', CHANGE banned_at banned_at DATETIME DEFAULT \'NULL\', CHANGE totp_secret totp_secret VARCHAR(255) DEFAULT \'NULL\'');
    }
}
