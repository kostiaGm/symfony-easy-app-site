<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20230522095827 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE gallery (id INT AUTO_INCREMENT NOT NULL, owner_id INT DEFAULT NULL, author_id INT DEFAULT NULL, editor_id INT DEFAULT NULL, name VARCHAR(65) NOT NULL, description LONGTEXT DEFAULT NULL, permission_mode SMALLINT NOT NULL, deleted_at DATETIME DEFAULT NULL, status SMALLINT NOT NULL, is_default TINYINT(1) NOT NULL, INDEX IDX_472B783A7E3C61F9 (owner_id), INDEX IDX_472B783AF675F31B (author_id), INDEX IDX_472B783A6995AC4C (editor_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('ALTER TABLE gallery ADD CONSTRAINT FK_472B783A7E3C61F9 FOREIGN KEY (owner_id) REFERENCES user (id)');
        $this->addSql('ALTER TABLE gallery ADD CONSTRAINT FK_472B783AF675F31B FOREIGN KEY (author_id) REFERENCES user (id)');
        $this->addSql('ALTER TABLE gallery ADD CONSTRAINT FK_472B783A6995AC4C FOREIGN KEY (editor_id) REFERENCES user (id)');

        $this->addSql('ALTER TABLE gallery_setting ADD gallery_id INT DEFAULT NULL, ADD name VARCHAR(65) NOT NULL, ADD width INT NOT NULL, ADD height INT NOT NULL, ADD path VARCHAR(255) DEFAULT NULL');
        $this->addSql('ALTER TABLE gallery_setting ADD CONSTRAINT FK_A70EF9B04E7AF8F FOREIGN KEY (gallery_id) REFERENCES gallery (id)');
        $this->addSql('CREATE INDEX IDX_A70EF9B04E7AF8F ON gallery_setting (gallery_id)');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE gallery_setting DROP FOREIGN KEY FK_A70EF9B04E7AF8F');
        $this->addSql('ALTER TABLE gallery DROP FOREIGN KEY FK_472B783A7E3C61F9');
        $this->addSql('ALTER TABLE gallery DROP FOREIGN KEY FK_472B783AF675F31B');
        $this->addSql('ALTER TABLE gallery DROP FOREIGN KEY FK_472B783A6995AC4C');
        $this->addSql('DROP TABLE gallery');

        $this->addSql('DROP INDEX IDX_A70EF9B04E7AF8F ON gallery_setting');
        $this->addSql('ALTER TABLE gallery_setting DROP gallery_id, DROP name, DROP width, DROP height, DROP path');
    }
}
