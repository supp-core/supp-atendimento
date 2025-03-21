<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20250320144844 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE SEQUENCE service_attachment_id_seq INCREMENT BY 1 MINVALUE 1 START 1');
        $this->addSql('CREATE TABLE service_attachment (id INT NOT NULL, service_id INT NOT NULL, filename VARCHAR(255) NOT NULL, original_filename VARCHAR(255) NOT NULL, mime_type VARCHAR(50) NOT NULL, file_size INT NOT NULL, PRIMARY KEY(id))');
        $this->addSql('CREATE INDEX IDX_EF0EE00FED5CA9E6 ON service_attachment (service_id)');
        $this->addSql('ALTER TABLE service_attachment ADD CONSTRAINT FK_EF0EE00FED5CA9E6 FOREIGN KEY (service_id) REFERENCES service (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE attendant DROP CONSTRAINT fk_attendant_user');
       
        $this->addSql('ALTER TABLE attendant ALTER user_id SET NOT NULL');
        $this->addSql('ALTER TABLE attendant ADD CONSTRAINT FK_B2508F91A76ED395 FOREIGN KEY (user_id) REFERENCES "user" (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('CREATE UNIQUE INDEX UNIQ_B2508F91A76ED395 ON attendant (user_id)');
       # $this->addSql('ALTER TABLE service ADD created_by_admin_id INT DEFAULT NULL');
       # $this->addSql('ALTER TABLE service ADD created_by_admin BOOLEAN DEFAULT false NOT NULL');
       # $this->addSql('ALTER TABLE service ADD CONSTRAINT FK_E19D9AD264F1F4EE FOREIGN KEY (created_by_admin_id) REFERENCES attendant (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
       # $this->addSql('CREATE INDEX IDX_E19D9AD264F1F4EE ON service (created_by_admin_id)');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE SCHEMA public');
        $this->addSql('DROP SEQUENCE service_attachment_id_seq CASCADE');
        $this->addSql('ALTER TABLE service_attachment DROP CONSTRAINT FK_EF0EE00FED5CA9E6');
        $this->addSql('DROP TABLE service_attachment');
        $this->addSql('ALTER TABLE attendant DROP CONSTRAINT FK_B2508F91A76ED395');
        $this->addSql('DROP INDEX UNIQ_B2508F91A76ED395');
        $this->addSql('ALTER TABLE attendant ALTER user_id DROP NOT NULL');
        $this->addSql('ALTER TABLE attendant ADD CONSTRAINT fk_attendant_user FOREIGN KEY (user_id) REFERENCES "user" (id) ON DELETE SET NULL NOT DEFERRABLE INITIALLY IMMEDIATE');
      
        #$this->addSql('ALTER TABLE service DROP CONSTRAINT FK_E19D9AD264F1F4EE');
        #$this->addSql('DROP INDEX IDX_E19D9AD264F1F4EE');
        #$this->addSql('ALTER TABLE service DROP created_by_admin_id');
        #'$this->addSql('ALTER TABLE service DROP created_by_admin');
    }
}
