<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20251007151648 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE tatoueur ADD user_id INT DEFAULT NULL');
        $this->addSql('ALTER TABLE tatoueur ADD CONSTRAINT FK_15966CDFA76ED395 FOREIGN KEY (user_id) REFERENCES user (id)');
        $this->addSql('CREATE UNIQUE INDEX UNIQ_15966CDFA76ED395 ON tatoueur (user_id)');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE tatoueur DROP FOREIGN KEY FK_15966CDFA76ED395');
        $this->addSql('DROP INDEX UNIQ_15966CDFA76ED395 ON tatoueur');
        $this->addSql('ALTER TABLE tatoueur DROP user_id');
    }
}
