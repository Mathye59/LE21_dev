<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20251004075418 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE entreprise ADD telephone DOUBLE PRECISION DEFAULT NULL, ADD email VARCHAR(50) NOT NULL, ADD horaires_fermeture VARCHAR(255) NOT NULL, ADD horaire_plus VARCHAR(255) DEFAULT NULL, CHANGE horaires horaires_ouverture VARCHAR(255) DEFAULT NULL');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE entreprise ADD horaires VARCHAR(255) DEFAULT NULL, DROP horaires_ouverture, DROP telephone, DROP email, DROP horaires_fermeture, DROP horaire_plus');
    }
}
