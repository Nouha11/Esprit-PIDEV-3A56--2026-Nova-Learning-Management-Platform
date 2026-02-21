<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20260220215346 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE note (id INT AUTO_INCREMENT NOT NULL, content LONGTEXT NOT NULL, created_at DATETIME NOT NULL, updated_at DATETIME NOT NULL, study_session_id INT NOT NULL, INDEX idx_study_session (study_session_id), PRIMARY KEY (id)) DEFAULT CHARACTER SET utf8mb4');
        $this->addSql('CREATE TABLE resource (id INT AUTO_INCREMENT NOT NULL, filename VARCHAR(255) NOT NULL, stored_filename VARCHAR(255) NOT NULL, file_size INT NOT NULL, mime_type VARCHAR(100) NOT NULL, uploaded_at DATETIME NOT NULL, study_session_id INT NOT NULL, UNIQUE INDEX UNIQ_BC91F416DF8EB9B7 (stored_filename), INDEX idx_study_session (study_session_id), PRIMARY KEY (id)) DEFAULT CHARACTER SET utf8mb4');
        $this->addSql('CREATE TABLE study_session_tag (study_session_id INT NOT NULL, tag_id INT NOT NULL, INDEX IDX_3365D285E6A388BF (study_session_id), INDEX IDX_3365D285BAD26311 (tag_id), PRIMARY KEY (study_session_id, tag_id)) DEFAULT CHARACTER SET utf8mb4');
        $this->addSql('CREATE TABLE study_streak (id INT AUTO_INCREMENT NOT NULL, current_streak INT NOT NULL, longest_streak INT NOT NULL, last_study_date DATE DEFAULT NULL, updated_at DATETIME NOT NULL, user_id INT NOT NULL, UNIQUE INDEX UNIQ_B9DF8301A76ED395 (user_id), PRIMARY KEY (id)) DEFAULT CHARACTER SET utf8mb4');
        $this->addSql('CREATE TABLE tag (id INT AUTO_INCREMENT NOT NULL, name VARCHAR(50) NOT NULL, created_at DATETIME NOT NULL, UNIQUE INDEX UNIQ_389B7835E237E06 (name), INDEX idx_name (name), PRIMARY KEY (id)) DEFAULT CHARACTER SET utf8mb4');
        $this->addSql('ALTER TABLE note ADD CONSTRAINT FK_CFBDFA14E6A388BF FOREIGN KEY (study_session_id) REFERENCES study_session (id)');
        $this->addSql('ALTER TABLE resource ADD CONSTRAINT FK_BC91F416E6A388BF FOREIGN KEY (study_session_id) REFERENCES study_session (id)');
        $this->addSql('ALTER TABLE study_session_tag ADD CONSTRAINT FK_3365D285E6A388BF FOREIGN KEY (study_session_id) REFERENCES study_session (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE study_session_tag ADD CONSTRAINT FK_3365D285BAD26311 FOREIGN KEY (tag_id) REFERENCES tag (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE study_streak ADD CONSTRAINT FK_B9DF8301A76ED395 FOREIGN KEY (user_id) REFERENCES user (id)');
        $this->addSql('ALTER TABLE books CHANGE price price NUMERIC(10, 2) DEFAULT NULL, CHANGE cover_image cover_image VARCHAR(255) DEFAULT NULL, CHANGE author author VARCHAR(255) DEFAULT NULL, CHANGE isbn isbn VARCHAR(20) DEFAULT NULL, CHANGE published_at published_at DATETIME DEFAULT NULL, CHANGE updated_at updated_at DATETIME DEFAULT NULL');
        $this->addSql('ALTER TABLE course CHANGE description description VARCHAR(255) DEFAULT NULL');
        $this->addSql('ALTER TABLE game_rating CHANGE updated_at updated_at DATETIME DEFAULT NULL');
        $this->addSql('ALTER TABLE libraries CHANGE address address VARCHAR(255) DEFAULT NULL');
        $this->addSql('ALTER TABLE loans CHANGE end_at end_at DATETIME DEFAULT NULL');
        $this->addSql('ALTER TABLE reward CHANGE icon icon VARCHAR(255) DEFAULT NULL');
        $this->addSql('ALTER TABLE student_profile CHANGE university university VARCHAR(100) DEFAULT NULL, CHANGE major major VARCHAR(100) DEFAULT NULL, CHANGE academic_level academic_level VARCHAR(50) DEFAULT NULL, CHANGE profile_picture profile_picture VARCHAR(255) DEFAULT NULL, CHANGE interests interests JSON DEFAULT NULL, CHANGE email email VARCHAR(180) DEFAULT NULL');
        $this->addSql('ALTER TABLE study_session ADD mood VARCHAR(20) DEFAULT NULL, ADD energy_level VARCHAR(20) DEFAULT NULL, ADD break_duration INT DEFAULT NULL, ADD break_count INT DEFAULT NULL, ADD pomodoro_count INT DEFAULT NULL, CHANGE ended_at ended_at DATETIME DEFAULT NULL, CHANGE completed_at completed_at DATETIME DEFAULT NULL');
        $this->addSql('ALTER TABLE tutor_profile CHANGE expertise expertise JSON DEFAULT NULL, CHANGE hourly_rate hourly_rate NUMERIC(10, 2) DEFAULT NULL, CHANGE profile_picture profile_picture VARCHAR(255) DEFAULT NULL');
        $this->addSql('ALTER TABLE user CHANGE verification_token verification_token VARCHAR(255) DEFAULT NULL, CHANGE verification_token_expires_at verification_token_expires_at DATETIME DEFAULT NULL, CHANGE reset_password_token reset_password_token VARCHAR(255) DEFAULT NULL, CHANGE reset_password_token_expires_at reset_password_token_expires_at DATETIME DEFAULT NULL');
        $this->addSql('ALTER TABLE messenger_messages CHANGE delivered_at delivered_at DATETIME DEFAULT NULL');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE note DROP FOREIGN KEY FK_CFBDFA14E6A388BF');
        $this->addSql('ALTER TABLE resource DROP FOREIGN KEY FK_BC91F416E6A388BF');
        $this->addSql('ALTER TABLE study_session_tag DROP FOREIGN KEY FK_3365D285E6A388BF');
        $this->addSql('ALTER TABLE study_session_tag DROP FOREIGN KEY FK_3365D285BAD26311');
        $this->addSql('ALTER TABLE study_streak DROP FOREIGN KEY FK_B9DF8301A76ED395');
        $this->addSql('DROP TABLE note');
        $this->addSql('DROP TABLE resource');
        $this->addSql('DROP TABLE study_session_tag');
        $this->addSql('DROP TABLE study_streak');
        $this->addSql('DROP TABLE tag');
        $this->addSql('ALTER TABLE books CHANGE price price NUMERIC(10, 2) DEFAULT \'NULL\', CHANGE cover_image cover_image VARCHAR(255) DEFAULT \'NULL\', CHANGE author author VARCHAR(255) DEFAULT \'NULL\', CHANGE isbn isbn VARCHAR(20) DEFAULT \'NULL\', CHANGE published_at published_at DATETIME DEFAULT \'NULL\', CHANGE updated_at updated_at DATETIME DEFAULT \'NULL\'');
        $this->addSql('ALTER TABLE course CHANGE description description VARCHAR(255) DEFAULT \'NULL\'');
        $this->addSql('ALTER TABLE game_rating CHANGE updated_at updated_at DATETIME DEFAULT \'NULL\'');
        $this->addSql('ALTER TABLE libraries CHANGE address address VARCHAR(255) DEFAULT \'NULL\'');
        $this->addSql('ALTER TABLE loans CHANGE end_at end_at DATETIME DEFAULT \'NULL\'');
        $this->addSql('ALTER TABLE messenger_messages CHANGE delivered_at delivered_at DATETIME DEFAULT \'NULL\'');
        $this->addSql('ALTER TABLE reward CHANGE icon icon VARCHAR(255) DEFAULT \'NULL\'');
        $this->addSql('ALTER TABLE student_profile CHANGE email email VARCHAR(180) DEFAULT \'NULL\', CHANGE university university VARCHAR(100) DEFAULT \'NULL\', CHANGE major major VARCHAR(100) DEFAULT \'NULL\', CHANGE academic_level academic_level VARCHAR(50) DEFAULT \'NULL\', CHANGE profile_picture profile_picture VARCHAR(255) DEFAULT \'NULL\', CHANGE interests interests LONGTEXT DEFAULT NULL COLLATE `utf8mb4_bin`');
        $this->addSql('ALTER TABLE study_session DROP mood, DROP energy_level, DROP break_duration, DROP break_count, DROP pomodoro_count, CHANGE ended_at ended_at DATETIME DEFAULT \'NULL\', CHANGE completed_at completed_at DATETIME DEFAULT \'NULL\'');
        $this->addSql('ALTER TABLE tutor_profile CHANGE expertise expertise LONGTEXT DEFAULT NULL COLLATE `utf8mb4_bin`, CHANGE hourly_rate hourly_rate NUMERIC(10, 2) DEFAULT \'NULL\', CHANGE profile_picture profile_picture VARCHAR(255) DEFAULT \'NULL\'');
        $this->addSql('ALTER TABLE user CHANGE verification_token verification_token VARCHAR(255) DEFAULT \'NULL\', CHANGE verification_token_expires_at verification_token_expires_at DATETIME DEFAULT \'NULL\', CHANGE reset_password_token reset_password_token VARCHAR(255) DEFAULT \'NULL\', CHANGE reset_password_token_expires_at reset_password_token_expires_at DATETIME DEFAULT \'NULL\'');
    }
}
