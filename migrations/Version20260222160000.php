<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20260222160000 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Make study_session_id nullable in resource table to support course-level resources';
    }

    public function up(Schema $schema): void
    {
        // Make study_session_id nullable
        $this->addSql('ALTER TABLE resource MODIFY study_session_id INT DEFAULT NULL');
    }

    public function down(Schema $schema): void
    {
        // Revert to NOT NULL (this might fail if there are NULL values)
        $this->addSql('ALTER TABLE resource MODIFY study_session_id INT NOT NULL');
    }
}
