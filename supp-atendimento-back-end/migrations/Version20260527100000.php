<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20260527100000 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'RF-001: Create project table with SUPP and LJPGM fixtures';
    }

    public function up(Schema $schema): void
    {
        $this->addSql('CREATE SEQUENCE project_id_seq INCREMENT BY 1 MINVALUE 1 START 1');

        $this->addSql('
            CREATE TABLE project (
                id          INT           NOT NULL DEFAULT nextval(\'project_id_seq\'),
                created_by  INT           NOT NULL,
                name        VARCHAR(100)  NOT NULL,
                acronym     VARCHAR(20)   NOT NULL,
                description TEXT,
                status      VARCHAR(20)   NOT NULL DEFAULT \'ATIVO\',
                date_start  DATE          NOT NULL,
                date_end    DATE,
                created_at  TIMESTAMP     NOT NULL DEFAULT NOW(),
                PRIMARY KEY (id),
                CONSTRAINT uq_project_name    UNIQUE (name),
                CONSTRAINT uq_project_acronym UNIQUE (acronym),
                CONSTRAINT fk_project_attendant FOREIGN KEY (created_by) REFERENCES attendant(id)
            )
        ');

        $this->addSql('CREATE INDEX idx_project_status ON project(status)');

        // Fixtures: SUPP e LJPGM — resolve o id do primeiro admin dinamicamente
        $this->addSql("
            INSERT INTO project (created_by, name, acronym, description, status, date_start, created_at)
            SELECT a.id, 'SUPP', 'SUPP', 'Sistema de Suporte da Procuradoria', 'ATIVO', '2024-01-01', NOW()
            FROM attendant a WHERE a.function = 'Admin' ORDER BY a.id LIMIT 1
            ON CONFLICT DO NOTHING
        ");

        $this->addSql("
            INSERT INTO project (created_by, name, acronym, description, status, date_start, created_at)
            SELECT a.id, 'Sistema LJPGM', 'LJPGM', 'Legislação e Jurisprudência da Procuradoria Geral do Município', 'ATIVO', '2024-01-01', NOW()
            FROM attendant a WHERE a.function = 'Admin' ORDER BY a.id LIMIT 1
            ON CONFLICT DO NOTHING
        ");
    }

    public function down(Schema $schema): void
    {
        $this->addSql('DROP TABLE IF EXISTS project');
        $this->addSql('DROP SEQUENCE IF EXISTS project_id_seq');
    }
}
