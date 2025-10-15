-- MySQL dump 10.13  Distrib 8.0.43, for Linux (x86_64)
--
-- Host: localhost    Database: le_21
-- ------------------------------------------------------
-- Server version	8.0.43

/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!50503 SET NAMES utf8mb4 */;
/*!40103 SET @OLD_TIME_ZONE=@@TIME_ZONE */;
/*!40103 SET TIME_ZONE='+00:00' */;
/*!40014 SET @OLD_UNIQUE_CHECKS=@@UNIQUE_CHECKS, UNIQUE_CHECKS=0 */;
/*!40014 SET @OLD_FOREIGN_KEY_CHECKS=@@FOREIGN_KEY_CHECKS, FOREIGN_KEY_CHECKS=0 */;
/*!40101 SET @OLD_SQL_MODE=@@SQL_MODE, SQL_MODE='NO_AUTO_VALUE_ON_ZERO' */;
/*!40111 SET @OLD_SQL_NOTES=@@SQL_NOTES, SQL_NOTES=0 */;

--
-- Current Database: `le_21`
--

CREATE DATABASE /*!32312 IF NOT EXISTS*/ `le_21` /*!40100 DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_0900_ai_ci */ /*!80016 DEFAULT ENCRYPTION='N' */;

USE `le_21`;

--
-- Table structure for table `article_accueil`
--

DROP TABLE IF EXISTS `article_accueil`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `article_accueil` (
  `id` int NOT NULL AUTO_INCREMENT,
  `media_id` int NOT NULL,
  `titre` varchar(100) COLLATE utf8mb4_unicode_ci NOT NULL,
  `contenu` longtext COLLATE utf8mb4_unicode_ci NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `UNIQ_EF4B0809EA9FDD75` (`media_id`),
  CONSTRAINT `FK_EF4B0809EA9FDD75` FOREIGN KEY (`media_id`) REFERENCES `media` (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=5 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `article_accueil`
--

LOCK TABLES `article_accueil` WRITE;
/*!40000 ALTER TABLE `article_accueil` DISABLE KEYS */;
INSERT INTO `article_accueil` VALUES (1,35,'Le 21','<div>Bienvenue au twenty one 21, votre salon de tatouage et piercing à Douai.<br /> <br />Situé en plein centre-ville, notre studio a ouvert ses portes il y a maintenant 3 ans et s'est rapidement imposé comme une référence dans le domaine du tatouage et du piercing dans le Douaisis. Avec plus de 140 avis positifs sur Google, nous avons su créer un lieu unique où l'art, la créativité et la convivialité se rencontrent.</div>'),(2,36,'Un duo de tatoueurs passionnés','<div>Le salon Le 21 est animé par Antoine "Tee one", tatoueur spécialisé dans le whip-shading, le blackwork et les tatouages aquarelle, et par Hylena "Sowi'ink", tatoueuse reconnue pour ses créations florales, minimalistes et inspirées du cabinet de curiosités.<br /> <br />À deux, nous formons une équipe complémentaire : un univers à la fois dark, artistique et détaillé, et un style fin, délicat et précieux.</div>'),(3,37,'Tatouage et piercing à Douai','<div>En plus du tatouage, nous proposons également un service de piercing professionnel ainsi qu'une sélection de bijoux de qualité à prix attractifs. <br /> <br />Que vous cherchiez un premier piercing discret ou une pièce plus originale, vous trouverez forcément votre bonheur dans notre collection.</div>'),(4,38,'Un salon inclusif et respectueux','<div>Au twenty one 21, nous croyons que l'art du tatouage doit être accessible à tous. Nous accueillons avec respect et bienveillance des personnes de toutes origines, orientations</div>');
/*!40000 ALTER TABLE `article_accueil` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `article_blog`
--

DROP TABLE IF EXISTS `article_blog`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `article_blog` (
  `id` int NOT NULL AUTO_INCREMENT,
  `auteur_id` int NOT NULL,
  `media_id` int NOT NULL,
  `titre` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `contenu` longtext COLLATE utf8mb4_unicode_ci NOT NULL,
  `date` date NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `UNIQ_7057D642EA9FDD75` (`media_id`),
  KEY `IDX_7057D64260BB6FE6` (`auteur_id`),
  CONSTRAINT `FK_7057D64260BB6FE6` FOREIGN KEY (`auteur_id`) REFERENCES `tatoueur` (`id`),
  CONSTRAINT `FK_7057D642EA9FDD75` FOREIGN KEY (`media_id`) REFERENCES `media` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `article_blog`
--

LOCK TABLES `article_blog` WRITE;
/*!40000 ALTER TABLE `article_blog` DISABLE KEYS */;
/*!40000 ALTER TABLE `article_blog` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `carrousel`
--

DROP TABLE IF EXISTS `carrousel`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `carrousel` (
  `id` int NOT NULL AUTO_INCREMENT,
  `is_active` tinyint(1) NOT NULL DEFAULT '0',
  `media_id` int NOT NULL,
  `title` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `position` int DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `uniq_carrousel_media` (`media_id`),
  CONSTRAINT `FK_EF01B088EA9FDD75` FOREIGN KEY (`media_id`) REFERENCES `media` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=26 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `carrousel`
--

LOCK TABLES `carrousel` WRITE;
/*!40000 ALTER TABLE `carrousel` DISABLE KEYS */;
INSERT INTO `carrousel` VALUES (1,1,18,'1000014892-68de9886df877783163513',1),(2,1,19,'1000015230-68de98872306b987099734',2),(3,1,20,'1000017951-68de98872c2cf717491038',3),(4,1,21,'1000019454-68de988744884183635486',4),(5,1,22,'1000019582-68de98875f2c4661261786',5),(6,1,23,'1000019586-68de9887657b8449011577',6),(7,1,24,'1000019731-68de98876d723886755334',7),(8,1,25,'1000019733-68de988774635707656743',8),(9,1,26,'1000019723-68de98ade073d569310671',9),(10,1,27,'1000019724-68de98ae13ed3954275560',10),(11,1,28,'1000019725-68de98ae17ae9218612429',11),(12,1,29,'1000019726-68de98ae1ba3d060879684',12),(13,1,30,'1000019727-68de98ae1fdd3566827128',13),(18,0,35,'articleacceuil-1-68df91d8f1770338519662',18),(19,0,36,'articleacceuil-2-68e25c8a7ee5e796232428',19),(20,0,37,'articleacceuil-3-68e25bd149944519565031',20),(21,0,38,'articleacceuil-4-68e68168cc4cc073469753',21),(22,1,39,'545605212-785579734345197-8739006152444288131-n-68e686d5c9e95836846488',22),(23,1,40,'545887551-785009234402247-5974231274099301883-n-68e686d5d059b901131020',23),(24,1,41,'546444716-785579787678525-7974246464779697872-n-68e686d5d509f232855093',24),(25,1,42,'548210748-789388827297621-3720988160850230558-n-68e686d5d9210549570231',25);
/*!40000 ALTER TABLE `carrousel` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `categorie`
--

DROP TABLE IF EXISTS `categorie`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `categorie` (
  `id` int NOT NULL AUTO_INCREMENT,
  `nom` varchar(50) COLLATE utf8mb4_unicode_ci NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=17 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `categorie`
--

LOCK TABLES `categorie` WRITE;
/*!40000 ALTER TABLE `categorie` DISABLE KEYS */;
INSERT INTO `categorie` VALUES (1,'Animaux'),(2,'Celtique'),(3,'Disney'),(4,'Fantaisie'),(5,'Fleur'),(6,'Dark'),(7,'Insecte'),(8,'Ornement'),(9,'Lettrage'),(10,'Manga'),(11,'Minimaliste'),(12,'Nature'),(13,'New school'),(14,'Poupées/Dolls'),(15,'Réaliste'),(16,'Water Color');
/*!40000 ALTER TABLE `categorie` ENABLE KEYS`;
UNLOCK TABLES;

--
-- Table structure for table `commentaire`
--

DROP TABLE IF EXISTS `commentaire`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `commentaire` (
  `id` int NOT NULL AUTO_INCREMENT,
  `article_id` int NOT NULL,
  `pseudo_client` varchar(50) COLLATE utf8mb4_unicode_ci NOT NULL,
  `texte` varchar(200) COLLATE utf8mb4_unicode_ci NOT NULL,
  `date` datetime NOT NULL COMMENT '(DC2Type:datetime_immutable)',
  `approuve` tinyint(1) NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`),
  KEY `IDX_67F068BC7294869C` (`article_id`),
  CONSTRAINT `FK_67F068BC7294869C` FOREIGN KEY (`article_id`) REFERENCES `article_blog` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `commentaire`
--

LOCK TABLES `commentaire` WRITE;
/*!40000 ALTER TABLE `commentaire` DISABLE KEYS */;
/*!40000 ALTER TABLE `commentaire` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `doctrine_migration_versions`
--

DROP TABLE IF EXISTS `doctrine_migration_versions`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `doctrine_migration_versions` (
  `version` varchar(191) COLLATE utf8mb3_unicode_ci NOT NULL,
  `executed_at` datetime DEFAULT NULL,
  `execution_time` int DEFAULT NULL,
  PRIMARY KEY (`version`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3 COLLATE=utf8mb3_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `doctrine_migration_versions`
--

LOCK TABLES `doctrine_migration_versions` WRITE;
/*!40000 ALTER TABLE `doctrine_migration_versions` DISABLE KEYS */;
INSERT INTO `doctrine_migration_versions` VALUES ('DoctrineMigrations\\Version20250925082406','2025-10-02 09:44:13',8781),('DoctrineMigrations\\Version20250930131647','2025-10-02 09:44:22',1534),('DoctrineMigrations\\Version20251003173836','2025-10-03 17:38:55',3257),('DoctrineMigrations\\Version20251004075418','2025-10-04 07:55:18',580),('DoctrineMigrations\\Version20251004164231','2025-10-04 16:45:28',166),('DoctrineMigrations\\Version20251004175003','2025-10-04 17:50:56',571),('DoctrineMigrations\\Version20251005102718','2025-10-05 10:27:56',470),('DoctrineMigrations\\Version20251006121453','2025-10-06 12:15:57',1177),('DoctrineMigrations\\Version20251006134303','2025-10-06 13:44:22',2277),('DoctrineMigrations\\Version20251007134349','2025-10-07 13:44:13',1130),('DoctrineMigrations\\Version20251007151648','2025-10-07 15:17:05',1564);
/*!40000 ALTER TABLE `doctrine_migration_versions` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `entreprise`
--

DROP TABLE IF EXISTS `entreprise`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `entreprise` (
  `id` int NOT NULL AUTO_INCREMENT,
  `nom` varchar(50) COLLATE utf8mb4_unicode_ci NOT NULL,
  `adresse` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `facebook` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `instagram` varchar(250) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `horaires_ouverture` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `logo_name` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `telephone` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `email` varchar(50) COLLATE utf8mb4_unicode_ci NOT NULL,
  `horaires_fermeture` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `horaire_plus` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `entreprise`
--

LOCK TABLES `entreprise` WRITE;
/*!40000 ALTER TABLE `entreprise` DISABLE KEYS */;
INSERT INTO `entreprise` VALUES (1,'Le Twenty-one Tattoo & Piercing','<div>21 Rue de la République<br><br>59500 Douai<br><br>France</div>','https://www.facebook.com/profile.php?id=100086795310012&sk=about','https://www.instagram.com/le_twenty_one_21/?fbclid=IwY2xjawNOJ2JleHRuA2FlbQIxMABicmlkETAyeWF5UlpwbkpTc1dubGZpAR5S8s77H_Ttf9j0_RaaB_JUhAMlleh_yKEDNZjAY-0IKyeZW0VBct6MM-ODRQ_aem_l5lr9I6RJVI5nDPrH81zQQ#','Mercredi-Vendredi-Samedi: 10h - 19h','logo-21-photoroom-68e14f78b2d79471794111.png','06 60 97 58 62','tee.one.tattoo@gmail.com','Lundi-Mardi-Jeudi-Dimanche: Fermé','Sur rendez-vous uniquement via contact');
/*!40000 ALTER TABLE `entreprise` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `flash`
--

DROP TABLE IF EXISTS `flash`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `flash` (
  `id` int NOT NULL AUTO_INCREMENT,
  `tatoueur_id` int NOT NULL,
  `temps` varchar(50) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `statut` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `image_name` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `updated_at` datetime DEFAULT NULL COMMENT '(DC2Type:datetime_immutable)',
  PRIMARY KEY (`id`),
  KEY `IDX_AFCE5F0330B0A5F2` (`tatoueur_id`),
  CONSTRAINT `FK_AFCE5F0330B0A5F2` FOREIGN KEY (`tatoueur_id`) REFERENCES `tatoueur` (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=3 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `flash`
--

LOCK TABLES `flash` WRITE;
/*!40000 ALTER TABLE `flash` DISABLE KEYS */;
INSERT INTO `flash` VALUES (1,1,'3h','disponible','dispo1-68e682feaec41863040877.jpg','2025-10-08 15:27:58'),(2,1,'3h','disponible','dispo2-68e685f8aa696437663359.jpg','2025-10-08 15:40:40');
/*!40000 ALTER TABLE `flash` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `flash_categorie`
--

DROP TABLE IF EXISTS `flash_categorie`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `flash_categorie` (
  `flash_id` int NOT NULL,
  `categorie_id` int NOT NULL,
  PRIMARY KEY (`flash_id`,`categorie_id`),
  KEY `IDX_9CF2B40F25F8D5EA` (`flash_id`),
  KEY `IDX_9CF2B40FBCF5E72D` (`categorie_id`),
  CONSTRAINT `FK_9CF2B40F25F8D5EA` FOREIGN KEY (`flash_id`) REFERENCES `flash` (`id`) ON DELETE CASCADE,
  CONSTRAINT `FK_9CF2B40FBCF5E72D` FOREIGN KEY (`categorie_id`) REFERENCES `categorie` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `flash_categorie`
--

LOCK TABLES `flash_categorie` WRITE;
/*!40000 ALTER TABLE `flash_categorie` DISABLE KEYS */;
INSERT INTO `flash_categorie` VALUES (1,1),(1,5),(2,15);
/*!40000 ALTER TABLE `flash_categorie` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `form_contact`
--

DROP TABLE IF EXISTS `form_contact`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `form_contact` (
  `id` int NOT NULL AUTO_INCREMENT,
  `tatoueur_id` int DEFAULT NULL,
  `nom_prenom` varchar(50) COLLATE utf8mb4_unicode_ci NOT NULL,
  `email` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `telephone` varchar(20) COLLATE utf8mb4_unicode_ci NOT NULL,
  `sujet` varchar(50) COLLATE utf8mb4_unicode_ci NOT NULL,
  `message` longtext COLLATE utf8mb4_unicode_ci NOT NULL,
  `date` datetime NOT NULL COMMENT '(DC2Type:datetime_immutable)',
  PRIMARY KEY (`id`),
  KEY `IDX_7D0E860330B0A5F2` (`tatoueur_id`),
  CONSTRAINT `FK_7D0E860330B0A5F2` FOREIGN KEY (`tatoueur_id`) REFERENCES `tatoueur` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `form_contact`
--

LOCK TABLES `form_contact` WRITE;
/*!40000 ALTER TABLE `form_contact` DISABLE KEYS */;
/*!40000 ALTER TABLE `form_contact` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `media`
--

DROP TABLE IF EXISTS `media`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `media` (
  `id` int NOT NULL AUTO_INCREMENT,
  `filename` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `date` date NOT NULL COMMENT '(DC2Type:date_immutable)',
  `alt` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=43 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `media`
--

LOCK TABLES `media` WRITE;
/*!40000 ALTER TABLE `media` DISABLE KEYS */;
INSERT INTO `media` VALUES (18,'1000014892-68de9886df877783163513.jpg','2025-10-02',NULL),(19,'1000015230-68de98872306b987099734.jpg','2025-10-02',NULL),(20,'1000017951-68de98872c2cf717491038.jpg','2025-10-02',NULL),(21,'1000019454-68de988744884183635486.jpg','2025-10-02',NULL),(22,'1000019582-68de98875f2c4661261786.jpg','2025-10-02',NULL),(23,'1000019586-68de9887657b8449011577.jpg','2025-10-02',NULL),(24,'1000019731-68de98876d723886755334.jpg','2025-10-02',NULL),(25,'1000019733-68de988774635707656743.jpg','2025-10-02',NULL),(26,'1000019723-68de98ade073d569310671.jpg','2025-10-02',NULL),(27,'1000019724-68de98ae13ed3954275560.jpg','2025-10-02',NULL),(28,'1000019725-68de98ae17ae9218612429.jpg','2025-10-02',NULL),(29,'1000019726-68de98ae1ba3d060879684.jpg','2025-10-02',NULL),(30,'1000019727-68de98ae1fdd3566827128.jpg','2025-10-02',NULL),(35,'articleacceuil-1-68df91d8f1770338519662.jpg','2025-10-03',NULL),(36,'articleacceuil-2-68e25c8a7ee5e796232428.png','2025-10-05',NULL),(37,'articleacceuil-3-68e25bd149944519565031.png','2025-10-05',''),(38,'articleacceuil-4-68e68168cc4cc073469753.png','2025-10-08','Article 4'),(39,'545605212-785579734345197-8739006152444288131-n-68e686d5c9e95836846488.jpg','2025-10-08',NULL),(40,'545887551-785009234402247-5974231274099301883-n-68e686d5d059b901131020.jpg','2025-10-08',NULL),(41,'546444716-785579787678525-7974246464779697872-n-68e686d5d509f232855093.jpg','2025-10-08',NULL),(42,'548210748-789388827297621-3720988160850230558-n-68e686d5d9210549570231.jpg','2025-10-08',NULL);
/*!40000 ALTER TABLE `media` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `reset_password_request`
--

DROP TABLE IF EXISTS `reset_password_request`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `reset_password_request` (
  `id` int NOT NULL AUTO_INCREMENT,
  `user_id` int NOT NULL,
  `selector` varchar(20) COLLATE utf8mb4_unicode_ci NOT NULL,
  `hashed_token` varchar(100) COLLATE utf8mb4_unicode_ci NOT NULL,
  `requested_at` datetime NOT NULL COMMENT '(DC2Type:datetime_immutable)',
  `expires_at` datetime NOT NULL COMMENT '(DC2Type:datetime_immutable)',
  PRIMARY KEY (`id`),
  KEY `IDX_7CE748AA76ED395` (`user_id`),
  CONSTRAINT `FK_7CE748AA76ED395` FOREIGN KEY (`user_id`) REFERENCES `user` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `reset_password_request`
--

LOCK TABLES `reset_password_request` WRITE;
/*!40000 ALTER TABLE `reset_password_request` DISABLE KEYS */;
/*!40000 ALTER TABLE `reset_password_request` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `tatoueur`
--

DROP TABLE IF EXISTS `tatoueur`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `tatoueur` (
  `id` int NOT NULL AUTO_INCREMENT,
  `entreprise_id` int NOT NULL,
  `nom` varchar(50) COLLATE utf8mb4_unicode_ci NOT NULL,
  `prenom` varchar(50) COLLATE utf8mb4_unicode_ci NOT NULL,
  `email` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `pseudo` varchar(25) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `user_id` int DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `UNIQ_15966CDFA76ED395` (`user_id`),
  KEY `IDX_15966CDFA4AEAFEA` (`entreprise_id`),
  CONSTRAINT `FK_15966CDFA4AEAFEA` FOREIGN KEY (`entreprise_id`) REFERENCES `entreprise` (`id`),
  CONSTRAINT `FK_15966CDFA76ED395` FOREIGN KEY (`user_id`) REFERENCES `user` (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `tatoueur`
--

LOCK TABLES `tatoueur` WRITE;
/*!40000 ALTER TABLE `tatoueur` DISABLE KEYS */;
INSERT INTO `tatoueur` VALUES (1,1,'Lemaréchal','Antoine','tee.one.tattoo@gmail.com','Tee-one',1);
/*!40000 ALTER TABLE `tatoueur` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `user`
--

DROP TABLE IF EXISTS `user`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `user` (
  `id` int NOT NULL AUTO_INCREMENT,
  `email` varchar(180