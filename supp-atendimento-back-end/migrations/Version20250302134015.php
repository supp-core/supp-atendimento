<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20250302134015 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE service ADD created_by_admin_id INT DEFAULT NULL');
        $this->addSql('ALTER TABLE service ADD created_by_admin BOOLEAN DEFAULT false NOT NULL');
        $this->addSql('ALTER TABLE service ADD CONSTRAINT FK_E19D9AD264F1F4EE FOREIGN KEY (created_by_admin_id) REFERENCES attendant (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('CREATE INDEX IDX_E19D9AD264F1F4EE ON service (created_by_admin_id)');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE SCHEMA public');
        $this->addSql('ALTER TABLE service DROP CONSTRAINT FK_E19D9AD264F1F4EE');
        $this->addSql('DROP INDEX IDX_E19D9AD264F1F4EE');
        $this->addSql('ALTER TABLE service DROP created_by_admin_id');
        $this->addSql('ALTER TABLE service DROP created_by_admin');
    }
}
