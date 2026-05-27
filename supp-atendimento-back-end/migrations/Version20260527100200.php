<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20260527100200 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'RF-003: Create service_attendant table and expand service_history';
    }

    public function up(Schema $schema): void
    {
        // Tabela pivot service_attendant
        $this->addSql('CREATE SEQUENCE service_attendant_id_seq INCREMENT BY 1 MINVALUE 1 START 1');

        $this->addSql('
            CREATE TABLE service_attendant (
                id            INT        NOT NULL DEFAULT nextval(\'service_attendant_id_seq\'),
                service_id    INT        NOT NULL,
                attendant_id  INT        NOT NULL,
                assigned_by   INT        NOT NULL,
                assigned_at   TIMESTAMP  NOT NULL DEFAULT NOW(),
                PRIMARY KEY (id),
                CONSTRAINT uq_service_attendant UNIQUE (service_id, attendant_id),
                CONSTRAINT fk_sa_service    FOREIGN KEY (service_id)   REFERENCES service(id)   ON DELETE CASCADE,
                CONSTRAINT fk_sa_attendant  FOREIGN KEY (attendant_id) REFERENCES attendant(id),
                CONSTRAINT fk_sa_assigned   FOREIGN KEY (assigned_by)  REFERENCES attendant(id)
            )
        ');

        $this->addSql('CREATE INDEX idx_sa_service ON service_attendant(service_id)');
        $this->addSql('CREATE INDEX idx_sa_attendant ON service_attendant(attendant_id)');

        // Expandir service_history
        $this->addSql("ALTER TABLE service_history ADD COLUMN type VARCHAR(30) NOT NULL DEFAULT 'STATUS_CHANGE'");
        $this->addSql('ALTER TABLE service_history ADD COLUMN attendant_id_evolution INT DEFAULT NULL');

        $this->addSql('
            ALTER TABLE service_history
                ADD CONSTRAINT fk_sh_evolution_attendant
                FOREIGN KEY (attendant_id_evolution) REFERENCES attendant(id) ON DELETE SET NULL
        ');

        $this->addSql('CREATE INDEX idx_sh_type ON service_history(type)');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('DROP TABLE IF EXISTS service_attendant');
        $this->addSql('DROP SEQUENCE IF EXISTS service_attendant_id_seq');

        $this->addSql('ALTER TABLE service_history DROP CONSTRAINT IF EXISTS fk_sh_evolution_attendant');
        $this->addSql('DROP INDEX IF EXISTS idx_sh_type');
        $this->addSql('ALTER TABLE service_history DROP COLUMN IF EXISTS type');
        $this->addSql('ALTER TABLE service_history DROP COLUMN IF EXISTS attendant_id_evolution');
    }
}
