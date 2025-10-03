<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20251003173836 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE carrousel DROP INDEX IDX_EF01B088EA9FDD75, ADD UNIQUE INDEX uniq_carrousel_media (media_id)');
        $this->addSql('ALTER TABLE carrousel DROP FOREIGN KEY FK_EF01B088EA9FDD75');
        $this->addSql('DROP INDEX uniq_carrousel_position ON carrousel');
        $this->addSql('ALTER TABLE carrousel CHANGE is_active is_active TINYINT(1) DEFAULT 0 NOT NULL, CHANGE position position INT DEFAULT NULL');
        $this->addSql('ALTER TABLE carrousel ADD CONSTRAINT FK_EF01B088EA9FDD75 FOREIGN KEY (media_id) REFERENCES media (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE tatoueur ADD pseudo VARCHAR(25) DEFAULT NULL');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE tatoueur DROP pseudo');
        $this->addSql('ALTER TABLE carrousel DROP INDEX uniq_carrousel_media, ADD INDEX IDX_EF01B088EA9FDD75 (media_id)');
        $this->addSql('ALTER TABLE carrousel DROP FOREIGN KEY FK_EF01B088EA9FDD75');
        $this->addSql('ALTER TABLE carrousel CHANGE is_active is_active TINYINT(1) DEFAULT 1 NOT NULL, CHANGE position position INT NOT NULL');
        $this->addSql('ALTER TABLE carrousel ADD CONSTRAINT FK_EF01B088EA9FDD75 FOREIGN KEY (media_id) REFERENCES media (id) ON UPDATE NO ACTION ON DELETE NO ACTION');
        $this->addSql('CREATE UNIQUE INDEX uniq_carrousel_position ON carrousel (position)');
    }
}
