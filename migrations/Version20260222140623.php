<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
<<<<<<<< HEAD:migrations/Version20260222150110.php
final class Version20260222150110 extends AbstractMigration
========
<<<<<<<< HEAD:migrations/Version20260222150800.php
final class Version20260222150800 extends AbstractMigration
========
final class Version20260222140623 extends AbstractMigration
>>>>>>>> 2587575edf99e23afaaed5e6141906049abd379d:migrations/Version20260222140623.php
>>>>>>>> 7cd8396a2c3fccc7c972e02ff302ee9e9ee46754:migrations/Version20260222140623.php
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
<<<<<<<< HEAD:migrations/Version20260222150110.php
        $this->addSql('CREATE TABLE game_content (id INT AUTO_INCREMENT NOT NULL, data JSON DEFAULT NULL, game_id INT NOT NULL, UNIQUE INDEX UNIQ_6B074F86E48FD905 (game_id), PRIMARY KEY (id)) DEFAULT CHARACTER SET utf8mb4');
        $this->addSql('CREATE TABLE user_activities (id INT AUTO_INCREMENT NOT NULL, activity_type VARCHAR(50) NOT NULL, description LONGTEXT NOT NULL, metadata JSON DEFAULT NULL, icon VARCHAR(50) DEFAULT NULL, color VARCHAR(50) DEFAULT NULL, created_at DATETIME NOT NULL, user_id INT NOT NULL, INDEX IDX_12966909A76ED395 (user_id), INDEX idx_user_created (user_id, created_at), INDEX idx_activity_type (activity_type), PRIMARY KEY (id)) DEFAULT CHARACTER SET utf8mb4');
        $this->addSql('ALTER TABLE game_content ADD CONSTRAINT FK_6B074F86E48FD905 FOREIGN KEY (game_id) REFERENCES game (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE user_activities ADD CONSTRAINT FK_12966909A76ED395 FOREIGN KEY (user_id) REFERENCES user (id) ON DELETE CASCADE');
========
<<<<<<<< HEAD:migrations/Version20260222150800.php
        $this->addSql('CREATE TABLE notifications (id INT AUTO_INCREMENT NOT NULL, type VARCHAR(50) NOT NULL, title VARCHAR(255) NOT NULL, message LONGTEXT NOT NULL, metadata JSON DEFAULT NULL, is_read TINYINT NOT NULL, created_at DATETIME NOT NULL, read_at DATETIME DEFAULT NULL, action_url VARCHAR(255) DEFAULT NULL, icon VARCHAR(50) DEFAULT NULL, color VARCHAR(50) DEFAULT NULL, user_id INT NOT NULL, INDEX IDX_6000B0D3A76ED395 (user_id), INDEX idx_user_read (user_id, is_read), INDEX idx_created_at (created_at), PRIMARY KEY (id)) DEFAULT CHARACTER SET utf8mb4');
        $this->addSql('ALTER TABLE notifications ADD CONSTRAINT FK_6000B0D3A76ED395 FOREIGN KEY (user_id) REFERENCES user (id) ON DELETE CASCADE');
========
        $this->addSql('CREATE TABLE user_activities (id INT AUTO_INCREMENT NOT NULL, activity_type VARCHAR(50) NOT NULL, description LONGTEXT NOT NULL, metadata JSON DEFAULT NULL, icon VARCHAR(50) DEFAULT NULL, color VARCHAR(50) DEFAULT NULL, created_at DATETIME NOT NULL, user_id INT NOT NULL, INDEX IDX_12966909A76ED395 (user_id), INDEX idx_user_created (user_id, created_at), INDEX idx_activity_type (activity_type), PRIMARY KEY (id)) DEFAULT CHARACTER SET utf8mb4');
        $this->addSql('ALTER TABLE user_activities ADD CONSTRAINT FK_12966909A76ED395 FOREIGN KEY (user_id) REFERENCES user (id) ON DELETE CASCADE');
>>>>>>>> 2587575edf99e23afaaed5e6141906049abd379d:migrations/Version20260222140623.php
>>>>>>>> 7cd8396a2c3fccc7c972e02ff302ee9e9ee46754:migrations/Version20260222140623.php
        $this->addSql('ALTER TABLE books CHANGE price price NUMERIC(10, 2) DEFAULT NULL, CHANGE cover_image cover_image VARCHAR(255) DEFAULT NULL, CHANGE author author VARCHAR(255) DEFAULT NULL, CHANGE isbn isbn VARCHAR(20) DEFAULT NULL, CHANGE published_at published_at DATETIME DEFAULT NULL, CHANGE updated_at updated_at DATETIME DEFAULT NULL');
        $this->addSql('ALTER TABLE course CHANGE description description VARCHAR(255) DEFAULT NULL');
        $this->addSql('ALTER TABLE game_rating CHANGE updated_at updated_at DATETIME DEFAULT NULL');
        $this->addSql('ALTER TABLE libraries CHANGE address address VARCHAR(255) DEFAULT NULL');
        $this->addSql('ALTER TABLE loans CHANGE end_at end_at DATETIME DEFAULT NULL');
        $this->addSql('ALTER TABLE login_history CHANGE ip_address ip_address VARCHAR(45) DEFAULT NULL, CHANGE browser browser VARCHAR(100) DEFAULT NULL, CHANGE platform platform VARCHAR(100) DEFAULT NULL, CHANGE device device VARCHAR(100) DEFAULT NULL, CHANGE location location VARCHAR(100) DEFAULT NULL, CHANGE failure_reason failure_reason VARCHAR(255) DEFAULT NULL');
        $this->addSql('ALTER TABLE post ADD attachment_name VARCHAR(255) DEFAULT NULL, CHANGE image_name image_name VARCHAR(255) DEFAULT NULL, CHANGE updated_at updated_at DATETIME DEFAULT NULL, CHANGE link link VARCHAR(255) DEFAULT NULL, CHANGE link_title link_title VARCHAR(255) DEFAULT NULL');
        $this->addSql('ALTER TABLE question CHANGE image_name image_name VARCHAR(255) DEFAULT NULL, CHANGE updated_at updated_at DATETIME DEFAULT NULL');
        $this->addSql('ALTER TABLE quiz_report CHANGE resolved_at resolved_at DATETIME DEFAULT NULL');
        $this->addSql('ALTER TABLE reward CHANGE icon icon VARCHAR(255) DEFAULT NULL');
        $this->addSql('ALTER TABLE student_profile CHANGE university university VARCHAR(100) DEFAULT NULL, CHANGE major major VARCHAR(100) DEFAULT NULL, CHANGE academic_level academic_level VARCHAR(50) DEFAULT NULL, CHANGE profile_picture profile_picture VARCHAR(255) DEFAULT NULL, CHANGE interests interests JSON DEFAULT NULL, CHANGE email email VARCHAR(180) DEFAULT NULL, CHANGE updated_at updated_at DATETIME DEFAULT NULL');
        $this->addSql('ALTER TABLE study_session CHANGE ended_at ended_at DATETIME DEFAULT NULL, CHANGE completed_at completed_at DATETIME DEFAULT NULL, CHANGE mood mood VARCHAR(20) DEFAULT NULL, CHANGE energy_level energy_level VARCHAR(20) DEFAULT NULL');
        $this->addSql('ALTER TABLE study_streak CHANGE last_study_date last_study_date DATE DEFAULT NULL');
        $this->addSql('ALTER TABLE tutor_profile CHANGE expertise expertise JSON DEFAULT NULL, CHANGE hourly_rate hourly_rate NUMERIC(10, 2) DEFAULT NULL, CHANGE profile_picture profile_picture VARCHAR(255) DEFAULT NULL, CHANGE updated_at updated_at DATETIME DEFAULT NULL');
        $this->addSql('ALTER TABLE user CHANGE verification_token verification_token VARCHAR(255) DEFAULT NULL, CHANGE verification_token_expires_at verification_token_expires_at DATETIME DEFAULT NULL, CHANGE reset_password_token reset_password_token VARCHAR(255) DEFAULT NULL, CHANGE reset_password_token_expires_at reset_password_token_expires_at DATETIME DEFAULT NULL, CHANGE banned_at banned_at DATETIME DEFAULT NULL, CHANGE totp_secret totp_secret VARCHAR(255) DEFAULT NULL');
<<<<<<<< HEAD:migrations/Version20260222150110.php
========
<<<<<<<< HEAD:migrations/Version20260222150800.php
        $this->addSql('ALTER TABLE user_activities CHANGE metadata metadata JSON DEFAULT NULL, CHANGE icon icon VARCHAR(50) DEFAULT NULL, CHANGE color color VARCHAR(50) DEFAULT NULL');
        $this->addSql('ALTER TABLE user_sessions CHANGE ip_address ip_address VARCHAR(45) DEFAULT NULL, CHANGE user_agent user_agent VARCHAR(255) DEFAULT NULL, CHANGE browser browser VARCHAR(100) DEFAULT NULL, CHANGE platform platform VARCHAR(100) DEFAULT NULL, CHANGE device device VARCHAR(100) DEFAULT NULL, CHANGE location location VARCHAR(100) DEFAULT NULL');
========
>>>>>>>> 2587575edf99e23afaaed5e6141906049abd379d:migrations/Version20260222140623.php
>>>>>>>> 7cd8396a2c3fccc7c972e02ff302ee9e9ee46754:migrations/Version20260222140623.php
        $this->addSql('ALTER TABLE messenger_messages CHANGE delivered_at delivered_at DATETIME DEFAULT NULL');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
<<<<<<<< HEAD:migrations/Version20260222150110.php
        $this->addSql('ALTER TABLE game_content DROP FOREIGN KEY FK_6B074F86E48FD905');
        $this->addSql('ALTER TABLE user_activities DROP FOREIGN KEY FK_12966909A76ED395');
        $this->addSql('DROP TABLE game_content');
        $this->addSql('DROP TABLE user_activities');
========
<<<<<<<< HEAD:migrations/Version20260222150800.php
        $this->addSql('ALTER TABLE notifications DROP FOREIGN KEY FK_6000B0D3A76ED395');
        $this->addSql('DROP TABLE notifications');
========
        $this->addSql('ALTER TABLE user_activities DROP FOREIGN KEY FK_12966909A76ED395');
        $this->addSql('DROP TABLE user_activities');
>>>>>>>> 2587575edf99e23afaaed5e6141906049abd379d:migrations/Version20260222140623.php
>>>>>>>> 7cd8396a2c3fccc7c972e02ff302ee9e9ee46754:migrations/Version20260222140623.php
        $this->addSql('ALTER TABLE books CHANGE price price NUMERIC(10, 2) DEFAULT \'NULL\', CHANGE cover_image cover_image VARCHAR(255) DEFAULT \'NULL\', CHANGE author author VARCHAR(255) DEFAULT \'NULL\', CHANGE isbn isbn VARCHAR(20) DEFAULT \'NULL\', CHANGE published_at published_at DATETIME DEFAULT \'NULL\', CHANGE updated_at updated_at DATETIME DEFAULT \'NULL\'');
        $this->addSql('ALTER TABLE course CHANGE description description VARCHAR(255) DEFAULT \'NULL\'');
        $this->addSql('ALTER TABLE game_rating CHANGE updated_at updated_at DATETIME DEFAULT \'NULL\'');
        $this->addSql('ALTER TABLE libraries CHANGE address address VARCHAR(255) DEFAULT \'NULL\'');
        $this->addSql('ALTER TABLE loans CHANGE end_at end_at DATETIME DEFAULT \'NULL\'');
        $this->addSql('ALTER TABLE login_history CHANGE ip_address ip_address VARCHAR(45) DEFAULT \'NULL\', CHANGE browser browser VARCHAR(100) DEFAULT \'NULL\', CHANGE platform platform VARCHAR(100) DEFAULT \'NULL\', CHANGE device device VARCHAR(100) DEFAULT \'NULL\', CHANGE location location VARCHAR(100) DEFAULT \'NULL\', CHANGE failure_reason failure_reason VARCHAR(255) DEFAULT \'NULL\'');
        $this->addSql('ALTER TABLE messenger_messages CHANGE delivered_at delivered_at DATETIME DEFAULT \'NULL\'');
        $this->addSql('ALTER TABLE post DROP attachment_name, CHANGE image_name image_name VARCHAR(255) DEFAULT \'NULL\', CHANGE updated_at updated_at DATETIME DEFAULT \'NULL\', CHANGE link link VARCHAR(255) DEFAULT \'NULL\', CHANGE link_title link_title VARCHAR(255) DEFAULT \'NULL\'');
        $this->addSql('ALTER TABLE question CHANGE image_name image_name VARCHAR(255) DEFAULT \'NULL\', CHANGE updated_at updated_at DATETIME DEFAULT \'NULL\'');
        $this->addSql('ALTER TABLE quiz_report CHANGE resolved_at resolved_at DATETIME DEFAULT \'NULL\'');
        $this->addSql('ALTER TABLE reward CHANGE icon icon VARCHAR(255) DEFAULT \'NULL\'');
        $this->addSql('ALTER TABLE student_profile CHANGE email email VARCHAR(180) DEFAULT \'NULL\', CHANGE university university VARCHAR(100) DEFAULT \'NULL\', CHANGE major major VARCHAR(100) DEFAULT \'NULL\', CHANGE academic_level academic_level VARCHAR(50) DEFAULT \'NULL\', CHANGE profile_picture profile_picture VARCHAR(255) DEFAULT \'NULL\', CHANGE updated_at updated_at DATETIME DEFAULT \'NULL\', CHANGE interests interests LONGTEXT DEFAULT NULL COLLATE `utf8mb4_bin`');
        $this->addSql('ALTER TABLE study_session CHANGE ended_at ended_at DATETIME DEFAULT \'NULL\', CHANGE completed_at completed_at DATETIME DEFAULT \'NULL\', CHANGE mood mood VARCHAR(20) DEFAULT \'NULL\', CHANGE energy_level energy_level VARCHAR(20) DEFAULT \'NULL\'');
        $this->addSql('ALTER TABLE study_streak CHANGE last_study_date last_study_date DATE DEFAULT \'NULL\'');
        $this->addSql('ALTER TABLE tutor_profile CHANGE expertise expertise LONGTEXT DEFAULT NULL COLLATE `utf8mb4_bin`, CHANGE hourly_rate hourly_rate NUMERIC(10, 2) DEFAULT \'NULL\', CHANGE profile_picture profile_picture VARCHAR(255) DEFAULT \'NULL\', CHANGE updated_at updated_at DATETIME DEFAULT \'NULL\'');
        $this->addSql('ALTER TABLE user CHANGE verification_token verification_token VARCHAR(255) DEFAULT \'NULL\', CHANGE verification_token_expires_at verification_token_expires_at DATETIME DEFAULT \'NULL\', CHANGE reset_password_token reset_password_token VARCHAR(255) DEFAULT \'NULL\', CHANGE reset_password_token_expires_at reset_password_token_expires_at DATETIME DEFAULT \'NULL\', CHANGE banned_at banned_at DATETIME DEFAULT \'NULL\', CHANGE totp_secret totp_secret VARCHAR(255) DEFAULT \'NULL\'');
<<<<<<<< HEAD:migrations/Version20260222150110.php
========
<<<<<<<< HEAD:migrations/Version20260222150800.php
        $this->addSql('ALTER TABLE user_activities CHANGE metadata metadata LONGTEXT DEFAULT NULL COLLATE `utf8mb4_bin`, CHANGE icon icon VARCHAR(50) DEFAULT \'NULL\', CHANGE color color VARCHAR(50) DEFAULT \'NULL\'');
        $this->addSql('ALTER TABLE user_sessions CHANGE ip_address ip_address VARCHAR(45) DEFAULT \'NULL\', CHANGE user_agent user_agent VARCHAR(255) DEFAULT \'NULL\', CHANGE browser browser VARCHAR(100) DEFAULT \'NULL\', CHANGE platform platform VARCHAR(100) DEFAULT \'NULL\', CHANGE device device VARCHAR(100) DEFAULT \'NULL\', CHANGE location location VARCHAR(100) DEFAULT \'NULL\'');
========
>>>>>>>> 2587575edf99e23afaaed5e6141906049abd379d:migrations/Version20260222140623.php
>>>>>>>> 7cd8396a2c3fccc7c972e02ff302ee9e9ee46754:migrations/Version20260222140623.php
    }
}
