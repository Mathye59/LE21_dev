<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20251006134303 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE flash_categorie (flash_id INT NOT NULL, categorie_id INT NOT NULL, INDEX IDX_9CF2B40F25F8D5EA (flash_id), INDEX IDX_9CF2B40FBCF5E72D (categorie_id), PRIMARY KEY(flash_id, categorie_id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('ALTER TABLE flash_categorie ADD CONSTRAINT FK_9CF2B40F25F8D5EA FOREIGN KEY (flash_id) REFERENCES flash (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE flash_categorie ADD CONSTRAINT FK_9CF2B40FBCF5E72D FOREIGN KEY (categorie_id) REFERENCES categorie (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE flash DROP FOREIGN KEY FK_AFCE5F03BCF5E72D');
        $this->addSql('DROP INDEX IDX_AFCE5F03BCF5E72D ON flash');
        $this->addSql('ALTER TABLE flash DROP categorie_id');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE flash_categorie DROP FOREIGN KEY FK_9CF2B40F25F8D5EA');
        $this->addSql('ALTER TABLE flash_categorie DROP FOREIGN KEY FK_9CF2B40FBCF5E72D');
        $this->addSql('DROP TABLE flash_categorie');
        $this->addSql('ALTER TABLE flash ADD categorie_id INT NOT NULL');
        $this->addSql('ALTER TABLE flash ADD CONSTRAINT FK_AFCE5F03BCF5E72D FOREIGN KEY (categorie_id) REFERENCES categorie (id) ON UPDATE NO ACTION ON DELETE NO ACTION');
        $this->addSql('CREATE INDEX IDX_AFCE5F03BCF5E72D ON flash (categorie_id)');
    }
}
