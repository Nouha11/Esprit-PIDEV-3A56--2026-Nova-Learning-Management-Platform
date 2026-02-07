<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20260207122812 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE books (id INT AUTO_INCREMENT NOT NULL, title VARCHAR(255) NOT NULL, description LONGTEXT DEFAULT NULL, is_digital TINYINT NOT NULL, PRIMARY KEY (id)) DEFAULT CHARACTER SET utf8mb4');
        $this->addSql('CREATE TABLE digital_purchases (id INT AUTO_INCREMENT NOT NULL, purchased_at DATETIME NOT NULL, book_id INT NOT NULL, user_id INT NOT NULL, INDEX IDX_418AF2AA16A2B381 (book_id), INDEX IDX_418AF2AAA76ED395 (user_id), PRIMARY KEY (id)) DEFAULT CHARACTER SET utf8mb4');
        $this->addSql('CREATE TABLE libraries (id INT AUTO_INCREMENT NOT NULL, name VARCHAR(255) NOT NULL, address VARCHAR(255) DEFAULT NULL, PRIMARY KEY (id)) DEFAULT CHARACTER SET utf8mb4');
        $this->addSql('CREATE TABLE loans (id INT AUTO_INCREMENT NOT NULL, start_at DATETIME NOT NULL, end_at DATETIME DEFAULT NULL, book_id INT NOT NULL, user_id INT NOT NULL, INDEX IDX_82C24DBC16A2B381 (book_id), INDEX IDX_82C24DBCA76ED395 (user_id), PRIMARY KEY (id)) DEFAULT CHARACTER SET utf8mb4');
        $this->addSql('CREATE TABLE user_libraries (id INT AUTO_INCREMENT NOT NULL, granted_at DATETIME NOT NULL, book_id INT NOT NULL, user_id INT NOT NULL, INDEX IDX_3A3DD84D16A2B381 (book_id), INDEX IDX_3A3DD84DA76ED395 (user_id), PRIMARY KEY (id)) DEFAULT CHARACTER SET utf8mb4');
        $this->addSql('ALTER TABLE digital_purchases ADD CONSTRAINT FK_418AF2AA16A2B381 FOREIGN KEY (book_id) REFERENCES books (id)');
        $this->addSql('ALTER TABLE digital_purchases ADD CONSTRAINT FK_418AF2AAA76ED395 FOREIGN KEY (user_id) REFERENCES user (id)');
        $this->addSql('ALTER TABLE loans ADD CONSTRAINT FK_82C24DBC16A2B381 FOREIGN KEY (book_id) REFERENCES books (id)');
        $this->addSql('ALTER TABLE loans ADD CONSTRAINT FK_82C24DBCA76ED395 FOREIGN KEY (user_id) REFERENCES user (id)');
        $this->addSql('ALTER TABLE user_libraries ADD CONSTRAINT FK_3A3DD84D16A2B381 FOREIGN KEY (book_id) REFERENCES books (id)');
        $this->addSql('ALTER TABLE user_libraries ADD CONSTRAINT FK_3A3DD84DA76ED395 FOREIGN KEY (user_id) REFERENCES user (id)');
        $this->addSql('ALTER TABLE course CHANGE description description VARCHAR(255) DEFAULT NULL');
        $this->addSql('ALTER TABLE reward CHANGE icon icon VARCHAR(255) DEFAULT NULL');
        $this->addSql('ALTER TABLE student_profile CHANGE major major VARCHAR(100) DEFAULT NULL, CHANGE academic_level academic_level VARCHAR(50) DEFAULT NULL, CHANGE profile_picture profile_picture VARCHAR(255) DEFAULT NULL');
        $this->addSql('ALTER TABLE study_session CHANGE ended_at ended_at DATETIME DEFAULT NULL');
        $this->addSql('ALTER TABLE tutor_profile CHANGE hourly_rate hourly_rate NUMERIC(10, 2) DEFAULT NULL, CHANGE profile_picture profile_picture VARCHAR(255) DEFAULT NULL');
        $this->addSql('ALTER TABLE messenger_messages CHANGE delivered_at delivered_at DATETIME DEFAULT NULL');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE digital_purchases DROP FOREIGN KEY FK_418AF2AA16A2B381');
        $this->addSql('ALTER TABLE digital_purchases DROP FOREIGN KEY FK_418AF2AAA76ED395');
        $this->addSql('ALTER TABLE loans DROP FOREIGN KEY FK_82C24DBC16A2B381');
        $this->addSql('ALTER TABLE loans DROP FOREIGN KEY FK_82C24DBCA76ED395');
        $this->addSql('ALTER TABLE user_libraries DROP FOREIGN KEY FK_3A3DD84D16A2B381');
        $this->addSql('ALTER TABLE user_libraries DROP FOREIGN KEY FK_3A3DD84DA76ED395');
        $this->addSql('DROP TABLE books');
        $this->addSql('DROP TABLE digital_purchases');
        $this->addSql('DROP TABLE libraries');
        $this->addSql('DROP TABLE loans');
        $this->addSql('DROP TABLE user_libraries');
        $this->addSql('ALTER TABLE course CHANGE description description VARCHAR(255) DEFAULT \'NULL\'');
        $this->addSql('ALTER TABLE messenger_messages CHANGE delivered_at delivered_at DATETIME DEFAULT \'NULL\'');
        $this->addSql('ALTER TABLE reward CHANGE icon icon VARCHAR(255) DEFAULT \'NULL\'');
        $this->addSql('ALTER TABLE student_profile CHANGE major major VARCHAR(100) DEFAULT \'NULL\', CHANGE academic_level academic_level VARCHAR(50) DEFAULT \'NULL\', CHANGE profile_picture profile_picture VARCHAR(255) DEFAULT \'NULL\'');
        $this->addSql('ALTER TABLE study_session CHANGE ended_at ended_at DATETIME DEFAULT \'NULL\'');
        $this->addSql('ALTER TABLE tutor_profile CHANGE hourly_rate hourly_rate NUMERIC(10, 2) DEFAULT \'NULL\', CHANGE profile_picture profile_picture VARCHAR(255) DEFAULT \'NULL\'');
    }
}
