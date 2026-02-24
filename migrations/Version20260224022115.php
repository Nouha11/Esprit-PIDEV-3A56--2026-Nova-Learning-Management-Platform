<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20260224022115 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE books ADD pdf_url VARCHAR(500) DEFAULT NULL, CHANGE price price NUMERIC(10, 2) DEFAULT NULL, CHANGE cover_image cover_image VARCHAR(255) DEFAULT NULL, CHANGE author author VARCHAR(255) DEFAULT NULL, CHANGE isbn isbn VARCHAR(20) DEFAULT NULL, CHANGE published_at published_at DATETIME DEFAULT NULL, CHANGE updated_at updated_at DATETIME DEFAULT NULL');
        $this->addSql('ALTER TABLE course CHANGE description description VARCHAR(255) DEFAULT NULL');
        $this->addSql('ALTER TABLE libraries CHANGE address address VARCHAR(255) DEFAULT NULL, CHANGE latitude latitude NUMERIC(10, 7) DEFAULT NULL, CHANGE longitude longitude NUMERIC(10, 7) DEFAULT NULL');
        $this->addSql('ALTER TABLE library_notifications CHANGE link link VARCHAR(255) DEFAULT NULL, CHANGE read_at read_at DATETIME DEFAULT NULL');
        $this->addSql('ALTER TABLE loans CHANGE end_at end_at DATETIME DEFAULT NULL, CHANGE approved_at approved_at DATETIME DEFAULT NULL');
        $this->addSql('ALTER TABLE payments CHANGE card_last_four card_last_four VARCHAR(20) DEFAULT NULL, CHANGE card_holder_name card_holder_name VARCHAR(50) DEFAULT NULL, CHANGE completed_at completed_at DATETIME DEFAULT NULL');
        $this->addSql('ALTER TABLE reward CHANGE icon icon VARCHAR(255) DEFAULT NULL');
        $this->addSql('ALTER TABLE student_profile CHANGE university university VARCHAR(100) DEFAULT NULL, CHANGE major major VARCHAR(100) DEFAULT NULL, CHANGE academic_level academic_level VARCHAR(50) DEFAULT NULL, CHANGE profile_picture profile_picture VARCHAR(255) DEFAULT NULL, CHANGE interests interests JSON DEFAULT NULL');
        $this->addSql('ALTER TABLE study_session CHANGE ended_at ended_at DATETIME DEFAULT NULL, CHANGE completed_at completed_at DATETIME DEFAULT NULL');
        $this->addSql('ALTER TABLE tutor_profile CHANGE expertise expertise JSON DEFAULT NULL, CHANGE hourly_rate hourly_rate NUMERIC(10, 2) DEFAULT NULL, CHANGE profile_picture profile_picture VARCHAR(255) DEFAULT NULL');
        $this->addSql('ALTER TABLE messenger_messages CHANGE delivered_at delivered_at DATETIME DEFAULT NULL');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE books DROP pdf_url, CHANGE price price NUMERIC(10, 2) DEFAULT \'NULL\', CHANGE cover_image cover_image VARCHAR(255) DEFAULT \'NULL\', CHANGE author author VARCHAR(255) DEFAULT \'NULL\', CHANGE isbn isbn VARCHAR(20) DEFAULT \'NULL\', CHANGE published_at published_at DATETIME DEFAULT \'NULL\', CHANGE updated_at updated_at DATETIME DEFAULT \'NULL\'');
        $this->addSql('ALTER TABLE course CHANGE description description VARCHAR(255) DEFAULT \'NULL\'');
        $this->addSql('ALTER TABLE libraries CHANGE address address VARCHAR(255) DEFAULT \'NULL\', CHANGE latitude latitude NUMERIC(10, 7) DEFAULT \'NULL\', CHANGE longitude longitude NUMERIC(10, 7) DEFAULT \'NULL\'');
        $this->addSql('ALTER TABLE library_notifications CHANGE link link VARCHAR(255) DEFAULT \'NULL\', CHANGE read_at read_at DATETIME DEFAULT \'NULL\'');
        $this->addSql('ALTER TABLE loans CHANGE end_at end_at DATETIME DEFAULT \'NULL\', CHANGE approved_at approved_at DATETIME DEFAULT \'NULL\'');
        $this->addSql('ALTER TABLE messenger_messages CHANGE delivered_at delivered_at DATETIME DEFAULT \'NULL\'');
        $this->addSql('ALTER TABLE payments CHANGE card_last_four card_last_four VARCHAR(20) DEFAULT \'NULL\', CHANGE card_holder_name card_holder_name VARCHAR(50) DEFAULT \'NULL\', CHANGE completed_at completed_at DATETIME DEFAULT \'NULL\'');
        $this->addSql('ALTER TABLE reward CHANGE icon icon VARCHAR(255) DEFAULT \'NULL\'');
        $this->addSql('ALTER TABLE student_profile CHANGE university university VARCHAR(100) DEFAULT \'NULL\', CHANGE major major VARCHAR(100) DEFAULT \'NULL\', CHANGE academic_level academic_level VARCHAR(50) DEFAULT \'NULL\', CHANGE profile_picture profile_picture VARCHAR(255) DEFAULT \'NULL\', CHANGE interests interests LONGTEXT DEFAULT NULL COLLATE `utf8mb4_bin`');
        $this->addSql('ALTER TABLE study_session CHANGE ended_at ended_at DATETIME DEFAULT \'NULL\', CHANGE completed_at completed_at DATETIME DEFAULT \'NULL\'');
        $this->addSql('ALTER TABLE tutor_profile CHANGE expertise expertise LONGTEXT DEFAULT NULL COLLATE `utf8mb4_bin`, CHANGE hourly_rate hourly_rate NUMERIC(10, 2) DEFAULT \'NULL\', CHANGE profile_picture profile_picture VARCHAR(255) DEFAULT \'NULL\'');
    }
}
