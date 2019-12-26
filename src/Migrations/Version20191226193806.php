<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20191226193806 extends AbstractMigration
{
    public function getDescription() : string
    {
        return '';
    }

    public function up(Schema $schema) : void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'postgresql', 'Migration can only be executed safely on \'postgresql\'.');

        $this->addSql('DROP TABLE alarm_measure');
        $this->addSql('ALTER TABLE measure ADD alarm_id INT DEFAULT NULL');
        $this->addSql('ALTER TABLE measure ADD CONSTRAINT FK_8007192525830571 FOREIGN KEY (alarm_id) REFERENCES alarm (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('CREATE INDEX IDX_8007192525830571 ON measure (alarm_id)');
    }

    public function down(Schema $schema) : void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'postgresql', 'Migration can only be executed safely on \'postgresql\'.');

        $this->addSql('CREATE SCHEMA public');
        $this->addSql('CREATE TABLE alarm_measure (alarm_id INT NOT NULL, measure_id INT NOT NULL, PRIMARY KEY(alarm_id, measure_id))');
        $this->addSql('CREATE INDEX idx_aeee394f5da37d00 ON alarm_measure (measure_id)');
        $this->addSql('CREATE INDEX idx_aeee394f25830571 ON alarm_measure (alarm_id)');
        $this->addSql('ALTER TABLE alarm_measure ADD CONSTRAINT fk_aeee394f25830571 FOREIGN KEY (alarm_id) REFERENCES alarm (id) ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE alarm_measure ADD CONSTRAINT fk_aeee394f5da37d00 FOREIGN KEY (measure_id) REFERENCES measure (id) ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE measure DROP CONSTRAINT FK_8007192525830571');
        $this->addSql('DROP INDEX IDX_8007192525830571');
        $this->addSql('ALTER TABLE measure DROP alarm_id');
    }
}
