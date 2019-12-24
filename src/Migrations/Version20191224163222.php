<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20191224163222 extends AbstractMigration
{
    public function getDescription() : string
    {
        return '';
    }

    public function up(Schema $schema) : void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'postgresql', 'Migration can only be executed safely on \'postgresql\'.');

        $this->addSql('ALTER TABLE user_role DROP CONSTRAINT fk_2de8c6a3bd4b1a3b');
        $this->addSql('DROP SEQUENCE role_id_seq CASCADE');
        $this->addSql('DROP SEQUENCE user_role_id_seq CASCADE');
        $this->addSql('DROP TABLE user_role');
        $this->addSql('DROP TABLE role');
        $this->addSql('ALTER TABLE user_account ADD main_role VARCHAR(32) DEFAULT NULL');
        $this->addSql('ALTER TABLE user_account DROP is_administrator');
        $this->addSql('ALTER TABLE system_role DROP CONSTRAINT fk_46a5399cf675f31b');
        $this->addSql('DROP INDEX idx_46a5399cf675f31b');
        $this->addSql('ALTER TABLE system_role ADD role VARCHAR(32) NOT NULL');
        $this->addSql('ALTER TABLE system_role DROP can_view');
        $this->addSql('ALTER TABLE system_role DROP can_encode');
        $this->addSql('ALTER TABLE system_role DROP can_validate');
        $this->addSql('ALTER TABLE system_role DROP can_export');
        $this->addSql('ALTER TABLE system_role DROP can_delete');
        $this->addSql('ALTER TABLE system_role RENAME COLUMN author_id TO user_account_id');
        $this->addSql('ALTER TABLE system_role ADD CONSTRAINT FK_46A5399C3C0C9956 FOREIGN KEY (user_account_id) REFERENCES user_account (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('CREATE INDEX IDX_46A5399C3C0C9956 ON system_role (user_account_id)');
    }

    public function down(Schema $schema) : void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'postgresql', 'Migration can only be executed safely on \'postgresql\'.');

        $this->addSql('CREATE SCHEMA public');
        $this->addSql('CREATE SEQUENCE role_id_seq INCREMENT BY 1 MINVALUE 1 START 1');
        $this->addSql('CREATE SEQUENCE user_role_id_seq INCREMENT BY 1 MINVALUE 1 START 1');
        $this->addSql('CREATE TABLE user_role (id INT NOT NULL, linked_user_id INT NOT NULL, linked_role_id INT NOT NULL, linked_system_id INT DEFAULT NULL, PRIMARY KEY(id))');
        $this->addSql('CREATE INDEX idx_2de8c6a3c697e0ae ON user_role (linked_system_id)');
        $this->addSql('CREATE INDEX idx_2de8c6a3bd4b1a3b ON user_role (linked_role_id)');
        $this->addSql('CREATE INDEX idx_2de8c6a3cc26eb02 ON user_role (linked_user_id)');
        $this->addSql('CREATE TABLE role (id INT NOT NULL, role VARCHAR(255) NOT NULL, PRIMARY KEY(id))');
        $this->addSql('ALTER TABLE user_role ADD CONSTRAINT fk_2de8c6a3cc26eb02 FOREIGN KEY (linked_user_id) REFERENCES user_account (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE user_role ADD CONSTRAINT fk_2de8c6a3bd4b1a3b FOREIGN KEY (linked_role_id) REFERENCES role (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE user_role ADD CONSTRAINT fk_2de8c6a3c697e0ae FOREIGN KEY (linked_system_id) REFERENCES system (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE system_role DROP CONSTRAINT FK_46A5399C3C0C9956');
        $this->addSql('DROP INDEX IDX_46A5399C3C0C9956');
        $this->addSql('ALTER TABLE system_role ADD can_view BOOLEAN DEFAULT NULL');
        $this->addSql('ALTER TABLE system_role ADD can_encode BOOLEAN DEFAULT NULL');
        $this->addSql('ALTER TABLE system_role ADD can_validate BOOLEAN DEFAULT NULL');
        $this->addSql('ALTER TABLE system_role ADD can_export BOOLEAN DEFAULT NULL');
        $this->addSql('ALTER TABLE system_role ADD can_delete BOOLEAN DEFAULT NULL');
        $this->addSql('ALTER TABLE system_role DROP role');
        $this->addSql('ALTER TABLE system_role RENAME COLUMN user_account_id TO author_id');
        $this->addSql('ALTER TABLE system_role ADD CONSTRAINT fk_46a5399cf675f31b FOREIGN KEY (author_id) REFERENCES user_account (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('CREATE INDEX idx_46a5399cf675f31b ON system_role (author_id)');
        $this->addSql('ALTER TABLE user_account ADD is_administrator BOOLEAN DEFAULT NULL');
        $this->addSql('ALTER TABLE user_account DROP main_role');
    }
}
