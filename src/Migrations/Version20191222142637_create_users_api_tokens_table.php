<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20191222142637_create_users_api_tokens_table extends AbstractMigration
{
    public function getDescription() : string
    {
        return '';
    }

    public function up(Schema $schema) : void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'postgresql', 'Migration can only be executed safely on \'postgresql\'.');

        $this->addSql('CREATE TABLE users (id UUID DEFAULT uuid_generate_v4(), email VARCHAR(180) NOT NULL, roles JSON NOT NULL, password VARCHAR(255) NOT NULL, PRIMARY KEY(id))');
        $this->addSql('CREATE UNIQUE INDEX UNIQ_1483A5E9E7927C74 ON users (email)');
        $this->addSql('COMMENT ON COLUMN users.id IS \'(DC2Type:uuid)\'');
        $this->addSql('CREATE TABLE api_tokens (id UUID DEFAULT uuid_generate_v4(), user_id UUID NOT NULL, token VARCHAR(255) NOT NULL, expire_at TIMESTAMP(0) WITHOUT TIME ZONE NOT NULL, expired_at TIMESTAMP(0) WITHOUT TIME ZONE DEFAULT NULL, PRIMARY KEY(id))');
        $this->addSql('CREATE INDEX IDX_2CAD560EDB38439E ON api_tokens (user_id)');
        $this->addSql('COMMENT ON COLUMN api_tokens.id IS \'(DC2Type:uuid)\'');
        $this->addSql('COMMENT ON COLUMN api_tokens.user_id IS \'(DC2Type:uuid)\'');
        $this->addSql('ALTER TABLE api_tokens ADD CONSTRAINT FK_2CAD560EDB38439E FOREIGN KEY (user_id) REFERENCES users (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
    }

    public function down(Schema $schema) : void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'postgresql', 'Migration can only be executed safely on \'postgresql\'.');

        $this->addSql('CREATE SCHEMA public');
        $this->addSql('ALTER TABLE api_tokens DROP CONSTRAINT FK_2CAD560EDB38439E');
        $this->addSql('DROP TABLE api_tokens');
        $this->addSql('DROP TABLE users');
    }
}
