-- MySQL dump 10.13  Distrib 8.0.45, for Linux (x86_64)
--
-- Host: 127.0.0.1    Database: miumi_db_production
-- ------------------------------------------------------
-- Server version	8.0.45-0ubuntu0.24.04.1

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
-- Table structure for table `academic_profiles`
--

DROP TABLE IF EXISTS `academic_profiles`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `academic_profiles` (
  `user_id` bigint unsigned NOT NULL,
  `modules` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin,
  `career_id` bigint unsigned DEFAULT NULL,
  `semestre` int DEFAULT NULL,
  `departamento` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `anios_activos` tinyint unsigned DEFAULT NULL,
  `status` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `matricula` varchar(20) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `documentoSEP_path` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `is_anfitrion` tinyint(1) NOT NULL DEFAULT '0',
  `doc_acta_nacimiento` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `doc_certificado_prepa` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `doc_curp` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `doc_ine` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `rol` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin,
  `documentos` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `doc_acta_rechazado` tinyint(1) NOT NULL DEFAULT '0',
  `doc_certificado_rechazado` tinyint(1) NOT NULL DEFAULT '0',
  `doc_curp_rechazado` tinyint(1) NOT NULL DEFAULT '0',
  `doc_ine_rechazado` tinyint(1) NOT NULL DEFAULT '0',
  `doc_ficha_pago` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `doc_factura_xml` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `doc_ficha_pago_rechazado` tinyint(1) NOT NULL DEFAULT '0',
  `doc_factura_xml_rechazado` tinyint(1) NOT NULL DEFAULT '0',
  PRIMARY KEY (`user_id`),
  UNIQUE KEY `academic_profiles_matricula_unique` (`matricula`),
  KEY `academic_profiles_career_id_foreign` (`career_id`),
  CONSTRAINT `academic_profiles_career_id_foreign` FOREIGN KEY (`career_id`) REFERENCES `careers` (`id`) ON DELETE SET NULL,
  CONSTRAINT `academic_profiles_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE,
  CONSTRAINT `academic_profiles_chk_1` CHECK (json_valid(`modules`)),
  CONSTRAINT `academic_profiles_chk_2` CHECK (json_valid(`rol`)),
  CONSTRAINT `academic_profiles_chk_3` CHECK (json_valid(`documentos`))
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `academic_profiles`
--

LOCK TABLES `academic_profiles` WRITE;
/*!40000 ALTER TABLE `academic_profiles` DISABLE KEYS */;
INSERT INTO `academic_profiles` VALUES (107,NULL,1,1,NULL,NULL,'Aspirante',NULL,NULL,0,'documentos/107/expediente/rW9cN0yKy4exZK6VkBpm1qS00YbYBKw1vuGXbFM3.pdf','documentos/107/expediente/LSP0OwtMM30VN7NqjKBAlPNdXs4dSNXJAEaxWxme.pdf','documentos/107/expediente/CnJUdzzTqk8fFWmiZe9CEtR4uylZ5CpXTYdHhBJb.pdf','documentos/107/expediente/09R95NEL60nbLn60XdftsRrddxpV0IwC5rz2hHEJ.webp',NULL,NULL,'2026-04-27 22:30:07','2026-04-27 22:37:00',0,0,0,0,'facturas/107/OUcDyUWMKs0w3IY40YGxxJKrvPnMv2GWSzTAArYK.pdf',NULL,0,0);
/*!40000 ALTER TABLE `academic_profiles` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `activities`
--

DROP TABLE IF EXISTS `activities`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `activities` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `topic_id` bigint unsigned DEFAULT NULL,
  `subtopic_id` bigint unsigned DEFAULT NULL,
  `course_id` bigint unsigned DEFAULT NULL,
  `title` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `description` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci,
  `type` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `content` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin NOT NULL,
  `is_final_exam` tinyint(1) NOT NULL DEFAULT '0',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `activities_topic_id_foreign` (`topic_id`),
  KEY `activities_subtopic_id_foreign` (`subtopic_id`),
  KEY `activities_course_id_foreign` (`course_id`),
  CONSTRAINT `activities_course_id_foreign` FOREIGN KEY (`course_id`) REFERENCES `courses` (`id`) ON DELETE CASCADE,
  CONSTRAINT `activities_subtopic_id_foreign` FOREIGN KEY (`subtopic_id`) REFERENCES `subtopics` (`id`) ON DELETE CASCADE,
  CONSTRAINT `activities_topic_id_foreign` FOREIGN KEY (`topic_id`) REFERENCES `topics` (`id`) ON DELETE CASCADE,
  CONSTRAINT `activities_chk_1` CHECK (json_valid(`content`))
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `activities`
--

LOCK TABLES `activities` WRITE;
/*!40000 ALTER TABLE `activities` DISABLE KEYS */;
/*!40000 ALTER TABLE `activities` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `addresses`
--

DROP TABLE IF EXISTS `addresses`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `addresses` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `colonia` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `calle` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `ciudad` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `estado` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `codigo_postal` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `addresses`
--

LOCK TABLES `addresses` WRITE;
/*!40000 ALTER TABLE `addresses` DISABLE KEYS */;
INSERT INTO `addresses` VALUES (1,'Prueba','Prueba','Prueba','Prueba','39810','2026-04-27 22:36:59','2026-04-27 22:36:59');
/*!40000 ALTER TABLE `addresses` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `becas_documentos`
--

DROP TABLE IF EXISTS `becas_documentos`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `becas_documentos` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `user_id` bigint unsigned NOT NULL,
  `nombre_documento` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `descripcion` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci,
  `archivo_path` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `mime_type` varchar(120) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `tamano_bytes` bigint unsigned DEFAULT NULL,
  `uploaded_by` bigint unsigned DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `becas_documentos_uploaded_by_foreign` (`uploaded_by`),
  KEY `becas_documentos_user_id_created_at_index` (`user_id`,`created_at`),
  CONSTRAINT `becas_documentos_uploaded_by_foreign` FOREIGN KEY (`uploaded_by`) REFERENCES `users` (`id`) ON DELETE SET NULL,
  CONSTRAINT `becas_documentos_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `becas_documentos`
--

LOCK TABLES `becas_documentos` WRITE;
/*!40000 ALTER TABLE `becas_documentos` DISABLE KEYS */;
/*!40000 ALTER TABLE `becas_documentos` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `billing_concepts`
--

DROP TABLE IF EXISTS `billing_concepts`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `billing_concepts` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `institution_id` bigint unsigned NOT NULL,
  `concept` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `amount` decimal(10,2) NOT NULL,
  `porcentaje_cargo_moratorio` decimal(5,2) DEFAULT NULL,
  `cargo_monetario` decimal(12,2) DEFAULT NULL,
  `description` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci,
  `is_active` tinyint(1) NOT NULL DEFAULT '1',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `billing_concepts`
--

LOCK TABLES `billing_concepts` WRITE;
/*!40000 ALTER TABLE `billing_concepts` DISABLE KEYS */;
/*!40000 ALTER TABLE `billing_concepts` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `billings`
--

DROP TABLE IF EXISTS `billings`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `billings` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `factura_uid` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `user_id` bigint unsigned NOT NULL,
  `period_id` bigint unsigned DEFAULT NULL,
  `concepto` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `monto` decimal(10,2) NOT NULL,
  `porcentaje_cargo_moratorio` decimal(5,2) DEFAULT NULL,
  `cargo_monetario` decimal(12,2) DEFAULT NULL,
  `fecha_vencimiento` date NOT NULL,
  `archivo_path` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `xml_path` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `status` enum('Pendiente','Abonado','Pagada') CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'Pendiente',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `billings_factura_uid_unique` (`factura_uid`),
  KEY `billings_user_id_foreign` (`user_id`),
  KEY `billings_period_id_foreign` (`period_id`),
  CONSTRAINT `billings_period_id_foreign` FOREIGN KEY (`period_id`) REFERENCES `periods` (`id`) ON DELETE SET NULL,
  CONSTRAINT `billings_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `billings`
--

LOCK TABLES `billings` WRITE;
/*!40000 ALTER TABLE `billings` DISABLE KEYS */;
INSERT INTO `billings` VALUES (1,'INS-20260427000001',107,2,'Inscripción',18000.00,NULL,NULL,'2026-04-27','facturas/107/OUcDyUWMKs0w3IY40YGxxJKrvPnMv2GWSzTAArYK.pdf',NULL,'Pagada','2026-04-27 22:37:00','2026-04-27 22:37:00',NULL);
/*!40000 ALTER TABLE `billings` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `cache`
--

DROP TABLE IF EXISTS `cache`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `cache` (
  `key` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `value` mediumtext CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `expiration` int NOT NULL,
  PRIMARY KEY (`key`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `cache`
--

LOCK TABLES `cache` WRITE;
/*!40000 ALTER TABLE `cache` DISABLE KEYS */;
INSERT INTO `cache` VALUES ('miumi-cache-356a192b7913b04c54574d18c28d46e6395428ab','i:1;',1777329514),('miumi-cache-356a192b7913b04c54574d18c28d46e6395428ab:timer','i:1777329514;',1777329514),('miumi-cache-e114c448f4ab8554ad14eff3d66dfeb3965ce8fc','i:1;',1777508721),('miumi-cache-e114c448f4ab8554ad14eff3d66dfeb3965ce8fc:timer','i:1777508721;',1777508721);
/*!40000 ALTER TABLE `cache` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `cache_locks`
--

DROP TABLE IF EXISTS `cache_locks`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `cache_locks` (
  `key` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `owner` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `expiration` int NOT NULL,
  PRIMARY KEY (`key`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `cache_locks`
--

LOCK TABLES `cache_locks` WRITE;
/*!40000 ALTER TABLE `cache_locks` DISABLE KEYS */;
/*!40000 ALTER TABLE `cache_locks` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `career_classifications`
--

DROP TABLE IF EXISTS `career_classifications`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `career_classifications` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `institution_id` bigint unsigned NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `career_classifications_institution_id_name_unique` (`institution_id`,`name`),
  CONSTRAINT `career_classifications_institution_id_foreign` FOREIGN KEY (`institution_id`) REFERENCES `institutions` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=4 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `career_classifications`
--

LOCK TABLES `career_classifications` WRITE;
/*!40000 ALTER TABLE `career_classifications` DISABLE KEYS */;
INSERT INTO `career_classifications` VALUES (1,'Licenciatura',4,'2026-04-27 22:21:54','2026-04-27 22:21:54'),(2,'Posgrado',4,'2026-04-30 00:31:22','2026-04-30 00:31:22'),(3,'Educación Ejecutiva',4,'2026-04-30 00:31:33','2026-04-30 00:31:33');
/*!40000 ALTER TABLE `career_classifications` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `career_user`
--

DROP TABLE IF EXISTS `career_user`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `career_user` (
  `user_id` bigint unsigned NOT NULL,
  `career_id` bigint unsigned NOT NULL,
  PRIMARY KEY (`user_id`,`career_id`),
  KEY `career_user_career_id_foreign` (`career_id`),
  CONSTRAINT `career_user_career_id_foreign` FOREIGN KEY (`career_id`) REFERENCES `careers` (`id`) ON DELETE CASCADE,
  CONSTRAINT `career_user_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `career_user`
--

LOCK TABLES `career_user` WRITE;
/*!40000 ALTER TABLE `career_user` DISABLE KEYS */;
/*!40000 ALTER TABLE `career_user` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `careers`
--

DROP TABLE IF EXISTS `careers`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `careers` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `official_id` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `name` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `description1` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `description2` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `description3` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `type` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `semesters` int NOT NULL DEFAULT '1',
  `pricing_mode` varchar(20) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'uniform',
  `monthly_prices` json DEFAULT NULL,
  `credits` int NOT NULL DEFAULT '0',
  `monto_mensualidad` decimal(12,2) DEFAULT NULL,
  `porcentaje_cargo_moratorio` decimal(5,2) DEFAULT NULL,
  `cargo_monetario` decimal(14,2) DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `institution_id` bigint unsigned NOT NULL,
  `career_classification_id` bigint unsigned DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `careers_official_id_unique` (`official_id`),
  KEY `careers_institution_id_foreign` (`institution_id`),
  KEY `careers_career_classification_id_foreign` (`career_classification_id`),
  CONSTRAINT `careers_career_classification_id_foreign` FOREIGN KEY (`career_classification_id`) REFERENCES `career_classifications` (`id`) ON DELETE SET NULL,
  CONSTRAINT `careers_institution_id_foreign` FOREIGN KEY (`institution_id`) REFERENCES `institutions` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=3 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `careers`
--

LOCK TABLES `careers` WRITE;
/*!40000 ALTER TABLE `careers` DISABLE KEYS */;
INSERT INTO `careers` VALUES (1,'34532','Gastronomia','Prueba',NULL,NULL,'Presencial',6,'uniform',NULL,0,3000.00,15.00,2700.00,'2026-04-27 22:22:40','2026-04-27 22:22:40',4,1),(2,'r2222','gn','ewrwerwer',NULL,NULL,'Presencial',8,'uniform',NULL,0,1.00,2.00,0.16,'2026-04-30 00:40:16','2026-04-30 00:40:16',4,1);
/*!40000 ALTER TABLE `careers` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `carreras`
--

DROP TABLE IF EXISTS `carreras`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `carreras` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `nombre` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `carreras`
--

LOCK TABLES `carreras` WRITE;
/*!40000 ALTER TABLE `carreras` DISABLE KEYS */;
/*!40000 ALTER TABLE `carreras` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `clase_alumno`
--

DROP TABLE IF EXISTS `clase_alumno`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `clase_alumno` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `clase_id` bigint unsigned NOT NULL,
  `alumno_id` bigint unsigned NOT NULL,
  `parcial_1` decimal(5,2) DEFAULT NULL,
  `parcial_2` decimal(5,2) DEFAULT NULL,
  `parcial_3` decimal(5,2) DEFAULT NULL,
  `parcial_4` decimal(5,2) DEFAULT NULL,
  `calificacion_final` decimal(5,2) DEFAULT NULL,
  `asistencia` decimal(5,2) NOT NULL DEFAULT '100.00',
  `status` enum('cursando','aprobado','reprobado','baja') CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'cursando',
  `observaciones` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `clase_alumno_clase_id_alumno_id_unique` (`clase_id`,`alumno_id`),
  KEY `clase_alumno_alumno_id_foreign` (`alumno_id`),
  CONSTRAINT `clase_alumno_alumno_id_foreign` FOREIGN KEY (`alumno_id`) REFERENCES `users` (`id`) ON DELETE CASCADE,
  CONSTRAINT `clase_alumno_clase_id_foreign` FOREIGN KEY (`clase_id`) REFERENCES `clases` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `clase_alumno`
--

LOCK TABLES `clase_alumno` WRITE;
/*!40000 ALTER TABLE `clase_alumno` DISABLE KEYS */;
/*!40000 ALTER TABLE `clase_alumno` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `clase_inscripciones`
--

DROP TABLE IF EXISTS `clase_inscripciones`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `clase_inscripciones` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `user_id` bigint unsigned NOT NULL,
  `horario_clase_id` bigint unsigned NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `clase_inscripciones_user_id_horario_clase_id_unique` (`user_id`,`horario_clase_id`),
  KEY `clase_inscripciones_horario_clase_id_foreign` (`horario_clase_id`),
  CONSTRAINT `clase_inscripciones_horario_clase_id_foreign` FOREIGN KEY (`horario_clase_id`) REFERENCES `horario_clases` (`id`) ON DELETE CASCADE,
  CONSTRAINT `clase_inscripciones_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `clase_inscripciones`
--

LOCK TABLES `clase_inscripciones` WRITE;
/*!40000 ALTER TABLE `clase_inscripciones` DISABLE KEYS */;
/*!40000 ALTER TABLE `clase_inscripciones` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `clases`
--

DROP TABLE IF EXISTS `clases`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `clases` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `materia_id` bigint unsigned NOT NULL,
  `docente_id` bigint unsigned NOT NULL,
  `period_id` bigint unsigned NOT NULL,
  `career_id` bigint unsigned NOT NULL,
  `grupo` varchar(10) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'A',
  `semestre` tinyint NOT NULL DEFAULT '1',
  `capacidad` int NOT NULL DEFAULT '30',
  `facility_id` bigint unsigned DEFAULT NULL,
  `horario_texto` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `status` enum('activa','finalizada','cancelada') CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'activa',
  `calificaciones_capturadas` tinyint(1) NOT NULL DEFAULT '0',
  `fecha_cierre_calificaciones` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `clase_unica` (`materia_id`,`docente_id`,`period_id`,`grupo`),
  KEY `clases_docente_id_foreign` (`docente_id`),
  KEY `clases_period_id_foreign` (`period_id`),
  KEY `clases_career_id_foreign` (`career_id`),
  KEY `clases_facility_id_foreign` (`facility_id`),
  CONSTRAINT `clases_career_id_foreign` FOREIGN KEY (`career_id`) REFERENCES `careers` (`id`) ON DELETE CASCADE,
  CONSTRAINT `clases_docente_id_foreign` FOREIGN KEY (`docente_id`) REFERENCES `users` (`id`) ON DELETE CASCADE,
  CONSTRAINT `clases_facility_id_foreign` FOREIGN KEY (`facility_id`) REFERENCES `facilities` (`id`) ON DELETE SET NULL,
  CONSTRAINT `clases_materia_id_foreign` FOREIGN KEY (`materia_id`) REFERENCES `materias` (`id`) ON DELETE CASCADE,
  CONSTRAINT `clases_period_id_foreign` FOREIGN KEY (`period_id`) REFERENCES `periods` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `clases`
--

LOCK TABLES `clases` WRITE;
/*!40000 ALTER TABLE `clases` DISABLE KEYS */;
/*!40000 ALTER TABLE `clases` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `comisiones`
--

DROP TABLE IF EXISTS `comisiones`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `comisiones` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `clasificacion` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `producto` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `precio` decimal(10,2) NOT NULL,
  `porcentaje` decimal(5,2) NOT NULL,
  `total` decimal(10,2) NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `comisiones`
--

LOCK TABLES `comisiones` WRITE;
/*!40000 ALTER TABLE `comisiones` DISABLE KEYS */;
INSERT INTO `comisiones` VALUES (1,'1','Gastronomia',3000.00,5.00,150.00,'2026-04-27 22:23:23','2026-04-27 22:23:23');
/*!40000 ALTER TABLE `comisiones` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `completions`
--

DROP TABLE IF EXISTS `completions`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `completions` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `user_id` bigint unsigned NOT NULL,
  `completable_type` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `completable_id` bigint unsigned NOT NULL,
  `score` decimal(5,2) DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `user_completion_unique` (`user_id`,`completable_id`,`completable_type`),
  KEY `completions_completable_type_completable_id_index` (`completable_type`,`completable_id`),
  CONSTRAINT `completions_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `completions`
--

LOCK TABLES `completions` WRITE;
/*!40000 ALTER TABLE `completions` DISABLE KEYS */;
/*!40000 ALTER TABLE `completions` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `control_escolar_documentos`
--

DROP TABLE IF EXISTS `control_escolar_documentos`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `control_escolar_documentos` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `user_id` bigint unsigned NOT NULL,
  `modulo` enum('becas','titulacion') CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `nombre_documento` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `descripcion` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci,
  `archivo_path` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `mime_type` varchar(120) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `tamano_bytes` bigint unsigned DEFAULT NULL,
  `uploaded_by` bigint unsigned DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `control_escolar_documentos_user_id_foreign` (`user_id`),
  KEY `control_escolar_documentos_uploaded_by_foreign` (`uploaded_by`),
  KEY `control_escolar_documentos_modulo_user_id_index` (`modulo`,`user_id`),
  CONSTRAINT `control_escolar_documentos_uploaded_by_foreign` FOREIGN KEY (`uploaded_by`) REFERENCES `users` (`id`) ON DELETE SET NULL,
  CONSTRAINT `control_escolar_documentos_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `control_escolar_documentos`
--

LOCK TABLES `control_escolar_documentos` WRITE;
/*!40000 ALTER TABLE `control_escolar_documentos` DISABLE KEYS */;
/*!40000 ALTER TABLE `control_escolar_documentos` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `corporate_profiles`
--

DROP TABLE IF EXISTS `corporate_profiles`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `corporate_profiles` (
  `user_id` bigint unsigned NOT NULL,
  `unidad_negocio` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `rol` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `department_id` bigint unsigned DEFAULT NULL,
  `workstation_id` bigint unsigned DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`user_id`),
  KEY `corporate_profiles_department_id_foreign` (`department_id`),
  KEY `corporate_profiles_workstation_id_foreign` (`workstation_id`),
  CONSTRAINT `corporate_profiles_department_id_foreign` FOREIGN KEY (`department_id`) REFERENCES `departments` (`id`) ON DELETE SET NULL,
  CONSTRAINT `corporate_profiles_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE,
  CONSTRAINT `corporate_profiles_workstation_id_foreign` FOREIGN KEY (`workstation_id`) REFERENCES `workstations` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `corporate_profiles`
--

LOCK TABLES `corporate_profiles` WRITE;
/*!40000 ALTER TABLE `corporate_profiles` DISABLE KEYS */;
INSERT INTO `corporate_profiles` VALUES (106,NULL,NULL,NULL,NULL,'2026-04-27 22:25:32','2026-04-27 22:25:32');
/*!40000 ALTER TABLE `corporate_profiles` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `course_user`
--

DROP TABLE IF EXISTS `course_user`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `course_user` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `user_id` bigint unsigned NOT NULL,
  `progress` int NOT NULL DEFAULT '0',
  `course_id` bigint unsigned NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `course_user_user_id_course_id_unique` (`user_id`,`course_id`),
  KEY `course_user_course_id_foreign` (`course_id`),
  CONSTRAINT `course_user_course_id_foreign` FOREIGN KEY (`course_id`) REFERENCES `courses` (`id`) ON DELETE CASCADE,
  CONSTRAINT `course_user_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `course_user`
--

LOCK TABLES `course_user` WRITE;
/*!40000 ALTER TABLE `course_user` DISABLE KEYS */;
/*!40000 ALTER TABLE `course_user` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `courses`
--

DROP TABLE IF EXISTS `courses`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `courses` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `title` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `description` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `credits` int NOT NULL DEFAULT '0',
  `hours` int NOT NULL DEFAULT '0',
  `image` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `guide_material_path` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `cert_bg_path` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `cert_sig_1_path` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `cert_sig_2_path` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `cert_sig_1_name` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `cert_sig_2_name` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `instructor_id` bigint unsigned NOT NULL,
  `institution_id` bigint unsigned NOT NULL,
  `target_department_career` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL COMMENT 'Carrera (Académico) o Nombre del Departamento (Corporativo)',
  `target_job_title` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL COMMENT 'Puesto de trabajo específico para el filtrado corporativo',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `courses_instructor_id_foreign` (`instructor_id`),
  KEY `courses_institution_id_foreign` (`institution_id`),
  CONSTRAINT `courses_institution_id_foreign` FOREIGN KEY (`institution_id`) REFERENCES `institutions` (`id`) ON DELETE CASCADE,
  CONSTRAINT `courses_instructor_id_foreign` FOREIGN KEY (`instructor_id`) REFERENCES `users` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `courses`
--

LOCK TABLES `courses` WRITE;
/*!40000 ALTER TABLE `courses` DISABLE KEYS */;
/*!40000 ALTER TABLE `courses` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `departments`
--

DROP TABLE IF EXISTS `departments`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `departments` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `institution_id` bigint unsigned NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `departments_institution_id_foreign` (`institution_id`),
  CONSTRAINT `departments_institution_id_foreign` FOREIGN KEY (`institution_id`) REFERENCES `institutions` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `departments`
--

LOCK TABLES `departments` WRITE;
/*!40000 ALTER TABLE `departments` DISABLE KEYS */;
/*!40000 ALTER TABLE `departments` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `enrollments`
--

DROP TABLE IF EXISTS `enrollments`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `enrollments` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `user_id` bigint unsigned NOT NULL,
  `career_id` bigint unsigned NOT NULL,
  `semestre` int NOT NULL,
  `periodo` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `doc_acta_nacimiento` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `doc_certificado_prepa` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `doc_curp` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `doc_ine` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `doc_comprobante_domicilio` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `status` enum('Inscrito','Pendiente','Baja') CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'Pendiente',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `enrollments_user_id_foreign` (`user_id`),
  KEY `enrollments_career_id_foreign` (`career_id`),
  CONSTRAINT `enrollments_career_id_foreign` FOREIGN KEY (`career_id`) REFERENCES `careers` (`id`),
  CONSTRAINT `enrollments_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `enrollments`
--

LOCK TABLES `enrollments` WRITE;
/*!40000 ALTER TABLE `enrollments` DISABLE KEYS */;
INSERT INTO `enrollments` VALUES (1,107,1,1,'2','documentos/107/expediente/rW9cN0yKy4exZK6VkBpm1qS00YbYBKw1vuGXbFM3.pdf','documentos/107/expediente/LSP0OwtMM30VN7NqjKBAlPNdXs4dSNXJAEaxWxme.pdf','documentos/107/expediente/CnJUdzzTqk8fFWmiZe9CEtR4uylZ5CpXTYdHhBJb.pdf','documentos/107/expediente/09R95NEL60nbLn60XdftsRrddxpV0IwC5rz2hHEJ.webp',NULL,'Pendiente','2026-04-27 22:37:00','2026-04-27 22:37:00');
/*!40000 ALTER TABLE `enrollments` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `facilities`
--

DROP TABLE IF EXISTS `facilities`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `facilities` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `nombre_aula` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `career_id` bigint unsigned DEFAULT NULL,
  `tipo_materia` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `facilities_career_id_foreign` (`career_id`),
  CONSTRAINT `facilities_career_id_foreign` FOREIGN KEY (`career_id`) REFERENCES `careers` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `facilities`
--

LOCK TABLES `facilities` WRITE;
/*!40000 ALTER TABLE `facilities` DISABLE KEYS */;
/*!40000 ALTER TABLE `facilities` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `failed_jobs`
--

DROP TABLE IF EXISTS `failed_jobs`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `failed_jobs` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `uuid` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `connection` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `queue` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `payload` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `exception` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `failed_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `failed_jobs_uuid_unique` (`uuid`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `failed_jobs`
--

LOCK TABLES `failed_jobs` WRITE;
/*!40000 ALTER TABLE `failed_jobs` DISABLE KEYS */;
/*!40000 ALTER TABLE `failed_jobs` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `horario_clase_ocultas`
--

DROP TABLE IF EXISTS `horario_clase_ocultas`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `horario_clase_ocultas` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `user_id` bigint unsigned NOT NULL,
  `horario_clase_id` bigint unsigned NOT NULL,
  `alumno_id` bigint unsigned DEFAULT NULL,
  `carrera_nombre` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `semestre` varchar(32) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `matricula` varchar(64) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `materia_nombre` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `horario_resumen` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci,
  `alumno_nombre` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `horario_clase_ocultas_alumno_id_foreign` (`alumno_id`),
  KEY `hco_user_hc_alumno_idx` (`user_id`,`horario_clase_id`,`alumno_id`),
  KEY `horario_clase_ocultas_horario_clase_id_foreign` (`horario_clase_id`),
  CONSTRAINT `horario_clase_ocultas_alumno_id_foreign` FOREIGN KEY (`alumno_id`) REFERENCES `users` (`id`) ON DELETE SET NULL,
  CONSTRAINT `horario_clase_ocultas_horario_clase_id_foreign` FOREIGN KEY (`horario_clase_id`) REFERENCES `horario_clases` (`id`) ON DELETE CASCADE,
  CONSTRAINT `horario_clase_ocultas_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=35 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `horario_clase_ocultas`
--

LOCK TABLES `horario_clase_ocultas` WRITE;
/*!40000 ALTER TABLE `horario_clase_ocultas` DISABLE KEYS */;
/*!40000 ALTER TABLE `horario_clase_ocultas` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `horario_clase_user`
--

DROP TABLE IF EXISTS `horario_clase_user`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `horario_clase_user` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `horario_clase_id` bigint unsigned NOT NULL,
  `user_id` bigint unsigned NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `horario_clase_user_horario_clase_id_user_id_unique` (`horario_clase_id`,`user_id`),
  KEY `horario_clase_user_user_id_foreign` (`user_id`),
  CONSTRAINT `horario_clase_user_horario_clase_id_foreign` FOREIGN KEY (`horario_clase_id`) REFERENCES `horario_clases` (`id`) ON DELETE CASCADE,
  CONSTRAINT `horario_clase_user_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `horario_clase_user`
--

LOCK TABLES `horario_clase_user` WRITE;
/*!40000 ALTER TABLE `horario_clase_user` DISABLE KEYS */;
/*!40000 ALTER TABLE `horario_clase_user` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `horario_clases`
--

DROP TABLE IF EXISTS `horario_clases`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `horario_clases` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `materia_id` bigint unsigned NOT NULL,
  `career_id` bigint unsigned NOT NULL,
  `user_id` bigint unsigned NOT NULL,
  `aula_id` bigint unsigned DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `horario_clases_materia_id_foreign` (`materia_id`),
  KEY `horario_clases_career_id_foreign` (`career_id`),
  KEY `horario_clases_user_id_foreign` (`user_id`),
  KEY `horario_clases_aula_id_foreign` (`aula_id`),
  CONSTRAINT `horario_clases_aula_id_foreign` FOREIGN KEY (`aula_id`) REFERENCES `facilities` (`id`) ON DELETE CASCADE,
  CONSTRAINT `horario_clases_career_id_foreign` FOREIGN KEY (`career_id`) REFERENCES `careers` (`id`) ON DELETE CASCADE,
  CONSTRAINT `horario_clases_materia_id_foreign` FOREIGN KEY (`materia_id`) REFERENCES `materias` (`id`) ON DELETE CASCADE,
  CONSTRAINT `horario_clases_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=100 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `horario_clases`
--

LOCK TABLES `horario_clases` WRITE;
/*!40000 ALTER TABLE `horario_clases` DISABLE KEYS */;
/*!40000 ALTER TABLE `horario_clases` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `horario_franjas`
--

DROP TABLE IF EXISTS `horario_franjas`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `horario_franjas` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `horario_clase_id` bigint unsigned NOT NULL,
  `dias_semana` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin NOT NULL COMMENT 'Array de días de la semana: [1, 2, 5] para Lunes, Martes y Viernes.',
  `hora_inicio` time NOT NULL,
  `hora_fin` time NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `horario_franjas_horario_clase_id_foreign` (`horario_clase_id`),
  CONSTRAINT `horario_franjas_horario_clase_id_foreign` FOREIGN KEY (`horario_clase_id`) REFERENCES `horario_clases` (`id`) ON DELETE CASCADE,
  CONSTRAINT `horario_franjas_chk_1` CHECK (json_valid(`dias_semana`))
) ENGINE=InnoDB AUTO_INCREMENT=389 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `horario_franjas`
--

LOCK TABLES `horario_franjas` WRITE;
/*!40000 ALTER TABLE `horario_franjas` DISABLE KEYS */;
/*!40000 ALTER TABLE `horario_franjas` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `institution_user`
--

DROP TABLE IF EXISTS `institution_user`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `institution_user` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `user_id` bigint unsigned NOT NULL,
  `institution_id` bigint unsigned NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `institution_user_user_id_foreign` (`user_id`),
  KEY `institution_user_institution_id_foreign` (`institution_id`),
  CONSTRAINT `institution_user_institution_id_foreign` FOREIGN KEY (`institution_id`) REFERENCES `institutions` (`id`) ON DELETE CASCADE,
  CONSTRAINT `institution_user_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=61 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `institution_user`
--

LOCK TABLES `institution_user` WRITE;
/*!40000 ALTER TABLE `institution_user` DISABLE KEYS */;
INSERT INTO `institution_user` VALUES (1,1,1,'2026-03-24 14:52:06','2026-03-24 14:52:06'),(2,1,2,NULL,NULL),(3,1,3,NULL,NULL),(4,1,4,NULL,NULL),(58,105,4,'2026-04-24 20:24:43','2026-04-24 20:24:43'),(59,106,4,NULL,NULL),(60,107,4,NULL,NULL);
/*!40000 ALTER TABLE `institution_user` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `institutions`
--

DROP TABLE IF EXISTS `institutions`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `institutions` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `logo_path` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `is_administrativo` tinyint(1) NOT NULL DEFAULT '0',
  `is_universidad` tinyint(1) NOT NULL DEFAULT '0',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `institutions_name_unique` (`name`)
) ENGINE=InnoDB AUTO_INCREMENT=13 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `institutions`
--

LOCK TABLES `institutions` WRITE;
/*!40000 ALTER TABLE `institutions` DISABLE KEYS */;
INSERT INTO `institutions` VALUES (1,'Palacio Mundo Imperial',NULL,0,0,'2025-12-06 18:21:35','2025-12-06 18:21:35'),(2,'Princess Mundo Imperial',NULL,0,0,'2025-12-06 18:21:35','2025-12-06 18:21:35'),(3,'Pierre Mundo Imperial',NULL,0,0,'2025-12-06 18:21:35','2025-12-06 18:21:35'),(4,'Universidad Mundo Imperial',NULL,0,0,'2025-12-06 18:21:35','2025-12-06 18:21:35');
/*!40000 ALTER TABLE `institutions` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `job_batches`
--

DROP TABLE IF EXISTS `job_batches`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `job_batches` (
  `id` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `name` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `total_jobs` int NOT NULL,
  `pending_jobs` int NOT NULL,
  `failed_jobs` int NOT NULL,
  `failed_job_ids` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `options` mediumtext CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci,
  `cancelled_at` int DEFAULT NULL,
  `created_at` int NOT NULL,
  `finished_at` int DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `job_batches`
--

LOCK TABLES `job_batches` WRITE;
/*!40000 ALTER TABLE `job_batches` DISABLE KEYS */;
/*!40000 ALTER TABLE `job_batches` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `jobs`
--

DROP TABLE IF EXISTS `jobs`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `jobs` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `queue` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `payload` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `attempts` tinyint unsigned NOT NULL,
  `reserved_at` int unsigned DEFAULT NULL,
  `available_at` int unsigned NOT NULL,
  `created_at` int unsigned NOT NULL,
  PRIMARY KEY (`id`),
  KEY `jobs_queue_index` (`queue`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `jobs`
--

LOCK TABLES `jobs` WRITE;
/*!40000 ALTER TABLE `jobs` DISABLE KEYS */;
/*!40000 ALTER TABLE `jobs` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `lead_seguimientos`
--

DROP TABLE IF EXISTS `lead_seguimientos`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `lead_seguimientos` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `lead_id` bigint unsigned NOT NULL,
  `estado` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `fecha` date DEFAULT NULL,
  `hora` time DEFAULT NULL,
  `comentario` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `lead_seguimientos_lead_id_foreign` (`lead_id`),
  CONSTRAINT `lead_seguimientos_lead_id_foreign` FOREIGN KEY (`lead_id`) REFERENCES `leads` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=145 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `lead_seguimientos`
--

LOCK TABLES `lead_seguimientos` WRITE;
/*!40000 ALTER TABLE `lead_seguimientos` DISABLE KEYS */;
INSERT INTO `lead_seguimientos` VALUES (137,29,'Prospecto','2026-04-27','16:23:34',NULL,'2026-04-27 22:23:34','2026-04-27 22:23:34'),(138,29,'Prospecto frío','2026-04-27','16:25:46',NULL,'2026-04-27 22:25:46','2026-04-27 22:25:46'),(139,29,'Prospecto caliente','2026-04-27','16:26:42','Comentario','2026-04-27 22:26:42','2026-04-27 22:26:42'),(140,29,'Aspirante','2026-04-27','16:27:20','Comentario','2026-04-27 22:27:20','2026-04-27 22:27:20');
/*!40000 ALTER TABLE `lead_seguimientos` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `leads`
--

DROP TABLE IF EXISTS `leads`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `leads` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `ctp_id` bigint unsigned DEFAULT NULL,
  `tutor_nombre` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `tutor_paterno` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `tutor_materno` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `tutor_curp` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `telefono1` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `telefono2` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `tutor_email` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `alumno_nombre` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `alumno_paterno` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `alumno_materno` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `alumno_email` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `alumno_telefono` varchar(30) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `alumno_curp` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `origen` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'formulario_publico',
  `clasificacion` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'nuevo',
  `doc_acta_nacimiento` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `doc_certificado_prepa` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `doc_curp` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `doc_ine` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `comentario_reasignacion` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci,
  `carrera_id` bigint unsigned DEFAULT NULL,
  `semestre` tinyint unsigned DEFAULT '1',
  `user_id` bigint unsigned DEFAULT NULL,
  `doc_acta_rechazado` tinyint(1) NOT NULL DEFAULT '0',
  `doc_certificado_rechazado` tinyint(1) NOT NULL DEFAULT '0',
  `doc_curp_rechazado` tinyint(1) NOT NULL DEFAULT '0',
  `doc_ine_rechazado` tinyint(1) NOT NULL DEFAULT '0',
  `doc_ficha_pago` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `doc_factura_xml` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `doc_ficha_pago_rechazado` tinyint(1) NOT NULL DEFAULT '0',
  `doc_factura_xml_rechazado` tinyint(1) NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`),
  KEY `leads_ctp_id_foreign` (`ctp_id`),
  KEY `leads_user_id_foreign` (`user_id`),
  KEY `leads_carrera_id_foreign` (`carrera_id`),
  CONSTRAINT `leads_carrera_id_foreign` FOREIGN KEY (`carrera_id`) REFERENCES `careers` (`id`) ON DELETE SET NULL,
  CONSTRAINT `leads_ctp_id_foreign` FOREIGN KEY (`ctp_id`) REFERENCES `users` (`id`) ON DELETE SET NULL,
  CONSTRAINT `leads_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB AUTO_INCREMENT=31 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `leads`
--

LOCK TABLES `leads` WRITE;
/*!40000 ALTER TABLE `leads` DISABLE KEYS */;
INSERT INTO `leads` VALUES (29,106,'ANDREA','SALMERON','CARDENAS','SACA001008MGRRLNA0','7442673054','7442942522','andy_lu2000@live.com.mx','ANELIS','SALMERON','CARDENAS',NULL,NULL,'SACA009007GFHJFH34','formulario_publico','Prospecto',NULL,NULL,NULL,NULL,'2026-04-27 22:23:34','2026-04-27 22:25:46',NULL,1,1,NULL,0,0,0,0,NULL,NULL,0,0);
/*!40000 ALTER TABLE `leads` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `materias`
--

DROP TABLE IF EXISTS `materias`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `materias` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `nombre` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `clave` varchar(10) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `creditos` int NOT NULL,
  `career_id` bigint unsigned NOT NULL,
  `descripcion` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `objetivo` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci,
  `temario` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci,
  `infografia` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci,
  `type` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `semestre` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `materias_career_id_nombre_unique` (`career_id`,`nombre`),
  UNIQUE KEY `materias_clave_unique` (`clave`),
  KEY `materias_career_id_foreign` (`career_id`),
  CONSTRAINT `materias_career_id_foreign` FOREIGN KEY (`career_id`) REFERENCES `careers` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=29 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `materias`
--

LOCK TABLES `materias` WRITE;
/*!40000 ALTER TABLE `materias` DISABLE KEYS */;
/*!40000 ALTER TABLE `materias` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `migrations`
--

DROP TABLE IF EXISTS `migrations`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `migrations` (
  `id` int unsigned NOT NULL AUTO_INCREMENT,
  `migration` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `batch` int NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=93 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `migrations`
--

LOCK TABLES `migrations` WRITE;
/*!40000 ALTER TABLE `migrations` DISABLE KEYS */;
INSERT INTO `migrations` VALUES (1,'0001_01_01_000001_create_cache_table',1),(2,'0001_01_01_000002_create_jobs_table',1),(3,'2025_10_01_000001_create_institution_table',1),(4,'2025_10_01_000002_create_departments_table',1),(5,'2025_10_01_000003_create_workstations_table',1),(6,'2025_10_01_000004_create_addresses_table',1),(7,'2025_10_01_000005_create_roles_table',1),(8,'2025_10_01_000006_create_users_table',1),(9,'2025_10_04_021355_create_careers_table',1),(10,'2025_10_04_210135_create_academic_profiles_table',1),(11,'2025_10_04_210852_create_corporate_profiles_table',1),(12,'2025_10_05_142349_create_institution_user_table',1),(13,'2025_10_05_151143_create_sessions_table',1),(14,'2025_10_06_021352_create_courses_table',1),(15,'2025_10_06_041312_create_facilities_table',1),(16,'2025_10_06_193138_create_materias_table',1),(17,'2025_10_07_091757_create_periods_table',1),(18,'2025_10_07_222917_create_topics_table',1),(19,'2025_10_07_222919_create_subtopics_table',1),(20,'2025_10_07_222937_create_activities_table',1),(21,'2025_10_12_232400_create_targetables_table',1),(22,'2025_10_13_174118_create_facturas_table',1),(23,'2025_10_26_154218_create_course_user_table',1),(24,'2025_10_27_041316_create_horario_clases_table',1),(25,'2025_10_27_043532_create_horario_franjas_table',1),(26,'2025_10_27_222753_create_user_roles_institution_table',1),(27,'2025_10_28_031414_create_completions_table',1),(28,'2025_11_03_024824_add_period_id_to_billings_table',1),(29,'2025_11_03_210057_create_payments_table',1),(30,'2025_11_10_053411_add_modules_to_academic_profiles_table',1),(31,'2025_11_10_155119_add_xml_path_to_billings_table',1),(32,'2025_11_13_053913_add_monthly_payments_to_periods_table',1),(33,'2025_11_17_072025_add_is_active_to_user_roles_institution_table',1),(34,'2025_11_24_170532_add_payment_dates_to_periods_table',1),(35,'2025_11_24_221431_add_soft_deletes_to_billings_table',1),(36,'2025_11_26_032911_add_employee_data_and_create_enrollments',1),(37,'2025_11_26_050455_add_soft_deletes_to_users_table',1),(38,'2025_11_26_234354_add_columns_to_academic_profiles_table',1),(39,'2025_11_28_061158_add_matricula_to_academic_profiles_table',1),(40,'2025_11_29_222656_create_billing_concepts_table',1),(41,'2025_12_01_170545_add_progress_to_course_user_table',1),(42,'2025_12_02_230258_add_documentosep_path_to_academic_profiles',1),(43,'2026_01_26_000001_create_clases_table',2),(44,'2026_01_26_000002_create_clase_alumno_table',2),(45,'2026_01_29_220000_materias_unique_por_carrera',3),(46,'2026_01_30_000000_add_anios_activos_to_academic_profiles_table',4),(47,'2026_02_06_000000_make_aula_id_nullable_in_horario_clases',5),(48,'2026_02_05_000000_create_clase_inscripciones_table',6),(49,'2026_02_05_000000_create_horario_clase_user_table',7),(50,'2026_03_04_000000_add_curp_to_users_table',8),(51,'2026_03_09_000000_create_horario_clase_ocultas_table',9),(52,'2026_03_10_000001_create_teacher_careers_table',10),(53,'2026_03_10_000000_create_career_user_table',11),(54,'2026_02_04_151948_create_leads_table',12),(55,'2026_02_10_184604_create_lead_seguimientos_table',12),(56,'2026_02_17_213514_add_ctp_id_to_leads_table',12),(57,'2026_02_19_203235_rename_alumno_to_aspirante_in_seguimientos',12),(58,'2026_02_23_182833_drop_curp_from_leads_table',12),(59,'2026_02_23_183810_add_tutor_fields_to_leads_table',12),(60,'2026_02_23_185616_drop_rfc_from_leads_table',12),(61,'2026_02_24_150153_add_comentario_reasignacion_to_leads_table',12),(62,'2026_03_04_182849_add_comentario_to_seguimientos_table',12),(63,'2026_03_11_000000_ensure_ctp_roles_exist',13),(64,'2026_03_13_000000_make_users_email_nullable',14),(65,'2026_03_11_215224_create_carreras_table',15),(66,'2026_03_12_130554_add_carrera_id_to_leads_table',15),(67,'2026_03_23_000000_add_user_id_to_leads_table',15),(68,'2026_03_23_175944_add_matricula_semestre_docs_to_leads_table',16),(69,'2026_03_23_190222_add_semestre_to_leads_table',17),(70,'2026_03_24_120000_change_leads_carrera_id_foreign_to_careers',18),(71,'2026_03_29_180000_add_alumno_contact_to_leads_table',19),(72,'2026_03_30_120000_create_career_classifications_table',20),(73,'2026_03_30_140000_add_career_classification_id_to_careers_table',21),(74,'2026_03_30_160000_set_estudiante_role_display_name_to_alumno',22),(75,'2026_03_30_120000_add_document_rejection_flags_to_leads_and_academic_profiles',23),(76,'2026_03_30_160000_add_facturacion_docs_to_leads_and_academic_profiles',24),(77,'2026_04_01_120000_create_career_user_pivot_table',25),(78,'2026_03_25_223845_create_comisiones_table',26),(79,'2026_04_08_120000_add_detalle_alumno_to_horario_clase_ocultas_table',27),(80,'2026_04_09_000001_add_flags_to_institutions_table',28),(81,'2026_04_12_000001_add_monto_mensualidad_cargo_monetario_to_careers_table',29),(82,'2026_04_12_120000_add_nombre_carrera_tipo_materia_to_facilities_table',30),(83,'2026_04_13_000000_drop_legacy_columns_from_facilities_table',31),(84,'2026_04_12_000001_add_porcentaje_cargo_moratorio_to_billings_table',32),(85,'2026_04_12_000002_add_cargo_monetario_to_billings_table',33),(86,'2026_04_15_000003_add_pricing_mode_and_monthly_prices_to_careers_table',34),(87,'2026_04_15_000004_add_porcentaje_cargo_moratorio_to_careers_table',35),(88,'2026_04_15_000005_add_detalle_academico_to_materias_table',36),(89,'2026_04_20_000006_add_moratorio_fields_to_billing_concepts_table',37),(90,'2026_04_21_120000_create_control_escolar_documentos_table',38),(91,'2026_04_21_130000_create_becas_documentos_table',39),(92,'2026_04_21_130100_create_titulacion_documentos_table',39);
/*!40000 ALTER TABLE `migrations` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `payments`
--

DROP TABLE IF EXISTS `payments`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `payments` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `billing_id` bigint unsigned NOT NULL,
  `user_id` bigint unsigned NOT NULL,
  `monto` decimal(10,2) NOT NULL,
  `fecha_pago` date NOT NULL,
  `metodo_pago` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `nota` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `payments_billing_id_foreign` (`billing_id`),
  KEY `payments_user_id_foreign` (`user_id`),
  CONSTRAINT `payments_billing_id_foreign` FOREIGN KEY (`billing_id`) REFERENCES `billings` (`id`) ON DELETE CASCADE,
  CONSTRAINT `payments_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=7 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `payments`
--

LOCK TABLES `payments` WRITE;
/*!40000 ALTER TABLE `payments` DISABLE KEYS */;
INSERT INTO `payments` VALUES (1,4,1,1500.00,'2025-12-06',NULL,'Pago automático por seeder','2025-12-06 18:21:38','2025-12-06 18:21:38'),(2,5,1,1500.00,'2025-12-06',NULL,'Pago automático por seeder','2025-12-06 18:21:38','2025-12-06 18:21:38'),(3,6,1,1500.00,'2025-12-06',NULL,'Pago automático por seeder','2025-12-06 18:21:39','2025-12-06 18:21:39'),(4,7,1,1500.00,'2025-12-06',NULL,'Pago automático por seeder','2025-12-06 18:21:39','2025-12-06 18:21:39'),(5,8,1,1500.00,'2025-12-06',NULL,'Pago automático por seeder','2025-12-06 18:21:40','2025-12-06 18:21:40'),(6,9,1,1500.00,'2025-12-06',NULL,'Pago automático por seeder','2025-12-06 18:21:40','2025-12-06 18:21:40');
/*!40000 ALTER TABLE `payments` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `periods`
--

DROP TABLE IF EXISTS `periods`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `periods` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `start_date` date NOT NULL,
  `end_date` date NOT NULL,
  `monthly_payments_count` int DEFAULT NULL,
  `payment_dates` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin,
  `re_enrollment_deadline` date DEFAULT NULL,
  `is_active` tinyint(1) NOT NULL DEFAULT '0',
  `institution_id` bigint unsigned NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `periods_name_unique` (`name`),
  KEY `periods_institution_id_foreign` (`institution_id`),
  CONSTRAINT `periods_institution_id_foreign` FOREIGN KEY (`institution_id`) REFERENCES `institutions` (`id`) ON DELETE CASCADE,
  CONSTRAINT `periods_chk_1` CHECK (json_valid(`payment_dates`))
) ENGINE=InnoDB AUTO_INCREMENT=3 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `periods`
--

LOCK TABLES `periods` WRITE;
/*!40000 ALTER TABLE `periods` DISABLE KEYS */;
INSERT INTO `periods` VALUES (2,'FEB 2026 - JUL 2026','2026-02-01','2026-07-31',6,'[\"2026-02-10\",\"2026-03-10\",\"2026-04-10\",\"2026-05-10\",\"2026-06-10\",\"2026-07-10\"]',NULL,1,4,'2026-04-27 22:31:51','2026-04-27 22:31:51');
/*!40000 ALTER TABLE `periods` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `roles`
--

DROP TABLE IF EXISTS `roles`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `roles` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `display_name` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `roles_name_unique` (`name`)
) ENGINE=InnoDB AUTO_INCREMENT=10 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `roles`
--

LOCK TABLES `roles` WRITE;
/*!40000 ALTER TABLE `roles` DISABLE KEYS */;
INSERT INTO `roles` VALUES (1,'master','Master','2025-12-06 18:21:35','2025-12-06 18:21:35'),(2,'gerente_capacitacion','Gerente de Capacitación','2025-12-06 18:21:35','2025-12-06 18:21:35'),(3,'gerente_th','Gerente de Talento Humano','2025-12-06 18:21:35','2025-12-06 18:21:35'),(4,'anfitrion','Anfitrión','2025-12-06 18:21:35','2025-12-06 18:21:35'),(5,'control_administrativo','Control Administrativo','2025-12-06 18:21:35','2025-12-06 18:21:35'),(6,'docente','Docente','2025-12-06 18:21:35','2025-12-06 18:21:35'),(7,'estudiante','Alumno','2025-12-06 18:21:35','2025-12-06 18:21:35'),(8,'ctp','CTP','2026-03-11 18:00:31','2026-03-11 18:00:31'),(9,'coordinador_ctp','Coordinador del CTP','2026-03-11 18:00:31','2026-03-11 18:00:31');
/*!40000 ALTER TABLE `roles` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `sessions`
--

DROP TABLE IF EXISTS `sessions`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `sessions` (
  `id` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `user_id` bigint unsigned DEFAULT NULL,
  `ip_address` varchar(45) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `user_agent` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci,
  `payload` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `last_activity` int NOT NULL,
  PRIMARY KEY (`id`),
  KEY `sessions_user_id_index` (`user_id`),
  KEY `sessions_last_activity_index` (`last_activity`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `sessions`
--

LOCK TABLES `sessions` WRITE;
/*!40000 ALTER TABLE `sessions` DISABLE KEYS */;
INSERT INTO `sessions` VALUES ('0ccEgjF9vNKaLGOxvIQufs43R9V6GULy1cipnD41',NULL,'31.220.63.131','Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/44.0.2403.157 Safari/537.36','YTozOntzOjY6Il90b2tlbiI7czo0MDoibU5sRlJpUEFER2NOdGhrUWJiODduZU9XUkxOaGhwODJnNE9JS2h1MyI7czo5OiJfcHJldmlvdXMiO2E6MTp7czozOiJ1cmwiO3M6MzE6Imh0dHBzOi8vbWl1bWkubWltdW5kb2RpZ2l0YWwubXgiO31zOjY6Il9mbGFzaCI7YToyOntzOjM6Im9sZCI7YTowOnt9czozOiJuZXciO2E6MDp7fX19',1777696091),('0ikfe0rVNQTZzH9qIdPj0ghri7ILbQZoDf5CdrEn',NULL,'31.220.63.131','Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/44.0.2403.157 Safari/537.36','YTozOntzOjY6Il90b2tlbiI7czo0MDoiMmJ3d2dJS0pWTnN6YlJoTHRLM2R4eDFtaU1OeHFlTUVzRHB5aU1iSSI7czo5OiJfcHJldmlvdXMiO2E6MTp7czozOiJ1cmwiO3M6Mzc6Imh0dHBzOi8vbWl1bWkubWltdW5kb2RpZ2l0YWwubXgvbG9naW4iO31zOjY6Il9mbGFzaCI7YToyOntzOjM6Im9sZCI7YTowOnt9czozOiJuZXciO2E6MDp7fX19',1777704965),('0wETztV00Wt67wtEPtvI3rXlw8KjnD525EfTcV4p',NULL,'31.220.63.131','Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/44.0.2403.157 Safari/537.36','YTozOntzOjY6Il90b2tlbiI7czo0MDoiSEpsVmJXSWRkQjBqV2I0VHQ2b1Zocm91RnJnNnJnRjRBM0xjZUIwVyI7czo5OiJfcHJldmlvdXMiO2E6MTp7czozOiJ1cmwiO3M6Mzc6Imh0dHBzOi8vbWl1bWkubWltdW5kb2RpZ2l0YWwubXgvbG9naW4iO31zOjY6Il9mbGFzaCI7YToyOntzOjM6Im9sZCI7YTowOnt9czozOiJuZXciO2E6MDp7fX19',1777669214),('1gFBQNasOmmrV92r0fQYJiDZhbDvJqVefMwC58Zx',NULL,'31.220.63.131','Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/44.0.2403.157 Safari/537.36','YTozOntzOjY6Il90b2tlbiI7czo0MDoiSWRabWdtUHNTREM4Snc2UU1yRnZDNUFWSlUwMGY0TnFFYU9sdnpCayI7czo5OiJfcHJldmlvdXMiO2E6MTp7czozOiJ1cmwiO3M6Mzc6Imh0dHBzOi8vbWl1bWkubWltdW5kb2RpZ2l0YWwubXgvbG9naW4iO31zOjY6Il9mbGFzaCI7YToyOntzOjM6Im9sZCI7YTowOnt9czozOiJuZXciO2E6MDp7fX19',1777651208),('1LVR9xeZktgUVo6ieYJaKfzDmlWNrDQiIfWpRw6e',NULL,'31.220.63.131','Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/44.0.2403.157 Safari/537.36','YTozOntzOjY6Il90b2tlbiI7czo0MDoiTEVWS1Y4UzZmdWtjck9PSnpjV3JOTnd6akFzMEJhYkY2aVRmMGFkeCI7czo5OiJfcHJldmlvdXMiO2E6MTp7czozOiJ1cmwiO3M6Mzc6Imh0dHBzOi8vbWl1bWkubWltdW5kb2RpZ2l0YWwubXgvbG9naW4iO31zOjY6Il9mbGFzaCI7YToyOntzOjM6Im9sZCI7YTowOnt9czozOiJuZXciO2E6MDp7fX19',1777698023),('1wnHiz9xJKfW109hrjirrjjv37bpv1q5DJCmf2ut',NULL,'31.220.63.131','Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/44.0.2403.157 Safari/537.36','YTozOntzOjY6Il90b2tlbiI7czo0MDoidm9zMEg5Z1pIR0dSQVppRW1WNXlTZVlDeTlpVFY0NDZZSXA1aVJjRSI7czo5OiJfcHJldmlvdXMiO2E6MTp7czozOiJ1cmwiO3M6Mzc6Imh0dHBzOi8vbWl1bWkubWltdW5kb2RpZ2l0YWwubXgvbG9naW4iO31zOjY6Il9mbGFzaCI7YToyOntzOjM6Im9sZCI7YTowOnt9czozOiJuZXciO2E6MDp7fX19',1777701365),('1xQKcN3c6RFiw0jcFFxOhKp0CROPN6XUXZmqJsc7',NULL,'31.220.63.131','Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/44.0.2403.157 Safari/537.36','YTozOntzOjY6Il90b2tlbiI7czo0MDoiSTc2REcybUVQblFIR1ExMFdXUjVVZkppd3hiSFo4QXk5ZXJpNjNXbCI7czo5OiJfcHJldmlvdXMiO2E6MTp7czozOiJ1cmwiO3M6Mzc6Imh0dHBzOi8vbWl1bWkubWltdW5kb2RpZ2l0YWwubXgvbG9naW4iO31zOjY6Il9mbGFzaCI7YToyOntzOjM6Im9sZCI7YTowOnt9czozOiJuZXciO2E6MDp7fX19',1777654809),('24PIp7Ff0jVf4EZtjwDfSPVaJdDCFYo7VQegKjxi',NULL,'31.220.63.131','Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/44.0.2403.157 Safari/537.36','YTozOntzOjY6Il90b2tlbiI7czo0MDoidGc5TDBnb2hLZW5KNlBoN1FoaEZ3WkU1QXlQR2IzYkxKNGhDWDdzbiI7czo5OiJfcHJldmlvdXMiO2E6MTp7czozOiJ1cmwiO3M6MzE6Imh0dHBzOi8vbWl1bWkubWltdW5kb2RpZ2l0YWwubXgiO31zOjY6Il9mbGFzaCI7YToyOntzOjM6Im9sZCI7YTowOnt9czozOiJuZXciO2E6MDp7fX19',1777668966),('2Bd4ziTHcTNPeo5tp9mmpKOnBRswdHHBNQZqhLrH',NULL,'31.220.63.131','Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/44.0.2403.157 Safari/537.36','YTozOntzOjY6Il90b2tlbiI7czo0MDoiVm4xTE5IZHBIdUdmUmg3dE5vbHUyWkFmTlN3YklybjZPZUk5MUc3OCI7czo5OiJfcHJldmlvdXMiO2E6MTp7czozOiJ1cmwiO3M6MzE6Imh0dHBzOi8vbWl1bWkubWltdW5kb2RpZ2l0YWwubXgiO31zOjY6Il9mbGFzaCI7YToyOntzOjM6Im9sZCI7YTowOnt9czozOiJuZXciO2E6MDp7fX19',1777717683),('2Btq1lVLTnqQNp3TqGQ1R2jdLbeXGGm3f8RSbbSr',NULL,'31.220.63.131','Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/44.0.2403.157 Safari/537.36','YTozOntzOjY6Il90b2tlbiI7czo0MDoiTnFDNFJiMkdtY053eWQyU1g4aHpyd0xBcTNadThuQ2lDdDJTSTVyeiI7czo5OiJfcHJldmlvdXMiO2E6MTp7czozOiJ1cmwiO3M6MzE6Imh0dHBzOi8vbWl1bWkubWltdW5kb2RpZ2l0YWwubXgiO31zOjY6Il9mbGFzaCI7YToyOntzOjM6Im9sZCI7YTowOnt9czozOiJuZXciO2E6MDp7fX19',1777696927),('2dGVXScGLgGgOquh3tjBIRutumX2wrZWOixedP8t',NULL,'31.220.63.131','Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/44.0.2403.157 Safari/537.36','YTozOntzOjY6Il90b2tlbiI7czo0MDoiY1RKWWlKemIzcExRamhvcklpTkFMb1BjT2hONnI5SkRGR0JCTlZyUSI7czo5OiJfcHJldmlvdXMiO2E6MTp7czozOiJ1cmwiO3M6MzE6Imh0dHBzOi8vbWl1bWkubWltdW5kb2RpZ2l0YWwubXgiO31zOjY6Il9mbGFzaCI7YToyOntzOjM6Im9sZCI7YTowOnt9czozOiJuZXciO2E6MDp7fX19',1777666444),('2FFeuKde4HOuCNbO3LTfCCcaAWDS3nM8JDkZF7tz',NULL,'31.220.63.131','Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/44.0.2403.157 Safari/537.36','YTozOntzOjY6Il90b2tlbiI7czo0MDoielkwV1pXT3l5NTFacEVNdkpubG9nQzBPaDhUbk9wckNYR3NkWHlpNCI7czo5OiJfcHJldmlvdXMiO2E6MTp7czozOiJ1cmwiO3M6MzE6Imh0dHBzOi8vbWl1bWkubWltdW5kb2RpZ2l0YWwubXgiO31zOjY6Il9mbGFzaCI7YToyOntzOjM6Im9sZCI7YTowOnt9czozOiJuZXciO2E6MDp7fX19',1777649283),('2gjv1dAOQeza6k8zrrHnwF8dGL3Iv9he4WC0Za7U',NULL,'31.220.63.131','Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/44.0.2403.157 Safari/537.36','YTozOntzOjY6Il90b2tlbiI7czo0MDoiaDR3MGhUc21HN0JDV0U4aVpmZG1pR3J2c0tUT2YzRTJzTXZUT25DVCI7czo5OiJfcHJldmlvdXMiO2E6MTp7czozOiJ1cmwiO3M6Mzc6Imh0dHBzOi8vbWl1bWkubWltdW5kb2RpZ2l0YWwubXgvbG9naW4iO31zOjY6Il9mbGFzaCI7YToyOntzOjM6Im9sZCI7YTowOnt9czozOiJuZXciO2E6MDp7fX19',1777661763),('2Iq9g5BmxlxYBkxXphbGrO9SdyfAhPLn754rWSoB',NULL,'31.220.63.131','Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/44.0.2403.157 Safari/537.36','YTozOntzOjY6Il90b2tlbiI7czo0MDoibVoyMWFZY3F0dWg1Z1hocjB0Y0FNRk9EM0ZzUklIUWlnclN6YU9VdyI7czo5OiJfcHJldmlvdXMiO2E6MTp7czozOiJ1cmwiO3M6Mzc6Imh0dHBzOi8vbWl1bWkubWltdW5kb2RpZ2l0YWwubXgvbG9naW4iO31zOjY6Il9mbGFzaCI7YToyOntzOjM6Im9sZCI7YTowOnt9czozOiJuZXciO2E6MDp7fX19',1777712410),('3i2RKHZwPhPVPWpwSnwaKj6zKV2Gh43eKWdSLp2J',NULL,'31.220.63.131','Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/44.0.2403.157 Safari/537.36','YTozOntzOjY6Il90b2tlbiI7czo0MDoiRzh3R3Bnbkg3S04zQ3JnYkc2SkJPMkVZZGtSbXhDajdwanowUXFYeiI7czo5OiJfcHJldmlvdXMiO2E6MTp7czozOiJ1cmwiO3M6MzE6Imh0dHBzOi8vbWl1bWkubWltdW5kb2RpZ2l0YWwubXgiO31zOjY6Il9mbGFzaCI7YToyOntzOjM6Im9sZCI7YTowOnt9czozOiJuZXciO2E6MDp7fX19',1777688047),('3IcdrQUe2V0fqE1TGWbBVSdwC9qMSX2z7NIsgaU6',NULL,'31.220.63.131','Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/44.0.2403.157 Safari/537.36','YTozOntzOjY6Il90b2tlbiI7czo0MDoiTXpVVXFYcHA4QlRuRmR0S2Z5aEx5TDJvbmh1Vkh3S1ZpMzdCZlU3YiI7czo5OiJfcHJldmlvdXMiO2E6MTp7czozOiJ1cmwiO3M6Mzc6Imh0dHBzOi8vbWl1bWkubWltdW5kb2RpZ2l0YWwubXgvbG9naW4iO31zOjY6Il9mbGFzaCI7YToyOntzOjM6Im9sZCI7YTowOnt9czozOiJuZXciO2E6MDp7fX19',1777655644),('3KCIpnQrGpfMXJzGESDjnOeLrK4KPgbgVuOqZuC5',NULL,'31.220.63.131','Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/44.0.2403.157 Safari/537.36','YTozOntzOjY6Il90b2tlbiI7czo0MDoiU25nT1FsM1hwT3hDYkFxekJhNnp4N2VVUjVmZ1ZtUkRvcUo5dUM4TyI7czo5OiJfcHJldmlvdXMiO2E6MTp7czozOiJ1cmwiO3M6MzE6Imh0dHBzOi8vbWl1bWkubWltdW5kb2RpZ2l0YWwubXgiO31zOjY6Il9mbGFzaCI7YToyOntzOjM6Im9sZCI7YTowOnt9czozOiJuZXciO2E6MDp7fX19',1777714084),('3LDCMHnKo0m38jVES0bGuKwL1zEoCLNjsiHEAJVZ',NULL,'31.220.63.131','Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/44.0.2403.157 Safari/537.36','YTozOntzOjY6Il90b2tlbiI7czo0MDoiTmExd3lxakNkcVVyMDljNDUxY2tzWWFMRGdhQ20zdlAwWDljd0NqOCI7czo5OiJfcHJldmlvdXMiO2E6MTp7czozOiJ1cmwiO3M6MzE6Imh0dHBzOi8vbWl1bWkubWltdW5kb2RpZ2l0YWwubXgiO31zOjY6Il9mbGFzaCI7YToyOntzOjM6Im9sZCI7YTowOnt9czozOiJuZXciO2E6MDp7fX19',1777686967),('3OyKIVb1mTkhg1Rtk7SxgKtnf2VINfTdjZMzeOiN',NULL,'31.220.63.131','Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/44.0.2403.157 Safari/537.36','YTozOntzOjY6Il90b2tlbiI7czo0MDoiT3FndGxYYlBpSUFCSmtXbWlNVWVWMWdNQ1U1N29FMDNna3VUNjloWiI7czo5OiJfcHJldmlvdXMiO2E6MTp7czozOiJ1cmwiO3M6MzE6Imh0dHBzOi8vbWl1bWkubWltdW5kb2RpZ2l0YWwubXgiO31zOjY6Il9mbGFzaCI7YToyOntzOjM6Im9sZCI7YTowOnt9czozOiJuZXciO2E6MDp7fX19',1777731247),('3xFkNVlvIg2vg4MRA4yVesJgrvZ3VjLibrr9wcD9',NULL,'31.220.63.131','Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/44.0.2403.157 Safari/537.36','YTozOntzOjY6Il90b2tlbiI7czo0MDoibzNKclV4TjB2UVZLY25mdFBJeUs4UTROZ2JnSlFabDQ2bEU1QTdZZCI7czo5OiJfcHJldmlvdXMiO2E6MTp7czozOiJ1cmwiO3M6MzE6Imh0dHBzOi8vbWl1bWkubWltdW5kb2RpZ2l0YWwubXgiO31zOjY6Il9mbGFzaCI7YToyOntzOjM6Im9sZCI7YTowOnt9czozOiJuZXciO2E6MDp7fX19',1777676165),('3YKDqIFVMe33yLxO8Lt5HLjpBLDrKzlIlsRfFazg',NULL,'31.220.63.131','Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/44.0.2403.157 Safari/537.36','YTozOntzOjY6Il90b2tlbiI7czo0MDoiVGxjbk5JQ2F0eGtXY1pqRFlxN0tuUGJlM3F2SUVqRnRzaThEd2pRSyI7czo5OiJfcHJldmlvdXMiO2E6MTp7czozOiJ1cmwiO3M6Mzc6Imh0dHBzOi8vbWl1bWkubWltdW5kb2RpZ2l0YWwubXgvbG9naW4iO31zOjY6Il9mbGFzaCI7YToyOntzOjM6Im9sZCI7YTowOnt9czozOiJuZXciO2E6MDp7fX19',1777724884),('3yNb8yvSY9WqOf65cwVqfcZqAJVtIHeJ02UBbY89',NULL,'31.220.63.131','Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/44.0.2403.157 Safari/537.36','YTozOntzOjY6Il90b2tlbiI7czo0MDoiU25VVm9aNDNvVnI0dVlRT25TckZtQzEySDZRV1RmUFcxWDNldUtnZyI7czo5OiJfcHJldmlvdXMiO2E6MTp7czozOiJ1cmwiO3M6Mzc6Imh0dHBzOi8vbWl1bWkubWltdW5kb2RpZ2l0YWwubXgvbG9naW4iO31zOjY6Il9mbGFzaCI7YToyOntzOjM6Im9sZCI7YTowOnt9czozOiJuZXciO2E6MDp7fX19',1777672813),('3YVfNbU3fab3EOWYhuaCcF1e2rqHxePlqEFYUHhE',NULL,'31.220.63.131','Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/44.0.2403.157 Safari/537.36','YTozOntzOjY6Il90b2tlbiI7czo0MDoiMUNtbGJ5N0NxbnBPVU13RUNXY2VqNWtWMkR1SHRiOERrYUhCMnhwUiI7czo5OiJfcHJldmlvdXMiO2E6MTp7czozOiJ1cmwiO3M6Mzc6Imh0dHBzOi8vbWl1bWkubWltdW5kb2RpZ2l0YWwubXgvbG9naW4iO31zOjY6Il9mbGFzaCI7YToyOntzOjM6Im9sZCI7YTowOnt9czozOiJuZXciO2E6MDp7fX19',1777652044),('4D2QNxPI6hO7WqPPdsR0jyJq5HbhZ7qwxyF5oYP1',NULL,'31.220.63.131','Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/44.0.2403.157 Safari/537.36','YTozOntzOjY6Il90b2tlbiI7czo0MDoiUUx4a0dmUGc4MWx3dG1QVzFWVDBDVFBWRVRSR1A2dE51ZVBDN3ZXWSI7czo5OiJfcHJldmlvdXMiO2E6MTp7czozOiJ1cmwiO3M6MzE6Imh0dHBzOi8vbWl1bWkubWltdW5kb2RpZ2l0YWwubXgiO31zOjY6Il9mbGFzaCI7YToyOntzOjM6Im9sZCI7YTowOnt9czozOiJuZXciO2E6MDp7fX19',1777677244),('4xIMfO3yLmwCNCzKBTqJQ7XLpfIjpz4Mujv6A6fq',NULL,'31.220.63.131','Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/44.0.2403.157 Safari/537.36','YTozOntzOjY6Il90b2tlbiI7czo0MDoiRkJmdkZMSmVqZ2hGbE94aktoNlh5dWhFVEFvWjVOaWFoOHJXY0Y3MyI7czo5OiJfcHJldmlvdXMiO2E6MTp7czozOiJ1cmwiO3M6MzE6Imh0dHBzOi8vbWl1bWkubWltdW5kb2RpZ2l0YWwubXgiO31zOjY6Il9mbGFzaCI7YToyOntzOjM6Im9sZCI7YTowOnt9czozOiJuZXciO2E6MDp7fX19',1777697777),('51AsDYTmEj6BU3jDp9KKSgwSh7PSEdxr25GGzZTq',NULL,'31.220.63.131','Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/44.0.2403.157 Safari/537.36','YTozOntzOjY6Il90b2tlbiI7czo0MDoiSmxhejBSbDhPMmwwMzhsQWVDRXNQdWhWN1A1V0t2VG11VEFXRmx6NyI7czo5OiJfcHJldmlvdXMiO2E6MTp7czozOiJ1cmwiO3M6MzE6Imh0dHBzOi8vbWl1bWkubWltdW5kb2RpZ2l0YWwubXgiO31zOjY6Il9mbGFzaCI7YToyOntzOjM6Im9sZCI7YTowOnt9czozOiJuZXciO2E6MDp7fX19',1777721283),('5NYIznGwu6CjsYYhSKvEqea3HDTAV6tf2kkKSSXX',NULL,'31.220.63.131','Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/44.0.2403.157 Safari/537.36','YTozOntzOjY6Il90b2tlbiI7czo0MDoiOXR1VXByY3RXdUpNOUhvanR0Y3k5UXFKeHl3cll6cDREeFhSS2x2QSI7czo5OiJfcHJldmlvdXMiO2E6MTp7czozOiJ1cmwiO3M6MzE6Imh0dHBzOi8vbWl1bWkubWltdW5kb2RpZ2l0YWwubXgiO31zOjY6Il9mbGFzaCI7YToyOntzOjM6Im9sZCI7YTowOnt9czozOiJuZXciO2E6MDp7fX19',1777660085),('64HoNnNZJ1klgQpvjqiQ9FB8BYEi1WwtXoGPmBZT',NULL,'31.220.63.131','Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/44.0.2403.157 Safari/537.36','YTozOntzOjY6Il90b2tlbiI7czo0MDoiTVVocmdWSTNqNU82VTlMUE85TlpiRDlmSlpKck9zNlBVMm1ralNBQyI7czo5OiJfcHJldmlvdXMiO2E6MTp7czozOiJ1cmwiO3M6MzE6Imh0dHBzOi8vbWl1bWkubWltdW5kb2RpZ2l0YWwubXgiO31zOjY6Il9mbGFzaCI7YToyOntzOjM6Im9sZCI7YTowOnt9czozOiJuZXciO2E6MDp7fX19',1777695246),('6Hfz6PM8CfcApsDfo5nQJSrY3lrCvwXtHmLiHu6l',NULL,'31.220.63.131','Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/44.0.2403.157 Safari/537.36','YTozOntzOjY6Il90b2tlbiI7czo0MDoic3pRUENjMFVZaXhzRzk0a1J2RjM0c3YyeFNYYmQ4enJYRmIyZVNwUiI7czo5OiJfcHJldmlvdXMiO2E6MTp7czozOiJ1cmwiO3M6MzE6Imh0dHBzOi8vbWl1bWkubWltdW5kb2RpZ2l0YWwubXgiO31zOjY6Il9mbGFzaCI7YToyOntzOjM6Im9sZCI7YTowOnt9czozOiJuZXciO2E6MDp7fX19',1777710483),('6PnmGxf6wGhCpjk4Dif6RyTUUaByeIkMgVsCHNs6',NULL,'31.220.63.131','Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/44.0.2403.157 Safari/537.36','YTozOntzOjY6Il90b2tlbiI7czo0MDoibW90OEw0WFRHVXl6VXdmSmg0amZmeVN4TmtrTHMyT3M5ZVNZUjVwTSI7czo5OiJfcHJldmlvdXMiO2E6MTp7czozOiJ1cmwiO3M6Mzc6Imh0dHBzOi8vbWl1bWkubWltdW5kb2RpZ2l0YWwubXgvbG9naW4iO31zOjY6Il9mbGFzaCI7YToyOntzOjM6Im9sZCI7YTowOnt9czozOiJuZXciO2E6MDp7fX19',1777733767),('79AUmByNYlcnLKnZFAbj4DVnXGcMBXIByiPLiAgS',NULL,'31.220.63.131','Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/44.0.2403.157 Safari/537.36','YTozOntzOjY6Il90b2tlbiI7czo0MDoia05FZEcyWndtcEE2d3Y3VnJxRUo0Nmg0QnU5Y0wyOHc4Ym1HYlZ1MSI7czo5OiJfcHJldmlvdXMiO2E6MTp7czozOiJ1cmwiO3M6MzE6Imh0dHBzOi8vbWl1bWkubWltdW5kb2RpZ2l0YWwubXgiO31zOjY6Il9mbGFzaCI7YToyOntzOjM6Im9sZCI7YTowOnt9czozOiJuZXciO2E6MDp7fX19',1777709643),('7BPYvkvGzXWGlzffBgLZUgP0GCVyjgpLR0NAhqsJ',NULL,'31.220.63.131','Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/44.0.2403.157 Safari/537.36','YTozOntzOjY6Il90b2tlbiI7czo0MDoiVzhKcUlSSnBOWWhqanhac2x0N2xHWXVvWXpvemJkc1NIVmU5U0M4SyI7czo5OiJfcHJldmlvdXMiO2E6MTp7czozOiJ1cmwiO3M6Mzc6Imh0dHBzOi8vbWl1bWkubWltdW5kb2RpZ2l0YWwubXgvbG9naW4iO31zOjY6Il9mbGFzaCI7YToyOntzOjM6Im9sZCI7YTowOnt9czozOiJuZXciO2E6MDp7fX19',1777643763),('7No2K6M8BuPQD2WzxYsaulgx31ezfALgFSHREhSG',NULL,'31.220.63.131','Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/44.0.2403.157 Safari/537.36','YTozOntzOjY6Il90b2tlbiI7czo0MDoiQXJlaDZ2aFoxMzVjNE0xcHl5cVpZYm1OdVk5V2xoZjZUVjBHeUZFbiI7czo5OiJfcHJldmlvdXMiO2E6MTp7czozOiJ1cmwiO3M6MzE6Imh0dHBzOi8vbWl1bWkubWltdW5kb2RpZ2l0YWwubXgiO31zOjY6Il9mbGFzaCI7YToyOntzOjM6Im9sZCI7YTowOnt9czozOiJuZXciO2E6MDp7fX19',1777685287),('7XTLkcuip06rqivMh931FGaXxjjqjpNUyNHnW4BD',NULL,'31.220.63.131','Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/44.0.2403.157 Safari/537.36','YTozOntzOjY6Il90b2tlbiI7czo0MDoiSkRyUnhjT05wUEs4aTlHTmQ1Zk9JY2VuY3JFOUdVVGRWbXIwNWtLdyI7czo5OiJfcHJldmlvdXMiO2E6MTp7czozOiJ1cmwiO3M6Mzc6Imh0dHBzOi8vbWl1bWkubWltdW5kb2RpZ2l0YWwubXgvbG9naW4iO31zOjY6Il9mbGFzaCI7YToyOntzOjM6Im9sZCI7YTowOnt9czozOiJuZXciO2E6MDp7fX19',1777656485),('8fycBKmQcsnwx2KlkEVUinsxd87FgToP5Mxfh5AA',NULL,'31.220.63.131','Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/44.0.2403.157 Safari/537.36','YTozOntzOjY6Il90b2tlbiI7czo0MDoieXJBdm5CcnZ1R05Vd1lCalM4aTIwTTBDc3d2ZkhBVklPZW1Gd1VlWCI7czo5OiJfcHJldmlvdXMiO2E6MTp7czozOiJ1cmwiO3M6Mzc6Imh0dHBzOi8vbWl1bWkubWltdW5kb2RpZ2l0YWwubXgvbG9naW4iO31zOjY6Il9mbGFzaCI7YToyOntzOjM6Im9sZCI7YTowOnt9czozOiJuZXciO2E6MDp7fX19',1777712163),('9kRTiuV9QUA0XVwtNYi9ryL2bQrGtKOnGhsyjWp6',NULL,'31.220.63.131','Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/44.0.2403.157 Safari/537.36','YTozOntzOjY6Il90b2tlbiI7czo0MDoiQzY3S1dENk1uemxiVm9rNkdZSGZvY3ltQTAxSGM0ZmNLQ3lJU2tYcCI7czo5OiJfcHJldmlvdXMiO2E6MTp7czozOiJ1cmwiO3M6Mzc6Imh0dHBzOi8vbWl1bWkubWltdW5kb2RpZ2l0YWwubXgvbG9naW4iO31zOjY6Il9mbGFzaCI7YToyOntzOjM6Im9sZCI7YTowOnt9czozOiJuZXciO2E6MDp7fX19',1777698847),('9m4VT25PtIrpIajDW3DZormwkyr2UrVRrXjWNtCk',NULL,'31.220.63.131','Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/44.0.2403.157 Safari/537.36','YTozOntzOjY6Il90b2tlbiI7czo0MDoic0RXSUd5N3M1U1JzNVVzRUthcGZ5SGE1RDQ5Zm5kRG5VeXpFbGpCSCI7czo5OiJfcHJldmlvdXMiO2E6MTp7czozOiJ1cmwiO3M6Mzc6Imh0dHBzOi8vbWl1bWkubWltdW5kb2RpZ2l0YWwubXgvbG9naW4iO31zOjY6Il9mbGFzaCI7YToyOntzOjM6Im9sZCI7YTowOnt9czozOiJuZXciO2E6MDp7fX19',1777714084),('9nkaekffkcjNk4mfoPvXIiiIQEJFFdio0NwiFllA',NULL,'31.220.63.131','Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/44.0.2403.157 Safari/537.36','YTozOntzOjY6Il90b2tlbiI7czo0MDoiRkVBTkF5cVpDaU5kTEtKQklMS2N1WjJKNVphNzk3eE51WlZMblg1YiI7czo5OiJfcHJldmlvdXMiO2E6MTp7czozOiJ1cmwiO3M6Mzc6Imh0dHBzOi8vbWl1bWkubWltdW5kb2RpZ2l0YWwubXgvbG9naW4iO31zOjY6Il9mbGFzaCI7YToyOntzOjM6Im9sZCI7YTowOnt9czozOiJuZXciO2E6MDp7fX19',1777725726),('9OWcFOmrDfq58CpeSWaQWhYbTfWPl6gFO5LLWT9N',NULL,'31.220.63.131','Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/44.0.2403.157 Safari/537.36','YTozOntzOjY6Il90b2tlbiI7czo0MDoic3VhUGhJNndzM0xsOEJTQVVzWHJyVGpCR29QNXNOSTd6TlMwN1J2MSI7czo5OiJfcHJldmlvdXMiO2E6MTp7czozOiJ1cmwiO3M6Mzc6Imh0dHBzOi8vbWl1bWkubWltdW5kb2RpZ2l0YWwubXgvbG9naW4iO31zOjY6Il9mbGFzaCI7YToyOntzOjM6Im9sZCI7YTowOnt9czozOiJuZXciO2E6MDp7fX19',1777713244),('9s55kNbdUnzlcLeIex67RHMoxe5hftosHPWJUeKS',NULL,'31.220.63.131','Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/44.0.2403.157 Safari/537.36','YTozOntzOjY6Il90b2tlbiI7czo0MDoicXVCRE5LT1AxYUNLSFY0c1VDdVk1alMzeGNjU0FHd2V3RHdGVUZYeiI7czo5OiJfcHJldmlvdXMiO2E6MTp7czozOiJ1cmwiO3M6Mzc6Imh0dHBzOi8vbWl1bWkubWltdW5kb2RpZ2l0YWwubXgvbG9naW4iO31zOjY6Il9mbGFzaCI7YToyOntzOjM6Im9sZCI7YTowOnt9czozOiJuZXciO2E6MDp7fX19',1777700528),('a33I64fOZ3wfjW3M30Wi2cBJgbPhnpbV2qLq7jMQ',NULL,'31.220.63.131','Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/44.0.2403.157 Safari/537.36','YTozOntzOjY6Il90b2tlbiI7czo0MDoiY2R4RUpsUHVubUZkbElrY0JndUluRnV1emFzV01RN1hPNzFqekRYOCI7czo5OiJfcHJldmlvdXMiO2E6MTp7czozOiJ1cmwiO3M6Mzc6Imh0dHBzOi8vbWl1bWkubWltdW5kb2RpZ2l0YWwubXgvbG9naW4iO31zOjY6Il9mbGFzaCI7YToyOntzOjM6Im9sZCI7YTowOnt9czozOiJuZXciO2E6MDp7fX19',1777671726),('Adroxj6ubhRICIJUPnhMULUJ1UV4ieJSmdkgibvw',NULL,'31.220.63.131','Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/44.0.2403.157 Safari/537.36','YTozOntzOjY6Il90b2tlbiI7czo0MDoidzliVFp5U28wMUlvaFNjS0hWUjEwV045RlR5NFF2QzM5eUZBZndoZiI7czo5OiJfcHJldmlvdXMiO2E6MTp7czozOiJ1cmwiO3M6Mzc6Imh0dHBzOi8vbWl1bWkubWltdW5kb2RpZ2l0YWwubXgvbG9naW4iO31zOjY6Il9mbGFzaCI7YToyOntzOjM6Im9sZCI7YTowOnt9czozOiJuZXciO2E6MDp7fX19',1777684450),('afohw6QUmLjUJkOdwcIIWetUSF6FkE4w118MP9JR',NULL,'31.220.63.131','Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/44.0.2403.157 Safari/537.36','YTozOntzOjY6Il90b2tlbiI7czo0MDoiY2N2SURRYklmWGhxNWlyOUJoOXM1c1R2eXpIZlpRNjg3WnR1ZjRTSiI7czo5OiJfcHJldmlvdXMiO2E6MTp7czozOiJ1cmwiO3M6Mzc6Imh0dHBzOi8vbWl1bWkubWltdW5kb2RpZ2l0YWwubXgvbG9naW4iO31zOjY6Il9mbGFzaCI7YToyOntzOjM6Im9sZCI7YTowOnt9czozOiJuZXciO2E6MDp7fX19',1777688890),('AFv3hi3DrhIZJJHA7RrErOpSUZRii2kJRdOZ59tv',NULL,'31.220.63.131','Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/44.0.2403.157 Safari/537.36','YTozOntzOjY6Il90b2tlbiI7czo0MDoiNExsNkNWaGRiS1ZiTTZDNTBzVFVTQ0xVN0NKNHVIVzdHQlZpakRpUSI7czo5OiJfcHJldmlvdXMiO2E6MTp7czozOiJ1cmwiO3M6MzE6Imh0dHBzOi8vbWl1bWkubWltdW5kb2RpZ2l0YWwubXgiO31zOjY6Il9mbGFzaCI7YToyOntzOjM6Im9sZCI7YTowOnt9czozOiJuZXciO2E6MDp7fX19',1777734013),('aHTCZj8hI3MveujqxP2lfhtz08On2SWSlrzSY3Dy',NULL,'31.220.63.131','Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/44.0.2403.157 Safari/537.36','YTozOntzOjY6Il90b2tlbiI7czo0MDoieUQ3UW5RM1hvc0gyZTlWZ3lFbnBFZHpyRk5iWXhSRW4xUG5nUW95dyI7czo5OiJfcHJldmlvdXMiO2E6MTp7czozOiJ1cmwiO3M6Mzc6Imh0dHBzOi8vbWl1bWkubWltdW5kb2RpZ2l0YWwubXgvbG9naW4iO31zOjY6Il9mbGFzaCI7YToyOntzOjM6Im9sZCI7YTowOnt9czozOiJuZXciO2E6MDp7fX19',1777709643),('AiYQshcbymi67VsoAdRpxoIIP3wJWGlMe0BM8MQ3',NULL,'31.220.63.131','Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/44.0.2403.157 Safari/537.36','YTozOntzOjY6Il90b2tlbiI7czo0MDoiUERBU2h1T05KSDB0bWFCOEoxZHNINURpN0hwOENSbWQ0elBPR2JITSI7czo5OiJfcHJldmlvdXMiO2E6MTp7czozOiJ1cmwiO3M6Mzc6Imh0dHBzOi8vbWl1bWkubWltdW5kb2RpZ2l0YWwubXgvbG9naW4iO31zOjY6Il9mbGFzaCI7YToyOntzOjM6Im9sZCI7YTowOnt9czozOiJuZXciO2E6MDp7fX19',1777730165),('AladjILFYiXks4HNe89iAXY7Pxv8l0nBztxLqOvq',NULL,'31.220.63.131','Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/44.0.2403.157 Safari/537.36','YTozOntzOjY6Il90b2tlbiI7czo0MDoiaGlUZWpKMTRZZ3RPZkdZMHhzbkZZeG1iMzRzZVNXN3NOM2ZhbDhMQyI7czo5OiJfcHJldmlvdXMiO2E6MTp7czozOiJ1cmwiO3M6MzE6Imh0dHBzOi8vbWl1bWkubWltdW5kb2RpZ2l0YWwubXgiO31zOjY6Il9mbGFzaCI7YToyOntzOjM6Im9sZCI7YTowOnt9czozOiJuZXciO2E6MDp7fX19',1777706045),('ArBjeOYjAafzPui1zjeO88AnQfggZS3oPfhNFs03',NULL,'31.220.63.131','Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/44.0.2403.157 Safari/537.36','YTozOntzOjY6Il90b2tlbiI7czo0MDoiYU5MODBLMkVvaVRzeHlrOU1UdTNpNVlVblJPN1ZrRzR2Y1AycE5tSCI7czo5OiJfcHJldmlvdXMiO2E6MTp7czozOiJ1cmwiO3M6Mzc6Imh0dHBzOi8vbWl1bWkubWltdW5kb2RpZ2l0YWwubXgvbG9naW4iO31zOjY6Il9mbGFzaCI7YToyOntzOjM6Im9sZCI7YTowOnt9czozOiJuZXciO2E6MDp7fX19',1777706884),('AridhVu8ZoKDnI8wmkfX8d5zeJIIflOKLkSaJREo',NULL,'31.220.63.131','Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/44.0.2403.157 Safari/537.36','YTozOntzOjY6Il90b2tlbiI7czo0MDoidDM2M1laU3poZ3lXVjR5S2dVRWpwSEtSY0tSZGtNalY2VmRxMDBneSI7czo5OiJfcHJldmlvdXMiO2E6MTp7czozOiJ1cmwiO3M6MzE6Imh0dHBzOi8vbWl1bWkubWltdW5kb2RpZ2l0YWwubXgiO31zOjY6Il9mbGFzaCI7YToyOntzOjM6Im9sZCI7YTowOnt9czozOiJuZXciO2E6MDp7fX19',1777733767),('at3Yp0oWn7R756iCNXQ6jaZ4bkwbsa3UqIrzMGWh',NULL,'31.220.63.131','Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/44.0.2403.157 Safari/537.36','YTozOntzOjY6Il90b2tlbiI7czo0MDoiTThwVGtmZHk5eElkY1Bwb3ZWQTVJNVpzTnVaSGdsWnBEUDg2TjRrVCI7czo5OiJfcHJldmlvdXMiO2E6MTp7czozOiJ1cmwiO3M6MzE6Imh0dHBzOi8vbWl1bWkubWltdW5kb2RpZ2l0YWwubXgiO31zOjY6Il9mbGFzaCI7YToyOntzOjM6Im9sZCI7YTowOnt9czozOiJuZXciO2E6MDp7fX19',1777699683),('AUzJRYIaIOZotaDwBvuotqwfcpm0xNrHAMJIpUsl',NULL,'31.220.63.131','Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/44.0.2403.157 Safari/537.36','YTozOntzOjY6Il90b2tlbiI7czo0MDoiY2ppTmRyNENpN0c2QVNCWVhlQ1FrUkRMVEh5SmhOcktOTndMVjJReiI7czo5OiJfcHJldmlvdXMiO2E6MTp7czozOiJ1cmwiO3M6MzE6Imh0dHBzOi8vbWl1bWkubWltdW5kb2RpZ2l0YWwubXgiO31zOjY6Il9mbGFzaCI7YToyOntzOjM6Im9sZCI7YTowOnt9czozOiJuZXciO2E6MDp7fX19',1777701623),('BC3FRHgMALFrDxg9UjC8tQT23keFAGtKZKoVRl3G',NULL,'31.220.63.131','Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/44.0.2403.157 Safari/537.36','YTozOntzOjY6Il90b2tlbiI7czo0MDoiM2l1RTRvbmFiTXRzbnFYYm9uNGR0T096UjlZdmx4cGVDZk9PNE1ZbSI7czo5OiJfcHJldmlvdXMiO2E6MTp7czozOiJ1cmwiO3M6Mzc6Imh0dHBzOi8vbWl1bWkubWltdW5kb2RpZ2l0YWwubXgvbG9naW4iO31zOjY6Il9mbGFzaCI7YToyOntzOjM6Im9sZCI7YTowOnt9czozOiJuZXciO2E6MDp7fX19',1777641243),('beLjlaJnGqj15tgxkDrggIYSh7CJZrAJ8tFZkZMx',NULL,'31.220.63.131','Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/44.0.2403.157 Safari/537.36','YTozOntzOjY6Il90b2tlbiI7czo0MDoiNGhsZlFwVlZ4eVhjOTN6akMzVUxncDNuOVdrMHV5dmgwd1I1bXlOVSI7czo5OiJfcHJldmlvdXMiO2E6MTp7czozOiJ1cmwiO3M6Mzc6Imh0dHBzOi8vbWl1bWkubWltdW5kb2RpZ2l0YWwubXgvbG9naW4iO31zOjY6Il9mbGFzaCI7YToyOntzOjM6Im9sZCI7YTowOnt9czozOiJuZXciO2E6MDp7fX19',1777670044),('BpArNeqJ6xcBNKH3KHtqauZ2g24S8Ry5BQ79O51P',NULL,'31.220.63.131','Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/44.0.2403.157 Safari/537.36','YTozOntzOjY6Il90b2tlbiI7czo0MDoiTWFPS1U2dHRQV1pYbFZuN1NmdkpSTnhZMFdRRlp0d2xWWWxUMW9JTSI7czo5OiJfcHJldmlvdXMiO2E6MTp7czozOiJ1cmwiO3M6MzE6Imh0dHBzOi8vbWl1bWkubWltdW5kb2RpZ2l0YWwubXgiO31zOjY6Il9mbGFzaCI7YToyOntzOjM6Im9sZCI7YTowOnt9czozOiJuZXciO2E6MDp7fX19',1777718523),('CBlKj8oD855VB0HvXdNWMyikCHpv9atIDoyqd9ln',NULL,'31.220.63.131','Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/44.0.2403.157 Safari/537.36','YTozOntzOjY6Il90b2tlbiI7czo0MDoia2dvb2QybFJubGpCV0dIM3duclRCNTBzNG1RdmZ2ZTR5V1FnYmc0NSI7czo5OiJfcHJldmlvdXMiO2E6MTp7czozOiJ1cmwiO3M6MzE6Imh0dHBzOi8vbWl1bWkubWltdW5kb2RpZ2l0YWwubXgiO31zOjY6Il9mbGFzaCI7YToyOntzOjM6Im9sZCI7YTowOnt9czozOiJuZXciO2E6MDp7fX19',1777660923),('CiGzivsHnZ6rxzBeY9N1qQufMWTp8WIoGqG7gV2j',NULL,'31.220.63.131','Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/44.0.2403.157 Safari/537.36','YTozOntzOjY6Il90b2tlbiI7czo0MDoiV3Y3VGYxdExsV0YwYkxzVmUyako5YnprN0p5dExXMmFNOW1vTjliVyI7czo5OiJfcHJldmlvdXMiO2E6MTp7czozOiJ1cmwiO3M6Mzc6Imh0dHBzOi8vbWl1bWkubWltdW5kb2RpZ2l0YWwubXgvbG9naW4iO31zOjY6Il9mbGFzaCI7YToyOntzOjM6Im9sZCI7YTowOnt9czozOiJuZXciO2E6MDp7fX19',1777642084),('cPIYKNMOK9PmSHP5QyXgcG1Jg65YXjcrxfSfqKsA',NULL,'31.220.63.131','Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/44.0.2403.157 Safari/537.36','YTozOntzOjY6Il90b2tlbiI7czo0MDoidElLSUlFRWNRbmphVUR2cDRKVHJCejVNWE9ld2xNZTFnOWdBdnBjbCI7czo5OiJfcHJldmlvdXMiO2E6MTp7czozOiJ1cmwiO3M6Mzc6Imh0dHBzOi8vbWl1bWkubWltdW5kb2RpZ2l0YWwubXgvbG9naW4iO31zOjY6Il9mbGFzaCI7YToyOntzOjM6Im9sZCI7YTowOnt9czozOiJuZXciO2E6MDp7fX19',1777680846),('CTCeU8IyJOCph73ANKA4oeQMSozHMl5b9oSdm8ut',NULL,'31.220.63.131','Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/44.0.2403.157 Safari/537.36','YTozOntzOjY6Il90b2tlbiI7czo0MDoiYkFmc1JJZTIxQVB2VkhpclZIVUJaZ3Z0eUVmV29FQjlhbjdWUkNrcSI7czo5OiJfcHJldmlvdXMiO2E6MTp7czozOiJ1cmwiO3M6MzE6Imh0dHBzOi8vbWl1bWkubWltdW5kb2RpZ2l0YWwubXgiO31zOjY6Il9mbGFzaCI7YToyOntzOjM6Im9sZCI7YTowOnt9czozOiJuZXciO2E6MDp7fX19',1777644844),('cVMdK7oeNbnGuVZCdAawgvNN7QqUtH6ImwUKG2FO',NULL,'31.220.63.131','Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/44.0.2403.157 Safari/537.36','YTozOntzOjY6Il90b2tlbiI7czo0MDoiNkVXT0pwVzBIcU80amJQNUhoTWg5bjRtV3NnMzZuVVA1ek9oVWM4bSI7czo5OiJfcHJldmlvdXMiO2E6MTp7czozOiJ1cmwiO3M6Mzc6Imh0dHBzOi8vbWl1bWkubWltdW5kb2RpZ2l0YWwubXgvbG9naW4iO31zOjY6Il9mbGFzaCI7YToyOntzOjM6Im9sZCI7YTowOnt9czozOiJuZXciO2E6MDp7fX19',1777650123),('cXi3S3AUGIZn1VqOxRLILHg3UeI5sQEKNoGu0ekj',NULL,'31.220.63.131','Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/44.0.2403.157 Safari/537.36','YTozOntzOjY6Il90b2tlbiI7czo0MDoiTG0wN3VJdnRyd1pUdHEyOGplYzZlcmdMRzJOUTZ4Y0JOakF1TnlndyI7czo5OiJfcHJldmlvdXMiO2E6MTp7czozOiJ1cmwiO3M6Mzc6Imh0dHBzOi8vbWl1bWkubWltdW5kb2RpZ2l0YWwubXgvbG9naW4iO31zOjY6Il9mbGFzaCI7YToyOntzOjM6Im9sZCI7YTowOnt9czozOiJuZXciO2E6MDp7fX19',1777665363),('D1L4Zaw1beQaSh2E41McCkIftzIQpemUtRUAdrpl',NULL,'31.220.63.131','Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/44.0.2403.157 Safari/537.36','YTozOntzOjY6Il90b2tlbiI7czo0MDoiS1dtTlNYQ2F5cGU3aWNNenR4WGZwajJCOWM5RkR1eFVjTXoydDRrVSI7czo5OiJfcHJldmlvdXMiO2E6MTp7czozOiJ1cmwiO3M6Mzc6Imh0dHBzOi8vbWl1bWkubWltdW5kb2RpZ2l0YWwubXgvbG9naW4iO31zOjY6Il9mbGFzaCI7YToyOntzOjM6Im9sZCI7YTowOnt9czozOiJuZXciO2E6MDp7fX19',1777686123),('d21aXfLXGJYMOgfc2RN7cVzORZ1U0q13N7M7bQjF',NULL,'31.220.63.131','Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/44.0.2403.157 Safari/537.36','YTozOntzOjY6Il90b2tlbiI7czo0MDoiOUh2cmVUYW1GZmVtNDUzQWJjTkJhNFkyeTdXRzdHWFBrNGlPdFNiQyI7czo5OiJfcHJldmlvdXMiO2E6MTp7czozOiJ1cmwiO3M6MzE6Imh0dHBzOi8vbWl1bWkubWltdW5kb2RpZ2l0YWwubXgiO31zOjY6Il9mbGFzaCI7YToyOntzOjM6Im9sZCI7YTowOnt9czozOiJuZXciO2E6MDp7fX19',1777730409),('d8rlu6CoOV0Dbi4OipOoGg6tDG86HtjXvCSMe8qR',NULL,'31.220.63.131','Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/44.0.2403.157 Safari/537.36','YTozOntzOjY6Il90b2tlbiI7czo0MDoiOTB0UFJWZ3NNU3RsNlBURHNsNmJ2SEMxeXVWdXhHN0ZSUWRDWjJ4YSI7czo5OiJfcHJldmlvdXMiO2E6MTp7czozOiJ1cmwiO3M6Mzc6Imh0dHBzOi8vbWl1bWkubWltdW5kb2RpZ2l0YWwubXgvbG9naW4iO31zOjY6Il9mbGFzaCI7YToyOntzOjM6Im9sZCI7YTowOnt9czozOiJuZXciO2E6MDp7fX19',1777701623),('dHcaeeLwTBo4qbJNQxCD9msUgRSw86qbkb0mdmZD',NULL,'31.220.63.131','Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/44.0.2403.157 Safari/537.36','YTozOntzOjY6Il90b2tlbiI7czo0MDoib0dONUhtMVVtUnhYY0hkU3pWVmhob0JGZGJIZHlUVUFYVGRldWI3cyI7czo5OiJfcHJldmlvdXMiO2E6MTp7czozOiJ1cmwiO3M6Mzc6Imh0dHBzOi8vbWl1bWkubWltdW5kb2RpZ2l0YWwubXgvbG9naW4iO31zOjY6Il9mbGFzaCI7YToyOntzOjM6Im9sZCI7YTowOnt9czozOiJuZXciO2E6MDp7fX19',1777675325),('Dk9KqBCSNQW71Np0mkkeuyke994eNDMktHEjW8ic',NULL,'31.220.63.131','Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/44.0.2403.157 Safari/537.36','YTozOntzOjY6Il90b2tlbiI7czo0MDoiNHBYbnlxSkd0Y0JiNHZUSWNWbm4waVBPT0F5eGpnNWFwMFpia0RVdyI7czo5OiJfcHJldmlvdXMiO2E6MTp7czozOiJ1cmwiO3M6MzE6Imh0dHBzOi8vbWl1bWkubWltdW5kb2RpZ2l0YWwubXgiO31zOjY6Il9mbGFzaCI7YToyOntzOjM6Im9sZCI7YTowOnt9czozOiJuZXciO2E6MDp7fX19',1777735685),('dlDxXrvIheQMadRJBDOIafg6W6aSJ9QCKelX0xP6',NULL,'31.220.63.131','Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/44.0.2403.157 Safari/537.36','YTozOntzOjY6Il90b2tlbiI7czo0MDoidkhUZnBqdU1UaGp2NjRVbjVmT1J4WHNFRHhFMDhpQ2NyOFlYY3YyYiI7czo5OiJfcHJldmlvdXMiO2E6MTp7czozOiJ1cmwiO3M6MzE6Imh0dHBzOi8vbWl1bWkubWltdW5kb2RpZ2l0YWwubXgiO31zOjY6Il9mbGFzaCI7YToyOntzOjM6Im9sZCI7YTowOnt9czozOiJuZXciO2E6MDp7fX19',1777730165),('DmxOzgeSDlOB7qpNOsLez6UrXKDloSWmRNvB1ltV',NULL,'31.220.63.131','Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/44.0.2403.157 Safari/537.36','YTozOntzOjY6Il90b2tlbiI7czo0MDoiaFpscGxEOUU2TmNrTTRuSVpndGpPdlNhNlhxbFM4ZmY2b1FKQ29maSI7czo5OiJfcHJldmlvdXMiO2E6MTp7czozOiJ1cmwiO3M6MzE6Imh0dHBzOi8vbWl1bWkubWltdW5kb2RpZ2l0YWwubXgiO31zOjY6Il9mbGFzaCI7YToyOntzOjM6Im9sZCI7YTowOnt9czozOiJuZXciO2E6MDp7fX19',1777667287),('dPoj8GNuiHhND6ZsMWQnYyEyyj7D9YaxwWSrNsU7',NULL,'31.220.63.131','Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/44.0.2403.157 Safari/537.36','YTozOntzOjY6Il90b2tlbiI7czo0MDoiZmxYYjU3cWhyUWJWYndmR3hIVzVNMTVRUDg0OWZ6cmJvUFZ6OWQyRSI7czo5OiJfcHJldmlvdXMiO2E6MTp7czozOiJ1cmwiO3M6MzE6Imh0dHBzOi8vbWl1bWkubWltdW5kb2RpZ2l0YWwubXgiO31zOjY6Il9mbGFzaCI7YToyOntzOjM6Im9sZCI7YTowOnt9czozOiJuZXciO2E6MDp7fX19',1777694170),('DqDkUUzdpVodqQKYH96N3iIFBhtuFTOGXnwkORSk',NULL,'31.220.63.131','Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/44.0.2403.157 Safari/537.36','YTozOntzOjY6Il90b2tlbiI7czo0MDoicWtiZHcwdUFHSXZQQ0R2SVhKSGlHMGpWQ0tPUDU1b09ROG9jN0ZndyI7czo5OiJfcHJldmlvdXMiO2E6MTp7czozOiJ1cmwiO3M6Mzc6Imh0dHBzOi8vbWl1bWkubWltdW5kb2RpZ2l0YWwubXgvbG9naW4iO31zOjY6Il9mbGFzaCI7YToyOntzOjM6Im9sZCI7YTowOnt9czozOiJuZXciO2E6MDp7fX19',1777736531),('DX6pWxa6InLiyz9VdTT7nKuCXN3xcIzePw0syfzf',NULL,'31.220.63.131','Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/44.0.2403.157 Safari/537.36','YTozOntzOjY6Il90b2tlbiI7czo0MDoiblR4QlJmcHNaQ3RGUUcxejViNnJKT1lXcERhOWthdEJLVzZnZW9xMyI7czo5OiJfcHJldmlvdXMiO2E6MTp7czozOiJ1cmwiO3M6MzE6Imh0dHBzOi8vbWl1bWkubWltdW5kb2RpZ2l0YWwubXgiO31zOjY6Il9mbGFzaCI7YToyOntzOjM6Im9sZCI7YTowOnt9czozOiJuZXciO2E6MDp7fX19',1777673646),('DYnd8JoW8OAcVD8ta3RI8N1OtGkiq7gWz9YrnT2O',NULL,'31.220.63.131','Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/44.0.2403.157 Safari/537.36','YTozOntzOjY6Il90b2tlbiI7czo0MDoiQXRtNDRhMVBsNDJnTTEyendiTjNYTmdtR3pHa1pVSVZnSGlpc2RYUCI7czo5OiJfcHJldmlvdXMiO2E6MTp7czozOiJ1cmwiO3M6MzE6Imh0dHBzOi8vbWl1bWkubWltdW5kb2RpZ2l0YWwubXgiO31zOjY6Il9mbGFzaCI7YToyOntzOjM6Im9sZCI7YTowOnt9czozOiJuZXciO2E6MDp7fX19',1777684450),('EfVy5wVOcSVcX6PHLXxLcEKFjwh85pl3IFHnYxmd',NULL,'31.220.63.131','Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/44.0.2403.157 Safari/537.36','YTozOntzOjY6Il90b2tlbiI7czo0MDoiNGRBSURNUnFwUU5aV1VTb1duTXhacVllUFcycld5bmVwTmo3N3dRYiI7czo5OiJfcHJldmlvdXMiO2E6MTp7czozOiJ1cmwiO3M6MzE6Imh0dHBzOi8vbWl1bWkubWltdW5kb2RpZ2l0YWwubXgiO31zOjY6Il9mbGFzaCI7YToyOntzOjM6Im9sZCI7YTowOnt9czozOiJuZXciO2E6MDp7fX19',1777680846),('eGQjEwlWciPsTvks3WjWRtbDnUk3SiXpEHaoZB5q',NULL,'31.220.63.131','Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/44.0.2403.157 Safari/537.36','YTozOntzOjY6Il90b2tlbiI7czo0MDoicE41dU1XdXMxSmd2VU1kU0ROWFJmdzhHSWtaYnBScXAwbklXSmlGdCI7czo5OiJfcHJldmlvdXMiO2E6MTp7czozOiJ1cmwiO3M6MzE6Imh0dHBzOi8vbWl1bWkubWltdW5kb2RpZ2l0YWwubXgiO31zOjY6Il9mbGFzaCI7YToyOntzOjM6Im9sZCI7YTowOnt9czozOiJuZXciO2E6MDp7fX19',1777692496),('EHpj2rC8xz29vlRUGKRkM7VpAKHwQuh401NsHcci',NULL,'31.220.63.131','Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/44.0.2403.157 Safari/537.36','YTozOntzOjY6Il90b2tlbiI7czo0MDoiVG5SalE5ekVUSkcyVUI1NWhJZHMxOXJMVHdwSGhrOGl6TXBrbzh4TSI7czo5OiJfcHJldmlvdXMiO2E6MTp7czozOiJ1cmwiO3M6Mzc6Imh0dHBzOi8vbWl1bWkubWltdW5kb2RpZ2l0YWwubXgvbG9naW4iO31zOjY6Il9mbGFzaCI7YToyOntzOjM6Im9sZCI7YTowOnt9czozOiJuZXciO2E6MDp7fX19',1777650964),('EjC7kQI6pAhbYL6ZbNJ9mNAJn9NWRwGcpiOkWHQV',NULL,'31.220.63.131','Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/44.0.2403.157 Safari/537.36','YTozOntzOjY6Il90b2tlbiI7czo0MDoiWDByMVNwUUxVVmpSNWdONTVkY0N6U3NrQ0ZSZk1IZEh2Sjd0TUcwTSI7czo5OiJfcHJldmlvdXMiO2E6MTp7czozOiJ1cmwiO3M6MzE6Imh0dHBzOi8vbWl1bWkubWltdW5kb2RpZ2l0YWwubXgiO31zOjY6Il9mbGFzaCI7YToyOntzOjM6Im9sZCI7YTowOnt9czozOiJuZXciO2E6MDp7fX19',1777711323),('EoAwsQdVjFyEN9FyYdIB8099Sz4WNHUXRaT7A4Bv',NULL,'31.220.63.131','Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/44.0.2403.157 Safari/537.36','YTozOntzOjY6Il90b2tlbiI7czo0MDoieE5ScGtnQU1hUDBvYmhxWVRVZzlHQ29rSVBYbkZQcWp2eWhndjYwcyI7czo5OiJfcHJldmlvdXMiO2E6MTp7czozOiJ1cmwiO3M6Mzc6Imh0dHBzOi8vbWl1bWkubWltdW5kb2RpZ2l0YWwubXgvbG9naW4iO31zOjY6Il9mbGFzaCI7YToyOntzOjM6Im9sZCI7YTowOnt9czozOiJuZXciO2E6MDp7fX19',1777649283),('EOTIqqXiTsJwQzRMf4SN88ac1w9Ba68YDiencegZ',NULL,'31.220.63.131','Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/44.0.2403.157 Safari/537.36','YTozOntzOjY6Il90b2tlbiI7czo0MDoiY2QyUk56UHZMODlkckdtb25jS2k5clNmcVZmTXNVanJtQnRyWkdIVSI7czo5OiJfcHJldmlvdXMiO2E6MTp7czozOiJ1cmwiO3M6MzE6Imh0dHBzOi8vbWl1bWkubWltdW5kb2RpZ2l0YWwubXgiO31zOjY6Il9mbGFzaCI7YToyOntzOjM6Im9sZCI7YTowOnt9czozOiJuZXciO2E6MDp7fX19',1777683617),('eOWJqTxofVs5KXU69l0d81Y8rYyc2MxC0ndPqPKe',NULL,'31.220.63.131','Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/44.0.2403.157 Safari/537.36','YTozOntzOjY6Il90b2tlbiI7czo0MDoiaGhBR25tVTA0VHBvbVRoTlNxSm5aWVdldDg0Z2FXOE95eHhsOEdGSCI7czo5OiJfcHJldmlvdXMiO2E6MTp7czozOiJ1cmwiO3M6MzE6Imh0dHBzOi8vbWl1bWkubWltdW5kb2RpZ2l0YWwubXgiO31zOjY6Il9mbGFzaCI7YToyOntzOjM6Im9sZCI7YTowOnt9czozOiJuZXciO2E6MDp7fX19',1777654566),('eRIy2pD0sYONHmcTgUrqPQCAub2nVjrMpxEUMQPJ',NULL,'31.220.63.131','Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/44.0.2403.157 Safari/537.36','YTozOntzOjY6Il90b2tlbiI7czo0MDoickZqN2dwQU5pRUdnUjd1bVYyekVIbmRkYXFlTlJkU3RIcDVoTFhVayI7czo5OiJfcHJldmlvdXMiO2E6MTp7czozOiJ1cmwiO3M6Mzc6Imh0dHBzOi8vbWl1bWkubWltdW5kb2RpZ2l0YWwubXgvbG9naW4iO31zOjY6Il9mbGFzaCI7YToyOntzOjM6Im9sZCI7YTowOnt9czozOiJuZXciO2E6MDp7fX19',1777693331),('EvTZK5UP1SNZTjaXbX1tpU70QCWLtT8hNQZA0f5X',NULL,'31.220.63.131','Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/44.0.2403.157 Safari/537.36','YTozOntzOjY6Il90b2tlbiI7czo0MDoicnNqZVdUa1JpaGxKNVduVzhCTUw0UTZrTGxkNXdQU0dncDdRM3h4UyI7czo5OiJfcHJldmlvdXMiO2E6MTp7czozOiJ1cmwiO3M6Mzc6Imh0dHBzOi8vbWl1bWkubWltdW5kb2RpZ2l0YWwubXgvbG9naW4iO31zOjY6Il9mbGFzaCI7YToyOntzOjM6Im9sZCI7YTowOnt9czozOiJuZXciO2E6MDp7fX19',1777666444),('EZ67xZIT7V68P3PytSxE6mB8S7vTRjMt3M43MnZe',NULL,'31.220.63.131','Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/44.0.2403.157 Safari/537.36','YTozOntzOjY6Il90b2tlbiI7czo0MDoieFZ3YjJ1RWFtOVdadFZzbWlPUGptVUF1VEc3SEhFMVhTdDV1UGhSVSI7czo5OiJfcHJldmlvdXMiO2E6MTp7czozOiJ1cmwiO3M6Mzc6Imh0dHBzOi8vbWl1bWkubWltdW5kb2RpZ2l0YWwubXgvbG9naW4iO31zOjY6Il9mbGFzaCI7YToyOntzOjM6Im9sZCI7YTowOnt9czozOiJuZXciO2E6MDp7fX19',1777640410),('F38rSxqgW3YogaEOkkIN9IwKYitBh1nZV71NaYVv',NULL,'31.220.63.131','Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/44.0.2403.157 Safari/537.36','YTozOntzOjY6Il90b2tlbiI7czo0MDoiZjR2Y0s1MnFNMkxTNldndnAxeFRTRGJKM2pzWjVDckdNMzZPWkdwdiI7czo5OiJfcHJldmlvdXMiO2E6MTp7czozOiJ1cmwiO3M6MzE6Imh0dHBzOi8vbWl1bWkubWltdW5kb2RpZ2l0YWwubXgiO31zOjY6Il9mbGFzaCI7YToyOntzOjM6Im9sZCI7YTowOnt9czozOiJuZXciO2E6MDp7fX19',1777674484),('FdfX1wZyH4oOxd0Sr1UekdeSt3xmoBLlkSuCsmK9',NULL,'31.220.63.131','Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/44.0.2403.157 Safari/537.36','YTozOntzOjY6Il90b2tlbiI7czo0MDoiUElTcnplb1VrMm5Lajcwblp3UmVnVWhWOE1keU1JNVI2QXhJM3pvVyI7czo5OiJfcHJldmlvdXMiO2E6MTp7czozOiJ1cmwiO3M6Mzc6Imh0dHBzOi8vbWl1bWkubWltdW5kb2RpZ2l0YWwubXgvbG9naW4iO31zOjY6Il9mbGFzaCI7YToyOntzOjM6Im9sZCI7YTowOnt9czozOiJuZXciO2E6MDp7fX19',1777692496),('Fdz1Mq2deAAtrga1pIGeXhqMITnPuosTb2iXmRdb',NULL,'31.220.63.131','Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/44.0.2403.157 Safari/537.36','YTozOntzOjY6Il90b2tlbiI7czo0MDoibVRVOU96TjJBMjF5VTlwRkoxVDVlMVdqZ2pIRmUyV1VyaDJVTHh4aSI7czo5OiJfcHJldmlvdXMiO2E6MTp7czozOiJ1cmwiO3M6Mzc6Imh0dHBzOi8vbWl1bWkubWltdW5kb2RpZ2l0YWwubXgvbG9naW4iO31zOjY6Il9mbGFzaCI7YToyOntzOjM6Im9sZCI7YTowOnt9czozOiJuZXciO2E6MDp7fX19',1777726563),('fFTwq5EVG0b0N9PL78VhiY8FYWYfNMTnW9IZ33Vh',NULL,'31.220.63.131','Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/44.0.2403.157 Safari/537.36','YTozOntzOjY6Il90b2tlbiI7czo0MDoiQWY2dm1rWEhhMXdYbHg2SndVd2JwM3dwbVdQaGNxcnNuVTJsRHJSOCI7czo5OiJfcHJldmlvdXMiO2E6MTp7czozOiJ1cmwiO3M6MzE6Imh0dHBzOi8vbWl1bWkubWltdW5kb2RpZ2l0YWwubXgiO31zOjY6Il9mbGFzaCI7YToyOntzOjM6Im9sZCI7YTowOnt9czozOiJuZXciO2E6MDp7fX19',1777679767),('FIHE4FCQ8xaZhDXDOrj16R0UYn25ltfRJ9bgk3cg',NULL,'31.220.63.131','Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/44.0.2403.157 Safari/537.36','YTozOntzOjY6Il90b2tlbiI7czo0MDoicURBVzAxM1FQYVFIaUVPeUlOaG5wbzlZWmlHWHQ2aFFJWGxKNmpWWCI7czo5OiJfcHJldmlvdXMiO2E6MTp7czozOiJ1cmwiO3M6Mzc6Imh0dHBzOi8vbWl1bWkubWltdW5kb2RpZ2l0YWwubXgvbG9naW4iO31zOjY6Il9mbGFzaCI7YToyOntzOjM6Im9sZCI7YTowOnt9czozOiJuZXciO2E6MDp7fX19',1777654566),('fkAaTIv73sUE83N697zkojzvxlAxQCUEXg1l20go',NULL,'31.220.63.131','Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/44.0.2403.157 Safari/537.36','YTozOntzOjY6Il90b2tlbiI7czo0MDoiWDNBM21TNndTZXlRNXR5N3hrY1NZNndDeGxweVpPdkhNRGxOT0dTQiI7czo5OiJfcHJldmlvdXMiO2E6MTp7czozOiJ1cmwiO3M6Mzc6Imh0dHBzOi8vbWl1bWkubWltdW5kb2RpZ2l0YWwubXgvbG9naW4iO31zOjY6Il9mbGFzaCI7YToyOntzOjM6Im9sZCI7YTowOnt9czozOiJuZXciO2E6MDp7fX19',1777647611),('fKQdWL0F9v9BkjisRIv5TIyPxg7wFD1Jk1XD2CJ4',NULL,'31.220.63.131','Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/44.0.2403.157 Safari/537.36','YTozOntzOjY6Il90b2tlbiI7czo0MDoiMlZaZHJaYXZMczF0czAxdU9LbkFkMjhHWmN1TXJRcGtxdDV4aVRmbiI7czo5OiJfcHJldmlvdXMiO2E6MTp7czozOiJ1cmwiO3M6Mzc6Imh0dHBzOi8vbWl1bWkubWltdW5kb2RpZ2l0YWwubXgvbG9naW4iO31zOjY6Il9mbGFzaCI7YToyOntzOjM6Im9sZCI7YTowOnt9czozOiJuZXciO2E6MDp7fX19',1777681688),('fui2VWI5jrkfrGeP0KvS6kLkP0wF3JfRCc7ywq0q',NULL,'31.220.63.131','Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/44.0.2403.157 Safari/537.36','YTozOntzOjY6Il90b2tlbiI7czo0MDoiTG5qTmZMYk1seVNkNnJUalljU3BnU1hwdG44b3BvczBXS09lbTEweSI7czo5OiJfcHJldmlvdXMiO2E6MTp7czozOiJ1cmwiO3M6Mzc6Imh0dHBzOi8vbWl1bWkubWltdW5kb2RpZ2l0YWwubXgvbG9naW4iO31zOjY6Il9mbGFzaCI7YToyOntzOjM6Im9sZCI7YTowOnt9czozOiJuZXciO2E6MDp7fX19',1777657324),('fWE6FZLcanns8GBPp3zNBeKHSTtOz7Om52dZSdnM',NULL,'31.220.63.131','Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/44.0.2403.157 Safari/537.36','YTozOntzOjY6Il90b2tlbiI7czo0MDoiVExnQk5MUE9jV1lTT01hb1BvRGxkVUNzV2J0RWNQRFZPTHBxeHNzcSI7czo5OiJfcHJldmlvdXMiO2E6MTp7czozOiJ1cmwiO3M6Mzc6Imh0dHBzOi8vbWl1bWkubWltdW5kb2RpZ2l0YWwubXgvbG9naW4iO31zOjY6Il9mbGFzaCI7YToyOntzOjM6Im9sZCI7YTowOnt9czozOiJuZXciO2E6MDp7fX19',1777724044),('fYP6k0lqBnThkeJKKeWlKdXNQODs1xFXMMXcnmvr',NULL,'31.220.63.131','Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/44.0.2403.157 Safari/537.36','YTozOntzOjY6Il90b2tlbiI7czo0MDoieVZSWWU4aklKR25ZTk8yeUNpaTFqUGJWWWhCZGFRV0hKaHhyN1ZldCI7czo5OiJfcHJldmlvdXMiO2E6MTp7czozOiJ1cmwiO3M6MzE6Imh0dHBzOi8vbWl1bWkubWltdW5kb2RpZ2l0YWwubXgiO31zOjY6Il9mbGFzaCI7YToyOntzOjM6Im9sZCI7YTowOnt9czozOiJuZXciO2E6MDp7fX19',1777698846),('g3UyQYjlbU99iwkVMPfbsCmz7EISpU2kMHL6FGYn',NULL,'31.220.63.131','Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/44.0.2403.157 Safari/537.36','YTozOntzOjY6Il90b2tlbiI7czo0MDoiekhhczVEUW1ZMENaVmdmVk54SjhnVTk5Zjl1WGRNN3lKQjNjWkZGTyI7czo5OiJfcHJldmlvdXMiO2E6MTp7czozOiJ1cmwiO3M6Mzc6Imh0dHBzOi8vbWl1bWkubWltdW5kb2RpZ2l0YWwubXgvbG9naW4iO31zOjY6Il9mbGFzaCI7YToyOntzOjM6Im9sZCI7YTowOnt9czozOiJuZXciO2E6MDp7fX19',1777690824),('G8LDk6vndiTKDSGVXsxuQ7WLgHyaqcIJXE3hOBIz',NULL,'31.220.63.131','Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/44.0.2403.157 Safari/537.36','YTozOntzOjY6Il90b2tlbiI7czo0MDoiSHFsM1RLdmVjbkhSQkl0bWxVOUxTTnV4amJJZ1lURFB5MzdmZHIwNiI7czo5OiJfcHJldmlvdXMiO2E6MTp7czozOiJ1cmwiO3M6MzE6Imh0dHBzOi8vbWl1bWkubWltdW5kb2RpZ2l0YWwubXgiO31zOjY6Il9mbGFzaCI7YToyOntzOjM6Im9sZCI7YTowOnt9czozOiJuZXciO2E6MDp7fX19',1777645684),('G8lnhH4IfQorrrbGXpIALiwW7N5y5MojQYsSxPDP',NULL,'31.220.63.131','Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/44.0.2403.157 Safari/537.36','YTozOntzOjY6Il90b2tlbiI7czo0MDoiWERHS3dwNDFzNHlYeUh5OTI0cDNIa2ZCbnhPVU16YUttNnl0WjgybSI7czo5OiJfcHJldmlvdXMiO2E6MTp7czozOiJ1cmwiO3M6Mzc6Imh0dHBzOi8vbWl1bWkubWltdW5kb2RpZ2l0YWwubXgvbG9naW4iO31zOjY6Il9mbGFzaCI7YToyOntzOjM6Im9sZCI7YTowOnt9czozOiJuZXciO2E6MDp7fX19',1777726815),('ggfaM5iGI6CmdR9kfZRkFjVTU4Xrm6xZ59fVCsg2',NULL,'31.220.63.131','Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/44.0.2403.157 Safari/537.36','YTozOntzOjY6Il90b2tlbiI7czo0MDoiQkJ4c2YzODJyTXRmSzI2V1pRbEFHcWs5Z1pQYjlTdVFMUzJNbjdJRSI7czo5OiJfcHJldmlvdXMiO2E6MTp7czozOiJ1cmwiO3M6MzE6Imh0dHBzOi8vbWl1bWkubWltdW5kb2RpZ2l0YWwubXgiO31zOjY6Il9mbGFzaCI7YToyOntzOjM6Im9sZCI7YTowOnt9czozOiJuZXciO2E6MDp7fX19',1777642084),('GnIdLD7u75UtXSEn36bbZLXefvyst1aENbKRrL3i',NULL,'31.220.63.131','Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/44.0.2403.157 Safari/537.36','YTozOntzOjY6Il90b2tlbiI7czo0MDoiZEI2R1VHT04wMGhLcmZhcHZiaEV0RXNKNGZnRVNpaUtrRnJBSktMUiI7czo5OiJfcHJldmlvdXMiO2E6MTp7czozOiJ1cmwiO3M6MzE6Imh0dHBzOi8vbWl1bWkubWltdW5kb2RpZ2l0YWwubXgiO31zOjY6Il9mbGFzaCI7YToyOntzOjM6Im9sZCI7YTowOnt9czozOiJuZXciO2E6MDp7fX19',1777647611),('GoAapYapjmLWbEeBmRjw9sW4WFqjlwTgjkexM8Pf',NULL,'31.220.63.131','Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/44.0.2403.157 Safari/537.36','YTozOntzOjY6Il90b2tlbiI7czo0MDoiWDdhUTQxYk5YT0l5NHBia0M3STZmWnpDVkExeVdodWo2VVpRMlE3biI7czo5OiJfcHJldmlvdXMiO2E6MTp7czozOiJ1cmwiO3M6MzE6Imh0dHBzOi8vbWl1bWkubWltdW5kb2RpZ2l0YWwubXgiO31zOjY6Il9mbGFzaCI7YToyOntzOjM6Im9sZCI7YTowOnt9czozOiJuZXciO2E6MDp7fX19',1777712163),('gUWsWiv8jdj6ZNIh6swPOuzZg67LVNA74pHnCn1N',NULL,'31.220.63.131','Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/44.0.2403.157 Safari/537.36','YTozOntzOjY6Il90b2tlbiI7czo0MDoieDNpbENYZHNlQkR4a3ZqZUpvNW1kcjR2Z1BJbTJHTEJGSlpTVEI2aCI7czo5OiJfcHJldmlvdXMiO2E6MTp7czozOiJ1cmwiO3M6MzE6Imh0dHBzOi8vbWl1bWkubWltdW5kb2RpZ2l0YWwubXgiO31zOjY6Il9mbGFzaCI7YToyOntzOjM6Im9sZCI7YTowOnt9czozOiJuZXciO2E6MDp7fX19',1777650123),('gxW2E3dbZ244FPlEpX0AsWqf5Dku60ApFu4Wt17D',NULL,'31.220.63.131','Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/44.0.2403.157 Safari/537.36','YTozOntzOjY6Il90b2tlbiI7czo0MDoieEdQNW8zQ2syQnNuQmxwNU45SWZMcWZUNlBqb0xEZjJ3U1lhM1d4RCI7czo5OiJfcHJldmlvdXMiO2E6MTp7czozOiJ1cmwiO3M6MzE6Imh0dHBzOi8vbWl1bWkubWltdW5kb2RpZ2l0YWwubXgiO31zOjY6Il9mbGFzaCI7YToyOntzOjM6Im9sZCI7YTowOnt9czozOiJuZXciO2E6MDp7fX19',1777642923),('h6PC58RF5doJazu1eZgY7kmcjMPu0hhqQLQXAjw7',NULL,'31.220.63.131','Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/44.0.2403.157 Safari/537.36','YTozOntzOjY6Il90b2tlbiI7czo0MDoiZWMyMFFJTU96dEZrNEtVS1JUbVJ4cFF2MDdRTmFoeUxSamVSQVI5ZyI7czo5OiJfcHJldmlvdXMiO2E6MTp7czozOiJ1cmwiO3M6MzE6Imh0dHBzOi8vbWl1bWkubWltdW5kb2RpZ2l0YWwubXgiO31zOjY6Il9mbGFzaCI7YToyOntzOjM6Im9sZCI7YTowOnt9czozOiJuZXciO2E6MDp7fX19',1777643763),('H9gGXsvyOrUP2fw4OWGEAxVQ95K40e8wRK93N08u',NULL,'31.220.63.131','Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/44.0.2403.157 Safari/537.36','YTozOntzOjY6Il90b2tlbiI7czo0MDoiTjVZc1AzbElRS0d0bU1pd29PZzA0T1RNQXdReDU4djR4cVQ4QU92OCI7czo5OiJfcHJldmlvdXMiO2E6MTp7czozOiJ1cmwiO3M6MzE6Imh0dHBzOi8vbWl1bWkubWltdW5kb2RpZ2l0YWwubXgiO31zOjY6Il9mbGFzaCI7YToyOntzOjM6Im9sZCI7YTowOnt9czozOiJuZXciO2E6MDp7fX19',1777662843),('HmLRj7wAYFLa6JMIqydvi1V29W5iMxSaKKH0HMI6',NULL,'31.220.63.131','Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/44.0.2403.157 Safari/537.36','YTozOntzOjY6Il90b2tlbiI7czo0MDoiVm5iQ09HcHh3aUZxSE9XQld2UDdHdGZrRGIySkZqUzVYeVRtcXNMdSI7czo5OiJfcHJldmlvdXMiO2E6MTp7czozOiJ1cmwiO3M6MzE6Imh0dHBzOi8vbWl1bWkubWltdW5kb2RpZ2l0YWwubXgiO31zOjY6Il9mbGFzaCI7YToyOntzOjM6Im9sZCI7YTowOnt9czozOiJuZXciO2E6MDp7fX19',1777690824),('hmZRCwuzehg7dNNl4jlMx04D1v2sUvQz9kheQHnI',NULL,'31.220.63.131','Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/44.0.2403.157 Safari/537.36','YTozOntzOjY6Il90b2tlbiI7czo0MDoiamszY09DOE5lVDVHRTQ2ZFJOQmtHTHU3V1FSdWo4VEo2RG9VN2w3RCI7czo5OiJfcHJldmlvdXMiO2E6MTp7czozOiJ1cmwiO3M6MzE6Imh0dHBzOi8vbWl1bWkubWltdW5kb2RpZ2l0YWwubXgiO31zOjY6Il9mbGFzaCI7YToyOntzOjM6Im9sZCI7YTowOnt9czozOiJuZXciO2E6MDp7fX19',1777650964),('hq4sA9cQpygr6KUTYOeInnhpRtVSrRdDYeLDE9GL',NULL,'31.220.63.131','Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/44.0.2403.157 Safari/537.36','YTozOntzOjY6Il90b2tlbiI7czo0MDoidmNLVDlrTmFheGYyQzA5dkZuYkphT3dXaGR4Y2RxWnBMOTVWMWlhSiI7czo5OiJfcHJldmlvdXMiO2E6MTp7czozOiJ1cmwiO3M6MzE6Imh0dHBzOi8vbWl1bWkubWltdW5kb2RpZ2l0YWwubXgiO31zOjY6Il9mbGFzaCI7YToyOntzOjM6Im9sZCI7YTowOnt9czozOiJuZXciO2E6MDp7fX19',1777670885),('hxN8VaPVi56WgpUY0H2517CjGScPRB5LJpc1Y0pX',NULL,'31.220.63.131','Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/44.0.2403.157 Safari/537.36','YTozOntzOjY6Il90b2tlbiI7czo0MDoiVmlxbU4zeFRXZHFEaFYwZnV4SVRXOHdybURKYVFWUk9yYVVVZXFpdiI7czo5OiJfcHJldmlvdXMiO2E6MTp7czozOiJ1cmwiO3M6Mzc6Imh0dHBzOi8vbWl1bWkubWltdW5kb2RpZ2l0YWwubXgvbG9naW4iO31zOjY6Il9mbGFzaCI7YToyOntzOjM6Im9sZCI7YTowOnt9czozOiJuZXciO2E6MDp7fX19',1777694426),('HY9odelPYV6eCmGY1pptDXqGZK9rNlkwLR2So9tk',NULL,'31.220.63.131','Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/44.0.2403.157 Safari/537.36','YTozOntzOjY6Il90b2tlbiI7czo0MDoiNURtOE9nYmxrOGRnOXhsNTNhSnpqNW4xWlc5eDZodHlnb1kzenF1eCI7czo5OiJfcHJldmlvdXMiO2E6MTp7czozOiJ1cmwiO3M6MzE6Imh0dHBzOi8vbWl1bWkubWltdW5kb2RpZ2l0YWwubXgiO31zOjY6Il9mbGFzaCI7YToyOntzOjM6Im9sZCI7YTowOnt9czozOiJuZXciO2E6MDp7fX19',1777732085),('iMx9TG6FcsHmWffmB76s4BIJLiMrMEq2Y9NnQy1D',NULL,'31.220.63.131','Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/44.0.2403.157 Safari/537.36','YTozOntzOjY6Il90b2tlbiI7czo0MDoibmlUZnBQR1ZQVWw2b1YzU3h5cVdCcWxsSU5OaWFTb09qYUREeG96RiI7czo5OiJfcHJldmlvdXMiO2E6MTp7czozOiJ1cmwiO3M6Mzc6Imh0dHBzOi8vbWl1bWkubWltdW5kb2RpZ2l0YWwubXgvbG9naW4iO31zOjY6Il9mbGFzaCI7YToyOntzOjM6Im9sZCI7YTowOnt9czozOiJuZXciO2E6MDp7fX19',1777647363),('IPapzuBBAFs3HjmNYRbYS8mRacWe5D6dP11Y7ClS',NULL,'31.220.63.131','Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/44.0.2403.157 Safari/537.36','YTozOntzOjY6Il90b2tlbiI7czo0MDoieE00WmR6anZzVWsydkN5ODJ2OHliVkVaN3JtN3RKUlBmN25FdmZ5aSI7czo5OiJfcHJldmlvdXMiO2E6MTp7czozOiJ1cmwiO3M6MzE6Imh0dHBzOi8vbWl1bWkubWltdW5kb2RpZ2l0YWwubXgiO31zOjY6Il9mbGFzaCI7YToyOntzOjM6Im9sZCI7YTowOnt9czozOiJuZXciO2E6MDp7fX19',1777683365),('IsBsOtg6FTZmrXVYjRVS21tpsUco5UskOZK3vZWD',NULL,'31.220.63.131','Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/44.0.2403.157 Safari/537.36','YTozOntzOjY6Il90b2tlbiI7czo0MDoia1ZodWhldElOTDFQOUU1MHVNS01BS1c1RWtQZlV3UUZROUgzb2hDdyI7czo5OiJfcHJldmlvdXMiO2E6MTp7czozOiJ1cmwiO3M6MzE6Imh0dHBzOi8vbWl1bWkubWltdW5kb2RpZ2l0YWwubXgiO31zOjY6Il9mbGFzaCI7YToyOntzOjM6Im9sZCI7YTowOnt9czozOiJuZXciO2E6MDp7fX19',1777726815),('IUjvifqlnx7pMVaLYRm75GOTBnwHQURggI6ly7Tx',NULL,'31.220.63.131','Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/44.0.2403.157 Safari/537.36','YTozOntzOjY6Il90b2tlbiI7czo0MDoid3hpcGluNmVsQk5oajBReXIyUVVoNlhYakUyZzRBS05CYlFPOWZMYiI7czo5OiJfcHJldmlvdXMiO2E6MTp7czozOiJ1cmwiO3M6MzE6Imh0dHBzOi8vbWl1bWkubWltdW5kb2RpZ2l0YWwubXgiO31zOjY6Il9mbGFzaCI7YToyOntzOjM6Im9sZCI7YTowOnt9czozOiJuZXciO2E6MDp7fX19',1777664524),('j8LQLUAsHEJDWmezmdsFy0LYIA1xLRf3S0eRJJcc',NULL,'31.220.63.131','Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/44.0.2403.157 Safari/537.36','YTozOntzOjY6Il90b2tlbiI7czo0MDoicE14NDJWSDIwNGZLT2drSGc2TG9FYW05TWFnRG1lWTB1Z0RKc1dGcyI7czo5OiJfcHJldmlvdXMiO2E6MTp7czozOiJ1cmwiO3M6Mzc6Imh0dHBzOi8vbWl1bWkubWltdW5kb2RpZ2l0YWwubXgvbG9naW4iO31zOjY6Il9mbGFzaCI7YToyOntzOjM6Im9sZCI7YTowOnt9czozOiJuZXciO2E6MDp7fX19',1777689725),('JAd97uTiBkKJrFGIRJPVXaqC3WLhvXly0pAzhMfR',NULL,'31.220.63.131','Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/44.0.2403.157 Safari/537.36','YTozOntzOjY6Il90b2tlbiI7czo0MDoidW9LOHZYMjJ2OG1Fa3RvbFBFVFp1UjNvT0xMZUplejRSY1Zub2NSRSI7czo5OiJfcHJldmlvdXMiO2E6MTp7czozOiJ1cmwiO3M6Mzc6Imh0dHBzOi8vbWl1bWkubWltdW5kb2RpZ2l0YWwubXgvbG9naW4iO31zOjY6Il9mbGFzaCI7YToyOntzOjM6Im9sZCI7YTowOnt9czozOiJuZXciO2E6MDp7fX19',1777717683),('jaR6mxg8R1z6KtuHg7gpvxjjv8UDgEoh5RL7IKXb',NULL,'31.220.63.131','Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/44.0.2403.157 Safari/537.36','YTozOntzOjY6Il90b2tlbiI7czo0MDoidnhMS0l2bFlkenFPUEoyN2o0dWNuV3RRVlN4WURFalJzNWlGNnhIZyI7czo5OiJfcHJldmlvdXMiO2E6MTp7czozOiJ1cmwiO3M6Mzc6Imh0dHBzOi8vbWl1bWkubWltdW5kb2RpZ2l0YWwubXgvbG9naW4iO31zOjY6Il9mbGFzaCI7YToyOntzOjM6Im9sZCI7YTowOnt9czozOiJuZXciO2E6MDp7fX19',1777658163),('JATiciVnzLZ3yDpNxY7TfFcRXb9KDfJ5wK847JP1',NULL,'31.220.63.131','Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/44.0.2403.157 Safari/537.36','YTozOntzOjY6Il90b2tlbiI7czo0MDoiajQ5M0oweGZWcWVEeFhRYVY0QlpyZG9aZ0s5b0Y2RHhXNlM2dUVtRCI7czo5OiJfcHJldmlvdXMiO2E6MTp7czozOiJ1cmwiO3M6Mzc6Imh0dHBzOi8vbWl1bWkubWltdW5kb2RpZ2l0YWwubXgvbG9naW4iO31zOjY6Il9mbGFzaCI7YToyOntzOjM6Im9sZCI7YTowOnt9czozOiJuZXciO2E6MDp7fX19',1777668125),('jbnIF7pD6SZvjByEj1DqrTH24GEEMvOTrDZ2tE8l',NULL,'31.220.63.131','Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/44.0.2403.157 Safari/537.36','YTozOntzOjY6Il90b2tlbiI7czo0MDoiTVNlaWlSMG1lV3FqeUx0VENpeEVUVm55alZsd1IzVTF2eU96MEE1eCI7czo5OiJfcHJldmlvdXMiO2E6MTp7czozOiJ1cmwiO3M6MzE6Imh0dHBzOi8vbWl1bWkubWltdW5kb2RpZ2l0YWwubXgiO31zOjY6Il9mbGFzaCI7YToyOntzOjM6Im9sZCI7YTowOnt9czozOiJuZXciO2E6MDp7fX19',1777700528),('Jd2dZfLiYq1vKOCOLXOhFqMck0upzv1GvaHogfTr',NULL,'31.220.63.131','Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/44.0.2403.157 Safari/537.36','YTozOntzOjY6Il90b2tlbiI7czo0MDoiVVkxYnpXNWtJSDJLY3AwTWN6a1VwbUp1ek9YR1ZlNGhaazR2N3FYViI7czo5OiJfcHJldmlvdXMiO2E6MTp7czozOiJ1cmwiO3M6Mzc6Imh0dHBzOi8vbWl1bWkubWltdW5kb2RpZ2l0YWwubXgvbG9naW4iO31zOjY6Il9mbGFzaCI7YToyOntzOjM6Im9sZCI7YTowOnt9czozOiJuZXciO2E6MDp7fX19',1777648443),('jdD3yYAJNX3Imxllz0YwNBRaqfiBwoAMfgUIDVUR',NULL,'31.220.63.131','Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/44.0.2403.157 Safari/537.36','YTozOntzOjY6Il90b2tlbiI7czo0MDoiYXg2ekVOTFNhd3Q4aEZSQ01OTUdYVUJ6cjhKZENNVEhJb3dHVUZKRSI7czo5OiJfcHJldmlvdXMiO2E6MTp7czozOiJ1cmwiO3M6Mzc6Imh0dHBzOi8vbWl1bWkubWltdW5kb2RpZ2l0YWwubXgvbG9naW4iO31zOjY6Il9mbGFzaCI7YToyOntzOjM6Im9sZCI7YTowOnt9czozOiJuZXciO2E6MDp7fX19',1777665610),('jDDq8spw75XTL4VBQpPzM5owpKJtPYoVHY7dPBhh',NULL,'31.220.63.131','Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/44.0.2403.157 Safari/537.36','YTozOntzOjY6Il90b2tlbiI7czo0MDoiazdNbmY5cW00WVF3b1JWQ3VvYkUyeEN1QW9zdDR4cjN1a2pzQ1AyUyI7czo5OiJfcHJldmlvdXMiO2E6MTp7czozOiJ1cmwiO3M6MzE6Imh0dHBzOi8vbWl1bWkubWltdW5kb2RpZ2l0YWwubXgiO31zOjY6Il9mbGFzaCI7YToyOntzOjM6Im9sZCI7YTowOnt9czozOiJuZXciO2E6MDp7fX19',1777707723),('JfRFwt9S55GEEzj2FGQk0xsgbBU2QY1bAl3qSKoJ',NULL,'31.220.63.131','Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/44.0.2403.157 Safari/537.36','YTozOntzOjY6Il90b2tlbiI7czo0MDoiNWFhdDY5UGI1OFNZQURSVXNNQ3czM0x3ejc2a1E1RExPbFFtazZ5UCI7czo5OiJfcHJldmlvdXMiO2E6MTp7czozOiJ1cmwiO3M6Mzc6Imh0dHBzOi8vbWl1bWkubWltdW5kb2RpZ2l0YWwubXgvbG9naW4iO31zOjY6Il9mbGFzaCI7YToyOntzOjM6Im9sZCI7YTowOnt9czozOiJuZXciO2E6MDp7fX19',1777644844),('jjsHnPxravPXskGVFQMUW111DwbjhxO6u8BwVPvl',NULL,'31.220.63.131','Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/44.0.2403.157 Safari/537.36','YTozOntzOjY6Il90b2tlbiI7czo0MDoiWEZWelA4aFZIVG5MTkFFcVVhOWVKZHBFRGFibGd1bWxlZml2dUhkUSI7czo5OiJfcHJldmlvdXMiO2E6MTp7czozOiJ1cmwiO3M6Mzc6Imh0dHBzOi8vbWl1bWkubWltdW5kb2RpZ2l0YWwubXgvbG9naW4iO31zOjY6Il9mbGFzaCI7YToyOntzOjM6Im9sZCI7YTowOnt9czozOiJuZXciO2E6MDp7fX19',1777708563),('jR8LOwOhWctWQjo1beeLp4ZXEBKoVeeFQpzFnVDz',NULL,'31.220.63.131','Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/44.0.2403.157 Safari/537.36','YTozOntzOjY6Il90b2tlbiI7czo0MDoibGlFc0RTRkRiR05acmhOajVQRGdGcG1mR3AwRTRwdDRxb3ZOblM3dCI7czo5OiJfcHJldmlvdXMiO2E6MTp7czozOiJ1cmwiO3M6Mzc6Imh0dHBzOi8vbWl1bWkubWltdW5kb2RpZ2l0YWwubXgvbG9naW4iO31zOjY6Il9mbGFzaCI7YToyOntzOjM6Im9sZCI7YTowOnt9czozOiJuZXciO2E6MDp7fX19',1777697777),('JrekMWBMt8v1aKSxe5tDr0gZa2eluGz5rcb4YDaY',NULL,'31.220.63.131','Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/44.0.2403.157 Safari/537.36','YTozOntzOjY6Il90b2tlbiI7czo0MDoidlJvV0VtSTd5ZnhRVVFWRlBQZ2FnWnZ4N1FUaVViVFo0MXVRdmZKQSI7czo5OiJfcHJldmlvdXMiO2E6MTp7czozOiJ1cmwiO3M6MzE6Imh0dHBzOi8vbWl1bWkubWltdW5kb2RpZ2l0YWwubXgiO31zOjY6Il9mbGFzaCI7YToyOntzOjM6Im9sZCI7YTowOnt9czozOiJuZXciO2E6MDp7fX19',1777647363),('JZA0EmqvRPy8WI12Wdp5ZFv9wB6pezbcHKZ126kU',NULL,'31.220.63.131','Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/44.0.2403.157 Safari/537.36','YTozOntzOjY6Il90b2tlbiI7czo0MDoiNm80MklSN0ZEQmFNR3BBZ1BPaU9mME9vYXg3TElEZXQ0N2ZWUXY2ciI7czo5OiJfcHJldmlvdXMiO2E6MTp7czozOiJ1cmwiO3M6MzE6Imh0dHBzOi8vbWl1bWkubWltdW5kb2RpZ2l0YWwubXgiO31zOjY6Il9mbGFzaCI7YToyOntzOjM6Im9sZCI7YTowOnt9czozOiJuZXciO2E6MDp7fX19',1777640410),('k7wEniDnQr23K2qhwPX1ErsbWvCaObYoOO7rGgmK',NULL,'31.220.63.131','Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/44.0.2403.157 Safari/537.36','YTozOntzOjY6Il90b2tlbiI7czo0MDoiejBBTUx2RDdjQjRZUFZ2eFR0andOSlN3Z0h5bk1nWkUxdTNDTExEeiI7czo5OiJfcHJldmlvdXMiO2E6MTp7czozOiJ1cmwiO3M6Mzc6Imh0dHBzOi8vbWl1bWkubWltdW5kb2RpZ2l0YWwubXgvbG9naW4iO31zOjY6Il9mbGFzaCI7YToyOntzOjM6Im9sZCI7YTowOnt9czozOiJuZXciO2E6MDp7fX19',1777663685),('kaG9ZOdW81Vgo0YJQIlq0FuU86MAvluqB535DXYe',NULL,'31.220.63.131','Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/44.0.2403.157 Safari/537.36','YTozOntzOjY6Il90b2tlbiI7czo0MDoiMGNWSmE2WFBnQmlNYmZCY3M0NDlBdXA4ZXI2bFluVHYzUmR2c25XSyI7czo5OiJfcHJldmlvdXMiO2E6MTp7czozOiJ1cmwiO3M6Mzc6Imh0dHBzOi8vbWl1bWkubWltdW5kb2RpZ2l0YWwubXgvbG9naW4iO31zOjY6Il9mbGFzaCI7YToyOntzOjM6Im9sZCI7YTowOnt9czozOiJuZXciO2E6MDp7fX19',1777677244),('kaWK9M5iHbibrJFIew7iSPVAMtlnBoIE2VkkfT5E',NULL,'31.220.63.131','Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/44.0.2403.157 Safari/537.36','YTozOntzOjY6Il90b2tlbiI7czo0MDoieEJLMmhVSmdvY0UzUXhNSldOMWJqVkRLVlFBWDBzcjIzMGZCQ3ZQaiI7czo5OiJfcHJldmlvdXMiO2E6MTp7czozOiJ1cmwiO3M6Mzc6Imh0dHBzOi8vbWl1bWkubWltdW5kb2RpZ2l0YWwubXgvbG9naW4iO31zOjY6Il9mbGFzaCI7YToyOntzOjM6Im9sZCI7YTowOnt9czozOiJuZXciO2E6MDp7fX19',1777714923),('KcsjqedLPL17qgx04nV9JHKqnCo8EGWJKHrmUBNl',NULL,'31.220.63.131','Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/44.0.2403.157 Safari/537.36','YTozOntzOjY6Il90b2tlbiI7czo0MDoiRmNWR3JlT3R6M282ellnN2pVV3VwckxjVFRUcjVzSDNENmRPa3pjRSI7czo5OiJfcHJldmlvdXMiO2E6MTp7czozOiJ1cmwiO3M6MzE6Imh0dHBzOi8vbWl1bWkubWltdW5kb2RpZ2l0YWwubXgiO31zOjY6Il9mbGFzaCI7YToyOntzOjM6Im9sZCI7YTowOnt9czozOiJuZXciO2E6MDp7fX19',1777665363),('KlhFVmmZYKmf6ZF2OOgnDdHlpbexvv7hBM5t3vBM',NULL,'31.220.63.131','Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/44.0.2403.157 Safari/537.36','YTozOntzOjY6Il90b2tlbiI7czo0MDoiZEpRTzBDck1BZU1DeGRpVm9tUHY5VzB4Rzd1dWdsMkRHRWZ6SkFLQyI7czo5OiJfcHJldmlvdXMiO2E6MTp7czozOiJ1cmwiO3M6Mzc6Imh0dHBzOi8vbWl1bWkubWltdW5kb2RpZ2l0YWwubXgvbG9naW4iO31zOjY6Il9mbGFzaCI7YToyOntzOjM6Im9sZCI7YTowOnt9czozOiJuZXciO2E6MDp7fX19',1777721283),('kQWvkhQyK1ZB84chUljsjp9plpKcncue1wLJ8ilO',NULL,'31.220.63.131','Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/44.0.2403.157 Safari/537.36','YTozOntzOjY6Il90b2tlbiI7czo0MDoiSWlpek9UTWl6T1p3RFZaY3Z3ZXp4RXZ0VTMyWTlVNEZNbUdSWFFLVyI7czo5OiJfcHJldmlvdXMiO2E6MTp7czozOiJ1cmwiO3M6Mzc6Imh0dHBzOi8vbWl1bWkubWltdW5kb2RpZ2l0YWwubXgvbG9naW4iO31zOjY6Il9mbGFzaCI7YToyOntzOjM6Im9sZCI7YTowOnt9czozOiJuZXciO2E6MDp7fX19',1777730410),('KV6jjLp76j5FuEw6ZN7PLgLlEQt81GSbKccWJkNv',NULL,'31.220.63.131','Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/44.0.2403.157 Safari/537.36','YTozOntzOjY6Il90b2tlbiI7czo0MDoiSmNVRDZoOVVDZEZhYUliTXg1Y0Z2c3dRZGhtT1FUYTFDSHhUN1NLbSI7czo5OiJfcHJldmlvdXMiO2E6MTp7czozOiJ1cmwiO3M6MzE6Imh0dHBzOi8vbWl1bWkubWltdW5kb2RpZ2l0YWwubXgiO31zOjY6Il9mbGFzaCI7YToyOntzOjM6Im9sZCI7YTowOnt9czozOiJuZXciO2E6MDp7fX19',1777658408),('KVDlPdieU6sMi6pLfi1854J4kEDKdlomNqOECHYd',NULL,'31.220.63.131','Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/44.0.2403.157 Safari/537.36','YTozOntzOjY6Il90b2tlbiI7czo0MDoiYUpDMUxPVHdLZGNUQ2JsMldVbFRqSHFTWHBsaFVpM2FWdXk4UFhTYyI7czo5OiJfcHJldmlvdXMiO2E6MTp7czozOiJ1cmwiO3M6Mzc6Imh0dHBzOi8vbWl1bWkubWltdW5kb2RpZ2l0YWwubXgvbG9naW4iO31zOjY6Il9mbGFzaCI7YToyOntzOjM6Im9sZCI7YTowOnt9czozOiJuZXciO2E6MDp7fX19',1777685287),('L0T9WEdC3RwrJLCPQ8vtA5Rl0s5LIjYlFybvF3Hd',NULL,'31.220.63.131','Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/44.0.2403.157 Safari/537.36','YTozOntzOjY6Il90b2tlbiI7czo0MDoia1FNaGRpTzVRalFhTGlQejJ5Q3I0ME1KUnZBcllDeEVRTWx5YXZZeCI7czo5OiJfcHJldmlvdXMiO2E6MTp7czozOiJ1cmwiO3M6MzE6Imh0dHBzOi8vbWl1bWkubWltdW5kb2RpZ2l0YWwubXgiO31zOjY6Il9mbGFzaCI7YToyOntzOjM6Im9sZCI7YTowOnt9czozOiJuZXciO2E6MDp7fX19',1777708808),('L4xq0NNYuAmmUGtBxTFIq1D6eHmgbjanDoBnrj9z',NULL,'31.220.63.131','Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/44.0.2403.157 Safari/537.36','YTozOntzOjY6Il90b2tlbiI7czo0MDoiVFFlc1JSUVV4dnFmWTk1ZlNTb2pCV1o0VlRvaGwwWnZhVzhYM3Q3aCI7czo5OiJfcHJldmlvdXMiO2E6MTp7czozOiJ1cmwiO3M6Mzc6Imh0dHBzOi8vbWl1bWkubWltdW5kb2RpZ2l0YWwubXgvbG9naW4iO31zOjY6Il9mbGFzaCI7YToyOntzOjM6Im9sZCI7YTowOnt9czozOiJuZXciO2E6MDp7fX19',1777695246),('L8NNWya1I11GzXTmXHEOq1EdnC49CrIIiGibU8q1',NULL,'31.220.63.131','Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/44.0.2403.157 Safari/537.36','YTozOntzOjY6Il90b2tlbiI7czo0MDoibzF3b015VW9TaTA5eHR6dHlWRlFRRzYxbFRXdnp1djNoY3JnbWU3NCI7czo5OiJfcHJldmlvdXMiO2E6MTp7czozOiJ1cmwiO3M6MzE6Imh0dHBzOi8vbWl1bWkubWltdW5kb2RpZ2l0YWwubXgiO31zOjY6Il9mbGFzaCI7YToyOntzOjM6Im9sZCI7YTowOnt9czozOiJuZXciO2E6MDp7fX19',1777716009),('Leu7RNx3x0qLQbaIPTXg0hLX5ir23sm8bf5buKvQ',NULL,'31.220.63.131','Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/44.0.2403.157 Safari/537.36','YTozOntzOjY6Il90b2tlbiI7czo0MDoiSmhmbFNvdEFGd1JBUjRScDlqRmRXUkVFQmVTNXoxTlcyaGcwd2M1QyI7czo5OiJfcHJldmlvdXMiO2E6MTp7czozOiJ1cmwiO3M6MzE6Imh0dHBzOi8vbWl1bWkubWltdW5kb2RpZ2l0YWwubXgiO31zOjY6Il9mbGFzaCI7YToyOntzOjM6Im9sZCI7YTowOnt9czozOiJuZXciO2E6MDp7fX19',1777678927),('LJavbEkY13wq9g3rVMBZO9LUs5tyDoPDGYVYJNMS',NULL,'31.220.63.131','Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/44.0.2403.157 Safari/537.36','YTozOntzOjY6Il90b2tlbiI7czo0MDoiMnl6VmRtQmhtR29GOHNzbERCR0l3UVFPZTUzcWJMVFo2em5CcWFPRyI7czo5OiJfcHJldmlvdXMiO2E6MTp7czozOiJ1cmwiO3M6MzE6Imh0dHBzOi8vbWl1bWkubWltdW5kb2RpZ2l0YWwubXgiO31zOjY6Il9mbGFzaCI7YToyOntzOjM6Im9sZCI7YTowOnt9czozOiJuZXciO2E6MDp7fX19',1777705212),('LKWd5vtN6pGwOgZg4Viz2nEIJTeiiraYVzvjrxgT',NULL,'31.220.63.131','Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/44.0.2403.157 Safari/537.36','YTozOntzOjY6Il90b2tlbiI7czo0MDoiOE81TXd6d1N3WnVFa0Qxbk82VDBlUTBBcHNOTWJacVBGMW9TU3VVZiI7czo5OiJfcHJldmlvdXMiO2E6MTp7czozOiJ1cmwiO3M6MzE6Imh0dHBzOi8vbWl1bWkubWltdW5kb2RpZ2l0YWwubXgiO31zOjY6Il9mbGFzaCI7YToyOntzOjM6Im9sZCI7YTowOnt9czozOiJuZXciO2E6MDp7fX19',1777728485),('LO49zaXzEILk4KIl4Bcc9kSZuLp9KxxFk89sY6f7',NULL,'31.220.63.131','Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/44.0.2403.157 Safari/537.36','YTozOntzOjY6Il90b2tlbiI7czo0MDoiY3A0QUpkd3RiVGVBWk9FY2k2OEpFZmptMFhEbktnVTZBU0tkdEZ2UiI7czo5OiJfcHJldmlvdXMiO2E6MTp7czozOiJ1cmwiO3M6MzE6Imh0dHBzOi8vbWl1bWkubWltdW5kb2RpZ2l0YWwubXgiO31zOjY6Il9mbGFzaCI7YToyOntzOjM6Im9sZCI7YTowOnt9czozOiJuZXciO2E6MDp7fX19',1777665610),('LOg6EwELdXqH1WvEQ16xicZxBfKj2Z0fr4QqiKll',NULL,'31.220.63.131','Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/44.0.2403.157 Safari/537.36','YTozOntzOjY6Il90b2tlbiI7czo0MDoiVE1sZklVdW5sZmJUdkV2VmtMUDB0WlBQbzFCTzJlVUg5SURPcmtTMCI7czo5OiJfcHJldmlvdXMiO2E6MTp7czozOiJ1cmwiO3M6Mzc6Imh0dHBzOi8vbWl1bWkubWltdW5kb2RpZ2l0YWwubXgvbG9naW4iO31zOjY6Il9mbGFzaCI7YToyOntzOjM6Im9sZCI7YTowOnt9czozOiJuZXciO2E6MDp7fX19',1777680031),('lSdPgEa0xT8Cwx124doqBNX5s7RNm6hun0XB11Jp',NULL,'31.220.63.131','Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/44.0.2403.157 Safari/537.36','YTozOntzOjY6Il90b2tlbiI7czo0MDoiWEFOTmZOdm5iZVBwVThpMlFKMDhnRWE4S0s1S0NjOXFiMFN5VUFmVyI7czo5OiJfcHJldmlvdXMiO2E6MTp7czozOiJ1cmwiO3M6MzE6Imh0dHBzOi8vbWl1bWkubWltdW5kb2RpZ2l0YWwubXgiO31zOjY6Il9mbGFzaCI7YToyOntzOjM6Im9sZCI7YTowOnt9czozOiJuZXciO2E6MDp7fX19',1777726563),('LvgYySurLjU4YsXGweq264bJcGv0c4NSUUowh2jX',NULL,'31.220.63.131','Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/44.0.2403.157 Safari/537.36','YTozOntzOjY6Il90b2tlbiI7czo0MDoiaUozMHNuRnFwSVJkdHFVNTJac1VVRUVSTkVzeEh1YUtieVdOaXNQYyI7czo5OiJfcHJldmlvdXMiO2E6MTp7czozOiJ1cmwiO3M6MzE6Imh0dHBzOi8vbWl1bWkubWltdW5kb2RpZ2l0YWwubXgiO31zOjY6Il9mbGFzaCI7YToyOntzOjM6Im9sZCI7YTowOnt9czozOiJuZXciO2E6MDp7fX19',1777727645),('LWpiWZ2BZejV5uNHnOTWKHJ7iQisFiUnMXY4PmiN',NULL,'31.220.63.131','Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/44.0.2403.157 Safari/537.36','YTozOntzOjY6Il90b2tlbiI7czo0MDoiWFhORk4zNnBBVlhJTWVMa2k4V0pjSHRvU3ZCellWTGlyeklHZnZSaCI7czo5OiJfcHJldmlvdXMiO2E6MTp7czozOiJ1cmwiO3M6Mzc6Imh0dHBzOi8vbWl1bWkubWltdW5kb2RpZ2l0YWwubXgvbG9naW4iO31zOjY6Il9mbGFzaCI7YToyOntzOjM6Im9sZCI7YTowOnt9czozOiJuZXciO2E6MDp7fX19',1777653725),('LyWM6ja17qWV9YqGZjm4xuXCWsIqT8KOm3GzNaNe',NULL,'31.220.63.131','Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/44.0.2403.157 Safari/537.36','YTozOntzOjY6Il90b2tlbiI7czo0MDoieEVoYTNEYmlvUVRFYUpxOWZvOWVVbjBPSmVzbnBqVEFMdTVFWHZieCI7czo5OiJfcHJldmlvdXMiO2E6MTp7czozOiJ1cmwiO3M6Mzc6Imh0dHBzOi8vbWl1bWkubWltdW5kb2RpZ2l0YWwubXgvbG9naW4iO31zOjY6Il9mbGFzaCI7YToyOntzOjM6Im9sZCI7YTowOnt9czozOiJuZXciO2E6MDp7fX19',1777708809),('m2crXMjLF944UaW1pRIKpUevEr3X0nN0Arg4uGTL',NULL,'31.220.63.131','Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/44.0.2403.157 Safari/537.36','YTozOntzOjY6Il90b2tlbiI7czo0MDoickloblRhQ1BaZ0VyRWl4ZVE3NVNDNFhVbWNBRERYZTdocUJjZjRIbSI7czo5OiJfcHJldmlvdXMiO2E6MTp7czozOiJ1cmwiO3M6MzE6Imh0dHBzOi8vbWl1bWkubWltdW5kb2RpZ2l0YWwubXgiO31zOjY6Il9mbGFzaCI7YToyOntzOjM6Im9sZCI7YTowOnt9czozOiJuZXciO2E6MDp7fX19',1777688890),('m5WQckt7hEmRc5PpMYWZjmkqk1WCySebQW8rzvr2',NULL,'31.220.63.131','Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/44.0.2403.157 Safari/537.36','YTozOntzOjY6Il90b2tlbiI7czo0MDoiTE1qUmNSZlRpZmVBMHlDblcwSGNWUmNpZzNmbHVsMHZMSFYzRm91dCI7czo5OiJfcHJldmlvdXMiO2E6MTp7czozOiJ1cmwiO3M6Mzc6Imh0dHBzOi8vbWl1bWkubWltdW5kb2RpZ2l0YWwubXgvbG9naW4iO31zOjY6Il9mbGFzaCI7YToyOntzOjM6Im9sZCI7YTowOnt9czozOiJuZXciO2E6MDp7fX19',1777707723),('M9qSQwvqromH3pPfBQAzOcGkaT50pGKvhkoctetD',NULL,'31.220.63.131','Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/44.0.2403.157 Safari/537.36','YTozOntzOjY6Il90b2tlbiI7czo0MDoiME4yanh6V0hMbENMRnI2aVpselF6UXpMTzQzbjd1SU1uSXkydGU0dSI7czo5OiJfcHJldmlvdXMiO2E6MTp7czozOiJ1cmwiO3M6Mzc6Imh0dHBzOi8vbWl1bWkubWltdW5kb2RpZ2l0YWwubXgvbG9naW4iO31zOjY6Il9mbGFzaCI7YToyOntzOjM6Im9sZCI7YTowOnt9czozOiJuZXciO2E6MDp7fX19',1777660923),('MbArsN3uXOheGKu8YUaqEbAiHtiKImtAYGbAdGrg',NULL,'31.220.63.131','Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/44.0.2403.157 Safari/537.36','YTozOntzOjY6Il90b2tlbiI7czo0MDoiRG1TdWdOQ2I4ZEJTTW9HRXZCZGc2eHNrVnZLalYyYmhHQ1gwcjVjYiI7czo5OiJfcHJldmlvdXMiO2E6MTp7czozOiJ1cmwiO3M6MzE6Imh0dHBzOi8vbWl1bWkubWltdW5kb2RpZ2l0YWwubXgiO31zOjY6Il9mbGFzaCI7YToyOntzOjM6Im9sZCI7YTowOnt9czozOiJuZXciO2E6MDp7fX19',1777729326),('mcl9mlJxizEqULJdGzos26njvuK4KLJJ5wB0153b',NULL,'31.220.63.131','Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/44.0.2403.157 Safari/537.36','YTozOntzOjY6Il90b2tlbiI7czo0MDoiZmE5cmhRZ1YyMEl1TE9zMnFrQXlEd3g3OTUyVkpMbmhvM2hlVkw1eSI7czo5OiJfcHJldmlvdXMiO2E6MTp7czozOiJ1cmwiO3M6Mzc6Imh0dHBzOi8vbWl1bWkubWltdW5kb2RpZ2l0YWwubXgvbG9naW4iO31zOjY6Il9mbGFzaCI7YToyOntzOjM6Im9sZCI7YTowOnt9czozOiJuZXciO2E6MDp7fX19',1777704125),('MfHvexvgpKNmsEtjT4Y3J5QJURXMFkqQGuqSFLrl',NULL,'31.220.63.131','Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/44.0.2403.157 Safari/537.36','YTozOntzOjY6Il90b2tlbiI7czo0MDoiMUZGMkJNUzJiNFFvaHJtRlJWUFRTZjg5ekExUVlpQktGcnRpV2ZjQyI7czo5OiJfcHJldmlvdXMiO2E6MTp7czozOiJ1cmwiO3M6Mzc6Imh0dHBzOi8vbWl1bWkubWltdW5kb2RpZ2l0YWwubXgvbG9naW4iO31zOjY6Il9mbGFzaCI7YToyOntzOjM6Im9sZCI7YTowOnt9czozOiJuZXciO2E6MDp7fX19',1777722123),('mICZ5W9QqWG4BHs0kcehRxtpQ3PxIUlxzpbblHnz',NULL,'31.220.63.131','Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/44.0.2403.157 Safari/537.36','YTozOntzOjY6Il90b2tlbiI7czo0MDoiYWhVbkluRWVHN1ZQZ3Q4SFB6ZXduWThweDFzNWVGZEFCVEZvSXZXbiI7czo5OiJfcHJldmlvdXMiO2E6MTp7czozOiJ1cmwiO3M6MzE6Imh0dHBzOi8vbWl1bWkubWltdW5kb2RpZ2l0YWwubXgiO31zOjY6Il9mbGFzaCI7YToyOntzOjM6Im9sZCI7YTowOnt9czozOiJuZXciO2E6MDp7fX19',1777716844),('MomAdHJQcUfJ1SPrinjcYYbgkYj45ysUINpXi4jI',NULL,'31.220.63.131','Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/44.0.2403.157 Safari/537.36','YTozOntzOjY6Il90b2tlbiI7czo0MDoiTkRLRmt4UlQwRkdnZTdxUXZUZWozTDNrVTBjb2xsY0hEclJhUGtJNSI7czo5OiJfcHJldmlvdXMiO2E6MTp7czozOiJ1cmwiO3M6MzE6Imh0dHBzOi8vbWl1bWkubWltdW5kb2RpZ2l0YWwubXgiO31zOjY6Il9mbGFzaCI7YToyOntzOjM6Im9sZCI7YTowOnt9czozOiJuZXciO2E6MDp7fX19',1777680031),('MQl5WL7tVVhvnGk1dbnrY9eOZJaY0FOwilaxejuE',NULL,'31.220.63.131','Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/44.0.2403.157 Safari/537.36','YTozOntzOjY6Il90b2tlbiI7czo0MDoiazJ5UVBYU3BDMkp2a2RIajBNYlFvbHpUUnJyZ0x6QWQycFhlSHdtVyI7czo5OiJfcHJldmlvdXMiO2E6MTp7czozOiJ1cmwiO3M6MzE6Imh0dHBzOi8vbWl1bWkubWltdW5kb2RpZ2l0YWwubXgiO31zOjY6Il9mbGFzaCI7YToyOntzOjM6Im9sZCI7YTowOnt9czozOiJuZXciO2E6MDp7fX19',1777641243),('n2mcdDzeDhDaCPvtRilbPP36rZXiVpSG1tswxSBR',NULL,'31.220.63.131','Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/44.0.2403.157 Safari/537.36','YTozOntzOjY6Il90b2tlbiI7czo0MDoiQlZwTlN2UHZrR0E0UVFrYXo4RFlqSlhIampUdFdGWkU4R2RNRGpIZyI7czo5OiJfcHJldmlvdXMiO2E6MTp7czozOiJ1cmwiO3M6Mzc6Imh0dHBzOi8vbWl1bWkubWltdW5kb2RpZ2l0YWwubXgvbG9naW4iO31zOjY6Il9mbGFzaCI7YToyOntzOjM6Im9sZCI7YTowOnt9czozOiJuZXciO2E6MDp7fX19',1777716009),('N2uU5ZnqhskQdOp3y1iUQJzugNLM8bQNC2dTEMZ5',NULL,'31.220.63.131','Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/44.0.2403.157 Safari/537.36','YTozOntzOjY6Il90b2tlbiI7czo0MDoia1k0SnFldmtyblBPNU9QaWlFSlh2MW10bXJaQjFPQmRRcWk2MWE2WSI7czo5OiJfcHJldmlvdXMiO2E6MTp7czozOiJ1cmwiO3M6MzE6Imh0dHBzOi8vbWl1bWkubWltdW5kb2RpZ2l0YWwubXgiO31zOjY6Il9mbGFzaCI7YToyOntzOjM6Im9sZCI7YTowOnt9czozOiJuZXciO2E6MDp7fX19',1777663685),('N4G5d46upXdYLBUitSjq1VSQjuQinr4bQrwFDsXS',NULL,'31.220.63.131','Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/44.0.2403.157 Safari/537.36','YTozOntzOjY6Il90b2tlbiI7czo0MDoienZWV2g5YkYwUldURlk3TkIxUTFlRHFwU3ZxSlZYaHd1RnZMbkdoeSI7czo5OiJfcHJldmlvdXMiO2E6MTp7czozOiJ1cmwiO3M6MzE6Imh0dHBzOi8vbWl1bWkubWltdW5kb2RpZ2l0YWwubXgiO31zOjY6Il9mbGFzaCI7YToyOntzOjM6Im9sZCI7YTowOnt9czozOiJuZXciO2E6MDp7fX19',1777714923),('nA14vS86QdSnYim4dlSK0AHGHPK7a6EWWh2Rb9G7',NULL,'31.220.63.131','Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/44.0.2403.157 Safari/537.36','YTozOntzOjY6Il90b2tlbiI7czo0MDoiOEZ3djA2TU9EWmcwNTBUZUJyS1pjekFGZ2lUcU94SWpuaUlseGZLbyI7czo5OiJfcHJldmlvdXMiO2E6MTp7czozOiJ1cmwiO3M6MzE6Imh0dHBzOi8vbWl1bWkubWltdW5kb2RpZ2l0YWwubXgiO31zOjY6Il9mbGFzaCI7YToyOntzOjM6Im9sZCI7YTowOnt9czozOiJuZXciO2E6MDp7fX19',1777704125),('NAyBEOR7XvJKdhbg6u9FmBEEbr55Jd2sUqkozbFy',NULL,'31.220.63.131','Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/44.0.2403.157 Safari/537.36','YTozOntzOjY6Il90b2tlbiI7czo0MDoiUjllZVlGb1NvNEFZRmNaQUNncE1FZTVzejBmY0lMTWNqWmZJcXRQaSI7czo5OiJfcHJldmlvdXMiO2E6MTp7czozOiJ1cmwiO3M6Mzc6Imh0dHBzOi8vbWl1bWkubWltdW5kb2RpZ2l0YWwubXgvbG9naW4iO31zOjY6Il9mbGFzaCI7YToyOntzOjM6Im9sZCI7YTowOnt9czozOiJuZXciO2E6MDp7fX19',1777732927),('nLLOXvfiGBELeTkwCjLStUX5jC175XcSIrTq48UL',NULL,'31.220.63.131','Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/44.0.2403.157 Safari/537.36','YTozOntzOjY6Il90b2tlbiI7czo0MDoiQ0NjZUI0M3dNT0FSM2d6alNkcUVzUGJCbTNQcFlSb1FRWFdYdkJXMiI7czo5OiJfcHJldmlvdXMiO2E6MTp7czozOiJ1cmwiO3M6MzE6Imh0dHBzOi8vbWl1bWkubWltdW5kb2RpZ2l0YWwubXgiO31zOjY6Il9mbGFzaCI7YToyOntzOjM6Im9sZCI7YTowOnt9czozOiJuZXciO2E6MDp7fX19',1777652883),('NNYlfH1aQPnDjsiT9GZO1vCKdX3G1KaHlWVIllbV',NULL,'31.220.63.131','Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/44.0.2403.157 Safari/537.36','YTozOntzOjY6Il90b2tlbiI7czo0MDoiNWZteHVzWHdmVnZoak9TeHBRVVBtV016dExtZVJQbldMdndRQU10OCI7czo5OiJfcHJldmlvdXMiO2E6MTp7czozOiJ1cmwiO3M6MzE6Imh0dHBzOi8vbWl1bWkubWltdW5kb2RpZ2l0YWwubXgiO31zOjY6Il9mbGFzaCI7YToyOntzOjM6Im9sZCI7YTowOnt9czozOiJuZXciO2E6MDp7fX19',1777653725),('NXfXwFWUlnoj6R2CSR7C9iF0Kr0V2IuhnZcCnEMK',NULL,'31.220.63.131','Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/44.0.2403.157 Safari/537.36','YTozOntzOjY6Il90b2tlbiI7czo0MDoicE04RmszNHNKT1FjV3hlWGFjZUQ0V1RFdmFpWVFMOXdkS2JGYkg1cSI7czo5OiJfcHJldmlvdXMiO2E6MTp7czozOiJ1cmwiO3M6MzE6Imh0dHBzOi8vbWl1bWkubWltdW5kb2RpZ2l0YWwubXgiO31zOjY6Il9mbGFzaCI7YToyOntzOjM6Im9sZCI7YTowOnt9czozOiJuZXciO2E6MDp7fX19',1777671726),('Nxo6aC93SPHOHIKSLlpODGXKHSmMTtznACgImxv6',NULL,'31.220.63.131','Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/44.0.2403.157 Safari/537.36','YTozOntzOjY6Il90b2tlbiI7czo0MDoidG0xZjRhcGhLeERIbnBVNVhMdXVmU25HekE5a1NLSVliTlZBeXFVRCI7czo5OiJfcHJldmlvdXMiO2E6MTp7czozOiJ1cmwiO3M6Mzc6Imh0dHBzOi8vbWl1bWkubWltdW5kb2RpZ2l0YWwubXgvbG9naW4iO31zOjY6Il9mbGFzaCI7YToyOntzOjM6Im9sZCI7YTowOnt9czozOiJuZXciO2E6MDp7fX19',1777660085),('nXu2X2zTSG5W0TswqiNcR5nUZVKKvgeUU2HVc9X3',NULL,'31.220.63.131','Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/44.0.2403.157 Safari/537.36','YTozOntzOjY6Il90b2tlbiI7czo0MDoiQzNCVHFMSnVxTVJYV0V0bWp4ZXM4YktzeERRcExnOVhFVnVyTVVsRSI7czo5OiJfcHJldmlvdXMiO2E6MTp7czozOiJ1cmwiO3M6Mzc6Imh0dHBzOi8vbWl1bWkubWltdW5kb2RpZ2l0YWwubXgvbG9naW4iO31zOjY6Il9mbGFzaCI7YToyOntzOjM6Im9sZCI7YTowOnt9czozOiJuZXciO2E6MDp7fX19',1777716844),('o7f1BZFI63Fmqkb0uQ1mW8kmZK48X7VbrzrMEBN6',NULL,'31.220.63.131','Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/44.0.2403.157 Safari/537.36','YTozOntzOjY6Il90b2tlbiI7czo0MDoiZTdyT2t6U1dmMW1CZ0pUU3U0b0wwZDhhWDEybTRnRFZLbDhkcDB6UCI7czo5OiJfcHJldmlvdXMiO2E6MTp7czozOiJ1cmwiO3M6Mzc6Imh0dHBzOi8vbWl1bWkubWltdW5kb2RpZ2l0YWwubXgvbG9naW4iO31zOjY6Il9mbGFzaCI7YToyOntzOjM6Im9sZCI7YTowOnt9czozOiJuZXciO2E6MDp7fX19',1777645684),('OOlmqCPDIsK5lAbdYk6GGsqvJ9uJxes9gLKTXYOS',NULL,'31.220.63.131','Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/44.0.2403.157 Safari/537.36','YTozOntzOjY6Il90b2tlbiI7czo0MDoid3JwVTZDR01Qb3pRQXFJdzJMOE92Y1lkVWtIRHpMZzQyb2xhUVlRcCI7czo5OiJfcHJldmlvdXMiO2E6MTp7czozOiJ1cmwiO3M6Mzc6Imh0dHBzOi8vbWl1bWkubWltdW5kb2RpZ2l0YWwubXgvbG9naW4iO31zOjY6Il9mbGFzaCI7YToyOntzOjM6Im9sZCI7YTowOnt9czozOiJuZXciO2E6MDp7fX19',1777691651),('oPohKjUgxC5pzFt03KkynD32c5S8ch7HjIJIOkQI',NULL,'31.220.63.131','Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/44.0.2403.157 Safari/537.36','YTozOntzOjY6Il90b2tlbiI7czo0MDoiUU03T1F0TlNWZDJ2NFIxR0VLSWgzaTZkQ1VwMkI4TEpqeUozNFVWSSI7czo5OiJfcHJldmlvdXMiO2E6MTp7czozOiJ1cmwiO3M6Mzc6Imh0dHBzOi8vbWl1bWkubWltdW5kb2RpZ2l0YWwubXgvbG9naW4iO31zOjY6Il9mbGFzaCI7YToyOntzOjM6Im9sZCI7YTowOnt9czozOiJuZXciO2E6MDp7fX19',1777720444),('OrwuIfAmA109mkau2faC6BNBvps9W4kkBmLb4JJT',NULL,'31.220.63.131','Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/44.0.2403.157 Safari/537.36','YTozOntzOjY6Il90b2tlbiI7czo0MDoiSERjTE1iWTIxSGF3clBlVzhtN1llMG5NdUU3bUgwOXBvNEZ4d1VNTyI7czo5OiJfcHJldmlvdXMiO2E6MTp7czozOiJ1cmwiO3M6Mzc6Imh0dHBzOi8vbWl1bWkubWltdW5kb2RpZ2l0YWwubXgvbG9naW4iO31zOjY6Il9mbGFzaCI7YToyOntzOjM6Im9sZCI7YTowOnt9czozOiJuZXciO2E6MDp7fX19',1777722964),('oUb0cvnUUGT97Ik85VZlF4UvRcBHcxqFKyHlaYDb',NULL,'31.220.63.131','Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/44.0.2403.157 Safari/537.36','YTozOntzOjY6Il90b2tlbiI7czo0MDoiVjRxT05qNU43czlKWjM1bVFKb0FuZ3FPUVp4UnQ1eXBqNEdtbmsxMSI7czo5OiJfcHJldmlvdXMiO2E6MTp7czozOiJ1cmwiO3M6MzE6Imh0dHBzOi8vbWl1bWkubWltdW5kb2RpZ2l0YWwubXgiO31zOjY6Il9mbGFzaCI7YToyOntzOjM6Im9sZCI7YTowOnt9czozOiJuZXciO2E6MDp7fX19',1777657323),('OUiZexEAnokHKHT5wdm5ymeH2PRFJdJxVznQFqv7',NULL,'31.220.63.131','Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/44.0.2403.157 Safari/537.36','YTozOntzOjY6Il90b2tlbiI7czo0MDoibEg1ZVlRb1RLY1cwUW9nbmhBeHNTd2d0dW5abXRSckhGNExvdHlESyI7czo5OiJfcHJldmlvdXMiO2E6MTp7czozOiJ1cmwiO3M6MzE6Imh0dHBzOi8vbWl1bWkubWltdW5kb2RpZ2l0YWwubXgiO31zOjY6Il9mbGFzaCI7YToyOntzOjM6Im9sZCI7YTowOnt9czozOiJuZXciO2E6MDp7fX19',1777736531),('Ov5ADUB3S45xvQwZ8uyKKGAwJ5iMvFNZcPy9jpZX',NULL,'31.220.63.131','Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/44.0.2403.157 Safari/537.36','YTozOntzOjY6Il90b2tlbiI7czo0MDoidVBKSHZlbW5qcGJsSklMZkE5UFFKbzd1eGJiMWV1allYRnRFOUpTTiI7czo5OiJfcHJldmlvdXMiO2E6MTp7czozOiJ1cmwiO3M6Mzc6Imh0dHBzOi8vbWl1bWkubWltdW5kb2RpZ2l0YWwubXgvbG9naW4iO31zOjY6Il9mbGFzaCI7YToyOntzOjM6Im9sZCI7YTowOnt9czozOiJuZXciO2E6MDp7fX19',1777734013),('P4a7TONzKbjm9J3RLhhpxliSWMmmw8topIMH5ZQK',NULL,'31.220.63.131','Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/44.0.2403.157 Safari/537.36','YTozOntzOjY6Il90b2tlbiI7czo0MDoiZGpJelRRZE9KV0p6SlZLd01sTU45VWxZVmdubzljRHgwYU02b3JONCI7czo5OiJfcHJldmlvdXMiO2E6MTp7czozOiJ1cmwiO3M6Mzc6Imh0dHBzOi8vbWl1bWkubWltdW5kb2RpZ2l0YWwubXgvbG9naW4iO31zOjY6Il9mbGFzaCI7YToyOntzOjM6Im9sZCI7YTowOnt9czozOiJuZXciO2E6MDp7fX19',1777710483),('PbPV6zhiIwyuuT03qhzmalWbbwvawApyTUA8OuMt',NULL,'31.220.63.131','Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/44.0.2403.157 Safari/537.36','YTozOntzOjY6Il90b2tlbiI7czo0MDoiNWlTY0pCVnJPeE5FOVU1WGtEem5XaDNOY1JHSmhiWXVDdG8zQVh6ayI7czo5OiJfcHJldmlvdXMiO2E6MTp7czozOiJ1cmwiO3M6Mzc6Imh0dHBzOi8vbWl1bWkubWltdW5kb2RpZ2l0YWwubXgvbG9naW4iO31zOjY6Il9mbGFzaCI7YToyOntzOjM6Im9sZCI7YTowOnt9czozOiJuZXciO2E6MDp7fX19',1777668966),('pclFjt03Llrga7cl6zzS4SAZXC2wI1SRZq1ITFJg',NULL,'31.220.63.131','Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/44.0.2403.157 Safari/537.36','YTozOntzOjY6Il90b2tlbiI7czo0MDoicWV2NnI4ZWJ4Y21oNXdPU01heWUzRHdPaDFlQk82NEdnM1VaMVBtbyI7czo5OiJfcHJldmlvdXMiO2E6MTp7czozOiJ1cmwiO3M6Mzc6Imh0dHBzOi8vbWl1bWkubWltdW5kb2RpZ2l0YWwubXgvbG9naW4iO31zOjY6Il9mbGFzaCI7YToyOntzOjM6Im9sZCI7YTowOnt9czozOiJuZXciO2E6MDp7fX19',1777683365),('pKIeRN4ETpXGyF1g7lx3BIFGlODVdeWpa8CGZRYo',NULL,'31.220.63.131','Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/44.0.2403.157 Safari/537.36','YTozOntzOjY6Il90b2tlbiI7czo0MDoiTWtGbjFFd1g3aldlZkxoM0I4TmlFWXhMSUdFdDdCekE0QWo5UlFSRSI7czo5OiJfcHJldmlvdXMiO2E6MTp7czozOiJ1cmwiO3M6MzE6Imh0dHBzOi8vbWl1bWkubWltdW5kb2RpZ2l0YWwubXgiO31zOjY6Il9mbGFzaCI7YToyOntzOjM6Im9sZCI7YTowOnt9czozOiJuZXciO2E6MDp7fX19',1777701364),('PKY2LXuOLmxFLl5XykuwbtfLbomF7aSYofY4zqBd',NULL,'31.220.63.131','Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/44.0.2403.157 Safari/537.36','YTozOntzOjY6Il90b2tlbiI7czo0MDoiOE9hb2NHaUlneHBBNjRHSFQ4RXJncmlXSVFJUTZ3SFdpUVJLN2pEZCI7czo5OiJfcHJldmlvdXMiO2E6MTp7czozOiJ1cmwiO3M6Mzc6Imh0dHBzOi8vbWl1bWkubWltdW5kb2RpZ2l0YWwubXgvbG9naW4iO31zOjY6Il9mbGFzaCI7YToyOntzOjM6Im9sZCI7YTowOnt9czozOiJuZXciO2E6MDp7fX19',1777640163),('PmRxZgVr8LBWpB7LiY9SNyGxk4uLag437OYIj9C1',NULL,'31.220.63.131','Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/44.0.2403.157 Safari/537.36','YTozOntzOjY6Il90b2tlbiI7czo0MDoicGFLRXZNaHlkbU1HaVFmaW42MmkyaWRxNGpNQ0FDVGpwZ21EY1NFYyI7czo5OiJfcHJldmlvdXMiO2E6MTp7czozOiJ1cmwiO3M6Mzc6Imh0dHBzOi8vbWl1bWkubWltdW5kb2RpZ2l0YWwubXgvbG9naW4iO31zOjY6Il9mbGFzaCI7YToyOntzOjM6Im9sZCI7YTowOnt9czozOiJuZXciO2E6MDp7fX19',1777686968),('PyFrxXyUBa9KVd49joW2NORLxowT4oToMstpLltf',NULL,'31.220.63.131','Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/44.0.2403.157 Safari/537.36','YTozOntzOjY6Il90b2tlbiI7czo0MDoiYjAzeGtqeHFDOXBwNjd2ZDNRZGNWQXJNamhnNkZiTjFmWUlqUDFlNCI7czo5OiJfcHJldmlvdXMiO2E6MTp7czozOiJ1cmwiO3M6Mzc6Imh0dHBzOi8vbWl1bWkubWltdW5kb2RpZ2l0YWwubXgvbG9naW4iO31zOjY6Il9mbGFzaCI7YToyOntzOjM6Im9sZCI7YTowOnt9czozOiJuZXciO2E6MDp7fX19',1777678084),('Q4IO0sXovo7PBnTksc0aNBjV3gfTqL51UDelSejl',NULL,'31.220.63.131','Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/44.0.2403.157 Safari/537.36','YTozOntzOjY6Il90b2tlbiI7czo0MDoiNmUyMXVyODhCbEFKeHV5WnczNWk2TnZ5TEZDQldZc21uNm5GV3dQeCI7czo5OiJfcHJldmlvdXMiO2E6MTp7czozOiJ1cmwiO3M6MzE6Imh0dHBzOi8vbWl1bWkubWltdW5kb2RpZ2l0YWwubXgiO31zOjY6Il9mbGFzaCI7YToyOntzOjM6Im9sZCI7YTowOnt9czozOiJuZXciO2E6MDp7fX19',1777725726),('Q6vqPWZAA3Nhkh0nzbtJZFRDDZiTpTvW7XWF0kBi',NULL,'31.220.63.131','Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/44.0.2403.157 Safari/537.36','YTozOntzOjY6Il90b2tlbiI7czo0MDoiTUs1c2l4WTJSbWYyZzIzSW91dTNRd2pLdHpWT3FvbFJRbEYzOGx5cSI7czo5OiJfcHJldmlvdXMiO2E6MTp7czozOiJ1cmwiO3M6MzE6Imh0dHBzOi8vbWl1bWkubWltdW5kb2RpZ2l0YWwubXgiO31zOjY6Il9mbGFzaCI7YToyOntzOjM6Im9sZCI7YTowOnt9czozOiJuZXciO2E6MDp7fX19',1777723208),('QfRxzBukK16PLwCbqQdVWqU8xvkQlLQ13ArMGSd5',NULL,'31.220.63.131','Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/44.0.2403.157 Safari/537.36','YTozOntzOjY6Il90b2tlbiI7czo0MDoiSzdIQUVzSG95M3pvZVlWR04yTHRoUkxSZ2FuV1pxQzFmTlhQU3Z3cyI7czo5OiJfcHJldmlvdXMiO2E6MTp7czozOiJ1cmwiO3M6MzE6Imh0dHBzOi8vbWl1bWkubWltdW5kb2RpZ2l0YWwubXgiO31zOjY6Il9mbGFzaCI7YToyOntzOjM6Im9sZCI7YTowOnt9czozOiJuZXciO2E6MDp7fX19',1777715764),('qia3fp3Ip1cSE7QAQ2cco8Esj2k3UYK3d03wBMQO',NULL,'31.220.63.131','Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/44.0.2403.157 Safari/537.36','YTozOntzOjY6Il90b2tlbiI7czo0MDoiamJNbW04Vm5jd24xWlNLZWREeFpTYURUYnp0Z2NleDdqdnF3TlFaSCI7czo5OiJfcHJldmlvdXMiO2E6MTp7czozOiJ1cmwiO3M6MzE6Imh0dHBzOi8vbWl1bWkubWltdW5kb2RpZ2l0YWwubXgiO31zOjY6Il9mbGFzaCI7YToyOntzOjM6Im9sZCI7YTowOnt9czozOiJuZXciO2E6MDp7fX19',1777719365),('QkhQ3F6Set8x21a82KFa2x9R6e4UQdjaBquuKTTL',NULL,'31.220.63.131','Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/44.0.2403.157 Safari/537.36','YTozOntzOjY6Il90b2tlbiI7czo0MDoiN0lvMlRqS05HcVJ4dkhZM1RsSTFRNHdVMXZLcVhrMHNtNXBzVldvaiI7czo5OiJfcHJldmlvdXMiO2E6MTp7czozOiJ1cmwiO3M6MzE6Imh0dHBzOi8vbWl1bWkubWltdW5kb2RpZ2l0YWwubXgiO31zOjY6Il9mbGFzaCI7YToyOntzOjM6Im9sZCI7YTowOnt9czozOiJuZXciO2E6MDp7fX19',1777648443),('QkxrIRYMhsA5u1G6k2EaOB41xbWYZSDsE4YoCnye',NULL,'31.220.63.131','Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/44.0.2403.157 Safari/537.36','YTozOntzOjY6Il90b2tlbiI7czo0MDoiRVZvWFRXSUtUN0p2R3FmZHVBMTNhdmRsMmQyZjdqbUdpYUlIVkVNZCI7czo5OiJfcHJldmlvdXMiO2E6MTp7czozOiJ1cmwiO3M6Mzc6Imh0dHBzOi8vbWl1bWkubWltdW5kb2RpZ2l0YWwubXgvbG9naW4iO31zOjY6Il9mbGFzaCI7YToyOntzOjM6Im9sZCI7YTowOnt9czozOiJuZXciO2E6MDp7fX19',1777719606),('qNi3h96fvYuwY0lb5x105nBAD5RD25ZXuPzRxG6C',NULL,'31.220.63.131','Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/44.0.2403.157 Safari/537.36','YTozOntzOjY6Il90b2tlbiI7czo0MDoicWFZU1dHRThMak1ncTRHalBsREU0WXh4WUpLUTdqYnpINFNLQXZhVyI7czo5OiJfcHJldmlvdXMiO2E6MTp7czozOiJ1cmwiO3M6MzE6Imh0dHBzOi8vbWl1bWkubWltdW5kb2RpZ2l0YWwubXgiO31zOjY6Il9mbGFzaCI7YToyOntzOjM6Im9sZCI7YTowOnt9czozOiJuZXciO2E6MDp7fX19',1777669214),('qonhSLrWXihiELgOzHRKvL0NJu3E9v0eLCVa16l9',NULL,'31.220.63.131','Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/44.0.2403.157 Safari/537.36','YTozOntzOjY6Il90b2tlbiI7czo0MDoiZ0JKT1JlRXJJeGhXVmlSc09IMTZZWldsWmJKeFdEZzdpeUpPOE40eiI7czo5OiJfcHJldmlvdXMiO2E6MTp7czozOiJ1cmwiO3M6Mzc6Imh0dHBzOi8vbWl1bWkubWltdW5kb2RpZ2l0YWwubXgvbG9naW4iO31zOjY6Il9mbGFzaCI7YToyOntzOjM6Im9sZCI7YTowOnt9czozOiJuZXciO2E6MDp7fX19',1777644007),('qpT54ZGkPKvwq0c5eg9QSfKDTnvaKlkEn0j7b8Uj',NULL,'31.220.63.131','Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/44.0.2403.157 Safari/537.36','YTozOntzOjY6Il90b2tlbiI7czo0MDoiV1BlRDZlMGhGcXJiakJaVzhzZVZvSFFxWG56RU5vaG5od0ZBekxXUyI7czo5OiJfcHJldmlvdXMiO2E6MTp7czozOiJ1cmwiO3M6MzE6Imh0dHBzOi8vbWl1bWkubWltdW5kb2RpZ2l0YWwubXgiO31zOjY6Il9mbGFzaCI7YToyOntzOjM6Im9sZCI7YTowOnt9czozOiJuZXciO2E6MDp7fX19',1777732927),('qs4K0FPiK1ae30xRRC3pACRZZz429XjUyy3MHoO9',NULL,'31.220.63.131','Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/44.0.2403.157 Safari/537.36','YTozOntzOjY6Il90b2tlbiI7czo0MDoiS09GN2RoSW5vREoyaVJDcHlKNWJxWURaMGxBazRkeXQwN3FpVmJ6NSI7czo5OiJfcHJldmlvdXMiO2E6MTp7czozOiJ1cmwiO3M6Mzc6Imh0dHBzOi8vbWl1bWkubWltdW5kb2RpZ2l0YWwubXgvbG9naW4iO31zOjY6Il9mbGFzaCI7YToyOntzOjM6Im9sZCI7YTowOnt9czozOiJuZXciO2E6MDp7fX19',1777694170),('QTEHMkjlpnoYWrlfyLNyn90a8VwJRe2E8D1sAxYD',NULL,'31.220.63.131','Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/44.0.2403.157 Safari/537.36','YTozOntzOjY6Il90b2tlbiI7czo0MDoiczJxVFcwRFVMb2RzODRoQjhabG13MXBSa1h4S1Z2S1IyOURMTzJVVCI7czo5OiJfcHJldmlvdXMiO2E6MTp7czozOiJ1cmwiO3M6Mzc6Imh0dHBzOi8vbWl1bWkubWltdW5kb2RpZ2l0YWwubXgvbG9naW4iO31zOjY6Il9mbGFzaCI7YToyOntzOjM6Im9sZCI7YTowOnt9czozOiJuZXciO2E6MDp7fX19',1777715764),('QUNH8Fe0IxkrwQxBp8ihqn1abuAlQXwHtEUQr6tc',NULL,'31.220.63.131','Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/44.0.2403.157 Safari/537.36','YTozOntzOjY6Il90b2tlbiI7czo0MDoiV09pVGZRa0MxNW9KOTVEVnMyWFJTTFZtVGF1blZ6dXdZOGZjOGhNaiI7czo5OiJfcHJldmlvdXMiO2E6MTp7czozOiJ1cmwiO3M6Mzc6Imh0dHBzOi8vbWl1bWkubWltdW5kb2RpZ2l0YWwubXgvbG9naW4iO31zOjY6Il9mbGFzaCI7YToyOntzOjM6Im9sZCI7YTowOnt9czozOiJuZXciO2E6MDp7fX19',1777688047),('QWF9cfTAHKqJWyiqrSl0CyLNKJiqvIA8brss2PIV',NULL,'31.220.63.131','Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/44.0.2403.157 Safari/537.36','YTozOntzOjY6Il90b2tlbiI7czo0MDoiNjRMRnJweHRZVkdnUHZNYjFYSUw2TmVSbmVHb0xGR3MzY3ZJblFYbyI7czo5OiJfcHJldmlvdXMiO2E6MTp7czozOiJ1cmwiO3M6MzE6Imh0dHBzOi8vbWl1bWkubWltdW5kb2RpZ2l0YWwubXgiO31zOjY6Il9mbGFzaCI7YToyOntzOjM6Im9sZCI7YTowOnt9czozOiJuZXciO2E6MDp7fX19',1777698023),('QXtklRAscA0wB1jT6aI96QEJTKT7iaxGa5oERNkO',NULL,'31.220.63.131','Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/44.0.2403.157 Safari/537.36','YTozOntzOjY6Il90b2tlbiI7czo0MDoiUk5qMlAzWmEydWpTeDJ0d2ljYWozZ2FLNHVtY2RpTjE4Q1R6cENwaSI7czo5OiJfcHJldmlvdXMiO2E6MTp7czozOiJ1cmwiO3M6MzE6Imh0dHBzOi8vbWl1bWkubWltdW5kb2RpZ2l0YWwubXgiO31zOjY6Il9mbGFzaCI7YToyOntzOjM6Im9sZCI7YTowOnt9czozOiJuZXciO2E6MDp7fX19',1777662012),('r6SfwDTyvSUy0WXMaplaVgjYl1FlPKnXiTX5aG42',NULL,'31.220.63.131','Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/44.0.2403.157 Safari/537.36','YTozOntzOjY6Il90b2tlbiI7czo0MDoidEJDVnVuR3VOTmdyaWJ5M3N5OFlWOFFsSGlrYnRtSFFDSFNGZWJzUyI7czo5OiJfcHJldmlvdXMiO2E6MTp7czozOiJ1cmwiO3M6MzE6Imh0dHBzOi8vbWl1bWkubWltdW5kb2RpZ2l0YWwubXgiO31zOjY6Il9mbGFzaCI7YToyOntzOjM6Im9sZCI7YTowOnt9czozOiJuZXciO2E6MDp7fX19',1777687221),('rBCMlsUpIIqM13p5CEy5ajth09FaqMAbrlv8QCsi',NULL,'31.220.63.131','Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/44.0.2403.157 Safari/537.36','YTozOntzOjY6Il90b2tlbiI7czo0MDoiUGtrdE5GOEtHbWxNOFphSndnSWdDY3hicExBdTNQamhwcDlKQWxlVSI7czo5OiJfcHJldmlvdXMiO2E6MTp7czozOiJ1cmwiO3M6MzE6Imh0dHBzOi8vbWl1bWkubWltdW5kb2RpZ2l0YWwubXgiO31zOjY6Il9mbGFzaCI7YToyOntzOjM6Im9sZCI7YTowOnt9czozOiJuZXciO2E6MDp7fX19',1777722123),('RcIXN6VwDTEjTyzGKNZjxWNa75M8zwAieG6J2k1m',NULL,'31.220.63.131','Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/44.0.2403.157 Safari/537.36','YTozOntzOjY6Il90b2tlbiI7czo0MDoiYmxwSlZXQUhjR1AwNVlGb01KTGliZ0F1Rk5ZZldVUUhySFFxdzFDdCI7czo5OiJfcHJldmlvdXMiO2E6MTp7czozOiJ1cmwiO3M6Mzc6Imh0dHBzOi8vbWl1bWkubWltdW5kb2RpZ2l0YWwubXgvbG9naW4iO31zOjY6Il9mbGFzaCI7YToyOntzOjM6Im9sZCI7YTowOnt9czozOiJuZXciO2E6MDp7fX19',1777696927),('rdzcjMUN76SvWkZ4N2VZo4g9kX1EbnVhOzm2znRJ',NULL,'31.220.63.131','Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/44.0.2403.157 Safari/537.36','YTozOntzOjY6Il90b2tlbiI7czo0MDoiNXlQTzh4V0p3OHhwamtnNDZGTXVZRmJCTGxwYnMwM3pLcGpVODBjNCI7czo5OiJfcHJldmlvdXMiO2E6MTp7czozOiJ1cmwiO3M6Mzc6Imh0dHBzOi8vbWl1bWkubWltdW5kb2RpZ2l0YWwubXgvbG9naW4iO31zOjY6Il9mbGFzaCI7YToyOntzOjM6Im9sZCI7YTowOnt9czozOiJuZXciO2E6MDp7fX19',1777662012),('rG6dhBpNFvhuLdp4PG2fpPjXo4FKlsT68UywLoxK',NULL,'31.220.63.131','Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/44.0.2403.157 Safari/537.36','YTozOntzOjY6Il90b2tlbiI7czo0MDoiR3F5WnJPVG9wVUxzajdlUGFwUjBnbU5NeTVianZzYlNYdmxRbFJkRCI7czo5OiJfcHJldmlvdXMiO2E6MTp7czozOiJ1cmwiO3M6MzE6Imh0dHBzOi8vbWl1bWkubWltdW5kb2RpZ2l0YWwubXgiO31zOjY6Il9mbGFzaCI7YToyOntzOjM6Im9sZCI7YTowOnt9czozOiJuZXciO2E6MDp7fX19',1777670044),('RiAnArQUQdnXPoMZAUJXysCOQF1NTpRL6r96sKqg',NULL,'31.220.63.131','Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/44.0.2403.157 Safari/537.36','YTozOntzOjY6Il90b2tlbiI7czo0MDoiSTI1bllhdlpvTWxxOWd1YVFaWHdnWUV2Y252eVZ4a3cyNHVFVTAySiI7czo5OiJfcHJldmlvdXMiO2E6MTp7czozOiJ1cmwiO3M6Mzc6Imh0dHBzOi8vbWl1bWkubWltdW5kb2RpZ2l0YWwubXgvbG9naW4iO31zOjY6Il9mbGFzaCI7YToyOntzOjM6Im9sZCI7YTowOnt9czozOiJuZXciO2E6MDp7fX19',1777662843),('RKZBzrQZ7C94ED1lVAzTJflBlGDdYbL1u7Sg1nMb',NULL,'31.220.63.131','Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/44.0.2403.157 Safari/537.36','YTozOntzOjY6Il90b2tlbiI7czo0MDoielcxOXhPOWxCNVkxZW1rQXhhdG1ZSWZpMXJweUEwSTN0WG12SU02VyI7czo5OiJfcHJldmlvdXMiO2E6MTp7czozOiJ1cmwiO3M6MzE6Imh0dHBzOi8vbWl1bWkubWltdW5kb2RpZ2l0YWwubXgiO31zOjY6Il9mbGFzaCI7YToyOntzOjM6Im9sZCI7YTowOnt9czozOiJuZXciO2E6MDp7fX19',1777646525),('rNhItqkyJfeaHSAMd8is5WEHtbG79MOgy5GfURxq',NULL,'31.220.63.131','Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/44.0.2403.157 Safari/537.36','YTozOntzOjY6Il90b2tlbiI7czo0MDoiTUVneGFaOXdQT01sNzJIV09LSDlyZHdLazlnZ3dFaGFVT0toR3l0ciI7czo5OiJfcHJldmlvdXMiO2E6MTp7czozOiJ1cmwiO3M6MzE6Imh0dHBzOi8vbWl1bWkubWltdW5kb2RpZ2l0YWwubXgiO31zOjY6Il9mbGFzaCI7YToyOntzOjM6Im9sZCI7YTowOnt9czozOiJuZXciO2E6MDp7fX19',1777651208),('rnyz6yDIXXEdjvQQAfSoONbE1jLtRsfEtX7JadlC',NULL,'31.220.63.131','Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/44.0.2403.157 Safari/537.36','YTozOntzOjY6Il90b2tlbiI7czo0MDoiVnJvTTFLYVpoakVFRWFQODU3Z2xHYU9FYnVoY2NnVU9jMDlPVjVlZCI7czo5OiJfcHJldmlvdXMiO2E6MTp7czozOiJ1cmwiO3M6MzE6Imh0dHBzOi8vbWl1bWkubWltdW5kb2RpZ2l0YWwubXgiO31zOjY6Il9mbGFzaCI7YToyOntzOjM6Im9sZCI7YTowOnt9czozOiJuZXciO2E6MDp7fX19',1777712410),('sC11CQ1xVoxJGaGnhAjljbdD1r5SrtmtrsXTJA3Q',NULL,'31.220.63.131','Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/44.0.2403.157 Safari/537.36','YTozOntzOjY6Il90b2tlbiI7czo0MDoiRDhPSDdDVmlSVVk1VTRybmJXT3hDaDVMN1IxaWJ3clpNM08xNUE1MSI7czo5OiJfcHJldmlvdXMiO2E6MTp7czozOiJ1cmwiO3M6Mzc6Imh0dHBzOi8vbWl1bWkubWltdW5kb2RpZ2l0YWwubXgvbG9naW4iO31zOjY6Il9mbGFzaCI7YToyOntzOjM6Im9sZCI7YTowOnt9czozOiJuZXciO2E6MDp7fX19',1777658409),('sCNiWnN0i9qlN6s6K4EBamxmuR3OQSBobDw17ymp',NULL,'31.220.63.131','Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/44.0.2403.157 Safari/537.36','YTozOntzOjY6Il90b2tlbiI7czo0MDoiMlN0MkF4eDBEdXFSZ3VETHlkU0xoN0d4SW5wYjViRnQzUjg4QmFTcCI7czo5OiJfcHJldmlvdXMiO2E6MTp7czozOiJ1cmwiO3M6MzE6Imh0dHBzOi8vbWl1bWkubWltdW5kb2RpZ2l0YWwubXgiO31zOjY6Il9mbGFzaCI7YToyOntzOjM6Im9sZCI7YTowOnt9czozOiJuZXciO2E6MDp7fX19',1777652044),('SL5IiM9Zrj5GrzxL4LKNpLkE14IguX4icFg2SGm0',NULL,'31.220.63.131','Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/44.0.2403.157 Safari/537.36','YTozOntzOjY6Il90b2tlbiI7czo0MDoiTTRPd2l1eVhyUjVMa0tnYUVYU0YzQkRHQjhObEs4cTlDc3pZS3Y5WCI7czo5OiJfcHJldmlvdXMiO2E6MTp7czozOiJ1cmwiO3M6Mzc6Imh0dHBzOi8vbWl1bWkubWltdW5kb2RpZ2l0YWwubXgvbG9naW4iO31zOjY6Il9mbGFzaCI7YToyOntzOjM6Im9sZCI7YTowOnt9czozOiJuZXciO2E6MDp7fX19',1777702444),('SREmdnlIRXQfP36WhXVlJ1EMrr0uSRsRP5Wv883X',NULL,'31.220.63.131','Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/44.0.2403.157 Safari/537.36','YTozOntzOjY6Il90b2tlbiI7czo0MDoiczBDMllQNjZTaDFFQnJBek14OHp3Y1hxZ0JFS21PNHRwZFhHWVdIZSI7czo5OiJfcHJldmlvdXMiO2E6MTp7czozOiJ1cmwiO3M6MzE6Imh0dHBzOi8vbWl1bWkubWltdW5kb2RpZ2l0YWwubXgiO31zOjY6Il9mbGFzaCI7YToyOntzOjM6Im9sZCI7YTowOnt9czozOiJuZXciO2E6MDp7fX19',1777682524),('SV7EZAVN3XMBM4FNFbGpKyTfP4MrpNEEjsp1fTGS',NULL,'31.220.63.131','Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/44.0.2403.157 Safari/537.36','YTozOntzOjY6Il90b2tlbiI7czo0MDoiWHdsZFpqbjZlU3AzM2ZxM3ZnOFlJaUJpYmtOdkJ5T2RDUnlVY256byI7czo5OiJfcHJldmlvdXMiO2E6MTp7czozOiJ1cmwiO3M6MzE6Imh0dHBzOi8vbWl1bWkubWltdW5kb2RpZ2l0YWwubXgiO31zOjY6Il9mbGFzaCI7YToyOntzOjM6Im9sZCI7YTowOnt9czozOiJuZXciO2E6MDp7fX19',1777655644),('sWPOaJXGMgfy41wZdI0xcDLjJgEpoMgA1Bc2aGgA',NULL,'31.220.63.131','Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/44.0.2403.157 Safari/537.36','YTozOntzOjY6Il90b2tlbiI7czo0MDoiOUxWN0hncWd5aUV2UDZkQW5oSE5Fc2laSTBvdGY3cFJ0NGZDeVdKaSI7czo5OiJfcHJldmlvdXMiO2E6MTp7czozOiJ1cmwiO3M6Mzc6Imh0dHBzOi8vbWl1bWkubWltdW5kb2RpZ2l0YWwubXgvbG9naW4iO31zOjY6Il9mbGFzaCI7YToyOntzOjM6Im9sZCI7YTowOnt9czozOiJuZXciO2E6MDp7fX19',1777703285),('sZT3gsmAe8BTtNeYhRE1UK2PYkf449oHWg5SMPyv',NULL,'31.220.63.131','Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/44.0.2403.157 Safari/537.36','YTozOntzOjY6Il90b2tlbiI7czo0MDoiT3kyeDZOa20wZnk0ZTJmdW5NVHhwZDRUNDUycUdOdE5FUlhUcjQ3YSI7czo5OiJfcHJldmlvdXMiO2E6MTp7czozOiJ1cmwiO3M6Mzc6Imh0dHBzOi8vbWl1bWkubWltdW5kb2RpZ2l0YWwubXgvbG9naW4iO31zOjY6Il9mbGFzaCI7YToyOntzOjM6Im9sZCI7YTowOnt9czozOiJuZXciO2E6MDp7fX19',1777676165),('t1Xw62y9yJWGZhernVhdoecTuPJEGR5KvBxGTuNP',NULL,'31.220.63.131','Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/44.0.2403.157 Safari/537.36','YTozOntzOjY6Il90b2tlbiI7czo0MDoidzhBV1ltcTNySG5HdGxOSXVCS1lFRm1ZcXU5WTlKNEZXUEFORWtJRyI7czo5OiJfcHJldmlvdXMiO2E6MTp7czozOiJ1cmwiO3M6Mzc6Imh0dHBzOi8vbWl1bWkubWltdW5kb2RpZ2l0YWwubXgvbG9naW4iO31zOjY6Il9mbGFzaCI7YToyOntzOjM6Im9sZCI7YTowOnt9czozOiJuZXciO2E6MDp7fX19',1777642923),('TGpHNwBJtCKJXx52VmhUkirjxP2y9AbGHahV7e2L',NULL,'31.220.63.131','Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/44.0.2403.157 Safari/537.36','YTozOntzOjY6Il90b2tlbiI7czo0MDoib1V6eFlHTGc4V0V5OVcyQ0JQN3FFdnpvZjZXMzVjQ3VJaVNiOFl5UCI7czo5OiJfcHJldmlvdXMiO2E6MTp7czozOiJ1cmwiO3M6Mzc6Imh0dHBzOi8vbWl1bWkubWltdW5kb2RpZ2l0YWwubXgvbG9naW4iO31zOjY6Il9mbGFzaCI7YToyOntzOjM6Im9sZCI7YTowOnt9czozOiJuZXciO2E6MDp7fX19',1777734849),('tPKAZ0rN47hju8Mst3rCxfA35z2dPQrfE7mAWeZZ',NULL,'31.220.63.131','Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/44.0.2403.157 Safari/537.36','YTozOntzOjY6Il90b2tlbiI7czo0MDoiV05iRTFPaW9CNlVodUZxS0h4ZEpsUUY3bFB6Ylo3ZWhrOUVTUGtQMSI7czo5OiJfcHJldmlvdXMiO2E6MTp7czozOiJ1cmwiO3M6Mzc6Imh0dHBzOi8vbWl1bWkubWltdW5kb2RpZ2l0YWwubXgvbG9naW4iO31zOjY6Il9mbGFzaCI7YToyOntzOjM6Im9sZCI7YTowOnt9czozOiJuZXciO2E6MDp7fX19',1777696091),('tpyghVBOeWOXpnE23PgAvsMtOgXbg2Z3kwTrazdb',NULL,'31.220.63.131','Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/44.0.2403.157 Safari/537.36','YTozOntzOjY6Il90b2tlbiI7czo0MDoieDZzbWhKNU9yTkQ0cXZRWXRnZVhhWDhla1RlWDlaV3ZabW83ek1sTCI7czo5OiJfcHJldmlvdXMiO2E6MTp7czozOiJ1cmwiO3M6MzE6Imh0dHBzOi8vbWl1bWkubWltdW5kb2RpZ2l0YWwubXgiO31zOjY6Il9mbGFzaCI7YToyOntzOjM6Im9sZCI7YTowOnt9czozOiJuZXciO2E6MDp7fX19',1777681688),('tvHXJCT66nLZxTOt58RRU7eFA0bsntaI7J7gwu8g',NULL,'31.220.63.131','Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/44.0.2403.157 Safari/537.36','YTozOntzOjY6Il90b2tlbiI7czo0MDoiUDMybUE3R0dhV1MyejE3MXVpYXRLNDdXd3RkQlM2bGhRUTNHQzRvSSI7czo5OiJfcHJldmlvdXMiO2E6MTp7czozOiJ1cmwiO3M6MzE6Imh0dHBzOi8vbWl1bWkubWltdW5kb2RpZ2l0YWwubXgiO31zOjY6Il9mbGFzaCI7YToyOntzOjM6Im9sZCI7YTowOnt9czozOiJuZXciO2E6MDp7fX19',1777668125),('TyfjChR8ohAuHrnl7JjHISwcUxZLub3fCS98HHgx',NULL,'31.220.63.131','Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/44.0.2403.157 Safari/537.36','YTozOntzOjY6Il90b2tlbiI7czo0MDoiMnhSQW5nbkQ1VUJ6YjE4UGpSWnBBQkdiZ3pLQlRlQTVMUFZWRTRhayI7czo5OiJfcHJldmlvdXMiO2E6MTp7czozOiJ1cmwiO3M6MzE6Imh0dHBzOi8vbWl1bWkubWltdW5kb2RpZ2l0YWwubXgiO31zOjY6Il9mbGFzaCI7YToyOntzOjM6Im9sZCI7YTowOnt9czozOiJuZXciO2E6MDp7fX19',1777658163),('u0lF686QfB6S0iVRtGFjIXm3Mlf9onC72Q7x5UlK',NULL,'31.220.63.131','Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/44.0.2403.157 Safari/537.36','YTozOntzOjY6Il90b2tlbiI7czo0MDoiUGFLSEc1UGVNcnFYeThCcERPSWZ4aE4ycTE5cDE4bFhBSTlHMWlRMSI7czo5OiJfcHJldmlvdXMiO2E6MTp7czozOiJ1cmwiO3M6Mzc6Imh0dHBzOi8vbWl1bWkubWltdW5kb2RpZ2l0YWwubXgvbG9naW4iO31zOjY6Il9mbGFzaCI7YToyOntzOjM6Im9sZCI7YTowOnt9czozOiJuZXciO2E6MDp7fX19',1777723208),('U5WnE8MwImgj15CUsrKbVZKnRXl5KmlqNw33QejB',NULL,'31.220.63.131','Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/44.0.2403.157 Safari/537.36','YTozOntzOjY6Il90b2tlbiI7czo0MDoiMkVTZnA4YlNwQmpYbHFlZUJjUlNZbVBId3lscW5PRHlSVnJPamhOSiI7czo5OiJfcHJldmlvdXMiO2E6MTp7czozOiJ1cmwiO3M6MzE6Imh0dHBzOi8vbWl1bWkubWltdW5kb2RpZ2l0YWwubXgiO31zOjY6Il9mbGFzaCI7YToyOntzOjM6Im9sZCI7YTowOnt9czozOiJuZXciO2E6MDp7fX19',1777708563),('u9k3aiG1xXqhFVTc9VLbvQdpjrv9IZEQUkO0aqMJ',NULL,'31.220.63.131','Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/44.0.2403.157 Safari/537.36','YTozOntzOjY6Il90b2tlbiI7czo0MDoiRFBhMmxoNnNrTHoyZzZKU3Z4YWxyMHdZdWRib2Rha1RGa25FWFQzMSI7czo5OiJfcHJldmlvdXMiO2E6MTp7czozOiJ1cmwiO3M6Mzc6Imh0dHBzOi8vbWl1bWkubWltdW5kb2RpZ2l0YWwubXgvbG9naW4iO31zOjY6Il9mbGFzaCI7YToyOntzOjM6Im9sZCI7YTowOnt9czozOiJuZXciO2E6MDp7fX19',1777687221),('U9vT8qlPRLXxJeUVyMjxS52jk5OvpYhIoiFyG8sD',NULL,'31.220.63.131','Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/44.0.2403.157 Safari/537.36','YTozOntzOjY6Il90b2tlbiI7czo0MDoia213M1NNRHpleVdDYVhiNGJzQUdiUmlwdml6T3lXNm5sU0lsRUZYUCI7czo5OiJfcHJldmlvdXMiO2E6MTp7czozOiJ1cmwiO3M6Mzc6Imh0dHBzOi8vbWl1bWkubWltdW5kb2RpZ2l0YWwubXgvbG9naW4iO31zOjY6Il9mbGFzaCI7YToyOntzOjM6Im9sZCI7YTowOnt9czozOiJuZXciO2E6MDp7fX19',1777728486),('uBDIpLEOPx4loJeOtiZuyu8cq1m8fXnXdbBtRVZW',NULL,'31.220.63.131','Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/44.0.2403.157 Safari/537.36','YTozOntzOjY6Il90b2tlbiI7czo0MDoiSWtIMDZlQUMyelVEcFZ6S3I0cFEweURVQ0R6VVh6YzFudThMSkdmdiI7czo5OiJfcHJldmlvdXMiO2E6MTp7czozOiJ1cmwiO3M6MzE6Imh0dHBzOi8vbWl1bWkubWltdW5kb2RpZ2l0YWwubXgiO31zOjY6Il9mbGFzaCI7YToyOntzOjM6Im9sZCI7YTowOnt9czozOiJuZXciO2E6MDp7fX19',1777706884),('uDi0tbJMxlAYZDsOlfVeqHEKPGHqM7zBxX740aEx',NULL,'31.220.63.131','Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/44.0.2403.157 Safari/537.36','YTozOntzOjY6Il90b2tlbiI7czo0MDoiU2pGVXNJZmx4OTBGRk9maUJGSkZRTUxsako2NzNtZnFuS1pITkh3NyI7czo5OiJfcHJldmlvdXMiO2E6MTp7czozOiJ1cmwiO3M6MzE6Imh0dHBzOi8vbWl1bWkubWltdW5kb2RpZ2l0YWwubXgiO31zOjY6Il9mbGFzaCI7YToyOntzOjM6Im9sZCI7YTowOnt9czozOiJuZXciO2E6MDp7fX19',1777719606),('uFzfESwhyKgRb6HXDKiUxIxzbPHCQW13BEXTgYjS',NULL,'31.220.63.131','Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/44.0.2403.157 Safari/537.36','YTozOntzOjY6Il90b2tlbiI7czo0MDoiNVJtNFlBSVNKOVVaVEdFdEdhcVphZ1g3Y0tnRUNFU2JVdzlLTUFnVSI7czo5OiJfcHJldmlvdXMiO2E6MTp7czozOiJ1cmwiO3M6Mzc6Imh0dHBzOi8vbWl1bWkubWltdW5kb2RpZ2l0YWwubXgvbG9naW4iO31zOjY6Il9mbGFzaCI7YToyOntzOjM6Im9sZCI7YTowOnt9czozOiJuZXciO2E6MDp7fX19',1777719365),('UJhCfwjiHG1JnUV2A6si1g5KfIsWLQrpGJRJ5bT5',NULL,'31.220.63.131','Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/44.0.2403.157 Safari/537.36','YTozOntzOjY6Il90b2tlbiI7czo0MDoidDNLM3NqSVRoM2tNREg1eXc1SHdmOE1wZVFHbWltMnlqd2M2UGhrZCI7czo5OiJfcHJldmlvdXMiO2E6MTp7czozOiJ1cmwiO3M6MzE6Imh0dHBzOi8vbWl1bWkubWltdW5kb2RpZ2l0YWwubXgiO31zOjY6Il9mbGFzaCI7YToyOntzOjM6Im9sZCI7YTowOnt9czozOiJuZXciO2E6MDp7fX19',1777694425),('uKedjq4o4ABEv9O7zFGjpZq3qoqKQ0ZShfWDkAo3',NULL,'31.220.63.131','Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/44.0.2403.157 Safari/537.36','YTozOntzOjY6Il90b2tlbiI7czo0MDoiQ1BaRmZ6MGFsb2FSN3BISEhjeUg2N2w4TU1qaUYzWDBYbXkxNmd3QyI7czo5OiJfcHJldmlvdXMiO2E6MTp7czozOiJ1cmwiO3M6MzE6Imh0dHBzOi8vbWl1bWkubWltdW5kb2RpZ2l0YWwubXgiO31zOjY6Il9mbGFzaCI7YToyOntzOjM6Im9sZCI7YTowOnt9czozOiJuZXciO2E6MDp7fX19',1777720444),('UMWotkAXA62datd0noKTLbfzWXC3bHmiNCLHC32C',NULL,'31.220.63.131','Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/44.0.2403.157 Safari/537.36','YTozOntzOjY6Il90b2tlbiI7czo0MDoic2xPUjZjOGNaQTlIU005ZGVEQVhnUjNMMUs1SVRmM0c0a1ZxbHFQUCI7czo5OiJfcHJldmlvdXMiO2E6MTp7czozOiJ1cmwiO3M6MzE6Imh0dHBzOi8vbWl1bWkubWltdW5kb2RpZ2l0YWwubXgiO31zOjY6Il9mbGFzaCI7YToyOntzOjM6Im9sZCI7YTowOnt9czozOiJuZXciO2E6MDp7fX19',1777702443),('UoURovfXCBRwxgALUhG4tPIahWeGY0qSILd6LrDz',NULL,'31.220.63.131','Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/44.0.2403.157 Safari/537.36','YTozOntzOjY6Il90b2tlbiI7czo0MDoidVpoYW9zY2w4bU8zQkVQckNjUmJybGRvUktqdlRtcWxqbkZrWVkwTiI7czo5OiJfcHJldmlvdXMiO2E6MTp7czozOiJ1cmwiO3M6Mzc6Imh0dHBzOi8vbWl1bWkubWltdW5kb2RpZ2l0YWwubXgvbG9naW4iO31zOjY6Il9mbGFzaCI7YToyOntzOjM6Im9sZCI7YTowOnt9czozOiJuZXciO2E6MDp7fX19',1777731247),('v0YKXOV99pagww1ylEe8T8q5USHJxHAU6J6m3K1R',NULL,'31.220.63.131','Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/44.0.2403.157 Safari/537.36','YTozOntzOjY6Il90b2tlbiI7czo0MDoiRGNHb29VTng4MW5FVHBod1dBcUdkeWlxcFRDYlJjaTc0cFN3ZXR6YSI7czo5OiJfcHJldmlvdXMiO2E6MTp7czozOiJ1cmwiO3M6Mzc6Imh0dHBzOi8vbWl1bWkubWltdW5kb2RpZ2l0YWwubXgvbG9naW4iO31zOjY6Il9mbGFzaCI7YToyOntzOjM6Im9sZCI7YTowOnt9czozOiJuZXciO2E6MDp7fX19',1777646525),('v3j2lQ5aNWSBHU64Bk8c8rJrzcjN56ZHmmwQE80w',NULL,'31.220.63.131','Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/44.0.2403.157 Safari/537.36','YTozOntzOjY6Il90b2tlbiI7czo0MDoiWGE5T3Y0NDR4N1NGMlMwdk44d0dnc2lUVklzYzZnV1NEaWw0azVSaSI7czo5OiJfcHJldmlvdXMiO2E6MTp7czozOiJ1cmwiO3M6Mzc6Imh0dHBzOi8vbWl1bWkubWltdW5kb2RpZ2l0YWwubXgvbG9naW4iO31zOjY6Il9mbGFzaCI7YToyOntzOjM6Im9sZCI7YTowOnt9czozOiJuZXciO2E6MDp7fX19',1777670885),('vgvRO9UZp3DpJM3tUEO31I1HNKixiu1MgbfniMUr',NULL,'31.220.63.131','Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/44.0.2403.157 Safari/537.36','YTozOntzOjY6Il90b2tlbiI7czo0MDoiMzlzemJ2OE1IVlp1ZW85RTBKQnRWNTFkUWt1dGl0eUYzSlVQekIwZCI7czo5OiJfcHJldmlvdXMiO2E6MTp7czozOiJ1cmwiO3M6MzE6Imh0dHBzOi8vbWl1bWkubWltdW5kb2RpZ2l0YWwubXgiO31zOjY6Il9mbGFzaCI7YToyOntzOjM6Im9sZCI7YTowOnt9czozOiJuZXciO2E6MDp7fX19',1777693330),('vnqFZ3Vuu9RkeKjvfl7ZiwzGzdAZ9g5YWWvhGIU7',NULL,'31.220.63.131','Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/44.0.2403.157 Safari/537.36','YTozOntzOjY6Il90b2tlbiI7czo0MDoiTkdRbXVyTDdOd0duZVFESVBqeU9Tbnl3ZnRZWEdmWkE4R1VvUU04MiI7czo5OiJfcHJldmlvdXMiO2E6MTp7czozOiJ1cmwiO3M6MzE6Imh0dHBzOi8vbWl1bWkubWltdW5kb2RpZ2l0YWwubXgiO31zOjY6Il9mbGFzaCI7YToyOntzOjM6Im9sZCI7YTowOnt9czozOiJuZXciO2E6MDp7fX19',1777724883),('vPeQus2Hvh1V4ycm0DYwOcU50DZbwDLgtIAnhcCe',NULL,'31.220.63.131','Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/44.0.2403.157 Safari/537.36','YTozOntzOjY6Il90b2tlbiI7czo0MDoia0ltTUUxVU9lVkVTbE1ObnRJak1NdmZTTEZyTXNMOHRuc0VEQVZsbCI7czo5OiJfcHJldmlvdXMiO2E6MTp7czozOiJ1cmwiO3M6MzE6Imh0dHBzOi8vbWl1bWkubWltdW5kb2RpZ2l0YWwubXgiO31zOjY6Il9mbGFzaCI7YToyOntzOjM6Im9sZCI7YTowOnt9czozOiJuZXciO2E6MDp7fX19',1777724044),('VRzi0cOKOdIT9bSsgwp2vydAKZN7Pj0Lj9ZufoqA',NULL,'31.220.63.131','Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/44.0.2403.157 Safari/537.36','YTozOntzOjY6Il90b2tlbiI7czo0MDoic1A0S2syelN4RjkydWJCR0c5Tm4zUG9xbG1qM1dkemtzcm1MTGJwWSI7czo5OiJfcHJldmlvdXMiO2E6MTp7czozOiJ1cmwiO3M6MzE6Imh0dHBzOi8vbWl1bWkubWltdW5kb2RpZ2l0YWwubXgiO31zOjY6Il9mbGFzaCI7YToyOntzOjM6Im9sZCI7YTowOnt9czozOiJuZXciO2E6MDp7fX19',1777661763),('VsST4Yyk9SuIWvmvaFm6PcwULp7vYZfuyaBxCRO2',NULL,'31.220.63.131','Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/44.0.2403.157 Safari/537.36','YTozOntzOjY6Il90b2tlbiI7czo0MDoiM2Zuc2lzU0VETnhsakdlZFJlMXQxaUZicXRGT3UwT0h4aFp5MHgxOCI7czo5OiJfcHJldmlvdXMiO2E6MTp7czozOiJ1cmwiO3M6Mzc6Imh0dHBzOi8vbWl1bWkubWltdW5kb2RpZ2l0YWwubXgvbG9naW4iO31zOjY6Il9mbGFzaCI7YToyOntzOjM6Im9sZCI7YTowOnt9czozOiJuZXciO2E6MDp7fX19',1777676411),('vT11Z7I5h0zlAPrgIdohfzk0duWWPbBtbbqtnSSH',NULL,'31.220.63.131','Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/44.0.2403.157 Safari/537.36','YTozOntzOjY6Il90b2tlbiI7czo0MDoiTzkyN2xKWVFEbnRTbGJwRlNaQ3pDdGV3TzNLMjVQR09PUFM3b25XMyI7czo5OiJfcHJldmlvdXMiO2E6MTp7czozOiJ1cmwiO3M6Mzc6Imh0dHBzOi8vbWl1bWkubWltdW5kb2RpZ2l0YWwubXgvbG9naW4iO31zOjY6Il9mbGFzaCI7YToyOntzOjM6Im9sZCI7YTowOnt9czozOiJuZXciO2E6MDp7fX19',1777732085),('VYvpoZUcH643JrC78ZjUc0UaNpWhBHyRqlWe9Cj0',NULL,'31.220.63.131','Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/44.0.2403.157 Safari/537.36','YTozOntzOjY6Il90b2tlbiI7czo0MDoiTHVFWUZsc05HRmpuSHR1SHVCWWdOUHdjSHBzbVB4N1o4RkpKWHlJcyI7czo5OiJfcHJldmlvdXMiO2E6MTp7czozOiJ1cmwiO3M6Mzc6Imh0dHBzOi8vbWl1bWkubWltdW5kb2RpZ2l0YWwubXgvbG9naW4iO31zOjY6Il9mbGFzaCI7YToyOntzOjM6Im9sZCI7YTowOnt9czozOiJuZXciO2E6MDp7fX19',1777678927),('w3bGwGDki0hjk47KPC62ODw7UEWTTYb6Jb9pzhKE',NULL,'31.220.63.131','Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/44.0.2403.157 Safari/537.36','YTozOntzOjY6Il90b2tlbiI7czo0MDoicmY0ajFvcmxpY3dtb2pUdWFVdG00ek1pMEsySDA1M1BoeWJSSE9OQiI7czo5OiJfcHJldmlvdXMiO2E6MTp7czozOiJ1cmwiO3M6Mzc6Imh0dHBzOi8vbWl1bWkubWltdW5kb2RpZ2l0YWwubXgvbG9naW4iO31zOjY6Il9mbGFzaCI7YToyOntzOjM6Im9sZCI7YTowOnt9czozOiJuZXciO2E6MDp7fX19',1777659243),('W4J8UjtEQyjjxImz8W12QJcrzsT9GO6NpJ7Fe7cs',NULL,'31.220.63.131','Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/44.0.2403.157 Safari/537.36','YTozOntzOjY6Il90b2tlbiI7czo0MDoicnFUTVBNNXZCYnE3QzY4S2luOHlMTVZUcWlIN3Z2eEFuYm9od0xNeiI7czo5OiJfcHJldmlvdXMiO2E6MTp7czozOiJ1cmwiO3M6Mzc6Imh0dHBzOi8vbWl1bWkubWltdW5kb2RpZ2l0YWwubXgvbG9naW4iO31zOjY6Il9mbGFzaCI7YToyOntzOjM6Im9sZCI7YTowOnt9czozOiJuZXciO2E6MDp7fX19',1777652883),('w66YhTofzqlTPJPBEfmHtMhlQif54CaWqMEEweej',NULL,'31.220.63.131','Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/44.0.2403.157 Safari/537.36','YTozOntzOjY6Il90b2tlbiI7czo0MDoibDJwREM2cUI4QjJVWnlxb2NPcUVzcDF0bEF5OWdhZWt0cEhxNXl4RyI7czo5OiJfcHJldmlvdXMiO2E6MTp7czozOiJ1cmwiO3M6Mzc6Imh0dHBzOi8vbWl1bWkubWltdW5kb2RpZ2l0YWwubXgvbG9naW4iO31zOjY6Il9mbGFzaCI7YToyOntzOjM6Im9sZCI7YTowOnt9czozOiJuZXciO2E6MDp7fX19',1777705212),('W7MG79jtbyRljAusG7KqWehGtM0PeLuVkYh6q3nc',NULL,'31.220.63.131','Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/44.0.2403.157 Safari/537.36','YTozOntzOjY6Il90b2tlbiI7czo0MDoia05GMEViMjBDY3BCdWZZWGF4RWlJOGE5YTNSTTV3cVdhaDRjaFZkNSI7czo5OiJfcHJldmlvdXMiO2E6MTp7czozOiJ1cmwiO3M6MzE6Imh0dHBzOi8vbWl1bWkubWltdW5kb2RpZ2l0YWwubXgiO31zOjY6Il9mbGFzaCI7YToyOntzOjM6Im9sZCI7YTowOnt9czozOiJuZXciO2E6MDp7fX19',1777713244),('w7Qga97H9rW3mPhCO4JmEV1CzWonZzeiiJFH3K8N',NULL,'31.220.63.131','Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/44.0.2403.157 Safari/537.36','YTozOntzOjY6Il90b2tlbiI7czo0MDoiYndmbGNYRXNYUmdPUG9DSW11MHc0U3FwZmdXeldmeEtNZkR3YmlwYyI7czo5OiJfcHJldmlvdXMiO2E6MTp7czozOiJ1cmwiO3M6Mzc6Imh0dHBzOi8vbWl1bWkubWltdW5kb2RpZ2l0YWwubXgvbG9naW4iO31zOjY6Il9mbGFzaCI7YToyOntzOjM6Im9sZCI7YTowOnt9czozOiJuZXciO2E6MDp7fX19',1777683617),('Wi7cbsjpOdEZ1TIbY34CgDeRB1CNqix6v8f2vE0e',NULL,'31.220.63.131','Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/44.0.2403.157 Safari/537.36','YTozOntzOjY6Il90b2tlbiI7czo0MDoidzZYQ2dRYUZ2cE81Vk5RTWRZNDc4OWRRa0ROV1p2VEdZNEtsWTBEUiI7czo5OiJfcHJldmlvdXMiO2E6MTp7czozOiJ1cmwiO3M6MzE6Imh0dHBzOi8vbWl1bWkubWltdW5kb2RpZ2l0YWwubXgiO31zOjY6Il9mbGFzaCI7YToyOntzOjM6Im9sZCI7YTowOnt9czozOiJuZXciO2E6MDp7fX19',1777704965),('wJEJTChG3nbhzQJWo4nbKfzIY40qHr50r5IEO9QP',NULL,'31.220.63.131','Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/44.0.2403.157 Safari/537.36','YTozOntzOjY6Il90b2tlbiI7czo0MDoidWQzaDZlakd0d21BSFdxa3pUQW1CRUZIWTZiRGJ6UEVIQ1lOemxtdCI7czo5OiJfcHJldmlvdXMiO2E6MTp7czozOiJ1cmwiO3M6MzE6Imh0dHBzOi8vbWl1bWkubWltdW5kb2RpZ2l0YWwubXgiO31zOjY6Il9mbGFzaCI7YToyOntzOjM6Im9sZCI7YTowOnt9czozOiJuZXciO2E6MDp7fX19',1777703284),('wKOG0Q8zAoU6W2G1EqaAag5DAjBO0kEBPBEKVw6I',NULL,'31.220.63.131','Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/44.0.2403.157 Safari/537.36','YTozOntzOjY6Il90b2tlbiI7czo0MDoia2lrYlFlb2dlTlRTUzJka3hSRHZmaDdJb0g2R3R2UjlwUkRYMlBmbiI7czo5OiJfcHJldmlvdXMiO2E6MTp7czozOiJ1cmwiO3M6Mzc6Imh0dHBzOi8vbWl1bWkubWltdW5kb2RpZ2l0YWwubXgvbG9naW4iO31zOjY6Il9mbGFzaCI7YToyOntzOjM6Im9sZCI7YTowOnt9czozOiJuZXciO2E6MDp7fX19',1777679767),('wNl82TendmDl10FvUC9c3Nv2kwGn1CvkxKTt9T67',NULL,'31.220.63.131','Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/44.0.2403.157 Safari/537.36','YTozOntzOjY6Il90b2tlbiI7czo0MDoiS3hQWEtwNzVLQmNkaFBUenhuVU1STVpiZ2t1ZjJsbUo4R1QzdDh0MCI7czo5OiJfcHJldmlvdXMiO2E6MTp7czozOiJ1cmwiO3M6MzE6Imh0dHBzOi8vbWl1bWkubWltdW5kb2RpZ2l0YWwubXgiO31zOjY6Il9mbGFzaCI7YToyOntzOjM6Im9sZCI7YTowOnt9czozOiJuZXciO2E6MDp7fX19',1777734849),('x12JmXAodJiR7j55Rc2PWp27ZcbyZgWhBxd3k6dj',NULL,'31.220.63.131','Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/44.0.2403.157 Safari/537.36','YTozOntzOjY6Il90b2tlbiI7czo0MDoib242bGE1SFBabmlzZGhlZXZMc3Q2dExIS21kNWpSR2RhcTdrY0FodSI7czo5OiJfcHJldmlvdXMiO2E6MTp7czozOiJ1cmwiO3M6MzE6Imh0dHBzOi8vbWl1bWkubWltdW5kb2RpZ2l0YWwubXgiO31zOjY6Il9mbGFzaCI7YToyOntzOjM6Im9sZCI7YTowOnt9czozOiJuZXciO2E6MDp7fX19',1777722963),('x7Bp4BArxhmOpb8tG8gZ0iQ4wPBn36R2ftKbEJWy',NULL,'31.220.63.131','Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/44.0.2403.157 Safari/537.36','YTozOntzOjY6Il90b2tlbiI7czo0MDoiUVhNUmh4QkFiSzVHaEdLNkZyVGM0VVZYQ3dwWmJjN3FDTGdGUFlONiI7czo5OiJfcHJldmlvdXMiO2E6MTp7czozOiJ1cmwiO3M6Mzc6Imh0dHBzOi8vbWl1bWkubWltdW5kb2RpZ2l0YWwubXgvbG9naW4iO31zOjY6Il9mbGFzaCI7YToyOntzOjM6Im9sZCI7YTowOnt9czozOiJuZXciO2E6MDp7fX19',1777672564),('xdNikyAHW1ajTEPFxQsy3ss0EgQmboVjz3OIBHpr',NULL,'31.220.63.131','Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/44.0.2403.157 Safari/537.36','YTozOntzOjY6Il90b2tlbiI7czo0MDoiSFdYSGdmWjN4QjhGTlhkbHJQckZKNTdGa2dtV1JZU0loaldlYThuaiI7czo5OiJfcHJldmlvdXMiO2E6MTp7czozOiJ1cmwiO3M6Mzc6Imh0dHBzOi8vbWl1bWkubWltdW5kb2RpZ2l0YWwubXgvbG9naW4iO31zOjY6Il9mbGFzaCI7YToyOntzOjM6Im9sZCI7YTowOnt9czozOiJuZXciO2E6MDp7fX19',1777690563),('XHZhoaRE1c5QWkimWD9qY5ufib3yysIgB48Ymgkg',NULL,'31.220.63.131','Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/44.0.2403.157 Safari/537.36','YTozOntzOjY6Il90b2tlbiI7czo0MDoiMHYzUlZjYUx4bEFWUEJ2dVRvakZ6cmhsNkNpTjI4M1VNc0Q4VW80ZSI7czo5OiJfcHJldmlvdXMiO2E6MTp7czozOiJ1cmwiO3M6Mzc6Imh0dHBzOi8vbWl1bWkubWltdW5kb2RpZ2l0YWwubXgvbG9naW4iO31zOjY6Il9mbGFzaCI7YToyOntzOjM6Im9sZCI7YTowOnt9czozOiJuZXciO2E6MDp7fX19',1777711323),('xn3PHwDrGhfsuvkVe2LNtnlMRpaqya2coBnEgfja',NULL,'31.220.63.131','Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/44.0.2403.157 Safari/537.36','YTozOntzOjY6Il90b2tlbiI7czo0MDoibmkxNEtmMlY3VzdEVHloa051OUR1NEJWOWMwUXppVnMzYkVsVGRwdiI7czo5OiJfcHJldmlvdXMiO2E6MTp7czozOiJ1cmwiO3M6Mzc6Imh0dHBzOi8vbWl1bWkubWltdW5kb2RpZ2l0YWwubXgvbG9naW4iO31zOjY6Il9mbGFzaCI7YToyOntzOjM6Im9sZCI7YTowOnt9czozOiJuZXciO2E6MDp7fX19',1777664524),('xomoW8w3lQrxE4zXSyACdo0l1xSqUCyyuWn7NxJG',NULL,'31.220.63.131','Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/44.0.2403.157 Safari/537.36','YTozOntzOjY6Il90b2tlbiI7czo0MDoiUklaajRLY2FFZmZNUUNFSVFGRzFzV3FGY281a0MzMUZrUUZucjhDbyI7czo5OiJfcHJldmlvdXMiO2E6MTp7czozOiJ1cmwiO3M6MzE6Imh0dHBzOi8vbWl1bWkubWltdW5kb2RpZ2l0YWwubXgiO31zOjY6Il9mbGFzaCI7YToyOntzOjM6Im9sZCI7YTowOnt9czozOiJuZXciO2E6MDp7fX19',1777676411),('XZCX2vHaG6m7rax17X9HXBNgwwMyZvSagCWE2x8F',NULL,'31.220.63.131','Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/44.0.2403.157 Safari/537.36','YTozOntzOjY6Il90b2tlbiI7czo0MDoibGc0dmFlUkxPaW5saDluTHhLdUlvSXhKaDZXZVc3M0xWZ0d0TXUwZSI7czo5OiJfcHJldmlvdXMiO2E6MTp7czozOiJ1cmwiO3M6MzE6Imh0dHBzOi8vbWl1bWkubWltdW5kb2RpZ2l0YWwubXgiO31zOjY6Il9mbGFzaCI7YToyOntzOjM6Im9sZCI7YTowOnt9czozOiJuZXciO2E6MDp7fX19',1777654809),('xzHwTTk9SazIuKoxKqNPKNyG5cNk6hzq34PwrXQa',NULL,'31.220.63.131','Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/44.0.2403.157 Safari/537.36','YTozOntzOjY6Il90b2tlbiI7czo0MDoiYUtER3VlNENRZWxMekkxTUxJY0xOT2x4cFE0NEhJbHN4d2tnaUVxNyI7czo5OiJfcHJldmlvdXMiO2E6MTp7czozOiJ1cmwiO3M6Mzc6Imh0dHBzOi8vbWl1bWkubWltdW5kb2RpZ2l0YWwubXgvbG9naW4iO31zOjY6Il9mbGFzaCI7YToyOntzOjM6Im9sZCI7YTowOnt9czozOiJuZXciO2E6MDp7fX19',1777699683),('Y6c9QUFgsrSXaraTh85BwCecxUwJdNyoOSIuAL6p',NULL,'31.220.63.131','Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/44.0.2403.157 Safari/537.36','YTozOntzOjY6Il90b2tlbiI7czo0MDoiNGVZSEF3cDZPU3k3bVVPeTlFV25mUFdpRWxSbHRVNGFIb0djN1RUZiI7czo5OiJfcHJldmlvdXMiO2E6MTp7czozOiJ1cmwiO3M6MzE6Imh0dHBzOi8vbWl1bWkubWltdW5kb2RpZ2l0YWwubXgiO31zOjY6Il9mbGFzaCI7YToyOntzOjM6Im9sZCI7YTowOnt9czozOiJuZXciO2E6MDp7fX19',1777672813),('Yi6hpIuxsFjyR6hX1OrVEbvZHBwUBgCJRVeHBbYW',NULL,'31.220.63.131','Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/44.0.2403.157 Safari/537.36','YTozOntzOjY6Il90b2tlbiI7czo0MDoiaTN6VllaamZ0M1FzbzgxdjhBT3hOYVVwNlM5NDk2TzFCQVB2NDAxTiI7czo5OiJfcHJldmlvdXMiO2E6MTp7czozOiJ1cmwiO3M6MzE6Imh0dHBzOi8vbWl1bWkubWltdW5kb2RpZ2l0YWwubXgiO31zOjY6Il9mbGFzaCI7YToyOntzOjM6Im9sZCI7YTowOnt9czozOiJuZXciO2E6MDp7fX19',1777686123),('yidI8TlY9zP1Hmlzoidl7H9ysLucwiIfCnuqglU0',NULL,'31.220.63.131','Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/44.0.2403.157 Safari/537.36','YTozOntzOjY6Il90b2tlbiI7czo0MDoibzZ3R1JLS0ZoZTd0WkhpTDB6aFRITVFMZDBFYVprMWwxVEJ0S3VRbCI7czo5OiJfcHJldmlvdXMiO2E6MTp7czozOiJ1cmwiO3M6MzE6Imh0dHBzOi8vbWl1bWkubWltdW5kb2RpZ2l0YWwubXgiO31zOjY6Il9mbGFzaCI7YToyOntzOjM6Im9sZCI7YTowOnt9czozOiJuZXciO2E6MDp7fX19',1777691651),('YLbp14rJ4T6di2vrynH4vDmyXJ9RG7X9PLdQZf9A',NULL,'31.220.63.131','Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/44.0.2403.157 Safari/537.36','YTozOntzOjY6Il90b2tlbiI7czo0MDoiaGltWno5cFNCeXF0NndHbU5yakhLT2Zpbk80WDA0b1IzQ1FJSWVUcCI7czo5OiJfcHJldmlvdXMiO2E6MTp7czozOiJ1cmwiO3M6Mzc6Imh0dHBzOi8vbWl1bWkubWltdW5kb2RpZ2l0YWwubXgvbG9naW4iO31zOjY6Il9mbGFzaCI7YToyOntzOjM6Im9sZCI7YTowOnt9czozOiJuZXciO2E6MDp7fX19',1777735685),('YnEMTaqlPfBKSTEwiMGM1uV0AbE6TltAiAUJCqUJ',NULL,'31.220.63.131','Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/44.0.2403.157 Safari/537.36','YTozOntzOjY6Il90b2tlbiI7czo0MDoiWG41WmxRdFlkb2NJSE5uY212SzQxS3Y5dmZSalBRTDVzTHdBemhzcCI7czo5OiJfcHJldmlvdXMiO2E6MTp7czozOiJ1cmwiO3M6Mzc6Imh0dHBzOi8vbWl1bWkubWltdW5kb2RpZ2l0YWwubXgvbG9naW4iO31zOjY6Il9mbGFzaCI7YToyOntzOjM6Im9sZCI7YTowOnt9czozOiJuZXciO2E6MDp7fX19',1777674484),('ynGrwpcN0FcxfXNJnEN2Rd91nF1SabP9968GD5rK',NULL,'31.220.63.131','Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/44.0.2403.157 Safari/537.36','YTozOntzOjY6Il90b2tlbiI7czo0MDoiVjNwZFc5QU9QaXR6R0JJbEJ4am9GVEtoQWNTcTJpVXhUZTZ2UEJ3RiI7czo5OiJfcHJldmlvdXMiO2E6MTp7czozOiJ1cmwiO3M6MzE6Imh0dHBzOi8vbWl1bWkubWltdW5kb2RpZ2l0YWwubXgiO31zOjY6Il9mbGFzaCI7YToyOntzOjM6Im9sZCI7YTowOnt9czozOiJuZXciO2E6MDp7fX19',1777675325),('yQOUaNxMUDs9WfeN3vjoqlbCKm0KtD0bvdSrOo5W',NULL,'31.220.63.131','Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/44.0.2403.157 Safari/537.36','YTozOntzOjY6Il90b2tlbiI7czo0MDoiOGg0WU1HZW5uSUlpd1ZVWmZNaENHY1ZjbGtTbDM2RVdHWW1yUDk2byI7czo5OiJfcHJldmlvdXMiO2E6MTp7czozOiJ1cmwiO3M6MzE6Imh0dHBzOi8vbWl1bWkubWltdW5kb2RpZ2l0YWwubXgiO31zOjY6Il9mbGFzaCI7YToyOntzOjM6Im9sZCI7YTowOnt9czozOiJuZXciO2E6MDp7fX19',1777644007),('YXYIPCTfpFX7tD5oPxLUnFI4cNYj0conSuoMHcHZ',NULL,'31.220.63.131','Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/44.0.2403.157 Safari/537.36','YTozOntzOjY6Il90b2tlbiI7czo0MDoiMVlTVnFkbTdiQlMwaTdNY1h3WFJkaE9FVUpueENPaVNxbHhYVzJmRyI7czo5OiJfcHJldmlvdXMiO2E6MTp7czozOiJ1cmwiO3M6Mzc6Imh0dHBzOi8vbWl1bWkubWltdW5kb2RpZ2l0YWwubXgvbG9naW4iO31zOjY6Il9mbGFzaCI7YToyOntzOjM6Im9sZCI7YTowOnt9czozOiJuZXciO2E6MDp7fX19',1777718523),('Z1J9ct2Epbcoau3CelBJwX78LUZ87V31ZkOv4JMD',NULL,'31.220.63.131','Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/44.0.2403.157 Safari/537.36','YTozOntzOjY6Il90b2tlbiI7czo0MDoiSHhKM29aUEl1UFZ1WVkxM3QyMUdYczVuSVU1aGdwME96aUFZN1RNTiI7czo5OiJfcHJldmlvdXMiO2E6MTp7czozOiJ1cmwiO3M6Mzc6Imh0dHBzOi8vbWl1bWkubWltdW5kb2RpZ2l0YWwubXgvbG9naW4iO31zOjY6Il9mbGFzaCI7YToyOntzOjM6Im9sZCI7YTowOnt9czozOiJuZXciO2E6MDp7fX19',1777667287),('z4dYHdmSv0TIPjyySk9GxYkfl5FL2TzFoOkCMBsi',NULL,'31.220.63.131','Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/44.0.2403.157 Safari/537.36','YTozOntzOjY6Il90b2tlbiI7czo0MDoia0pBcEdkTGt0TVA5bFlvVEpkeE9xODd3eldzcXdvUlNZMHo2VlRScSI7czo5OiJfcHJldmlvdXMiO2E6MTp7czozOiJ1cmwiO3M6MzE6Imh0dHBzOi8vbWl1bWkubWltdW5kb2RpZ2l0YWwubXgiO31zOjY6Il9mbGFzaCI7YToyOntzOjM6Im9sZCI7YTowOnt9czozOiJuZXciO2E6MDp7fX19',1777690563),('z6BLV8eamXYhBrRymLktYeZO2eEjey7O5hHfMUpM',NULL,'31.220.63.131','Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/44.0.2403.157 Safari/537.36','YTozOntzOjY6Il90b2tlbiI7czo0MDoiMjkyV2RGS0lPREtOMklmcWl3em1vM0x4WmI4MDBrVUtuMExrZHFGeiI7czo5OiJfcHJldmlvdXMiO2E6MTp7czozOiJ1cmwiO3M6MzE6Imh0dHBzOi8vbWl1bWkubWltdW5kb2RpZ2l0YWwubXgiO31zOjY6Il9mbGFzaCI7YToyOntzOjM6Im9sZCI7YTowOnt9czozOiJuZXciO2E6MDp7fX19',1777656484),('ZBrIO4GkDaTI3PAC4pljNQc7pzPHY24MuKm1tJgU',NULL,'31.220.63.131','Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/44.0.2403.157 Safari/537.36','YTozOntzOjY6Il90b2tlbiI7czo0MDoiek9iVm9rZGkyaXFmR2M4U21PWTRPcUpzS2tzMHlYRGFCMzZZUEREeSI7czo5OiJfcHJldmlvdXMiO2E6MTp7czozOiJ1cmwiO3M6MzE6Imh0dHBzOi8vbWl1bWkubWltdW5kb2RpZ2l0YWwubXgiO31zOjY6Il9mbGFzaCI7YToyOntzOjM6Im9sZCI7YTowOnt9czozOiJuZXciO2E6MDp7fX19',1777678084),('zdxrZ8JfSEYeppm7hE6zfdN6DUE7na8Z13yzqZb3',NULL,'31.220.63.131','Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/44.0.2403.157 Safari/537.36','YTozOntzOjY6Il90b2tlbiI7czo0MDoid3IxMk11T2lOdG4zaU5yOXVRQmk4Z3dyYmZScFh3eGNWSkQyeENVWSI7czo5OiJfcHJldmlvdXMiO2E6MTp7czozOiJ1cmwiO3M6Mzc6Imh0dHBzOi8vbWl1bWkubWltdW5kb2RpZ2l0YWwubXgvbG9naW4iO31zOjY6Il9mbGFzaCI7YToyOntzOjM6Im9sZCI7YTowOnt9czozOiJuZXciO2E6MDp7fX19',1777727645),('ZEa9iCBUyVl0MdsBTZlTNSQdo2aInRnhSRT5sLa3',NULL,'31.220.63.131','Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/44.0.2403.157 Safari/537.36','YTozOntzOjY6Il90b2tlbiI7czo0MDoibEpJRVpwVm5NbG1VWXdPRmhUSTc3NzlXeDg2eEkwSmM0ek9lVElLWCI7czo5OiJfcHJldmlvdXMiO2E6MTp7czozOiJ1cmwiO3M6Mzc6Imh0dHBzOi8vbWl1bWkubWltdW5kb2RpZ2l0YWwubXgvbG9naW4iO31zOjY6Il9mbGFzaCI7YToyOntzOjM6Im9sZCI7YTowOnt9czozOiJuZXciO2E6MDp7fX19',1777706045),('ZF7wliCyPgT1C0KuINL620U6hTDErHegHDH14X4s',NULL,'31.220.63.131','Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/44.0.2403.157 Safari/537.36','YTozOntzOjY6Il90b2tlbiI7czo0MDoiSFljb2I0WmV6eFFDUm5VdWh0UVNMNGVtcFZmVElTb0dzQzVSWkFlcCI7czo5OiJfcHJldmlvdXMiO2E6MTp7czozOiJ1cmwiO3M6MzE6Imh0dHBzOi8vbWl1bWkubWltdW5kb2RpZ2l0YWwubXgiO31zOjY6Il9mbGFzaCI7YToyOntzOjM6Im9sZCI7YTowOnt9czozOiJuZXciO2E6MDp7fX19',1777640163),('ZIf6RWTVsinoEDzYLfive4jpQ5yiVfEXbjWtINc9',NULL,'31.220.63.131','Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/44.0.2403.157 Safari/537.36','YTozOntzOjY6Il90b2tlbiI7czo0MDoiVExkUjZ6V0hLUFltU1VPUWVHMjRwc3NlalB1VDFneWtTdkh5T2JXRSI7czo5OiJfcHJldmlvdXMiO2E6MTp7czozOiJ1cmwiO3M6MzE6Imh0dHBzOi8vbWl1bWkubWltdW5kb2RpZ2l0YWwubXgiO31zOjY6Il9mbGFzaCI7YToyOntzOjM6Im9sZCI7YTowOnt9czozOiJuZXciO2E6MDp7fX19',1777689725),('zIZmmNBTFQTRkrzJwtxGJiPuTDiUpwI7YDF8QYFM',NULL,'31.220.63.131','Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/44.0.2403.157 Safari/537.36','YTozOntzOjY6Il90b2tlbiI7czo0MDoiTFpsNU5NbDE0bjdwaDZzUVRFbmJZUWpXV2Nra0U2UWIyS0JMQ3RBMSI7czo5OiJfcHJldmlvdXMiO2E6MTp7czozOiJ1cmwiO3M6MzE6Imh0dHBzOi8vbWl1bWkubWltdW5kb2RpZ2l0YWwubXgiO31zOjY6Il9mbGFzaCI7YToyOntzOjM6Im9sZCI7YTowOnt9czozOiJuZXciO2E6MDp7fX19',1777659243),('ZJNsEDU8eQVF1wI2kFGAT57CcmchoxiZpyJD73Bt',NULL,'31.220.63.131','Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/44.0.2403.157 Safari/537.36','YTozOntzOjY6Il90b2tlbiI7czo0MDoiT0N2dzk5dW9rTVdsWmU5QU1VNEoyTURRdzZpNVZ2dFlOeTVZSzE2WCI7czo5OiJfcHJldmlvdXMiO2E6MTp7czozOiJ1cmwiO3M6Mzc6Imh0dHBzOi8vbWl1bWkubWltdW5kb2RpZ2l0YWwubXgvbG9naW4iO31zOjY6Il9mbGFzaCI7YToyOntzOjM6Im9sZCI7YTowOnt9czozOiJuZXciO2E6MDp7fX19',1777729326),('zSKEERYMVr7I4ZjirIc6qzDSLafSQoACX7NHlzzw',NULL,'31.220.63.131','Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/44.0.2403.157 Safari/537.36','YTozOntzOjY6Il90b2tlbiI7czo0MDoiSkRVcWVRQ0xOTDJpMVhTQ0pzZFJZMnQ2eWdETWk5RUhpWWVZclp4bCI7czo5OiJfcHJldmlvdXMiO2E6MTp7czozOiJ1cmwiO3M6Mzc6Imh0dHBzOi8vbWl1bWkubWltdW5kb2RpZ2l0YWwubXgvbG9naW4iO31zOjY6Il9mbGFzaCI7YToyOntzOjM6Im9sZCI7YTowOnt9czozOiJuZXciO2E6MDp7fX19',1777682524),('zTaGYRVPMw4mrNLVG13e7B3f1afR1fCNa3HGyADo',NULL,'31.220.63.131','Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/44.0.2403.157 Safari/537.36','YTozOntzOjY6Il90b2tlbiI7czo0MDoicXpFNEdCSFN3dE5qMHFjZGNKNG9TT3VrdWkxdTZacWMxcWV0aHVXTCI7czo5OiJfcHJldmlvdXMiO2E6MTp7czozOiJ1cmwiO3M6MzE6Imh0dHBzOi8vbWl1bWkubWltdW5kb2RpZ2l0YWwubXgiO31zOjY6Il9mbGFzaCI7YToyOntzOjM6Im9sZCI7YTowOnt9czozOiJuZXciO2E6MDp7fX19',1777672564),('ZZtjadHz7w3xrOr3X8yvUy7GpECF4ydqhV8fD9vr',NULL,'31.220.63.131','Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/44.0.2403.157 Safari/537.36','YTozOntzOjY6Il90b2tlbiI7czo0MDoiQVJzaEpEQUtDWkVMOXRJeGNuMU9jRVM3RUFaUW56RDloakJkd1V2VSI7czo5OiJfcHJldmlvdXMiO2E6MTp7czozOiJ1cmwiO3M6Mzc6Imh0dHBzOi8vbWl1bWkubWltdW5kb2RpZ2l0YWwubXgvbG9naW4iO31zOjY6Il9mbGFzaCI7YToyOntzOjM6Im9sZCI7YTowOnt9czozOiJuZXciO2E6MDp7fX19',1777673646);
/*!40000 ALTER TABLE `sessions` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `subtopics`
--

DROP TABLE IF EXISTS `subtopics`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `subtopics` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `topic_id` bigint unsigned NOT NULL,
  `title` varchar(150) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `description` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci,
  `file_path` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `order` int NOT NULL DEFAULT '0',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `subtopics_topic_id_foreign` (`topic_id`),
  CONSTRAINT `subtopics_topic_id_foreign` FOREIGN KEY (`topic_id`) REFERENCES `topics` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `subtopics`
--

LOCK TABLES `subtopics` WRITE;
/*!40000 ALTER TABLE `subtopics` DISABLE KEYS */;
/*!40000 ALTER TABLE `subtopics` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `targetables`
--

DROP TABLE IF EXISTS `targetables`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `targetables` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `course_id` bigint unsigned NOT NULL,
  `targetable_type` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `targetable_id` bigint unsigned NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `targetables_course_id_foreign` (`course_id`),
  KEY `targetables_targetable_type_targetable_id_index` (`targetable_type`,`targetable_id`),
  CONSTRAINT `targetables_course_id_foreign` FOREIGN KEY (`course_id`) REFERENCES `courses` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `targetables`
--

LOCK TABLES `targetables` WRITE;
/*!40000 ALTER TABLE `targetables` DISABLE KEYS */;
INSERT INTO `targetables` VALUES (1,1,'App\\Models\\Users\\Department',1,NULL,NULL);
/*!40000 ALTER TABLE `targetables` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `teacher_careers`
--

DROP TABLE IF EXISTS `teacher_careers`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `teacher_careers` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `user_id` bigint unsigned NOT NULL,
  `career_id` bigint unsigned NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `teacher_careers_user_id_career_id_unique` (`user_id`,`career_id`),
  KEY `teacher_careers_career_id_foreign` (`career_id`),
  CONSTRAINT `teacher_careers_career_id_foreign` FOREIGN KEY (`career_id`) REFERENCES `careers` (`id`) ON DELETE CASCADE,
  CONSTRAINT `teacher_careers_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=7 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `teacher_careers`
--

LOCK TABLES `teacher_careers` WRITE;
/*!40000 ALTER TABLE `teacher_careers` DISABLE KEYS */;
/*!40000 ALTER TABLE `teacher_careers` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `titulacion_documentos`
--

DROP TABLE IF EXISTS `titulacion_documentos`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `titulacion_documentos` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `user_id` bigint unsigned NOT NULL,
  `nombre_documento` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `descripcion` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci,
  `archivo_path` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `mime_type` varchar(120) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `tamano_bytes` bigint unsigned DEFAULT NULL,
  `uploaded_by` bigint unsigned DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `titulacion_documentos_uploaded_by_foreign` (`uploaded_by`),
  KEY `titulacion_documentos_user_id_created_at_index` (`user_id`,`created_at`),
  CONSTRAINT `titulacion_documentos_uploaded_by_foreign` FOREIGN KEY (`uploaded_by`) REFERENCES `users` (`id`) ON DELETE SET NULL,
  CONSTRAINT `titulacion_documentos_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=3 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `titulacion_documentos`
--

LOCK TABLES `titulacion_documentos` WRITE;
/*!40000 ALTER TABLE `titulacion_documentos` DISABLE KEYS */;
/*!40000 ALTER TABLE `titulacion_documentos` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `topics`
--

DROP TABLE IF EXISTS `topics`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `topics` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `course_id` bigint unsigned NOT NULL,
  `title` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `description` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci,
  `file_path` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `topics_course_id_foreign` (`course_id`),
  CONSTRAINT `topics_course_id_foreign` FOREIGN KEY (`course_id`) REFERENCES `courses` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=3 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `topics`
--

LOCK TABLES `topics` WRITE;
/*!40000 ALTER TABLE `topics` DISABLE KEYS */;
INSERT INTO `topics` VALUES (1,1,'Tema 1','Este es el tema 1','topic_files/PXj9vUYZMcTvrCgaJR05Kfd8Etrkw2sSj59dSoQg.pdf','2025-12-06 18:54:34','2025-12-06 18:54:34'),(2,1,'Subtema 1','Este es el subtema 1',NULL,'2025-12-06 18:54:56','2025-12-06 18:54:56');
/*!40000 ALTER TABLE `topics` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `user_roles_institution`
--

DROP TABLE IF EXISTS `user_roles_institution`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `user_roles_institution` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `user_id` bigint unsigned NOT NULL,
  `role_id` bigint unsigned NOT NULL,
  `is_active` tinyint(1) NOT NULL DEFAULT '1',
  `institution_id` bigint unsigned NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `user_roles_institution_user_id_role_id_institution_id_unique` (`user_id`,`role_id`,`institution_id`),
  KEY `user_roles_institution_user_id_index` (`user_id`),
  KEY `user_roles_institution_role_id_index` (`role_id`),
  KEY `user_roles_institution_institution_id_index` (`institution_id`),
  CONSTRAINT `user_roles_institution_institution_id_foreign` FOREIGN KEY (`institution_id`) REFERENCES `institutions` (`id`) ON DELETE CASCADE,
  CONSTRAINT `user_roles_institution_role_id_foreign` FOREIGN KEY (`role_id`) REFERENCES `roles` (`id`) ON DELETE CASCADE,
  CONSTRAINT `user_roles_institution_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=137 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `user_roles_institution`
--

LOCK TABLES `user_roles_institution` WRITE;
/*!40000 ALTER TABLE `user_roles_institution` DISABLE KEYS */;
INSERT INTO `user_roles_institution` VALUES (2,1,1,1,2,'2026-01-26 19:29:07','2026-01-26 19:29:07'),(3,1,1,1,3,'2026-01-26 19:29:07','2026-01-26 19:29:07'),(4,1,1,1,4,'2026-01-26 19:29:07','2026-01-26 19:29:07'),(48,1,9,1,4,'2026-03-11 18:04:05','2026-03-11 18:04:05'),(75,1,1,1,1,'2026-03-24 15:19:35','2026-03-24 15:19:35'),(134,105,1,1,4,'2026-04-24 20:24:43','2026-04-24 20:24:43'),(135,106,8,1,4,'2026-04-27 22:25:32','2026-04-27 22:25:32'),(136,107,7,1,4,'2026-04-27 22:30:07','2026-04-27 22:30:07');
/*!40000 ALTER TABLE `user_roles_institution` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `users`
--

DROP TABLE IF EXISTS `users`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `users` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `nombre` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `apellido_paterno` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `apellido_materno` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `email` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `password` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `is_active` tinyint(1) NOT NULL DEFAULT '1',
  `RFC` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `curp` varchar(18) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `telefono` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `fecha_nacimiento` date DEFAULT NULL,
  `edad` int DEFAULT NULL,
  `role_id` bigint unsigned DEFAULT NULL,
  `address_id` bigint unsigned DEFAULT NULL,
  `institution_id` bigint unsigned NOT NULL,
  `department_id` bigint unsigned DEFAULT NULL,
  `workstation_id` bigint unsigned DEFAULT NULL,
  `remember_token` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `users_rfc_unique` (`RFC`),
  UNIQUE KEY `users_email_unique` (`email`),
  KEY `users_role_id_foreign` (`role_id`),
  KEY `users_address_id_foreign` (`address_id`),
  KEY `users_institution_id_foreign` (`institution_id`),
  KEY `users_department_id_foreign` (`department_id`),
  KEY `users_workstation_id_foreign` (`workstation_id`),
  CONSTRAINT `users_address_id_foreign` FOREIGN KEY (`address_id`) REFERENCES `addresses` (`id`) ON DELETE SET NULL,
  CONSTRAINT `users_department_id_foreign` FOREIGN KEY (`department_id`) REFERENCES `departments` (`id`),
  CONSTRAINT `users_institution_id_foreign` FOREIGN KEY (`institution_id`) REFERENCES `institutions` (`id`) ON DELETE CASCADE,
  CONSTRAINT `users_role_id_foreign` FOREIGN KEY (`role_id`) REFERENCES `roles` (`id`),
  CONSTRAINT `users_workstation_id_foreign` FOREIGN KEY (`workstation_id`) REFERENCES `workstations` (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=108 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `users`
--

LOCK TABLES `users` WRITE;
/*!40000 ALTER TABLE `users` DISABLE KEYS */;
INSERT INTO `users` VALUES (1,'Esteban','Rivers','Molina','master@UMI.com','$2y$12$5ZFGv9L4o8Mgip9/.myAtOPh7/S/nqiEeFd.I3ILR1kHc92EXOtY2',1,'XAXX010101000',NULL,NULL,NULL,NULL,1,NULL,1,2,NULL,NULL,'2025-12-06 18:21:36','2026-03-24 15:19:35',NULL),(105,'Gonzalo','Esparza','Soto','gonzalo@mail.com','$2y$10$DSq1q9EHlrq4IsbcsNnGDuC0ZCp4HQlL2WV9Nx742ZywQiG/wNzgm',1,'EASG690723172',NULL,NULL,NULL,NULL,1,NULL,4,NULL,NULL,NULL,'2026-04-24 19:57:55','2026-04-24 20:12:30',NULL),(106,'Jaime','Huerta','Lopez',NULL,'$2y$12$sU1B3NahrKve1fQDW.LrLeCOBekQspST0dCdaX8MMeHdVxrLLaKpu',1,'SECA237645THG',NULL,NULL,NULL,NULL,8,NULL,4,NULL,NULL,NULL,'2026-04-27 22:25:32','2026-04-27 22:25:32',NULL),(107,'ANELIS','SALMERON','CARDENAS','calidadpalacio@mundoimperial.com','$2y$12$q6pxou9FYTDFW3mth88Iku8EXlxxXCmRkkWr.hXz1dketSaRPRWpm',1,'SACA009007G3H','SACA001008MGRLRNA0','2343234564','2005-10-11',20,7,1,4,NULL,NULL,NULL,'2026-04-27 22:30:07','2026-04-27 22:36:59',NULL);
/*!40000 ALTER TABLE `users` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `workstations`
--

DROP TABLE IF EXISTS `workstations`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `workstations` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `institution_id` bigint unsigned NOT NULL,
  `department_id` bigint unsigned NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `workstations_institution_id_foreign` (`institution_id`),
  KEY `workstations_department_id_foreign` (`department_id`),
  CONSTRAINT `workstations_department_id_foreign` FOREIGN KEY (`department_id`) REFERENCES `departments` (`id`) ON DELETE CASCADE,
  CONSTRAINT `workstations_institution_id_foreign` FOREIGN KEY (`institution_id`) REFERENCES `institutions` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=5 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `workstations`
--

LOCK TABLES `workstations` WRITE;
/*!40000 ALTER TABLE `workstations` DISABLE KEYS */;
INSERT INTO `workstations` VALUES (1,'Reclutador',1,1,'2025-12-06 18:21:35','2025-12-06 18:21:35'),(2,'Analista de Nomina',1,1,'2025-12-06 18:21:35','2025-12-06 18:21:35'),(3,'Auxiliar “A”',1,72,'2026-03-19 17:48:02','2026-03-19 17:48:02');
/*!40000 ALTER TABLE `workstations` ENABLE KEYS */;
UNLOCK TABLES;
/*!40103 SET TIME_ZONE=@OLD_TIME_ZONE */;

/*!40101 SET SQL_MODE=@OLD_SQL_MODE */;
/*!40014 SET FOREIGN_KEY_CHECKS=@OLD_FOREIGN_KEY_CHECKS */;
/*!40014 SET UNIQUE_CHECKS=@OLD_UNIQUE_CHECKS */;
/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
/*!40111 SET SQL_NOTES=@OLD_SQL_NOTES */;

-- Dump completed on 2026-05-02  9:52:15
