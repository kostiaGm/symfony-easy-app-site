<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20221230202127 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE menu ADD site_id INT DEFAULT NULL, ADD is_route TINYINT(1) NOT NULL, DROP route');
        $this->addSql('ALTER TABLE menu ADD CONSTRAINT FK_7D053A93F6BD1646 FOREIGN KEY (site_id) REFERENCES site (id)');
        $this->addSql('CREATE INDEX IDX_7D053A93F6BD1646 ON menu (site_id)');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE menu DROP FOREIGN KEY FK_7D053A93F6BD1646');
        $this->addSql('DROP INDEX IDX_7D053A93F6BD1646 ON menu');
        $this->addSql('ALTER TABLE menu ADD route VARCHAR(255) DEFAULT NULL, DROP site_id, DROP is_route');
    }
}
