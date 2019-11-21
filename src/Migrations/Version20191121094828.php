<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20191121094828 extends AbstractMigration
{
    public function getDescription() : string
    {
        return '';
    }

    public function up(Schema $schema) : void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'postgresql', 'Migration can only be executed safely on \'postgresql\'.');

        $this->addSql('CREATE SEQUENCE filter_measure_id_seq INCREMENT BY 1 MINVALUE 1 START 1');
        $this->addSql('CREATE SEQUENCE filter_id_seq INCREMENT BY 1 MINVALUE 1 START 1');
        $this->addSql('CREATE TABLE filter_measure (id INT NOT NULL, filter_id INT NOT NULL, parameter_id INT NOT NULL, minimum_value DOUBLE PRECISION DEFAULT NULL, maximum_value DOUBLE PRECISION DEFAULT NULL, PRIMARY KEY(id))');
        $this->addSql('CREATE INDEX IDX_37976E40D395B25E ON filter_measure (filter_id)');
        $this->addSql('CREATE INDEX IDX_37976E407C56DBD6 ON filter_measure (parameter_id)');
        $this->addSql('CREATE TABLE filter (id INT NOT NULL, minimum_date TIMESTAMP(0) WITHOUT TIME ZONE DEFAULT NULL, maximum_date TIMESTAMP(0) WITHOUT TIME ZONE DEFAULT NULL, name VARCHAR(255) NOT NULL, PRIMARY KEY(id))');
        $this->addSql('CREATE TABLE filter_system (filter_id INT NOT NULL, system_id INT NOT NULL, PRIMARY KEY(filter_id, system_id))');
        $this->addSql('CREATE INDEX IDX_E4664FF5D395B25E ON filter_system (filter_id)');
        $this->addSql('CREATE INDEX IDX_E4664FF5D0952FA5 ON filter_system (system_id)');
        $this->addSql('CREATE TABLE filter_basin (filter_id INT NOT NULL, basin_id INT NOT NULL, PRIMARY KEY(filter_id, basin_id))');
        $this->addSql('CREATE INDEX IDX_C779FA11D395B25E ON filter_basin (filter_id)');
        $this->addSql('CREATE INDEX IDX_C779FA118140C9BF ON filter_basin (basin_id)');
        $this->addSql('CREATE TABLE filter_station (filter_id INT NOT NULL, station_id INT NOT NULL, PRIMARY KEY(filter_id, station_id))');
        $this->addSql('CREATE INDEX IDX_28A98FD4D395B25E ON filter_station (filter_id)');
        $this->addSql('CREATE INDEX IDX_28A98FD421BDB235 ON filter_station (station_id)');
        $this->addSql('ALTER TABLE filter_measure ADD CONSTRAINT FK_37976E40D395B25E FOREIGN KEY (filter_id) REFERENCES filter (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE filter_measure ADD CONSTRAINT FK_37976E407C56DBD6 FOREIGN KEY (parameter_id) REFERENCES parameter (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE filter_system ADD CONSTRAINT FK_E4664FF5D395B25E FOREIGN KEY (filter_id) REFERENCES filter (id) ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE filter_system ADD CONSTRAINT FK_E4664FF5D0952FA5 FOREIGN KEY (system_id) REFERENCES system (id) ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE filter_basin ADD CONSTRAINT FK_C779FA11D395B25E FOREIGN KEY (filter_id) REFERENCES filter (id) ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE filter_basin ADD CONSTRAINT FK_C779FA118140C9BF FOREIGN KEY (basin_id) REFERENCES basin (id) ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE filter_station ADD CONSTRAINT FK_28A98FD4D395B25E FOREIGN KEY (filter_id) REFERENCES filter (id) ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE filter_station ADD CONSTRAINT FK_28A98FD421BDB235 FOREIGN KEY (station_id) REFERENCES station (id) ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE measure ADD valid BOOLEAN DEFAULT NULL');
    }

    public function down(Schema $schema) : void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'postgresql', 'Migration can only be executed safely on \'postgresql\'.');

        $this->addSql('CREATE SCHEMA public');
        $this->addSql('ALTER TABLE filter_measure DROP CONSTRAINT FK_37976E40D395B25E');
        $this->addSql('ALTER TABLE filter_system DROP CONSTRAINT FK_E4664FF5D395B25E');
        $this->addSql('ALTER TABLE filter_basin DROP CONSTRAINT FK_C779FA11D395B25E');
        $this->addSql('ALTER TABLE filter_station DROP CONSTRAINT FK_28A98FD4D395B25E');
        $this->addSql('DROP SEQUENCE filter_measure_id_seq CASCADE');
        $this->addSql('DROP SEQUENCE filter_id_seq CASCADE');
        $this->addSql('DROP TABLE filter_measure');
        $this->addSql('DROP TABLE filter');
        $this->addSql('DROP TABLE filter_system');
        $this->addSql('DROP TABLE filter_basin');
        $this->addSql('DROP TABLE filter_station');
        $this->addSql('ALTER TABLE measure DROP valid');
    }
}
