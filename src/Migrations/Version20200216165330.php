<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20200216165330 extends AbstractMigration
{
    public function getDescription() : string
    {
        return '';
    }

    public function up(Schema $schema) : void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'postgresql', 'Migration can only be executed safely on \'postgresql\'.');

        $this->addSql('CREATE SEQUENCE attachment_id_seq INCREMENT BY 1 MINVALUE 1 START 1');
        $this->addSql('CREATE TABLE attachment (id INT NOT NULL, system_reading_id INT DEFAULT NULL, upload_author_id INT NOT NULL, file_name VARCHAR(255) NOT NULL, mime_type VARCHAR(255) DEFAULT NULL, upload_date_time TIMESTAMP(0) WITHOUT TIME ZONE NOT NULL, name VARCHAR(255) NOT NULL, PRIMARY KEY(id))');
        $this->addSql('CREATE INDEX IDX_795FD9BBE7A165F4 ON attachment (system_reading_id)');
        $this->addSql('CREATE INDEX IDX_795FD9BBCBEA1134 ON attachment (upload_author_id)');
        $this->addSql('ALTER TABLE attachment ADD CONSTRAINT FK_795FD9BBE7A165F4 FOREIGN KEY (system_reading_id) REFERENCES system_reading (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE attachment ADD CONSTRAINT FK_795FD9BBCBEA1134 FOREIGN KEY (upload_author_id) REFERENCES user_account (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
    }

    public function down(Schema $schema) : void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'postgresql', 'Migration can only be executed safely on \'postgresql\'.');

        $this->addSql('CREATE SCHEMA public');
        $this->addSql('DROP SEQUENCE attachment_id_seq CASCADE');
        $this->addSql('DROP TABLE attachment');
    }
}
