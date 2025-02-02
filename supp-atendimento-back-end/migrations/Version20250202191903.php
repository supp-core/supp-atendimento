<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20250202191903 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
       $this->addSql('ALTER TABLE service DROP CONSTRAINT IF EXISTS service_priority_check');
        $this->addSql("ALTER TABLE service ADD CONSTRAINT service_priority_check CHECK (priority IN ('BAIXA', 'NORMAL', 'ALTA', 'URGENTE'))");
    }

    public function down(Schema $schema): void
    {
        $this->addSql('ALTER TABLE service DROP CONSTRAINT IF EXISTS service_priority_check');
        $this->addSql("ALTER TABLE service ADD CONSTRAINT service_priority_check CHECK (priority = 'NORMAL')");

    }
}
