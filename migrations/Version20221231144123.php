<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20221231144123 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE menu DROP deleted_at, CHANGE is_route route TINYINT(1) NOT NULL');
        $this->addSql('ALTER TABLE page DROP FOREIGN KEY FK_140AB620F6BD1646');
        $this->addSql('DROP INDEX IDX_140AB620F6BD1646 ON page');
        $this->addSql('ALTER TABLE page DROP site_id, DROP deleted_at');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE menu ADD deleted_at DATETIME DEFAULT NULL, CHANGE route is_route TINYINT(1) NOT NULL');
        $this->addSql('ALTER TABLE page ADD site_id INT DEFAULT NULL, ADD deleted_at DATETIME DEFAULT NULL');
        $this->addSql('ALTER TABLE page ADD CONSTRAINT FK_140AB620F6BD1646 FOREIGN KEY (site_id) REFERENCES site (id)');
        $this->addSql('CREATE INDEX IDX_140AB620F6BD1646 ON page (site_id)');
    }
}
