<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20191226155049 extends AbstractMigration
{
    public function getDescription() : string
    {
        return '';
    }

    public function up(Schema $schema) : void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'postgresql', 'Migration can only be executed safely on \'postgresql\'.');

        $this->addSql('CREATE SEQUENCE alarm_id_seq INCREMENT BY 1 MINVALUE 1 START 1');
        $this->addSql('CREATE TABLE alarm (id INT NOT NULL, system_id INT NOT NULL, reporting_author_id INT NOT NULL, beginning_date TIMESTAMP(0) WITHOUT TIME ZONE DEFAULT NULL, ending_date TIMESTAMP(0) WITHOUT TIME ZONE DEFAULT NULL, reporting_date TIMESTAMP(0) WITHOUT TIME ZONE NOT NULL, notes TEXT NOT NULL, PRIMARY KEY(id))');
        $this->addSql('CREATE INDEX IDX_749F46DDD0952FA5 ON alarm (system_id)');
        $this->addSql('CREATE INDEX IDX_749F46DD32CC62D9 ON alarm (reporting_author_id)');
        $this->addSql('CREATE TABLE alarm_measure (alarm_id INT NOT NULL, measure_id INT NOT NULL, PRIMARY KEY(alarm_id, measure_id))');
        $this->addSql('CREATE INDEX IDX_AEEE394F25830571 ON alarm_measure (alarm_id)');
        $this->addSql('CREATE INDEX IDX_AEEE394F5DA37D00 ON alarm_measure (measure_id)');
        $this->addSql('ALTER TABLE alarm ADD CONSTRAINT FK_749F46DDD0952FA5 FOREIGN KEY (system_id) REFERENCES system (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE alarm ADD CONSTRAINT FK_749F46DD32CC62D9 FOREIGN KEY (reporting_author_id) REFERENCES user_account (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE alarm_measure ADD CONSTRAINT FK_AEEE394F25830571 FOREIGN KEY (alarm_id) REFERENCES alarm (id) ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE alarm_measure ADD CONSTRAINT FK_AEEE394F5DA37D00 FOREIGN KEY (measure_id) REFERENCES measure (id) ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE');
    }

    public function down(Schema $schema) : void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'postgresql', 'Migration can only be executed safely on \'postgresql\'.');

        $this->addSql('CREATE SCHEMA public');
        $this->addSql('ALTER TABLE alarm_measure DROP CONSTRAINT FK_AEEE394F25830571');
        $this->addSql('DROP SEQUENCE alarm_id_seq CASCADE');
        $this->addSql('DROP TABLE alarm');
        $this->addSql('DROP TABLE alarm_measure');
    }
}
