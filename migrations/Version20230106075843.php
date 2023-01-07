<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20230106075843 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE page ADD editor_id INT DEFAULT NULL, ADD permission_mode SMALLINT NOT NULL');
        $this->addSql('ALTER TABLE page ADD CONSTRAINT FK_140AB6206995AC4C FOREIGN KEY (editor_id) REFERENCES user (id)');
        $this->addSql('CREATE INDEX IDX_140AB6206995AC4C ON page (editor_id)');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE page DROP FOREIGN KEY FK_140AB6206995AC4C');
        $this->addSql('DROP INDEX IDX_140AB6206995AC4C ON page');
        $this->addSql('ALTER TABLE page DROP editor_id, DROP permission_mode');
    }
}
