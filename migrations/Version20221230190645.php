<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20221230190645 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE menu (id INT AUTO_INCREMENT NOT NULL, name VARCHAR(65) NOT NULL, lft INT NOT NULL, rgt INT NOT NULL, lvl INT NOT NULL, tree INT NOT NULL, parent_id INT NOT NULL, route VARCHAR(255) DEFAULT NULL, url VARCHAR(255) DEFAULT NULL, path VARCHAR(255) DEFAULT NULL, type SMALLINT DEFAULT NULL, status SMALLINT NOT NULL, created_at DATETIME DEFAULT \'0000-00-00 00:00:00\' NOT NULL, updated_at DATETIME DEFAULT NULL, deleted_at DATETIME DEFAULT NULL, INDEX id_lft_rgt (id, lft, rgt), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('DROP TABLE menu');
    }
}
