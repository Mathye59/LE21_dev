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
-- Table structure for table `article_accueil`
--

DROP TABLE IF EXISTS `article_accueil`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `article_accueil` (
  `id` int NOT NULL AUTO_INCREMENT,
  `media_id` int NOT NULL,
  `titre` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `contenu` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
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
INSERT INTO `article_accueil` VALUES (1,35,'Le 21','<div>Bienvenue au twenty one 21, votre salon de tatouage et piercing à Douai.<br /><br />Situé en plein centre-ville, notre studio a ouvert ses portes il y a maintenant 3 ans et s&#039;est rapidement imposé comme une référence dans le domaine du tatouage et du piercing dans le Douaisis. Avec plus de 140 avis positifs sur Google, nous avons su créer un lieu unique où l&#039;art, la créativité et la convivialité se rencontrent.</div>'),(2,36,'Un duo de tatoueurs passionnés','<div>Le salon Le 21 est animé par Antoine Tee one , tatoueur spécialisé dans le whip-shading, le blackwork et les tatouages aquarelle, et par Hylena Sowiink, tatoueuse reconnue pour ses créations florales, minimalistes et inspirées du cabinet de curiosités.<br /><br />A deux, nous formons une équipe complémentaire : un univers à la fois dark, artistique et détaillé, et un style fin, délicat et précieux.</div>'),(3,37,'Tatouage et piercing à Douai','<div>En plus du tatouage, nous proposons également un service de piercing professionnel ainsi qu&#039;une sélection de bijoux de qualité à prix attractifs.<br /><br />Que vous cherchiez un premier piercing discret ou une pièce plus originale, vous trouverez forcément votre bonheur dans notre collection.</div>'),(4,38,'Un salon inclusif et respectueux','<div>Au twenty one 21, nous croyons que l&#039;art du tatouage doit être accessible à tous. Nous accueillons avec respect et bienveillance des personnes de toutes origines, orientations.</div>');
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
  `titre` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `contenu` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `date` date NOT NULL,
  `resume` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci COMMENT 'Résumé court de l’article',
  PRIMARY KEY (`id`),
  UNIQUE KEY `UNIQ_7057D642EA9FDD75` (`media_id`),
  KEY `IDX_7057D64260BB6FE6` (`auteur_id`),
  CONSTRAINT `FK_7057D64260BB6FE6` FOREIGN KEY (`auteur_id`) REFERENCES `tatoueur` (`id`),
  CONSTRAINT `FK_7057D642EA9FDD75` FOREIGN KEY (`media_id`) REFERENCES `media` (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=7 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `article_blog`
--

LOCK TABLES `article_blog` WRITE;
/*!40000 ALTER TABLE `article_blog` DISABLE KEYS */;
INSERT INTO `article_blog` VALUES (1,1,35,'Se faire tatouer pour la première fois : ce que vous devez savoir','<div>Vous pensez à vous faire tatouer pour la première fois ? Que ce soit une idée que vous mûrissez depuis des mois ou une décision plus spontanée, il est normal d’avoir des questions, voire quelques appréhensions. Dans cet article, je vous partage l’essentiel à savoir avant de franchir le pas, pour que votre première expérience soit aussi agréable que possible.<br /><br />1. Choisissez le bon motif (et pour les bonnes raisons)<br />Un tatouage, c’est personnel. Prenez le temps de réfléchir à ce que vous voulez vraiment. Est-ce un symbole fort pour vous ? Un hommage ? Un motif purement esthétique ? Il n’y a pas de bonne ou de mauvaise raison, tant que c’est la vôtre.<br />💡 Conseil pro : Évitez les décisions prises sur un coup de tête. Même si certains tatouages spontanés peuvent avoir une belle histoire, mieux vaut ne rien regretter.<br /><br />2. L’emplacement, ça compte<br />Certains endroits du corps sont plus sensibles que d’autres (côtes, doigts, pieds…). D&#039;autres zones vieillissent moins bien, comme les mains ou le cou, surtout si elles sont souvent exposées au soleil.<br />🎯 Astuce : Pour un premier tatouage, je recommande souvent une zone moins sensible, comme l&#039;avant-bras ou la cuisse, pour une première expérience plus confortable.<br /><br />3. Trouvez le bon tatoueur<br />Chaque artiste a son style : old school, réaliste, minimaliste, graphique… Consultez son portfolio, regardez ses réseaux sociaux, et surtout, prenez le temps d’échanger avec lui ou elle.<br />🖌️ Chez [Nom du Studio], je m’assure toujours que le projet corresponde à votre personnalité, et je vous guide dans chaque étape.<br /><br />4. Préparez-vous pour le jour J<br />Voici quelques petits conseils pratiques :</div><ul><li>Dormez bien la veille.</li><li>Mangez un bon repas avant de venir.</li><li>Évitez l’alcool ou les médicaments fluidifiants (comme l’aspirine).</li><li>Portez des vêtements confortables.</li></ul><div>💬 Et n’hésitez pas à poser des questions ! Je suis là pour ça.<br /><br />5. Après le tatouage : les soins sont cruciaux<br />Un beau tatouage peut vite se détériorer sans les bons soins. Je vous fournirai un guide personnalisé après chaque séance, mais retenez ceci :</div><ul><li>Lavez-le délicatement.</li><li>Appliquez une crème adaptée.</li><li>Évitez le soleil, la piscine, et les frottements pendant quelques semaines.</li></ul><div>✅ Un bon suivi &#61; un beau tatouage qui dure.<br /><br />Conclusion : un acte artistique et personnel<br />Se faire tatouer, c’est bien plus que se faire &#34;dessiner la peau&#34;. C’est souvent une manière d’exprimer quelque chose de profond. En tant que tatoueur passionné, mon rôle est de vous accompagner avec bienveillance, écoute et professionnalisme.<br />👋 Envie d’en parler ? Passez me voir au studio ou prenez rendez-vous.</div>','2025-10-17','Vous pensez à vous faire tatouer pour la première fois ? Que ce soit une idée que vous mûrissez depuis des mois ou une décision plus spontanée, il est normal d’avoir des questions, voire quelques appréhensions. Dans cet article, je vous partage l’essentiel à savoir avant de franchir le pas, pour que votre première expérience soit aussi agréable que possible.'),(2,1,47,'Le tatouage minimaliste : l’art de dire beaucoup avec peu','<div> Le tatouage minimaliste repose sur des lignes épurées, des symboles fins et une grande sobriété. Inspiré par le design scandinave et la culture zen, il incarne une approche subtile de l’art corporel.<br /> Les motifs les plus populaires incluent les constellations, les cœurs fins, les mots simples ou les silhouettes d’animaux. Le succès du minimalisme vient aussi de sa versatilité : il s’adapte parfaitement à toutes les zones du corps, du poignet à la nuque.<br /> Ce type de tatouage séduit notamment les personnes qui souhaitent un premier tatouage, doux mais chargé d’émotion.<br /> 💡 <em>Note d’humour :</em> On pourrait presque l’oublier… jusqu’à ce que quelqu’un dise « oh, c’est discret » — et là on sait que c’est fait exprès. </div>','2025-10-21','Le tatouage minimaliste, tout en finesse, est devenu une véritable tendance. Discret, élégant et symbolique, il séduit ceux qui veulent un tatouage plein de sens sans extravagance.'),(3,1,48,'Tatouages et symboles spirituels : un lien entre corps et âme','<div> Le tatouage spirituel dépasse la simple esthétique. Chaque symbole, chaque forme, représente une idée, une croyance ou une étape du voyage personnel.<br /> Le <strong>mandala</strong>, par exemple, incarne l’harmonie universelle ; le <strong>lotus</strong>, la résilience et la renaissance ; la <strong>spirale celtique</strong>, l’évolution de la vie.<br /> Ces tatouages demandent souvent une approche méditative, tant dans leur conception que dans leur réalisation. Ils rappellent que l’encre, sur la peau, peut aussi être une prière silencieuse.<br /> 💡 <em>Humour :</em> Parce qu’un tatouage « juste pour l’esthétique » va très bien aussi… mais là on a un peu plus d’âme. </div>','2025-10-21','Des mandalas bouddhistes aux symboles celtiques, les tatouages spirituels relient le corps à la quête intérieure. Un art porteur de sens plus que de style.'),(4,1,49,'Le retour du tatouage japonais traditionnel','<div> L’Irezumi, tatouage japonais ancestral, se distingue par ses couleurs vives et ses motifs puissants. Chaque élément a une signification : le <strong>dragon</strong> symbolise la force et la sagesse, la <strong>carpe koi</strong> la persévérance, et la <strong>pivoine</strong> la beauté courageuse.<br /> Ce style, longtemps marginalisé au Japon, renaît aujourd’hui dans le monde entier grâce à des artistes qui allient respect de la tradition et modernité du trait.<br /> Un Irezumi complet demande du temps, du courage, et une véritable confiance entre le tatoueur et le tatoué.<br /> 🎯 <em>Petit clin d’œil :</em> Si ton tatouage me donne envie de fuir un samouraï… c’est que c’est peut-être réussi. </div>','2025-10-21','Des vagues à la Hokusai aux dragons mythiques, le style japonais (Irezumi) fait un grand retour dans les studios. Un mélange d’histoire, de puissance et d’élégance.'),(5,1,46,'Les soins après tatouage : les bons réflexes pour une peau parfaite','<div>Après un tatouage, la peau entre dans une phase de guérison qui dure environ 2 à 4 semaines. Pour éviter les infections et préserver les couleurs, il est essentiel de :<br /><br /></div><ol><li><strong>Laver délicatement</strong> la zone avec un savon doux sans parfum.<br /><br /></li><li><strong>Appliquer une crème hydratante</strong> adaptée, plusieurs fois par jour.<br /><br /></li><li><strong>Éviter le soleil</strong>, les bains prolongés et les vêtements trop serrés.<br /><br /></li><li><strong>Ne jamais gratter les croûtes</strong>, même si ça démange (c’est le piège classique).<br /> Un bon soin, c’est le secret d’un tatouage éclatant pour des années.</li></ol><div><br /></div><div> 🌟 <em>Humour léger</em> : Si tu termines avec « j’ai mutilé mon tatouage à cause d’un gratouillis », pas de panique… mets la crème, respire, et rappelle-toi que la patience est ton amie.<br /><br /></div>','2025-10-21','Un beau tatouage, c’est aussi une bonne cicatrisation. Voici les étapes essentielles pour préserver les couleurs et la qualité de ton encre.'),(6,1,45,'Tatouage botanique : la nature sous la peau','<div> Le tatouage botanique s’inspire de l’illustration naturaliste : lignes fines, dégradés subtils, et sens du détail. On le voit beaucoup sur l’avant-bras, la clavicule ou le mollet, en brins qui suivent l’anatomie.<br /> Les motifs phares : <strong>fougères</strong> (légèreté), <strong>olivier</strong> (paix), <strong>lavande</strong> (apaisement), <strong>pivoine</strong> (force délicate).<br /> Côté technique, on privilégie l’aiguille fine (single needle) et un placement qui “coule” avec les muscles. Astuce cicatrisation : hydrater sans surcharger, car les tracés fins marquent vite si la peau tire.<br /> Si on te demande si c’est une herbaria sur toi… dis “oui, et sans besoin d’arrosage”. </div>','2025-10-21','Les tatouages botaniques mêlent finesse et poésie. Feuilles, fleurs et tiges stylisées offrent des compositions élégantes, souvent en noir et gris, parfois rehaussées d’un vert discret.');
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
  `title` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `position` int DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `uniq_carrousel_media` (`media_id`),
  CONSTRAINT `FK_EF01B088EA9FDD75` FOREIGN KEY (`media_id`) REFERENCES `media` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=33 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `carrousel`
--

LOCK TABLES `carrousel` WRITE;
/*!40000 ALTER TABLE `carrousel` DISABLE KEYS */;
INSERT INTO `carrousel` VALUES (1,1,18,'1000014892-68de9886df877783163513',1),(2,1,19,'1000015230-68de98872306b987099734',2),(3,1,20,'1000017951-68de98872c2cf717491038',3),(4,1,21,'1000019454-68de988744884183635486',4),(5,1,22,'1000019582-68de98875f2c4661261786',5),(6,1,23,'1000019586-68de9887657b8449011577',6),(7,1,24,'1000019731-68de98876d723886755334',7),(8,1,25,'1000019733-68de988774635707656743',8),(9,1,26,'1000019723-68de98ade073d569310671',9),(10,1,27,'1000019724-68de98ae13ed3954275560',10),(11,1,28,'1000019725-68de98ae17ae9218612429',11),(12,1,29,'1000019726-68de98ae1ba3d060879684',12),(13,1,30,'1000019727-68de98ae1fdd3566827128',13),(18,0,35,'articleacceuil-1-68df91d8f1770338519662',18),(19,0,36,'articleacceuil-2-68e25c8a7ee5e796232428',19),(20,0,37,'articleacceuil-3-68e25bd149944519565031',20),(21,0,38,'articleacceuil-4-68e68168cc4cc073469753',21),(22,1,39,'545605212-785579734345197-8739006152444288131-n-68e686d5c9e95836846488',22),(23,1,40,'545887551-785009234402247-5974231274099301883-n-68e686d5d059b901131020',23),(24,1,41,'546444716-785579787678525-7974246464779697872-n-68e686d5d509f232855093',24),(25,1,42,'548210748-789388827297621-3720988160850230558-n-68e686d5d9210549570231',25),(26,1,43,'495364334-676050835298088-3247544017125466615-n-68f00dc7d3cab268042407',26),(27,1,44,'547825077-785009144402256-3598512334901055772-n-68f00dc7e0ad1128096366',27),(28,0,45,'626f94954cdc8059cde1cdd09500c1f8-68f7528ed35c7419179637',28),(29,0,46,'tattoo-aftercare-68f7592fe7828335476729',29),(30,0,47,'6e62ba3df6cf5a225df9216af3483884-68f75a0ab8049520789644',30),(31,0,48,'e9db608232c07990ee6e3398748d1fab-68f75b3a1ea25998816877',31),(32,0,49,'c3f63f88ab33e53043ca872278dcf98b-68f75d01d1e48624465177',32);
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
  `nom` varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=17 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `categorie`
--

LOCK TABLES `categorie` WRITE;
/*!40000 ALTER TABLE `categorie` DISABLE KEYS */;
INSERT INTO `categorie` VALUES (1,'Animaux'),(2,'Celtique'),(3,'Disney'),(4,'Fantaisie'),(5,'Fleur'),(6,'Dark'),(7,'Insecte'),(8,'Ornement'),(9,'Lettrage'),(10,'Manga'),(11,'Minimaliste'),(12,'Nature'),(13,'New school'),(14,'Poupées/Dolls'),(15,'Réaliste'),(16,'Water Color');
/*!40000 ALTER TABLE `categorie` ENABLE KEYS */;
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
  `pseudo_client` varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `texte` varchar(200) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
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
  `version` varchar(191) CHARACTER SET utf8mb3 COLLATE utf8mb3_unicode_ci NOT NULL,
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
INSERT INTO `doctrine_migration_versions` VALUES ('DoctrineMigrations\\Version20250925082406','2025-10-02 09:44:13',8781),('DoctrineMigrations\\Version20250930131647','2025-10-02 09:44:22',1534),('DoctrineMigrations\\Version20251003173836','2025-10-03 17:38:55',3257),('DoctrineMigrations\\Version20251004075418','2025-10-04 07:55:18',580),('DoctrineMigrations\\Version20251004164231','2025-10-04 16:45:28',166),('DoctrineMigrations\\Version20251004175003','2025-10-04 17:50:56',571),('DoctrineMigrations\\Version20251005102718','2025-10-05 10:27:56',470),('DoctrineMigrations\\Version20251006121453','2025-10-06 12:15:57',1177),('DoctrineMigrations\\Version20251006134303','2025-10-06 13:44:22',2277),('DoctrineMigrations\\Version20251007134349','2025-10-07 13:44:13',1130),('DoctrineMigrations\\Version20251007151648','2025-10-07 15:17:05',1564),('DoctrineMigrations\\Version20251009100243','2025-10-13 11:48:11',382),('DoctrineMigrations\\Version20251017084851','2025-10-17 08:49:57',1309),('DoctrineMigrations\\Version20251017122632','2025-10-17 12:27:01',491),('DoctrineMigrations\\Version20251017134909','2025-10-17 13:50:22',850);
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
  `nom` varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `adresse` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `facebook` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `instagram` varchar(250) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `horaires_ouverture` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `logo_name` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `telephone` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `email` varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `horaires_fermeture` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `horaire_plus` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `singleton_key` varchar(1) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'X',
  `updated_at` datetime DEFAULT NULL COMMENT '(DC2Type:datetime_immutable)',
  PRIMARY KEY (`id`),
  UNIQUE KEY `uniq_entreprise_singleton` (`singleton_key`)
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `entreprise`
--

LOCK TABLES `entreprise` WRITE;
/*!40000 ALTER TABLE `entreprise` DISABLE KEYS */;
INSERT INTO `entreprise` VALUES (1,'Le Twenty-one Tattoo & Piercing','<div>21 Rue de la République<br><br>59500 Douai<br><br>France</div>','https://www.facebook.com/profile.php?id=100086795310012&sk=about','https://www.instagram.com/le_twenty_one_21/?fbclid=IwY2xjawNOJ2JleHRuA2FlbQIxMABicmlkETAyeWF5UlpwbkpTc1dubGZpAR5S8s77H_Ttf9j0_RaaB_JUhAMlleh_yKEDNZjAY-0IKyeZW0VBct6MM-ODRQ_aem_l5lr9I6RJVI5nDPrH81zQQ#','Mercredi-Vendredi-Samedi: 10h - 19h','logo-21-photoroom-68e14bb51a1fe097182910.png','06 60 97 58 62','tee.one.tattoo@gmail.com','Lundi-Mardi-Jeudi-Dimanche: Fermé','Sur rendez-vous uniquement via contact','X',NULL);
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
  `temps` varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `statut` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `image_name` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `updated_at` datetime DEFAULT NULL COMMENT '(DC2Type:datetime_immutable)',
  `prix` decimal(8,2) DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `IDX_AFCE5F0330B0A5F2` (`tatoueur_id`),
  CONSTRAINT `FK_AFCE5F0330B0A5F2` FOREIGN KEY (`tatoueur_id`) REFERENCES `tatoueur` (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=12 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `flash`
--

LOCK TABLES `flash` WRITE;
/*!40000 ALTER TABLE `flash` DISABLE KEYS */;
INSERT INTO `flash` VALUES (1,1,'3h','disponible','dispo1-68e682feaec41863040877.jpg','2025-10-08 15:27:58',160.00),(2,1,'3h','disponible','dispo2-68e685f8aa696437663359.jpg','2025-10-08 15:40:40',200.00),(3,1,'3h','disponible','dispo3-68f20f55b4303982091563.jpg','2025-10-17 09:41:41',150.00),(4,1,'3h','disponible','dispo4-68f20fd7a2555478042255.jpg','2025-10-17 09:43:51',250.00),(5,1,'4h','disponible','dispo5-68f2105f7665c055386879.jpg','2025-10-17 09:46:07',300.00),(6,1,'2h','disponible','dispo6-68f210f82e07c086829990.jpg','2025-10-17 09:48:40',200.00),(7,1,'2h','disponible','dispo7-68f211b0c71e9755159121.jpg','2025-10-17 09:51:44',200.00),(8,1,'2h30','disponible','dispo8-68f21241670fb488875243.jpg','2025-10-17 09:54:09',125.00),(9,1,'3h','indisponible','indispo1-68f212d881f3a956053226.jpg','2025-10-17 09:56:40',275.00),(10,1,'2h','indisponible','indispo2-68f2135fe7a50962666084.jpg','2025-10-17 09:58:56',75.00),(11,1,'3h','reserve','reserve1-68f213fb79059669393355.jpg','2025-10-17 10:01:31',350.00);
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
INSERT INTO `flash_categorie` VALUES (1,1),(1,5),(2,15),(3,8),(3,15),(4,15),(5,1),(5,5),(6,1),(6,6),(7,4),(7,10),(8,1),(9,1),(9,5),(10,5),(11,6);
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
  `nom_prenom` varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `email` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `telephone` varchar(20) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `sujet` varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `message` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
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
  `filename` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `date` date NOT NULL COMMENT '(DC2Type:date_immutable)',
  `alt` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=50 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `media`
--

LOCK TABLES `media` WRITE;
/*!40000 ALTER TABLE `media` DISABLE KEYS */;
INSERT INTO `media` VALUES (18,'1000014892-68de9886df877783163513.jpg','2025-10-02',NULL),(19,'1000015230-68de98872306b987099734.jpg','2025-10-02',NULL),(20,'1000017951-68de98872c2cf717491038.jpg','2025-10-02',NULL),(21,'1000019454-68de988744884183635486.jpg','2025-10-02',NULL),(22,'1000019582-68de98875f2c4661261786.jpg','2025-10-02',NULL),(23,'1000019586-68de9887657b8449011577.jpg','2025-10-02',NULL),(24,'1000019731-68de98876d723886755334.jpg','2025-10-02',NULL),(25,'1000019733-68de988774635707656743.jpg','2025-10-02',NULL),(26,'1000019723-68de98ade073d569310671.jpg','2025-10-02',NULL),(27,'1000019724-68de98ae13ed3954275560.jpg','2025-10-02',NULL),(28,'1000019725-68de98ae17ae9218612429.jpg','2025-10-02',NULL),(29,'1000019726-68de98ae1ba3d060879684.jpg','2025-10-02',NULL),(30,'1000019727-68de98ae1fdd3566827128.jpg','2025-10-02',NULL),(35,'articleacceuil-1-68df91d8f1770338519662.jpg','2025-10-03',NULL),(36,'articleacceuil-2-68e25c8a7ee5e796232428.png','2025-10-05',NULL),(37,'articleacceuil-3-68e25bd149944519565031.png','2025-10-05',''),(38,'articleacceuil-4-68e68168cc4cc073469753.png','2025-10-08','Article 4'),(39,'545605212-785579734345197-8739006152444288131-n-68e686d5c9e95836846488.jpg','2025-10-08',NULL),(40,'545887551-785009234402247-5974231274099301883-n-68e686d5d059b901131020.jpg','2025-10-08',NULL),(41,'546444716-785579787678525-7974246464779697872-n-68e686d5d509f232855093.jpg','2025-10-08',NULL),(42,'548210748-789388827297621-3720988160850230558-n-68e686d5d9210549570231.jpg','2025-10-08',NULL),(43,'495364334-676050835298088-3247544017125466615-n-68f00dc7d3cab268042407.jpg','2025-10-15',NULL),(44,'547825077-785009144402256-3598512334901055772-n-68f00dc7e0ad1128096366.jpg','2025-10-15',NULL),(45,'626f94954cdc8059cde1cdd09500c1f8-68f7528ed35c7419179637.jpg','2025-10-21',''),(46,'tattoo-aftercare-68f7592fe7828335476729.jpg','2025-10-21',''),(47,'6e62ba3df6cf5a225df9216af3483884-68f75a0ab8049520789644.jpg','2025-10-21',''),(48,'e9db608232c07990ee6e3398748d1fab-68f75b3a1ea25998816877.jpg','2025-10-21',''),(49,'c3f63f88ab33e53043ca872278dcf98b-68f75d01d1e48624465177.jpg','2025-10-21','');
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
  `selector` varchar(20) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `hashed_token` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
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
  `nom` varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `prenom` varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `email` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `pseudo` varchar(25) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
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
INSERT INTO `tatoueur` VALUES (1,1,'Lemar%chal','Antoine','tee.one.tattoo@gmail.com','Tee-one',1);
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
  `email` varchar(180) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `roles` json NOT NULL,
  `password` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `UNIQ_IDENTIFIER_EMAIL` (`email`)
) ENGINE=InnoDB AUTO_INCREMENT=3 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `user`
--

LOCK TABLES `user` WRITE;
/*!40000 ALTER TABLE `user` DISABLE KEYS */;
INSERT INTO `user` VALUES (1,'tee.one.tattoo@gmail.com','[\"ROLE_USER\", \"ROLE_ADMIN\"]','$2y$13$VvEodedu/MMVYrTC.XgxX.o3F.UU1usTRadwkjWbQmbQMnc331MJK'),(2,'lherbiermanon@gmail.com','[\"ROLE_USER\", \"ROLE_ADMIN\"]','$2y$13$Dmc1Q2Kd67pe/N.nJCItxOVPHeZi8l3We2cCmz8ecrPXrTHMb2cgi');
/*!40000 ALTER TABLE `user` ENABLE KEYS */;
UNLOCK TABLES;
/*!40103 SET TIME_ZONE=@OLD_TIME_ZONE */;

/*!40101 SET SQL_MODE=@OLD_SQL_MODE */;
/*!40014 SET FOREIGN_KEY_CHECKS=@OLD_FOREIGN_KEY_CHECKS */;
/*!40014 SET UNIQUE_CHECKS=@OLD_UNIQUE_CHECKS */;
/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
/*!40111 SET SQL_NOTES=@OLD_SQL_NOTES */;

-- Dump completed on 2025-10-21 10:15:42
