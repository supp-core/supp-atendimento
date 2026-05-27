<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20260527100100 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'RF-002: Add project_id to service table';
    }

    public function up(Schema $schema): void
    {
        $this->addSql('ALTER TABLE service ADD COLUMN project_id INT DEFAULT NULL');

        $this->addSql('
            ALTER TABLE service
                ADD CONSTRAINT fk_service_project
                FOREIGN KEY (project_id) REFERENCES project(id)
                ON DELETE SET NULL
        ');

        $this->addSql('CREATE INDEX idx_service_project ON service(project_id)');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('ALTER TABLE service DROP CONSTRAINT IF EXISTS fk_service_project');
        $this->addSql('DROP INDEX IF EXISTS idx_service_project');
        $this->addSql('ALTER TABLE service DROP COLUMN IF EXISTS project_id');
    }
}
