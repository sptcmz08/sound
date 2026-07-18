
/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;
/*!40103 SET @OLD_TIME_ZONE=@@TIME_ZONE */;
/*!40103 SET TIME_ZONE='+00:00' */;
/*!40014 SET @OLD_UNIQUE_CHECKS=@@UNIQUE_CHECKS, UNIQUE_CHECKS=0 */;
/*!40014 SET @OLD_FOREIGN_KEY_CHECKS=@@FOREIGN_KEY_CHECKS, FOREIGN_KEY_CHECKS=0 */;
/*!40101 SET @OLD_SQL_MODE=@@SQL_MODE, SQL_MODE='NO_AUTO_VALUE_ON_ZERO' */;
/*!40111 SET @OLD_SQL_NOTES=@@SQL_NOTES, SQL_NOTES=0 */;
DROP TABLE IF EXISTS `audit_logs`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `audit_logs` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `user_id` bigint(20) unsigned DEFAULT NULL,
  `action` varchar(255) NOT NULL,
  `entity_type` varchar(255) NOT NULL,
  `entity_id` bigint(20) unsigned DEFAULT NULL,
  `old_values` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`old_values`)),
  `new_values` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`new_values`)),
  `ip_address` varchar(45) DEFAULT NULL,
  `user_agent` text DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `audit_logs_user_id_foreign` (`user_id`),
  KEY `audit_logs_entity_type_entity_id_index` (`entity_type`,`entity_id`),
  CONSTRAINT `audit_logs_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB AUTO_INCREMENT=42 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

LOCK TABLES `audit_logs` WRITE;
/*!40000 ALTER TABLE `audit_logs` DISABLE KEYS */;
INSERT INTO `audit_logs` VALUES (1,1,'LOGIN','user',1,NULL,NULL,'127.0.0.1','Mozilla/5.0 (Windows NT; Windows NT 10.0; en-US) WindowsPowerShell/5.1.26100.8655','2026-07-17 20:51:34'),(2,1,'LOGIN','user',1,NULL,NULL,'127.0.0.1','Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/150.0.0.0 Safari/537.36','2026-07-17 21:03:45'),(3,1,'LOGIN','user',1,NULL,NULL,'127.0.0.1','Mozilla/5.0 (Windows NT; Windows NT 10.0; en-US) WindowsPowerShell/5.1.26100.8655','2026-07-17 21:15:52'),(4,1,'LOGIN','user',1,NULL,NULL,'127.0.0.1','Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/150.0.0.0 Safari/537.36','2026-07-17 21:16:57'),(5,1,'LOGIN','user',1,NULL,NULL,'127.0.0.1','Mozilla/5.0 (Windows NT; Windows NT 10.0; en-US) WindowsPowerShell/5.1.26100.8655','2026-07-17 21:26:34'),(6,1,'LOGIN','user',1,NULL,NULL,'127.0.0.1','Mozilla/5.0 (Windows NT; Windows NT 10.0; en-US) WindowsPowerShell/5.1.26100.8655','2026-07-17 21:32:40'),(7,1,'LOGIN','user',1,NULL,NULL,'127.0.0.1','Mozilla/5.0 (Windows NT; Windows NT 10.0; en-US) WindowsPowerShell/5.1.26100.8655','2026-07-17 22:00:10'),(8,1,'UPDATE','product',4,'{\"id\":4,\"code\":\"FG-002\",\"barcode\":null,\"name\":\"\\u0e2a\\u0e34\\u0e19\\u0e04\\u0e49\\u0e32\\u0e2a\\u0e33\\u0e40\\u0e23\\u0e47\\u0e08\\u0e23\\u0e39\\u0e1b B\",\"product_type\":\"FG\",\"unit_id\":3,\"minimum_stock\":\"0\",\"location_text\":null,\"image_path\":null,\"note\":null,\"is_active\":true,\"created_by\":1,\"updated_by\":1,\"created_at\":\"2026-07-18T03:36:31.000000Z\",\"updated_at\":\"2026-07-18T03:36:31.000000Z\",\"deleted_at\":null}','{\"id\":4,\"code\":\"FG-002\",\"barcode\":null,\"name\":\"\\u0e2a\\u0e34\\u0e19\\u0e04\\u0e49\\u0e32\\u0e2a\\u0e33\\u0e40\\u0e23\\u0e47\\u0e08\\u0e23\\u0e39\\u0e1b B\",\"product_type\":\"FG\",\"unit_id\":3,\"minimum_stock\":\"0\",\"location_text\":null,\"image_path\":null,\"note\":null,\"is_active\":true,\"created_by\":1,\"updated_by\":1,\"created_at\":\"2026-07-18T03:36:31.000000Z\",\"updated_at\":\"2026-07-18T03:36:31.000000Z\",\"deleted_at\":null}','127.0.0.1','Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/150.0.0.0 Safari/537.36','2026-07-17 22:00:37'),(9,1,'DELETE','product',4,NULL,NULL,'127.0.0.1','Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/150.0.0.0 Safari/537.36','2026-07-17 22:00:46'),(10,1,'POST','stock_document',1,NULL,'{\"document_no\":\"PIN-202607-000001\",\"type\":\"PART_IN\"}','127.0.0.1','Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/150.0.0.0 Safari/537.36','2026-07-17 22:02:23'),(11,1,'POST','stock_document',2,NULL,'{\"document_no\":\"POUT-202607-000001\",\"type\":\"PART_OUT\"}','127.0.0.1','Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/150.0.0.0 Safari/537.36','2026-07-17 22:02:42'),(12,1,'LOGIN','user',1,NULL,NULL,'127.0.0.1','Mozilla/5.0 (Windows NT; Windows NT 10.0; en-US) WindowsPowerShell/5.1.26100.8655','2026-07-17 22:07:20'),(13,1,'LOGIN','user',1,NULL,NULL,'127.0.0.1','Mozilla/5.0 (Windows NT; Windows NT 10.0; en-US) WindowsPowerShell/5.1.26100.8655','2026-07-17 22:22:34'),(14,1,'LOGIN','user',1,NULL,NULL,'127.0.0.1','Mozilla/5.0 (Windows NT; Windows NT 10.0; en-US) WindowsPowerShell/5.1.26100.8655','2026-07-18 03:48:48'),(15,1,'DEACTIVATE','product',1,NULL,NULL,'127.0.0.1','Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/150.0.0.0 Safari/537.36','2026-07-18 03:49:43'),(16,1,'DEACTIVATE','product',1,NULL,NULL,'127.0.0.1','Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/150.0.0.0 Safari/537.36','2026-07-18 03:49:47'),(17,1,'LOGIN','user',1,NULL,NULL,'127.0.0.1','Mozilla/5.0 (Windows NT; Windows NT 10.0; en-US) WindowsPowerShell/5.1.26100.8655','2026-07-18 03:56:30'),(18,1,'LOGIN','user',1,NULL,NULL,'127.0.0.1','Mozilla/5.0 (Windows NT; Windows NT 10.0; en-US) WindowsPowerShell/5.1.26100.8655','2026-07-18 04:15:28'),(19,1,'LOGIN','user',1,NULL,NULL,'127.0.0.1','Mozilla/5.0 (Windows NT; Windows NT 10.0; en-US) WindowsPowerShell/5.1.26100.8655','2026-07-18 04:28:10'),(20,1,'LOGIN','user',1,NULL,NULL,'127.0.0.1','Mozilla/5.0 (Windows NT; Windows NT 10.0; en-US) WindowsPowerShell/5.1.26100.8655','2026-07-18 04:45:31'),(21,1,'LOGIN','user',1,NULL,NULL,'127.0.0.1','Mozilla/5.0 (Windows NT; Windows NT 10.0; en-US) WindowsPowerShell/5.1.26100.8655','2026-07-18 04:48:44'),(22,1,'LOGIN','user',1,NULL,NULL,'127.0.0.1','Mozilla/5.0 (Windows NT; Windows NT 10.0; en-US) WindowsPowerShell/5.1.26100.8655','2026-07-18 07:00:18'),(23,1,'CREATE','product',5,NULL,'{\"code\":\"WIP-260718-00005\",\"name\":\"\\u0e25\\u0e33\\u0e42\\u0e1e\\u0e07\",\"product_type\":\"WIP\",\"unit_id\":1,\"minimum_stock\":\"0\",\"is_active\":true,\"created_by\":1,\"updated_by\":1,\"updated_at\":\"2026-07-18T14:01:21.000000Z\",\"created_at\":\"2026-07-18T14:01:21.000000Z\",\"id\":5,\"components\":[{\"id\":2,\"code\":\"PART-002\",\"barcode\":\"STK-00000002\",\"name\":\"\\u0e2a\\u0e32\\u0e22\\u0e44\\u0e1f B\",\"product_type\":\"PART\",\"unit_id\":1,\"minimum_stock\":\"0\",\"location_text\":null,\"image_path\":null,\"note\":null,\"is_active\":true,\"created_by\":1,\"updated_by\":1,\"created_at\":\"2026-07-18T03:36:30.000000Z\",\"updated_at\":\"2026-07-18T05:21:53.000000Z\",\"deleted_at\":null,\"pivot\":{\"parent_product_id\":5,\"component_product_id\":2,\"quantity\":1,\"created_at\":\"2026-07-18T14:01:21.000000Z\",\"updated_at\":\"2026-07-18T14:01:21.000000Z\"}}]}','127.0.0.1','Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/150.0.0.0 Safari/537.36','2026-07-18 07:01:21'),(24,1,'CREATE','requisition',1,NULL,'{\"request_no\":\"REQ-202607-000001\",\"type\":\"BUILD_WIP\"}','127.0.0.1','Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/150.0.0.0 Safari/537.36','2026-07-18 07:01:21'),(25,1,'LOGIN','user',1,NULL,NULL,'127.0.0.1','Mozilla/5.0 (Windows NT; Windows NT 10.0; en-US) WindowsPowerShell/5.1.26100.8655','2026-07-18 07:09:10'),(26,1,'UPDATE','product',2,'{\"id\":2,\"code\":\"PART-002\",\"barcode\":\"STK-00000002\",\"name\":\"\\u0e2a\\u0e32\\u0e22\\u0e44\\u0e1f B\",\"product_type\":\"PART\",\"unit_id\":1,\"minimum_stock\":\"0\",\"location_text\":null,\"image_path\":null,\"note\":null,\"is_active\":true,\"created_by\":1,\"updated_by\":1,\"created_at\":\"2026-07-18T03:36:30.000000Z\",\"updated_at\":\"2026-07-18T05:21:53.000000Z\",\"deleted_at\":null,\"components\":[]}','{\"id\":2,\"code\":\"PART-002\",\"barcode\":\"STK-00000002\",\"name\":\"\\u0e2a\\u0e32\\u0e22\\u0e44\\u0e1f B\",\"product_type\":\"PART\",\"unit_id\":1,\"minimum_stock\":\"100\",\"location_text\":null,\"image_path\":null,\"note\":null,\"is_active\":true,\"created_by\":1,\"updated_by\":1,\"created_at\":\"2026-07-18T03:36:30.000000Z\",\"updated_at\":\"2026-07-18T14:39:43.000000Z\",\"deleted_at\":null,\"components\":[]}','127.0.0.1','Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/150.0.0.0 Safari/537.36','2026-07-18 07:39:43'),(27,1,'LOGIN','user',1,NULL,NULL,'127.0.0.1','Mozilla/5.0 (Windows NT; Windows NT 10.0; en-US) WindowsPowerShell/5.1.26100.8655','2026-07-18 07:45:51'),(28,1,'POST','stock_document',3,NULL,'{\"document_no\":\"PIN-202607-000002\",\"type\":\"PART_IN\"}','127.0.0.1','Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/150.0.0.0 Safari/537.36','2026-07-18 07:47:35'),(29,1,'UPDATE','product',1,'{\"id\":1,\"code\":\"PART-001\",\"barcode\":\"STK-00000001\",\"name\":\"\\u0e19\\u0e47\\u0e2d\\u0e15 A\",\"product_type\":\"PART\",\"unit_id\":1,\"minimum_stock\":\"0\",\"location_text\":null,\"image_path\":null,\"note\":null,\"is_active\":false,\"created_by\":1,\"updated_by\":1,\"created_at\":\"2026-07-18T03:36:30.000000Z\",\"updated_at\":\"2026-07-18T10:49:43.000000Z\",\"deleted_at\":null,\"components\":[]}','{\"id\":1,\"code\":\"PART-001\",\"barcode\":\"STK-00000001\",\"name\":\"\\u0e19\\u0e47\\u0e2d\\u0e15 A\",\"product_type\":\"PART\",\"unit_id\":1,\"minimum_stock\":\"0\",\"location_text\":null,\"image_path\":null,\"note\":null,\"is_active\":true,\"created_by\":1,\"updated_by\":1,\"created_at\":\"2026-07-18T03:36:30.000000Z\",\"updated_at\":\"2026-07-18T14:48:10.000000Z\",\"deleted_at\":null,\"components\":[]}','127.0.0.1','Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/150.0.0.0 Safari/537.36','2026-07-18 07:48:10'),(30,1,'CREATE','requisition',2,NULL,'{\"request_no\":\"REQ-202607-000002\",\"type\":\"GENERAL_ISSUE\"}','127.0.0.1','Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/150.0.0.0 Safari/537.36','2026-07-18 07:48:24'),(31,1,'POST','stock_document',4,NULL,'{\"document_no\":\"POUT-202607-000002\",\"type\":\"PART_OUT\"}','127.0.0.1','Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/150.0.0.0 Safari/537.36','2026-07-18 07:48:24'),(32,1,'APPROVE','requisition',2,'{\"status\":\"PENDING\"}','{\"status\":\"APPROVED\",\"documents\":[\"POUT-202607-000002\"]}','127.0.0.1','Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/150.0.0.0 Safari/537.36','2026-07-18 07:48:24'),(33,1,'POST','stock_document',5,NULL,'{\"document_no\":\"POUT-202607-000003\",\"type\":\"PART_OUT\"}','127.0.0.1','Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/150.0.0.0 Safari/537.36','2026-07-18 07:52:14'),(34,1,'POST','stock_document',6,NULL,'{\"document_no\":\"WIN-202607-000001\",\"type\":\"WIP_IN\"}','127.0.0.1','Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/150.0.0.0 Safari/537.36','2026-07-18 07:52:14'),(35,1,'APPROVE','requisition',1,'{\"status\":\"PENDING\"}','{\"status\":\"APPROVED\",\"documents\":[\"POUT-202607-000003\",\"WIN-202607-000001\"]}','127.0.0.1','Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/150.0.0.0 Safari/537.36','2026-07-18 07:52:14'),(36,1,'LOGIN','user',1,NULL,NULL,'127.0.0.1','Mozilla/5.0 (Windows NT; Windows NT 10.0; en-US) WindowsPowerShell/5.1.26100.8655','2026-07-18 07:54:02'),(37,1,'LOGIN','user',1,NULL,NULL,'127.0.0.1','Mozilla/5.0 (Windows NT; Windows NT 10.0; en-US) WindowsPowerShell/5.1.26100.8655','2026-07-18 07:54:41'),(38,1,'LOGIN','user',1,NULL,NULL,'127.0.0.1','Mozilla/5.0 (Windows NT; Windows NT 10.0; en-US) WindowsPowerShell/5.1.26100.8655','2026-07-18 08:02:23'),(39,1,'LOGIN','user',1,NULL,NULL,'127.0.0.1','Mozilla/5.0 (Windows NT; Windows NT 10.0; en-US) WindowsPowerShell/5.1.26100.8655','2026-07-18 08:02:57'),(40,1,'LOGIN','user',1,NULL,NULL,'127.0.0.1','Mozilla/5.0 (Windows NT; Windows NT 10.0; en-US) WindowsPowerShell/5.1.26100.8655','2026-07-18 08:03:54'),(41,1,'LOGIN','user',1,NULL,NULL,'127.0.0.1','Mozilla/5.0 (Windows NT; Windows NT 10.0; en-US) WindowsPowerShell/5.1.26100.8655','2026-07-18 08:04:17');
/*!40000 ALTER TABLE `audit_logs` ENABLE KEYS */;
UNLOCK TABLES;
DROP TABLE IF EXISTS `cache`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `cache` (
  `key` varchar(255) NOT NULL,
  `value` mediumtext NOT NULL,
  `expiration` int(11) NOT NULL,
  PRIMARY KEY (`key`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `cache_locks`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `cache_locks` (
  `key` varchar(255) NOT NULL,
  `owner` varchar(255) NOT NULL,
  `expiration` int(11) NOT NULL,
  PRIMARY KEY (`key`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `document_sequences`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `document_sequences` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `prefix` varchar(255) NOT NULL,
  `period` varchar(255) NOT NULL,
  `current_number` bigint(20) unsigned NOT NULL DEFAULT 0,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `document_sequences_prefix_period_unique` (`prefix`,`period`)
) ENGINE=InnoDB AUTO_INCREMENT=7 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

LOCK TABLES `document_sequences` WRITE;
/*!40000 ALTER TABLE `document_sequences` DISABLE KEYS */;
INSERT INTO `document_sequences` VALUES (1,'PIN','202607',2,'2026-07-17 22:02:23','2026-07-18 07:47:35'),(2,'POUT','202607',3,'2026-07-17 22:02:42','2026-07-18 07:52:14'),(6,'WIN','202607',1,'2026-07-18 07:52:14','2026-07-18 07:52:14');
/*!40000 ALTER TABLE `document_sequences` ENABLE KEYS */;
UNLOCK TABLES;
DROP TABLE IF EXISTS `failed_jobs`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `failed_jobs` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `uuid` varchar(255) NOT NULL,
  `connection` text NOT NULL,
  `queue` text NOT NULL,
  `payload` longtext NOT NULL,
  `exception` longtext NOT NULL,
  `failed_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  UNIQUE KEY `failed_jobs_uuid_unique` (`uuid`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `job_batches`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `job_batches` (
  `id` varchar(255) NOT NULL,
  `name` varchar(255) NOT NULL,
  `total_jobs` int(11) NOT NULL,
  `pending_jobs` int(11) NOT NULL,
  `failed_jobs` int(11) NOT NULL,
  `failed_job_ids` longtext NOT NULL,
  `options` mediumtext DEFAULT NULL,
  `cancelled_at` int(11) DEFAULT NULL,
  `created_at` int(11) NOT NULL,
  `finished_at` int(11) DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `jobs`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `jobs` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `queue` varchar(255) NOT NULL,
  `payload` longtext NOT NULL,
  `attempts` tinyint(3) unsigned NOT NULL,
  `reserved_at` int(10) unsigned DEFAULT NULL,
  `available_at` int(10) unsigned NOT NULL,
  `created_at` int(10) unsigned NOT NULL,
  PRIMARY KEY (`id`),
  KEY `jobs_queue_index` (`queue`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `migrations`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `migrations` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `migration` varchar(255) NOT NULL,
  `batch` int(11) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=7 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

LOCK TABLES `migrations` WRITE;
/*!40000 ALTER TABLE `migrations` DISABLE KEYS */;
INSERT INTO `migrations` VALUES (1,'0001_01_01_000000_create_users_table',1),(2,'0001_01_01_000001_create_cache_table',1),(3,'0001_01_01_000002_create_jobs_table',1),(4,'2026_07_18_000001_create_stock_system_tables',1),(5,'2026_07_18_000002_create_requisition_workflow_tables',2),(6,'2026_07_18_000003_add_online_signatures',3);
/*!40000 ALTER TABLE `migrations` ENABLE KEYS */;
UNLOCK TABLES;
DROP TABLE IF EXISTS `password_reset_tokens`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `password_reset_tokens` (
  `email` varchar(255) NOT NULL,
  `token` varchar(255) NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`email`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `product_components`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `product_components` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `parent_product_id` bigint(20) unsigned NOT NULL,
  `component_product_id` bigint(20) unsigned NOT NULL,
  `quantity` decimal(18,4) NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `product_components_parent_product_id_component_product_id_unique` (`parent_product_id`,`component_product_id`),
  KEY `product_components_component_product_id_foreign` (`component_product_id`),
  CONSTRAINT `product_components_component_product_id_foreign` FOREIGN KEY (`component_product_id`) REFERENCES `products` (`id`),
  CONSTRAINT `product_components_parent_product_id_foreign` FOREIGN KEY (`parent_product_id`) REFERENCES `products` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

LOCK TABLES `product_components` WRITE;
/*!40000 ALTER TABLE `product_components` DISABLE KEYS */;
INSERT INTO `product_components` VALUES (1,5,2,1.0000,'2026-07-18 07:01:21','2026-07-18 07:01:21');
/*!40000 ALTER TABLE `product_components` ENABLE KEYS */;
UNLOCK TABLES;
DROP TABLE IF EXISTS `products`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `products` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `code` varchar(255) NOT NULL,
  `barcode` varchar(255) DEFAULT NULL,
  `name` varchar(255) NOT NULL,
  `product_type` varchar(255) NOT NULL,
  `unit_id` bigint(20) unsigned NOT NULL,
  `minimum_stock` decimal(18,4) NOT NULL DEFAULT 0.0000,
  `location_text` varchar(255) DEFAULT NULL,
  `image_path` varchar(255) DEFAULT NULL,
  `note` text DEFAULT NULL,
  `is_active` tinyint(1) NOT NULL DEFAULT 1,
  `created_by` bigint(20) unsigned NOT NULL,
  `updated_by` bigint(20) unsigned NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `products_code_unique` (`code`),
  UNIQUE KEY `products_barcode_unique` (`barcode`),
  KEY `products_unit_id_foreign` (`unit_id`),
  KEY `products_created_by_foreign` (`created_by`),
  KEY `products_updated_by_foreign` (`updated_by`),
  KEY `products_product_type_index` (`product_type`),
  KEY `products_is_active_index` (`is_active`),
  CONSTRAINT `products_created_by_foreign` FOREIGN KEY (`created_by`) REFERENCES `users` (`id`),
  CONSTRAINT `products_unit_id_foreign` FOREIGN KEY (`unit_id`) REFERENCES `units` (`id`),
  CONSTRAINT `products_updated_by_foreign` FOREIGN KEY (`updated_by`) REFERENCES `users` (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=6 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

LOCK TABLES `products` WRITE;
/*!40000 ALTER TABLE `products` DISABLE KEYS */;
INSERT INTO `products` VALUES (1,'PART-001','STK-00000001','น็อต A','PART',1,0.0000,NULL,NULL,NULL,1,1,1,'2026-07-17 20:36:30','2026-07-18 07:48:10',NULL),(2,'PART-002','STK-00000002','สายไฟ B','PART',1,100.0000,NULL,NULL,NULL,1,1,1,'2026-07-17 20:36:30','2026-07-18 07:39:43',NULL),(3,'FG-001','STK-00000003','สินค้าสำเร็จรูป A','FG',1,0.0000,NULL,NULL,NULL,1,1,1,'2026-07-17 20:36:30','2026-07-17 22:21:53',NULL),(4,'FG-002','STK-00000004','สินค้าสำเร็จรูป B','FG',3,0.0000,NULL,NULL,NULL,1,1,1,'2026-07-17 20:36:31','2026-07-17 22:21:53','2026-07-17 22:00:46'),(5,'WIP-260718-00005',NULL,'ลำโพง','WIP',1,0.0000,NULL,NULL,NULL,1,1,1,'2026-07-18 07:01:21','2026-07-18 07:01:21',NULL);
/*!40000 ALTER TABLE `products` ENABLE KEYS */;
UNLOCK TABLES;
DROP TABLE IF EXISTS `requisition_items`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `requisition_items` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `requisition_id` bigint(20) unsigned NOT NULL,
  `product_id` bigint(20) unsigned NOT NULL,
  `quantity` decimal(18,4) NOT NULL,
  `note` text DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `requisition_items_requisition_id_product_id_unique` (`requisition_id`,`product_id`),
  KEY `requisition_items_product_id_foreign` (`product_id`),
  CONSTRAINT `requisition_items_product_id_foreign` FOREIGN KEY (`product_id`) REFERENCES `products` (`id`),
  CONSTRAINT `requisition_items_requisition_id_foreign` FOREIGN KEY (`requisition_id`) REFERENCES `requisitions` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=3 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

LOCK TABLES `requisition_items` WRITE;
/*!40000 ALTER TABLE `requisition_items` DISABLE KEYS */;
INSERT INTO `requisition_items` VALUES (1,1,2,1.0000,'ส่วนประกอบตามสูตร','2026-07-18 07:01:21','2026-07-18 07:01:21'),(2,2,1,5.0000,NULL,'2026-07-18 07:48:24','2026-07-18 07:48:24');
/*!40000 ALTER TABLE `requisition_items` ENABLE KEYS */;
UNLOCK TABLES;
DROP TABLE IF EXISTS `requisition_stock_documents`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `requisition_stock_documents` (
  `requisition_id` bigint(20) unsigned NOT NULL,
  `stock_document_id` bigint(20) unsigned NOT NULL,
  PRIMARY KEY (`requisition_id`,`stock_document_id`),
  KEY `requisition_stock_documents_stock_document_id_foreign` (`stock_document_id`),
  CONSTRAINT `requisition_stock_documents_requisition_id_foreign` FOREIGN KEY (`requisition_id`) REFERENCES `requisitions` (`id`) ON DELETE CASCADE,
  CONSTRAINT `requisition_stock_documents_stock_document_id_foreign` FOREIGN KEY (`stock_document_id`) REFERENCES `stock_documents` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

LOCK TABLES `requisition_stock_documents` WRITE;
/*!40000 ALTER TABLE `requisition_stock_documents` DISABLE KEYS */;
INSERT INTO `requisition_stock_documents` VALUES (1,5),(1,6),(2,4);
/*!40000 ALTER TABLE `requisition_stock_documents` ENABLE KEYS */;
UNLOCK TABLES;
DROP TABLE IF EXISTS `requisitions`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `requisitions` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `request_no` varchar(255) NOT NULL,
  `request_type` varchar(255) NOT NULL,
  `status` varchar(255) NOT NULL DEFAULT 'PENDING',
  `warehouse_id` bigint(20) unsigned NOT NULL,
  `target_product_id` bigint(20) unsigned DEFAULT NULL,
  `target_quantity` decimal(18,4) DEFAULT NULL,
  `department_name` varchar(255) DEFAULT NULL,
  `purpose` varchar(255) NOT NULL,
  `note` text DEFAULT NULL,
  `requested_by` bigint(20) unsigned NOT NULL,
  `requested_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  `requester_signature_path` varchar(255) DEFAULT NULL,
  `requester_signed_at` timestamp NULL DEFAULT NULL,
  `approved_by` bigint(20) unsigned DEFAULT NULL,
  `approved_at` timestamp NULL DEFAULT NULL,
  `approval_signature` longtext DEFAULT NULL,
  `rejected_by` bigint(20) unsigned DEFAULT NULL,
  `rejected_at` timestamp NULL DEFAULT NULL,
  `rejection_reason` text DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `requisitions_request_no_unique` (`request_no`),
  KEY `requisitions_warehouse_id_foreign` (`warehouse_id`),
  KEY `requisitions_target_product_id_foreign` (`target_product_id`),
  KEY `requisitions_requested_by_foreign` (`requested_by`),
  KEY `requisitions_approved_by_foreign` (`approved_by`),
  KEY `requisitions_rejected_by_foreign` (`rejected_by`),
  KEY `requisitions_request_type_index` (`request_type`),
  KEY `requisitions_status_index` (`status`),
  CONSTRAINT `requisitions_approved_by_foreign` FOREIGN KEY (`approved_by`) REFERENCES `users` (`id`),
  CONSTRAINT `requisitions_rejected_by_foreign` FOREIGN KEY (`rejected_by`) REFERENCES `users` (`id`),
  CONSTRAINT `requisitions_requested_by_foreign` FOREIGN KEY (`requested_by`) REFERENCES `users` (`id`),
  CONSTRAINT `requisitions_target_product_id_foreign` FOREIGN KEY (`target_product_id`) REFERENCES `products` (`id`),
  CONSTRAINT `requisitions_warehouse_id_foreign` FOREIGN KEY (`warehouse_id`) REFERENCES `warehouses` (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=3 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

LOCK TABLES `requisitions` WRITE;
/*!40000 ALTER TABLE `requisitions` DISABLE KEYS */;
INSERT INTO `requisitions` VALUES (1,'REQ-202607-000001','BUILD_WIP','APPROVED',1,5,1.0000,NULL,'สร้างวิช ลำโพง',NULL,1,'2026-07-18 07:01:21',NULL,NULL,1,'2026-07-18 07:52:14',NULL,NULL,NULL,NULL,'2026-07-18 07:01:21','2026-07-18 07:52:14'),(2,'REQ-202607-000002','GENERAL_ISSUE','APPROVED',1,NULL,1.0000,NULL,'เบิกอะไหล่ทั่วไป',NULL,1,'2026-07-18 07:48:24',NULL,NULL,1,'2026-07-18 07:48:24',NULL,NULL,NULL,NULL,'2026-07-18 07:48:24','2026-07-18 07:48:24');
/*!40000 ALTER TABLE `requisitions` ENABLE KEYS */;
UNLOCK TABLES;
DROP TABLE IF EXISTS `sessions`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `sessions` (
  `id` varchar(255) NOT NULL,
  `user_id` bigint(20) unsigned DEFAULT NULL,
  `ip_address` varchar(45) DEFAULT NULL,
  `user_agent` text DEFAULT NULL,
  `payload` longtext NOT NULL,
  `last_activity` int(11) NOT NULL,
  PRIMARY KEY (`id`),
  KEY `sessions_user_id_index` (`user_id`),
  KEY `sessions_last_activity_index` (`last_activity`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `stock_balances`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `stock_balances` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `product_id` bigint(20) unsigned NOT NULL,
  `warehouse_id` bigint(20) unsigned NOT NULL,
  `quantity` decimal(18,4) NOT NULL DEFAULT 0.0000,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `stock_balances_product_id_warehouse_id_unique` (`product_id`,`warehouse_id`),
  KEY `stock_balances_warehouse_id_foreign` (`warehouse_id`),
  CONSTRAINT `stock_balances_product_id_foreign` FOREIGN KEY (`product_id`) REFERENCES `products` (`id`),
  CONSTRAINT `stock_balances_warehouse_id_foreign` FOREIGN KEY (`warehouse_id`) REFERENCES `warehouses` (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=7 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

LOCK TABLES `stock_balances` WRITE;
/*!40000 ALTER TABLE `stock_balances` DISABLE KEYS */;
INSERT INTO `stock_balances` VALUES (1,1,1,75.0000,'2026-07-17 22:02:23','2026-07-18 07:48:24'),(3,2,1,99.0000,'2026-07-18 07:47:35','2026-07-18 07:52:14'),(6,5,1,1.0000,'2026-07-18 07:52:14','2026-07-18 07:52:14');
/*!40000 ALTER TABLE `stock_balances` ENABLE KEYS */;
UNLOCK TABLES;
DROP TABLE IF EXISTS `stock_document_items`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `stock_document_items` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `stock_document_id` bigint(20) unsigned NOT NULL,
  `product_id` bigint(20) unsigned NOT NULL,
  `quantity` decimal(18,4) NOT NULL,
  `unit_id` bigint(20) unsigned NOT NULL,
  `note` text DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `stock_document_items_stock_document_id_product_id_unique` (`stock_document_id`,`product_id`),
  KEY `stock_document_items_product_id_foreign` (`product_id`),
  KEY `stock_document_items_unit_id_foreign` (`unit_id`),
  CONSTRAINT `stock_document_items_product_id_foreign` FOREIGN KEY (`product_id`) REFERENCES `products` (`id`),
  CONSTRAINT `stock_document_items_stock_document_id_foreign` FOREIGN KEY (`stock_document_id`) REFERENCES `stock_documents` (`id`) ON DELETE CASCADE,
  CONSTRAINT `stock_document_items_unit_id_foreign` FOREIGN KEY (`unit_id`) REFERENCES `units` (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=7 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

LOCK TABLES `stock_document_items` WRITE;
/*!40000 ALTER TABLE `stock_document_items` DISABLE KEYS */;
INSERT INTO `stock_document_items` VALUES (1,1,1,100.0000,1,NULL,'2026-07-17 22:02:23','2026-07-17 22:02:23'),(2,2,1,20.0000,1,NULL,'2026-07-17 22:02:42','2026-07-17 22:02:42'),(3,3,2,100.0000,1,NULL,'2026-07-18 07:47:35','2026-07-18 07:47:35'),(4,4,1,5.0000,1,NULL,'2026-07-18 07:48:24','2026-07-18 07:48:24'),(5,5,2,1.0000,1,NULL,'2026-07-18 07:52:14','2026-07-18 07:52:14'),(6,6,5,1.0000,1,NULL,'2026-07-18 07:52:14','2026-07-18 07:52:14');
/*!40000 ALTER TABLE `stock_document_items` ENABLE KEYS */;
UNLOCK TABLES;
DROP TABLE IF EXISTS `stock_documents`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `stock_documents` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `document_no` varchar(255) NOT NULL,
  `document_type` varchar(255) NOT NULL,
  `document_date` date NOT NULL,
  `warehouse_id` bigint(20) unsigned NOT NULL,
  `reference_no` varchar(255) DEFAULT NULL,
  `contact_name` varchar(255) DEFAULT NULL,
  `department_name` varchar(255) DEFAULT NULL,
  `purpose` varchar(255) DEFAULT NULL,
  `note` text DEFAULT NULL,
  `status` varchar(255) NOT NULL DEFAULT 'DRAFT',
  `idempotency_key` char(36) DEFAULT NULL,
  `created_by` bigint(20) unsigned NOT NULL,
  `posted_by` bigint(20) unsigned DEFAULT NULL,
  `posted_at` timestamp NULL DEFAULT NULL,
  `cancelled_by` bigint(20) unsigned DEFAULT NULL,
  `cancelled_at` timestamp NULL DEFAULT NULL,
  `reversal_of_id` bigint(20) unsigned DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `stock_documents_document_no_unique` (`document_no`),
  UNIQUE KEY `stock_documents_idempotency_key_unique` (`idempotency_key`),
  UNIQUE KEY `stock_documents_reversal_of_id_unique` (`reversal_of_id`),
  KEY `stock_documents_warehouse_id_foreign` (`warehouse_id`),
  KEY `stock_documents_created_by_foreign` (`created_by`),
  KEY `stock_documents_posted_by_foreign` (`posted_by`),
  KEY `stock_documents_cancelled_by_foreign` (`cancelled_by`),
  KEY `stock_documents_document_type_document_date_index` (`document_type`,`document_date`),
  KEY `stock_documents_status_index` (`status`),
  CONSTRAINT `stock_documents_cancelled_by_foreign` FOREIGN KEY (`cancelled_by`) REFERENCES `users` (`id`),
  CONSTRAINT `stock_documents_created_by_foreign` FOREIGN KEY (`created_by`) REFERENCES `users` (`id`),
  CONSTRAINT `stock_documents_posted_by_foreign` FOREIGN KEY (`posted_by`) REFERENCES `users` (`id`),
  CONSTRAINT `stock_documents_reversal_of_id_foreign` FOREIGN KEY (`reversal_of_id`) REFERENCES `stock_documents` (`id`),
  CONSTRAINT `stock_documents_warehouse_id_foreign` FOREIGN KEY (`warehouse_id`) REFERENCES `warehouses` (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=7 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

LOCK TABLES `stock_documents` WRITE;
/*!40000 ALTER TABLE `stock_documents` DISABLE KEYS */;
INSERT INTO `stock_documents` VALUES (1,'PIN-202607-000001','PART_IN','2026-07-18',1,NULL,'123','123','456','123456','POSTED','c487fef2-c65c-42e5-bd15-df00903e048c',1,1,'2026-07-17 22:02:23',NULL,NULL,NULL,'2026-07-17 22:02:23','2026-07-17 22:02:23'),(2,'POUT-202607-000001','PART_OUT','2026-07-18',1,NULL,'123','123','123','123','POSTED','18f38185-2329-4c66-92cb-e4bdc0a17dd2',1,1,'2026-07-17 22:02:42',NULL,NULL,NULL,'2026-07-17 22:02:42','2026-07-17 22:02:42'),(3,'PIN-202607-000002','PART_IN','2026-07-18',1,NULL,NULL,NULL,'คีย์รับสินค้าเข้าสต็อก',NULL,'POSTED','c208ba26-7bde-4a79-a1e0-96697b955324',1,1,'2026-07-18 07:47:35',NULL,NULL,NULL,'2026-07-18 07:47:35','2026-07-18 07:47:35'),(4,'POUT-202607-000002','PART_OUT','2026-07-18',1,'REQ-202607-000002',NULL,NULL,'เบิกอะไหล่ทั่วไป','อนุมัติตามคำขอ REQ-202607-000002','POSTED','f100a00a-ac5f-439f-8321-b5abda0c5e71',1,1,'2026-07-18 07:48:24',NULL,NULL,NULL,'2026-07-18 07:48:24','2026-07-18 07:48:24'),(5,'POUT-202607-000003','PART_OUT','2026-07-18',1,'REQ-202607-000001',NULL,NULL,'สร้างวิช ลำโพง','อนุมัติตามคำขอ REQ-202607-000001','POSTED','80e7af50-9f2e-4b7a-9dc5-819a518e6469',1,1,'2026-07-18 07:52:14',NULL,NULL,NULL,'2026-07-18 07:52:14','2026-07-18 07:52:14'),(6,'WIN-202607-000001','WIP_IN','2026-07-18',1,'REQ-202607-000001',NULL,NULL,'สร้างวิช ลำโพง','อนุมัติตามคำขอ REQ-202607-000001','POSTED','503ccd50-b5d5-4251-9ec4-858f7ad5cdb2',1,1,'2026-07-18 07:52:14',NULL,NULL,NULL,'2026-07-18 07:52:14','2026-07-18 07:52:14');
/*!40000 ALTER TABLE `stock_documents` ENABLE KEYS */;
UNLOCK TABLES;
DROP TABLE IF EXISTS `stock_transactions`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `stock_transactions` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `transaction_uuid` char(36) NOT NULL,
  `stock_document_id` bigint(20) unsigned NOT NULL,
  `stock_document_item_id` bigint(20) unsigned DEFAULT NULL,
  `product_id` bigint(20) unsigned NOT NULL,
  `warehouse_id` bigint(20) unsigned NOT NULL,
  `transaction_type` varchar(255) NOT NULL,
  `quantity_in` decimal(18,4) NOT NULL DEFAULT 0.0000,
  `quantity_out` decimal(18,4) NOT NULL DEFAULT 0.0000,
  `balance_after` decimal(18,4) NOT NULL,
  `occurred_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  `created_by` bigint(20) unsigned NOT NULL,
  `note` text DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  UNIQUE KEY `stock_transactions_transaction_uuid_unique` (`transaction_uuid`),
  KEY `stock_transactions_stock_document_item_id_foreign` (`stock_document_item_id`),
  KEY `stock_transactions_warehouse_id_foreign` (`warehouse_id`),
  KEY `stock_transactions_created_by_foreign` (`created_by`),
  KEY `stock_transactions_product_id_warehouse_id_occurred_at_index` (`product_id`,`warehouse_id`,`occurred_at`),
  KEY `stock_transactions_stock_document_id_index` (`stock_document_id`),
  KEY `stock_transactions_transaction_type_index` (`transaction_type`),
  CONSTRAINT `stock_transactions_created_by_foreign` FOREIGN KEY (`created_by`) REFERENCES `users` (`id`),
  CONSTRAINT `stock_transactions_product_id_foreign` FOREIGN KEY (`product_id`) REFERENCES `products` (`id`),
  CONSTRAINT `stock_transactions_stock_document_id_foreign` FOREIGN KEY (`stock_document_id`) REFERENCES `stock_documents` (`id`),
  CONSTRAINT `stock_transactions_stock_document_item_id_foreign` FOREIGN KEY (`stock_document_item_id`) REFERENCES `stock_document_items` (`id`),
  CONSTRAINT `stock_transactions_warehouse_id_foreign` FOREIGN KEY (`warehouse_id`) REFERENCES `warehouses` (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=7 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

LOCK TABLES `stock_transactions` WRITE;
/*!40000 ALTER TABLE `stock_transactions` DISABLE KEYS */;
INSERT INTO `stock_transactions` VALUES (1,'edef810b-2491-478c-a7e1-529c82d5a95f',1,1,1,1,'IN',100.0000,0.0000,100.0000,'2026-07-17 22:02:23',1,'123456','2026-07-17 22:02:23'),(2,'c5f082d6-ec19-4dc4-8a57-56ada13df03e',2,2,1,1,'OUT',0.0000,20.0000,80.0000,'2026-07-17 22:02:42',1,'123','2026-07-17 22:02:42'),(3,'cf5328a5-3349-4d67-a129-f5f11fe86124',3,3,2,1,'IN',100.0000,0.0000,100.0000,'2026-07-18 07:47:35',1,NULL,'2026-07-18 07:47:35'),(4,'e67cc983-330e-4583-8852-52e998084859',4,4,1,1,'OUT',0.0000,5.0000,75.0000,'2026-07-18 07:48:24',1,'อนุมัติตามคำขอ REQ-202607-000002','2026-07-18 07:48:24'),(5,'dae1550f-0e58-478f-9646-379c7ee42493',5,5,2,1,'OUT',0.0000,1.0000,99.0000,'2026-07-18 07:52:14',1,'อนุมัติตามคำขอ REQ-202607-000001','2026-07-18 07:52:14'),(6,'4e8950a1-84bc-4a95-a54d-7585b177bb6a',6,6,5,1,'IN',1.0000,0.0000,1.0000,'2026-07-18 07:52:14',1,'อนุมัติตามคำขอ REQ-202607-000001','2026-07-18 07:52:14');
/*!40000 ALTER TABLE `stock_transactions` ENABLE KEYS */;
UNLOCK TABLES;
DROP TABLE IF EXISTS `units`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `units` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `code` varchar(255) NOT NULL,
  `name` varchar(255) NOT NULL,
  `is_active` tinyint(1) NOT NULL DEFAULT 1,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `units_code_unique` (`code`)
) ENGINE=InnoDB AUTO_INCREMENT=4 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

LOCK TABLES `units` WRITE;
/*!40000 ALTER TABLE `units` DISABLE KEYS */;
INSERT INTO `units` VALUES (1,'PCS','ชิ้น',1,'2026-07-17 20:36:30','2026-07-17 20:36:30'),(2,'BOX','กล่อง',1,'2026-07-17 20:36:30','2026-07-17 20:36:30'),(3,'SET','ชุด',1,'2026-07-17 20:36:30','2026-07-17 20:36:30');
/*!40000 ALTER TABLE `units` ENABLE KEYS */;
UNLOCK TABLES;
DROP TABLE IF EXISTS `user_signatures`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `user_signatures` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `user_id` bigint(20) unsigned NOT NULL,
  `signature_path` varchar(255) NOT NULL,
  `pin_hash` varchar(255) NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `user_signatures_user_id_unique` (`user_id`),
  CONSTRAINT `user_signatures_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

LOCK TABLES `user_signatures` WRITE;
/*!40000 ALTER TABLE `user_signatures` DISABLE KEYS */;
/*!40000 ALTER TABLE `user_signatures` ENABLE KEYS */;
UNLOCK TABLES;
DROP TABLE IF EXISTS `users`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `users` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(255) NOT NULL,
  `email` varchar(255) NOT NULL,
  `email_verified_at` timestamp NULL DEFAULT NULL,
  `password` varchar(255) NOT NULL,
  `remember_token` varchar(100) DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `role` varchar(255) NOT NULL DEFAULT 'VIEWER',
  `is_active` tinyint(1) NOT NULL DEFAULT 1,
  `last_login_at` timestamp NULL DEFAULT NULL,
  `must_change_password` tinyint(1) NOT NULL DEFAULT 0,
  PRIMARY KEY (`id`),
  UNIQUE KEY `users_email_unique` (`email`),
  KEY `users_role_index` (`role`)
) ENGINE=InnoDB AUTO_INCREMENT=4 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

LOCK TABLES `users` WRITE;
/*!40000 ALTER TABLE `users` DISABLE KEYS */;
INSERT INTO `users` VALUES (1,'ผู้ดูแลระบบ','admin@example.com',NULL,'$2y$12$N/kr5FjzqmdUlbMAxA4KHOpmZJ3KoETbaG6x6MkJgUteFeNZaFN9y','VChvJCaXl6OeD4VMabHydhEIyeMvd7nVbVNRcqsoh2opgqIdbg7WZ6GjL67y','2026-07-17 20:36:29','2026-07-18 08:04:17','ADMIN',1,'2026-07-18 08:04:17',1),(2,'เจ้าหน้าที่สต๊อก','stock@example.com',NULL,'$2y$12$yHDFgFxqsIQRu706FgFZyupz9VNnpChB4HGvWVx1vtsjNUJYCjuBK',NULL,'2026-07-17 20:36:29','2026-07-17 20:36:29','STOCK_STAFF',1,NULL,1),(3,'ผู้ดูรายงาน','viewer@example.com',NULL,'$2y$12$FR0cIiPRJOlhXBFkVo6qEOw0WLdllv1P1vkwLxM0XISAWrdPndvwi',NULL,'2026-07-17 20:36:30','2026-07-17 20:36:30','VIEWER',1,NULL,1);
/*!40000 ALTER TABLE `users` ENABLE KEYS */;
UNLOCK TABLES;
DROP TABLE IF EXISTS `warehouses`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `warehouses` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `code` varchar(255) NOT NULL,
  `name` varchar(255) NOT NULL,
  `address` text DEFAULT NULL,
  `is_active` tinyint(1) NOT NULL DEFAULT 1,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `warehouses_code_unique` (`code`)
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

LOCK TABLES `warehouses` WRITE;
/*!40000 ALTER TABLE `warehouses` DISABLE KEYS */;
INSERT INTO `warehouses` VALUES (1,'MAIN','คลังหลัก',NULL,1,'2026-07-17 20:36:30','2026-07-17 20:36:30');
/*!40000 ALTER TABLE `warehouses` ENABLE KEYS */;
UNLOCK TABLES;
/*!40103 SET TIME_ZONE=@OLD_TIME_ZONE */;

/*!40101 SET SQL_MODE=@OLD_SQL_MODE */;
/*!40014 SET FOREIGN_KEY_CHECKS=@OLD_FOREIGN_KEY_CHECKS */;
/*!40014 SET UNIQUE_CHECKS=@OLD_UNIQUE_CHECKS */;
/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
/*!40111 SET SQL_NOTES=@OLD_SQL_NOTES */;
