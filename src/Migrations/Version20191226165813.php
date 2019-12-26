<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20191226165813 extends AbstractMigration
{
    public function getDescription() : string
    {
        return '';
    }

    public function up(Schema $schema) : void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'postgresql', 'Migration can only be executed safely on \'postgresql\'.');

        $this->addSql('CREATE SEQUENCE alarm_kind_id_seq INCREMENT BY 1 MINVALUE 1 START 1');
        $this->addSql('CREATE TABLE alarm_kind (id INT NOT NULL, name VARCHAR(255) NOT NULL, description TEXT DEFAULT NULL, PRIMARY KEY(id))');
        $this->addSql('ALTER TABLE alarm ADD kind_id INT DEFAULT NULL');
        $this->addSql('ALTER TABLE alarm ADD CONSTRAINT FK_749F46DD30602CA9 FOREIGN KEY (kind_id) REFERENCES alarm_kind (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('CREATE INDEX IDX_749F46DD30602CA9 ON alarm (kind_id)');
    }

    public function down(Schema $schema) : void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'postgresql', 'Migration can only be executed safely on \'postgresql\'.');

        $this->addSql('CREATE SCHEMA public');
        $this->addSql('ALTER TABLE alarm DROP CONSTRAINT FK_749F46DD30602CA9');
        $this->addSql('DROP SEQUENCE alarm_kind_id_seq CASCADE');
        $this->addSql('DROP TABLE alarm_kind');
        $this->addSql('DROP INDEX IDX_749F46DD30602CA9');
        $this->addSql('ALTER TABLE alarm DROP kind_id');
    }
}
