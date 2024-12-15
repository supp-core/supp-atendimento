<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20241214153519 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE SEQUENCE attendant_id_seq INCREMENT BY 1 MINVALUE 1 START 1');
        $this->addSql('CREATE SEQUENCE sector_id_seq INCREMENT BY 1 MINVALUE 1 START 1');
        $this->addSql('CREATE SEQUENCE service_id_seq INCREMENT BY 1 MINVALUE 1 START 1');
        $this->addSql('CREATE SEQUENCE service_history_id_seq INCREMENT BY 1 MINVALUE 1 START 1');
        $this->addSql('CREATE SEQUENCE "user_id_seq" INCREMENT BY 1 MINVALUE 1 START 1');
        $this->addSql('CREATE TABLE attendant (id INT NOT NULL, sector_id INT DEFAULT NULL, name VARCHAR(100) NOT NULL, status VARCHAR(30) NOT NULL, function VARCHAR(100) NOT NULL, email VARCHAR(180) NOT NULL, password VARCHAR(255) NOT NULL, roles JSON NOT NULL, PRIMARY KEY(id))');
        $this->addSql('CREATE UNIQUE INDEX UNIQ_B2508F91E7927C74 ON attendant (email)');
        $this->addSql('CREATE INDEX IDX_B2508F91DE95C867 ON attendant (sector_id)');
        $this->addSql('CREATE TABLE sector (id INT NOT NULL, name VARCHAR(20) NOT NULL, PRIMARY KEY(id))');
        $this->addSql('CREATE TABLE service (id INT NOT NULL, sector_id INT NOT NULL, requester_id INT DEFAULT NULL, reponsible_id INT DEFAULT NULL, title VARCHAR(100) NOT NULL, description TEXT NOT NULL, status VARCHAR(30) NOT NULL, date_create TIMESTAMP(0) WITHOUT TIME ZONE NOT NULL, date_update TIMESTAMP(0) WITHOUT TIME ZONE DEFAULT NULL, date_conclusion TIMESTAMP(0) WITHOUT TIME ZONE DEFAULT NULL, PRIMARY KEY(id))');
        $this->addSql('CREATE INDEX IDX_E19D9AD2DE95C867 ON service (sector_id)');
        $this->addSql('CREATE INDEX IDX_E19D9AD2ED442CF4 ON service (requester_id)');
        $this->addSql('CREATE INDEX IDX_E19D9AD29FB57326 ON service (reponsible_id)');
        $this->addSql('CREATE TABLE service_history (id INT NOT NULL, service_id INT NOT NULL, responsible_id INT DEFAULT NULL, comment TEXT DEFAULT NULL, date_history TIMESTAMP(0) WITHOUT TIME ZONE NOT NULL, status_prev VARCHAR(30) NOT NULL, status_post VARCHAR(30) DEFAULT NULL, PRIMARY KEY(id))');
        $this->addSql('CREATE INDEX IDX_E83E22D7ED5CA9E6 ON service_history (service_id)');
        $this->addSql('CREATE INDEX IDX_E83E22D7602AD315 ON service_history (responsible_id)');
        $this->addSql('CREATE TABLE "user" (id INT NOT NULL, name VARCHAR(50) NOT NULL, email VARCHAR(100) NOT NULL, password VARCHAR(255) NOT NULL, roles JSON NOT NULL, PRIMARY KEY(id))');
        $this->addSql('CREATE UNIQUE INDEX UNIQ_8D93D649E7927C74 ON "user" (email)');
        $this->addSql('ALTER TABLE attendant ADD CONSTRAINT FK_B2508F91DE95C867 FOREIGN KEY (sector_id) REFERENCES sector (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE service ADD CONSTRAINT FK_E19D9AD2DE95C867 FOREIGN KEY (sector_id) REFERENCES sector (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE service ADD CONSTRAINT FK_E19D9AD2ED442CF4 FOREIGN KEY (requester_id) REFERENCES "user" (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE service ADD CONSTRAINT FK_E19D9AD29FB57326 FOREIGN KEY (reponsible_id) REFERENCES attendant (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE service_history ADD CONSTRAINT FK_E83E22D7ED5CA9E6 FOREIGN KEY (service_id) REFERENCES service (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE service_history ADD CONSTRAINT FK_E83E22D7602AD315 FOREIGN KEY (responsible_id) REFERENCES attendant (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE SCHEMA public');
        $this->addSql('DROP SEQUENCE attendant_id_seq CASCADE');
        $this->addSql('DROP SEQUENCE sector_id_seq CASCADE');
        $this->addSql('DROP SEQUENCE service_id_seq CASCADE');
        $this->addSql('DROP SEQUENCE service_history_id_seq CASCADE');
        $this->addSql('DROP SEQUENCE "user_id_seq" CASCADE');
        $this->addSql('ALTER TABLE attendant DROP CONSTRAINT FK_B2508F91DE95C867');
        $this->addSql('ALTER TABLE service DROP CONSTRAINT FK_E19D9AD2DE95C867');
        $this->addSql('ALTER TABLE service DROP CONSTRAINT FK_E19D9AD2ED442CF4');
        $this->addSql('ALTER TABLE service DROP CONSTRAINT FK_E19D9AD29FB57326');
        $this->addSql('ALTER TABLE service_history DROP CONSTRAINT FK_E83E22D7ED5CA9E6');
        $this->addSql('ALTER TABLE service_history DROP CONSTRAINT FK_E83E22D7602AD315');
        $this->addSql('DROP TABLE attendant');
        $this->addSql('DROP TABLE sector');
        $this->addSql('DROP TABLE service');
        $this->addSql('DROP TABLE service_history');
        $this->addSql('DROP TABLE "user"');
    }
}
