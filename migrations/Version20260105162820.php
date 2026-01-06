<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20260105162820 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE logos (id VARCHAR(36) NOT NULL, name VARCHAR(255) NOT NULL, url VARCHAR(500) NOT NULL, PRIMARY KEY(id))');
        $this->addSql('CREATE TABLE tier_list_items (id SERIAL NOT NULL, tier_list_id VARCHAR(36) NOT NULL, logo_id VARCHAR(36) NOT NULL, tier_category VARCHAR(255) NOT NULL, PRIMARY KEY(id))');
        $this->addSql('CREATE INDEX IDX_929486BDB25FD8A1 ON tier_list_items (tier_list_id)');
        $this->addSql('CREATE TABLE tier_lists (id VARCHAR(36) NOT NULL, user_id VARCHAR(36) NOT NULL, PRIMARY KEY(id))');
        $this->addSql('CREATE UNIQUE INDEX UNIQ_3CC8C72CA76ED395 ON tier_lists (user_id)');
        $this->addSql('CREATE TABLE users (id VARCHAR(36) NOT NULL, email VARCHAR(180) NOT NULL, password VARCHAR(255) NOT NULL, PRIMARY KEY(id))');
        $this->addSql('CREATE UNIQUE INDEX UNIQ_1483A5E9E7927C74 ON users (email)');
        $this->addSql('CREATE TABLE messenger_messages (id BIGSERIAL NOT NULL, body TEXT NOT NULL, headers TEXT NOT NULL, queue_name VARCHAR(190) NOT NULL, created_at TIMESTAMP(0) WITHOUT TIME ZONE NOT NULL, available_at TIMESTAMP(0) WITHOUT TIME ZONE NOT NULL, delivered_at TIMESTAMP(0) WITHOUT TIME ZONE DEFAULT NULL, PRIMARY KEY(id))');
        $this->addSql('CREATE INDEX IDX_75EA56E0FB7336F0E3BD61CE16BA31DBBF396750 ON messenger_messages (queue_name, available_at, delivered_at, id)');
        $this->addSql('ALTER TABLE tier_list_items ADD CONSTRAINT FK_929486BDB25FD8A1 FOREIGN KEY (tier_list_id) REFERENCES tier_lists (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE tier_lists ADD CONSTRAINT FK_3CC8C72CA76ED395 FOREIGN KEY (user_id) REFERENCES users (id) ON DELETE CASCADE');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE tier_list_items DROP CONSTRAINT FK_929486BDB25FD8A1');
        $this->addSql('ALTER TABLE tier_lists DROP CONSTRAINT FK_3CC8C72CA76ED395');
        $this->addSql('DROP TABLE logos');
        $this->addSql('DROP TABLE tier_list_items');
        $this->addSql('DROP TABLE tier_lists');
        $this->addSql('DROP TABLE users');
        $this->addSql('DROP TABLE messenger_messages');
    }
}
