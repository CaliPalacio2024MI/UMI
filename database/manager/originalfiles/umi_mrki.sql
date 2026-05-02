CREATE DATABASE  IF NOT EXISTS `umi_mrki` /*!40100 DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_0900_ai_ci */ /*!80016 DEFAULT ENCRYPTION='N' */;
USE `umi_mrki`;
-- MySQL dump 10.13  Distrib 8.0.36, for Win64 (x86_64)
--
-- Host: 127.0.0.1    Database: umi_mrki
-- ------------------------------------------------------
-- Server version	8.0.36

/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!50503 SET NAMES utf8 */;
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
  `departamento` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `anios_activos` tinyint unsigned DEFAULT NULL,
  `status` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `matricula` varchar(20) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `documentoSEP_path` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `is_anfitrion` tinyint(1) NOT NULL DEFAULT '0',
  `doc_acta_nacimiento` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `doc_certificado_prepa` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `doc_curp` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `doc_ine` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `rol` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin,
  `documentos` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `doc_acta_rechazado` tinyint(1) NOT NULL DEFAULT '0',
  `doc_certificado_rechazado` tinyint(1) NOT NULL DEFAULT '0',
  `doc_curp_rechazado` tinyint(1) NOT NULL DEFAULT '0',
  `doc_ine_rechazado` tinyint(1) NOT NULL DEFAULT '0',
  `doc_ficha_pago` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `doc_factura_xml` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
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
INSERT INTO `academic_profiles` VALUES (1,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,0,NULL,NULL,NULL,NULL,NULL,NULL,'2026-03-13 21:59:02','2026-03-13 21:59:02',0,0,0,0,NULL,NULL,0,0),(2,NULL,NULL,1,NULL,NULL,'Aspirante',NULL,NULL,0,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,0,0,0,0,NULL,NULL,0,0),(3,NULL,NULL,1,NULL,NULL,'Aspirante',NULL,NULL,0,NULL,NULL,NULL,NULL,NULL,NULL,'2025-12-06 18:21:37','2025-12-06 18:21:37',0,0,0,0,NULL,NULL,0,0),(4,NULL,NULL,1,NULL,NULL,'Aspirante',NULL,NULL,0,NULL,NULL,NULL,NULL,NULL,NULL,'2025-12-06 18:21:37','2025-12-06 18:21:37',0,0,0,0,NULL,NULL,0,0),(5,NULL,NULL,1,NULL,NULL,'Aspirante',NULL,NULL,0,NULL,NULL,NULL,NULL,NULL,NULL,'2025-12-06 18:21:37','2025-12-06 18:21:37',0,0,0,0,NULL,NULL,0,0),(6,NULL,NULL,1,NULL,NULL,'Aspirante',NULL,NULL,0,NULL,NULL,NULL,NULL,NULL,NULL,'2025-12-06 18:21:38','2025-12-06 18:21:38',0,0,0,0,NULL,NULL,0,0),(7,NULL,NULL,1,NULL,NULL,'Aspirante',NULL,NULL,0,NULL,NULL,NULL,NULL,NULL,NULL,'2025-12-06 18:21:38','2025-12-06 18:21:38',0,0,0,0,NULL,NULL,0,0),(8,NULL,NULL,1,NULL,NULL,'Aspirante',NULL,NULL,0,NULL,NULL,NULL,NULL,NULL,NULL,'2025-12-06 18:21:39','2025-12-06 18:21:39',0,0,0,0,NULL,NULL,0,0),(9,NULL,NULL,1,NULL,NULL,'Alumno Activo','2025-SYS-001',NULL,0,NULL,NULL,NULL,NULL,NULL,NULL,'2025-12-06 18:21:39','2025-12-06 18:21:39',0,0,0,0,NULL,NULL,0,0),(10,NULL,NULL,1,NULL,NULL,'Alumno Activo','2025-SYS-002',NULL,0,NULL,NULL,NULL,NULL,NULL,NULL,'2025-12-06 18:21:40','2025-12-06 18:21:40',0,0,0,0,NULL,NULL,0,0),(11,NULL,NULL,1,NULL,NULL,'Alumno Activo','2025-SYS-003',NULL,0,NULL,NULL,NULL,NULL,NULL,NULL,'2025-12-06 18:21:40','2025-12-06 18:21:40',0,0,0,0,NULL,NULL,0,0),(16,NULL,NULL,NULL,'304',NULL,'Activo',NULL,NULL,0,NULL,NULL,NULL,NULL,NULL,NULL,'2026-01-30 19:15:22','2026-01-30 19:15:22',0,0,0,0,NULL,NULL,0,0),(19,NULL,NULL,NULL,'308',NULL,'Activo',NULL,NULL,0,NULL,NULL,NULL,NULL,NULL,NULL,'2026-01-30 19:41:59','2026-01-30 19:48:58',0,0,0,0,NULL,NULL,0,0),(23,NULL,NULL,NULL,'098',NULL,'Activo',NULL,NULL,0,NULL,NULL,NULL,NULL,NULL,NULL,'2026-01-30 23:04:11','2026-02-05 18:47:50',0,0,0,0,NULL,NULL,0,0),(25,NULL,NULL,NULL,'100',NULL,'Activo',NULL,NULL,0,NULL,NULL,NULL,NULL,NULL,NULL,'2026-01-31 00:20:37','2026-01-31 00:20:37',0,0,0,0,NULL,NULL,0,0),(33,NULL,NULL,NULL,'099',NULL,'Activo',NULL,NULL,0,NULL,NULL,NULL,NULL,NULL,NULL,'2026-02-05 20:44:22','2026-02-05 20:44:22',0,0,0,0,NULL,NULL,0,0),(35,NULL,NULL,NULL,NULL,NULL,'Activo',NULL,NULL,0,NULL,NULL,NULL,NULL,NULL,NULL,'2026-02-18 21:29:56','2026-02-18 22:02:57',0,0,0,0,NULL,NULL,0,0),(36,NULL,NULL,NULL,NULL,NULL,'Activo',NULL,NULL,0,NULL,NULL,NULL,NULL,NULL,NULL,'2026-02-18 21:40:48','2026-02-18 21:40:48',0,0,0,0,NULL,NULL,0,0),(37,NULL,NULL,NULL,NULL,NULL,'Activo',NULL,NULL,0,NULL,NULL,NULL,NULL,NULL,NULL,'2026-02-18 21:43:47','2026-02-18 21:43:47',0,0,0,0,NULL,NULL,0,0),(38,NULL,NULL,NULL,NULL,NULL,'Activo',NULL,NULL,0,NULL,NULL,NULL,NULL,NULL,NULL,'2026-02-18 21:45:45','2026-02-18 21:46:02',0,0,0,0,NULL,NULL,0,0),(39,NULL,NULL,NULL,NULL,NULL,'Activo',NULL,NULL,0,NULL,NULL,NULL,NULL,NULL,NULL,'2026-02-18 21:48:59','2026-02-18 21:48:59',0,0,0,0,NULL,NULL,0,0),(40,NULL,NULL,NULL,NULL,NULL,'Activo',NULL,NULL,0,NULL,NULL,NULL,NULL,NULL,NULL,'2026-02-18 21:50:43','2026-02-18 21:50:43',0,0,0,0,NULL,NULL,0,0),(42,NULL,NULL,1,NULL,NULL,'Aspirante',NULL,NULL,1,NULL,NULL,NULL,NULL,NULL,NULL,'2026-02-20 05:06:23','2026-02-20 05:06:23',0,0,0,0,NULL,NULL,0,0),(43,NULL,NULL,1,NULL,NULL,'Aspirante',NULL,NULL,1,NULL,NULL,NULL,NULL,NULL,NULL,'2026-02-20 05:07:45','2026-02-20 05:07:45',0,0,0,0,NULL,NULL,0,0),(44,NULL,NULL,3,NULL,NULL,'Aspirante',NULL,NULL,1,NULL,NULL,NULL,NULL,NULL,NULL,'2026-02-20 05:47:21','2026-02-20 07:12:43',0,0,0,0,NULL,NULL,0,0),(45,NULL,NULL,8,NULL,NULL,'Aspirante',NULL,NULL,1,NULL,NULL,NULL,NULL,NULL,NULL,'2026-02-20 05:51:35','2026-02-20 07:12:34',0,0,0,0,NULL,NULL,0,0),(46,NULL,NULL,3,NULL,NULL,'Aspirante',NULL,NULL,1,NULL,NULL,NULL,NULL,NULL,NULL,'2026-02-20 06:00:18','2026-02-20 07:12:28',0,0,0,0,NULL,NULL,0,0),(47,NULL,NULL,1,NULL,NULL,'Aspirante',NULL,NULL,1,NULL,NULL,NULL,NULL,NULL,NULL,'2026-02-20 06:02:45','2026-02-20 07:19:37',0,0,0,0,NULL,NULL,0,0),(48,NULL,NULL,4,NULL,NULL,'Aspirante',NULL,NULL,1,NULL,NULL,NULL,NULL,NULL,NULL,'2026-02-20 06:06:28','2026-02-20 07:12:14',0,0,0,0,NULL,NULL,0,0),(49,NULL,NULL,5,NULL,NULL,'Aspirante',NULL,NULL,1,NULL,NULL,NULL,NULL,NULL,NULL,'2026-02-20 06:08:41','2026-02-20 07:12:10',0,0,0,0,NULL,NULL,0,0),(50,NULL,NULL,1,NULL,NULL,'Alumno Inactivo',NULL,NULL,0,NULL,NULL,NULL,NULL,NULL,NULL,'2026-03-05 02:13:25','2026-03-05 02:13:25',0,0,0,0,NULL,NULL,0,0),(51,NULL,NULL,1,NULL,NULL,'Alumno Inactivo',NULL,NULL,0,NULL,NULL,NULL,NULL,NULL,NULL,'2026-03-05 02:58:12','2026-03-05 02:58:12',0,0,0,0,NULL,NULL,0,0),(52,NULL,NULL,1,NULL,NULL,'Alumno Inactivo',NULL,NULL,0,NULL,NULL,NULL,NULL,NULL,NULL,'2026-03-10 18:55:33','2026-03-10 18:55:33',0,0,0,0,NULL,NULL,0,0),(53,NULL,NULL,1,NULL,NULL,'Alumno Inactivo',NULL,NULL,0,NULL,NULL,NULL,NULL,NULL,NULL,'2026-03-10 18:57:40','2026-03-10 18:57:40',0,0,0,0,NULL,NULL,0,0),(54,NULL,NULL,1,NULL,NULL,'Alumno Inactivo',NULL,NULL,0,NULL,NULL,NULL,NULL,NULL,NULL,'2026-03-10 18:58:27','2026-03-26 18:17:48',0,0,0,0,NULL,NULL,0,0),(57,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,0,NULL,NULL,NULL,NULL,NULL,NULL,'2026-03-13 22:11:41','2026-03-13 22:11:41',0,0,0,0,NULL,NULL,0,0),(58,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,0,NULL,NULL,NULL,NULL,NULL,NULL,'2026-03-13 22:28:25','2026-03-13 22:28:25',0,0,0,0,NULL,NULL,0,0),(59,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,0,NULL,NULL,NULL,NULL,NULL,NULL,'2026-03-13 22:29:02','2026-03-13 22:29:02',0,0,0,0,NULL,NULL,0,0),(62,NULL,NULL,1,NULL,NULL,'Alumno Inactivo',NULL,NULL,0,NULL,NULL,NULL,NULL,NULL,NULL,'2026-03-23 22:28:58','2026-03-23 22:28:58',0,0,0,0,NULL,NULL,0,0),(63,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,0,NULL,NULL,NULL,NULL,NULL,NULL,'2026-03-24 15:34:09','2026-03-24 15:34:09',0,0,0,0,NULL,NULL,0,0),(64,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,0,NULL,NULL,NULL,NULL,NULL,NULL,'2026-03-24 15:52:33','2026-03-24 15:52:33',0,0,0,0,NULL,NULL,0,0),(65,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,0,NULL,NULL,NULL,NULL,NULL,NULL,'2026-03-24 21:54:18','2026-03-24 21:54:18',0,0,0,0,NULL,NULL,0,0),(66,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,0,NULL,NULL,NULL,NULL,NULL,NULL,'2026-03-26 18:18:34','2026-03-26 18:18:34',0,0,0,0,NULL,NULL,0,0),(67,NULL,NULL,1,NULL,NULL,'Alumno',NULL,NULL,0,NULL,NULL,NULL,NULL,NULL,NULL,'2026-03-26 18:37:51','2026-03-26 21:33:02',0,0,0,0,NULL,NULL,0,0),(68,NULL,NULL,1,NULL,NULL,'Alumno',NULL,NULL,0,NULL,NULL,NULL,NULL,NULL,NULL,'2026-03-26 21:53:13','2026-03-30 00:43:30',0,0,0,0,NULL,NULL,0,0),(69,NULL,NULL,1,NULL,NULL,'Alumno',NULL,NULL,0,NULL,NULL,NULL,NULL,NULL,NULL,'2026-03-30 01:06:02','2026-03-30 04:02:11',0,0,0,0,NULL,NULL,0,0),(70,NULL,NULL,1,NULL,NULL,'Alumno',NULL,NULL,0,NULL,NULL,NULL,NULL,NULL,NULL,'2026-03-30 04:10:56','2026-03-30 04:13:41',0,0,0,0,NULL,NULL,0,0),(71,NULL,NULL,1,NULL,NULL,'Alumno',NULL,NULL,0,NULL,NULL,NULL,NULL,NULL,NULL,'2026-03-30 05:01:26','2026-03-30 06:08:30',0,0,0,0,NULL,NULL,0,0),(72,NULL,NULL,1,NULL,NULL,'Alumno',NULL,NULL,0,NULL,NULL,NULL,NULL,NULL,NULL,'2026-03-30 05:19:17','2026-03-30 07:54:05',0,0,0,0,NULL,NULL,0,0),(73,NULL,NULL,1,NULL,NULL,'Alumno',NULL,NULL,0,NULL,NULL,NULL,NULL,NULL,NULL,'2026-03-30 08:14:25','2026-03-30 08:48:01',0,0,0,0,NULL,NULL,0,0),(74,NULL,NULL,1,NULL,NULL,'Alumno',NULL,NULL,0,'documentos/74/expediente/xeeu2TqlRzzhuWg96BtdbE44ZoVJAfbXQXdTP4tf.pdf','documentos/74/expediente/DqcQbBsEXoJ7OBUc4wDthyfbrxRIE4QIIJufcXNo.pdf','documentos/74/expediente/YtJfIhnYneMg4YV3TYDwVI3DUwQUABGFVeUXv8hP.pdf','documentos/74/expediente/SCM8uN60ejuuosJYWGIaJkvCYCvEwgSKTI8cQhtV.pdf',NULL,NULL,'2026-03-30 23:07:20','2026-04-02 19:16:42',0,0,0,0,'facturas/74/U6wL55Of6h758jged4eq9rtLEh3mxunFyyknsLqv.pdf','facturas/74/4Hpki8fYFkQHHOIb4vZMr1Htl1LBypEHoPKg12mv.pdf',0,0),(75,NULL,NULL,1,NULL,NULL,'Alumno',NULL,NULL,0,NULL,'documentos/75/expediente/ZeL9sOz2If9G1VfcQrNpbLOUoO3xZeGKRlco862e.pdf','documentos/75/expediente/qmvC10I5Ye8hJizc8nsut6pRHsYcVEzHRjrhhyrK.pdf','documentos/75/expediente/Xu8Vy5XxAYwbUlQxTpKCc0w4vi1JfxvwIOueYyod.pdf',NULL,NULL,'2026-03-30 23:18:43','2026-04-02 18:29:18',1,0,0,0,'facturas/75/shzX1AGuMq8lqRwXbraoKo4iVtXAXH1WLY2NUOcv.pdf',NULL,0,0),(76,NULL,NULL,NULL,NULL,NULL,'Activo',NULL,NULL,0,NULL,NULL,NULL,NULL,NULL,NULL,'2026-04-01 21:44:30','2026-04-01 21:44:30',0,0,0,0,NULL,NULL,0,0),(77,NULL,NULL,NULL,NULL,NULL,'Activo',NULL,NULL,0,NULL,NULL,NULL,NULL,NULL,NULL,'2026-04-01 23:45:07','2026-04-02 00:02:54',0,0,0,0,NULL,NULL,0,0),(79,NULL,NULL,NULL,NULL,NULL,'Activo',NULL,NULL,0,NULL,NULL,NULL,NULL,NULL,NULL,'2026-04-02 00:37:55','2026-04-02 00:37:55',0,0,0,0,NULL,NULL,0,0),(80,NULL,NULL,NULL,NULL,NULL,'Activo',NULL,NULL,0,NULL,NULL,NULL,NULL,NULL,NULL,'2026-04-02 00:55:03','2026-04-02 00:55:03',0,0,0,0,NULL,NULL,0,0),(81,NULL,NULL,NULL,NULL,NULL,'Activo',NULL,NULL,0,NULL,NULL,NULL,NULL,NULL,NULL,'2026-04-02 00:57:03','2026-04-02 00:57:03',0,0,0,0,NULL,NULL,0,0),(82,NULL,NULL,NULL,NULL,NULL,'Activo',NULL,NULL,0,NULL,NULL,NULL,NULL,NULL,NULL,'2026-04-02 00:58:31','2026-04-02 00:58:31',0,0,0,0,NULL,NULL,0,0),(83,NULL,65,NULL,NULL,NULL,'Activo',NULL,NULL,0,NULL,NULL,NULL,NULL,NULL,NULL,'2026-04-02 04:32:25','2026-04-08 21:11:06',0,0,0,0,NULL,NULL,0,0),(84,NULL,67,NULL,NULL,NULL,'Activo',NULL,NULL,0,NULL,NULL,NULL,NULL,NULL,NULL,'2026-04-02 04:37:43','2026-04-08 21:11:14',0,0,0,0,NULL,NULL,0,0),(85,NULL,69,NULL,NULL,NULL,'Activo',NULL,NULL,0,NULL,NULL,NULL,NULL,NULL,NULL,'2026-04-02 04:40:18','2026-04-08 21:11:21',0,0,0,0,NULL,NULL,0,0),(86,NULL,65,1,NULL,NULL,'Alumno',NULL,NULL,0,'documentos/86/expediente/Z45UBfTdmCVJEygENQQny3SEkNvMbzGYhYXoL5gT.pdf','documentos/86/expediente/3siOYTEi7EJtpxf5Qm0pkzxqiF8NSNK6aSyKmiw0.pdf','documentos/86/expediente/rAEnxjdHF278QDxmVJq6y62uMXFmrPSb29pU70Qp.pdf','documentos/86/expediente/XaesJB9BPqggCns6sj2lpFMNuZFsGIKSWfAA6zyJ.pdf',NULL,NULL,'2026-04-06 06:25:18','2026-04-08 22:28:38',0,0,0,0,'facturas/86/OQTJsne8QGuMVanwbLUT5Zn4jjmNw4M6MzXLht8n.pdf',NULL,0,0),(87,NULL,69,1,NULL,NULL,'Alumno',NULL,NULL,0,'documentos/87/expediente/3sT3xuS0HLnXKlawJIHhAEvoprwnKyPffs69hnob.pdf','documentos/87/expediente/1uQyb3eDcT8qlQtEqkDH9mNnN1J8txbeQhJ4QYv6.pdf','documentos/87/expediente/1TPm6Qx4kgX0Yf1JajNH4HEFToXiWAPD0MdpZmLu.pdf','documentos/87/expediente/46wJys0JevrsH4h7EYkJtvmBjmh8vBGWvvB16Kmy.pdf',NULL,NULL,'2026-04-06 07:01:17','2026-04-08 22:28:31',0,0,0,0,'facturas/87/Wp6RCRykzY975H0ySRZDW4Yxej7iiMQBi5HRNich.pdf',NULL,0,0),(88,NULL,NULL,1,NULL,NULL,'Alumno',NULL,NULL,0,NULL,'documentos/88/expediente/xFiNh9Vgy4hB4y1Tl7k33W8x5uPRutUNkNWnvXdX.pdf','documentos/88/expediente/koGp34mAwoOtzFAQngJ9hFBVJk3NSlAzvhhxE4Da.pdf','documentos/88/expediente/hOSP2RCFlq3DrO19bmtqJRv6zCfBgeJGVnljkprd.pdf',NULL,NULL,'2026-04-06 07:21:14','2026-04-06 07:30:56',1,0,0,0,'facturas/88/Fhm6V1CmFlWywstyIRy4rJfPkja8EvfRXO8uQ9PN.pdf',NULL,0,0),(89,NULL,66,NULL,NULL,NULL,'Activo',NULL,NULL,0,NULL,NULL,NULL,NULL,NULL,NULL,'2026-04-08 21:14:54','2026-04-08 21:14:54',0,0,0,0,NULL,NULL,0,0),(90,NULL,68,NULL,NULL,NULL,'Activo',NULL,NULL,0,NULL,NULL,NULL,NULL,NULL,NULL,'2026-04-08 21:18:03','2026-04-08 21:18:03',0,0,0,0,NULL,NULL,0,0),(91,NULL,66,1,NULL,NULL,'Alumno',NULL,NULL,0,'documentos/91/expediente/Xga2fb7k8PkfVVWDwZMfTlxF9A4cLAE6nagem4gl.pdf','documentos/91/expediente/I4EJlbJ8geDD90ff8d8iL19lonziY0rHX0s9BUK2.pdf','documentos/91/expediente/KAAyG2ErURfOqNqD8DfFlNYu0eHODBCM4lQWabr9.pdf','documentos/91/expediente/bkOtK8DEepsjEfEZjNm5xN9wq6ZlSsOWEUPSt5Jf.pdf',NULL,NULL,'2026-04-08 23:26:53','2026-04-08 23:36:31',0,0,0,0,NULL,NULL,0,0),(92,NULL,68,1,NULL,NULL,'Alumno',NULL,NULL,0,'documentos/92/expediente/8gDkeP6WPjW3pxSN6bU9Oyn9PhwAEZg1xuRQZBK4.pdf','documentos/92/expediente/hNadKGbACKITTv50aXC2WtF5XQuGfkYG32KFPHU4.pdf','documentos/92/expediente/93rHTsbuO2bwjfR7pfobQbYfxpyIXOp56s2XZI9Q.pdf','documentos/92/expediente/8GVfV83O0nFVAALAno3TPgXyjvomMYK8YgckEvQo.pdf',NULL,NULL,'2026-04-08 23:27:07','2026-04-08 23:35:04',0,0,0,0,NULL,NULL,0,0),(93,NULL,67,1,NULL,NULL,'Alumno',NULL,NULL,0,'documentos/93/expediente/DNbLoKFApEJaaxa2NqCWElFaHPp6i1swRSv5N8MQ.pdf','documentos/93/expediente/v4QNMUUjf82o5btItQKTqFIg2bfNhoDftKNnvWeM.pdf','documentos/93/expediente/B0ZATuKjs6wlrSfMRyQp6YEx8vXswAAz0kuHAE8u.pdf','documentos/93/expediente/DaLzuzFwdLK9r4WSe0Dy3NF43xzElnjZhJTz12V7.pdf',NULL,NULL,'2026-04-08 23:27:23','2026-04-08 23:32:34',0,0,0,0,'facturas/93/9IxhuOhVZlgRGKn1SFPp3H93A928pWqNgZ8DfGa0.pdf',NULL,0,0),(94,NULL,69,1,NULL,NULL,'Alumno',NULL,NULL,0,'documentos/94/expediente/ySMxaeknPUQhk8AgFPXiCSDqs8k50D7HoDVa6K1V.pdf','documentos/94/expediente/1L9WiZ9ofdRBSPqASMCY4VgHtRreVabt9b1ZrwlO.pdf','documentos/94/expediente/Uwzmd4uaWucl8KgsXSSIAmOFCs2smDOZokSfYp4v.pdf','documentos/94/expediente/eD4yHW1jYz8gy3BhiLTZ0zClrree3xunonbKBwQS.pdf',NULL,NULL,'2026-04-08 23:44:29','2026-04-09 00:18:01',0,0,0,0,NULL,NULL,0,0),(95,NULL,66,1,NULL,NULL,'Alumno',NULL,NULL,0,'documentos/95/expediente/rfyBel3xOQyXnZpoeV7mrLtihRPIBh4G7JSiTBSb.pdf','documentos/95/expediente/pht3F1IXF7hnfW6t1UjZoBI2JTh8p4EIb2jbxkSa.pdf','documentos/95/expediente/T5xbOnyHgHXpPlxnUqPdUYw69ZjQF0Zhli0sXGaO.pdf','documentos/95/expediente/TUQ4rKKto2wdDFGBe0HS0yfkvbEqNCnAe64a8xjn.pdf',NULL,NULL,'2026-04-12 22:16:21','2026-04-12 22:32:11',0,0,0,0,'facturas/95/VmK7ZN5szI0ajy1iRlsi1ITTkbhF2Jal1FtiknMJ.pdf',NULL,0,0),(96,NULL,66,1,NULL,NULL,'Alumno',NULL,NULL,0,NULL,'documentos/96/expediente/UNeUa07L1guA30YEyksuTEYM1yGvAFONDOxcRHpJ.pdf','documentos/96/expediente/pqYvnLBUIDkl7u7nK9MlwWc6MHHCPCVdJKS3JFac.pdf','documentos/96/expediente/v4bHfcZXmyDZy8VTbyjVHaraXx8P0YN3erZlqbV1.pdf',NULL,NULL,'2026-04-12 23:01:25','2026-04-21 05:14:32',0,0,0,0,NULL,NULL,1,0),(97,NULL,65,NULL,NULL,NULL,'Activo',NULL,NULL,0,NULL,NULL,NULL,NULL,NULL,NULL,'2026-04-13 16:57:13','2026-04-13 16:57:13',0,0,0,0,NULL,NULL,0,0),(98,NULL,65,NULL,NULL,NULL,'Activo',NULL,NULL,0,NULL,NULL,NULL,NULL,NULL,NULL,'2026-04-21 04:03:56','2026-04-21 04:03:56',0,0,0,0,NULL,NULL,0,0),(99,NULL,70,1,NULL,NULL,'Alumno',NULL,NULL,0,'documentos/99/expediente/MGqxCljDSv6qR4CMsp2FSiXkFU55hkHxLjCepACu.pdf','documentos/99/expediente/xzOykQYl1eFfySq7LcwNyhi6xPBIIU0TkVCUK4aA.pdf','documentos/99/expediente/n5Yd1on8MnurpzabpAR6qLCi53IwQnhGNRs6XugQ.pdf','documentos/99/expediente/SykX2MkWXJGgzVU2MU9gH3ugPb55THLpRNBf9vDQ.pdf',NULL,NULL,'2026-04-21 21:23:00','2026-04-22 14:27:46',0,0,0,0,'facturas/99/A6zRD3mTEnCnokUx3ZF0UmIds8yX0YVqpUhWfoU9.pdf',NULL,0,0),(100,NULL,70,NULL,NULL,NULL,'Activo',NULL,NULL,0,NULL,NULL,NULL,NULL,NULL,NULL,'2026-04-22 05:05:00','2026-04-22 05:05:00',0,0,0,0,NULL,NULL,0,0),(102,NULL,65,NULL,NULL,NULL,'Activo',NULL,NULL,0,NULL,NULL,NULL,NULL,NULL,NULL,'2026-04-22 07:07:44','2026-04-22 07:07:44',0,0,0,0,NULL,NULL,0,0),(103,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,0,NULL,NULL,NULL,NULL,NULL,NULL,'2026-04-22 19:11:23','2026-04-22 19:11:23',0,0,0,0,NULL,NULL,0,0),(104,NULL,70,1,NULL,NULL,'Alumno',NULL,NULL,0,'documentos/104/expediente/OPQuJ7PCimfKTwt1ueaSd0GPx6fRhNqKCytfIRSM.pdf','documentos/104/expediente/ZUIpV5hMVP9b7feMtCRZCsll0MZQooWpfwK2sOXw.pdf','documentos/104/expediente/sl6vREUuKeytR4eKFLCNuDVlhMqJ60npgYmiKkJm.pdf','documentos/104/expediente/nTRktr6dSntLmW4vGBY0qYsAudDMe0mE9pBAikFk.pdf',NULL,NULL,'2026-04-22 19:52:05','2026-04-22 20:12:14',0,0,0,0,'facturas/104/UTHGqbeYlFQK7hZwhLgY0AJyaEumgghf2wlxHby1.pdf',NULL,0,0);
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
  `title` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `description` text COLLATE utf8mb4_unicode_ci,
  `type` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
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
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `activities`
--

LOCK TABLES `activities` WRITE;
/*!40000 ALTER TABLE `activities` DISABLE KEYS */;
INSERT INTO `activities` VALUES (1,NULL,NULL,1,'Actividad',NULL,'Cuestionario','{\"question\":\"hola\",\"correct_answer\":\"0\",\"options\":[\"1\",\"2\",\"3\",\"4\"]}',1,'2025-12-06 18:55:25','2025-12-06 18:55:25');
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
  `colonia` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `calle` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `ciudad` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `estado` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `codigo_postal` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=88 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `addresses`
--

LOCK TABLES `addresses` WRITE;
/*!40000 ALTER TABLE `addresses` DISABLE KEYS */;
INSERT INTO `addresses` VALUES (1,'Colonia Test','Calle Test','Acapulco','GRO','39000','2025-12-06 18:21:36','2025-12-06 18:21:36'),(2,'Colonia Test','Calle Test','Acapulco','GRO','39000','2025-12-06 18:21:37','2025-12-06 18:21:37'),(3,'Colonia Test','Calle Test','Acapulco','GRO','39000','2025-12-06 18:21:37','2025-12-06 18:21:37'),(4,'Colonia Test','Calle Test','Acapulco','GRO','39000','2025-12-06 18:21:37','2025-12-06 18:21:37'),(5,'Colonia Test','Calle Test','Acapulco','GRO','39000','2025-12-06 18:21:38','2025-12-06 18:21:38'),(6,'Colonia Test','Calle Test','Acapulco','GRO','39000','2025-12-06 18:21:38','2025-12-06 18:21:38'),(7,'Colonia Test','Calle Test','Acapulco','GRO','39000','2025-12-06 18:21:39','2025-12-06 18:21:39'),(8,'Colonia Test','Calle Test','Acapulco','GRO','39000','2025-12-06 18:21:39','2025-12-06 18:21:39'),(9,'Colonia Test','Calle Test','Acapulco','GRO','39000','2025-12-06 18:21:40','2025-12-06 18:21:40'),(10,'Colonia Test','Calle Test','Acapulco','GRO','39000','2026-01-26 19:29:07','2026-01-26 19:29:07'),(12,'LA ZANJA O LA POZA','Flors','Acapulco','Guerrero','30996','2026-01-30 19:15:22','2026-01-30 19:15:22'),(15,'LA ZANJA O LA POZA','Flors','Acap','Guerr','30069','2026-01-30 19:41:58','2026-01-30 19:48:40'),(19,'Caleta','Universidad','Acapulco','Guerrero','33304','2026-01-30 23:04:11','2026-01-30 23:04:11'),(21,'Caleta','Vicente','Acapulco','Guerrero','33906','2026-01-31 00:20:37','2026-01-31 00:20:37'),(29,'Caleta','Universidad','Acapulco','Guerrero','33304','2026-02-05 20:44:21','2026-02-05 20:44:21'),(31,'Icacos','Av. Costera Miguel Alemán 123','Acapulco','Guerrer','39302','2026-02-18 21:29:56','2026-03-05 05:09:47'),(32,'Centro','Calle Morelos 45','Chilpancingo','Guerrero','39000','2026-02-18 21:40:48','2026-02-18 21:40:48'),(33,'Progreso','Calle Reforma 200','Acapulco','Guerrero','39570','2026-02-18 21:43:47','2026-02-18 21:43:47'),(34,'La Garita','Calle Juárez 89','Acapulco','Guerrero','39350','2026-02-18 21:45:45','2026-02-18 21:45:45'),(35,'La Sabana','Calle Independencia 120','Acapulco','Guerrero','39750','2026-02-18 21:48:59','2026-02-18 21:48:59'),(36,'Centro','Calle Benito Juárez 77','Chilpancingo','Guerrero','39010','2026-02-18 21:50:43','2026-02-18 21:50:43'),(38,'La poza','Universidad','Acapulco','soltero','39906','2026-02-20 05:06:23','2026-02-20 05:06:23'),(39,'La poza','Vicente Guerrero','Acapulco','Guerrero','39906','2026-02-20 05:07:45','2026-02-20 05:07:45'),(40,'Centro','Reforma 120','Chilpancingo','Guerrero','39000','2026-02-20 05:47:21','2026-02-20 05:47:21'),(41,'Renacimiento','Av. Universidad 45','Acapulco','Guerrero','39700','2026-02-20 05:51:35','2026-02-20 05:51:35'),(42,'Progreso','Hidalgo 250','Acapulco','Guerrero','39570','2026-02-20 06:00:18','2026-02-20 06:00:18'),(43,'Icacos','Av. Costera 89','Acapulco','Guerrero','39300','2026-02-20 06:02:45','2026-02-20 06:02:45'),(44,'Centro','Juárez 77','Chilpancingo','Guerrero','39010','2026-02-20 06:06:28','2026-02-20 06:06:28'),(45,'Renacimiento','Calle Universidad 55','Acapulco','Guerrero','39700','2026-02-20 06:08:40','2026-02-20 06:08:40'),(46,'Progreso','Av. Costera Miguel Alemán 123','Acapulco','Guerrero','39302','2026-03-05 02:13:25','2026-03-05 02:13:25'),(47,'La poza','Av. Costera Miguel Alemán 123','Acapulco','Guerrero','39906','2026-03-05 02:58:12','2026-03-05 02:58:12'),(48,'La poza','Hidalgo 250','Acapulco','Guerrero','39906','2026-03-10 18:55:33','2026-03-10 18:55:33'),(49,'La poza','Av. Costera Miguel Alemán 123','Acapulco','Pendiente','39302','2026-03-10 18:57:40','2026-03-10 18:57:40'),(50,'La poza','Av. Costera Miguel Alemán 123','Acapulco','Pendiente','39302','2026-03-10 18:58:26','2026-03-10 18:58:26'),(51,'La poza','Vicente Guerrero','Acapulco','Guerrero','39906','2026-03-23 22:28:58','2026-03-23 22:28:58'),(52,'La glorieta','Viceten Guerrero','Acapulco','Guerrero','389906','2026-03-26 21:03:46','2026-03-26 21:03:46'),(53,'llanolargo','vicente Guerrer','Acapulco','Guerrero','39906','2026-03-29 23:23:31','2026-03-29 23:23:31'),(54,'llanolargo','vicente Guerrer','Acapulco','Guerrero','39906','2026-03-30 02:29:52','2026-03-30 02:29:52'),(55,'Colosio','Glorieta','Acapulco','Guerrero','32903','2026-03-30 04:13:23','2026-03-30 04:13:23'),(56,'La poza','vicente Guerrer','Acapulco','Guerrero','39906','2026-03-30 06:03:28','2026-03-30 06:03:28'),(57,'LA poza','Vicente Guerrer','Acapulco','GUERRERO','39906','2026-03-30 06:08:05','2026-03-30 06:08:05'),(58,'La poza','vicente Guerrero','aCAPULCO','Guerrero','39906','2026-03-30 23:09:12','2026-03-30 23:09:12'),(59,'llanolargo','vicente Guerrer','Acapulco','Guerrero','39906','2026-03-30 23:19:48','2026-03-30 23:19:48'),(60,'Hornos','Av. Costera Miguel Alemán #123','Acapulco','Acapulco','39355','2026-04-01 21:44:30','2026-04-01 21:44:30'),(61,'Centro','Calle Reforma #456','Puebla','Puebla','72000','2026-04-01 23:45:07','2026-04-01 23:45:07'),(63,'Centro','Calle Reforma #456','Puebla','Puebla','72000','2026-04-02 00:37:55','2026-04-02 00:37:55'),(64,'San Pedro','Av. Universidad #321','Monterrey','Nuevo León','64000','2026-04-02 00:55:03','2026-04-02 00:55:03'),(65,'El Marqués','Av. Constituyentes #210','Querétaro','Querétaro','76140','2026-04-02 00:57:03','2026-04-02 00:57:03'),(66,'Del Valle','Calle Insurgentes Sur #890','Ciudad de México','CDMX','03100','2026-04-02 00:58:30','2026-04-02 00:58:30'),(67,'La Paz','Calle Morelos #234','Mérida','Yucat','97000','2026-04-02 04:32:24','2026-04-22 06:52:24'),(68,'San Miguel','Calle Independencia #145','León','Guanajuato','37000','2026-04-02 04:37:43','2026-04-02 04:37:43'),(69,'La Esperanza','Calle Zaragoza #678','Oaxaca de Juárez','Oaxaca','68000','2026-04-02 04:40:18','2026-04-02 04:40:18'),(70,'La poza','vicente Guerrero','Acapulco','Guerrero','39906','2026-04-06 06:48:34','2026-04-06 06:48:34'),(71,'La poza','vicente Guerrero','Acapulco','Guerrero','39906','2026-04-06 07:02:42','2026-04-06 07:02:42'),(72,'La poza','vicente Guerrero','Acapulco','Guerrero','39906','2026-04-06 07:22:33','2026-04-06 07:22:33'),(73,'La Garita','Paseo de la Cañada #789','Acapulco de Juárez','Guerrero','39690','2026-04-08 21:14:54','2026-04-08 21:14:54'),(74,'Renacimiento','Av. Universidad #234','Acapulco de Juárez','Guerrero','39715','2026-04-08 21:18:03','2026-04-08 21:18:03'),(75,'La poza','vicente Guerrero','Acapulco','Guerrero','39906','2026-04-08 23:32:19','2026-04-08 23:32:19'),(76,'La poza','vicente Guerrero','Acapulco','Guerrero','39906','2026-04-08 23:34:52','2026-04-08 23:34:52'),(77,'La poza','vicente Guerrero','Acapulco','Guerrero','39906','2026-04-08 23:36:27','2026-04-08 23:36:27'),(78,'La poza','vicente Guerrero','Acapulco','Guerrero','39906','2026-04-08 23:45:45','2026-04-08 23:45:45'),(79,'La poza','Vicente Guerrer','Acapulco','Guerrero','39906','2026-04-12 22:19:19','2026-04-12 22:19:19'),(80,'La poza','Vicente Guerrer','Acapulco','Guerrero','39906','2026-04-12 23:17:55','2026-04-12 23:17:55'),(81,'LA POZA','Vicente Guerrero','Acapulco','Pendiente','39897','2026-04-13 16:57:13','2026-04-13 16:57:13'),(82,'LA ZANJA O LA POZA','Av. Costera Miguel Alemán 123','Acapulco','Guerrero','33906','2026-04-21 04:03:56','2026-04-21 04:03:56'),(83,'La POZA','Vicente Guerrero','aCAPULCO','gUERRERO','399906','2026-04-21 22:08:55','2026-04-21 22:08:55'),(84,'LA ZANJA O LA POZA','Av. Costera Miguel Alemán 123','Acapulco','Guerrero','33906','2026-04-22 05:05:00','2026-04-22 05:05:00'),(86,'La Paz','Calle Morelos #234','Mérida','Yucat','97000','2026-04-22 07:07:44','2026-04-22 07:07:44'),(87,'La POZA','Vicente Guerrero','aCAPULCO','gUERRERO','399906','2026-04-22 20:09:14','2026-04-22 20:09:14');
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
  `nombre_documento` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `descripcion` text COLLATE utf8mb4_unicode_ci,
  `archivo_path` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `mime_type` varchar(120) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
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
) ENGINE=InnoDB AUTO_INCREMENT=3 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `becas_documentos`
--

LOCK TABLES `becas_documentos` WRITE;
/*!40000 ALTER TABLE `becas_documentos` DISABLE KEYS */;
INSERT INTO `becas_documentos` VALUES (1,91,'nose','nose','documentos/91/becas/GJxPbLwf3NKNIzgIPtBn6DisLuYBA70QVdWDlhf9.pdf','application/pdf',116305,1,'2026-04-22 08:12:54','2026-04-22 08:12:54',NULL),(2,92,'nos','nose','documentos/92/becas/Lz3jurQrsVW0gfrpQ1i7HUeMP3BnoiaRfypq37KU.pdf','application/pdf',815214,1,'2026-04-22 08:14:55','2026-04-22 18:40:59',NULL);
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
  `concept` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `amount` decimal(10,2) NOT NULL,
  `porcentaje_cargo_moratorio` decimal(5,2) DEFAULT NULL,
  `cargo_monetario` decimal(12,2) DEFAULT NULL,
  `description` text COLLATE utf8mb4_unicode_ci,
  `is_active` tinyint(1) NOT NULL DEFAULT '1',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=3 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `billing_concepts`
--

LOCK TABLES `billing_concepts` WRITE;
/*!40000 ALTER TABLE `billing_concepts` DISABLE KEYS */;
INSERT INTO `billing_concepts` VALUES (1,4,'k',0.01,NULL,NULL,'k',1,'2026-01-22 17:17:39','2026-01-22 17:17:57','2026-01-22 17:17:57'),(2,4,'inscripcion',2000.00,10.00,200.00,'nose',1,'2026-04-21 07:07:16','2026-04-21 07:07:16',NULL);
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
  `factura_uid` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `user_id` bigint unsigned NOT NULL,
  `period_id` bigint unsigned DEFAULT NULL,
  `concepto` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `monto` decimal(10,2) NOT NULL,
  `porcentaje_cargo_moratorio` decimal(5,2) DEFAULT NULL,
  `cargo_monetario` decimal(12,2) DEFAULT NULL,
  `fecha_vencimiento` date NOT NULL,
  `archivo_path` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `xml_path` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `status` enum('Pendiente','Abonado','Pagada') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'Pendiente',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `billings_factura_uid_unique` (`factura_uid`),
  KEY `billings_user_id_foreign` (`user_id`),
  KEY `billings_period_id_foreign` (`period_id`),
  CONSTRAINT `billings_period_id_foreign` FOREIGN KEY (`period_id`) REFERENCES `periods` (`id`) ON DELETE SET NULL,
  CONSTRAINT `billings_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=62 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `billings`
--

LOCK TABLES `billings` WRITE;
/*!40000 ALTER TABLE `billings` DISABLE KEYS */;
INSERT INTO `billings` VALUES (1,'INS-6934743131EFE',3,1,'Inscripción Nuevo Ingreso',1500.00,NULL,NULL,'2025-12-13',NULL,NULL,'Pendiente','2025-12-06 18:21:37','2025-12-06 18:21:37',NULL),(2,'INS-693474318E5DC',4,1,'Inscripción Nuevo Ingreso',1500.00,NULL,NULL,'2025-12-13',NULL,NULL,'Pendiente','2025-12-06 18:21:37','2025-12-06 18:21:37',NULL),(3,'INS-69347431E0407',5,1,'Inscripción Nuevo Ingreso',1500.00,NULL,NULL,'2025-12-13',NULL,NULL,'Pendiente','2025-12-06 18:21:37','2025-12-06 18:21:37',NULL),(4,'INS-69347432550B2',6,1,'Inscripción Nuevo Ingreso',1500.00,NULL,NULL,'2025-12-13',NULL,NULL,'Pagada','2025-12-06 18:21:38','2025-12-06 18:21:38',NULL),(5,'INS-69347432C957D',7,1,'Inscripción Nuevo Ingreso',1500.00,NULL,NULL,'2025-12-13',NULL,NULL,'Pagada','2025-12-06 18:21:38','2025-12-06 18:21:38',NULL),(6,'INS-69347433459AB',8,1,'Inscripción Nuevo Ingreso',1500.00,NULL,NULL,'2025-12-13',NULL,NULL,'Pagada','2025-12-06 18:21:39','2025-12-06 18:21:39',NULL),(7,'INS-69347433AB652',9,1,'Inscripción Nuevo Ingreso',1500.00,NULL,NULL,'2025-12-13',NULL,NULL,'Pagada','2025-12-06 18:21:39','2025-12-06 18:21:39',NULL),(8,'INS-693474340CD22',10,1,'Inscripción Nuevo Ingreso',1500.00,NULL,NULL,'2025-12-13',NULL,NULL,'Pagada','2025-12-06 18:21:40','2025-12-06 18:21:40',NULL),(9,'INS-693474346B48B',11,1,'Inscripción Nuevo Ingreso',1500.00,NULL,NULL,'2025-12-13',NULL,NULL,'Pagada','2025-12-06 18:21:40','2025-12-06 18:21:40',NULL),(10,'INS-20260304000001',50,1,'Inscripción de nuevo ingreso',80000.00,NULL,NULL,'2026-03-04',NULL,NULL,'Pendiente','2026-03-05 02:13:25','2026-03-05 02:13:25',NULL),(11,'INS-20260304000002',51,1,'Inscripción de nuevo ingreso',80000.00,NULL,NULL,'2026-03-04',NULL,NULL,'Pendiente','2026-03-05 02:58:12','2026-03-05 02:58:12',NULL),(12,'INS-20260310000001',52,1,'Inscripción de nuevo ingreso',80000.00,NULL,NULL,'2026-03-10',NULL,NULL,'Pendiente','2026-03-10 18:55:33','2026-03-10 18:55:33',NULL),(13,'INS-20260310000002',53,1,'Inscripción de nuevo ingreso',80000.00,NULL,NULL,'2026-03-10',NULL,NULL,'Pendiente','2026-03-10 18:57:40','2026-03-10 18:57:40',NULL),(14,'INS-20260310000003',54,1,'Inscripción de nuevo ingreso',80000.00,NULL,NULL,'2026-03-10',NULL,NULL,'Pendiente','2026-03-10 18:58:27','2026-03-10 18:58:27',NULL),(15,'INS-20260323000001',62,1,'Inscripción de nuevo ingreso',80000.00,NULL,NULL,'2026-03-23',NULL,NULL,'Pendiente','2026-03-23 22:28:58','2026-03-23 22:28:58',NULL),(16,'INS-20260326000001',67,1,'Inscripción de nuevo ingreso',80000.00,NULL,NULL,'2026-03-26',NULL,NULL,'Pendiente','2026-03-26 21:03:47','2026-03-26 21:03:47',NULL),(17,'INS-20260326000002',67,1,'Inscripción de nuevo ingreso',80000.00,NULL,NULL,'2026-03-26',NULL,NULL,'Pendiente','2026-03-26 21:04:50','2026-03-26 21:04:50',NULL),(18,'INS-20260326000003',67,1,'Inscripción de nuevo ingreso',80000.00,NULL,NULL,'2026-03-26',NULL,NULL,'Pendiente','2026-03-26 21:09:12','2026-03-26 21:09:12',NULL),(19,'INS-20260326000004',67,1,'Inscripción de nuevo ingreso',80000.00,NULL,NULL,'2026-03-26',NULL,NULL,'Pendiente','2026-03-26 21:09:54','2026-03-26 21:09:54',NULL),(20,'INS-20260326000005',67,1,'Inscripción de nuevo ingreso',80000.00,NULL,NULL,'2026-03-26',NULL,NULL,'Pendiente','2026-03-26 21:10:42','2026-03-26 21:10:42',NULL),(21,'INS-20260326000006',67,1,'Inscripción de nuevo ingreso',80000.00,NULL,NULL,'2026-03-26',NULL,NULL,'Pendiente','2026-03-26 21:15:14','2026-03-26 21:15:14',NULL),(22,'INS-20260326000007',67,1,'Inscripción de nuevo ingreso',80000.00,NULL,NULL,'2026-03-26',NULL,NULL,'Pendiente','2026-03-26 21:16:05','2026-03-26 21:16:05',NULL),(23,'INS-20260329000001',68,1,'Inscripción de nuevo ingreso',80000.00,NULL,NULL,'2026-03-29',NULL,NULL,'Pendiente','2026-03-29 23:23:31','2026-03-29 23:23:31',NULL),(24,'INS-20260329000002',69,1,'Inscripción de nuevo ingreso',80000.00,NULL,NULL,'2026-03-29',NULL,NULL,'Pendiente','2026-03-30 02:29:53','2026-03-30 02:29:53',NULL),(25,'INS-20260329000003',70,1,'Inscripción de nuevo ingreso',80000.00,NULL,NULL,'2026-03-29',NULL,NULL,'Pendiente','2026-03-30 04:13:23','2026-03-30 04:13:23',NULL),(26,'INS-20260330000001',71,1,'Inscripción de nuevo ingreso',80000.00,NULL,NULL,'2026-03-30',NULL,NULL,'Pendiente','2026-03-30 06:03:28','2026-03-30 06:03:28',NULL),(27,'INS-20260330000002',72,1,'Inscripción de nuevo ingreso',80000.00,NULL,NULL,'2026-03-30',NULL,NULL,'Pendiente','2026-03-30 06:08:05','2026-03-30 06:08:05',NULL),(28,'INS-20260330000003',74,1,'Inscripción de nuevo ingreso',80000.00,NULL,NULL,'2026-03-30',NULL,NULL,'Pendiente','2026-03-30 23:09:13','2026-03-30 23:09:13',NULL),(29,'INS-20260330000004',75,1,'Inscripción de nuevo ingreso',80000.00,NULL,NULL,'2026-03-30',NULL,NULL,'Pendiente','2026-03-30 23:19:48','2026-03-30 23:19:48',NULL),(30,'INS-20260330000005',75,1,'Inscripción de nuevo ingreso',80000.00,NULL,NULL,'2026-03-30',NULL,NULL,'Pendiente','2026-03-30 23:41:00','2026-03-30 23:41:00',NULL),(31,'INS-20260330000006',75,1,'Inscripción de nuevo ingreso',80000.00,NULL,NULL,'2026-03-30',NULL,NULL,'Pendiente','2026-03-30 23:41:13','2026-03-30 23:41:13',NULL),(32,'INS-20260330000007',75,1,'Inscripción de nuevo ingreso',80000.00,NULL,NULL,'2026-03-30',NULL,NULL,'Pendiente','2026-03-30 23:59:25','2026-03-30 23:59:25',NULL),(33,'INS-20260330000008',75,1,'Inscripción de nuevo ingreso',80000.00,NULL,NULL,'2026-03-30',NULL,NULL,'Pendiente','2026-03-31 00:01:38','2026-03-31 00:01:38',NULL),(34,'INS-20260330000009',75,1,'Inscripción de nuevo ingreso',80000.00,NULL,NULL,'2026-03-30','facturas/75/shzX1AGuMq8lqRwXbraoKo4iVtXAXH1WLY2NUOcv.pdf','facturas/75/Sb31TvbQPHO0fgg5kq006y5Qcp7UguL4h1RCun2j.pdf','Pendiente','2026-03-31 00:56:38','2026-03-31 00:56:38',NULL),(35,'INS-20260402000001',74,1,'Inscripción de nuevo ingreso',80000.00,NULL,NULL,'2026-04-02',NULL,NULL,'Pendiente','2026-04-02 18:28:55','2026-04-02 18:28:55',NULL),(36,'INS-20260402000002',74,1,'Inscripción de nuevo ingreso',80000.00,NULL,NULL,'2026-04-02','facturas/74/U6wL55Of6h758jged4eq9rtLEh3mxunFyyknsLqv.pdf','facturas/74/4Hpki8fYFkQHHOIb4vZMr1Htl1LBypEHoPKg12mv.pdf','Pendiente','2026-04-02 19:13:55','2026-04-02 19:13:55',NULL),(37,'INS-20260406000001',86,1,'Inscripción de nuevo ingreso',80000.00,NULL,NULL,'2026-04-06',NULL,NULL,'Pendiente','2026-04-06 06:48:34','2026-04-06 06:48:34',NULL),(38,'INS-20260406000002',86,1,'Inscripción de nuevo ingreso',80000.00,NULL,NULL,'2026-04-06',NULL,NULL,'Pendiente','2026-04-06 06:54:28','2026-04-06 06:54:28',NULL),(39,'INS-20260406000003',86,1,'Inscripción de nuevo ingreso',80000.00,NULL,NULL,'2026-04-06','facturas/86/OQTJsne8QGuMVanwbLUT5Zn4jjmNw4M6MzXLht8n.pdf',NULL,'Pendiente','2026-04-06 06:57:44','2026-04-06 06:57:44',NULL),(40,'INS-20260406000004',87,1,'Inscripción de nuevo ingreso',80000.00,NULL,NULL,'2026-04-06','facturas/87/60cGXiwbW8RHpj2L353vvpDPe798jKXTyMcQdx47.pdf',NULL,'Pendiente','2026-04-06 07:02:42','2026-04-06 07:02:42',NULL),(41,'INS-20260406000005',87,1,'Inscripción de nuevo ingreso',80000.00,NULL,NULL,'2026-04-06','facturas/87/Wp6RCRykzY975H0ySRZDW4Yxej7iiMQBi5HRNich.pdf',NULL,'Pendiente','2026-04-06 07:06:08','2026-04-06 07:06:08',NULL),(42,'INS-20260406000006',88,1,'Inscripción de nuevo ingreso',80000.00,NULL,NULL,'2026-04-06',NULL,NULL,'Pendiente','2026-04-06 07:22:33','2026-04-06 07:22:33',NULL),(43,'INS-20260406000007',88,1,'Inscripción de nuevo ingreso',80000.00,NULL,NULL,'2026-04-06','facturas/88/Fhm6V1CmFlWywstyIRy4rJfPkja8EvfRXO8uQ9PN.pdf',NULL,'Pendiente','2026-04-06 07:25:38','2026-04-06 07:25:38',NULL),(44,'INS-20260408000001',93,1,'Inscripción de nuevo ingreso',80000.00,NULL,NULL,'2026-04-08','facturas/93/9IxhuOhVZlgRGKn1SFPp3H93A928pWqNgZ8DfGa0.pdf',NULL,'Pendiente','2026-04-08 23:32:19','2026-04-08 23:32:19',NULL),(45,'INS-20260408000002',92,1,'Inscripción de nuevo ingreso',80000.00,NULL,NULL,'2026-04-08',NULL,NULL,'Pendiente','2026-04-08 23:34:52','2026-04-08 23:34:52',NULL),(46,'INS-20260408000003',91,1,'Inscripción de nuevo ingreso',80000.00,NULL,NULL,'2026-04-08',NULL,NULL,'Pendiente','2026-04-08 23:36:27','2026-04-08 23:36:27',NULL),(47,'INS-20260408000004',94,1,'Inscripción de nuevo ingreso',80000.00,NULL,NULL,'2026-04-08',NULL,NULL,'Pendiente','2026-04-08 23:45:45','2026-04-08 23:45:45',NULL),(48,'INS-20260412000001',95,1,'Inscripción de nuevo ingreso',80000.00,NULL,NULL,'2026-04-12','facturas/95/m62wI5LLxFiI6GoO5t8BVW4jnrLGQ3P3r4xPrweE.pdf',NULL,'Pendiente','2026-04-12 22:19:19','2026-04-12 22:19:19',NULL),(49,'INS-20260412000002',95,1,'Inscripción de nuevo ingreso',80000.00,NULL,NULL,'2026-04-12','facturas/95/ioVpcN4DUsuN8t6AaMEYgBvKqlCr5YS8YdTeooS1.pdf',NULL,'Pendiente','2026-04-12 22:22:43','2026-04-12 22:22:43',NULL),(50,'INS-20260412000003',95,1,'Inscripción de nuevo ingreso',80000.00,NULL,NULL,'2026-04-12','facturas/95/QzcVjtD0aTpSWcG2OpJ1qeaJ6JecqVY1kZuo8UYj.pdf',NULL,'Pendiente','2026-04-12 22:25:56','2026-04-12 22:25:56',NULL),(51,'INS-20260412000004',95,1,'Inscripción de nuevo ingreso',80000.00,NULL,NULL,'2026-04-12','facturas/95/VmK7ZN5szI0ajy1iRlsi1ITTkbhF2Jal1FtiknMJ.pdf',NULL,'Pendiente','2026-04-12 22:30:03','2026-04-12 22:30:03',NULL),(52,'INS-20260412000005',95,1,'Inscripción de nuevo ingreso',80000.00,NULL,NULL,'2026-04-12',NULL,NULL,'Pendiente','2026-04-12 22:31:27','2026-04-12 22:31:27',NULL),(53,'INS-20260412000006',96,1,'Inscripción',600.00,NULL,NULL,'2026-04-12','facturas/96/fSvVQiLqqDYxv67kNgRHhmuHWOS3W362uNP4PhpL.pdf',NULL,'Pendiente','2026-04-12 23:17:55','2026-04-12 23:17:55',NULL),(54,'INS-20260412000007',96,1,'Inscripción',600.00,NULL,NULL,'2026-04-12',NULL,NULL,'Pendiente','2026-04-12 23:19:05','2026-04-12 23:19:05',NULL),(55,'EXT-20260412000001',91,1,'libro',1000.00,13.00,130.00,'2026-04-13',NULL,NULL,'Pendiente','2026-04-13 02:09:46','2026-04-13 02:10:08','2026-04-13 02:10:08'),(56,'EXT-20260421000001',91,1,'inscripcion',2000.00,10.00,200.00,'2026-04-21',NULL,NULL,'Pendiente','2026-04-21 07:08:53','2026-04-21 07:09:02','2026-04-21 07:09:02'),(57,'INS-20260421000001',99,1,'Inscripción',7000.00,NULL,NULL,'2026-04-21','facturas/99/X9BiqbzcOJUHzgwxLy6Ryf4rXf81BjHlhKxt6gTm.pdf',NULL,'Pagada','2026-04-21 22:08:55','2026-04-21 22:08:55',NULL),(58,'INS-20260421000002',99,1,'Inscripción',7000.00,NULL,NULL,'2026-04-21','facturas/99/A6zRD3mTEnCnokUx3ZF0UmIds8yX0YVqpUhWfoU9.pdf',NULL,'Pagada','2026-04-21 23:17:45','2026-04-21 23:17:45',NULL),(59,'EXT-20260422000001',91,1,'inscripcion',2000.00,10.00,200.00,'2026-04-22',NULL,NULL,'Pendiente','2026-04-22 18:36:55','2026-04-22 18:36:55',NULL),(60,'INS-20260422000001',104,1,'Inscripción',7000.00,NULL,NULL,'2026-04-22','facturas/104/UTHGqbeYlFQK7hZwhLgY0AJyaEumgghf2wlxHby1.pdf',NULL,'Pagada','2026-04-22 20:09:14','2026-04-22 20:09:14',NULL),(61,'INS-20260422000002',104,1,'Inscripción',7000.00,NULL,NULL,'2026-04-22',NULL,NULL,'Pagada','2026-04-22 20:11:38','2026-04-22 20:11:38',NULL);
/*!40000 ALTER TABLE `billings` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `cache`
--

DROP TABLE IF EXISTS `cache`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `cache` (
  `key` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `value` mediumtext COLLATE utf8mb4_unicode_ci NOT NULL,
  `expiration` int NOT NULL,
  PRIMARY KEY (`key`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `cache`
--

LOCK TABLES `cache` WRITE;
/*!40000 ALTER TABLE `cache` DISABLE KEYS */;
INSERT INTO `cache` VALUES ('laravel-cache-5c785c036466adea360111aa28563bfd556b5fba','i:1;',1776888309),('laravel-cache-5c785c036466adea360111aa28563bfd556b5fba:timer','i:1776888309;',1776888309);
/*!40000 ALTER TABLE `cache` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `cache_locks`
--

DROP TABLE IF EXISTS `cache_locks`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `cache_locks` (
  `key` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `owner` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
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
  `name` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `institution_id` bigint unsigned NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `career_classifications_institution_id_name_unique` (`institution_id`,`name`),
  CONSTRAINT `career_classifications_institution_id_foreign` FOREIGN KEY (`institution_id`) REFERENCES `institutions` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=24 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `career_classifications`
--

LOCK TABLES `career_classifications` WRITE;
/*!40000 ALTER TABLE `career_classifications` DISABLE KEYS */;
INSERT INTO `career_classifications` VALUES (11,'Posgrado',4,'2026-03-31 01:37:16','2026-03-31 01:37:16'),(16,'Maestria',4,'2026-04-01 18:29:26','2026-04-01 18:29:26'),(21,'Licenciatura',4,'2026-04-08 20:19:29','2026-04-08 20:19:29'),(22,'Dosctorado',4,'2026-04-10 19:15:27','2026-04-10 19:15:27');
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
INSERT INTO `career_user` VALUES (83,65),(97,65),(98,65),(102,65),(89,66),(84,67),(90,68),(97,68),(102,68),(85,69),(100,70);
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
  `official_id` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `name` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `description1` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `description2` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `description3` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `type` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `semesters` int NOT NULL DEFAULT '1',
  `pricing_mode` varchar(20) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'uniform',
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
) ENGINE=InnoDB AUTO_INCREMENT=71 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `careers`
--

LOCK TABLES `careers` WRITE;
/*!40000 ALTER TABLE `careers` DISABLE KEYS */;
INSERT INTO `careers` VALUES (65,'2026-IS-001','Ingeniería en Sistemas Computacionales','Forma profesionales capaces de diseñar, implementar y administrar sistemas informáticos en empresas públicas y privadas.','Desarrollar competencias en programación, redes y gestión de proyectos tecnológicos.','Innovador en soluciones digitales.','Presencial',4,'uniform',NULL,0,NULL,NULL,NULL,'2026-04-08 20:34:59','2026-04-08 20:34:59',4,21),(66,'2026-LD-003','Licenciatura en Derecho','Prepara abogados con conocimientos en derecho civil, penal y constitucional.','Formar profesionales que defiendan la justicia y los derechos humanos.','Defensor de la legalidad.','Presencial',2,'uniform',NULL,0,300.00,NULL,600.00,'2026-04-08 20:41:27','2026-04-12 23:15:41',4,21),(67,'2026-LAE-002','Licenciatura en Administración de Empresas','Capacita en gestión organizacional, finanzas y liderazgo empresarial.','Formar administradores capaces de dirigir y optimizar recursos humanos y financieros','Líder en gestión empresaria','En linea',1,'uniform',NULL,0,NULL,NULL,NULL,'2026-04-08 20:43:37','2026-04-08 20:43:37',4,16),(68,'2026-LP-004','Licenciatura en Psicología','Capacita en evaluación, diagnóstico y tratamiento de la conducta humana.','Promover la salud mental y el bienestar social.','Facilitador del desarrollo humano','En linea',5,'uniform',NULL,0,NULL,NULL,NULL,'2026-04-08 20:44:23','2026-04-08 20:44:23',4,16),(69,'2026-IC-005','Ingeniería Civil','Forma ingenieros capaces de diseñar, construir y supervisar obras de infraestructura.','Garantizar soluciones seguras y sostenibles en construcción.','Constructor del futuro.','Presencial',5,'uniform',NULL,0,NULL,NULL,NULL,'2026-04-08 20:46:34','2026-04-08 20:46:34',4,11),(70,'23','Quimica','Quimico',NULL,NULL,'Presencial',7,'uniform',NULL,0,1000.00,10.00,700.00,'2026-04-12 22:52:44','2026-04-22 05:38:48',4,22);
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
  `nombre` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
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
  `status` enum('cursando','aprobado','reprobado','baja') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'cursando',
  `observaciones` text COLLATE utf8mb4_unicode_ci,
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
  `grupo` varchar(10) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'A',
  `semestre` tinyint NOT NULL DEFAULT '1',
  `capacidad` int NOT NULL DEFAULT '30',
  `facility_id` bigint unsigned DEFAULT NULL,
  `horario_texto` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `status` enum('activa','finalizada','cancelada') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'activa',
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
  `clasificacion` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `producto` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `precio` decimal(10,2) NOT NULL,
  `porcentaje` decimal(5,2) NOT NULL,
  `total` decimal(10,2) NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `comisiones`
--

LOCK TABLES `comisiones` WRITE;
/*!40000 ALTER TABLE `comisiones` DISABLE KEYS */;
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
  `completable_type` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `completable_id` bigint unsigned NOT NULL,
  `score` decimal(5,2) DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `user_completion_unique` (`user_id`,`completable_id`,`completable_type`),
  KEY `completions_completable_type_completable_id_index` (`completable_type`,`completable_id`),
  CONSTRAINT `completions_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=3 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `completions`
--

LOCK TABLES `completions` WRITE;
/*!40000 ALTER TABLE `completions` DISABLE KEYS */;
INSERT INTO `completions` VALUES (1,1,'App\\Models\\Cursos\\Topics',1,NULL,'2025-12-06 18:59:20','2025-12-06 18:59:20'),(2,1,'App\\Models\\Cursos\\Activities',1,NULL,'2025-12-06 18:59:23','2025-12-06 18:59:23');
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
  `modulo` enum('becas','titulacion') COLLATE utf8mb4_unicode_ci NOT NULL,
  `nombre_documento` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `descripcion` text COLLATE utf8mb4_unicode_ci,
  `archivo_path` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `mime_type` varchar(120) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
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
  `unidad_negocio` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `rol` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
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
INSERT INTO `corporate_profiles` VALUES (1,NULL,NULL,NULL,NULL,'2026-03-11 18:04:05','2026-03-11 18:04:05'),(2,NULL,NULL,1,1,NULL,NULL),(35,NULL,NULL,NULL,NULL,'2026-03-11 18:06:21','2026-03-11 18:06:21'),(46,NULL,NULL,NULL,NULL,'2026-03-11 18:12:55','2026-03-11 18:12:55'),(47,NULL,NULL,NULL,NULL,'2026-03-11 18:03:17','2026-03-11 18:03:17'),(55,NULL,NULL,NULL,NULL,'2026-03-11 18:09:36','2026-03-11 18:09:36'),(56,NULL,NULL,NULL,NULL,'2026-03-11 18:15:26','2026-03-11 18:15:26'),(60,NULL,NULL,NULL,NULL,'2026-03-23 20:31:34','2026-03-23 20:31:34'),(61,NULL,NULL,NULL,NULL,'2026-03-23 20:42:43','2026-03-23 20:42:43'),(83,NULL,NULL,NULL,NULL,'2026-04-21 04:22:05','2026-04-21 04:22:05'),(84,NULL,NULL,NULL,NULL,'2026-04-21 04:23:02','2026-04-21 04:23:02'),(98,NULL,NULL,NULL,NULL,'2026-04-21 04:15:09','2026-04-21 04:15:09');
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
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `course_user`
--

LOCK TABLES `course_user` WRITE;
/*!40000 ALTER TABLE `course_user` DISABLE KEYS */;
INSERT INTO `course_user` VALUES (1,1,100,1,NULL,NULL);
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
  `title` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `description` text COLLATE utf8mb4_unicode_ci NOT NULL,
  `credits` int NOT NULL DEFAULT '0',
  `hours` int NOT NULL DEFAULT '0',
  `image` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `guide_material_path` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `cert_bg_path` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `cert_sig_1_path` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `cert_sig_2_path` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `cert_sig_1_name` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `cert_sig_2_name` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `instructor_id` bigint unsigned NOT NULL,
  `institution_id` bigint unsigned NOT NULL,
  `target_department_career` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL COMMENT 'Carrera (Académico) o Nombre del Departamento (Corporativo)',
  `target_job_title` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL COMMENT 'Puesto de trabajo específico para el filtrado corporativo',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `courses_instructor_id_foreign` (`instructor_id`),
  KEY `courses_institution_id_foreign` (`institution_id`),
  CONSTRAINT `courses_institution_id_foreign` FOREIGN KEY (`institution_id`) REFERENCES `institutions` (`id`) ON DELETE CASCADE,
  CONSTRAINT `courses_instructor_id_foreign` FOREIGN KEY (`instructor_id`) REFERENCES `users` (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `courses`
--

LOCK TABLES `courses` WRITE;
/*!40000 ALTER TABLE `courses` DISABLE KEYS */;
INSERT INTO `courses` VALUES (1,'Curso de prueba','Este curso es solo de prueba',0,4,'courses/ndvB9lIjNgYrVKCEDsESF4jhbRjFSWBz5rLmWB5q.png','courses/guides/6sEruJ8Fs0mnr6f4x6Pmkf6Ae06iYsCBdp00NQ2b.pdf','certificates/backgrounds/PEeJf4clWFyUD7W8G0mDaHQcIuz6WmAGz87k6FVO.png',NULL,NULL,NULL,NULL,1,1,NULL,NULL,'2025-12-06 18:50:56','2025-12-06 18:50:56');
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
  `name` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `institution_id` bigint unsigned NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `departments_institution_id_foreign` (`institution_id`),
  CONSTRAINT `departments_institution_id_foreign` FOREIGN KEY (`institution_id`) REFERENCES `institutions` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=79 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `departments`
--

LOCK TABLES `departments` WRITE;
/*!40000 ALTER TABLE `departments` DISABLE KEYS */;
INSERT INTO `departments` VALUES (1,'Talento Humano',1,'2025-12-06 18:21:35','2025-12-06 18:21:35'),(2,'Calidad',1,'2025-12-06 18:21:35','2025-12-06 18:21:35'),(72,'Contabilidad Variable',1,'2026-03-19 17:28:35','2026-03-19 17:28:35'),(73,'A&B Administración Variable',1,'2026-03-19 17:31:33','2026-03-19 17:31:33'),(74,'Kids Club Variable',1,'2026-03-19 17:35:03','2026-03-19 17:35:03'),(75,'Seguridad Variable',1,'2026-03-19 17:49:45','2026-03-19 17:49:45'),(76,'Recepción Variable',1,'2026-03-19 17:56:20','2026-03-19 17:56:20');
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
  `periodo` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `doc_acta_nacimiento` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `doc_certificado_prepa` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `doc_curp` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `doc_ine` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `doc_comprobante_domicilio` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `status` enum('Inscrito','Pendiente','Baja') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'Pendiente',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `enrollments_user_id_foreign` (`user_id`),
  KEY `enrollments_career_id_foreign` (`career_id`),
  CONSTRAINT `enrollments_career_id_foreign` FOREIGN KEY (`career_id`) REFERENCES `careers` (`id`),
  CONSTRAINT `enrollments_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=84 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `enrollments`
--

LOCK TABLES `enrollments` WRITE;
/*!40000 ALTER TABLE `enrollments` DISABLE KEYS */;
INSERT INTO `enrollments` VALUES (63,93,67,1,'1','documentos/93/expediente/DNbLoKFApEJaaxa2NqCWElFaHPp6i1swRSv5N8MQ.pdf','documentos/93/expediente/v4QNMUUjf82o5btItQKTqFIg2bfNhoDftKNnvWeM.pdf','documentos/93/expediente/B0ZATuKjs6wlrSfMRyQp6YEx8vXswAAz0kuHAE8u.pdf','documentos/93/expediente/DaLzuzFwdLK9r4WSe0Dy3NF43xzElnjZhJTz12V7.pdf',NULL,'Inscrito','2026-04-08 23:32:19','2026-04-08 23:32:34'),(64,93,67,1,'1','documentos/93/expediente/DNbLoKFApEJaaxa2NqCWElFaHPp6i1swRSv5N8MQ.pdf','documentos/93/expediente/v4QNMUUjf82o5btItQKTqFIg2bfNhoDftKNnvWeM.pdf','documentos/93/expediente/B0ZATuKjs6wlrSfMRyQp6YEx8vXswAAz0kuHAE8u.pdf','documentos/93/expediente/DaLzuzFwdLK9r4WSe0Dy3NF43xzElnjZhJTz12V7.pdf',NULL,'Inscrito','2026-04-08 23:32:34','2026-04-08 23:32:34'),(65,92,68,1,'1','documentos/92/expediente/8gDkeP6WPjW3pxSN6bU9Oyn9PhwAEZg1xuRQZBK4.pdf','documentos/92/expediente/hNadKGbACKITTv50aXC2WtF5XQuGfkYG32KFPHU4.pdf','documentos/92/expediente/93rHTsbuO2bwjfR7pfobQbYfxpyIXOp56s2XZI9Q.pdf','documentos/92/expediente/8GVfV83O0nFVAALAno3TPgXyjvomMYK8YgckEvQo.pdf',NULL,'Inscrito','2026-04-08 23:34:52','2026-04-08 23:35:04'),(66,92,68,1,'1','documentos/92/expediente/8gDkeP6WPjW3pxSN6bU9Oyn9PhwAEZg1xuRQZBK4.pdf','documentos/92/expediente/hNadKGbACKITTv50aXC2WtF5XQuGfkYG32KFPHU4.pdf','documentos/92/expediente/93rHTsbuO2bwjfR7pfobQbYfxpyIXOp56s2XZI9Q.pdf','documentos/92/expediente/8GVfV83O0nFVAALAno3TPgXyjvomMYK8YgckEvQo.pdf',NULL,'Inscrito','2026-04-08 23:35:04','2026-04-08 23:35:04'),(67,91,66,1,'1','documentos/91/expediente/Xga2fb7k8PkfVVWDwZMfTlxF9A4cLAE6nagem4gl.pdf','documentos/91/expediente/I4EJlbJ8geDD90ff8d8iL19lonziY0rHX0s9BUK2.pdf','documentos/91/expediente/KAAyG2ErURfOqNqD8DfFlNYu0eHODBCM4lQWabr9.pdf','documentos/91/expediente/bkOtK8DEepsjEfEZjNm5xN9wq6ZlSsOWEUPSt5Jf.pdf',NULL,'Inscrito','2026-04-08 23:36:27','2026-04-08 23:36:31'),(68,91,66,1,'1','documentos/91/expediente/Xga2fb7k8PkfVVWDwZMfTlxF9A4cLAE6nagem4gl.pdf','documentos/91/expediente/I4EJlbJ8geDD90ff8d8iL19lonziY0rHX0s9BUK2.pdf','documentos/91/expediente/KAAyG2ErURfOqNqD8DfFlNYu0eHODBCM4lQWabr9.pdf','documentos/91/expediente/bkOtK8DEepsjEfEZjNm5xN9wq6ZlSsOWEUPSt5Jf.pdf',NULL,'Inscrito','2026-04-08 23:36:31','2026-04-08 23:36:31'),(69,94,65,1,'1','documentos/94/expediente/ySMxaeknPUQhk8AgFPXiCSDqs8k50D7HoDVa6K1V.pdf','documentos/94/expediente/1L9WiZ9ofdRBSPqASMCY4VgHtRreVabt9b1ZrwlO.pdf','documentos/94/expediente/Uwzmd4uaWucl8KgsXSSIAmOFCs2smDOZokSfYp4v.pdf','documentos/94/expediente/eD4yHW1jYz8gy3BhiLTZ0zClrree3xunonbKBwQS.pdf',NULL,'Inscrito','2026-04-08 23:45:45','2026-04-08 23:45:56'),(70,94,65,1,'1','documentos/94/expediente/ySMxaeknPUQhk8AgFPXiCSDqs8k50D7HoDVa6K1V.pdf','documentos/94/expediente/1L9WiZ9ofdRBSPqASMCY4VgHtRreVabt9b1ZrwlO.pdf','documentos/94/expediente/Uwzmd4uaWucl8KgsXSSIAmOFCs2smDOZokSfYp4v.pdf','documentos/94/expediente/eD4yHW1jYz8gy3BhiLTZ0zClrree3xunonbKBwQS.pdf',NULL,'Inscrito','2026-04-08 23:45:56','2026-04-08 23:45:56'),(71,95,66,1,'1','documentos/95/expediente/rfyBel3xOQyXnZpoeV7mrLtihRPIBh4G7JSiTBSb.pdf','documentos/95/expediente/pht3F1IXF7hnfW6t1UjZoBI2JTh8p4EIb2jbxkSa.pdf','documentos/95/expediente/T5xbOnyHgHXpPlxnUqPdUYw69ZjQF0Zhli0sXGaO.pdf','documentos/95/expediente/TUQ4rKKto2wdDFGBe0HS0yfkvbEqNCnAe64a8xjn.pdf',NULL,'Inscrito','2026-04-12 22:19:19','2026-04-12 22:32:11'),(72,95,66,1,'1','documentos/95/expediente/rfyBel3xOQyXnZpoeV7mrLtihRPIBh4G7JSiTBSb.pdf','documentos/95/expediente/pht3F1IXF7hnfW6t1UjZoBI2JTh8p4EIb2jbxkSa.pdf','documentos/95/expediente/T5xbOnyHgHXpPlxnUqPdUYw69ZjQF0Zhli0sXGaO.pdf','documentos/95/expediente/TUQ4rKKto2wdDFGBe0HS0yfkvbEqNCnAe64a8xjn.pdf',NULL,'Inscrito','2026-04-12 22:22:43','2026-04-12 22:32:11'),(73,95,66,1,'1','documentos/95/expediente/rfyBel3xOQyXnZpoeV7mrLtihRPIBh4G7JSiTBSb.pdf','documentos/95/expediente/pht3F1IXF7hnfW6t1UjZoBI2JTh8p4EIb2jbxkSa.pdf','documentos/95/expediente/T5xbOnyHgHXpPlxnUqPdUYw69ZjQF0Zhli0sXGaO.pdf','documentos/95/expediente/TUQ4rKKto2wdDFGBe0HS0yfkvbEqNCnAe64a8xjn.pdf',NULL,'Inscrito','2026-04-12 22:25:56','2026-04-12 22:32:11'),(74,95,66,1,'1','documentos/95/expediente/rfyBel3xOQyXnZpoeV7mrLtihRPIBh4G7JSiTBSb.pdf','documentos/95/expediente/pht3F1IXF7hnfW6t1UjZoBI2JTh8p4EIb2jbxkSa.pdf','documentos/95/expediente/T5xbOnyHgHXpPlxnUqPdUYw69ZjQF0Zhli0sXGaO.pdf','documentos/95/expediente/TUQ4rKKto2wdDFGBe0HS0yfkvbEqNCnAe64a8xjn.pdf',NULL,'Inscrito','2026-04-12 22:30:03','2026-04-12 22:32:11'),(75,95,66,1,'1','documentos/95/expediente/rfyBel3xOQyXnZpoeV7mrLtihRPIBh4G7JSiTBSb.pdf','documentos/95/expediente/pht3F1IXF7hnfW6t1UjZoBI2JTh8p4EIb2jbxkSa.pdf','documentos/95/expediente/T5xbOnyHgHXpPlxnUqPdUYw69ZjQF0Zhli0sXGaO.pdf','documentos/95/expediente/TUQ4rKKto2wdDFGBe0HS0yfkvbEqNCnAe64a8xjn.pdf',NULL,'Inscrito','2026-04-12 22:31:27','2026-04-12 22:32:11'),(76,95,66,1,'1','documentos/95/expediente/rfyBel3xOQyXnZpoeV7mrLtihRPIBh4G7JSiTBSb.pdf','documentos/95/expediente/pht3F1IXF7hnfW6t1UjZoBI2JTh8p4EIb2jbxkSa.pdf','documentos/95/expediente/T5xbOnyHgHXpPlxnUqPdUYw69ZjQF0Zhli0sXGaO.pdf','documentos/95/expediente/TUQ4rKKto2wdDFGBe0HS0yfkvbEqNCnAe64a8xjn.pdf',NULL,'Inscrito','2026-04-12 22:32:11','2026-04-12 22:32:11'),(77,96,66,1,'1','documentos/96/expediente/Ju1bi423rJIwIJqwg0cdRNaK0nwBAilh44XoUr9M.pdf','documentos/96/expediente/UNeUa07L1guA30YEyksuTEYM1yGvAFONDOxcRHpJ.pdf','documentos/96/expediente/pqYvnLBUIDkl7u7nK9MlwWc6MHHCPCVdJKS3JFac.pdf','documentos/96/expediente/v4bHfcZXmyDZy8VTbyjVHaraXx8P0YN3erZlqbV1.pdf',NULL,'Inscrito','2026-04-12 23:17:55','2026-04-12 23:19:12'),(78,96,66,1,'1','documentos/96/expediente/Ju1bi423rJIwIJqwg0cdRNaK0nwBAilh44XoUr9M.pdf','documentos/96/expediente/UNeUa07L1guA30YEyksuTEYM1yGvAFONDOxcRHpJ.pdf','documentos/96/expediente/pqYvnLBUIDkl7u7nK9MlwWc6MHHCPCVdJKS3JFac.pdf','documentos/96/expediente/v4bHfcZXmyDZy8VTbyjVHaraXx8P0YN3erZlqbV1.pdf',NULL,'Inscrito','2026-04-12 23:19:05','2026-04-12 23:19:12'),(79,96,66,1,'1','documentos/96/expediente/Ju1bi423rJIwIJqwg0cdRNaK0nwBAilh44XoUr9M.pdf','documentos/96/expediente/UNeUa07L1guA30YEyksuTEYM1yGvAFONDOxcRHpJ.pdf','documentos/96/expediente/pqYvnLBUIDkl7u7nK9MlwWc6MHHCPCVdJKS3JFac.pdf','documentos/96/expediente/v4bHfcZXmyDZy8VTbyjVHaraXx8P0YN3erZlqbV1.pdf',NULL,'Inscrito','2026-04-12 23:19:12','2026-04-12 23:19:12'),(80,99,70,1,'1','documentos/99/expediente/MGqxCljDSv6qR4CMsp2FSiXkFU55hkHxLjCepACu.pdf','documentos/99/expediente/xzOykQYl1eFfySq7LcwNyhi6xPBIIU0TkVCUK4aA.pdf','documentos/99/expediente/n5Yd1on8MnurpzabpAR6qLCi53IwQnhGNRs6XugQ.pdf','documentos/99/expediente/SykX2MkWXJGgzVU2MU9gH3ugPb55THLpRNBf9vDQ.pdf',NULL,'Inscrito','2026-04-21 22:08:55','2026-04-22 02:35:07'),(81,99,70,1,'1','documentos/99/expediente/MGqxCljDSv6qR4CMsp2FSiXkFU55hkHxLjCepACu.pdf','documentos/99/expediente/xzOykQYl1eFfySq7LcwNyhi6xPBIIU0TkVCUK4aA.pdf','documentos/99/expediente/n5Yd1on8MnurpzabpAR6qLCi53IwQnhGNRs6XugQ.pdf','documentos/99/expediente/SykX2MkWXJGgzVU2MU9gH3ugPb55THLpRNBf9vDQ.pdf',NULL,'Inscrito','2026-04-22 02:35:07','2026-04-22 02:35:07'),(82,104,70,1,'1','documentos/104/expediente/OPQuJ7PCimfKTwt1ueaSd0GPx6fRhNqKCytfIRSM.pdf','documentos/104/expediente/ZUIpV5hMVP9b7feMtCRZCsll0MZQooWpfwK2sOXw.pdf','documentos/104/expediente/sl6vREUuKeytR4eKFLCNuDVlhMqJ60npgYmiKkJm.pdf','documentos/104/expediente/nTRktr6dSntLmW4vGBY0qYsAudDMe0mE9pBAikFk.pdf',NULL,'Inscrito','2026-04-22 20:09:14','2026-04-22 20:12:14'),(83,104,70,1,'1','documentos/104/expediente/OPQuJ7PCimfKTwt1ueaSd0GPx6fRhNqKCytfIRSM.pdf','documentos/104/expediente/ZUIpV5hMVP9b7feMtCRZCsll0MZQooWpfwK2sOXw.pdf','documentos/104/expediente/sl6vREUuKeytR4eKFLCNuDVlhMqJ60npgYmiKkJm.pdf','documentos/104/expediente/nTRktr6dSntLmW4vGBY0qYsAudDMe0mE9pBAikFk.pdf',NULL,'Inscrito','2026-04-22 20:12:14','2026-04-22 20:12:14');
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
  `nombre_aula` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `career_id` bigint unsigned DEFAULT NULL,
  `tipo_materia` varchar(100) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `facilities_career_id_foreign` (`career_id`),
  CONSTRAINT `facilities_career_id_foreign` FOREIGN KEY (`career_id`) REFERENCES `careers` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB AUTO_INCREMENT=14 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `facilities`
--

LOCK TABLES `facilities` WRITE;
/*!40000 ALTER TABLE `facilities` DISABLE KEYS */;
INSERT INTO `facilities` VALUES (4,'Aula Neuwton',69,'Matematica 2','2026-04-13 00:04:10','2026-04-22 05:57:49'),(5,'Aula Tesla',69,'Matemáticas Aplicadas','2026-04-22 05:58:18','2026-04-22 05:58:18'),(7,'Aula Ada Lovelace',65,'Programación I','2026-04-22 05:59:45','2026-04-22 05:59:45'),(8,'Aula Da Vinci',67,'Fundamentos de Administración','2026-04-22 06:00:16','2026-04-22 06:00:16'),(9,'Aula Curie',66,'Introducción al Derecho','2026-04-22 06:02:23','2026-04-22 06:02:23'),(11,'Aula Cervantes',68,'Psicología General','2026-04-22 06:03:13','2026-04-22 06:03:13'),(12,'Aula Sor Juana',70,'Fisica','2026-04-22 06:03:35','2026-04-22 06:03:35'),(13,'Aula Sor Juana',70,'Fisica 2','2026-04-22 06:04:05','2026-04-22 06:04:05');
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
  `uuid` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `connection` text COLLATE utf8mb4_unicode_ci NOT NULL,
  `queue` text COLLATE utf8mb4_unicode_ci NOT NULL,
  `payload` longtext COLLATE utf8mb4_unicode_ci NOT NULL,
  `exception` longtext COLLATE utf8mb4_unicode_ci NOT NULL,
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
  `carrera_nombre` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `semestre` varchar(32) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `matricula` varchar(64) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `materia_nombre` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `horario_resumen` text COLLATE utf8mb4_unicode_ci,
  `alumno_nombre` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
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
INSERT INTO `horario_clase_ocultas` VALUES (22,1,89,NULL,NULL,NULL,NULL,NULL,NULL,NULL,'2026-04-09 16:11:45','2026-04-09 16:11:45'),(23,1,90,NULL,NULL,NULL,NULL,NULL,NULL,NULL,'2026-04-09 17:27:01','2026-04-09 17:27:01'),(24,1,89,91,'Licenciatura en Derecho','1','Pendiente','Introducción al Derecho','Martes 00:00 – 15:00, Jueves 00:00 – 15:00','Camila López Castillo','2026-04-09 17:38:47','2026-04-09 17:38:47'),(27,1,93,87,'Ingeniería Civil','1','Pendiente','Matematica 2','Lunes 13:00 – 14:00','Sofía García Ramírez','2026-04-13 00:31:35','2026-04-13 00:31:35'),(28,1,89,95,'Licenciatura en Derecho','1','Pendiente','Introducción al Derecho','Martes 00:00 – 15:00, Jueves 00:00 – 15:00','Carolina López Hernández','2026-04-16 01:19:10','2026-04-16 01:19:10'),(29,1,89,96,'Licenciatura en Derecho','1','Pendiente','Introducción al Derecho','Martes 00:00 – 15:00, Jueves 00:00 – 15:00','Valeria Rosa Medel','2026-04-20 01:30:08','2026-04-20 01:30:08'),(30,1,88,86,'Ingeniería en Sistemas Computacionales','1','Pendiente','Programación I','Lunes 00:00 – 13:00, Viernes 00:00 – 13:00','Mariana Morales Estrada','2026-04-21 03:10:25','2026-04-21 03:10:25'),(31,1,90,93,'Licenciatura en Administración de Empresas','1','Pendiente','Fundamentos de Administración','Miércoles 07:00 – 08:00, Jueves 07:00 – 08:00','Valeria Ramírez Aguilar','2026-04-21 03:11:07','2026-04-21 03:11:07'),(32,1,91,92,'Licenciatura en Psicología','1','Pendiente','Psicología General','Miércoles 10:00 – 11:00','Daniela Pérez Salinas','2026-04-21 03:28:51','2026-04-21 03:28:51'),(33,1,98,99,'Quimica','1','Pendiente','Fisica','Lunes 13:00 – 14:00, Viernes 13:00 – 14:00','Maximina Márquez Gómez','2026-04-22 05:07:07','2026-04-22 05:07:07'),(34,1,99,87,'Ingeniería Civil','1','Pendiente','Matemáticas Aplicadas','Domingo 00:00 – 14:00','Sofía García Ramírez','2026-04-22 18:59:40','2026-04-22 18:59:40');
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
INSERT INTO `horario_clases` VALUES (88,20,65,83,7,'2026-04-08 22:06:05','2026-04-22 06:06:56'),(89,22,66,89,9,'2026-04-08 22:06:29','2026-04-22 06:09:31'),(90,21,67,84,8,'2026-04-08 22:06:57','2026-04-22 06:10:37'),(91,23,68,90,11,'2026-04-08 22:07:26','2026-04-22 06:11:09'),(93,25,69,85,4,'2026-04-13 00:22:27','2026-04-13 00:22:27'),(98,26,70,100,12,'2026-04-22 05:05:56','2026-04-22 06:12:18'),(99,24,69,85,5,'2026-04-22 06:15:14','2026-04-22 06:15:14');
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
INSERT INTO `horario_franjas` VALUES (378,88,'1','13:00:00','14:00:00','2026-04-22 06:06:56','2026-04-22 06:06:56'),(379,88,'2','13:00:00','14:00:00','2026-04-22 06:06:56','2026-04-22 06:06:56'),(380,89,'1','15:00:00','16:00:00','2026-04-22 06:09:31','2026-04-22 06:09:31'),(381,89,'3','15:00:00','16:00:00','2026-04-22 06:09:31','2026-04-22 06:09:31'),(382,90,'3','07:00:00','08:00:00','2026-04-22 06:10:37','2026-04-22 06:10:37'),(383,90,'4','07:00:00','08:00:00','2026-04-22 06:10:37','2026-04-22 06:10:37'),(384,91,'5','10:00:00','11:00:00','2026-04-22 06:11:09','2026-04-22 06:11:09'),(385,93,'6','09:00:00','10:00:00','2026-04-22 06:11:45','2026-04-22 06:11:45'),(386,98,'1','13:00:00','14:00:00','2026-04-22 06:12:18','2026-04-22 06:12:18'),(387,98,'5','13:00:00','14:00:00','2026-04-22 06:12:18','2026-04-22 06:12:18'),(388,99,'7','00:00:00','14:00:00','2026-04-22 06:15:14','2026-04-22 06:15:14');
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
) ENGINE=InnoDB AUTO_INCREMENT=47 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `institution_user`
--

LOCK TABLES `institution_user` WRITE;
/*!40000 ALTER TABLE `institution_user` DISABLE KEYS */;
INSERT INTO `institution_user` VALUES (1,1,1,'2026-03-24 14:52:06','2026-03-24 14:52:06'),(2,1,2,NULL,NULL),(3,1,3,NULL,NULL),(4,1,4,NULL,NULL),(5,2,4,NULL,NULL),(6,2,1,NULL,NULL),(13,58,1,NULL,NULL),(14,59,4,NULL,NULL),(15,60,4,NULL,NULL),(16,61,4,NULL,NULL),(18,64,4,NULL,NULL),(19,65,4,NULL,NULL),(20,66,4,NULL,NULL),(21,67,4,NULL,NULL),(22,68,4,NULL,NULL),(23,69,4,NULL,NULL),(24,70,4,NULL,NULL),(25,71,4,NULL,NULL),(26,72,4,NULL,NULL),(27,73,4,NULL,NULL),(28,74,4,NULL,NULL),(29,75,4,NULL,NULL),(30,86,4,NULL,NULL),(31,87,4,NULL,NULL),(32,88,4,NULL,NULL),(33,91,4,NULL,NULL),(34,92,4,NULL,NULL),(35,93,4,NULL,NULL),(36,94,4,NULL,NULL),(37,95,4,NULL,NULL),(38,96,4,NULL,NULL),(39,98,4,NULL,NULL),(40,83,4,NULL,NULL),(41,84,4,NULL,NULL),(42,99,4,NULL,NULL),(43,100,4,NULL,NULL),(44,102,4,NULL,NULL),(46,104,4,NULL,NULL);
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
  `name` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `logo_path` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
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
  `id` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `name` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `total_jobs` int NOT NULL,
  `pending_jobs` int NOT NULL,
  `failed_jobs` int NOT NULL,
  `failed_job_ids` longtext COLLATE utf8mb4_unicode_ci NOT NULL,
  `options` mediumtext COLLATE utf8mb4_unicode_ci,
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
  `queue` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `payload` longtext COLLATE utf8mb4_unicode_ci NOT NULL,
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
  `estado` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `fecha` date DEFAULT NULL,
  `hora` time DEFAULT NULL,
  `comentario` text COLLATE utf8mb4_unicode_ci,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `lead_seguimientos_lead_id_foreign` (`lead_id`),
  CONSTRAINT `lead_seguimientos_lead_id_foreign` FOREIGN KEY (`lead_id`) REFERENCES `leads` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=133 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `lead_seguimientos`
--

LOCK TABLES `lead_seguimientos` WRITE;
/*!40000 ALTER TABLE `lead_seguimientos` DISABLE KEYS */;
INSERT INTO `lead_seguimientos` VALUES (69,16,'Prospecto','2026-04-06','00:16:48',NULL,'2026-04-06 06:16:48','2026-04-06 06:16:48'),(70,16,'Prospecto frío','2026-04-06','00:17:14',NULL,'2026-04-06 06:17:14','2026-04-06 06:17:14'),(71,16,'Prospecto caliente','2026-04-06','00:18:11',NULL,'2026-04-06 06:18:11','2026-04-06 06:18:11'),(72,16,'Aspirante','2026-04-06','00:18:13',NULL,'2026-04-06 06:18:13','2026-04-06 06:18:13'),(73,17,'Prospecto','2026-04-06','00:59:43',NULL,'2026-04-06 06:59:43','2026-04-06 06:59:43'),(74,17,'Prospecto frío','2026-04-06','01:00:31',NULL,'2026-04-06 07:00:31','2026-04-06 07:00:31'),(75,17,'Prospecto caliente','2026-04-06','01:00:47',NULL,'2026-04-06 07:00:47','2026-04-06 07:00:47'),(76,17,'Aspirante','2026-04-06','01:00:49',NULL,'2026-04-06 07:00:49','2026-04-06 07:00:49'),(77,17,'Alumno','2026-04-06','01:09:38','Aspirante aceptado desde Control Escolar.','2026-04-06 07:09:38','2026-04-06 07:09:38'),(78,17,'Alumno','2026-04-06','01:13:28','Aspirante aceptado desde Control Escolar.','2026-04-06 07:13:28','2026-04-06 07:13:28'),(79,17,'Alumno','2026-04-06','01:15:05','Aspirante aceptado desde Control Escolar.','2026-04-06 07:15:05','2026-04-06 07:15:05'),(80,17,'Alumno','2026-04-06','01:15:11','Aspirante aceptado desde Control Escolar.','2026-04-06 07:15:11','2026-04-06 07:15:11'),(81,17,'Alumno','2026-04-06','01:15:15','Aspirante aceptado desde Control Escolar.','2026-04-06 07:15:15','2026-04-06 07:15:15'),(82,16,'Alumno','2026-04-06','01:15:18','Aspirante aceptado desde Control Escolar.','2026-04-06 07:15:18','2026-04-06 07:15:18'),(83,17,'Alumno','2026-04-06','01:15:21','Aspirante aceptado desde Control Escolar.','2026-04-06 07:15:21','2026-04-06 07:15:21'),(89,19,'Prospecto','2026-04-08','17:18:23',NULL,'2026-04-08 23:18:23','2026-04-08 23:18:23'),(90,20,'Prospecto','2026-04-08','17:22:42',NULL,'2026-04-08 23:22:42','2026-04-08 23:22:42'),(91,21,'Prospecto','2026-04-08','17:24:03',NULL,'2026-04-08 23:24:03','2026-04-08 23:24:03'),(92,21,'Prospecto frío','2026-04-08','17:25:06',NULL,'2026-04-08 23:25:06','2026-04-08 23:25:06'),(93,20,'Prospecto frío','2026-04-08','17:25:10',NULL,'2026-04-08 23:25:10','2026-04-08 23:25:10'),(94,19,'Prospecto frío','2026-04-08','17:25:17',NULL,'2026-04-08 23:25:17','2026-04-08 23:25:17'),(95,21,'Prospecto caliente','2026-04-08','17:25:55',NULL,'2026-04-08 23:25:55','2026-04-08 23:25:55'),(96,21,'Aspirante','2026-04-08','17:25:57',NULL,'2026-04-08 23:25:57','2026-04-08 23:25:57'),(97,20,'Prospecto caliente','2026-04-08','17:26:04',NULL,'2026-04-08 23:26:04','2026-04-08 23:26:04'),(98,20,'Aspirante','2026-04-08','17:26:07',NULL,'2026-04-08 23:26:07','2026-04-08 23:26:07'),(99,19,'Prospecto caliente','2026-04-08','17:26:11',NULL,'2026-04-08 23:26:11','2026-04-08 23:26:11'),(100,19,'Aspirante','2026-04-08','17:26:14',NULL,'2026-04-08 23:26:14','2026-04-08 23:26:14'),(101,20,'Alumno','2026-04-08','17:32:34','Aspirante aceptado desde Control Escolar.','2026-04-08 23:32:34','2026-04-08 23:32:34'),(102,21,'Alumno','2026-04-08','17:35:04','Aspirante aceptado desde Control Escolar.','2026-04-08 23:35:04','2026-04-08 23:35:04'),(103,19,'Alumno','2026-04-08','17:36:31','Aspirante aceptado desde Control Escolar.','2026-04-08 23:36:31','2026-04-08 23:36:31'),(108,23,'Prospecto','2026-04-08','17:43:22',NULL,'2026-04-08 23:43:22','2026-04-08 23:43:22'),(109,23,'Prospecto frío','2026-04-08','17:43:42',NULL,'2026-04-08 23:43:42','2026-04-08 23:43:42'),(110,23,'Prospecto caliente','2026-04-08','17:44:06',NULL,'2026-04-08 23:44:06','2026-04-08 23:44:06'),(111,23,'Aspirante','2026-04-08','17:44:08',NULL,'2026-04-08 23:44:08','2026-04-08 23:44:08'),(112,23,'Alumno','2026-04-08','17:45:56','Aspirante aceptado desde Control Escolar.','2026-04-08 23:45:56','2026-04-08 23:45:56'),(113,24,'Prospecto','2026-04-12','16:12:42',NULL,'2026-04-12 22:12:42','2026-04-12 22:12:42'),(114,24,'Prospecto frío','2026-04-12','16:14:16',NULL,'2026-04-12 22:14:16','2026-04-12 22:14:16'),(115,24,'Prospecto caliente','2026-04-12','16:14:28',NULL,'2026-04-12 22:14:28','2026-04-12 22:14:28'),(116,24,'Aspirante','2026-04-12','16:14:31',NULL,'2026-04-12 22:14:31','2026-04-12 22:14:31'),(117,24,'Alumno','2026-04-12','16:32:11','Aspirante aceptado desde Control Escolar.','2026-04-12 22:32:11','2026-04-12 22:32:11'),(118,25,'Prospecto','2026-04-12','17:00:12',NULL,'2026-04-12 23:00:12','2026-04-12 23:00:12'),(119,25,'Prospecto frío','2026-04-12','17:00:44',NULL,'2026-04-12 23:00:44','2026-04-12 23:00:44'),(120,25,'Prospecto caliente','2026-04-12','17:00:58',NULL,'2026-04-12 23:00:58','2026-04-12 23:00:58'),(121,25,'Aspirante','2026-04-12','17:01:01',NULL,'2026-04-12 23:01:01','2026-04-12 23:01:01'),(122,25,'Alumno','2026-04-12','17:19:12','Aspirante aceptado desde Control Escolar.','2026-04-12 23:19:12','2026-04-12 23:19:12'),(123,26,'Prospecto','2026-04-21','14:37:38',NULL,'2026-04-21 20:37:38','2026-04-21 20:37:38'),(124,26,'Prospecto frío','2026-04-21','14:41:50',NULL,'2026-04-21 20:41:50','2026-04-21 20:41:50'),(125,26,'Prospecto caliente','2026-04-21','15:22:18',NULL,'2026-04-21 21:22:18','2026-04-21 21:22:18'),(126,26,'Aspirante','2026-04-21','15:22:21',NULL,'2026-04-21 21:22:21','2026-04-21 21:22:21'),(127,26,'Alumno','2026-04-21','20:35:07','Aspirante aceptado desde Control Escolar.','2026-04-22 02:35:07','2026-04-22 02:35:07'),(128,27,'Prospecto','2026-04-22','13:50:09',NULL,'2026-04-22 19:50:09','2026-04-22 19:50:09'),(129,27,'Prospecto frío','2026-04-22','13:51:06',NULL,'2026-04-22 19:51:06','2026-04-22 19:51:06'),(130,27,'Prospecto caliente','2026-04-22','13:51:16',NULL,'2026-04-22 19:51:16','2026-04-22 19:51:16'),(131,27,'Aspirante','2026-04-22','13:51:19',NULL,'2026-04-22 19:51:19','2026-04-22 19:51:19'),(132,27,'Alumno','2026-04-22','14:12:14','Aspirante aceptado desde Control Escolar.','2026-04-22 20:12:14','2026-04-22 20:12:14');
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
  `tutor_nombre` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `tutor_paterno` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `tutor_materno` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `tutor_curp` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `telefono1` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `telefono2` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `tutor_email` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `alumno_nombre` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `alumno_paterno` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `alumno_materno` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `alumno_email` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `alumno_telefono` varchar(30) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `alumno_curp` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `origen` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'formulario_publico',
  `clasificacion` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'nuevo',
  `doc_acta_nacimiento` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `doc_certificado_prepa` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `doc_curp` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `doc_ine` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `comentario_reasignacion` text COLLATE utf8mb4_unicode_ci,
  `carrera_id` bigint unsigned DEFAULT NULL,
  `semestre` tinyint unsigned DEFAULT '1',
  `user_id` bigint unsigned DEFAULT NULL,
  `doc_acta_rechazado` tinyint(1) NOT NULL DEFAULT '0',
  `doc_certificado_rechazado` tinyint(1) NOT NULL DEFAULT '0',
  `doc_curp_rechazado` tinyint(1) NOT NULL DEFAULT '0',
  `doc_ine_rechazado` tinyint(1) NOT NULL DEFAULT '0',
  `doc_ficha_pago` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `doc_factura_xml` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `doc_ficha_pago_rechazado` tinyint(1) NOT NULL DEFAULT '0',
  `doc_factura_xml_rechazado` tinyint(1) NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`),
  KEY `leads_ctp_id_foreign` (`ctp_id`),
  KEY `leads_user_id_foreign` (`user_id`),
  KEY `leads_carrera_id_foreign` (`carrera_id`),
  CONSTRAINT `leads_carrera_id_foreign` FOREIGN KEY (`carrera_id`) REFERENCES `careers` (`id`) ON DELETE SET NULL,
  CONSTRAINT `leads_ctp_id_foreign` FOREIGN KEY (`ctp_id`) REFERENCES `users` (`id`) ON DELETE SET NULL,
  CONSTRAINT `leads_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB AUTO_INCREMENT=28 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `leads`
--

LOCK TABLES `leads` WRITE;
/*!40000 ALTER TABLE `leads` DISABLE KEYS */;
INSERT INTO `leads` VALUES (16,60,'Ricardo','Morales','Estrada','ROME850623HDFLNS0','7443342191','7447654','ricardo.morales@example.com','Mariana','Morales','Estrada',NULL,NULL,'MARE201015MGRLNS08','formulario_publico','Prospecto','documentos/86/expediente/Z45UBfTdmCVJEygENQQny3SEkNvMbzGYhYXoL5gT.pdf','documentos/86/expediente/3siOYTEi7EJtpxf5Qm0pkzxqiF8NSNK6aSyKmiw0.pdf','documentos/86/expediente/rAEnxjdHF278QDxmVJq6y62uMXFmrPSb29pU70Qp.pdf','documentos/86/expediente/XaesJB9BPqggCns6sj2lpFMNuZFsGIKSWfAA6zyJ.pdf','2026-04-06 06:16:48','2026-04-08 22:28:38',NULL,65,1,NULL,0,0,0,0,'facturas/86/OQTJsne8QGuMVanwbLUT5Zn4jjmNw4M6MzXLht8n.pdf',NULL,0,0),(17,60,'Javier','García','Ramírez','GARC790412HDFRRL0','7443342191','55987654','javier.garcia@example.org','Sofía','García','Ramírez',NULL,NULL,'GARA200908MDFRRL07','formulario_publico','Prospecto','documentos/87/expediente/3sT3xuS0HLnXKlawJIHhAEvoprwnKyPffs69hnob.pdf','documentos/87/expediente/1uQyb3eDcT8qlQtEqkDH9mNnN1J8txbeQhJ4QYv6.pdf','documentos/87/expediente/1TPm6Qx4kgX0Yf1JajNH4HEFToXiWAPD0MdpZmLu.pdf','documentos/87/expediente/46wJys0JevrsH4h7EYkJtvmBjmh8vBGWvvB16Kmy.pdf','2026-04-06 06:59:43','2026-04-08 22:28:31',NULL,69,1,NULL,0,0,0,0,'facturas/87/Wp6RCRykzY975H0ySRZDW4Yxej7iiMQBi5HRNich.pdf',NULL,0,0),(19,60,'Manuel','García','Rivera','MARG750812HGRRZN05','74488822','74477733','manuel.garciar@example.com','Camila','López','Castillo',NULL,NULL,'LOPC101215MGRRZS08','formulario_publico','Prospecto','documentos/91/expediente/Xga2fb7k8PkfVVWDwZMfTlxF9A4cLAE6nagem4gl.pdf','documentos/91/expediente/I4EJlbJ8geDD90ff8d8iL19lonziY0rHX0s9BUK2.pdf','documentos/91/expediente/KAAyG2ErURfOqNqD8DfFlNYu0eHODBCM4lQWabr9.pdf','documentos/91/expediente/bkOtK8DEepsjEfEZjNm5xN9wq6ZlSsOWEUPSt5Jf.pdf','2026-04-08 23:18:23','2026-04-08 23:36:27',NULL,66,1,NULL,0,0,0,0,NULL,NULL,0,0),(20,60,'Javier','Herrera','Sánchez','HERR680921HDFLNS03','74466611','74455522','javier.herreras@example.com','Valeria','Ramírez','Aguilar',NULL,NULL,'RAMA120506MDFLNR07','formulario_publico','Prospecto','documentos/93/expediente/DNbLoKFApEJaaxa2NqCWElFaHPp6i1swRSv5N8MQ.pdf','documentos/93/expediente/v4QNMUUjf82o5btItQKTqFIg2bfNhoDftKNnvWeM.pdf','documentos/93/expediente/B0ZATuKjs6wlrSfMRyQp6YEx8vXswAAz0kuHAE8u.pdf','documentos/93/expediente/DaLzuzFwdLK9r4WSe0Dy3NF43xzElnjZhJTz12V7.pdf','2026-04-08 23:22:42','2026-04-08 23:32:19',NULL,67,1,NULL,0,0,0,0,'facturas/93/9IxhuOhVZlgRGKn1SFPp3H93A928pWqNgZ8DfGa0.pdf',NULL,0,0),(21,60,'Ernesto','Gómez','Luna','GOME720315HMSLRN04','74444488','74433399','ernesto.gomezl@example.com','Daniela','Pérez','Salinas',NULL,NULL,'PERE140918MMSLRS09','formulario_publico','Prospecto','documentos/92/expediente/8gDkeP6WPjW3pxSN6bU9Oyn9PhwAEZg1xuRQZBK4.pdf','documentos/92/expediente/hNadKGbACKITTv50aXC2WtF5XQuGfkYG32KFPHU4.pdf','documentos/92/expediente/93rHTsbuO2bwjfR7pfobQbYfxpyIXOp56s2XZI9Q.pdf','documentos/92/expediente/8GVfV83O0nFVAALAno3TPgXyjvomMYK8YgckEvQo.pdf','2026-04-08 23:24:03','2026-04-08 23:34:52',NULL,68,1,NULL,0,0,0,0,NULL,NULL,0,0),(23,60,'José Antonio','Rivera','Jiménez','RIVJ710812HMSLNR06','7443342191','74488855','jose.riveraj@example.com','Lucía','Castillo','Navarro',NULL,NULL,'CAST150327MMSLNS02','formulario_publico','Prospecto','documentos/94/expediente/ySMxaeknPUQhk8AgFPXiCSDqs8k50D7HoDVa6K1V.pdf','documentos/94/expediente/1L9WiZ9ofdRBSPqASMCY4VgHtRreVabt9b1ZrwlO.pdf','documentos/94/expediente/Uwzmd4uaWucl8KgsXSSIAmOFCs2smDOZokSfYp4v.pdf','documentos/94/expediente/eD4yHW1jYz8gy3BhiLTZ0zClrree3xunonbKBwQS.pdf','2026-04-08 23:43:21','2026-04-09 00:18:01',NULL,69,1,NULL,0,0,0,0,NULL,NULL,0,0),(24,60,'Martín José','Gómez','Ramírez','IOMJ750812HDFRRL0','7445821937','74432647','martin.gomez.ramirez@example.com','Carolina','López','Hernández',NULL,NULL,'ZOPC120305MDFNRN08','formulario_publico','Prospecto','documentos/95/expediente/rfyBel3xOQyXnZpoeV7mrLtihRPIBh4G7JSiTBSb.pdf','documentos/95/expediente/pht3F1IXF7hnfW6t1UjZoBI2JTh8p4EIb2jbxkSa.pdf','documentos/95/expediente/T5xbOnyHgHXpPlxnUqPdUYw69ZjQF0Zhli0sXGaO.pdf','documentos/95/expediente/TUQ4rKKto2wdDFGBe0HS0yfkvbEqNCnAe64a8xjn.pdf','2026-04-12 22:12:42','2026-04-12 22:31:27',NULL,66,1,NULL,0,0,0,0,'facturas/95/VmK7ZN5szI0ajy1iRlsi1ITTkbhF2Jal1FtiknMJ.pdf',NULL,0,0),(25,60,'Carlos','Rivera','Núñez','RIVC680921HDFNLL05','7445821937','3310987654','carlos.rivera@testmail.com','Valeria','Rosa','Medel',NULL,NULL,'RIVC200805HDFNLL07','formulario_publico','Prospecto',NULL,'documentos/96/expediente/UNeUa07L1guA30YEyksuTEYM1yGvAFONDOxcRHpJ.pdf','documentos/96/expediente/pqYvnLBUIDkl7u7nK9MlwWc6MHHCPCVdJKS3JFac.pdf','documentos/96/expediente/v4bHfcZXmyDZy8VTbyjVHaraXx8P0YN3erZlqbV1.pdf','2026-04-12 23:00:12','2026-04-21 05:14:32',NULL,66,1,NULL,0,0,0,0,NULL,NULL,1,0),(26,98,'Josefina','García','Sánchez','JOFG850312MGRSSS01','7443342191','7449876543','josefina.garcia@testmail.com','Maximina','Márquez','Gómez',NULL,NULL,'MAMG920715MGRSSS02','formulario_publico','Prospecto','documentos/99/expediente/MGqxCljDSv6qR4CMsp2FSiXkFU55hkHxLjCepACu.pdf','documentos/99/expediente/xzOykQYl1eFfySq7LcwNyhi6xPBIIU0TkVCUK4aA.pdf','documentos/99/expediente/n5Yd1on8MnurpzabpAR6qLCi53IwQnhGNRs6XugQ.pdf','documentos/99/expediente/SykX2MkWXJGgzVU2MU9gH3ugPb55THLpRNBf9vDQ.pdf','2026-04-21 20:37:38','2026-04-22 14:27:46',NULL,70,1,NULL,0,0,0,0,'facturas/99/A6zRD3mTEnCnokUx3ZF0UmIds8yX0YVqpUhWfoU9.pdf',NULL,0,0),(27,60,'Ricardo','Hernández','López','MAFJ850421HDFLRN01','7443342191','7449876543','ricardo.hdz@example.com','Laura','Martínez','Pérez',NULL,NULL,'GUHJ920422MDFPLS02','formulario_publico','Prospecto','documentos/104/expediente/OPQuJ7PCimfKTwt1ueaSd0GPx6fRhNqKCytfIRSM.pdf','documentos/104/expediente/ZUIpV5hMVP9b7feMtCRZCsll0MZQooWpfwK2sOXw.pdf','documentos/104/expediente/sl6vREUuKeytR4eKFLCNuDVlhMqJ60npgYmiKkJm.pdf','documentos/104/expediente/nTRktr6dSntLmW4vGBY0qYsAudDMe0mE9pBAikFk.pdf','2026-04-22 19:50:09','2026-04-22 20:11:38',NULL,70,1,NULL,0,0,0,0,'facturas/104/UTHGqbeYlFQK7hZwhLgY0AJyaEumgghf2wlxHby1.pdf',NULL,0,0);
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
  `nombre` varchar(100) COLLATE utf8mb4_unicode_ci NOT NULL,
  `clave` varchar(10) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `creditos` int NOT NULL,
  `career_id` bigint unsigned NOT NULL,
  `descripcion` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `objetivo` text COLLATE utf8mb4_unicode_ci,
  `temario` text COLLATE utf8mb4_unicode_ci,
  `infografia` text COLLATE utf8mb4_unicode_ci,
  `type` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `semestre` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
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
INSERT INTO `materias` VALUES (20,'Programación I',NULL,10,65,'',NULL,NULL,NULL,'Presencial','1','2026-04-08 20:49:48','2026-04-08 20:53:08'),(21,'Fundamentos de Administración',NULL,1,67,'',NULL,NULL,NULL,'En linea','1','2026-04-08 20:50:26','2026-04-08 20:50:36'),(22,'Introducción al Derecho',NULL,5,66,'',NULL,NULL,NULL,'Presencial','1','2026-04-08 20:52:03','2026-04-16 01:15:31'),(23,'Psicología General',NULL,4,68,'',NULL,NULL,NULL,'En linea','1','2026-04-08 20:52:31','2026-04-16 01:15:41'),(24,'Matemáticas Aplicadas',NULL,4,69,'',NULL,NULL,NULL,'En linea','1','2026-04-08 20:52:55','2026-04-08 20:52:55'),(25,'Matematica 2',NULL,1,69,'',NULL,NULL,NULL,'Presencial','1','2026-04-09 19:03:46','2026-04-09 19:03:46'),(26,'Fisica',NULL,1,70,'nose','nose','nose','hola','Presencial','1','2026-04-13 23:30:11','2026-04-22 05:47:29'),(27,'Fisica 2',NULL,1,70,'',NULL,NULL,NULL,'Presencial','1','2026-04-13 23:32:43','2026-04-13 23:32:43');
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
  `migration` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
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
  `metodo_pago` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `nota` text COLLATE utf8mb4_unicode_ci,
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
  `name` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
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
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `periods`
--

LOCK TABLES `periods` WRITE;
/*!40000 ALTER TABLE `periods` DISABLE KEYS */;
INSERT INTO `periods` VALUES (1,'AGO 2025 - DIC 2025','2025-12-06','2026-06-06',NULL,NULL,NULL,1,4,'2025-12-06 18:21:36','2025-12-06 18:21:36');
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
  `name` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `display_name` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
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
  `id` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `user_id` bigint unsigned DEFAULT NULL,
  `ip_address` varchar(45) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `user_agent` text COLLATE utf8mb4_unicode_ci,
  `payload` longtext COLLATE utf8mb4_unicode_ci NOT NULL,
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
INSERT INTO `sessions` VALUES ('EEdnyDM1it2Pi5gzzP4JdKMRKbttbihF7ezWUBq9',1,'127.0.0.1','Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/140.0.0.0 Safari/537.36 Edg/140.0.0.0','YToxMjp7czo2OiJfdG9rZW4iO3M6NDA6IlAxdWIzblRoenJIS0thQmNNZ1pXaEZBVkJKOGs5WUNXRGVnZEJtQVYiO3M6NjoiX2ZsYXNoIjthOjI6e3M6Mzoib2xkIjthOjA6e31zOjM6Im5ldyI7YTowOnt9fXM6OToiX3ByZXZpb3VzIjthOjE6e3M6MzoidXJsIjtzOjUxOiJodHRwOi8vMTI3LjAuMC4xOjgwMDAvY29udHJvbC1lc2NvbGFyL2xpc3RhLWFsdW1ub3MiO31zOjM6InVybCI7YToxOntzOjg6ImludGVuZGVkIjtzOjM2OiJodHRwOi8vMTI3LjAuMC4xOjgwMDAvbWktaW5mb3JtYWNpb24iO31zOjUwOiJsb2dpbl93ZWJfNTliYTM2YWRkYzJiMmY5NDAxNTgwZjAxNGM3ZjU4ZWE0ZTMwOTg5ZCI7aToxO3M6MTg6ImNvbnRleHRfcHJlZmVyZW5jZSI7czo5OiJjb3Jwb3JhdGUiO3M6MjE6ImFjdGl2ZV9pbnN0aXR1dGlvbl9pZCI7aTo0O3M6MTQ6ImFjdGl2ZV9yb2xlX2lkIjtpOjE7czoxNjoiYWN0aXZlX3JvbGVfbmFtZSI7czo2OiJtYXN0ZXIiO3M6MjM6ImFjdGl2ZV9pbnN0aXR1dGlvbl9uYW1lIjtzOjI2OiJVbml2ZXJzaWRhZCBNdW5kbyBJbXBlcmlhbCI7czoyNDoiYWN0aXZlX3JvbGVfZGlzcGxheV9uYW1lIjtzOjY6Ik1hc3RlciI7czoyMzoiYWN0aXZlX2luc3RpdHV0aW9uX2xvZ28iO047fQ==',1776888789),('YURla1LdpUf7CJ4Zp1QxqhR5LQ7CUvLjmPWVdGKG',104,'127.0.0.1','Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/147.0.0.0 Safari/537.36','YToxMjp7czo2OiJfdG9rZW4iO3M6NDA6IjNZTDhQeVdYd1U1YmxkSTNySFVPcUFlQzdzQVVwaXZNTjZQclJ2cGEiO3M6NjoiX2ZsYXNoIjthOjI6e3M6Mzoib2xkIjthOjA6e31zOjM6Im5ldyI7YTowOnt9fXM6OToiX3ByZXZpb3VzIjthOjE6e3M6MzoidXJsIjtzOjMyOiJodHRwOi8vMTI3LjAuMC4xOjgwMDAvYmllbnZlbmlkbyI7fXM6MzoidXJsIjthOjE6e3M6ODoiaW50ZW5kZWQiO3M6MzY6Imh0dHA6Ly8xMjcuMC4wLjE6ODAwMC9taS1pbmZvcm1hY2lvbiI7fXM6NTA6ImxvZ2luX3dlYl81OWJhMzZhZGRjMmIyZjk0MDE1ODBmMDE0YzdmNThlYTRlMzA5ODlkIjtpOjEwNDtzOjE4OiJjb250ZXh0X3ByZWZlcmVuY2UiO3M6OToiY29ycG9yYXRlIjtzOjIxOiJhY3RpdmVfaW5zdGl0dXRpb25faWQiO2k6NDtzOjE0OiJhY3RpdmVfcm9sZV9pZCI7aTo3O3M6MTY6ImFjdGl2ZV9yb2xlX25hbWUiO3M6MTA6ImVzdHVkaWFudGUiO3M6MjM6ImFjdGl2ZV9pbnN0aXR1dGlvbl9uYW1lIjtzOjI2OiJVbml2ZXJzaWRhZCBNdW5kbyBJbXBlcmlhbCI7czoyNDoiYWN0aXZlX3JvbGVfZGlzcGxheV9uYW1lIjtzOjY6IkFsdW1ubyI7czoyMzoiYWN0aXZlX2luc3RpdHV0aW9uX2xvZ28iO047fQ==',1776888808);
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
  `title` varchar(150) COLLATE utf8mb4_unicode_ci NOT NULL,
  `description` text COLLATE utf8mb4_unicode_ci,
  `file_path` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
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
  `targetable_type` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
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
  `nombre_documento` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `descripcion` text COLLATE utf8mb4_unicode_ci,
  `archivo_path` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `mime_type` varchar(120) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
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
INSERT INTO `titulacion_documentos` VALUES (1,91,'nose','wkdmksamdksa','documentos/91/titulacion/KGtAcMRAhlxso8gIg4i9N7FRO6cWbax64ozMPcRT.pdf','application/pdf',815214,1,'2026-04-22 13:52:35','2026-04-22 13:52:35',NULL),(2,92,'nose','dvdvdvds','documentos/92/titulacion/7y8aIm1t1XuIxofR6famK1g21NbDPwCcZlZISDDM.pdf','application/pdf',815214,1,'2026-04-22 13:54:04','2026-04-22 13:54:04',NULL);
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
  `title` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `description` text COLLATE utf8mb4_unicode_ci,
  `file_path` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
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
) ENGINE=InnoDB AUTO_INCREMENT=123 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `user_roles_institution`
--

LOCK TABLES `user_roles_institution` WRITE;
/*!40000 ALTER TABLE `user_roles_institution` DISABLE KEYS */;
INSERT INTO `user_roles_institution` VALUES (2,1,1,1,2,'2026-01-26 19:29:07','2026-01-26 19:29:07'),(3,1,1,1,3,'2026-01-26 19:29:07','2026-01-26 19:29:07'),(4,1,1,1,4,'2026-01-26 19:29:07','2026-01-26 19:29:07'),(5,2,7,1,4,'2026-01-26 19:29:07','2026-01-26 19:29:07'),(6,2,4,1,1,'2026-01-26 19:29:07','2026-01-26 19:29:07'),(7,3,7,1,4,'2025-12-06 18:21:37','2025-12-06 18:21:37'),(8,4,7,1,4,'2025-12-06 18:21:37','2025-12-06 18:21:37'),(9,5,7,1,4,'2025-12-06 18:21:37','2025-12-06 18:21:37'),(10,6,7,1,4,'2025-12-06 18:21:38','2025-12-06 18:21:38'),(11,7,7,1,4,'2025-12-06 18:21:38','2025-12-06 18:21:38'),(12,8,7,1,4,'2025-12-06 18:21:39','2025-12-06 18:21:39'),(13,9,7,1,4,'2025-12-06 18:21:39','2025-12-06 18:21:39'),(14,10,7,1,4,'2025-12-06 18:21:40','2025-12-06 18:21:40'),(15,11,7,1,4,'2025-12-06 18:21:40','2025-12-06 18:21:40'),(22,16,6,1,4,'2026-01-30 19:15:22','2026-01-30 19:15:22'),(23,19,6,1,4,'2026-01-30 19:41:59','2026-01-30 19:41:59'),(24,23,6,1,4,'2026-01-30 23:04:11','2026-01-30 23:04:11'),(25,25,6,1,4,'2026-01-31 00:20:37','2026-01-31 00:20:37'),(26,33,6,1,4,'2026-02-05 20:44:22','2026-02-05 20:44:22'),(28,36,6,1,4,'2026-02-18 21:40:48','2026-02-18 21:40:48'),(29,37,6,1,4,'2026-02-18 21:43:47','2026-02-18 21:43:47'),(30,38,6,1,4,'2026-02-18 21:45:45','2026-02-18 21:45:45'),(31,39,6,1,4,'2026-02-18 21:48:59','2026-02-18 21:48:59'),(32,40,6,1,4,'2026-02-18 21:50:43','2026-02-18 21:50:43'),(33,42,7,1,4,'2026-02-20 05:06:23','2026-02-20 05:06:23'),(34,43,7,1,4,'2026-02-20 05:07:45','2026-02-20 05:07:45'),(35,44,7,1,4,'2026-02-20 05:47:21','2026-02-20 05:47:21'),(36,45,7,1,4,'2026-02-20 05:51:35','2026-02-20 05:51:35'),(39,48,7,1,4,'2026-02-20 06:06:28','2026-02-20 06:06:28'),(40,49,7,1,4,'2026-02-20 06:08:41','2026-02-20 06:08:41'),(41,50,7,1,4,'2026-03-05 02:13:25','2026-03-05 02:13:25'),(42,51,7,1,4,'2026-03-05 02:58:12','2026-03-05 02:58:12'),(43,52,7,1,4,'2026-03-10 18:55:33','2026-03-10 18:55:33'),(44,53,7,1,4,'2026-03-10 18:57:40','2026-03-10 18:57:40'),(45,54,7,1,4,'2026-03-10 18:58:27','2026-03-10 18:58:27'),(48,1,9,1,4,'2026-03-11 18:04:05','2026-03-11 18:04:05'),(58,58,7,1,1,'2026-03-13 22:28:25','2026-03-13 22:28:25'),(59,59,7,1,4,'2026-03-13 22:29:02','2026-03-13 22:29:02'),(71,60,8,1,4,'2026-03-23 20:50:42','2026-03-23 20:50:42'),(72,62,7,1,4,'2026-03-23 22:28:58','2026-03-23 22:28:58'),(75,1,1,1,1,'2026-03-24 15:19:35','2026-03-24 15:19:35'),(77,64,7,1,4,'2026-03-24 15:52:33','2026-03-24 15:52:33'),(78,65,7,1,4,'2026-03-24 21:54:18','2026-03-24 21:54:18'),(79,66,7,1,4,'2026-03-26 18:18:34','2026-03-26 18:18:34'),(80,67,7,1,4,'2026-03-26 18:37:51','2026-03-26 21:33:02'),(81,68,7,1,4,'2026-03-26 21:53:13','2026-03-30 00:43:30'),(82,69,7,1,4,'2026-03-30 01:06:02','2026-03-30 04:02:11'),(83,70,7,1,4,'2026-03-30 04:10:56','2026-03-30 04:13:41'),(84,71,7,1,4,'2026-03-30 05:01:26','2026-03-30 06:08:30'),(85,72,7,1,4,'2026-03-30 05:19:17','2026-03-30 08:00:50'),(86,73,7,1,4,'2026-03-30 08:14:25','2026-03-30 08:48:01'),(88,61,9,1,4,'2026-03-30 22:26:36','2026-03-30 22:26:36'),(89,74,7,1,4,'2026-03-30 23:07:20','2026-04-06 06:11:41'),(90,75,7,1,4,'2026-03-30 23:18:43','2026-03-31 01:20:31'),(91,76,6,1,4,'2026-04-01 21:44:30','2026-04-01 21:44:30'),(92,77,6,1,4,'2026-04-01 23:45:07','2026-04-01 23:45:07'),(93,79,6,1,4,'2026-04-02 00:37:55','2026-04-02 00:37:55'),(94,80,6,1,4,'2026-04-02 00:55:03','2026-04-02 00:55:03'),(95,81,6,1,4,'2026-04-02 00:57:03','2026-04-02 00:57:03'),(96,82,6,1,4,'2026-04-02 00:58:31','2026-04-02 00:58:31'),(97,83,6,1,4,'2026-04-02 04:32:25','2026-04-02 04:32:25'),(98,84,6,1,4,'2026-04-02 04:37:43','2026-04-02 04:37:43'),(99,85,6,1,4,'2026-04-02 04:40:18','2026-04-02 04:40:18'),(100,86,7,1,4,'2026-04-06 06:25:18','2026-04-06 07:15:18'),(101,87,7,1,4,'2026-04-06 07:01:17','2026-04-06 07:15:21'),(102,88,7,1,4,'2026-04-06 07:21:14','2026-04-06 07:30:37'),(103,89,6,1,4,'2026-04-08 21:14:54','2026-04-08 21:14:54'),(104,90,6,1,4,'2026-04-08 21:18:03','2026-04-08 21:18:03'),(105,91,7,1,4,'2026-04-08 23:26:53','2026-04-08 23:36:31'),(106,92,7,1,4,'2026-04-08 23:27:07','2026-04-08 23:35:04'),(107,93,7,1,4,'2026-04-08 23:27:23','2026-04-08 23:32:34'),(108,94,7,1,4,'2026-04-08 23:44:29','2026-04-08 23:45:56'),(109,95,7,1,4,'2026-04-12 22:16:21','2026-04-12 22:32:11'),(110,96,7,1,4,'2026-04-12 23:01:25','2026-04-12 23:19:12'),(111,97,6,1,4,'2026-04-13 16:57:13','2026-04-13 16:57:13'),(114,83,8,1,4,'2026-04-21 04:22:05','2026-04-21 04:22:05'),(115,84,8,1,4,'2026-04-21 04:23:02','2026-04-21 04:23:02'),(116,98,8,1,4,'2026-04-21 21:21:48','2026-04-21 21:21:48'),(117,99,7,1,4,'2026-04-21 21:23:00','2026-04-22 02:35:07'),(118,100,6,1,4,'2026-04-22 05:05:00','2026-04-22 05:05:00'),(119,102,6,1,4,'2026-04-22 07:07:44','2026-04-22 07:07:44'),(122,104,7,1,4,'2026-04-22 19:52:05','2026-04-22 20:12:14');
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
  `nombre` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `apellido_paterno` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `apellido_materno` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `email` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `password` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `is_active` tinyint(1) NOT NULL DEFAULT '1',
  `RFC` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `curp` varchar(18) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `telefono` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `fecha_nacimiento` date DEFAULT NULL,
  `edad` int DEFAULT NULL,
  `role_id` bigint unsigned DEFAULT NULL,
  `address_id` bigint unsigned DEFAULT NULL,
  `institution_id` bigint unsigned NOT NULL,
  `department_id` bigint unsigned DEFAULT NULL,
  `workstation_id` bigint unsigned DEFAULT NULL,
  `remember_token` varchar(100) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
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
) ENGINE=InnoDB AUTO_INCREMENT=105 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `users`
--

LOCK TABLES `users` WRITE;
/*!40000 ALTER TABLE `users` DISABLE KEYS */;
INSERT INTO `users` VALUES (1,'Esteban','Rivers','Molina','master@UMI.com','$2y$12$HTT2PUmps3fbCiOhXRTzDetP6aD7OPAge5fyFV752ZjmdZBSYAaVK',1,'XAXX010101000',NULL,NULL,NULL,NULL,1,NULL,1,2,NULL,NULL,'2025-12-06 18:21:36','2026-03-24 15:19:35',NULL),(2,'Tribilin','Cobo','Loquendo','multirol@UMI.com','$2y$12$aQZxDddUUMqRilMkGVTB1uQ3f/KROc7KPweHrPvWHlgO5Kqz6qZF.',1,'GAPX010101XYZ',NULL,NULL,NULL,NULL,7,NULL,4,1,1,NULL,'2025-12-06 18:21:36','2026-02-20 04:12:35','2026-02-20 04:12:35'),(3,'Aspirante','Nuevo 1','Prueba','aspirante1@umi.edu.mx','$2y$12$4J0Xl2pdvRJyZFmRYn4uF.xsT0uRurcD4lQKj0avb.0Xw1/bELode',1,'TEST7430C6E7A',NULL,'7440000000','2000-01-01',25,7,1,4,NULL,NULL,NULL,'2025-12-06 18:21:37','2026-02-20 04:10:18','2026-02-20 04:10:18'),(4,'Aspirante','Nuevo 2','Prueba','aspirante2@umi.edu.mx','$2y$12$cPUBGjEgB1mIVPHMmQJ.RekVtmuumx/6hCqeWnzo/eGld1YAe5t62',1,'TEST743133DBE',NULL,'7440000000','2000-01-01',25,7,2,4,NULL,NULL,NULL,'2025-12-06 18:21:37','2026-02-20 04:12:46','2026-02-20 04:12:46'),(5,'Aspirante','Nuevo 3','Prueba','aspirante3@umi.edu.mx','$2y$12$WMIG0ZAjGYIoXqIuPz5mJuCDxSTKizy.GDov3UReO0BtSAEoxJ86q',1,'TEST74319037C',NULL,'7440000000','2000-01-01',25,7,3,4,NULL,NULL,NULL,'2025-12-06 18:21:37','2026-02-20 04:12:38','2026-02-20 04:12:38'),(6,'Pagador','Listo 1','Prueba','pagado1@umi.edu.mx','$2y$12$U1eeTBW3rBO0A0T4o3VdluxnctLxxMsvrX4LlMYvZHKtQo4sCmvsS',1,'TEST7431E1F1C',NULL,'7440000000','2000-01-01',25,7,4,4,NULL,NULL,NULL,'2025-12-06 18:21:38','2026-02-20 04:10:11','2026-02-20 04:10:11'),(7,'Pagador','Listo 2','Prueba','pagado2@umi.edu.mx','$2y$12$.e/j0So7tchjdRNqFY2MyOE0L8/VnWgXwy87o99vluQ1kMFHMVsEK',1,'TEST74325B301',NULL,'7440000000','2000-01-01',25,7,5,4,NULL,NULL,NULL,'2025-12-06 18:21:38','2026-02-20 04:10:14','2026-02-20 04:10:14'),(8,'Pagador','Listo 3','Prueba','pagado3@umi.edu.mx','$2y$12$gX4QE7dLN.6a7jQYDIcWz.HBpJkTQBCYJDtgXmdcb2MJOcmgxUCvG',1,'TEST7432CC974',NULL,'7440000000','2000-01-01',25,7,6,4,NULL,NULL,NULL,'2025-12-06 18:21:39','2026-02-20 04:10:03','2026-02-20 04:10:03'),(9,'Alumno','Activo 1','Prueba','activo1@umi.edu.mx','$2y$12$STcZWNFWWEUC.Ye1L4NXAui8FYZcZGhSNQFMFG.vbrKW8KxjtdV7O',1,'TEST7433495FA',NULL,'7440000000','2000-01-01',25,7,7,4,NULL,NULL,NULL,'2025-12-06 18:21:39','2026-02-20 04:10:07','2026-02-20 04:10:07'),(10,'Alumno','Activo 2','Prueba','activo2@umi.edu.mx','$2y$12$NfFkvhbMJcg9tNWPJF4epOUYIBj/Q1B7lb2acFG6ZrSCPCK3184Qe',1,'TEST7433AF530',NULL,'7440000000','2000-01-01',25,7,8,4,NULL,NULL,NULL,'2025-12-06 18:21:40','2026-02-20 04:09:57','2026-02-20 04:09:57'),(11,'Alumno','Activo 3','Prueba','activo3@umi.edu.mx','$2y$12$nejHQaJFqOZZBDtAQnWe1uB0a16SQJzVPPwd0T8a28rz8oZke.rTe',1,'TEST74341076D',NULL,'7440000000','2000-01-01',25,7,9,4,NULL,NULL,NULL,'2025-12-06 18:21:40','2026-02-20 04:10:00','2026-02-20 04:10:00'),(16,'Jose Gulmaro','Marquez','Flores','josegulmaro@gmail.com','$2y$12$CmC..bO4CFSLDzCv91XG7uBt2DPAZ/OFeUgmnbYRP7xphOiN75ZYW',1,'MAAAAAAAAAAA',NULL,'3243243241','2004-03-05',23,NULL,12,4,NULL,NULL,NULL,'2026-01-30 19:15:22','2026-01-30 19:17:59','2026-01-30 19:17:59'),(19,'Jose','Marquez','Flor','marquezgulmaro@gmail.com','$2y$12$RpFZnem1JuBJJZzjVggvNuvwub9DhQ6eQO0c0p2Yn4IXsnSxrRePS',1,'XAXX0100',NULL,'32432432','2004-03-06',29,NULL,15,4,NULL,NULL,NULL,'2026-01-30 19:41:59','2026-02-05 17:33:55','2026-02-05 17:33:55'),(23,'Elena','Garcia','Luz','elena@gmail.com','$2y$12$3Hn..mSXh4cSWaNXgSEBPeFMDIKfLlSPf.8YupWDsy4eWT5Y5Ta1i',1,'XAXX010101001',NULL,'7333456789','2018-06-12',20,NULL,19,4,NULL,NULL,NULL,'2026-01-30 23:04:11','2026-02-18 19:27:00','2026-02-18 19:27:00'),(25,'Maria','Garcia','Kinfuju','Maria@gmail.com','$2y$12$kfj.3q.0YU6ddpKfIH/NkuyO6vMNUcQMcvEeTOLxNiqRZsq0GMMmW',1,'MALG00323212',NULL,'7443342191','2026-01-09',25,NULL,21,4,NULL,NULL,NULL,'2026-01-31 00:20:37','2026-01-31 00:26:33','2026-01-31 00:26:33'),(33,'Elena','Garcia','Luz','elen@gmail.com','$2y$12$0N0ba8Zv8IvK0PcpkcS/iO2buQZZ6qdFHH3w6rLR3fGbOL1dQ76Be',1,'XAXX01010100',NULL,'7333456789','2018-06-12',20,NULL,29,4,NULL,NULL,NULL,'2026-02-05 20:44:22','2026-02-05 20:44:29','2026-02-05 20:44:29'),(35,'Laura','Hernández','Gómez','laura.hernandez@example.com','$2y$12$2d7krXyU/.eBvcVDOiQmLuLtmaUaGReSDahUttMNZTDwBboQaNO/y',1,'HEGL850912ABC',NULL,'7441234567','1985-09-12',40,8,31,4,NULL,NULL,NULL,'2026-02-18 21:29:56','2026-03-11 18:08:53','2026-03-11 18:08:53'),(36,'Ricardo','Martínez','López','ricardo.martinez@example.com','$2y$12$dsbOCWCQ8ftG7jCYzb5GrO3eVsuiz5N4BvQq/n/5ifGVXTROaMmxu',1,'MALR780305XYZ',NULL,'7449876543','1978-03-05',48,NULL,32,4,NULL,NULL,NULL,'2026-02-18 21:40:48','2026-04-01 21:24:53','2026-04-01 21:24:53'),(37,'Sofía','Ramírez','Torres','sofia.ramirez@email.com','$2y$12$mmYF8NNjJOkGeSiGpaIQ3uWVyOsTKHuvcjKQHlTqW/3Kddn2QmURm',1,'RATC900715LMN',NULL,'7445551122','1990-07-15',35,NULL,33,4,NULL,NULL,NULL,'2026-02-18 21:43:47','2026-04-01 21:25:00','2026-04-01 21:25:00'),(38,'Miguel Ángel','Pérez','Sánchez','miguel.perez@example.com','$2y$12$DLL/yt3pKIFc6.Iov0jgwe6anjgiKgWcSr9i9buzHm/gj2BE24A.K',1,'PESM820420DEF',NULL,'7443342211','1982-04-20',43,NULL,34,4,NULL,NULL,NULL,'2026-02-18 21:45:45','2026-04-01 21:25:03','2026-04-01 21:25:03'),(39,'Daniela','Morales','Vega','daniela.morales@example.com','$2y$12$dHG48L2AoLz.4WQGF0jIzOUtThbP0AflsRUXXmvl2vbIgvzRn2l3i',1,'MOVE920215JK',NULL,'7447788990','1992-02-15',34,NULL,35,4,NULL,NULL,NULL,'2026-02-18 21:48:59','2026-04-01 21:25:07','2026-04-01 21:25:07'),(40,'Alejandro','Cruz','Hernández','alejandro.cruz@example.com','$2y$12$nk5Tu7X4FddwKOFMIbcpRebmUIaWaTEMorgXEzmE.MK1fetcmN4WS',1,'CRHA810930UVW',NULL,'7448899001','1981-12-30',44,NULL,36,4,NULL,NULL,NULL,'2026-02-18 21:50:43','2026-04-01 21:25:17','2026-04-01 21:25:17'),(42,'Jose Gulmaro','Marquez','Flores','jose@gmail.com','$2y$12$YXrHLVhXO.dEO8rbHmaD6e9ME4R/Cyj6FjzysDfw5lmzWgBGS8VLe',1,'QWERTYUIOPASD',NULL,'7533243243','2026-02-12',0,7,38,4,2,1,NULL,'2026-02-20 05:06:23','2026-02-20 05:42:41','2026-02-20 05:42:41'),(43,'Jose Gulmaro','Marquez','Flores','josegulmaromarquezflores@gmail.com','$2y$12$pgNYbxwX1FRrwHRotGN03u.q6/6wpa6MrQ9nsPV1N4xt/uIDPpMsy',1,'EWDWECECWCQWE',NULL,'3243243241','2025-03-13',0,7,39,4,1,2,NULL,'2026-02-20 05:07:45','2026-02-20 05:42:38','2026-02-20 05:42:38'),(44,'Mariana','López','García','mariana.lopez@example.com','$2y$12$qQTTCzPzaRSWEAnM/am0D.IQkgLVs068ZDhkgZ3DQ2Yp13.6z9yty',1,'LOGM950412ABC',NULL,'7441122334','1995-04-12',30,7,40,4,1,1,NULL,'2026-02-20 05:47:21','2026-03-05 01:48:42','2026-03-05 01:48:42'),(45,'Carlos','Hernández','Torres','carlos.hernandez@example.com','$2y$12$9Z9YVxdJndPadu.P/afqqu1RDCKsOzD2xkTiKtlAJWYZ9BhoHtLQy',1,'HETC870715XYZ',NULL,'7445566778','1987-07-15',38,7,41,4,2,2,NULL,'2026-02-20 05:51:35','2026-03-05 01:48:39','2026-03-05 01:48:39'),(46,'virginia','Castillo','Ramírez','virginia@gmail.com','$2y$12$DN7lP59.JJoxU6ckpsO5D.1XoDfx4XX6v8kA4ljOv1uQOt5o8ZSSW',1,'CARA990305LMN',NULL,'7449988776','2004-03-05',21,9,42,4,NULL,NULL,NULL,'2026-02-20 06:00:18','2026-03-13 14:22:23','2026-03-13 14:22:23'),(47,'Fernando','Gutiérrez','Morales','fernando.gutierrez@example.com','$2y$12$H/icZaEO1u7QSMWuRt9vYeP3jYXiDtwH5raBc409KUaZV/4GhwOWu',1,'GUMF880812OPQ',NULL,'7443344556','1988-09-12',37,9,43,4,NULL,NULL,NULL,'2026-02-20 06:02:45','2026-03-11 18:03:46','2026-03-11 18:03:46'),(48,'Patricia','Díaz','Sánchez','patricia.diaz@example.com','$2y$12$nU88573iKBPt2gDEzM347.08ZfR9bIOZaNb1fDZZpgI9FeS3HqkMK',1,'DISP970715RST',NULL,'7442233445','1997-07-15',28,7,44,4,2,1,NULL,'2026-02-20 06:06:28','2026-03-05 01:48:24','2026-03-05 01:48:24'),(49,'Jorge','Navarro','Cruz','jorge.navarro@example.com','$2y$12$jxc39I.Xp5WVGCLZOqY8xut5kJJs.OZLSOounolX4ZurKgAv.Kt/W',1,'NACJ850101UVW',NULL,'7446677889','1998-01-01',28,7,45,4,2,2,NULL,'2026-02-20 06:08:41','2026-03-05 01:48:21','2026-03-05 01:48:21'),(50,'Laura','Hernández','Gómez','Laura.castillo@example.com','$2y$12$FYjEniiqMlfYf5CCA3q3feTLuXI5BUd4Yq1teriRkVgfvQkUGCyse',1,'XAXX010101000407','PELJ900312HDFRPN01','7441234567','2004-03-04',22,7,46,4,NULL,NULL,NULL,'2026-03-05 02:13:25','2026-03-13 14:54:08','2026-03-13 14:54:08'),(51,'Jose Gulmaro','Marquez','Flores','marquezflores.josegulmaro@utacapulco.edu.mx','$2y$12$HEciJBaazNhdQtQE0qJqXOwdQGsq6V3EojmPW60YOCxTO6I1LgzLi',1,'XAXX010101000671','PELJ900312HDFRPN01','3243243241','2004-03-04',22,7,47,4,NULL,NULL,NULL,'2026-03-05 02:58:12','2026-03-05 03:05:33','2026-03-05 03:05:33'),(52,'Jose Gulmaro','Marquez','Flores','mwqdewd@gmail.com','$2y$12$oviccRaRXtTQ1JUYQ7ACgejSrMf.ozeQsb0HNWwVQB/OCxGYGbGaO',1,'XAXX010101000765','MALG720113HGRRNL05','3243243241','1998-09-02',27,7,48,4,NULL,NULL,NULL,'2026-03-10 18:55:33','2026-03-13 14:54:05','2026-03-13 14:54:05'),(53,'Roberto','Hernández','Gómez','mewd@gmail.com','$2y$12$xlo3oOuDCqTueTrbp7u62O1vjS/JWwX1GOZvESCjXj7lmrGEIXDni',1,'XAXX010101000227','PELJ900312HDFRAN01','7441234567','1998-09-02',27,7,49,4,NULL,NULL,NULL,'2026-03-10 18:57:40','2026-03-13 14:54:12','2026-03-13 14:54:12'),(54,'Roberto','Hernández','Gómez','med@gmail.com','$2y$12$CW2v3WrrCSoW83tgndYRLOOTeS6ZM8fv14d5NeIMNE/UErsqk99VW',1,'XAXX010101000494',NULL,'7441234567','1998-09-02',27,7,50,4,NULL,NULL,NULL,'2026-03-10 18:58:27','2026-03-26 18:18:05','2026-03-26 18:18:05'),(55,'MAteo','Hernández','Dias','Mateo@gmail.com','$2y$12$GqfncC9LINNWtGXw.wLU.OoSVVYl4VJLdaB2j4747hSUkELkjiPDC',1,'32ehudewewdfe',NULL,NULL,NULL,NULL,8,NULL,4,NULL,NULL,NULL,'2026-03-11 18:09:36','2026-03-13 14:22:26','2026-03-13 14:22:26'),(56,'Asael','Marquez','Flores','asael.marquez@utacapulco.edu.mx','$2y$12$noQV1xvaOAmAxFqyNjI4meVh8icnEpYSzoT20mGTtNanYwDq7s37K',1,'wdnjwencjewcj',NULL,NULL,NULL,NULL,9,NULL,4,NULL,NULL,NULL,'2026-03-11 18:15:26','2026-03-13 14:22:30','2026-03-13 14:22:30'),(57,'Jose Gulmaro','Marquez','Flores',NULL,'$2y$12$Wz.zh4YG6YOxRnMxqBdaY.MUYbeWzwS7WZ.HASZKSLTbxwIdnm8ui',1,'XAXX010101232',NULL,NULL,NULL,NULL,7,NULL,4,NULL,NULL,NULL,'2026-03-13 22:11:41','2026-03-13 22:11:52','2026-03-13 22:11:52'),(58,'Jose Gulmaro','Marquez','Flores',NULL,'$2y$12$xxE9zIqm6pqqzbgFmZz.cOUqPQjrHDBwdbim/31G4.nDiRHPexOe6',1,'XAXX010101wdw',NULL,NULL,NULL,NULL,7,NULL,1,NULL,NULL,NULL,'2026-03-13 22:28:25','2026-03-13 23:38:57','2026-03-13 23:38:57'),(59,'GULMARO','MARQUEZ','LEON',NULL,'$2y$12$xJTa4UzIy0I3bw083davgenJ.P8PpDn.l7ut7C6pmc.dPvvx15ErO',1,'XAXX010101342',NULL,NULL,NULL,NULL,7,NULL,4,NULL,NULL,NULL,'2026-03-13 22:29:02','2026-03-13 23:38:54','2026-03-13 23:38:54'),(60,'Virginia','Sotelo','Morales',NULL,'$2y$12$QFIjLtNkYke596E0Rpdv6ea1TbYiI6evKc0eqfu9epaLoJoYA3LgW',1,'GOMJ850623ABC',NULL,NULL,NULL,NULL,8,NULL,4,NULL,NULL,NULL,'2026-03-23 20:31:34','2026-03-23 20:50:42',NULL),(61,'Josseline','Sotelo','Morales',NULL,'$2y$12$fdGzwGyA.P1TNidIOUrkNuen2EQlBSVa3.ukDBHMCf2qxXKr7C2gO',1,'RAMC920415XYZ',NULL,NULL,NULL,NULL,9,NULL,4,NULL,NULL,NULL,'2026-03-23 20:42:43','2026-03-30 22:26:36',NULL),(62,'Jose Gulmaro','Marquez','Flores','marqueflores.josegulmaro@utacapulco.edu.mx','$2y$12$DW.D/akHaMTnRUYHBj1y6.1Qb60Xgm.k05mtwRP6maveTfX/b3goK',1,'XAXX010101000480','MAFG040305HGRRLLA1','3243243241','1998-02-22',28,7,51,4,NULL,NULL,NULL,'2026-03-23 22:28:58','2026-03-23 22:38:19','2026-03-23 22:38:19'),(63,'Josefina','Flores','Alonso',NULL,'$2y$12$ALI19XXl0WxiqcTXZap1eOScYv.iL9m5Y7JijuMgVRORlCnhg3/ia',1,'PEMJ900315XXX',NULL,NULL,NULL,NULL,7,NULL,4,NULL,NULL,NULL,'2026-03-24 15:34:09','2026-03-24 15:52:48','2026-03-24 15:52:48'),(64,'Josefina','Flores','Alonso',NULL,'$2y$12$HcFKQNbes12mhbCSCcB13O4Xqtrlf9LHeqHkBwH.o4ZO4SBnDF9Xe',1,'PEMJ900315XXz',NULL,NULL,NULL,NULL,7,NULL,4,NULL,NULL,NULL,'2026-03-24 15:52:33','2026-03-24 17:43:09','2026-03-24 17:43:09'),(65,'Laura','Gomez','Martinez',NULL,'$2y$12$Wog8SYguYgCCwLf.03HapONtuSKBG6e9ngChCHkMKvHTIjglm8reC',1,'GOML870812HDFMRL09','GOML870812HDFMRL09',NULL,NULL,NULL,7,NULL,4,NULL,NULL,NULL,'2026-03-24 21:54:18','2026-03-26 18:28:22','2026-03-26 18:28:22'),(66,'María Fernanda','López','Martínez',NULL,'$2y$12$1prOE88EThB91Pr9p.0LoOi/k6ErdOp/51g6SwyiW.jWyZaWhb4YO',1,'LOPM920415MDFRZN08','LOPM920415MDFRZN08',NULL,NULL,NULL,7,NULL,4,NULL,NULL,NULL,'2026-03-26 18:18:34','2026-03-26 18:29:44','2026-03-26 18:29:44'),(67,'Laura Beatriz','Hernández','Morales',NULL,'$2y$12$2L8Dmq0y4mcHtCqaDH.2a.37HVASipsY190OPchEpaCCpNHweeKNG',1,'HERM780921MDFCRL02','HERM780921MDFCRL02','7443342191','2004-02-05',22,7,52,4,NULL,NULL,NULL,'2026-03-26 18:37:51','2026-03-30 08:56:45','2026-03-30 08:56:45'),(68,'María Fernanda','García','Rodríguez',NULL,'$2y$12$T1pkC7KSMdh5tJGQFakidOKP5Gl95Y8s7ktPzdC5KmJmmgHlzCu/a',1,'GARC010512MDFRRL08','GARC010512MDFRRL08','7667766689','2004-03-05',22,7,53,4,NULL,NULL,NULL,'2026-03-26 21:53:13','2026-03-30 08:56:43','2026-03-30 08:56:43'),(69,'Javier Alejandro','Gómez','Ramírez',NULL,'$2y$12$6Z9OJiV0Nzk96PVvRVAHY.NWvXxPTAV/EO931NwLskaueq27H/PSG',1,'GOMJ010412MDFLRS07','GOMJ010412MDFLRS07','7667766689','1998-03-02',28,7,54,4,NULL,NULL,NULL,'2026-03-30 01:06:02','2026-03-30 08:56:40','2026-03-30 08:56:40'),(70,'María Fernanda','Gómez','Ramírez',NULL,'$2y$12$/kEUNi9vJI86ZBsLrjy5tucvqW5uCqs1sZe1DIkY85hc9VlGeWaIW',1,'GOMR120315MDFRRL08','GOMR120315MDFRRL08','7443342191','2003-03-05',23,7,55,4,NULL,NULL,NULL,'2026-03-30 04:10:56','2026-03-30 08:56:37','2026-03-30 08:56:37'),(71,'Carolina Sofía','López','Martínez',NULL,'$2y$12$LRe6GEzLh4YNslYG3LiamOFwCjZWUAxks09TZBDH//gDJSncbcJLe',1,'LOPC140927MDFNRR06','LOPC140927MDFNRR06','7443342191','2004-03-05',22,7,56,4,NULL,NULL,NULL,'2026-03-30 05:01:26','2026-03-30 08:56:35','2026-03-30 08:56:35'),(72,'Daniela Paola','Herrera','López','Daniela@gmail.com','$2y$12$k0qs4M8l3GtD/aKPFWgWuOsk4hEJWZhwUDToYJL0CQcG6YH9/4nti',1,'HERM101215MDFNLL09','HERM101215MDFNLL09','7667766689','2004-02-03',22,7,57,4,NULL,NULL,NULL,'2026-03-30 05:19:17','2026-03-30 08:56:32','2026-03-30 08:56:32'),(73,'Sofía Alejandra','Ramírez','Núñez',NULL,'$2y$12$.hiHgLy1IrRTrsAWx30ns.YKQ.UZdsP3PWU.JKbLN7SQfizXLwFca',1,'RAMS110823MDFNZN04','RAMS110823MDFNZN04',NULL,NULL,NULL,7,NULL,4,NULL,NULL,NULL,'2026-03-30 08:14:25','2026-03-30 08:56:29','2026-03-30 08:56:29'),(74,'Lucía','García','León','LuciaGarcia@gmail.com','$2y$12$FiG5axR2gxbGOe2cLUP4U.V.Z1XiQYzrr7oQbOm6CfeW6Pf5IWo5S',1,'GARC110304MDFLLL05','GARC110304MDFLLL05','7443342191','2004-02-22',22,7,58,4,NULL,NULL,NULL,'2026-03-30 23:07:20','2026-04-06 06:12:00','2026-04-06 06:12:00'),(75,'Andrea','Ramírez','Vargas','andrea@gmail.com','$2y$12$kXC5zdGGl8T2uMNN8i0awe28EUm/IkxUgd3dF05pkvL84j9k1zzB2',1,'RAMA130218MDFNVR02','RAMA130218MDFNVR02','7443342191','2004-01-20',22,7,59,4,NULL,NULL,NULL,'2026-03-30 23:18:43','2026-04-06 06:11:57','2026-04-06 06:11:57'),(76,'María Fernanda','González','Ramírez','mfernanda.gonzalez@example.com','$2y$12$bOGIIi8WKETz1tXVMvLyee0PiZCW3h1Bn6ar3jm14nYr41CASKt7W',0,'GORF860912ABC',NULL,'744-123-4567','2003-02-02',NULL,NULL,60,4,NULL,NULL,NULL,'2026-04-01 21:44:30','2026-04-01 21:53:58','2026-04-01 21:53:58'),(77,'Jorge Luis','Martínez','Martínez','jlmartinez.hernandez@example.com','$2y$12$wUZ1KAZJYzBptvKxpxDx8u8FGHXhuKxkJnKhpeJB/mlffiVlRjTIq',0,'MAHJ750421XYZ',NULL,'55-7890-2345','1998-04-21',27,NULL,61,4,NULL,NULL,NULL,'2026-04-01 23:45:07','2026-04-02 00:36:14','2026-04-02 00:36:14'),(79,'Laura Beatriz','Sánchez','Ortiz','laura.sanchez.ortiz@example.com','$2y$12$Ps5VZKZTVzHvSUOUHakWYe8nQ7ZQOV24h.WeITbcPCnwszqjd5ujW',0,'MAHJ750421XYS',NULL,'55-7890-2345','2004-03-04',NULL,NULL,63,4,NULL,NULL,NULL,'2026-04-02 00:37:55','2026-04-02 00:41:04','2026-04-02 00:41:04'),(80,'Ricardo Antonio','López','Delgado','ricardo.lopez.delgado@example.com','$2y$12$D4AZwxndZ.S7ksVdV8jYLeaxB2LyD7xbA7c9EXOLoBMZQhBIZrIXS',0,'LODR690305QWE',NULL,'81-2345-6789','1969-03-05',57,NULL,64,4,NULL,NULL,NULL,'2026-04-02 00:55:03','2026-04-02 01:02:35','2026-04-02 01:02:35'),(81,'Claudia Patricia','Reyes','Morales','claudia.reyes.morales@example.com','$2y$12$x/UTflEA9RNdrBTm1LNNNuMZmhFIcI0KPaLWIQLeN.zZ05Hxh765m',0,'REMJ790812DEF',NULL,'442-678-9012','1979-08-12',NULL,NULL,65,4,NULL,NULL,NULL,'2026-04-02 00:57:03','2026-04-02 01:02:40','2026-04-02 01:02:40'),(82,'Alejandro Iván','Castro','Mendoza','alejandro.castro.mendoza@example.com','$2y$12$6MnKX3McaHKrxGY0tGwvuuyfWvg8lFa2XnypVt6Z1DhwFpqV86tX6',0,'CAMA850210JKL',NULL,'55-9012-3456','1985-02-10',NULL,NULL,66,4,NULL,NULL,NULL,'2026-04-02 00:58:31','2026-04-02 01:02:53','2026-04-02 01:02:53'),(83,'Gabriela Sofía','Torres','Jiménez','gabriela.torres.jimenez@example.com','$2y$12$nUFS0JwofZuMHB0EdSOVFusru2ndgjTppnul7/q/ZSBN3U6bsxT1O',1,'TOJG900415PQR',NULL,'55-3456-7890','1990-04-15',36,8,67,4,NULL,NULL,NULL,'2026-04-02 04:32:25','2026-04-22 06:52:24',NULL),(84,'Héctor Manuel','Vargas','Salinas','hector.vargas.salinas@example.com','$2y$12$w.MtnWlWde4tzSD.Xsja1.YbTNXqfHVFMLyqDW7N3vwX4.xZCkY1G',1,'VASJ720610RST',NULL,'55-1122-3344','2004-06-10',21,8,68,4,NULL,NULL,NULL,'2026-04-02 04:37:43','2026-04-21 04:23:02',NULL),(85,'Silvia Marisol','Domínguez','Pérez','silvia.dominguez.perez@example.com','$2y$12$kpfYcC0I1.oxB.X5dBE1xuncVTiwpNiC2Z901kvty5pSur1Xhuk1.',1,'DOPS760918UVW',NULL,'55-2233-4455','2004-12-20',21,NULL,69,4,NULL,NULL,NULL,'2026-04-02 04:40:18','2026-04-02 04:40:25',NULL),(86,'Mariana','Morales','Estrada','MarianaMorales@gmail.com','$2y$12$2XN5RuMeBPlPZCeVZ0lIUObMpTdfy2/tGEbyZrDRojfxsS/QleFma',1,'MARE201015MGRLNS08','MARE201015MGRLNS08','7443342191','2004-01-03',22,7,70,4,NULL,NULL,NULL,'2026-04-06 06:25:18','2026-04-06 06:48:34',NULL),(87,'Sofía','García','Ramírez','SoficaGarcia@gmail.com','$2y$12$Hde.lTXN3vgA8OhC/Y88seJtAh.IYGbtQN3vzWWk6ZPFN50aLks6y',1,'GARA200908MDFRRL07','GARA200908MDFRRL07','7443342191','2004-03-05',22,7,71,4,NULL,NULL,NULL,'2026-04-06 07:01:17','2026-04-06 07:02:42',NULL),(88,'Daniela','López','Núñez','DanielaLopez@gmail.com','$2y$12$LolVpv3YcVRzQ1udx2JYy.uNkKRskeUznkJm5PuTYnPcQwH1aQvk.',1,'LONU201112MDFNZN06','LONU201112MDFNZN06','7443342191','2003-02-03',23,7,72,4,NULL,NULL,NULL,'2026-04-06 07:21:14','2026-04-06 07:48:30','2026-04-06 07:48:30'),(89,'Alejandra','Ramírez','Torres','alejandra.ramirezt@example.com','$2y$12$CYrU03Cjuh5GchUvG4rTl.SYnPHUSPGn0EeX5egSiogRv3wy2gOpm',1,'RATA870715LMN',NULL,'744-555-1122','1987-07-15',NULL,NULL,73,4,NULL,NULL,NULL,'2026-04-08 21:14:54','2026-04-08 21:14:54',NULL),(90,'Sofía','Delgado','Cruz','sofia.delgadoc@example.com','$2y$12$tgdzu9L2GBcxGEeCSis./.LXL.bFfXRbktOEZI7XRCrJEzrPWJ2Z2',1,'DECS930210QRS',NULL,'744-321-7788','1993-02-10',NULL,NULL,74,4,NULL,NULL,NULL,'2026-04-08 21:18:03','2026-04-08 21:18:03',NULL),(91,'Camila','López','Castillo','Camila@gmail.com','$2y$12$kYWSxWiN1X1bLqa1yWpHFumiGNllrtirkNRmBw7rIm27c89w4hsne',1,'LOPC101215MGRRZS08','LOPC101215MGRRZS08','7443342191','2009-02-03',17,7,77,4,NULL,NULL,NULL,'2026-04-08 23:26:53','2026-04-08 23:36:27',NULL),(92,'Daniela','Pérez','Salinas','Damiela@gmail.com','$2y$12$ko8fphM6nFCEbBT58IKgfun9XvB0R/Rap2SIpyiG2DWvIO.t/7RZC',1,'PERE140918MMSLRS09','PERE140918MMSLRS09','7443342191','2004-03-05',22,7,76,4,NULL,NULL,NULL,'2026-04-08 23:27:07','2026-04-08 23:34:52',NULL),(93,'Valeria','Ramírez','Aguilar','Valera@gmail.com','$2y$12$6wKK5GIJDKhRfxMDw1SDKOY8EWp249U/8l/SjQIWYhOLBllsmijuG',1,'RAMA120506MDFLNR07','RAMA120506MDFLNR07','7443342191','2003-03-05',23,7,75,4,NULL,NULL,NULL,'2026-04-08 23:27:23','2026-04-08 23:32:19',NULL),(94,'Lucía','Castillo','Navarro','LuciaCastillo@gmail.com','$2y$12$CSz/WDrALd57nOpREl.DsOaZo/TQahdKUh3qNw7f3uUvihZgLEIHG',1,'CAST150327MMSLNS02','CAST150327MMSLNS02','7443342191','2000-03-05',26,7,78,4,NULL,NULL,NULL,'2026-04-08 23:44:29','2026-04-09 18:25:07','2026-04-09 18:25:07'),(95,'Carolina','López','Hernández','Carolina@gmail.com','$2y$12$kH6vHCo2SSHfShv/3VnVkOHQwPttO/UxCnft1krmRxZv9S.WA5aH.',1,'ZOPC120305MDFNRN08','ZOPC120305MDFNRN08','7445821937','2004-03-05',22,7,79,4,NULL,NULL,NULL,'2026-04-12 22:16:21','2026-04-12 22:19:19',NULL),(96,'Valeria','Rosa','Medel','Valeria@gmail.com','$2y$12$tG5kcQIRWR3VR2Mf1V99KeikyxzrqGJd1fY0EQpjd5SMb/6FhEWX2',1,'RIVC200805HDFNLL07','RIVC200805HDFNLL07','7445821937','2004-02-03',22,7,80,4,NULL,NULL,NULL,'2026-04-12 23:01:25','2026-04-12 23:17:55',NULL),(97,'Juarez','Dias','Hernández','juarez@gmail.com','$2y$12$2BGLj0MTx2z9iWbxk6wlsOQXqhYTpMgTghZr9LwroKooOeI/WZT8y',1,'ZOPC120305MDF',NULL,'7442747829','2004-03-03',22,NULL,81,4,NULL,NULL,NULL,'2026-04-13 16:57:13','2026-04-13 16:57:35','2026-04-13 16:57:35'),(98,'GULMARO','Marquez','LEON','gulmaro@gmail.com','$2y$12$jfoLRLy0psrBp3jeFPUno.aPTTg58kaZGOdDpTCs.pU5ss7Xl5RoG',1,'MAFJ800101H23',NULL,'7443342191','2004-03-05',NULL,8,82,4,NULL,NULL,NULL,'2026-04-21 04:03:56','2026-04-21 21:21:48',NULL),(99,'Maximina','Márquez','Gómez','Maximina@gmail.com','$2y$12$TxW.3z6.FuvAWtw8y5bC7uKHBa0gKqogyf2Nd48WFEqt/iekXqsz6',1,'MAMG920715MGRSSS02','MAMG920715MGRSSS02','7443342191','2000-02-05',26,7,83,4,NULL,NULL,NULL,'2026-04-21 21:23:00','2026-04-21 22:08:55',NULL),(100,'Irving','Daniel','Marquez','Daniel@gmail.com','$2y$12$3BjpQhJ8mwoR8fw9wwOQuOyHc/kuPIsU4Va5fgUvnFkZVZpXpPKNy',1,'MAFJ850421ABC',NULL,'7443342191','2004-03-05',22,NULL,84,4,NULL,NULL,NULL,'2026-04-22 05:05:00','2026-04-22 05:05:00',NULL),(102,'nose','nose','nose','nose@gmail.com','$2y$12$a1UpVQpcuyu3akyqwZ16xOLAxXJ/6CYmfG0NNZ0cOapT9Axa5tU0W',1,'TOJG900415PQX',NULL,'55-3456-7890','1990-04-15',36,NULL,86,4,NULL,NULL,NULL,'2026-04-22 07:07:44','2026-04-22 07:10:44','2026-04-22 07:10:44'),(103,'Mateo','garcia','dias',NULL,'$2y$12$xr8jq9mw0uT2uzjBFh5tOeYKzMU5HIAoQivKsad8Xo6fTLEet6HvW',1,'GUFJ920422XYZ',NULL,NULL,NULL,NULL,8,NULL,4,NULL,NULL,NULL,'2026-04-22 19:11:23','2026-04-22 19:51:49','2026-04-22 19:51:49'),(104,'Laura','Martínez','Pérez','laura@gmail.com','$2y$12$3xiqIJfb2HblyDsV.DWFp.7PH93GYLpcg/gHJdf7QBcYw.QZugiLK',1,'GUHJ920422MDFPLS02','GUHJ920422MDFPLS02','7443342191','2004-02-04',22,7,87,4,NULL,NULL,NULL,'2026-04-22 19:52:05','2026-04-22 20:09:14',NULL);
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
  `name` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
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

-- Dump completed on 2026-04-22 17:20:47
