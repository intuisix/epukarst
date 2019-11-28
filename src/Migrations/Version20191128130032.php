<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20191128130032 extends AbstractMigration
{
    public function getDescription() : string
    {
        return '';
    }

    public function up(Schema $schema) : void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'postgresql', 'Migration can only be executed safely on \'postgresql\'.');

        $this->addSql('CREATE SEQUENCE station_kind_id_seq INCREMENT BY 1 MINVALUE 1 START 1');
        $this->addSql('CREATE TABLE station_kind (id INT NOT NULL, name VARCHAR(255) NOT NULL, PRIMARY KEY(id))');
        $this->addSql('ALTER TABLE station ADD kind_id INT DEFAULT NULL');
        $this->addSql('ALTER TABLE station DROP kind');
        $this->addSql('ALTER TABLE station ADD CONSTRAINT FK_9F39F8B130602CA9 FOREIGN KEY (kind_id) REFERENCES station_kind (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('CREATE INDEX IDX_9F39F8B130602CA9 ON station (kind_id)');
    }

    public function down(Schema $schema) : void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'postgresql', 'Migration can only be executed safely on \'postgresql\'.');

        $this->addSql('CREATE SCHEMA public');
        $this->addSql('ALTER TABLE station DROP CONSTRAINT FK_9F39F8B130602CA9');
        $this->addSql('DROP SEQUENCE station_kind_id_seq CASCADE');
        $this->addSql('DROP TABLE station_kind');
        $this->addSql('DROP INDEX IDX_9F39F8B130602CA9');
        $this->addSql('ALTER TABLE station ADD kind VARCHAR(255) NOT NULL');
        $this->addSql('ALTER TABLE station DROP kind_id');
    }
}
