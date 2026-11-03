<?php
return <<<'SQLDATA'
-- MySQL dump 10.13  Distrib 8.0.37, for Win64 (x86_64)
--
-- Host: localhost    Database: devfolio
-- ------------------------------------------------------
-- Server version	5.5.5-10.4.32-MariaDB

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
-- Table structure for table `admin_users`
--

DROP TABLE IF EXISTS `admin_users`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `admin_users` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `username` varchar(50) NOT NULL,
  `email` varchar(150) NOT NULL,
  `password_hash` varchar(255) NOT NULL,
  `last_login_at` datetime DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  UNIQUE KEY `username` (`username`),
  UNIQUE KEY `email` (`email`)
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `admin_users`
--

LOCK TABLES `admin_users` WRITE;
/*!40000 ALTER TABLE `admin_users` DISABLE KEYS */;
INSERT INTO `admin_users` VALUES (1,'admin','admin@example.com','$2y$10$WPtRlBe4NpbjXKbg7GIanufqGkRctgbleqHTuenzvFcSc6Yy37CyO','2026-11-02 15:35:03','2026-11-02 09:35:03');
/*!40000 ALTER TABLE `admin_users` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `contact_messages`
--

DROP TABLE IF EXISTS `contact_messages`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `contact_messages` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(100) NOT NULL,
  `email` varchar(150) NOT NULL,
  `subject` varchar(200) NOT NULL,
  `message` text NOT NULL,
  `ip_address` varchar(45) DEFAULT NULL,
  `user_agent` varchar(255) DEFAULT NULL,
  `is_read` tinyint(1) NOT NULL DEFAULT 0,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `idx_ip_created` (`ip_address`,`created_at`)
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `contact_messages`
--

LOCK TABLES `contact_messages` WRITE;
/*!40000 ALTER TABLE `contact_messages` DISABLE KEYS */;
INSERT INTO `contact_messages` VALUES (1,'Sample Visitor','visitor@example.com','Welcome','This is a sample message stored by the contact form.','127.0.0.1',NULL,0,'2026-11-02 08:50:14');
/*!40000 ALTER TABLE `contact_messages` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `faqs`
--

DROP TABLE IF EXISTS `faqs`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `faqs` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `question` varchar(255) NOT NULL,
  `answer` text NOT NULL,
  `sort_order` int(11) NOT NULL DEFAULT 0,
  `is_active` tinyint(1) NOT NULL DEFAULT 1,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=11 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `faqs`
--

LOCK TABLES `faqs` WRITE;
/*!40000 ALTER TABLE `faqs` DISABLE KEYS */;
INSERT INTO `faqs` VALUES (6,'When is the deadline for submitting individual income tax returns in Bangladesh?','The general deadline for submitting individual income tax returns is usually November 30 of each tax year, as set by the National Board of Revenue (NBR).',1,1),(7,'What is the schedule for monthly VAT return submission?','Businesses registered under VAT must submit their monthly return (Mushak 9.1) within the 15th day of the following month.',2,1),(8,'What documents are required for private limited company registration at RJSC?','Key documents include Name Clearance from RJSC, Memorandum and Articles of Association (MoA/AoA), directors\' NID copies, passport-size photographs, and encashment certificate of paid-up capital.',3,1),(9,'How can I obtain a trade license for my business in Dhaka?','A trade license is issued by the respective City Corporation or Municipality office upon submitting tenancy agreement, trade particulars, passport-size photos, and a valid e-TIN.',4,1),(10,'How do I book a compliance consultation session?','You can easily schedule a consultation by filling out the contact form on our website or by reaching out directly via phone or WhatsApp.',5,1);
/*!40000 ALTER TABLE `faqs` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `login_attempts`
--

DROP TABLE IF EXISTS `login_attempts`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `login_attempts` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `ip_address` varchar(45) NOT NULL,
  `username` varchar(100) DEFAULT NULL,
  `attempted_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `idx_ip_time` (`ip_address`,`attempted_at`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `login_attempts`
--

LOCK TABLES `login_attempts` WRITE;
/*!40000 ALTER TABLE `login_attempts` DISABLE KEYS */;
/*!40000 ALTER TABLE `login_attempts` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `nav_menu`
--

DROP TABLE IF EXISTS `nav_menu`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `nav_menu` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `label` varchar(50) NOT NULL,
  `url` varchar(255) NOT NULL DEFAULT '#',
  `sort_order` int(11) NOT NULL DEFAULT 0,
  `is_active` tinyint(1) NOT NULL DEFAULT 1,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=6 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `nav_menu`
--

LOCK TABLES `nav_menu` WRITE;
/*!40000 ALTER TABLE `nav_menu` DISABLE KEYS */;
INSERT INTO `nav_menu` VALUES (1,'Home','index.php',1,1),(2,'About','about.php',2,1),(3,'Services','services.php',3,1),(4,'Portfolio','portfolio.php',4,1),(5,'Contact','contact.php',5,1);
/*!40000 ALTER TABLE `nav_menu` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `portfolio_categories`
--

DROP TABLE IF EXISTS `portfolio_categories`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `portfolio_categories` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(100) NOT NULL,
  `slug` varchar(100) NOT NULL COMMENT 'lowercase letters/digits/hyphens - used as CSS class filter-<slug>',
  `sort_order` int(11) NOT NULL DEFAULT 0,
  `is_active` tinyint(1) NOT NULL DEFAULT 1,
  PRIMARY KEY (`id`),
  UNIQUE KEY `slug` (`slug`)
) ENGINE=InnoDB AUTO_INCREMENT=5 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `portfolio_categories`
--

LOCK TABLES `portfolio_categories` WRITE;
/*!40000 ALTER TABLE `portfolio_categories` DISABLE KEYS */;
INSERT INTO `portfolio_categories` VALUES (1,'App','app',1,1),(2,'Product','product',2,1),(3,'Branding','branding',3,1),(4,'Books','books',4,1);
/*!40000 ALTER TABLE `portfolio_categories` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `portfolio_images`
--

DROP TABLE IF EXISTS `portfolio_images`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `portfolio_images` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `portfolio_item_id` int(10) unsigned NOT NULL,
  `image_path` varchar(255) NOT NULL,
  `sort_order` int(11) NOT NULL DEFAULT 0,
  PRIMARY KEY (`id`),
  KEY `fk_img_item` (`portfolio_item_id`),
  CONSTRAINT `fk_img_item` FOREIGN KEY (`portfolio_item_id`) REFERENCES `portfolio_items` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=49 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `portfolio_images`
--

LOCK TABLES `portfolio_images` WRITE;
/*!40000 ALTER TABLE `portfolio_images` DISABLE KEYS */;
INSERT INTO `portfolio_images` VALUES (1,1,'img/app-1.jpg',1),(2,1,'img/product-1.jpg',2),(3,1,'img/branding-1.jpg',3),(4,1,'img/books-1.jpg',4),(5,2,'img/app-1.jpg',1),(6,2,'img/product-1.jpg',2),(7,2,'img/branding-1.jpg',3),(8,2,'img/books-1.jpg',4),(9,3,'img/app-1.jpg',1),(10,3,'img/product-1.jpg',2),(11,3,'img/branding-1.jpg',3),(12,3,'img/books-1.jpg',4),(13,4,'img/app-1.jpg',1),(14,4,'img/product-1.jpg',2),(15,4,'img/branding-1.jpg',3),(16,4,'img/books-1.jpg',4),(17,5,'img/app-1.jpg',1),(18,5,'img/product-1.jpg',2),(19,5,'img/branding-1.jpg',3),(20,5,'img/books-1.jpg',4),(21,6,'img/app-1.jpg',1),(22,6,'img/product-1.jpg',2),(23,6,'img/branding-1.jpg',3),(24,6,'img/books-1.jpg',4),(25,7,'img/app-1.jpg',1),(26,7,'img/product-1.jpg',2),(27,7,'img/branding-1.jpg',3),(28,7,'img/books-1.jpg',4),(29,8,'img/app-1.jpg',1),(30,8,'img/product-1.jpg',2),(31,8,'img/branding-1.jpg',3),(32,8,'img/books-1.jpg',4),(33,9,'img/app-1.jpg',1),(34,9,'img/product-1.jpg',2),(35,9,'img/branding-1.jpg',3),(36,9,'img/books-1.jpg',4),(37,10,'img/app-1.jpg',1),(38,10,'img/product-1.jpg',2),(39,10,'img/branding-1.jpg',3),(40,10,'img/books-1.jpg',4),(41,11,'img/app-1.jpg',1),(42,11,'img/product-1.jpg',2),(43,11,'img/branding-1.jpg',3),(44,11,'img/books-1.jpg',4),(45,12,'img/app-1.jpg',1),(46,12,'img/product-1.jpg',2),(47,12,'img/branding-1.jpg',3),(48,12,'img/books-1.jpg',4);
/*!40000 ALTER TABLE `portfolio_images` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `portfolio_items`
--

DROP TABLE IF EXISTS `portfolio_items`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `portfolio_items` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `category_id` int(10) unsigned NOT NULL,
  `title` varchar(150) NOT NULL,
  `short_description` varchar(255) NOT NULL,
  `image_path` varchar(255) NOT NULL,
  `details_title` varchar(255) DEFAULT NULL,
  `details_text` text DEFAULT NULL COMMENT 'paragraphs separated by a blank line',
  `client` varchar(150) DEFAULT NULL,
  `project_date` date DEFAULT NULL,
  `project_url` varchar(255) DEFAULT NULL,
  `testimonial_id` int(10) unsigned DEFAULT NULL,
  `sort_order` int(11) NOT NULL DEFAULT 0,
  `is_active` tinyint(1) NOT NULL DEFAULT 1,
  PRIMARY KEY (`id`),
  KEY `fk_pi_cat` (`category_id`),
  KEY `fk_pi_test` (`testimonial_id`),
  CONSTRAINT `fk_pi_cat` FOREIGN KEY (`category_id`) REFERENCES `portfolio_categories` (`id`),
  CONSTRAINT `fk_pi_test` FOREIGN KEY (`testimonial_id`) REFERENCES `testimonials` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB AUTO_INCREMENT=13 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `portfolio_items`
--

LOCK TABLES `portfolio_items` WRITE;
/*!40000 ALTER TABLE `portfolio_items` DISABLE KEYS */;
INSERT INTO `portfolio_items` VALUES (1,1,'App 1','Lorem ipsum, dolor sit amet consectetur','img/app-1.jpg','This is an example of portfolio details','Autem ipsum nam porro corporis rerum. Quis eos dolorem eos itaque inventore commodi labore quia quia. Exercitationem repudiandae officiis neque suscipit non officia eaque itaque enim. Voluptatem officia accusantium nesciunt est omnis tempora consectetur dignissimos. Sequi nulla at esse enim cum deserunt eius.\n\nAmet consequatur qui dolore veniam voluptatem voluptatem sit. Non aspernatur atque natus ut cum nam et. Praesentium error dolores rerum minus sequi quia veritatis eum. Eos et doloribus doloremque nesciunt molestiae laboriosam.\n\nImpedit ipsum quae et aliquid doloribus et voluptatem quasi. Perspiciatis occaecati earum et magnam animi. Quibusdam non qui ea vitae suscipit vitae sunt. Repudiandae incidunt cumque minus deserunt assumenda tempore. Delectus voluptas necessitatibus est.\n\nSunt voluptatum sapiente facilis quo odio aut ipsum repellat debitis. Molestiae et autem libero. Explicabo et quod necessitatibus similique quis dolor eum. Numquam eaque praesentium rem et qui nesciunt.','ASU Company','2020-03-01','https://www.example.com',2,1,1),(2,2,'Product 1','Lorem ipsum, dolor sit amet consectetur','img/product-1.jpg','This is an example of portfolio details','Autem ipsum nam porro corporis rerum. Quis eos dolorem eos itaque inventore commodi labore quia quia. Exercitationem repudiandae officiis neque suscipit non officia eaque itaque enim. Voluptatem officia accusantium nesciunt est omnis tempora consectetur dignissimos. Sequi nulla at esse enim cum deserunt eius.\n\nAmet consequatur qui dolore veniam voluptatem voluptatem sit. Non aspernatur atque natus ut cum nam et. Praesentium error dolores rerum minus sequi quia veritatis eum. Eos et doloribus doloremque nesciunt molestiae laboriosam.\n\nImpedit ipsum quae et aliquid doloribus et voluptatem quasi. Perspiciatis occaecati earum et magnam animi. Quibusdam non qui ea vitae suscipit vitae sunt. Repudiandae incidunt cumque minus deserunt assumenda tempore. Delectus voluptas necessitatibus est.\n\nSunt voluptatum sapiente facilis quo odio aut ipsum repellat debitis. Molestiae et autem libero. Explicabo et quod necessitatibus similique quis dolor eum. Numquam eaque praesentium rem et qui nesciunt.','ASU Company','2020-03-01','https://www.example.com',2,2,1),(3,3,'Branding 1','Lorem ipsum, dolor sit amet consectetur','img/branding-1.jpg','This is an example of portfolio details','Autem ipsum nam porro corporis rerum. Quis eos dolorem eos itaque inventore commodi labore quia quia. Exercitationem repudiandae officiis neque suscipit non officia eaque itaque enim. Voluptatem officia accusantium nesciunt est omnis tempora consectetur dignissimos. Sequi nulla at esse enim cum deserunt eius.\n\nAmet consequatur qui dolore veniam voluptatem voluptatem sit. Non aspernatur atque natus ut cum nam et. Praesentium error dolores rerum minus sequi quia veritatis eum. Eos et doloribus doloremque nesciunt molestiae laboriosam.\n\nImpedit ipsum quae et aliquid doloribus et voluptatem quasi. Perspiciatis occaecati earum et magnam animi. Quibusdam non qui ea vitae suscipit vitae sunt. Repudiandae incidunt cumque minus deserunt assumenda tempore. Delectus voluptas necessitatibus est.\n\nSunt voluptatum sapiente facilis quo odio aut ipsum repellat debitis. Molestiae et autem libero. Explicabo et quod necessitatibus similique quis dolor eum. Numquam eaque praesentium rem et qui nesciunt.','ASU Company','2020-03-01','https://www.example.com',2,3,1),(4,4,'Books 1','Lorem ipsum, dolor sit amet consectetur','img/books-1.jpg','This is an example of portfolio details','Autem ipsum nam porro corporis rerum. Quis eos dolorem eos itaque inventore commodi labore quia quia. Exercitationem repudiandae officiis neque suscipit non officia eaque itaque enim. Voluptatem officia accusantium nesciunt est omnis tempora consectetur dignissimos. Sequi nulla at esse enim cum deserunt eius.\n\nAmet consequatur qui dolore veniam voluptatem voluptatem sit. Non aspernatur atque natus ut cum nam et. Praesentium error dolores rerum minus sequi quia veritatis eum. Eos et doloribus doloremque nesciunt molestiae laboriosam.\n\nImpedit ipsum quae et aliquid doloribus et voluptatem quasi. Perspiciatis occaecati earum et magnam animi. Quibusdam non qui ea vitae suscipit vitae sunt. Repudiandae incidunt cumque minus deserunt assumenda tempore. Delectus voluptas necessitatibus est.\n\nSunt voluptatum sapiente facilis quo odio aut ipsum repellat debitis. Molestiae et autem libero. Explicabo et quod necessitatibus similique quis dolor eum. Numquam eaque praesentium rem et qui nesciunt.','ASU Company','2020-03-01','https://www.example.com',2,4,1),(5,1,'App 2','Lorem ipsum, dolor sit amet consectetur','img/app-2.jpg','This is an example of portfolio details','Autem ipsum nam porro corporis rerum. Quis eos dolorem eos itaque inventore commodi labore quia quia. Exercitationem repudiandae officiis neque suscipit non officia eaque itaque enim. Voluptatem officia accusantium nesciunt est omnis tempora consectetur dignissimos. Sequi nulla at esse enim cum deserunt eius.\n\nAmet consequatur qui dolore veniam voluptatem voluptatem sit. Non aspernatur atque natus ut cum nam et. Praesentium error dolores rerum minus sequi quia veritatis eum. Eos et doloribus doloremque nesciunt molestiae laboriosam.\n\nImpedit ipsum quae et aliquid doloribus et voluptatem quasi. Perspiciatis occaecati earum et magnam animi. Quibusdam non qui ea vitae suscipit vitae sunt. Repudiandae incidunt cumque minus deserunt assumenda tempore. Delectus voluptas necessitatibus est.\n\nSunt voluptatum sapiente facilis quo odio aut ipsum repellat debitis. Molestiae et autem libero. Explicabo et quod necessitatibus similique quis dolor eum. Numquam eaque praesentium rem et qui nesciunt.','ASU Company','2020-03-01','https://www.example.com',2,5,1),(6,2,'Product 2','Lorem ipsum, dolor sit amet consectetur','img/product-2.jpg','This is an example of portfolio details','Autem ipsum nam porro corporis rerum. Quis eos dolorem eos itaque inventore commodi labore quia quia. Exercitationem repudiandae officiis neque suscipit non officia eaque itaque enim. Voluptatem officia accusantium nesciunt est omnis tempora consectetur dignissimos. Sequi nulla at esse enim cum deserunt eius.\n\nAmet consequatur qui dolore veniam voluptatem voluptatem sit. Non aspernatur atque natus ut cum nam et. Praesentium error dolores rerum minus sequi quia veritatis eum. Eos et doloribus doloremque nesciunt molestiae laboriosam.\n\nImpedit ipsum quae et aliquid doloribus et voluptatem quasi. Perspiciatis occaecati earum et magnam animi. Quibusdam non qui ea vitae suscipit vitae sunt. Repudiandae incidunt cumque minus deserunt assumenda tempore. Delectus voluptas necessitatibus est.\n\nSunt voluptatum sapiente facilis quo odio aut ipsum repellat debitis. Molestiae et autem libero. Explicabo et quod necessitatibus similique quis dolor eum. Numquam eaque praesentium rem et qui nesciunt.','ASU Company','2020-03-01','https://www.example.com',2,6,1),(7,3,'Branding 2','Lorem ipsum, dolor sit amet consectetur','img/branding-2.jpg','This is an example of portfolio details','Autem ipsum nam porro corporis rerum. Quis eos dolorem eos itaque inventore commodi labore quia quia. Exercitationem repudiandae officiis neque suscipit non officia eaque itaque enim. Voluptatem officia accusantium nesciunt est omnis tempora consectetur dignissimos. Sequi nulla at esse enim cum deserunt eius.\n\nAmet consequatur qui dolore veniam voluptatem voluptatem sit. Non aspernatur atque natus ut cum nam et. Praesentium error dolores rerum minus sequi quia veritatis eum. Eos et doloribus doloremque nesciunt molestiae laboriosam.\n\nImpedit ipsum quae et aliquid doloribus et voluptatem quasi. Perspiciatis occaecati earum et magnam animi. Quibusdam non qui ea vitae suscipit vitae sunt. Repudiandae incidunt cumque minus deserunt assumenda tempore. Delectus voluptas necessitatibus est.\n\nSunt voluptatum sapiente facilis quo odio aut ipsum repellat debitis. Molestiae et autem libero. Explicabo et quod necessitatibus similique quis dolor eum. Numquam eaque praesentium rem et qui nesciunt.','ASU Company','2020-03-01','https://www.example.com',2,7,1),(8,4,'Books 2','Lorem ipsum, dolor sit amet consectetur','img/books-2.jpg','This is an example of portfolio details','Autem ipsum nam porro corporis rerum. Quis eos dolorem eos itaque inventore commodi labore quia quia. Exercitationem repudiandae officiis neque suscipit non officia eaque itaque enim. Voluptatem officia accusantium nesciunt est omnis tempora consectetur dignissimos. Sequi nulla at esse enim cum deserunt eius.\n\nAmet consequatur qui dolore veniam voluptatem voluptatem sit. Non aspernatur atque natus ut cum nam et. Praesentium error dolores rerum minus sequi quia veritatis eum. Eos et doloribus doloremque nesciunt molestiae laboriosam.\n\nImpedit ipsum quae et aliquid doloribus et voluptatem quasi. Perspiciatis occaecati earum et magnam animi. Quibusdam non qui ea vitae suscipit vitae sunt. Repudiandae incidunt cumque minus deserunt assumenda tempore. Delectus voluptas necessitatibus est.\n\nSunt voluptatum sapiente facilis quo odio aut ipsum repellat debitis. Molestiae et autem libero. Explicabo et quod necessitatibus similique quis dolor eum. Numquam eaque praesentium rem et qui nesciunt.','ASU Company','2020-03-01','https://www.example.com',2,8,1),(9,1,'App 3','Lorem ipsum, dolor sit amet consectetur','img/app-3.jpg','This is an example of portfolio details','Autem ipsum nam porro corporis rerum. Quis eos dolorem eos itaque inventore commodi labore quia quia. Exercitationem repudiandae officiis neque suscipit non officia eaque itaque enim. Voluptatem officia accusantium nesciunt est omnis tempora consectetur dignissimos. Sequi nulla at esse enim cum deserunt eius.\n\nAmet consequatur qui dolore veniam voluptatem voluptatem sit. Non aspernatur atque natus ut cum nam et. Praesentium error dolores rerum minus sequi quia veritatis eum. Eos et doloribus doloremque nesciunt molestiae laboriosam.\n\nImpedit ipsum quae et aliquid doloribus et voluptatem quasi. Perspiciatis occaecati earum et magnam animi. Quibusdam non qui ea vitae suscipit vitae sunt. Repudiandae incidunt cumque minus deserunt assumenda tempore. Delectus voluptas necessitatibus est.\n\nSunt voluptatum sapiente facilis quo odio aut ipsum repellat debitis. Molestiae et autem libero. Explicabo et quod necessitatibus similique quis dolor eum. Numquam eaque praesentium rem et qui nesciunt.','ASU Company','2020-03-01','https://www.example.com',2,9,1),(10,2,'Product 3','Lorem ipsum, dolor sit amet consectetur','img/product-3.jpg','This is an example of portfolio details','Autem ipsum nam porro corporis rerum. Quis eos dolorem eos itaque inventore commodi labore quia quia. Exercitationem repudiandae officiis neque suscipit non officia eaque itaque enim. Voluptatem officia accusantium nesciunt est omnis tempora consectetur dignissimos. Sequi nulla at esse enim cum deserunt eius.\n\nAmet consequatur qui dolore veniam voluptatem voluptatem sit. Non aspernatur atque natus ut cum nam et. Praesentium error dolores rerum minus sequi quia veritatis eum. Eos et doloribus doloremque nesciunt molestiae laboriosam.\n\nImpedit ipsum quae et aliquid doloribus et voluptatem quasi. Perspiciatis occaecati earum et magnam animi. Quibusdam non qui ea vitae suscipit vitae sunt. Repudiandae incidunt cumque minus deserunt assumenda tempore. Delectus voluptas necessitatibus est.\n\nSunt voluptatum sapiente facilis quo odio aut ipsum repellat debitis. Molestiae et autem libero. Explicabo et quod necessitatibus similique quis dolor eum. Numquam eaque praesentium rem et qui nesciunt.','ASU Company','2020-03-01','https://www.example.com',2,10,1),(11,3,'Branding 3','Lorem ipsum, dolor sit amet consectetur','img/branding-3.jpg','This is an example of portfolio details','Autem ipsum nam porro corporis rerum. Quis eos dolorem eos itaque inventore commodi labore quia quia. Exercitationem repudiandae officiis neque suscipit non officia eaque itaque enim. Voluptatem officia accusantium nesciunt est omnis tempora consectetur dignissimos. Sequi nulla at esse enim cum deserunt eius.\n\nAmet consequatur qui dolore veniam voluptatem voluptatem sit. Non aspernatur atque natus ut cum nam et. Praesentium error dolores rerum minus sequi quia veritatis eum. Eos et doloribus doloremque nesciunt molestiae laboriosam.\n\nImpedit ipsum quae et aliquid doloribus et voluptatem quasi. Perspiciatis occaecati earum et magnam animi. Quibusdam non qui ea vitae suscipit vitae sunt. Repudiandae incidunt cumque minus deserunt assumenda tempore. Delectus voluptas necessitatibus est.\n\nSunt voluptatum sapiente facilis quo odio aut ipsum repellat debitis. Molestiae et autem libero. Explicabo et quod necessitatibus similique quis dolor eum. Numquam eaque praesentium rem et qui nesciunt.','ASU Company','2020-03-01','https://www.example.com',2,11,1),(12,4,'Books 3','Lorem ipsum, dolor sit amet consectetur','img/books-3.jpg','This is an example of portfolio details','Autem ipsum nam porro corporis rerum. Quis eos dolorem eos itaque inventore commodi labore quia quia. Exercitationem repudiandae officiis neque suscipit non officia eaque itaque enim. Voluptatem officia accusantium nesciunt est omnis tempora consectetur dignissimos. Sequi nulla at esse enim cum deserunt eius.\n\nAmet consequatur qui dolore veniam voluptatem voluptatem sit. Non aspernatur atque natus ut cum nam et. Praesentium error dolores rerum minus sequi quia veritatis eum. Eos et doloribus doloremque nesciunt molestiae laboriosam.\n\nImpedit ipsum quae et aliquid doloribus et voluptatem quasi. Perspiciatis occaecati earum et magnam animi. Quibusdam non qui ea vitae suscipit vitae sunt. Repudiandae incidunt cumque minus deserunt assumenda tempore. Delectus voluptas necessitatibus est.\n\nSunt voluptatum sapiente facilis quo odio aut ipsum repellat debitis. Molestiae et autem libero. Explicabo et quod necessitatibus similique quis dolor eum. Numquam eaque praesentium rem et qui nesciunt.','ASU Company','2020-03-01','https://www.example.com',2,12,1);
/*!40000 ALTER TABLE `portfolio_items` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `pricing_plans`
--

DROP TABLE IF EXISTS `pricing_plans`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `pricing_plans` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `title` varchar(150) NOT NULL,
  `price` decimal(10,2) NOT NULL,
  `sort_order` int(11) NOT NULL DEFAULT 0,
  `is_active` tinyint(1) NOT NULL DEFAULT 1,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=13 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `pricing_plans`
--

LOCK TABLES `pricing_plans` WRITE;
/*!40000 ALTER TABLE `pricing_plans` DISABLE KEYS */;
INSERT INTO `pricing_plans` VALUES (7,'Individual Tax Return (e-TIN)',50.00,1,1),(8,'Monthly VAT Return (Mushak 9.1)',100.00,2,1),(9,'Trade License Registration',150.00,3,1),(10,'Private Ltd Company Formation',175.00,4,1),(11,'SME Compliance Retainer',210.00,5,1),(12,'Tax Audit & Legal Advisory',250.00,6,1);
/*!40000 ALTER TABLE `pricing_plans` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `resume_items`
--

DROP TABLE IF EXISTS `resume_items`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `resume_items` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `section` enum('summary','education','experience') NOT NULL,
  `title` varchar(255) NOT NULL,
  `period` varchar(100) DEFAULT NULL,
  `subtitle` text DEFAULT NULL,
  `description` text DEFAULT NULL,
  `bullets` text DEFAULT NULL,
  `sort_order` int(11) NOT NULL DEFAULT 0,
  `is_active` tinyint(1) NOT NULL DEFAULT 1,
  PRIMARY KEY (`id`),
  KEY `idx_section` (`section`,`sort_order`)
) ENGINE=InnoDB AUTO_INCREMENT=11 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `resume_items`
--

LOCK TABLES `resume_items` WRITE;
/*!40000 ALTER TABLE `resume_items` DISABLE KEYS */;
INSERT INTO `resume_items` VALUES (6,'summary','S M MAHFUZUR RAHMAN',NULL,'Senior Business Compliance & Tax Consultant','Experienced Business Compliance and Tax Consultant with a strong track record in NBR taxation, VAT management, and RJSC company formation in Bangladesh. Dedicated to helping businesses and individuals achieve absolute legal compliance and financial accuracy.','Expertise in NBR Tax & VAT Consultancy\nSpecialized in RJSC Company Formation & Trade License Support\nCommitted to Timely Delivery and Accurate Financial Solutions',1,1),(7,'education','MASTER OF BUSINESS ADMINISTRATION (MBA)','2015 - 2016','University of Dhaka, Dhaka','Specialized in Corporate Finance, Advanced Auditing, and Taxation Laws in Bangladesh.','Major in Accounting & Information Systems\nFocused on National Board of Revenue (NBR) compliance structures',2,1),(8,'education','BACHELOR OF BUSINESS ADMINISTRATION (BBA)','2012 - 2015','University of Dhaka, Dhaka','Gained strong foundational knowledge in financial accounting, business legal environment, and tax management.','Graduated with Honors in Accounting\nCoursework in Commercial Law and Business Statistics',3,1),(9,'experience','SENIOR TAX & COMPLIANCE SPECIALIST','2017 - Present','Business Compliance Experts, Dhaka','Leading tax and business compliance strategies for corporate clients, small businesses, and high-net-worth individuals across Bangladesh.','Managed corporate tax filing, e-TIN processing, and annual NBR income tax assessments.\nHandled monthly VAT return preparation (Mushak 9.1) and regulatory compliance audits.\nSupervised company incorporation at RJSC, trade license renewals, and legal documentation.\nProvided strategic financial consultation and bookkeeping oversight for clients.',4,1),(10,'experience','TAX & ACCOUNTS EXECUTIVE','2017 - 2020','Financial & Advisory Services Ltd., Dhaka','Assisted individual and corporate clients with income tax filing, bookkeeping, and business license processing.','Prepared and filed individual e-TIN income tax returns for diverse clients.\nAssisted in trade license registration and City Corporation compliance updates.\nMaintained financial records, ledger books, and monthly bank reconciliation statements.\nCoordinated with NBR officials for tax assessments and clearance certificates.',5,1);
/*!40000 ALTER TABLE `resume_items` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `services`
--

DROP TABLE IF EXISTS `services`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `services` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `icon` varchar(64) NOT NULL COMMENT 'Bootstrap Icons class, e.g. bi-activity',
  `title` varchar(150) NOT NULL,
  `description` text NOT NULL,
  `sort_order` int(11) NOT NULL DEFAULT 0,
  `is_active` tinyint(1) NOT NULL DEFAULT 1,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=13 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `services`
--

LOCK TABLES `services` WRITE;
/*!40000 ALTER TABLE `services` DISABLE KEYS */;
INSERT INTO `services` VALUES (7,'bi-receipt-cutoff','VAT Consultancy & Registration','Comprehensive support for new BIN registration, monthly VAT returns submission (Mushak 9.1), and overall regulatory compliance under Bangladeshi VAT laws.',1,1),(8,'bi-currency-exchange','Income Tax Services','Professional assistance with e-TIN registration, personal and corporate annual income tax return preparation, submission, and tax assessment management.',2,1),(9,'bi-building-check','Company Formation & Registration','End-to-end registration services with the Registrar of Joint Stock Companies and Firms (RJSC) for Private Limited Companies, Partnership firms, and Branch offices.',3,1),(10,'bi-card-heading','Trade License & TIN Support','Fast and reliable assistance for acquiring and renewing trade licenses from City Corporations/Municipalities, alongside corporate TIN support.',4,1),(11,'bi-shield-check','RJSC & Business Compliance','Annual return filings, share transfers, name changes, and regular secretarial maintenance to keep your corporate entity 100% compliant.',5,1),(12,'bi-journal-check','Accounting & Financial Solutions','Accurate bookkeeping, financial statement preparation, and customized balance sheet generation for bank loans, audits, and management review.',6,1);
/*!40000 ALTER TABLE `services` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `site_settings`
--

DROP TABLE IF EXISTS `site_settings`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `site_settings` (
  `setting_key` varchar(64) NOT NULL,
  `setting_value` text DEFAULT NULL,
  PRIMARY KEY (`setting_key`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `site_settings`
--

LOCK TABLES `site_settings` WRITE;
/*!40000 ALTER TABLE `site_settings` DISABLE KEYS */;
INSERT INTO `site_settings` VALUES ('about_email','contact@example.com'),('about_heading','About me'),('about_image','img/mahfuzur_rahman.jpg'),('about_name','S M MAHFUZUR RAHMAN'),('about_phone','+880 1745 954670'),('about_profile','Senior Business Compliance & Tax Consultant'),('about_text','I am a dedicated Business Compliance Expert in Bangladesh with extensive experience in navigating the complexities of NBR tax laws, VAT structures, and corporate legalities. My expertise lies in helping individuals, startups, and established companies maintain 100% legal compliance while optimizing their financial workflows.\n\nFrom company registration at RJSC and trade license processing to annual income tax filing and monthly VAT returns, I provide end-to-end consultancy. My goal is to streamline your business operations so you can focus on growth while staying fully compliant with Bangladeshi regulations.\n\nWhether you need strategic tax planning, corporate audit defense, or financial documentation, I deliver accurate advice, reliable execution, and timely support tailored to your business needs.'),('contact_address','A108 Adam Street, New York, NY 535022'),('contact_email','info@example.com'),('contact_phone','+880 1745 954670'),('contact_subtitle','Necessitatibus eius consequatur ex aliquid fuga eum quidem sint consectetur velit'),('contact_title','Contact'),('faq_intro','Find answers to frequently asked questions about our tax, VAT, company registration, and business compliance services in Bangladesh.'),('hero_cta_text',''),('hero_cta_url','#contact'),('hero_image','img/hero-img.jpg'),('hero_subtitle',''),('hero_title','I am S M MAHFUZUR RAHMAN'),('hero_typed_items','Senior Business Compliance & Tax Consultant, NBR Tax & VAT Expert, RJSC Company Formation Specialist'),('meta_description',''),('meta_keywords',''),('page_banner_image','img/hero-img.jpg'),('portfolio_subtitle','Necessitatibus eius consequatur ex aliquid fuga eum quidem sint consectetur velit'),('portfolio_title','Portfolio'),('pricing_subtitle','Affordable and transparent pricing packages for your tax, VAT, and business compliance needs in Bangladesh'),('pricing_title','Pricing'),('resume_education_heading','Education'),('resume_experience_heading','Professional Experience'),('resume_subtitle','Professional background, qualifications, and extensive experience in corporate compliance'),('resume_summary_heading','Sumary'),('resume_title','Resume'),('services_subtitle','Professional solutions, accurate advice, and timely support for all your business compliance needs in Bangladesh'),('services_title','Services'),('site_logo','img/business_compliance_logo.png'),('site_name','Business Compliance'),('stats_image','img/stats-bg.jpg'),('testimonials_bg','img/testimonials-bg.jpg');
/*!40000 ALTER TABLE `site_settings` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `skills`
--

DROP TABLE IF EXISTS `skills`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `skills` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(100) NOT NULL,
  `percentage` tinyint(3) unsigned NOT NULL DEFAULT 0,
  `sort_order` int(11) NOT NULL DEFAULT 0,
  `is_active` tinyint(1) NOT NULL DEFAULT 1,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=5 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `skills`
--

LOCK TABLES `skills` WRITE;
/*!40000 ALTER TABLE `skills` DISABLE KEYS */;
INSERT INTO `skills` VALUES (1,'HTML',100,1,1),(2,'CSS',90,2,1),(3,'JavaScript',75,3,1),(4,'Photoshop',55,4,1);
/*!40000 ALTER TABLE `skills` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `social_links`
--

DROP TABLE IF EXISTS `social_links`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `social_links` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `icon` varchar(64) NOT NULL COMMENT 'Bootstrap Icons class, e.g. bi-twitter-x',
  `url` varchar(255) NOT NULL DEFAULT '#',
  `sort_order` int(11) NOT NULL DEFAULT 0,
  `is_active` tinyint(1) NOT NULL DEFAULT 1,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=5 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `social_links`
--

LOCK TABLES `social_links` WRITE;
/*!40000 ALTER TABLE `social_links` DISABLE KEYS */;
INSERT INTO `social_links` VALUES (1,'bi-twitter-x','#',1,1),(2,'bi-facebook','#',2,1),(3,'bi-instagram','#',3,1),(4,'bi-linkedin','#',4,1);
/*!40000 ALTER TABLE `social_links` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `stats`
--

DROP TABLE IF EXISTS `stats`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `stats` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `label` varchar(100) NOT NULL,
  `value` int(10) unsigned NOT NULL DEFAULT 0,
  `sort_order` int(11) NOT NULL DEFAULT 0,
  `is_active` tinyint(1) NOT NULL DEFAULT 1,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=5 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `stats`
--

LOCK TABLES `stats` WRITE;
/*!40000 ALTER TABLE `stats` DISABLE KEYS */;
INSERT INTO `stats` VALUES (1,'Clients',232,1,1),(2,'Projects',521,2,1),(3,'Hours Of Support',1453,3,1),(4,'Awards',32,4,1);
/*!40000 ALTER TABLE `stats` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `testimonials`
--

DROP TABLE IF EXISTS `testimonials`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `testimonials` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(100) NOT NULL,
  `position` varchar(100) NOT NULL,
  `image_path` varchar(255) NOT NULL,
  `rating` tinyint(3) unsigned NOT NULL DEFAULT 5,
  `quote` text NOT NULL,
  `sort_order` int(11) NOT NULL DEFAULT 0,
  `is_active` tinyint(1) NOT NULL DEFAULT 1,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=6 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `testimonials`
--

LOCK TABLES `testimonials` WRITE;
/*!40000 ALTER TABLE `testimonials` DISABLE KEYS */;
INSERT INTO `testimonials` VALUES (1,'Saul Goodman','Ceo & Founder','img/testimonials-1.jpg',5,'Proin iaculis purus consequat sem cure digni ssim donec porttitora entum suscipit rhoncus. Accusantium quam, ultricies eget id, aliquam eget nibh et. Maecen aliquam, risus at semper.',1,1),(2,'Sara Wilsson','Designer','img/testimonials-2.jpg',5,'Export tempor illum tamen malis malis eram quae irure esse labore quem cillum quid cillum eram malis quorum velit fore eram velit sunt aliqua noster fugiat irure amet legam anim culpa.',2,1),(3,'Jena Karlis','Store Owner','img/testimonials-3.jpg',5,'Enim nisi quem export duis labore cillum quae magna enim sint quorum nulla quem veniam duis minim tempor labore quem eram duis noster aute amet eram fore quis sint minim.',3,1),(4,'Matt Brandon','Freelancer','img/testimonials-4.jpg',5,'Fugiat enim eram quae cillum dolore dolor amet nulla culpa multos export minim fugiat minim velit minim dolor enim duis veniam ipsum anim magna sunt elit fore quem dolore labore illum veniam.',4,1),(5,'John Larson','Entrepreneur','img/testimonials-5.jpg',5,'Quis quorum aliqua sint quem legam fore sunt eram irure aliqua veniam tempor noster veniam enim culpa labore duis sunt culpa nulla illum cillum fugiat legam esse veniam culpa fore nisi cillum quid.',5,1);
/*!40000 ALTER TABLE `testimonials` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `uploaded_files`
--

DROP TABLE IF EXISTS `uploaded_files`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `uploaded_files` (
  `path` varchar(255) NOT NULL,
  `mime_type` varchar(64) NOT NULL,
  `data` longblob NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`path`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `uploaded_files`
--

LOCK TABLES `uploaded_files` WRITE;
/*!40000 ALTER TABLE `uploaded_files` DISABLE KEYS */;
/*!40000 ALTER TABLE `uploaded_files` ENABLE KEYS */;
UNLOCK TABLES;
/*!40103 SET TIME_ZONE=@OLD_TIME_ZONE */;

/*!40101 SET SQL_MODE=@OLD_SQL_MODE */;
/*!40014 SET FOREIGN_KEY_CHECKS=@OLD_FOREIGN_KEY_CHECKS */;
/*!40014 SET UNIQUE_CHECKS=@OLD_UNIQUE_CHECKS */;
/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
/*!40111 SET SQL_NOTES=@OLD_SQL_NOTES */;

-- Dump completed on 2026-11-03 17:18:26

SQLDATA;

