<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20191229200427 extends AbstractMigration
{
    public function getDescription() : string
    {
        return '';
    }

    public function up(Schema $schema) : void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'postgresql', 'Migration can only be executed safely on \'postgresql\'.');

        $this->addSql('ALTER TABLE system_reading ADD alarm_id INT DEFAULT NULL');
        $this->addSql('ALTER TABLE system_reading ADD CONSTRAINT FK_2D6980D225830571 FOREIGN KEY (alarm_id) REFERENCES alarm (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('CREATE INDEX IDX_2D6980D225830571 ON system_reading (alarm_id)');
    }

    public function down(Schema $schema) : void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'postgresql', 'Migration can only be executed safely on \'postgresql\'.');

        $this->addSql('CREATE SCHEMA public');
        $this->addSql('ALTER TABLE system_reading DROP CONSTRAINT FK_2D6980D225830571');
        $this->addSql('DROP INDEX IDX_2D6980D225830571');
        $this->addSql('ALTER TABLE system_reading DROP alarm_id');
    }
}
