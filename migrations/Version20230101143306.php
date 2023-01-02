<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20230101143306 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE menu DROP FOREIGN KEY FK_7D053A93F6BD1646');
        $this->addSql('CREATE INDEX path ON menu (site_id, path)');
        $this->addSql('DROP INDEX site ON menu');
        $this->addSql('CREATE INDEX IDX_7D053A93F6BD1646 ON menu (site_id)');
        $this->addSql('ALTER TABLE menu ADD CONSTRAINT FK_7D053A93F6BD1646 FOREIGN KEY (site_id) REFERENCES site (id)');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('DROP INDEX path ON menu');
        $this->addSql('ALTER TABLE menu DROP FOREIGN KEY FK_7D053A93F6BD1646');
        $this->addSql('DROP INDEX idx_7d053a93f6bd1646 ON menu');
        $this->addSql('CREATE INDEX site ON menu (site_id)');
        $this->addSql('ALTER TABLE menu ADD CONSTRAINT FK_7D053A93F6BD1646 FOREIGN KEY (site_id) REFERENCES site (id)');
    }
}
