<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20191129123826 extends AbstractMigration
{
    public function getDescription() : string
    {
        return '';
    }

    public function up(Schema $schema) : void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'postgresql', 'Migration can only be executed safely on \'postgresql\'.');

        $this->addSql('UPDATE post SET parent_id = NULL');
        $this->addSql('CREATE SEQUENCE menu_id_seq INCREMENT BY 1 MINVALUE 1 START 1');
        $this->addSql('CREATE TABLE menu (id INT NOT NULL, title VARCHAR(255) NOT NULL, order_number INT DEFAULT NULL, PRIMARY KEY(id))');
        $this->addSql('ALTER TABLE post DROP CONSTRAINT fk_5a8a6c8d727aca70');
        $this->addSql('DROP INDEX idx_5a8a6c8d727aca70');
        $this->addSql('ALTER TABLE post RENAME COLUMN parent_id TO menu_id');
        $this->addSql('ALTER TABLE post ADD CONSTRAINT FK_5A8A6C8DCCD7E912 FOREIGN KEY (menu_id) REFERENCES menu (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('CREATE INDEX IDX_5A8A6C8DCCD7E912 ON post (menu_id)');
    }

    public function down(Schema $schema) : void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'postgresql', 'Migration can only be executed safely on \'postgresql\'.');

        $this->addSql('CREATE SCHEMA public');
        $this->addSql('ALTER TABLE post DROP CONSTRAINT FK_5A8A6C8DCCD7E912');
        $this->addSql('DROP SEQUENCE menu_id_seq CASCADE');
        $this->addSql('DROP TABLE menu');
        $this->addSql('DROP INDEX IDX_5A8A6C8DCCD7E912');
        $this->addSql('ALTER TABLE post RENAME COLUMN menu_id TO parent_id');
        $this->addSql('ALTER TABLE post ADD CONSTRAINT fk_5a8a6c8d727aca70 FOREIGN KEY (parent_id) REFERENCES post (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('CREATE INDEX idx_5a8a6c8d727aca70 ON post (parent_id)');
    }
}
