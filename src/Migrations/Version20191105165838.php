<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20191105165838 extends AbstractMigration
{
    public function getDescription() : string
    {
        return '';
    }

    public function up(Schema $schema) : void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'postgresql', 'Migration can only be executed safely on \'postgresql\'.');

        $this->addSql('CREATE SEQUENCE basin_id_seq INCREMENT BY 1 MINVALUE 1 START 1');
        $this->addSql('CREATE SEQUENCE user_account_id_seq INCREMENT BY 1 MINVALUE 1 START 1');
        $this->addSql('CREATE SEQUENCE role_id_seq INCREMENT BY 1 MINVALUE 1 START 1');
        $this->addSql('CREATE SEQUENCE measurability_id_seq INCREMENT BY 1 MINVALUE 1 START 1');
        $this->addSql('CREATE SEQUENCE system_id_seq INCREMENT BY 1 MINVALUE 1 START 1');
        $this->addSql('CREATE SEQUENCE measure_id_seq INCREMENT BY 1 MINVALUE 1 START 1');
        $this->addSql('CREATE SEQUENCE reading_id_seq INCREMENT BY 1 MINVALUE 1 START 1');
        $this->addSql('CREATE SEQUENCE instrument_id_seq INCREMENT BY 1 MINVALUE 1 START 1');
        $this->addSql('CREATE SEQUENCE system_picture_id_seq INCREMENT BY 1 MINVALUE 1 START 1');
        $this->addSql('CREATE SEQUENCE user_role_id_seq INCREMENT BY 1 MINVALUE 1 START 1');
        $this->addSql('CREATE SEQUENCE parameter_id_seq INCREMENT BY 1 MINVALUE 1 START 1');
        $this->addSql('CREATE SEQUENCE station_id_seq INCREMENT BY 1 MINVALUE 1 START 1');
        $this->addSql('CREATE TABLE basin (id INT NOT NULL, system_id INT NOT NULL, code VARCHAR(255) NOT NULL, name VARCHAR(255) NOT NULL, description TEXT NOT NULL, PRIMARY KEY(id))');
        $this->addSql('CREATE INDEX IDX_EEC809EED0952FA5 ON basin (system_id)');
        $this->addSql('CREATE TABLE user_account (id INT NOT NULL, first_name VARCHAR(255) NOT NULL, last_name VARCHAR(255) NOT NULL, organization VARCHAR(255) DEFAULT NULL, email VARCHAR(255) NOT NULL, password VARCHAR(255) NOT NULL, phone VARCHAR(255) DEFAULT NULL, display_name VARCHAR(255) NOT NULL, description TEXT DEFAULT NULL, picture VARCHAR(255) DEFAULT NULL, PRIMARY KEY(id))');
        $this->addSql('CREATE TABLE role (id INT NOT NULL, role VARCHAR(255) NOT NULL, can_manage_users BOOLEAN DEFAULT NULL, can_manage_systems BOOLEAN DEFAULT NULL, can_coordinate_system BOOLEAN DEFAULT NULL, can_contribute_system BOOLEAN DEFAULT NULL, can_observe_system BOOLEAN DEFAULT NULL, PRIMARY KEY(id))');
        $this->addSql('CREATE TABLE measurability (id INT NOT NULL, parameter_id INT NOT NULL, instrument_id INT NOT NULL, maximum_value DOUBLE PRECISION DEFAULT NULL, minimum_value DOUBLE PRECISION DEFAULT NULL, tolerance DOUBLE PRECISION DEFAULT NULL, PRIMARY KEY(id))');
        $this->addSql('CREATE INDEX IDX_869CB1EC7C56DBD6 ON measurability (parameter_id)');
        $this->addSql('CREATE INDEX IDX_869CB1ECCF11D9C ON measurability (instrument_id)');
        $this->addSql('CREATE TABLE system (id INT NOT NULL, code VARCHAR(255) NOT NULL, name VARCHAR(255) NOT NULL, introduction VARCHAR(255) NOT NULL, commune VARCHAR(255) NOT NULL, basin VARCHAR(255) NOT NULL, number VARCHAR(255) NOT NULL, description TEXT NOT NULL, slug VARCHAR(255) NOT NULL, picture VARCHAR(255) NOT NULL, water_mass VARCHAR(255) NOT NULL, PRIMARY KEY(id))');
        $this->addSql('CREATE TABLE measure (id INT NOT NULL, reading_id INT NOT NULL, measurability_id INT NOT NULL, encoding_author_id INT NOT NULL, value DOUBLE PRECISION NOT NULL, tolerance DOUBLE PRECISION DEFAULT NULL, stabilized BOOLEAN NOT NULL, notes TEXT DEFAULT NULL, encoding_date_time TIMESTAMP(0) WITHOUT TIME ZONE NOT NULL, PRIMARY KEY(id))');
        $this->addSql('CREATE INDEX IDX_80071925527275CD ON measure (reading_id)');
        $this->addSql('CREATE INDEX IDX_80071925C2DEACE4 ON measure (measurability_id)');
        $this->addSql('CREATE INDEX IDX_80071925AC2CA030 ON measure (encoding_author_id)');
        $this->addSql('CREATE TABLE reading (id INT NOT NULL, station_id INT NOT NULL, encoding_author_id INT NOT NULL, validation_author_id INT DEFAULT NULL, code VARCHAR(255) NOT NULL, field_date_time TIMESTAMP(0) WITHOUT TIME ZONE NOT NULL, encoding_date_time TIMESTAMP(0) WITHOUT TIME ZONE NOT NULL, encoding_notes TEXT DEFAULT NULL, validation_date_time TIMESTAMP(0) WITHOUT TIME ZONE DEFAULT NULL, validation_notes TEXT DEFAULT NULL, PRIMARY KEY(id))');
        $this->addSql('CREATE INDEX IDX_C11AFC4121BDB235 ON reading (station_id)');
        $this->addSql('CREATE INDEX IDX_C11AFC41AC2CA030 ON reading (encoding_author_id)');
        $this->addSql('CREATE INDEX IDX_C11AFC417D2CE259 ON reading (validation_author_id)');
        $this->addSql('CREATE TABLE instrument (id INT NOT NULL, code VARCHAR(255) NOT NULL, name VARCHAR(255) NOT NULL, model VARCHAR(255) NOT NULL, serial_number VARCHAR(255) NOT NULL, description TEXT NOT NULL, PRIMARY KEY(id))');
        $this->addSql('CREATE TABLE system_picture (id INT NOT NULL, system_id INT NOT NULL, caption VARCHAR(255) NOT NULL, file_name VARCHAR(255) NOT NULL, PRIMARY KEY(id))');
        $this->addSql('CREATE INDEX IDX_FAA8331AD0952FA5 ON system_picture (system_id)');
        $this->addSql('CREATE TABLE user_role (id INT NOT NULL, linked_user_id INT NOT NULL, linked_role_id INT NOT NULL, linked_system_id INT DEFAULT NULL, PRIMARY KEY(id))');
        $this->addSql('CREATE INDEX IDX_2DE8C6A3CC26EB02 ON user_role (linked_user_id)');
        $this->addSql('CREATE INDEX IDX_2DE8C6A3BD4B1A3B ON user_role (linked_role_id)');
        $this->addSql('CREATE INDEX IDX_2DE8C6A3C697E0AE ON user_role (linked_system_id)');
        $this->addSql('CREATE TABLE parameter (id INT NOT NULL, name VARCHAR(255) NOT NULL, favorite BOOLEAN NOT NULL, normative_minimum DOUBLE PRECISION DEFAULT NULL, normative_maximum DOUBLE PRECISION DEFAULT NULL, physical_minimum DOUBLE PRECISION DEFAULT NULL, physical_maximum DOUBLE PRECISION DEFAULT NULL, unit VARCHAR(255) NOT NULL, description TEXT NOT NULL, introduction VARCHAR(255) NOT NULL, title VARCHAR(255) NOT NULL, PRIMARY KEY(id))');
        $this->addSql('CREATE TABLE station (id INT NOT NULL, basin_id INT NOT NULL, code VARCHAR(255) NOT NULL, name VARCHAR(255) NOT NULL, kind VARCHAR(255) NOT NULL, description TEXT NOT NULL, PRIMARY KEY(id))');
        $this->addSql('CREATE INDEX IDX_9F39F8B18140C9BF ON station (basin_id)');
        $this->addSql('ALTER TABLE basin ADD CONSTRAINT FK_EEC809EED0952FA5 FOREIGN KEY (system_id) REFERENCES system (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE measurability ADD CONSTRAINT FK_869CB1EC7C56DBD6 FOREIGN KEY (parameter_id) REFERENCES parameter (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE measurability ADD CONSTRAINT FK_869CB1ECCF11D9C FOREIGN KEY (instrument_id) REFERENCES instrument (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE measure ADD CONSTRAINT FK_80071925527275CD FOREIGN KEY (reading_id) REFERENCES reading (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE measure ADD CONSTRAINT FK_80071925C2DEACE4 FOREIGN KEY (measurability_id) REFERENCES measurability (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE measure ADD CONSTRAINT FK_80071925AC2CA030 FOREIGN KEY (encoding_author_id) REFERENCES user_account (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE reading ADD CONSTRAINT FK_C11AFC4121BDB235 FOREIGN KEY (station_id) REFERENCES station (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE reading ADD CONSTRAINT FK_C11AFC41AC2CA030 FOREIGN KEY (encoding_author_id) REFERENCES user_account (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE reading ADD CONSTRAINT FK_C11AFC417D2CE259 FOREIGN KEY (validation_author_id) REFERENCES user_account (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE system_picture ADD CONSTRAINT FK_FAA8331AD0952FA5 FOREIGN KEY (system_id) REFERENCES system (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE user_role ADD CONSTRAINT FK_2DE8C6A3CC26EB02 FOREIGN KEY (linked_user_id) REFERENCES user_account (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE user_role ADD CONSTRAINT FK_2DE8C6A3BD4B1A3B FOREIGN KEY (linked_role_id) REFERENCES role (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE user_role ADD CONSTRAINT FK_2DE8C6A3C697E0AE FOREIGN KEY (linked_system_id) REFERENCES system (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE station ADD CONSTRAINT FK_9F39F8B18140C9BF FOREIGN KEY (basin_id) REFERENCES basin (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
    }

    public function down(Schema $schema) : void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'postgresql', 'Migration can only be executed safely on \'postgresql\'.');

        $this->addSql('CREATE SCHEMA public');
        $this->addSql('ALTER TABLE station DROP CONSTRAINT FK_9F39F8B18140C9BF');
        $this->addSql('ALTER TABLE measure DROP CONSTRAINT FK_80071925AC2CA030');
        $this->addSql('ALTER TABLE reading DROP CONSTRAINT FK_C11AFC41AC2CA030');
        $this->addSql('ALTER TABLE reading DROP CONSTRAINT FK_C11AFC417D2CE259');
        $this->addSql('ALTER TABLE user_role DROP CONSTRAINT FK_2DE8C6A3CC26EB02');
        $this->addSql('ALTER TABLE user_role DROP CONSTRAINT FK_2DE8C6A3BD4B1A3B');
        $this->addSql('ALTER TABLE measure DROP CONSTRAINT FK_80071925C2DEACE4');
        $this->addSql('ALTER TABLE basin DROP CONSTRAINT FK_EEC809EED0952FA5');
        $this->addSql('ALTER TABLE system_picture DROP CONSTRAINT FK_FAA8331AD0952FA5');
        $this->addSql('ALTER TABLE user_role DROP CONSTRAINT FK_2DE8C6A3C697E0AE');
        $this->addSql('ALTER TABLE measure DROP CONSTRAINT FK_80071925527275CD');
        $this->addSql('ALTER TABLE measurability DROP CONSTRAINT FK_869CB1ECCF11D9C');
        $this->addSql('ALTER TABLE measurability DROP CONSTRAINT FK_869CB1EC7C56DBD6');
        $this->addSql('ALTER TABLE reading DROP CONSTRAINT FK_C11AFC4121BDB235');
        $this->addSql('DROP SEQUENCE basin_id_seq CASCADE');
        $this->addSql('DROP SEQUENCE user_account_id_seq CASCADE');
        $this->addSql('DROP SEQUENCE role_id_seq CASCADE');
        $this->addSql('DROP SEQUENCE measurability_id_seq CASCADE');
        $this->addSql('DROP SEQUENCE system_id_seq CASCADE');
        $this->addSql('DROP SEQUENCE measure_id_seq CASCADE');
        $this->addSql('DROP SEQUENCE reading_id_seq CASCADE');
        $this->addSql('DROP SEQUENCE instrument_id_seq CASCADE');
        $this->addSql('DROP SEQUENCE system_picture_id_seq CASCADE');
        $this->addSql('DROP SEQUENCE user_role_id_seq CASCADE');
        $this->addSql('DROP SEQUENCE parameter_id_seq CASCADE');
        $this->addSql('DROP SEQUENCE station_id_seq CASCADE');
        $this->addSql('DROP TABLE basin');
        $this->addSql('DROP TABLE user_account');
        $this->addSql('DROP TABLE role');
        $this->addSql('DROP TABLE measurability');
        $this->addSql('DROP TABLE system');
        $this->addSql('DROP TABLE measure');
        $this->addSql('DROP TABLE reading');
        $this->addSql('DROP TABLE instrument');
        $this->addSql('DROP TABLE system_picture');
        $this->addSql('DROP TABLE user_role');
        $this->addSql('DROP TABLE parameter');
        $this->addSql('DROP TABLE station');
    }
}
