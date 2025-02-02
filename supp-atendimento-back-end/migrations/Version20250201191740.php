<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20250201191740 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
       // 1. Adicionar campo user_id na tabela attendant
       $this->addSql('ALTER TABLE attendant ADD user_id INT DEFAULT NULL');
        
       // Criar a chave estrangeira
       $this->addSql('ALTER TABLE attendant 
           ADD CONSTRAINT FK_ATTENDANT_USER 
           FOREIGN KEY (user_id) 
           REFERENCES "user" (id) 
           ON DELETE SET NULL');

       // 2. Remover campos password e email da tabela attendant
       $this->addSql('ALTER TABLE attendant DROP COLUMN email');
       $this->addSql('ALTER TABLE attendant DROP COLUMN password');

       // 3. Adicionar campo is_attendant na tabela user
       $this->addSql('ALTER TABLE "user" ADD is_attendant BOOLEAN DEFAULT false NOT NULL');

       // 4. Avaliar campo roles na tabela attendant
       // Se o campo roles não for mais necessário, remova-o
       $this->addSql('ALTER TABLE attendant DROP COLUMN roles');

    }

    public function down(Schema $schema): void
    {
        // Método de rollback para reverter as alterações
        $this->addSql('ALTER TABLE attendant DROP CONSTRAINT FK_ATTENDANT_USER');
        $this->addSql('ALTER TABLE attendant DROP COLUMN user_id');
        
        // Recriar campos removidos
        $this->addSql('ALTER TABLE attendant 
            ADD email VARCHAR(180) NOT NULL, 
            ADD password VARCHAR(255) NOT NULL,
            ADD roles JSON DEFAULT NULL');
        
        // Remover o campo is_attendant da tabela user
        $this->addSql('ALTER TABLE "user" DROP COLUMN is_attendant');
    }
}
