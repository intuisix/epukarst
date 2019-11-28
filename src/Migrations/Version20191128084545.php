<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20191128084545 extends AbstractMigration
{
    public function getDescription() : string
    {
        return '';
    }

    public function up(Schema $schema) : void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'postgresql', 'Migration can only be executed safely on \'postgresql\'.');

        $this->addSql('CREATE SEQUENCE system_parameter_id_seq INCREMENT BY 1 MINVALUE 1 START 1');
        $this->addSql('CREATE TABLE system_parameter (id INT NOT NULL, system_id INT NOT NULL, instrument_parameter_id INT NOT NULL, notes TEXT DEFAULT NULL, PRIMARY KEY(id))');
        $this->addSql('CREATE INDEX IDX_294899ADD0952FA5 ON system_parameter (system_id)');
        $this->addSql('CREATE INDEX IDX_294899ADC386750D ON system_parameter (instrument_parameter_id)');
        $this->addSql('ALTER TABLE system_parameter ADD CONSTRAINT FK_294899ADD0952FA5 FOREIGN KEY (system_id) REFERENCES system (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE system_parameter ADD CONSTRAINT FK_294899ADC386750D FOREIGN KEY (instrument_parameter_id) REFERENCES measurability (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
    }

    public function down(Schema $schema) : void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'postgresql', 'Migration can only be executed safely on \'postgresql\'.');

        $this->addSql('CREATE SCHEMA public');
        $this->addSql('DROP SEQUENCE system_parameter_id_seq CASCADE');
        $this->addSql('DROP TABLE system_parameter');
    }
}
