<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20260220131400 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE books CHANGE price price NUMERIC(10, 2) DEFAULT NULL, CHANGE cover_image cover_image VARCHAR(255) DEFAULT NULL, CHANGE author author VARCHAR(255) DEFAULT NULL, CHANGE isbn isbn VARCHAR(20) DEFAULT NULL, CHANGE published_at published_at DATETIME DEFAULT NULL, CHANGE updated_at updated_at DATETIME DEFAULT NULL, ADD PRIMARY KEY (id)');
        $this->addSql('ALTER TABLE books ADD CONSTRAINT FK_4A1B2A92A76ED395 FOREIGN KEY (user_id) REFERENCES user (id)');
        $this->addSql('CREATE INDEX IDX_4A1B2A92A76ED395 ON books (user_id)');
        $this->addSql('ALTER TABLE book_library ADD PRIMARY KEY (book_id, library_id)');
        $this->addSql('ALTER TABLE book_library ADD CONSTRAINT FK_32A0B02A16A2B381 FOREIGN KEY (book_id) REFERENCES books (id)');
        $this->addSql('ALTER TABLE book_library ADD CONSTRAINT FK_32A0B02AFE2541D7 FOREIGN KEY (library_id) REFERENCES libraries (id)');
        $this->addSql('CREATE INDEX IDX_32A0B02A16A2B381 ON book_library (book_id)');
        $this->addSql('CREATE INDEX IDX_32A0B02AFE2541D7 ON book_library (library_id)');
        $this->addSql('ALTER TABLE choice ADD PRIMARY KEY (id)');
        $this->addSql('ALTER TABLE choice ADD CONSTRAINT FK_C1AB5A921E27F6BF FOREIGN KEY (question_id) REFERENCES question (id)');
        $this->addSql('CREATE INDEX IDX_C1AB5A921E27F6BF ON choice (question_id)');
        $this->addSql('ALTER TABLE comment ADD PRIMARY KEY (id)');
        $this->addSql('ALTER TABLE comment ADD CONSTRAINT FK_9474526C4B89032C FOREIGN KEY (post_id) REFERENCES post (id)');
        $this->addSql('ALTER TABLE comment ADD CONSTRAINT FK_9474526CF675F31B FOREIGN KEY (author_id) REFERENCES user (id)');
        $this->addSql('CREATE INDEX IDX_9474526C4B89032C ON comment (post_id)');
        $this->addSql('CREATE INDEX IDX_9474526CF675F31B ON comment (author_id)');
        $this->addSql('ALTER TABLE comment_upvoters ADD PRIMARY KEY (comment_id, user_id)');
        $this->addSql('ALTER TABLE comment_upvoters ADD CONSTRAINT FK_C8E3F802F8697D13 FOREIGN KEY (comment_id) REFERENCES comment (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE comment_upvoters ADD CONSTRAINT FK_C8E3F802A76ED395 FOREIGN KEY (user_id) REFERENCES user (id) ON DELETE CASCADE');
        $this->addSql('CREATE INDEX IDX_C8E3F802F8697D13 ON comment_upvoters (comment_id)');
        $this->addSql('CREATE INDEX IDX_C8E3F802A76ED395 ON comment_upvoters (user_id)');
        $this->addSql('ALTER TABLE comment_downvoters ADD PRIMARY KEY (comment_id, user_id)');
        $this->addSql('ALTER TABLE comment_downvoters ADD CONSTRAINT FK_FFFCACAAF8697D13 FOREIGN KEY (comment_id) REFERENCES comment (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE comment_downvoters ADD CONSTRAINT FK_FFFCACAAA76ED395 FOREIGN KEY (user_id) REFERENCES user (id) ON DELETE CASCADE');
        $this->addSql('CREATE INDEX IDX_FFFCACAAF8697D13 ON comment_downvoters (comment_id)');
        $this->addSql('CREATE INDEX IDX_FFFCACAAA76ED395 ON comment_downvoters (user_id)');
        $this->addSql('ALTER TABLE course CHANGE description description VARCHAR(255) DEFAULT NULL, ADD PRIMARY KEY (id)');
        $this->addSql('ALTER TABLE course ADD CONSTRAINT FK_169E6FB9B03A8386 FOREIGN KEY (created_by_id) REFERENCES user (id)');
        $this->addSql('CREATE INDEX IDX_169E6FB9B03A8386 ON course (created_by_id)');
        $this->addSql('ALTER TABLE digital_purchases ADD PRIMARY KEY (id)');
        $this->addSql('ALTER TABLE digital_purchases ADD CONSTRAINT FK_418AF2AA16A2B381 FOREIGN KEY (book_id) REFERENCES books (id)');
        $this->addSql('ALTER TABLE digital_purchases ADD CONSTRAINT FK_418AF2AAA76ED395 FOREIGN KEY (user_id) REFERENCES user (id)');
        $this->addSql('CREATE INDEX IDX_418AF2AA16A2B381 ON digital_purchases (book_id)');
        $this->addSql('CREATE INDEX IDX_418AF2AAA76ED395 ON digital_purchases (user_id)');
        $this->addSql('ALTER TABLE game ADD PRIMARY KEY (id)');
        $this->addSql('ALTER TABLE game ADD CONSTRAINT FK_232B318CA76ED395 FOREIGN KEY (user_id) REFERENCES user (id)');
        $this->addSql('CREATE UNIQUE INDEX UNIQ_232B318C5E237E06 ON game (name)');
        $this->addSql('CREATE INDEX IDX_232B318CA76ED395 ON game (user_id)');
        $this->addSql('ALTER TABLE game_rewards ADD PRIMARY KEY (game_id, reward_id)');
        $this->addSql('ALTER TABLE game_rewards ADD CONSTRAINT FK_7CE06118E48FD905 FOREIGN KEY (game_id) REFERENCES game (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE game_rewards ADD CONSTRAINT FK_7CE06118E466ACA1 FOREIGN KEY (reward_id) REFERENCES reward (id) ON DELETE CASCADE');
        $this->addSql('CREATE INDEX IDX_7CE06118E48FD905 ON game_rewards (game_id)');
        $this->addSql('CREATE INDEX IDX_7CE06118E466ACA1 ON game_rewards (reward_id)');
        $this->addSql('ALTER TABLE libraries CHANGE address address VARCHAR(255) DEFAULT NULL, ADD PRIMARY KEY (id)');
        $this->addSql('ALTER TABLE loans CHANGE end_at end_at DATETIME DEFAULT NULL, ADD PRIMARY KEY (id)');
        $this->addSql('ALTER TABLE loans ADD CONSTRAINT FK_82C24DBC16A2B381 FOREIGN KEY (book_id) REFERENCES books (id)');
        $this->addSql('ALTER TABLE loans ADD CONSTRAINT FK_82C24DBCA76ED395 FOREIGN KEY (user_id) REFERENCES user (id)');
        $this->addSql('CREATE INDEX IDX_82C24DBC16A2B381 ON loans (book_id)');
        $this->addSql('CREATE INDEX IDX_82C24DBCA76ED395 ON loans (user_id)');
        $this->addSql('ALTER TABLE planning ADD PRIMARY KEY (id)');
        $this->addSql('ALTER TABLE planning ADD CONSTRAINT FK_D499BFF6591CC992 FOREIGN KEY (course_id) REFERENCES course (id)');
        $this->addSql('CREATE INDEX IDX_D499BFF6591CC992 ON planning (course_id)');
        $this->addSql('ALTER TABLE post ADD PRIMARY KEY (id)');
        $this->addSql('ALTER TABLE post ADD CONSTRAINT FK_5A8A6C8DF675F31B FOREIGN KEY (author_id) REFERENCES user (id)');
        $this->addSql('CREATE INDEX IDX_5A8A6C8DF675F31B ON post (author_id)');
        $this->addSql('ALTER TABLE post_upvoters ADD PRIMARY KEY (post_id, user_id)');
        $this->addSql('ALTER TABLE post_upvoters ADD CONSTRAINT FK_6A4A12A54B89032C FOREIGN KEY (post_id) REFERENCES post (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE post_upvoters ADD CONSTRAINT FK_6A4A12A5A76ED395 FOREIGN KEY (user_id) REFERENCES user (id) ON DELETE CASCADE');
        $this->addSql('CREATE INDEX IDX_6A4A12A54B89032C ON post_upvoters (post_id)');
        $this->addSql('CREATE INDEX IDX_6A4A12A5A76ED395 ON post_upvoters (user_id)');
        $this->addSql('ALTER TABLE question ADD PRIMARY KEY (id)');
        $this->addSql('ALTER TABLE question ADD CONSTRAINT FK_B6F7494E853CD175 FOREIGN KEY (quiz_id) REFERENCES quiz (id)');
        $this->addSql('ALTER TABLE question ADD CONSTRAINT FK_B6F7494EA76ED395 FOREIGN KEY (user_id) REFERENCES user (id)');
        $this->addSql('CREATE INDEX IDX_B6F7494E853CD175 ON question (quiz_id)');
        $this->addSql('CREATE INDEX IDX_B6F7494EA76ED395 ON question (user_id)');
        $this->addSql('ALTER TABLE quiz ADD PRIMARY KEY (id)');
        $this->addSql('ALTER TABLE reward CHANGE icon icon VARCHAR(255) DEFAULT NULL, ADD PRIMARY KEY (id)');
        $this->addSql('ALTER TABLE student_profile CHANGE university university VARCHAR(100) DEFAULT NULL, CHANGE major major VARCHAR(100) DEFAULT NULL, CHANGE academic_level academic_level VARCHAR(50) DEFAULT NULL, CHANGE profile_picture profile_picture VARCHAR(255) DEFAULT NULL, CHANGE interests interests JSON DEFAULT NULL, CHANGE email email VARCHAR(180) DEFAULT NULL, ADD PRIMARY KEY (id)');
        $this->addSql('CREATE UNIQUE INDEX UNIQ_6C611FF7E7927C74 ON student_profile (email)');
        $this->addSql('ALTER TABLE student_earned_rewards ADD PRIMARY KEY (student_profile_id, reward_id)');
        $this->addSql('ALTER TABLE student_earned_rewards ADD CONSTRAINT FK_199824092125FF59 FOREIGN KEY (student_profile_id) REFERENCES student_profile (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE student_earned_rewards ADD CONSTRAINT FK_19982409E466ACA1 FOREIGN KEY (reward_id) REFERENCES reward (id) ON DELETE CASCADE');
        $this->addSql('CREATE INDEX IDX_199824092125FF59 ON student_earned_rewards (student_profile_id)');
        $this->addSql('CREATE INDEX IDX_19982409E466ACA1 ON student_earned_rewards (reward_id)');
        $this->addSql('ALTER TABLE study_session CHANGE ended_at ended_at DATETIME DEFAULT NULL, CHANGE completed_at completed_at DATETIME DEFAULT NULL, ADD PRIMARY KEY (id)');
        $this->addSql('ALTER TABLE study_session ADD CONSTRAINT FK_E55128B6A76ED395 FOREIGN KEY (user_id) REFERENCES user (id)');
        $this->addSql('ALTER TABLE study_session ADD CONSTRAINT FK_E55128B63D865311 FOREIGN KEY (planning_id) REFERENCES planning (id)');
        $this->addSql('CREATE INDEX IDX_E55128B6A76ED395 ON study_session (user_id)');
        $this->addSql('CREATE INDEX IDX_E55128B63D865311 ON study_session (planning_id)');
        $this->addSql('ALTER TABLE tutor_profile CHANGE expertise expertise JSON DEFAULT NULL, CHANGE hourly_rate hourly_rate NUMERIC(10, 2) DEFAULT NULL, CHANGE profile_picture profile_picture VARCHAR(255) DEFAULT NULL, ADD PRIMARY KEY (id)');
        $this->addSql('ALTER TABLE user CHANGE verification_token verification_token VARCHAR(255) DEFAULT NULL, CHANGE verification_token_expires_at verification_token_expires_at DATETIME DEFAULT NULL, ADD PRIMARY KEY (id)');
        $this->addSql('ALTER TABLE user ADD CONSTRAINT FK_8D93D6492125FF59 FOREIGN KEY (student_profile_id) REFERENCES student_profile (id)');
        $this->addSql('ALTER TABLE user ADD CONSTRAINT FK_8D93D649430AF9E FOREIGN KEY (tutor_profile_id) REFERENCES tutor_profile (id)');
        $this->addSql('CREATE UNIQUE INDEX UNIQ_8D93D649E7927C74 ON user (email)');
        $this->addSql('CREATE UNIQUE INDEX UNIQ_8D93D649F85E0677 ON user (username)');
        $this->addSql('CREATE UNIQUE INDEX UNIQ_8D93D6492125FF59 ON user (student_profile_id)');
        $this->addSql('CREATE UNIQUE INDEX UNIQ_8D93D649430AF9E ON user (tutor_profile_id)');
        $this->addSql('ALTER TABLE user_favorite_games ADD PRIMARY KEY (user_id, game_id)');
        $this->addSql('ALTER TABLE user_favorite_games ADD CONSTRAINT FK_222EDF4CA76ED395 FOREIGN KEY (user_id) REFERENCES user (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE user_favorite_games ADD CONSTRAINT FK_222EDF4CE48FD905 FOREIGN KEY (game_id) REFERENCES game (id) ON DELETE CASCADE');
        $this->addSql('CREATE INDEX IDX_222EDF4CA76ED395 ON user_favorite_games (user_id)');
        $this->addSql('CREATE INDEX IDX_222EDF4CE48FD905 ON user_favorite_games (game_id)');
        $this->addSql('ALTER TABLE user_libraries ADD PRIMARY KEY (id)');
        $this->addSql('ALTER TABLE user_libraries ADD CONSTRAINT FK_3A3DD84D16A2B381 FOREIGN KEY (book_id) REFERENCES books (id)');
        $this->addSql('ALTER TABLE user_libraries ADD CONSTRAINT FK_3A3DD84DA76ED395 FOREIGN KEY (user_id) REFERENCES user (id)');
        $this->addSql('CREATE INDEX IDX_3A3DD84D16A2B381 ON user_libraries (book_id)');
        $this->addSql('CREATE INDEX IDX_3A3DD84DA76ED395 ON user_libraries (user_id)');
        $this->addSql('ALTER TABLE messenger_messages CHANGE delivered_at delivered_at DATETIME DEFAULT NULL, ADD PRIMARY KEY (id)');
        $this->addSql('CREATE INDEX IDX_75EA56E0FB7336F0E3BD61CE16BA31DBBF396750 ON messenger_messages (queue_name, available_at, delivered_at, id)');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE books DROP FOREIGN KEY FK_4A1B2A92A76ED395');
        $this->addSql('DROP INDEX IDX_4A1B2A92A76ED395 ON books');
        $this->addSql('ALTER TABLE books MODIFY id INT NOT NULL');
        $this->addSql('ALTER TABLE books CHANGE price price NUMERIC(10, 2) DEFAULT \'NULL\', CHANGE cover_image cover_image VARCHAR(255) DEFAULT \'NULL\', CHANGE author author VARCHAR(255) DEFAULT \'NULL\', CHANGE isbn isbn VARCHAR(20) DEFAULT \'NULL\', CHANGE published_at published_at DATETIME DEFAULT \'NULL\', CHANGE updated_at updated_at DATETIME DEFAULT \'NULL\', DROP PRIMARY KEY');
        $this->addSql('ALTER TABLE book_library DROP FOREIGN KEY FK_32A0B02A16A2B381');
        $this->addSql('ALTER TABLE book_library DROP FOREIGN KEY FK_32A0B02AFE2541D7');
        $this->addSql('DROP INDEX IDX_32A0B02A16A2B381 ON book_library');
        $this->addSql('DROP INDEX IDX_32A0B02AFE2541D7 ON book_library');
        $this->addSql('ALTER TABLE book_library DROP PRIMARY KEY');
        $this->addSql('ALTER TABLE choice DROP FOREIGN KEY FK_C1AB5A921E27F6BF');
        $this->addSql('DROP INDEX IDX_C1AB5A921E27F6BF ON choice');
        $this->addSql('ALTER TABLE choice MODIFY id INT NOT NULL');
        $this->addSql('ALTER TABLE choice DROP PRIMARY KEY');
        $this->addSql('ALTER TABLE comment DROP FOREIGN KEY FK_9474526C4B89032C');
        $this->addSql('ALTER TABLE comment DROP FOREIGN KEY FK_9474526CF675F31B');
        $this->addSql('DROP INDEX IDX_9474526C4B89032C ON comment');
        $this->addSql('DROP INDEX IDX_9474526CF675F31B ON comment');
        $this->addSql('ALTER TABLE comment MODIFY id INT NOT NULL');
        $this->addSql('ALTER TABLE comment DROP PRIMARY KEY');
        $this->addSql('ALTER TABLE comment_downvoters DROP FOREIGN KEY FK_FFFCACAAF8697D13');
        $this->addSql('ALTER TABLE comment_downvoters DROP FOREIGN KEY FK_FFFCACAAA76ED395');
        $this->addSql('DROP INDEX IDX_FFFCACAAF8697D13 ON comment_downvoters');
        $this->addSql('DROP INDEX IDX_FFFCACAAA76ED395 ON comment_downvoters');
        $this->addSql('ALTER TABLE comment_downvoters DROP PRIMARY KEY');
        $this->addSql('ALTER TABLE comment_upvoters DROP FOREIGN KEY FK_C8E3F802F8697D13');
        $this->addSql('ALTER TABLE comment_upvoters DROP FOREIGN KEY FK_C8E3F802A76ED395');
        $this->addSql('DROP INDEX IDX_C8E3F802F8697D13 ON comment_upvoters');
        $this->addSql('DROP INDEX IDX_C8E3F802A76ED395 ON comment_upvoters');
        $this->addSql('ALTER TABLE comment_upvoters DROP PRIMARY KEY');
        $this->addSql('ALTER TABLE course DROP FOREIGN KEY FK_169E6FB9B03A8386');
        $this->addSql('DROP INDEX IDX_169E6FB9B03A8386 ON course');
        $this->addSql('ALTER TABLE course MODIFY id INT NOT NULL');
        $this->addSql('ALTER TABLE course CHANGE description description VARCHAR(255) DEFAULT \'NULL\', DROP PRIMARY KEY');
        $this->addSql('ALTER TABLE digital_purchases DROP FOREIGN KEY FK_418AF2AA16A2B381');
        $this->addSql('ALTER TABLE digital_purchases DROP FOREIGN KEY FK_418AF2AAA76ED395');
        $this->addSql('DROP INDEX IDX_418AF2AA16A2B381 ON digital_purchases');
        $this->addSql('DROP INDEX IDX_418AF2AAA76ED395 ON digital_purchases');
        $this->addSql('ALTER TABLE digital_purchases MODIFY id INT NOT NULL');
        $this->addSql('ALTER TABLE digital_purchases DROP PRIMARY KEY');
        $this->addSql('ALTER TABLE game DROP FOREIGN KEY FK_232B318CA76ED395');
        $this->addSql('DROP INDEX UNIQ_232B318C5E237E06 ON game');
        $this->addSql('DROP INDEX IDX_232B318CA76ED395 ON game');
        $this->addSql('ALTER TABLE game MODIFY id INT NOT NULL');
        $this->addSql('ALTER TABLE game DROP PRIMARY KEY');
        $this->addSql('ALTER TABLE game_rewards DROP FOREIGN KEY FK_7CE06118E48FD905');
        $this->addSql('ALTER TABLE game_rewards DROP FOREIGN KEY FK_7CE06118E466ACA1');
        $this->addSql('DROP INDEX IDX_7CE06118E48FD905 ON game_rewards');
        $this->addSql('DROP INDEX IDX_7CE06118E466ACA1 ON game_rewards');
        $this->addSql('ALTER TABLE game_rewards DROP PRIMARY KEY');
        $this->addSql('ALTER TABLE libraries MODIFY id INT NOT NULL');
        $this->addSql('ALTER TABLE libraries CHANGE address address VARCHAR(255) DEFAULT \'NULL\', DROP PRIMARY KEY');
        $this->addSql('ALTER TABLE loans DROP FOREIGN KEY FK_82C24DBC16A2B381');
        $this->addSql('ALTER TABLE loans DROP FOREIGN KEY FK_82C24DBCA76ED395');
        $this->addSql('DROP INDEX IDX_82C24DBC16A2B381 ON loans');
        $this->addSql('DROP INDEX IDX_82C24DBCA76ED395 ON loans');
        $this->addSql('ALTER TABLE loans MODIFY id INT NOT NULL');
        $this->addSql('ALTER TABLE loans CHANGE end_at end_at DATETIME DEFAULT \'NULL\', DROP PRIMARY KEY');
        $this->addSql('DROP INDEX IDX_75EA56E0FB7336F0E3BD61CE16BA31DBBF396750 ON messenger_messages');
        $this->addSql('ALTER TABLE messenger_messages MODIFY id BIGINT NOT NULL');
        $this->addSql('ALTER TABLE messenger_messages CHANGE delivered_at delivered_at DATETIME DEFAULT \'NULL\', DROP PRIMARY KEY');
        $this->addSql('ALTER TABLE planning DROP FOREIGN KEY FK_D499BFF6591CC992');
        $this->addSql('DROP INDEX IDX_D499BFF6591CC992 ON planning');
        $this->addSql('ALTER TABLE planning MODIFY id INT NOT NULL');
        $this->addSql('ALTER TABLE planning DROP PRIMARY KEY');
        $this->addSql('ALTER TABLE post DROP FOREIGN KEY FK_5A8A6C8DF675F31B');
        $this->addSql('DROP INDEX IDX_5A8A6C8DF675F31B ON post');
        $this->addSql('ALTER TABLE post MODIFY id INT NOT NULL');
        $this->addSql('ALTER TABLE post DROP PRIMARY KEY');
        $this->addSql('ALTER TABLE post_upvoters DROP FOREIGN KEY FK_6A4A12A54B89032C');
        $this->addSql('ALTER TABLE post_upvoters DROP FOREIGN KEY FK_6A4A12A5A76ED395');
        $this->addSql('DROP INDEX IDX_6A4A12A54B89032C ON post_upvoters');
        $this->addSql('DROP INDEX IDX_6A4A12A5A76ED395 ON post_upvoters');
        $this->addSql('ALTER TABLE post_upvoters DROP PRIMARY KEY');
        $this->addSql('ALTER TABLE question DROP FOREIGN KEY FK_B6F7494E853CD175');
        $this->addSql('ALTER TABLE question DROP FOREIGN KEY FK_B6F7494EA76ED395');
        $this->addSql('DROP INDEX IDX_B6F7494E853CD175 ON question');
        $this->addSql('DROP INDEX IDX_B6F7494EA76ED395 ON question');
        $this->addSql('ALTER TABLE question MODIFY id INT NOT NULL');
        $this->addSql('ALTER TABLE question DROP PRIMARY KEY');
        $this->addSql('ALTER TABLE quiz MODIFY id INT NOT NULL');
        $this->addSql('ALTER TABLE quiz DROP PRIMARY KEY');
        $this->addSql('ALTER TABLE reward MODIFY id INT NOT NULL');
        $this->addSql('ALTER TABLE reward CHANGE icon icon VARCHAR(255) DEFAULT \'NULL\', DROP PRIMARY KEY');
        $this->addSql('ALTER TABLE student_earned_rewards DROP FOREIGN KEY FK_199824092125FF59');
        $this->addSql('ALTER TABLE student_earned_rewards DROP FOREIGN KEY FK_19982409E466ACA1');
        $this->addSql('DROP INDEX IDX_199824092125FF59 ON student_earned_rewards');
        $this->addSql('DROP INDEX IDX_19982409E466ACA1 ON student_earned_rewards');
        $this->addSql('ALTER TABLE student_earned_rewards DROP PRIMARY KEY');
        $this->addSql('DROP INDEX UNIQ_6C611FF7E7927C74 ON student_profile');
        $this->addSql('ALTER TABLE student_profile MODIFY id INT NOT NULL');
        $this->addSql('ALTER TABLE student_profile CHANGE email email VARCHAR(180) DEFAULT \'NULL\', CHANGE university university VARCHAR(100) DEFAULT \'NULL\', CHANGE major major VARCHAR(100) DEFAULT \'NULL\', CHANGE academic_level academic_level VARCHAR(50) DEFAULT \'NULL\', CHANGE profile_picture profile_picture VARCHAR(255) DEFAULT \'NULL\', CHANGE interests interests LONGTEXT DEFAULT NULL COLLATE `utf8mb4_bin`, DROP PRIMARY KEY');
        $this->addSql('ALTER TABLE study_session DROP FOREIGN KEY FK_E55128B6A76ED395');
        $this->addSql('ALTER TABLE study_session DROP FOREIGN KEY FK_E55128B63D865311');
        $this->addSql('DROP INDEX IDX_E55128B6A76ED395 ON study_session');
        $this->addSql('DROP INDEX IDX_E55128B63D865311 ON study_session');
        $this->addSql('ALTER TABLE study_session MODIFY id INT NOT NULL');
        $this->addSql('ALTER TABLE study_session CHANGE ended_at ended_at DATETIME DEFAULT \'NULL\', CHANGE completed_at completed_at DATETIME DEFAULT \'NULL\', DROP PRIMARY KEY');
        $this->addSql('ALTER TABLE tutor_profile MODIFY id INT NOT NULL');
        $this->addSql('ALTER TABLE tutor_profile CHANGE expertise expertise LONGTEXT DEFAULT NULL COLLATE `utf8mb4_bin`, CHANGE hourly_rate hourly_rate NUMERIC(10, 2) DEFAULT \'NULL\', CHANGE profile_picture profile_picture VARCHAR(255) DEFAULT \'NULL\', DROP PRIMARY KEY');
        $this->addSql('ALTER TABLE user DROP FOREIGN KEY FK_8D93D6492125FF59');
        $this->addSql('ALTER TABLE user DROP FOREIGN KEY FK_8D93D649430AF9E');
        $this->addSql('DROP INDEX UNIQ_8D93D649E7927C74 ON user');
        $this->addSql('DROP INDEX UNIQ_8D93D649F85E0677 ON user');
        $this->addSql('DROP INDEX UNIQ_8D93D6492125FF59 ON user');
        $this->addSql('DROP INDEX UNIQ_8D93D649430AF9E ON user');
        $this->addSql('ALTER TABLE user MODIFY id INT NOT NULL');
        $this->addSql('ALTER TABLE user CHANGE verification_token verification_token VARCHAR(255) DEFAULT \'NULL\', CHANGE verification_token_expires_at verification_token_expires_at DATETIME DEFAULT \'NULL\', DROP PRIMARY KEY');
        $this->addSql('ALTER TABLE user_favorite_games DROP FOREIGN KEY FK_222EDF4CA76ED395');
        $this->addSql('ALTER TABLE user_favorite_games DROP FOREIGN KEY FK_222EDF4CE48FD905');
        $this->addSql('DROP INDEX IDX_222EDF4CA76ED395 ON user_favorite_games');
        $this->addSql('DROP INDEX IDX_222EDF4CE48FD905 ON user_favorite_games');
        $this->addSql('ALTER TABLE user_favorite_games DROP PRIMARY KEY');
        $this->addSql('ALTER TABLE user_libraries DROP FOREIGN KEY FK_3A3DD84D16A2B381');
        $this->addSql('ALTER TABLE user_libraries DROP FOREIGN KEY FK_3A3DD84DA76ED395');
        $this->addSql('DROP INDEX IDX_3A3DD84D16A2B381 ON user_libraries');
        $this->addSql('DROP INDEX IDX_3A3DD84DA76ED395 ON user_libraries');
        $this->addSql('ALTER TABLE user_libraries MODIFY id INT NOT NULL');
        $this->addSql('ALTER TABLE user_libraries DROP PRIMARY KEY');
    }
}
