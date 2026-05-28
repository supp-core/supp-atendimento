<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20260528000000 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Rename attendant_id_evolution to attendant_evolution_id in service_history';
    }

    public function up(Schema $schema): void
    {
        $this->addSql('ALTER TABLE service_history RENAME COLUMN attendant_id_evolution TO attendant_evolution_id');
        $this->addSql('ALTER TABLE service_history ADD CONSTRAINT FK_E83E22D7BEB16F0A FOREIGN KEY (attendant_evolution_id) REFERENCES attendant (id) ON DELETE SET NULL NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('CREATE INDEX IDX_E83E22D7BEB16F0A ON service_history (attendant_evolution_id)');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('DROP INDEX IDX_E83E22D7BEB16F0A');
        $this->addSql('ALTER TABLE service_history DROP CONSTRAINT FK_E83E22D7BEB16F0A');
        $this->addSql('ALTER TABLE service_history RENAME COLUMN attendant_evolution_id TO attendant_id_evolution');
    }
}
