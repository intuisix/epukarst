<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20200125202555 extends AbstractMigration
{
    public function getDescription() : string
    {
        return '';
    }

    public function up(Schema $schema) : void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'postgresql', 'Migration can only be executed safely on \'postgresql\'.');

        $this->addSql('CREATE SEQUENCE control_id_seq INCREMENT BY 1 MINVALUE 1 START 1');
        $this->addSql('CREATE TABLE control (id INT NOT NULL, system_reading_id INT DEFAULT NULL, instrument_parameter_id INT NOT NULL, date_time TIMESTAMP(0) WITHOUT TIME ZONE NOT NULL, value DOUBLE PRECISION NOT NULL, PRIMARY KEY(id))');
        $this->addSql('CREATE INDEX IDX_EDDB2C4BE7A165F4 ON control (system_reading_id)');
        $this->addSql('CREATE INDEX IDX_EDDB2C4BC386750D ON control (instrument_parameter_id)');
        $this->addSql('ALTER TABLE control ADD CONSTRAINT FK_EDDB2C4BE7A165F4 FOREIGN KEY (system_reading_id) REFERENCES system_reading (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE control ADD CONSTRAINT FK_EDDB2C4BC386750D FOREIGN KEY (instrument_parameter_id) REFERENCES measurability (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
    }

    public function down(Schema $schema) : void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'postgresql', 'Migration can only be executed safely on \'postgresql\'.');

        $this->addSql('CREATE SCHEMA public');
        $this->addSql('DROP SEQUENCE control_id_seq CASCADE');
        $this->addSql('DROP TABLE control');
    }
}
