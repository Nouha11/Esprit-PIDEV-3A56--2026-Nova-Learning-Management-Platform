<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20260208215102 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE books (id INT AUTO_INCREMENT NOT NULL, title VARCHAR(255) NOT NULL, description LONGTEXT DEFAULT NULL, is_digital TINYINT NOT NULL, price NUMERIC(10, 2) DEFAULT NULL, cover_image VARCHAR(255) DEFAULT NULL, author VARCHAR(255) DEFAULT NULL, isbn VARCHAR(20) DEFAULT NULL, published_at DATETIME DEFAULT NULL, uploader_id INT DEFAULT NULL, created_at DATETIME NOT NULL, updated_at DATETIME DEFAULT NULL, PRIMARY KEY (id)) DEFAULT CHARACTER SET utf8mb4');
        $this->addSql('CREATE TABLE book_library (book_id INT NOT NULL, library_id INT NOT NULL, INDEX IDX_32A0B02A16A2B381 (book_id), INDEX IDX_32A0B02AFE2541D7 (library_id), PRIMARY KEY (book_id, library_id)) DEFAULT CHARACTER SET utf8mb4');
        $this->addSql('CREATE TABLE choice (id INT AUTO_INCREMENT NOT NULL, content VARCHAR(255) NOT NULL, is_correct TINYINT NOT NULL, question_id INT NOT NULL, INDEX IDX_C1AB5A921E27F6BF (question_id), PRIMARY KEY (id)) DEFAULT CHARACTER SET utf8mb4');
        $this->addSql('CREATE TABLE comment (id INT AUTO_INCREMENT NOT NULL, content LONGTEXT NOT NULL, created_at DATETIME NOT NULL, is_solution TINYINT NOT NULL, post_id INT NOT NULL, author_id INT NOT NULL, INDEX IDX_9474526C4B89032C (post_id), INDEX IDX_9474526CF675F31B (author_id), PRIMARY KEY (id)) DEFAULT CHARACTER SET utf8mb4');
        $this->addSql('CREATE TABLE course (id INT AUTO_INCREMENT NOT NULL, course_name VARCHAR(255) NOT NULL, description VARCHAR(255) DEFAULT NULL, difficulty VARCHAR(255) NOT NULL, estimated_duration INT NOT NULL, progress INT DEFAULT NULL, status VARCHAR(255) NOT NULL, created_at DATETIME NOT NULL, category VARCHAR(255) NOT NULL, max_students INT DEFAULT NULL, is_published TINYINT NOT NULL, created_by_id INT DEFAULT NULL, INDEX IDX_169E6FB9B03A8386 (created_by_id), PRIMARY KEY (id)) DEFAULT CHARACTER SET utf8mb4');
        $this->addSql('CREATE TABLE digital_purchases (id INT AUTO_INCREMENT NOT NULL, purchased_at DATETIME NOT NULL, book_id INT NOT NULL, user_id INT NOT NULL, INDEX IDX_418AF2AA16A2B381 (book_id), INDEX IDX_418AF2AAA76ED395 (user_id), PRIMARY KEY (id)) DEFAULT CHARACTER SET utf8mb4');
        $this->addSql('CREATE TABLE game (id INT AUTO_INCREMENT NOT NULL, name VARCHAR(255) NOT NULL, description LONGTEXT NOT NULL, type VARCHAR(50) NOT NULL, difficulty VARCHAR(50) NOT NULL, token_cost INT NOT NULL, reward_tokens INT NOT NULL, reward_xp INT NOT NULL, is_active TINYINT NOT NULL, created_at DATETIME NOT NULL, user_id INT DEFAULT NULL, UNIQUE INDEX UNIQ_232B318C5E237E06 (name), INDEX IDX_232B318CA76ED395 (user_id), PRIMARY KEY (id)) DEFAULT CHARACTER SET utf8mb4');
        $this->addSql('CREATE TABLE libraries (id INT AUTO_INCREMENT NOT NULL, name VARCHAR(255) NOT NULL, address VARCHAR(255) DEFAULT NULL, PRIMARY KEY (id)) DEFAULT CHARACTER SET utf8mb4');
        $this->addSql('CREATE TABLE loans (id INT AUTO_INCREMENT NOT NULL, start_at DATETIME NOT NULL, end_at DATETIME DEFAULT NULL, book_id INT NOT NULL, user_id INT NOT NULL, INDEX IDX_82C24DBC16A2B381 (book_id), INDEX IDX_82C24DBCA76ED395 (user_id), PRIMARY KEY (id)) DEFAULT CHARACTER SET utf8mb4');
        $this->addSql('CREATE TABLE planning (id INT AUTO_INCREMENT NOT NULL, title VARCHAR(255) NOT NULL, scheduled_date DATE NOT NULL, scheduled_time TIME NOT NULL, planned_duration INT NOT NULL, status VARCHAR(255) NOT NULL, reminder TINYINT NOT NULL, created_at DATETIME NOT NULL, course_id INT NOT NULL, INDEX IDX_D499BFF6591CC992 (course_id), PRIMARY KEY (id)) DEFAULT CHARACTER SET utf8mb4');
        $this->addSql('CREATE TABLE post (id INT AUTO_INCREMENT NOT NULL, title VARCHAR(255) NOT NULL, content LONGTEXT NOT NULL, upvotes INT NOT NULL, created_at DATETIME NOT NULL, author_id INT NOT NULL, INDEX IDX_5A8A6C8DF675F31B (author_id), PRIMARY KEY (id)) DEFAULT CHARACTER SET utf8mb4');
        $this->addSql('CREATE TABLE question (id INT AUTO_INCREMENT NOT NULL, text LONGTEXT NOT NULL, xp_value INT NOT NULL, difficulty VARCHAR(255) NOT NULL, quiz_id INT NOT NULL, user_id INT DEFAULT NULL, INDEX IDX_B6F7494E853CD175 (quiz_id), INDEX IDX_B6F7494EA76ED395 (user_id), PRIMARY KEY (id)) DEFAULT CHARACTER SET utf8mb4');
        $this->addSql('CREATE TABLE quiz (id INT AUTO_INCREMENT NOT NULL, title VARCHAR(255) NOT NULL, description LONGTEXT DEFAULT NULL, PRIMARY KEY (id)) DEFAULT CHARACTER SET utf8mb4');
        $this->addSql('CREATE TABLE reward (id INT AUTO_INCREMENT NOT NULL, name VARCHAR(255) NOT NULL, description LONGTEXT DEFAULT NULL, type VARCHAR(50) NOT NULL, value INT NOT NULL, requirement LONGTEXT DEFAULT NULL, icon VARCHAR(255) DEFAULT NULL, is_active TINYINT NOT NULL, PRIMARY KEY (id)) DEFAULT CHARACTER SET utf8mb4');
        $this->addSql('CREATE TABLE student_profile (id INT AUTO_INCREMENT NOT NULL, first_name VARCHAR(100) NOT NULL, last_name VARCHAR(100) NOT NULL, bio LONGTEXT DEFAULT NULL, university VARCHAR(100) DEFAULT NULL, major VARCHAR(100) DEFAULT NULL, academic_level VARCHAR(50) DEFAULT NULL, profile_picture VARCHAR(255) DEFAULT NULL, interests JSON DEFAULT NULL, total_xp INT NOT NULL, total_tokens INT NOT NULL, level INT NOT NULL, PRIMARY KEY (id)) DEFAULT CHARACTER SET utf8mb4');
        $this->addSql('CREATE TABLE study_session (id INT AUTO_INCREMENT NOT NULL, started_at DATETIME NOT NULL, ended_at DATETIME DEFAULT NULL, duration INT NOT NULL, actual_duration INT DEFAULT NULL, energy_used INT DEFAULT NULL, xp_earned INT DEFAULT NULL, burnout_risk VARCHAR(255) NOT NULL, completed_at DATETIME DEFAULT NULL, user_id INT NOT NULL, planning_id INT NOT NULL, INDEX IDX_E55128B6A76ED395 (user_id), INDEX IDX_E55128B63D865311 (planning_id), PRIMARY KEY (id)) DEFAULT CHARACTER SET utf8mb4');
        $this->addSql('CREATE TABLE tutor_profile (id INT AUTO_INCREMENT NOT NULL, first_name VARCHAR(100) NOT NULL, last_name VARCHAR(100) NOT NULL, bio LONGTEXT DEFAULT NULL, expertise JSON DEFAULT NULL, qualifications LONGTEXT DEFAULT NULL, years_of_experience INT NOT NULL, hourly_rate NUMERIC(10, 2) DEFAULT NULL, is_available TINYINT NOT NULL, profile_picture VARCHAR(255) DEFAULT NULL, PRIMARY KEY (id)) DEFAULT CHARACTER SET utf8mb4');
        $this->addSql('CREATE TABLE user (id INT AUTO_INCREMENT NOT NULL, email VARCHAR(180) NOT NULL, password VARCHAR(255) NOT NULL, username VARCHAR(100) NOT NULL, role VARCHAR(50) NOT NULL, is_active TINYINT NOT NULL, created_at DATETIME NOT NULL, updated_at DATETIME NOT NULL, xp INT DEFAULT 0 NOT NULL, student_profile_id INT DEFAULT NULL, tutor_profile_id INT DEFAULT NULL, UNIQUE INDEX UNIQ_8D93D649E7927C74 (email), UNIQUE INDEX UNIQ_8D93D649F85E0677 (username), UNIQUE INDEX UNIQ_8D93D6492125FF59 (student_profile_id), UNIQUE INDEX UNIQ_8D93D649430AF9E (tutor_profile_id), PRIMARY KEY (id)) DEFAULT CHARACTER SET utf8mb4');
        $this->addSql('CREATE TABLE user_libraries (id INT AUTO_INCREMENT NOT NULL, granted_at DATETIME NOT NULL, book_id INT NOT NULL, user_id INT NOT NULL, INDEX IDX_3A3DD84D16A2B381 (book_id), INDEX IDX_3A3DD84DA76ED395 (user_id), PRIMARY KEY (id)) DEFAULT CHARACTER SET utf8mb4');
        $this->addSql('CREATE TABLE messenger_messages (id BIGINT AUTO_INCREMENT NOT NULL, body LONGTEXT NOT NULL, headers LONGTEXT NOT NULL, queue_name VARCHAR(190) NOT NULL, created_at DATETIME NOT NULL, available_at DATETIME NOT NULL, delivered_at DATETIME DEFAULT NULL, INDEX IDX_75EA56E0FB7336F0E3BD61CE16BA31DBBF396750 (queue_name, available_at, delivered_at, id), PRIMARY KEY (id)) DEFAULT CHARACTER SET utf8mb4');
        $this->addSql('ALTER TABLE book_library ADD CONSTRAINT FK_32A0B02A16A2B381 FOREIGN KEY (book_id) REFERENCES books (id)');
        $this->addSql('ALTER TABLE book_library ADD CONSTRAINT FK_32A0B02AFE2541D7 FOREIGN KEY (library_id) REFERENCES libraries (id)');
        $this->addSql('ALTER TABLE choice ADD CONSTRAINT FK_C1AB5A921E27F6BF FOREIGN KEY (question_id) REFERENCES question (id)');
        $this->addSql('ALTER TABLE comment ADD CONSTRAINT FK_9474526C4B89032C FOREIGN KEY (post_id) REFERENCES post (id)');
        $this->addSql('ALTER TABLE comment ADD CONSTRAINT FK_9474526CF675F31B FOREIGN KEY (author_id) REFERENCES user (id)');
        $this->addSql('ALTER TABLE course ADD CONSTRAINT FK_169E6FB9B03A8386 FOREIGN KEY (created_by_id) REFERENCES user (id)');
        $this->addSql('ALTER TABLE digital_purchases ADD CONSTRAINT FK_418AF2AA16A2B381 FOREIGN KEY (book_id) REFERENCES books (id)');
        $this->addSql('ALTER TABLE digital_purchases ADD CONSTRAINT FK_418AF2AAA76ED395 FOREIGN KEY (user_id) REFERENCES user (id)');
        $this->addSql('ALTER TABLE game ADD CONSTRAINT FK_232B318CA76ED395 FOREIGN KEY (user_id) REFERENCES user (id)');
        $this->addSql('ALTER TABLE loans ADD CONSTRAINT FK_82C24DBC16A2B381 FOREIGN KEY (book_id) REFERENCES books (id)');
        $this->addSql('ALTER TABLE loans ADD CONSTRAINT FK_82C24DBCA76ED395 FOREIGN KEY (user_id) REFERENCES user (id)');
        $this->addSql('ALTER TABLE planning ADD CONSTRAINT FK_D499BFF6591CC992 FOREIGN KEY (course_id) REFERENCES course (id)');
        $this->addSql('ALTER TABLE post ADD CONSTRAINT FK_5A8A6C8DF675F31B FOREIGN KEY (author_id) REFERENCES user (id)');
        $this->addSql('ALTER TABLE question ADD CONSTRAINT FK_B6F7494E853CD175 FOREIGN KEY (quiz_id) REFERENCES quiz (id)');
        $this->addSql('ALTER TABLE question ADD CONSTRAINT FK_B6F7494EA76ED395 FOREIGN KEY (user_id) REFERENCES user (id)');
        $this->addSql('ALTER TABLE study_session ADD CONSTRAINT FK_E55128B6A76ED395 FOREIGN KEY (user_id) REFERENCES user (id)');
        $this->addSql('ALTER TABLE study_session ADD CONSTRAINT FK_E55128B63D865311 FOREIGN KEY (planning_id) REFERENCES planning (id)');
        $this->addSql('ALTER TABLE user ADD CONSTRAINT FK_8D93D6492125FF59 FOREIGN KEY (student_profile_id) REFERENCES student_profile (id)');
        $this->addSql('ALTER TABLE user ADD CONSTRAINT FK_8D93D649430AF9E FOREIGN KEY (tutor_profile_id) REFERENCES tutor_profile (id)');
        $this->addSql('ALTER TABLE user_libraries ADD CONSTRAINT FK_3A3DD84D16A2B381 FOREIGN KEY (book_id) REFERENCES books (id)');
        $this->addSql('ALTER TABLE user_libraries ADD CONSTRAINT FK_3A3DD84DA76ED395 FOREIGN KEY (user_id) REFERENCES user (id)');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE book_library DROP FOREIGN KEY FK_32A0B02A16A2B381');
        $this->addSql('ALTER TABLE book_library DROP FOREIGN KEY FK_32A0B02AFE2541D7');
        $this->addSql('ALTER TABLE choice DROP FOREIGN KEY FK_C1AB5A921E27F6BF');
        $this->addSql('ALTER TABLE comment DROP FOREIGN KEY FK_9474526C4B89032C');
        $this->addSql('ALTER TABLE comment DROP FOREIGN KEY FK_9474526CF675F31B');
        $this->addSql('ALTER TABLE course DROP FOREIGN KEY FK_169E6FB9B03A8386');
        $this->addSql('ALTER TABLE digital_purchases DROP FOREIGN KEY FK_418AF2AA16A2B381');
        $this->addSql('ALTER TABLE digital_purchases DROP FOREIGN KEY FK_418AF2AAA76ED395');
        $this->addSql('ALTER TABLE game DROP FOREIGN KEY FK_232B318CA76ED395');
        $this->addSql('ALTER TABLE loans DROP FOREIGN KEY FK_82C24DBC16A2B381');
        $this->addSql('ALTER TABLE loans DROP FOREIGN KEY FK_82C24DBCA76ED395');
        $this->addSql('ALTER TABLE planning DROP FOREIGN KEY FK_D499BFF6591CC992');
        $this->addSql('ALTER TABLE post DROP FOREIGN KEY FK_5A8A6C8DF675F31B');
        $this->addSql('ALTER TABLE question DROP FOREIGN KEY FK_B6F7494E853CD175');
        $this->addSql('ALTER TABLE question DROP FOREIGN KEY FK_B6F7494EA76ED395');
        $this->addSql('ALTER TABLE study_session DROP FOREIGN KEY FK_E55128B6A76ED395');
        $this->addSql('ALTER TABLE study_session DROP FOREIGN KEY FK_E55128B63D865311');
        $this->addSql('ALTER TABLE user DROP FOREIGN KEY FK_8D93D6492125FF59');
        $this->addSql('ALTER TABLE user DROP FOREIGN KEY FK_8D93D649430AF9E');
        $this->addSql('ALTER TABLE user_libraries DROP FOREIGN KEY FK_3A3DD84D16A2B381');
        $this->addSql('ALTER TABLE user_libraries DROP FOREIGN KEY FK_3A3DD84DA76ED395');
        $this->addSql('DROP TABLE books');
        $this->addSql('DROP TABLE book_library');
        $this->addSql('DROP TABLE choice');
        $this->addSql('DROP TABLE comment');
        $this->addSql('DROP TABLE course');
        $this->addSql('DROP TABLE digital_purchases');
        $this->addSql('DROP TABLE game');
        $this->addSql('DROP TABLE libraries');
        $this->addSql('DROP TABLE loans');
        $this->addSql('DROP TABLE planning');
        $this->addSql('DROP TABLE post');
        $this->addSql('DROP TABLE question');
        $this->addSql('DROP TABLE quiz');
        $this->addSql('DROP TABLE reward');
        $this->addSql('DROP TABLE student_profile');
        $this->addSql('DROP TABLE study_session');
        $this->addSql('DROP TABLE tutor_profile');
        $this->addSql('DROP TABLE user');
        $this->addSql('DROP TABLE user_libraries');
        $this->addSql('DROP TABLE messenger_messages');
    }
}
