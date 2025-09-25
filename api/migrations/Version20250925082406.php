<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20250925082406 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE article_accueil (id INT AUTO_INCREMENT NOT NULL, media_id INT NOT NULL, titre VARCHAR(100) NOT NULL, contenu LONGTEXT NOT NULL, UNIQUE INDEX UNIQ_EF4B0809EA9FDD75 (media_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE article_blog (id INT AUTO_INCREMENT NOT NULL, auteur_id INT NOT NULL, media_id INT NOT NULL, titre VARCHAR(255) NOT NULL, contenu LONGTEXT NOT NULL, date DATE NOT NULL, INDEX IDX_7057D64260BB6FE6 (auteur_id), UNIQUE INDEX UNIQ_7057D642EA9FDD75 (media_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE carrousel (id INT AUTO_INCREMENT NOT NULL, titre VARCHAR(100) DEFAULT NULL, autoplay TINYINT(1) DEFAULT NULL, interval_ms INT DEFAULT NULL, is_active TINYINT(1) DEFAULT NULL, PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE carrousel_slide (id INT AUTO_INCREMENT NOT NULL, carrousel_id INT NOT NULL, media_id INT NOT NULL, position INT NOT NULL, titre VARCHAR(100) DEFAULT NULL, lien VARCHAR(255) DEFAULT NULL, INDEX IDX_F16CA71AA511C9 (carrousel_id), INDEX IDX_F16CA7EA9FDD75 (media_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE categorie (id INT AUTO_INCREMENT NOT NULL, nom VARCHAR(50) NOT NULL, PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE commentaire (id INT AUTO_INCREMENT NOT NULL, article_id INT NOT NULL, pseudo_client VARCHAR(50) NOT NULL, texte VARCHAR(200) NOT NULL, date DATETIME NOT NULL COMMENT \'(DC2Type:datetime_immutable)\', INDEX IDX_67F068BC7294869C (article_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE entreprise (id INT AUTO_INCREMENT NOT NULL, nom VARCHAR(50) NOT NULL, adresse VARCHAR(255) NOT NULL, facebook VARCHAR(255) DEFAULT NULL, instagram VARCHAR(150) DEFAULT NULL, horaires VARCHAR(255) DEFAULT NULL, logo_name VARCHAR(255) DEFAULT NULL, PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE flash (id INT AUTO_INCREMENT NOT NULL, categorie_id INT NOT NULL, tatoueur_id INT NOT NULL, temps VARCHAR(50) DEFAULT NULL, statut VARCHAR(20) NOT NULL, image_name VARCHAR(255) DEFAULT NULL, updated_at DATETIME DEFAULT NULL COMMENT \'(DC2Type:datetime_immutable)\', INDEX IDX_AFCE5F03BCF5E72D (categorie_id), INDEX IDX_AFCE5F0330B0A5F2 (tatoueur_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE form_contact (id INT AUTO_INCREMENT NOT NULL, tatoueur_id INT DEFAULT NULL, nom_prenom VARCHAR(50) NOT NULL, email VARCHAR(255) NOT NULL, telephone VARCHAR(20) NOT NULL, sujet VARCHAR(50) NOT NULL, message LONGTEXT NOT NULL, date DATETIME NOT NULL COMMENT \'(DC2Type:datetime_immutable)\', INDEX IDX_7D0E860330B0A5F2 (tatoueur_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE media (id INT AUTO_INCREMENT NOT NULL, filename VARCHAR(255) DEFAULT NULL, date DATE NOT NULL COMMENT \'(DC2Type:date_immutable)\', PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE tatoueur (id INT AUTO_INCREMENT NOT NULL, entreprise_id INT NOT NULL, nom VARCHAR(50) NOT NULL, prenom VARCHAR(50) NOT NULL, email VARCHAR(255) NOT NULL, INDEX IDX_15966CDFA4AEAFEA (entreprise_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('ALTER TABLE article_accueil ADD CONSTRAINT FK_EF4B0809EA9FDD75 FOREIGN KEY (media_id) REFERENCES media (id)');
        $this->addSql('ALTER TABLE article_blog ADD CONSTRAINT FK_7057D64260BB6FE6 FOREIGN KEY (auteur_id) REFERENCES tatoueur (id)');
        $this->addSql('ALTER TABLE article_blog ADD CONSTRAINT FK_7057D642EA9FDD75 FOREIGN KEY (media_id) REFERENCES media (id)');
        $this->addSql('ALTER TABLE carrousel_slide ADD CONSTRAINT FK_F16CA71AA511C9 FOREIGN KEY (carrousel_id) REFERENCES carrousel (id)');
        $this->addSql('ALTER TABLE carrousel_slide ADD CONSTRAINT FK_F16CA7EA9FDD75 FOREIGN KEY (media_id) REFERENCES media (id)');
        $this->addSql('ALTER TABLE commentaire ADD CONSTRAINT FK_67F068BC7294869C FOREIGN KEY (article_id) REFERENCES article_blog (id)');
        $this->addSql('ALTER TABLE flash ADD CONSTRAINT FK_AFCE5F03BCF5E72D FOREIGN KEY (categorie_id) REFERENCES categorie (id)');
        $this->addSql('ALTER TABLE flash ADD CONSTRAINT FK_AFCE5F0330B0A5F2 FOREIGN KEY (tatoueur_id) REFERENCES tatoueur (id)');
        $this->addSql('ALTER TABLE form_contact ADD CONSTRAINT FK_7D0E860330B0A5F2 FOREIGN KEY (tatoueur_id) REFERENCES tatoueur (id)');
        $this->addSql('ALTER TABLE tatoueur ADD CONSTRAINT FK_15966CDFA4AEAFEA FOREIGN KEY (entreprise_id) REFERENCES entreprise (id)');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE article_accueil DROP FOREIGN KEY FK_EF4B0809EA9FDD75');
        $this->addSql('ALTER TABLE article_blog DROP FOREIGN KEY FK_7057D64260BB6FE6');
        $this->addSql('ALTER TABLE article_blog DROP FOREIGN KEY FK_7057D642EA9FDD75');
        $this->addSql('ALTER TABLE carrousel_slide DROP FOREIGN KEY FK_F16CA71AA511C9');
        $this->addSql('ALTER TABLE carrousel_slide DROP FOREIGN KEY FK_F16CA7EA9FDD75');
        $this->addSql('ALTER TABLE commentaire DROP FOREIGN KEY FK_67F068BC7294869C');
        $this->addSql('ALTER TABLE flash DROP FOREIGN KEY FK_AFCE5F03BCF5E72D');
        $this->addSql('ALTER TABLE flash DROP FOREIGN KEY FK_AFCE5F0330B0A5F2');
        $this->addSql('ALTER TABLE form_contact DROP FOREIGN KEY FK_7D0E860330B0A5F2');
        $this->addSql('ALTER TABLE tatoueur DROP FOREIGN KEY FK_15966CDFA4AEAFEA');
        $this->addSql('DROP TABLE article_accueil');
        $this->addSql('DROP TABLE article_blog');
        $this->addSql('DROP TABLE carrousel');
        $this->addSql('DROP TABLE carrousel_slide');
        $this->addSql('DROP TABLE categorie');
        $this->addSql('DROP TABLE commentaire');
        $this->addSql('DROP TABLE entreprise');
        $this->addSql('DROP TABLE flash');
        $this->addSql('DROP TABLE form_contact');
        $this->addSql('DROP TABLE media');
        $this->addSql('DROP TABLE tatoueur');
    }
}
