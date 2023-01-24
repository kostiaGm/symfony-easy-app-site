<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20230123205218 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE seo DROP FOREIGN KEY FK_6C71EC30F675F31B');
        $this->addSql('ALTER TABLE seo DROP FOREIGN KEY FK_6C71EC307E3C61F9');
        $this->addSql('ALTER TABLE seo_item DROP FOREIGN KEY FK_E3CD35CF97E3DD86');
        $this->addSql('DROP TABLE seo');
        $this->addSql('DROP TABLE seo_item');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE seo (id INT AUTO_INCREMENT NOT NULL, owner_id INT DEFAULT NULL, author_id INT DEFAULT NULL, entity VARCHAR(255) CHARACTER SET utf8mb4 NOT NULL COLLATE `utf8mb4_unicode_ci`, entity_id INT NOT NULL, site_id INT NOT NULL, INDEX IDX_6C71EC307E3C61F9 (owner_id), INDEX entity_entity_id_site_id (site_id, entity_id, entity), INDEX IDX_6C71EC30F675F31B (author_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB COMMENT = \'\' ');
        $this->addSql('CREATE TABLE seo_item (id INT AUTO_INCREMENT NOT NULL, seo_id INT DEFAULT NULL, type VARCHAR(60) CHARACTER SET utf8mb4 NOT NULL COLLATE `utf8mb4_unicode_ci`, content VARCHAR(255) CHARACTER SET utf8mb4 NOT NULL COLLATE `utf8mb4_unicode_ci`, INDEX IDX_E3CD35CF97E3DD86 (seo_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB COMMENT = \'\' ');
        $this->addSql('ALTER TABLE seo ADD CONSTRAINT FK_6C71EC30F675F31B FOREIGN KEY (author_id) REFERENCES user (id)');
        $this->addSql('ALTER TABLE seo ADD CONSTRAINT FK_6C71EC307E3C61F9 FOREIGN KEY (owner_id) REFERENCES user (id)');
        $this->addSql('ALTER TABLE seo_item ADD CONSTRAINT FK_E3CD35CF97E3DD86 FOREIGN KEY (seo_id) REFERENCES seo (id)');
    }
}
