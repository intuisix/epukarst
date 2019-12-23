<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20191223203230 extends AbstractMigration
{
    public function getDescription() : string
    {
        return '';
    }

    public function up(Schema $schema) : void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'postgresql', 'Migration can only be executed safely on \'postgresql\'.');

        $this->addSql('CREATE SEQUENCE system_role_id_seq INCREMENT BY 1 MINVALUE 1 START 1');
        $this->addSql('CREATE TABLE system_role (id INT NOT NULL, system_id INT NOT NULL, author_id INT NOT NULL, can_view BOOLEAN DEFAULT NULL, can_encode BOOLEAN DEFAULT NULL, can_validate BOOLEAN DEFAULT NULL, can_export BOOLEAN DEFAULT NULL, can_delete BOOLEAN DEFAULT NULL, PRIMARY KEY(id))');
        $this->addSql('CREATE INDEX IDX_46A5399CD0952FA5 ON system_role (system_id)');
        $this->addSql('CREATE INDEX IDX_46A5399CF675F31B ON system_role (author_id)');
        $this->addSql('ALTER TABLE system_role ADD CONSTRAINT FK_46A5399CD0952FA5 FOREIGN KEY (system_id) REFERENCES system (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE system_role ADD CONSTRAINT FK_46A5399CF675F31B FOREIGN KEY (author_id) REFERENCES user_account (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
    }

    public function down(Schema $schema) : void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'postgresql', 'Migration can only be executed safely on \'postgresql\'.');

        $this->addSql('CREATE SCHEMA public');
        $this->addSql('DROP SEQUENCE system_role_id_seq CASCADE');
        $this->addSql('DROP TABLE system_role');
    }
}
