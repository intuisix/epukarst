<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20200215124317 extends AbstractMigration
{
    public function getDescription() : string
    {
        return '';
    }

    public function up(Schema $schema) : void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'postgresql', 'Migration can only be executed safely on \'postgresql\'.');

        $this->addSql('CREATE TABLE instrument_instrument (instrument_source INT NOT NULL, instrument_target INT NOT NULL, PRIMARY KEY(instrument_source, instrument_target))');
        $this->addSql('CREATE INDEX IDX_F3725ADD6B3C7C3B ON instrument_instrument (instrument_source)');
        $this->addSql('CREATE INDEX IDX_F3725ADD72D92CB4 ON instrument_instrument (instrument_target)');
        $this->addSql('ALTER TABLE instrument_instrument ADD CONSTRAINT FK_F3725ADD6B3C7C3B FOREIGN KEY (instrument_source) REFERENCES instrument (id) ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE instrument_instrument ADD CONSTRAINT FK_F3725ADD72D92CB4 FOREIGN KEY (instrument_target) REFERENCES instrument (id) ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE');
    }

    public function down(Schema $schema) : void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'postgresql', 'Migration can only be executed safely on \'postgresql\'.');

        $this->addSql('CREATE SCHEMA public');
        $this->addSql('DROP TABLE instrument_instrument');
    }
}
