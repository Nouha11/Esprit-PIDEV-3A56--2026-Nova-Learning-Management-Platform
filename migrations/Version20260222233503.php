<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20260222233503 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE post_downvoters (post_id INT NOT NULL, user_id INT NOT NULL, INDEX IDX_5E652F4B4B89032C (post_id), INDEX IDX_5E652F4BA76ED395 (user_id), PRIMARY KEY (post_id, user_id)) DEFAULT CHARACTER SET utf8mb4');
        $this->addSql('ALTER TABLE post_downvoters ADD CONSTRAINT FK_5E652F4B4B89032C FOREIGN KEY (post_id) REFERENCES post (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE post_downvoters ADD CONSTRAINT FK_5E652F4BA76ED395 FOREIGN KEY (user_id) REFERENCES user (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE books CHANGE price price NUMERIC(10, 2) DEFAULT NULL, CHANGE cover_image cover_image VARCHAR(255) DEFAULT NULL, CHANGE author author VARCHAR(255) DEFAULT NULL, CHANGE isbn isbn VARCHAR(20) DEFAULT NULL, CHANGE published_at published_at DATETIME DEFAULT NULL, CHANGE updated_at updated_at DATETIME DEFAULT NULL');
        $this->addSql('ALTER TABLE comment ADD image_name VARCHAR(255) DEFAULT NULL, ADD updated_at DATETIME DEFAULT NULL, ADD parent_id INT DEFAULT NULL');
        $this->addSql('ALTER TABLE comment ADD CONSTRAINT FK_9474526C727ACA70 FOREIGN KEY (parent_id) REFERENCES comment (id) ON DELETE CASCADE');
        $this->addSql('CREATE INDEX IDX_9474526C727ACA70 ON comment (parent_id)');
        $this->addSql('ALTER TABLE course CHANGE description description VARCHAR(255) DEFAULT NULL');
        $this->addSql('ALTER TABLE enrollment_requests CHANGE responded_at responded_at DATETIME DEFAULT NULL');
        $this->addSql('ALTER TABLE game_content CHANGE data data JSON DEFAULT NULL');
        $this->addSql('ALTER TABLE game_rating CHANGE updated_at updated_at DATETIME DEFAULT NULL');
        $this->addSql('ALTER TABLE libraries CHANGE address address VARCHAR(255) DEFAULT NULL');
        $this->addSql('ALTER TABLE loans CHANGE end_at end_at DATETIME DEFAULT NULL');
        $this->addSql('ALTER TABLE login_history CHANGE ip_address ip_address VARCHAR(45) DEFAULT NULL, CHANGE browser browser VARCHAR(100) DEFAULT NULL, CHANGE platform platform VARCHAR(100) DEFAULT NULL, CHANGE device device VARCHAR(100) DEFAULT NULL, CHANGE location location VARCHAR(100) DEFAULT NULL, CHANGE failure_reason failure_reason VARCHAR(255) DEFAULT NULL');
        $this->addSql('ALTER TABLE notifications CHANGE metadata metadata JSON DEFAULT NULL, CHANGE read_at read_at DATETIME DEFAULT NULL, CHANGE action_url action_url VARCHAR(255) DEFAULT NULL, CHANGE icon icon VARCHAR(50) DEFAULT NULL, CHANGE color color VARCHAR(50) DEFAULT NULL');
        $this->addSql('ALTER TABLE post ADD attachment_name VARCHAR(255) DEFAULT NULL, ADD hot_score DOUBLE PRECISION NOT NULL, CHANGE image_name image_name VARCHAR(255) DEFAULT NULL, CHANGE updated_at updated_at DATETIME DEFAULT NULL, CHANGE link link VARCHAR(255) DEFAULT NULL, CHANGE link_title link_title VARCHAR(255) DEFAULT NULL');
        $this->addSql('ALTER TABLE question CHANGE image_name image_name VARCHAR(255) DEFAULT NULL, CHANGE updated_at updated_at DATETIME DEFAULT NULL');
        $this->addSql('ALTER TABLE quiz_report CHANGE resolved_at resolved_at DATETIME DEFAULT NULL');
        $this->addSql('ALTER TABLE report ADD comment_id INT DEFAULT NULL, CHANGE post_id post_id INT DEFAULT NULL');
        $this->addSql('ALTER TABLE report ADD CONSTRAINT FK_C42F7784F8697D13 FOREIGN KEY (comment_id) REFERENCES comment (id) ON DELETE CASCADE');
        $this->addSql('CREATE INDEX IDX_C42F7784F8697D13 ON report (comment_id)');
        $this->addSql('ALTER TABLE reward CHANGE icon icon VARCHAR(255) DEFAULT NULL');
        $this->addSql('ALTER TABLE student_profile CHANGE university university VARCHAR(100) DEFAULT NULL, CHANGE major major VARCHAR(100) DEFAULT NULL, CHANGE academic_level academic_level VARCHAR(50) DEFAULT NULL, CHANGE profile_picture profile_picture VARCHAR(255) DEFAULT NULL, CHANGE interests interests JSON DEFAULT NULL, CHANGE email email VARCHAR(180) DEFAULT NULL, CHANGE updated_at updated_at DATETIME DEFAULT NULL, CHANGE last_energy_update last_energy_update DATETIME DEFAULT NULL');
        $this->addSql('ALTER TABLE study_session CHANGE ended_at ended_at DATETIME DEFAULT NULL, CHANGE completed_at completed_at DATETIME DEFAULT NULL, CHANGE mood mood VARCHAR(20) DEFAULT NULL, CHANGE energy_level energy_level VARCHAR(20) DEFAULT NULL');
        $this->addSql('ALTER TABLE study_streak CHANGE last_study_date last_study_date DATE DEFAULT NULL');
        $this->addSql('ALTER TABLE tutor_profile CHANGE expertise expertise JSON DEFAULT NULL, CHANGE hourly_rate hourly_rate NUMERIC(10, 2) DEFAULT NULL, CHANGE profile_picture profile_picture VARCHAR(255) DEFAULT NULL, CHANGE updated_at updated_at DATETIME DEFAULT NULL');
        $this->addSql('ALTER TABLE user CHANGE verification_token verification_token VARCHAR(255) DEFAULT NULL, CHANGE verification_token_expires_at verification_token_expires_at DATETIME DEFAULT NULL, CHANGE reset_password_token reset_password_token VARCHAR(255) DEFAULT NULL, CHANGE reset_password_token_expires_at reset_password_token_expires_at DATETIME DEFAULT NULL, CHANGE banned_at banned_at DATETIME DEFAULT NULL, CHANGE totp_secret totp_secret VARCHAR(255) DEFAULT NULL');
        $this->addSql('ALTER TABLE user_activities CHANGE metadata metadata JSON DEFAULT NULL, CHANGE icon icon VARCHAR(50) DEFAULT NULL, CHANGE color color VARCHAR(50) DEFAULT NULL');
        $this->addSql('ALTER TABLE user_sessions CHANGE ip_address ip_address VARCHAR(45) DEFAULT NULL, CHANGE user_agent user_agent VARCHAR(255) DEFAULT NULL, CHANGE browser browser VARCHAR(100) DEFAULT NULL, CHANGE platform platform VARCHAR(100) DEFAULT NULL, CHANGE device device VARCHAR(100) DEFAULT NULL, CHANGE location location VARCHAR(100) DEFAULT NULL');
        $this->addSql('ALTER TABLE messenger_messages CHANGE delivered_at delivered_at DATETIME DEFAULT NULL');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE post_downvoters DROP FOREIGN KEY FK_5E652F4B4B89032C');
        $this->addSql('ALTER TABLE post_downvoters DROP FOREIGN KEY FK_5E652F4BA76ED395');
        $this->addSql('DROP TABLE post_downvoters');
        $this->addSql('ALTER TABLE books CHANGE price price NUMERIC(10, 2) DEFAULT \'NULL\', CHANGE cover_image cover_image VARCHAR(255) DEFAULT \'NULL\', CHANGE author author VARCHAR(255) DEFAULT \'NULL\', CHANGE isbn isbn VARCHAR(20) DEFAULT \'NULL\', CHANGE published_at published_at DATETIME DEFAULT \'NULL\', CHANGE updated_at updated_at DATETIME DEFAULT \'NULL\'');
        $this->addSql('ALTER TABLE comment DROP FOREIGN KEY FK_9474526C727ACA70');
        $this->addSql('DROP INDEX IDX_9474526C727ACA70 ON comment');
        $this->addSql('ALTER TABLE comment DROP image_name, DROP updated_at, DROP parent_id');
        $this->addSql('ALTER TABLE course CHANGE description description VARCHAR(255) DEFAULT \'NULL\'');
        $this->addSql('ALTER TABLE enrollment_requests CHANGE responded_at responded_at DATETIME DEFAULT \'NULL\'');
        $this->addSql('ALTER TABLE game_content CHANGE data data LONGTEXT DEFAULT NULL COLLATE `utf8mb4_bin`');
        $this->addSql('ALTER TABLE game_rating CHANGE updated_at updated_at DATETIME DEFAULT \'NULL\'');
        $this->addSql('ALTER TABLE libraries CHANGE address address VARCHAR(255) DEFAULT \'NULL\'');
        $this->addSql('ALTER TABLE loans CHANGE end_at end_at DATETIME DEFAULT \'NULL\'');
        $this->addSql('ALTER TABLE login_history CHANGE ip_address ip_address VARCHAR(45) DEFAULT \'NULL\', CHANGE browser browser VARCHAR(100) DEFAULT \'NULL\', CHANGE platform platform VARCHAR(100) DEFAULT \'NULL\', CHANGE device device VARCHAR(100) DEFAULT \'NULL\', CHANGE location location VARCHAR(100) DEFAULT \'NULL\', CHANGE failure_reason failure_reason VARCHAR(255) DEFAULT \'NULL\'');
        $this->addSql('ALTER TABLE messenger_messages CHANGE delivered_at delivered_at DATETIME DEFAULT \'NULL\'');
        $this->addSql('ALTER TABLE notifications CHANGE metadata metadata LONGTEXT DEFAULT NULL COLLATE `utf8mb4_bin`, CHANGE read_at read_at DATETIME DEFAULT \'NULL\', CHANGE action_url action_url VARCHAR(255) DEFAULT \'NULL\', CHANGE icon icon VARCHAR(50) DEFAULT \'NULL\', CHANGE color color VARCHAR(50) DEFAULT \'NULL\'');
        $this->addSql('ALTER TABLE post DROP attachment_name, DROP hot_score, CHANGE image_name image_name VARCHAR(255) DEFAULT \'NULL\', CHANGE updated_at updated_at DATETIME DEFAULT \'NULL\', CHANGE link link VARCHAR(255) DEFAULT \'NULL\', CHANGE link_title link_title VARCHAR(255) DEFAULT \'NULL\'');
        $this->addSql('ALTER TABLE question CHANGE image_name image_name VARCHAR(255) DEFAULT \'NULL\', CHANGE updated_at updated_at DATETIME DEFAULT \'NULL\'');
        $this->addSql('ALTER TABLE quiz_report CHANGE resolved_at resolved_at DATETIME DEFAULT \'NULL\'');
        $this->addSql('ALTER TABLE report DROP FOREIGN KEY FK_C42F7784F8697D13');
        $this->addSql('DROP INDEX IDX_C42F7784F8697D13 ON report');
        $this->addSql('ALTER TABLE report DROP comment_id, CHANGE post_id post_id INT NOT NULL');
        $this->addSql('ALTER TABLE reward CHANGE icon icon VARCHAR(255) DEFAULT \'NULL\'');
        $this->addSql('ALTER TABLE student_profile CHANGE email email VARCHAR(180) DEFAULT \'NULL\', CHANGE university university VARCHAR(100) DEFAULT \'NULL\', CHANGE major major VARCHAR(100) DEFAULT \'NULL\', CHANGE academic_level academic_level VARCHAR(50) DEFAULT \'NULL\', CHANGE profile_picture profile_picture VARCHAR(255) DEFAULT \'NULL\', CHANGE updated_at updated_at DATETIME DEFAULT \'NULL\', CHANGE interests interests LONGTEXT DEFAULT NULL COLLATE `utf8mb4_bin`, CHANGE last_energy_update last_energy_update DATETIME DEFAULT \'NULL\'');
        $this->addSql('ALTER TABLE study_session CHANGE ended_at ended_at DATETIME DEFAULT \'NULL\', CHANGE completed_at completed_at DATETIME DEFAULT \'NULL\', CHANGE mood mood VARCHAR(20) DEFAULT \'NULL\', CHANGE energy_level energy_level VARCHAR(20) DEFAULT \'NULL\'');
        $this->addSql('ALTER TABLE study_streak CHANGE last_study_date last_study_date DATE DEFAULT \'NULL\'');
        $this->addSql('ALTER TABLE tutor_profile CHANGE expertise expertise LONGTEXT DEFAULT NULL COLLATE `utf8mb4_bin`, CHANGE hourly_rate hourly_rate NUMERIC(10, 2) DEFAULT \'NULL\', CHANGE profile_picture profile_picture VARCHAR(255) DEFAULT \'NULL\', CHANGE updated_at updated_at DATETIME DEFAULT \'NULL\'');
        $this->addSql('ALTER TABLE user CHANGE verification_token verification_token VARCHAR(255) DEFAULT \'NULL\', CHANGE verification_token_expires_at verification_token_expires_at DATETIME DEFAULT \'NULL\', CHANGE reset_password_token reset_password_token VARCHAR(255) DEFAULT \'NULL\', CHANGE reset_password_token_expires_at reset_password_token_expires_at DATETIME DEFAULT \'NULL\', CHANGE banned_at banned_at DATETIME DEFAULT \'NULL\', CHANGE totp_secret totp_secret VARCHAR(255) DEFAULT \'NULL\'');
        $this->addSql('ALTER TABLE user_activities CHANGE metadata metadata LONGTEXT DEFAULT NULL COLLATE `utf8mb4_bin`, CHANGE icon icon VARCHAR(50) DEFAULT \'NULL\', CHANGE color color VARCHAR(50) DEFAULT \'NULL\'');
        $this->addSql('ALTER TABLE user_sessions CHANGE ip_address ip_address VARCHAR(45) DEFAULT \'NULL\', CHANGE user_agent user_agent VARCHAR(255) DEFAULT \'NULL\', CHANGE browser browser VARCHAR(100) DEFAULT \'NULL\', CHANGE platform platform VARCHAR(100) DEFAULT \'NULL\', CHANGE device device VARCHAR(100) DEFAULT \'NULL\', CHANGE location location VARCHAR(100) DEFAULT \'NULL\'');
    }
}
