<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20250405154207 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE SEQUENCE category_id_seq INCREMENT BY 1 MINVALUE 1 START 1');
        $this->addSql('CREATE SEQUENCE service_type_id_seq INCREMENT BY 1 MINVALUE 1 START 1');
        $this->addSql('CREATE TABLE category (id INT NOT NULL, name VARCHAR(50) NOT NULL, description VARCHAR(255) DEFAULT NULL, PRIMARY KEY(id))');
        $this->addSql('CREATE TABLE service_type (id INT NOT NULL, name VARCHAR(50) NOT NULL, description VARCHAR(255) DEFAULT NULL, PRIMARY KEY(id))');
        $this->addSql('ALTER TABLE service ADD category_id INT DEFAULT NULL');
        $this->addSql('ALTER TABLE service ADD service_type_id INT DEFAULT NULL');
        $this->addSql('ALTER TABLE service ADD CONSTRAINT FK_E19D9AD212469DE2 FOREIGN KEY (category_id) REFERENCES category (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE service ADD CONSTRAINT FK_E19D9AD2AC8DE0F FOREIGN KEY (service_type_id) REFERENCES service_type (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('CREATE INDEX IDX_E19D9AD212469DE2 ON service (category_id)');
        $this->addSql('CREATE INDEX IDX_E19D9AD2AC8DE0F ON service (service_type_id)');

          // Inserir categorias
          $this->addSql("INSERT INTO category (id, name, description) VALUES (nextval('category_id_seq'), 'SUPP', 'Relacionado ao SUPP')");
          $this->addSql("INSERT INTO category (id, name, description) VALUES (nextval('category_id_seq'), 'Geral', 'Atendimentos gerais')");

             // Inserir tipos de serviço
        $this->addSql("INSERT INTO service_type (id, name, description) VALUES (nextval('service_type_id_seq'), 'Bug', 'Correção de bug no sistema')");
        $this->addSql("INSERT INTO service_type (id, name, description) VALUES (nextval('service_type_id_seq'), 'Implantação', 'Implantação de nova funcionalidade')");
        $this->addSql("INSERT INTO service_type (id, name, description) VALUES (nextval('service_type_id_seq'), 'Solicitação', 'Solicitação de serviço')");
        $this->addSql("INSERT INTO service_type (id, name, description) VALUES (nextval('service_type_id_seq'), 'Email', 'Problema relacionado a e-mail')");
        $this->addSql("INSERT INTO service_type (id, name, description) VALUES (nextval('service_type_id_seq'), 'Técnico', 'Atendimento técnico')");
        $this->addSql("INSERT INTO service_type (id, name, description) VALUES (nextval('service_type_id_seq'), 'Outros', 'Outros tipos de atendimento')");

  
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE SCHEMA public');
        $this->addSql('ALTER TABLE service DROP CONSTRAINT FK_E19D9AD212469DE2');
        $this->addSql('ALTER TABLE service DROP CONSTRAINT FK_E19D9AD2AC8DE0F');
        $this->addSql('DROP SEQUENCE category_id_seq CASCADE');
        $this->addSql('DROP SEQUENCE service_type_id_seq CASCADE');
        $this->addSql('DROP TABLE category');
        $this->addSql('DROP TABLE service_type');
        $this->addSql('DROP INDEX IDX_E19D9AD212469DE2');
        $this->addSql('DROP INDEX IDX_E19D9AD2AC8DE0F');
        $this->addSql('ALTER TABLE service DROP category_id');
        $this->addSql('ALTER TABLE service DROP service_type_id');
    }
}
