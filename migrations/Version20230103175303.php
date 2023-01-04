<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20230103175303 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('DROP INDEX entity_entity_id_site_id ON seo');
        $this->addSql('CREATE INDEX entity_entity_id_site_id ON seo (site_id, entity_id, entity)');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('DROP INDEX entity_entity_id_site_id ON seo');
        $this->addSql('CREATE INDEX entity_entity_id_site_id ON seo (site_id, entity, entity_id)');
    }
}
