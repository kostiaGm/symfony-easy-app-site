<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20230103131004 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE seo DROP FOREIGN KEY FK_6C71EC30126F525E');
        $this->addSql('DROP INDEX IDX_6C71EC30126F525E ON seo');
        $this->addSql('ALTER TABLE seo DROP item_id');
        $this->addSql('ALTER TABLE seo_item ADD seo_id INT DEFAULT NULL');
        $this->addSql('ALTER TABLE seo_item ADD CONSTRAINT FK_E3CD35CF97E3DD86 FOREIGN KEY (seo_id) REFERENCES seo (id)');
        $this->addSql('CREATE INDEX IDX_E3CD35CF97E3DD86 ON seo_item (seo_id)');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE seo ADD item_id INT DEFAULT NULL');
        $this->addSql('ALTER TABLE seo ADD CONSTRAINT FK_6C71EC30126F525E FOREIGN KEY (item_id) REFERENCES seo_item (id)');
        $this->addSql('CREATE INDEX IDX_6C71EC30126F525E ON seo (item_id)');
        $this->addSql('ALTER TABLE seo_item DROP FOREIGN KEY FK_E3CD35CF97E3DD86');
        $this->addSql('DROP INDEX IDX_E3CD35CF97E3DD86 ON seo_item');
        $this->addSql('ALTER TABLE seo_item DROP seo_id');
    }
}
