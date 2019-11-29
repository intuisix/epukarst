<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20191129131639 extends AbstractMigration
{
    public function getDescription() : string
    {
        return '';
    }

    public function up(Schema $schema) : void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'postgresql', 'Migration can only be executed safely on \'postgresql\'.');

        $this->addSql('ALTER TABLE post ALTER author_id DROP NOT NULL');
        $this->addSql('ALTER TABLE post ALTER date DROP NOT NULL');
        $this->addSql('ALTER TABLE post ALTER title DROP NOT NULL');
        $this->addSql('ALTER TABLE post ALTER slug DROP NOT NULL');
    }

    public function down(Schema $schema) : void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'postgresql', 'Migration can only be executed safely on \'postgresql\'.');

        $this->addSql('CREATE SCHEMA public');
        $this->addSql('ALTER TABLE post ALTER author_id SET NOT NULL');
        $this->addSql('ALTER TABLE post ALTER date SET NOT NULL');
        $this->addSql('ALTER TABLE post ALTER title SET NOT NULL');
        $this->addSql('ALTER TABLE post ALTER slug SET NOT NULL');
    }
}
