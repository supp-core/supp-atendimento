<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20250119110052 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        $this->addSql('ALTER TABLE service ADD priority VARCHAR(20) DEFAULT \'NORMAL\' NOT NULL');
        // Add a check constraint to ensure only valid priorities are entered
        $this->addSql('ALTER TABLE service ADD CONSTRAINT service_priority_check CHECK (priority IN (\'LOW\', \'NORMAL\', \'HIGH\', \'URGENT\'))');


    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE SCHEMA public');
    }
}
