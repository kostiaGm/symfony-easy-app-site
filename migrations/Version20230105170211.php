<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20230105170211 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE menu ADD owner_id INT DEFAULT NULL');
        $this->addSql('ALTER TABLE menu ADD CONSTRAINT FK_7D053A937E3C61F9 FOREIGN KEY (owner_id) REFERENCES user (id)');
        $this->addSql('CREATE INDEX IDX_7D053A937E3C61F9 ON menu (owner_id)');
        $this->addSql('ALTER TABLE page ADD owner_id INT DEFAULT NULL');
        $this->addSql('ALTER TABLE page ADD CONSTRAINT FK_140AB6207E3C61F9 FOREIGN KEY (owner_id) REFERENCES user (id)');
        $this->addSql('CREATE INDEX IDX_140AB6207E3C61F9 ON page (owner_id)');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE menu DROP FOREIGN KEY FK_7D053A937E3C61F9');
        $this->addSql('DROP INDEX IDX_7D053A937E3C61F9 ON menu');
        $this->addSql('ALTER TABLE menu DROP owner_id');
        $this->addSql('ALTER TABLE page DROP FOREIGN KEY FK_140AB6207E3C61F9');
        $this->addSql('DROP INDEX IDX_140AB6207E3C61F9 ON page');
        $this->addSql('ALTER TABLE page DROP owner_id');
    }
}
