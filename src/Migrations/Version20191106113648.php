<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20191106113648 extends AbstractMigration
{
    public function getDescription() : string
    {
        return '';
    }

    public function up(Schema $schema) : void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'postgresql', 'Migration can only be executed safely on \'postgresql\'.');

        $this->addSql('ALTER TABLE role DROP can_manage_users');
        $this->addSql('ALTER TABLE role DROP can_manage_systems');
        $this->addSql('ALTER TABLE role DROP can_coordinate_system');
        $this->addSql('ALTER TABLE role DROP can_contribute_system');
        $this->addSql('ALTER TABLE role DROP can_observe_system');
    }

    public function down(Schema $schema) : void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'postgresql', 'Migration can only be executed safely on \'postgresql\'.');

        $this->addSql('CREATE SCHEMA public');
        $this->addSql('ALTER TABLE role ADD can_manage_users BOOLEAN DEFAULT NULL');
        $this->addSql('ALTER TABLE role ADD can_manage_systems BOOLEAN DEFAULT NULL');
        $this->addSql('ALTER TABLE role ADD can_coordinate_system BOOLEAN DEFAULT NULL');
        $this->addSql('ALTER TABLE role ADD can_contribute_system BOOLEAN DEFAULT NULL');
        $this->addSql('ALTER TABLE role ADD can_observe_system BOOLEAN DEFAULT NULL');
    }
}
