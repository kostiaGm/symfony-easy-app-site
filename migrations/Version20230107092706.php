<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20230107092706 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE menu ADD author_id INT DEFAULT NULL');
        $this->addSql('ALTER TABLE menu ADD CONSTRAINT FK_7D053A93F675F31B FOREIGN KEY (author_id) REFERENCES user (id)');
        $this->addSql('CREATE INDEX IDX_7D053A93F675F31B ON menu (author_id)');
        $this->addSql('ALTER TABLE page ADD author_id INT DEFAULT NULL');
        $this->addSql('ALTER TABLE page ADD CONSTRAINT FK_140AB620F675F31B FOREIGN KEY (author_id) REFERENCES user (id)');
        $this->addSql('CREATE INDEX IDX_140AB620F675F31B ON page (author_id)');
        $this->addSql('ALTER TABLE seo ADD owner_id INT DEFAULT NULL, ADD author_id INT DEFAULT NULL');
        $this->addSql('ALTER TABLE seo ADD CONSTRAINT FK_6C71EC307E3C61F9 FOREIGN KEY (owner_id) REFERENCES user (id)');
        $this->addSql('ALTER TABLE seo ADD CONSTRAINT FK_6C71EC30F675F31B FOREIGN KEY (author_id) REFERENCES user (id)');
        $this->addSql('CREATE INDEX IDX_6C71EC307E3C61F9 ON seo (owner_id)');
        $this->addSql('CREATE INDEX IDX_6C71EC30F675F31B ON seo (author_id)');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE menu DROP FOREIGN KEY FK_7D053A93F675F31B');
        $this->addSql('DROP INDEX IDX_7D053A93F675F31B ON menu');
        $this->addSql('ALTER TABLE menu DROP author_id');
        $this->addSql('ALTER TABLE seo DROP FOREIGN KEY FK_6C71EC307E3C61F9');
        $this->addSql('ALTER TABLE seo DROP FOREIGN KEY FK_6C71EC30F675F31B');
        $this->addSql('DROP INDEX IDX_6C71EC307E3C61F9 ON seo');
        $this->addSql('DROP INDEX IDX_6C71EC30F675F31B ON seo');
        $this->addSql('ALTER TABLE seo DROP owner_id, DROP author_id');
        $this->addSql('ALTER TABLE page DROP FOREIGN KEY FK_140AB620F675F31B');
        $this->addSql('DROP INDEX IDX_140AB620F675F31B ON page');
        $this->addSql('ALTER TABLE page DROP author_id');
    }
}
