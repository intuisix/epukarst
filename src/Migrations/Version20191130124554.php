<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20191130124554 extends AbstractMigration
{
    public function getDescription() : string
    {
        return '';
    }

    public function up(Schema $schema) : void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'postgresql', 'Migration can only be executed safely on \'postgresql\'.');

        $this->addSql('CREATE SEQUENCE system_reading_id_seq INCREMENT BY 1 MINVALUE 1 START 1');
        $this->addSql('CREATE TABLE system_reading (id INT NOT NULL, system_id INT NOT NULL, encoding_author_id INT NOT NULL, validation_author_id INT DEFAULT NULL, field_date_time TIMESTAMP(0) WITHOUT TIME ZONE NOT NULL, encoding_date_time TIMESTAMP(0) WITHOUT TIME ZONE NOT NULL, validation_date_time TIMESTAMP(0) WITHOUT TIME ZONE DEFAULT NULL, encoding_notes TEXT DEFAULT NULL, validation_notes TEXT DEFAULT NULL, valid BOOLEAN DEFAULT NULL, code VARCHAR(255) NOT NULL, PRIMARY KEY(id))');
        $this->addSql('CREATE INDEX IDX_2D6980D2D0952FA5 ON system_reading (system_id)');
        $this->addSql('CREATE INDEX IDX_2D6980D2AC2CA030 ON system_reading (encoding_author_id)');
        $this->addSql('CREATE INDEX IDX_2D6980D27D2CE259 ON system_reading (validation_author_id)');
        $this->addSql('ALTER TABLE system_reading ADD CONSTRAINT FK_2D6980D2D0952FA5 FOREIGN KEY (system_id) REFERENCES system (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE system_reading ADD CONSTRAINT FK_2D6980D2AC2CA030 FOREIGN KEY (encoding_author_id) REFERENCES user_account (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE system_reading ADD CONSTRAINT FK_2D6980D27D2CE259 FOREIGN KEY (validation_author_id) REFERENCES user_account (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE reading ADD system_reading_id INT DEFAULT NULL');
        $this->addSql('ALTER TABLE reading ADD CONSTRAINT FK_C11AFC41E7A165F4 FOREIGN KEY (system_reading_id) REFERENCES system_reading (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('CREATE INDEX IDX_C11AFC41E7A165F4 ON reading (system_reading_id)');
    }

    public function down(Schema $schema) : void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'postgresql', 'Migration can only be executed safely on \'postgresql\'.');

        $this->addSql('CREATE SCHEMA public');
        $this->addSql('ALTER TABLE reading DROP CONSTRAINT FK_C11AFC41E7A165F4');
        $this->addSql('DROP SEQUENCE system_reading_id_seq CASCADE');
        $this->addSql('DROP TABLE system_reading');
        $this->addSql('DROP INDEX IDX_C11AFC41E7A165F4');
        $this->addSql('ALTER TABLE reading DROP system_reading_id');
    }
}
