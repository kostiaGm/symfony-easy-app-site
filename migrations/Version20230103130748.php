<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20230103130748 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE seo (id INT AUTO_INCREMENT NOT NULL, item_id INT DEFAULT NULL, entity VARCHAR(255) NOT NULL, entity_id INT NOT NULL, site_id INT NOT NULL, INDEX IDX_6C71EC30126F525E (item_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE seo_item (id INT AUTO_INCREMENT NOT NULL, type VARCHAR(60) NOT NULL, content VARCHAR(255) NOT NULL, PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('ALTER TABLE seo ADD CONSTRAINT FK_6C71EC30126F525E FOREIGN KEY (item_id) REFERENCES seo_item (id)');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE seo DROP FOREIGN KEY FK_6C71EC30126F525E');
        $this->addSql('DROP TABLE seo');
        $this->addSql('DROP TABLE seo_item');
    }
}
