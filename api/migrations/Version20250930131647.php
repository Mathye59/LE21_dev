<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20250930131647 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE carrousel_slide DROP FOREIGN KEY FK_F16CA71AA511C9');
        $this->addSql('ALTER TABLE carrousel_slide DROP FOREIGN KEY FK_F16CA7EA9FDD75');
        $this->addSql('DROP TABLE carrousel_slide');
        $this->addSql('ALTER TABLE carrousel ADD media_id INT NOT NULL, ADD title VARCHAR(255) DEFAULT NULL, ADD position INT NOT NULL, DROP titre, DROP autoplay, DROP interval_ms, CHANGE is_active is_active TINYINT(1) DEFAULT 1 NOT NULL');
        $this->addSql('ALTER TABLE carrousel ADD CONSTRAINT FK_EF01B088EA9FDD75 FOREIGN KEY (media_id) REFERENCES media (id)');
        $this->addSql('CREATE INDEX IDX_EF01B088EA9FDD75 ON carrousel (media_id)');
        $this->addSql('CREATE UNIQUE INDEX uniq_carrousel_position ON carrousel (position)');
        $this->addSql('ALTER TABLE commentaire ADD approuve TINYINT(1) DEFAULT 0 NOT NULL');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE carrousel_slide (id INT AUTO_INCREMENT NOT NULL, carrousel_id INT NOT NULL, media_id INT NOT NULL, position INT NOT NULL, titre VARCHAR(100) CHARACTER SET utf8mb4 DEFAULT NULL COLLATE `utf8mb4_unicode_ci`, lien VARCHAR(255) CHARACTER SET utf8mb4 DEFAULT NULL COLLATE `utf8mb4_unicode_ci`, INDEX IDX_F16CA7EA9FDD75 (media_id), INDEX IDX_F16CA71AA511C9 (carrousel_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB COMMENT = \'\' ');
        $this->addSql('ALTER TABLE carrousel_slide ADD CONSTRAINT FK_F16CA71AA511C9 FOREIGN KEY (carrousel_id) REFERENCES carrousel (id) ON UPDATE NO ACTION ON DELETE NO ACTION');
        $this->addSql('ALTER TABLE carrousel_slide ADD CONSTRAINT FK_F16CA7EA9FDD75 FOREIGN KEY (media_id) REFERENCES media (id) ON UPDATE NO ACTION ON DELETE NO ACTION');
        $this->addSql('ALTER TABLE carrousel DROP FOREIGN KEY FK_EF01B088EA9FDD75');
        $this->addSql('DROP INDEX IDX_EF01B088EA9FDD75 ON carrousel');
        $this->addSql('DROP INDEX uniq_carrousel_position ON carrousel');
        $this->addSql('ALTER TABLE carrousel ADD titre VARCHAR(100) DEFAULT NULL, ADD autoplay TINYINT(1) DEFAULT NULL, ADD interval_ms INT DEFAULT NULL, DROP media_id, DROP title, DROP position, CHANGE is_active is_active TINYINT(1) DEFAULT NULL');
        $this->addSql('ALTER TABLE commentaire DROP approuve');
    }
}
