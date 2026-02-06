<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20260206150915 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE study_session (id INT AUTO_INCREMENT NOT NULL, started_at DATETIME NOT NULL, ended_at DATETIME DEFAULT NULL, duration INT NOT NULL, energy_used INT DEFAULT NULL, xp_earned INT DEFAULT NULL, burnout_risk VARCHAR(255) NOT NULL, user_id INT NOT NULL, planning_id INT NOT NULL, INDEX IDX_E55128B6A76ED395 (user_id), INDEX IDX_E55128B63D865311 (planning_id), PRIMARY KEY (id)) DEFAULT CHARACTER SET utf8mb4');
        $this->addSql('ALTER TABLE study_session ADD CONSTRAINT FK_E55128B6A76ED395 FOREIGN KEY (user_id) REFERENCES user (id)');
        $this->addSql('ALTER TABLE study_session ADD CONSTRAINT FK_E55128B63D865311 FOREIGN KEY (planning_id) REFERENCES planning (id)');
        $this->addSql('ALTER TABLE course CHANGE description description VARCHAR(255) DEFAULT NULL');
        $this->addSql('ALTER TABLE planning ADD course_id INT NOT NULL');
        $this->addSql('ALTER TABLE planning ADD CONSTRAINT FK_D499BFF6591CC992 FOREIGN KEY (course_id) REFERENCES course (id)');
        $this->addSql('CREATE INDEX IDX_D499BFF6591CC992 ON planning (course_id)');
        $this->addSql('ALTER TABLE reward CHANGE icon icon VARCHAR(255) DEFAULT NULL');
        $this->addSql('ALTER TABLE student_profile CHANGE major major VARCHAR(100) DEFAULT NULL, CHANGE academic_level academic_level VARCHAR(50) DEFAULT NULL, CHANGE profile_picture profile_picture VARCHAR(255) DEFAULT NULL');
        $this->addSql('ALTER TABLE tutor_profile CHANGE hourly_rate hourly_rate NUMERIC(10, 2) DEFAULT NULL, CHANGE profile_picture profile_picture VARCHAR(255) DEFAULT NULL');
        $this->addSql('ALTER TABLE messenger_messages CHANGE delivered_at delivered_at DATETIME DEFAULT NULL');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE study_session DROP FOREIGN KEY FK_E55128B6A76ED395');
        $this->addSql('ALTER TABLE study_session DROP FOREIGN KEY FK_E55128B63D865311');
        $this->addSql('DROP TABLE study_session');
        $this->addSql('ALTER TABLE course CHANGE description description VARCHAR(255) DEFAULT \'NULL\'');
        $this->addSql('ALTER TABLE messenger_messages CHANGE delivered_at delivered_at DATETIME DEFAULT \'NULL\'');
        $this->addSql('ALTER TABLE planning DROP FOREIGN KEY FK_D499BFF6591CC992');
        $this->addSql('DROP INDEX IDX_D499BFF6591CC992 ON planning');
        $this->addSql('ALTER TABLE planning DROP course_id');
        $this->addSql('ALTER TABLE reward CHANGE icon icon VARCHAR(255) DEFAULT \'NULL\'');
        $this->addSql('ALTER TABLE student_profile CHANGE major major VARCHAR(100) DEFAULT \'NULL\', CHANGE academic_level academic_level VARCHAR(50) DEFAULT \'NULL\', CHANGE profile_picture profile_picture VARCHAR(255) DEFAULT \'NULL\'');
        $this->addSql('ALTER TABLE tutor_profile CHANGE hourly_rate hourly_rate NUMERIC(10, 2) DEFAULT \'NULL\', CHANGE profile_picture profile_picture VARCHAR(255) DEFAULT \'NULL\'');
    }
}
