-- MySQL dump 10.13  Distrib 8.0.46, for Win64 (x86_64)
--
-- Host: 116.193.129.229    Database: smartinventory_core
-- ------------------------------------------------------
-- Server version	8.0.46

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
) ENGINE=InnoDB AUTO_INCREMENT=4 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `migrations`
--

LOCK TABLES `migrations` WRITE;
/*!40000 ALTER TABLE `migrations` DISABLE KEYS */;
/*!40000 ALTER TABLE `migrations` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `mst_accountingyear`
--

DROP TABLE IF EXISTS `mst_accountingyear`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `mst_accountingyear` (
  `Year_Id` tinyint NOT NULL AUTO_INCREMENT,
  `Year_Desc` varchar(10) DEFAULT NULL,
  `Year_Start` date DEFAULT NULL,
  `Year_End` date DEFAULT NULL,
  `Is_Active` bit(1) DEFAULT NULL,
  PRIMARY KEY (`Year_Id`)
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8mb3 AVG_ROW_LENGTH=16384;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `mst_accountingyear`
--

LOCK TABLES `mst_accountingyear` WRITE;
/*!40000 ALTER TABLE `mst_accountingyear` DISABLE KEYS */;
INSERT INTO `mst_accountingyear` VALUES (1,'2026-2027','2026-04-01','2027-03-31',_binary '');
/*!40000 ALTER TABLE `mst_accountingyear` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `mst_acct_category`
--

DROP TABLE IF EXISTS `mst_acct_category`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `mst_acct_category` (
  `Cate_Id` tinyint NOT NULL,
  `Cate_Desc` varchar(25) DEFAULT NULL,
  `Cate_Code` varchar(5) DEFAULT NULL,
  PRIMARY KEY (`Cate_Id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3 AVG_ROW_LENGTH=2730;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `mst_acct_category`
--

LOCK TABLES `mst_acct_category` WRITE;
/*!40000 ALTER TABLE `mst_acct_category` DISABLE KEYS */;
INSERT INTO `mst_acct_category` VALUES (1,'ASSETS','10'),(2,'LIABILITIES','20'),(3,'INCOME','30'),(4,'EXPENDITURE','40'),(5,'TRADING INCOME','50'),(6,'TRADING EXPENDITURE','60');
/*!40000 ALTER TABLE `mst_acct_category` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `mst_acct_glhead`
--

DROP TABLE IF EXISTS `mst_acct_glhead`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `mst_acct_glhead` (
  `Account_Id` smallint NOT NULL AUTO_INCREMENT,
  `Account_Code` varchar(6) DEFAULT NULL,
  `Account_Desc` varchar(200) DEFAULT NULL,
  `Account_For` char(1) DEFAULT NULL,
  `MainHd_Id` smallint DEFAULT NULL,
  `Is_Active` bit(1) DEFAULT NULL,
  PRIMARY KEY (`Account_Id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `mst_acct_glhead`
--

LOCK TABLES `mst_acct_glhead` WRITE;
/*!40000 ALTER TABLE `mst_acct_glhead` DISABLE KEYS */;
/*!40000 ALTER TABLE `mst_acct_glhead` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `mst_acct_mainhd`
--

DROP TABLE IF EXISTS `mst_acct_mainhd`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `mst_acct_mainhd` (
  `MainHd_Id` smallint NOT NULL AUTO_INCREMENT,
  `MainHd_Code` varchar(5) DEFAULT NULL,
  `MainHd_Desc` varchar(150) DEFAULT NULL,
  `Cate_Id` tinyint DEFAULT NULL,
  PRIMARY KEY (`MainHd_Id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `mst_acct_mainhd`
--

LOCK TABLES `mst_acct_mainhd` WRITE;
/*!40000 ALTER TABLE `mst_acct_mainhd` DISABLE KEYS */;
/*!40000 ALTER TABLE `mst_acct_mainhd` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `mst_default_gl`
--

DROP TABLE IF EXISTS `mst_default_gl`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `mst_default_gl` (
  `Id` smallint NOT NULL,
  `Particulars` varchar(75) DEFAULT NULL,
  `GL_Id` smallint DEFAULT NULL,
  `Is_Active` bit(1) DEFAULT NULL,
  PRIMARY KEY (`Id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3 AVG_ROW_LENGTH=1820;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `mst_default_gl`
--

LOCK TABLES `mst_default_gl` WRITE;
/*!40000 ALTER TABLE `mst_default_gl` DISABLE KEYS */;
INSERT INTO `mst_default_gl` VALUES (1,'Admission Fees',NULL,_binary ''),(2,'Share',NULL,_binary ''),(3,'Purchase Head',NULL,_binary ''),(4,'Sale Head',NULL,_binary ''),(5,'Damage Head',NULL,_binary ''),(6,'Sundry Debtors',NULL,_binary ''),(7,'Sundry Creditors',NULL,_binary ''),(8,'Opening Stock',NULL,_binary ''),(9,'Closing Stock',NULL,_binary '');
/*!40000 ALTER TABLE `mst_default_gl` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `mst_gl_opening`
--

DROP TABLE IF EXISTS `mst_gl_opening`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `mst_gl_opening` (
  `Open_Id` int NOT NULL AUTO_INCREMENT,
  `Open_Date` date DEFAULT NULL,
  `Year_Id` tinyint DEFAULT NULL,
  `Org_Id` smallint DEFAULT NULL,
  `Branch_Id` smallint DEFAULT NULL,
  `GlHead_Id` smallint DEFAULT NULL,
  `Open_Balance` decimal(18,2) DEFAULT NULL,
  PRIMARY KEY (`Open_Id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `mst_gl_opening`
--

LOCK TABLES `mst_gl_opening` WRITE;
/*!40000 ALTER TABLE `mst_gl_opening` DISABLE KEYS */;
/*!40000 ALTER TABLE `mst_gl_opening` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `mst_gst_codes`
--

DROP TABLE IF EXISTS `mst_gst_codes`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `mst_gst_codes` (
  `Id` smallint NOT NULL AUTO_INCREMENT,
  `Gst_Code` varchar(20) DEFAULT NULL,
  `Category` varchar(250) DEFAULT NULL,
  `Tax_Percent` decimal(5,2) DEFAULT NULL,
  `Sgst` decimal(5,2) DEFAULT NULL,
  `Cgst` decimal(5,2) DEFAULT NULL,
  `Igst` decimal(5,2) DEFAULT NULL,
  `Ugst` decimal(5,2) DEFAULT NULL,
  `Is_Active` bit(1) DEFAULT NULL,
  PRIMARY KEY (`Id`)
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8mb3;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `mst_gst_codes`
--

LOCK TABLES `mst_gst_codes` WRITE;
/*!40000 ALTER TABLE `mst_gst_codes` DISABLE KEYS */;
INSERT INTO `mst_gst_codes` VALUES (1,'WB5678','RAWMATERIAL',10.00,4.50,5.50,8.40,1.40,_binary '');
/*!40000 ALTER TABLE `mst_gst_codes` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `mst_options`
--

DROP TABLE IF EXISTS `mst_options`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `mst_options` (
  `Option_Sl` smallint NOT NULL AUTO_INCREMENT,
  `Option_Id` smallint DEFAULT NULL,
  `Option_Name` varchar(100) DEFAULT NULL,
  `Value_Id` smallint DEFAULT NULL,
  `Value_Name` varchar(100) DEFAULT NULL,
  `Is_Active` bit(1) DEFAULT NULL,
  PRIMARY KEY (`Option_Sl`)
) ENGINE=InnoDB AUTO_INCREMENT=59 DEFAULT CHARSET=utf8mb3 AVG_ROW_LENGTH=496;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `mst_options`
--

LOCK TABLES `mst_options` WRITE;
/*!40000 ALTER TABLE `mst_options` DISABLE KEYS */;
INSERT INTO `mst_options` VALUES (1,1,'Master Status Code',1,'Active',_binary ''),(2,1,'Master Status Code',2,'Inactive',_binary ''),(3,1,'Master Status Code',3,'Withdrawn',_binary ''),(4,2,'Transaction Status Code',1,'In Process',_binary ''),(5,2,'Transaction Status Code',2,'Success',_binary ''),(6,2,'Transaction Status Code',3,'Cancelled',_binary ''),(7,2,'Transaction Status Code',4,'Deleted',_binary ''),(8,4,'Party Type',1,'Sundry Creditors',_binary ''),(9,4,'Party Type',2,'Sundry Debtors',_binary ''),(10,5,'Item Type',1,'Raw Material',_binary ''),(11,5,'Item Type',2,'Finished Goods',_binary ''),(12,6,'Trading Type',1,'Production Entries',_binary ''),(13,6,'Trading Type',2,'Purchase Entry',_binary ''),(14,6,'Trading Type',3,'Sales Entry',_binary ''),(15,6,'Trading Type',4,'Purchase Return Entry',_binary ''),(16,6,'Trading Type',5,'Sales Return Entry',_binary ''),(17,6,'Trading Type',6,'Damages Entry',_binary ''),(18,7,'Stock Type',1,'Opening',_binary ''),(19,7,'Stock Type',2,'Purchase',_binary ''),(20,7,'Stock Type',3,'Counter Sales',_binary ''),(21,7,'Stock Type',4,'Agent Issued',_binary ''),(22,7,'Stock Type',5,'Agent Sales',_binary ''),(23,7,'Stock Type',6,'Agent Office Return',_binary ''),(24,7,'Stock Type',7,'Purchase Return',_binary ''),(25,7,'Stock Type',8,'Sales Return',_binary ''),(26,7,'Stock Type',9,'Damage/Wastage',_binary ''),(27,8,'Stock Position',1,'Godown',_binary ''),(28,8,'Stock Position',2,'Store',_binary ''),(29,8,'Stock Position',3,'Agent',_binary ''),(30,8,'Stock Position',4,'Return',_binary ''),(31,8,'Stock Position',5,'Discarded',_binary ''),(32,9,'Member Type',1,'Individual',_binary ''),(33,9,'Member Type',2,'Group',_binary ''),(34,10,'GST Type',1,'Regular',_binary ''),(35,10,'GST Type',2,'Composite',_binary ''),(36,11,'Voucher Type',1,'Receipt',_binary ''),(37,11,'Voucher Type',2,'Payment',_binary ''),(38,11,'Voucher Type',3,'Contra',_binary ''),(39,11,'Voucher Type',4,'Journal',_binary ''),(40,12,'Voucher Mode',1,'Cash',_binary ''),(41,12,'Voucher Mode',2,'Bank',_binary ''),(42,12,'Voucher Mode',3,'Credit',_binary ''),(43,13,'Transaction Type',1,'Purhcase',_binary ''),(44,13,'Transaction Type',2,'Counter Sales',_binary ''),(45,13,'Transaction Type',3,'Agent Sales',_binary ''),(46,13,'Transaction Type',4,'Agent Return',_binary ''),(47,13,'Transaction Type',5,'Purchase Return',_binary ''),(48,13,'Transaction Type',6,'Sales Return',_binary ''),(49,13,'Transaction Type',7,'Damage/Wastage',_binary ''),(50,13,'Transaction Type',8,'Yearly Adjustment',_binary ''),(51,13,'Transaction Type',9,'Cash Book Voucher',_binary ''),(52,14,'Agent Indent Type',1,'Agent Issue',_binary ''),(53,14,'Agent Indent Type',3,'Agent Counter Return',_binary ''),(54,15,'Transaction Mode',1,'Cash',_binary ''),(55,15,'Transaction Mode',2,'Bank',_binary ''),(56,15,'Transaction Mode',3,'Credit',_binary ''),(57,14,'Agent Indent Type',2,'Customer Return',_binary ''),(58,7,'Stock Type',10,'Agent Customer Return',_binary '');
/*!40000 ALTER TABLE `mst_options` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `mst_org_branch`
--

DROP TABLE IF EXISTS `mst_org_branch`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `mst_org_branch` (
  `Branch_Id` smallint NOT NULL AUTO_INCREMENT,
  `Org_Id` smallint NOT NULL,
  `Branch_Code` varchar(6) DEFAULT NULL,
  `Branch_Name` varchar(100) DEFAULT NULL,
  `Branch_Address` varchar(200) DEFAULT NULL,
  `Branch_Mobile` varchar(50) DEFAULT NULL,
  `Branch_Mail` varchar(50) DEFAULT NULL,
  `Is_Head` bit(1) DEFAULT b'0',
  `Is_Active` bit(1) DEFAULT b'1',
  PRIMARY KEY (`Branch_Id`)
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8mb3 AVG_ROW_LENGTH=5461;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `mst_org_branch`
--

LOCK TABLES `mst_org_branch` WRITE;
/*!40000 ALTER TABLE `mst_org_branch` DISABLE KEYS */;
INSERT INTO `mst_org_branch` VALUES (1,1,'01','Head Office',NULL,NULL,NULL,_binary '',_binary '');
/*!40000 ALTER TABLE `mst_org_branch` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `mst_org_config`
--

DROP TABLE IF EXISTS `mst_org_config`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `mst_org_config` (
  `Id` tinyint NOT NULL AUTO_INCREMENT,
  `Org_Id` smallint DEFAULT NULL,
  `Is_GST` bit(1) DEFAULT b'0',
  `Gst_Type` smallint DEFAULT NULL,
  `Created_On` datetime DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`Id`)
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8mb3;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `mst_org_config`
--

LOCK TABLES `mst_org_config` WRITE;
/*!40000 ALTER TABLE `mst_org_config` DISABLE KEYS */;
INSERT INTO `mst_org_config` VALUES (1,1,_binary '',2,'2026-04-24 18:16:55');
/*!40000 ALTER TABLE `mst_org_config` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `mst_organisation`
--

DROP TABLE IF EXISTS `mst_organisation`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `mst_organisation` (
  `Org_Id` smallint NOT NULL AUTO_INCREMENT,
  `Org_Code` varchar(4) DEFAULT NULL,
  `Org_Name` varchar(255) DEFAULT NULL,
  `Org_Contact` varchar(25) DEFAULT NULL,
  `Address` varchar(150) DEFAULT NULL,
  `City` varchar(25) DEFAULT NULL,
  `District` varchar(25) DEFAULT NULL,
  `State` varchar(25) DEFAULT NULL,
  `PinCode` varchar(6) DEFAULT NULL,
  `Email` varchar(50) DEFAULT NULL,
  `Pan_No` varchar(25) DEFAULT NULL,
  `Tan_No` varchar(25) DEFAULT NULL,
  `GstIn` varchar(25) DEFAULT NULL,
  `State_Cd` varchar(5) DEFAULT NULL,
  `Cin_No` varchar(25) DEFAULT NULL,
  `PTax_No` varchar(25) DEFAULT NULL,
  `Regd_Date` date DEFAULT NULL,
  `Org_Logo` blob,
  `Org_Schema` varchar(100) DEFAULT NULL,
  `Is_Active` bit(1) DEFAULT NULL,
  PRIMARY KEY (`Org_Id`),
  UNIQUE KEY `UK_mst_organisation_Org_Code` (`Org_Code`)
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8mb3 AVG_ROW_LENGTH=16384;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `mst_organisation`
--

LOCK TABLES `mst_organisation` WRITE;
/*!40000 ALTER TABLE `mst_organisation` DISABLE KEYS */;
INSERT INTO `mst_organisation` VALUES (1,'1901','Bagnan-I Mahila Bikash Consumer Cooperative Society Ltd.',NULL,'Bangalpur','Bagnan','Howrah','West Bengal','711303',NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,'smartinventory_bccsl',_binary '');
/*!40000 ALTER TABLE `mst_organisation` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `mst_prod_category`
--

DROP TABLE IF EXISTS `mst_prod_category`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `mst_prod_category` (
  `Prd_CateId` smallint NOT NULL AUTO_INCREMENT,
  `Prd_CateNm` varchar(75) DEFAULT NULL,
  `Is_Fmcg` bit(1) DEFAULT b'0',
  PRIMARY KEY (`Prd_CateId`)
) ENGINE=InnoDB AUTO_INCREMENT=10 DEFAULT CHARSET=utf8mb3 AVG_ROW_LENGTH=5461;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `mst_prod_category`
--

LOCK TABLES `mst_prod_category` WRITE;
/*!40000 ALTER TABLE `mst_prod_category` DISABLE KEYS */;
INSERT INTO `mst_prod_category` VALUES (1,'Raw Meterial',_binary '\0'),(2,'Consumer Goods (FMCG)',_binary ''),(3,'Garments',_binary '\0');
/*!40000 ALTER TABLE `mst_prod_category` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `mst_prod_subcategory`
--

DROP TABLE IF EXISTS `mst_prod_subcategory`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `mst_prod_subcategory` (
  `Prd_SubCateId` smallint NOT NULL AUTO_INCREMENT,
  `Prd_SubCateNm` varchar(100) DEFAULT NULL,
  `Prd_CateId` smallint DEFAULT NULL,
  PRIMARY KEY (`Prd_SubCateId`)
) ENGINE=InnoDB AUTO_INCREMENT=7 DEFAULT CHARSET=utf8mb3;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `mst_prod_subcategory`
--

LOCK TABLES `mst_prod_subcategory` WRITE;
/*!40000 ALTER TABLE `mst_prod_subcategory` DISABLE KEYS */;
INSERT INTO `mst_prod_subcategory` VALUES (1,'Food Ingredients',1),(2,'Natural Raw Materials',1),(3,'Food Products',2),(4,'Beverages',2),(5,'Men’s Wear',3),(6,'Women’s Wear',3);
/*!40000 ALTER TABLE `mst_prod_subcategory` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `mst_unit`
--

DROP TABLE IF EXISTS `mst_unit`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `mst_unit` (
  `Unit_Id` smallint NOT NULL AUTO_INCREMENT,
  `Unit_Name` varchar(25) DEFAULT NULL,
  PRIMARY KEY (`Unit_Id`)
) ENGINE=InnoDB AUTO_INCREMENT=19 DEFAULT CHARSET=utf8mb3 AVG_ROW_LENGTH=1820;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `mst_unit`
--

LOCK TABLES `mst_unit` WRITE;
/*!40000 ALTER TABLE `mst_unit` DISABLE KEYS */;
INSERT INTO `mst_unit` VALUES (1,'Gm'),(2,'Kg'),(3,'Qtl'),(4,'Ton'),(5,'Ml'),(6,'Ltr'),(7,'Pcs'),(8,'Nos'),(9,'Pkt'),(10,'Carton'),(11,'Bag'),(12,'Jar'),(13,'Bottle'),(14,'Can'),(15,'Tin'),(16,'Dozen'),(17,'Bunch'),(18,'Cm');
/*!40000 ALTER TABLE `mst_unit` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Dumping routines for database 'smartinventory_core'
--
/*!50003 DROP FUNCTION IF EXISTS `UDF_GET_ORG_SCHEMA` */;
/*!50003 SET @saved_cs_client      = @@character_set_client */ ;
/*!50003 SET @saved_cs_results     = @@character_set_results */ ;
/*!50003 SET @saved_col_connection = @@collation_connection */ ;
/*!50003 SET character_set_client  = utf8mb4 */ ;
/*!50003 SET character_set_results = utf8mb4 */ ;
/*!50003 SET collation_connection  = utf8mb4_0900_ai_ci */ ;
/*!50003 SET @saved_sql_mode       = @@sql_mode */ ;
/*!50003 SET sql_mode              = 'ONLY_FULL_GROUP_BY,STRICT_TRANS_TABLES,NO_ZERO_IN_DATE,NO_ZERO_DATE,ERROR_FOR_DIVISION_BY_ZERO,NO_ENGINE_SUBSTITUTION' */ ;
DELIMITER ;;
CREATE DEFINER=`priority`@`%` FUNCTION `UDF_GET_ORG_SCHEMA`(
	pOrg_Code		Varchar(4)
) RETURNS varchar(100) CHARSET utf8mb3
    DETERMINISTIC
BEGIN

	Return (Select Org_Schema From mst_organisation Where Org_Code=pOrg_Code);

END ;;
DELIMITER ;
/*!50003 SET sql_mode              = @saved_sql_mode */ ;
/*!50003 SET character_set_client  = @saved_cs_client */ ;
/*!50003 SET character_set_results = @saved_cs_results */ ;
/*!50003 SET collation_connection  = @saved_col_connection */ ;
/*!50003 DROP PROCEDURE IF EXISTS `USP_GET_APPLICATION_OPTION` */;
/*!50003 SET @saved_cs_client      = @@character_set_client */ ;
/*!50003 SET @saved_cs_results     = @@character_set_results */ ;
/*!50003 SET @saved_col_connection = @@collation_connection */ ;
/*!50003 SET character_set_client  = utf8mb4 */ ;
/*!50003 SET character_set_results = utf8mb4 */ ;
/*!50003 SET collation_connection  = utf8mb4_0900_ai_ci */ ;
/*!50003 SET @saved_sql_mode       = @@sql_mode */ ;
/*!50003 SET sql_mode              = 'ONLY_FULL_GROUP_BY,STRICT_TRANS_TABLES,NO_ZERO_IN_DATE,NO_ZERO_DATE,ERROR_FOR_DIVISION_BY_ZERO,NO_ENGINE_SUBSTITUTION' */ ;
DELIMITER ;;
CREATE DEFINER=`priority`@`%` PROCEDURE `USP_GET_APPLICATION_OPTION`(
	pGroup_Id		Smallint	
)
BEGIN

	Select Value_Id,Value_Name From mst_options Where Option_Id=pGroup_Id;

END ;;
DELIMITER ;
/*!50003 SET sql_mode              = @saved_sql_mode */ ;
/*!50003 SET character_set_client  = @saved_cs_client */ ;
/*!50003 SET character_set_results = @saved_cs_results */ ;
/*!50003 SET collation_connection  = @saved_col_connection */ ;
/*!50003 DROP PROCEDURE IF EXISTS `USP_GET_GST_HSN` */;
/*!50003 SET @saved_cs_client      = @@character_set_client */ ;
/*!50003 SET @saved_cs_results     = @@character_set_results */ ;
/*!50003 SET @saved_col_connection = @@collation_connection */ ;
/*!50003 SET character_set_client  = utf8mb4 */ ;
/*!50003 SET character_set_results = utf8mb4 */ ;
/*!50003 SET collation_connection  = utf8mb4_0900_ai_ci */ ;
/*!50003 SET @saved_sql_mode       = @@sql_mode */ ;
/*!50003 SET sql_mode              = 'ONLY_FULL_GROUP_BY,STRICT_TRANS_TABLES,NO_ZERO_IN_DATE,NO_ZERO_DATE,ERROR_FOR_DIVISION_BY_ZERO,NO_ENGINE_SUBSTITUTION' */ ;
DELIMITER ;;
CREATE DEFINER=`priority`@`%` PROCEDURE `USP_GET_GST_HSN`()
BEGIN
	
    Select Id,Concat(Gst_Code,' - ',Tax_Percent) As HSN From mst_gst_codes Where Is_Active;

END ;;
DELIMITER ;
/*!50003 SET sql_mode              = @saved_sql_mode */ ;
/*!50003 SET character_set_client  = @saved_cs_client */ ;
/*!50003 SET character_set_results = @saved_cs_results */ ;
/*!50003 SET collation_connection  = @saved_col_connection */ ;
/*!50003 DROP PROCEDURE IF EXISTS `USP_GET_ITEM_CAT` */;
/*!50003 SET @saved_cs_client      = @@character_set_client */ ;
/*!50003 SET @saved_cs_results     = @@character_set_results */ ;
/*!50003 SET @saved_col_connection = @@collation_connection */ ;
/*!50003 SET character_set_client  = utf8mb4 */ ;
/*!50003 SET character_set_results = utf8mb4 */ ;
/*!50003 SET collation_connection  = utf8mb4_0900_ai_ci */ ;
/*!50003 SET @saved_sql_mode       = @@sql_mode */ ;
/*!50003 SET sql_mode              = 'ONLY_FULL_GROUP_BY,STRICT_TRANS_TABLES,NO_ZERO_IN_DATE,NO_ZERO_DATE,ERROR_FOR_DIVISION_BY_ZERO,NO_ENGINE_SUBSTITUTION' */ ;
DELIMITER ;;
CREATE DEFINER=`priority`@`%` PROCEDURE `USP_GET_ITEM_CAT`(
	pOrg_Id			Smallint
)
BEGIN

	Select Prd_CateId,Prd_CateNm,Is_Fmcg From mst_prod_category;

END ;;
DELIMITER ;
/*!50003 SET sql_mode              = @saved_sql_mode */ ;
/*!50003 SET character_set_client  = @saved_cs_client */ ;
/*!50003 SET character_set_results = @saved_cs_results */ ;
/*!50003 SET collation_connection  = @saved_col_connection */ ;
/*!50003 DROP PROCEDURE IF EXISTS `USP_GET_ITEM_SUB_CAT` */;
/*!50003 SET @saved_cs_client      = @@character_set_client */ ;
/*!50003 SET @saved_cs_results     = @@character_set_results */ ;
/*!50003 SET @saved_col_connection = @@collation_connection */ ;
/*!50003 SET character_set_client  = utf8mb4 */ ;
/*!50003 SET character_set_results = utf8mb4 */ ;
/*!50003 SET collation_connection  = utf8mb4_0900_ai_ci */ ;
/*!50003 SET @saved_sql_mode       = @@sql_mode */ ;
/*!50003 SET sql_mode              = 'ONLY_FULL_GROUP_BY,STRICT_TRANS_TABLES,NO_ZERO_IN_DATE,NO_ZERO_DATE,ERROR_FOR_DIVISION_BY_ZERO,NO_ENGINE_SUBSTITUTION' */ ;
DELIMITER ;;
CREATE DEFINER=`priority`@`%` PROCEDURE `USP_GET_ITEM_SUB_CAT`(
	pOrg_Id			Smallint,
    pCat_Id			Smallint
)
BEGIN

	Select Prd_SubCateId,Prd_SubCateNm From mst_prod_subcategory Where Prd_CateId=pCat_Id;

END ;;
DELIMITER ;
/*!50003 SET sql_mode              = @saved_sql_mode */ ;
/*!50003 SET character_set_client  = @saved_cs_client */ ;
/*!50003 SET character_set_results = @saved_cs_results */ ;
/*!50003 SET collation_connection  = @saved_col_connection */ ;
/*!50003 DROP PROCEDURE IF EXISTS `USP_GET_ITEM_UNIT` */;
/*!50003 SET @saved_cs_client      = @@character_set_client */ ;
/*!50003 SET @saved_cs_results     = @@character_set_results */ ;
/*!50003 SET @saved_col_connection = @@collation_connection */ ;
/*!50003 SET character_set_client  = utf8mb4 */ ;
/*!50003 SET character_set_results = utf8mb4 */ ;
/*!50003 SET collation_connection  = utf8mb4_0900_ai_ci */ ;
/*!50003 SET @saved_sql_mode       = @@sql_mode */ ;
/*!50003 SET sql_mode              = 'ONLY_FULL_GROUP_BY,STRICT_TRANS_TABLES,NO_ZERO_IN_DATE,NO_ZERO_DATE,ERROR_FOR_DIVISION_BY_ZERO,NO_ENGINE_SUBSTITUTION' */ ;
DELIMITER ;;
CREATE DEFINER=`priority`@`%` PROCEDURE `USP_GET_ITEM_UNIT`(
	pOrg_Id			Smallint
)
BEGIN

	Select Unit_Id,Unit_Name From mst_unit;

END ;;
DELIMITER ;
/*!50003 SET sql_mode              = @saved_sql_mode */ ;
/*!50003 SET character_set_client  = @saved_cs_client */ ;
/*!50003 SET character_set_results = @saved_cs_results */ ;
/*!50003 SET collation_connection  = @saved_col_connection */ ;
/*!40103 SET TIME_ZONE=@OLD_TIME_ZONE */;

/*!40101 SET SQL_MODE=@OLD_SQL_MODE */;
/*!40014 SET FOREIGN_KEY_CHECKS=@OLD_FOREIGN_KEY_CHECKS */;
/*!40014 SET UNIQUE_CHECKS=@OLD_UNIQUE_CHECKS */;
/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
/*!40111 SET SQL_NOTES=@OLD_SQL_NOTES */;

-- Dump completed on 2026-09-07 13:22:07
-- MySQL dump 10.13  Distrib 8.0.46, for Win64 (x86_64)
--
-- Host: 116.193.129.229    Database: smartinventory_bccsl
-- ------------------------------------------------------
-- Server version	8.0.46

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
-- Table structure for table `mst_accountingyear`
--

DROP TABLE IF EXISTS `mst_accountingyear`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `mst_accountingyear` (
  `Year_Id` tinyint NOT NULL AUTO_INCREMENT,
  `Year_Desc` varchar(10) DEFAULT NULL,
  `Year_Start` date DEFAULT NULL,
  `Year_End` date DEFAULT NULL,
  `Is_Active` bit(1) DEFAULT NULL,
  `Created_By` smallint DEFAULT NULL,
  `Created_On` datetime DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`Year_Id`)
) ENGINE=InnoDB AUTO_INCREMENT=15 DEFAULT CHARSET=utf8mb3 AVG_ROW_LENGTH=16384;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `mst_accountingyear`
--

LOCK TABLES `mst_accountingyear` WRITE;
/*!40000 ALTER TABLE `mst_accountingyear` DISABLE KEYS */;
INSERT INTO `mst_accountingyear` VALUES (1,'2026-2027','2026-04-01','2027-03-31',_binary '',12,'2026-08-07 10:23:54');
/*!40000 ALTER TABLE `mst_accountingyear` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `mst_acct_category`
--

DROP TABLE IF EXISTS `mst_acct_category`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `mst_acct_category` (
  `Cate_Id` tinyint NOT NULL,
  `Cate_Desc` varchar(25) DEFAULT NULL,
  `Cate_Code` varchar(5) DEFAULT NULL,
  PRIMARY KEY (`Cate_Id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3 AVG_ROW_LENGTH=2730;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `mst_acct_category`
--

LOCK TABLES `mst_acct_category` WRITE;
/*!40000 ALTER TABLE `mst_acct_category` DISABLE KEYS */;
INSERT INTO `mst_acct_category` VALUES (1,'LIABILITIES','10'),(2,'ASSETS','20'),(3,'INCOME','30'),(4,'EXPENDITURE','40'),(5,'TRADING INCOME','50'),(6,'TRADING EXPENDITURE','60');
/*!40000 ALTER TABLE `mst_acct_category` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `mst_acct_glhead`
--

DROP TABLE IF EXISTS `mst_acct_glhead`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `mst_acct_glhead` (
  `Account_Id` smallint NOT NULL AUTO_INCREMENT,
  `Account_Code` varchar(6) DEFAULT NULL,
  `Account_Desc` varchar(200) DEFAULT NULL,
  `Account_For` char(1) DEFAULT NULL,
  `MainHd_Id` smallint DEFAULT NULL,
  `Is_Active` bit(1) DEFAULT NULL,
  PRIMARY KEY (`Account_Id`)
) ENGINE=InnoDB AUTO_INCREMENT=97 DEFAULT CHARSET=utf8mb3;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `mst_acct_glhead`
--

LOCK TABLES `mst_acct_glhead` WRITE;
/*!40000 ALTER TABLE `mst_acct_glhead` DISABLE KEYS */;
INSERT INTO `mst_acct_glhead` VALUES (1,'100101','PAID UP SHARE','O',1,_binary ''),(2,'100201','ADMISSION FEES','O',2,_binary ''),(3,'100301','SUNDRY CREDITOR','O',3,_binary ''),(4,'100302','EXCESS RECEIVED','O',3,_binary ''),(5,'100303','BHARAT ENGINEERING','O',3,_binary ''),(6,'100304','PROVISION FOR AUDIT FEES','O',3,_binary ''),(7,'100305','FROM MANAGER','O',3,_binary ''),(8,'100306','ASHOK ENTERPRISE','O',3,_binary ''),(9,'100307','DWCRA GROUP SAMANNAY SAMITY','O',3,_binary ''),(10,'100308','LANKA HALUD BARABAZAR','O',3,_binary ''),(11,'200101','CASH IN HAND','C',4,_binary ''),(12,'200102','BMBCCSL CA 132965','B',4,_binary ''),(13,'200103','HDCCB-518-7','B',4,_binary ''),(14,'200104','HDCCB 8303-0','B',4,_binary ''),(15,'200105','ICICI CA','B',4,_binary ''),(16,'200106','RD DEPOSIT','O',4,_binary ''),(17,'200107','FIXED DEPOSIT','O',4,_binary ''),(18,'200108','ADVANCE PAID','O',4,_binary ''),(19,'200109','SUNDRY DEBTORS','O',4,_binary ''),(20,'200110','SUSPANSE','O',4,_binary ''),(21,'200111','STOCK IN HAND','O',4,_binary ''),(22,'200112','DWCRA GROUP','O',4,_binary ''),(23,'200113','RMPA BAGNAN','O',4,_binary ''),(24,'200114','BOYS CLUB','O',4,_binary ''),(25,'200201','FURNITURE (DWCRA)','O',5,_binary ''),(26,'200202','SOLAR SYSTEM','O',5,_binary ''),(27,'200203','XEROX MACHINE (DWCRA)','O',5,_binary ''),(28,'200204','COMPUTER & HARDWARE','O',5,_binary ''),(29,'200205','MACHINE FOR BAG','O',5,_binary ''),(30,'200206','MOTOR TROLLY','O',5,_binary ''),(31,'200207','ELECTRICAL INSTALLATION','O',5,_binary ''),(32,'200208','KNITTING MACHINE','O',5,_binary ''),(33,'200209','COOK STOVE','O',5,_binary ''),(34,'200210','TANK','O',5,_binary ''),(35,'200211','UTENSILS','O',5,_binary ''),(36,'200212','TAILORING  MACHINE','O',5,_binary ''),(37,'200213','EMBROIDERY MACHINE','O',5,_binary ''),(38,'200214','WEIGHT MECHINE','O',5,_binary ''),(39,'200215','COMPUTER SOFTWARE','O',5,_binary ''),(40,'200216','COMPUTER & HARDWARE (DWCRA)','O',5,_binary ''),(41,'300101','MISC INCOME','O',6,_binary ''),(42,'300102','FAIR RECEIVED','O',6,_binary ''),(43,'300103','INTEREST ON SB','O',6,_binary ''),(44,'300104','TRAINING AND EXPOSURE','O',6,_binary ''),(45,'300105','SERVICE CHARGE','O',6,_binary ''),(46,'400101','TRAVELING','O',7,_binary ''),(47,'400102','REPAIRING & MAINTENANCE','O',7,_binary ''),(48,'400103','FUEL CHARGE','O',7,_binary ''),(49,'400104','GAS AND OTHER FUEL','O',7,_binary ''),(50,'400105','MISC. EXPENCES','O',7,_binary ''),(51,'400106','TEA & TIFFIN & MEAL','O',7,_binary ''),(52,'400107','PRINTING & STATIONARY','O',7,_binary ''),(53,'400108','WATER SUPPLY','O',7,_binary ''),(54,'400109','TAX TO PANCHAYET','O',7,_binary ''),(55,'400110','AMC','O',7,_binary ''),(56,'400111','FESTIVAL INCENTIVE','O',7,_binary ''),(57,'400112','TRAINING','O',7,_binary ''),(58,'400113','P TAX','O',7,_binary ''),(59,'400114','GST REGISTRATION','O',7,_binary ''),(60,'400115','TRADE LICENCE','O',7,_binary ''),(61,'400116','BANK CHARGES','O',7,_binary ''),(62,'400117','GST PAYMENT','O',7,_binary ''),(63,'400118','TAX FILLING CHARGE','O',7,_binary ''),(64,'400119','ELECTION A/C','O',7,_binary ''),(65,'400120','AGM','O',7,_binary ''),(66,'400121','GIFT','O',7,_binary ''),(67,'400122','TRAINING AND EXPOSURE','O',7,_binary ''),(68,'400123','TAX RETURN FILLING CHARGE','O',7,_binary ''),(69,'400124','SERVICE CHARGES','O',7,_binary ''),(70,'400125','FASSI LICENCE','O',7,_binary ''),(71,'400126','DATA RECTIFICATION','O',7,_binary ''),(72,'400127','AUDIT FEES','O',7,_binary ''),(73,'400128','DEPRECIATION','O',7,_binary ''),(74,'500101','SALES','O',8,_binary ''),(75,'500102','XEROX','O',8,_binary ''),(76,'500103','FOODING','O',8,_binary ''),(77,'500104','PURCHASE DISCOUNT','O',8,_binary ''),(78,'500105','UMBRELLA INCOME','O',8,_binary ''),(79,'500106','FUCHKA','O',8,_binary ''),(80,'600101','PURCHASE','O',9,_binary ''),(81,'600102','VEGETABLE & OTHER PURCHASE','O',9,_binary ''),(82,'600103','LABOUR CHARGES','O',9,_binary ''),(83,'600104','CARRIAGE CHARGED','O',9,_binary ''),(84,'600105','XEROX EXPENSES','O',9,_binary ''),(85,'600106','COMMISSION','O',9,_binary ''),(86,'600107','HONORARIUM','O',9,_binary ''),(87,'600108','SALARY','O',9,_binary ''),(88,'600109','RENT & ELECTRIFICATION','O',9,_binary ''),(89,'600110','CANDEL EXPENSES','O',9,_binary ''),(90,'600111','UMBRELLA  EXPENDITURE','O',9,_binary ''),(91,'600112','RAW MATERIAL PURCHASE','O',9,_binary ''),(92,'600113','PACKING  & FORWARDING','O',9,_binary ''),(93,'600114','OTHER PURCHASE','O',9,_binary ''),(94,'600115','SALE DISCOUNT','O',9,_binary ''),(95,'200115','Stock In Trade','S',4,NULL),(96,'500107','Closing Stock','T',8,NULL);
/*!40000 ALTER TABLE `mst_acct_glhead` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `mst_acct_glsubhead`
--

DROP TABLE IF EXISTS `mst_acct_glsubhead`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `mst_acct_glsubhead` (
  `SubHd_Id` int NOT NULL AUTO_INCREMENT,
  `SubHd_Desc` varchar(250) NOT NULL,
  `Account_Id` smallint DEFAULT NULL,
  `Branch_Id` smallint DEFAULT NULL,
  `Created_By` smallint DEFAULT NULL,
  `Created_On` datetime DEFAULT NULL,
  `Is_Active` bit(1) DEFAULT NULL,
  PRIMARY KEY (`SubHd_Id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `mst_acct_glsubhead`
--

LOCK TABLES `mst_acct_glsubhead` WRITE;
/*!40000 ALTER TABLE `mst_acct_glsubhead` DISABLE KEYS */;
/*!40000 ALTER TABLE `mst_acct_glsubhead` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `mst_acct_mainhd`
--

DROP TABLE IF EXISTS `mst_acct_mainhd`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `mst_acct_mainhd` (
  `MainHd_Id` smallint NOT NULL AUTO_INCREMENT,
  `MainHd_Code` varchar(5) DEFAULT NULL,
  `MainHd_Desc` varchar(150) DEFAULT NULL,
  `Cate_Id` tinyint DEFAULT NULL,
  PRIMARY KEY (`MainHd_Id`)
) ENGINE=InnoDB AUTO_INCREMENT=10 DEFAULT CHARSET=utf8mb3;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `mst_acct_mainhd`
--

LOCK TABLES `mst_acct_mainhd` WRITE;
/*!40000 ALTER TABLE `mst_acct_mainhd` DISABLE KEYS */;
INSERT INTO `mst_acct_mainhd` VALUES (1,'1001','SHARE CAPITAL',1),(2,'1002','RESERVE & SURPLUS',1),(3,'1003','CURRENT LIABILITY',1),(4,'2001','CURRENT ASSETS',2),(5,'2002','FIXED ASSETS',2),(6,'3001','INDIRECT INCOME',3),(7,'4001','INDIRECT EXPENSE',4),(8,'5001','DIRECT INCOME',5),(9,'6001','DIRECT EXPENSE',6);
/*!40000 ALTER TABLE `mst_acct_mainhd` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `mst_agent`
--

DROP TABLE IF EXISTS `mst_agent`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `mst_agent` (
  `Agent_Id` smallint NOT NULL AUTO_INCREMENT,
  `Agent_Code` int DEFAULT NULL,
  `Agent_Name` varchar(100) DEFAULT NULL,
  `Address` varchar(150) DEFAULT NULL,
  `Contact_No` varchar(15) DEFAULT NULL,
  `Join_Date` date DEFAULT NULL,
  `Stock_Limit` decimal(10,2) DEFAULT NULL,
  `Credit_Sell_Limit` decimal(10,2) DEFAULT NULL,
  `Branch_Id` smallint DEFAULT NULL,
  `LogIn_Pwd` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `Status_Cd` tinyint DEFAULT NULL,
  `Settle_Token` varchar(20) DEFAULT NULL,
  PRIMARY KEY (`Agent_Id`)
) ENGINE=InnoDB AUTO_INCREMENT=37 DEFAULT CHARSET=utf8mb3;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `mst_agent`
--

LOCK TABLES `mst_agent` WRITE;
/*!40000 ALTER TABLE `mst_agent` DISABLE KEYS */;
INSERT INTO `mst_agent` VALUES (30,1001,'Rajesh Kumar','kolkata westbengal','8989989898','2026-09-06',10000000.00,NULL,1,'$2y$12$nZHcz2P/hKXpZV4NmABFGOHo9FyfucxX6xuZ5B4NlZ47.XDrXdOlu',1,NULL);
/*!40000 ALTER TABLE `mst_agent` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `mst_default_ledger`
--

DROP TABLE IF EXISTS `mst_default_ledger`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `mst_default_ledger` (
  `Id` int NOT NULL AUTO_INCREMENT,
  `Cash_Ledg` int DEFAULT NULL,
  `Bank_Ledg` int DEFAULT NULL,
  `CGST_Ledg` int DEFAULT NULL,
  `SGST_Ledg` int DEFAULT NULL,
  `Round_Ledg` int DEFAULT NULL,
  `Discs_Ledg` int DEFAULT NULL COMMENT 'For Sale Discount Allowed',
  `Misc_Ledg` int DEFAULT NULL,
  `Discp_Ledg` int DEFAULT NULL COMMENT 'For Discount Received Purchase',
  `Credit_Ledger` int DEFAULT NULL,
  `Debit_Ledger` int DEFAULT NULL,
  `Freight_Ledg` int DEFAULT NULL,
  `Adm_Fees_Ledg` int DEFAULT NULL COMMENT 'Admission Fees Ledger',
  `Share_Ledg` int DEFAULT NULL COMMENT 'Share Capital Ledger',
  PRIMARY KEY (`Id`)
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8mb3;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `mst_default_ledger`
--

LOCK TABLES `mst_default_ledger` WRITE;
/*!40000 ALTER TABLE `mst_default_ledger` DISABLE KEYS */;
INSERT INTO `mst_default_ledger` VALUES (1,11,NULL,NULL,NULL,NULL,NULL,NULL,NULL,3,19,NULL,2,1);
/*!40000 ALTER TABLE `mst_default_ledger` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `mst_dist`
--

DROP TABLE IF EXISTS `mst_dist`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `mst_dist` (
  `Dist_Id` int NOT NULL AUTO_INCREMENT,
  `Dist_Name` varchar(100) NOT NULL,
  PRIMARY KEY (`Dist_Id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `mst_dist`
--

LOCK TABLES `mst_dist` WRITE;
/*!40000 ALTER TABLE `mst_dist` DISABLE KEYS */;
/*!40000 ALTER TABLE `mst_dist` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `mst_group`
--

DROP TABLE IF EXISTS `mst_group`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `mst_group` (
  `Group_Id` smallint NOT NULL AUTO_INCREMENT,
  `Group_Code` varchar(15) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `Group_Name` varchar(250) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `Formation_Date` date DEFAULT NULL,
  `Address` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `Member_No` tinyint DEFAULT NULL,
  `Staff_Id` smallint DEFAULT NULL,
  `Share_No` smallint DEFAULT NULL,
  `Rate_Per_Share` decimal(10,2) DEFAULT NULL,
  `Share_Amount` decimal(10,2) DEFAULT NULL,
  `Status_Cd` tinyint DEFAULT NULL,
  PRIMARY KEY (`Group_Id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `mst_group`
--

LOCK TABLES `mst_group` WRITE;
/*!40000 ALTER TABLE `mst_group` DISABLE KEYS */;
/*!40000 ALTER TABLE `mst_group` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `mst_gst_codes`
--

DROP TABLE IF EXISTS `mst_gst_codes`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `mst_gst_codes` (
  `Id` smallint NOT NULL AUTO_INCREMENT,
  `Gst_Code` varchar(20) DEFAULT NULL,
  `Category` varchar(250) DEFAULT NULL,
  `Tax_Percent` decimal(5,2) DEFAULT NULL,
  `Sgst` decimal(5,2) DEFAULT NULL,
  `Cgst` decimal(5,2) DEFAULT NULL,
  `Igst` decimal(5,2) DEFAULT NULL,
  `Ugst` decimal(5,2) DEFAULT NULL,
  `Is_Active` bit(1) DEFAULT NULL,
  `Created_By` smallint DEFAULT NULL,
  `Created_On` datetime DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`Id`)
) ENGINE=InnoDB AUTO_INCREMENT=6 DEFAULT CHARSET=utf8mb3;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `mst_gst_codes`
--

LOCK TABLES `mst_gst_codes` WRITE;
/*!40000 ALTER TABLE `mst_gst_codes` DISABLE KEYS */;
INSERT INTO `mst_gst_codes` VALUES (2,'WB5678','raw material',10.00,2.00,2.00,2.00,2.00,_binary '',12,'2026-08-06 14:45:47'),(5,'HSN009','WEDEW',23.00,2.00,3.00,4.00,4.00,_binary '',12,'2026-08-20 12:31:21');
/*!40000 ALTER TABLE `mst_gst_codes` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `mst_member`
--

DROP TABLE IF EXISTS `mst_member`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `mst_member` (
  `Member_Id` int NOT NULL AUTO_INCREMENT,
  `Member_Code` varchar(25) DEFAULT NULL,
  `Member_Type` tinyint DEFAULT NULL COMMENT '1" Member; 2: SHG Group',
  `Admission_Date` date DEFAULT NULL,
  `Member_Name` varchar(200) DEFAULT NULL,
  `Guardian_Name` varchar(150) DEFAULT NULL,
  `Address` varchar(255) DEFAULT NULL,
  `Contact_No` varchar(20) DEFAULT NULL,
  `Aadhar_No` varchar(25) DEFAULT NULL,
  `Voter_No` varchar(25) DEFAULT NULL,
  `Pan_No` varchar(25) DEFAULT NULL,
  `Adm_Fees` decimal(12,2) DEFAULT NULL,
  `Share_No` smallint DEFAULT NULL,
  `Share_Amount` decimal(12,2) DEFAULT NULL,
  `Status_Cd` tinyint DEFAULT NULL,
  `Adm_By` smallint DEFAULT NULL,
  `Created_On` datetime DEFAULT CURRENT_TIMESTAMP,
  `Ref_Member_No` varchar(25) DEFAULT NULL,
  `Rate_Per_Share` decimal(10,2) DEFAULT NULL,
  `Total_Amount` decimal(12,2) DEFAULT NULL,
  PRIMARY KEY (`Member_Id`)
) ENGINE=InnoDB AUTO_INCREMENT=46 DEFAULT CHARSET=utf8mb3;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `mst_member`
--

LOCK TABLES `mst_member` WRITE;
/*!40000 ALTER TABLE `mst_member` DISABLE KEYS */;
INSERT INTO `mst_member` VALUES (41,NULL,1,'2026-08-26','rakesh','rakesh','jkuj','9089897878','343434343444','NRZ7867','DDRTT5656H',100.00,10,100.00,1,12,'2026-08-26 12:10:58',NULL,10.00,200.00),(42,NULL,1,'2026-09-06','kumar sanu','kumar sanu','kolkata','9090898978','909089897823','NRZ556','GGTYY6756R',100.00,100,1000.00,1,12,'2026-09-06 14:20:32',NULL,10.00,1100.00),(43,'M0043',1,'2026-09-07','rishab','rishab','kolkata',NULL,NULL,NULL,NULL,100.00,10,100.00,1,12,'2026-09-07 11:29:55',NULL,10.00,200.00),(44,'M0044',1,'2026-09-07','anil','anil','kolkata',NULL,NULL,NULL,NULL,100.00,0,0.00,1,12,'2026-09-07 11:35:04','wer',10.00,0.00),(45,'M0045',1,'2026-09-07','rrr','rrr','rrr',NULL,NULL,NULL,NULL,100.00,44,440.00,1,12,'2026-09-07 11:48:09','ref43',10.00,540.00);
/*!40000 ALTER TABLE `mst_member` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `mst_menus`
--

DROP TABLE IF EXISTS `mst_menus`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `mst_menus` (
  `Menu_Sl` smallint NOT NULL AUTO_INCREMENT,
  `Menu_Id` smallint DEFAULT NULL,
  `Menu_Name` varchar(50) DEFAULT NULL,
  `SubMenu_Id` smallint DEFAULT NULL,
  `SubMenu_Name` varchar(50) DEFAULT NULL,
  `Icon` varchar(80) DEFAULT NULL,
  `Route` varchar(50) DEFAULT NULL,
  `Is_Active` tinyint(1) DEFAULT NULL,
  PRIMARY KEY (`Menu_Sl`)
) ENGINE=InnoDB AUTO_INCREMENT=78 DEFAULT CHARSET=utf8mb3 AVG_ROW_LENGTH=1489;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `mst_menus`
--

LOCK TABLES `mst_menus` WRITE;
/*!40000 ALTER TABLE `mst_menus` DISABLE KEYS */;
INSERT INTO `mst_menus` VALUES (1,1,'Master Setup',NULL,NULL,'isax isax-setting-2',NULL,1),(2,1,NULL,1,'Organisation Profile',NULL,NULL,1),(4,1,NULL,3,'Chart of Accounts',NULL,'chart-of-accounts',1),(5,1,NULL,4,'Product Master',NULL,'product-master',1),(6,1,NULL,6,'Agent Profile',NULL,'agent-profile',1),(7,1,NULL,7,'Member & Share',NULL,'member-share',1),(8,1,NULL,8,'Supplier Master',NULL,'supplier-master',1),(9,1,NULL,9,'Customer Master',NULL,'customer-master',1),(10,1,NULL,10,'Barcode Setup',NULL,NULL,1),(11,1,NULL,11,'User Roles & Permissions',NULL,'user-roles',1),(12,2,'Purchase & Production Management',NULL,NULL,'isax isax-box-add',NULL,1),(13,2,NULL,1,'Goods Received Entry(GRN)',NULL,'good-received-entry',1),(14,2,NULL,2,'Production',NULL,NULL,1),(15,2,NULL,3,'Purchase Returns ',NULL,'purchase-return',1),(16,3,'Sales & Issue Module',NULL,NULL,'isax isax-shop',NULL,1),(17,3,NULL,1,'Counter Sales ',NULL,'counter-sale',1),(18,3,NULL,2,'Agent Indent ',NULL,'agent-indent',1),(19,3,NULL,3,'Sales Return',NULL,'sale-return',1),(20,3,NULL,4,'Agent Return',NULL,'agent-return',1),(21,4,'Inventory Management',NULL,NULL,'isax isax-layer',NULL,1),(22,4,NULL,1,'Wastage/Damage Entry',NULL,'wastage-damage',1),(23,4,NULL,2,'Stock in / Stock out Entry',NULL,NULL,1),(24,4,NULL,3,'Batch & Expiry',NULL,NULL,0),(25,4,NULL,4,'Stoc Llevel Alerts',NULL,NULL,1),(26,4,NULL,5,'Inventory Reconciliation',NULL,NULL,1),(27,5,'Accounting Entry',NULL,NULL,'isax isax-wallet-3',NULL,1),(28,5,NULL,1,'General Vouchers',NULL,'general-voucher',1),(29,5,NULL,2,'Supplier Payment',NULL,'supplier-payment',1),(30,5,NULL,3,'Customer Collection',NULL,'customer-collection',1),(31,5,NULL,4,'Daily Cash Denomination',NULL,NULL,1),(32,6,'Barcode & Label Management',NULL,NULL,'isax isax-scan',NULL,1),(33,6,NULL,1,'Auto Generate SKU Barcodes',NULL,'barcode-label',1),(34,6,NULL,2,'Print Barcode Labels',NULL,'print-barcode',1),(35,7,'User Management & Security',NULL,NULL,'isax isax-profile-2user',NULL,1),(36,7,NULL,1,'User Group',NULL,NULL,1),(37,7,NULL,2,'Group Previledges',NULL,NULL,1),(38,7,NULL,3,'User Creation',NULL,'user-creation',1),(39,8,'Inventory Reports',NULL,NULL,'isax isax-chart-2',NULL,1),(40,9,'Accounts Reports',NULL,NULL,'isax isax-document-text',NULL,1),(41,1,NULL,12,'Product Category',NULL,'prod-category',1),(42,1,NULL,13,'Product Sub Category',NULL,'prod-subcategory',1),(43,1,NULL,14,'Unit Master',NULL,'unit-master',1),(44,1,NULL,15,'Accounting Year',NULL,'accounting-year',1),(45,1,NULL,16,'GST Codes',NULL,'gst-codes',1),(49,8,NULL,1,'Stock Summary Report',NULL,'stock-summary',1),(50,8,NULL,2,'Purchase Register',NULL,'purchase-register',1),(51,8,NULL,3,'Sales Register (Counter, Agent, Consolidated)',NULL,'sales-register',1),(52,8,NULL,4,'Agent Register (Indent, Stock)',NULL,'agent-register',1),(53,8,NULL,5,'Return Register (Purchase, Sales)',NULL,'return-register',1),(54,8,NULL,6,'Supplier Register & Detailed List',NULL,'supplier-register',1),(55,8,NULL,7,'Customer Register & Detailed List',NULL,'customer-register',1),(56,8,NULL,8,'Share Register',NULL,'share-register',1),(57,8,NULL,9,'User Scroll (Counter, Agent)',NULL,'user-scroll',1),(58,8,NULL,10,'GST Register',NULL,'gst-register',1),(59,8,NULL,11,'Stock Statement & Register (Item wise/All)',NULL,'stock-statement',1),(60,8,NULL,12,'Expiry Report',NULL,'expiry-report',1),(61,8,NULL,13,'Reorder Level Report',NULL,'reorder-report',1),(62,8,NULL,14,'Slow-moving / Fast-moving Item Analysis',NULL,'movement-report',1),(63,9,NULL,1,'User Scroll (Counter, Agent)',NULL,'account-user-scroll',1),(64,9,NULL,2,'Cash Book',NULL,'cash-book',1),(65,9,NULL,3,'Journal Book',NULL,'journal-book',1),(66,9,NULL,4,'Cash A/c',NULL,'cash-account',1),(67,9,NULL,5,'Ledger Book',NULL,'ledger-book',1),(68,9,NULL,6,'Receipt & Payment',NULL,'receipt-payment',1),(69,9,NULL,7,'Trial Balance',NULL,'trial-balance',1),(70,9,NULL,8,'Trading & P/L A/c',NULL,'trading-pl',1),(71,9,NULL,9,'P/L Appropriation A/C',NULL,'pl-appropriation',1),(72,9,NULL,10,'Balance Sheet',NULL,'balance-sheet',1),(73,1,NULL,17,'Counter Balance',NULL,'counter-balance',1),(75,3,NULL,5,'Agent Settlement',NULL,'agent-settlement',1),(76,0,'Dashboard',NULL,NULL,'isax isax-home-2','user-dashboard',1),(77,1,NULL,5,'Address Master',NULL,'address-master',1);
/*!40000 ALTER TABLE `mst_menus` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `mst_menus_agent`
--

DROP TABLE IF EXISTS `mst_menus_agent`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `mst_menus_agent` (
  `Menu_Sl` smallint NOT NULL AUTO_INCREMENT,
  `Menu_Id` smallint DEFAULT NULL,
  `Menu_Name` varchar(50) DEFAULT NULL,
  `SubMenu_Id` smallint DEFAULT NULL,
  `SubMenu_Name` varchar(50) DEFAULT NULL,
  `Icon` varchar(25) DEFAULT NULL,
  `Route` varchar(50) DEFAULT NULL,
  `Is_Active` tinyint(1) DEFAULT NULL,
  PRIMARY KEY (`Menu_Sl`)
) ENGINE=InnoDB AUTO_INCREMENT=15 DEFAULT CHARSET=utf8mb3 AVG_ROW_LENGTH=1489;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `mst_menus_agent`
--

LOCK TABLES `mst_menus_agent` WRITE;
/*!40000 ALTER TABLE `mst_menus_agent` DISABLE KEYS */;
INSERT INTO `mst_menus_agent` VALUES (1,1,'Dashboard',NULL,NULL,'fas fa-home','agent.dashboard',1),(2,2,'Item Requisition',NULL,NULL,'fas fa-clipboard-list','agent.requisition',1),(3,3,'Item Sale',NULL,NULL,'fas fa-shopping-cart','agent.sale',1),(4,5,'Customer Return',NULL,NULL,'fas fa-undo','agent.customer',1),(5,6,'Office Return',NULL,NULL,'fas fa-truck','agent.office.return',1),(6,7,'Register',NULL,NULL,'fas fa-chart-bar',NULL,1),(7,7,NULL,1,'Indent',NULL,'agent.report.indent',1),(8,7,NULL,2,'Issue',NULL,'agent.report.issue',1),(9,7,NULL,3,'Sales',NULL,'agent.report.sale',1),(10,7,NULL,4,'Return',NULL,'agent.report.return',1),(11,7,NULL,5,'Stock',NULL,'agent.report.stock',1),(12,12,'Office Settlement',NULL,NULL,'fas fa-hand-holding-usd','agent.settlement',1),(13,4,'Customer Payment',NULL,NULL,'fas fa-money-bill-wave','agent.customer.payment',1),(14,7,NULL,6,'Customer Due',NULL,'agent.report.customer.due',1);
/*!40000 ALTER TABLE `mst_menus_agent` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `mst_party`
--

DROP TABLE IF EXISTS `mst_party`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `mst_party` (
  `Party_Id` int NOT NULL AUTO_INCREMENT,
  `Branch_Id` smallint DEFAULT NULL,
  `Party_Type` tinyint DEFAULT NULL COMMENT '1: Sundry Creditor; 2: Sundry Debtor',
  `Party_Code` varchar(25) DEFAULT NULL,
  `Party_Name` varchar(250) DEFAULT NULL,
  `Contact_No` varchar(25) DEFAULT NULL,
  `AltContact_No` varchar(25) DEFAULT NULL,
  `EMail` varchar(50) DEFAULT NULL,
  `Contact_Person` varchar(150) DEFAULT NULL,
  `Designation` varchar(50) DEFAULT NULL,
  `Address_Line1` varchar(200) DEFAULT NULL,
  `Address_Line2` varchar(200) DEFAULT NULL,
  `City` varchar(50) DEFAULT NULL,
  `District` varchar(50) DEFAULT NULL,
  `State` varchar(25) DEFAULT NULL,
  `PinCode` varchar(10) DEFAULT NULL,
  `Pan_No` varchar(25) DEFAULT NULL,
  `GstIn` varchar(25) DEFAULT NULL,
  `State_Cd` varchar(5) DEFAULT NULL,
  `Start_Date` date DEFAULT NULL,
  `Credit_Limit` decimal(18,2) DEFAULT NULL,
  `Cust_Agent_Id` smallint DEFAULT NULL,
  `Opening_Bal` decimal(18,2) DEFAULT '0.00',
  `Created_By` smallint DEFAULT NULL,
  `Created_On` datetime DEFAULT NULL,
  `Status_Cd` tinyint DEFAULT NULL,
  PRIMARY KEY (`Party_Id`)
) ENGINE=InnoDB AUTO_INCREMENT=45 DEFAULT CHARSET=utf8mb3;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `mst_party`
--

LOCK TABLES `mst_party` WRITE;
/*!40000 ALTER TABLE `mst_party` DISABLE KEYS */;
INSERT INTO `mst_party` VALUES (38,1,1,'SUP101','Ganesh Distributors','9089787867',NULL,NULL,NULL,NULL,'KOLKATA ,WESTBENGAL,INDIA',NULL,NULL,NULL,NULL,NULL,NULL,NULL,'19','2026-08-25',NULL,NULL,100.00,NULL,NULL,1),(39,1,1,'SUP102','BALAJI GOODS AND SUPPLIER','9089786756',NULL,NULL,NULL,NULL,'TARKESHWER,WESTBENGAL',NULL,NULL,NULL,NULL,NULL,NULL,NULL,'19','2026-08-25',NULL,NULL,0.00,NULL,NULL,1),(40,1,2,'CUST1','MUKESH ROY','9089787654',NULL,NULL,NULL,NULL,'KOLKATA,WESTBENGAL,INDIA',NULL,NULL,NULL,NULL,NULL,NULL,NULL,'19','2026-08-25',NULL,NULL,0.00,NULL,NULL,1),(41,1,2,'CUST2','ASHIM GHOSH','9089876756',NULL,NULL,NULL,NULL,'JOYPUR,WESTBENGAL,INDIA',NULL,NULL,NULL,NULL,NULL,NULL,NULL,'19','2026-08-25',NULL,30,0.00,NULL,NULL,1),(42,1,2,'cust300','monoj roy','9898786765',NULL,NULL,NULL,NULL,'kolkata,westbengal',NULL,NULL,NULL,NULL,NULL,NULL,NULL,'19','2026-09-01',NULL,30,0.00,NULL,NULL,1),(43,1,1,'S0040','Ratna Tredars',NULL,NULL,NULL,NULL,NULL,'kolkata',NULL,NULL,NULL,NULL,NULL,NULL,NULL,'19','2026-09-07',NULL,NULL,0.00,NULL,NULL,1),(44,1,2,'C0043','ajit roy',NULL,NULL,NULL,NULL,NULL,'kolkata',NULL,NULL,NULL,NULL,NULL,NULL,NULL,'19','2026-09-07',NULL,30,0.00,NULL,NULL,1);
/*!40000 ALTER TABLE `mst_party` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `mst_pin`
--

DROP TABLE IF EXISTS `mst_pin`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `mst_pin` (
  `Pin_Id` int NOT NULL AUTO_INCREMENT,
  `Pin_Code` varchar(10) NOT NULL,
  PRIMARY KEY (`Pin_Id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `mst_pin`
--

LOCK TABLES `mst_pin` WRITE;
/*!40000 ALTER TABLE `mst_pin` DISABLE KEYS */;
/*!40000 ALTER TABLE `mst_pin` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `mst_post`
--

DROP TABLE IF EXISTS `mst_post`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `mst_post` (
  `Post_Id` int NOT NULL AUTO_INCREMENT,
  `Post_Name` varchar(100) NOT NULL,
  PRIMARY KEY (`Post_Id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `mst_post`
--

LOCK TABLES `mst_post` WRITE;
/*!40000 ALTER TABLE `mst_post` DISABLE KEYS */;
/*!40000 ALTER TABLE `mst_post` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `mst_prod_category`
--

DROP TABLE IF EXISTS `mst_prod_category`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `mst_prod_category` (
  `Prd_CateId` smallint NOT NULL AUTO_INCREMENT,
  `Prd_CateNm` varchar(75) DEFAULT NULL,
  `Is_Fmcg` bit(1) DEFAULT b'0',
  `Agent_Comm` decimal(6,2) DEFAULT NULL,
  `Pur_Ledg` int DEFAULT NULL,
  `Sale_Ledg` int DEFAULT NULL,
  `Created_By` smallint DEFAULT NULL,
  `Created_On` datetime DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`Prd_CateId`)
) ENGINE=InnoDB AUTO_INCREMENT=17 DEFAULT CHARSET=utf8mb3 AVG_ROW_LENGTH=5461;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `mst_prod_category`
--

LOCK TABLES `mst_prod_category` WRITE;
/*!40000 ALTER TABLE `mst_prod_category` DISABLE KEYS */;
INSERT INTO `mst_prod_category` VALUES (10,'Raw Materials',_binary '\0',6.00,80,0,12,'2026-08-06 10:38:02'),(12,'Consumer Goods (FMCG)',_binary '',4.00,93,78,12,'2026-08-06 10:50:13'),(13,'Garments',_binary '\0',3.00,93,74,12,'2026-08-06 10:53:04');
/*!40000 ALTER TABLE `mst_prod_category` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `mst_prod_subcategory`
--

DROP TABLE IF EXISTS `mst_prod_subcategory`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `mst_prod_subcategory` (
  `Prd_SubCateId` smallint NOT NULL AUTO_INCREMENT,
  `Prd_SubCateNm` varchar(100) DEFAULT NULL,
  `Prd_CateId` smallint DEFAULT NULL,
  `Created_By` smallint DEFAULT NULL,
  `Created_On` datetime DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`Prd_SubCateId`)
) ENGINE=InnoDB AUTO_INCREMENT=21 DEFAULT CHARSET=utf8mb3;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `mst_prod_subcategory`
--

LOCK TABLES `mst_prod_subcategory` WRITE;
/*!40000 ALTER TABLE `mst_prod_subcategory` DISABLE KEYS */;
INSERT INTO `mst_prod_subcategory` VALUES (13,'Food Ingredientseee',10,12,'2026-08-06 11:17:52'),(14,'Men’s Wear',13,12,'2026-08-06 11:18:06'),(15,'Food Products',12,12,'2026-08-06 11:18:22'),(16,'Beverages',12,12,'2026-08-06 11:19:26'),(17,'Natural Raw Materials',10,12,'2026-08-06 11:19:48'),(18,'Women’s Wear',13,12,'2026-08-06 11:20:03'),(20,'snaks',12,12,'2026-08-26 16:27:54');
/*!40000 ALTER TABLE `mst_prod_subcategory` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `mst_product`
--

DROP TABLE IF EXISTS `mst_product`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `mst_product` (
  `Prod_Id` int NOT NULL AUTO_INCREMENT,
  `Prod_Code` varchar(25) NOT NULL,
  `Prod_ShortNm` varchar(100) NOT NULL,
  `Prod_PrintNm` varchar(50) DEFAULT NULL,
  `Unit_Id` smallint DEFAULT NULL,
  `Cate_Id` smallint DEFAULT NULL,
  `SubCate_Id` smallint DEFAULT NULL,
  `Gst_Id` smallint DEFAULT NULL,
  `Sale_Rate` decimal(10,2) DEFAULT NULL,
  `Sales_Comm` decimal(5,2) DEFAULT NULL,
  `Sale_Margin` decimal(6,2) DEFAULT NULL,
  `ReOrder_Qty` smallint DEFAULT NULL,
  `Barcode_Label` varchar(25) DEFAULT NULL,
  `Prod_Life` smallint DEFAULT NULL,
  `Is_Active` bit(1) DEFAULT b'1',
  PRIMARY KEY (`Prod_Id`),
  UNIQUE KEY `Prod_Code_UNIQUE` (`Prod_Code`)
) ENGINE=InnoDB AUTO_INCREMENT=117 DEFAULT CHARSET=utf8mb3;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `mst_product`
--

LOCK TABLES `mst_product` WRITE;
/*!40000 ALTER TABLE `mst_product` DISABLE KEYS */;
INSERT INTO `mst_product` VALUES (97,'PROD1','Dry Fruits','Dry Fruits',24,10,13,2,NULL,NULL,10.00,10,NULL,NULL,_binary ''),(98,'PROD2','PADDY SEEDS','PADDY  SEEDS',24,10,17,2,NULL,NULL,10.00,20,NULL,NULL,_binary ''),(99,'PROD3','Biscuits','Biscuits',27,12,15,2,NULL,NULL,2.00,10,NULL,120,_binary ''),(100,'PROD4','Coca Cola','Coca Cola',27,12,16,2,NULL,NULL,10.00,10,NULL,200,_binary ''),(101,'PROD5','ASUS Men Blue T-Shirt','T-Shirts',28,13,14,2,NULL,NULL,20.00,50,NULL,NULL,_binary ''),(102,'PROD6','Cotton A-Line Dress','COTTON DRESS',28,13,18,2,NULL,NULL,10.00,10,NULL,NULL,_binary ''),(103,'901','coconut oil 5 kg','coconut oil',24,10,17,2,NULL,NULL,10.00,10,NULL,NULL,_binary ''),(104,'PROD2019','RICE  50 KG','BASHMATI RICE',27,10,13,2,NULL,NULL,10.00,10,NULL,NULL,_binary ''),(105,'1010','masala pasta','pasta',27,10,13,2,NULL,NULL,10.00,10,NULL,NULL,_binary ''),(106,'9090','chini 50kg','chini',27,10,13,2,NULL,NULL,10.00,10,NULL,NULL,_binary ''),(107,'7878','milk product','milk',26,12,15,2,NULL,NULL,10.00,10,NULL,200,_binary ''),(108,'9999','BUTTER','butter',24,10,13,2,NULL,NULL,10.00,10,NULL,NULL,_binary ''),(109,'prod555','masala 10kg','masala',27,10,13,2,NULL,NULL,10.00,10,NULL,NULL,_binary ''),(110,'prod666','poteto','poteto',24,12,15,2,NULL,NULL,10.00,10,NULL,20,_binary ''),(111,'prod21','bashmati rice','bashmati rice',27,10,13,2,NULL,NULL,10.00,10,NULL,NULL,_binary ''),(112,'prod10','Lanka','Lanka',24,10,13,2,NULL,NULL,10.00,5,NULL,NULL,_binary ''),(113,'prod15','Holud','Holud',25,10,13,2,NULL,NULL,10.00,5,NULL,NULL,_binary ''),(114,'prod20','Sari','Sari',28,13,18,2,NULL,NULL,10.00,5,NULL,NULL,_binary ''),(115,'// #     ////....','rsdtdr','werw',24,10,13,2,NULL,NULL,100.00,56,NULL,NULL,_binary ''),(116,'2002','chanachur','chanachur',27,10,13,2,NULL,NULL,10.00,10,NULL,NULL,_binary '');
/*!40000 ALTER TABLE `mst_product` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `mst_ps`
--

DROP TABLE IF EXISTS `mst_ps`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `mst_ps` (
  `Ps_Id` int NOT NULL AUTO_INCREMENT,
  `Ps_Name` varchar(100) NOT NULL,
  PRIMARY KEY (`Ps_Id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `mst_ps`
--

LOCK TABLES `mst_ps` WRITE;
/*!40000 ALTER TABLE `mst_ps` DISABLE KEYS */;
/*!40000 ALTER TABLE `mst_ps` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `mst_share_config`
--

DROP TABLE IF EXISTS `mst_share_config`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `mst_share_config` (
  `id` int NOT NULL AUTO_INCREMENT,
  `adm_fees` decimal(12,2) NOT NULL DEFAULT '0.00',
  `rate_share` decimal(10,2) NOT NULL DEFAULT '0.00',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8mb3;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `mst_share_config`
--

LOCK TABLES `mst_share_config` WRITE;
/*!40000 ALTER TABLE `mst_share_config` DISABLE KEYS */;
INSERT INTO `mst_share_config` VALUES (1,100.00,10.00);
/*!40000 ALTER TABLE `mst_share_config` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `mst_subgl_opening`
--

DROP TABLE IF EXISTS `mst_subgl_opening`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `mst_subgl_opening` (
  `Open_Id` int NOT NULL AUTO_INCREMENT,
  `Open_Date` date DEFAULT NULL,
  `Year_Id` tinyint DEFAULT NULL,
  `Branch_Id` smallint DEFAULT NULL,
  `SubHd_Id` int DEFAULT NULL,
  `Open_Balance` decimal(18,2) DEFAULT NULL,
  PRIMARY KEY (`Open_Id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `mst_subgl_opening`
--

LOCK TABLES `mst_subgl_opening` WRITE;
/*!40000 ALTER TABLE `mst_subgl_opening` DISABLE KEYS */;
/*!40000 ALTER TABLE `mst_subgl_opening` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `mst_unit`
--

DROP TABLE IF EXISTS `mst_unit`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `mst_unit` (
  `Unit_Id` smallint NOT NULL AUTO_INCREMENT,
  `Unit_Name` varchar(25) DEFAULT NULL,
  `Created_By` smallint DEFAULT NULL,
  `Created_On` datetime DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`Unit_Id`)
) ENGINE=InnoDB AUTO_INCREMENT=32 DEFAULT CHARSET=utf8mb3 AVG_ROW_LENGTH=1820;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `mst_unit`
--

LOCK TABLES `mst_unit` WRITE;
/*!40000 ALTER TABLE `mst_unit` DISABLE KEYS */;
INSERT INTO `mst_unit` VALUES (24,'KG',12,'2026-08-06 11:32:12'),(25,'GM',12,'2026-08-06 11:32:20'),(26,'LTR',12,'2026-08-06 11:32:36'),(27,'PKT',12,'2026-08-06 11:32:43'),(28,'PIECE',12,'2026-08-06 11:32:51'),(30,'BOTTLE',12,'2026-08-06 11:34:02'),(31,'BAG',12,'2026-08-06 17:40:48');
/*!40000 ALTER TABLE `mst_unit` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `mst_user`
--

DROP TABLE IF EXISTS `mst_user`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `mst_user` (
  `User_Id` smallint NOT NULL AUTO_INCREMENT,
  `User_Code` varchar(25) DEFAULT NULL,
  `User_FullName` varchar(100) DEFAULT NULL,
  `Contact_No` varchar(25) DEFAULT NULL,
  `Email_Id` varchar(25) DEFAULT NULL,
  `User_Name` varchar(25) DEFAULT NULL,
  `User_Pwd` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `UGrp_Id` smallint DEFAULT NULL,
  `Branch_Id` smallint DEFAULT NULL,
  `Created_By` smallint DEFAULT NULL,
  `Created_On` datetime DEFAULT NULL,
  `Status_Cd` tinyint DEFAULT NULL,
  PRIMARY KEY (`User_Id`)
) ENGINE=InnoDB AUTO_INCREMENT=22 DEFAULT CHARSET=utf8mb3;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `mst_user`
--

LOCK TABLES `mst_user` WRITE;
/*!40000 ALTER TABLE `mst_user` DISABLE KEYS */;
INSERT INTO `mst_user` VALUES (12,'user23','Rakesh ','8989898989','rakesh@gmail.com','rakesh23','$2y$12$jywDVNc8EXWg.Ta0qekbRu8J9Xbqf6LjkP.SOIZVyDdq9TC2rToRe',1,1,1,NULL,1),(21,'user24','mukesh kumar','8989898978',NULL,'mukesh','$2y$12$FEPmhq0FHkWMz6hYeF3Da.eFx/pfGRHYG7WLFn0ETGsrmmo21/2Ii',22,1,12,NULL,1);
/*!40000 ALTER TABLE `mst_user` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `mst_user_group`
--

DROP TABLE IF EXISTS `mst_user_group`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `mst_user_group` (
  `UGrp_Id` smallint NOT NULL AUTO_INCREMENT,
  `UGrp_Name` varchar(50) DEFAULT NULL,
  `UGrp_Description` varchar(150) DEFAULT NULL,
  `Is_Admin` bit(1) DEFAULT NULL,
  `Is_Active` bit(1) DEFAULT b'1',
  PRIMARY KEY (`UGrp_Id`)
) ENGINE=InnoDB AUTO_INCREMENT=26 DEFAULT CHARSET=utf8mb3;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `mst_user_group`
--

LOCK TABLES `mst_user_group` WRITE;
/*!40000 ALTER TABLE `mst_user_group` DISABLE KEYS */;
INSERT INTO `mst_user_group` VALUES (1,'Admin','System Admin',_binary '',NULL),(22,'Accountant','Maintains receipts, payments, ledgers, vouchers, cash book, and financial reports of the societ',_binary '\0',_binary ''),(23,'Purchase Officer','Handles supplier management, purchase orders, purchase entries, and purchase returns.',_binary '\0',_binary ''),(24,'Sales Counter Operator','Sells products to members/customers, generates bills, scans barcodes, and processes sales returns.',_binary '\0',_binary ''),(25,'Member Registration Officer','Registers new members, updates member information, and maintains membership records.',_binary '\0',_binary '');
/*!40000 ALTER TABLE `mst_user_group` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `mst_user_permission`
--

DROP TABLE IF EXISTS `mst_user_permission`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `mst_user_permission` (
  `Perm_Id` smallint NOT NULL AUTO_INCREMENT,
  `UGrp_Id` smallint DEFAULT NULL,
  `Menu_Id` smallint DEFAULT NULL,
  `Sub_men_Id` int DEFAULT NULL,
  `User_View` bit(1) DEFAULT b'0',
  `User_Add` bit(1) DEFAULT b'0',
  `User_Edit` bit(1) DEFAULT b'0',
  `User_Del` bit(1) DEFAULT b'0',
  `User_Print` bit(1) DEFAULT b'0',
  `Is_Active` bit(1) DEFAULT b'1',
  PRIMARY KEY (`Perm_Id`)
) ENGINE=InnoDB AUTO_INCREMENT=820 DEFAULT CHARSET=utf8mb3;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `mst_user_permission`
--

LOCK TABLES `mst_user_permission` WRITE;
/*!40000 ALTER TABLE `mst_user_permission` DISABLE KEYS */;
INSERT INTO `mst_user_permission` VALUES (683,23,0,NULL,_binary '',_binary '\0',_binary '\0',_binary '\0',_binary '\0',_binary ''),(684,24,0,NULL,_binary '',_binary '\0',_binary '\0',_binary '\0',_binary '\0',_binary ''),(749,1,5,1,_binary '',_binary '\0',_binary '\0',_binary '\0',_binary '\0',_binary ''),(750,1,5,2,_binary '',_binary '\0',_binary '\0',_binary '\0',_binary '\0',_binary ''),(751,1,5,3,_binary '',_binary '\0',_binary '\0',_binary '\0',_binary '\0',_binary ''),(752,1,5,4,_binary '',_binary '\0',_binary '\0',_binary '\0',_binary '\0',_binary ''),(753,1,6,1,_binary '',_binary '\0',_binary '\0',_binary '\0',_binary '\0',_binary ''),(754,1,6,2,_binary '',_binary '\0',_binary '\0',_binary '\0',_binary '\0',_binary ''),(755,1,0,NULL,_binary '',_binary '\0',_binary '\0',_binary '\0',_binary '\0',_binary ''),(756,22,1,1,_binary '',_binary '\0',_binary '\0',_binary '\0',_binary '\0',_binary ''),(757,22,1,3,_binary '',_binary '\0',_binary '\0',_binary '\0',_binary '\0',_binary ''),(758,22,1,4,_binary '',_binary '\0',_binary '\0',_binary '\0',_binary '\0',_binary ''),(759,22,1,5,_binary '',_binary '\0',_binary '\0',_binary '\0',_binary '\0',_binary ''),(760,22,1,6,_binary '',_binary '\0',_binary '\0',_binary '\0',_binary '\0',_binary ''),(761,22,1,7,_binary '',_binary '\0',_binary '\0',_binary '\0',_binary '\0',_binary ''),(762,22,1,8,_binary '',_binary '\0',_binary '\0',_binary '\0',_binary '\0',_binary ''),(763,22,1,9,_binary '',_binary '\0',_binary '\0',_binary '\0',_binary '\0',_binary ''),(764,22,1,10,_binary '',_binary '\0',_binary '\0',_binary '\0',_binary '\0',_binary ''),(765,22,1,11,_binary '',_binary '\0',_binary '\0',_binary '\0',_binary '\0',_binary ''),(766,22,1,12,_binary '',_binary '\0',_binary '\0',_binary '\0',_binary '\0',_binary ''),(767,22,1,13,_binary '',_binary '\0',_binary '\0',_binary '\0',_binary '\0',_binary ''),(768,22,1,14,_binary '',_binary '\0',_binary '\0',_binary '\0',_binary '\0',_binary ''),(769,22,1,15,_binary '',_binary '\0',_binary '\0',_binary '\0',_binary '\0',_binary ''),(770,22,1,16,_binary '',_binary '\0',_binary '\0',_binary '\0',_binary '\0',_binary ''),(771,22,2,1,_binary '',_binary '\0',_binary '\0',_binary '\0',_binary '\0',_binary ''),(772,22,2,2,_binary '',_binary '\0',_binary '\0',_binary '\0',_binary '\0',_binary ''),(773,22,2,3,_binary '',_binary '\0',_binary '\0',_binary '\0',_binary '\0',_binary ''),(774,22,3,1,_binary '',_binary '\0',_binary '\0',_binary '\0',_binary '\0',_binary ''),(775,22,3,2,_binary '',_binary '\0',_binary '\0',_binary '\0',_binary '\0',_binary ''),(776,22,3,3,_binary '',_binary '\0',_binary '\0',_binary '\0',_binary '\0',_binary ''),(777,22,3,4,_binary '',_binary '\0',_binary '\0',_binary '\0',_binary '\0',_binary ''),(778,22,3,5,_binary '',_binary '\0',_binary '\0',_binary '\0',_binary '\0',_binary ''),(779,22,4,1,_binary '',_binary '\0',_binary '\0',_binary '\0',_binary '\0',_binary ''),(780,22,4,2,_binary '',_binary '\0',_binary '\0',_binary '\0',_binary '\0',_binary ''),(781,22,4,4,_binary '',_binary '\0',_binary '\0',_binary '\0',_binary '\0',_binary ''),(782,22,4,5,_binary '',_binary '\0',_binary '\0',_binary '\0',_binary '\0',_binary ''),(783,22,5,1,_binary '',_binary '\0',_binary '\0',_binary '\0',_binary '\0',_binary ''),(784,22,5,2,_binary '',_binary '\0',_binary '\0',_binary '\0',_binary '\0',_binary ''),(785,22,5,3,_binary '',_binary '\0',_binary '\0',_binary '\0',_binary '\0',_binary ''),(786,22,5,4,_binary '',_binary '\0',_binary '\0',_binary '\0',_binary '\0',_binary ''),(787,22,6,1,_binary '',_binary '\0',_binary '\0',_binary '\0',_binary '\0',_binary ''),(788,22,6,2,_binary '',_binary '\0',_binary '\0',_binary '\0',_binary '\0',_binary ''),(789,22,7,1,_binary '',_binary '\0',_binary '\0',_binary '\0',_binary '\0',_binary ''),(790,22,7,2,_binary '',_binary '\0',_binary '\0',_binary '\0',_binary '\0',_binary ''),(791,22,7,3,_binary '',_binary '\0',_binary '\0',_binary '\0',_binary '\0',_binary ''),(792,22,8,1,_binary '',_binary '\0',_binary '\0',_binary '\0',_binary '\0',_binary ''),(793,22,8,2,_binary '',_binary '\0',_binary '\0',_binary '\0',_binary '\0',_binary ''),(794,22,8,3,_binary '',_binary '\0',_binary '\0',_binary '\0',_binary '\0',_binary ''),(795,22,8,4,_binary '',_binary '\0',_binary '\0',_binary '\0',_binary '\0',_binary ''),(796,22,8,5,_binary '',_binary '\0',_binary '\0',_binary '\0',_binary '\0',_binary ''),(797,22,8,6,_binary '',_binary '\0',_binary '\0',_binary '\0',_binary '\0',_binary ''),(798,22,8,7,_binary '',_binary '\0',_binary '\0',_binary '\0',_binary '\0',_binary ''),(799,22,8,8,_binary '',_binary '\0',_binary '\0',_binary '\0',_binary '\0',_binary ''),(800,22,8,9,_binary '',_binary '\0',_binary '\0',_binary '\0',_binary '\0',_binary ''),(801,22,8,10,_binary '',_binary '\0',_binary '\0',_binary '\0',_binary '\0',_binary ''),(802,22,8,11,_binary '',_binary '\0',_binary '\0',_binary '\0',_binary '\0',_binary ''),(803,22,8,12,_binary '',_binary '\0',_binary '\0',_binary '\0',_binary '\0',_binary ''),(804,22,8,13,_binary '',_binary '\0',_binary '\0',_binary '\0',_binary '\0',_binary ''),(805,22,8,14,_binary '',_binary '\0',_binary '\0',_binary '\0',_binary '\0',_binary ''),(806,22,9,1,_binary '',_binary '\0',_binary '\0',_binary '\0',_binary '\0',_binary ''),(807,22,9,2,_binary '',_binary '\0',_binary '\0',_binary '\0',_binary '\0',_binary ''),(808,22,9,3,_binary '',_binary '\0',_binary '\0',_binary '\0',_binary '\0',_binary ''),(809,22,9,4,_binary '',_binary '\0',_binary '\0',_binary '\0',_binary '\0',_binary ''),(810,22,9,5,_binary '',_binary '\0',_binary '\0',_binary '\0',_binary '\0',_binary ''),(811,22,9,6,_binary '',_binary '\0',_binary '\0',_binary '\0',_binary '\0',_binary ''),(812,22,9,7,_binary '',_binary '\0',_binary '\0',_binary '\0',_binary '\0',_binary ''),(813,22,9,8,_binary '',_binary '\0',_binary '\0',_binary '\0',_binary '\0',_binary ''),(814,22,9,9,_binary '',_binary '\0',_binary '\0',_binary '\0',_binary '\0',_binary ''),(815,22,9,10,_binary '',_binary '\0',_binary '\0',_binary '\0',_binary '\0',_binary ''),(816,22,0,NULL,_binary '',_binary '\0',_binary '\0',_binary '\0',_binary '\0',_binary ''),(819,25,0,NULL,_binary '',_binary '\0',_binary '\0',_binary '\0',_binary '\0',_binary '');
/*!40000 ALTER TABLE `mst_user_permission` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `mst_village`
--

DROP TABLE IF EXISTS `mst_village`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `mst_village` (
  `Village_Id` int NOT NULL AUTO_INCREMENT,
  `Village_Name` varchar(100) NOT NULL,
  PRIMARY KEY (`Village_Id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `mst_village`
--

LOCK TABLES `mst_village` WRITE;
/*!40000 ALTER TABLE `mst_village` DISABLE KEYS */;
/*!40000 ALTER TABLE `mst_village` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `stores_id`
--

DROP TABLE IF EXISTS `stores_id`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `stores_id` (
  `Id` smallint NOT NULL AUTO_INCREMENT,
  `Element_Name` varchar(100) DEFAULT NULL,
  `Element_For` varchar(150) DEFAULT NULL,
  `Cur_Val` int DEFAULT NULL,
  `Pref_Len` smallint DEFAULT NULL,
  `Year_Id` tinyint DEFAULT NULL,
  `Branch_Id` tinyint DEFAULT NULL,
  PRIMARY KEY (`Id`)
) ENGINE=InnoDB AUTO_INCREMENT=33 DEFAULT CHARSET=utf8mb3;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `stores_id`
--

LOCK TABLES `stores_id` WRITE;
/*!40000 ALTER TABLE `stores_id` DISABLE KEYS */;
INSERT INTO `stores_id` VALUES (19,'AGENT','Agenet No Generate',9,6,NULL,NULL),(20,'REC','For Receive Voucher Generate',70,NULL,1,1),(21,'PAY','For Payment Voucher Generate',74,NULL,1,1),(22,'JOU','For Journal Voucher Generate',21,NULL,1,1),(24,'PV','For Purchase Voucher',84,NULL,1,NULL),(25,'SR','For Sale Return',8,NULL,1,NULL),(26,'PR','For Purchase Return',24,NULL,1,NULL),(27,'AI','For Agent indent No',49,NULL,1,NULL),(28,'AR','For Agent Return',0,NULL,1,NULL),(29,'INV','For Sales Invoice Number Generate',81,NULL,1,NULL),(30,'AR','For Agent Return To Office',0,NULL,1,NULL),(31,'ID','Item Damage ',2,NULL,1,NULL),(32,'ACP','For Agent Customer Collection No',5,NULL,1,NULL);
/*!40000 ALTER TABLE `stores_id` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `trans_agent_indent`
--

DROP TABLE IF EXISTS `trans_agent_indent`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `trans_agent_indent` (
  `Indent_Id` int NOT NULL AUTO_INCREMENT,
  `Indent_Date` date DEFAULT NULL,
  `Indent_No` varchar(25) NOT NULL,
  `Agent_Id` smallint DEFAULT NULL,
  `Indent_Type_Id` tinyint NOT NULL DEFAULT '1',
  `Remarks` varchar(100) DEFAULT NULL,
  `Entry_DtTm` datetime DEFAULT NULL,
  `Is_Issued` bit(1) DEFAULT b'0',
  `Issued_DtTm` datetime DEFAULT NULL,
  PRIMARY KEY (`Indent_Id`)
) ENGINE=InnoDB AUTO_INCREMENT=78 DEFAULT CHARSET=utf8mb3;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `trans_agent_indent`
--

LOCK TABLES `trans_agent_indent` WRITE;
/*!40000 ALTER TABLE `trans_agent_indent` DISABLE KEYS */;
INSERT INTO `trans_agent_indent` VALUES (77,'2026-09-04','AI26 - 27/49',30,1,NULL,'2026-09-04 10:52:40',_binary '','2026-09-04 10:57:33');
/*!40000 ALTER TABLE `trans_agent_indent` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `trans_agent_indent_details`
--

DROP TABLE IF EXISTS `trans_agent_indent_details`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `trans_agent_indent_details` (
  `Indent_Sl` bigint NOT NULL AUTO_INCREMENT,
  `Indent_Id` int DEFAULT NULL,
  `Prod_Id` int DEFAULT NULL,
  `Quantity` smallint DEFAULT NULL,
  `Reject_Qty` decimal(10,2) NOT NULL DEFAULT '0.00',
  `Unit_Id` smallint DEFAULT NULL,
  PRIMARY KEY (`Indent_Sl`)
) ENGINE=InnoDB AUTO_INCREMENT=142 DEFAULT CHARSET=utf8mb3;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `trans_agent_indent_details`
--

LOCK TABLES `trans_agent_indent_details` WRITE;
/*!40000 ALTER TABLE `trans_agent_indent_details` DISABLE KEYS */;
INSERT INTO `trans_agent_indent_details` VALUES (138,77,97,10,5.00,24),(139,77,98,20,10.00,24),(140,77,99,30,15.00,27),(141,77,100,40,20.00,27);
/*!40000 ALTER TABLE `trans_agent_indent_details` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `trans_agent_stock`
--

DROP TABLE IF EXISTS `trans_agent_stock`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `trans_agent_stock` (
  `AgntStock_Id` bigint NOT NULL AUTO_INCREMENT,
  `TrnsStock_Id` bigint DEFAULT NULL,
  `Prod_Id` int DEFAULT NULL,
  `Agent_Id` smallint DEFAULT NULL,
  `Trans_Date` datetime DEFAULT NULL,
  `Stock_Status` tinyint DEFAULT NULL COMMENT '1: In Hand, 2: Sales, 3: Return By Customer, 4:  Return To  Counter, 5: Damages',
  `Remarks` varchar(150) DEFAULT NULL,
  PRIMARY KEY (`AgntStock_Id`)
) ENGINE=InnoDB AUTO_INCREMENT=142 DEFAULT CHARSET=utf8mb3;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `trans_agent_stock`
--

LOCK TABLES `trans_agent_stock` WRITE;
/*!40000 ALTER TABLE `trans_agent_stock` DISABLE KEYS */;
INSERT INTO `trans_agent_stock` VALUES (115,823,97,30,'2026-09-04 00:00:00',3,NULL),(116,824,98,30,'2026-09-04 00:00:00',3,NULL),(117,825,99,30,'2026-09-04 00:00:00',3,NULL),(118,826,100,30,'2026-09-04 00:00:00',3,NULL),(119,827,97,30,'2026-09-04 00:00:00',3,NULL),(120,828,98,30,'2026-09-04 00:00:00',3,NULL),(121,829,99,30,'2026-09-04 00:00:00',3,NULL),(122,830,100,30,'2026-09-04 00:00:00',3,NULL),(123,831,100,30,'2026-09-04 00:00:00',3,NULL),(124,832,98,30,'2026-09-04 00:00:00',3,NULL),(125,833,97,30,'2026-09-04 00:00:00',3,NULL),(126,834,97,30,'2026-09-04 00:00:00',3,NULL),(127,835,98,30,'2026-09-04 00:00:00',3,NULL),(128,836,97,30,'2026-09-04 00:00:00',3,NULL),(129,837,97,30,'2026-09-04 00:00:00',3,NULL),(130,838,98,30,'2026-09-04 00:00:00',3,NULL),(131,839,99,30,'2026-09-04 00:00:00',3,NULL),(132,840,99,30,'2026-09-04 00:00:00',3,NULL),(133,841,100,30,'2026-09-04 00:00:00',3,NULL),(134,842,100,30,'2026-09-04 00:00:00',3,NULL),(135,843,99,30,'2026-09-04 00:00:00',3,NULL),(136,844,98,30,'2026-09-04 00:00:00',3,NULL),(137,845,99,30,'2026-09-04 00:00:00',3,NULL),(138,846,99,30,'2026-09-05 00:00:00',3,NULL),(139,847,99,30,'2026-09-05 00:00:00',3,NULL),(140,848,100,30,'2026-09-05 00:00:00',3,NULL),(141,849,99,30,'2026-09-05 00:00:00',3,NULL);
/*!40000 ALTER TABLE `trans_agent_stock` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `trans_production`
--

DROP TABLE IF EXISTS `trans_production`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `trans_production` (
  `Id` int NOT NULL AUTO_INCREMENT,
  `Production_No` varchar(25) DEFAULT NULL,
  `Production_Date` date DEFAULT NULL,
  `Remarks` varchar(150) DEFAULT NULL,
  `Status_Cd` tinyint DEFAULT NULL,
  `Created_By` smallint DEFAULT NULL,
  `Created_On` datetime DEFAULT NULL,
  PRIMARY KEY (`Id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `trans_production`
--

LOCK TABLES `trans_production` WRITE;
/*!40000 ALTER TABLE `trans_production` DISABLE KEYS */;
/*!40000 ALTER TABLE `trans_production` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `trans_production_items`
--

DROP TABLE IF EXISTS `trans_production_items`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `trans_production_items` (
  `Sl` int NOT NULL AUTO_INCREMENT,
  `Production_Id` int DEFAULT NULL,
  `Item_Type` tinyint DEFAULT NULL COMMENT '1: Raw Meterial; 2: Finished Goods',
  `Product_Id` int DEFAULT NULL,
  `Quantity` decimal(8,2) DEFAULT NULL,
  `Qty_Unit` smallint DEFAULT NULL,
  PRIMARY KEY (`Sl`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `trans_production_items`
--

LOCK TABLES `trans_production_items` WRITE;
/*!40000 ALTER TABLE `trans_production_items` DISABLE KEYS */;
/*!40000 ALTER TABLE `trans_production_items` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `trans_trading`
--

DROP TABLE IF EXISTS `trans_trading`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `trans_trading` (
  `Trading_Id` bigint NOT NULL AUTO_INCREMENT,
  `Branch_Id` smallint DEFAULT NULL,
  `Tranding_Type` tinyint DEFAULT NULL COMMENT '1: Production Entries; 2: Purchase Entry; 3: Sales Entry; 4: Purchase Return Entry; 5: Sales Return Entry; 6: Damages Entry',
  `Invoice_No` varchar(25) DEFAULT NULL,
  `Ref_No` varchar(45) DEFAULT NULL,
  `Invoice_Date` date DEFAULT NULL,
  `Party_Id` int DEFAULT NULL,
  `Contact_No` varchar(25) DEFAULT NULL,
  `Invoice_Amt` decimal(12,2) DEFAULT NULL,
  `Disc_Percent` decimal(5,2) DEFAULT NULL,
  `Disc_Amount` decimal(8,2) DEFAULT NULL,
  `Freight_Amt` decimal(8,2) DEFAULT NULL,
  `Taxable_Amt` decimal(12,2) DEFAULT NULL,
  `GST_Amt` decimal(10,2) DEFAULT NULL,
  `Round_Off` decimal(5,2) DEFAULT NULL,
  `Net_Amt` decimal(12,2) DEFAULT NULL,
  `Remarks` varchar(150) DEFAULT NULL,
  `Status_Cd` tinyint DEFAULT NULL,
  `Agent_Id` smallint DEFAULT NULL,
  `Pay_Mode` tinyint DEFAULT NULL COMMENT '1: Cash; 2: UPI/Bank; 3: Credit',
  `Voucher_Id` bigint DEFAULT NULL,
  `Is_Settle` bit(1) DEFAULT b'0',
  `Settle_Date` date DEFAULT NULL,
  `Created_By` smallint DEFAULT NULL,
  `Created_On` datetime DEFAULT NULL,
  PRIMARY KEY (`Trading_Id`)
) ENGINE=InnoDB AUTO_INCREMENT=469 DEFAULT CHARSET=utf8mb3;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `trans_trading`
--

LOCK TABLES `trans_trading` WRITE;
/*!40000 ALTER TABLE `trans_trading` DISABLE KEYS */;
INSERT INTO `trans_trading` VALUES (455,1,2,'PV26 - 27/74','INV2000','2026-09-05',38,NULL,1000.00,0.00,0.00,0.00,1000.00,40.00,0.00,1040.00,NULL,1,NULL,NULL,161,_binary '\0',NULL,12,NULL),(456,1,2,'PV26 - 27/75',NULL,'2026-09-05',39,NULL,4000.00,0.00,0.00,0.00,4000.00,160.00,0.00,4160.00,NULL,1,NULL,NULL,162,_binary '\0',NULL,12,NULL),(457,1,2,'PV26 - 27/76',NULL,'2026-09-05',39,NULL,9000.00,0.00,0.00,0.00,9000.00,360.00,0.00,9360.00,NULL,1,NULL,NULL,163,_binary '\0',NULL,12,NULL),(458,1,4,'PR26 - 27/24',NULL,'2026-09-05',39,NULL,16500.00,0.00,0.00,0.00,16500.00,660.00,0.00,17160.00,NULL,1,NULL,NULL,164,_binary '\0',NULL,12,NULL),(459,1,2,'PV26 - 27/77',NULL,'2026-09-07',38,NULL,1452.00,0.00,0.00,0.00,1452.00,58.08,-0.08,1510.00,NULL,1,NULL,NULL,169,_binary '\0',NULL,12,NULL),(460,1,2,'PV26 - 27/78',NULL,'2026-09-07',38,NULL,75548.00,0.00,0.00,0.00,75548.00,3021.92,0.08,78570.00,NULL,1,NULL,NULL,170,_binary '\0',NULL,12,NULL),(461,1,2,'PV26 - 27/79',NULL,'2026-09-07',38,NULL,726.00,0.00,0.00,0.00,726.00,29.04,-0.04,755.00,NULL,1,NULL,NULL,171,_binary '\0',NULL,12,NULL),(462,1,2,'PV26 - 27/80',NULL,'2026-09-07',38,NULL,2904.00,0.00,0.00,0.00,2904.00,116.16,-0.16,3020.00,NULL,1,NULL,NULL,172,_binary '\0',NULL,12,NULL),(463,1,2,'PV26 - 27/81',NULL,'2026-09-07',39,NULL,1452.00,0.00,0.00,0.00,1452.00,58.08,-0.08,1510.00,NULL,1,NULL,NULL,173,_binary '\0',NULL,12,NULL),(464,1,2,'PV26 - 27/82',NULL,'2026-09-07',38,NULL,264.00,0.00,5.28,0.00,258.72,10.35,-0.07,269.00,NULL,1,NULL,NULL,174,_binary '\0',NULL,12,NULL),(465,1,2,'PV26 - 27/83',NULL,'2026-09-07',39,NULL,1452.00,0.00,0.00,0.00,1452.00,58.08,-0.08,1510.00,NULL,1,NULL,NULL,175,_binary '\0',NULL,12,NULL),(466,1,2,'PV26 - 27/84',NULL,'2026-09-07',38,NULL,2420.00,0.00,0.00,0.00,2420.00,96.80,0.20,2517.00,NULL,1,NULL,NULL,176,_binary '\0',NULL,12,NULL),(467,1,3,'INV26 - 27/80','INV26 - 27/80','2026-09-07',40,NULL,260.92,NULL,0.00,NULL,260.92,0.00,0.08,261.00,NULL,1,NULL,NULL,177,_binary '\0',NULL,12,NULL),(468,1,3,'INV26 - 27/81','INV26 - 27/81','2026-09-07',41,NULL,56.10,NULL,0.00,NULL,56.10,0.00,-0.10,56.00,NULL,1,NULL,NULL,178,_binary '\0',NULL,12,NULL);
/*!40000 ALTER TABLE `trans_trading` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `trans_trading_gst`
--

DROP TABLE IF EXISTS `trans_trading_gst`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `trans_trading_gst` (
  `Id` bigint NOT NULL AUTO_INCREMENT,
  `Trading_Id` bigint DEFAULT NULL,
  `HSN_No` varchar(10) DEFAULT NULL,
  `Taxable_Amt` decimal(12,2) DEFAULT NULL,
  `SGST_Prcnt` decimal(5,2) DEFAULT NULL,
  `SGST_Amt` decimal(10,2) DEFAULT NULL,
  `CGST_Prcnt` decimal(5,2) DEFAULT NULL,
  `CGST_Amt` decimal(10,2) DEFAULT NULL,
  `Tax_Amount` decimal(10,2) DEFAULT NULL,
  PRIMARY KEY (`Id`)
) ENGINE=InnoDB AUTO_INCREMENT=525 DEFAULT CHARSET=utf8mb3;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `trans_trading_gst`
--

LOCK TABLES `trans_trading_gst` WRITE;
/*!40000 ALTER TABLE `trans_trading_gst` DISABLE KEYS */;
INSERT INTO `trans_trading_gst` VALUES (511,455,'WB5678',1000.00,2.00,20.00,2.00,20.00,40.00),(512,456,'WB5678',4000.00,2.00,80.00,2.00,80.00,160.00),(513,457,'WB5678',9000.00,2.00,180.00,2.00,180.00,360.00),(514,458,'2',16500.00,2.00,330.00,2.00,330.00,660.00),(515,459,'WB5678',1452.00,2.00,29.04,2.00,29.04,58.08),(516,460,'WB5678',75548.00,2.00,1510.96,2.00,1510.96,3021.92),(517,461,'WB5678',726.00,2.00,14.52,2.00,14.52,29.04),(518,462,'WB5678',2904.00,2.00,58.08,2.00,58.08,116.16),(519,463,'WB5678',1452.00,2.00,29.04,2.00,29.04,58.08),(520,464,'WB5678',258.72,2.00,5.17,2.00,5.17,10.34),(521,465,'WB5678',1452.00,2.00,29.04,2.00,29.04,58.08),(522,466,'WB5678',2420.00,2.00,48.40,2.00,48.40,96.80),(523,467,'2',260.92,0.00,0.00,0.00,0.00,0.00),(524,468,'2',56.10,0.00,0.00,0.00,0.00,0.00);
/*!40000 ALTER TABLE `trans_trading_gst` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `trans_trading_products`
--

DROP TABLE IF EXISTS `trans_trading_products`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `trans_trading_products` (
  `Sl` bigint NOT NULL AUTO_INCREMENT,
  `Trading_Id` bigint DEFAULT NULL,
  `Prod_Id` int DEFAULT NULL,
  `Gst_Code` varchar(20) DEFAULT NULL,
  `Item_Qty` decimal(10,2) DEFAULT NULL,
  `Item_Rate` decimal(10,2) DEFAULT NULL,
  `Unit_Id` smallint DEFAULT NULL,
  `Item_Total` decimal(10,2) DEFAULT NULL,
  `MRP` decimal(10,2) DEFAULT NULL,
  `Disc_Prcnt` decimal(5,2) DEFAULT NULL,
  `Disc_Amt` decimal(8,2) DEFAULT NULL,
  `Taxable_Amt` decimal(10,2) DEFAULT NULL,
  `SGST_Prcnt` decimal(5,2) DEFAULT NULL,
  `CGST_Prcnt` decimal(5,2) DEFAULT NULL,
  `SGST_Amt` decimal(10,2) DEFAULT NULL,
  `CGST_Amt` decimal(10,2) DEFAULT NULL,
  `Net_Amt` decimal(10,2) DEFAULT NULL,
  PRIMARY KEY (`Sl`)
) ENGINE=InnoDB AUTO_INCREMENT=806 DEFAULT CHARSET=utf8mb3;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `trans_trading_products`
--

LOCK TABLES `trans_trading_products` WRITE;
/*!40000 ALTER TABLE `trans_trading_products` DISABLE KEYS */;
INSERT INTO `trans_trading_products` VALUES (790,455,109,'2',100.00,10.00,27,1000.00,11.00,0.00,0.00,1000.00,2.00,2.00,20.00,20.00,1040.00),(791,456,109,'2',200.00,20.00,27,4000.00,22.00,0.00,0.00,4000.00,2.00,2.00,80.00,80.00,4160.00),(792,457,109,'2',300.00,30.00,27,9000.00,33.00,0.00,0.00,9000.00,2.00,2.00,180.00,180.00,9360.00),(793,458,109,'2',500.00,33.00,27,16500.00,0.00,0.00,0.00,16500.00,2.00,2.00,330.00,330.00,17160.00),(794,459,98,'2',33.00,44.00,24,1452.00,48.40,0.00,0.00,1452.00,2.00,2.00,29.04,29.04,1510.08),(795,460,99,'2',22.00,3434.00,27,75548.00,3502.68,0.00,0.00,75548.00,2.00,2.00,1510.96,1510.96,78569.92),(796,461,101,'2',22.00,33.00,28,726.00,39.60,0.00,0.00,726.00,2.00,2.00,14.52,14.52,755.04),(797,462,101,'2',33.00,44.00,28,1452.00,52.80,0.00,0.00,1452.00,2.00,2.00,29.04,29.04,1510.08),(798,462,101,'2',33.00,44.00,28,1452.00,52.80,0.00,0.00,1452.00,2.00,2.00,29.04,29.04,1510.08),(800,463,104,'2',33.00,44.00,27,1452.00,48.40,0.00,0.00,1452.00,2.00,2.00,29.04,29.04,1510.08),(801,464,97,'2',12.00,22.00,24,264.00,23.72,2.00,5.28,258.72,2.00,2.00,5.17,5.17,269.07),(802,465,99,'2',33.00,44.00,27,1452.00,44.88,0.00,0.00,1452.00,2.00,2.00,29.04,29.04,1510.08),(803,466,99,'2',44.00,55.00,27,2420.00,56.10,0.00,0.00,2420.00,2.00,2.00,48.40,48.40,2516.80),(804,467,97,'2',11.00,23.72,24,260.92,23.72,0.00,0.00,260.92,0.00,0.00,0.00,0.00,260.92),(805,468,99,'2',1.00,56.10,27,56.10,56.10,0.00,0.00,56.10,0.00,0.00,0.00,0.00,56.10);
/*!40000 ALTER TABLE `trans_trading_products` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `trans_voucher_details`
--

DROP TABLE IF EXISTS `trans_voucher_details`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `trans_voucher_details` (
  `VouDtls_Id` int NOT NULL AUTO_INCREMENT,
  `Voucher_Id` bigint DEFAULT NULL,
  `Trans_Type` char(1) DEFAULT NULL,
  `GlHead_Id` smallint DEFAULT NULL,
  `SubLedger_Id` int DEFAULT NULL,
  `Vou_Amount` decimal(10,2) DEFAULT NULL,
  `Remkrs` varchar(25) DEFAULT NULL,
  `IsRecon` bit(1) DEFAULT NULL,
  PRIMARY KEY (`VouDtls_Id`)
) ENGINE=InnoDB AUTO_INCREMENT=692 DEFAULT CHARSET=utf8mb3;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `trans_voucher_details`
--

LOCK TABLES `trans_voucher_details` WRITE;
/*!40000 ALTER TABLE `trans_voucher_details` DISABLE KEYS */;
INSERT INTO `trans_voucher_details` VALUES (615,161,'D',91,NULL,1000.00,NULL,NULL),(616,161,'D',NULL,NULL,20.00,NULL,NULL),(617,161,'D',NULL,NULL,20.00,NULL,NULL),(618,161,'C',11,NULL,1040.00,NULL,NULL),(619,0,'D',91,NULL,1000.00,NULL,NULL),(620,0,'D',NULL,NULL,20.00,NULL,NULL),(621,0,'D',NULL,NULL,20.00,NULL,NULL),(622,0,'C',11,NULL,1040.00,NULL,NULL),(623,162,'D',91,NULL,4000.00,NULL,NULL),(624,162,'D',NULL,NULL,80.00,NULL,NULL),(625,162,'D',NULL,NULL,80.00,NULL,NULL),(626,162,'C',11,NULL,4160.00,NULL,NULL),(627,163,'D',91,NULL,9000.00,NULL,NULL),(628,163,'D',NULL,NULL,180.00,NULL,NULL),(629,163,'D',NULL,NULL,180.00,NULL,NULL),(630,163,'C',11,NULL,9360.00,NULL,NULL),(631,164,'D',11,NULL,17160.00,NULL,NULL),(632,164,'C',91,NULL,16500.00,NULL,NULL),(633,164,'C',NULL,NULL,330.00,NULL,NULL),(634,164,'C',NULL,NULL,330.00,NULL,NULL),(635,165,'D',11,NULL,0.00,NULL,NULL),(636,165,'C',2,NULL,100.00,NULL,NULL),(637,166,'D',12,NULL,200.00,NULL,NULL),(638,166,'C',2,NULL,100.00,NULL,NULL),(639,166,'C',1,NULL,100.00,NULL,NULL),(640,167,'D',14,NULL,0.00,NULL,NULL),(641,167,'C',2,NULL,100.00,NULL,NULL),(642,168,'D',14,NULL,540.00,NULL,NULL),(643,168,'C',2,NULL,100.00,NULL,NULL),(644,168,'C',1,NULL,440.00,NULL,NULL),(645,169,'D',80,NULL,1452.00,NULL,NULL),(646,169,'D',NULL,NULL,29.04,NULL,NULL),(647,169,'D',NULL,NULL,29.04,NULL,NULL),(648,169,'C',NULL,NULL,0.08,NULL,NULL),(649,169,'C',11,NULL,1510.00,NULL,NULL),(650,170,'D',93,NULL,75548.00,NULL,NULL),(651,170,'D',NULL,NULL,1510.96,NULL,NULL),(652,170,'D',NULL,NULL,1510.96,NULL,NULL),(653,170,'D',NULL,NULL,0.08,NULL,NULL),(654,170,'C',12,NULL,78570.00,NULL,NULL),(655,171,'D',93,NULL,726.00,NULL,NULL),(656,171,'D',NULL,NULL,14.52,NULL,NULL),(657,171,'D',NULL,NULL,14.52,NULL,NULL),(658,171,'C',NULL,NULL,0.04,NULL,NULL),(659,171,'C',11,NULL,755.00,NULL,NULL),(660,172,'D',93,NULL,2904.00,NULL,NULL),(661,172,'D',NULL,NULL,58.08,NULL,NULL),(662,172,'D',NULL,NULL,58.08,NULL,NULL),(663,172,'C',NULL,NULL,0.16,NULL,NULL),(664,172,'C',11,NULL,3020.00,NULL,NULL),(665,173,'D',80,NULL,1452.00,NULL,NULL),(666,173,'D',NULL,NULL,29.04,NULL,NULL),(667,173,'D',NULL,NULL,29.04,NULL,NULL),(668,173,'C',NULL,NULL,0.08,NULL,NULL),(669,173,'C',11,NULL,1510.00,NULL,NULL),(670,174,'D',80,NULL,264.00,NULL,NULL),(671,174,'D',NULL,NULL,5.17,NULL,NULL),(672,174,'D',NULL,NULL,5.17,NULL,NULL),(673,174,'C',NULL,NULL,0.07,NULL,NULL),(674,174,'C',NULL,NULL,5.28,NULL,NULL),(675,174,'C',11,NULL,269.00,NULL,NULL),(676,175,'D',93,NULL,1452.00,NULL,NULL),(677,175,'D',NULL,NULL,29.04,NULL,NULL),(678,175,'D',NULL,NULL,29.04,NULL,NULL),(679,175,'C',NULL,NULL,0.08,NULL,NULL),(680,175,'C',11,NULL,1510.00,NULL,NULL),(681,176,'D',93,NULL,2420.00,NULL,NULL),(682,176,'D',NULL,NULL,48.40,NULL,NULL),(683,176,'D',NULL,NULL,48.40,NULL,NULL),(684,176,'D',NULL,NULL,0.20,NULL,NULL),(685,176,'C',11,NULL,2517.00,NULL,NULL),(686,177,'D',11,NULL,260.92,NULL,NULL),(687,177,'C',80,NULL,260.92,NULL,NULL),(688,177,'D',NULL,NULL,0.08,NULL,NULL),(689,178,'D',11,NULL,56.10,NULL,NULL),(690,178,'C',93,NULL,56.10,NULL,NULL),(691,178,'C',NULL,NULL,0.10,NULL,NULL);
/*!40000 ALTER TABLE `trans_voucher_details` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `trans_voucher_master`
--

DROP TABLE IF EXISTS `trans_voucher_master`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `trans_voucher_master` (
  `Voucher_Id` bigint NOT NULL AUTO_INCREMENT,
  `Year_Id` int DEFAULT NULL,
  `Vou_Date` date DEFAULT NULL,
  `Vou_Type` tinyint DEFAULT NULL,
  `Vou_Mode` tinyint DEFAULT NULL,
  `Vou_Sl` int DEFAULT NULL,
  `Vou_No` varchar(50) DEFAULT NULL,
  `Ref_Vou_No` varchar(50) DEFAULT NULL,
  `Particulars` varchar(200) DEFAULT NULL,
  `Trans_Type` tinyint DEFAULT NULL COMMENT '1: Purhcase; 2: Counter Sales, 3: Agent Sales, 4: Agent Return, 5: Purchase Return, 6: Sales Return, \n7: Damage/Wastage, 8: Yearly Adjustment, 9: Cash Book Voucher',
  `Type_Id` bigint DEFAULT NULL,
  `Is_Adjustment` bit(1) DEFAULT NULL,
  `Status` tinyint DEFAULT NULL,
  `Created_By` smallint DEFAULT NULL,
  `Created_On` datetime DEFAULT CURRENT_TIMESTAMP,
  `Approved_By` smallint DEFAULT NULL,
  `Approved_On` datetime DEFAULT NULL,
  PRIMARY KEY (`Voucher_Id`)
) ENGINE=InnoDB AUTO_INCREMENT=179 DEFAULT CHARSET=utf8mb3;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `trans_voucher_master`
--

LOCK TABLES `trans_voucher_master` WRITE;
/*!40000 ALTER TABLE `trans_voucher_master` DISABLE KEYS */;
INSERT INTO `trans_voucher_master` VALUES (161,1,'2026-09-05',2,1,NULL,'PAY26 - 27/65','PV26 - 27/74','By Purchase INV2000',1,NULL,NULL,2,12,'2026-09-05 18:11:58',12,'2026-09-05 18:11:58'),(162,1,'2026-09-05',2,1,NULL,'PAY26 - 27/66','PV26 - 27/75',NULL,1,NULL,NULL,2,12,'2026-09-05 18:22:18',12,'2026-09-05 18:22:18'),(163,1,'2026-09-05',2,1,NULL,'PAY26 - 27/67','PV26 - 27/76',NULL,1,NULL,NULL,2,12,'2026-09-05 18:23:21',12,'2026-09-05 18:23:21'),(164,1,'2026-09-05',1,1,NULL,'REC26 - 27/67','PR26 - 27/24',NULL,5,458,NULL,2,12,'2026-09-05 18:40:31',12,'2026-09-05 18:40:31'),(165,1,'2026-09-06',1,1,NULL,'REC26 - 27/68','REC26 - 27/68','Member Admission - kumar sanu',9,42,NULL,2,12,'2026-09-06 14:20:32',12,'2026-09-06 14:20:32'),(166,1,'2026-09-07',4,2,NULL,'JOU26 - 27/18','JOU26 - 27/18','Member Admission - rishab',9,43,NULL,2,12,'2026-09-07 11:29:55',12,'2026-09-07 11:29:55'),(167,1,'2026-09-07',4,2,NULL,'JOU26 - 27/19','wre','Member Admission - anil',9,44,NULL,2,12,'2026-09-07 11:35:04',12,'2026-09-07 11:35:04'),(168,1,'2026-09-07',4,2,NULL,'JOU26 - 27/20','refvouch','Member Admission - rrr | bank remarks',9,45,NULL,2,12,'2026-09-07 11:48:09',12,'2026-09-07 11:48:09'),(169,1,'2026-09-07',2,1,NULL,'PAY26 - 27/68','PV26 - 27/77',NULL,1,NULL,NULL,2,12,'2026-09-07 12:01:18',12,'2026-09-07 12:01:18'),(170,1,'2026-09-07',4,2,NULL,'JOU26 - 27/21','PV26 - 27/78',NULL,1,NULL,NULL,2,12,'2026-09-07 12:02:07',12,'2026-09-07 12:02:07'),(171,1,'2026-09-07',2,1,NULL,'PAY26 - 27/69','PV26 - 27/79',NULL,1,NULL,NULL,2,12,'2026-09-07 12:02:22',12,'2026-09-07 12:02:22'),(172,1,'2026-09-07',2,1,NULL,'PAY26 - 27/70','PV26 - 27/80',NULL,1,NULL,NULL,2,12,'2026-09-07 12:07:47',12,'2026-09-07 12:07:47'),(173,1,'2026-09-07',2,1,NULL,'PAY26 - 27/71','PV26 - 27/81',NULL,1,NULL,NULL,2,12,'2026-09-07 12:08:00',12,'2026-09-07 12:08:00'),(174,1,'2026-09-07',2,1,NULL,'PAY26 - 27/72','PV26 - 27/82',NULL,1,NULL,NULL,2,12,'2026-09-07 12:21:07',12,'2026-09-07 12:21:07'),(175,1,'2026-09-07',2,1,NULL,'PAY26 - 27/73','PV26 - 27/83',NULL,1,NULL,NULL,2,12,'2026-09-07 12:21:22',12,'2026-09-07 12:21:22'),(176,1,'2026-09-07',2,1,NULL,'PAY26 - 27/74','PV26 - 27/84',NULL,1,NULL,NULL,2,12,'2026-09-07 12:22:21',12,'2026-09-07 12:22:21'),(177,1,'2026-09-07',1,1,NULL,'REC26 - 27/69','INV26 - 27/80','By Counter SaleINV26 - 27/80',1,NULL,NULL,2,12,'2026-09-07 12:56:00',12,'2026-09-07 12:56:00'),(178,1,'2026-09-07',1,1,NULL,'REC26 - 27/70','INV26 - 27/81','By Counter SaleINV26 - 27/81',1,NULL,NULL,2,12,'2026-09-07 12:56:56',12,'2026-09-07 12:56:56');
/*!40000 ALTER TABLE `trans_voucher_master` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `trn_counter_balance`
--

DROP TABLE IF EXISTS `trn_counter_balance`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `trn_counter_balance` (
  `id` int NOT NULL AUTO_INCREMENT,
  `counter_id` smallint NOT NULL,
  `balance` decimal(18,2) NOT NULL DEFAULT '0.00',
  `date` date NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8mb3;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `trn_counter_balance`
--

LOCK TABLES `trn_counter_balance` WRITE;
/*!40000 ALTER TABLE `trn_counter_balance` DISABLE KEYS */;
INSERT INTO `trn_counter_balance` VALUES (1,12,2000.00,'2026-04-01');
/*!40000 ALTER TABLE `trn_counter_balance` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `trn_party_trans`
--

DROP TABLE IF EXISTS `trn_party_trans`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `trn_party_trans` (
  `Id` bigint NOT NULL AUTO_INCREMENT,
  `Party_Id` int DEFAULT NULL,
  `Trans_Date` date DEFAULT NULL,
  `Trans_Type` char(1) DEFAULT NULL,
  `Amount` decimal(18,2) DEFAULT NULL,
  `Txn_Id` bigint DEFAULT NULL,
  `Trading_Id` bigint DEFAULT NULL,
  `Remarks` varchar(150) DEFAULT NULL,
  `Trans_Mode` tinyint DEFAULT NULL,
  `Created_By` int DEFAULT NULL,
  `Created_On` datetime DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`Id`)
) ENGINE=InnoDB AUTO_INCREMENT=65 DEFAULT CHARSET=utf8mb3;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `trn_party_trans`
--

LOCK TABLES `trn_party_trans` WRITE;
/*!40000 ALTER TABLE `trn_party_trans` DISABLE KEYS */;
INSERT INTO `trn_party_trans` VALUES (51,38,'2026-09-05','C',1040.00,0,455,'Purchase INV2000',1,12,'2026-09-05 18:13:28'),(52,39,'2026-09-05','C',4160.00,162,456,'Purchase PV26 - 27/75',1,12,'2026-09-05 18:22:18'),(53,39,'2026-09-05','C',9360.00,163,457,'Purchase PV26 - 27/76',1,12,'2026-09-05 18:23:21'),(54,39,'2026-09-05','D',17160.00,164,458,'Purchase Return PR26 - 27/24',1,12,'2026-09-05 18:40:31'),(55,38,'2026-09-07','C',1510.00,169,459,'Purchase PV26 - 27/77',1,12,'2026-09-07 12:01:18'),(56,38,'2026-09-07','C',78570.00,170,460,'Purchase PV26 - 27/78',2,12,'2026-09-07 12:02:07'),(57,38,'2026-09-07','C',755.00,171,461,'Purchase PV26 - 27/79',1,12,'2026-09-07 12:02:22'),(58,38,'2026-09-07','C',3020.00,172,462,'Purchase PV26 - 27/80',1,12,'2026-09-07 12:07:47'),(59,39,'2026-09-07','C',1510.00,173,463,'Purchase PV26 - 27/81',1,12,'2026-09-07 12:08:00'),(60,38,'2026-09-07','C',269.00,174,464,'Purchase PV26 - 27/82',1,12,'2026-09-07 12:21:07'),(61,39,'2026-09-07','C',1510.00,175,465,'Purchase PV26 - 27/83',1,12,'2026-09-07 12:21:22'),(62,38,'2026-09-07','C',2517.00,176,466,'Purchase PV26 - 27/84',1,12,'2026-09-07 12:22:21'),(63,40,'2026-09-07','D',261.00,177,467,'Counter Sale INV26 - 27/80',1,12,'2026-09-07 12:56:00'),(64,41,'2026-09-07','D',56.00,178,468,'Counter Sale INV26 - 27/81',1,12,'2026-09-07 12:56:56');
/*!40000 ALTER TABLE `trn_party_trans` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `trns_stockinout`
--

DROP TABLE IF EXISTS `trns_stockinout`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `trns_stockinout` (
  `Stock_Id` bigint NOT NULL AUTO_INCREMENT,
  `Branch_Id` smallint DEFAULT NULL,
  `Stock_Type` tinyint DEFAULT NULL COMMENT '1: Opening; 2: Purhcase; 3: Counter Sales, 4: Agent Issued, 5: Agent Sales, 6: Agent Return, 7: Purchase Return, 8: Sales Return, 9: Damage/Wastage',
  `Type_Id` bigint DEFAULT NULL,
  `Type_Nature` char(1) DEFAULT NULL,
  `InOut_Date` date DEFAULT NULL,
  `Prod_Id` int DEFAULT NULL,
  `Unit_Id` smallint DEFAULT NULL,
  `Quantity` smallint DEFAULT NULL,
  `Rate` decimal(12,2) DEFAULT NULL,
  `Total_Amount` decimal(18,2) DEFAULT NULL,
  `MRP` decimal(12,2) DEFAULT NULL,
  `Pack_Date` date DEFAULT NULL,
  `Barcode` bigint DEFAULT NULL,
  `Stock_At` tinyint DEFAULT NULL COMMENT '1: Godown; 2: Store, 3: Agent, 4: Return, 5: Discarded',
  `Expiry_Date` date DEFAULT NULL,
  PRIMARY KEY (`Stock_Id`)
) ENGINE=InnoDB AUTO_INCREMENT=868 DEFAULT CHARSET=utf8mb3;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `trns_stockinout`
--

LOCK TABLES `trns_stockinout` WRITE;
/*!40000 ALTER TABLE `trns_stockinout` DISABLE KEYS */;
INSERT INTO `trns_stockinout` VALUES (852,1,2,455,'P','2026-09-05',109,27,100,10.00,1000.00,11.00,NULL,19010126090652,1,NULL),(853,1,2,456,'P','2026-09-05',109,27,200,20.00,4000.00,22.00,NULL,19010126090753,1,NULL),(854,1,2,457,'P','2026-09-05',109,27,300,30.00,9000.00,33.00,NULL,19010126090754,1,NULL),(855,1,7,458,'R','2026-09-05',109,27,500,33.00,16500.00,0.00,NULL,NULL,4,NULL),(856,1,2,459,'P','2026-09-07',98,24,33,44.00,1452.00,48.40,NULL,19010126090756,1,NULL),(857,1,2,460,'P','2026-09-07',99,27,22,3434.00,75548.00,3502.68,'2026-09-01',19010126090757,1,'2026-12-30'),(858,1,2,461,'P','2026-09-07',101,28,22,33.00,726.00,39.60,NULL,19010126090758,1,NULL),(859,1,2,462,'P','2026-09-07',101,28,33,44.00,1452.00,52.80,NULL,19010126090759,1,NULL),(860,1,2,462,'P','2026-09-07',101,28,33,44.00,1452.00,52.80,NULL,19010126090760,1,NULL),(862,1,2,463,'P','2026-09-07',104,27,33,44.00,1452.00,48.40,NULL,19010126090762,1,NULL),(863,1,2,464,'P','2026-09-07',97,24,12,22.00,264.00,23.72,NULL,19010126090763,1,NULL),(864,1,2,465,'P','2026-09-07',99,27,33,44.00,1452.00,44.88,'2026-09-01',NULL,1,'2026-12-30'),(865,1,2,466,'P','2026-09-07',99,27,44,55.00,2420.00,56.10,'2026-09-01',NULL,1,'2026-12-30'),(866,1,3,467,'S','2026-09-07',97,24,11,23.72,260.92,NULL,NULL,NULL,NULL,NULL),(867,1,3,468,'S','2026-09-07',99,27,1,56.10,56.10,NULL,NULL,NULL,NULL,NULL);
/*!40000 ALTER TABLE `trns_stockinout` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Dumping routines for database 'smartinventory_bccsl'
--
/*!50003 DROP FUNCTION IF EXISTS `UDF_CAL_AGENT_STOCK` */;
/*!50003 SET @saved_cs_client      = @@character_set_client */ ;
/*!50003 SET @saved_cs_results     = @@character_set_results */ ;
/*!50003 SET @saved_col_connection = @@collation_connection */ ;
/*!50003 SET character_set_client  = utf8mb4 */ ;
/*!50003 SET character_set_results = utf8mb4 */ ;
/*!50003 SET collation_connection  = utf8mb4_0900_ai_ci */ ;
/*!50003 SET @saved_sql_mode       = @@sql_mode */ ;
/*!50003 SET sql_mode              = 'ONLY_FULL_GROUP_BY,STRICT_TRANS_TABLES,NO_ZERO_IN_DATE,NO_ZERO_DATE,ERROR_FOR_DIVISION_BY_ZERO,NO_ENGINE_SUBSTITUTION' */ ;
DELIMITER ;;
CREATE DEFINER=`priority`@`%` FUNCTION `UDF_CAL_AGENT_STOCK`(
	pAgent_Id		Int,
	pProd_Id		Int,
    pDate			Date
) RETURNS smallint
    DETERMINISTIC
BEGIN



Declare	pPurchase			Smallint;
Declare	pPur_Ret			Smallint;
Declare	pSale				Smallint;
Declare	pSale_Ret			Smallint;
Declare	pFinal				Smallint;

Set pPurchase = Ifnull((Select Sum(Quantity) From trns_stockinout Where Stock_Type=4 And Stock_Id In (Select TrnsStock_Id From trans_agent_stock Where Agent_Id=pAgent_Id And Prod_Id=pProd_Id And Trans_Date<=pDate)),0);
Set pPur_Ret = Ifnull((Select Sum(Quantity) From trns_stockinout Where Stock_Type=6 And Stock_Id In (Select TrnsStock_Id From trans_agent_stock Where Agent_Id=pAgent_Id And Prod_Id=pProd_Id And Trans_Date<=pDate )),0);
Set pSale = Ifnull((Select Sum(Quantity) From trns_stockinout Where Stock_Type=5 And Stock_Id In (Select TrnsStock_Id From trans_agent_stock Where Agent_Id=pAgent_Id And Prod_Id=pProd_Id And Trans_Date<=pDate )),0);
Set pSale_Ret = Ifnull((Select Sum(Quantity) From trns_stockinout Where Stock_Type=8 And Stock_Id In (Select TrnsStock_Id From trans_agent_stock Where Agent_Id=pAgent_Id And Prod_Id=pProd_Id And Trans_Date<=pDate)),0);


Set pFinal = (pPurchase-pPur_Ret-pSale+pSale_Ret);

Return pFinal;
END ;;
DELIMITER ;
/*!50003 SET sql_mode              = @saved_sql_mode */ ;
/*!50003 SET character_set_client  = @saved_cs_client */ ;
/*!50003 SET character_set_results = @saved_cs_results */ ;
/*!50003 SET collation_connection  = @saved_col_connection */ ;
/*!50003 DROP FUNCTION IF EXISTS `UDF_CAL_AVAIL_STOCK` */;
/*!50003 SET @saved_cs_client      = @@character_set_client */ ;
/*!50003 SET @saved_cs_results     = @@character_set_results */ ;
/*!50003 SET @saved_col_connection = @@collation_connection */ ;
/*!50003 SET character_set_client  = utf8mb4 */ ;
/*!50003 SET character_set_results = utf8mb4 */ ;
/*!50003 SET collation_connection  = utf8mb4_0900_ai_ci */ ;
/*!50003 SET @saved_sql_mode       = @@sql_mode */ ;
/*!50003 SET sql_mode              = 'ONLY_FULL_GROUP_BY,STRICT_TRANS_TABLES,NO_ZERO_IN_DATE,NO_ZERO_DATE,ERROR_FOR_DIVISION_BY_ZERO,NO_ENGINE_SUBSTITUTION' */ ;
DELIMITER ;;
CREATE DEFINER=`priority`@`%` FUNCTION `UDF_CAL_AVAIL_STOCK`(
	pProd_Id		Int,
    pDate			Date
) RETURNS smallint
    DETERMINISTIC
BEGIN


Declare	pOpening			Smallint;
Declare	pPurchase			Smallint;
Declare	pPur_Ret			Smallint;
Declare	pSale				Smallint;
Declare	pSale_Ret			Smallint;
Declare	pDamage				Smallint;
Declare	pFinal				Smallint;

Set pOpening = Ifnull((Select Sum(Quantity) From trns_stockinout Where Prod_Id=pProd_Id And InOut_Date<=pDate And Stock_Type=1),0);
Set pPurchase = Ifnull((Select Sum(Quantity) From trns_stockinout Where Prod_Id=pProd_Id And InOut_Date<=pDate And Stock_Type=2),0);
Set pPur_Ret = Ifnull((Select Sum(Quantity) From trns_stockinout Where Prod_Id=pProd_Id And InOut_Date<=pDate And Stock_Type=7),0);
Set pSale = Ifnull((Select Sum(Quantity) From trns_stockinout Where Prod_Id=pProd_Id And InOut_Date<=pDate And Stock_Type In(3,4)),0);
Set pSale_Ret = Ifnull((Select Sum(Quantity) From trns_stockinout Where Prod_Id=pProd_Id And InOut_Date<=pDate And Stock_Type In(6,8)),0);
Set pDamage = Ifnull((Select Sum(Quantity) From trns_stockinout Where Prod_Id=pProd_Id And InOut_Date<=pDate And Stock_Type=9),0);

Set pFinal = (pOpening+pPurchase-pPur_Ret-pSale+pSale_Ret-pDamage);

Return pFinal;
END ;;
DELIMITER ;
/*!50003 SET sql_mode              = @saved_sql_mode */ ;
/*!50003 SET character_set_client  = @saved_cs_client */ ;
/*!50003 SET character_set_results = @saved_cs_results */ ;
/*!50003 SET collation_connection  = @saved_col_connection */ ;
/*!50003 DROP FUNCTION IF EXISTS `UDF_CAL_PARTY_CLOSING` */;
/*!50003 SET @saved_cs_client      = @@character_set_client */ ;
/*!50003 SET @saved_cs_results     = @@character_set_results */ ;
/*!50003 SET @saved_col_connection = @@collation_connection */ ;
/*!50003 SET character_set_client  = utf8mb4 */ ;
/*!50003 SET character_set_results = utf8mb4 */ ;
/*!50003 SET collation_connection  = utf8mb4_unicode_ci */ ;
/*!50003 SET @saved_sql_mode       = @@sql_mode */ ;
/*!50003 SET sql_mode              = 'ONLY_FULL_GROUP_BY,STRICT_TRANS_TABLES,NO_ZERO_IN_DATE,NO_ZERO_DATE,ERROR_FOR_DIVISION_BY_ZERO,NO_ENGINE_SUBSTITUTION' */ ;
DELIMITER ;;
CREATE DEFINER=`priority`@`%` FUNCTION `UDF_CAL_PARTY_CLOSING`(
    pParty_Id       INT,
    pAsOn_Date      DATE
) RETURNS decimal(18,2)
    DETERMINISTIC
BEGIN
    DECLARE pOpening    DECIMAL(18,2);
    DECLARE pCredit     DECIMAL(18,2);
    DECLARE pDebit      DECIMAL(18,2);
    DECLARE pType       INT;

    SET pOpening = IFNULL((SELECT Opening_Bal FROM mst_party WHERE Party_Id = pParty_Id), 0);
    SET pType = IFNULL((SELECT Party_Type FROM mst_party WHERE Party_Id = pParty_Id), 1);
    SET pCredit = IFNULL((
        SELECT SUM(Amount) FROM trn_party_trans
        WHERE Party_Id = pParty_Id
          AND Trans_Type = 'C'
          AND Trans_Date <= pAsOn_Date
          AND (IFNULL(Trading_Id, 0) = 0 OR Trans_Mode = 3)
    ), 0);
    SET pDebit = IFNULL((
        SELECT SUM(Amount) FROM trn_party_trans
        WHERE Party_Id = pParty_Id
          AND Trans_Type = 'D'
          AND Trans_Date <= pAsOn_Date
          AND (IFNULL(Trading_Id, 0) = 0 OR Trans_Mode = 3)
    ), 0);

    IF pType = 2 THEN
        RETURN pOpening + pDebit - pCredit;
    END IF;

    RETURN pOpening + pCredit - pDebit;
END ;;
DELIMITER ;
/*!50003 SET sql_mode              = @saved_sql_mode */ ;
/*!50003 SET character_set_client  = @saved_cs_client */ ;
/*!50003 SET character_set_results = @saved_cs_results */ ;
/*!50003 SET collation_connection  = @saved_col_connection */ ;
/*!50003 DROP FUNCTION IF EXISTS `UDF_GEN_AGENT_NO` */;
/*!50003 SET @saved_cs_client      = @@character_set_client */ ;
/*!50003 SET @saved_cs_results     = @@character_set_results */ ;
/*!50003 SET @saved_col_connection = @@collation_connection */ ;
/*!50003 SET character_set_client  = utf8mb4 */ ;
/*!50003 SET character_set_results = utf8mb4 */ ;
/*!50003 SET collation_connection  = utf8mb4_0900_ai_ci */ ;
/*!50003 SET @saved_sql_mode       = @@sql_mode */ ;
/*!50003 SET sql_mode              = 'ONLY_FULL_GROUP_BY,STRICT_TRANS_TABLES,NO_ZERO_IN_DATE,NO_ZERO_DATE,ERROR_FOR_DIVISION_BY_ZERO,NO_ENGINE_SUBSTITUTION' */ ;
DELIMITER ;;
CREATE DEFINER=`priority`@`%` FUNCTION `UDF_GEN_AGENT_NO`(
    pElement_Type VARCHAR(100)
) RETURNS varchar(100) CHARSET utf8mb3
    DETERMINISTIC
BEGIN
    DECLARE pCurr_Val INT;
    DECLARE pPref_Len INT;
set sql_safe_updates = 0;
    -- Get next value
    SELECT Cur_Val + 1, Pref_Len
    INTO pCurr_Val, pPref_Len
    FROM stores_id
    WHERE Element_Name = pElement_Type;

    -- Update current value
    UPDATE stores_id
    SET Cur_Val = pCurr_Val
    WHERE Element_Name = pElement_Type;

    -- Return with leading zeros
   RETURN CONCAT(
    '1',
    LPAD(pCurr_Val, pPref_Len - 1, '0')
);

END ;;
DELIMITER ;
/*!50003 SET sql_mode              = @saved_sql_mode */ ;
/*!50003 SET character_set_client  = @saved_cs_client */ ;
/*!50003 SET character_set_results = @saved_cs_results */ ;
/*!50003 SET collation_connection  = @saved_col_connection */ ;
/*!50003 DROP FUNCTION IF EXISTS `UDF_GEN_BARCODE` */;
/*!50003 SET @saved_cs_client      = @@character_set_client */ ;
/*!50003 SET @saved_cs_results     = @@character_set_results */ ;
/*!50003 SET @saved_col_connection = @@collation_connection */ ;
/*!50003 SET character_set_client  = utf8mb4 */ ;
/*!50003 SET character_set_results = utf8mb4 */ ;
/*!50003 SET collation_connection  = utf8mb4_unicode_ci */ ;
/*!50003 SET @saved_sql_mode       = @@sql_mode */ ;
/*!50003 SET sql_mode              = 'ONLY_FULL_GROUP_BY,STRICT_TRANS_TABLES,NO_ZERO_IN_DATE,NO_ZERO_DATE,ERROR_FOR_DIVISION_BY_ZERO,NO_ENGINE_SUBSTITUTION' */ ;
DELIMITER ;;
CREATE DEFINER=`priority`@`%` FUNCTION `UDF_GEN_BARCODE`(
    pOrg_Id      INT,
    pBr_Id       VARCHAR(6),
    pStock_Date  DATE,
    pStock_Id    BIGINT
) RETURNS varchar(30) CHARSET utf8mb3
    DETERMINISTIC
BEGIN
    DECLARE pNew_Code VARCHAR(30);
    DECLARE pOrg_Code VARCHAR(4);

    SET pOrg_Code = (
        SELECT Org_Code
        FROM smartinventory_core.mst_organisation
        WHERE Org_Id = pOrg_Id
        LIMIT 1
    );

    SET pNew_Code = CAST(CONCAT(
        pOrg_Code,
        LPAD(pBr_Id, 2, '0'),
        DATE_FORMAT(pStock_Date, '%y%m%d'),
        LPAD(pStock_Id % 100, 2, '0')
    ) AS UNSIGNED);

    RETURN pNew_Code;
END ;;
DELIMITER ;
/*!50003 SET sql_mode              = @saved_sql_mode */ ;
/*!50003 SET character_set_client  = @saved_cs_client */ ;
/*!50003 SET character_set_results = @saved_cs_results */ ;
/*!50003 SET collation_connection  = @saved_col_connection */ ;
/*!50003 DROP FUNCTION IF EXISTS `UDF_GEN_SYS_NO` */;
/*!50003 SET @saved_cs_client      = @@character_set_client */ ;
/*!50003 SET @saved_cs_results     = @@character_set_results */ ;
/*!50003 SET @saved_col_connection = @@collation_connection */ ;
/*!50003 SET character_set_client  = utf8mb4 */ ;
/*!50003 SET character_set_results = utf8mb4 */ ;
/*!50003 SET collation_connection  = utf8mb4_0900_ai_ci */ ;
/*!50003 SET @saved_sql_mode       = @@sql_mode */ ;
/*!50003 SET sql_mode              = 'ONLY_FULL_GROUP_BY,STRICT_TRANS_TABLES,NO_ZERO_IN_DATE,NO_ZERO_DATE,ERROR_FOR_DIVISION_BY_ZERO,NO_ENGINE_SUBSTITUTION' */ ;
DELIMITER ;;
CREATE DEFINER=`priority`@`%` FUNCTION `UDF_GEN_SYS_NO`(
	pElement_Type		Varchar(10),
    pFin_Id				Int,
    pBranch_Id			Int
) RETURNS varchar(100) CHARSET utf8mb3
    DETERMINISTIC
BEGIN


Declare	pCurrent_Val		Int;
Declare	pYear				Varchar(50);
Declare	pFinal				Varchar(100);

Set pYear = (Select Concat(DATE_FORMAT(Year_Start,'%y'),' - ',DATE_FORMAT(Year_End,'%y')) From mst_accountingyear Where Year_Id=pFin_Id);
Set pCurrent_Val = (Select (Cur_Val+1) From stores_id Where Element_Name=pElement_Type And Year_Id=pFin_Id AND (
        (Branch_Id IS NULL)
        OR (Branch_Id = pBranch_Id)
      ));
set sql_safe_updates = 0;
Update stores_id Set Cur_Val=pCurrent_Val Where Element_Name=pElement_Type And Year_Id=pFin_Id AND (
        (Branch_Id IS NULL)
        OR (Branch_Id = pBranch_Id)
      );

Set pFinal = Concat(pElement_Type,pYear,'/',pCurrent_Val);

Return pFinal;
END ;;
DELIMITER ;
/*!50003 SET sql_mode              = @saved_sql_mode */ ;
/*!50003 SET character_set_client  = @saved_cs_client */ ;
/*!50003 SET character_set_results = @saved_cs_results */ ;
/*!50003 SET collation_connection  = @saved_col_connection */ ;
/*!50003 DROP FUNCTION IF EXISTS `UDF_GET_CAT_SUB_NAME` */;
/*!50003 SET @saved_cs_client      = @@character_set_client */ ;
/*!50003 SET @saved_cs_results     = @@character_set_results */ ;
/*!50003 SET @saved_col_connection = @@collation_connection */ ;
/*!50003 SET character_set_client  = utf8mb4 */ ;
/*!50003 SET character_set_results = utf8mb4 */ ;
/*!50003 SET collation_connection  = utf8mb4_0900_ai_ci */ ;
/*!50003 SET @saved_sql_mode       = @@sql_mode */ ;
/*!50003 SET sql_mode              = 'ONLY_FULL_GROUP_BY,STRICT_TRANS_TABLES,NO_ZERO_IN_DATE,NO_ZERO_DATE,ERROR_FOR_DIVISION_BY_ZERO,NO_ENGINE_SUBSTITUTION' */ ;
DELIMITER ;;
CREATE DEFINER=`priority`@`%` FUNCTION `UDF_GET_CAT_SUB_NAME`(
	pOption_Id			Smallint,
    pMode				Smallint -- 1 For Catagory 2 For Sub Catagory
) RETURNS varchar(100) CHARSET utf8mb3
    DETERMINISTIC
BEGIN
	
    Declare	pFinal			Varchar(100);
    
    If(pMode=1) Then
		Select Prd_CateNm Into pFinal From mst_prod_category Where Prd_CateId=pOption_Id;
    End If;
    
	If(pMode=2) Then
		Select Prd_SubCateNm Into pFinal From mst_prod_subcategory Where Prd_SubCateId=pOption_Id;
    End If;
	
    Return pFinal;
    
END ;;
DELIMITER ;
/*!50003 SET sql_mode              = @saved_sql_mode */ ;
/*!50003 SET character_set_client  = @saved_cs_client */ ;
/*!50003 SET character_set_results = @saved_cs_results */ ;
/*!50003 SET collation_connection  = @saved_col_connection */ ;
/*!50003 DROP FUNCTION IF EXISTS `UDF_GET_GST_CODE` */;
/*!50003 SET @saved_cs_client      = @@character_set_client */ ;
/*!50003 SET @saved_cs_results     = @@character_set_results */ ;
/*!50003 SET @saved_col_connection = @@collation_connection */ ;
/*!50003 SET character_set_client  = utf8mb4 */ ;
/*!50003 SET character_set_results = utf8mb4 */ ;
/*!50003 SET collation_connection  = utf8mb4_0900_ai_ci */ ;
/*!50003 SET @saved_sql_mode       = @@sql_mode */ ;
/*!50003 SET sql_mode              = 'ONLY_FULL_GROUP_BY,STRICT_TRANS_TABLES,NO_ZERO_IN_DATE,NO_ZERO_DATE,ERROR_FOR_DIVISION_BY_ZERO,NO_ENGINE_SUBSTITUTION' */ ;
DELIMITER ;;
CREATE DEFINER=`priority`@`%` FUNCTION `UDF_GET_GST_CODE`(
	pGst_Id		Int
) RETURNS varchar(20) CHARSET utf8mb3
    DETERMINISTIC
BEGIN
	
	Return (Select Gst_Code From mst_gst_codes Where Id=pGst_Id);
    
END ;;
DELIMITER ;
/*!50003 SET sql_mode              = @saved_sql_mode */ ;
/*!50003 SET character_set_client  = @saved_cs_client */ ;
/*!50003 SET character_set_results = @saved_cs_results */ ;
/*!50003 SET collation_connection  = @saved_col_connection */ ;
/*!50003 DROP FUNCTION IF EXISTS `UDF_GET_ITEM_GST_DETAILS` */;
/*!50003 SET @saved_cs_client      = @@character_set_client */ ;
/*!50003 SET @saved_cs_results     = @@character_set_results */ ;
/*!50003 SET @saved_col_connection = @@collation_connection */ ;
/*!50003 SET character_set_client  = utf8mb4 */ ;
/*!50003 SET character_set_results = utf8mb4 */ ;
/*!50003 SET collation_connection  = utf8mb4_0900_ai_ci */ ;
/*!50003 SET @saved_sql_mode       = @@sql_mode */ ;
/*!50003 SET sql_mode              = 'ONLY_FULL_GROUP_BY,STRICT_TRANS_TABLES,NO_ZERO_IN_DATE,NO_ZERO_DATE,ERROR_FOR_DIVISION_BY_ZERO,NO_ENGINE_SUBSTITUTION' */ ;
DELIMITER ;;
CREATE DEFINER=`priority`@`%` FUNCTION `UDF_GET_ITEM_GST_DETAILS`(pGst_Id INT) RETURNS json
    DETERMINISTIC
BEGIN
    DECLARE pData JSON;
    SELECT JSON_OBJECT(
        'Gst_Code', Gst_Code,
        'SGST',     Sgst,
        'CGST',     Cgst,
        'IGST',     Igst
    ) INTO pData
    FROM mst_gst_codes
    WHERE Id = pGst_Id;
    RETURN pData;
END ;;
DELIMITER ;
/*!50003 SET sql_mode              = @saved_sql_mode */ ;
/*!50003 SET character_set_client  = @saved_cs_client */ ;
/*!50003 SET character_set_results = @saved_cs_results */ ;
/*!50003 SET collation_connection  = @saved_col_connection */ ;
/*!50003 DROP FUNCTION IF EXISTS `UDF_GET_OPTION_NAME` */;
/*!50003 SET @saved_cs_client      = @@character_set_client */ ;
/*!50003 SET @saved_cs_results     = @@character_set_results */ ;
/*!50003 SET @saved_col_connection = @@collation_connection */ ;
/*!50003 SET character_set_client  = utf8mb4 */ ;
/*!50003 SET character_set_results = utf8mb4 */ ;
/*!50003 SET collation_connection  = utf8mb4_0900_ai_ci */ ;
/*!50003 SET @saved_sql_mode       = @@sql_mode */ ;
/*!50003 SET sql_mode              = 'ONLY_FULL_GROUP_BY,STRICT_TRANS_TABLES,NO_ZERO_IN_DATE,NO_ZERO_DATE,ERROR_FOR_DIVISION_BY_ZERO,NO_ENGINE_SUBSTITUTION' */ ;
DELIMITER ;;
CREATE DEFINER=`priority`@`%` FUNCTION `UDF_GET_OPTION_NAME`(
	pGrp_Id			Smallint,
    pOption_Value	Smallint 
) RETURNS varchar(100) CHARSET utf8mb3
    DETERMINISTIC
BEGIN
	
	Return (Select Option_Name From smartinventory_core.mst_options Where Option_Id=pGrp_Id And Value_Id=pOption_Value);
    
END ;;
DELIMITER ;
/*!50003 SET sql_mode              = @saved_sql_mode */ ;
/*!50003 SET character_set_client  = @saved_cs_client */ ;
/*!50003 SET character_set_results = @saved_cs_results */ ;
/*!50003 SET collation_connection  = @saved_col_connection */ ;
/*!50003 DROP FUNCTION IF EXISTS `UDF_GET_PARTY_NAME` */;
/*!50003 SET @saved_cs_client      = @@character_set_client */ ;
/*!50003 SET @saved_cs_results     = @@character_set_results */ ;
/*!50003 SET @saved_col_connection = @@collation_connection */ ;
/*!50003 SET character_set_client  = utf8mb4 */ ;
/*!50003 SET character_set_results = utf8mb4 */ ;
/*!50003 SET collation_connection  = utf8mb4_0900_ai_ci */ ;
/*!50003 SET @saved_sql_mode       = @@sql_mode */ ;
/*!50003 SET sql_mode              = 'ONLY_FULL_GROUP_BY,STRICT_TRANS_TABLES,NO_ZERO_IN_DATE,NO_ZERO_DATE,ERROR_FOR_DIVISION_BY_ZERO,NO_ENGINE_SUBSTITUTION' */ ;
DELIMITER ;;
CREATE DEFINER=`priority`@`%` FUNCTION `UDF_GET_PARTY_NAME`(
	pParty_Id		Int
) RETURNS varchar(100) CHARSET utf8mb3
    DETERMINISTIC
BEGIN
	
	Return (Select Party_Name From mst_party Where Party_Id=pParty_Id);
    
END ;;
DELIMITER ;
/*!50003 SET sql_mode              = @saved_sql_mode */ ;
/*!50003 SET character_set_client  = @saved_cs_client */ ;
/*!50003 SET character_set_results = @saved_cs_results */ ;
/*!50003 SET collation_connection  = @saved_col_connection */ ;
/*!50003 DROP FUNCTION IF EXISTS `UDF_GET_UNIT_NAME` */;
/*!50003 SET @saved_cs_client      = @@character_set_client */ ;
/*!50003 SET @saved_cs_results     = @@character_set_results */ ;
/*!50003 SET @saved_col_connection = @@collation_connection */ ;
/*!50003 SET character_set_client  = utf8mb4 */ ;
/*!50003 SET character_set_results = utf8mb4 */ ;
/*!50003 SET collation_connection  = utf8mb4_0900_ai_ci */ ;
/*!50003 SET @saved_sql_mode       = @@sql_mode */ ;
/*!50003 SET sql_mode              = 'ONLY_FULL_GROUP_BY,STRICT_TRANS_TABLES,NO_ZERO_IN_DATE,NO_ZERO_DATE,ERROR_FOR_DIVISION_BY_ZERO,NO_ENGINE_SUBSTITUTION' */ ;
DELIMITER ;;
CREATE DEFINER=`priority`@`%` FUNCTION `UDF_GET_UNIT_NAME`(pUnit_Id INT) RETURNS varchar(100) CHARSET utf8mb3
    DETERMINISTIC
BEGIN
    RETURN (SELECT Unit_Name FROM mst_unit WHERE Unit_Id = pUnit_Id);
END ;;
DELIMITER ;
/*!50003 SET sql_mode              = @saved_sql_mode */ ;
/*!50003 SET character_set_client  = @saved_cs_client */ ;
/*!50003 SET character_set_results = @saved_cs_results */ ;
/*!50003 SET collation_connection  = @saved_col_connection */ ;
/*!50003 DROP PROCEDURE IF EXISTS `USP_ADD_EDIT_ACCOUNTING_YEAR` */;
/*!50003 SET @saved_cs_client      = @@character_set_client */ ;
/*!50003 SET @saved_cs_results     = @@character_set_results */ ;
/*!50003 SET @saved_col_connection = @@collation_connection */ ;
/*!50003 SET character_set_client  = utf8mb4 */ ;
/*!50003 SET character_set_results = utf8mb4 */ ;
/*!50003 SET collation_connection  = utf8mb4_0900_ai_ci */ ;
/*!50003 SET @saved_sql_mode       = @@sql_mode */ ;
/*!50003 SET sql_mode              = 'ONLY_FULL_GROUP_BY,STRICT_TRANS_TABLES,NO_ZERO_IN_DATE,NO_ZERO_DATE,ERROR_FOR_DIVISION_BY_ZERO,NO_ENGINE_SUBSTITUTION' */ ;
DELIMITER ;;
CREATE DEFINER=`priority`@`%` PROCEDURE `USP_ADD_EDIT_ACCOUNTING_YEAR`(
    IN p_YearDesc   VARCHAR(20),
    IN p_YearStart  DATE,
    IN p_YearEnd    DATE,
    IN p_UserId     INT
)
BEGIN
    DECLARE pExists_Count   INT;
    DECLARE pError_No       INT DEFAULT 0;
    DECLARE pError_Message  VARCHAR(100) DEFAULT '';

    -- Validate April 1 to March 31
    IF NOT (MONTH(p_YearStart) = 4 AND DAY(p_YearStart) = 1 AND MONTH(p_YearEnd) = 3 AND DAY(p_YearEnd) = 31) THEN
        SET pError_No = -1;
        SET pError_Message = 'Start date must be April 1st and End date must be March 31st';
    END IF;

    -- Check duplicate year desc
    IF pError_No = 0 THEN
        SET pExists_Count = (SELECT COUNT(*) FROM mst_accountingyear WHERE Year_Desc = p_YearDesc);
        IF pExists_Count > 0 THEN
            SET pError_No = -1;
            SET pError_Message = 'Accounting year already exists';
        END IF;
    END IF;

    IF pError_No = 0 THEN
        -- Deactivate all existing years
        UPDATE mst_accountingyear SET Is_Active = 0 WHERE Is_Active = 1;
        -- Insert new active year
        INSERT INTO mst_accountingyear(Year_Desc, Year_Start, Year_End, Is_Active, Created_By, Created_On)
        VALUES(p_YearDesc, p_YearStart, p_YearEnd, 1, p_UserId, NOW());
    END IF;

    SELECT pError_No AS Error_No, pError_Message AS Message;
END ;;
DELIMITER ;
/*!50003 SET sql_mode              = @saved_sql_mode */ ;
/*!50003 SET character_set_client  = @saved_cs_client */ ;
/*!50003 SET character_set_results = @saved_cs_results */ ;
/*!50003 SET collation_connection  = @saved_col_connection */ ;
/*!50003 DROP PROCEDURE IF EXISTS `USP_ADD_EDIT_ADDRESS_MASTER` */;
/*!50003 SET @saved_cs_client      = @@character_set_client */ ;
/*!50003 SET @saved_cs_results     = @@character_set_results */ ;
/*!50003 SET @saved_col_connection = @@collation_connection */ ;
/*!50003 SET character_set_client  = utf8mb4 */ ;
/*!50003 SET character_set_results = utf8mb4 */ ;
/*!50003 SET collation_connection  = utf8mb4_0900_ai_ci */ ;
/*!50003 SET @saved_sql_mode       = @@sql_mode */ ;
/*!50003 SET sql_mode              = 'ONLY_FULL_GROUP_BY,STRICT_TRANS_TABLES,NO_ZERO_IN_DATE,NO_ZERO_DATE,ERROR_FOR_DIVISION_BY_ZERO,NO_ENGINE_SUBSTITUTION' */ ;
DELIMITER ;;
CREATE DEFINER=`priority`@`%` PROCEDURE `USP_ADD_EDIT_ADDRESS_MASTER`(
    pType    VARCHAR(10),
    pId      INT,
    pName    VARCHAR(100),
    pMode    TINYINT
)
BEGIN
    DECLARE pError_No      INT DEFAULT 0;
    DECLARE pError_Message VARCHAR(150);

    IF pMode = 1 THEN
        CASE pType
            WHEN 'village' THEN INSERT INTO mst_village (Village_Name) VALUES (pName);
            WHEN 'ps'      THEN INSERT INTO mst_ps      (Ps_Name)      VALUES (pName);
            WHEN 'post'    THEN INSERT INTO mst_post    (Post_Name)    VALUES (pName);
            WHEN 'pin'     THEN INSERT INTO mst_pin     (Pin_Code)     VALUES (pName);
            WHEN 'dist'    THEN INSERT INTO mst_dist    (Dist_Name)    VALUES (pName);
            ELSE
                SET pError_No = -1;
                SET pError_Message = 'Invalid type';
        END CASE;
        IF pError_No = 0 THEN
            SET pError_Message = CONCAT(pType, ' added successfully');
        END IF;
    END IF;

    IF pMode = 2 THEN
        CASE pType
            WHEN 'village' THEN UPDATE mst_village SET Village_Name = pName WHERE Village_Id = pId;
            WHEN 'ps'      THEN UPDATE mst_ps      SET Ps_Name      = pName WHERE Ps_Id      = pId;
            WHEN 'post'    THEN UPDATE mst_post    SET Post_Name    = pName WHERE Post_Id    = pId;
            WHEN 'pin'     THEN UPDATE mst_pin     SET Pin_Code     = pName WHERE Pin_Id     = pId;
            WHEN 'dist'    THEN UPDATE mst_dist    SET Dist_Name    = pName WHERE Dist_Id    = pId;
            ELSE
                SET pError_No = -1;
                SET pError_Message = 'Invalid type';
        END CASE;
        IF pError_No = 0 THEN
            SET pError_Message = CONCAT(pType, ' updated successfully');
        END IF;
    END IF;

    SELECT pError_No AS Error_No, pError_Message AS Message;
END ;;
DELIMITER ;
/*!50003 SET sql_mode              = @saved_sql_mode */ ;
/*!50003 SET character_set_client  = @saved_cs_client */ ;
/*!50003 SET character_set_results = @saved_cs_results */ ;
/*!50003 SET collation_connection  = @saved_col_connection */ ;
/*!50003 DROP PROCEDURE IF EXISTS `USP_GET_ADDRESS_MASTER` */;
DELIMITER ;;
CREATE DEFINER=`priority`@`%` PROCEDURE `USP_GET_ADDRESS_MASTER`(
    IN pType VARCHAR(10)
)
BEGIN
    CASE LOWER(pType)
        WHEN 'village' THEN
            SELECT Village_Id AS Id, Village_Name AS Name FROM mst_village ORDER BY Village_Name;
        WHEN 'ps' THEN
            SELECT Ps_Id AS Id, Ps_Name AS Name FROM mst_ps ORDER BY Ps_Name;
        WHEN 'post' THEN
            SELECT Post_Id AS Id, Post_Name AS Name FROM mst_post ORDER BY Post_Name;
        WHEN 'pin' THEN
            SELECT Pin_Id AS Id, Pin_Code AS Name FROM mst_pin ORDER BY Pin_Code;
        WHEN 'dist' THEN
            SELECT Dist_Id AS Id, Dist_Name AS Name FROM mst_dist ORDER BY Dist_Name;
        ELSE
            SELECT CAST(NULL AS SIGNED) AS Id, CAST(NULL AS CHAR(100)) AS Name WHERE 1 = 0;
    END CASE;
END ;;
DELIMITER ;
/*!50003 DROP PROCEDURE IF EXISTS `USP_GET_ADDRESS_NAME` */;
DELIMITER ;;
CREATE DEFINER=`priority`@`%` PROCEDURE `USP_GET_ADDRESS_NAME`(
    IN pType VARCHAR(10),
    IN pId   INT
)
BEGIN
    CASE LOWER(pType)
        WHEN 'village' THEN
            SELECT Village_Name AS Name FROM mst_village WHERE Village_Id = pId LIMIT 1;
        WHEN 'ps' THEN
            SELECT Ps_Name AS Name FROM mst_ps WHERE Ps_Id = pId LIMIT 1;
        WHEN 'post' THEN
            SELECT Post_Name AS Name FROM mst_post WHERE Post_Id = pId LIMIT 1;
        WHEN 'pin' THEN
            SELECT Pin_Code AS Name FROM mst_pin WHERE Pin_Id = pId LIMIT 1;
        WHEN 'dist' THEN
            SELECT Dist_Name AS Name FROM mst_dist WHERE Dist_Id = pId LIMIT 1;
        ELSE
            SELECT CAST(NULL AS CHAR(100)) AS Name WHERE 1 = 0;
    END CASE;
END ;;
DELIMITER ;
/*!50003 DROP PROCEDURE IF EXISTS `USP_UPDATE_ENTITY_ADDRESS` */;
DELIMITER ;;
CREATE DEFINER=`priority`@`%` PROCEDURE `USP_UPDATE_ENTITY_ADDRESS`(
    IN pEntity      VARCHAR(20),
    IN pEntity_Id   INT,
    IN pEntity_Code VARCHAR(50),
    IN pVillage_Id  INT,
    IN pPs_Id       INT,
    IN pPost_Id     INT,
    IN pPin_Id      INT,
    IN pDist_Id     INT
)
BEGIN
    DECLARE vId INT DEFAULT NULL;
    DECLARE pError_No INT DEFAULT 0;
    DECLARE pError_Message VARCHAR(150) DEFAULT 'Address updated successfully';

    SET vId = NULLIF(pEntity_Id, 0);

    IF vId IS NULL AND IFNULL(TRIM(pEntity_Code), '') <> '' THEN
        IF LOWER(pEntity) = 'agent' THEN
            SELECT Agent_Id INTO vId FROM mst_agent WHERE Agent_Code = pEntity_Code ORDER BY Agent_Id DESC LIMIT 1;
        ELSEIF LOWER(pEntity) = 'member' THEN
            SELECT Member_Id INTO vId FROM mst_member WHERE Member_Code = pEntity_Code ORDER BY Member_Id DESC LIMIT 1;
        ELSEIF LOWER(pEntity) = 'party' THEN
            SELECT Party_Id INTO vId FROM mst_party WHERE Party_Code = pEntity_Code ORDER BY Party_Id DESC LIMIT 1;
        END IF;
    END IF;

    IF vId IS NULL THEN
        SET pError_No = -1;
        SET pError_Message = 'Entity not found for address update';
    ELSE
        IF LOWER(pEntity) = 'agent' THEN
            UPDATE mst_agent
               SET Village_Id = pVillage_Id, Ps_Id = pPs_Id, Post_Id = pPost_Id, Pin_Id = pPin_Id, Dist_Id = pDist_Id
             WHERE Agent_Id = vId;
        ELSEIF LOWER(pEntity) = 'member' THEN
            UPDATE mst_member
               SET Village_Id = pVillage_Id, Ps_Id = pPs_Id, Post_Id = pPost_Id, Pin_Id = pPin_Id, Dist_Id = pDist_Id
             WHERE Member_Id = vId;
        ELSEIF LOWER(pEntity) = 'party' THEN
            UPDATE mst_party
               SET Village_Id = pVillage_Id, Ps_Id = pPs_Id, Post_Id = pPost_Id, Pin_Id = pPin_Id, Dist_Id = pDist_Id
             WHERE Party_Id = vId;
        ELSE
            SET pError_No = -2;
            SET pError_Message = 'Invalid entity type';
        END IF;
    END IF;

    SELECT pError_No AS Error_No, pError_Message AS Message, vId AS Entity_Id;
END ;;
DELIMITER ;
/*!50003 DROP PROCEDURE IF EXISTS `USP_ADD_EDIT_AGENT` */;
/*!50003 SET @saved_cs_client      = @@character_set_client */ ;
/*!50003 SET @saved_cs_results     = @@character_set_results */ ;
/*!50003 SET @saved_col_connection = @@collation_connection */ ;
/*!50003 SET character_set_client  = utf8mb4 */ ;
/*!50003 SET character_set_results = utf8mb4 */ ;
/*!50003 SET collation_connection  = utf8mb4_unicode_ci */ ;
/*!50003 SET @saved_sql_mode       = @@sql_mode */ ;
/*!50003 SET sql_mode              = 'ONLY_FULL_GROUP_BY,STRICT_TRANS_TABLES,NO_ZERO_IN_DATE,NO_ZERO_DATE,ERROR_FOR_DIVISION_BY_ZERO,NO_ENGINE_SUBSTITUTION' */ ;
DELIMITER ;;
CREATE DEFINER=`priority`@`%` PROCEDURE `USP_ADD_EDIT_AGENT`(
    pAgent_Id           SMALLINT,
    pAgent_Name         VARCHAR(100),
    pAddress            VARCHAR(150),
    pMobile             VARCHAR(15),
    pJoin_Date          DATE,
    pStock_Limit        NUMERIC(10,2),
    pCredit_Sell_Limit  NUMERIC(10,2),
    pBranch_Id          SMALLINT,
    pPassword           VARCHAR(255),
    pStatus             TINYINT,
    pMode               SMALLINT
)
BEGIN
    DECLARE pExists_Count   INT;
    DECLARE pError_No       INT DEFAULT 0;
    DECLARE pError_Message  VARCHAR(150);
    DECLARE pAgent_Code     VARCHAR(25);

    IF (pMode = 1) THEN
        SET pAgent_Code = UDF_GEN_AGENT_NO('AGENT');

        IF (pAgent_Code IS NULL OR pAgent_Code = '' OR pAgent_Code = '0') THEN
            SET pError_No = -1;
            SET pError_Message = 'Agent Code is not generated !!';
        ELSE
            INSERT INTO mst_agent (
                Agent_Code, Agent_Name, Address, Contact_No, Join_Date,
                Stock_Limit, Credit_Sell_Limit, Branch_Id, LogIn_Pwd, Status_Cd, Settle_Token
            ) VALUES (
                pAgent_Code, pAgent_Name, pAddress, pMobile, pJoin_Date,
                pStock_Limit, pCredit_Sell_Limit, pBranch_Id,
                pPassword,
                1,
                NULL
            );

            SET pError_No = 0;
            SET pError_Message = CONCAT('Agent added successfully. Agent Code is ', pAgent_Code);
        END IF;
    END IF;

    IF (pMode = 2) THEN
        UPDATE mst_agent
           SET Agent_Name         = pAgent_Name,
               Address            = pAddress,
               Contact_No         = pMobile,
               Join_Date          = pJoin_Date,
               Stock_Limit        = pStock_Limit,
               Credit_Sell_Limit  = pCredit_Sell_Limit,
               Branch_Id          = pBranch_Id,
               LogIn_Pwd          = CASE
                                        WHEN pPassword IS NOT NULL AND pPassword <> ''
                                        THEN pPassword
                                        ELSE LogIn_Pwd
                                    END,
               Status_Cd          = pStatus
         WHERE Agent_Id = pAgent_Id;

        SET pError_No = 0;
        SET pError_Message = 'Agent updated successfully';
    END IF;

    SELECT pError_No AS Error_No, pError_Message AS Message;
END ;;
DELIMITER ;
/*!50003 SET sql_mode              = @saved_sql_mode */ ;
/*!50003 SET character_set_client  = @saved_cs_client */ ;
/*!50003 SET character_set_results = @saved_cs_results */ ;
/*!50003 SET collation_connection  = @saved_col_connection */ ;
/*!50003 DROP PROCEDURE IF EXISTS `USP_ADD_EDIT_AGENT_CUST_PAYMENT` */;
/*!50003 SET @saved_cs_client      = @@character_set_client */ ;
/*!50003 SET @saved_cs_results     = @@character_set_results */ ;
/*!50003 SET @saved_col_connection = @@collation_connection */ ;
/*!50003 SET character_set_client  = utf8mb4 */ ;
/*!50003 SET character_set_results = utf8mb4 */ ;
/*!50003 SET collation_connection  = utf8mb4_unicode_ci */ ;
/*!50003 SET @saved_sql_mode       = @@sql_mode */ ;
/*!50003 SET sql_mode              = 'ONLY_FULL_GROUP_BY,STRICT_TRANS_TABLES,NO_ZERO_IN_DATE,NO_ZERO_DATE,ERROR_FOR_DIVISION_BY_ZERO,NO_ENGINE_SUBSTITUTION' */ ;
DELIMITER ;;
CREATE DEFINER=`priority`@`%` PROCEDURE `USP_ADD_EDIT_AGENT_CUST_PAYMENT`(
    pPayment_Id     BIGINT,          -- trn_party_trans.Id (0 = new)
    pTrans_Date     DATE,
    pParty_Id       INT,
    pTrans_Mode     TINYINT,         -- 1 Cash, 2 Bank/UPI
    pAmount         NUMERIC(18,2),
    pParticulars    VARCHAR(150),
    pRef_No         VARCHAR(50),
    pAgent_Id       SMALLINT,
    pFin_Id         INT,
    pBranch_Id      SMALLINT,
    pMode           INT              -- 1 Add, 2 Edit
)
BEGIN
    DECLARE pError_No       INT DEFAULT 0;
    DECLARE pError_Message  VARCHAR(150) DEFAULT '';
    DECLARE pColl_No        VARCHAR(100);
    DECLARE pRemarks        VARCHAR(150);
    DECLARE pParty_Type     INT;
    DECLARE pCust_Agent     INT;
    DECLARE pOutstanding    DECIMAL(18,2);
    DECLARE pEdit_Amt       DECIMAL(18,2) DEFAULT 0;
    DECLARE pMax_Payable    DECIMAL(18,2);
    DECLARE pExist_Txn      BIGINT;
    DECLARE pExist_Trade    BIGINT;
    DECLARE pExist_Agent    INT;
    DECLARE pRef_Clean      VARCHAR(50);
    DECLARE pPart_Clean     VARCHAR(150);

    SET pRef_Clean  = TRIM(IFNULL(pRef_No, ''));
    SET pPart_Clean = TRIM(IFNULL(pParticulars, ''));
    SET pAmount     = IFNULL(pAmount, 0);
    SET pTrans_Mode = IFNULL(pTrans_Mode, 0);
    SET pParty_Id   = IFNULL(pParty_Id, 0);
    SET pPayment_Id = IFNULL(pPayment_Id, 0);
    SET pMode       = IFNULL(pMode, 1);

    IF pMode NOT IN (1, 2) THEN
        SET pError_No = -1;
        SET pError_Message = 'Invalid mode';
    ELSEIF pTrans_Mode NOT IN (1, 2) THEN
        SET pError_No = -1;
        SET pError_Message = 'Only Cash and Bank/UPI are allowed';
    ELSEIF pAmount <= 0 THEN
        SET pError_No = -1;
        SET pError_Message = 'Amount must be greater than 0';
    ELSEIF pParty_Id <= 0 THEN
        SET pError_No = -1;
        SET pError_Message = 'Please select a customer';
    ELSEIF pPart_Clean = '' THEN
        SET pError_No = -1;
        SET pError_Message = 'Particulars is required';
    ELSEIF pTrans_Date IS NULL THEN
        SET pError_No = -1;
        SET pError_Message = 'Payment date is required';
    END IF;

    IF pError_No = 0 THEN
        SELECT Party_Type, Cust_Agent_Id
          INTO pParty_Type, pCust_Agent
          FROM mst_party
         WHERE Party_Id = pParty_Id
         LIMIT 1;

        IF pParty_Type IS NULL OR pParty_Type <> 2 THEN
            SET pError_No = -1;
            SET pError_Message = 'Invalid customer';
        ELSEIF IFNULL(pCust_Agent, 0) <> pAgent_Id THEN
            SET pError_No = -1;
            SET pError_Message = 'Customer is not assigned to this agent';
        END IF;
    END IF;

    IF pError_No = 0 AND pMode = 2 THEN
        SELECT Txn_Id, Trading_Id, Created_By, Amount
          INTO pExist_Txn, pExist_Trade, pExist_Agent, pEdit_Amt
          FROM trn_party_trans
         WHERE Id = pPayment_Id
           AND Trans_Type = 'C'
         LIMIT 1;

        IF pExist_Agent IS NULL THEN
            SET pError_No = -3;
            SET pError_Message = 'Payment not found';
        ELSEIF IFNULL(pExist_Agent, 0) <> pAgent_Id THEN
            SET pError_No = -3;
            SET pError_Message = 'Payment not found';
        ELSEIF IFNULL(pExist_Txn, 0) <> 0 THEN
            SET pError_No = -1;
            SET pError_Message = 'Legacy voucher payment cannot be edited here';
        ELSEIF IFNULL(pExist_Trade, 0) <> 0 THEN
            SET pError_No = -1;
            SET pError_Message = 'Invalid payment record';
        END IF;
    END IF;

    IF pError_No = 0 THEN
        SET pOutstanding = IFNULL(UDF_CAL_PARTY_CLOSING(pParty_Id, pTrans_Date), 0);
        IF pMode = 2 THEN
            SET pMax_Payable = pOutstanding + IFNULL(pEdit_Amt, 0);
        ELSE
            SET pMax_Payable = pOutstanding;
        END IF;

        IF pMax_Payable <= 0 THEN
            SET pError_No = -1;
            SET pError_Message = 'Customer has no outstanding balance to collect';
        ELSEIF pAmount > pMax_Payable THEN
            SET pError_No = -1;
            SET pError_Message = CONCAT('Amount cannot exceed outstanding ', FORMAT(pMax_Payable, 2));
        END IF;
    END IF;

    IF pError_No = 0 AND pMode = 1 THEN
        SET pColl_No = UDF_GEN_SYS_NO('ACP', pFin_Id, pBranch_Id);
        IF pColl_No IS NULL THEN
            SET pError_No = -2;
            SET pError_Message = 'Collection number is not generated';
        END IF;
    END IF;

    IF pError_No = 0 AND pMode = 2 THEN
        SET pColl_No = (
            SELECT TRIM(SUBSTRING_INDEX(Remarks, '|', 1))
              FROM trn_party_trans
             WHERE Id = pPayment_Id
             LIMIT 1
        );
        IF IFNULL(pColl_No, '') = '' THEN
            SET pColl_No = CONCAT('ACP-', pPayment_Id);
        END IF;
    END IF;

    IF pError_No = 0 THEN
        IF pRef_Clean = '' THEN
            SET pRemarks = LEFT(CONCAT(pColl_No, '|', pPart_Clean), 150);
        ELSE
            SET pRemarks = LEFT(CONCAT(pColl_No, '|', pRef_Clean, '|', pPart_Clean), 150);
        END IF;
    END IF;

    IF pError_No = 0 AND pMode = 1 THEN
        INSERT INTO trn_party_trans (
            Party_Id, Trans_Date, Trans_Type, Amount, Txn_Id, Trading_Id,
            Remarks, Trans_Mode, Created_By
        ) VALUES (
            pParty_Id, pTrans_Date, 'C', pAmount, NULL, NULL,
            pRemarks, pTrans_Mode, pAgent_Id
        );
        SET pPayment_Id = LAST_INSERT_ID();
        SET pError_Message = CONCAT('Payment saved. No is ', pColl_No);
    END IF;

    IF pError_No = 0 AND pMode = 2 THEN
        UPDATE trn_party_trans
           SET Party_Id   = pParty_Id,
               Trans_Date = pTrans_Date,
               Amount     = pAmount,
               Remarks    = pRemarks,
               Trans_Mode = pTrans_Mode
         WHERE Id = pPayment_Id
           AND Created_By = pAgent_Id
           AND Trans_Type = 'C'
           AND IFNULL(Txn_Id, 0) = 0
           AND IFNULL(Trading_Id, 0) = 0;

        IF ROW_COUNT() = 0 THEN
            SET pError_No = -3;
            SET pError_Message = 'Payment not found';
        ELSE
            SET pError_Message = CONCAT('Payment updated. No is ', pColl_No);
        END IF;
    END IF;

    SELECT pError_No AS Error_No,
           pError_Message AS Message,
           pPayment_Id AS Payment_Id,
           pColl_No AS Coll_No;
END ;;
DELIMITER ;
/*!50003 SET sql_mode              = @saved_sql_mode */ ;
/*!50003 SET character_set_client  = @saved_cs_client */ ;
/*!50003 SET character_set_results = @saved_cs_results */ ;
/*!50003 SET collation_connection  = @saved_col_connection */ ;
/*!50003 DROP PROCEDURE IF EXISTS `USP_ADD_EDIT_AGENT_INDENT` */;
/*!50003 SET @saved_cs_client      = @@character_set_client */ ;
/*!50003 SET @saved_cs_results     = @@character_set_results */ ;
/*!50003 SET @saved_col_connection = @@collation_connection */ ;
/*!50003 SET character_set_client  = utf8mb4 */ ;
/*!50003 SET character_set_results = utf8mb4 */ ;
/*!50003 SET collation_connection  = utf8mb4_unicode_ci */ ;
/*!50003 SET @saved_sql_mode       = @@sql_mode */ ;
/*!50003 SET sql_mode              = 'ONLY_FULL_GROUP_BY,STRICT_TRANS_TABLES,NO_ZERO_IN_DATE,NO_ZERO_DATE,ERROR_FOR_DIVISION_BY_ZERO,NO_ENGINE_SUBSTITUTION' */ ;
DELIMITER ;;
CREATE DEFINER=`priority`@`%` PROCEDURE `USP_ADD_EDIT_AGENT_INDENT`(
    pAgent_Id           INT,
    pDate               DATE,
    pBranch_Id          INT,
    pFin_Id             INT,
    pType               INT,
    pMode               SMALLINT
)
BEGIN

    DECLARE pData_Count     INT;
    DECLARE pTemp_Id        INT;
    DECLARE pLoop_Count     INT;
    DECLARE pError_No       INT;
    DECLARE pStock_Id       BIGINT;
    DECLARE pIndent_No      VARCHAR(25);
    DECLARE pInsert_Id      INT;
    DECLARE pError_Message  VARCHAR(100);

    SET pData_Count = (SELECT COUNT(*) FROM tempitem);

    IF (pData_Count = 0) THEN
        SET pError_No = -1;
        SET pError_Message = 'No Product ';
    ELSE
        SET pError_No = 0;
    END IF;

    IF (pError_No = 0) THEN
        IF (pMode = 1) THEN
            IF (pType = 1) THEN
                SET pLoop_Count = 0;
                WHILE (pLoop_Count < pData_Count) DO
                    SET pTemp_Id = (SELECT id FROM tempitem ORDER BY id LIMIT pLoop_Count, 1);
                    IF ((SELECT IFNULL(qnty, 0) FROM tempitem WHERE id = pTemp_Id) > 0) THEN
                        INSERT INTO trns_stockinout (Branch_Id, Stock_Type, Type_Nature, InOut_Date, Prod_Id, Unit_Id, Quantity, Stock_At, MRP)
                        SELECT pBranch_Id, 4, 'I', pDate, prod_id, unit_id, qnty, 3, IFNULL(mrp, 0)
                        FROM tempitem
                        WHERE id = pTemp_Id;
                        SET pStock_Id = LAST_INSERT_ID();
                        INSERT INTO trans_agent_stock (TrnsStock_Id, Agent_Id, Trans_Date, Stock_Status, Prod_Id)
                        SELECT pStock_Id, pAgent_Id, pDate, 3, prod_id
                        FROM tempitem
                        WHERE id = pTemp_Id;
                    END IF;
                    SET pLoop_Count = pLoop_Count + 1;
                END WHILE;

                UPDATE trans_agent_indent_details d
                INNER JOIN tempitem t ON t.indent_id = d.Indent_Id AND t.prod_id = d.Prod_Id
                SET d.Reject_Qty = GREATEST(0, CAST(IFNULL(d.Quantity, 0) AS DECIMAL(10,2)) - IFNULL(t.qnty, 0));
            END IF;
            UPDATE trans_agent_indent
               SET Is_Issued = 1,
                   Issued_DtTm = CURRENT_TIMESTAMP()
             WHERE Indent_Id = (SELECT MAX(indent_id) FROM tempitem);
            SET pError_Message = 'Agent Indent Successfully Saved !!';
        END IF;

        IF (pType = 2) THEN
            SET pIndent_No = (UDF_GEN_SYS_NO('AI', pFin_Id, pBranch_Id));
            IF (pIndent_No IS NULL) THEN
                SET pError_No = -2;
                SET pError_Message = 'Indent Number Is Not Genereated !!';
            ELSE
                SET pError_No = 0;
            END IF;

            IF (pError_No = 0) THEN
                INSERT INTO trans_agent_indent (Indent_Date, Indent_No, Agent_Id, Remarks, Entry_DtTm)
                VALUES (pDate, pIndent_No, pAgent_Id, 'From Office', CURRENT_TIMESTAMP());
                SET pInsert_Id = LAST_INSERT_ID();
                INSERT INTO trans_agent_indent_details (Indent_Id, Prod_Id, Quantity, Unit_Id, Reject_Qty)
                SELECT pInsert_Id, item_id, qnty, unit_id, 0 FROM tempitem;

                SET pLoop_Count = 0;
                WHILE (pLoop_Count < pData_Count) DO
                    SET pTemp_Id = (SELECT id FROM tempitem ORDER BY id LIMIT pLoop_Count, 1);
                    IF ((SELECT IFNULL(qnty, 0) FROM tempitem WHERE id = pTemp_Id) > 0) THEN
                        INSERT INTO trns_stockinout (Branch_Id, Stock_Type, Type_Nature, InOut_Date, Prod_Id, Unit_Id, Quantity, Stock_At, MRP)
                        SELECT pBranch_Id, 4, 'I', pDate, prod_id, unit_id, qnty, 3, IFNULL(mrp, 0)
                        FROM tempitem
                        WHERE id = pTemp_Id;
                        SET pStock_Id = LAST_INSERT_ID();
                        INSERT INTO trans_agent_stock (TrnsStock_Id, Agent_Id, Trans_Date, Stock_Status, Prod_Id)
                        SELECT pStock_Id, pAgent_Id, pDate, 3, prod_id
                        FROM tempitem
                        WHERE id = pTemp_Id;
                    END IF;
                    SET pLoop_Count = pLoop_Count + 1;
                END WHILE;

                UPDATE trans_agent_indent
                   SET Is_Issued = 1,
                       Issued_DtTm = CURRENT_TIMESTAMP()
                 WHERE Indent_Id = pInsert_Id;
                SET pError_Message = CONCAT('Agent Indent Successful, No Is ', pIndent_No);
            END IF;
        END IF;
    END IF;

    SELECT pError_No AS Error_No, pError_Message AS Message;
END ;;
DELIMITER ;
/*!50003 SET sql_mode              = @saved_sql_mode */ ;
/*!50003 SET character_set_client  = @saved_cs_client */ ;
/*!50003 SET character_set_results = @saved_cs_results */ ;
/*!50003 SET collation_connection  = @saved_col_connection */ ;
/*!50003 DROP PROCEDURE IF EXISTS `USP_ADD_EDIT_AGENT_OFFICE_RETURN` */;
/*!50003 SET @saved_cs_client      = @@character_set_client */ ;
/*!50003 SET @saved_cs_results     = @@character_set_results */ ;
/*!50003 SET @saved_col_connection = @@collation_connection */ ;
/*!50003 SET character_set_client  = utf8mb4 */ ;
/*!50003 SET character_set_results = utf8mb4 */ ;
/*!50003 SET collation_connection  = utf8mb4_unicode_ci */ ;
/*!50003 SET @saved_sql_mode       = @@sql_mode */ ;
/*!50003 SET sql_mode              = 'ONLY_FULL_GROUP_BY,STRICT_TRANS_TABLES,NO_ZERO_IN_DATE,NO_ZERO_DATE,ERROR_FOR_DIVISION_BY_ZERO,NO_ENGINE_SUBSTITUTION' */ ;
DELIMITER ;;
CREATE DEFINER=`priority`@`%` PROCEDURE `USP_ADD_EDIT_AGENT_OFFICE_RETURN`(
    pAgent_Id INT,
    pDate DATE,
    pBranch_Id INT,
    pRemarks VARCHAR(100)
)
BEGIN
    DECLARE pData_Count INT;
    DECLARE pTemp_Id INT;
    DECLARE pLoop_Count INT;
    DECLARE pError_No INT;
    DECLARE pStock_Id BIGINT;
    DECLARE pError_Message VARCHAR(100);

    SET pData_Count = (SELECT COUNT(*) FROM tempitem);

    IF (pData_Count = 0) THEN
        SET pError_No = -1;
        SET pError_Message = 'No Product Found !!';
    ELSE
        SET pError_No = 0;
    END IF;

    IF (pError_No = 0) THEN
        SET pLoop_Count = 0;
        WHILE (pLoop_Count < pData_Count) DO
            SET pTemp_Id = (SELECT id FROM tempitem ORDER BY id LIMIT pLoop_Count, 1);

            INSERT INTO trns_stockinout (
                Branch_Id, Stock_Type, Type_Nature, InOut_Date, Prod_Id, Unit_Id, Quantity, Stock_At, MRP
            )
            SELECT pBranch_Id, 6, 'R', pDate, prod_id, unit_id, qnty, 1, IFNULL(mrp, 0)
            FROM tempitem
            WHERE id = pTemp_Id;

            SET pStock_Id = LAST_INSERT_ID();

            INSERT INTO trans_agent_stock (TrnsStock_Id, Agent_Id, Trans_Date, Stock_Status, Prod_Id)
            SELECT pStock_Id, pAgent_Id, pDate, 3, prod_id
            FROM tempitem
            WHERE id = pTemp_Id;

            SET pLoop_Count = pLoop_Count + 1;
        END WHILE;

        UPDATE trans_agent_indent
           SET Is_Issued = 1,
               Issued_DtTm = CURRENT_TIMESTAMP()
         WHERE Indent_Id IN (
             SELECT DISTINCT indent_id FROM tempitem WHERE IFNULL(indent_id, 0) > 0
         );

        SET pError_Message = 'Office Return Saved Successfully !!';
    END IF;

    SELECT pError_No AS Error_No, pError_Message AS Message;
END ;;
DELIMITER ;
/*!50003 SET sql_mode              = @saved_sql_mode */ ;
/*!50003 SET character_set_client  = @saved_cs_client */ ;
/*!50003 SET character_set_results = @saved_cs_results */ ;
/*!50003 SET collation_connection  = @saved_col_connection */ ;
/*!50003 DROP PROCEDURE IF EXISTS `USP_ADD_EDIT_AGENT_SALE` */;
/*!50003 SET @saved_cs_client      = @@character_set_client */ ;
/*!50003 SET @saved_cs_results     = @@character_set_results */ ;
/*!50003 SET @saved_col_connection = @@collation_connection */ ;
/*!50003 SET character_set_client  = utf8mb4 */ ;
/*!50003 SET character_set_results = utf8mb4 */ ;
/*!50003 SET collation_connection  = utf8mb4_unicode_ci */ ;
/*!50003 SET @saved_sql_mode       = @@sql_mode */ ;
/*!50003 SET sql_mode              = 'ONLY_FULL_GROUP_BY,STRICT_TRANS_TABLES,NO_ZERO_IN_DATE,NO_ZERO_DATE,ERROR_FOR_DIVISION_BY_ZERO,NO_ENGINE_SUBSTITUTION' */ ;
DELIMITER ;;
CREATE DEFINER=`priority`@`%` PROCEDURE `USP_ADD_EDIT_AGENT_SALE`(
    pSale_Id            BIGINT,
    pBranch_Id          SMALLINT,
    pSale_Date          DATE,
    pSale_No            VARCHAR(45),
    pParty_Id           INT,
    pTot_Amt            NUMERIC(18,2),
    pDisc_Amt           NUMERIC(8,2),
    pTaxble_Amt         NUMERIC(12,2),
    pTot_Gst            NUMERIC(10,2),
    pRound_Amt          NUMERIC(5,2),
    pNet_Amt            NUMERIC(12,2),
    pTrans_Mode         TINYINT,
    pUser_Id            SMALLINT,
    pFin_Id             INT,
    pMode               INT
)
BEGIN

    DECLARE pError_No       INT;
    DECLARE pError_Message  VARCHAR(100);
    DECLARE pData_Count     INT;
    DECLARE pPur_Id         INT;
    DECLARE pTrans_Id       INT;
    DECLARE pNpur_No        VARCHAR(100);
    DECLARE pGst_Have       BIT;
    DECLARE pGst_Type       SMALLINT;
    DECLARE pBill_Data      JSON;
    DECLARE pItem_Count     INT;
    DECLARE pItem_Loop      INT;
    DECLARE pTemp_Id        INT;
    DECLARE pStock_Id       INT;

    SELECT Is_GST, Gst_Type
      INTO pGst_Have, pGst_Type
      FROM smartinventory_core.mst_org_config
     WHERE Org_Id = (
        SELECT Org_Id
          FROM smartinventory_core.mst_org_branch
         WHERE Branch_Id = (SELECT Branch_Id FROM mst_agent WHERE Agent_Id = pUser_Id)
     );

    SET pData_Count = (SELECT COUNT(*) FROM temppurchase);
    SET pTrans_Mode = IFNULL(pTrans_Mode, 1);
    SET pParty_Id   = IFNULL(pParty_Id, 0);
    SET pTrans_Id   = NULL;

    IF (pData_Count = 0) THEN
        SET pError_No = -1;
        SET pError_Message = 'No Sale Item Found !!';
    ELSEIF (pTrans_Mode NOT IN (1, 2, 3)) THEN
        SET pError_No = -1;
        SET pError_Message = 'Invalid payment mode !!';
    ELSEIF (pTrans_Mode = 3 AND pParty_Id <= 0) THEN
        SET pError_No = -1;
        SET pError_Message = 'Customer is required for credit sale !!';
    ELSE
        SET pError_No = 0;
    END IF;

    IF (pError_No = 0 AND pMode = 1) THEN
        SET pNpur_No = (UDF_GEN_SYS_NO('INV', pFin_Id, pBranch_Id));

        IF (pNpur_No IS NULL) THEN
            SET pError_No = -2;
            SET pError_Message = 'Sale Number Not Genereated !!';
        ELSE
            SET pError_No = 0;
        END IF;
    END IF;

    IF (pError_No = 0) THEN
        IF (pMode = 1) THEN
            INSERT INTO trans_trading (
                Branch_Id, Tranding_Type, Invoice_No, Ref_No, Invoice_Date, Party_Id,
                Invoice_Amt, Disc_Amount, Taxable_Amt, GST_Amt, Round_Off, Net_Amt,
                Status_Cd, Voucher_Id, Agent_Id, Pay_Mode
            ) VALUES (
                pBranch_Id, 3, pNpur_No,
                CASE WHEN pSale_No IS NULL THEN pNpur_No ELSE pSale_No END,
                pSale_Date, NULLIF(pParty_Id, 0),
                pTot_Amt, pDisc_Amt, pTaxble_Amt, pTot_Gst, pRound_Amt, pNet_Amt,
                1, pTrans_Id, pUser_Id, pTrans_Mode
            );

            SET pPur_Id = LAST_INSERT_ID();

            INSERT INTO trans_trading_products (
                Trading_Id, Prod_Id, Gst_Code, Item_Qty, Item_Rate, Unit_Id, Item_Total, MRP,
                Disc_Prcnt, Disc_Amt, Taxable_Amt, SGST_Prcnt, SGST_Amt, CGST_Prcnt, CGST_Amt, Net_Amt
            )
            SELECT pPur_Id, item_id, hsn_code, qnty, rate, unit, tot_amt, sale_mrp,
                   disc_perc, disc_amt, tax_amt, sgst_perc, sgst_amt, cgst_perc, cgst_amt, net_amt
              FROM temppurchase;

            INSERT INTO trans_trading_gst (
                Trading_Id, HSN_No, Taxable_Amt, SGST_Prcnt, SGST_Amt, CGST_Prcnt, CGST_Amt, Tax_Amount
            )
            SELECT pPur_Id, ANY_VALUE(hsn_code), SUM(tax_amt), ANY_VALUE(sgst_perc), SUM(sgst_amt),
                   ANY_VALUE(cgst_perc), SUM(cgst_amt), (SUM(sgst_amt) + SUM(cgst_amt))
              FROM temppurchase
             GROUP BY hsn_code;

            SET pItem_Count = (SELECT COUNT(*) FROM temppurchase);
            SET pItem_Loop = 0;

            WHILE (pItem_Loop < pItem_Count) DO
                SET pTemp_Id = (SELECT Id FROM temppurchase ORDER BY item_id LIMIT pItem_Loop, 1);

                INSERT INTO trns_stockinout (
                    Branch_Id, Stock_Type, Type_Id, Type_Nature, InOut_Date,
                    Prod_Id, Unit_Id, Quantity, Rate, Total_Amount
                )
                SELECT pBranch_Id, 5, pPur_Id, 'S', pSale_Date,
                       item_id, unit, qnty, rate, tot_amt
                  FROM temppurchase
                 WHERE Id = pTemp_Id;

                SET pStock_Id = LAST_INSERT_ID();

                INSERT INTO trans_agent_stock (TrnsStock_Id, Prod_Id, Agent_Id, Trans_Date, Stock_Status)
                SELECT pStock_Id, item_id, pUser_Id, pSale_Date, 3
                  FROM temppurchase
                 WHERE Id = pTemp_Id;

                SET pItem_Loop = pItem_Loop + 1;
            END WHILE;

            IF (pTrans_Mode = 3 AND pParty_Id > 0) THEN
                INSERT INTO trn_party_trans (
                    Party_Id, Trans_Date, Trans_Type, Amount, Txn_Id, Trading_Id,
                    Remarks, Trans_Mode, Created_By
                ) VALUES (
                    pParty_Id, pSale_Date, 'D', pNet_Amt, NULL, pPur_Id,
                    CONCAT('Agent Sale ', pNpur_No), pTrans_Mode, pUser_Id
                );
            END IF;

            SET pError_Message = CONCAT('Sale Successfully Saved No Is ', pNpur_No);
        END IF;

        IF (pMode = 2) THEN
            SET pPur_Id = pSale_Id;
            SET pNpur_No = (SELECT Invoice_No FROM trans_trading WHERE Trading_Id = pSale_Id);

            UPDATE trans_trading
               SET Invoice_Date = pSale_Date,
                   Party_Id     = NULLIF(pParty_Id, 0),
                   Invoice_Amt  = pTot_Amt,
                   Disc_Amount  = pDisc_Amt,
                   Taxable_Amt  = pTaxble_Amt,
                   GST_Amt      = pTot_Gst,
                   Round_Off    = pRound_Amt,
                   Net_Amt      = pNet_Amt,
                   Pay_Mode     = pTrans_Mode
             WHERE Trading_Id = pSale_Id;

            DELETE FROM trans_trading_products WHERE Trading_Id = pSale_Id;
            DELETE FROM trans_trading_gst WHERE Trading_Id = pSale_Id;
            DELETE FROM trn_party_trans WHERE Trading_Id = pSale_Id;

            DELETE a FROM trans_agent_stock a
            INNER JOIN trns_stockinout i ON i.Stock_Id = a.TrnsStock_Id
            WHERE i.Type_Id = pSale_Id;

            DELETE FROM trns_stockinout WHERE Type_Id = pSale_Id;

            INSERT INTO trans_trading_products (
                Trading_Id, Prod_Id, Gst_Code, Item_Qty, Item_Rate, Unit_Id, Item_Total, MRP,
                Disc_Prcnt, Disc_Amt, Taxable_Amt, SGST_Prcnt, SGST_Amt, CGST_Prcnt, CGST_Amt, Net_Amt
            )
            SELECT pPur_Id, item_id, hsn_code, qnty, rate, unit, tot_amt, sale_mrp,
                   disc_perc, disc_amt, tax_amt, sgst_perc, sgst_amt, cgst_perc, cgst_amt, net_amt
              FROM temppurchase;

            INSERT INTO trans_trading_gst (
                Trading_Id, HSN_No, Taxable_Amt, SGST_Prcnt, SGST_Amt, CGST_Prcnt, CGST_Amt, Tax_Amount
            )
            SELECT pPur_Id, ANY_VALUE(hsn_code), SUM(tax_amt), ANY_VALUE(sgst_perc), SUM(sgst_amt),
                   ANY_VALUE(cgst_perc), SUM(cgst_amt), (SUM(sgst_amt) + SUM(cgst_amt))
              FROM temppurchase
             GROUP BY hsn_code;

            SET pItem_Count = (SELECT COUNT(*) FROM temppurchase);
            SET pItem_Loop = 0;

            WHILE (pItem_Loop < pItem_Count) DO
                SET pTemp_Id = (SELECT Id FROM temppurchase ORDER BY item_id LIMIT pItem_Loop, 1);

                INSERT INTO trns_stockinout (
                    Branch_Id, Stock_Type, Type_Id, Type_Nature, InOut_Date,
                    Prod_Id, Unit_Id, Quantity, Rate, Total_Amount
                )
                SELECT pBranch_Id, 5, pPur_Id, 'S', pSale_Date,
                       item_id, unit, qnty, rate, tot_amt
                  FROM temppurchase
                 WHERE Id = pTemp_Id;

                SET pStock_Id = LAST_INSERT_ID();

                INSERT INTO trans_agent_stock (TrnsStock_Id, Prod_Id, Agent_Id, Trans_Date, Stock_Status)
                SELECT pStock_Id, item_id, pUser_Id, pSale_Date, 3
                  FROM temppurchase
                 WHERE Id = pTemp_Id;

                SET pItem_Loop = pItem_Loop + 1;
            END WHILE;

            IF (pTrans_Mode = 3 AND pParty_Id > 0) THEN
                INSERT INTO trn_party_trans (
                    Party_Id, Trans_Date, Trans_Type, Amount, Txn_Id, Trading_Id,
                    Remarks, Trans_Mode, Created_By
                ) VALUES (
                    pParty_Id, pSale_Date, 'D', pNet_Amt, NULL, pPur_Id,
                    CONCAT('Agent Sale ', IFNULL(pNpur_No, pSale_Id)), pTrans_Mode, pUser_Id
                );
            END IF;

            SET pError_Message = 'Sale Successfully Updated !!';
        END IF;
    END IF;

    IF (pGst_Have) THEN
        IF (pGst_Type = 2) THEN
            SET pBill_Data = (
                SELECT JSON_OBJECT(
                    'Invoice_No', m.Invoice_No,
                    'Invoice_Date', m.Invoice_Date,
                    'Cust_Name', UDF_GET_PARTY_NAME(m.Party_Id),
                    'Discount', m.Disc_Amount,
                    'Tot_Amount', m.Net_Amt,
                    'items', (
                        SELECT JSON_ARRAYAGG(
                            JSON_OBJECT(
                                'item_name', CONCAT(p.Prod_ShortNm, ' - ', p.Prod_Code),
                                'qty', d.Item_Qty,
                                'rate', d.MRP,
                                'amount', d.Item_Total
                            )
                        )
                        FROM trans_trading_products d
                        JOIN mst_product p ON p.Prod_Id = d.Prod_Id
                        WHERE d.Trading_Id = m.Trading_Id
                    )
                )
                FROM trans_trading m
                WHERE m.Trading_Id = pPur_Id
            );
        END IF;
    END IF;

    SELECT pError_No AS Error_No, pError_Message AS Message, pBill_Data AS Bill_Data;

    DROP TEMPORARY TABLE IF EXISTS temppurchase;

END ;;
DELIMITER ;
/*!50003 SET sql_mode              = @saved_sql_mode */ ;
/*!50003 SET character_set_client  = @saved_cs_client */ ;
/*!50003 SET character_set_results = @saved_cs_results */ ;
/*!50003 SET collation_connection  = @saved_col_connection */ ;
/*!50003 DROP PROCEDURE IF EXISTS `USP_ADD_EDIT_AGENT_SALE_RET` */;
/*!50003 SET @saved_cs_client      = @@character_set_client */ ;
/*!50003 SET @saved_cs_results     = @@character_set_results */ ;
/*!50003 SET @saved_col_connection = @@collation_connection */ ;
/*!50003 SET character_set_client  = utf8mb4 */ ;
/*!50003 SET character_set_results = utf8mb4 */ ;
/*!50003 SET collation_connection  = utf8mb4_0900_ai_ci */ ;
/*!50003 SET @saved_sql_mode       = @@sql_mode */ ;
/*!50003 SET sql_mode              = 'ONLY_FULL_GROUP_BY,STRICT_TRANS_TABLES,NO_ZERO_IN_DATE,NO_ZERO_DATE,ERROR_FOR_DIVISION_BY_ZERO,NO_ENGINE_SUBSTITUTION' */ ;
DELIMITER ;;
CREATE DEFINER=`priority`@`%` PROCEDURE `USP_ADD_EDIT_AGENT_SALE_RET`(
    pSale_Id     BIGINT,
    pBranch_Id   SMALLINT,
    pSale_Date   DATE,
    pSale_No     VARCHAR(45),
    pParty_Id    INT,
    pTot_Amt     NUMERIC(18,2),
    pDisc_Amt    NUMERIC(8,2),
    pTaxble_Amt  NUMERIC(12,2),
    pTot_Gst     NUMERIC(10,2),
    pRound_Amt   NUMERIC(5,2),
    pNet_Amt     NUMERIC(12,2),
    pUser_Id     SMALLINT,
    pFin_Id      INT,
    pMode        INT
)
BEGIN
    DECLARE pError_No       INT;
    DECLARE pError_Message  VARCHAR(100);
    DECLARE pData_Count     INT;
    DECLARE pPur_Id         INT;
    DECLARE pTrans_Id       INT;
    DECLARE pNpur_No        VARCHAR(100);
    DECLARE pGst_Have       BIT;
    DECLARE pGst_Type       SMALLINT;
    DECLARE pBill_Data      JSON;
    DECLARE pItem_Count     INT;
    DECLARE pItem_Loop      INT;
    DECLARE pTemp_Id        INT;
    DECLARE pStock_Id       INT;
    DECLARE pTradingType    TINYINT;
    DECLARE pStockType      TINYINT;

    SET pTradingType = (
        SELECT Value_Id FROM smartinventory_core.mst_options
         WHERE Option_Id = 6 AND Value_Name = 'Sales Return Entry' LIMIT 1
    );
    SET pStockType = (
        SELECT Value_Id FROM smartinventory_core.mst_options
         WHERE Option_Id = 7 AND Value_Name = 'Sales Return' LIMIT 1
    );
    SET pTradingType = IFNULL(pTradingType, 5);
    SET pStockType   = 10;

    SELECT Is_GST, Gst_Type
      INTO pGst_Have, pGst_Type
      FROM smartinventory_core.mst_org_config
     WHERE Org_Id = (
            SELECT Org_Id FROM smartinventory_core.mst_org_branch
             WHERE Branch_Id = (SELECT Branch_Id FROM mst_agent WHERE Agent_Id = pUser_Id)
        );

    SET pData_Count = (SELECT COUNT(*) FROM temppurchase);

    IF (pData_Count = 0) THEN
        SET pError_No = -1;
        SET pError_Message = 'No Item Found For Return !!';
    ELSE
        SET pError_No = 0;
    END IF;

    IF (pError_No = 0 AND pMode = 1) THEN
        SET pNpur_No = UDF_GEN_SYS_NO('SR', pFin_Id, pBranch_Id);
        IF (pNpur_No IS NULL) THEN
            SET pError_No = -2;
            SET pError_Message = 'Return Number Not Genereated !!';
        END IF;
    END IF;

    IF (pError_No = 0 AND pMode = 1) THEN
        INSERT INTO trans_trading (
            Branch_Id, Tranding_Type, Invoice_No, Ref_No, Invoice_Date, Party_Id,
            Invoice_Amt, Disc_Amount, Taxable_Amt, GST_Amt, Round_Off, Net_Amt,
            Status_Cd, Voucher_Id, Agent_Id, Created_By
        )
        VALUES (
            pBranch_Id, pTradingType, pNpur_No,
            CASE WHEN pSale_No IS NULL OR TRIM(pSale_No) = '' THEN pNpur_No ELSE pSale_No END,
            pSale_Date, pParty_Id,
            pTot_Amt, pDisc_Amt, pTaxble_Amt, pTot_Gst, pRound_Amt, pNet_Amt,
            1, pTrans_Id, pUser_Id, pUser_Id
        );

        SET pPur_Id = LAST_INSERT_ID();

        INSERT INTO trans_trading_products (
            Trading_Id, Prod_Id, Gst_Code, Item_Qty, Item_Rate, Unit_Id, Item_Total,
            MRP, Disc_Prcnt, Disc_Amt, Taxable_Amt, SGST_Prcnt, SGST_Amt, CGST_Prcnt, CGST_Amt, Net_Amt
        )
        SELECT pPur_Id, item_id, hsn_code, qnty, rate, unit, tot_amt,
               sale_mrp, disc_perc, disc_amt, tax_amt, sgst_perc, sgst_amt, cgst_perc, cgst_amt, net_amt
          FROM temppurchase;

        INSERT INTO trans_trading_gst (
            Trading_Id, HSN_No, Taxable_Amt, SGST_Prcnt, SGST_Amt, CGST_Prcnt, CGST_Amt, Tax_Amount
        )
        SELECT pPur_Id, ANY_VALUE(hsn_code), SUM(tax_amt), ANY_VALUE(sgst_perc), SUM(sgst_amt),
               ANY_VALUE(cgst_perc), SUM(cgst_amt), (SUM(sgst_amt) + SUM(cgst_amt))
          FROM temppurchase
         GROUP BY hsn_code;

        SET pItem_Count = (SELECT COUNT(*) FROM temppurchase);
        SET pItem_Loop = 0;
        WHILE (pItem_Loop < pItem_Count) DO
            SET pTemp_Id = (SELECT Id FROM temppurchase ORDER BY Id LIMIT pItem_Loop, 1);

            INSERT INTO trns_stockinout (
                Branch_Id, Stock_Type, Type_Id, Type_Nature, InOut_Date, Prod_Id, Unit_Id,
                Quantity, Rate, Total_Amount, Stock_At
            )
            SELECT pBranch_Id, pStockType, pPur_Id, 'R', pSale_Date, item_id, unit,
                   qnty, rate, tot_amt, 3
              FROM temppurchase
             WHERE Id = pTemp_Id;

            SET pStock_Id = LAST_INSERT_ID();

            INSERT INTO trans_agent_stock (TrnsStock_Id, Prod_Id, Agent_Id, Trans_Date, Stock_Status)
            SELECT pStock_Id, item_id, pUser_Id, pSale_Date, 3
              FROM temppurchase
             WHERE Id = pTemp_Id;

            SET pItem_Loop = pItem_Loop + 1;
        END WHILE;

        SET pError_Message = CONCAT('Return Successfully Saved, No. Is ', pNpur_No);
    END IF;

    IF (pError_No = 0 AND pMode = 2) THEN
        SET pPur_Id = pSale_Id;

        UPDATE trans_trading
           SET Invoice_Date = pSale_Date,
               Party_Id     = pParty_Id,
               Invoice_Amt  = pTot_Amt,
               Disc_Amount  = pDisc_Amt,
               Taxable_Amt  = pTaxble_Amt,
               GST_Amt      = pTot_Gst,
               Round_Off    = pRound_Amt,
               Net_Amt      = pNet_Amt,
               Tranding_Type = pTradingType,
               Agent_Id     = pUser_Id,
               Ref_No       = CASE
                                  WHEN pSale_No IS NULL OR TRIM(pSale_No) = '' THEN Ref_No
                                  ELSE pSale_No
                              END
         WHERE Trading_Id = pSale_Id;

        DELETE a FROM trans_agent_stock a
        INNER JOIN trns_stockinout i ON i.Stock_Id = a.TrnsStock_Id
         WHERE i.Type_Id = pSale_Id;

        DELETE FROM trans_trading_products WHERE Trading_Id = pSale_Id;
        DELETE FROM trans_trading_gst WHERE Trading_Id = pSale_Id;
        DELETE FROM trns_stockinout WHERE Type_Id = pSale_Id;

        INSERT INTO trans_trading_products (
            Trading_Id, Prod_Id, Gst_Code, Item_Qty, Item_Rate, Unit_Id, Item_Total,
            MRP, Disc_Prcnt, Disc_Amt, Taxable_Amt, SGST_Prcnt, SGST_Amt, CGST_Prcnt, CGST_Amt, Net_Amt
        )
        SELECT pSale_Id, item_id, hsn_code, qnty, rate, unit, tot_amt,
               sale_mrp, disc_perc, disc_amt, tax_amt, sgst_perc, sgst_amt, cgst_perc, cgst_amt, net_amt
          FROM temppurchase;

        INSERT INTO trans_trading_gst (
            Trading_Id, HSN_No, Taxable_Amt, SGST_Prcnt, SGST_Amt, CGST_Prcnt, CGST_Amt, Tax_Amount
        )
        SELECT pSale_Id, ANY_VALUE(hsn_code), SUM(tax_amt), ANY_VALUE(sgst_perc), SUM(sgst_amt),
               ANY_VALUE(cgst_perc), SUM(cgst_amt), (SUM(sgst_amt) + SUM(cgst_amt))
          FROM temppurchase
         GROUP BY hsn_code;

        SET pItem_Count = (SELECT COUNT(*) FROM temppurchase);
        SET pItem_Loop = 0;
        WHILE (pItem_Loop < pItem_Count) DO
            SET pTemp_Id = (SELECT Id FROM temppurchase ORDER BY Id LIMIT pItem_Loop, 1);

            INSERT INTO trns_stockinout (
                Branch_Id, Stock_Type, Type_Id, Type_Nature, InOut_Date, Prod_Id, Unit_Id,
                Quantity, Rate, Total_Amount, Stock_At
            )
            SELECT pBranch_Id, pStockType, pSale_Id, 'R', pSale_Date, item_id, unit,
                   qnty, rate, tot_amt, 3
              FROM temppurchase
             WHERE Id = pTemp_Id;

            SET pStock_Id = LAST_INSERT_ID();

            INSERT INTO trans_agent_stock (TrnsStock_Id, Prod_Id, Agent_Id, Trans_Date, Stock_Status)
            SELECT pStock_Id, item_id, pUser_Id, pSale_Date, 3
              FROM temppurchase
             WHERE Id = pTemp_Id;

            SET pItem_Loop = pItem_Loop + 1;
        END WHILE;

        SET pError_Message = 'Sale Return Is Successfully Updated !!';
    END IF;

    IF (pGst_Have AND pGst_Type = 2 AND pPur_Id IS NOT NULL) THEN
        SET pBill_Data = (
            SELECT JSON_OBJECT(
                'Invoice_No', m.Invoice_No,
                'Invoice_Date', m.Invoice_Date,
                'Cust_Name', UDF_GET_PARTY_NAME(m.Party_Id),
                'Discount', m.Disc_Amount,
                'Tot_Amount', m.Net_Amt,
                'items', (
                    SELECT JSON_ARRAYAGG(
                        JSON_OBJECT(
                            'item_name', CONCAT(p.Prod_ShortNm, ' - ', p.Prod_Code),
                            'qty', d.Item_Qty,
                            'rate', d.MRP,
                            'amount', d.Item_Total
                        )
                    )
                    FROM trans_trading_products d
                    JOIN mst_product p ON p.Prod_Id = d.Prod_Id
                    WHERE d.Trading_Id = m.Trading_Id
                )
            )
            FROM trans_trading m
            WHERE m.Trading_Id = pPur_Id
        );
    END IF;

    SELECT pError_No AS Error_No, pError_Message AS Message, pBill_Data AS Bill_Data;

    DROP TEMPORARY TABLE IF EXISTS temppurchase;
END ;;
DELIMITER ;
/*!50003 SET sql_mode              = @saved_sql_mode */ ;
/*!50003 SET character_set_client  = @saved_cs_client */ ;
/*!50003 SET character_set_results = @saved_cs_results */ ;
/*!50003 SET collation_connection  = @saved_col_connection */ ;
/*!50003 DROP PROCEDURE IF EXISTS `USP_ADD_EDIT_COUNTER_BALANCE` */;
/*!50003 SET @saved_cs_client      = @@character_set_client */ ;
/*!50003 SET @saved_cs_results     = @@character_set_results */ ;
/*!50003 SET @saved_col_connection = @@collation_connection */ ;
/*!50003 SET character_set_client  = utf8mb4 */ ;
/*!50003 SET character_set_results = utf8mb4 */ ;
/*!50003 SET collation_connection  = utf8mb4_unicode_ci */ ;
/*!50003 SET @saved_sql_mode       = @@sql_mode */ ;
/*!50003 SET sql_mode              = 'ONLY_FULL_GROUP_BY,STRICT_TRANS_TABLES,NO_ZERO_IN_DATE,NO_ZERO_DATE,ERROR_FOR_DIVISION_BY_ZERO,NO_ENGINE_SUBSTITUTION' */ ;
DELIMITER ;;
CREATE DEFINER=`priority`@`%` PROCEDURE `USP_ADD_EDIT_COUNTER_BALANCE`(
    pId         INT,
    pCounter_Id SMALLINT,
    pBalance    DECIMAL(18,2),
    pDate       DATE,
    pMode       TINYINT
)
BEGIN
    IF pDate IS NULL THEN
        SET pDate = CURDATE();
    END IF;

    IF pMode = 1 THEN
        IF EXISTS (
            SELECT 1 FROM trn_counter_balance
             WHERE counter_id = pCounter_Id AND `date` = pDate
        ) THEN
            SELECT -1 AS Error_No, 'Balance for this counter and date already exists' AS Message;
        ELSE
            INSERT INTO trn_counter_balance (counter_id, balance, `date`)
            VALUES (pCounter_Id, pBalance, pDate);
            SELECT 0 AS Error_No, 'Counter balance added successfully' AS Message;
        END IF;
    ELSEIF pMode = 2 THEN
        IF EXISTS (
            SELECT 1 FROM trn_counter_balance
             WHERE counter_id = pCounter_Id AND `date` = pDate AND id <> pId
        ) THEN
            SELECT -1 AS Error_No, 'Balance for this counter and date already exists' AS Message;
        ELSE
            UPDATE trn_counter_balance
               SET counter_id = pCounter_Id,
                   balance    = pBalance,
                   `date`     = pDate
             WHERE id = pId;
            SELECT 0 AS Error_No, 'Counter balance updated successfully' AS Message;
        END IF;
    END IF;
END ;;
DELIMITER ;
/*!50003 SET sql_mode              = @saved_sql_mode */ ;
/*!50003 SET character_set_client  = @saved_cs_client */ ;
/*!50003 SET character_set_results = @saved_cs_results */ ;
/*!50003 SET collation_connection  = @saved_col_connection */ ;
/*!50003 DROP PROCEDURE IF EXISTS `USP_ADD_EDIT_CUSTOMER` */;
/*!50003 SET @saved_cs_client      = @@character_set_client */ ;
/*!50003 SET @saved_cs_results     = @@character_set_results */ ;
/*!50003 SET @saved_col_connection = @@collation_connection */ ;
/*!50003 SET character_set_client  = utf8mb4 */ ;
/*!50003 SET character_set_results = utf8mb4 */ ;
/*!50003 SET collation_connection  = utf8mb4_0900_ai_ci */ ;
/*!50003 SET @saved_sql_mode       = @@sql_mode */ ;
/*!50003 SET sql_mode              = 'ONLY_FULL_GROUP_BY,STRICT_TRANS_TABLES,NO_ZERO_IN_DATE,NO_ZERO_DATE,ERROR_FOR_DIVISION_BY_ZERO,NO_ENGINE_SUBSTITUTION' */ ;
DELIMITER ;;
CREATE DEFINER=`priority`@`%` PROCEDURE `USP_ADD_EDIT_CUSTOMER`(
    pParty_Id           INT,
    pParty_Code         VARCHAR(25),
    pParty_Name         VARCHAR(250),
    pParty_Mob          VARCHAR(25),
    pParty_Alt_Mob      VARCHAR(25),
    pParty_Mail         VARCHAR(50),
    pParty_Add1         VARCHAR(200),
    pParty_Add2         VARCHAR(200),
    pParty_City         VARCHAR(50),
    pParty_Dist         VARCHAR(50),
    pParty_State        VARCHAR(25),
    pParty_Pin          VARCHAR(10),
    pParty_Pan          VARCHAR(25),
    pParty_GST          VARCHAR(25),
    pGst_State          VARCHAR(5),
    pStart_Date         DATE,
    pCredit_Limit       DECIMAL(18,2),
    pOpening_Bal        DECIMAL(18,2),
    pCust_Agent_Id      INT,
    pBranch_Id          INT,
    pUser_Id            INT,
    pMode               INT
)
BEGIN
    DECLARE pExists_Count   INT;
    DECLARE pError_No       INT;
    DECLARE pError_Message  VARCHAR(100);

    IF (pMode = 1) THEN
        -- Auto-generate Party_Code: C + zero-padded next number
        SET pParty_Code = CONCAT('C', LPAD(
            (SELECT IFNULL(MAX(Party_Id), 0) + 1 FROM mst_party WHERE Party_Type = 2),
            4, '0'
        ));

        SET pExists_Count = (SELECT COUNT(*) FROM mst_party WHERE Party_Name = pParty_Name);
        IF (pExists_Count > 0) THEN
            SET pError_No = -2;
            SET pError_Message = 'Same Party Name Already Exists !!';
        ELSE
            SET pError_No = 0;
        END IF;

        IF (pError_No = 0) THEN
            INSERT INTO mst_party (
                Branch_Id, Party_Type, Party_Code, Party_Name, Contact_No, AltContact_No, EMail,
                Address_Line1, Address_Line2, City, District, State, PinCode, Pan_No, GstIn, State_Cd,
                Start_Date, Credit_Limit, Opening_Bal, Cust_Agent_Id, Status_Cd
            ) VALUES (
                pBranch_Id, 2, pParty_Code, pParty_Name, pParty_Mob, pParty_Alt_Mob, pParty_Mail,
                pParty_Add1, pParty_Add2, pParty_City, pParty_Dist, pParty_State, pParty_Pin,
                pParty_Pan, pParty_GST, pGst_State, pStart_Date, pCredit_Limit, IFNULL(pOpening_Bal, 0),
                NULLIF(pCust_Agent_Id, 0), 1
            );
        END IF;
    END IF;

    IF (pMode = 2) THEN
        SET pError_No = 0;
        UPDATE mst_party
        SET Party_Name = pParty_Name,
            Contact_No = pParty_Mob,
            AltContact_No = pParty_Alt_Mob,
            EMail = pParty_Mail,
            Address_Line1 = pParty_Add1,
            Address_Line2 = pParty_Add2,
            City = pParty_City,
            District = pParty_Dist,
            State = pParty_State,
            PinCode = pParty_Pin,
            Pan_No = pParty_Pan,
            GstIn = pParty_GST,
            State_Cd = pGst_State,
            Credit_Limit = pCredit_Limit,
            Opening_Bal = IFNULL(pOpening_Bal, Opening_Bal),
            Cust_Agent_Id = NULLIF(pCust_Agent_Id, 0)
        WHERE Party_Id = pParty_Id;
    END IF;

    SELECT pError_No AS Error_No, pError_Message AS Message, pParty_Code AS Party_Code;
END ;;
DELIMITER ;
/*!50003 SET sql_mode              = @saved_sql_mode */ ;
/*!50003 SET character_set_client  = @saved_cs_client */ ;
/*!50003 SET character_set_results = @saved_cs_results */ ;
/*!50003 SET collation_connection  = @saved_col_connection */ ;
/*!50003 DROP PROCEDURE IF EXISTS `USP_ADD_EDIT_DAMAGE` */;
/*!50003 SET @saved_cs_client      = @@character_set_client */ ;
/*!50003 SET @saved_cs_results     = @@character_set_results */ ;
/*!50003 SET @saved_col_connection = @@collation_connection */ ;
/*!50003 SET character_set_client  = utf8mb4 */ ;
/*!50003 SET character_set_results = utf8mb4 */ ;
/*!50003 SET collation_connection  = utf8mb4_unicode_ci */ ;
/*!50003 SET @saved_sql_mode       = @@sql_mode */ ;
/*!50003 SET sql_mode              = 'ONLY_FULL_GROUP_BY,STRICT_TRANS_TABLES,NO_ZERO_IN_DATE,NO_ZERO_DATE,ERROR_FOR_DIVISION_BY_ZERO,NO_ENGINE_SUBSTITUTION' */ ;
DELIMITER ;;
CREATE DEFINER=`priority`@`%` PROCEDURE `USP_ADD_EDIT_DAMAGE`(
    pDamage_Id     BIGINT,
    pVouch_Id      BIGINT,
    pBranch_Id     SMALLINT,
    pDmg_Date      DATE,
    pParticulars   VARCHAR(200),
    pUser_Id       SMALLINT,
    pFin_Id        INT,
    pMode          INT
)
BEGIN
    DECLARE pError_No       INT DEFAULT 0;
    DECLARE pError_Message  VARCHAR(150);
    DECLARE pData_Count     INT;
    DECLARE pTrading_Id     BIGINT;
    DECLARE pTxn_Id         BIGINT;
    DECLARE pDoc_No         VARCHAR(100);
    DECLARE pVouch_No       VARCHAR(100);
    DECLARE pNet_Amt        NUMERIC(18,2);
    DECLARE pTradingType    TINYINT;
    DECLARE pStockType      TINYINT;
    DECLARE pVouTransType   TINYINT;
    DECLARE pStockLedg      INT;
    DECLARE pAdjLedg        INT;

    SET pTradingType = IFNULL((
        SELECT Value_Id FROM smartinventory_core.mst_options
         WHERE Option_Id = 6 AND Value_Name = 'Damages Entry' LIMIT 1
    ), 6);
    SET pStockType = IFNULL((
        SELECT Value_Id FROM smartinventory_core.mst_options
         WHERE Option_Id = 7 AND Value_Name = 'Damage/Wastage' LIMIT 1
    ), 9);
    SET pVouTransType = IFNULL((
        SELECT Value_Id FROM smartinventory_core.mst_options
         WHERE Option_Id = 13 AND Value_Name = 'Damage/Wastage' LIMIT 1
    ), 7);

    SET pStockLedg = (
        SELECT Account_Id FROM mst_acct_glhead
         WHERE Account_For = 'S'
         ORDER BY Account_Id
         LIMIT 1
    );
    SET pAdjLedg = (
        SELECT Account_Id FROM mst_acct_glhead
         WHERE Account_For = 'T'
         ORDER BY Account_Id
         LIMIT 1
    );

    SET pData_Count = (SELECT COUNT(*) FROM tempdamage);
    IF (pData_Count = 0) THEN
        SET pError_No = -1;
        SET pError_Message = 'No Item Found For Damage / Wastage !!';
    ELSEIF pStockLedg IS NULL OR pAdjLedg IS NULL THEN
        SET pError_No = -4;
        SET pError_Message = 'Stock ledger (S / T) is not mapped in chart of accounts !!';
    ELSE
        SET pError_No = 0;
    END IF;

    IF (pError_No = 0) THEN
        SET pNet_Amt = IFNULL((SELECT SUM(tot_amt) FROM tempdamage), 0);
    END IF;

    IF (pError_No = 0 AND pMode = 1) THEN
        SET pDoc_No = UDF_GEN_SYS_NO('ID', pFin_Id, pBranch_Id);
        IF (pDoc_No IS NULL) THEN
            SET pError_No = -2;
            SET pError_Message = 'Damage Number Is Not Generated !!';
        END IF;
        IF (pError_No = 0) THEN
            SET pVouch_No = UDF_GEN_SYS_NO('JOU', pFin_Id, pBranch_Id);
            IF (pVouch_No IS NULL) THEN
                SET pError_No = -3;
                SET pError_Message = 'Voucher Number Is Not Generated !!';
            END IF;
        END IF;
    END IF;

    IF (pError_No = 0 AND pMode = 1) THEN
        INSERT INTO trans_voucher_master (
            Year_Id, Vou_Date, Vou_Type, Vou_Mode, Vou_No, Ref_Vou_No, Particulars,
            Trans_Type, Type_Id, Status, Created_By, Approved_By, Approved_On
        ) VALUES (
            pFin_Id, pDmg_Date, 4, 3, pVouch_No, pDoc_No,
            IFNULL(NULLIF(TRIM(pParticulars), ''), CONCAT('By Damage/Wastage ', pDoc_No)),
            pVouTransType, NULL, 2, pUser_Id, pUser_Id, CURRENT_TIMESTAMP()
        );
        SET pTxn_Id = LAST_INSERT_ID();

        INSERT INTO trans_trading (
            Branch_Id, Tranding_Type, Invoice_No, Ref_No, Invoice_Date,
            Invoice_Amt, Taxable_Amt, Net_Amt, Remarks, Status_Cd, Voucher_Id, Created_By, Created_On
        ) VALUES (
            pBranch_Id, pTradingType, pDoc_No, pDoc_No, pDmg_Date,
            pNet_Amt, pNet_Amt, pNet_Amt, pParticulars, 1, pTxn_Id, pUser_Id, CURRENT_TIMESTAMP()
        );
        SET pTrading_Id = LAST_INSERT_ID();

        UPDATE trans_voucher_master
           SET Type_Id = pTrading_Id
         WHERE Voucher_Id = pTxn_Id;

        INSERT INTO trans_voucher_details (Voucher_Id, Trans_Type, GlHead_Id, Vou_Amount)
        VALUES (pTxn_Id, 'D', pAdjLedg, pNet_Amt);
        INSERT INTO trans_voucher_details (Voucher_Id, Trans_Type, GlHead_Id, Vou_Amount)
        VALUES (pTxn_Id, 'C', pStockLedg, pNet_Amt);

        INSERT INTO trans_trading_products (
            Trading_Id, Prod_Id, Item_Qty, Item_Rate, Unit_Id, Item_Total, Taxable_Amt, Net_Amt
        )
        SELECT pTrading_Id, item_id, qnty, rate, unit, tot_amt, tot_amt, tot_amt FROM tempdamage;

        INSERT INTO trns_stockinout (
            Branch_Id, Stock_Type, Type_Id, Type_Nature, InOut_Date, Prod_Id, Unit_Id,
            Quantity, Rate, Total_Amount, Stock_At
        )
        SELECT pBranch_Id, pStockType, pTrading_Id, 'D', pDmg_Date, item_id, unit,
               qnty, rate, tot_amt, 5
          FROM tempdamage;

        SET pError_Message = CONCAT('Damage / Wastage Saved, No. Is ', pDoc_No, ', Voucher No Is ', pVouch_No);
    END IF;

    IF (pError_No = 0 AND pMode = 2) THEN
        SET pTrading_Id = pDamage_Id;
        SET pTxn_Id = pVouch_Id;

        UPDATE trans_trading
           SET Invoice_Date = pDmg_Date,
               Invoice_Amt = pNet_Amt,
               Taxable_Amt = pNet_Amt,
               Net_Amt = pNet_Amt,
               Remarks = pParticulars,
               Tranding_Type = pTradingType
         WHERE Trading_Id = pTrading_Id;

        UPDATE trans_voucher_master
           SET Vou_Date = pDmg_Date,
               Particulars = IFNULL(NULLIF(TRIM(pParticulars), ''), Particulars),
               Trans_Type = pVouTransType,
               Vou_Type = 4,
               Vou_Mode = 3
         WHERE Voucher_Id = pTxn_Id;

        DELETE FROM trans_trading_products WHERE Trading_Id = pTrading_Id;
        DELETE FROM trns_stockinout WHERE Type_Id = pTrading_Id;
        DELETE FROM trans_voucher_details WHERE Voucher_Id = pTxn_Id;

        INSERT INTO trans_voucher_details (Voucher_Id, Trans_Type, GlHead_Id, Vou_Amount)
        VALUES (pTxn_Id, 'D', pAdjLedg, pNet_Amt);
        INSERT INTO trans_voucher_details (Voucher_Id, Trans_Type, GlHead_Id, Vou_Amount)
        VALUES (pTxn_Id, 'C', pStockLedg, pNet_Amt);

        INSERT INTO trans_trading_products (
            Trading_Id, Prod_Id, Item_Qty, Item_Rate, Unit_Id, Item_Total, Taxable_Amt, Net_Amt
        )
        SELECT pTrading_Id, item_id, qnty, rate, unit, tot_amt, tot_amt, tot_amt FROM tempdamage;

        INSERT INTO trns_stockinout (
            Branch_Id, Stock_Type, Type_Id, Type_Nature, InOut_Date, Prod_Id, Unit_Id,
            Quantity, Rate, Total_Amount, Stock_At
        )
        SELECT pBranch_Id, pStockType, pTrading_Id, 'D', pDmg_Date, item_id, unit,
               qnty, rate, tot_amt, 5
          FROM tempdamage;

        SET pError_Message = 'Damage / Wastage Updated Successfully !!';
    END IF;

    SELECT pError_No AS Error_No, pError_Message AS Message;
    DROP TEMPORARY TABLE IF EXISTS tempdamage;
END ;;
DELIMITER ;
/*!50003 SET sql_mode              = @saved_sql_mode */ ;
/*!50003 SET character_set_client  = @saved_cs_client */ ;
/*!50003 SET character_set_results = @saved_cs_results */ ;
/*!50003 SET collation_connection  = @saved_col_connection */ ;
/*!50003 DROP PROCEDURE IF EXISTS `USP_ADD_EDIT_GENERAL_VOUCHER` */;
/*!50003 SET @saved_cs_client      = @@character_set_client */ ;
/*!50003 SET @saved_cs_results     = @@character_set_results */ ;
/*!50003 SET @saved_col_connection = @@collation_connection */ ;
/*!50003 SET character_set_client  = utf8mb4 */ ;
/*!50003 SET character_set_results = utf8mb4 */ ;
/*!50003 SET collation_connection  = utf8mb4_0900_ai_ci */ ;
/*!50003 SET @saved_sql_mode       = @@sql_mode */ ;
/*!50003 SET sql_mode              = 'ONLY_FULL_GROUP_BY,STRICT_TRANS_TABLES,NO_ZERO_IN_DATE,NO_ZERO_DATE,ERROR_FOR_DIVISION_BY_ZERO,NO_ENGINE_SUBSTITUTION' */ ;
DELIMITER ;;
CREATE DEFINER=`priority`@`%` PROCEDURE `USP_ADD_EDIT_GENERAL_VOUCHER`(
    pVouch_Id       BIGINT,
    pVou_Date       DATE,
    pVou_Type       TINYINT,
    pVou_Mode       TINYINT,
    pLedger_Id      INT,
    pAmount         NUMERIC(18,2),
    pParticulars    VARCHAR(200),
    pRef_Vou_No     VARCHAR(50),
    pBank_Ledg      INT,
    pUser_Id        SMALLINT,
    pFin_Id         INT,
    pBranch_Id      SMALLINT,
    pMode           INT
)
BEGIN
    DECLARE pError_No       INT;
    DECLARE pError_Message  VARCHAR(150);
    DECLARE pVouch_No       VARCHAR(100);
    DECLARE pRef_No         VARCHAR(50);
    DECLARE pTrans_Id       BIGINT;
    DECLARE pCash_Bank      INT;

    SET pError_No = 0;
    SET pCash_Bank = (CASE
        WHEN pVou_Mode = 1 THEN (SELECT Cash_Ledg FROM mst_default_ledger)
        ELSE pBank_Ledg
    END);

    IF pMode = 1 THEN
        SET pVouch_No = UDF_GEN_SYS_NO(CASE WHEN pVou_Type = 1 THEN 'REC' ELSE 'PAY' END, pFin_Id, pBranch_Id);
    ELSE
        SET pVouch_No = (SELECT Vou_No FROM trans_voucher_master WHERE Voucher_Id = pVouch_Id);
    END IF;

    IF IFNULL(TRIM(pRef_Vou_No), '') = '' THEN
        SET pRef_No = pVouch_No;
    ELSE
        SET pRef_No = TRIM(pRef_Vou_No);
    END IF;

    IF pMode = 1 THEN
        INSERT INTO trans_voucher_master
            (Year_Id, Vou_Date, Vou_Type, Vou_Mode, Vou_No, Ref_Vou_No, Particulars, Trans_Type, Type_Id, Status, Created_By, Approved_By, Approved_On)
        VALUES
            (pFin_Id, pVou_Date, pVou_Type, pVou_Mode, pVouch_No, pRef_No, pParticulars, 9, 0, 2, pUser_Id, pUser_Id, CURRENT_TIMESTAMP());
        SET pTrans_Id = LAST_INSERT_ID();
    ELSE
        UPDATE trans_voucher_master
        SET Vou_Date = pVou_Date,
            Vou_Type = pVou_Type,
            Vou_Mode = pVou_Mode,
            Ref_Vou_No = pRef_No,
            Particulars = pParticulars
        WHERE Voucher_Id = pVouch_Id;
        DELETE FROM trans_voucher_details WHERE Voucher_Id = pVouch_Id;
        SET pTrans_Id = pVouch_Id;
    END IF;

    IF pVou_Type = 1 THEN
        INSERT INTO trans_voucher_details (Voucher_Id, Trans_Type, GlHead_Id, Vou_Amount)
        VALUES (pTrans_Id, 'D', pCash_Bank, pAmount);
        INSERT INTO trans_voucher_details (Voucher_Id, Trans_Type, GlHead_Id, Vou_Amount)
        VALUES (pTrans_Id, 'C', pLedger_Id, pAmount);
    ELSE
        INSERT INTO trans_voucher_details (Voucher_Id, Trans_Type, GlHead_Id, Vou_Amount)
        VALUES (pTrans_Id, 'D', pLedger_Id, pAmount);
        INSERT INTO trans_voucher_details (Voucher_Id, Trans_Type, GlHead_Id, Vou_Amount)
        VALUES (pTrans_Id, 'C', pCash_Bank, pAmount);
    END IF;

    SET pError_Message = CONCAT(CASE WHEN pMode = 1 THEN 'Voucher saved. No is ' ELSE 'Voucher updated. No is ' END, pVouch_No);
    SELECT pError_No AS Error_No, pError_Message AS Message;
END ;;
DELIMITER ;
/*!50003 SET sql_mode              = @saved_sql_mode */ ;
/*!50003 SET character_set_client  = @saved_cs_client */ ;
/*!50003 SET character_set_results = @saved_cs_results */ ;
/*!50003 SET collation_connection  = @saved_col_connection */ ;
/*!50003 DROP PROCEDURE IF EXISTS `USP_ADD_EDIT_GST_CODE` */;
/*!50003 SET @saved_cs_client      = @@character_set_client */ ;
/*!50003 SET @saved_cs_results     = @@character_set_results */ ;
/*!50003 SET @saved_col_connection = @@collation_connection */ ;
/*!50003 SET character_set_client  = utf8mb4 */ ;
/*!50003 SET character_set_results = utf8mb4 */ ;
/*!50003 SET collation_connection  = utf8mb4_0900_ai_ci */ ;
/*!50003 SET @saved_sql_mode       = @@sql_mode */ ;
/*!50003 SET sql_mode              = 'ONLY_FULL_GROUP_BY,STRICT_TRANS_TABLES,NO_ZERO_IN_DATE,NO_ZERO_DATE,ERROR_FOR_DIVISION_BY_ZERO,NO_ENGINE_SUBSTITUTION' */ ;
DELIMITER ;;
CREATE DEFINER=`priority`@`%` PROCEDURE `USP_ADD_EDIT_GST_CODE`(
    IN p_Id          INT,
    IN p_GstCode     VARCHAR(20),
    IN p_Category    VARCHAR(50),
    IN p_TaxPercent  DECIMAL(5,2),
    IN p_Sgst        DECIMAL(5,2),
    IN p_Cgst        DECIMAL(5,2),
    IN p_Igst        DECIMAL(5,2),
    IN p_Ugst        DECIMAL(5,2),
    IN p_IsActive    TINYINT,
    IN p_UserId      INT,
    IN p_Mode        TINYINT
)
BEGIN
    DECLARE pError_No      INT DEFAULT 0;
    DECLARE pError_Message VARCHAR(100) DEFAULT '';

    IF p_Mode = 1 THEN
        IF EXISTS(SELECT 1 FROM mst_gst_codes WHERE Gst_Code = p_GstCode) THEN
            SET pError_No = -1;
            SET pError_Message = 'GST Code already exists';
        END IF;
        IF pError_No = 0 THEN
            INSERT INTO mst_gst_codes(Gst_Code, Category, Tax_Percent, Sgst, Cgst, Igst, Ugst, Is_Active, Created_By, Created_On)
            VALUES(p_GstCode, p_Category, p_TaxPercent, p_Sgst, p_Cgst, p_Igst, p_Ugst, p_IsActive, p_UserId, NOW());
        END IF;
    ELSEIF p_Mode = 2 THEN
        IF EXISTS(SELECT 1 FROM mst_gst_codes WHERE Gst_Code = p_GstCode AND Id != p_Id) THEN
            SET pError_No = -1;
            SET pError_Message = 'GST Code already exists';
        END IF;
        IF pError_No = 0 THEN
            UPDATE mst_gst_codes
            SET Gst_Code = p_GstCode, Category = p_Category, Tax_Percent = p_TaxPercent,
                Sgst = p_Sgst, Cgst = p_Cgst, Igst = p_Igst, Ugst = p_Ugst, Is_Active = p_IsActive
            WHERE Id = p_Id;
        END IF;
    END IF;

    SELECT pError_No AS Error_No, pError_Message AS Message;
END ;;
DELIMITER ;
/*!50003 SET sql_mode              = @saved_sql_mode */ ;
/*!50003 SET character_set_client  = @saved_cs_client */ ;
/*!50003 SET character_set_results = @saved_cs_results */ ;
/*!50003 SET collation_connection  = @saved_col_connection */ ;
/*!50003 DROP PROCEDURE IF EXISTS `USP_ADD_EDIT_MEMBER` */;
/*!50003 SET @saved_cs_client      = @@character_set_client */ ;
/*!50003 SET @saved_cs_results     = @@character_set_results */ ;
/*!50003 SET @saved_col_connection = @@collation_connection */ ;
/*!50003 SET character_set_client  = utf8mb4 */ ;
/*!50003 SET character_set_results = utf8mb4 */ ;
/*!50003 SET collation_connection  = utf8mb4_0900_ai_ci */ ;
/*!50003 SET @saved_sql_mode       = @@sql_mode */ ;
/*!50003 SET sql_mode              = 'ONLY_FULL_GROUP_BY,STRICT_TRANS_TABLES,NO_ZERO_IN_DATE,NO_ZERO_DATE,ERROR_FOR_DIVISION_BY_ZERO,NO_ENGINE_SUBSTITUTION' */ ;
DELIMITER ;;
CREATE DEFINER=`priority`@`%` PROCEDURE `USP_ADD_EDIT_MEMBER`(
    pMem_Id         INT,
    pMem_Type       TINYINT,
    pMem_Name       VARCHAR(200),
    pGur_Name       VARCHAR(150),
    pAddress        VARCHAR(255),
    pMob_No         VARCHAR(20),
    pAdhar_No       VARCHAR(25),
    pVoter_No       VARCHAR(25),
    pPan_No         VARCHAR(25),
    pAdm_Date       DATE,
    pAdm_Fees       NUMERIC(12,2),
    pShare_No       VARCHAR(25),
    pShare_Amt      NUMERIC(12,2),
    pUser_Id        SMALLINT,
    pTrans_Mode     SMALLINT,
    pBank_Id        SMALLINT,
    pRef_Mem_No     VARCHAR(25),
    pRate_Share     NUMERIC(10,2),
    pTot_Amt        NUMERIC(12,2),
    pRef_Vou_No     VARCHAR(50),
    pBank_Remarks   VARCHAR(100),
    pYear_Id        INT,
    pBranch_Id      SMALLINT,
    pMode           SMALLINT
)
BEGIN
    DECLARE pExists_Count   SMALLINT;
    DECLARE pError_No       INT DEFAULT 0;
    DECLARE pError_Message  VARCHAR(150);
    DECLARE pMember_Id      INT;
    DECLARE pMember_Code    VARCHAR(25);
    DECLARE pVouch_No       VARCHAR(100);
    DECLARE pRef_No         VARCHAR(50);
    DECLARE pTrans_Id       BIGINT;
    DECLARE pDr_Ledg        INT;
    DECLARE pAdm_Ledg       INT;
    DECLARE pShare_LedgId   INT;
    DECLARE pVou_Type       TINYINT;

    IF (pMode = 1) THEN

        SET pMember_Code = CONCAT('M', LPAD(
            (SELECT IFNULL(MAX(Member_Id), 0) + 1 FROM mst_member),
            4, '0'
        ));

        IF (IFNULL(TRIM(pAdhar_No), '') <> '') THEN
            SET pExists_Count = (SELECT COUNT(*) FROM mst_member WHERE Aadhar_No = pAdhar_No);
            IF (pExists_Count > 0) THEN
                SET pError_No = -1;
                SET pError_Message = 'Same Member Already Exists !!';
            END IF;
        END IF;

        IF (pError_No = 0 AND pTrans_Mode = 2 AND IFNULL(pBank_Id, 0) = 0) THEN
            SET pError_No = -1;
            SET pError_Message = 'Please select a bank';
        END IF;

        IF (pError_No = 0) THEN
            SELECT Cash_Ledg, Adm_Fees_Ledg, Share_Ledg
              INTO pDr_Ledg, pAdm_Ledg, pShare_LedgId
              FROM mst_default_ledger
             LIMIT 1;

            IF (pTrans_Mode = 2) THEN
                SET pDr_Ledg = pBank_Id;
            END IF;

            IF (IFNULL(pDr_Ledg, 0) = 0) THEN
                SET pError_No = -2;
                SET pError_Message = 'Cash/Bank ledger not found';
            ELSEIF (IFNULL(pAdm_Ledg, 0) = 0 OR IFNULL(pShare_LedgId, 0) = 0) THEN
                SET pError_No = -2;
                SET pError_Message = 'Admission Fees / Share ledger not set in default ledger';
            END IF;
        END IF;

        IF (pError_No = 0) THEN
            INSERT INTO mst_member (
                Member_Type, Admission_Date, Member_Name, Guardian_Name, Address,
                Contact_No, Aadhar_No, Voter_No, Pan_No, Adm_Fees, Share_No, Share_Amount,
                Status_Cd, Adm_By, Member_Code, Ref_Member_No, Rate_Per_Share, Total_Amount
            ) VALUES (
                pMem_Type, pAdm_Date, pMem_Name, pGur_Name, pAddress,
                pMob_No, pAdhar_No, pVoter_No, pPan_No, pAdm_Fees, pShare_No, pShare_Amt,
                1, pUser_Id, pMember_Code, pRef_Mem_No, pRate_Share, pTot_Amt
            );
            SET pMember_Id = LAST_INSERT_ID();

            SET pVou_Type = CASE WHEN pTrans_Mode = 1 THEN 1 ELSE 4 END;
            SET pVouch_No = UDF_GEN_SYS_NO(CASE WHEN pTrans_Mode = 1 THEN 'REC' ELSE 'JOU' END, pYear_Id, pBranch_Id);

            IF (pVouch_No IS NULL) THEN
                SET pError_No = -3;
                SET pError_Message = 'Voucher number is not generated';
            ELSE
                IF IFNULL(TRIM(pRef_Vou_No), '') = '' THEN
                    SET pRef_No = pVouch_No;
                ELSE
                    SET pRef_No = TRIM(pRef_Vou_No);
                END IF;

                INSERT INTO trans_voucher_master (
                    Year_Id, Vou_Date, Vou_Type, Vou_Mode, Vou_No, Ref_Vou_No, Particulars,
                    Trans_Type, Type_Id, Status, Created_By, Approved_By, Approved_On
                ) VALUES (
                    pYear_Id, pAdm_Date, pVou_Type, pTrans_Mode, pVouch_No, pRef_No,
                    CONCAT('Member Admission - ', pMem_Name, IF(IFNULL(TRIM(pBank_Remarks), '') = '', '', CONCAT(' | ', pBank_Remarks))),
                    9, pMember_Id, 2, pUser_Id, pUser_Id, CURRENT_TIMESTAMP()
                );
                SET pTrans_Id = LAST_INSERT_ID();

                INSERT INTO trans_voucher_details (Voucher_Id, Trans_Type, GlHead_Id, Vou_Amount)
                VALUES (pTrans_Id, 'D', pDr_Ledg, pTot_Amt);

                IF (IFNULL(pAdm_Fees, 0) <> 0) THEN
                    INSERT INTO trans_voucher_details (Voucher_Id, Trans_Type, GlHead_Id, Vou_Amount)
                    VALUES (pTrans_Id, 'C', pAdm_Ledg, pAdm_Fees);
                END IF;

                IF (IFNULL(pShare_Amt, 0) <> 0) THEN
                    INSERT INTO trans_voucher_details (Voucher_Id, Trans_Type, GlHead_Id, Vou_Amount)
                    VALUES (pTrans_Id, 'C', pShare_LedgId, pShare_Amt);
                END IF;

                SET pError_Message = CONCAT('Member added successfully. Voucher No. is ', pVouch_No);
            END IF;
        END IF;

    END IF;

    IF (pMode = 2) THEN
        SET pError_No = 0;
        UPDATE mst_member
           SET Member_Type    = pMem_Type,
               Guardian_Name  = pGur_Name,
               Address        = pAddress,
               Contact_No     = pMob_No,
               Aadhar_No      = pAdhar_No,
               Voter_No       = pVoter_No,
               Pan_No         = pPan_No,
               Ref_Member_No  = pRef_Mem_No,
               Share_No       = pShare_No,
               Share_Amount   = pShare_Amt,
               Rate_Per_Share = pRate_Share,
               Total_Amount   = pTot_Amt
         WHERE Member_Id = pMem_Id;
        SET pError_Message = 'Member updated successfully';
    END IF;

    SELECT pError_No AS Error_No, pError_Message AS Message, pMember_Code AS Member_Code;

END ;;
DELIMITER ;
/*!50003 SET sql_mode              = @saved_sql_mode */ ;
/*!50003 SET character_set_client  = @saved_cs_client */ ;
/*!50003 SET character_set_results = @saved_cs_results */ ;
/*!50003 SET collation_connection  = @saved_col_connection */ ;
/*!50003 DROP PROCEDURE IF EXISTS `USP_ADD_EDIT_PARTY` */;
/*!50003 SET @saved_cs_client      = @@character_set_client */ ;
/*!50003 SET @saved_cs_results     = @@character_set_results */ ;
/*!50003 SET @saved_col_connection = @@collation_connection */ ;
/*!50003 SET character_set_client  = utf8mb4 */ ;
/*!50003 SET character_set_results = utf8mb4 */ ;
/*!50003 SET collation_connection  = utf8mb4_0900_ai_ci */ ;
/*!50003 SET @saved_sql_mode       = @@sql_mode */ ;
/*!50003 SET sql_mode              = 'ONLY_FULL_GROUP_BY,STRICT_TRANS_TABLES,NO_ZERO_IN_DATE,NO_ZERO_DATE,ERROR_FOR_DIVISION_BY_ZERO,NO_ENGINE_SUBSTITUTION' */ ;
DELIMITER ;;
CREATE DEFINER=`priority`@`%` PROCEDURE `USP_ADD_EDIT_PARTY`(
    pParty_Id           Int,
    pParty_Code         Varchar(25),
    pParty_Name         Varchar(250),
    pParty_Mob          Varchar(25),
    pParty_Alt_Mob      Varchar(25),
    pParty_Mail         Varchar(50),
    pParty_per_Name     Varchar(150),
    pParty_Desg         Varchar(50),
    pParty_Add1         Varchar(200),
    pParty_Add2         Varchar(200),
    pParty_City         Varchar(50),
    pParty_Dist         Varchar(50),
    pParty_State        Varchar(25),
    pParty_Pin          Varchar(10),
    pParty_Pan          Varchar(25),
    pParty_GST          Varchar(25),
    pGst_State          Varchar(5),
    pStart_Date         Date,
    pCredit_Limit       Numeric(18,2),
    pOpening_Bal        Numeric(18,2),
    pBranch_Id          Int,
    pUser_Id            Int,
    pMode               Int
)
BEGIN
    Declare pExists_Count   Int;
    Declare pError_No       Int;
    Declare pError_Message  Varchar(100);

    If (pMode = 1) Then
        -- Auto-generate Party_Code: S + zero-padded next number
        Set pParty_Code = CONCAT('S', LPAD(
            (SELECT IFNULL(MAX(Party_Id), 0) + 1 FROM mst_party WHERE Party_Type = 1),
            4, '0'
        ));

        Set pExists_Count = (Select Count(*) From mst_party Where Party_Name = pParty_Name);
        If (pExists_Count > 0) Then
            Set pError_No = -2;
            Set pError_Message = 'Same Party Name Already Exists !!';
        Else
            Set pError_No = 0;
        End If;

        If (pError_No = 0) Then
            Insert Into mst_party (Branch_Id, Party_Type, Party_Code, Party_Name, Contact_No, AltContact_No, EMail,
                Contact_Person, Designation, Address_Line1, Address_Line2, City, District, State, PinCode,
                Pan_No, GstIn, State_Cd, Start_Date, Credit_Limit, Opening_Bal, Status_Cd)
            Values (pBranch_Id, 1, pParty_Code, pParty_Name, pParty_Mob, pParty_Alt_Mob, pParty_Mail,
                pParty_per_Name, pParty_Desg, pParty_Add1, pParty_Add2, pParty_City, pParty_Dist,
                pParty_State, pParty_Pin, pParty_Pan, pParty_GST, pGst_State, pStart_Date,
                pCredit_Limit, IFNULL(pOpening_Bal, 0), 1);
        End If;
    End If;

    If (pMode = 2) Then
        Set pError_No = 0;
        Update mst_party Set Party_Name=pParty_Name, Contact_No=pParty_Mob, AltContact_No=pParty_Alt_Mob,
            EMail=pParty_Mail, Contact_Person=pParty_per_Name, Designation=pParty_Desg,
            Address_Line1=pParty_Add1, Address_Line2=pParty_Add2, City=pParty_City, District=pParty_Dist,
            State=pParty_State, PinCode=pParty_Pin, Pan_No=pParty_Pan, GstIn=pParty_GST,
            State_Cd=pGst_State, Credit_Limit=pCredit_Limit, Opening_Bal=IFNULL(pOpening_Bal, Opening_Bal)
        Where Party_Id = pParty_Id;
    End If;

    Select pError_No As Error_No, pError_Message As Message, pParty_Code As Party_Code;
END ;;
DELIMITER ;
/*!50003 SET sql_mode              = @saved_sql_mode */ ;
/*!50003 SET character_set_client  = @saved_cs_client */ ;
/*!50003 SET character_set_results = @saved_cs_results */ ;
/*!50003 SET collation_connection  = @saved_col_connection */ ;
/*!50003 DROP PROCEDURE IF EXISTS `USP_ADD_EDIT_PARTY_VOUCHER` */;
/*!50003 SET @saved_cs_client      = @@character_set_client */ ;
/*!50003 SET @saved_cs_results     = @@character_set_results */ ;
/*!50003 SET @saved_col_connection = @@collation_connection */ ;
/*!50003 SET character_set_client  = utf8mb4 */ ;
/*!50003 SET character_set_results = utf8mb4 */ ;
/*!50003 SET collation_connection  = utf8mb4_0900_ai_ci */ ;
/*!50003 SET @saved_sql_mode       = @@sql_mode */ ;
/*!50003 SET sql_mode              = 'ONLY_FULL_GROUP_BY,STRICT_TRANS_TABLES,NO_ZERO_IN_DATE,NO_ZERO_DATE,ERROR_FOR_DIVISION_BY_ZERO,NO_ENGINE_SUBSTITUTION' */ ;
DELIMITER ;;
CREATE DEFINER=`priority`@`%` PROCEDURE `USP_ADD_EDIT_PARTY_VOUCHER`(
    pVouch_Id       BIGINT,
    pVou_Date       DATE,
    pParty_Type     TINYINT,     -- 1 Supplier, 2 Customer
    pParty_Id       INT,
    pVou_Mode       TINYINT,     -- 1 Cash, 2 Bank
    pAmount         NUMERIC(18,2),
    pParticulars    VARCHAR(200),
    pRef_Vou_No     VARCHAR(50),
    pBank_Ledg      INT,
    pUser_Id        SMALLINT,
    pFin_Id         INT,
    pBranch_Id      SMALLINT,
    pMode           INT
)
BEGIN
    DECLARE pError_No       INT;
    DECLARE pError_Message  VARCHAR(150);
    DECLARE pVouch_No       VARCHAR(100);
    DECLARE pRef_No         VARCHAR(50);
    DECLARE pTrans_Id       BIGINT;
    DECLARE pCash_Bank      INT;
    DECLARE pParty_Ledg     INT;
    DECLARE pVou_Type       TINYINT;
    DECLARE pDb_Type        TINYINT;
    DECLARE pParty_Side     CHAR(1);
    SET pError_No = 0;
    IF pParty_Type NOT IN (1, 2) THEN
        SET pError_No = -1;
        SET pError_Message = 'Invalid party type';
    END IF;
    IF pError_No = 0 AND pVou_Mode NOT IN (1, 2) THEN
        SET pError_No = -1;
        SET pError_Message = 'Only Cash and Bank are allowed';
    END IF;
    IF pError_No = 0 AND IFNULL(pAmount, 0) <= 0 THEN
        SET pError_No = -1;
        SET pError_Message = 'Amount must be greater than 0';
    END IF;
    IF pError_No = 0 AND IFNULL(pParty_Id, 0) = 0 THEN
        SET pError_No = -1;
        SET pError_Message = 'Please select a party';
    END IF;
    IF pError_No = 0 AND pVou_Mode = 2 AND IFNULL(pBank_Ledg, 0) = 0 THEN
        SET pError_No = -1;
        SET pError_Message = 'Please select a Bank';
    END IF;
    IF pError_No = 0 THEN
        SET pDb_Type = (SELECT Party_Type FROM mst_party WHERE Party_Id = pParty_Id);
        IF pDb_Type IS NULL OR pDb_Type <> pParty_Type THEN
            SET pError_No = -1;
            SET pError_Message = 'Invalid supplier/customer';
        END IF;
    END IF;
    IF pError_No = 0 THEN
        SET pParty_Ledg = (CASE
            WHEN pParty_Type = 1 THEN (SELECT Credit_Ledger FROM mst_default_ledger)
            WHEN pParty_Type = 2 THEN (SELECT Debit_Ledger FROM mst_default_ledger)
        END);
        SET pCash_Bank = (CASE
            WHEN pVou_Mode = 1 THEN (SELECT Cash_Ledg FROM mst_default_ledger)
            WHEN pVou_Mode = 2 THEN pBank_Ledg
        END);
        IF IFNULL(pParty_Ledg, 0) = 0 THEN
            SET pError_No = -1;
            SET pError_Message = 'Default party ledger not found';
        ELSEIF IFNULL(pCash_Bank, 0) = 0 THEN
            SET pError_No = -1;
            SET pError_Message = 'Cash/Bank ledger not found';
        END IF;
    END IF;
    IF pError_No = 0 THEN
        -- Supplier = Payment, Customer = Receipt
        SET pVou_Type = CASE WHEN pParty_Type = 1 THEN 2 ELSE 1 END;
        SET pParty_Side = CASE WHEN pParty_Type = 1 THEN 'D' ELSE 'C' END;
    END IF;
    IF pError_No = 0 AND pMode = 1 THEN
        SET pVouch_No = UDF_GEN_SYS_NO(
            CASE WHEN pVou_Type = 1 THEN 'REC' ELSE 'PAY' END,
            pFin_Id,
            pBranch_Id
        );
        IF pVouch_No IS NULL THEN
            SET pError_No = -2;
            SET pError_Message = 'Voucher number is not generated';
        END IF;
    END IF;
    IF pError_No = 0 AND pMode = 2 THEN
        SET pVouch_No = (SELECT Vou_No FROM trans_voucher_master WHERE Voucher_Id = pVouch_Id AND Trans_Type = 9);
        IF pVouch_No IS NULL THEN
            SET pError_No = -3;
            SET pError_Message = 'Voucher not found';
        END IF;
    END IF;
    IF pError_No = 0 THEN
        IF IFNULL(TRIM(pRef_Vou_No), '') = '' THEN
            SET pRef_No = pVouch_No;
        ELSE
            SET pRef_No = TRIM(pRef_Vou_No);
        END IF;
    END IF;
    IF pError_No = 0 AND pMode = 1 THEN
        INSERT INTO trans_voucher_master
            (Year_Id, Vou_Date, Vou_Type, Vou_Mode, Vou_No, Ref_Vou_No, Particulars, Trans_Type, Type_Id, Status, Created_By, Approved_By, Approved_On)
        VALUES
            (pFin_Id, pVou_Date, pVou_Type, pVou_Mode, pVouch_No, pRef_No, pParticulars, 9, pParty_Id, 2, pUser_Id, pUser_Id, CURRENT_TIMESTAMP());
        SET pTrans_Id = LAST_INSERT_ID();
    END IF;
    IF pError_No = 0 AND pMode = 2 THEN
        UPDATE trans_voucher_master
        SET Vou_Date = pVou_Date,
            Vou_Type = pVou_Type,
            Vou_Mode = pVou_Mode,
            Ref_Vou_No = pRef_No,
            Particulars = pParticulars,
            Type_Id = pParty_Id
        WHERE Voucher_Id = pVouch_Id;
        DELETE FROM trans_voucher_details WHERE Voucher_Id = pVouch_Id;
        DELETE FROM trn_party_trans WHERE Txn_Id = pVouch_Id;
        SET pTrans_Id = pVouch_Id;
    END IF;
    IF pError_No = 0 THEN
        IF pVou_Type = 1 THEN
            INSERT INTO trans_voucher_details (Voucher_Id, Trans_Type, GlHead_Id, SubLedger_Id, Vou_Amount)
            VALUES (pTrans_Id, 'D', pCash_Bank, NULL, pAmount);
            INSERT INTO trans_voucher_details (Voucher_Id, Trans_Type, GlHead_Id, SubLedger_Id, Vou_Amount)
            VALUES (pTrans_Id, 'C', pParty_Ledg, pParty_Id, pAmount);
        ELSE
            INSERT INTO trans_voucher_details (Voucher_Id, Trans_Type, GlHead_Id, SubLedger_Id, Vou_Amount)
            VALUES (pTrans_Id, 'D', pParty_Ledg, pParty_Id, pAmount);
            INSERT INTO trans_voucher_details (Voucher_Id, Trans_Type, GlHead_Id, SubLedger_Id, Vou_Amount)
            VALUES (pTrans_Id, 'C', pCash_Bank, NULL, pAmount);
        END IF;
        INSERT INTO trn_party_trans (Party_Id, Trans_Date, Trans_Type, Amount, Txn_Id, Remarks, Trans_Mode, Created_By)
        VALUES (pParty_Id, pVou_Date, pParty_Side, pAmount, pTrans_Id, pParticulars, pVou_Mode, pUser_Id);
        SET pError_Message = CONCAT(
            CASE WHEN pMode = 1 THEN 'Voucher saved. No is ' ELSE 'Voucher updated. No is ' END,
            pVouch_No
        );
    END IF;
    SELECT pError_No AS Error_No, pError_Message AS Message;
END ;;
DELIMITER ;
/*!50003 SET sql_mode              = @saved_sql_mode */ ;
/*!50003 SET character_set_client  = @saved_cs_client */ ;
/*!50003 SET character_set_results = @saved_cs_results */ ;
/*!50003 SET collation_connection  = @saved_col_connection */ ;
/*!50003 DROP PROCEDURE IF EXISTS `USP_ADD_EDIT_PRODUCT` */;
/*!50003 SET @saved_cs_client      = @@character_set_client */ ;
/*!50003 SET @saved_cs_results     = @@character_set_results */ ;
/*!50003 SET @saved_col_connection = @@collation_connection */ ;
/*!50003 SET character_set_client  = utf8mb4 */ ;
/*!50003 SET character_set_results = utf8mb4 */ ;
/*!50003 SET collation_connection  = utf8mb4_unicode_ci */ ;
/*!50003 SET @saved_sql_mode       = @@sql_mode */ ;
/*!50003 SET sql_mode              = 'ONLY_FULL_GROUP_BY,STRICT_TRANS_TABLES,NO_ZERO_IN_DATE,NO_ZERO_DATE,ERROR_FOR_DIVISION_BY_ZERO,NO_ENGINE_SUBSTITUTION' */ ;
DELIMITER ;;
CREATE DEFINER=`priority`@`%` PROCEDURE `USP_ADD_EDIT_PRODUCT`(
    pProd_Id        INT,
    pProd_Code      VARCHAR(25),
    pProd_Name      VARCHAR(100),
    pPrint_Name     VARCHAR(50),
    pUnit_Id        SMALLINT,
    pCate_Id        SMALLINT,
    pSub_Cat        SMALLINT,
    pGst_Id         SMALLINT,
    pMrp            NUMERIC(10,2),
    pMargin         NUMERIC(6,2),
    pReorder_Qnty   SMALLINT,
    pProd_Life      SMALLINT,
    pMode           SMALLINT
)
BEGIN
    DECLARE pExists_Count   INT;
    DECLARE pError_No       INT;
    DECLARE pError_Message  VARCHAR(100);

    IF (pMode = 1) THEN
        SET pExists_Count = (SELECT COUNT(*) FROM mst_product WHERE Prod_Code = pProd_Code);

        IF (pExists_Count > 0) THEN
            SET pError_No = -1;
            SET pError_Message = 'Same Product Code Already Exists !!';
        ELSE
            SET pError_No = 0;
        END IF;

        IF (pError_No = 0) THEN
            INSERT INTO mst_product (
                Prod_Code, Prod_ShortNm, Prod_PrintNm, Unit_Id, Cate_Id, SubCate_Id,
                Gst_Id, ReOrder_Qty, Prod_Life, Sale_Margin
            ) VALUES (
                pProd_Code, pProd_Name, pPrint_Name, pUnit_Id, pCate_Id, pSub_Cat,
                pGst_Id, pReorder_Qnty, pProd_Life, pMargin
            );
        END IF;
    END IF;

    IF (pMode = 2) THEN
        SET pError_No = 0;
        UPDATE mst_product
           SET Prod_Code    = pProd_Code,
               Prod_ShortNm = pProd_Name,
               Prod_PrintNm = pPrint_Name,
               Unit_Id      = pUnit_Id,
               Cate_Id      = pCate_Id,
               SubCate_Id   = pSub_Cat,
               Gst_Id       = pGst_Id,
               ReOrder_Qty  = pReorder_Qnty,
               Prod_Life    = pProd_Life,
               Sale_Margin  = pMargin
         WHERE Prod_Id = pProd_Id;
    END IF;

    SELECT pError_No AS Error_No, pError_Message AS Message;
END ;;
DELIMITER ;
/*!50003 SET sql_mode              = @saved_sql_mode */ ;
/*!50003 SET character_set_client  = @saved_cs_client */ ;
/*!50003 SET character_set_results = @saved_cs_results */ ;
/*!50003 SET collation_connection  = @saved_col_connection */ ;
/*!50003 DROP PROCEDURE IF EXISTS `USP_ADD_EDIT_PROD_CATEGORY` */;
/*!50003 SET @saved_cs_client      = @@character_set_client */ ;
/*!50003 SET @saved_cs_results     = @@character_set_results */ ;
/*!50003 SET @saved_col_connection = @@collation_connection */ ;
/*!50003 SET character_set_client  = utf8mb4 */ ;
/*!50003 SET character_set_results = utf8mb4 */ ;
/*!50003 SET collation_connection  = utf8mb4_0900_ai_ci */ ;
/*!50003 SET @saved_sql_mode       = @@sql_mode */ ;
/*!50003 SET sql_mode              = 'ONLY_FULL_GROUP_BY,STRICT_TRANS_TABLES,NO_ZERO_IN_DATE,NO_ZERO_DATE,ERROR_FOR_DIVISION_BY_ZERO,NO_ENGINE_SUBSTITUTION' */ ;
DELIMITER ;;
CREATE DEFINER=`priority`@`%` PROCEDURE `USP_ADD_EDIT_PROD_CATEGORY`(
    IN p_CateId INT,
    IN p_CateNm VARCHAR(50),
    IN p_IsFmcg TINYINT,
    IN p_AgentComm DECIMAL(5,2),
    IN	pPur_Ledg	Int,
    IN	pSale_Ledg	Int,
    IN p_UserId INT,
    IN p_Mode TINYINT
)
BEGIN
	
    IF p_Mode = 1 THEN
        IF EXISTS(SELECT 1 FROM mst_prod_category WHERE Prd_CateNm = p_CateNm) THEN
            SELECT -1 AS Error_No, 'Category name already exists' AS Message;
        ELSE
            INSERT INTO mst_prod_category(Prd_CateNm, Is_Fmcg, Agent_Comm,Pur_Ledg,Sale_Ledg,Created_By, Created_On)
            VALUES(p_CateNm, p_IsFmcg, p_AgentComm,pPur_Ledg,pSale_Ledg,p_UserId, NOW());
            SELECT 0 AS Error_No, 'Category added successfully' AS Message;
        END IF;
    ELSEIF p_Mode = 2 THEN
        UPDATE mst_prod_category
        SET Prd_CateNm = p_CateNm, Is_Fmcg = p_IsFmcg, Agent_Comm = p_AgentComm,Pur_Ledg=pPur_Ledg,Sale_Ledg=pSale_Ledg
        WHERE Prd_CateId = p_CateId;
        SELECT 0 AS Error_No, 'Category updated successfully' AS Message;
    END IF;
END ;;
DELIMITER ;
/*!50003 SET sql_mode              = @saved_sql_mode */ ;
/*!50003 SET character_set_client  = @saved_cs_client */ ;
/*!50003 SET character_set_results = @saved_cs_results */ ;
/*!50003 SET collation_connection  = @saved_col_connection */ ;
/*!50003 DROP PROCEDURE IF EXISTS `USP_ADD_EDIT_PROD_SUBCATEGORY` */;
/*!50003 SET @saved_cs_client      = @@character_set_client */ ;
/*!50003 SET @saved_cs_results     = @@character_set_results */ ;
/*!50003 SET @saved_col_connection = @@collation_connection */ ;
/*!50003 SET character_set_client  = utf8mb4 */ ;
/*!50003 SET character_set_results = utf8mb4 */ ;
/*!50003 SET collation_connection  = utf8mb4_0900_ai_ci */ ;
/*!50003 SET @saved_sql_mode       = @@sql_mode */ ;
/*!50003 SET sql_mode              = 'ONLY_FULL_GROUP_BY,STRICT_TRANS_TABLES,NO_ZERO_IN_DATE,NO_ZERO_DATE,ERROR_FOR_DIVISION_BY_ZERO,NO_ENGINE_SUBSTITUTION' */ ;
DELIMITER ;;
CREATE DEFINER=`priority`@`%` PROCEDURE `USP_ADD_EDIT_PROD_SUBCATEGORY`(
    IN p_SubCateId INT,
    IN p_SubCateNm VARCHAR(50),
    IN p_CateId INT,
    IN p_UserId INT,
    IN p_Mode TINYINT
)
BEGIN
    IF p_Mode = 1 THEN
        IF EXISTS(SELECT 1 FROM mst_prod_subcategory WHERE Prd_SubCateNm = p_SubCateNm AND Prd_CateId = p_CateId) THEN
            SELECT -1 AS Error_No, 'Sub Category already exists under this category' AS Message;
        ELSE
            INSERT INTO mst_prod_subcategory(Prd_SubCateNm, Prd_CateId, Created_By, Created_On)
            VALUES(p_SubCateNm, p_CateId, p_UserId, NOW());
            SELECT 0 AS Error_No, 'Sub Category added successfully' AS Message;
        END IF;
    ELSEIF p_Mode = 2 THEN
        UPDATE mst_prod_subcategory
        SET Prd_SubCateNm = p_SubCateNm, Prd_CateId = p_CateId
        WHERE Prd_SubCateId = p_SubCateId;
        SELECT 0 AS Error_No, 'Sub Category updated successfully' AS Message;
    END IF;
END ;;
DELIMITER ;
/*!50003 SET sql_mode              = @saved_sql_mode */ ;
/*!50003 SET character_set_client  = @saved_cs_client */ ;
/*!50003 SET character_set_results = @saved_cs_results */ ;
/*!50003 SET collation_connection  = @saved_col_connection */ ;
/*!50003 DROP PROCEDURE IF EXISTS `USP_ADD_EDIT_PURCHASE` */;
/*!50003 SET @saved_cs_client      = @@character_set_client */ ;
/*!50003 SET @saved_cs_results     = @@character_set_results */ ;
/*!50003 SET @saved_col_connection = @@collation_connection */ ;
/*!50003 SET character_set_client  = utf8mb4 */ ;
/*!50003 SET character_set_results = utf8mb4 */ ;
/*!50003 SET collation_connection  = utf8mb4_unicode_ci */ ;
/*!50003 SET @saved_sql_mode       = @@sql_mode */ ;
/*!50003 SET sql_mode              = 'ONLY_FULL_GROUP_BY,STRICT_TRANS_TABLES,NO_ZERO_IN_DATE,NO_ZERO_DATE,ERROR_FOR_DIVISION_BY_ZERO,NO_ENGINE_SUBSTITUTION' */ ;
DELIMITER ;;
CREATE DEFINER=`priority`@`%` PROCEDURE `USP_ADD_EDIT_PURCHASE`(
	pPurchase_Id		bigint,
    pVouch_Id			Bigint,
    pBranch_Id			Smallint,
    pPur_No				Varchar(25),
    pPur_Date			Date,
    pParty_Id			Int,
    pTot_Amt			Numeric(18,2),
    pDisc_Perc			Numeric(5,2),
    pDisc_Amt			Numeric(8,2),
    pFreg_Amt			Numeric(8,2),
    pTaxble_Amt			Numeric(12,2),
    pTot_Gst			Numeric(10,2),
    pRound_Amt			Numeric(5,2),
    pNet_Amt			Numeric(12,2),
    pTrans_Mode			Tinyint,
    pBank_Ledg			Int,
    pUser_Id			Smallint,
    pFin_Id				Int,
    pMode				Int
)
BEGIN

Declare	pError_No			Int;
Declare	pError_Message		Varchar(100);
Declare	pData_Count			Int;
Declare	pPur_Id				Int;
Declare	pNpur_No			Varchar(100);
Declare	pVouch_No			Varchar(100);
Declare	pTxn_Id				Bigint;
Declare	pCr_Ledger			Int;
Declare	pCGST_Ledg			Int;
Declare	pSGST_Ledg			Int;
Declare	pRound_Ledg			Int;
Declare	pDisc_Ledg			Int;
Declare	pFreg_Ledg			Int;
Declare	pParty_Ledg			Int;
Declare pParty_Type			Tinyint;
Declare	pTot_Cgst			Numeric(18,2);
Declare	pTot_Sgst			Numeric(18,2);

Set pParty_Type = (Select Party_Type From mst_party Where Party_Id=pParty_Id);
Set pParty_Ledg = (Case When pParty_Type=1 Then (Select Credit_Ledger From mst_default_ledger) When pParty_Type=2 Then (Select Debit_Ledger From mst_default_ledger) End );
Set pCr_Ledger = (Case When pTrans_Mode=1 Then (Select Cash_Ledg From mst_default_ledger) When pTrans_Mode=2 Then pBank_Ledg When pTrans_Mode=3 Then pParty_Ledg End);


Select CGST_Ledg,SGST_Ledg,Round_Ledg,Discp_Ledg,Freight_Ledg Into pCGST_Ledg,pSGST_Ledg,pRound_Ledg,pDisc_Ledg,pFreg_Ledg From mst_default_ledger;

Set pData_Count = (Select Count(*) From temppurchase);

If(pData_Count=0) Then
	Set pError_No = -1;
    Set pError_Message = 'No Purchase Item Found !!';
Else	
	Set pError_No = 0;
End If;

If(pError_No=0) Then
If(pMode=1) Then
	Set pNpur_No = (UDF_GEN_SYS_NO('PV',pFin_Id,null));
    
    If(pNpur_No Is Null) Then
		Set pError_No = -2;
        Set pError_Message = 'Purchase Number Not Genereated !!';
	Else
		Set pError_No =0;
    End If;
    
    If(pError_No=0) Then
		Set pVouch_No = (UDF_GEN_SYS_NO(Case When pTrans_Mode=1 Then 'PAY' Else  'JOU' End,pFin_Id,pBranch_Id));
        
        If(pVouch_No Is Null) Then
			Set pError_No = -3;
            Set pError_Message = 'voucher Number Is Not Genereated !!';
		Else
			Set pError_No=0;
        End If;
        
    End If;
    
  End If;  
End If;

If(pError_No=0) Then
	If(pMode=1) Then
		
        
        Insert Into trans_voucher_master (Year_Id,Vou_Date,Vou_Type,Vou_Mode,Vou_No,Ref_Vou_No,Particulars,Trans_Type,Type_Id,Status,Created_By,Approved_By,Approved_On)
        Values (pFin_Id,pPur_Date,Case When pTrans_Mode=1 Then 2 Else 4 End,pTrans_Mode,pVouch_No,pNpur_No,Concat('By Purchase ',pPur_No),1,pPur_Id,2,pUser_Id,pUser_Id,Current_timestamp());
        
        
        Set pTxn_Id = LAST_INSERT_ID();
        
        Insert Into trans_trading (Branch_Id,Tranding_Type,Invoice_No,Ref_No,Invoice_Date,Party_Id,Invoice_Amt,Disc_Percent,Disc_Amount,Freight_Amt,Taxable_Amt,GST_Amt,Round_Off,Net_Amt,Status_Cd,Voucher_Id,Created_By) 
		Values (pBranch_Id,2,pNpur_No,pPur_No,pPur_Date,pParty_Id,pTot_Amt,pDisc_Perc,pDisc_Amt,pFreg_Amt,pTaxble_Amt,pTot_Gst,pRound_Amt,pNet_Amt,1,pTxn_Id,pUser_Id);
        
        Set pPur_Id = LAST_INSERT_ID();
        
        Insert Into trans_voucher_details (Voucher_Id,Trans_Type,GlHead_Id,Vou_Amount) Select pTxn_Id,'D',ANY_VALUE(c.Pur_Ledg),Sum(tot_amt) From temppurchase p Join mst_product i On i.Prod_Id=p.item_id Join mst_prod_category c on c.Prd_CateId=i.Cate_Id Group By c.Pur_Ledg;
        
        If(pTot_Gst<>0) Then
			Set pTot_Cgst = Ifnull((Select Sum(cgst_amt) From temppurchase),0);
            Set pTot_Sgst = Ifnull((Select Sum(sgst_amt) From temppurchase),0);
            If(pTot_Cgst<>0) Then
				Insert Into trans_voucher_details (Voucher_Id,Trans_Type,GlHead_Id,Vou_Amount) Values (pTxn_Id,'D',pCGST_Ledg,pTot_Cgst);
            End If;
            
            If(pTot_Sgst<>0) Then
				Insert Into trans_voucher_details (Voucher_Id,Trans_Type,GlHead_Id,Vou_Amount) Values (pTxn_Id,'D',pSGST_Ledg,pTot_Sgst);
            End If;
            
        End If;
        
        If(pRound_Amt<>0) Then
			Insert Into trans_voucher_details (Voucher_Id,Trans_Type,GlHead_Id,Vou_Amount) Values (pTxn_Id,Case When pRound_Amt<0 Then 'C' Else 'D' End ,pRound_Ledg,Case When pRound_Amt<0 Then (pRound_Amt*-1) Else pRound_Amt End);
        End If;
        
        If(pDisc_Amt<>0) Then
			Insert Into trans_voucher_details (Voucher_Id,Trans_Type,GlHead_Id,Vou_Amount) Values (pTxn_Id,'C',pDisc_Ledg,pDisc_Amt);
        End If;
        
        If(pFreg_Amt<>0) Then
			Insert Into trans_voucher_details (Voucher_Id,Trans_Type,GlHead_Id,Vou_Amount) Values (pTxn_Id,'D',pFreg_Ledg,pFreg_Amt);
        End If;
		
        Insert Into trans_voucher_details (Voucher_Id,Trans_Type,GlHead_Id,Vou_Amount) Values (pTxn_Id,'C',pCr_Ledger,pNet_Amt);
        
        Insert Into trans_trading_products (Trading_Id,Prod_Id,Gst_Code,Item_Qty,Item_Rate,Unit_Id,Item_Total,MRP,Disc_Prcnt,Disc_Amt,Taxable_Amt,SGST_Prcnt,SGST_Amt,CGST_Prcnt,CGST_Amt,Net_Amt)
        Select pPur_Id,item_id,hsn_code,qnty,rate,unit,tot_amt,sale_mrp,disc_perc,disc_amt,tax_amt,sgst_perc,sgst_amt,cgst_perc,cgst_amt,net_amt From temppurchase;
        
        Insert Into trans_trading_gst (Trading_Id,HSN_No,Taxable_Amt,SGST_Prcnt,SGST_Amt,CGST_Prcnt,CGST_Amt,Tax_Amount)
			Select pPur_Id,UDF_GET_GST_CODE(ANY_VALUE(hsn_code)),Sum(tax_amt),ANY_VALUE(sgst_perc),SUM(sgst_amt),ANY_VALUE(cgst_perc),SUM(cgst_amt),(SUM(sgst_amt)+SUM(cgst_amt)) From temppurchase Group By hsn_code;
        
      Insert Into trns_stockinout (Branch_Id,Stock_Type,Type_Id,Type_Nature,InOut_Date,Prod_Id,Unit_Id,Quantity,Rate,Total_Amount,MRP,Stock_At,Pack_Date,Expiry_Date)
    Select pBranch_Id,2,pPur_Id,'P',pPur_Date,t.item_id,t.unit,t.qnty,t.rate,t.tot_amt,t.sale_mrp,1,t.Pack_Date,
        Case
            When t.Pack_Date Is Null Then Null
            When Ifnull(p.Prod_Life, 0) <= 0 Then Null
            Else Date_Add(t.Pack_Date, Interval p.Prod_Life Day)
        End
    From temppurchase t
    Left Join mst_product p On p.Prod_Id = t.item_id;

        INSERT INTO trn_party_trans (Party_Id, Trans_Date, Trans_Type, Amount, Txn_Id, Trading_Id, Remarks, Trans_Mode, Created_By)
        VALUES (pParty_Id, pPur_Date, 'C', pNet_Amt, pTxn_Id, pPur_Id, CONCAT('Purchase ', pNpur_No), pTrans_Mode, pUser_Id);
        
        Set pError_Message = Concat('Purchase Entry Successfully Complete No Is ',pNpur_No,' System Generate voucher No Is ',pVouch_No);
        
    End If;
    
    If(pMode=2) Then
		
        Set pData_Count = (Select Count(*) From trns_stockinout Where Type_Id=pPurchase_Id And Barcode Is Not Null);
        
        If(pData_Count>0) Then
			Set pError_No=-2;
            Set pError_Message = 'This Purchase Already Barcode Generated Not Editable !!';
		Else
			Set pError_No=0;
        End If;
        
        If(pError_No=0) Then
        
        Update trans_trading Set Ref_No=pPur_No,Invoice_Date=pPur_Date,Party_Id=pParty_Id,Invoice_Amt=pTot_Amt,Disc_Percent=pDisc_Perc,Disc_Amount=pDisc_Amt,Freight_Amt=pFreg_Amt,Taxable_Amt=pTaxble_Amt,GST_Amt=pTot_Gst,Round_Off=pRound_Amt,Net_Amt=pNet_Amt Where Trading_Id=pPurchase_Id;
        
        Delete From trans_trading_products Where Trading_Id=pPurchase_Id;
        Delete From trans_trading_gst Where Trading_Id=pPurchase_Id;
        Delete From trns_stockinout Where Type_Id=pPurchase_Id;
        DELETE FROM trn_party_trans WHERE Trading_Id = pPurchase_Id;
        
        Delete From trans_voucher_details Where Voucher_Id=pVouch_Id;
        
        Insert Into trans_voucher_details (Voucher_Id,Trans_Type,GlHead_Id,Vou_Amount) Select pVouch_Id,'D',ANY_VALUE(c.Pur_Ledg),Sum(tot_amt) From temppurchase p Join mst_product i On i.Prod_Id=p.item_id Join mst_prod_category c on c.Prd_CateId=i.Cate_Id Group By c.Pur_Ledg;
        
        If(pTot_Gst<>0) Then
			Set pTot_Cgst = Ifnull((Select Sum(cgst_amt) From temppurchase),0);
            Set pTot_Sgst = Ifnull((Select Sum(sgst_amt) From temppurchase),0);
            If(pTot_Cgst<>0) Then
				Insert Into trans_voucher_details (Voucher_Id,Trans_Type,GlHead_Id,Vou_Amount) Values (pVouch_Id,'D',pCGST_Ledg,pTot_Cgst);
            End If;
            
            If(pTot_Sgst<>0) Then
				Insert Into trans_voucher_details (Voucher_Id,Trans_Type,GlHead_Id,Vou_Amount) Values (pVouch_Id,'D',pSGST_Ledg,pTot_Sgst);
            End If;
            
        End If;
        
        If(pRound_Amt<>0) Then
			Insert Into trans_voucher_details (Voucher_Id,Trans_Type,GlHead_Id,Vou_Amount) Values (pVouch_Id,Case When pRound_Amt<0 Then 'C' Else 'D' End ,pRound_Ledg,Case When pRound_Amt<0 Then (pRound_Amt*-1) Else pRound_Amt End);
        End If;
        
        If(pDisc_Amt<>0) Then
			Insert Into trans_voucher_details (Voucher_Id,Trans_Type,GlHead_Id,Vou_Amount) Values (pVouch_Id,'C',pDisc_Ledg,pDisc_Amt);
        End If;
        
        If(pFreg_Amt<>0) Then
			Insert Into trans_voucher_details (Voucher_Id,Trans_Type,GlHead_Id,Vou_Amount) Values (pVouch_Id,'D',pFreg_Ledg,pFreg_Amt);
        End If;
        
        Insert Into trans_voucher_details (Voucher_Id,Trans_Type,GlHead_Id,Vou_Amount) Values (pVouch_Id,'C',pCr_Ledger,pNet_Amt);
        
        Insert Into trans_trading_products (Trading_Id,Prod_Id,Gst_Code,Item_Qty,Item_Rate,Unit_Id,Item_Total,MRP,Disc_Prcnt,Disc_Amt,Taxable_Amt,SGST_Prcnt,SGST_Amt,CGST_Prcnt,CGST_Amt,Net_Amt)
        Select pPurchase_Id,item_id,hsn_code,qnty,rate,unit,tot_amt,sale_mrp,disc_perc,disc_amt,tax_amt,sgst_perc,sgst_amt,cgst_perc,cgst_amt,net_amt From temppurchase;
        
        Insert Into trans_trading_gst (Trading_Id,HSN_No,Taxable_Amt,SGST_Prcnt,SGST_Amt,CGST_Prcnt,CGST_Amt,Tax_Amount)
			Select pPurchase_Id,UDF_GET_GST_CODE(ANY_VALUE(hsn_code)),Sum(tax_amt),ANY_VALUE(sgst_perc),SUM(sgst_amt),ANY_VALUE(cgst_perc),SUM(cgst_amt),(SUM(sgst_amt)+SUM(cgst_amt)) From temppurchase Group By hsn_code;
        
      Insert Into trns_stockinout (Branch_Id,Stock_Type,Type_Id,Type_Nature,InOut_Date,Prod_Id,Unit_Id,Quantity,Rate,Total_Amount,MRP,Stock_At,Pack_Date,Expiry_Date)
    Select pBranch_Id,2,pPurchase_Id,'P',pPur_Date,t.item_id,t.unit,t.qnty,t.rate,t.tot_amt,t.sale_mrp,1,t.Pack_Date,
        Case
            When t.Pack_Date Is Null Then Null
            When Ifnull(p.Prod_Life, 0) <= 0 Then Null
            Else Date_Add(t.Pack_Date, Interval p.Prod_Life Day)
        End
    From temppurchase t
    Left Join mst_product p On p.Prod_Id = t.item_id;

            INSERT INTO trn_party_trans (Party_Id, Trans_Date, Trans_Type, Amount, Txn_Id, Trading_Id, Remarks, Trans_Mode, Created_By)
            VALUES (pParty_Id, pPur_Date, 'C', pNet_Amt, pVouch_Id, pPurchase_Id, CONCAT('Purchase ', IFNULL(pNpur_No, pPur_No)), pTrans_Mode, pUser_Id);
            
            Set pError_Message = 'Purchase Update Is Successfully Completed !!';
            
        End If;
    End If;
    
End If;

Select pError_No As Error_No,pError_Message As Message;

Drop Temporary Table If Exists temppurchase;

END ;;
DELIMITER ;
/*!50003 SET sql_mode              = @saved_sql_mode */ ;
/*!50003 SET character_set_client  = @saved_cs_client */ ;
/*!50003 SET character_set_results = @saved_cs_results */ ;
/*!50003 SET collation_connection  = @saved_col_connection */ ;
/*!50003 DROP PROCEDURE IF EXISTS `USP_ADD_EDIT_PURCHASE_RETURN` */;
/*!50003 SET @saved_cs_client      = @@character_set_client */ ;
/*!50003 SET @saved_cs_results     = @@character_set_results */ ;
/*!50003 SET @saved_col_connection = @@collation_connection */ ;
/*!50003 SET character_set_client  = utf8mb4 */ ;
/*!50003 SET character_set_results = utf8mb4 */ ;
/*!50003 SET collation_connection  = utf8mb4_unicode_ci */ ;
/*!50003 SET @saved_sql_mode       = @@sql_mode */ ;
/*!50003 SET sql_mode              = 'ONLY_FULL_GROUP_BY,STRICT_TRANS_TABLES,NO_ZERO_IN_DATE,NO_ZERO_DATE,ERROR_FOR_DIVISION_BY_ZERO,NO_ENGINE_SUBSTITUTION' */ ;
DELIMITER ;;
CREATE DEFINER=`priority`@`%` PROCEDURE `USP_ADD_EDIT_PURCHASE_RETURN`(
    pPurchase_Id        BIGINT,
    pVouch_Id           BIGINT,
    pBranch_Id          SMALLINT,
    pPur_No             VARCHAR(25),
    pPur_Date           DATE,
    pParty_Id           INT,
    pTot_Amt            NUMERIC(18,2),
    pDisc_Perc          NUMERIC(5,2),
    pDisc_Amt           NUMERIC(8,2),
    pFreg_Amt           NUMERIC(8,2),
    pTaxble_Amt         NUMERIC(12,2),
    pTot_Gst            NUMERIC(10,2),
    pRound_Amt          NUMERIC(5,2),
    pNet_Amt            NUMERIC(12,2),
    pTrans_Mode         TINYINT,
    pBank_Ledg          INT,
    pUser_Id            SMALLINT,
    pFin_Id             INT,
    pMode               INT
)
BEGIN

    DECLARE pError_No           INT;
    DECLARE pError_Message      VARCHAR(100);
    DECLARE pData_Count         INT;
    DECLARE pPur_Id             INT;
    DECLARE pTrans_Id           BIGINT;
    DECLARE pNpur_No            VARCHAR(100);
    DECLARE pVouch_No           VARCHAR(100);
    DECLARE pCr_Ledger          INT;
    DECLARE pCGST_Ledg          INT;
    DECLARE pSGST_Ledg          INT;
    DECLARE pRound_Ledg         INT;
    DECLARE pDisc_Ledg          INT;
    DECLARE pFreg_Ledg          INT;
    DECLARE pParty_Ledg         INT;
    DECLARE pParty_Type         TINYINT;
    DECLARE pTot_Cgst           NUMERIC(18,2);
    DECLARE pTot_Sgst           NUMERIC(18,2);
    DECLARE pTradingType        TINYINT;
    DECLARE pVouTransType       TINYINT;
    DECLARE pStockType          TINYINT;
    DECLARE pStockAt            TINYINT;

    SET pTradingType = (
        SELECT Value_Id
          FROM smartinventory_core.mst_options
         WHERE Option_Id = 6
           AND Value_Name = 'Purchase Return Entry'
         LIMIT 1
    );
    SET pVouTransType = (
        SELECT Value_Id
          FROM smartinventory_core.mst_options
         WHERE Option_Id = 13
           AND Value_Name = 'Purchase Return'
         LIMIT 1
    );
    SET pStockType = (
        SELECT Value_Id
          FROM smartinventory_core.mst_options
         WHERE Option_Id = 7
           AND Value_Name = 'Purchase Return'
         LIMIT 1
    );
    SET pStockAt = (
        SELECT Value_Id
          FROM smartinventory_core.mst_options
         WHERE Option_Id = 8
           AND Value_Name = 'Return'
         LIMIT 1
    );

    SET pTradingType  = IFNULL(pTradingType, 4);
    SET pVouTransType = IFNULL(pVouTransType, 5);
    SET pStockType    = IFNULL(pStockType, 7);
    SET pStockAt      = IFNULL(pStockAt, 4);

    SET pParty_Type = (SELECT Party_Type FROM mst_party WHERE Party_Id = pParty_Id);
    SET pParty_Ledg = (
        CASE
            WHEN pParty_Type = 1 THEN (SELECT Credit_Ledger FROM mst_default_ledger)
            WHEN pParty_Type = 2 THEN (SELECT Debit_Ledger FROM mst_default_ledger)
        END
    );
    SET pCr_Ledger = (
        CASE
            WHEN pTrans_Mode = 1 THEN (SELECT Cash_Ledg FROM mst_default_ledger)
            WHEN pTrans_Mode = 2 THEN pBank_Ledg
            WHEN pTrans_Mode = 3 THEN pParty_Ledg
        END
    );

    SELECT CGST_Ledg, SGST_Ledg, Round_Ledg, Discp_Ledg, Freight_Ledg
      INTO pCGST_Ledg, pSGST_Ledg, pRound_Ledg, pDisc_Ledg, pFreg_Ledg
      FROM mst_default_ledger;

    SET pData_Count = (SELECT COUNT(*) FROM temppurchase);

    IF (pData_Count = 0) THEN
        SET pError_No = -1;
        SET pError_Message = 'No Purchase Item Found !!';
    ELSE
        SET pError_No = 0;
    END IF;

    IF (pError_No = 0) THEN
        IF (pMode = 1) THEN
            SET pNpur_No = (UDF_GEN_SYS_NO('PR', pFin_Id, pBranch_Id));

            IF (pNpur_No IS NULL) THEN
                SET pError_No = -2;
                SET pError_Message = 'Return Number Not Genereated !!';
            ELSE
                SET pError_No = 0;
            END IF;

            IF (pError_No = 0) THEN
                SET pVouch_No = (UDF_GEN_SYS_NO(CASE WHEN pTrans_Mode = 1 THEN 'REC' ELSE 'JOU' END, pFin_Id, pBranch_Id));

                IF (pVouch_No IS NULL) THEN
                    SET pError_No = -3;
                    SET pError_Message = 'voucher Number Is Not Genereated !!';
                ELSE
                    SET pError_No = 0;
                END IF;
            END IF;
        END IF;
    END IF;

    IF (pError_No = 0) THEN
        IF (pMode = 1) THEN

            INSERT INTO trans_voucher_master (
                Year_Id, Vou_Date, Vou_Type, Vou_Mode, Vou_No, Ref_Vou_No, Particulars,
                Trans_Type, Type_Id, Status, Created_By, Approved_By, Approved_On
            )
            VALUES (
                pFin_Id, pPur_Date,
                CASE WHEN pTrans_Mode = 1 THEN 1 ELSE 4 END,
                pTrans_Mode, pVouch_No, pNpur_No, CONCAT('By Purchase Return', pPur_No),
                pVouTransType, pPur_Id, 2, pUser_Id, pUser_Id, CURRENT_TIMESTAMP()
            );

            SET pTrans_Id = LAST_INSERT_ID();

            INSERT INTO trans_voucher_details (Voucher_Id, Trans_Type, GlHead_Id, Vou_Amount)
            VALUES (pTrans_Id, 'D', pCr_Ledger, pNet_Amt);

            INSERT INTO trans_voucher_details (Voucher_Id, Trans_Type, GlHead_Id, Vou_Amount)
            SELECT pTrans_Id, 'C', ANY_VALUE(c.Pur_Ledg), SUM(tot_amt)
              FROM temppurchase p
              JOIN mst_product i ON i.Prod_Id = p.item_id
              JOIN mst_prod_category c ON c.Prd_CateId = i.Cate_Id
             GROUP BY c.Pur_Ledg;

            IF (pTot_Gst <> 0) THEN
                SET pTot_Cgst = IFNULL((SELECT SUM(cgst_amt) FROM temppurchase), 0);
                SET pTot_Sgst = IFNULL((SELECT SUM(sgst_amt) FROM temppurchase), 0);
                IF (pTot_Cgst <> 0) THEN
                    INSERT INTO trans_voucher_details (Voucher_Id, Trans_Type, GlHead_Id, Vou_Amount)
                    VALUES (pTrans_Id, 'C', pCGST_Ledg, pTot_Cgst);
                END IF;
                IF (pTot_Sgst <> 0) THEN
                    INSERT INTO trans_voucher_details (Voucher_Id, Trans_Type, GlHead_Id, Vou_Amount)
                    VALUES (pTrans_Id, 'C', pSGST_Ledg, pTot_Sgst);
                END IF;
            END IF;

            IF (pRound_Amt <> 0) THEN
                INSERT INTO trans_voucher_details (Voucher_Id, Trans_Type, GlHead_Id, Vou_Amount)
                VALUES (
                    pTrans_Id,
                    CASE WHEN pRound_Amt < 0 THEN 'D' ELSE 'C' END,
                    pRound_Ledg,
                    CASE WHEN pRound_Amt < 0 THEN (pRound_Amt * -1) ELSE pRound_Amt END
                );
            END IF;

            IF (pDisc_Amt <> 0) THEN
                INSERT INTO trans_voucher_details (Voucher_Id, Trans_Type, GlHead_Id, Vou_Amount)
                VALUES (pTrans_Id, 'D', pDisc_Ledg, pDisc_Amt);
            END IF;

            IF (pFreg_Amt <> 0) THEN
                INSERT INTO trans_voucher_details (Voucher_Id, Trans_Type, GlHead_Id, Vou_Amount)
                VALUES (pTrans_Id, 'C', pFreg_Ledg, pFreg_Amt);
            END IF;

            INSERT INTO trans_trading (
                Branch_Id, Tranding_Type, Invoice_No, Ref_No, Invoice_Date, Party_Id,
                Invoice_Amt, Disc_Percent, Disc_Amount, Freight_Amt, Taxable_Amt, GST_Amt,
                Round_Off, Net_Amt, Status_Cd, Voucher_Id, Created_By
            )
            VALUES (
                pBranch_Id, pTradingType, pNpur_No, pPur_No, pPur_Date, pParty_Id,
                pTot_Amt, pDisc_Perc, pDisc_Amt, pFreg_Amt, pTaxble_Amt, pTot_Gst,
                pRound_Amt, pNet_Amt, 1, pTrans_Id, pUser_Id
            );

            SET pPur_Id = LAST_INSERT_ID();

            UPDATE trans_voucher_master
               SET Type_Id = pPur_Id,
                   Trans_Type = pVouTransType
             WHERE Voucher_Id = pTrans_Id;

            INSERT INTO trans_trading_products (
                Trading_Id, Prod_Id, Gst_Code, Item_Qty, Item_Rate, Unit_Id, Item_Total,
                MRP, Disc_Prcnt, Disc_Amt, Taxable_Amt, SGST_Prcnt, SGST_Amt, CGST_Prcnt, CGST_Amt, Net_Amt
            )
            SELECT pPur_Id, item_id, hsn_code, qnty, rate, unit, tot_amt,
                   sale_mrp, disc_perc, disc_amt, tax_amt, sgst_perc, sgst_amt, cgst_perc, cgst_amt, net_amt
              FROM temppurchase;

            INSERT INTO trans_trading_gst (
                Trading_Id, HSN_No, Taxable_Amt, SGST_Prcnt, SGST_Amt, CGST_Prcnt, CGST_Amt, Tax_Amount
            )
            SELECT pPur_Id, ANY_VALUE(hsn_code), SUM(tax_amt), ANY_VALUE(sgst_perc), SUM(sgst_amt),
                   ANY_VALUE(cgst_perc), SUM(cgst_amt), (SUM(sgst_amt) + SUM(cgst_amt))
              FROM temppurchase
             GROUP BY hsn_code;

            INSERT INTO trns_stockinout (
                Branch_Id, Stock_Type, Type_Id, Type_Nature, InOut_Date, Prod_Id, Unit_Id,
                Quantity, Rate, Total_Amount, MRP, Stock_At, Pack_Date
            )
            SELECT pBranch_Id, pStockType, pPur_Id, 'R', pPur_Date, item_id, unit,
                   qnty, rate, tot_amt, sale_mrp, pStockAt, Pack_Date
              FROM temppurchase;

            INSERT INTO trn_party_trans (Party_Id, Trans_Date, Trans_Type, Amount, Txn_Id, Trading_Id, Remarks, Trans_Mode, Created_By)
            VALUES (pParty_Id, pPur_Date, 'D', pNet_Amt, pTrans_Id, pPur_Id, CONCAT('Purchase Return ', pNpur_No), pTrans_Mode, pUser_Id);

            SET pError_Message = CONCAT('Purchase Return Is Successful Return No Is ', pNpur_No, ' Adn Voucher No Is ', pVouch_No);

        END IF;

        IF (pMode = 2) THEN

            UPDATE trans_trading
               SET Ref_No = pPur_No,
                   Invoice_Date = pPur_Date,
                   Party_Id = pParty_Id,
                   Invoice_Amt = pTot_Amt,
                   Disc_Percent = pDisc_Perc,
                   Disc_Amount = pDisc_Amt,
                   Freight_Amt = pFreg_Amt,
                   Taxable_Amt = pTaxble_Amt,
                   GST_Amt = pTot_Gst,
                   Round_Off = pRound_Amt,
                   Net_Amt = pNet_Amt,
                   Tranding_Type = pTradingType
             WHERE Trading_Id = pPurchase_Id;

            UPDATE trans_voucher_master
               SET Trans_Type = pVouTransType
             WHERE Voucher_Id = pVouch_Id;

            DELETE FROM trans_trading_products WHERE Trading_Id = pPurchase_Id;
            DELETE FROM trans_trading_gst WHERE Trading_Id = pPurchase_Id;
            DELETE FROM trns_stockinout WHERE Type_Id = pPurchase_Id;
            DELETE FROM trn_party_trans WHERE Trading_Id = pPurchase_Id;
            DELETE FROM trans_voucher_details WHERE Voucher_Id = pVouch_Id;

            INSERT INTO trans_voucher_details (Voucher_Id, Trans_Type, GlHead_Id, Vou_Amount)
            VALUES (pVouch_Id, 'D', pCr_Ledger, pNet_Amt);

            INSERT INTO trans_voucher_details (Voucher_Id, Trans_Type, GlHead_Id, Vou_Amount)
            SELECT pVouch_Id, 'C', ANY_VALUE(c.Pur_Ledg), SUM(tot_amt)
              FROM temppurchase p
              JOIN mst_product i ON i.Prod_Id = p.item_id
              JOIN mst_prod_category c ON c.Prd_CateId = i.Cate_Id
             GROUP BY c.Pur_Ledg;

            IF (pTot_Gst <> 0) THEN
                SET pTot_Cgst = IFNULL((SELECT SUM(cgst_amt) FROM temppurchase), 0);
                SET pTot_Sgst = IFNULL((SELECT SUM(sgst_amt) FROM temppurchase), 0);
                IF (pTot_Cgst <> 0) THEN
                    INSERT INTO trans_voucher_details (Voucher_Id, Trans_Type, GlHead_Id, Vou_Amount)
                    VALUES (pVouch_Id, 'C', pCGST_Ledg, pTot_Cgst);
                END IF;
                IF (pTot_Sgst <> 0) THEN
                    INSERT INTO trans_voucher_details (Voucher_Id, Trans_Type, GlHead_Id, Vou_Amount)
                    VALUES (pVouch_Id, 'C', pSGST_Ledg, pTot_Sgst);
                END IF;
            END IF;

            IF (pRound_Amt <> 0) THEN
                INSERT INTO trans_voucher_details (Voucher_Id, Trans_Type, GlHead_Id, Vou_Amount)
                VALUES (
                    pVouch_Id,
                    CASE WHEN pRound_Amt < 0 THEN 'D' ELSE 'C' END,
                    pRound_Ledg,
                    CASE WHEN pRound_Amt < 0 THEN (pRound_Amt * -1) ELSE pRound_Amt END
                );
            END IF;

            IF (pDisc_Amt <> 0) THEN
                INSERT INTO trans_voucher_details (Voucher_Id, Trans_Type, GlHead_Id, Vou_Amount)
                VALUES (pVouch_Id, 'D', pDisc_Ledg, pDisc_Amt);
            END IF;

            IF (pFreg_Amt <> 0) THEN
                INSERT INTO trans_voucher_details (Voucher_Id, Trans_Type, GlHead_Id, Vou_Amount)
                VALUES (pVouch_Id, 'C', pFreg_Ledg, pFreg_Amt);
            END IF;

            INSERT INTO trans_trading_products (
                Trading_Id, Prod_Id, Gst_Code, Item_Qty, Item_Rate, Unit_Id, Item_Total,
                MRP, Disc_Prcnt, Disc_Amt, Taxable_Amt, SGST_Prcnt, SGST_Amt, CGST_Prcnt, CGST_Amt, Net_Amt
            )
            SELECT pPurchase_Id, item_id, hsn_code, qnty, rate, unit, tot_amt,
                   sale_mrp, disc_perc, disc_amt, tax_amt, sgst_perc, sgst_amt, cgst_perc, cgst_amt, net_amt
              FROM temppurchase;

            INSERT INTO trans_trading_gst (
                Trading_Id, HSN_No, Taxable_Amt, SGST_Prcnt, SGST_Amt, CGST_Prcnt, CGST_Amt, Tax_Amount
            )
            SELECT pPurchase_Id, ANY_VALUE(hsn_code), SUM(tax_amt), ANY_VALUE(sgst_perc), SUM(sgst_amt),
                   ANY_VALUE(cgst_perc), SUM(cgst_amt), (SUM(sgst_amt) + SUM(cgst_amt))
              FROM temppurchase
             GROUP BY hsn_code;

            INSERT INTO trns_stockinout (
                Branch_Id, Stock_Type, Type_Id, Type_Nature, InOut_Date, Prod_Id, Unit_Id,
                Quantity, Rate, Total_Amount, MRP, Stock_At, Pack_Date
            )
            SELECT pBranch_Id, pStockType, pPurchase_Id, 'R', pPur_Date, item_id, unit,
                   qnty, rate, tot_amt, sale_mrp, pStockAt, Pack_Date
              FROM temppurchase;

            INSERT INTO trn_party_trans (Party_Id, Trans_Date, Trans_Type, Amount, Txn_Id, Trading_Id, Remarks, Trans_Mode, Created_By)
            VALUES (pParty_Id, pPur_Date, 'D', pNet_Amt, pVouch_Id, pPurchase_Id, CONCAT('Purchase Return ', IFNULL(pNpur_No, pPur_No)), pTrans_Mode, pUser_Id);

            SET pError_Message = 'Purchase Return Is Successfully Updated !!';

        END IF;
    END IF;

    SELECT pError_No AS Error_No, pError_Message AS Message;

    DROP TEMPORARY TABLE IF EXISTS temppurchase;

END ;;
DELIMITER ;
/*!50003 SET sql_mode              = @saved_sql_mode */ ;
/*!50003 SET character_set_client  = @saved_cs_client */ ;
/*!50003 SET character_set_results = @saved_cs_results */ ;
/*!50003 SET collation_connection  = @saved_col_connection */ ;
/*!50003 DROP PROCEDURE IF EXISTS `USP_ADD_EDIT_SALE` */;
/*!50003 SET @saved_cs_client      = @@character_set_client */ ;
/*!50003 SET @saved_cs_results     = @@character_set_results */ ;
/*!50003 SET @saved_col_connection = @@collation_connection */ ;
/*!50003 SET character_set_client  = utf8mb4 */ ;
/*!50003 SET character_set_results = utf8mb4 */ ;
/*!50003 SET collation_connection  = utf8mb4_unicode_ci */ ;
/*!50003 SET @saved_sql_mode       = @@sql_mode */ ;
/*!50003 SET sql_mode              = 'ONLY_FULL_GROUP_BY,STRICT_TRANS_TABLES,NO_ZERO_IN_DATE,NO_ZERO_DATE,ERROR_FOR_DIVISION_BY_ZERO,NO_ENGINE_SUBSTITUTION' */ ;
DELIMITER ;;
CREATE DEFINER=`priority`@`%` PROCEDURE `USP_ADD_EDIT_SALE`(
	pSale_Id			bigint,
    pVouch_Id			Bigint,
    pBranch_Id			Smallint,
    pSale_Date			Date,
    pSale_No			Varchar(45),
    pParty_Id			Int,
    pTot_Amt			Numeric(18,2),
    pDisc_Amt			Numeric(8,2),
    pTaxble_Amt			Numeric(12,2),
    pTot_Gst			Numeric(10,2),
    pRound_Amt			Numeric(5,2),
    pNet_Amt			Numeric(12,2),
    pTrans_Mode			Tinyint,
    pBank_Ledg			Int,
    pUser_Id			Smallint,
    pFin_Id				Int,
    pMode				Int
)
BEGIN

Declare	pError_No			Int;
Declare	pError_Message		Varchar(100);
Declare	pData_Count			Int;
Declare	pPur_Id				Int;
Declare	pTrans_Id			Int;
Declare	pNpur_No			Varchar(100);
Declare	pGst_Have			Bit;
Declare	pGst_Type			Smallint;
Declare	pBill_Data			JSON;
Declare	pVouch_No			Varchar(100);
Declare	pDr_Ledg			Int;
Declare	pCGST_Ledg			Int;
Declare	pSGST_Ledg			Int;
Declare	pRound_Ledg			Int;
Declare	pDisc_Ledg			Int;
Declare	pFreg_Ledg			Int;
Declare	pParty_Ledg			Int;
Declare pParty_Type			Tinyint;
Declare	pTot_Cgst			Numeric(18,2);
Declare	pTot_Sgst			Numeric(18,2);

Select Is_GST,Gst_Type Into pGst_Have,pGst_Type From smartinventory_core.mst_org_config Where Org_Id=(Select Org_Id From smartinventory_core.mst_org_branch Where Branch_Id=(Select Branch_Id From mst_user Where User_Id=pUser_Id));

Set pData_Count = (Select Count(*) From temppurchase);

Set pParty_Type = (Select Party_Type From mst_party Where Party_Id=pParty_Id);
Set pParty_Ledg = (Case When pParty_Type=1 Then (Select Credit_Ledger From mst_default_ledger) When pParty_Type=2 Then (Select Debit_Ledger From mst_default_ledger) End );
Set pDr_Ledg = (Case When pTrans_Mode=1 Then (Select Cash_Ledg From mst_default_ledger) When pTrans_Mode=2 Then pBank_Ledg When pTrans_Mode=3 Then pParty_Ledg End);

If(pData_Count=0) Then
	Set pError_No = -1;
    Set pError_Message = 'No Sale Item Found !!';
Else	
	Set pError_No = 0;
End If;

If(pError_No=0) Then
If(pMode=1) Then
	Set pNpur_No = (UDF_GEN_SYS_NO('INV',pFin_Id,pBranch_Id));
    
    If(pNpur_No Is Null) Then
		Set pError_No = -2;
        Set pError_Message = 'Sale Number Not Genereated !!';
	Else
		Set pError_No =0;
    End If;
    
    If(pError_No=0) Then
		Set pVouch_No = (UDF_GEN_SYS_NO(Case When pTrans_Mode=1 Then 'REC' Else  'JOU' End,pFin_Id,pBranch_Id));
        
        If(pVouch_No Is Null) Then
			Set pError_No = -3;
            Set pError_Message = 'voucher Number Is Not Genereated !!';
		Else
			Set pError_No=0;
        End If;
    End If;
    
    End If;
End If;

If(pError_No=0) Then
	If(pMode=1) Then
		
        Insert Into trans_voucher_master (Year_Id,Vou_Date,Vou_Type,Vou_Mode,Vou_No,Ref_Vou_No,Particulars,Trans_Type,Type_Id,Status,Created_By,Approved_By,Approved_On)
        Values (pFin_Id,pSale_Date,Case When pTrans_Mode=1 Then 1 Else 4 End,pTrans_Mode,pVouch_No,pNpur_No,Concat('By Counter Sale',pNpur_No),1,pPur_Id,2,pUser_Id,pUser_Id,Current_timestamp());
		
        Set pTrans_Id = LAST_INSERT_ID();
		
        Insert Into trans_voucher_details (Voucher_Id,Trans_Type,GlHead_Id,Vou_Amount) Values (pTrans_Id,'D',pDr_Ledg,pTot_Amt);
        
        Insert Into trans_voucher_details (Voucher_Id,Trans_Type,GlHead_Id,Vou_Amount) Select pTrans_Id,'C',ANY_VALUE(c.Pur_Ledg),Sum(tot_amt) From temppurchase p Join mst_product i On i.Prod_Id=p.item_id Join mst_prod_category c on c.Prd_CateId=i.Cate_Id Group By c.Pur_Ledg;
        
        If(pTot_Gst<>0) Then
			Set pTot_Cgst = Ifnull((Select Sum(cgst_amt) From temppurchase),0);
            Set pTot_Sgst = Ifnull((Select Sum(sgst_amt) From temppurchase),0);
            If(pTot_Cgst<>0) Then
				Insert Into trans_voucher_details (Voucher_Id,Trans_Type,GlHead_Id,Vou_Amount) Values (pTrans_Id,'C',pCGST_Ledg,pTot_Cgst);
            End If;
            
            If(pTot_Sgst<>0) Then
				Insert Into trans_voucher_details (Voucher_Id,Trans_Type,GlHead_Id,Vou_Amount) Values (pTrans_Id,'C',pSGST_Ledg,pTot_Sgst);
            End If;
            
        End If;
        
        If(pRound_Amt<>0) Then
			Insert Into trans_voucher_details (Voucher_Id,Trans_Type,GlHead_Id,Vou_Amount) Values (pTrans_Id,Case When pRound_Amt<0 Then 'C' Else 'D' End ,pRound_Ledg,Case When pRound_Amt<0 Then (pRound_Amt*-1) Else pRound_Amt End);
        End If;
        
         If(pDisc_Amt<>0) Then
			Insert Into trans_voucher_details (Voucher_Id,Trans_Type,GlHead_Id,Vou_Amount) Values (pTrans_Id,'D',pDisc_Ledg,pDisc_Amt);
        End If;
        
		Insert Into trans_trading (Branch_Id,Tranding_Type,Invoice_No,Ref_No,Invoice_Date,Party_Id,Invoice_Amt,Disc_Amount,Taxable_Amt,GST_Amt,Round_Off,Net_Amt,Status_Cd,Voucher_Id,Created_By) 
		Values (pBranch_Id,3,pNpur_No,Case When pSale_No Is Null Then pNpur_No Else pSale_No End,pSale_Date,pParty_Id,pTot_Amt,pDisc_Amt,pTaxble_Amt,pTot_Gst,pRound_Amt,pNet_Amt,1,pTrans_Id,pUser_Id);
        
        Set pPur_Id = LAST_INSERT_ID();
        
        Insert Into trans_trading_products (Trading_Id,Prod_Id,Gst_Code,Item_Qty,Item_Rate,Unit_Id,Item_Total,MRP,Disc_Prcnt,Disc_Amt,Taxable_Amt,SGST_Prcnt,SGST_Amt,CGST_Prcnt,CGST_Amt,Net_Amt)
        Select pPur_Id,item_id,hsn_code,qnty,rate,unit,tot_amt,sale_mrp,disc_perc,disc_amt,tax_amt,sgst_perc,sgst_amt,cgst_perc,cgst_amt,net_amt From temppurchase;
        
        Insert Into trans_trading_gst (Trading_Id,HSN_No,Taxable_Amt,SGST_Prcnt,SGST_Amt,CGST_Prcnt,CGST_Amt,Tax_Amount)
			Select pPur_Id,ANY_VALUE(hsn_code),Sum(tax_amt),ANY_VALUE(sgst_perc),SUM(sgst_amt),ANY_VALUE(cgst_perc),SUM(cgst_amt),(SUM(sgst_amt)+SUM(cgst_amt)) From temppurchase Group By hsn_code;
        
        Insert Into trns_stockinout (Branch_Id,Stock_Type,Type_Id,Type_Nature,InOut_Date,Prod_Id,Unit_Id,Quantity,Rate,Total_Amount)
			Select pBranch_Id,3,pPur_Id,'S',pSale_Date,item_id,unit,qnty,rate,tot_amt From temppurchase;

        INSERT INTO trn_party_trans (Party_Id, Trans_Date, Trans_Type, Amount, Txn_Id, Trading_Id, Remarks, Trans_Mode, Created_By)
        VALUES (pParty_Id, pSale_Date, 'D', pNet_Amt, pTrans_Id, pPur_Id, CONCAT('Counter Sale ', pNpur_No), pTrans_Mode, pUser_Id);
        
        Set pError_Message = Concat('Sale Successfully Saved No Is ',pNpur_No,' Voucher No Is ',pVouch_No);
        
    End If;
    
    If(pMode=2) Then
		
        Update trans_trading Set Invoice_Date=pSale_Date,Party_Id=pParty_Id,Invoice_Amt=pTot_Amt,Disc_Amount=pDisc_Amt,Taxable_Amt=pTaxble_Amt,GST_Amt=pTot_Gst,Round_Off=pRound_Amt,Net_Amt=pNet_Amt Where Trading_Id=pSale_Id;
        
        Delete From trans_trading_products Where Trading_Id=pSale_Id;
        Delete From trans_trading_gst Where Trading_Id=pSale_Id;
        Delete From trns_stockinout Where Type_Id=pSale_Id;
        DELETE FROM trn_party_trans WHERE Trading_Id = pSale_Id;
        
        Delete From trans_voucher_details Where Voucher_Id=pVouch_Id;
        
        Insert Into trans_voucher_details (Voucher_Id,Trans_Type,GlHead_Id,Vou_Amount) Values (pVouch_Id,'D',pDr_Ledg,pTot_Amt);
        
        Insert Into trans_voucher_details (Voucher_Id,Trans_Type,GlHead_Id,Vou_Amount) Select pVouch_Id,'C',ANY_VALUE(c.Pur_Ledg),Sum(tot_amt) From temppurchase p Join mst_product i On i.Prod_Id=p.item_id Join mst_prod_category c on c.Prd_CateId=i.Cate_Id Group By c.Pur_Ledg;
        
        If(pTot_Gst<>0) Then
			Set pTot_Cgst = Ifnull((Select Sum(cgst_amt) From temppurchase),0);
            Set pTot_Sgst = Ifnull((Select Sum(sgst_amt) From temppurchase),0);
            If(pTot_Cgst<>0) Then
				Insert Into trans_voucher_details (Voucher_Id,Trans_Type,GlHead_Id,Vou_Amount) Values (pVouch_Id,'C',pCGST_Ledg,pTot_Cgst);
            End If;
            
            If(pTot_Sgst<>0) Then
				Insert Into trans_voucher_details (Voucher_Id,Trans_Type,GlHead_Id,Vou_Amount) Values (pVouch_Id,'C',pSGST_Ledg,pTot_Sgst);
            End If;
            
        End If;
        
        If(pRound_Amt<>0) Then
			Insert Into trans_voucher_details (Voucher_Id,Trans_Type,GlHead_Id,Vou_Amount) Values (pVouch_Id,Case When pRound_Amt<0 Then 'C' Else 'D' End ,pRound_Ledg,Case When pRound_Amt<0 Then (pRound_Amt*-1) Else pRound_Amt End);
        End If;
        
         If(pDisc_Amt<>0) Then
			Insert Into trans_voucher_details (Voucher_Id,Trans_Type,GlHead_Id,Vou_Amount) Values (pVouch_Id,'D',pDisc_Ledg,pDisc_Amt);
        End If;
        
        Insert Into trans_trading_products (Trading_Id,Prod_Id,Gst_Code,Item_Qty,Item_Rate,Unit_Id,Item_Total,MRP,Disc_Prcnt,Disc_Amt,Taxable_Amt,SGST_Prcnt,SGST_Amt,CGST_Prcnt,CGST_Amt,Net_Amt)
        Select pPur_Id,item_id,hsn_code,qnty,rate,unit,tot_amt,sale_mrp,disc_perc,disc_amt,tax_amt,sgst_perc,sgst_amt,cgst_perc,cgst_amt,net_amt From temppurchase;
        
        Insert Into trans_trading_gst (Trading_Id,HSN_No,Taxable_Amt,SGST_Prcnt,SGST_Amt,CGST_Prcnt,CGST_Amt,Tax_Amount)
			Select pPur_Id,ANY_VALUE(hsn_code),Sum(tax_amt),ANY_VALUE(sgst_perc),SUM(sgst_amt),ANY_VALUE(cgst_perc),SUM(cgst_amt),(SUM(sgst_amt)+SUM(cgst_amt)) From temppurchase Group By hsn_code;
        
        Insert Into trns_stockinout (Branch_Id,Stock_Type,Type_Id,Type_Nature,InOut_Date,Prod_Id,Unit_Id,Quantity,Rate,Total_Amount)
			Select pBranch_Id,3,pPur_Id,'S',pSale_Date,item_id,unit,qnty,rate,tot_amt From temppurchase;

        INSERT INTO trn_party_trans (Party_Id, Trans_Date, Trans_Type, Amount, Txn_Id, Trading_Id, Remarks, Trans_Mode, Created_By)
        VALUES (pParty_Id, pSale_Date, 'D', pNet_Amt, pVouch_Id, pSale_Id, CONCAT('Counter Sale ', IFNULL(pNpur_No, pSale_No)), pTrans_Mode, pUser_Id);
        
    End If;
    
End If;

If(pGst_Have) Then
	If(pGst_Type=2) Then
		SET pBill_Data = (
		SELECT JSON_OBJECT(
			'Invoice_No', m.Invoice_No,
			'Invoice_Date', m.Invoice_Date,
			'Cust_Name', UDF_GET_PARTY_NAME(m.Party_Id),
            'Discount',m.Disc_Amount,
            'Tot_Amount',m.Net_Amt,
			'items',
			(
				SELECT JSON_ARRAYAGG(
					JSON_OBJECT(
						'item_name', Concat(p.Prod_ShortNm,' - ',p.Prod_Code),
						'qty', d.Item_Qty,
						'rate', d.MRP,
						'amount', d.Item_Total
					)
				)
				FROM trans_trading_products d
                Join mst_product p On p.Prod_Id=d.Prod_Id
				WHERE d.Trading_Id = m.Trading_Id
			)
		)
		FROM trans_trading m
		WHERE m.Trading_Id = pPur_Id
	);
		
    End If;
    
    If(pGst_Type=1) Then
		SET pBill_Data = (
    SELECT JSON_OBJECT(
        'Invoice_No', m.Invoice_No,
        'Invoice_Date', m.Invoice_Date,
        'Cust_Name', UDF_GET_PARTY_NAME(m.Party_Id),
        'Discount', m.Disc_Amount,
        'Tot_Amount', m.Net_Amt,

        'items',
        (
            SELECT JSON_ARRAYAGG(
                JSON_OBJECT(
                    'item_name', CONCAT(p.Prod_ShortNm, ' - ', p.Prod_Code),
                    'qty', d.Item_Qty,
                    'rate', d.MRP,
                    'CGST', d.CGST_Amt,
                    'SGST', d.SGST_Amt,
                    'amount', d.Item_Total
                )
            )
            FROM trans_trading_products d
            JOIN mst_product p 
                ON p.Prod_Id = d.Prod_Id
            WHERE d.Trading_Id = m.Trading_Id
        ),

        'GST_Details',
        (
            SELECT JSON_ARRAYAGG(
                JSON_OBJECT(
                    'HSN_No', g.HSN_No,
                    'Taxable_Amt', g.Taxable_Amt,
                    'SGST_Prcnt', g.SGST_Prcnt,
                    'SGST_Amt', g.SGST_Amt,
                    'CGST_Prcnt', g.CGST_Prcnt,
                    'CGST_Amt', g.CGST_Amt,
                    'Tax_Amount', g.Tax_Amount
                )
            )
            FROM (
                SELECT
                    HSN_No,
                    SUM(Taxable_Amt) AS Taxable_Amt,
                    MAX(SGST_Prcnt) AS SGST_Prcnt,
                    SUM(SGST_Amt) AS SGST_Amt,
                    MAX(CGST_Prcnt) AS CGST_Prcnt,
                    SUM(CGST_Amt) AS CGST_Amt,
                    SUM(Tax_Amount) AS Tax_Amount
                FROM trans_trading_gst
                WHERE Trading_Id = m.Trading_Id
                GROUP BY HSN_No
            ) g
        )

    )
    FROM trans_trading m
    WHERE m.Trading_Id = pPur_Id
);
    End If;
    
End If;

Select pError_No As Error_No,pError_Message As Message,pBill_Data As Bill_Data;

Drop Temporary Table If Exists temppurchase;

END ;;
DELIMITER ;
/*!50003 SET sql_mode              = @saved_sql_mode */ ;
/*!50003 SET character_set_client  = @saved_cs_client */ ;
/*!50003 SET character_set_results = @saved_cs_results */ ;
/*!50003 SET collation_connection  = @saved_col_connection */ ;
/*!50003 DROP PROCEDURE IF EXISTS `USP_ADD_EDIT_SALE_RETURN` */;
/*!50003 SET @saved_cs_client      = @@character_set_client */ ;
/*!50003 SET @saved_cs_results     = @@character_set_results */ ;
/*!50003 SET @saved_col_connection = @@collation_connection */ ;
/*!50003 SET character_set_client  = utf8mb4 */ ;
/*!50003 SET character_set_results = utf8mb4 */ ;
/*!50003 SET collation_connection  = utf8mb4_unicode_ci */ ;
/*!50003 SET @saved_sql_mode       = @@sql_mode */ ;
/*!50003 SET sql_mode              = 'ONLY_FULL_GROUP_BY,STRICT_TRANS_TABLES,NO_ZERO_IN_DATE,NO_ZERO_DATE,ERROR_FOR_DIVISION_BY_ZERO,NO_ENGINE_SUBSTITUTION' */ ;
DELIMITER ;;
CREATE DEFINER=`priority`@`%` PROCEDURE `USP_ADD_EDIT_SALE_RETURN`(
    pSale_Id            BIGINT,
    pBranch_Id          SMALLINT,
    pSale_Date          DATE,
    pParty_Id           INT,
    pTot_Amt            NUMERIC(18,2),
    pDisc_Amt           NUMERIC(8,2),
    pTaxble_Amt         NUMERIC(12,2),
    pTot_Gst            NUMERIC(10,2),
    pRound_Amt          NUMERIC(5,2),
    pNet_Amt            NUMERIC(12,2),
    pUser_Id            SMALLINT,
    pFin_Id             INT,
    pMode               INT,
    pRef_No             VARCHAR(45)
)
BEGIN

    DECLARE pError_No           INT;
    DECLARE pError_Message      VARCHAR(100);
    DECLARE pData_Count         INT;
    DECLARE pPur_Id             INT;
    DECLARE pTrans_Id           INT;
    DECLARE pNpur_No            VARCHAR(100);
    DECLARE pOrigRef            VARCHAR(45);
    DECLARE pTradingType        TINYINT;
    DECLARE pStockType          TINYINT;

    SET pTradingType = (
        SELECT Value_Id FROM smartinventory_core.mst_options
         WHERE Option_Id = 6 AND Value_Name = 'Sales Return Entry' LIMIT 1
    );
    SET pStockType = (
        SELECT Value_Id FROM smartinventory_core.mst_options
         WHERE Option_Id = 7 AND Value_Name = 'Sales Return' LIMIT 1
    );
    SET pTradingType = IFNULL(pTradingType, 5);
    SET pStockType   = IFNULL(pStockType, 8);
    SET pOrigRef     = NULLIF(TRIM(IFNULL(pRef_No, '')), '');

    SET pData_Count = (SELECT COUNT(*) FROM temppurchase);

    IF (pData_Count = 0) THEN
        SET pError_No = -1;
        SET pError_Message = 'No Item Found For Return !!';
    ELSE
        SET pError_No = 0;
    END IF;

    IF (pError_No = 0) THEN
        IF (pMode = 1) THEN
            SET pNpur_No = (UDF_GEN_SYS_NO('SR', pFin_Id, pBranch_Id));

            IF (pNpur_No IS NULL) THEN
                SET pError_No = -2;
                SET pError_Message = 'Return Number Not Genereated !!';
            ELSE
                SET pError_No = 0;
            END IF;
        END IF;
    END IF;

    IF (pError_No = 0) THEN
        IF (pMode = 1) THEN
            INSERT INTO trans_trading (
                Branch_Id, Tranding_Type, Invoice_No, Ref_No, Invoice_Date, Party_Id,
                Invoice_Amt, Disc_Amount, Taxable_Amt, GST_Amt, Round_Off, Net_Amt,
                Status_Cd, Voucher_Id, Created_By
            )
            VALUES (
                pBranch_Id, pTradingType, pNpur_No, IFNULL(pOrigRef, pNpur_No), pSale_Date, pParty_Id,
                pTot_Amt, pDisc_Amt, pTaxble_Amt, pTot_Gst, pRound_Amt, pNet_Amt,
                1, pTrans_Id, pUser_Id
            );

            SET pPur_Id = LAST_INSERT_ID();

            INSERT INTO trans_trading_products (
                Trading_Id, Prod_Id, Gst_Code, Item_Qty, Item_Rate, Unit_Id, Item_Total,
                MRP, Disc_Prcnt, Disc_Amt, Taxable_Amt, SGST_Prcnt, SGST_Amt, CGST_Prcnt, CGST_Amt, Net_Amt
            )
            SELECT pPur_Id, item_id, hsn_code, qnty, rate, unit, tot_amt,
                   sale_mrp, disc_perc, disc_amt, tax_amt, sgst_perc, sgst_amt, cgst_perc, cgst_amt, net_amt
              FROM temppurchase;

            INSERT INTO trans_trading_gst (
                Trading_Id, HSN_No, Taxable_Amt, SGST_Prcnt, SGST_Amt, CGST_Prcnt, CGST_Amt, Tax_Amount
            )
            SELECT pPur_Id, ANY_VALUE(hsn_code), SUM(tax_amt), ANY_VALUE(sgst_perc), SUM(sgst_amt),
                   ANY_VALUE(cgst_perc), SUM(cgst_amt), (SUM(sgst_amt) + SUM(cgst_amt))
              FROM temppurchase
             GROUP BY hsn_code;

            INSERT INTO trns_stockinout (
                Branch_Id, Stock_Type, Type_Id, Type_Nature, InOut_Date, Prod_Id, Unit_Id, Quantity, Rate, Total_Amount
            )
            SELECT pBranch_Id, pStockType, pPur_Id, 'R', pSale_Date, item_id, unit, qnty, rate, tot_amt
              FROM temppurchase;

            INSERT INTO trn_party_trans (Party_Id, Trans_Date, Trans_Type, Amount, Txn_Id, Trading_Id, Remarks, Trans_Mode, Created_By)
            VALUES (pParty_Id, pSale_Date, 'C', pNet_Amt, IFNULL(pTrans_Id, pPur_Id), pPur_Id, CONCAT('Sale Return ', pNpur_No), NULL, pUser_Id);

            SET pError_Message = CONCAT('Sale Retun Successfully Saved, No. Is ', pNpur_No);
        END IF;

        IF (pMode = 2) THEN
            UPDATE trans_trading
               SET Invoice_Date = pSale_Date,
                   Party_Id = pParty_Id,
                   Invoice_Amt = pTot_Amt,
                   Disc_Amount = pDisc_Amt,
                   Taxable_Amt = pTaxble_Amt,
                   GST_Amt = pTot_Gst,
                   Round_Off = pRound_Amt,
                   Net_Amt = pNet_Amt,
                   Tranding_Type = pTradingType,
                   Ref_No = IFNULL(pOrigRef, Ref_No)
             WHERE Trading_Id = pSale_Id;

            DELETE FROM trans_trading_products WHERE Trading_Id = pSale_Id;
            DELETE FROM trans_trading_gst WHERE Trading_Id = pSale_Id;
            DELETE FROM trns_stockinout WHERE Type_Id = pSale_Id;
            DELETE FROM trn_party_trans WHERE Trading_Id = pSale_Id;

            INSERT INTO trans_trading_products (
                Trading_Id, Prod_Id, Gst_Code, Item_Qty, Item_Rate, Unit_Id, Item_Total,
                MRP, Disc_Prcnt, Disc_Amt, Taxable_Amt, SGST_Prcnt, SGST_Amt, CGST_Prcnt, CGST_Amt, Net_Amt
            )
            SELECT pSale_Id, item_id, hsn_code, qnty, rate, unit, tot_amt,
                   sale_mrp, disc_perc, disc_amt, tax_amt, sgst_perc, sgst_amt, cgst_perc, cgst_amt, net_amt
              FROM temppurchase;

            INSERT INTO trans_trading_gst (
                Trading_Id, HSN_No, Taxable_Amt, SGST_Prcnt, SGST_Amt, CGST_Prcnt, CGST_Amt, Tax_Amount
            )
            SELECT pSale_Id, ANY_VALUE(hsn_code), SUM(tax_amt), ANY_VALUE(sgst_perc), SUM(sgst_amt),
                   ANY_VALUE(cgst_perc), SUM(cgst_amt), (SUM(sgst_amt) + SUM(cgst_amt))
              FROM temppurchase
             GROUP BY hsn_code;

            INSERT INTO trns_stockinout (
                Branch_Id, Stock_Type, Type_Id, Type_Nature, InOut_Date, Prod_Id, Unit_Id, Quantity, Rate, Total_Amount
            )
            SELECT pBranch_Id, pStockType, pSale_Id, 'R', pSale_Date, item_id, unit, qnty, rate, tot_amt
              FROM temppurchase;

            INSERT INTO trn_party_trans (Party_Id, Trans_Date, Trans_Type, Amount, Txn_Id, Trading_Id, Remarks, Trans_Mode, Created_By)
            VALUES (pParty_Id, pSale_Date, 'C', pNet_Amt, pSale_Id, pSale_Id, CONCAT('Sale Return ', IFNULL(pNpur_No, pOrigRef)), NULL, pUser_Id);

            SET pError_Message = 'Sale Return Is Successfully Updated !!';
        END IF;
    END IF;

    SELECT pError_No AS Error_No, pError_Message AS Message;

    DROP TEMPORARY TABLE IF EXISTS temppurchase;

END ;;
DELIMITER ;
/*!50003 SET sql_mode              = @saved_sql_mode */ ;
/*!50003 SET character_set_client  = @saved_cs_client */ ;
/*!50003 SET character_set_results = @saved_cs_results */ ;
/*!50003 SET collation_connection  = @saved_col_connection */ ;
/*!50003 DROP PROCEDURE IF EXISTS `USP_ADD_EDIT_UNIT` */;
/*!50003 SET @saved_cs_client      = @@character_set_client */ ;
/*!50003 SET @saved_cs_results     = @@character_set_results */ ;
/*!50003 SET @saved_col_connection = @@collation_connection */ ;
/*!50003 SET character_set_client  = utf8mb4 */ ;
/*!50003 SET character_set_results = utf8mb4 */ ;
/*!50003 SET collation_connection  = utf8mb4_0900_ai_ci */ ;
/*!50003 SET @saved_sql_mode       = @@sql_mode */ ;
/*!50003 SET sql_mode              = 'ONLY_FULL_GROUP_BY,STRICT_TRANS_TABLES,NO_ZERO_IN_DATE,NO_ZERO_DATE,ERROR_FOR_DIVISION_BY_ZERO,NO_ENGINE_SUBSTITUTION' */ ;
DELIMITER ;;
CREATE DEFINER=`priority`@`%` PROCEDURE `USP_ADD_EDIT_UNIT`(
    IN p_UnitId INT,
    IN p_UnitName VARCHAR(25),
    IN p_UserId INT,
    IN p_Mode TINYINT
)
BEGIN
    IF p_Mode = 1 THEN
        IF EXISTS(SELECT 1 FROM mst_unit WHERE LOWER(TRIM(Unit_Name)) = LOWER(TRIM(p_UnitName))) THEN
            SELECT -1 AS Error_No, 'Unit name already exists' AS Message;
        ELSE
            INSERT INTO mst_unit(Unit_Name, Created_By, Created_On)
            VALUES(TRIM(p_UnitName), p_UserId, NOW());
            SELECT 0 AS Error_No, 'Unit added successfully' AS Message;
        END IF;
    ELSEIF p_Mode = 2 THEN
        IF EXISTS(SELECT 1 FROM mst_unit WHERE LOWER(TRIM(Unit_Name)) = LOWER(TRIM(p_UnitName)) AND Unit_Id != p_UnitId) THEN
            SELECT -1 AS Error_No, 'Unit name already exists' AS Message;
        ELSE
            UPDATE mst_unit SET Unit_Name = TRIM(p_UnitName) WHERE Unit_Id = p_UnitId;
            SELECT 0 AS Error_No, 'Unit updated successfully' AS Message;
        END IF;
    END IF;
END ;;
DELIMITER ;
/*!50003 SET sql_mode              = @saved_sql_mode */ ;
/*!50003 SET character_set_client  = @saved_cs_client */ ;
/*!50003 SET character_set_results = @saved_cs_results */ ;
/*!50003 SET collation_connection  = @saved_col_connection */ ;
/*!50003 DROP PROCEDURE IF EXISTS `USP_ADD_EDIT_USER` */;
/*!50003 SET @saved_cs_client      = @@character_set_client */ ;
/*!50003 SET @saved_cs_results     = @@character_set_results */ ;
/*!50003 SET @saved_col_connection = @@collation_connection */ ;
/*!50003 SET character_set_client  = utf8mb4 */ ;
/*!50003 SET character_set_results = utf8mb4 */ ;
/*!50003 SET collation_connection  = utf8mb4_unicode_ci */ ;
/*!50003 SET @saved_sql_mode       = @@sql_mode */ ;
/*!50003 SET sql_mode              = 'ONLY_FULL_GROUP_BY,STRICT_TRANS_TABLES,NO_ZERO_IN_DATE,NO_ZERO_DATE,ERROR_FOR_DIVISION_BY_ZERO,NO_ENGINE_SUBSTITUTION' */ ;
DELIMITER ;;
CREATE DEFINER=`priority`@`%` PROCEDURE `USP_ADD_EDIT_USER`(
	pNuser_Id			Int,
	pUser_Code			Varchar(25),
    pUser_Fname			Varchar(100),
    pMob_No				Varchar(25),
    pMail_id			Varchar(25),
    pUser_Name			Varchar(25),
    pUser_Pass			Varchar(255),
    pGrp_Id				Smallint,
    pBranch_Id			Smallint,
    pUser_Id			Smallint,
    pStatus				Tinyint,
    pMode				Smallint
)
BEGIN

Declare	pExists_Count			Int;
Declare	pError_No				Int;
Declare	pError_Message			Varchar(100);


If(pMode=1) Then 
	Set pExists_Count = (Select Count(*) From mst_user Where User_Code=pUser_Code);
    
    If(pExists_Count>0) Then
		Set pError_No = -1;
        Set pError_Message = 'Same User Code Already Exists !!';
	Else
		Set pError_No = 0;
    End If;
    
    If(pError_No=0) Then
		Set pExists_Count = (Select Count(*) From mst_user Where User_Name=pUser_Name);
        
        If(pExists_Count>0) Then
			Set pError_No = -2;
            Set pError_Message = 'Same User Name Already Exists !!';
		Else
			Set pError_No =0;
        End If;
        
    End If;	
    
    If(pError_No=0) Then
		Insert Into mst_user (User_Code,User_FullName,Contact_No,Email_Id,User_Name,User_Pwd,UGrp_Id,Branch_Id,Created_By,Status_Cd) Values (pUser_Code,pUser_Fname,pMob_No,pMail_id,pUser_Name,pUser_Pass,pGrp_Id,pBranch_Id,pUser_Id,1);
    End If;
    
End If;

If(pMode=2) Then
	Update mst_user Set User_FullName=pUser_Fname,Contact_No=pMob_No,Email_Id=pMail_id,User_Pwd=Case When pUser_Pass Is Not null Then pUser_Pass  ELSE User_Pwd End,UGrp_Id=pGrp_Id,Status_Cd=pStatus Where User_Id=pNuser_Id;
End If;

Select pError_No As Error_No,pError_Message As Message;

END ;;
DELIMITER ;
/*!50003 SET sql_mode              = @saved_sql_mode */ ;
/*!50003 SET character_set_client  = @saved_cs_client */ ;
/*!50003 SET character_set_results = @saved_cs_results */ ;
/*!50003 SET collation_connection  = @saved_col_connection */ ;
/*!50003 DROP PROCEDURE IF EXISTS `USP_ADD_USER_GROUP` */;
/*!50003 SET @saved_cs_client      = @@character_set_client */ ;
/*!50003 SET @saved_cs_results     = @@character_set_results */ ;
/*!50003 SET @saved_col_connection = @@collation_connection */ ;
/*!50003 SET character_set_client  = utf8mb4 */ ;
/*!50003 SET character_set_results = utf8mb4 */ ;
/*!50003 SET collation_connection  = utf8mb4_0900_ai_ci */ ;
/*!50003 SET @saved_sql_mode       = @@sql_mode */ ;
/*!50003 SET sql_mode              = 'ONLY_FULL_GROUP_BY,STRICT_TRANS_TABLES,NO_ZERO_IN_DATE,NO_ZERO_DATE,ERROR_FOR_DIVISION_BY_ZERO,NO_ENGINE_SUBSTITUTION' */ ;
DELIMITER ;;
CREATE DEFINER=`priority`@`%` PROCEDURE `USP_ADD_USER_GROUP`(
    pGrp_Id     Int,
    pGrp_Name   Varchar(50),
    pGrp_Desc   Varchar(150),
    pIs_Admin   TINYINT,
    pMode       Smallint
)
BEGIN
    Declare pExists_Count   Int;
    Declare pError_No       Int;
    Declare pError_Message  Varchar(100);
    Declare pGrp_Nid        Int;

    If(pMode=1) Then
        Set pExists_Count = (Select Count(*) From mst_user_group Where UGrp_Name=pGrp_Name);
        
        If(pExists_Count>0) Then
            Set pError_No = -1;
            Set pError_Message = 'Same User Group Already Exists !!';
        Else
            Set pError_No = 0;
        End If;
        
        If(pError_No=0) Then
            Set pExists_Count = (Select Count(*) From tempmenue);
            
            If(pExists_Count=0) Then
                Set pError_No = -2;
                Set pError_Message = 'Group Menue Details Not Found !!';
            Else
                Set pError_No = 0;
            End If;
            
            If(pError_No=0) Then
                Insert Into mst_user_group (UGrp_Name,UGrp_Description,Is_Admin) 
                Values (pGrp_Name,pGrp_Desc,pIs_Admin);
                Set pGrp_Nid = @@identity;
                
                Insert Into mst_user_permission (UGrp_Id,Menu_Id,Sub_men_Id,User_View,User_Add,User_Edit,User_Del,User_Print,Is_Active) 
                Select pGrp_Nid, Parraint_Id, Child_Id, 1, 0, 0, 0, 0, 1 From tempmenue;
            End If;
        End If;
    End If;

    If(pMode=2) Then
        Set pError_No = 0;
        Update mst_user_group Set UGrp_Name=pGrp_Name,UGrp_Description=pGrp_Desc,Is_Admin=pIs_Admin Where UGrp_Id=pGrp_Id;
        Set pExists_Count = (Select Count(*) From tempmenue);
        
        If(pExists_Count>0) Then
            Delete From mst_user_permission Where UGrp_Id=pGrp_Id;
            Insert Into mst_user_permission (UGrp_Id,Menu_Id,Sub_men_Id,User_View,User_Add,User_Edit,User_Del,User_Print,Is_Active) 
            Select pGrp_Id, Parraint_Id, Child_Id, 1, 0, 0, 0, 0, 1 From tempmenue;
        End If;
    End If;

    Select pError_No As Error_No, pError_Message As Message;
    Drop temporary Table If Exists tempmenue;
END ;;
DELIMITER ;
/*!50003 SET sql_mode              = @saved_sql_mode */ ;
/*!50003 SET character_set_client  = @saved_cs_client */ ;
/*!50003 SET character_set_results = @saved_cs_results */ ;
/*!50003 SET collation_connection  = @saved_col_connection */ ;
/*!50003 DROP PROCEDURE IF EXISTS `USP_AGENT_REQUISITION` */;
/*!50003 SET @saved_cs_client      = @@character_set_client */ ;
/*!50003 SET @saved_cs_results     = @@character_set_results */ ;
/*!50003 SET @saved_col_connection = @@collation_connection */ ;
/*!50003 SET character_set_client  = utf8mb4 */ ;
/*!50003 SET character_set_results = utf8mb4 */ ;
/*!50003 SET collation_connection  = utf8mb4_unicode_ci */ ;
/*!50003 SET @saved_sql_mode       = @@sql_mode */ ;
/*!50003 SET sql_mode              = 'ONLY_FULL_GROUP_BY,STRICT_TRANS_TABLES,NO_ZERO_IN_DATE,NO_ZERO_DATE,ERROR_FOR_DIVISION_BY_ZERO,NO_ENGINE_SUBSTITUTION' */ ;
DELIMITER ;;
CREATE DEFINER=`priority`@`%` PROCEDURE `USP_AGENT_REQUISITION`(
    pIndent_Id INT,
    pAgent_Id INT,
    pDate DATE,
    pRemarks VARCHAR(100),
    pFin_Id INT,
    pBranch_Id INT,
    pMode INT,
    pIndent_Type_Id INT
)
BEGIN
    DECLARE pItem_Count INT;
    DECLARE pError_No INT;
    DECLARE pError_Message VARCHAR(100);
    DECLARE pIndent_No VARCHAR(25);
    DECLARE pInsert_Id INT;

    SET pItem_Count = (SELECT COUNT(*) FROM tempitem);

    IF (pItem_Count = 0) THEN
        SET pError_No = -1;
        SET pError_Message = 'No Item Found For Indent !!';
    ELSE
        SET pError_No = 0;
    END IF;

    IF (pMode = 1) THEN
        IF (pError_No = 0) THEN
            SET pIndent_No = (UDF_GEN_SYS_NO('AI', pFin_Id, pBranch_Id));

            IF (pIndent_No IS NULL) THEN
                SET pError_No = -2;
                SET pError_Message = 'Indent Number Is Not Genereated !!';
            ELSE
                SET pError_No = 0;
            END IF;
        END IF;

        IF (pError_No = 0) THEN
            INSERT INTO trans_agent_indent (Indent_Date, Indent_No, Agent_Id, Indent_Type_Id, Remarks, Entry_DtTm)
            VALUES (pDate, pIndent_No, pAgent_Id, IFNULL(pIndent_Type_Id, 1), pRemarks, CURRENT_TIMESTAMP());
            SET pInsert_Id = LAST_INSERT_ID();

            INSERT INTO trans_agent_indent_details (Indent_Id, Prod_Id, Quantity, Unit_Id)
            SELECT pInsert_Id, item_id, qnty, unit_id FROM tempitem;

            SET pError_Message = CONCAT('Agent Requisition Successful, No. Is ', pIndent_No);
        END IF;
    END IF;

    IF (pMode = 2) THEN
        UPDATE trans_agent_indent SET Remarks = pRemarks WHERE Indent_Id = pIndent_Id;
        DELETE FROM trans_agent_indent_details WHERE Indent_Id = pIndent_Id;
        INSERT INTO trans_agent_indent_details (Indent_Id, Prod_Id, Quantity, Unit_Id)
        SELECT pIndent_Id, item_id, qnty, unit_id FROM tempitem;

        SET pError_Message = 'Agent Requisition Is Update Successful !!';
    END IF;

    SELECT pError_No AS Error_No, pError_Message AS Message;
END ;;
DELIMITER ;
/*!50003 SET sql_mode              = @saved_sql_mode */ ;
/*!50003 SET character_set_client  = @saved_cs_client */ ;
/*!50003 SET character_set_results = @saved_cs_results */ ;
/*!50003 SET collation_connection  = @saved_col_connection */ ;
/*!50003 DROP PROCEDURE IF EXISTS `USP_GENERATE_BARCODE` */;
/*!50003 SET @saved_cs_client      = @@character_set_client */ ;
/*!50003 SET @saved_cs_results     = @@character_set_results */ ;
/*!50003 SET @saved_col_connection = @@collation_connection */ ;
/*!50003 SET character_set_client  = utf8mb4 */ ;
/*!50003 SET character_set_results = utf8mb4 */ ;
/*!50003 SET collation_connection  = utf8mb4_0900_ai_ci */ ;
/*!50003 SET @saved_sql_mode       = @@sql_mode */ ;
/*!50003 SET sql_mode              = 'ONLY_FULL_GROUP_BY,STRICT_TRANS_TABLES,NO_ZERO_IN_DATE,NO_ZERO_DATE,ERROR_FOR_DIVISION_BY_ZERO,NO_ENGINE_SUBSTITUTION' */ ;
DELIMITER ;;
CREATE DEFINER=`priority`@`%` PROCEDURE `USP_GENERATE_BARCODE`(
	pOrg_Id			Int,
    pBr_Code		Varchar(6),
    pStock_Date		Date,
    pStock_Id		Bigint
)
BEGIN


Declare	pExists_Count		Int;
Declare	pError_No			Int;
Declare	pError_Message		Varchar(100);
Declare	pBarcode			Varchar(30);

Set pBarcode = UDF_GEN_BARCODE(pOrg_Id,pBr_Code,pStock_Date,pStock_Id);

If(pBarcode Is Null Or pBarcode='0') Then 
	Set pError_No = -1;
    Set pError_Message = 'Barcode Not Generated !!';
Else
	Set pError_No = 0;
End If;

If(pError_No=0) Then
	Set pExists_Count = (Select Count(*) From trns_stockinout Where Barcode=pBarcode);
    
    If(pExists_Count>0) Then
		Set pError_No = -2;
        Set pError_Message = 'Same Barcode Already Exists !!';
	Else
		Set pError_No =0;
    End If;
    
End If;

If(pError_No=0) Then
	Update trns_stockinout Set Barcode=pBarcode Where Stock_Id=pStock_Id;
End If;

Select pError_No As Error_No,pError_Message As Message;

END ;;
DELIMITER ;
/*!50003 SET sql_mode              = @saved_sql_mode */ ;
/*!50003 SET character_set_client  = @saved_cs_client */ ;
/*!50003 SET character_set_results = @saved_cs_results */ ;
/*!50003 SET collation_connection  = @saved_col_connection */ ;
/*!50003 DROP PROCEDURE IF EXISTS `USP_GEN_AGENT_SETTLE_TOKEN` */;
/*!50003 SET @saved_cs_client      = @@character_set_client */ ;
/*!50003 SET @saved_cs_results     = @@character_set_results */ ;
/*!50003 SET @saved_col_connection = @@collation_connection */ ;
/*!50003 SET character_set_client  = utf8mb4 */ ;
/*!50003 SET character_set_results = utf8mb4 */ ;
/*!50003 SET collation_connection  = utf8mb4_unicode_ci */ ;
/*!50003 SET @saved_sql_mode       = @@sql_mode */ ;
/*!50003 SET sql_mode              = 'ONLY_FULL_GROUP_BY,STRICT_TRANS_TABLES,NO_ZERO_IN_DATE,NO_ZERO_DATE,ERROR_FOR_DIVISION_BY_ZERO,NO_ENGINE_SUBSTITUTION' */ ;
DELIMITER ;;
CREATE DEFINER=`priority`@`%` PROCEDURE `USP_GEN_AGENT_SETTLE_TOKEN`(
    pAgent_Id   INT
)
BEGIN
    DECLARE pError_No INT DEFAULT 0;
    DECLARE pError_Message VARCHAR(150);
    DECLARE pSettle_Token VARCHAR(20);
    DECLARE pPending_Count INT DEFAULT 0;
    DECLARE pAlready TINYINT DEFAULT 0;

    IF IFNULL(pAgent_Id, 0) = 0 THEN
        SET pError_No = -1;
        SET pError_Message = 'Invalid agent';
    ELSEIF NOT EXISTS (
        SELECT 1 FROM mst_agent WHERE Agent_Id = pAgent_Id AND Status_Cd = 1
    ) THEN
        SET pError_No = -1;
        SET pError_Message = 'Agent not found';
    ELSE
        SET pPending_Count = IFNULL((
            SELECT COUNT(*)
            FROM trans_trading
            WHERE Agent_Id = pAgent_Id
              AND Tranding_Type = 3
              AND IFNULL(Status_Cd, 1) = 1
              AND IFNULL(Is_Settle, 0) = 0
        ), 0);

        IF pPending_Count = 0 THEN
            -- Clear stale token when nothing left to settle
            UPDATE mst_agent
               SET Settle_Token = NULL
             WHERE Agent_Id = pAgent_Id
               AND IFNULL(Settle_Token, '') <> '';

            SET pError_No = -1;
            SET pError_Message = 'No unsettled sales found to generate token';
            SET pSettle_Token = NULL;
        ELSE
            SET pSettle_Token = NULLIF(TRIM((
                SELECT Settle_Token FROM mst_agent WHERE Agent_Id = pAgent_Id LIMIT 1
            )), '');

            IF pSettle_Token IS NOT NULL THEN
                SET pAlready = 1;
                SET pError_No = 0;
                SET pError_Message = CONCAT(
                    'Token already generated for ',
                    pPending_Count,
                    ' unsettled sale(s). Give this token to admin.'
                );
            ELSE
                SET pSettle_Token = CONCAT('T', LPAD(pAgent_Id, 4, '0'), LPAD(FLOOR(RAND() * 10000), 4, '0'));
                UPDATE mst_agent
                   SET Settle_Token = pSettle_Token
                 WHERE Agent_Id = pAgent_Id;

                SET pError_No = 0;
                SET pError_Message = CONCAT(
                    'Token generated for ',
                    pPending_Count,
                    ' unsettled sale(s). Give this token to admin.'
                );
            END IF;
        END IF;
    END IF;

    SELECT pError_No AS Error_No,
           pError_Message AS Message,
           pSettle_Token AS Settle_Token,
           pPending_Count AS Pending_Count,
           pAlready AS Already_Exists;
END ;;
DELIMITER ;
/*!50003 SET sql_mode              = @saved_sql_mode */ ;
/*!50003 SET character_set_client  = @saved_cs_client */ ;
/*!50003 SET character_set_results = @saved_cs_results */ ;
/*!50003 SET collation_connection  = @saved_col_connection */ ;
/*!50003 DROP PROCEDURE IF EXISTS `USP_GET_ACCOUNTING_YEAR` */;
/*!50003 SET @saved_cs_client      = @@character_set_client */ ;
/*!50003 SET @saved_cs_results     = @@character_set_results */ ;
/*!50003 SET @saved_col_connection = @@collation_connection */ ;
/*!50003 SET character_set_client  = utf8mb4 */ ;
/*!50003 SET character_set_results = utf8mb4 */ ;
/*!50003 SET collation_connection  = utf8mb4_0900_ai_ci */ ;
/*!50003 SET @saved_sql_mode       = @@sql_mode */ ;
/*!50003 SET sql_mode              = 'ONLY_FULL_GROUP_BY,STRICT_TRANS_TABLES,NO_ZERO_IN_DATE,NO_ZERO_DATE,ERROR_FOR_DIVISION_BY_ZERO,NO_ENGINE_SUBSTITUTION' */ ;
DELIMITER ;;
CREATE DEFINER=`priority`@`%` PROCEDURE `USP_GET_ACCOUNTING_YEAR`()
BEGIN
    SELECT Year_Id, Year_Desc, Year_Start, Year_End, Is_Active
    FROM mst_accountingyear
    WHERE Is_Active = 1;
END ;;
DELIMITER ;
/*!50003 SET sql_mode              = @saved_sql_mode */ ;
/*!50003 SET character_set_client  = @saved_cs_client */ ;
/*!50003 SET character_set_results = @saved_cs_results */ ;
/*!50003 SET collation_connection  = @saved_col_connection */ ;
/*!50003 DROP PROCEDURE IF EXISTS `USP_GET_ACCT_CATEGORY` */;
/*!50003 SET @saved_cs_client      = @@character_set_client */ ;
/*!50003 SET @saved_cs_results     = @@character_set_results */ ;
/*!50003 SET @saved_col_connection = @@collation_connection */ ;
/*!50003 SET character_set_client  = utf8mb4 */ ;
/*!50003 SET character_set_results = utf8mb4 */ ;
/*!50003 SET collation_connection  = utf8mb4_0900_ai_ci */ ;
/*!50003 SET @saved_sql_mode       = @@sql_mode */ ;
/*!50003 SET sql_mode              = 'ONLY_FULL_GROUP_BY,STRICT_TRANS_TABLES,NO_ZERO_IN_DATE,NO_ZERO_DATE,ERROR_FOR_DIVISION_BY_ZERO,NO_ENGINE_SUBSTITUTION' */ ;
DELIMITER ;;
CREATE DEFINER=`priority`@`%` PROCEDURE `USP_GET_ACCT_CATEGORY`()
BEGIN
    SELECT Cate_Id, Cate_Code, Cate_Desc FROM mst_acct_category ORDER BY Cate_Id;
END ;;
DELIMITER ;
/*!50003 SET sql_mode              = @saved_sql_mode */ ;
/*!50003 SET character_set_client  = @saved_cs_client */ ;
/*!50003 SET character_set_results = @saved_cs_results */ ;
/*!50003 SET collation_connection  = @saved_col_connection */ ;
/*!50003 DROP PROCEDURE IF EXISTS `USP_GET_ACCT_GLHEAD` */;
/*!50003 SET @saved_cs_client      = @@character_set_client */ ;
/*!50003 SET @saved_cs_results     = @@character_set_results */ ;
/*!50003 SET @saved_col_connection = @@collation_connection */ ;
/*!50003 SET character_set_client  = utf8mb4 */ ;
/*!50003 SET character_set_results = utf8mb4 */ ;
/*!50003 SET collation_connection  = utf8mb4_0900_ai_ci */ ;
/*!50003 SET @saved_sql_mode       = @@sql_mode */ ;
/*!50003 SET sql_mode              = 'ONLY_FULL_GROUP_BY,STRICT_TRANS_TABLES,NO_ZERO_IN_DATE,NO_ZERO_DATE,ERROR_FOR_DIVISION_BY_ZERO,NO_ENGINE_SUBSTITUTION' */ ;
DELIMITER ;;
CREATE DEFINER=`priority`@`%` PROCEDURE `USP_GET_ACCT_GLHEAD`()
BEGIN
    SELECT g.Account_Id, g.Account_Code, g.Account_Desc, g.Account_For,
           m.MainHd_Desc, g.Is_Active
    FROM mst_acct_glhead g
    INNER JOIN mst_acct_mainhd m ON m.MainHd_Id = g.MainHd_Id
    ORDER BY g.Account_Id;
END ;;
DELIMITER ;
/*!50003 SET sql_mode              = @saved_sql_mode */ ;
/*!50003 SET character_set_client  = @saved_cs_client */ ;
/*!50003 SET character_set_results = @saved_cs_results */ ;
/*!50003 SET collation_connection  = @saved_col_connection */ ;
/*!50003 DROP PROCEDURE IF EXISTS `USP_GET_ACCT_MAINHD` */;
/*!50003 SET @saved_cs_client      = @@character_set_client */ ;
/*!50003 SET @saved_cs_results     = @@character_set_results */ ;
/*!50003 SET @saved_col_connection = @@collation_connection */ ;
/*!50003 SET character_set_client  = utf8mb4 */ ;
/*!50003 SET character_set_results = utf8mb4 */ ;
/*!50003 SET collation_connection  = utf8mb4_0900_ai_ci */ ;
/*!50003 SET @saved_sql_mode       = @@sql_mode */ ;
/*!50003 SET sql_mode              = 'ONLY_FULL_GROUP_BY,STRICT_TRANS_TABLES,NO_ZERO_IN_DATE,NO_ZERO_DATE,ERROR_FOR_DIVISION_BY_ZERO,NO_ENGINE_SUBSTITUTION' */ ;
DELIMITER ;;
CREATE DEFINER=`priority`@`%` PROCEDURE `USP_GET_ACCT_MAINHD`()
BEGIN
    SELECT m.MainHd_Id, m.MainHd_Code, m.MainHd_Desc, c.Cate_Desc
    FROM mst_acct_mainhd m
    INNER JOIN mst_acct_category c ON c.Cate_Id = m.Cate_Id
    ORDER BY m.MainHd_Id;
END ;;
DELIMITER ;
/*!50003 SET sql_mode              = @saved_sql_mode */ ;
/*!50003 SET character_set_client  = @saved_cs_client */ ;
/*!50003 SET character_set_results = @saved_cs_results */ ;
/*!50003 SET collation_connection  = @saved_col_connection */ ;
/*!50003 DROP PROCEDURE IF EXISTS `USP_GET_AGENT_DASHBOARD_STATS` */;
/*!50003 SET @saved_cs_client      = @@character_set_client */ ;
/*!50003 SET @saved_cs_results     = @@character_set_results */ ;
/*!50003 SET @saved_col_connection = @@collation_connection */ ;
/*!50003 SET character_set_client  = utf8mb4 */ ;
/*!50003 SET character_set_results = utf8mb4 */ ;
/*!50003 SET collation_connection  = utf8mb4_0900_ai_ci */ ;
/*!50003 SET @saved_sql_mode       = @@sql_mode */ ;
/*!50003 SET sql_mode              = 'ONLY_FULL_GROUP_BY,STRICT_TRANS_TABLES,NO_ZERO_IN_DATE,NO_ZERO_DATE,ERROR_FOR_DIVISION_BY_ZERO,NO_ENGINE_SUBSTITUTION' */ ;
DELIMITER ;;
CREATE DEFINER=`priority`@`%` PROCEDURE `USP_GET_AGENT_DASHBOARD_STATS`(
    pAgent_Id   INT,
    pYear_Id    TINYINT,
    pToday      DATE
)
BEGIN
    DECLARE pYearStart  DATE;
    DECLARE pYearEnd    DATE;
    DECLARE pMonthStart DATE;

    IF pToday IS NULL THEN
        SET pToday = CURDATE();
    END IF;

    SET pMonthStart = DATE_FORMAT(pToday, '%Y-%m-01');

    SELECT Year_Start, Year_End
      INTO pYearStart, pYearEnd
      FROM mst_accountingyear
     WHERE Year_Id = pYear_Id
     LIMIT 1;

    SELECT
        IFNULL((
            SELECT SUM(t.Net_Amt)
            FROM trans_trading t
            WHERE t.Agent_Id = pAgent_Id
              AND t.Tranding_Type = 3
              AND t.Status_Cd = 1
              AND t.Invoice_Date BETWEEN pMonthStart AND pToday
              AND t.Invoice_Date BETWEEN pYearStart AND pYearEnd
        ), 0) AS agent_sales_month,
        IFNULL((
            SELECT SUM(d.Quantity * IFNULL((
                SELECT s.MRP
                FROM trns_stockinout s
                WHERE s.Prod_Id = d.Prod_Id
                  AND IFNULL(s.MRP, 0) > 0
                ORDER BY s.InOut_Date DESC, s.Stock_Id DESC
                LIMIT 1
            ), 0))
            FROM trans_agent_indent m
            JOIN trans_agent_indent_details d ON d.Indent_Id = m.Indent_Id
            WHERE m.Agent_Id = pAgent_Id
              AND m.Indent_Date BETWEEN pMonthStart AND pToday
              AND m.Indent_Date BETWEEN pYearStart AND pYearEnd
        ), 0) AS requisitions_month,
        IFNULL((
            SELECT SUM(t.Net_Amt)
            FROM trans_trading t
            WHERE t.Agent_Id = pAgent_Id
              AND t.Tranding_Type = 5
              AND t.Status_Cd = 1
              AND t.Invoice_Date BETWEEN pMonthStart AND pToday
              AND t.Invoice_Date BETWEEN pYearStart AND pYearEnd
        ), 0) AS customer_returns_month,
        IFNULL((
            SELECT SUM(x.Qty)
            FROM (
                SELECT
                    SUM(
                        CASE i.Stock_Type
                            WHEN 4 THEN i.Quantity
                            WHEN 6 THEN -i.Quantity
                            WHEN 5 THEN -i.Quantity
                            WHEN 8 THEN i.Quantity
                            ELSE 0
                        END
                    ) AS Qty
                FROM trans_agent_stock s
                JOIN trns_stockinout i ON i.Stock_Id = s.TrnsStock_Id
                WHERE s.Agent_Id = pAgent_Id
                  AND DATE(s.Trans_Date) <= pToday
                  AND s.Prod_Id IS NOT NULL
                GROUP BY s.Prod_Id
            ) x
            WHERE x.Qty > 0
        ), 0) AS stock_with_agent,
        IFNULL((
            SELECT SUM(t.Net_Amt)
            FROM trans_trading t
            WHERE t.Agent_Id = pAgent_Id
              AND t.Tranding_Type = 3
              AND t.Status_Cd = 1
              AND t.Invoice_Date = pToday
        ), 0) AS today_sales,
        IFNULL((
            SELECT SUM(t.Net_Amt)
            FROM trans_trading t
            WHERE t.Agent_Id = pAgent_Id
              AND t.Tranding_Type = 5
              AND t.Status_Cd = 1
              AND t.Invoice_Date = pToday
        ), 0) AS today_return,
        IFNULL((
            SELECT COUNT(*)
            FROM trans_agent_indent m
            WHERE m.Agent_Id = pAgent_Id
              AND IFNULL(m.Is_Issued, 0) = 0
        ), 0) AS pending_indents,
        IFNULL((
            SELECT COUNT(*)
            FROM (
                SELECT
                    p.Prod_Id,
                    p.ReOrder_Qty,
                    SUM(
                        CASE i.Stock_Type
                            WHEN 4 THEN i.Quantity
                            WHEN 6 THEN -i.Quantity
                            WHEN 5 THEN -i.Quantity
                            WHEN 8 THEN i.Quantity
                            ELSE 0
                        END
                    ) AS Qty
                FROM trans_agent_stock s
                JOIN trns_stockinout i ON i.Stock_Id = s.TrnsStock_Id
                JOIN mst_product p ON p.Prod_Id = s.Prod_Id
                WHERE s.Agent_Id = pAgent_Id
                  AND DATE(s.Trans_Date) <= pToday
                  AND s.Prod_Id IS NOT NULL
                  AND p.Is_Active = 1
                GROUP BY p.Prod_Id, p.ReOrder_Qty
            ) z
            WHERE z.Qty > 0
              AND (
                    (IFNULL(z.ReOrder_Qty, 0) > 0 AND z.Qty < z.ReOrder_Qty)
                 OR (IFNULL(z.ReOrder_Qty, 0) = 0 AND z.Qty <= 10)
              )
        ), 0) AS low_stock_count;
END ;;
DELIMITER ;
/*!50003 SET sql_mode              = @saved_sql_mode */ ;
/*!50003 SET character_set_client  = @saved_cs_client */ ;
/*!50003 SET character_set_results = @saved_cs_results */ ;
/*!50003 SET collation_connection  = @saved_col_connection */ ;
/*!50003 DROP PROCEDURE IF EXISTS `USP_GET_AGENT_LIST` */;
/*!50003 SET @saved_cs_client      = @@character_set_client */ ;
/*!50003 SET @saved_cs_results     = @@character_set_results */ ;
/*!50003 SET @saved_col_connection = @@collation_connection */ ;
/*!50003 SET character_set_client  = utf8mb4 */ ;
/*!50003 SET character_set_results = utf8mb4 */ ;
/*!50003 SET collation_connection  = utf8mb4_unicode_ci */ ;
/*!50003 SET @saved_sql_mode       = @@sql_mode */ ;
/*!50003 SET sql_mode              = 'ONLY_FULL_GROUP_BY,STRICT_TRANS_TABLES,NO_ZERO_IN_DATE,NO_ZERO_DATE,ERROR_FOR_DIVISION_BY_ZERO,NO_ENGINE_SUBSTITUTION' */ ;
DELIMITER ;;
CREATE DEFINER=`priority`@`%` PROCEDURE `USP_GET_AGENT_LIST`(
    IN pBranch_Id  INT,
    IN pSearch     VARCHAR(100),
    IN pPage       INT,
    IN pPageSize   INT
)
BEGIN
    DECLARE pOffset INT;
    SET pOffset = (pPage - 1) * pPageSize;

    IF pPageSize = 0 THEN
        SELECT Agent_Id, Agent_Code, Agent_Name, Address, Contact_No,
               Join_Date, Stock_Limit, Credit_Sell_Limit, Branch_Id, Status_Cd,
               UDF_GET_OPTION_NAME(1, Status_Cd) AS Status_Name
        FROM mst_agent
        WHERE Branch_Id = pBranch_Id
          AND Status_Cd = 1
          AND (pSearch = '' OR Agent_Name LIKE CONCAT('%', pSearch, '%')
                            OR Agent_Code LIKE CONCAT('%', pSearch, '%'))
        ORDER BY Agent_Name;

        SELECT COUNT(*) AS Total
        FROM mst_agent
        WHERE Branch_Id = pBranch_Id
          AND Status_Cd = 1
          AND (pSearch = '' OR Agent_Name LIKE CONCAT('%', pSearch, '%')
                            OR Agent_Code LIKE CONCAT('%', pSearch, '%'));
    ELSE
        SELECT Agent_Id, Agent_Code, Agent_Name, Address, Contact_No,
               Join_Date, Stock_Limit, Credit_Sell_Limit, Branch_Id, Status_Cd,
               UDF_GET_OPTION_NAME(1, Status_Cd) AS Status_Name
        FROM mst_agent
        WHERE Branch_Id = pBranch_Id
          AND (pSearch = '' OR Agent_Name LIKE CONCAT('%', pSearch, '%')
                            OR Agent_Code LIKE CONCAT('%', pSearch, '%'))
        ORDER BY Agent_Id
        LIMIT pPageSize OFFSET pOffset;

        SELECT COUNT(*) AS Total
        FROM mst_agent
        WHERE Branch_Id = pBranch_Id
          AND (pSearch = '' OR Agent_Name LIKE CONCAT('%', pSearch, '%')
                            OR Agent_Code LIKE CONCAT('%', pSearch, '%'));
    END IF;
END ;;
DELIMITER ;
/*!50003 SET sql_mode              = @saved_sql_mode */ ;
/*!50003 SET character_set_client  = @saved_cs_client */ ;
/*!50003 SET character_set_results = @saved_cs_results */ ;
/*!50003 SET collation_connection  = @saved_col_connection */ ;
/*!50003 DROP PROCEDURE IF EXISTS `USP_GET_AGENT_LOW_STOCK` */;
/*!50003 SET @saved_cs_client      = @@character_set_client */ ;
/*!50003 SET @saved_cs_results     = @@character_set_results */ ;
/*!50003 SET @saved_col_connection = @@collation_connection */ ;
/*!50003 SET character_set_client  = utf8mb4 */ ;
/*!50003 SET character_set_results = utf8mb4 */ ;
/*!50003 SET collation_connection  = utf8mb4_unicode_ci */ ;
/*!50003 SET @saved_sql_mode       = @@sql_mode */ ;
/*!50003 SET sql_mode              = 'ONLY_FULL_GROUP_BY,STRICT_TRANS_TABLES,NO_ZERO_IN_DATE,NO_ZERO_DATE,ERROR_FOR_DIVISION_BY_ZERO,NO_ENGINE_SUBSTITUTION' */ ;
DELIMITER ;;
CREATE DEFINER=`priority`@`%` PROCEDURE `USP_GET_AGENT_LOW_STOCK`(
    pAgent_Id   INT,
    pToday      DATE
)
BEGIN
    IF pToday IS NULL THEN
        SET pToday = CURDATE();
    END IF;

    SELECT
        x.Prod_Id,
        x.Prod_Code,
        x.Prod_Name,
        x.On_Hand,
        x.ReOrder_Qty,
        CASE
            WHEN x.On_Hand <= 5 THEN 'Critical'
            WHEN IFNULL(x.ReOrder_Qty, 0) > 0 AND x.On_Hand <= (x.ReOrder_Qty * 0.5) THEN 'Critical'
            ELSE 'Low'
        END AS Alert_Status
    FROM (
        SELECT
            p.Prod_Id,
            p.Prod_Code,
            p.Prod_ShortNm AS Prod_Name,
            SUM(
                CASE i.Stock_Type
                    WHEN 4 THEN i.Quantity
                    WHEN 6 THEN -i.Quantity
                    WHEN 5 THEN -i.Quantity
                    WHEN 8 THEN i.Quantity
                    ELSE 0
                END
            ) AS On_Hand,
            IFNULL(p.ReOrder_Qty, 0) AS ReOrder_Qty
        FROM trans_agent_stock s
        JOIN trns_stockinout i ON i.Stock_Id = s.TrnsStock_Id
        JOIN mst_product p ON p.Prod_Id = s.Prod_Id
        WHERE s.Agent_Id = pAgent_Id
          AND DATE(s.Trans_Date) <= pToday
          AND s.Prod_Id IS NOT NULL
          AND p.Is_Active = 1
        GROUP BY p.Prod_Id, p.Prod_Code, p.Prod_ShortNm, p.ReOrder_Qty
    ) x
    WHERE x.On_Hand > 0
      AND (
            (IFNULL(x.ReOrder_Qty, 0) > 0 AND x.On_Hand < x.ReOrder_Qty)
         OR (IFNULL(x.ReOrder_Qty, 0) = 0 AND x.On_Hand <= 10)
      )
    ORDER BY x.On_Hand ASC, x.Prod_Name
    LIMIT 10;
END ;;
DELIMITER ;
/*!50003 SET sql_mode              = @saved_sql_mode */ ;
/*!50003 SET character_set_client  = @saved_cs_client */ ;
/*!50003 SET character_set_results = @saved_cs_results */ ;
/*!50003 SET collation_connection  = @saved_col_connection */ ;
/*!50003 DROP PROCEDURE IF EXISTS `USP_GET_AGENT_MENUE` */;
/*!50003 SET @saved_cs_client      = @@character_set_client */ ;
/*!50003 SET @saved_cs_results     = @@character_set_results */ ;
/*!50003 SET @saved_col_connection = @@collation_connection */ ;
/*!50003 SET character_set_client  = utf8mb4 */ ;
/*!50003 SET character_set_results = utf8mb4 */ ;
/*!50003 SET collation_connection  = utf8mb4_0900_ai_ci */ ;
/*!50003 SET @saved_sql_mode       = @@sql_mode */ ;
/*!50003 SET sql_mode              = 'ONLY_FULL_GROUP_BY,STRICT_TRANS_TABLES,NO_ZERO_IN_DATE,NO_ZERO_DATE,ERROR_FOR_DIVISION_BY_ZERO,NO_ENGINE_SUBSTITUTION' */ ;
DELIMITER ;;
CREATE DEFINER=`priority`@`%` PROCEDURE `USP_GET_AGENT_MENUE`()
BEGIN

Select Menu_Id,Menu_Name,SubMenu_Id,SubMenu_Name,Icon,Route From mst_menus_agent Where Is_Active;


END ;;
DELIMITER ;
/*!50003 SET sql_mode              = @saved_sql_mode */ ;
/*!50003 SET character_set_client  = @saved_cs_client */ ;
/*!50003 SET character_set_results = @saved_cs_results */ ;
/*!50003 SET collation_connection  = @saved_col_connection */ ;
/*!50003 DROP PROCEDURE IF EXISTS `USP_GET_AGENT_MONTHLY_SALE_RETURN` */;
/*!50003 SET @saved_cs_client      = @@character_set_client */ ;
/*!50003 SET @saved_cs_results     = @@character_set_results */ ;
/*!50003 SET @saved_col_connection = @@collation_connection */ ;
/*!50003 SET character_set_client  = utf8mb4 */ ;
/*!50003 SET character_set_results = utf8mb4 */ ;
/*!50003 SET collation_connection  = utf8mb4_unicode_ci */ ;
/*!50003 SET @saved_sql_mode       = @@sql_mode */ ;
/*!50003 SET sql_mode              = 'ONLY_FULL_GROUP_BY,STRICT_TRANS_TABLES,NO_ZERO_IN_DATE,NO_ZERO_DATE,ERROR_FOR_DIVISION_BY_ZERO,NO_ENGINE_SUBSTITUTION' */ ;
DELIMITER ;;
CREATE DEFINER=`priority`@`%` PROCEDURE `USP_GET_AGENT_MONTHLY_SALE_RETURN`(
    pAgent_Id   INT,
    pYear_Id    TINYINT
)
BEGIN
    DECLARE vYearStart DATE;
    DECLARE vYearEnd   DATE;

    DECLARE CONTINUE HANDLER FOR NOT FOUND
    BEGIN
        SET vYearStart = NULL;
        SET vYearEnd = NULL;
    END;

    SELECT Year_Start, Year_End
      INTO vYearStart, vYearEnd
      FROM mst_accountingyear
     WHERE Year_Id = pYear_Id
     LIMIT 1;

    IF vYearStart IS NULL OR vYearEnd IS NULL THEN
        SELECT
            CAST(NULL AS CHAR(20)) AS Month_Label,
            CAST(NULL AS CHAR(7))  AS Month_Key,
            CAST(0 AS DECIMAL(12,2)) AS Sales_Amt,
            CAST(0 AS DECIMAL(12,2)) AS Return_Amt
        WHERE 1 = 0;
    ELSE
        WITH RECURSIVE months AS (
            SELECT DATE_FORMAT(vYearStart, '%Y-%m-01') AS Month_Start
            UNION ALL
            SELECT DATE_ADD(Month_Start, INTERVAL 1 MONTH)
              FROM months
             WHERE DATE_ADD(Month_Start, INTERVAL 1 MONTH) <= vYearEnd
        )
        SELECT
            DATE_FORMAT(m.Month_Start, '%b %Y') AS Month_Label,
            DATE_FORMAT(m.Month_Start, '%Y-%m') AS Month_Key,
            IFNULL((
                SELECT SUM(t.Net_Amt)
                FROM trans_trading t
                WHERE t.Agent_Id = pAgent_Id
                  AND t.Tranding_Type = 3
                  AND t.Status_Cd = 1
                  AND t.Invoice_Date >= GREATEST(m.Month_Start, vYearStart)
                  AND t.Invoice_Date <= LEAST(LAST_DAY(m.Month_Start), vYearEnd)
            ), 0) AS Sales_Amt,
            IFNULL((
                SELECT SUM(t.Net_Amt)
                FROM trans_trading t
                WHERE t.Agent_Id = pAgent_Id
                  AND t.Tranding_Type = 5
                  AND t.Status_Cd = 1
                  AND t.Invoice_Date >= GREATEST(m.Month_Start, vYearStart)
                  AND t.Invoice_Date <= LEAST(LAST_DAY(m.Month_Start), vYearEnd)
            ), 0) AS Return_Amt
        FROM months m
        ORDER BY m.Month_Start;
    END IF;
END ;;
DELIMITER ;
/*!50003 SET sql_mode              = @saved_sql_mode */ ;
/*!50003 SET character_set_client  = @saved_cs_client */ ;
/*!50003 SET character_set_results = @saved_cs_results */ ;
/*!50003 SET collation_connection  = @saved_col_connection */ ;
/*!50003 DROP PROCEDURE IF EXISTS `USP_GET_AGENT_NOTIFICATIONS` */;
/*!50003 SET @saved_cs_client      = @@character_set_client */ ;
/*!50003 SET @saved_cs_results     = @@character_set_results */ ;
/*!50003 SET @saved_col_connection = @@collation_connection */ ;
/*!50003 SET character_set_client  = utf8mb4 */ ;
/*!50003 SET character_set_results = utf8mb4 */ ;
/*!50003 SET collation_connection  = utf8mb4_unicode_ci */ ;
/*!50003 SET @saved_sql_mode       = @@sql_mode */ ;
/*!50003 SET sql_mode              = 'ONLY_FULL_GROUP_BY,STRICT_TRANS_TABLES,NO_ZERO_IN_DATE,NO_ZERO_DATE,ERROR_FOR_DIVISION_BY_ZERO,NO_ENGINE_SUBSTITUTION' */ ;
DELIMITER ;;
CREATE DEFINER=`priority`@`%` PROCEDURE `USP_GET_AGENT_NOTIFICATIONS`(
    pAgent_Id INT,
    pDate     DATE
)
BEGIN
    IF pDate IS NULL THEN
        SET pDate = CURDATE();
    END IF;

    SELECT NType, Message, NDate, Link_Key
    FROM (
        SELECT
            'ISSUED' AS NType,
            CONCAT(p.Prod_ShortNm, ' issued to you (', CAST(SUM(i.Quantity) AS CHAR), ')') AS Message,
            pDate AS NDate,
            'issue' AS Link_Key,
            1 AS Sort_No
        FROM trans_agent_stock s
        JOIN trns_stockinout i ON i.Stock_Id = s.TrnsStock_Id
        JOIN mst_product p ON p.Prod_Id = s.Prod_Id
        WHERE s.Agent_Id = pAgent_Id
          AND i.Stock_Type = 4
          AND DATE(s.Trans_Date) = pDate
        GROUP BY p.Prod_Id, p.Prod_ShortNm

        UNION ALL

        SELECT
            'PENDING',
            CONCAT('Pending indent ', IFNULL(m.Indent_No, ''), ' awaiting issue'),
            m.Indent_Date,
            'requisition',
            2
        FROM trans_agent_indent m
        WHERE m.Agent_Id = pAgent_Id
          AND IFNULL(m.Is_Issued, 0) = 0

        UNION ALL

        SELECT
            'RETURN',
            CONCAT(p.Prod_ShortNm, ' customer return received (', CAST(SUM(i.Quantity) AS CHAR), ')'),
            pDate,
            'return',
            3
        FROM trans_agent_stock s
        JOIN trns_stockinout i ON i.Stock_Id = s.TrnsStock_Id
        JOIN mst_product p ON p.Prod_Id = s.Prod_Id
        WHERE s.Agent_Id = pAgent_Id
          AND i.Stock_Type = 8
          AND DATE(s.Trans_Date) = pDate
        GROUP BY p.Prod_Id, p.Prod_ShortNm

        UNION ALL

        SELECT
            'LOW_STOCK',
            CONCAT(x.Prod_ShortNm, ' is low (', CAST(x.Qty AS CHAR), ' left, reorder ', CAST(x.ReOrder_Qty AS CHAR), ')'),
            pDate,
            'stock',
            4
        FROM (
            SELECT
                p.Prod_Id,
                p.Prod_ShortNm,
                IFNULL(p.ReOrder_Qty, 0) AS ReOrder_Qty,
                UDF_CAL_AGENT_STOCK(pAgent_Id, p.Prod_Id, pDate) AS Qty
            FROM mst_product p
            WHERE IFNULL(p.ReOrder_Qty, 0) > 0
              AND EXISTS (
                    SELECT 1 FROM trans_agent_stock s
                    WHERE s.Agent_Id = pAgent_Id AND s.Prod_Id = p.Prod_Id
              )
        ) x
        WHERE x.Qty > 0 AND x.Qty <= x.ReOrder_Qty
    ) n
    ORDER BY n.Sort_No, n.NDate DESC
    LIMIT 20;
END ;;
DELIMITER ;
/*!50003 SET sql_mode              = @saved_sql_mode */ ;
/*!50003 SET character_set_client  = @saved_cs_client */ ;
/*!50003 SET character_set_results = @saved_cs_results */ ;
/*!50003 SET collation_connection  = @saved_col_connection */ ;
/*!50003 DROP PROCEDURE IF EXISTS `USP_GET_AGENT_SALE_RETURNABLE_QTY` */;
/*!50003 SET @saved_cs_client      = @@character_set_client */ ;
/*!50003 SET @saved_cs_results     = @@character_set_results */ ;
/*!50003 SET @saved_col_connection = @@collation_connection */ ;
/*!50003 SET character_set_client  = utf8mb4 */ ;
/*!50003 SET character_set_results = utf8mb4 */ ;
/*!50003 SET collation_connection  = utf8mb4_unicode_ci */ ;
/*!50003 SET @saved_sql_mode       = @@sql_mode */ ;
/*!50003 SET sql_mode              = 'ONLY_FULL_GROUP_BY,STRICT_TRANS_TABLES,NO_ZERO_IN_DATE,NO_ZERO_DATE,ERROR_FOR_DIVISION_BY_ZERO,NO_ENGINE_SUBSTITUTION' */ ;
DELIMITER ;;
CREATE DEFINER=`priority`@`%` PROCEDURE `USP_GET_AGENT_SALE_RETURNABLE_QTY`(
    pAgent_Id            INT,
    pParty_Id            INT,
    pProd_Id             INT,
    pExclude_Trading_Id  BIGINT
)
BEGIN
    DECLARE pSaleType   TINYINT;
    DECLARE pReturnType TINYINT;
    DECLARE pSold       DECIMAL(18,2);
    DECLARE pReturned   DECIMAL(18,2);

    SET pSaleType = (
        SELECT Value_Id FROM smartinventory_core.mst_options
         WHERE Option_Id = 6 AND Value_Name = 'Sales Entry' LIMIT 1
    );
    SET pReturnType = (
        SELECT Value_Id FROM smartinventory_core.mst_options
         WHERE Option_Id = 6 AND Value_Name = 'Sales Return Entry' LIMIT 1
    );
    SET pSaleType   = IFNULL(pSaleType, 3);
    SET pReturnType = IFNULL(pReturnType, 5);

    SET pSold = IFNULL((
        SELECT SUM(d.Item_Qty)
        FROM trans_trading m
        INNER JOIN trans_trading_products d ON d.Trading_Id = m.Trading_Id
        WHERE m.Tranding_Type = pSaleType
          AND IFNULL(m.Status_Cd, 1) = 1
          AND m.Agent_Id = pAgent_Id
          AND m.Party_Id = pParty_Id
          AND d.Prod_Id = pProd_Id
    ), 0);

    SET pReturned = IFNULL((
        SELECT SUM(d.Item_Qty)
        FROM trans_trading m
        INNER JOIN trans_trading_products d ON d.Trading_Id = m.Trading_Id
        WHERE m.Tranding_Type = pReturnType
          AND IFNULL(m.Status_Cd, 1) = 1
          AND (m.Agent_Id = pAgent_Id OR m.Created_By = pAgent_Id)
          AND m.Party_Id = pParty_Id
          AND d.Prod_Id = pProd_Id
          AND (pExclude_Trading_Id = 0 OR m.Trading_Id <> pExclude_Trading_Id)
    ), 0);

    SELECT
        pSold AS Sold_Qty,
        pReturned AS Returned_Qty,
        CASE
            WHEN pSold > pReturned THEN pSold - pReturned
            ELSE 0
        END AS Remaining_Qty;
END ;;
DELIMITER ;
/*!50003 SET sql_mode              = @saved_sql_mode */ ;
/*!50003 SET character_set_client  = @saved_cs_client */ ;
/*!50003 SET character_set_results = @saved_cs_results */ ;
/*!50003 SET collation_connection  = @saved_col_connection */ ;
/*!50003 DROP PROCEDURE IF EXISTS `USP_GET_AGENT_STOCK_REPORT` */;
/*!50003 SET @saved_cs_client      = @@character_set_client */ ;
/*!50003 SET @saved_cs_results     = @@character_set_results */ ;
/*!50003 SET @saved_col_connection = @@collation_connection */ ;
/*!50003 SET character_set_client  = utf8mb4 */ ;
/*!50003 SET character_set_results = utf8mb4 */ ;
/*!50003 SET collation_connection  = utf8mb4_0900_ai_ci */ ;
/*!50003 SET @saved_sql_mode       = @@sql_mode */ ;
/*!50003 SET sql_mode              = 'ONLY_FULL_GROUP_BY,STRICT_TRANS_TABLES,NO_ZERO_IN_DATE,NO_ZERO_DATE,ERROR_FOR_DIVISION_BY_ZERO,NO_ENGINE_SUBSTITUTION' */ ;
DELIMITER ;;
CREATE DEFINER=`priority`@`%` PROCEDURE `USP_GET_AGENT_STOCK_REPORT`(
    pAgent_Id   INT,
    pDate       DATE
)
BEGIN
    SELECT x.Prod_Id, x.Prod_Code, x.Prod_ShortNm, x.Unit_Id, x.Unit_Name, x.Qty
    FROM (
        SELECT
            p.Prod_Id,
            p.Prod_Code,
            p.Prod_ShortNm,
            p.Unit_Id,
            UDF_GET_UNIT_NAME(p.Unit_Id) AS Unit_Name,
            (
                IFNULL((
                    SELECT SUM(i.Quantity)
                    FROM trans_agent_stock s
                    JOIN trns_stockinout i ON i.Stock_Id = s.TrnsStock_Id
                    WHERE s.Agent_Id = pAgent_Id
                      AND s.Prod_Id = p.Prod_Id
                      AND DATE(s.Trans_Date) <= pDate
                      AND i.Stock_Type = 4
                ), 0)
              - IFNULL((
                    SELECT SUM(i.Quantity)
                    FROM trans_agent_stock s
                    JOIN trns_stockinout i ON i.Stock_Id = s.TrnsStock_Id
                    WHERE s.Agent_Id = pAgent_Id
                      AND s.Prod_Id = p.Prod_Id
                      AND DATE(s.Trans_Date) <= pDate
                      AND i.Stock_Type = 6
                ), 0)
              - IFNULL((
                    SELECT SUM(i.Quantity)
                    FROM trans_agent_stock s
                    JOIN trns_stockinout i ON i.Stock_Id = s.TrnsStock_Id
                    WHERE s.Agent_Id = pAgent_Id
                      AND s.Prod_Id = p.Prod_Id
                      AND DATE(s.Trans_Date) <= pDate
                      AND i.Stock_Type = 5
                ), 0)
              + IFNULL((
                    SELECT SUM(i.Quantity)
                    FROM trans_agent_stock s
                    JOIN trns_stockinout i ON i.Stock_Id = s.TrnsStock_Id
                    WHERE s.Agent_Id = pAgent_Id
                      AND s.Prod_Id = p.Prod_Id
                      AND DATE(s.Trans_Date) <= pDate
                      AND i.Stock_Type = 8
                ), 0)
            ) AS Qty
        FROM mst_product p
        WHERE EXISTS (
            SELECT 1
            FROM trans_agent_stock s
            WHERE s.Agent_Id = pAgent_Id
              AND s.Prod_Id = p.Prod_Id
              AND DATE(s.Trans_Date) <= pDate
        )
    ) x
    WHERE x.Qty <> 0
    ORDER BY x.Prod_ShortNm;
END ;;
DELIMITER ;
/*!50003 SET sql_mode              = @saved_sql_mode */ ;
/*!50003 SET character_set_client  = @saved_cs_client */ ;
/*!50003 SET character_set_results = @saved_cs_results */ ;
/*!50003 SET collation_connection  = @saved_col_connection */ ;
/*!50003 DROP PROCEDURE IF EXISTS `USP_GET_ALL_MENUE` */;
/*!50003 SET @saved_cs_client      = @@character_set_client */ ;
/*!50003 SET @saved_cs_results     = @@character_set_results */ ;
/*!50003 SET @saved_col_connection = @@collation_connection */ ;
/*!50003 SET character_set_client  = utf8mb4 */ ;
/*!50003 SET character_set_results = utf8mb4 */ ;
/*!50003 SET collation_connection  = utf8mb4_0900_ai_ci */ ;
/*!50003 SET @saved_sql_mode       = @@sql_mode */ ;
/*!50003 SET sql_mode              = 'ONLY_FULL_GROUP_BY,STRICT_TRANS_TABLES,NO_ZERO_IN_DATE,NO_ZERO_DATE,ERROR_FOR_DIVISION_BY_ZERO,NO_ENGINE_SUBSTITUTION' */ ;
DELIMITER ;;
CREATE DEFINER=`priority`@`%` PROCEDURE `USP_GET_ALL_MENUE`()
BEGIN

Select Menu_Id As Parraint_Id,Menu_Name As Parraint_Name,SubMenu_Id As Child_Id,SubMenu_Name As Child_Name From mst_menus Where Is_Active;

END ;;
DELIMITER ;
/*!50003 SET sql_mode              = @saved_sql_mode */ ;
/*!50003 SET character_set_client  = @saved_cs_client */ ;
/*!50003 SET character_set_results = @saved_cs_results */ ;
/*!50003 SET collation_connection  = @saved_col_connection */ ;
/*!50003 DROP PROCEDURE IF EXISTS `USP_GET_BANK_LEDGER` */;
/*!50003 SET @saved_cs_client      = @@character_set_client */ ;
/*!50003 SET @saved_cs_results     = @@character_set_results */ ;
/*!50003 SET @saved_col_connection = @@collation_connection */ ;
/*!50003 SET character_set_client  = utf8mb4 */ ;
/*!50003 SET character_set_results = utf8mb4 */ ;
/*!50003 SET collation_connection  = utf8mb4_0900_ai_ci */ ;
/*!50003 SET @saved_sql_mode       = @@sql_mode */ ;
/*!50003 SET sql_mode              = 'ONLY_FULL_GROUP_BY,STRICT_TRANS_TABLES,NO_ZERO_IN_DATE,NO_ZERO_DATE,ERROR_FOR_DIVISION_BY_ZERO,NO_ENGINE_SUBSTITUTION' */ ;
DELIMITER ;;
CREATE DEFINER=`priority`@`%` PROCEDURE `USP_GET_BANK_LEDGER`(
	
)
BEGIN

	Select Account_Id,Concat(Account_Code,' - ',Account_Desc) As Ledger_Name From mst_acct_glhead Where Account_For='B' And Is_Active;

END ;;
DELIMITER ;
/*!50003 SET sql_mode              = @saved_sql_mode */ ;
/*!50003 SET character_set_client  = @saved_cs_client */ ;
/*!50003 SET character_set_results = @saved_cs_results */ ;
/*!50003 SET collation_connection  = @saved_col_connection */ ;
/*!50003 DROP PROCEDURE IF EXISTS `USP_GET_BARCODE_PRINT_LIST` */;
/*!50003 SET @saved_cs_client      = @@character_set_client */ ;
/*!50003 SET @saved_cs_results     = @@character_set_results */ ;
/*!50003 SET @saved_col_connection = @@collation_connection */ ;
/*!50003 SET character_set_client  = utf8mb4 */ ;
/*!50003 SET character_set_results = utf8mb4 */ ;
/*!50003 SET collation_connection  = utf8mb4_0900_ai_ci */ ;
/*!50003 SET @saved_sql_mode       = @@sql_mode */ ;
/*!50003 SET sql_mode              = 'ONLY_FULL_GROUP_BY,STRICT_TRANS_TABLES,NO_ZERO_IN_DATE,NO_ZERO_DATE,ERROR_FOR_DIVISION_BY_ZERO,NO_ENGINE_SUBSTITUTION' */ ;
DELIMITER ;;
CREATE DEFINER=`priority`@`%` PROCEDURE `USP_GET_BARCODE_PRINT_LIST`(
	pType			Int
)
BEGIN

	If(pType=1) Then
		Select m.Stock_Id,m.InOut_Date,m.Quantity,p.Prod_Code,p.Prod_ShortNm,m.MRP,m.Pack_Date,m.Barcode From trns_stockinout m Join mst_product p On p.Prod_Id=m.Prod_Id Where m.Stock_Type=1 And m.Barcode Is Not Null;
    End If;
    
    If(pType=2) Then
				Select t.Trading_Id,t.Invoice_No,t.Ref_No,t.Invoice_Date,UDF_GET_PARTY_NAME(t.Party_Id) As Party_Name,t.Net_Amt,(Select JSON_ARRAYAGG(
    JSON_OBJECT(
        'Prod_Id', m.Prod_Id,
		'Stock_Id',m.Stock_Id,
        'Prod_ShortNm',p.Prod_ShortNm,
        'Prod_Code',p.Prod_Code,
        'Quantity',m.Quantity,
        'MRP',m.MRP,
        'Pack_Date',m.Pack_Date,
        'Barcode',m.Barcode
    )
) From trns_stockinout m Join mst_product p On p.Prod_Id=m.Prod_Id Where m.Stock_Type=2 And m.Type_Id=t.Trading_Id And m.Barcode Is Not Null) As Item_Details From trans_trading t Where t.Tranding_Type=2 And t.Status_Cd=1 And t.Trading_Id In (Select Type_Id From trns_stockinout Where Stock_Type=2 And Barcode Is Not Null);
    End If;

END ;;
DELIMITER ;
/*!50003 SET sql_mode              = @saved_sql_mode */ ;
/*!50003 SET character_set_client  = @saved_cs_client */ ;
/*!50003 SET character_set_results = @saved_cs_results */ ;
/*!50003 SET collation_connection  = @saved_col_connection */ ;
/*!50003 DROP PROCEDURE IF EXISTS `USP_GET_COUNTER_BALANCE` */;
/*!50003 SET @saved_cs_client      = @@character_set_client */ ;
/*!50003 SET @saved_cs_results     = @@character_set_results */ ;
/*!50003 SET @saved_col_connection = @@collation_connection */ ;
/*!50003 SET character_set_client  = utf8mb4 */ ;
/*!50003 SET character_set_results = utf8mb4 */ ;
/*!50003 SET collation_connection  = utf8mb4_unicode_ci */ ;
/*!50003 SET @saved_sql_mode       = @@sql_mode */ ;
/*!50003 SET sql_mode              = 'ONLY_FULL_GROUP_BY,STRICT_TRANS_TABLES,NO_ZERO_IN_DATE,NO_ZERO_DATE,ERROR_FOR_DIVISION_BY_ZERO,NO_ENGINE_SUBSTITUTION' */ ;
DELIMITER ;;
CREATE DEFINER=`priority`@`%` PROCEDURE `USP_GET_COUNTER_BALANCE`()
BEGIN
    SELECT
        b.id,
        b.counter_id,
        u.User_Code AS Counter_Code,
        u.User_FullName AS Counter_Name,
        b.balance,
        b.`date`
    FROM trn_counter_balance b
    LEFT JOIN mst_user u ON u.User_Id = b.counter_id
    ORDER BY b.`date` DESC, b.id DESC;
END ;;
DELIMITER ;
/*!50003 SET sql_mode              = @saved_sql_mode */ ;
/*!50003 SET character_set_client  = @saved_cs_client */ ;
/*!50003 SET character_set_results = @saved_cs_results */ ;
/*!50003 SET collation_connection  = @saved_col_connection */ ;
/*!50003 DROP PROCEDURE IF EXISTS `USP_GET_DAMAGE_DTLS` */;
/*!50003 SET @saved_cs_client      = @@character_set_client */ ;
/*!50003 SET @saved_cs_results     = @@character_set_results */ ;
/*!50003 SET @saved_col_connection = @@collation_connection */ ;
/*!50003 SET character_set_client  = utf8mb4 */ ;
/*!50003 SET character_set_results = utf8mb4 */ ;
/*!50003 SET collation_connection  = utf8mb4_unicode_ci */ ;
/*!50003 SET @saved_sql_mode       = @@sql_mode */ ;
/*!50003 SET sql_mode              = 'ONLY_FULL_GROUP_BY,STRICT_TRANS_TABLES,NO_ZERO_IN_DATE,NO_ZERO_DATE,ERROR_FOR_DIVISION_BY_ZERO,NO_ENGINE_SUBSTITUTION' */ ;
DELIMITER ;;
CREATE DEFINER=`priority`@`%` PROCEDURE `USP_GET_DAMAGE_DTLS`(
    pDmg_Id INT
)
BEGIN
    SELECT
        m.Trading_Id AS Dmg_Id,
        m.Invoice_No,
        m.Invoice_Date,
        IFNULL(m.Remarks, '') AS Particulars,
        IFNULL(m.Net_Amt, 0) AS Net_Amt,
        m.Voucher_Id,
        (
            SELECT JSON_ARRAYAGG(
                JSON_OBJECT(
                    'Prod_Id', p.Prod_Id,
                    'Prod_Code', i.Prod_Code,
                    'Prod_Name', i.Prod_ShortNm,
                    'qnty', p.Item_Qty,
                    'Item_Rate', p.Item_Rate,
                    'Unit_Id', p.Unit_Id,
                    'Unit_Name', UDF_GET_UNIT_NAME(p.Unit_Id),
                    'Item_Total', p.Item_Total
                )
            )
            FROM trans_trading_products p
            JOIN mst_product i ON i.Prod_Id = p.Prod_Id
            WHERE p.Trading_Id = m.Trading_Id
        ) AS Item_Details
    FROM trans_trading m
    WHERE m.Trading_Id = pDmg_Id;
END ;;
DELIMITER ;
/*!50003 SET sql_mode              = @saved_sql_mode */ ;
/*!50003 SET character_set_client  = @saved_cs_client */ ;
/*!50003 SET character_set_results = @saved_cs_results */ ;
/*!50003 SET collation_connection  = @saved_col_connection */ ;
/*!50003 DROP PROCEDURE IF EXISTS `USP_GET_DASHBOARD_STATS` */;
/*!50003 SET @saved_cs_client      = @@character_set_client */ ;
/*!50003 SET @saved_cs_results     = @@character_set_results */ ;
/*!50003 SET @saved_col_connection = @@collation_connection */ ;
/*!50003 SET character_set_client  = utf8mb4 */ ;
/*!50003 SET character_set_results = utf8mb4 */ ;
/*!50003 SET collation_connection  = utf8mb4_unicode_ci */ ;
/*!50003 SET @saved_sql_mode       = @@sql_mode */ ;
/*!50003 SET sql_mode              = 'ONLY_FULL_GROUP_BY,STRICT_TRANS_TABLES,NO_ZERO_IN_DATE,NO_ZERO_DATE,ERROR_FOR_DIVISION_BY_ZERO,NO_ENGINE_SUBSTITUTION' */ ;
DELIMITER ;;
CREATE DEFINER=`priority`@`%` PROCEDURE `USP_GET_DASHBOARD_STATS`(
    pBranch_Id  SMALLINT,
    pYear_Id    TINYINT
)
BEGIN
    DECLARE pToday      DATE;
    DECLARE pMonthStart DATE;
    DECLARE pYearStart  DATE;
    DECLARE pYearEnd    DATE;

    SET pToday      = CURDATE();
    SET pMonthStart = DATE_FORMAT(pToday, '%Y-%m-01');

    SELECT Year_Start, Year_End
      INTO pYearStart, pYearEnd
      FROM mst_accountingyear
     WHERE Year_Id = pYear_Id;

    SELECT
        IFNULL((
            SELECT SUM(Net_Amt) FROM trans_trading
            WHERE Branch_Id = pBranch_Id AND Tranding_Type = 3 AND Status_Cd = 1
              AND Invoice_Date BETWEEN pMonthStart AND pToday
              AND Invoice_Date BETWEEN pYearStart AND pYearEnd
        ), 0) AS counter_sales_month,
        IFNULL((
            SELECT SUM(Net_Amt) FROM trans_trading
            WHERE Branch_Id = pBranch_Id AND Tranding_Type = 2 AND Status_Cd = 1
              AND Invoice_Date BETWEEN pMonthStart AND pToday
              AND Invoice_Date BETWEEN pYearStart AND pYearEnd
        ), 0) AS grn_month,
        IFNULL((
            SELECT SUM(s.Quantity * IFNULL(s.MRP, 0))
            FROM trns_stockinout s
            WHERE s.Branch_Id = pBranch_Id AND s.Stock_Type = 4
              AND s.InOut_Date BETWEEN pMonthStart AND pToday
              AND s.InOut_Date BETWEEN pYearStart AND pYearEnd
        ), 0) AS agent_indent_month,
        IFNULL((
            SELECT SUM(Net_Amt) FROM trans_trading
            WHERE Branch_Id = pBranch_Id AND Tranding_Type IN (4, 5, 6) AND Status_Cd = 1
              AND Invoice_Date BETWEEN pMonthStart AND pToday
              AND Invoice_Date BETWEEN pYearStart AND pYearEnd
        ), 0) AS returns_month,
        IFNULL((
            SELECT SUM(Net_Amt) FROM trans_trading
            WHERE Branch_Id = pBranch_Id AND Tranding_Type = 3 AND Status_Cd = 1
              AND Invoice_Date = pToday
        ), 0) AS today_sales,
        IFNULL((
            SELECT COUNT(*) FROM trans_trading
            WHERE Branch_Id = pBranch_Id AND Tranding_Type = 3 AND Status_Cd = 1
              AND Invoice_Date = pToday
        ), 0) AS today_sales_bills,
        IFNULL((
            SELECT SUM(Net_Amt) FROM trans_trading
            WHERE Branch_Id = pBranch_Id AND Tranding_Type = 2 AND Status_Cd = 1
              AND Invoice_Date = pToday
        ), 0) AS today_grn,
        IFNULL((
            SELECT COUNT(*) FROM trans_trading
            WHERE Branch_Id = pBranch_Id AND Tranding_Type = 2 AND Status_Cd = 1
              AND Invoice_Date = pToday
        ), 0) AS today_grn_invoices,
        IFNULL((
            SELECT COUNT(*) FROM trns_stockinout
            WHERE Stock_Type IN (1, 2) AND Barcode IS NULL
              AND (pBranch_Id = 0 OR Branch_Id = pBranch_Id)
        ), 0) AS pending_barcodes,
        IFNULL((
            SELECT COUNT(*) FROM (
                SELECT UDF_CAL_AVAIL_STOCK(p.Prod_Id, pToday) AS On_Hand, p.ReOrder_Qty
                FROM mst_product p
                WHERE p.Is_Active = 1 AND IFNULL(p.ReOrder_Qty, 0) > 0
            ) x
            WHERE x.On_Hand < x.ReOrder_Qty
        ), 0) AS low_stock_count;
END ;;
DELIMITER ;
/*!50003 SET sql_mode              = @saved_sql_mode */ ;
/*!50003 SET character_set_client  = @saved_cs_client */ ;
/*!50003 SET character_set_results = @saved_cs_results */ ;
/*!50003 SET collation_connection  = @saved_col_connection */ ;
/*!50003 DROP PROCEDURE IF EXISTS `USP_GET_GENERAL_VOUCHER_DTLS` */;
/*!50003 SET @saved_cs_client      = @@character_set_client */ ;
/*!50003 SET @saved_cs_results     = @@character_set_results */ ;
/*!50003 SET @saved_col_connection = @@collation_connection */ ;
/*!50003 SET character_set_client  = utf8mb4 */ ;
/*!50003 SET character_set_results = utf8mb4 */ ;
/*!50003 SET collation_connection  = utf8mb4_0900_ai_ci */ ;
/*!50003 SET @saved_sql_mode       = @@sql_mode */ ;
/*!50003 SET sql_mode              = 'ONLY_FULL_GROUP_BY,STRICT_TRANS_TABLES,NO_ZERO_IN_DATE,NO_ZERO_DATE,ERROR_FOR_DIVISION_BY_ZERO,NO_ENGINE_SUBSTITUTION' */ ;
DELIMITER ;;
CREATE DEFINER=`priority`@`%` PROCEDURE `USP_GET_GENERAL_VOUCHER_DTLS`(
    pVouch_Id BIGINT
)
BEGIN
    SELECT m.Voucher_Id,
           DATE_FORMAT(m.Vou_Date, '%Y-%m-%d') AS Vou_Date,
           m.Vou_Type,
           m.Vou_Mode,
           m.Vou_No,
           m.Ref_Vou_No,
           m.Particulars,
           CASE WHEN m.Vou_Type = 1 THEN cr.GlHead_Id ELSE dr.GlHead_Id END AS Ledger_Id,
           CASE WHEN m.Vou_Mode = 2 THEN
                CASE WHEN m.Vou_Type = 1 THEN dr.GlHead_Id ELSE cr.GlHead_Id END
           ELSE 0 END AS Bank_Ledg,
           dr.Vou_Amount AS Amount
    FROM trans_voucher_master m
    INNER JOIN trans_voucher_details dr ON dr.Voucher_Id = m.Voucher_Id AND dr.Trans_Type = 'D'
    INNER JOIN trans_voucher_details cr ON cr.Voucher_Id = m.Voucher_Id AND cr.Trans_Type = 'C'
    WHERE m.Voucher_Id = pVouch_Id
      AND m.Trans_Type = 9;
END ;;
DELIMITER ;
/*!50003 SET sql_mode              = @saved_sql_mode */ ;
/*!50003 SET character_set_client  = @saved_cs_client */ ;
/*!50003 SET character_set_results = @saved_cs_results */ ;
/*!50003 SET collation_connection  = @saved_col_connection */ ;
/*!50003 DROP PROCEDURE IF EXISTS `USP_GET_GENERAL_VOUCHER_LIST` */;
/*!50003 SET @saved_cs_client      = @@character_set_client */ ;
/*!50003 SET @saved_cs_results     = @@character_set_results */ ;
/*!50003 SET @saved_col_connection = @@collation_connection */ ;
/*!50003 SET character_set_client  = utf8mb4 */ ;
/*!50003 SET character_set_results = utf8mb4 */ ;
/*!50003 SET collation_connection  = utf8mb4_0900_ai_ci */ ;
/*!50003 SET @saved_sql_mode       = @@sql_mode */ ;
/*!50003 SET sql_mode              = 'ONLY_FULL_GROUP_BY,STRICT_TRANS_TABLES,NO_ZERO_IN_DATE,NO_ZERO_DATE,ERROR_FOR_DIVISION_BY_ZERO,NO_ENGINE_SUBSTITUTION' */ ;
DELIMITER ;;
CREATE DEFINER=`priority`@`%` PROCEDURE `USP_GET_GENERAL_VOUCHER_LIST`(
    pFin_Id INT
)
BEGIN
    SELECT m.Voucher_Id,
           DATE_FORMAT(m.Vou_Date, '%Y-%m-%d') AS Vou_Date,
           DATE_FORMAT(m.Vou_Date, '%d-%m-%Y') AS Vou_Date_Disp,
           m.Vou_No,
           m.Ref_Vou_No,
           m.Vou_Type,
           CASE m.Vou_Type WHEN 1 THEN 'Receipt' WHEN 2 THEN 'Payment' END AS Vou_Type_Name,
           m.Vou_Mode,
           d.Vou_Amount AS Amount
    FROM trans_voucher_master m
    INNER JOIN trans_voucher_details d
        ON d.Voucher_Id = m.Voucher_Id
       AND d.Trans_Type = CASE WHEN m.Vou_Type = 1 THEN 'C' ELSE 'D' END
    WHERE m.Trans_Type = 9
      AND IFNULL(m.Type_Id, 0) = 0
      AND m.Year_Id = pFin_Id
      AND m.Status = 2
    ORDER BY m.Voucher_Id DESC
    LIMIT 10;
END ;;
DELIMITER ;
/*!50003 SET sql_mode              = @saved_sql_mode */ ;
/*!50003 SET character_set_client  = @saved_cs_client */ ;
/*!50003 SET character_set_results = @saved_cs_results */ ;
/*!50003 SET collation_connection  = @saved_col_connection */ ;
/*!50003 DROP PROCEDURE IF EXISTS `USP_GET_GRPWISE_MENUE` */;
/*!50003 SET @saved_cs_client      = @@character_set_client */ ;
/*!50003 SET @saved_cs_results     = @@character_set_results */ ;
/*!50003 SET @saved_col_connection = @@collation_connection */ ;
/*!50003 SET character_set_client  = utf8mb4 */ ;
/*!50003 SET character_set_results = utf8mb4 */ ;
/*!50003 SET collation_connection  = utf8mb4_0900_ai_ci */ ;
/*!50003 SET @saved_sql_mode       = @@sql_mode */ ;
/*!50003 SET sql_mode              = 'ONLY_FULL_GROUP_BY,STRICT_TRANS_TABLES,NO_ZERO_IN_DATE,NO_ZERO_DATE,ERROR_FOR_DIVISION_BY_ZERO,NO_ENGINE_SUBSTITUTION' */ ;
DELIMITER ;;
CREATE DEFINER=`priority`@`%` PROCEDURE `USP_GET_GRPWISE_MENUE`(
	pGrp_Id			Int
)
BEGIN

SELECT 
    s.Menu_Id,
    m.Menu_Name,
    s.SubMenu_Id,
    s.SubMenu_Name
FROM mst_menus s
JOIN mst_menus m 
    ON m.Menu_Id = s.Menu_Id 
    AND m.SubMenu_Id IS NULL
 JOIN mst_user_permission p 
    ON p.Menu_Id = m.Menu_Id  
    AND (
            (p.Sub_men_Id IS NULL AND s.SubMenu_Id IS NULL)
         OR (p.Sub_men_Id = s.SubMenu_Id)
        )
WHERE p.UGrp_Id = pGrp_Id;


END ;;
DELIMITER ;
/*!50003 SET sql_mode              = @saved_sql_mode */ ;
/*!50003 SET character_set_client  = @saved_cs_client */ ;
/*!50003 SET character_set_results = @saved_cs_results */ ;
/*!50003 SET collation_connection  = @saved_col_connection */ ;
/*!50003 DROP PROCEDURE IF EXISTS `USP_GET_GST_CODES` */;
/*!50003 SET @saved_cs_client      = @@character_set_client */ ;
/*!50003 SET @saved_cs_results     = @@character_set_results */ ;
/*!50003 SET @saved_col_connection = @@collation_connection */ ;
/*!50003 SET character_set_client  = utf8mb4 */ ;
/*!50003 SET character_set_results = utf8mb4 */ ;
/*!50003 SET collation_connection  = utf8mb4_0900_ai_ci */ ;
/*!50003 SET @saved_sql_mode       = @@sql_mode */ ;
/*!50003 SET sql_mode              = 'ONLY_FULL_GROUP_BY,STRICT_TRANS_TABLES,NO_ZERO_IN_DATE,NO_ZERO_DATE,ERROR_FOR_DIVISION_BY_ZERO,NO_ENGINE_SUBSTITUTION' */ ;
DELIMITER ;;
CREATE DEFINER=`priority`@`%` PROCEDURE `USP_GET_GST_CODES`()
BEGIN
    SELECT Id, Gst_Code, Category, Tax_Percent, Sgst, Cgst, Igst, Ugst, Is_Active,
           CONCAT(Gst_Code, ' - ', Tax_Percent) AS HSN
    FROM mst_gst_codes
    ORDER BY Id;
END ;;
DELIMITER ;
/*!50003 SET sql_mode              = @saved_sql_mode */ ;
/*!50003 SET character_set_client  = @saved_cs_client */ ;
/*!50003 SET character_set_results = @saved_cs_results */ ;
/*!50003 SET collation_connection  = @saved_col_connection */ ;
/*!50003 DROP PROCEDURE IF EXISTS `USP_GET_GST_MONTH` */;
/*!50003 SET @saved_cs_client      = @@character_set_client */ ;
/*!50003 SET @saved_cs_results     = @@character_set_results */ ;
/*!50003 SET @saved_col_connection = @@collation_connection */ ;
/*!50003 SET character_set_client  = utf8mb4 */ ;
/*!50003 SET character_set_results = utf8mb4 */ ;
/*!50003 SET collation_connection  = utf8mb4_0900_ai_ci */ ;
/*!50003 SET @saved_sql_mode       = @@sql_mode */ ;
/*!50003 SET sql_mode              = 'ONLY_FULL_GROUP_BY,STRICT_TRANS_TABLES,NO_ZERO_IN_DATE,NO_ZERO_DATE,ERROR_FOR_DIVISION_BY_ZERO,NO_ENGINE_SUBSTITUTION' */ ;
DELIMITER ;;
CREATE DEFINER=`priority`@`%` PROCEDURE `USP_GET_GST_MONTH`(
    pBranch_Id  SMALLINT,
    pYear_Id    TINYINT
)
BEGIN
    DECLARE pMonthStart DATE;
    DECLARE pToday      DATE;
    DECLARE pYearStart  DATE;
    DECLARE pYearEnd    DATE;

    SET pToday      = CURDATE();
    SET pMonthStart = DATE_FORMAT(pToday, '%Y-%m-01');

    SELECT Year_Start, Year_End
      INTO pYearStart, pYearEnd
      FROM mst_accountingyear
     WHERE Year_Id = pYear_Id;

    SELECT
        IFNULL((
            SELECT SUM(CASE
                WHEN Tranding_Type = 3 THEN GST_Amt
                WHEN Tranding_Type = 5 THEN -GST_Amt
            END)
            FROM trans_trading
            WHERE Branch_Id = pBranch_Id
              AND Status_Cd = 1
              AND Invoice_Date BETWEEN pMonthStart AND pToday
              AND Invoice_Date BETWEEN pYearStart AND pYearEnd
              AND Tranding_Type IN (3, 5)
        ), 0) AS Output_Gst,
        IFNULL((
            SELECT SUM(CASE
                WHEN Tranding_Type = 2 THEN GST_Amt
                WHEN Tranding_Type = 4 THEN -GST_Amt
            END)
            FROM trans_trading
            WHERE Branch_Id = pBranch_Id
              AND Status_Cd = 1
              AND Invoice_Date BETWEEN pMonthStart AND pToday
              AND Invoice_Date BETWEEN pYearStart AND pYearEnd
              AND Tranding_Type IN (2, 4)
        ), 0) AS Input_Gst,
        IFNULL((
            SELECT SUM(CASE
                WHEN t.Tranding_Type = 3 THEN g.CGST_Amt
                WHEN t.Tranding_Type = 5 THEN -g.CGST_Amt
            END)
            FROM trans_trading t
            JOIN trans_trading_gst g ON g.Trading_Id = t.Trading_Id
            WHERE t.Branch_Id = pBranch_Id
              AND t.Status_Cd = 1
              AND t.Invoice_Date BETWEEN pMonthStart AND pToday
              AND t.Invoice_Date BETWEEN pYearStart AND pYearEnd
              AND t.Tranding_Type IN (3, 5)
        ), 0) AS Cgst_Amt,
        IFNULL((
            SELECT SUM(CASE
                WHEN t.Tranding_Type = 3 THEN g.SGST_Amt
                WHEN t.Tranding_Type = 5 THEN -g.SGST_Amt
            END)
            FROM trans_trading t
            JOIN trans_trading_gst g ON g.Trading_Id = t.Trading_Id
            WHERE t.Branch_Id = pBranch_Id
              AND t.Status_Cd = 1
              AND t.Invoice_Date BETWEEN pMonthStart AND pToday
              AND t.Invoice_Date BETWEEN pYearStart AND pYearEnd
              AND t.Tranding_Type IN (3, 5)
        ), 0) AS Sgst_Amt;
END ;;
DELIMITER ;
/*!50003 SET sql_mode              = @saved_sql_mode */ ;
/*!50003 SET character_set_client  = @saved_cs_client */ ;
/*!50003 SET character_set_results = @saved_cs_results */ ;
/*!50003 SET collation_connection  = @saved_col_connection */ ;
/*!50003 DROP PROCEDURE IF EXISTS `USP_GET_ITEM_CAT` */;
/*!50003 SET @saved_cs_client      = @@character_set_client */ ;
/*!50003 SET @saved_cs_results     = @@character_set_results */ ;
/*!50003 SET @saved_col_connection = @@collation_connection */ ;
/*!50003 SET character_set_client  = utf8mb4 */ ;
/*!50003 SET character_set_results = utf8mb4 */ ;
/*!50003 SET collation_connection  = utf8mb4_0900_ai_ci */ ;
/*!50003 SET @saved_sql_mode       = @@sql_mode */ ;
/*!50003 SET sql_mode              = 'ONLY_FULL_GROUP_BY,STRICT_TRANS_TABLES,NO_ZERO_IN_DATE,NO_ZERO_DATE,ERROR_FOR_DIVISION_BY_ZERO,NO_ENGINE_SUBSTITUTION' */ ;
DELIMITER ;;
CREATE DEFINER=`priority`@`%` PROCEDURE `USP_GET_ITEM_CAT`(IN p_OrgId INT)
BEGIN
    SELECT Prd_CateId, Prd_CateNm, Is_Fmcg, Agent_Comm,  IFNULL(Pur_Ledg, 0) AS Pur_Ledg,
    IFNULL(Sale_Ledg, 0) AS Sale_Ledg
    FROM mst_prod_category
    ORDER BY Prd_CateId;
END ;;
DELIMITER ;
/*!50003 SET sql_mode              = @saved_sql_mode */ ;
/*!50003 SET character_set_client  = @saved_cs_client */ ;
/*!50003 SET character_set_results = @saved_cs_results */ ;
/*!50003 SET collation_connection  = @saved_col_connection */ ;
/*!50003 DROP PROCEDURE IF EXISTS `USP_GET_ITEM_DETAILS` */;
/*!50003 SET @saved_cs_client      = @@character_set_client */ ;
/*!50003 SET @saved_cs_results     = @@character_set_results */ ;
/*!50003 SET @saved_col_connection = @@collation_connection */ ;
/*!50003 SET character_set_client  = utf8mb4 */ ;
/*!50003 SET character_set_results = utf8mb4 */ ;
/*!50003 SET collation_connection  = utf8mb4_unicode_ci */ ;
/*!50003 SET @saved_sql_mode       = @@sql_mode */ ;
/*!50003 SET sql_mode              = 'ONLY_FULL_GROUP_BY,STRICT_TRANS_TABLES,NO_ZERO_IN_DATE,NO_ZERO_DATE,ERROR_FOR_DIVISION_BY_ZERO,NO_ENGINE_SUBSTITUTION' */ ;
DELIMITER ;;
CREATE DEFINER=`priority`@`%` PROCEDURE `USP_GET_ITEM_DETAILS`(
    pProd_Id INT
)
BEGIN
    SELECT
        Prod_Id,
        Prod_Code,
        Prod_ShortNm,
        Prod_PrintNm,
        Unit_Id,
        Cate_Id,
        SubCate_Id,
        Gst_Id,
        ReOrder_Qty,
        Sale_Margin,
        Prod_Life
    FROM mst_product
    WHERE Prod_Id = pProd_Id;
END ;;
DELIMITER ;
/*!50003 SET sql_mode              = @saved_sql_mode */ ;
/*!50003 SET character_set_client  = @saved_cs_client */ ;
/*!50003 SET character_set_results = @saved_cs_results */ ;
/*!50003 SET collation_connection  = @saved_col_connection */ ;
/*!50003 DROP PROCEDURE IF EXISTS `USP_GET_ITEM_LIST` */;
/*!50003 SET @saved_cs_client      = @@character_set_client */ ;
/*!50003 SET @saved_cs_results     = @@character_set_results */ ;
/*!50003 SET @saved_col_connection = @@collation_connection */ ;
/*!50003 SET character_set_client  = utf8mb4 */ ;
/*!50003 SET character_set_results = utf8mb4 */ ;
/*!50003 SET collation_connection  = utf8mb4_0900_ai_ci */ ;
/*!50003 SET @saved_sql_mode       = @@sql_mode */ ;
/*!50003 SET sql_mode              = 'ONLY_FULL_GROUP_BY,STRICT_TRANS_TABLES,NO_ZERO_IN_DATE,NO_ZERO_DATE,ERROR_FOR_DIVISION_BY_ZERO,NO_ENGINE_SUBSTITUTION' */ ;
DELIMITER ;;
CREATE DEFINER=`priority`@`%` PROCEDURE `USP_GET_ITEM_LIST`(IN pCat_Id INT, IN pSub_Cat INT, IN pProd_Code VARCHAR(20))
BEGIN
    SELECT
        m.Prod_Id, m.Prod_Code, m.Prod_ShortNm, m.Unit_Id,
        m.Cate_Id    AS Prd_CateId,
        m.SubCate_Id AS Prd_SubCateId,
        UDF_GET_UNIT_NAME(m.Unit_Id)       AS Unit_Name,
        UDF_GET_ITEM_GST_DETAILS(m.Gst_Id) AS GST_Data,
        c.Prd_CateNm, s.Prd_SubCateNm, c.Is_Fmcg, m.Gst_Id,
        IFNULL(m.Sale_Margin, 0) AS Sale_Margin
    FROM mst_product m
    INNER JOIN mst_prod_category c    ON c.Prd_CateId    = m.Cate_Id
    INNER JOIN mst_prod_subcategory s ON s.Prd_SubCateId = m.SubCate_Id
    WHERE (pCat_Id   = 0 OR m.Cate_Id    = pCat_Id)
      AND (pSub_Cat  = 0 OR m.SubCate_Id = pSub_Cat)
      AND (pProd_Code = '' OR m.Prod_Code LIKE CONCAT(pProd_Code, '%')
                           OR m.Prod_ShortNm LIKE CONCAT('%', pProd_Code, '%'));
END ;;
DELIMITER ;
/*!50003 SET sql_mode              = @saved_sql_mode */ ;
/*!50003 SET character_set_client  = @saved_cs_client */ ;
/*!50003 SET character_set_results = @saved_cs_results */ ;
/*!50003 SET collation_connection  = @saved_col_connection */ ;
/*!50003 DROP PROCEDURE IF EXISTS `USP_GET_ITEM_SUB_CAT` */;
/*!50003 SET @saved_cs_client      = @@character_set_client */ ;
/*!50003 SET @saved_cs_results     = @@character_set_results */ ;
/*!50003 SET @saved_col_connection = @@collation_connection */ ;
/*!50003 SET character_set_client  = utf8mb4 */ ;
/*!50003 SET character_set_results = utf8mb4 */ ;
/*!50003 SET collation_connection  = utf8mb4_0900_ai_ci */ ;
/*!50003 SET @saved_sql_mode       = @@sql_mode */ ;
/*!50003 SET sql_mode              = 'ONLY_FULL_GROUP_BY,STRICT_TRANS_TABLES,NO_ZERO_IN_DATE,NO_ZERO_DATE,ERROR_FOR_DIVISION_BY_ZERO,NO_ENGINE_SUBSTITUTION' */ ;
DELIMITER ;;
CREATE DEFINER=`priority`@`%` PROCEDURE `USP_GET_ITEM_SUB_CAT`(IN p_OrgId INT, IN p_CateId INT)
BEGIN
    SELECT s.Prd_SubCateId, s.Prd_SubCateNm, s.Prd_CateId, c.Prd_CateNm
    FROM mst_prod_subcategory s
    INNER JOIN mst_prod_category c ON c.Prd_CateId = s.Prd_CateId
    WHERE (p_CateId = 0 OR s.Prd_CateId = p_CateId)
    ORDER BY s.Prd_CateId;
END ;;
DELIMITER ;
/*!50003 SET sql_mode              = @saved_sql_mode */ ;
/*!50003 SET character_set_client  = @saved_cs_client */ ;
/*!50003 SET character_set_results = @saved_cs_results */ ;
/*!50003 SET collation_connection  = @saved_col_connection */ ;
/*!50003 DROP PROCEDURE IF EXISTS `USP_GET_ITEM_UNIT` */;
/*!50003 SET @saved_cs_client      = @@character_set_client */ ;
/*!50003 SET @saved_cs_results     = @@character_set_results */ ;
/*!50003 SET @saved_col_connection = @@collation_connection */ ;
/*!50003 SET character_set_client  = utf8mb4 */ ;
/*!50003 SET character_set_results = utf8mb4 */ ;
/*!50003 SET collation_connection  = utf8mb4_0900_ai_ci */ ;
/*!50003 SET @saved_sql_mode       = @@sql_mode */ ;
/*!50003 SET sql_mode              = 'ONLY_FULL_GROUP_BY,STRICT_TRANS_TABLES,NO_ZERO_IN_DATE,NO_ZERO_DATE,ERROR_FOR_DIVISION_BY_ZERO,NO_ENGINE_SUBSTITUTION' */ ;
DELIMITER ;;
CREATE DEFINER=`priority`@`%` PROCEDURE `USP_GET_ITEM_UNIT`(IN p_OrgId INT)
BEGIN
    SELECT Unit_Id, Unit_Name
    FROM mst_unit
    ORDER BY Unit_Id;
END ;;
DELIMITER ;
/*!50003 SET sql_mode              = @saved_sql_mode */ ;
/*!50003 SET character_set_client  = @saved_cs_client */ ;
/*!50003 SET character_set_results = @saved_cs_results */ ;
/*!50003 SET collation_connection  = @saved_col_connection */ ;
/*!50003 DROP PROCEDURE IF EXISTS `USP_GET_LAST_PURCHASE_RATE` */;
/*!50003 SET @saved_cs_client      = @@character_set_client */ ;
/*!50003 SET @saved_cs_results     = @@character_set_results */ ;
/*!50003 SET @saved_col_connection = @@collation_connection */ ;
/*!50003 SET character_set_client  = utf8mb4 */ ;
/*!50003 SET character_set_results = utf8mb4 */ ;
/*!50003 SET collation_connection  = utf8mb4_unicode_ci */ ;
/*!50003 SET @saved_sql_mode       = @@sql_mode */ ;
/*!50003 SET sql_mode              = 'ONLY_FULL_GROUP_BY,STRICT_TRANS_TABLES,NO_ZERO_IN_DATE,NO_ZERO_DATE,ERROR_FOR_DIVISION_BY_ZERO,NO_ENGINE_SUBSTITUTION' */ ;
DELIMITER ;;
CREATE DEFINER=`priority`@`%` PROCEDURE `USP_GET_LAST_PURCHASE_RATE`(
    pProd_Id INT,
    pDate    DATE
)
BEGIN
    SELECT
        p.Prod_Id,
        p.Prod_Code,
        p.Prod_ShortNm,
        p.Unit_Id,
        UDF_GET_UNIT_NAME(p.Unit_Id) AS Unit_Name,
        UDF_CAL_AVAIL_STOCK(p.Prod_Id, pDate) AS Avil_Qnty,
        IFNULL((
            SELECT s.Rate
            FROM trns_stockinout s
            WHERE s.Prod_Id = p.Prod_Id
              AND s.Stock_Type = 2
              AND IFNULL(s.Rate, 0) > 0
              AND (pDate IS NULL OR s.InOut_Date <= pDate)
            ORDER BY s.InOut_Date DESC, s.Stock_Id DESC
            LIMIT 1
        ), 0) AS Purchase_Rate
    FROM mst_product p
    WHERE p.Prod_Id = pProd_Id;
END ;;
DELIMITER ;
/*!50003 SET sql_mode              = @saved_sql_mode */ ;
/*!50003 SET character_set_client  = @saved_cs_client */ ;
/*!50003 SET character_set_results = @saved_cs_results */ ;
/*!50003 SET collation_connection  = @saved_col_connection */ ;
/*!50003 DROP PROCEDURE IF EXISTS `USP_GET_MASTER_COUNTS` */;
/*!50003 SET @saved_cs_client      = @@character_set_client */ ;
/*!50003 SET @saved_cs_results     = @@character_set_results */ ;
/*!50003 SET @saved_col_connection = @@collation_connection */ ;
/*!50003 SET character_set_client  = utf8mb4 */ ;
/*!50003 SET character_set_results = utf8mb4 */ ;
/*!50003 SET collation_connection  = utf8mb4_0900_ai_ci */ ;
/*!50003 SET @saved_sql_mode       = @@sql_mode */ ;
/*!50003 SET sql_mode              = 'ONLY_FULL_GROUP_BY,STRICT_TRANS_TABLES,NO_ZERO_IN_DATE,NO_ZERO_DATE,ERROR_FOR_DIVISION_BY_ZERO,NO_ENGINE_SUBSTITUTION' */ ;
DELIMITER ;;
CREATE DEFINER=`priority`@`%` PROCEDURE `USP_GET_MASTER_COUNTS`(
    pBranch_Id  SMALLINT,
    pYear_Id    TINYINT
)
BEGIN
    DECLARE pYearStart DATE;
    DECLARE pYearEnd   DATE;

    SELECT Year_Start, Year_End
      INTO pYearStart, pYearEnd
      FROM mst_accountingyear
     WHERE Year_Id = pYear_Id;

    SELECT
        IFNULL((
            SELECT COUNT(*) FROM mst_party
            WHERE Party_Type = 1 AND Status_Cd = 1
              AND (pBranch_Id = 0 OR Branch_Id = pBranch_Id)
        ), 0) AS Supplier_Cnt,
        IFNULL((
            SELECT COUNT(*) FROM mst_party
            WHERE Party_Type = 2 AND Status_Cd = 1
              AND (pBranch_Id = 0 OR Branch_Id = pBranch_Id)
        ), 0) AS Customer_Cnt,
        IFNULL((
            SELECT COUNT(*) FROM mst_agent
            WHERE Status_Cd = 1
              AND (pBranch_Id = 0 OR Branch_Id = pBranch_Id)
        ), 0) AS Agent_Cnt,
        IFNULL((
            SELECT COUNT(*) FROM mst_member
            WHERE Status_Cd = 1
        ), 0) AS Member_Cnt,
        IFNULL((
            SELECT COUNT(*) FROM trans_trading
            WHERE Tranding_Type = 4 AND Status_Cd = 1
              AND Branch_Id = pBranch_Id
              AND Invoice_Date BETWEEN pYearStart AND pYearEnd
        ), 0) AS Purchase_Return_Cnt;
END ;;
DELIMITER ;
/*!50003 SET sql_mode              = @saved_sql_mode */ ;
/*!50003 SET character_set_client  = @saved_cs_client */ ;
/*!50003 SET character_set_results = @saved_cs_results */ ;
/*!50003 SET collation_connection  = @saved_col_connection */ ;
/*!50003 DROP PROCEDURE IF EXISTS `USP_GET_MEMBER_LIST` */;
/*!50003 SET @saved_cs_client      = @@character_set_client */ ;
/*!50003 SET @saved_cs_results     = @@character_set_results */ ;
/*!50003 SET @saved_col_connection = @@collation_connection */ ;
/*!50003 SET character_set_client  = utf8mb4 */ ;
/*!50003 SET character_set_results = utf8mb4 */ ;
/*!50003 SET collation_connection  = utf8mb4_0900_ai_ci */ ;
/*!50003 SET @saved_sql_mode       = @@sql_mode */ ;
/*!50003 SET sql_mode              = 'ONLY_FULL_GROUP_BY,STRICT_TRANS_TABLES,NO_ZERO_IN_DATE,NO_ZERO_DATE,ERROR_FOR_DIVISION_BY_ZERO,NO_ENGINE_SUBSTITUTION' */ ;
DELIMITER ;;
CREATE DEFINER=`priority`@`%` PROCEDURE `USP_GET_MEMBER_LIST`(
    pBranch_Id SMALLINT
)
BEGIN
    SELECT
        m.Member_Id                                         AS Mem_Id,
        m.Member_Type                                       AS Mem_Type,
        m.Member_Code                                       AS Mem_No,
        m.Member_Name                                       AS Mem_Name,
        m.Guardian_Name                                     AS Gur_Name,
        m.Address,
        m.Contact_No                                        AS Mob_No,
        m.Aadhar_No                                         AS Adhar_No,
        m.Voter_No,
        m.Pan_No,
        DATE_FORMAT(m.Admission_Date, '%Y-%m-%d')           AS Adm_Date,
        IFNULL(m.Share_No, 0)                               AS Share_No,
        IFNULL(v.Vou_Mode, 1)                               AS Trans_Mode,
        IFNULL(v.Ref_Vou_No, '')                            AS Ref_Vou_No,
        IFNULL(d.GlHead_Id, 0)                             AS Bank_Id,
        m.Ref_Member_No                                     AS Ref_Mem_No,
        IFNULL(
            NULLIF(SUBSTRING_INDEX(v.Particulars, ' | ', -1), CONCAT('Member Admission - ', m.Member_Name)),
            ''
        )                                                   AS Bank_Remarks
    FROM mst_member m
    LEFT JOIN trans_voucher_master v
        ON v.Trans_Type = 9 AND v.Type_Id = m.Member_Id
    LEFT JOIN trans_voucher_details d
        ON d.Voucher_Id = v.Voucher_Id AND d.Trans_Type = 'D' AND v.Vou_Mode = 2
    ORDER BY m.Member_Id DESC;
END ;;
DELIMITER ;
/*!50003 SET sql_mode              = @saved_sql_mode */ ;
/*!50003 SET character_set_client  = @saved_cs_client */ ;
/*!50003 SET character_set_results = @saved_cs_results */ ;
/*!50003 SET collation_connection  = @saved_col_connection */ ;
/*!50003 DROP PROCEDURE IF EXISTS `USP_GET_MONTHLY_PURCHASE_SALE` */;
/*!50003 SET @saved_cs_client      = @@character_set_client */ ;
/*!50003 SET @saved_cs_results     = @@character_set_results */ ;
/*!50003 SET @saved_col_connection = @@collation_connection */ ;
/*!50003 SET character_set_client  = utf8mb4 */ ;
/*!50003 SET character_set_results = utf8mb4 */ ;
/*!50003 SET collation_connection  = utf8mb4_0900_ai_ci */ ;
/*!50003 SET @saved_sql_mode       = @@sql_mode */ ;
/*!50003 SET sql_mode              = 'ONLY_FULL_GROUP_BY,STRICT_TRANS_TABLES,NO_ZERO_IN_DATE,NO_ZERO_DATE,ERROR_FOR_DIVISION_BY_ZERO,NO_ENGINE_SUBSTITUTION' */ ;
DELIMITER ;;
CREATE DEFINER=`priority`@`%` PROCEDURE `USP_GET_MONTHLY_PURCHASE_SALE`(
    IN pBranch_Id SMALLINT,
    IN pYear_Id   TINYINT
)
BEGIN
    DECLARE vYearStart DATE;
    DECLARE vYearEnd   DATE;

    DECLARE CONTINUE HANDLER FOR NOT FOUND
    BEGIN
        SET vYearStart = NULL;
        SET vYearEnd = NULL;
    END;

    SELECT Year_Start, Year_End
      INTO vYearStart, vYearEnd
      FROM mst_accountingyear
     WHERE Year_Id = pYear_Id
     LIMIT 1;

    IF vYearStart IS NULL OR vYearEnd IS NULL THEN
        SELECT
            CAST(NULL AS CHAR(20)) AS Month_Label,
            CAST(NULL AS CHAR(7))  AS Month_Key,
            CAST(0 AS DECIMAL(12,2)) AS Sales_Amt,
            CAST(0 AS DECIMAL(12,2)) AS Purchase_Amt,
            CAST(0 AS DECIMAL(12,2)) AS Counter_Amt,
            CAST(0 AS DECIMAL(12,2)) AS Agent_Amt
        WHERE 1 = 0;
    ELSE
        WITH RECURSIVE months AS (
            SELECT DATE_FORMAT(vYearStart, '%Y-%m-01') AS Month_Start
            UNION ALL
            SELECT DATE_ADD(Month_Start, INTERVAL 1 MONTH)
              FROM months
             WHERE DATE_ADD(Month_Start, INTERVAL 1 MONTH) <= vYearEnd
        )
        SELECT
            DATE_FORMAT(m.Month_Start, '%b %Y') AS Month_Label,
            DATE_FORMAT(m.Month_Start, '%Y-%m') AS Month_Key,
            IFNULL((
                SELECT SUM(t.Net_Amt)
                  FROM trans_trading t
                 WHERE t.Branch_Id = pBranch_Id
                   AND t.Tranding_Type = 3
                   AND t.Status_Cd = 1
                   AND t.Invoice_Date >= GREATEST(m.Month_Start, vYearStart)
                   AND t.Invoice_Date <= LEAST(LAST_DAY(m.Month_Start), vYearEnd)
            ), 0) AS Sales_Amt,
            IFNULL((
                SELECT SUM(t.Net_Amt)
                  FROM trans_trading t
                 WHERE t.Branch_Id = pBranch_Id
                   AND t.Tranding_Type = 2
                   AND t.Status_Cd = 1
                   AND t.Invoice_Date >= GREATEST(m.Month_Start, vYearStart)
                   AND t.Invoice_Date <= LEAST(LAST_DAY(m.Month_Start), vYearEnd)
            ), 0) AS Purchase_Amt,
            IFNULL((
                SELECT SUM(t.Net_Amt)
                  FROM trans_trading t
                 WHERE t.Branch_Id = pBranch_Id
                   AND t.Tranding_Type = 3
                   AND t.Status_Cd = 1
                   AND IFNULL(t.Agent_Id, 0) = 0
                   AND t.Invoice_Date >= GREATEST(m.Month_Start, vYearStart)
                   AND t.Invoice_Date <= LEAST(LAST_DAY(m.Month_Start), vYearEnd)
            ), 0) AS Counter_Amt,
            IFNULL((
                SELECT SUM(t.Net_Amt)
                  FROM trans_trading t
                 WHERE t.Branch_Id = pBranch_Id
                   AND t.Tranding_Type = 3
                   AND t.Status_Cd = 1
                   AND IFNULL(t.Agent_Id, 0) > 0
                   AND t.Invoice_Date >= GREATEST(m.Month_Start, vYearStart)
                   AND t.Invoice_Date <= LEAST(LAST_DAY(m.Month_Start), vYearEnd)
            ), 0) AS Agent_Amt
        FROM months m
        ORDER BY m.Month_Start;
    END IF;
END ;;
DELIMITER ;
/*!50003 SET sql_mode              = @saved_sql_mode */ ;
/*!50003 SET character_set_client  = @saved_cs_client */ ;
/*!50003 SET character_set_results = @saved_cs_results */ ;
/*!50003 SET collation_connection  = @saved_col_connection */ ;
/*!50003 DROP PROCEDURE IF EXISTS `USP_GET_PARTY_LIST` */;
/*!50003 SET @saved_cs_client      = @@character_set_client */ ;
/*!50003 SET @saved_cs_results     = @@character_set_results */ ;
/*!50003 SET @saved_col_connection = @@collation_connection */ ;
/*!50003 SET character_set_client  = utf8mb4 */ ;
/*!50003 SET character_set_results = utf8mb4 */ ;
/*!50003 SET collation_connection  = utf8mb4_unicode_ci */ ;
/*!50003 SET @saved_sql_mode       = @@sql_mode */ ;
/*!50003 SET sql_mode              = 'ONLY_FULL_GROUP_BY,STRICT_TRANS_TABLES,NO_ZERO_IN_DATE,NO_ZERO_DATE,ERROR_FOR_DIVISION_BY_ZERO,NO_ENGINE_SUBSTITUTION' */ ;
DELIMITER ;;
CREATE DEFINER=`priority`@`%` PROCEDURE `USP_GET_PARTY_LIST`(
    pParty_Type   INT,
    pBranch_Id    INT,
    pAgent_Id     INT
)
BEGIN
    SELECT
        Party_Id,
        Party_Code,
        Party_Name,
        Contact_No,
        AltContact_No,
        EMail,
        Contact_Person,
        Designation,
        Address_Line1,
        Address_Line2,
        City,
        District,
        State,
        PinCode,
        Pan_No,
        GstIn,
        State_Cd,
        Credit_Limit,
        Opening_Bal,
        Cust_Agent_Id,
        Status_Cd
    FROM mst_party
    WHERE Party_Type = pParty_Type
      AND (pBranch_Id = 0 OR Branch_Id = pBranch_Id)
      AND (IFNULL(pAgent_Id, 0) = 0 OR Cust_Agent_Id = pAgent_Id);
END ;;
DELIMITER ;
/*!50003 SET sql_mode              = @saved_sql_mode */ ;
/*!50003 SET character_set_client  = @saved_cs_client */ ;
/*!50003 SET character_set_results = @saved_cs_results */ ;
/*!50003 SET collation_connection  = @saved_col_connection */ ;
/*!50003 DROP PROCEDURE IF EXISTS `USP_GET_PARTY_VOUCHER_DTLS` */;
/*!50003 SET @saved_cs_client      = @@character_set_client */ ;
/*!50003 SET @saved_cs_results     = @@character_set_results */ ;
/*!50003 SET @saved_col_connection = @@collation_connection */ ;
/*!50003 SET character_set_client  = utf8mb4 */ ;
/*!50003 SET character_set_results = utf8mb4 */ ;
/*!50003 SET collation_connection  = utf8mb4_0900_ai_ci */ ;
/*!50003 SET @saved_sql_mode       = @@sql_mode */ ;
/*!50003 SET sql_mode              = 'ONLY_FULL_GROUP_BY,STRICT_TRANS_TABLES,NO_ZERO_IN_DATE,NO_ZERO_DATE,ERROR_FOR_DIVISION_BY_ZERO,NO_ENGINE_SUBSTITUTION' */ ;
DELIMITER ;;
CREATE DEFINER=`priority`@`%` PROCEDURE `USP_GET_PARTY_VOUCHER_DTLS`(
    pVouch_Id BIGINT
)
BEGIN
    SELECT m.Voucher_Id,
           DATE_FORMAT(m.Vou_Date, '%Y-%m-%d') AS Vou_Date,
           m.Vou_Type,
           m.Vou_Mode,
           m.Vou_No,
           m.Ref_Vou_No,
           m.Particulars,
           m.Type_Id AS Party_Id,
           CASE WHEN m.Vou_Mode = 2 THEN
                CASE WHEN m.Vou_Type = 1 THEN dr.GlHead_Id ELSE cr.GlHead_Id END
           ELSE 0 END AS Bank_Ledg,
           dr.Vou_Amount AS Amount
    FROM trans_voucher_master m
    INNER JOIN trans_voucher_details dr ON dr.Voucher_Id = m.Voucher_Id AND dr.Trans_Type = 'D'
    INNER JOIN trans_voucher_details cr ON cr.Voucher_Id = m.Voucher_Id AND cr.Trans_Type = 'C'
    WHERE m.Voucher_Id = pVouch_Id
      AND m.Trans_Type = 9
      AND IFNULL(m.Type_Id, 0) <> 0;
END ;;
DELIMITER ;
/*!50003 SET sql_mode              = @saved_sql_mode */ ;
/*!50003 SET character_set_client  = @saved_cs_client */ ;
/*!50003 SET character_set_results = @saved_cs_results */ ;
/*!50003 SET collation_connection  = @saved_col_connection */ ;
/*!50003 DROP PROCEDURE IF EXISTS `USP_GET_PARTY_VOUCHER_LIST` */;
/*!50003 SET @saved_cs_client      = @@character_set_client */ ;
/*!50003 SET @saved_cs_results     = @@character_set_results */ ;
/*!50003 SET @saved_col_connection = @@collation_connection */ ;
/*!50003 SET character_set_client  = utf8mb4 */ ;
/*!50003 SET character_set_results = utf8mb4 */ ;
/*!50003 SET collation_connection  = utf8mb4_0900_ai_ci */ ;
/*!50003 SET @saved_sql_mode       = @@sql_mode */ ;
/*!50003 SET sql_mode              = 'ONLY_FULL_GROUP_BY,STRICT_TRANS_TABLES,NO_ZERO_IN_DATE,NO_ZERO_DATE,ERROR_FOR_DIVISION_BY_ZERO,NO_ENGINE_SUBSTITUTION' */ ;
DELIMITER ;;
CREATE DEFINER=`priority`@`%` PROCEDURE `USP_GET_PARTY_VOUCHER_LIST`(
    pFin_Id     INT,
    pParty_Type TINYINT
)
BEGIN
    SELECT m.Voucher_Id,
           DATE_FORMAT(m.Vou_Date, '%d-%m-%Y') AS Vou_Date_Disp,
           m.Vou_No,
           p.Party_Name,
           d.Vou_Amount AS Amount
    FROM trans_voucher_master m
    INNER JOIN mst_party p ON p.Party_Id = m.Type_Id AND p.Party_Type = pParty_Type
    INNER JOIN trans_voucher_details d
        ON d.Voucher_Id = m.Voucher_Id
       AND d.Trans_Type = CASE WHEN m.Vou_Type = 1 THEN 'C' ELSE 'D' END
    WHERE m.Trans_Type = 9
      AND m.Year_Id = pFin_Id
      AND m.Status = 2
    ORDER BY m.Voucher_Id DESC
    LIMIT 10;
END ;;
DELIMITER ;
/*!50003 SET sql_mode              = @saved_sql_mode */ ;
/*!50003 SET character_set_client  = @saved_cs_client */ ;
/*!50003 SET character_set_results = @saved_cs_results */ ;
/*!50003 SET collation_connection  = @saved_col_connection */ ;
/*!50003 DROP PROCEDURE IF EXISTS `USP_GET_PENDING_BARCODE_LIST` */;
/*!50003 SET @saved_cs_client      = @@character_set_client */ ;
/*!50003 SET @saved_cs_results     = @@character_set_results */ ;
/*!50003 SET @saved_col_connection = @@collation_connection */ ;
/*!50003 SET character_set_client  = utf8mb4 */ ;
/*!50003 SET character_set_results = utf8mb4 */ ;
/*!50003 SET collation_connection  = utf8mb4_0900_ai_ci */ ;
/*!50003 SET @saved_sql_mode       = @@sql_mode */ ;
/*!50003 SET sql_mode              = 'ONLY_FULL_GROUP_BY,STRICT_TRANS_TABLES,NO_ZERO_IN_DATE,NO_ZERO_DATE,ERROR_FOR_DIVISION_BY_ZERO,NO_ENGINE_SUBSTITUTION' */ ;
DELIMITER ;;
CREATE DEFINER=`priority`@`%` PROCEDURE `USP_GET_PENDING_BARCODE_LIST`(
	pMode			Int
)
BEGIN

	If(pMode=1) Then
		Select m.Stock_Id,m.Prod_Id,p.Prod_ShortNm,p.Prod_Code,m.InOut_Date From trns_stockinout m Join mst_product p On p.Prod_Id=m.Prod_Id Where m.Stock_Type=1 And m.Barcode Is Null;
    End If;
	
    If(pMode=2) Then
		Select t.Trading_Id,t.Invoice_No,t.Ref_No,t.Invoice_Date,UDF_GET_PARTY_NAME(t.Party_Id) As Party_Name,t.Net_Amt,(Select JSON_ARRAYAGG(
    JSON_OBJECT(
        'Prod_Id', m.Prod_Id,
		'Stock_Id',m.Stock_Id,
        'Prod_ShortNm',p.Prod_ShortNm,
        'Prod_Code',p.Prod_Code
    )
) From trns_stockinout m Join mst_product p On p.Prod_Id=m.Prod_Id Where m.Stock_Type=2 And m.Type_Id=t.Trading_Id And m.Barcode Is Null) As Item_Details From trans_trading t Where t.Tranding_Type=2 And t.Status_Cd=1 And t.Trading_Id In (Select Type_Id From trns_stockinout Where Stock_Type=2 And Barcode Is Null);
    End If;

END ;;
DELIMITER ;
/*!50003 SET sql_mode              = @saved_sql_mode */ ;
/*!50003 SET character_set_client  = @saved_cs_client */ ;
/*!50003 SET character_set_results = @saved_cs_results */ ;
/*!50003 SET collation_connection  = @saved_col_connection */ ;
/*!50003 DROP PROCEDURE IF EXISTS `USP_GET_PENDING_INDENT` */;
/*!50003 SET @saved_cs_client      = @@character_set_client */ ;
/*!50003 SET @saved_cs_results     = @@character_set_results */ ;
/*!50003 SET @saved_col_connection = @@collation_connection */ ;
/*!50003 SET character_set_client  = utf8mb4 */ ;
/*!50003 SET character_set_results = utf8mb4 */ ;
/*!50003 SET collation_connection  = utf8mb4_unicode_ci */ ;
/*!50003 SET @saved_sql_mode       = @@sql_mode */ ;
/*!50003 SET sql_mode              = 'ONLY_FULL_GROUP_BY,STRICT_TRANS_TABLES,NO_ZERO_IN_DATE,NO_ZERO_DATE,ERROR_FOR_DIVISION_BY_ZERO,NO_ENGINE_SUBSTITUTION' */ ;
DELIMITER ;;
CREATE DEFINER=`priority`@`%` PROCEDURE `USP_GET_PENDING_INDENT`(
    pAgent_Id INT,
    pIndent_Type_Id INT
)
BEGIN
    SELECT
        m.Indent_Id,
        m.Indent_Date,
        m.Indent_No,
        m.Remarks,
        (
            SELECT JSON_ARRAYAGG(
                JSON_OBJECT(
                    'Indent_Sl', d.Indent_Sl,
                    'Prod_Id', d.Prod_Id,
                    'Prod_ShortNm', p.Prod_ShortNm,
                    'Prod_Code', p.Prod_Code,
                    'Quantity', d.Quantity,
                    'Reject_Qty', IFNULL(d.Reject_Qty, 0),
                    'Unit_Id', d.Unit_Id,
                    'Unit_Name', UDF_GET_UNIT_NAME(d.Unit_Id)
                )
            )
            FROM trans_agent_indent_details d
            JOIN mst_product p ON p.Prod_Id = d.Prod_Id
            WHERE d.Indent_Id = m.Indent_Id
        ) AS Item_Data
    FROM trans_agent_indent m
    WHERE m.Agent_Id = pAgent_Id
      AND m.Is_Issued = 0
      AND m.Indent_Type_Id = pIndent_Type_Id;
END ;;
DELIMITER ;
/*!50003 SET sql_mode              = @saved_sql_mode */ ;
/*!50003 SET character_set_client  = @saved_cs_client */ ;
/*!50003 SET character_set_results = @saved_cs_results */ ;
/*!50003 SET collation_connection  = @saved_col_connection */ ;
/*!50003 DROP PROCEDURE IF EXISTS `USP_GET_PRM_LEDGER` */;
/*!50003 SET @saved_cs_client      = @@character_set_client */ ;
/*!50003 SET @saved_cs_results     = @@character_set_results */ ;
/*!50003 SET @saved_col_connection = @@collation_connection */ ;
/*!50003 SET character_set_client  = utf8mb4 */ ;
/*!50003 SET character_set_results = utf8mb4 */ ;
/*!50003 SET collation_connection  = utf8mb4_0900_ai_ci */ ;
/*!50003 SET @saved_sql_mode       = @@sql_mode */ ;
/*!50003 SET sql_mode              = 'ONLY_FULL_GROUP_BY,STRICT_TRANS_TABLES,NO_ZERO_IN_DATE,NO_ZERO_DATE,ERROR_FOR_DIVISION_BY_ZERO,NO_ENGINE_SUBSTITUTION' */ ;
DELIMITER ;;
CREATE DEFINER=`priority`@`%` PROCEDURE `USP_GET_PRM_LEDGER`(IN pMode INT)
BEGIN
    IF pMode = 1 THEN
        SELECT g.Account_Id AS Id,
               CONCAT(g.Account_Code, ' - ', g.Account_Desc) AS Ledger_Name
        FROM mst_acct_glhead g
        INNER JOIN mst_acct_mainhd m ON m.MainHd_Id = g.MainHd_Id
        INNER JOIN mst_acct_category c ON c.Cate_Id = m.Cate_Id
        WHERE c.Cate_Id = 6 AND g.Is_Active = 1
        ORDER BY g.Account_Code;
    END IF;
    IF pMode = 2 THEN
        SELECT g.Account_Id AS Id,
               CONCAT(g.Account_Code, ' - ', g.Account_Desc) AS Ledger_Name
        FROM mst_acct_glhead g
        INNER JOIN mst_acct_mainhd m ON m.MainHd_Id = g.MainHd_Id
        INNER JOIN mst_acct_category c ON c.Cate_Id = m.Cate_Id
        WHERE c.Cate_Id = 5 AND g.Is_Active = 1
        ORDER BY g.Account_Code;
    END IF;
END ;;
DELIMITER ;
/*!50003 SET sql_mode              = @saved_sql_mode */ ;
/*!50003 SET character_set_client  = @saved_cs_client */ ;
/*!50003 SET character_set_results = @saved_cs_results */ ;
/*!50003 SET collation_connection  = @saved_col_connection */ ;
/*!50003 DROP PROCEDURE IF EXISTS `USP_GET_PROD_INFO` */;
/*!50003 SET @saved_cs_client      = @@character_set_client */ ;
/*!50003 SET @saved_cs_results     = @@character_set_results */ ;
/*!50003 SET @saved_col_connection = @@collation_connection */ ;
/*!50003 SET character_set_client  = utf8mb4 */ ;
/*!50003 SET character_set_results = utf8mb4 */ ;
/*!50003 SET collation_connection  = utf8mb4_unicode_ci */ ;
/*!50003 SET @saved_sql_mode       = @@sql_mode */ ;
/*!50003 SET sql_mode              = 'ONLY_FULL_GROUP_BY,STRICT_TRANS_TABLES,NO_ZERO_IN_DATE,NO_ZERO_DATE,ERROR_FOR_DIVISION_BY_ZERO,NO_ENGINE_SUBSTITUTION' */ ;
DELIMITER ;;
CREATE DEFINER=`priority`@`%` PROCEDURE `USP_GET_PROD_INFO`(
    pBarcode    BIGINT,
    pDate       DATE
)
BEGIN
    DECLARE pExists_Count   INT;
    DECLARE pEror_No        INT;
    DECLARE pError_Message  VARCHAR(100);

    SET pExists_Count = (SELECT COUNT(*) FROM trns_stockinout WHERE Barcode = pBarcode);

    IF (pExists_Count = 0) THEN
        SET pEror_No = -1;
        SET pError_Message = 'Invalid Code Entred !!';
    ELSE
        SET pEror_No = 0;
    END IF;

    IF (pEror_No = 0) THEN
        SELECT
            m.Prod_Id,
            m.MRP,
            m.MRP AS Rate,
            m.Pack_Date,
            p.Prod_ShortNm,
            UDF_CAL_AVAIL_STOCK(m.Prod_Id, pDate) AS Avil_Qnty,
            m.Unit_Id,
            UDF_GET_UNIT_NAME(p.Unit_Id) AS Unit_Name,
            CAST(0 AS DECIMAL(5,2)) AS Discount,
            UDF_GET_ITEM_GST_DETAILS(p.Gst_Id) AS Gst_Details
        FROM trns_stockinout m
        JOIN mst_product p ON p.Prod_Id = m.Prod_Id
        WHERE m.Barcode = pBarcode;
    END IF;
END ;;
DELIMITER ;
/*!50003 SET sql_mode              = @saved_sql_mode */ ;
/*!50003 SET character_set_client  = @saved_cs_client */ ;
/*!50003 SET character_set_results = @saved_cs_results */ ;
/*!50003 SET collation_connection  = @saved_col_connection */ ;
/*!50003 DROP PROCEDURE IF EXISTS `USP_GET_PROD_INFO_AGENT` */;
/*!50003 SET @saved_cs_client      = @@character_set_client */ ;
/*!50003 SET @saved_cs_results     = @@character_set_results */ ;
/*!50003 SET @saved_col_connection = @@collation_connection */ ;
/*!50003 SET character_set_client  = utf8mb4 */ ;
/*!50003 SET character_set_results = utf8mb4 */ ;
/*!50003 SET collation_connection  = utf8mb4_unicode_ci */ ;
/*!50003 SET @saved_sql_mode       = @@sql_mode */ ;
/*!50003 SET sql_mode              = 'ONLY_FULL_GROUP_BY,STRICT_TRANS_TABLES,NO_ZERO_IN_DATE,NO_ZERO_DATE,ERROR_FOR_DIVISION_BY_ZERO,NO_ENGINE_SUBSTITUTION' */ ;
DELIMITER ;;
CREATE DEFINER=`priority`@`%` PROCEDURE `USP_GET_PROD_INFO_AGENT`(
    pBarcode    BIGINT,
    pAgent_Id   INT,
    pDate       DATE
)
BEGIN
    DECLARE pExists_Count   INT;
    DECLARE pEror_No        INT;
    DECLARE pError_Message  VARCHAR(100);

    SET pExists_Count = (SELECT COUNT(*) FROM trns_stockinout WHERE Barcode = pBarcode);

    IF (pExists_Count = 0) THEN
        SET pEror_No = -1;
        SET pError_Message = 'Invalid Code Entred !!';
    ELSE
        SET pEror_No = 0;
    END IF;

    IF (pEror_No = 0) THEN
        SELECT
            m.Prod_Id,
            m.MRP,
            m.MRP AS Rate,
            m.Pack_Date,
            p.Prod_ShortNm,
            UDF_CAL_AGENT_STOCK(pAgent_Id, m.Prod_Id, pDate) AS Avil_Qnty,
            m.Unit_Id,
            UDF_GET_UNIT_NAME(p.Unit_Id) AS Unit_Name,
            CAST(0 AS DECIMAL(5,2)) AS Discount,
            UDF_GET_ITEM_GST_DETAILS(p.Gst_Id) AS Gst_Details
        FROM trns_stockinout m
        JOIN mst_product p ON p.Prod_Id = m.Prod_Id
        WHERE m.Barcode = pBarcode;
    END IF;
END ;;
DELIMITER ;
/*!50003 SET sql_mode              = @saved_sql_mode */ ;
/*!50003 SET character_set_client  = @saved_cs_client */ ;
/*!50003 SET character_set_results = @saved_cs_results */ ;
/*!50003 SET collation_connection  = @saved_col_connection */ ;
/*!50003 DROP PROCEDURE IF EXISTS `USP_GET_PROD_INFO_BY_ITEM` */;
/*!50003 SET @saved_cs_client      = @@character_set_client */ ;
/*!50003 SET @saved_cs_results     = @@character_set_results */ ;
/*!50003 SET @saved_col_connection = @@collation_connection */ ;
/*!50003 SET character_set_client  = utf8mb4 */ ;
/*!50003 SET character_set_results = utf8mb4 */ ;
/*!50003 SET collation_connection  = utf8mb4_unicode_ci */ ;
/*!50003 SET @saved_sql_mode       = @@sql_mode */ ;
/*!50003 SET sql_mode              = 'ONLY_FULL_GROUP_BY,STRICT_TRANS_TABLES,NO_ZERO_IN_DATE,NO_ZERO_DATE,ERROR_FOR_DIVISION_BY_ZERO,NO_ENGINE_SUBSTITUTION' */ ;
DELIMITER ;;
CREATE DEFINER=`priority`@`%` PROCEDURE `USP_GET_PROD_INFO_BY_ITEM`(
    pProd_Id    INT,
    pDate       DATE
)
BEGIN
    SELECT
        p.Prod_Id,
        IFNULL(s.MRP, 0) AS MRP,
        IFNULL(s.MRP, 0) AS Rate,
        NULL AS Pack_Date,
        p.Prod_ShortNm,
        UDF_CAL_AVAIL_STOCK(p.Prod_Id, pDate) AS Avil_Qnty,
        p.Unit_Id,
        UDF_GET_UNIT_NAME(p.Unit_Id) AS Unit_Name,
        CAST(0 AS DECIMAL(5,2)) AS Discount,
        UDF_GET_ITEM_GST_DETAILS(p.Gst_Id) AS Gst_Details
    FROM mst_product p
    LEFT JOIN (
        SELECT MRP
        FROM trns_stockinout
        WHERE Prod_Id = pProd_Id
          AND IFNULL(MRP, 0) > 0
        ORDER BY InOut_Date DESC, Stock_Id DESC
        LIMIT 1
    ) s ON 1 = 1
    WHERE p.Prod_Id = pProd_Id;
END ;;
DELIMITER ;
/*!50003 SET sql_mode              = @saved_sql_mode */ ;
/*!50003 SET character_set_client  = @saved_cs_client */ ;
/*!50003 SET character_set_results = @saved_cs_results */ ;
/*!50003 SET collation_connection  = @saved_col_connection */ ;
/*!50003 DROP PROCEDURE IF EXISTS `USP_GET_PROD_MRP_STOCK` */;
/*!50003 SET @saved_cs_client      = @@character_set_client */ ;
/*!50003 SET @saved_cs_results     = @@character_set_results */ ;
/*!50003 SET @saved_col_connection = @@collation_connection */ ;
/*!50003 SET character_set_client  = utf8mb4 */ ;
/*!50003 SET character_set_results = utf8mb4 */ ;
/*!50003 SET collation_connection  = utf8mb4_unicode_ci */ ;
/*!50003 SET @saved_sql_mode       = @@sql_mode */ ;
/*!50003 SET sql_mode              = 'ONLY_FULL_GROUP_BY,STRICT_TRANS_TABLES,NO_ZERO_IN_DATE,NO_ZERO_DATE,ERROR_FOR_DIVISION_BY_ZERO,NO_ENGINE_SUBSTITUTION' */ ;
DELIMITER ;;
CREATE DEFINER=`priority`@`%` PROCEDURE `USP_GET_PROD_MRP_STOCK`(
    pProd_Id INT,
    pDate    DATE
)
BEGIN
    SELECT
        x.MRP,
        x.Avil_Qnty
    FROM (
        SELECT
            CAST(IFNULL(s.MRP, 0) AS DECIMAL(10,2)) AS MRP,
            IFNULL(SUM(
                CASE
                    WHEN s.Stock_Type IN (1, 2, 6) THEN s.Quantity
                    WHEN s.Stock_Type = 8 AND NOT EXISTS (
                        SELECT 1 FROM trans_agent_stock a WHERE a.TrnsStock_Id = s.Stock_Id
                    ) THEN s.Quantity
                    WHEN s.Stock_Type IN (3, 4, 7) THEN -s.Quantity
                    ELSE 0
                END
            ), 0) AS Avil_Qnty
        FROM trns_stockinout s
        WHERE s.Prod_Id = pProd_Id
          AND s.InOut_Date <= pDate
          AND IFNULL(s.MRP, 0) > 0
        GROUP BY CAST(IFNULL(s.MRP, 0) AS DECIMAL(10,2))
    ) x
    ORDER BY x.MRP;
END ;;
DELIMITER ;
/*!50003 SET sql_mode              = @saved_sql_mode */ ;
/*!50003 SET character_set_client  = @saved_cs_client */ ;
/*!50003 SET character_set_results = @saved_cs_results */ ;
/*!50003 SET collation_connection  = @saved_col_connection */ ;
/*!50003 DROP PROCEDURE IF EXISTS `USP_GET_PROD_SALE_RATES` */;
/*!50003 SET @saved_cs_client      = @@character_set_client */ ;
/*!50003 SET @saved_cs_results     = @@character_set_results */ ;
/*!50003 SET @saved_col_connection = @@collation_connection */ ;
/*!50003 SET character_set_client  = utf8mb4 */ ;
/*!50003 SET character_set_results = utf8mb4 */ ;
/*!50003 SET collation_connection  = utf8mb4_unicode_ci */ ;
/*!50003 SET @saved_sql_mode       = @@sql_mode */ ;
/*!50003 SET sql_mode              = 'ONLY_FULL_GROUP_BY,STRICT_TRANS_TABLES,NO_ZERO_IN_DATE,NO_ZERO_DATE,ERROR_FOR_DIVISION_BY_ZERO,NO_ENGINE_SUBSTITUTION' */ ;
DELIMITER ;;
CREATE DEFINER=`priority`@`%` PROCEDURE `USP_GET_PROD_SALE_RATES`(
    pProd_Id INT
)
BEGIN
    SELECT IFNULL(s.MRP, 0) AS MRP
    FROM trns_stockinout s
    WHERE s.Prod_Id = pProd_Id
      AND IFNULL(s.MRP, 0) > 0
      AND s.Stock_Type IN (1, 2)
    GROUP BY IFNULL(s.MRP, 0)
    ORDER BY MRP;
END ;;
DELIMITER ;
/*!50003 SET sql_mode              = @saved_sql_mode */ ;
/*!50003 SET character_set_client  = @saved_cs_client */ ;
/*!50003 SET character_set_results = @saved_cs_results */ ;
/*!50003 SET collation_connection  = @saved_col_connection */ ;
/*!50003 DROP PROCEDURE IF EXISTS `USP_GET_PURCHASE_DTLS` */;
/*!50003 SET @saved_cs_client      = @@character_set_client */ ;
/*!50003 SET @saved_cs_results     = @@character_set_results */ ;
/*!50003 SET @saved_col_connection = @@collation_connection */ ;
/*!50003 SET character_set_client  = utf8mb4 */ ;
/*!50003 SET character_set_results = utf8mb4 */ ;
/*!50003 SET collation_connection  = utf8mb4_unicode_ci */ ;
/*!50003 SET @saved_sql_mode       = @@sql_mode */ ;
/*!50003 SET sql_mode              = 'ONLY_FULL_GROUP_BY,STRICT_TRANS_TABLES,NO_ZERO_IN_DATE,NO_ZERO_DATE,ERROR_FOR_DIVISION_BY_ZERO,NO_ENGINE_SUBSTITUTION' */ ;
DELIMITER ;;
CREATE DEFINER=`priority`@`%` PROCEDURE `USP_GET_PURCHASE_DTLS`(
    pPur_Id INT
)
BEGIN
    SELECT
        m.Trading_Id AS Pur_Id,
        m.Invoice_No,
        m.Ref_No,
        m.Invoice_Date,
        m.Party_Id,
        m.Invoice_Amt AS Tot_Amt,
        m.Disc_Percent,
        m.Disc_Amount,
        m.Freight_Amt,
        m.Taxable_Amt,
        m.GST_Amt,
        m.Round_Off,
        m.Net_Amt,
        v.Voucher_Id,
        (
            SELECT JSON_ARRAYAGG(
                JSON_OBJECT(
                    'Prod_Id', p.Prod_Id,
                    'Cat_Id', i.Cate_Id,
                    'Prod_Name', i.Prod_ShortNm,
                    'hsn_code', p.Gst_Code,
                    'qnty', p.Item_Qty,
                    'Item_Rate', p.Item_Rate,
                    'Unit_Id', p.Unit_Id,
                    'Unit_Name', UDF_GET_UNIT_NAME(p.Unit_Id),
                    'Item_Total', p.Item_Total,
                    'MRP', p.MRP,
                    'Sale_Margin', IFNULL(i.Sale_Margin, 0),
                    'Disc_Prcnt', p.Disc_Prcnt,
                    'Disc_Amt', p.Disc_Amt,
                    'Taxable_Amt', p.Taxable_Amt,
                    'SGST_Prcnt', p.SGST_Prcnt,
                    'CGST_Prcnt', p.CGST_Prcnt,
                    'SGST_Amt', p.SGST_Amt,
                    'CGST_Amt', p.CGST_Amt,
                    'Net_Amt', p.Net_Amt,
                    'Pack_Date', s.Pack_Date
                )
            )
            FROM trans_trading_products p
            JOIN mst_product i ON i.Prod_Id = p.Prod_Id
            JOIN trns_stockinout s ON s.Type_Id = m.Trading_Id AND s.Prod_Id = p.Prod_Id
            WHERE Trading_Id = m.Trading_Id
        ) AS Item_Details
    FROM trans_trading m
    LEFT JOIN trans_voucher_master v ON v.Type_Id = m.Trading_Id
    WHERE m.Trading_Id = pPur_Id;
END ;;
DELIMITER ;
/*!50003 SET sql_mode              = @saved_sql_mode */ ;
/*!50003 SET character_set_client  = @saved_cs_client */ ;
/*!50003 SET character_set_results = @saved_cs_results */ ;
/*!50003 SET collation_connection  = @saved_col_connection */ ;
/*!50003 DROP PROCEDURE IF EXISTS `USP_GET_PURCHASE_RETURNABLE_DTLS` */;
/*!50003 SET @saved_cs_client      = @@character_set_client */ ;
/*!50003 SET @saved_cs_results     = @@character_set_results */ ;
/*!50003 SET @saved_col_connection = @@collation_connection */ ;
/*!50003 SET character_set_client  = utf8mb4 */ ;
/*!50003 SET character_set_results = utf8mb4 */ ;
/*!50003 SET collation_connection  = utf8mb4_0900_ai_ci */ ;
/*!50003 SET @saved_sql_mode       = @@sql_mode */ ;
/*!50003 SET sql_mode              = 'ONLY_FULL_GROUP_BY,STRICT_TRANS_TABLES,NO_ZERO_IN_DATE,NO_ZERO_DATE,ERROR_FOR_DIVISION_BY_ZERO,NO_ENGINE_SUBSTITUTION' */ ;
DELIMITER ;;
CREATE DEFINER=`priority`@`%` PROCEDURE `USP_GET_PURCHASE_RETURNABLE_DTLS`(
    pPur_Id INT
)
BEGIN
    DECLARE pPurchaseType TINYINT;
    DECLARE pReturnType   TINYINT;

    SET pPurchaseType = (
        SELECT Value_Id FROM smartinventory_core.mst_options
         WHERE Option_Id = 6 AND Value_Name = 'Purchase Entry' LIMIT 1
    );
    SET pReturnType = (
        SELECT Value_Id FROM smartinventory_core.mst_options
         WHERE Option_Id = 6 AND Value_Name = 'Purchase Return Entry' LIMIT 1
    );
    SET pPurchaseType = IFNULL(pPurchaseType, 2);
    SET pReturnType   = IFNULL(pReturnType, 4);

    SELECT
        m.Trading_Id AS Pur_Id,
        m.Invoice_No,
        m.Ref_No,
        m.Invoice_Date,
        m.Party_Id,
        m.Invoice_Amt AS Tot_Amt,
        m.Disc_Percent,
        m.Disc_Amount,
        m.Freight_Amt,
        m.Taxable_Amt,
        m.GST_Amt,
        m.Round_Off,
        m.Net_Amt,
        v.Voucher_Id,
        (
            SELECT JSON_ARRAYAGG(
                JSON_OBJECT(
                    'Prod_Id', x.Prod_Id,
                    'Cat_Id', x.Cate_Id,
                    'Prod_Name', x.Prod_ShortNm,
                    'hsn_code', x.Gst_Code,
                    'Purchased_Qty', x.Purchased_Qty,
                    'Returned_Qty', x.Returned_Qty,
                    'Remaining_Qty', x.Remaining_Qty,
                    'qnty', x.Remaining_Qty,
                    'Item_Rate', x.Item_Rate,
                    'Unit_Id', x.Unit_Id,
                    'Unit_Name', x.Unit_Name,
                    'Item_Total', x.Item_Total,
                    'MRP', x.MRP,
                    'Sale_Margin', x.Sale_Margin,
                    'Disc_Prcnt', x.Disc_Prcnt,
                    'Disc_Amt', x.Disc_Amt,
                    'Taxable_Amt', x.Taxable_Amt,
                    'SGST_Prcnt', x.SGST_Prcnt,
                    'CGST_Prcnt', x.CGST_Prcnt,
                    'SGST_Amt', x.SGST_Amt,
                    'CGST_Amt', x.CGST_Amt,
                    'Net_Amt', x.Net_Amt,
                    'Pack_Date', x.Pack_Date
                )
            )
            FROM (
                SELECT
                    p.Prod_Id,
                    i.Cate_Id,
                    i.Prod_ShortNm,
                    p.Gst_Code,
                    p.Item_Qty AS Purchased_Qty,
                    IFNULL((
                        SELECT SUM(rp.Item_Qty)
                        FROM trans_trading r
                        INNER JOIN trans_trading_products rp
                            ON rp.Trading_Id = r.Trading_Id
                        WHERE r.Tranding_Type = pReturnType
                          AND r.Status_Cd = 1
                          AND r.Party_Id = m.Party_Id
                          AND rp.Prod_Id = p.Prod_Id
                          AND (
                                r.Ref_No = m.Invoice_No
                             OR r.Ref_No = m.Ref_No
                          )
                    ), 0) AS Returned_Qty,
                    (
                        IFNULL(p.Item_Qty, 0)
                        - IFNULL((
                            SELECT SUM(rp.Item_Qty)
                            FROM trans_trading r
                            INNER JOIN trans_trading_products rp
                                ON rp.Trading_Id = r.Trading_Id
                            WHERE r.Tranding_Type = pReturnType
                              AND r.Status_Cd = 1
                              AND r.Party_Id = m.Party_Id
                              AND rp.Prod_Id = p.Prod_Id
                              AND (
                                    r.Ref_No = m.Invoice_No
                                 OR r.Ref_No = m.Ref_No
                              )
                        ), 0)
                    ) AS Remaining_Qty,
                    p.Item_Rate,
                    p.Unit_Id,
                    UDF_GET_UNIT_NAME(p.Unit_Id) AS Unit_Name,
                    p.Item_Total,
                    p.MRP,
                    IFNULL(i.Sale_Margin, 0) AS Sale_Margin,
                    p.Disc_Prcnt,
                    p.Disc_Amt,
                    p.Taxable_Amt,
                    p.SGST_Prcnt,
                    p.CGST_Prcnt,
                    p.SGST_Amt,
                    p.CGST_Amt,
                    p.Net_Amt,
                    (
                        SELECT s.Pack_Date
                        FROM trns_stockinout s
                        WHERE s.Type_Id = m.Trading_Id
                          AND s.Prod_Id = p.Prod_Id
                        ORDER BY s.Stock_Id
                        LIMIT 1
                    ) AS Pack_Date
                FROM trans_trading_products p
                INNER JOIN mst_product i ON i.Prod_Id = p.Prod_Id
                WHERE p.Trading_Id = m.Trading_Id
            ) x
            WHERE x.Remaining_Qty > 0
        ) AS Item_Details
    FROM trans_trading m
    LEFT JOIN trans_voucher_master v ON v.Type_Id = m.Trading_Id
    WHERE m.Trading_Id = pPur_Id
      AND m.Tranding_Type = pPurchaseType;
END ;;
DELIMITER ;
/*!50003 SET sql_mode              = @saved_sql_mode */ ;
/*!50003 SET character_set_client  = @saved_cs_client */ ;
/*!50003 SET character_set_results = @saved_cs_results */ ;
/*!50003 SET collation_connection  = @saved_col_connection */ ;
/*!50003 DROP PROCEDURE IF EXISTS `USP_GET_PURCHASE_RETURNABLE_QTY` */;
/*!50003 SET @saved_cs_client      = @@character_set_client */ ;
/*!50003 SET @saved_cs_results     = @@character_set_results */ ;
/*!50003 SET @saved_col_connection = @@collation_connection */ ;
/*!50003 SET character_set_client  = utf8mb4 */ ;
/*!50003 SET character_set_results = utf8mb4 */ ;
/*!50003 SET collation_connection  = utf8mb4_unicode_ci */ ;
/*!50003 SET @saved_sql_mode       = @@sql_mode */ ;
/*!50003 SET sql_mode              = 'ONLY_FULL_GROUP_BY,STRICT_TRANS_TABLES,NO_ZERO_IN_DATE,NO_ZERO_DATE,ERROR_FOR_DIVISION_BY_ZERO,NO_ENGINE_SUBSTITUTION' */ ;
DELIMITER ;;
CREATE DEFINER=`priority`@`%` PROCEDURE `USP_GET_PURCHASE_RETURNABLE_QTY`(
    pParty_Id            INT,
    pProd_Id             INT,
    pBranch_Id           INT,
    pExclude_Trading_Id  BIGINT
)
BEGIN
    DECLARE pPurchaseType TINYINT;
    DECLARE pReturnType   TINYINT;
    DECLARE pPurchased    DECIMAL(18,2);
    DECLARE pReturned     DECIMAL(18,2);
    DECLARE pLastRate     DECIMAL(10,2);
    DECLARE pLastDisc     DECIMAL(5,2);

    SET pPurchaseType = (
        SELECT Value_Id FROM smartinventory_core.mst_options
         WHERE Option_Id = 6 AND Value_Name = 'Purchase Entry' LIMIT 1
    );
    SET pReturnType = (
        SELECT Value_Id FROM smartinventory_core.mst_options
         WHERE Option_Id = 6 AND Value_Name = 'Purchase Return Entry' LIMIT 1
    );
    SET pPurchaseType = IFNULL(pPurchaseType, 2);
    SET pReturnType   = IFNULL(pReturnType, 4);

    SET pPurchased = IFNULL((
        SELECT SUM(d.Item_Qty)
        FROM trans_trading m
        INNER JOIN trans_trading_products d ON d.Trading_Id = m.Trading_Id
        WHERE m.Tranding_Type = pPurchaseType
          AND m.Status_Cd = 1
          AND m.Party_Id = pParty_Id
          AND d.Prod_Id = pProd_Id
          AND (pBranch_Id = 0 OR m.Branch_Id = pBranch_Id)
    ), 0);

    SET pReturned = IFNULL((
        SELECT SUM(d.Item_Qty)
        FROM trans_trading m
        INNER JOIN trans_trading_products d ON d.Trading_Id = m.Trading_Id
        WHERE m.Tranding_Type = pReturnType
          AND m.Status_Cd = 1
          AND m.Party_Id = pParty_Id
          AND d.Prod_Id = pProd_Id
          AND (pBranch_Id = 0 OR m.Branch_Id = pBranch_Id)
          AND (pExclude_Trading_Id = 0 OR m.Trading_Id <> pExclude_Trading_Id)
    ), 0);

    SET pLastRate = IFNULL((
        SELECT d.Item_Rate
        FROM trans_trading m
        INNER JOIN trans_trading_products d ON d.Trading_Id = m.Trading_Id
        WHERE m.Tranding_Type = pPurchaseType
          AND m.Status_Cd = 1
          AND m.Party_Id = pParty_Id
          AND d.Prod_Id = pProd_Id
          AND (pBranch_Id = 0 OR m.Branch_Id = pBranch_Id)
        ORDER BY m.Invoice_Date DESC, m.Trading_Id DESC
        LIMIT 1
    ), 0);

    SET pLastDisc = IFNULL((
        SELECT CASE
            WHEN IFNULL(d.Disc_Prcnt, 0) > 0 THEN d.Disc_Prcnt
            ELSE IFNULL(m.Disc_Percent, 0)
        END
        FROM trans_trading m
        INNER JOIN trans_trading_products d ON d.Trading_Id = m.Trading_Id
        WHERE m.Tranding_Type = pPurchaseType
          AND m.Status_Cd = 1
          AND m.Party_Id = pParty_Id
          AND d.Prod_Id = pProd_Id
          AND (pBranch_Id = 0 OR m.Branch_Id = pBranch_Id)
        ORDER BY m.Invoice_Date DESC, m.Trading_Id DESC
        LIMIT 1
    ), 0);

    SELECT
        pPurchased AS Purchased_Qty,
        pReturned AS Returned_Qty,
        CASE
            WHEN pPurchased > pReturned THEN pPurchased - pReturned
            ELSE 0
        END AS Remaining_Qty,
        pLastRate AS Last_Purchase_Rate,
        pLastDisc AS Last_Purchase_Disc;
END ;;
DELIMITER ;
/*!50003 SET sql_mode              = @saved_sql_mode */ ;
/*!50003 SET character_set_client  = @saved_cs_client */ ;
/*!50003 SET character_set_results = @saved_cs_results */ ;
/*!50003 SET collation_connection  = @saved_col_connection */ ;
/*!50003 DROP PROCEDURE IF EXISTS `USP_GET_REORDER_ALERTS` */;
/*!50003 SET @saved_cs_client      = @@character_set_client */ ;
/*!50003 SET @saved_cs_results     = @@character_set_results */ ;
/*!50003 SET @saved_col_connection = @@collation_connection */ ;
/*!50003 SET character_set_client  = utf8mb4 */ ;
/*!50003 SET character_set_results = utf8mb4 */ ;
/*!50003 SET collation_connection  = utf8mb4_0900_ai_ci */ ;
/*!50003 SET @saved_sql_mode       = @@sql_mode */ ;
/*!50003 SET sql_mode              = 'ONLY_FULL_GROUP_BY,STRICT_TRANS_TABLES,NO_ZERO_IN_DATE,NO_ZERO_DATE,ERROR_FOR_DIVISION_BY_ZERO,NO_ENGINE_SUBSTITUTION' */ ;
DELIMITER ;;
CREATE DEFINER=`priority`@`%` PROCEDURE `USP_GET_REORDER_ALERTS`(
    pBranch_Id  SMALLINT,
    pYear_Id    TINYINT
)
BEGIN
    SELECT
        x.Prod_Id,
        x.Prod_Code,
        x.Prod_Name,
        x.On_Hand,
        x.ReOrder_Qty,
        CASE
            WHEN x.On_Hand <= 0 THEN 'Critical'
            WHEN x.On_Hand <= (x.ReOrder_Qty * 0.5) THEN 'Critical'
            ELSE 'Low'
        END AS Alert_Status
    FROM (
        SELECT
            p.Prod_Id,
            p.Prod_Code,
            p.Prod_ShortNm AS Prod_Name,
            UDF_CAL_AVAIL_STOCK(p.Prod_Id, CURDATE()) AS On_Hand,
            p.ReOrder_Qty
        FROM mst_product p
        WHERE p.Is_Active = 1
          AND IFNULL(p.ReOrder_Qty, 0) > 0
    ) x
    WHERE x.On_Hand < x.ReOrder_Qty
    ORDER BY (x.On_Hand / x.ReOrder_Qty) ASC, x.Prod_Name
    LIMIT 10;
END ;;
DELIMITER ;
/*!50003 SET sql_mode              = @saved_sql_mode */ ;
/*!50003 SET character_set_client  = @saved_cs_client */ ;
/*!50003 SET character_set_results = @saved_cs_results */ ;
/*!50003 SET collation_connection  = @saved_col_connection */ ;
/*!50003 DROP PROCEDURE IF EXISTS `USP_GET_SALES_MIX` */;
/*!50003 SET @saved_cs_client      = @@character_set_client */ ;
/*!50003 SET @saved_cs_results     = @@character_set_results */ ;
/*!50003 SET @saved_col_connection = @@collation_connection */ ;
/*!50003 SET character_set_client  = utf8mb4 */ ;
/*!50003 SET character_set_results = utf8mb4 */ ;
/*!50003 SET collation_connection  = utf8mb4_0900_ai_ci */ ;
/*!50003 SET @saved_sql_mode       = @@sql_mode */ ;
/*!50003 SET sql_mode              = 'ONLY_FULL_GROUP_BY,STRICT_TRANS_TABLES,NO_ZERO_IN_DATE,NO_ZERO_DATE,ERROR_FOR_DIVISION_BY_ZERO,NO_ENGINE_SUBSTITUTION' */ ;
DELIMITER ;;
CREATE DEFINER=`priority`@`%` PROCEDURE `USP_GET_SALES_MIX`(
    pBranch_Id  SMALLINT,
    pYear_Id    TINYINT
)
BEGIN
    DECLARE pMonthStart DATE;
    DECLARE pToday      DATE;
    DECLARE pYearStart  DATE;
    DECLARE pYearEnd    DATE;

    SET pToday      = CURDATE();
    SET pMonthStart = DATE_FORMAT(pToday, '%Y-%m-01');

    SELECT Year_Start, Year_End
      INTO pYearStart, pYearEnd
      FROM mst_accountingyear
     WHERE Year_Id = pYear_Id;

    SELECT
        IFNULL(SUM(CASE
            WHEN Tranding_Type = 3 AND Agent_Id IS NULL THEN Net_Amt
            ELSE 0 END), 0) AS Counter_Amt,
        IFNULL(SUM(CASE
            WHEN Tranding_Type = 3 AND Agent_Id IS NOT NULL THEN Net_Amt
            ELSE 0 END), 0) AS Agent_Amt,
        IFNULL(SUM(CASE
            WHEN Tranding_Type = 5 THEN Net_Amt
            ELSE 0 END), 0) AS Return_Amt
    FROM trans_trading
    WHERE Branch_Id = pBranch_Id
      AND Status_Cd = 1
      AND Invoice_Date BETWEEN pMonthStart AND pToday
      AND Invoice_Date BETWEEN pYearStart AND pYearEnd
      AND Tranding_Type IN (3, 5);
END ;;
DELIMITER ;
/*!50003 SET sql_mode              = @saved_sql_mode */ ;
/*!50003 SET character_set_client  = @saved_cs_client */ ;
/*!50003 SET character_set_results = @saved_cs_results */ ;
/*!50003 SET collation_connection  = @saved_col_connection */ ;
/*!50003 DROP PROCEDURE IF EXISTS `USP_GET_SALE_DTLS` */;
/*!50003 SET @saved_cs_client      = @@character_set_client */ ;
/*!50003 SET @saved_cs_results     = @@character_set_results */ ;
/*!50003 SET @saved_col_connection = @@collation_connection */ ;
/*!50003 SET character_set_client  = utf8mb4 */ ;
/*!50003 SET character_set_results = utf8mb4 */ ;
/*!50003 SET collation_connection  = utf8mb4_0900_ai_ci */ ;
/*!50003 SET @saved_sql_mode       = @@sql_mode */ ;
/*!50003 SET sql_mode              = 'ONLY_FULL_GROUP_BY,STRICT_TRANS_TABLES,NO_ZERO_IN_DATE,NO_ZERO_DATE,ERROR_FOR_DIVISION_BY_ZERO,NO_ENGINE_SUBSTITUTION' */ ;
DELIMITER ;;
CREATE DEFINER=`priority`@`%` PROCEDURE `USP_GET_SALE_DTLS`(
	pSale_Id			Int
)
BEGIN

Select m.Trading_Id As Sale_Id,m.Invoice_No,m.Ref_No,m.Invoice_Date,m.Party_Id,m.Invoice_Amt As Tot_Amt,m.Disc_Percent,m.Disc_Amount,m.Taxable_Amt,m.GST_Amt,m.Round_Off,m.Net_Amt,
(Select JSON_ARRAYAGG(
    JSON_OBJECT(
        'Prod_Id', p.Prod_Id,
        'Cat_Id', i.Cate_Id,
        'Prod_Name',i.Prod_ShortNm,
        'hsn_code', p.Gst_Code,
        'qnty', p.Item_Qty,
        'Item_Rate',p.Item_Rate,
        'Unit_Id',p.Unit_Id,
        'Unit_Name',UDF_GET_UNIT_NAME(p.Unit_Id),
        'Item_Total',p.Item_Total,
        'MRP',p.MRP,
        'Disc_Prcnt',p.Disc_Prcnt,
        'Disc_Amt',p.Disc_Amt,
        'Taxable_Amt',p.Taxable_Amt,
        'SGST_Prcnt',p.SGST_Prcnt,
        'CGST_Prcnt',p.CGST_Prcnt,
        'SGST_Amt',p.SGST_Amt,
        'CGST_Amt',p.CGST_Amt,
        'Net_Amt',p.Net_Amt,
        'Pack_Date',s.Pack_Date
    )
) As Item_Details
FROM trans_trading_products p Join mst_product i On i.Prod_Id=p.Prod_Id Join trns_stockinout s On s.Type_Id=m.Trading_Id And s.Prod_Id=p.Prod_Id Where Trading_Id=m.Trading_Id) As Item_Details From trans_trading m Where m.Trading_Id=pSale_Id;

END ;;
DELIMITER ;
/*!50003 SET sql_mode              = @saved_sql_mode */ ;
/*!50003 SET character_set_client  = @saved_cs_client */ ;
/*!50003 SET character_set_results = @saved_cs_results */ ;
/*!50003 SET collation_connection  = @saved_col_connection */ ;
/*!50003 DROP PROCEDURE IF EXISTS `USP_GET_SALE_RETURNABLE_DTLS` */;
/*!50003 SET @saved_cs_client      = @@character_set_client */ ;
/*!50003 SET @saved_cs_results     = @@character_set_results */ ;
/*!50003 SET @saved_col_connection = @@collation_connection */ ;
/*!50003 SET character_set_client  = utf8mb4 */ ;
/*!50003 SET character_set_results = utf8mb4 */ ;
/*!50003 SET collation_connection  = utf8mb4_0900_ai_ci */ ;
/*!50003 SET @saved_sql_mode       = @@sql_mode */ ;
/*!50003 SET sql_mode              = 'ONLY_FULL_GROUP_BY,STRICT_TRANS_TABLES,NO_ZERO_IN_DATE,NO_ZERO_DATE,ERROR_FOR_DIVISION_BY_ZERO,NO_ENGINE_SUBSTITUTION' */ ;
DELIMITER ;;
CREATE DEFINER=`priority`@`%` PROCEDURE `USP_GET_SALE_RETURNABLE_DTLS`(
    pSale_Id INT
)
BEGIN
    DECLARE pSaleType   TINYINT;
    DECLARE pReturnType TINYINT;

    SET pSaleType = (
        SELECT Value_Id FROM smartinventory_core.mst_options
         WHERE Option_Id = 6 AND Value_Name = 'Sales Entry' LIMIT 1
    );
    SET pReturnType = (
        SELECT Value_Id FROM smartinventory_core.mst_options
         WHERE Option_Id = 6 AND Value_Name = 'Sales Return Entry' LIMIT 1
    );
    SET pSaleType   = IFNULL(pSaleType, 3);
    SET pReturnType = IFNULL(pReturnType, 5);

    SELECT
        m.Trading_Id AS Sale_Id,
        m.Invoice_No,
        m.Ref_No,
        m.Invoice_Date,
        m.Party_Id,
        m.Invoice_Amt AS Tot_Amt,
        m.Disc_Percent,
        m.Disc_Amount,
        m.Freight_Amt,
        m.Taxable_Amt,
        m.GST_Amt,
        m.Round_Off,
        m.Net_Amt,
        v.Voucher_Id,
        (
            SELECT JSON_ARRAYAGG(
                JSON_OBJECT(
                    'Prod_Id', x.Prod_Id,
                    'Cat_Id', x.Cate_Id,
                    'Prod_Name', x.Prod_ShortNm,
                    'hsn_code', x.Gst_Code,
                    'Sold_Qty', x.Sold_Qty,
                    'Returned_Qty', x.Returned_Qty,
                    'Remaining_Qty', x.Remaining_Qty,
                    'qnty', x.Remaining_Qty,
                    'Item_Rate', x.Item_Rate,
                    'Unit_Id', x.Unit_Id,
                    'Unit_Name', x.Unit_Name,
                    'Item_Total', x.Item_Total,
                    'MRP', x.MRP,
                    'Disc_Prcnt', x.Disc_Prcnt,
                    'Disc_Amt', x.Disc_Amt,
                    'Taxable_Amt', x.Taxable_Amt,
                    'SGST_Prcnt', x.SGST_Prcnt,
                    'CGST_Prcnt', x.CGST_Prcnt,
                    'SGST_Amt', x.SGST_Amt,
                    'CGST_Amt', x.CGST_Amt,
                    'Net_Amt', x.Net_Amt,
                    'Pack_Date', x.Pack_Date
                )
            )
            FROM (
                SELECT
                    p.Prod_Id,
                    i.Cate_Id,
                    i.Prod_ShortNm,
                    p.Gst_Code,
                    p.Item_Qty AS Sold_Qty,
                    IFNULL((
                        SELECT SUM(rp.Item_Qty)
                        FROM trans_trading r
                        INNER JOIN trans_trading_products rp
                            ON rp.Trading_Id = r.Trading_Id
                        WHERE r.Tranding_Type = pReturnType
                          AND r.Status_Cd = 1
                          AND IFNULL(r.Agent_Id, 0) = 0
                          AND IFNULL(r.Party_Id, 0) = IFNULL(m.Party_Id, 0)
                          AND rp.Prod_Id = p.Prod_Id
                          AND (
                                r.Ref_No = m.Invoice_No
                             OR r.Ref_No = m.Ref_No
                          )
                    ), 0) AS Returned_Qty,
                    (
                        IFNULL(p.Item_Qty, 0)
                        - IFNULL((
                            SELECT SUM(rp.Item_Qty)
                            FROM trans_trading r
                            INNER JOIN trans_trading_products rp
                                ON rp.Trading_Id = r.Trading_Id
                            WHERE r.Tranding_Type = pReturnType
                              AND r.Status_Cd = 1
                              AND IFNULL(r.Agent_Id, 0) = 0
                              AND IFNULL(r.Party_Id, 0) = IFNULL(m.Party_Id, 0)
                              AND rp.Prod_Id = p.Prod_Id
                              AND (
                                    r.Ref_No = m.Invoice_No
                                 OR r.Ref_No = m.Ref_No
                              )
                        ), 0)
                    ) AS Remaining_Qty,
                    p.Item_Rate,
                    p.Unit_Id,
                    UDF_GET_UNIT_NAME(p.Unit_Id) AS Unit_Name,
                    p.Item_Total,
                    p.MRP,
                    p.Disc_Prcnt,
                    p.Disc_Amt,
                    p.Taxable_Amt,
                    p.SGST_Prcnt,
                    p.CGST_Prcnt,
                    p.SGST_Amt,
                    p.CGST_Amt,
                    p.Net_Amt,
                    (
                        SELECT s.Pack_Date
                        FROM trns_stockinout s
                        WHERE s.Type_Id = m.Trading_Id
                          AND s.Prod_Id = p.Prod_Id
                        ORDER BY s.Stock_Id
                        LIMIT 1
                    ) AS Pack_Date
                FROM trans_trading_products p
                INNER JOIN mst_product i ON i.Prod_Id = p.Prod_Id
                WHERE p.Trading_Id = m.Trading_Id
            ) x
            WHERE x.Remaining_Qty > 0
        ) AS Item_Details
    FROM trans_trading m
    LEFT JOIN trans_voucher_master v ON v.Type_Id = m.Trading_Id
    WHERE m.Trading_Id = pSale_Id
      AND m.Tranding_Type = pSaleType
      AND IFNULL(m.Agent_Id, 0) = 0;
END ;;
DELIMITER ;
/*!50003 SET sql_mode              = @saved_sql_mode */ ;
/*!50003 SET character_set_client  = @saved_cs_client */ ;
/*!50003 SET character_set_results = @saved_cs_results */ ;
/*!50003 SET collation_connection  = @saved_col_connection */ ;
/*!50003 DROP PROCEDURE IF EXISTS `USP_GET_SHARE_CONFIG` */;
/*!50003 SET @saved_cs_client      = @@character_set_client */ ;
/*!50003 SET @saved_cs_results     = @@character_set_results */ ;
/*!50003 SET @saved_col_connection = @@collation_connection */ ;
/*!50003 SET character_set_client  = utf8mb4 */ ;
/*!50003 SET character_set_results = utf8mb4 */ ;
/*!50003 SET collation_connection  = utf8mb4_unicode_ci */ ;
/*!50003 SET @saved_sql_mode       = @@sql_mode */ ;
/*!50003 SET sql_mode              = 'ONLY_FULL_GROUP_BY,STRICT_TRANS_TABLES,NO_ZERO_IN_DATE,NO_ZERO_DATE,ERROR_FOR_DIVISION_BY_ZERO,NO_ENGINE_SUBSTITUTION' */ ;
DELIMITER ;;
CREATE DEFINER=`priority`@`%` PROCEDURE `USP_GET_SHARE_CONFIG`()
BEGIN
    SELECT id, adm_fees, rate_share
      FROM mst_share_config
     ORDER BY id
     LIMIT 1;
END ;;
DELIMITER ;
/*!50003 SET sql_mode              = @saved_sql_mode */ ;
/*!50003 SET character_set_client  = @saved_cs_client */ ;
/*!50003 SET character_set_results = @saved_cs_results */ ;
/*!50003 SET collation_connection  = @saved_col_connection */ ;
/*!50003 DROP PROCEDURE IF EXISTS `USP_GET_SIDEBAR_MENUE` */;
/*!50003 SET @saved_cs_client      = @@character_set_client */ ;
/*!50003 SET @saved_cs_results     = @@character_set_results */ ;
/*!50003 SET @saved_col_connection = @@collation_connection */ ;
/*!50003 SET character_set_client  = utf8mb4 */ ;
/*!50003 SET character_set_results = utf8mb4 */ ;
/*!50003 SET collation_connection  = utf8mb4_unicode_ci */ ;
/*!50003 SET @saved_sql_mode       = @@sql_mode */ ;
/*!50003 SET sql_mode              = 'ONLY_FULL_GROUP_BY,STRICT_TRANS_TABLES,NO_ZERO_IN_DATE,NO_ZERO_DATE,ERROR_FOR_DIVISION_BY_ZERO,NO_ENGINE_SUBSTITUTION' */ ;
DELIMITER ;;
CREATE DEFINER=`priority`@`%` PROCEDURE `USP_GET_SIDEBAR_MENUE`(
    pUser_Group INT
)
BEGIN
    DECLARE vIsAdmin TINYINT DEFAULT 0;

    SELECT IFNULL(Is_Admin, 0)
      INTO vIsAdmin
      FROM mst_user_group
     WHERE UGrp_Id = pUser_Group
     LIMIT 1;

    IF (vIsAdmin = 1) THEN
        SELECT
            m.Menu_Id AS Parraint_Id,
            m.Menu_Name AS Parraint_Name,
            NULL AS Child_Id,
            NULL AS Child_Name,
            NULL AS Child_Route,
            m.Icon AS Parraint_Icon,
            m.Route AS Parraint_Route
        FROM mst_menus m
        WHERE m.Is_Active = 1
          AND m.SubMenu_Id IS NULL

        UNION ALL

        SELECT
            m.Menu_Id AS Parraint_Id,
            NULL AS Parraint_Name,
            m.SubMenu_Id AS Child_Id,
            m.SubMenu_Name AS Child_Name,
            m.Route AS Child_Route,
            NULL AS Parraint_Icon,
            NULL AS Parraint_Route
        FROM mst_menus m
        WHERE m.Is_Active = 1
          AND m.SubMenu_Id IS NOT NULL

        ORDER BY Parraint_Id, Child_Id;
    ELSE
        SELECT
            m.Menu_Id AS Parraint_Id,
            m.Menu_Name AS Parraint_Name,
            NULL AS Child_Id,
            NULL AS Child_Name,
            NULL AS Child_Route,
            m.Icon AS Parraint_Icon,
            m.Route AS Parraint_Route
        FROM mst_menus m
        WHERE m.Is_Active = 1
          AND m.SubMenu_Id IS NULL
          AND EXISTS (
                SELECT 1
                  FROM mst_user_permission p
                 WHERE p.Menu_Id = m.Menu_Id
                   AND p.UGrp_Id = pUser_Group
                   AND CAST(p.User_View AS UNSIGNED) = 1
                   AND CAST(p.Is_Active AS UNSIGNED) = 1
          )

        UNION ALL

        SELECT
            m.Menu_Id AS Parraint_Id,
            NULL AS Parraint_Name,
            m.SubMenu_Id AS Child_Id,
            m.SubMenu_Name AS Child_Name,
            m.Route AS Child_Route,
            NULL AS Parraint_Icon,
            NULL AS Parraint_Route
        FROM mst_menus m
        INNER JOIN mst_user_permission p
            ON p.Menu_Id = m.Menu_Id
           AND p.Sub_men_Id = m.SubMenu_Id
        WHERE m.Is_Active = 1
          AND m.SubMenu_Id IS NOT NULL
          AND p.UGrp_Id = pUser_Group
          AND CAST(p.User_View AS UNSIGNED) = 1
          AND CAST(p.Is_Active AS UNSIGNED) = 1

        ORDER BY Parraint_Id, Child_Id;
    END IF;
END ;;
DELIMITER ;
/*!50003 SET sql_mode              = @saved_sql_mode */ ;
/*!50003 SET character_set_client  = @saved_cs_client */ ;
/*!50003 SET character_set_results = @saved_cs_results */ ;
/*!50003 SET collation_connection  = @saved_col_connection */ ;
/*!50003 DROP PROCEDURE IF EXISTS `USP_GET_STOCK_SUMMARY` */;
/*!50003 SET @saved_cs_client      = @@character_set_client */ ;
/*!50003 SET @saved_cs_results     = @@character_set_results */ ;
/*!50003 SET @saved_col_connection = @@collation_connection */ ;
/*!50003 SET character_set_client  = utf8mb4 */ ;
/*!50003 SET character_set_results = utf8mb4 */ ;
/*!50003 SET collation_connection  = utf8mb4_unicode_ci */ ;
/*!50003 SET @saved_sql_mode       = @@sql_mode */ ;
/*!50003 SET sql_mode              = 'ONLY_FULL_GROUP_BY,STRICT_TRANS_TABLES,NO_ZERO_IN_DATE,NO_ZERO_DATE,ERROR_FOR_DIVISION_BY_ZERO,NO_ENGINE_SUBSTITUTION' */ ;
DELIMITER ;;
CREATE DEFINER=`priority`@`%` PROCEDURE `USP_GET_STOCK_SUMMARY`(
    pDate        DATE,
    pCate_Id     INT,
    pSubCate_Id  INT
)
BEGIN
    SELECT
        p.Prod_Id,
        p.Prod_Code,
        p.Prod_ShortNm,
        IFNULL(UDF_GET_CAT_SUB_NAME(p.Cate_Id, 1), '') AS Cate_Name,
        IFNULL(UDF_GET_CAT_SUB_NAME(p.SubCate_Id, 2), '') AS SubCate_Name,
        p.Unit_Id,
        IFNULL(UDF_GET_UNIT_NAME(p.Unit_Id), '') AS Unit_Name,
        x.MRP,
        x.Qty,
        ROUND(x.Qty * x.MRP, 2) AS Value
    FROM (
        SELECT
            s.Prod_Id,
            CAST(IFNULL(s.MRP, 0) AS DECIMAL(10,2)) AS MRP,
            IFNULL(SUM(
                CASE
                    WHEN s.Stock_Type IN (1, 2, 6) THEN s.Quantity
                    WHEN s.Stock_Type = 8 AND NOT EXISTS (
                        SELECT 1 FROM trans_agent_stock a WHERE a.TrnsStock_Id = s.Stock_Id
                    ) THEN s.Quantity
                    WHEN s.Stock_Type IN (3, 4, 7) THEN -s.Quantity
                    ELSE 0
                END
            ), 0) AS Qty
        FROM trns_stockinout s
        WHERE s.InOut_Date <= pDate
        GROUP BY s.Prod_Id, CAST(IFNULL(s.MRP, 0) AS DECIMAL(10,2))
    ) x
    INNER JOIN mst_product p ON p.Prod_Id = x.Prod_Id
    WHERE x.Qty <> 0
      AND (pCate_Id = 0 OR p.Cate_Id = pCate_Id)
      AND (pSubCate_Id = 0 OR p.SubCate_Id = pSubCate_Id)
    ORDER BY p.Prod_ShortNm, x.MRP;
END ;;
DELIMITER ;
/*!50003 SET sql_mode              = @saved_sql_mode */ ;
/*!50003 SET character_set_client  = @saved_cs_client */ ;
/*!50003 SET character_set_results = @saved_cs_results */ ;
/*!50003 SET collation_connection  = @saved_col_connection */ ;
/*!50003 DROP PROCEDURE IF EXISTS `USP_GET_USER_GROUP` */;
/*!50003 SET @saved_cs_client      = @@character_set_client */ ;
/*!50003 SET @saved_cs_results     = @@character_set_results */ ;
/*!50003 SET @saved_col_connection = @@collation_connection */ ;
/*!50003 SET character_set_client  = utf8mb4 */ ;
/*!50003 SET character_set_results = utf8mb4 */ ;
/*!50003 SET collation_connection  = utf8mb4_0900_ai_ci */ ;
/*!50003 SET @saved_sql_mode       = @@sql_mode */ ;
/*!50003 SET sql_mode              = 'ONLY_FULL_GROUP_BY,STRICT_TRANS_TABLES,NO_ZERO_IN_DATE,NO_ZERO_DATE,ERROR_FOR_DIVISION_BY_ZERO,NO_ENGINE_SUBSTITUTION' */ ;
DELIMITER ;;
CREATE DEFINER=`priority`@`%` PROCEDURE `USP_GET_USER_GROUP`()
BEGIN

Select UGrp_Id,UGrp_Name,UGrp_Description,Is_Admin From mst_user_group;

END ;;
DELIMITER ;
/*!50003 SET sql_mode              = @saved_sql_mode */ ;
/*!50003 SET character_set_client  = @saved_cs_client */ ;
/*!50003 SET character_set_results = @saved_cs_results */ ;
/*!50003 SET collation_connection  = @saved_col_connection */ ;
/*!50003 DROP PROCEDURE IF EXISTS `USP_GET_USER_LIST` */;
/*!50003 SET @saved_cs_client      = @@character_set_client */ ;
/*!50003 SET @saved_cs_results     = @@character_set_results */ ;
/*!50003 SET @saved_col_connection = @@collation_connection */ ;
/*!50003 SET character_set_client  = utf8mb4 */ ;
/*!50003 SET character_set_results = utf8mb4 */ ;
/*!50003 SET collation_connection  = utf8mb4_0900_ai_ci */ ;
/*!50003 SET @saved_sql_mode       = @@sql_mode */ ;
/*!50003 SET sql_mode              = 'ONLY_FULL_GROUP_BY,STRICT_TRANS_TABLES,NO_ZERO_IN_DATE,NO_ZERO_DATE,ERROR_FOR_DIVISION_BY_ZERO,NO_ENGINE_SUBSTITUTION' */ ;
DELIMITER ;;
CREATE DEFINER=`priority`@`%` PROCEDURE `USP_GET_USER_LIST`(
	pBranch_Id			Smallint
)
BEGIN

Select m.User_Id,m.User_Code,m.User_FullName,m.Contact_No,m.Email_Id,m.User_Name,m.Status_Cd,g.UGrp_Name,m.UGrp_Id From mst_user m join mst_user_group g On g.UGrp_Id=m.UGrp_Id Where m.Branch_Id=pBranch_Id;

END ;;
DELIMITER ;
/*!50003 SET sql_mode              = @saved_sql_mode */ ;
/*!50003 SET character_set_client  = @saved_cs_client */ ;
/*!50003 SET character_set_results = @saved_cs_results */ ;
/*!50003 SET collation_connection  = @saved_col_connection */ ;
/*!50003 DROP PROCEDURE IF EXISTS `USP_GET_WEEKLY_PURCHASE_SALE` */;
/*!50003 SET @saved_cs_client      = @@character_set_client */ ;
/*!50003 SET @saved_cs_results     = @@character_set_results */ ;
/*!50003 SET @saved_col_connection = @@collation_connection */ ;
/*!50003 SET character_set_client  = utf8mb4 */ ;
/*!50003 SET character_set_results = utf8mb4 */ ;
/*!50003 SET collation_connection  = utf8mb4_0900_ai_ci */ ;
/*!50003 SET @saved_sql_mode       = @@sql_mode */ ;
/*!50003 SET sql_mode              = 'ONLY_FULL_GROUP_BY,STRICT_TRANS_TABLES,NO_ZERO_IN_DATE,NO_ZERO_DATE,ERROR_FOR_DIVISION_BY_ZERO,NO_ENGINE_SUBSTITUTION' */ ;
DELIMITER ;;
CREATE DEFINER=`priority`@`%` PROCEDURE `USP_GET_WEEKLY_PURCHASE_SALE`(
    pBranch_Id  SMALLINT,
    pYear_Id    TINYINT
)
BEGIN
    DECLARE pWeekStart DATE;
    DECLARE pYearStart DATE;
    DECLARE pYearEnd   DATE;

    SELECT Year_Start, Year_End
      INTO pYearStart, pYearEnd
      FROM mst_accountingyear
     WHERE Year_Id = pYear_Id;

    SET pWeekStart = DATE_SUB(CURDATE(), INTERVAL WEEKDAY(CURDATE()) DAY);

    SELECT
        DATE_FORMAT(d.Week_Date, '%a') AS Day_Label,
        d.Week_Date AS Trans_Date,
        IFNULL((
            SELECT SUM(t.Net_Amt)
            FROM trans_trading t
            WHERE t.Branch_Id = pBranch_Id
              AND t.Tranding_Type = 3
              AND t.Status_Cd = 1
              AND t.Invoice_Date = d.Week_Date
              AND t.Invoice_Date BETWEEN pYearStart AND pYearEnd
        ), 0) AS Sales_Amt,
        IFNULL((
            SELECT SUM(t.Net_Amt)
            FROM trans_trading t
            WHERE t.Branch_Id = pBranch_Id
              AND t.Tranding_Type = 2
              AND t.Status_Cd = 1
              AND t.Invoice_Date = d.Week_Date
              AND t.Invoice_Date BETWEEN pYearStart AND pYearEnd
        ), 0) AS Purchase_Amt
    FROM (
        SELECT DATE_ADD(pWeekStart, INTERVAL 0 DAY) AS Week_Date
        UNION ALL SELECT DATE_ADD(pWeekStart, INTERVAL 1 DAY)
        UNION ALL SELECT DATE_ADD(pWeekStart, INTERVAL 2 DAY)
        UNION ALL SELECT DATE_ADD(pWeekStart, INTERVAL 3 DAY)
        UNION ALL SELECT DATE_ADD(pWeekStart, INTERVAL 4 DAY)
        UNION ALL SELECT DATE_ADD(pWeekStart, INTERVAL 5 DAY)
        UNION ALL SELECT DATE_ADD(pWeekStart, INTERVAL 6 DAY)
    ) d;
END ;;
DELIMITER ;
/*!50003 SET sql_mode              = @saved_sql_mode */ ;
/*!50003 SET character_set_client  = @saved_cs_client */ ;
/*!50003 SET character_set_results = @saved_cs_results */ ;
/*!50003 SET collation_connection  = @saved_col_connection */ ;
/*!50003 DROP PROCEDURE IF EXISTS `USP_RPT_AGENT_INDENT` */;
/*!50003 SET @saved_cs_client      = @@character_set_client */ ;
/*!50003 SET @saved_cs_results     = @@character_set_results */ ;
/*!50003 SET @saved_col_connection = @@collation_connection */ ;
/*!50003 SET character_set_client  = utf8mb4 */ ;
/*!50003 SET character_set_results = utf8mb4 */ ;
/*!50003 SET collation_connection  = utf8mb4_unicode_ci */ ;
/*!50003 SET @saved_sql_mode       = @@sql_mode */ ;
/*!50003 SET sql_mode              = 'ONLY_FULL_GROUP_BY,STRICT_TRANS_TABLES,NO_ZERO_IN_DATE,NO_ZERO_DATE,ERROR_FOR_DIVISION_BY_ZERO,NO_ENGINE_SUBSTITUTION' */ ;
DELIMITER ;;
CREATE DEFINER=`priority`@`%` PROCEDURE `USP_RPT_AGENT_INDENT`(
    pFrm_Date   DATE,
    pTo_Date    DATE,
    pAgent_Id   INT
)
BEGIN
    SELECT
        m.Indent_No,
        m.Indent_Date,
        a.Agent_Name,
        p.Prod_Code,
        p.Prod_ShortNm,
        IFNULL(d.Quantity, 0) AS Req_Qty,
        IFNULL(d.Reject_Qty, 0) AS Reject_Qty,
        (IFNULL(d.Quantity, 0) - IFNULL(d.Reject_Qty, 0)) AS Issue_Qty,
        UDF_GET_UNIT_NAME(d.Unit_Id) AS Unit_Name,
        CASE WHEN m.Is_Issued = 1 THEN 'Issued' ELSE 'Pending' END AS Status_Name
    FROM trans_agent_indent m
    JOIN trans_agent_indent_details d ON d.Indent_Id = m.Indent_Id
    JOIN mst_product p ON p.Prod_Id = d.Prod_Id
    JOIN mst_agent a ON a.Agent_Id = m.Agent_Id
    WHERE m.Indent_Date BETWEEN pFrm_Date AND pTo_Date
      AND (pAgent_Id = 0 OR m.Agent_Id = pAgent_Id)
    ORDER BY m.Indent_Date, m.Indent_Id, d.Indent_Sl;
END ;;
DELIMITER ;
/*!50003 SET sql_mode              = @saved_sql_mode */ ;
/*!50003 SET character_set_client  = @saved_cs_client */ ;
/*!50003 SET character_set_results = @saved_cs_results */ ;
/*!50003 SET collation_connection  = @saved_col_connection */ ;
/*!50003 DROP PROCEDURE IF EXISTS `USP_RPT_AGENT_STOCK` */;
/*!50003 SET @saved_cs_client      = @@character_set_client */ ;
/*!50003 SET @saved_cs_results     = @@character_set_results */ ;
/*!50003 SET @saved_col_connection = @@collation_connection */ ;
/*!50003 SET character_set_client  = utf8mb4 */ ;
/*!50003 SET character_set_results = utf8mb4 */ ;
/*!50003 SET collation_connection  = utf8mb4_unicode_ci */ ;
/*!50003 SET @saved_sql_mode       = @@sql_mode */ ;
/*!50003 SET sql_mode              = 'ONLY_FULL_GROUP_BY,STRICT_TRANS_TABLES,NO_ZERO_IN_DATE,NO_ZERO_DATE,ERROR_FOR_DIVISION_BY_ZERO,NO_ENGINE_SUBSTITUTION' */ ;
DELIMITER ;;
CREATE DEFINER=`priority`@`%` PROCEDURE `USP_RPT_AGENT_STOCK`(
    pDate       DATE,
    pAgent_Id   INT
)
BEGIN
    SELECT
        a.Agent_Name,
        p.Prod_Code,
        p.Prod_ShortNm,
        UDF_GET_UNIT_NAME(p.Unit_Id) AS Unit_Name,
        UDF_CAL_AGENT_STOCK(a.Agent_Id, p.Prod_Id, pDate) AS Qty
    FROM mst_agent a
    CROSS JOIN mst_product p
    WHERE a.Status_Cd = 1
      AND (pAgent_Id = 0 OR a.Agent_Id = pAgent_Id)
      AND UDF_CAL_AGENT_STOCK(a.Agent_Id, p.Prod_Id, pDate) <> 0
    ORDER BY a.Agent_Name, p.Prod_ShortNm;
END ;;
DELIMITER ;
/*!50003 SET sql_mode              = @saved_sql_mode */ ;
/*!50003 SET character_set_client  = @saved_cs_client */ ;
/*!50003 SET character_set_results = @saved_cs_results */ ;
/*!50003 SET collation_connection  = @saved_col_connection */ ;
/*!50003 DROP PROCEDURE IF EXISTS `USP_RPT_BALANCE_SHEET` */;
/*!50003 SET @saved_cs_client      = @@character_set_client */ ;
/*!50003 SET @saved_cs_results     = @@character_set_results */ ;
/*!50003 SET @saved_col_connection = @@collation_connection */ ;
/*!50003 SET character_set_client  = utf8mb4 */ ;
/*!50003 SET character_set_results = utf8mb4 */ ;
/*!50003 SET collation_connection  = utf8mb4_unicode_ci */ ;
/*!50003 SET @saved_sql_mode       = @@sql_mode */ ;
/*!50003 SET sql_mode              = 'ONLY_FULL_GROUP_BY,STRICT_TRANS_TABLES,NO_ZERO_IN_DATE,NO_ZERO_DATE,ERROR_FOR_DIVISION_BY_ZERO,NO_ENGINE_SUBSTITUTION' */ ;
DELIMITER ;;
CREATE DEFINER=`priority`@`%` PROCEDURE `USP_RPT_BALANCE_SHEET`(
    pYear_Id INT,
    pDate    DATE
)
BEGIN
    DECLARE vNP DECIMAL(18,2) DEFAULT 0;
    DECLARE vLiab DECIMAL(18,2) DEFAULT 0;
    DECLARE vAst DECIMAL(18,2) DEFAULT 0;

    DROP TEMPORARY TABLE IF EXISTS tmp_gl_bal;
    CREATE TEMPORARY TABLE tmp_gl_bal (
        Account_Id   SMALLINT,
        Account_Code VARCHAR(6),
        Account_Desc VARCHAR(200),
        Cate_Id      TINYINT,
        Cate_Desc    VARCHAR(25),
        Bal          DECIMAL(18,2)
    );

    INSERT INTO tmp_gl_bal (Account_Id, Account_Code, Account_Desc, Cate_Id, Cate_Desc, Bal)
    SELECT
        g.Account_Id,
        g.Account_Code,
        g.Account_Desc,
        c.Cate_Id,
        c.Cate_Desc,
        IFNULL((
            SELECT SUM(CASE WHEN d.Trans_Type = 'D' THEN d.Vou_Amount ELSE -d.Vou_Amount END)
            FROM trans_voucher_details d
            INNER JOIN trans_voucher_master m ON m.Voucher_Id = d.Voucher_Id
            WHERE d.GlHead_Id = g.Account_Id
              AND m.Year_Id = pYear_Id
              AND IFNULL(m.Status, 2) = 2
              AND m.Vou_Date <= pDate
        ), 0)
    FROM mst_acct_glhead g
    INNER JOIN mst_acct_mainhd h ON h.MainHd_Id = g.MainHd_Id
    INNER JOIN mst_acct_category c ON c.Cate_Id = h.Cate_Id;

    SELECT IFNULL(-SUM(Bal), 0) INTO vNP
    FROM tmp_gl_bal
    WHERE Cate_Id IN (3, 4, 5, 6);

    DROP TEMPORARY TABLE IF EXISTS tmp_bs;
    CREATE TEMPORARY TABLE tmp_bs (
        Sort_No     INT,
        Group_Name  VARCHAR(20),
        Particulars VARCHAR(250),
        Amount      DECIMAL(18,2),
        Row_Kind    TINYINT
    );

    INSERT INTO tmp_bs VALUES (10, 'Liabilities', 'Liabilities', NULL, 1);

    INSERT INTO tmp_bs (Sort_No, Group_Name, Particulars, Amount, Row_Kind)
    SELECT
        20,
        'Liabilities',
        Account_Desc,
        -Bal,
        0
    FROM tmp_gl_bal
    WHERE Cate_Id IN (1, 2)
      AND Bal < 0;

    IF vNP > 0 THEN
        INSERT INTO tmp_bs VALUES (30, 'Liabilities', 'Net Profit', vNP, 3);
    END IF;

    SELECT IFNULL(SUM(Amount), 0) INTO vLiab
    FROM tmp_bs
    WHERE Group_Name = 'Liabilities' AND Row_Kind <> 1;

    INSERT INTO tmp_bs VALUES (40, 'Liabilities', 'Total', vLiab, 2);

    INSERT INTO tmp_bs VALUES (50, 'Assets', 'Assets', NULL, 1);

    INSERT INTO tmp_bs (Sort_No, Group_Name, Particulars, Amount, Row_Kind)
    SELECT
        60,
        'Assets',
        Account_Desc,
        Bal,
        0
    FROM tmp_gl_bal
    WHERE Cate_Id IN (1, 2)
      AND Bal > 0;

    IF vNP < 0 THEN
        INSERT INTO tmp_bs VALUES (70, 'Assets', 'Net Loss', -vNP, 3);
    END IF;

    SELECT IFNULL(SUM(Amount), 0) INTO vAst
    FROM tmp_bs
    WHERE Group_Name = 'Assets' AND Row_Kind <> 1;

    INSERT INTO tmp_bs VALUES (80, 'Assets', 'Total', vAst, 2);

    SELECT Group_Name, Particulars, Amount, Row_Kind
    FROM tmp_bs
    ORDER BY Sort_No, Particulars;
END ;;
DELIMITER ;
/*!50003 SET sql_mode              = @saved_sql_mode */ ;
/*!50003 SET character_set_client  = @saved_cs_client */ ;
/*!50003 SET character_set_results = @saved_cs_results */ ;
/*!50003 SET collation_connection  = @saved_col_connection */ ;
/*!50003 DROP PROCEDURE IF EXISTS `USP_RPT_CASH_ACCOUNT` */;
/*!50003 SET @saved_cs_client      = @@character_set_client */ ;
/*!50003 SET @saved_cs_results     = @@character_set_results */ ;
/*!50003 SET @saved_col_connection = @@collation_connection */ ;
/*!50003 SET character_set_client  = utf8mb4 */ ;
/*!50003 SET character_set_results = utf8mb4 */ ;
/*!50003 SET collation_connection  = utf8mb4_unicode_ci */ ;
/*!50003 SET @saved_sql_mode       = @@sql_mode */ ;
/*!50003 SET sql_mode              = 'ONLY_FULL_GROUP_BY,STRICT_TRANS_TABLES,NO_ZERO_IN_DATE,NO_ZERO_DATE,ERROR_FOR_DIVISION_BY_ZERO,NO_ENGINE_SUBSTITUTION' */ ;
DELIMITER ;;
CREATE DEFINER=`priority`@`%` PROCEDURE `USP_RPT_CASH_ACCOUNT`(
    pYear_Id   INT,
    pFrm_Date  DATE,
    pTo_Date   DATE
)
BEGIN
    DECLARE vCashId INT DEFAULT 0;
    DECLARE vOpen DECIMAL(18,2) DEFAULT 0;

    SELECT IFNULL((
        SELECT d.Cash_Ledg FROM mst_default_ledger d WHERE d.Cash_Ledg IS NOT NULL LIMIT 1
    ), 0) INTO vCashId;

    IF vCashId = 0 THEN
        SELECT IFNULL((
            SELECT g.Account_Id FROM mst_acct_glhead g WHERE g.Account_For = 'C' LIMIT 1
        ), 0) INTO vCashId;
    END IF;

    SET vOpen = IFNULL((
        SELECT SUM(CASE WHEN d.Trans_Type = 'D' THEN d.Vou_Amount ELSE -d.Vou_Amount END)
        FROM trans_voucher_details d
        INNER JOIN trans_voucher_master m ON m.Voucher_Id = d.Voucher_Id
        WHERE d.GlHead_Id = vCashId
          AND m.Year_Id = pYear_Id
          AND IFNULL(m.Status, 2) = 2
          AND m.Vou_Date < pFrm_Date
    ), 0);

    DROP TEMPORARY TABLE IF EXISTS tmp_cash_ac;
    CREATE TEMPORARY TABLE tmp_cash_ac (
        Sort_No     INT,
        Voucher_Id  BIGINT,
        Vou_Date    DATE,
        Vou_No      VARCHAR(50),
        Particulars VARCHAR(250),
        Debit       DECIMAL(18,2),
        Credit      DECIMAL(18,2)
    );

    INSERT INTO tmp_cash_ac
    VALUES (
        0, 0, NULL, '', 'Opening Balance',
        CASE WHEN vOpen > 0 THEN vOpen ELSE 0 END,
        CASE WHEN vOpen < 0 THEN -vOpen ELSE 0 END
    );

    INSERT INTO tmp_cash_ac
    SELECT
        1,
        m.Voucher_Id,
        m.Vou_Date,
        IFNULL(m.Vou_No, ''),
        IFNULL(NULLIF(TRIM(m.Particulars), ''), IFNULL(m.Ref_Vou_No, '')),
        CASE WHEN d.Trans_Type = 'D' THEN IFNULL(d.Vou_Amount, 0) ELSE 0 END,
        CASE WHEN d.Trans_Type = 'C' THEN IFNULL(d.Vou_Amount, 0) ELSE 0 END
    FROM trans_voucher_details d
    INNER JOIN trans_voucher_master m ON m.Voucher_Id = d.Voucher_Id
    WHERE d.GlHead_Id = vCashId
      AND m.Year_Id = pYear_Id
      AND IFNULL(m.Status, 2) = 2
      AND m.Vou_Date BETWEEN pFrm_Date AND pTo_Date;

    SELECT
        t.Vou_Date,
        t.Vou_No,
        t.Particulars,
        t.Debit,
        t.Credit,
        SUM(t.Debit - t.Credit) OVER (
            ORDER BY t.Sort_No, t.Vou_Date, t.Voucher_Id
            ROWS UNBOUNDED PRECEDING
        ) AS Balance
    FROM tmp_cash_ac t
    ORDER BY t.Sort_No, t.Vou_Date, t.Voucher_Id;
END ;;
DELIMITER ;
/*!50003 SET sql_mode              = @saved_sql_mode */ ;
/*!50003 SET character_set_client  = @saved_cs_client */ ;
/*!50003 SET character_set_results = @saved_cs_results */ ;
/*!50003 SET collation_connection  = @saved_col_connection */ ;
/*!50003 DROP PROCEDURE IF EXISTS `USP_RPT_CASH_BOOK` */;
/*!50003 SET @saved_cs_client      = @@character_set_client */ ;
/*!50003 SET @saved_cs_results     = @@character_set_results */ ;
/*!50003 SET @saved_col_connection = @@collation_connection */ ;
/*!50003 SET character_set_client  = utf8mb4 */ ;
/*!50003 SET character_set_results = utf8mb4 */ ;
/*!50003 SET collation_connection  = utf8mb4_unicode_ci */ ;
/*!50003 SET @saved_sql_mode       = @@sql_mode */ ;
/*!50003 SET sql_mode              = 'ONLY_FULL_GROUP_BY,STRICT_TRANS_TABLES,NO_ZERO_IN_DATE,NO_ZERO_DATE,ERROR_FOR_DIVISION_BY_ZERO,NO_ENGINE_SUBSTITUTION' */ ;
DELIMITER ;;
CREATE DEFINER=`priority`@`%` PROCEDURE `USP_RPT_CASH_BOOK`(
    pYear_Id   INT,
    pFrm_Date  DATE,
    pTo_Date   DATE
)
BEGIN
    DECLARE vCashId INT DEFAULT 0;
    DECLARE vOpen DECIMAL(18,2) DEFAULT 0;

    SELECT IFNULL((
        SELECT d.Cash_Ledg FROM mst_default_ledger d WHERE d.Cash_Ledg IS NOT NULL LIMIT 1
    ), 0) INTO vCashId;

    IF vCashId = 0 THEN
        SELECT IFNULL((
            SELECT g.Account_Id FROM mst_acct_glhead g WHERE g.Account_For = 'C' LIMIT 1
        ), 0) INTO vCashId;
    END IF;

    SET vOpen = IFNULL((
        SELECT SUM(CASE WHEN d.Trans_Type = 'D' THEN d.Vou_Amount ELSE -d.Vou_Amount END)
        FROM trans_voucher_details d
        INNER JOIN trans_voucher_master m ON m.Voucher_Id = d.Voucher_Id
        WHERE d.GlHead_Id = vCashId
          AND m.Year_Id = pYear_Id
          AND IFNULL(m.Status, 2) = 2
          AND m.Vou_Date < pFrm_Date
    ), 0);

    DROP TEMPORARY TABLE IF EXISTS tmp_cash_book;
    CREATE TEMPORARY TABLE tmp_cash_book (
        Sort_No       INT,
        Voucher_Id    BIGINT,
        Vou_Date      DATE,
        Vou_No        VARCHAR(50),
        Vou_Type_Name VARCHAR(20),
        Particulars   VARCHAR(250),
        Receipt       DECIMAL(18,2),
        Payment       DECIMAL(18,2)
    );

    INSERT INTO tmp_cash_book
    VALUES (
        0, 0, NULL, '', '', 'Opening Balance',
        CASE WHEN vOpen > 0 THEN vOpen ELSE 0 END,
        CASE WHEN vOpen < 0 THEN -vOpen ELSE 0 END
    );

    INSERT INTO tmp_cash_book
    SELECT
        1,
        m.Voucher_Id,
        m.Vou_Date,
        IFNULL(m.Vou_No, ''),
        CASE m.Vou_Type
            WHEN 1 THEN 'Receipt'
            WHEN 2 THEN 'Payment'
            WHEN 3 THEN 'Contra'
            WHEN 4 THEN 'Journal'
            ELSE ''
        END,
        IFNULL(NULLIF(TRIM(m.Particulars), ''), IFNULL(m.Ref_Vou_No, '')),
        CASE WHEN d.Trans_Type = 'D' THEN IFNULL(d.Vou_Amount, 0) ELSE 0 END,
        CASE WHEN d.Trans_Type = 'C' THEN IFNULL(d.Vou_Amount, 0) ELSE 0 END
    FROM trans_voucher_details d
    INNER JOIN trans_voucher_master m ON m.Voucher_Id = d.Voucher_Id
    WHERE d.GlHead_Id = vCashId
      AND m.Year_Id = pYear_Id
      AND IFNULL(m.Status, 2) = 2
      AND m.Vou_Date BETWEEN pFrm_Date AND pTo_Date;

    SELECT
        t.Vou_Date,
        t.Vou_No,
        t.Vou_Type_Name,
        t.Particulars,
        t.Receipt,
        t.Payment,
        SUM(t.Receipt - t.Payment) OVER (
            ORDER BY t.Sort_No, t.Vou_Date, t.Voucher_Id
            ROWS UNBOUNDED PRECEDING
        ) AS Balance
    FROM tmp_cash_book t
    ORDER BY t.Sort_No, t.Vou_Date, t.Voucher_Id;
END ;;
DELIMITER ;
/*!50003 SET sql_mode              = @saved_sql_mode */ ;
/*!50003 SET character_set_client  = @saved_cs_client */ ;
/*!50003 SET character_set_results = @saved_cs_results */ ;
/*!50003 SET collation_connection  = @saved_col_connection */ ;
/*!50003 DROP PROCEDURE IF EXISTS `USP_RPT_CUSTOMER_REGISTER` */;
/*!50003 SET @saved_cs_client      = @@character_set_client */ ;
/*!50003 SET @saved_cs_results     = @@character_set_results */ ;
/*!50003 SET @saved_col_connection = @@collation_connection */ ;
/*!50003 SET character_set_client  = utf8mb4 */ ;
/*!50003 SET character_set_results = utf8mb4 */ ;
/*!50003 SET collation_connection  = utf8mb4_unicode_ci */ ;
/*!50003 SET @saved_sql_mode       = @@sql_mode */ ;
/*!50003 SET sql_mode              = 'ONLY_FULL_GROUP_BY,STRICT_TRANS_TABLES,NO_ZERO_IN_DATE,NO_ZERO_DATE,ERROR_FOR_DIVISION_BY_ZERO,NO_ENGINE_SUBSTITUTION' */ ;
DELIMITER ;;
CREATE DEFINER=`priority`@`%` PROCEDURE `USP_RPT_CUSTOMER_REGISTER`(
    pFrm_Date   DATE,
    pTo_Date    DATE,
    pBranch_Id  INT
)
BEGIN
    SELECT
        p.Party_Id,
        p.Party_Code,
        p.Party_Name,
        UDF_CAL_PARTY_CLOSING(p.Party_Id, DATE_SUB(pFrm_Date, INTERVAL 1 DAY)) AS Opening_Amt,
        IFNULL(prd.Debit_Amt, 0) AS Sale_Amt,
        IFNULL(prd.Credit_Amt, 0) AS Receipt_Amt,
        UDF_CAL_PARTY_CLOSING(p.Party_Id, pTo_Date) AS Closing_Amt
    FROM mst_party p
    LEFT JOIN (
        SELECT Party_Id,
               SUM(CASE WHEN Trans_Type = 'D' AND IFNULL(Trading_Id, 0) > 0 THEN IFNULL(Amount, 0) ELSE 0 END) AS Debit_Amt,
               SUM(CASE
                   WHEN Trans_Type = 'C' AND IFNULL(Trading_Id, 0) = 0 THEN IFNULL(Amount, 0)
                   WHEN Trans_Type = 'D' AND Trans_Mode IN (1, 2) AND IFNULL(Trading_Id, 0) > 0 THEN IFNULL(Amount, 0)
                   ELSE 0
               END) AS Credit_Amt
        FROM trn_party_trans
        WHERE Trans_Date BETWEEN pFrm_Date AND pTo_Date
        GROUP BY Party_Id
    ) prd ON prd.Party_Id = p.Party_Id
    WHERE p.Party_Type = 2
      AND (pBranch_Id = 0 OR p.Branch_Id = pBranch_Id)
    ORDER BY p.Party_Name;
END ;;
DELIMITER ;
/*!50003 SET sql_mode              = @saved_sql_mode */ ;
/*!50003 SET character_set_client  = @saved_cs_client */ ;
/*!50003 SET character_set_results = @saved_cs_results */ ;
/*!50003 SET collation_connection  = @saved_col_connection */ ;
/*!50003 DROP PROCEDURE IF EXISTS `USP_RPT_EXPIRY` */;
/*!50003 SET @saved_cs_client      = @@character_set_client */ ;
/*!50003 SET @saved_cs_results     = @@character_set_results */ ;
/*!50003 SET @saved_col_connection = @@collation_connection */ ;
/*!50003 SET character_set_client  = utf8mb4 */ ;
/*!50003 SET character_set_results = utf8mb4 */ ;
/*!50003 SET collation_connection  = utf8mb4_unicode_ci */ ;
/*!50003 SET @saved_sql_mode       = @@sql_mode */ ;
/*!50003 SET sql_mode              = 'ONLY_FULL_GROUP_BY,STRICT_TRANS_TABLES,NO_ZERO_IN_DATE,NO_ZERO_DATE,ERROR_FOR_DIVISION_BY_ZERO,NO_ENGINE_SUBSTITUTION' */ ;
DELIMITER ;;
CREATE DEFINER=`priority`@`%` PROCEDURE `USP_RPT_EXPIRY`(
    pDate        DATE,
    pCate_Id     INT,
    pSubCate_Id  INT
)
BEGIN
    SELECT
        p.Prod_Code,
        p.Prod_ShortNm,
        IFNULL(UDF_GET_CAT_SUB_NAME(p.Cate_Id, 1), '') AS Cate_Name,
        UDF_GET_UNIT_NAME(p.Unit_Id) AS Unit_Name,
        x.Expiry_Date,
        DATEDIFF(x.Expiry_Date, pDate) AS Days_Left,
        x.Qty,
        CASE
            WHEN x.Expiry_Date < pDate THEN 'Expired'
            WHEN DATEDIFF(x.Expiry_Date, pDate) <= 30 THEN 'Expiring'
            ELSE 'OK'
        END AS Status_Name
    FROM (
        SELECT
            s.Prod_Id,
            s.Expiry_Date,
            IFNULL(SUM(
                CASE
                    WHEN s.Stock_Type IN (1, 2) THEN s.Quantity
                    WHEN s.Stock_Type = 7 THEN -s.Quantity
                    ELSE 0
                END
            ), 0) AS Qty
        FROM trns_stockinout s
        WHERE s.Expiry_Date IS NOT NULL
          AND s.InOut_Date <= pDate
        GROUP BY s.Prod_Id, s.Expiry_Date
    ) x
    JOIN mst_product p ON p.Prod_Id = x.Prod_Id
    WHERE x.Qty > 0
      AND (pCate_Id = 0 OR p.Cate_Id = pCate_Id)
      AND (pSubCate_Id = 0 OR p.SubCate_Id = pSubCate_Id)
    ORDER BY x.Expiry_Date, p.Prod_ShortNm;
END ;;
DELIMITER ;
/*!50003 SET sql_mode              = @saved_sql_mode */ ;
/*!50003 SET character_set_client  = @saved_cs_client */ ;
/*!50003 SET character_set_results = @saved_cs_results */ ;
/*!50003 SET collation_connection  = @saved_col_connection */ ;
/*!50003 DROP PROCEDURE IF EXISTS `USP_RPT_GST_REGISTER` */;
/*!50003 SET @saved_cs_client      = @@character_set_client */ ;
/*!50003 SET @saved_cs_results     = @@character_set_results */ ;
/*!50003 SET @saved_col_connection = @@collation_connection */ ;
/*!50003 SET character_set_client  = utf8mb4 */ ;
/*!50003 SET character_set_results = utf8mb4 */ ;
/*!50003 SET collation_connection  = utf8mb4_unicode_ci */ ;
/*!50003 SET @saved_sql_mode       = @@sql_mode */ ;
/*!50003 SET sql_mode              = 'ONLY_FULL_GROUP_BY,STRICT_TRANS_TABLES,NO_ZERO_IN_DATE,NO_ZERO_DATE,ERROR_FOR_DIVISION_BY_ZERO,NO_ENGINE_SUBSTITUTION' */ ;
DELIMITER ;;
CREATE DEFINER=`priority`@`%` PROCEDURE `USP_RPT_GST_REGISTER`(
    pFrm_Date   DATE,
    pTo_Date    DATE,
    pBranch_Id  INT
)
BEGIN
    SELECT
        m.Invoice_No,
        m.Invoice_Date,
        CASE m.Tranding_Type
            WHEN 2 THEN 'Purchase'
            WHEN 3 THEN 'Sales'
            WHEN 4 THEN 'Purchase Return'
            WHEN 5 THEN 'Sales Return'
            ELSE 'Other'
        END AS Doc_Type,
        IFNULL(g.HSN_No, '') AS HSN_No,
        IFNULL(g.Taxable_Amt, 0) AS Taxable_Amt,
        IFNULL(g.SGST_Prcnt, 0) AS SGST_Prcnt,
        IFNULL(g.SGST_Amt, 0) AS SGST_Amt,
        IFNULL(g.CGST_Prcnt, 0) AS CGST_Prcnt,
        IFNULL(g.CGST_Amt, 0) AS CGST_Amt,
        IFNULL(g.Tax_Amount, 0) AS Tax_Amount
    FROM trans_trading_gst g
    JOIN trans_trading m ON m.Trading_Id = g.Trading_Id
    WHERE IFNULL(m.Status_Cd, 1) = 1
      AND m.Invoice_Date BETWEEN pFrm_Date AND pTo_Date
      AND (pBranch_Id = 0 OR m.Branch_Id = pBranch_Id)
    ORDER BY m.Invoice_Date, m.Trading_Id, g.Id;
END ;;
DELIMITER ;
/*!50003 SET sql_mode              = @saved_sql_mode */ ;
/*!50003 SET character_set_client  = @saved_cs_client */ ;
/*!50003 SET character_set_results = @saved_cs_results */ ;
/*!50003 SET collation_connection  = @saved_col_connection */ ;
/*!50003 DROP PROCEDURE IF EXISTS `USP_RPT_JOURNAL_BOOK` */;
/*!50003 SET @saved_cs_client      = @@character_set_client */ ;
/*!50003 SET @saved_cs_results     = @@character_set_results */ ;
/*!50003 SET @saved_col_connection = @@collation_connection */ ;
/*!50003 SET character_set_client  = utf8mb4 */ ;
/*!50003 SET character_set_results = utf8mb4 */ ;
/*!50003 SET collation_connection  = utf8mb4_unicode_ci */ ;
/*!50003 SET @saved_sql_mode       = @@sql_mode */ ;
/*!50003 SET sql_mode              = 'ONLY_FULL_GROUP_BY,STRICT_TRANS_TABLES,NO_ZERO_IN_DATE,NO_ZERO_DATE,ERROR_FOR_DIVISION_BY_ZERO,NO_ENGINE_SUBSTITUTION' */ ;
DELIMITER ;;
CREATE DEFINER=`priority`@`%` PROCEDURE `USP_RPT_JOURNAL_BOOK`(
    pYear_Id   INT,
    pFrm_Date  DATE,
    pTo_Date   DATE
)
BEGIN
    SELECT
        m.Vou_Date,
        IFNULL(m.Vou_No, '') AS Vou_No,
        CASE m.Vou_Type
            WHEN 1 THEN 'Receipt'
            WHEN 2 THEN 'Payment'
            WHEN 3 THEN 'Contra'
            WHEN 4 THEN 'Journal'
            ELSE ''
        END AS Vou_Type_Name,
        IFNULL(NULLIF(TRIM(m.Particulars), ''), IFNULL(m.Ref_Vou_No, '')) AS Particulars,
        IFNULL(CONCAT(g.Account_Code, ' - ', g.Account_Desc), 'Unallocated') AS Ledger_Name,
        CASE WHEN d.Trans_Type = 'D' THEN IFNULL(d.Vou_Amount, 0) ELSE 0 END AS Debit,
        CASE WHEN d.Trans_Type = 'C' THEN IFNULL(d.Vou_Amount, 0) ELSE 0 END AS Credit
    FROM trans_voucher_details d
    INNER JOIN trans_voucher_master m ON m.Voucher_Id = d.Voucher_Id
    LEFT JOIN mst_acct_glhead g ON g.Account_Id = d.GlHead_Id
    WHERE m.Year_Id = pYear_Id
      AND IFNULL(m.Status, 2) = 2
      AND m.Vou_Date BETWEEN pFrm_Date AND pTo_Date
    ORDER BY m.Vou_Date, m.Voucher_Id, d.VouDtls_Id;
END ;;
DELIMITER ;
/*!50003 SET sql_mode              = @saved_sql_mode */ ;
/*!50003 SET character_set_client  = @saved_cs_client */ ;
/*!50003 SET character_set_results = @saved_cs_results */ ;
/*!50003 SET collation_connection  = @saved_col_connection */ ;
/*!50003 DROP PROCEDURE IF EXISTS `USP_RPT_LEDGER_BOOK` */;
/*!50003 SET @saved_cs_client      = @@character_set_client */ ;
/*!50003 SET @saved_cs_results     = @@character_set_results */ ;
/*!50003 SET @saved_col_connection = @@collation_connection */ ;
/*!50003 SET character_set_client  = utf8mb4 */ ;
/*!50003 SET character_set_results = utf8mb4 */ ;
/*!50003 SET collation_connection  = utf8mb4_unicode_ci */ ;
/*!50003 SET @saved_sql_mode       = @@sql_mode */ ;
/*!50003 SET sql_mode              = 'ONLY_FULL_GROUP_BY,STRICT_TRANS_TABLES,NO_ZERO_IN_DATE,NO_ZERO_DATE,ERROR_FOR_DIVISION_BY_ZERO,NO_ENGINE_SUBSTITUTION' */ ;
DELIMITER ;;
CREATE DEFINER=`priority`@`%` PROCEDURE `USP_RPT_LEDGER_BOOK`(
    pYear_Id    INT,
    pFrm_Date   DATE,
    pTo_Date    DATE,
    pLedger_Id  INT
)
BEGIN
    DECLARE vOpen DECIMAL(18,2) DEFAULT 0;

    SET vOpen = IFNULL((
        SELECT SUM(CASE WHEN d.Trans_Type = 'D' THEN d.Vou_Amount ELSE -d.Vou_Amount END)
        FROM trans_voucher_details d
        INNER JOIN trans_voucher_master m ON m.Voucher_Id = d.Voucher_Id
        WHERE d.GlHead_Id = pLedger_Id
          AND m.Year_Id = pYear_Id
          AND IFNULL(m.Status, 2) = 2
          AND m.Vou_Date < pFrm_Date
    ), 0);

    DROP TEMPORARY TABLE IF EXISTS tmp_ledger;
    CREATE TEMPORARY TABLE tmp_ledger (
        Sort_No     INT,
        Voucher_Id  BIGINT,
        Vou_Date    DATE,
        Vou_No      VARCHAR(50),
        Particulars VARCHAR(250),
        Debit       DECIMAL(18,2),
        Credit      DECIMAL(18,2)
    );

    INSERT INTO tmp_ledger
    VALUES (
        0, 0, NULL, '', 'Opening Balance',
        CASE WHEN vOpen > 0 THEN vOpen ELSE 0 END,
        CASE WHEN vOpen < 0 THEN -vOpen ELSE 0 END
    );

    INSERT INTO tmp_ledger
    SELECT
        1,
        m.Voucher_Id,
        m.Vou_Date,
        IFNULL(m.Vou_No, ''),
        IFNULL(NULLIF(TRIM(m.Particulars), ''), IFNULL(m.Ref_Vou_No, '')),
        CASE WHEN d.Trans_Type = 'D' THEN IFNULL(d.Vou_Amount, 0) ELSE 0 END,
        CASE WHEN d.Trans_Type = 'C' THEN IFNULL(d.Vou_Amount, 0) ELSE 0 END
    FROM trans_voucher_details d
    INNER JOIN trans_voucher_master m ON m.Voucher_Id = d.Voucher_Id
    WHERE d.GlHead_Id = pLedger_Id
      AND m.Year_Id = pYear_Id
      AND IFNULL(m.Status, 2) = 2
      AND m.Vou_Date BETWEEN pFrm_Date AND pTo_Date;

    SELECT
        t.Vou_Date,
        t.Vou_No,
        t.Particulars,
        t.Debit,
        t.Credit,
        SUM(t.Debit - t.Credit) OVER (
            ORDER BY t.Sort_No, t.Vou_Date, t.Voucher_Id
            ROWS UNBOUNDED PRECEDING
        ) AS Balance
    FROM tmp_ledger t
    ORDER BY t.Sort_No, t.Vou_Date, t.Voucher_Id;
END ;;
DELIMITER ;
/*!50003 SET sql_mode              = @saved_sql_mode */ ;
/*!50003 SET character_set_client  = @saved_cs_client */ ;
/*!50003 SET character_set_results = @saved_cs_results */ ;
/*!50003 SET collation_connection  = @saved_col_connection */ ;
/*!50003 DROP PROCEDURE IF EXISTS `USP_RPT_MOVEMENT` */;
/*!50003 SET @saved_cs_client      = @@character_set_client */ ;
/*!50003 SET @saved_cs_results     = @@character_set_results */ ;
/*!50003 SET @saved_col_connection = @@collation_connection */ ;
/*!50003 SET character_set_client  = utf8mb4 */ ;
/*!50003 SET character_set_results = utf8mb4 */ ;
/*!50003 SET collation_connection  = utf8mb4_unicode_ci */ ;
/*!50003 SET @saved_sql_mode       = @@sql_mode */ ;
/*!50003 SET sql_mode              = 'ONLY_FULL_GROUP_BY,STRICT_TRANS_TABLES,NO_ZERO_IN_DATE,NO_ZERO_DATE,ERROR_FOR_DIVISION_BY_ZERO,NO_ENGINE_SUBSTITUTION' */ ;
DELIMITER ;;
CREATE DEFINER=`priority`@`%` PROCEDURE `USP_RPT_MOVEMENT`(
    pFrm_Date    DATE,
    pTo_Date     DATE,
    pCate_Id     INT,
    pSubCate_Id  INT
)
BEGIN
    SELECT
        p.Prod_Code,
        p.Prod_ShortNm,
        IFNULL(UDF_GET_CAT_SUB_NAME(p.Cate_Id, 1), '') AS Cate_Name,
        UDF_GET_UNIT_NAME(p.Unit_Id) AS Unit_Name,
        x.Sale_Qty,
        UDF_CAL_AVAIL_STOCK(p.Prod_Id, pTo_Date) AS Closing_Qty,
        CASE
            WHEN x.Sale_Qty = 0 THEN 'Slow'
            WHEN x.Sale_Qty >= 10 THEN 'Fast'
            ELSE 'Medium'
        END AS Movement
    FROM (
        SELECT
            s.Prod_Id,
            IFNULL(SUM(CASE WHEN s.Stock_Type IN (3, 5) THEN s.Quantity ELSE 0 END), 0) AS Sale_Qty
        FROM trns_stockinout s
        WHERE s.InOut_Date BETWEEN pFrm_Date AND pTo_Date
        GROUP BY s.Prod_Id
    ) x
    JOIN mst_product p ON p.Prod_Id = x.Prod_Id
    WHERE (pCate_Id = 0 OR p.Cate_Id = pCate_Id)
      AND (pSubCate_Id = 0 OR p.SubCate_Id = pSubCate_Id)
      AND (x.Sale_Qty <> 0 OR UDF_CAL_AVAIL_STOCK(p.Prod_Id, pTo_Date) <> 0)
    ORDER BY x.Sale_Qty DESC, p.Prod_ShortNm;
END ;;
DELIMITER ;
/*!50003 SET sql_mode              = @saved_sql_mode */ ;
/*!50003 SET character_set_client  = @saved_cs_client */ ;
/*!50003 SET character_set_results = @saved_cs_results */ ;
/*!50003 SET collation_connection  = @saved_col_connection */ ;
/*!50003 DROP PROCEDURE IF EXISTS `USP_RPT_PARTY_LEDGER` */;
/*!50003 SET @saved_cs_client      = @@character_set_client */ ;
/*!50003 SET @saved_cs_results     = @@character_set_results */ ;
/*!50003 SET @saved_col_connection = @@collation_connection */ ;
/*!50003 SET character_set_client  = utf8mb4 */ ;
/*!50003 SET character_set_results = utf8mb4 */ ;
/*!50003 SET collation_connection  = utf8mb4_unicode_ci */ ;
/*!50003 SET @saved_sql_mode       = @@sql_mode */ ;
/*!50003 SET sql_mode              = 'ONLY_FULL_GROUP_BY,STRICT_TRANS_TABLES,NO_ZERO_IN_DATE,NO_ZERO_DATE,ERROR_FOR_DIVISION_BY_ZERO,NO_ENGINE_SUBSTITUTION' */ ;
DELIMITER ;;
CREATE DEFINER=`priority`@`%` PROCEDURE `USP_RPT_PARTY_LEDGER`(
    pFrm_Date    DATE,
    pTo_Date     DATE,
    pBranch_Id   INT,
    pParty_Type  INT,
    pParty_Id    INT
)
BEGIN
    DECLARE v_done INT DEFAULT 0;
    DECLARE v_party_id INT;
    DECLARE v_party_code VARCHAR(25);
    DECLARE v_party_name VARCHAR(250);
    DECLARE v_balance DECIMAL(18,2);
    DECLARE v_opening DECIMAL(18,2);
    DECLARE v_affects TINYINT;
    DECLARE v_trans_id BIGINT;
    DECLARE v_trans_date DATE;
    DECLARE v_trans_type CHAR(1);
    DECLARE v_amount DECIMAL(18,2);
    DECLARE v_trans_mode TINYINT;
    DECLARE v_trading_id BIGINT;
    DECLARE v_remarks VARCHAR(150);
    DECLARE v_mode_name VARCHAR(50);
    DECLARE v_debit DECIMAL(18,2);
    DECLARE v_credit DECIMAL(18,2);
    DECLARE v_row_order INT;
    DECLARE v_balance_label VARCHAR(30);

    DECLARE cur_party CURSOR FOR
        SELECT Party_Id, Party_Code, Party_Name
        FROM mst_party
        WHERE Party_Type = pParty_Type
          AND (pBranch_Id = 0 OR Branch_Id = pBranch_Id)
          AND (pParty_Id = 0 OR Party_Id = pParty_Id)
        ORDER BY Party_Name;

    DECLARE cur_trans CURSOR FOR
        SELECT Id, Trans_Date, Trans_Type, Amount, Trans_Mode, Trading_Id, Remarks
        FROM trn_party_trans
        WHERE Party_Id = v_party_id
          AND Trans_Date BETWEEN pFrm_Date AND pTo_Date
        ORDER BY Trans_Date, Id;

    DECLARE CONTINUE HANDLER FOR NOT FOUND SET v_done = 1;

    DROP TEMPORARY TABLE IF EXISTS tmp_party_ledger;
    CREATE TEMPORARY TABLE tmp_party_ledger (
        Party_Id INT,
        Party_Code VARCHAR(25),
        Party_Name VARCHAR(250),
        Row_Kind TINYINT,
        Row_Order INT,
        Trans_Date DATE,
        Particulars VARCHAR(200),
        Mode_Name VARCHAR(50),
        Debit_Amt DECIMAL(18,2),
        Credit_Amt DECIMAL(18,2),
        Balance_Amt DECIMAL(18,2),
        Balance_Label VARCHAR(30)
    );

    SET v_done = 0;
    OPEN cur_party;
    party_loop: LOOP
        FETCH cur_party INTO v_party_id, v_party_code, v_party_name;
        IF v_done THEN
            LEAVE party_loop;
        END IF;

        SET v_opening = UDF_CAL_PARTY_CLOSING(v_party_id, DATE_SUB(pFrm_Date, INTERVAL 1 DAY));
        SET v_balance = v_opening;
        SET v_row_order = 1;

        IF pParty_Type = 2 THEN
            SET v_balance_label = CONCAT(FORMAT(ABS(v_opening), 2), ' ', IF(v_opening < 0, 'Cr', 'Dr'));
        ELSE
            SET v_balance_label = CONCAT(FORMAT(ABS(v_opening), 2), ' ', IF(v_opening < 0, 'Dr', 'Cr'));
        END IF;

        INSERT INTO tmp_party_ledger
        VALUES (v_party_id, v_party_code, v_party_name, 1, v_row_order, NULL,
                'By Opening Balance', '', 0, 0, v_opening, v_balance_label);

        SET v_done = 0;
        OPEN cur_trans;
        trans_loop: LOOP
            FETCH cur_trans INTO v_trans_id, v_trans_date, v_trans_type, v_amount, v_trans_mode, v_trading_id, v_remarks;
            IF v_done THEN
                LEAVE trans_loop;
            END IF;

            SET v_mode_name = IFNULL((
                SELECT Value_Name FROM smartinventory_core.mst_options
                WHERE Option_Id = 15 AND Value_Id = v_trans_mode
            ), '');

            SET v_debit = IF(v_trans_type = 'D', IFNULL(v_amount, 0), 0);
            SET v_credit = IF(v_trans_type = 'C', IFNULL(v_amount, 0), 0);
            SET v_affects = IF(IFNULL(v_trading_id, 0) = 0 OR v_trans_mode = 3, 1, 0);

            IF v_affects = 1 THEN
                IF pParty_Type = 2 THEN
                    SET v_balance = v_balance + v_debit - v_credit;
                ELSE
                    SET v_balance = v_balance + v_credit - v_debit;
                END IF;
            END IF;

            SET v_row_order = v_row_order + 1;

            IF IFNULL(v_trading_id, 0) > 0 AND IFNULL(v_remarks, '') <> '' THEN
                SET v_remarks = CONCAT('By ', v_remarks);
            END IF;

            IF pParty_Type = 2 THEN
                SET v_balance_label = CONCAT(FORMAT(ABS(v_balance), 2), ' ', IF(v_balance < 0, 'Cr', 'Dr'));
            ELSE
                SET v_balance_label = CONCAT(FORMAT(ABS(v_balance), 2), ' ', IF(v_balance < 0, 'Dr', 'Cr'));
            END IF;

            INSERT INTO tmp_party_ledger
            VALUES (v_party_id, v_party_code, v_party_name, 2, v_row_order, v_trans_date,
                    IFNULL(v_remarks, ''), v_mode_name, v_debit, v_credit, v_balance, v_balance_label);
        END LOOP;
        CLOSE cur_trans;
        SET v_done = 0;
    END LOOP;
    CLOSE cur_party;

    SELECT Party_Id, Party_Code, Party_Name, Row_Kind, Row_Order, Trans_Date, Particulars,
           Mode_Name, Debit_Amt, Credit_Amt, Balance_Amt, Balance_Label
    FROM tmp_party_ledger
    ORDER BY Party_Name, Row_Order;

    DROP TEMPORARY TABLE IF EXISTS tmp_party_ledger;
END ;;
DELIMITER ;
/*!50003 SET sql_mode              = @saved_sql_mode */ ;
/*!50003 SET character_set_client  = @saved_cs_client */ ;
/*!50003 SET character_set_results = @saved_cs_results */ ;
/*!50003 SET collation_connection  = @saved_col_connection */ ;
/*!50003 DROP PROCEDURE IF EXISTS `USP_RPT_PL_APPROPRIATION` */;
/*!50003 SET @saved_cs_client      = @@character_set_client */ ;
/*!50003 SET @saved_cs_results     = @@character_set_results */ ;
/*!50003 SET @saved_col_connection = @@collation_connection */ ;
/*!50003 SET character_set_client  = utf8mb4 */ ;
/*!50003 SET character_set_results = utf8mb4 */ ;
/*!50003 SET collation_connection  = utf8mb4_unicode_ci */ ;
/*!50003 SET @saved_sql_mode       = @@sql_mode */ ;
/*!50003 SET sql_mode              = 'ONLY_FULL_GROUP_BY,STRICT_TRANS_TABLES,NO_ZERO_IN_DATE,NO_ZERO_DATE,ERROR_FOR_DIVISION_BY_ZERO,NO_ENGINE_SUBSTITUTION' */ ;
DELIMITER ;;
CREATE DEFINER=`priority`@`%` PROCEDURE `USP_RPT_PL_APPROPRIATION`(
    pYear_Id INT,
    pDate    DATE
)
BEGIN
    DECLARE vNP DECIMAL(18,2) DEFAULT 0;

    DROP TEMPORARY TABLE IF EXISTS tmp_gl_bal;
    CREATE TEMPORARY TABLE tmp_gl_bal (
        Account_Id   SMALLINT,
        Account_Code VARCHAR(6),
        Account_Desc VARCHAR(200),
        Cate_Id      TINYINT,
        Cate_Desc    VARCHAR(25),
        Bal          DECIMAL(18,2)
    );

    INSERT INTO tmp_gl_bal (Account_Id, Account_Code, Account_Desc, Cate_Id, Cate_Desc, Bal)
    SELECT
        g.Account_Id,
        g.Account_Code,
        g.Account_Desc,
        c.Cate_Id,
        c.Cate_Desc,
        IFNULL((
            SELECT SUM(CASE WHEN d.Trans_Type = 'D' THEN d.Vou_Amount ELSE -d.Vou_Amount END)
            FROM trans_voucher_details d
            INNER JOIN trans_voucher_master m ON m.Voucher_Id = d.Voucher_Id
            WHERE d.GlHead_Id = g.Account_Id
              AND m.Year_Id = pYear_Id
              AND IFNULL(m.Status, 2) = 2
              AND m.Vou_Date <= pDate
        ), 0)
    FROM mst_acct_glhead g
    INNER JOIN mst_acct_mainhd h ON h.MainHd_Id = g.MainHd_Id
    INNER JOIN mst_acct_category c ON c.Cate_Id = h.Cate_Id;

    SELECT IFNULL(-SUM(Bal), 0) INTO vNP
    FROM tmp_gl_bal
    WHERE Cate_Id IN (3, 4, 5, 6);

    DROP TEMPORARY TABLE IF EXISTS tmp_app;
    CREATE TEMPORARY TABLE tmp_app (
        Sort_No     INT,
        Particulars VARCHAR(250),
        Debit       DECIMAL(18,2),
        Credit      DECIMAL(18,2),
        Row_Kind    TINYINT
    );

    INSERT INTO tmp_app VALUES (10, 'P/L Appropriation Account', NULL, NULL, 1);

    IF vNP >= 0 THEN
        INSERT INTO tmp_app VALUES (20, 'By Net Profit b/d', NULL, vNP, 3);
        INSERT INTO tmp_app VALUES (30, 'To Balance transferred to Balance Sheet', vNP, NULL, 3);
        INSERT INTO tmp_app VALUES (40, 'Total', vNP, vNP, 2);
    ELSE
        INSERT INTO tmp_app VALUES (20, 'To Net Loss b/d', -vNP, NULL, 3);
        INSERT INTO tmp_app VALUES (30, 'By Balance transferred to Balance Sheet', NULL, -vNP, 3);
        INSERT INTO tmp_app VALUES (40, 'Total', -vNP, -vNP, 2);
    END IF;

    SELECT Particulars, Debit, Credit, Row_Kind
    FROM tmp_app
    ORDER BY Sort_No;
END ;;
DELIMITER ;
/*!50003 SET sql_mode              = @saved_sql_mode */ ;
/*!50003 SET character_set_client  = @saved_cs_client */ ;
/*!50003 SET character_set_results = @saved_cs_results */ ;
/*!50003 SET collation_connection  = @saved_col_connection */ ;
/*!50003 DROP PROCEDURE IF EXISTS `USP_RPT_PURCHASE_REGISTER` */;
/*!50003 SET @saved_cs_client      = @@character_set_client */ ;
/*!50003 SET @saved_cs_results     = @@character_set_results */ ;
/*!50003 SET @saved_col_connection = @@collation_connection */ ;
/*!50003 SET character_set_client  = utf8mb4 */ ;
/*!50003 SET character_set_results = utf8mb4 */ ;
/*!50003 SET collation_connection  = utf8mb4_0900_ai_ci */ ;
/*!50003 SET @saved_sql_mode       = @@sql_mode */ ;
/*!50003 SET sql_mode              = 'ONLY_FULL_GROUP_BY,STRICT_TRANS_TABLES,NO_ZERO_IN_DATE,NO_ZERO_DATE,ERROR_FOR_DIVISION_BY_ZERO,NO_ENGINE_SUBSTITUTION' */ ;
DELIMITER ;;
CREATE DEFINER=`priority`@`%` PROCEDURE `USP_RPT_PURCHASE_REGISTER`(
    pFrm_Date   DATE,
    pTo_Date    DATE,
    pParty_Id   INT,
    pBranch_Id  INT,
    pGst_Filter INT  
)
BEGIN
    SELECT
        m.Trading_Id,
        m.Invoice_No,
        m.Invoice_Date,
        IFNULL(p.Party_Name, '')  AS Party_Name,
        IFNULL(p.GstIn, '')       AS GstIn,
        IFNULL(m.Invoice_Amt, 0)  AS Invoice_Amt,
        IFNULL(m.Disc_Amount, 0)  AS Disc_Amount,
        IFNULL(m.Taxable_Amt, 0)  AS Taxable_Amt,
        IFNULL(m.GST_Amt, 0)      AS GST_Amt,
        IFNULL(m.Net_Amt, 0)      AS Net_Amt
    FROM trans_trading m
    LEFT JOIN mst_party p ON p.Party_Id = m.Party_Id
    WHERE m.Tranding_Type = 2
      AND IFNULL(m.Status_Cd, 1) = 1
      AND m.Invoice_Date BETWEEN pFrm_Date AND pTo_Date
      AND (pParty_Id = 0 OR m.Party_Id = pParty_Id)
      AND (pBranch_Id = 0 OR m.Branch_Id = pBranch_Id)
      AND (
            pGst_Filter = 0
            OR (pGst_Filter = 1 AND IFNULL(TRIM(p.GstIn), '') <> '')
            OR (pGst_Filter = 2 AND IFNULL(TRIM(p.GstIn), '') = '')
          )
    ORDER BY m.Invoice_Date, m.Trading_Id;
END ;;
DELIMITER ;
/*!50003 SET sql_mode              = @saved_sql_mode */ ;
/*!50003 SET character_set_client  = @saved_cs_client */ ;
/*!50003 SET character_set_results = @saved_cs_results */ ;
/*!50003 SET collation_connection  = @saved_col_connection */ ;
/*!50003 DROP PROCEDURE IF EXISTS `USP_RPT_RECEIPT_PAYMENT` */;
/*!50003 SET @saved_cs_client      = @@character_set_client */ ;
/*!50003 SET @saved_cs_results     = @@character_set_results */ ;
/*!50003 SET @saved_col_connection = @@collation_connection */ ;
/*!50003 SET character_set_client  = utf8mb4 */ ;
/*!50003 SET character_set_results = utf8mb4 */ ;
/*!50003 SET collation_connection  = utf8mb4_unicode_ci */ ;
/*!50003 SET @saved_sql_mode       = @@sql_mode */ ;
/*!50003 SET sql_mode              = 'ONLY_FULL_GROUP_BY,STRICT_TRANS_TABLES,NO_ZERO_IN_DATE,NO_ZERO_DATE,ERROR_FOR_DIVISION_BY_ZERO,NO_ENGINE_SUBSTITUTION' */ ;
DELIMITER ;;
CREATE DEFINER=`priority`@`%` PROCEDURE `USP_RPT_RECEIPT_PAYMENT`(
    pYear_Id   INT,
    pFrm_Date  DATE,
    pTo_Date   DATE
)
BEGIN
    DECLARE vOpen DECIMAL(18,2) DEFAULT 0;
    DECLARE vClose DECIMAL(18,2) DEFAULT 0;
    DECLARE vRec DECIMAL(18,2) DEFAULT 0;
    DECLARE vPay DECIMAL(18,2) DEFAULT 0;

    DROP TEMPORARY TABLE IF EXISTS tmp_cb;
    CREATE TEMPORARY TABLE tmp_cb (
        Account_Id SMALLINT PRIMARY KEY
    );

    INSERT IGNORE INTO tmp_cb
    SELECT g.Account_Id FROM mst_acct_glhead g WHERE g.Account_For IN ('C', 'B');

    INSERT IGNORE INTO tmp_cb
    SELECT d.Cash_Ledg FROM mst_default_ledger d WHERE d.Cash_Ledg IS NOT NULL;

    INSERT IGNORE INTO tmp_cb
    SELECT d.Bank_Ledg FROM mst_default_ledger d WHERE d.Bank_Ledg IS NOT NULL;

    DROP TEMPORARY TABLE IF EXISTS tmp_cb2;
    CREATE TEMPORARY TABLE tmp_cb2 AS SELECT Account_Id FROM tmp_cb;

    SET vOpen = IFNULL((
        SELECT SUM(CASE WHEN d.Trans_Type = 'D' THEN d.Vou_Amount ELSE -d.Vou_Amount END)
        FROM trans_voucher_details d
        INNER JOIN trans_voucher_master m ON m.Voucher_Id = d.Voucher_Id
        INNER JOIN tmp_cb c ON c.Account_Id = d.GlHead_Id
        WHERE m.Year_Id = pYear_Id
          AND IFNULL(m.Status, 2) = 2
          AND m.Vou_Date < pFrm_Date
    ), 0);

    SET vClose = vOpen
    + IFNULL((
        SELECT SUM(CASE WHEN d.Trans_Type = 'D' THEN d.Vou_Amount ELSE -d.Vou_Amount END)
        FROM trans_voucher_details d
        INNER JOIN trans_voucher_master m ON m.Voucher_Id = d.Voucher_Id
        INNER JOIN tmp_cb c ON c.Account_Id = d.GlHead_Id
        WHERE m.Year_Id = pYear_Id
          AND IFNULL(m.Status, 2) = 2
          AND m.Vou_Date BETWEEN pFrm_Date AND pTo_Date
    ), 0);

    DROP TEMPORARY TABLE IF EXISTS tmp_rp;
    CREATE TEMPORARY TABLE tmp_rp (
        Sort_No     INT,
        Particulars VARCHAR(250),
        Receipt     DECIMAL(18,2),
        Payment     DECIMAL(18,2),
        Row_Kind    TINYINT
    );

    INSERT INTO tmp_rp VALUES (10, 'Receipts', NULL, NULL, 1);
    INSERT INTO tmp_rp VALUES (
        20, 'Opening Cash & Bank',
        CASE WHEN vOpen > 0 THEN vOpen ELSE 0 END,
        CASE WHEN vOpen < 0 THEN -vOpen ELSE NULL END,
        0
    );

    INSERT INTO tmp_rp (Sort_No, Particulars, Receipt, Payment, Row_Kind)
    SELECT
        30,
        CONCAT('To ', IFNULL(g.Account_Desc, 'Unallocated')),
        SUM(cr.Vou_Amount),
        NULL,
        0
    FROM trans_voucher_details cr
    INNER JOIN trans_voucher_master m ON m.Voucher_Id = cr.Voucher_Id
    INNER JOIN trans_voucher_details cash
            ON cash.Voucher_Id = m.Voucher_Id
           AND cash.Trans_Type = 'D'
    INNER JOIN tmp_cb cb1 ON cb1.Account_Id = cash.GlHead_Id
    LEFT JOIN tmp_cb2 cb2 ON cb2.Account_Id = cr.GlHead_Id
    LEFT JOIN mst_acct_glhead g ON g.Account_Id = cr.GlHead_Id
    WHERE cr.Trans_Type = 'C'
      AND cb2.Account_Id IS NULL
      AND m.Year_Id = pYear_Id
      AND IFNULL(m.Status, 2) = 2
      AND m.Vou_Date BETWEEN pFrm_Date AND pTo_Date
    GROUP BY cr.GlHead_Id, g.Account_Desc
    HAVING SUM(cr.Vou_Amount) <> 0;

    INSERT INTO tmp_rp VALUES (40, 'Payments', NULL, NULL, 1);

    INSERT INTO tmp_rp (Sort_No, Particulars, Receipt, Payment, Row_Kind)
    SELECT
        50,
        CONCAT('By ', IFNULL(g.Account_Desc, 'Unallocated')),
        NULL,
        SUM(dr.Vou_Amount),
        0
    FROM trans_voucher_details dr
    INNER JOIN trans_voucher_master m ON m.Voucher_Id = dr.Voucher_Id
    INNER JOIN trans_voucher_details cash
            ON cash.Voucher_Id = m.Voucher_Id
           AND cash.Trans_Type = 'C'
    INNER JOIN tmp_cb cb1 ON cb1.Account_Id = cash.GlHead_Id
    LEFT JOIN tmp_cb2 cb2 ON cb2.Account_Id = dr.GlHead_Id
    LEFT JOIN mst_acct_glhead g ON g.Account_Id = dr.GlHead_Id
    WHERE dr.Trans_Type = 'D'
      AND cb2.Account_Id IS NULL
      AND m.Year_Id = pYear_Id
      AND IFNULL(m.Status, 2) = 2
      AND m.Vou_Date BETWEEN pFrm_Date AND pTo_Date
    GROUP BY dr.GlHead_Id, g.Account_Desc
    HAVING SUM(dr.Vou_Amount) <> 0;

    INSERT INTO tmp_rp VALUES (
        60, 'Closing Cash & Bank',
        CASE WHEN vClose < 0 THEN -vClose ELSE NULL END,
        CASE WHEN vClose > 0 THEN vClose ELSE 0 END,
        0
    );

    SELECT IFNULL(SUM(Receipt), 0), IFNULL(SUM(Payment), 0)
      INTO vRec, vPay
    FROM tmp_rp
    WHERE Row_Kind = 0;

    INSERT INTO tmp_rp VALUES (70, 'Total', vRec, vPay, 2);

    SELECT Particulars, Receipt, Payment, Row_Kind
    FROM tmp_rp
    ORDER BY Sort_No, Particulars;
END ;;
DELIMITER ;
/*!50003 SET sql_mode              = @saved_sql_mode */ ;
/*!50003 SET character_set_client  = @saved_cs_client */ ;
/*!50003 SET character_set_results = @saved_cs_results */ ;
/*!50003 SET collation_connection  = @saved_col_connection */ ;
/*!50003 DROP PROCEDURE IF EXISTS `USP_RPT_REORDER` */;
/*!50003 SET @saved_cs_client      = @@character_set_client */ ;
/*!50003 SET @saved_cs_results     = @@character_set_results */ ;
/*!50003 SET @saved_col_connection = @@collation_connection */ ;
/*!50003 SET character_set_client  = utf8mb4 */ ;
/*!50003 SET character_set_results = utf8mb4 */ ;
/*!50003 SET collation_connection  = utf8mb4_unicode_ci */ ;
/*!50003 SET @saved_sql_mode       = @@sql_mode */ ;
/*!50003 SET sql_mode              = 'ONLY_FULL_GROUP_BY,STRICT_TRANS_TABLES,NO_ZERO_IN_DATE,NO_ZERO_DATE,ERROR_FOR_DIVISION_BY_ZERO,NO_ENGINE_SUBSTITUTION' */ ;
DELIMITER ;;
CREATE DEFINER=`priority`@`%` PROCEDURE `USP_RPT_REORDER`(
    pDate DATE
)
BEGIN
    SELECT
        p.Prod_Code,
        p.Prod_ShortNm,
        UDF_GET_UNIT_NAME(p.Unit_Id) AS Unit_Name,
        UDF_CAL_AVAIL_STOCK(p.Prod_Id, pDate) AS On_Hand,
        IFNULL(p.ReOrder_Qty, 0) AS ReOrder_Qty,
        (IFNULL(p.ReOrder_Qty, 0) - UDF_CAL_AVAIL_STOCK(p.Prod_Id, pDate)) AS Short_Qty,
        CASE
            WHEN UDF_CAL_AVAIL_STOCK(p.Prod_Id, pDate) <= 0 THEN 'Critical'
            WHEN UDF_CAL_AVAIL_STOCK(p.Prod_Id, pDate) <= (IFNULL(p.ReOrder_Qty, 0) * 0.5) THEN 'Critical'
            ELSE 'Low'
        END AS Alert_Status
    FROM mst_product p
    WHERE p.Is_Active = 1
      AND IFNULL(p.ReOrder_Qty, 0) > 0
      AND UDF_CAL_AVAIL_STOCK(p.Prod_Id, pDate) < IFNULL(p.ReOrder_Qty, 0)
    ORDER BY (UDF_CAL_AVAIL_STOCK(p.Prod_Id, pDate) / p.ReOrder_Qty) ASC, p.Prod_ShortNm;
END ;;
DELIMITER ;
/*!50003 SET sql_mode              = @saved_sql_mode */ ;
/*!50003 SET character_set_client  = @saved_cs_client */ ;
/*!50003 SET character_set_results = @saved_cs_results */ ;
/*!50003 SET collation_connection  = @saved_col_connection */ ;
/*!50003 DROP PROCEDURE IF EXISTS `USP_RPT_RETURN_REGISTER` */;
/*!50003 SET @saved_cs_client      = @@character_set_client */ ;
/*!50003 SET @saved_cs_results     = @@character_set_results */ ;
/*!50003 SET @saved_col_connection = @@collation_connection */ ;
/*!50003 SET character_set_client  = utf8mb4 */ ;
/*!50003 SET character_set_results = utf8mb4 */ ;
/*!50003 SET collation_connection  = utf8mb4_unicode_ci */ ;
/*!50003 SET @saved_sql_mode       = @@sql_mode */ ;
/*!50003 SET sql_mode              = 'ONLY_FULL_GROUP_BY,STRICT_TRANS_TABLES,NO_ZERO_IN_DATE,NO_ZERO_DATE,ERROR_FOR_DIVISION_BY_ZERO,NO_ENGINE_SUBSTITUTION' */ ;
DELIMITER ;;
CREATE DEFINER=`priority`@`%` PROCEDURE `USP_RPT_RETURN_REGISTER`(
    pFrm_Date   DATE,
    pTo_Date    DATE,
    pMode       INT,
    pBranch_Id  INT
)
BEGIN
    SELECT
        m.Invoice_No,
        m.Invoice_Date,
        CASE m.Tranding_Type
            WHEN 4 THEN 'Purchase Return'
            WHEN 5 THEN 'Sales Return'
            ELSE 'Return'
        END AS Return_Type,
        IFNULL(p.Party_Name, '') AS Party_Name,
        IFNULL(a.Agent_Name, '') AS Agent_Name,
        IFNULL(m.Taxable_Amt, 0) AS Taxable_Amt,
        IFNULL(m.GST_Amt, 0) AS GST_Amt,
        IFNULL(m.Net_Amt, 0) AS Net_Amt
    FROM trans_trading m
    LEFT JOIN mst_party p ON p.Party_Id = m.Party_Id
    LEFT JOIN mst_agent a ON a.Agent_Id = m.Agent_Id
    WHERE m.Tranding_Type IN (4, 5)
      AND IFNULL(m.Status_Cd, 1) = 1
      AND m.Invoice_Date BETWEEN pFrm_Date AND pTo_Date
      AND (pBranch_Id = 0 OR m.Branch_Id = pBranch_Id)
      AND (
            pMode = 0
         OR (pMode = 1 AND m.Tranding_Type = 4)
         OR (pMode = 2 AND m.Tranding_Type = 5)
      )
    ORDER BY m.Invoice_Date, m.Trading_Id;
END ;;
DELIMITER ;
/*!50003 SET sql_mode              = @saved_sql_mode */ ;
/*!50003 SET character_set_client  = @saved_cs_client */ ;
/*!50003 SET character_set_results = @saved_cs_results */ ;
/*!50003 SET collation_connection  = @saved_col_connection */ ;
/*!50003 DROP PROCEDURE IF EXISTS `USP_RPT_SALES_REGISTER` */;
/*!50003 SET @saved_cs_client      = @@character_set_client */ ;
/*!50003 SET @saved_cs_results     = @@character_set_results */ ;
/*!50003 SET @saved_col_connection = @@collation_connection */ ;
/*!50003 SET character_set_client  = utf8mb4 */ ;
/*!50003 SET character_set_results = utf8mb4 */ ;
/*!50003 SET collation_connection  = utf8mb4_0900_ai_ci */ ;
/*!50003 SET @saved_sql_mode       = @@sql_mode */ ;
/*!50003 SET sql_mode              = 'ONLY_FULL_GROUP_BY,STRICT_TRANS_TABLES,NO_ZERO_IN_DATE,NO_ZERO_DATE,ERROR_FOR_DIVISION_BY_ZERO,NO_ENGINE_SUBSTITUTION' */ ;
DELIMITER ;;
CREATE DEFINER=`priority`@`%` PROCEDURE `USP_RPT_SALES_REGISTER`(
    pFrm_Date   DATE,
    pTo_Date    DATE,
    pMode       INT,
    pBranch_Id  INT,
    pCate_Id    INT
)
BEGIN
    SELECT DISTINCT
        m.Trading_Id,
        m.Invoice_No,
        m.Invoice_Date,
        CASE WHEN m.Agent_Id IS NULL THEN 'Counter' ELSE 'Agent' END AS Sale_Type,
        IFNULL(p.Party_Name, '')  AS Party_Name,
        IFNULL(a.Agent_Name, '')  AS Agent_Name,
        IFNULL(m.Taxable_Amt, 0)  AS Taxable_Amt,
        IFNULL(m.GST_Amt, 0)      AS GST_Amt,
        IFNULL(m.Net_Amt, 0)      AS Net_Amt
    FROM trans_trading m
    LEFT JOIN mst_party p ON p.Party_Id = m.Party_Id
    LEFT JOIN mst_agent a ON a.Agent_Id = m.Agent_Id
    LEFT JOIN trans_trading_products tp ON tp.Trading_Id = m.Trading_Id
    LEFT JOIN mst_product pr ON pr.Prod_Id = tp.Prod_Id
    WHERE m.Tranding_Type = 3
      AND IFNULL(m.Status_Cd, 1) = 1
      AND m.Invoice_Date BETWEEN pFrm_Date AND pTo_Date
      AND (pBranch_Id = 0 OR m.Branch_Id = pBranch_Id)
      AND (
            pMode = 0
         OR (pMode = 1 AND m.Agent_Id IS NULL)
         OR (pMode = 2 AND m.Agent_Id IS NOT NULL)
      )
      AND (pCate_Id = 0 OR pr.Cate_Id = pCate_Id)
    ORDER BY m.Invoice_Date, m.Trading_Id;
END ;;
DELIMITER ;
/*!50003 SET sql_mode              = @saved_sql_mode */ ;
/*!50003 SET character_set_client  = @saved_cs_client */ ;
/*!50003 SET character_set_results = @saved_cs_results */ ;
/*!50003 SET collation_connection  = @saved_col_connection */ ;
/*!50003 DROP PROCEDURE IF EXISTS `USP_RPT_SHARE_REGISTER` */;
/*!50003 SET @saved_cs_client      = @@character_set_client */ ;
/*!50003 SET @saved_cs_results     = @@character_set_results */ ;
/*!50003 SET @saved_col_connection = @@collation_connection */ ;
/*!50003 SET character_set_client  = utf8mb4 */ ;
/*!50003 SET character_set_results = utf8mb4 */ ;
/*!50003 SET collation_connection  = utf8mb4_unicode_ci */ ;
/*!50003 SET @saved_sql_mode       = @@sql_mode */ ;
/*!50003 SET sql_mode              = 'ONLY_FULL_GROUP_BY,STRICT_TRANS_TABLES,NO_ZERO_IN_DATE,NO_ZERO_DATE,ERROR_FOR_DIVISION_BY_ZERO,NO_ENGINE_SUBSTITUTION' */ ;
DELIMITER ;;
CREATE DEFINER=`priority`@`%` PROCEDURE `USP_RPT_SHARE_REGISTER`(
    pFrm_Date DATE,
    pTo_Date  DATE
)
BEGIN
    SELECT
        m.Share_No,
        m.Admission_Date,
        m.Member_Name,
        IFNULL(m.Guardian_Name, '') AS Guardian_Name,
        IFNULL(m.Contact_No, '') AS Contact_No,
        IFNULL(m.Adm_Fees, 0) AS Adm_Fees,
        IFNULL(m.Rate_Per_Share, 0) AS Rate_Per_Share,
        IFNULL(m.Share_Amount, 0) AS Share_Amount,
        IFNULL(m.Total_Amount, 0) AS Total_Amount,
        CASE WHEN m.Status_Cd = 1 THEN 'Active' ELSE 'Inactive' END AS Status_Name
    FROM mst_member m
    WHERE (pFrm_Date IS NULL OR m.Admission_Date BETWEEN pFrm_Date AND pTo_Date)
    ORDER BY m.Admission_Date, m.Member_Id;
END ;;
DELIMITER ;
/*!50003 SET sql_mode              = @saved_sql_mode */ ;
/*!50003 SET character_set_client  = @saved_cs_client */ ;
/*!50003 SET character_set_results = @saved_cs_results */ ;
/*!50003 SET collation_connection  = @saved_col_connection */ ;
/*!50003 DROP PROCEDURE IF EXISTS `USP_RPT_STOCK_STATEMENT` */;
/*!50003 SET @saved_cs_client      = @@character_set_client */ ;
/*!50003 SET @saved_cs_results     = @@character_set_results */ ;
/*!50003 SET @saved_col_connection = @@collation_connection */ ;
/*!50003 SET character_set_client  = utf8mb4 */ ;
/*!50003 SET character_set_results = utf8mb4 */ ;
/*!50003 SET collation_connection  = utf8mb4_unicode_ci */ ;
/*!50003 SET @saved_sql_mode       = @@sql_mode */ ;
/*!50003 SET sql_mode              = 'ONLY_FULL_GROUP_BY,STRICT_TRANS_TABLES,NO_ZERO_IN_DATE,NO_ZERO_DATE,ERROR_FOR_DIVISION_BY_ZERO,NO_ENGINE_SUBSTITUTION' */ ;
DELIMITER ;;
CREATE DEFINER=`priority`@`%` PROCEDURE `USP_RPT_STOCK_STATEMENT`(
    pFrm_Date    DATE,
    pTo_Date     DATE,
    pCate_Id     INT,
    pSubCate_Id  INT
)
BEGIN
    SELECT
        p.Prod_Code,
        p.Prod_ShortNm,
        IFNULL(UDF_GET_CAT_SUB_NAME(p.Cate_Id, 1), '') AS Cate_Name,
        UDF_GET_UNIT_NAME(p.Unit_Id) AS Unit_Name,
        x.Opening_Qty,
        x.In_Qty,
        x.Out_Qty,
        (x.Opening_Qty + x.In_Qty - x.Out_Qty) AS Closing_Qty
    FROM (
        SELECT
            s.Prod_Id,
            IFNULL(SUM(CASE
                WHEN s.InOut_Date < pFrm_Date THEN
                    CASE
                        WHEN s.Stock_Type IN (1, 2, 6) THEN s.Quantity
                        WHEN s.Stock_Type = 8 AND NOT EXISTS (
                            SELECT 1 FROM trans_agent_stock a WHERE a.TrnsStock_Id = s.Stock_Id
                        ) THEN s.Quantity
                        WHEN s.Stock_Type IN (3, 4, 7) THEN -s.Quantity
                        ELSE 0
                    END
                ELSE 0
            END), 0) AS Opening_Qty,
            IFNULL(SUM(CASE
                WHEN s.InOut_Date BETWEEN pFrm_Date AND pTo_Date THEN
                    CASE
                        WHEN s.Stock_Type IN (1, 2, 6) THEN s.Quantity
                        WHEN s.Stock_Type = 8 AND NOT EXISTS (
                            SELECT 1 FROM trans_agent_stock a WHERE a.TrnsStock_Id = s.Stock_Id
                        ) THEN s.Quantity
                        ELSE 0
                    END
                ELSE 0
            END), 0) AS In_Qty,
            IFNULL(SUM(CASE
                WHEN s.InOut_Date BETWEEN pFrm_Date AND pTo_Date
                     AND s.Stock_Type IN (3, 4, 7) THEN s.Quantity
                ELSE 0
            END), 0) AS Out_Qty
        FROM trns_stockinout s
        WHERE s.InOut_Date <= pTo_Date
        GROUP BY s.Prod_Id
    ) x
    JOIN mst_product p ON p.Prod_Id = x.Prod_Id
    WHERE (x.Opening_Qty <> 0 OR x.In_Qty <> 0 OR x.Out_Qty <> 0)
      AND (pCate_Id = 0 OR p.Cate_Id = pCate_Id)
      AND (pSubCate_Id = 0 OR p.SubCate_Id = pSubCate_Id)
    ORDER BY p.Prod_ShortNm;
END ;;
DELIMITER ;
/*!50003 SET sql_mode              = @saved_sql_mode */ ;
/*!50003 SET character_set_client  = @saved_cs_client */ ;
/*!50003 SET character_set_results = @saved_cs_results */ ;
/*!50003 SET collation_connection  = @saved_col_connection */ ;
/*!50003 DROP PROCEDURE IF EXISTS `USP_RPT_SUPPLIER_REGISTER` */;
/*!50003 SET @saved_cs_client      = @@character_set_client */ ;
/*!50003 SET @saved_cs_results     = @@character_set_results */ ;
/*!50003 SET @saved_col_connection = @@collation_connection */ ;
/*!50003 SET character_set_client  = utf8mb4 */ ;
/*!50003 SET character_set_results = utf8mb4 */ ;
/*!50003 SET collation_connection  = utf8mb4_unicode_ci */ ;
/*!50003 SET @saved_sql_mode       = @@sql_mode */ ;
/*!50003 SET sql_mode              = 'ONLY_FULL_GROUP_BY,STRICT_TRANS_TABLES,NO_ZERO_IN_DATE,NO_ZERO_DATE,ERROR_FOR_DIVISION_BY_ZERO,NO_ENGINE_SUBSTITUTION' */ ;
DELIMITER ;;
CREATE DEFINER=`priority`@`%` PROCEDURE `USP_RPT_SUPPLIER_REGISTER`(
    pFrm_Date   DATE,
    pTo_Date    DATE,
    pBranch_Id  INT
)
BEGIN
    SELECT
        p.Party_Id,
        p.Party_Code,
        p.Party_Name,
        UDF_CAL_PARTY_CLOSING(p.Party_Id, DATE_SUB(pFrm_Date, INTERVAL 1 DAY)) AS Opening_Amt,
        IFNULL(prd.Credit_Amt, 0) AS Purchase_Amt,
        IFNULL(prd.Debit_Amt, 0) AS Payment_Amt,
        UDF_CAL_PARTY_CLOSING(p.Party_Id, pTo_Date) AS Closing_Amt
    FROM mst_party p
    LEFT JOIN (
        SELECT Party_Id,
               SUM(CASE WHEN Trans_Type = 'C' AND IFNULL(Trading_Id, 0) > 0 THEN IFNULL(Amount, 0) ELSE 0 END) AS Credit_Amt,
               SUM(CASE
                   WHEN Trans_Type = 'D' AND IFNULL(Trading_Id, 0) = 0 THEN IFNULL(Amount, 0)
                   WHEN Trans_Type = 'C' AND Trans_Mode IN (1, 2) AND IFNULL(Trading_Id, 0) > 0 THEN IFNULL(Amount, 0)
                   ELSE 0
               END) AS Debit_Amt
        FROM trn_party_trans
        WHERE Trans_Date BETWEEN pFrm_Date AND pTo_Date
        GROUP BY Party_Id
    ) prd ON prd.Party_Id = p.Party_Id
    WHERE p.Party_Type = 1
      AND (pBranch_Id = 0 OR p.Branch_Id = pBranch_Id)
    ORDER BY p.Party_Name;
END ;;
DELIMITER ;
/*!50003 SET sql_mode              = @saved_sql_mode */ ;
/*!50003 SET character_set_client  = @saved_cs_client */ ;
/*!50003 SET character_set_results = @saved_cs_results */ ;
/*!50003 SET collation_connection  = @saved_col_connection */ ;
/*!50003 DROP PROCEDURE IF EXISTS `USP_RPT_TRADING_PL` */;
/*!50003 SET @saved_cs_client      = @@character_set_client */ ;
/*!50003 SET @saved_cs_results     = @@character_set_results */ ;
/*!50003 SET @saved_col_connection = @@collation_connection */ ;
/*!50003 SET character_set_client  = utf8mb4 */ ;
/*!50003 SET character_set_results = utf8mb4 */ ;
/*!50003 SET collation_connection  = utf8mb4_unicode_ci */ ;
/*!50003 SET @saved_sql_mode       = @@sql_mode */ ;
/*!50003 SET sql_mode              = 'ONLY_FULL_GROUP_BY,STRICT_TRANS_TABLES,NO_ZERO_IN_DATE,NO_ZERO_DATE,ERROR_FOR_DIVISION_BY_ZERO,NO_ENGINE_SUBSTITUTION' */ ;
DELIMITER ;;
CREATE DEFINER=`priority`@`%` PROCEDURE `USP_RPT_TRADING_PL`(
    pYear_Id INT,
    pDate    DATE
)
BEGIN
    DECLARE vGP DECIMAL(18,2) DEFAULT 0;
    DECLARE vNP DECIMAL(18,2) DEFAULT 0;
    DECLARE vTrDr DECIMAL(18,2) DEFAULT 0;
    DECLARE vTrCr DECIMAL(18,2) DEFAULT 0;
    DECLARE vPlDr DECIMAL(18,2) DEFAULT 0;
    DECLARE vPlCr DECIMAL(18,2) DEFAULT 0;

    DROP TEMPORARY TABLE IF EXISTS tmp_gl_bal;
    CREATE TEMPORARY TABLE tmp_gl_bal (
        Account_Id   SMALLINT,
        Account_Code VARCHAR(6),
        Account_Desc VARCHAR(200),
        Cate_Id      TINYINT,
        Cate_Desc    VARCHAR(25),
        Bal          DECIMAL(18,2)
    );

    INSERT INTO tmp_gl_bal (Account_Id, Account_Code, Account_Desc, Cate_Id, Cate_Desc, Bal)
    SELECT
        g.Account_Id,
        g.Account_Code,
        g.Account_Desc,
        c.Cate_Id,
        c.Cate_Desc,
        IFNULL((
            SELECT SUM(CASE WHEN d.Trans_Type = 'D' THEN d.Vou_Amount ELSE -d.Vou_Amount END)
            FROM trans_voucher_details d
            INNER JOIN trans_voucher_master m ON m.Voucher_Id = d.Voucher_Id
            WHERE d.GlHead_Id = g.Account_Id
              AND m.Year_Id = pYear_Id
              AND IFNULL(m.Status, 2) = 2
              AND m.Vou_Date <= pDate
        ), 0)
    FROM mst_acct_glhead g
    INNER JOIN mst_acct_mainhd h ON h.MainHd_Id = g.MainHd_Id
    INNER JOIN mst_acct_category c ON c.Cate_Id = h.Cate_Id;

    SELECT IFNULL(-SUM(Bal), 0) INTO vGP
    FROM tmp_gl_bal
    WHERE Cate_Id IN (5, 6);

    SELECT IFNULL(-SUM(Bal), 0) INTO vNP
    FROM tmp_gl_bal
    WHERE Cate_Id IN (3, 4, 5, 6);

    DROP TEMPORARY TABLE IF EXISTS tmp_tpl;
    CREATE TEMPORARY TABLE tmp_tpl (
        Sort_No     INT,
        Section     VARCHAR(40),
        Particulars VARCHAR(250),
        Debit       DECIMAL(18,2),
        Credit      DECIMAL(18,2),
        Row_Kind    TINYINT
    );

    INSERT INTO tmp_tpl VALUES (10, 'Trading Account', 'Trading Account', NULL, NULL, 1);

    INSERT INTO tmp_tpl (Sort_No, Section, Particulars, Debit, Credit, Row_Kind)
    SELECT
        20,
        'Trading Account',
        CONCAT('To ', Account_Desc),
        CASE WHEN Bal > 0 THEN Bal ELSE NULL END,
        CASE WHEN Bal < 0 THEN -Bal ELSE NULL END,
        0
    FROM tmp_gl_bal
    WHERE Cate_Id = 6 AND Bal <> 0;

    INSERT INTO tmp_tpl (Sort_No, Section, Particulars, Debit, Credit, Row_Kind)
    SELECT
        30,
        'Trading Account',
        CONCAT('By ', Account_Desc),
        CASE WHEN Bal > 0 THEN Bal ELSE NULL END,
        CASE WHEN Bal < 0 THEN -Bal ELSE NULL END,
        0
    FROM tmp_gl_bal
    WHERE Cate_Id = 5 AND Bal <> 0;

    IF vGP >= 0 THEN
        INSERT INTO tmp_tpl VALUES (40, 'Trading Account', 'To Gross Profit c/d', vGP, NULL, 3);
    ELSE
        INSERT INTO tmp_tpl VALUES (40, 'Trading Account', 'By Gross Loss c/d', NULL, -vGP, 3);
    END IF;

    SELECT IFNULL(SUM(Debit), 0), IFNULL(SUM(Credit), 0)
      INTO vTrDr, vTrCr
    FROM tmp_tpl
    WHERE Section = 'Trading Account' AND Row_Kind <> 1;

    INSERT INTO tmp_tpl VALUES (50, 'Trading Account', 'Total', vTrDr, vTrCr, 2);

    INSERT INTO tmp_tpl VALUES (60, 'Profit & Loss Account', 'Profit & Loss Account', NULL, NULL, 1);

    IF vGP >= 0 THEN
        INSERT INTO tmp_tpl VALUES (70, 'Profit & Loss Account', 'By Gross Profit b/d', NULL, vGP, 3);
    ELSE
        INSERT INTO tmp_tpl VALUES (70, 'Profit & Loss Account', 'To Gross Loss b/d', -vGP, NULL, 3);
    END IF;

    INSERT INTO tmp_tpl (Sort_No, Section, Particulars, Debit, Credit, Row_Kind)
    SELECT
        80,
        'Profit & Loss Account',
        CONCAT('To ', Account_Desc),
        CASE WHEN Bal > 0 THEN Bal ELSE NULL END,
        CASE WHEN Bal < 0 THEN -Bal ELSE NULL END,
        0
    FROM tmp_gl_bal
    WHERE Cate_Id = 4 AND Bal <> 0;

    INSERT INTO tmp_tpl (Sort_No, Section, Particulars, Debit, Credit, Row_Kind)
    SELECT
        90,
        'Profit & Loss Account',
        CONCAT('By ', Account_Desc),
        CASE WHEN Bal > 0 THEN Bal ELSE NULL END,
        CASE WHEN Bal < 0 THEN -Bal ELSE NULL END,
        0
    FROM tmp_gl_bal
    WHERE Cate_Id = 3 AND Bal <> 0;

    IF vNP >= 0 THEN
        INSERT INTO tmp_tpl VALUES (100, 'Profit & Loss Account', 'To Net Profit', vNP, NULL, 3);
    ELSE
        INSERT INTO tmp_tpl VALUES (100, 'Profit & Loss Account', 'By Net Loss', NULL, -vNP, 3);
    END IF;

    SELECT IFNULL(SUM(Debit), 0), IFNULL(SUM(Credit), 0)
      INTO vPlDr, vPlCr
    FROM tmp_tpl
    WHERE Section = 'Profit & Loss Account' AND Row_Kind <> 1;

    INSERT INTO tmp_tpl VALUES (110, 'Profit & Loss Account', 'Total', vPlDr, vPlCr, 2);

    SELECT Section, Particulars, Debit, Credit, Row_Kind
    FROM tmp_tpl
    ORDER BY Sort_No, Particulars;
END ;;
DELIMITER ;
/*!50003 SET sql_mode              = @saved_sql_mode */ ;
/*!50003 SET character_set_client  = @saved_cs_client */ ;
/*!50003 SET character_set_results = @saved_cs_results */ ;
/*!50003 SET collation_connection  = @saved_col_connection */ ;
/*!50003 DROP PROCEDURE IF EXISTS `USP_RPT_TRIAL_BALANCE` */;
/*!50003 SET @saved_cs_client      = @@character_set_client */ ;
/*!50003 SET @saved_cs_results     = @@character_set_results */ ;
/*!50003 SET @saved_col_connection = @@collation_connection */ ;
/*!50003 SET character_set_client  = utf8mb4 */ ;
/*!50003 SET character_set_results = utf8mb4 */ ;
/*!50003 SET collation_connection  = utf8mb4_unicode_ci */ ;
/*!50003 SET @saved_sql_mode       = @@sql_mode */ ;
/*!50003 SET sql_mode              = 'ONLY_FULL_GROUP_BY,STRICT_TRANS_TABLES,NO_ZERO_IN_DATE,NO_ZERO_DATE,ERROR_FOR_DIVISION_BY_ZERO,NO_ENGINE_SUBSTITUTION' */ ;
DELIMITER ;;
CREATE DEFINER=`priority`@`%` PROCEDURE `USP_RPT_TRIAL_BALANCE`(
    pYear_Id INT,
    pDate    DATE
)
BEGIN
    DROP TEMPORARY TABLE IF EXISTS tmp_gl_bal;
    CREATE TEMPORARY TABLE tmp_gl_bal (
        Account_Id   SMALLINT,
        Account_Code VARCHAR(6),
        Account_Desc VARCHAR(200),
        Cate_Id      TINYINT,
        Cate_Desc    VARCHAR(25),
        Bal          DECIMAL(18,2)
    );

    INSERT INTO tmp_gl_bal (Account_Id, Account_Code, Account_Desc, Cate_Id, Cate_Desc, Bal)
    SELECT
        g.Account_Id,
        g.Account_Code,
        g.Account_Desc,
        c.Cate_Id,
        c.Cate_Desc,
        IFNULL((
            SELECT SUM(CASE WHEN d.Trans_Type = 'D' THEN d.Vou_Amount ELSE -d.Vou_Amount END)
            FROM trans_voucher_details d
            INNER JOIN trans_voucher_master m ON m.Voucher_Id = d.Voucher_Id
            WHERE d.GlHead_Id = g.Account_Id
              AND m.Year_Id = pYear_Id
              AND IFNULL(m.Status, 2) = 2
              AND m.Vou_Date <= pDate
        ), 0)
    FROM mst_acct_glhead g
    INNER JOIN mst_acct_mainhd h ON h.MainHd_Id = g.MainHd_Id
    INNER JOIN mst_acct_category c ON c.Cate_Id = h.Cate_Id;

    SELECT
        b.Account_Code,
        b.Account_Desc,
        b.Cate_Desc AS Category,
        CASE WHEN b.Bal > 0 THEN b.Bal ELSE 0 END AS Debit,
        CASE WHEN b.Bal < 0 THEN -b.Bal ELSE 0 END AS Credit
    FROM tmp_gl_bal b
    WHERE b.Bal <> 0

    UNION ALL

    SELECT
        '------',
        'Unallocated (no ledger)',
        '',
        CASE WHEN x.Bal > 0 THEN x.Bal ELSE 0 END,
        CASE WHEN x.Bal < 0 THEN -x.Bal ELSE 0 END
    FROM (
        SELECT IFNULL(SUM(CASE WHEN d.Trans_Type = 'D' THEN d.Vou_Amount ELSE -d.Vou_Amount END), 0) AS Bal
        FROM trans_voucher_details d
        INNER JOIN trans_voucher_master m ON m.Voucher_Id = d.Voucher_Id
        WHERE d.GlHead_Id IS NULL
          AND m.Year_Id = pYear_Id
          AND IFNULL(m.Status, 2) = 2
          AND m.Vou_Date <= pDate
    ) x
    WHERE x.Bal <> 0

    ORDER BY Account_Code, Account_Desc;
END ;;
DELIMITER ;
/*!50003 SET sql_mode              = @saved_sql_mode */ ;
/*!50003 SET character_set_client  = @saved_cs_client */ ;
/*!50003 SET character_set_results = @saved_cs_results */ ;
/*!50003 SET collation_connection  = @saved_col_connection */ ;
/*!50003 DROP PROCEDURE IF EXISTS `USP_RPT_USER_SCROLL` */;
/*!50003 SET @saved_cs_client      = @@character_set_client */ ;
/*!50003 SET @saved_cs_results     = @@character_set_results */ ;
/*!50003 SET @saved_col_connection = @@collation_connection */ ;
/*!50003 SET character_set_client  = utf8mb4 */ ;
/*!50003 SET character_set_results = utf8mb4 */ ;
/*!50003 SET collation_connection  = utf8mb4_unicode_ci */ ;
/*!50003 SET @saved_sql_mode       = @@sql_mode */ ;
/*!50003 SET sql_mode              = 'ONLY_FULL_GROUP_BY,STRICT_TRANS_TABLES,NO_ZERO_IN_DATE,NO_ZERO_DATE,ERROR_FOR_DIVISION_BY_ZERO,NO_ENGINE_SUBSTITUTION' */ ;
DELIMITER ;;
CREATE DEFINER=`priority`@`%` PROCEDURE `USP_RPT_USER_SCROLL`(
    pYear_Id    INT,
    pAsOn       DATE,
    pUser_Id    INT,
    pBranch_Id  INT
)
BEGIN
    DECLARE vCashId INT DEFAULT 0;

    SELECT IFNULL((
        SELECT d.Cash_Ledg FROM mst_default_ledger d WHERE d.Cash_Ledg IS NOT NULL LIMIT 1
    ), 0) INTO vCashId;

    IF vCashId = 0 THEN
        SELECT IFNULL((
            SELECT g.Account_Id FROM mst_acct_glhead g WHERE g.Account_For = 'C' LIMIT 1
        ), 0) INTO vCashId;
    END IF;

    SELECT
        IFNULL(CONCAT(g.Account_Code, ' - ', g.Account_Desc), 'Unallocated') AS Ledger_Name,
        IFNULL(m.Vou_No, '') AS Doc_No,
        IFNULL(NULLIF(TRIM(m.Particulars), ''), IFNULL(m.Ref_Vou_No, '')) AS Particulars,
        CASE WHEN cash.Trans_Type = 'D' THEN IFNULL(d.Vou_Amount, 0) ELSE 0 END AS Receipt_Amt,
        CASE WHEN cash.Trans_Type = 'C' THEN IFNULL(d.Vou_Amount, 0) ELSE 0 END AS Payment_Amt,
        d.VouDtls_Id AS Sort_Id
    FROM trans_voucher_master m
    INNER JOIN trans_voucher_details cash
        ON cash.Voucher_Id = m.Voucher_Id
       AND cash.GlHead_Id = vCashId
    INNER JOIN trans_voucher_details d
        ON d.Voucher_Id = m.Voucher_Id
       AND (d.GlHead_Id IS NULL OR d.GlHead_Id <> vCashId)
    LEFT JOIN mst_acct_glhead g ON g.Account_Id = d.GlHead_Id
    WHERE m.Year_Id = pYear_Id
      AND IFNULL(m.Status, 2) = 2
      AND m.Vou_Date = pAsOn
      AND (pUser_Id = 0 OR m.Created_By = pUser_Id)
      AND cash.Trans_Type IN ('D', 'C')
    ORDER BY Ledger_Name, m.Voucher_Id, d.VouDtls_Id;
END ;;
DELIMITER ;
/*!50003 SET sql_mode              = @saved_sql_mode */ ;
/*!50003 SET character_set_client  = @saved_cs_client */ ;
/*!50003 SET character_set_results = @saved_cs_results */ ;
/*!50003 SET collation_connection  = @saved_col_connection */ ;
/*!50003 DROP PROCEDURE IF EXISTS `USP_SEARCH_AGENT_INDENT` */;
/*!50003 SET @saved_cs_client      = @@character_set_client */ ;
/*!50003 SET @saved_cs_results     = @@character_set_results */ ;
/*!50003 SET @saved_col_connection = @@collation_connection */ ;
/*!50003 SET character_set_client  = utf8mb4 */ ;
/*!50003 SET character_set_results = utf8mb4 */ ;
/*!50003 SET collation_connection  = utf8mb4_unicode_ci */ ;
/*!50003 SET @saved_sql_mode       = @@sql_mode */ ;
/*!50003 SET sql_mode              = 'ONLY_FULL_GROUP_BY,STRICT_TRANS_TABLES,NO_ZERO_IN_DATE,NO_ZERO_DATE,ERROR_FOR_DIVISION_BY_ZERO,NO_ENGINE_SUBSTITUTION' */ ;
DELIMITER ;;
CREATE DEFINER=`priority`@`%` PROCEDURE `USP_SEARCH_AGENT_INDENT`(
    pAgent_Id   INT,
    pFrm_Date   DATE,
    pTo_Date    DATE
)
BEGIN
    SELECT
        m.Indent_Id,
        m.Indent_Date,
        m.Indent_No,
        m.Remarks,
        CAST(m.Is_Issued AS UNSIGNED) AS Is_Issued,
        m.Issued_DtTm,
        (
            SELECT JSON_ARRAYAGG(JSON_OBJECT(
                'Indent_Sl', d.Indent_Sl,
                'Prod_Id', d.Prod_Id,
                'Prod_ShortNm', p.Prod_ShortNm,
                'Prod_Code', p.Prod_Code,
                'Quantity', d.Quantity,
                'Reject_Qty', IFNULL(d.Reject_Qty, 0),
                'Issue_Qty', IFNULL(d.Quantity, 0) - IFNULL(d.Reject_Qty, 0),
                'Unit_Id', d.Unit_Id,
                'Unit_Name', UDF_GET_UNIT_NAME(d.Unit_Id)
            ))
            FROM trans_agent_indent_details d
            JOIN mst_product p ON p.Prod_Id = d.Prod_Id
            WHERE d.Indent_Id = m.Indent_Id
        ) AS Item_Data
    FROM trans_agent_indent m
    WHERE m.Agent_Id = pAgent_Id
      AND m.Indent_Date BETWEEN pFrm_Date AND pTo_Date
    ORDER BY m.Indent_Date DESC, m.Indent_Id DESC;
END ;;
DELIMITER ;
/*!50003 SET sql_mode              = @saved_sql_mode */ ;
/*!50003 SET character_set_client  = @saved_cs_client */ ;
/*!50003 SET character_set_results = @saved_cs_results */ ;
/*!50003 SET collation_connection  = @saved_col_connection */ ;
/*!50003 DROP PROCEDURE IF EXISTS `USP_SEARCH_AGENT_ISSUE` */;
/*!50003 SET @saved_cs_client      = @@character_set_client */ ;
/*!50003 SET @saved_cs_results     = @@character_set_results */ ;
/*!50003 SET @saved_col_connection = @@collation_connection */ ;
/*!50003 SET character_set_client  = utf8mb4 */ ;
/*!50003 SET character_set_results = utf8mb4 */ ;
/*!50003 SET collation_connection  = utf8mb4_unicode_ci */ ;
/*!50003 SET @saved_sql_mode       = @@sql_mode */ ;
/*!50003 SET sql_mode              = 'ONLY_FULL_GROUP_BY,STRICT_TRANS_TABLES,NO_ZERO_IN_DATE,NO_ZERO_DATE,ERROR_FOR_DIVISION_BY_ZERO,NO_ENGINE_SUBSTITUTION' */ ;
DELIMITER ;;
CREATE DEFINER=`priority`@`%` PROCEDURE `USP_SEARCH_AGENT_ISSUE`(
    pAgent_Id   INT,
    pFrm_Date   DATE,
    pTo_Date    DATE
)
BEGIN
    SELECT
        m.Indent_No AS Doc_No,
        DATE(IFNULL(m.Issued_DtTm, m.Indent_Date)) AS Doc_Date,
        p.Prod_Code,
        p.Prod_ShortNm,
        (IFNULL(d.Quantity, 0) - IFNULL(d.Reject_Qty, 0)) AS Quantity,
        IFNULL(d.Reject_Qty, 0) AS Reject_Qty,
        IFNULL(d.Quantity, 0) AS Requested_Qty,
        UDF_GET_UNIT_NAME(d.Unit_Id) AS Unit_Name
    FROM trans_agent_indent m
    JOIN trans_agent_indent_details d ON d.Indent_Id = m.Indent_Id
    JOIN mst_product p ON p.Prod_Id = d.Prod_Id
    WHERE m.Agent_Id = pAgent_Id
      AND m.Is_Issued = 1
      AND DATE(IFNULL(m.Issued_DtTm, m.Indent_Date)) BETWEEN pFrm_Date AND pTo_Date
    ORDER BY Doc_Date DESC, m.Indent_Id DESC, d.Indent_Sl;
END ;;
DELIMITER ;
/*!50003 SET sql_mode              = @saved_sql_mode */ ;
/*!50003 SET character_set_client  = @saved_cs_client */ ;
/*!50003 SET character_set_results = @saved_cs_results */ ;
/*!50003 SET collation_connection  = @saved_col_connection */ ;
/*!50003 DROP PROCEDURE IF EXISTS `USP_SEARCH_AGENT_OFFICE_RETURN` */;
/*!50003 SET @saved_cs_client      = @@character_set_client */ ;
/*!50003 SET @saved_cs_results     = @@character_set_results */ ;
/*!50003 SET @saved_col_connection = @@collation_connection */ ;
/*!50003 SET character_set_client  = utf8mb4 */ ;
/*!50003 SET character_set_results = utf8mb4 */ ;
/*!50003 SET collation_connection  = utf8mb4_unicode_ci */ ;
/*!50003 SET @saved_sql_mode       = @@sql_mode */ ;
/*!50003 SET sql_mode              = 'ONLY_FULL_GROUP_BY,STRICT_TRANS_TABLES,NO_ZERO_IN_DATE,NO_ZERO_DATE,ERROR_FOR_DIVISION_BY_ZERO,NO_ENGINE_SUBSTITUTION' */ ;
DELIMITER ;;
CREATE DEFINER=`priority`@`%` PROCEDURE `USP_SEARCH_AGENT_OFFICE_RETURN`(
    pAgent_Id   INT,
    pFrm_Date   DATE,
    pTo_Date    DATE
)
BEGIN
    SELECT
        CAST(s.AgntStock_Id AS CHAR) AS Doc_No,
        DATE(s.Trans_Date) AS Doc_Date,
        p.Prod_Code,
        p.Prod_ShortNm,
        i.Quantity,
        UDF_GET_UNIT_NAME(i.Unit_Id) AS Unit_Name,
        'Office Return' AS Return_Type
    FROM trans_agent_stock s
    JOIN trns_stockinout i ON i.Stock_Id = s.TrnsStock_Id
    JOIN mst_product p ON p.Prod_Id = IFNULL(s.Prod_Id, i.Prod_Id)
    WHERE s.Agent_Id = pAgent_Id
      AND i.Stock_Type = 6
      AND DATE(s.Trans_Date) BETWEEN pFrm_Date AND pTo_Date

    UNION ALL

    SELECT
        m.Invoice_No AS Doc_No,
        m.Invoice_Date AS Doc_Date,
        p.Prod_Code,
        p.Prod_ShortNm,
        d.Item_Qty AS Quantity,
        UDF_GET_UNIT_NAME(d.Unit_Id) AS Unit_Name,
        'Customer Return' AS Return_Type
    FROM trans_trading m
    JOIN trans_trading_products d ON d.Trading_Id = m.Trading_Id
    JOIN mst_product p ON p.Prod_Id = d.Prod_Id
    WHERE (m.Agent_Id = pAgent_Id OR m.Created_By = pAgent_Id)
      AND m.Tranding_Type = 5
      AND m.Invoice_Date BETWEEN pFrm_Date AND pTo_Date

    UNION ALL

    SELECT
        CAST(s.AgntStock_Id AS CHAR) AS Doc_No,
        DATE(s.Trans_Date) AS Doc_Date,
        p.Prod_Code,
        p.Prod_ShortNm,
        i.Quantity,
        UDF_GET_UNIT_NAME(i.Unit_Id) AS Unit_Name,
        'Customer Return' AS Return_Type
    FROM trans_agent_stock s
    JOIN trns_stockinout i ON i.Stock_Id = s.TrnsStock_Id
    JOIN mst_product p ON p.Prod_Id = IFNULL(s.Prod_Id, i.Prod_Id)
    WHERE s.Agent_Id = pAgent_Id
      AND i.Stock_Type = 8
      AND DATE(s.Trans_Date) BETWEEN pFrm_Date AND pTo_Date
      AND NOT EXISTS (
            SELECT 1
            FROM trans_trading t
            WHERE t.Trading_Id = i.Type_Id
              AND t.Tranding_Type = 5
              AND (t.Agent_Id = pAgent_Id OR t.Created_By = pAgent_Id)
      )

    ORDER BY Doc_Date DESC, Return_Type, Doc_No DESC;
END ;;
DELIMITER ;
/*!50003 SET sql_mode              = @saved_sql_mode */ ;
/*!50003 SET character_set_client  = @saved_cs_client */ ;
/*!50003 SET character_set_results = @saved_cs_results */ ;
/*!50003 SET collation_connection  = @saved_col_connection */ ;
/*!50003 DROP PROCEDURE IF EXISTS `USP_SEARCH_AGENT_REQ` */;
/*!50003 SET @saved_cs_client      = @@character_set_client */ ;
/*!50003 SET @saved_cs_results     = @@character_set_results */ ;
/*!50003 SET @saved_col_connection = @@collation_connection */ ;
/*!50003 SET character_set_client  = utf8mb4 */ ;
/*!50003 SET character_set_results = utf8mb4 */ ;
/*!50003 SET collation_connection  = utf8mb4_unicode_ci */ ;
/*!50003 SET @saved_sql_mode       = @@sql_mode */ ;
/*!50003 SET sql_mode              = 'ONLY_FULL_GROUP_BY,STRICT_TRANS_TABLES,NO_ZERO_IN_DATE,NO_ZERO_DATE,ERROR_FOR_DIVISION_BY_ZERO,NO_ENGINE_SUBSTITUTION' */ ;
DELIMITER ;;
CREATE DEFINER=`priority`@`%` PROCEDURE `USP_SEARCH_AGENT_REQ`(
    pAgent_Id INT,
    pFrm_Date DATE,
    pTo_Date DATE,
    pIndent_Type_Id INT
)
BEGIN
    SELECT
        m.Indent_Id,
        m.Indent_Date,
        m.Indent_No,
        m.Remarks,
        (
            SELECT JSON_ARRAYAGG(
                JSON_OBJECT(
                    'Indent_Sl', d.Indent_Sl,
                    'Prod_Id', d.Prod_Id,
                    'Prod_ShortNm', p.Prod_ShortNm,
                    'Prod_Code', p.Prod_Code,
                    'Quantity', d.Quantity,
                    'Reject_Qty', IFNULL(d.Reject_Qty, 0),
                    'Unit_Id', d.Unit_Id,
                    'Unit_Name', UDF_GET_UNIT_NAME(d.Unit_Id)
                )
            )
            FROM trans_agent_indent_details d
            JOIN mst_product p ON p.Prod_Id = d.Prod_Id
            WHERE d.Indent_Id = m.Indent_Id
        ) AS Item_Data
    FROM trans_agent_indent m
    WHERE m.Agent_Id = pAgent_Id
      AND m.Indent_Date BETWEEN pFrm_Date AND pTo_Date
      AND m.Is_Issued = 0
      AND m.Indent_Type_Id = IFNULL(pIndent_Type_Id, 1);
END ;;
DELIMITER ;
/*!50003 SET sql_mode              = @saved_sql_mode */ ;
/*!50003 SET character_set_client  = @saved_cs_client */ ;
/*!50003 SET character_set_results = @saved_cs_results */ ;
/*!50003 SET collation_connection  = @saved_col_connection */ ;
/*!50003 DROP PROCEDURE IF EXISTS `USP_SEARCH_AGENT_SALE` */;
/*!50003 SET @saved_cs_client      = @@character_set_client */ ;
/*!50003 SET @saved_cs_results     = @@character_set_results */ ;
/*!50003 SET @saved_col_connection = @@collation_connection */ ;
/*!50003 SET character_set_client  = utf8mb4 */ ;
/*!50003 SET character_set_results = utf8mb4 */ ;
/*!50003 SET collation_connection  = utf8mb4_unicode_ci */ ;
/*!50003 SET @saved_sql_mode       = @@sql_mode */ ;
/*!50003 SET sql_mode              = 'ONLY_FULL_GROUP_BY,STRICT_TRANS_TABLES,NO_ZERO_IN_DATE,NO_ZERO_DATE,ERROR_FOR_DIVISION_BY_ZERO,NO_ENGINE_SUBSTITUTION' */ ;
DELIMITER ;;
CREATE DEFINER=`priority`@`%` PROCEDURE `USP_SEARCH_AGENT_SALE`(
    pAgent_Id   INT,
    pFrm_Date   DATE,
    pTo_Date    DATE
)
BEGIN
    SELECT
        m.Trading_Id,
        m.Invoice_No AS Doc_No,
        m.Invoice_Date AS Doc_Date,
        p.Prod_Code,
        p.Prod_ShortNm,
        d.Item_Qty AS Quantity,
        UDF_GET_UNIT_NAME(d.Unit_Id) AS Unit_Name,
        IFNULL(d.Net_Amt, 0) AS Amount,
        IFNULL(m.Pay_Mode, 0) AS Pay_Mode,
        CASE IFNULL(m.Pay_Mode, 0)
            WHEN 1 THEN 'Cash'
            WHEN 2 THEN 'UPI / Bank'
            WHEN 3 THEN 'Credit'
            ELSE '-'
        END AS Trans_Mode,
        IFNULL(m.Is_Settle, 0) AS Is_Settle,
        CASE
            WHEN IFNULL(m.Pay_Mode, 0) <> 3 THEN '-'
            WHEN IFNULL(m.Is_Settle, 0) = 1 THEN 'Settled'
            ELSE 'Unsettled'
        END AS Settlement_Status
    FROM trans_trading m
    JOIN trans_trading_products d ON d.Trading_Id = m.Trading_Id
    JOIN mst_product p ON p.Prod_Id = d.Prod_Id
    WHERE m.Agent_Id = pAgent_Id
      AND m.Tranding_Type = 3
      AND m.Invoice_Date BETWEEN pFrm_Date AND pTo_Date
    ORDER BY m.Invoice_Date DESC, m.Trading_Id DESC, d.Sl;
END ;;
DELIMITER ;
/*!50003 SET sql_mode              = @saved_sql_mode */ ;
/*!50003 SET character_set_client  = @saved_cs_client */ ;
/*!50003 SET character_set_results = @saved_cs_results */ ;
/*!50003 SET collation_connection  = @saved_col_connection */ ;
/*!50003 DROP PROCEDURE IF EXISTS `USP_SEARCH_AGENT_SETTLEMENT` */;
/*!50003 SET @saved_cs_client      = @@character_set_client */ ;
/*!50003 SET @saved_cs_results     = @@character_set_results */ ;
/*!50003 SET @saved_col_connection = @@collation_connection */ ;
/*!50003 SET character_set_client  = utf8mb4 */ ;
/*!50003 SET character_set_results = utf8mb4 */ ;
/*!50003 SET collation_connection  = utf8mb4_unicode_ci */ ;
/*!50003 SET @saved_sql_mode       = @@sql_mode */ ;
/*!50003 SET sql_mode              = 'ONLY_FULL_GROUP_BY,STRICT_TRANS_TABLES,NO_ZERO_IN_DATE,NO_ZERO_DATE,ERROR_FOR_DIVISION_BY_ZERO,NO_ENGINE_SUBSTITUTION' */ ;
DELIMITER ;;
CREATE DEFINER=`priority`@`%` PROCEDURE `USP_SEARCH_AGENT_SETTLEMENT`(
    pAgent_Id   INT
)
BEGIN
    SELECT
        t.Trading_Id,
        t.Invoice_No,
        t.Invoice_Date,
        IFNULL(p.Party_Name, '') AS Customer_Name,
        IFNULL(t.Net_Amt, 0) AS Net_Amt,
        IFNULL(t.Pay_Mode, 0) AS Pay_Mode,
        CASE IFNULL(t.Pay_Mode, 0)
            WHEN 1 THEN 'Cash'
            WHEN 2 THEN 'UPI / Bank'
            WHEN 3 THEN 'Credit'
            ELSE '-'
        END AS Trans_Mode,
        IFNULL(t.Is_Settle, 0) AS Is_Settle,
        CASE WHEN IFNULL(t.Is_Settle, 0) = 1 THEN 'Yes' ELSE 'No' END AS Settlement_Status,
        t.Settle_Date
    FROM trans_trading t
    LEFT JOIN mst_party p ON p.Party_Id = t.Party_Id
    WHERE t.Agent_Id = pAgent_Id
      AND t.Tranding_Type = 3
      AND IFNULL(t.Status_Cd, 1) = 1
      AND IFNULL(t.Is_Settle, 0) = 0
    ORDER BY t.Invoice_Date DESC, t.Trading_Id DESC;
END ;;
DELIMITER ;
/*!50003 SET sql_mode              = @saved_sql_mode */ ;
/*!50003 SET character_set_client  = @saved_cs_client */ ;
/*!50003 SET character_set_results = @saved_cs_results */ ;
/*!50003 SET collation_connection  = @saved_col_connection */ ;
/*!50003 DROP PROCEDURE IF EXISTS `USP_SEARCH_AGENT_SETTLEMENT_BY_TOKEN` */;
/*!50003 SET @saved_cs_client      = @@character_set_client */ ;
/*!50003 SET @saved_cs_results     = @@character_set_results */ ;
/*!50003 SET @saved_col_connection = @@collation_connection */ ;
/*!50003 SET character_set_client  = utf8mb4 */ ;
/*!50003 SET character_set_results = utf8mb4 */ ;
/*!50003 SET collation_connection  = utf8mb4_unicode_ci */ ;
/*!50003 SET @saved_sql_mode       = @@sql_mode */ ;
/*!50003 SET sql_mode              = 'ONLY_FULL_GROUP_BY,STRICT_TRANS_TABLES,NO_ZERO_IN_DATE,NO_ZERO_DATE,ERROR_FOR_DIVISION_BY_ZERO,NO_ENGINE_SUBSTITUTION' */ ;
DELIMITER ;;
CREATE DEFINER=`priority`@`%` PROCEDURE `USP_SEARCH_AGENT_SETTLEMENT_BY_TOKEN`(
    pToken       VARCHAR(20),
    pBranch_Id   INT
)
BEGIN
    DECLARE vAgent_Id INT DEFAULT 0;

    SET vAgent_Id = IFNULL((
        SELECT Agent_Id
        FROM mst_agent
        WHERE Settle_Token = TRIM(pToken)
          AND Branch_Id = pBranch_Id
          AND Status_Cd = 1
        LIMIT 1
    ), 0);

    IF vAgent_Id = 0 THEN
        SELECT
            0 AS Trading_Id,
            '' AS Invoice_No,
            NULL AS Invoice_Date,
            '' AS Customer_Name,
            0 AS Net_Amt,
            0 AS Pay_Mode,
            '-' AS Trans_Mode,
            0 AS Is_Settle,
            'No' AS Settlement_Status,
            NULL AS Settle_Date,
            0 AS Agent_Id,
            '' AS Agent_Code,
            '' AS Agent_Name,
            -1 AS Error_No,
            'Invalid settlement token' AS Message;
    ELSEIF NOT EXISTS (
        SELECT 1
        FROM trans_trading t
        WHERE t.Agent_Id = vAgent_Id
          AND t.Tranding_Type = 3
          AND IFNULL(t.Status_Cd, 1) = 1
          AND IFNULL(t.Is_Settle, 0) = 0
    ) THEN
        SELECT
            0 AS Trading_Id,
            '' AS Invoice_No,
            NULL AS Invoice_Date,
            '' AS Customer_Name,
            0 AS Net_Amt,
            0 AS Pay_Mode,
            '-' AS Trans_Mode,
            0 AS Is_Settle,
            'No' AS Settlement_Status,
            NULL AS Settle_Date,
            a.Agent_Id,
            IFNULL(a.Agent_Code, '') AS Agent_Code,
            IFNULL(a.Agent_Name, '') AS Agent_Name,
            0 AS Error_No,
            'No unsettled transactions' AS Message
        FROM mst_agent a
        WHERE a.Agent_Id = vAgent_Id;
    ELSE
        SELECT
            t.Trading_Id,
            t.Invoice_No,
            t.Invoice_Date,
            IFNULL(p.Party_Name, '') AS Customer_Name,
            IFNULL(t.Net_Amt, 0) AS Net_Amt,
            IFNULL(t.Pay_Mode, 0) AS Pay_Mode,
            CASE IFNULL(t.Pay_Mode, 0)
                WHEN 1 THEN 'Cash'
                WHEN 2 THEN 'UPI / Bank'
                WHEN 3 THEN 'Credit'
                ELSE '-'
            END AS Trans_Mode,
            IFNULL(t.Is_Settle, 0) AS Is_Settle,
            CASE WHEN IFNULL(t.Is_Settle, 0) = 1 THEN 'Yes' ELSE 'No' END AS Settlement_Status,
            t.Settle_Date,
            a.Agent_Id,
            IFNULL(a.Agent_Code, '') AS Agent_Code,
            IFNULL(a.Agent_Name, '') AS Agent_Name,
            0 AS Error_No,
            '' AS Message
        FROM trans_trading t
        JOIN mst_agent a ON a.Agent_Id = t.Agent_Id
        LEFT JOIN mst_party p ON p.Party_Id = t.Party_Id
        WHERE t.Agent_Id = vAgent_Id
          AND t.Tranding_Type = 3
          AND IFNULL(t.Status_Cd, 1) = 1
          AND IFNULL(t.Is_Settle, 0) = 0
        ORDER BY t.Invoice_Date DESC, t.Trading_Id DESC;
    END IF;
END ;;
DELIMITER ;
/*!50003 SET sql_mode              = @saved_sql_mode */ ;
/*!50003 SET character_set_client  = @saved_cs_client */ ;
/*!50003 SET character_set_results = @saved_cs_results */ ;
/*!50003 SET collation_connection  = @saved_col_connection */ ;
/*!50003 DROP PROCEDURE IF EXISTS `USP_SEARCH_DAMAGE` */;
/*!50003 SET @saved_cs_client      = @@character_set_client */ ;
/*!50003 SET @saved_cs_results     = @@character_set_results */ ;
/*!50003 SET @saved_col_connection = @@collation_connection */ ;
/*!50003 SET character_set_client  = utf8mb4 */ ;
/*!50003 SET character_set_results = utf8mb4 */ ;
/*!50003 SET collation_connection  = utf8mb4_unicode_ci */ ;
/*!50003 SET @saved_sql_mode       = @@sql_mode */ ;
/*!50003 SET sql_mode              = 'ONLY_FULL_GROUP_BY,STRICT_TRANS_TABLES,NO_ZERO_IN_DATE,NO_ZERO_DATE,ERROR_FOR_DIVISION_BY_ZERO,NO_ENGINE_SUBSTITUTION' */ ;
DELIMITER ;;
CREATE DEFINER=`priority`@`%` PROCEDURE `USP_SEARCH_DAMAGE`(
    pFrm_Date   DATE,
    pTo_Date    DATE,
    pBranch_Id  INT
)
BEGIN
    DECLARE pTradingType TINYINT;
    SET pTradingType = IFNULL((
        SELECT Value_Id FROM smartinventory_core.mst_options
         WHERE Option_Id = 6 AND Value_Name = 'Damages Entry' LIMIT 1
    ), 6);

    SELECT
        m.Trading_Id AS Dmg_Id,
        m.Invoice_No,
        m.Invoice_Date,
        IFNULL(m.Remarks, '') AS Particulars,
        IFNULL(m.Net_Amt, 0) AS Net_Amt,
        m.Voucher_Id
    FROM trans_trading m
    WHERE m.Tranding_Type = pTradingType
      AND IFNULL(m.Status_Cd, 1) = 1
      AND m.Invoice_Date BETWEEN pFrm_Date AND pTo_Date
      AND (pBranch_Id = 0 OR m.Branch_Id = pBranch_Id)
    ORDER BY m.Invoice_Date DESC, m.Trading_Id DESC;
END ;;
DELIMITER ;
/*!50003 SET sql_mode              = @saved_sql_mode */ ;
/*!50003 SET character_set_client  = @saved_cs_client */ ;
/*!50003 SET character_set_results = @saved_cs_results */ ;
/*!50003 SET collation_connection  = @saved_col_connection */ ;
/*!50003 DROP PROCEDURE IF EXISTS `USP_SEARCH_ITEM` */;
/*!50003 SET @saved_cs_client      = @@character_set_client */ ;
/*!50003 SET @saved_cs_results     = @@character_set_results */ ;
/*!50003 SET @saved_col_connection = @@collation_connection */ ;
/*!50003 SET character_set_client  = utf8mb4 */ ;
/*!50003 SET character_set_results = utf8mb4 */ ;
/*!50003 SET collation_connection  = utf8mb4_0900_ai_ci */ ;
/*!50003 SET @saved_sql_mode       = @@sql_mode */ ;
/*!50003 SET sql_mode              = 'ONLY_FULL_GROUP_BY,STRICT_TRANS_TABLES,NO_ZERO_IN_DATE,NO_ZERO_DATE,ERROR_FOR_DIVISION_BY_ZERO,NO_ENGINE_SUBSTITUTION' */ ;
DELIMITER ;;
CREATE DEFINER=`priority`@`%` PROCEDURE `USP_SEARCH_ITEM`(
	pKeyWord			Varchar(100)
)
BEGIN

Select Prod_Id,Concat(Prod_ShortNm,' - ',UDF_GET_CAT_SUB_NAME(Cate_Id,1),' - ',UDF_GET_CAT_SUB_NAME(SubCate_Id,2)) As Item_Name,Unit_Id,UDF_GET_UNIT_NAME(Unit_Id) As Unit_Name From mst_product Where Prod_ShortNm Like Concat('%',pKeyWord,'%');

END ;;
DELIMITER ;
/*!50003 SET sql_mode              = @saved_sql_mode */ ;
/*!50003 SET character_set_client  = @saved_cs_client */ ;
/*!50003 SET character_set_results = @saved_cs_results */ ;
/*!50003 SET collation_connection  = @saved_col_connection */ ;
/*!50003 DROP PROCEDURE IF EXISTS `USP_SEARCH_ITEM_BY_CODE` */;
/*!50003 SET @saved_cs_client      = @@character_set_client */ ;
/*!50003 SET @saved_cs_results     = @@character_set_results */ ;
/*!50003 SET @saved_col_connection = @@collation_connection */ ;
/*!50003 SET character_set_client  = utf8mb4 */ ;
/*!50003 SET character_set_results = utf8mb4 */ ;
/*!50003 SET collation_connection  = utf8mb4_unicode_ci */ ;
/*!50003 SET @saved_sql_mode       = @@sql_mode */ ;
/*!50003 SET sql_mode              = 'ONLY_FULL_GROUP_BY,STRICT_TRANS_TABLES,NO_ZERO_IN_DATE,NO_ZERO_DATE,ERROR_FOR_DIVISION_BY_ZERO,NO_ENGINE_SUBSTITUTION' */ ;
DELIMITER ;;
CREATE DEFINER=`priority`@`%` PROCEDURE `USP_SEARCH_ITEM_BY_CODE`(
    IN pCat_Id     INT,
    IN pSub_Cat    INT,
    IN pProd_Code  VARCHAR(50)
)
BEGIN
    SELECT
        m.Prod_Id,
        m.Prod_Code,
        m.Prod_ShortNm,
        m.Unit_Id,
        m.Cate_Id    AS Prd_CateId,
        m.SubCate_Id AS Prd_SubCateId,
        UDF_GET_UNIT_NAME(m.Unit_Id)       AS Unit_Name,
        UDF_GET_ITEM_GST_DETAILS(m.Gst_Id) AS GST_Data,
        c.Prd_CateNm,
        s.Prd_SubCateNm,
        c.Is_Fmcg,
        m.Gst_Id,
        IFNULL(m.Sale_Margin, 0) AS Sale_Margin,
        IFNULL((
            SELECT stk.MRP
            FROM trns_stockinout stk
            WHERE stk.Prod_Id = m.Prod_Id
              AND IFNULL(stk.MRP, 0) > 0
              AND stk.Stock_Type IN (1, 2)
            ORDER BY stk.InOut_Date DESC, stk.Stock_Id DESC
            LIMIT 1
        ), 0) AS MRP
    FROM mst_product m
    LEFT JOIN mst_prod_category c    ON c.Prd_CateId    = m.Cate_Id
    LEFT JOIN mst_prod_subcategory s ON s.Prd_SubCateId = m.SubCate_Id
    WHERE (pCat_Id  = 0 OR m.Cate_Id    = pCat_Id)
      AND (pSub_Cat = 0 OR m.SubCate_Id = pSub_Cat)
      AND (
            pProd_Code = ''
         OR TRIM(m.Prod_Code) = pProd_Code
         OR m.Prod_Code LIKE CONCAT(pProd_Code, '%')
         OR m.Prod_ShortNm LIKE CONCAT('%', pProd_Code, '%')
         OR IFNULL(m.Barcode_Label, '') = pProd_Code
         OR (
                pProd_Code REGEXP '^[0-9]+$'
            AND EXISTS (
                    SELECT 1
                    FROM trns_stockinout st
                    WHERE st.Prod_Id = m.Prod_Id
                      AND st.Barcode = CAST(pProd_Code AS SIGNED)
                )
            )
      );
END ;;
DELIMITER ;
/*!50003 SET sql_mode              = @saved_sql_mode */ ;
/*!50003 SET character_set_client  = @saved_cs_client */ ;
/*!50003 SET character_set_results = @saved_cs_results */ ;
/*!50003 SET collation_connection  = @saved_col_connection */ ;
/*!50003 DROP PROCEDURE IF EXISTS `USP_SEARCH_PURCHASE` */;
/*!50003 SET @saved_cs_client      = @@character_set_client */ ;
/*!50003 SET @saved_cs_results     = @@character_set_results */ ;
/*!50003 SET @saved_col_connection = @@collation_connection */ ;
/*!50003 SET character_set_client  = utf8mb4 */ ;
/*!50003 SET character_set_results = utf8mb4 */ ;
/*!50003 SET collation_connection  = utf8mb4_0900_ai_ci */ ;
/*!50003 SET @saved_sql_mode       = @@sql_mode */ ;
/*!50003 SET sql_mode              = 'ONLY_FULL_GROUP_BY,STRICT_TRANS_TABLES,NO_ZERO_IN_DATE,NO_ZERO_DATE,ERROR_FOR_DIVISION_BY_ZERO,NO_ENGINE_SUBSTITUTION' */ ;
DELIMITER ;;
CREATE DEFINER=`priority`@`%` PROCEDURE `USP_SEARCH_PURCHASE`(
	pFrm_Date			Date,
    pTo_Date			Date,
    pParty_Id			Int,
    pBranch_Id			Int
)
BEGIN

Select Trading_Id As Pur_Id,Invoice_No,Ref_No,Invoice_Date,Net_Amt From trans_trading Where Tranding_Type=2 And Party_Id=pParty_Id And Invoice_Date Between pFrm_Date And pTo_Date;


END ;;
DELIMITER ;
/*!50003 SET sql_mode              = @saved_sql_mode */ ;
/*!50003 SET character_set_client  = @saved_cs_client */ ;
/*!50003 SET character_set_results = @saved_cs_results */ ;
/*!50003 SET collation_connection  = @saved_col_connection */ ;
/*!50003 DROP PROCEDURE IF EXISTS `USP_SEARCH_PURCHASE_FOR_RETURN` */;
/*!50003 SET @saved_cs_client      = @@character_set_client */ ;
/*!50003 SET @saved_cs_results     = @@character_set_results */ ;
/*!50003 SET @saved_col_connection = @@collation_connection */ ;
/*!50003 SET character_set_client  = utf8mb4 */ ;
/*!50003 SET character_set_results = utf8mb4 */ ;
/*!50003 SET collation_connection  = utf8mb4_0900_ai_ci */ ;
/*!50003 SET @saved_sql_mode       = @@sql_mode */ ;
/*!50003 SET sql_mode              = 'ONLY_FULL_GROUP_BY,STRICT_TRANS_TABLES,NO_ZERO_IN_DATE,NO_ZERO_DATE,ERROR_FOR_DIVISION_BY_ZERO,NO_ENGINE_SUBSTITUTION' */ ;
DELIMITER ;;
CREATE DEFINER=`priority`@`%` PROCEDURE `USP_SEARCH_PURCHASE_FOR_RETURN`(
    pFrm_Date  DATE,
    pTo_Date   DATE,
    pParty_Id  INT,
    pBranch_Id INT
)
BEGIN
    DECLARE pPurchaseType TINYINT;
    DECLARE pReturnType   TINYINT;

    SET pPurchaseType = (
        SELECT Value_Id FROM smartinventory_core.mst_options
         WHERE Option_Id = 6 AND Value_Name = 'Purchase Entry' LIMIT 1
    );
    SET pReturnType = (
        SELECT Value_Id FROM smartinventory_core.mst_options
         WHERE Option_Id = 6 AND Value_Name = 'Purchase Return Entry' LIMIT 1
    );
    SET pPurchaseType = IFNULL(pPurchaseType, 2);
    SET pReturnType   = IFNULL(pReturnType, 4);

    SELECT
        m.Trading_Id AS Pur_Id,
        m.Invoice_No,
        m.Ref_No,
        m.Invoice_Date,
        m.Net_Amt
    FROM trans_trading m
    WHERE m.Tranding_Type = pPurchaseType
      AND m.Status_Cd = 1
      AND m.Party_Id = pParty_Id
      AND m.Invoice_Date BETWEEN pFrm_Date AND pTo_Date
      AND (pBranch_Id = 0 OR m.Branch_Id = pBranch_Id)
      AND EXISTS (
            SELECT 1
            FROM trans_trading_products p
            WHERE p.Trading_Id = m.Trading_Id
              AND (
                    IFNULL(p.Item_Qty, 0)
                    - IFNULL((
                        SELECT SUM(rp.Item_Qty)
                        FROM trans_trading r
                        INNER JOIN trans_trading_products rp
                            ON rp.Trading_Id = r.Trading_Id
                        WHERE r.Tranding_Type = pReturnType
                          AND r.Status_Cd = 1
                          AND r.Party_Id = m.Party_Id
                          AND rp.Prod_Id = p.Prod_Id
                          AND (
                                r.Ref_No = m.Invoice_No
                             OR r.Ref_No = m.Ref_No
                          )
                    ), 0)
                  ) > 0
      )
    ORDER BY m.Invoice_Date DESC, m.Trading_Id DESC;
END ;;
DELIMITER ;
/*!50003 SET sql_mode              = @saved_sql_mode */ ;
/*!50003 SET character_set_client  = @saved_cs_client */ ;
/*!50003 SET character_set_results = @saved_cs_results */ ;
/*!50003 SET collation_connection  = @saved_col_connection */ ;
/*!50003 DROP PROCEDURE IF EXISTS `USP_SEARCH_PURCHASE_RETURN` */;
/*!50003 SET @saved_cs_client      = @@character_set_client */ ;
/*!50003 SET @saved_cs_results     = @@character_set_results */ ;
/*!50003 SET @saved_col_connection = @@collation_connection */ ;
/*!50003 SET character_set_client  = utf8mb4 */ ;
/*!50003 SET character_set_results = utf8mb4 */ ;
/*!50003 SET collation_connection  = utf8mb4_0900_ai_ci */ ;
/*!50003 SET @saved_sql_mode       = @@sql_mode */ ;
/*!50003 SET sql_mode              = 'ONLY_FULL_GROUP_BY,STRICT_TRANS_TABLES,NO_ZERO_IN_DATE,NO_ZERO_DATE,ERROR_FOR_DIVISION_BY_ZERO,NO_ENGINE_SUBSTITUTION' */ ;
DELIMITER ;;
CREATE DEFINER=`priority`@`%` PROCEDURE `USP_SEARCH_PURCHASE_RETURN`(
	pFrm_Date			Date,
    pTo_Date			Date,
    pParty_Id			Int,
    pBranch_Id			Int
)
BEGIN

Select Trading_Id As Pur_Id,Invoice_No,Ref_No,Invoice_Date,Net_Amt From trans_trading Where Tranding_Type=4 And Party_Id=pParty_Id And Invoice_Date Between pFrm_Date And pTo_Date;


END ;;
DELIMITER ;
/*!50003 SET sql_mode              = @saved_sql_mode */ ;
/*!50003 SET character_set_client  = @saved_cs_client */ ;
/*!50003 SET character_set_results = @saved_cs_results */ ;
/*!50003 SET collation_connection  = @saved_col_connection */ ;
/*!50003 DROP PROCEDURE IF EXISTS `USP_SEARCH_SALE` */;
/*!50003 SET @saved_cs_client      = @@character_set_client */ ;
/*!50003 SET @saved_cs_results     = @@character_set_results */ ;
/*!50003 SET @saved_col_connection = @@collation_connection */ ;
/*!50003 SET character_set_client  = utf8mb4 */ ;
/*!50003 SET character_set_results = utf8mb4 */ ;
/*!50003 SET collation_connection  = utf8mb4_0900_ai_ci */ ;
/*!50003 SET @saved_sql_mode       = @@sql_mode */ ;
/*!50003 SET sql_mode              = 'ONLY_FULL_GROUP_BY,STRICT_TRANS_TABLES,NO_ZERO_IN_DATE,NO_ZERO_DATE,ERROR_FOR_DIVISION_BY_ZERO,NO_ENGINE_SUBSTITUTION' */ ;
DELIMITER ;;
CREATE DEFINER=`priority`@`%` PROCEDURE `USP_SEARCH_SALE`(
	pFrm_Date		Date,
    pTo_Date		Date
)
BEGIN

Select Trading_Id As Sale_Id,Invoice_No,Invoice_Date,Net_Amt From trans_trading Where Invoice_Date Between pFrm_Date And pTo_Date And Tranding_Type=3;

END ;;
DELIMITER ;
/*!50003 SET sql_mode              = @saved_sql_mode */ ;
/*!50003 SET character_set_client  = @saved_cs_client */ ;
/*!50003 SET character_set_results = @saved_cs_results */ ;
/*!50003 SET collation_connection  = @saved_col_connection */ ;
/*!50003 DROP PROCEDURE IF EXISTS `USP_SEARCH_SALE_FOR_RETURN` */;
/*!50003 SET @saved_cs_client      = @@character_set_client */ ;
/*!50003 SET @saved_cs_results     = @@character_set_results */ ;
/*!50003 SET @saved_col_connection = @@collation_connection */ ;
/*!50003 SET character_set_client  = utf8mb4 */ ;
/*!50003 SET character_set_results = utf8mb4 */ ;
/*!50003 SET collation_connection  = utf8mb4_0900_ai_ci */ ;
/*!50003 SET @saved_sql_mode       = @@sql_mode */ ;
/*!50003 SET sql_mode              = 'ONLY_FULL_GROUP_BY,STRICT_TRANS_TABLES,NO_ZERO_IN_DATE,NO_ZERO_DATE,ERROR_FOR_DIVISION_BY_ZERO,NO_ENGINE_SUBSTITUTION' */ ;
DELIMITER ;;
CREATE DEFINER=`priority`@`%` PROCEDURE `USP_SEARCH_SALE_FOR_RETURN`(
    pFrm_Date  DATE,
    pTo_Date   DATE,
    pParty_Id  INT,
    pBranch_Id INT
)
BEGIN
    DECLARE pSaleType   TINYINT;
    DECLARE pReturnType TINYINT;

    SET pSaleType = (
        SELECT Value_Id FROM smartinventory_core.mst_options
         WHERE Option_Id = 6 AND Value_Name = 'Sales Entry' LIMIT 1
    );
    SET pReturnType = (
        SELECT Value_Id FROM smartinventory_core.mst_options
         WHERE Option_Id = 6 AND Value_Name = 'Sales Return Entry' LIMIT 1
    );
    SET pSaleType   = IFNULL(pSaleType, 3);
    SET pReturnType = IFNULL(pReturnType, 5);

    SELECT
        m.Trading_Id AS Sale_Id,
        m.Invoice_No,
        m.Ref_No,
        m.Invoice_Date,
        m.Party_Id,
        CASE
            WHEN IFNULL(m.Party_Id, 0) = 0 THEN 'Outside / Cash'
            ELSE UDF_GET_PARTY_NAME(m.Party_Id)
        END AS Party_Name,
        m.Net_Amt
    FROM trans_trading m
    WHERE m.Tranding_Type = pSaleType
      AND m.Status_Cd = 1
      AND IFNULL(m.Agent_Id, 0) = 0
      AND (pParty_Id = 0 OR m.Party_Id = pParty_Id)
      AND m.Invoice_Date BETWEEN pFrm_Date AND pTo_Date
      AND (pBranch_Id = 0 OR m.Branch_Id = pBranch_Id)
      AND EXISTS (
            SELECT 1
            FROM trans_trading_products p
            WHERE p.Trading_Id = m.Trading_Id
              AND (
                    IFNULL(p.Item_Qty, 0)
                    - IFNULL((
                        SELECT SUM(rp.Item_Qty)
                        FROM trans_trading r
                        INNER JOIN trans_trading_products rp
                            ON rp.Trading_Id = r.Trading_Id
                        WHERE r.Tranding_Type = pReturnType
                          AND r.Status_Cd = 1
                          AND IFNULL(r.Agent_Id, 0) = 0
                          AND IFNULL(r.Party_Id, 0) = IFNULL(m.Party_Id, 0)
                          AND rp.Prod_Id = p.Prod_Id
                          AND (
                                r.Ref_No = m.Invoice_No
                             OR r.Ref_No = m.Ref_No
                          )
                    ), 0)
                  ) > 0
      )
    ORDER BY m.Invoice_Date DESC, m.Trading_Id DESC;
END ;;
DELIMITER ;
/*!50003 SET sql_mode              = @saved_sql_mode */ ;
/*!50003 SET character_set_client  = @saved_cs_client */ ;
/*!50003 SET character_set_results = @saved_cs_results */ ;
/*!50003 SET collation_connection  = @saved_col_connection */ ;
/*!50003 DROP PROCEDURE IF EXISTS `USP_SEARCH_SALE_RETURN` */;
/*!50003 SET @saved_cs_client      = @@character_set_client */ ;
/*!50003 SET @saved_cs_results     = @@character_set_results */ ;
/*!50003 SET @saved_col_connection = @@collation_connection */ ;
/*!50003 SET character_set_client  = utf8mb4 */ ;
/*!50003 SET character_set_results = utf8mb4 */ ;
/*!50003 SET collation_connection  = utf8mb4_0900_ai_ci */ ;
/*!50003 SET @saved_sql_mode       = @@sql_mode */ ;
/*!50003 SET sql_mode              = 'ONLY_FULL_GROUP_BY,STRICT_TRANS_TABLES,NO_ZERO_IN_DATE,NO_ZERO_DATE,ERROR_FOR_DIVISION_BY_ZERO,NO_ENGINE_SUBSTITUTION' */ ;
DELIMITER ;;
CREATE DEFINER=`priority`@`%` PROCEDURE `USP_SEARCH_SALE_RETURN`(
	pFrm_Date		Date,
    pTo_Date		Date
)
BEGIN

Select Trading_Id As Sale_Id,Invoice_No,Invoice_Date,Net_Amt From trans_trading Where Invoice_Date Between pFrm_Date And pTo_Date And Tranding_Type=5;

END ;;
DELIMITER ;
/*!50003 SET sql_mode              = @saved_sql_mode */ ;
/*!50003 SET character_set_client  = @saved_cs_client */ ;
/*!50003 SET character_set_results = @saved_cs_results */ ;
/*!50003 SET collation_connection  = @saved_col_connection */ ;
/*!50003 DROP PROCEDURE IF EXISTS `USP_SETTLE_AGENT_SALES` */;
/*!50003 SET @saved_cs_client      = @@character_set_client */ ;
/*!50003 SET @saved_cs_results     = @@character_set_results */ ;
/*!50003 SET @saved_col_connection = @@collation_connection */ ;
/*!50003 SET character_set_client  = utf8mb4 */ ;
/*!50003 SET character_set_results = utf8mb4 */ ;
/*!50003 SET collation_connection  = utf8mb4_unicode_ci */ ;
/*!50003 SET @saved_sql_mode       = @@sql_mode */ ;
/*!50003 SET sql_mode              = 'ONLY_FULL_GROUP_BY,STRICT_TRANS_TABLES,NO_ZERO_IN_DATE,NO_ZERO_DATE,ERROR_FOR_DIVISION_BY_ZERO,NO_ENGINE_SUBSTITUTION' */ ;
DELIMITER ;;
CREATE DEFINER=`priority`@`%` PROCEDURE `USP_SETTLE_AGENT_SALES`(
    pSettle_Date   DATE,
    pToken         VARCHAR(20),
    pBranch_Id     INT,
    pFin_Id        INT,
    pUser_Id       SMALLINT
)
BEGIN
    DECLARE pError_No INT DEFAULT 0;
    DECLARE pError_Message VARCHAR(150) DEFAULT '';
    DECLARE pSettled_Count INT DEFAULT 0;
    DECLARE pVoucher_Count INT DEFAULT 0;
    DECLARE pAgent_Id INT DEFAULT 0;
    DECLARE pDone INT DEFAULT 0;

    DECLARE pTrading_Id BIGINT;
    DECLARE pInvoice_No VARCHAR(45);
    DECLARE pInvoice_Date DATE;
    DECLARE pParty_Id INT;
    DECLARE pTot_Amt DECIMAL(18,2);
    DECLARE pDisc_Amt DECIMAL(18,2);
    DECLARE pTot_Gst DECIMAL(18,2);
    DECLARE pRound_Amt DECIMAL(18,2);
    DECLARE pNet_Amt DECIMAL(18,2);
    DECLARE pPay_Mode TINYINT;
    DECLARE pExist_Vouch BIGINT;

    DECLARE pVouch_No VARCHAR(100);
    DECLARE pTrans_Id BIGINT;
    DECLARE pDr_Ledg INT;
    DECLARE pParty_Ledg INT;
    DECLARE pCash_Ledg INT;
    DECLARE pBank_Ledg INT;
    DECLARE pCGST_Ledg INT;
    DECLARE pSGST_Ledg INT;
    DECLARE pRound_Ledg INT;
    DECLARE pDisc_Ledg INT;
    DECLARE pTot_Cgst DECIMAL(18,2);
    DECLARE pTot_Sgst DECIMAL(18,2);
    DECLARE pItem_Sum DECIMAL(18,2);
    DECLARE pDiff DECIMAL(18,2);
    DECLARE pAdj_Ledg INT;

    DECLARE cur_sales CURSOR FOR
        SELECT t.Trading_Id,
               t.Invoice_No,
               t.Invoice_Date,
               IFNULL(t.Party_Id, 0),
               IFNULL(t.Invoice_Amt, 0),
               IFNULL(t.Disc_Amount, 0),
               IFNULL(t.GST_Amt, 0),
               IFNULL(t.Round_Off, 0),
               IFNULL(t.Net_Amt, 0),
               IFNULL(t.Pay_Mode, 1),
               IFNULL(t.Voucher_Id, 0)
          FROM trans_trading t
         WHERE t.Agent_Id = pAgent_Id
           AND t.Tranding_Type = 3
           AND IFNULL(t.Status_Cd, 1) = 1
           AND IFNULL(t.Is_Settle, 0) = 0
         ORDER BY t.Trading_Id;

    DECLARE CONTINUE HANDLER FOR NOT FOUND SET pDone = 1;

    IF pSettle_Date IS NULL THEN
        SET pError_No = -1;
        SET pError_Message = 'Please select settlement date';
    ELSEIF IFNULL(pToken, '') = '' THEN
        SET pError_No = -1;
        SET pError_Message = 'Please enter settlement token';
    ELSEIF IFNULL(pFin_Id, 0) = 0 THEN
        SET pError_No = -1;
        SET pError_Message = 'Financial year is required';
    ELSE
        SET pAgent_Id = IFNULL((
            SELECT Agent_Id
              FROM mst_agent
             WHERE Settle_Token = TRIM(pToken)
               AND Branch_Id = pBranch_Id
               AND Status_Cd = 1
             LIMIT 1
        ), 0);

        IF pAgent_Id = 0 THEN
            SET pError_No = -1;
            SET pError_Message = 'Invalid settlement token';
        END IF;
    END IF;

    IF pError_No = 0 THEN
        SELECT Cash_Ledg, Bank_Ledg, Debit_Ledger, CGST_Ledg, SGST_Ledg, Round_Ledg, Discp_Ledg
          INTO pCash_Ledg, pBank_Ledg, pParty_Ledg, pCGST_Ledg, pSGST_Ledg, pRound_Ledg, pDisc_Ledg
          FROM mst_default_ledger
         LIMIT 1;

        IF IFNULL(pCash_Ledg, 0) = 0 THEN
            SET pError_No = -1;
            SET pError_Message = 'Cash ledger not found in default ledger setup';
        ELSEIF IFNULL(pParty_Ledg, 0) = 0 THEN
            SET pError_No = -1;
            SET pError_Message = 'Default party (debtor) ledger not found';
        END IF;
    END IF;

    IF pError_No = 0 THEN
        OPEN cur_sales;

        sale_loop: LOOP
            SET pDone = 0;
            FETCH cur_sales INTO
                pTrading_Id, pInvoice_No, pInvoice_Date, pParty_Id,
                pTot_Amt, pDisc_Amt, pTot_Gst, pRound_Amt, pNet_Amt,
                pPay_Mode, pExist_Vouch;

            IF pDone = 1 THEN
                LEAVE sale_loop;
            END IF;

            IF pPay_Mode NOT IN (1, 2, 3) THEN
                SET pPay_Mode = 1;
            END IF;

            -- Already voucher-posted: only mark settled
            IF IFNULL(pExist_Vouch, 0) > 0 THEN
                UPDATE trans_trading
                   SET Is_Settle = 1,
                       Settle_Date = pSettle_Date
                 WHERE Trading_Id = pTrading_Id;
                SET pSettled_Count = pSettled_Count + 1;
                ITERATE sale_loop;
            END IF;

            SET pDr_Ledg = CASE
                WHEN pPay_Mode = 1 THEN pCash_Ledg
                WHEN pPay_Mode = 2 THEN IFNULL(NULLIF(pBank_Ledg, 0), pCash_Ledg)
                WHEN pPay_Mode = 3 THEN pParty_Ledg
                ELSE pCash_Ledg
            END;

            IF IFNULL(pDr_Ledg, 0) = 0 THEN
                SET pError_No = -1;
                SET pError_Message = CONCAT('Debit ledger missing for invoice ', IFNULL(pInvoice_No, pTrading_Id));
                LEAVE sale_loop;
            END IF;

            IF pPay_Mode = 3 AND IFNULL(pParty_Id, 0) = 0 THEN
                SET pError_No = -1;
                SET pError_Message = CONCAT('Customer missing for credit invoice ', IFNULL(pInvoice_No, pTrading_Id));
                LEAVE sale_loop;
            END IF;

            SET pVouch_No = UDF_GEN_SYS_NO(
                CASE WHEN pPay_Mode = 1 THEN 'REC' ELSE 'JOU' END,
                pFin_Id,
                pBranch_Id
            );

            IF pVouch_No IS NULL THEN
                SET pError_No = -3;
                SET pError_Message = CONCAT('Voucher number not generated for ', IFNULL(pInvoice_No, pTrading_Id));
                LEAVE sale_loop;
            END IF;

            INSERT INTO trans_voucher_master (
                Year_Id, Vou_Date, Vou_Type, Vou_Mode, Vou_No, Ref_Vou_No, Particulars,
                Trans_Type, Type_Id, Status, Created_By, Approved_By, Approved_On
            ) VALUES (
                pFin_Id,
                pSettle_Date,
                CASE WHEN pPay_Mode = 1 THEN 1 ELSE 4 END,
                pPay_Mode,
                pVouch_No,
                pInvoice_No,
                CONCAT('By Agent Sale ', pInvoice_No),
                1,
                pTrading_Id,
                2,
                pUser_Id,
                pUser_Id,
                CURRENT_TIMESTAMP()
            );

            SET pTrans_Id = LAST_INSERT_ID();

            -- Debit cash / bank / debtor with invoice amount (same as counter sale)
            INSERT INTO trans_voucher_details (Voucher_Id, Trans_Type, GlHead_Id, SubLedger_Id, Vou_Amount)
            VALUES (
                pTrans_Id,
                'D',
                pDr_Ledg,
                CASE WHEN pPay_Mode = 3 THEN pParty_Id ELSE NULL END,
                pTot_Amt
            );

            -- Credit sales ledgers by category (Pur_Ledg)
            INSERT INTO trans_voucher_details (Voucher_Id, Trans_Type, GlHead_Id, SubLedger_Id, Vou_Amount)
            SELECT pTrans_Id, 'C', ANY_VALUE(c.Pur_Ledg), NULL, SUM(d.Item_Total)
              FROM trans_trading_products d
              JOIN mst_product i ON i.Prod_Id = d.Prod_Id
              JOIN mst_prod_category c ON c.Prd_CateId = i.Cate_Id
             WHERE d.Trading_Id = pTrading_Id
             GROUP BY c.Pur_Ledg;

            IF IFNULL(pTot_Gst, 0) <> 0 THEN
                SET pTot_Cgst = IFNULL((
                    SELECT SUM(CGST_Amt) FROM trans_trading_products WHERE Trading_Id = pTrading_Id
                ), 0);
                SET pTot_Sgst = IFNULL((
                    SELECT SUM(SGST_Amt) FROM trans_trading_products WHERE Trading_Id = pTrading_Id
                ), 0);

                IF pTot_Cgst <> 0 AND IFNULL(pCGST_Ledg, 0) <> 0 THEN
                    INSERT INTO trans_voucher_details (Voucher_Id, Trans_Type, GlHead_Id, Vou_Amount)
                    VALUES (pTrans_Id, 'C', pCGST_Ledg, pTot_Cgst);
                END IF;

                IF pTot_Sgst <> 0 AND IFNULL(pSGST_Ledg, 0) <> 0 THEN
                    INSERT INTO trans_voucher_details (Voucher_Id, Trans_Type, GlHead_Id, Vou_Amount)
                    VALUES (pTrans_Id, 'C', pSGST_Ledg, pTot_Sgst);
                END IF;
            END IF;

            IF IFNULL(pDisc_Amt, 0) <> 0 AND IFNULL(pDisc_Ledg, 0) <> 0 THEN
                INSERT INTO trans_voucher_details (Voucher_Id, Trans_Type, GlHead_Id, Vou_Amount)
                VALUES (pTrans_Id, 'D', pDisc_Ledg, ABS(pDisc_Amt));
            END IF;

            -- Round-off / balance difference (counter-sale style)
            SET pItem_Sum = IFNULL((
                SELECT SUM(Item_Total) FROM trans_trading_products WHERE Trading_Id = pTrading_Id
            ), 0);
            SET pTot_Cgst = IFNULL((
                SELECT SUM(CGST_Amt) FROM trans_trading_products WHERE Trading_Id = pTrading_Id
            ), 0);
            SET pTot_Sgst = IFNULL((
                SELECT SUM(SGST_Amt) FROM trans_trading_products WHERE Trading_Id = pTrading_Id
            ), 0);

            -- Debit side so far: Tot_Amt (+ Disc if posted)
            -- Credit side: Item_Sum + GST
            -- Diff should match Round_Off
            SET pDiff = IFNULL(pRound_Amt, 0);
            IF pDiff <> 0 THEN
                IF IFNULL(pRound_Ledg, 0) <> 0 THEN
                    INSERT INTO trans_voucher_details (Voucher_Id, Trans_Type, GlHead_Id, Vou_Amount)
                    VALUES (
                        pTrans_Id,
                        CASE WHEN pDiff < 0 THEN 'C' ELSE 'D' END,
                        pRound_Ledg,
                        ABS(pDiff)
                    );
                ELSE
                    -- No round ledger: adjust debit to Net_Amt by posting remainder on Dr ledger
                    IF pDiff > 0 THEN
                        INSERT INTO trans_voucher_details (Voucher_Id, Trans_Type, GlHead_Id, SubLedger_Id, Vou_Amount)
                        VALUES (
                            pTrans_Id,
                            'D',
                            pDr_Ledg,
                            CASE WHEN pPay_Mode = 3 THEN pParty_Id ELSE NULL END,
                            pDiff
                        );
                    ELSE
                        INSERT INTO trans_voucher_details (Voucher_Id, Trans_Type, GlHead_Id, SubLedger_Id, Vou_Amount)
                        VALUES (
                            pTrans_Id,
                            'C',
                            pDr_Ledg,
                            CASE WHEN pPay_Mode = 3 THEN pParty_Id ELSE NULL END,
                            ABS(pDiff)
                        );
                    END IF;
                END IF;
            END IF;

            UPDATE trans_trading
               SET Is_Settle = 1,
                   Settle_Date = pSettle_Date,
                   Voucher_Id = pTrans_Id
             WHERE Trading_Id = pTrading_Id;

            -- Link existing credit party ledger row to voucher (no duplicate due entry)
            IF pPay_Mode = 3 AND pParty_Id > 0 THEN
                UPDATE trn_party_trans
                   SET Txn_Id = pTrans_Id
                 WHERE Trading_Id = pTrading_Id
                   AND Trans_Type = 'D'
                   AND IFNULL(Txn_Id, 0) = 0;
            ELSEIF pParty_Id > 0 AND NOT EXISTS (
                SELECT 1 FROM trn_party_trans WHERE Trading_Id = pTrading_Id
            ) THEN
                INSERT INTO trn_party_trans (
                    Party_Id, Trans_Date, Trans_Type, Amount, Txn_Id, Trading_Id,
                    Remarks, Trans_Mode, Created_By
                ) VALUES (
                    pParty_Id, pInvoice_Date, 'D', pNet_Amt, pTrans_Id, pTrading_Id,
                    CONCAT('Agent Sale ', pInvoice_No), pPay_Mode, pUser_Id
                );
            END IF;

            SET pSettled_Count = pSettled_Count + 1;
            SET pVoucher_Count = pVoucher_Count + 1;
        END LOOP;

        CLOSE cur_sales;
    END IF;

    IF pError_No = 0 THEN
        IF pSettled_Count = 0 THEN
            SET pError_No = -1;
            SET pError_Message = 'No pending sales found for settlement';
        ELSE
            UPDATE mst_agent
               SET Settle_Token = NULL
             WHERE Agent_Id = pAgent_Id;

            SET pError_Message = CONCAT(
                'Settlement completed. ',
                pSettled_Count, ' sale(s) settled, ',
                pVoucher_Count, ' voucher(s) created.'
            );
        END IF;
    END IF;

    SELECT pError_No AS Error_No,
           pError_Message AS Message,
           pSettled_Count AS Settled_Count,
           pVoucher_Count AS Voucher_Count,
           pAgent_Id AS Agent_Id;
END ;;
DELIMITER ;
/*!50003 SET sql_mode              = @saved_sql_mode */ ;
/*!50003 SET character_set_client  = @saved_cs_client */ ;
/*!50003 SET character_set_results = @saved_cs_results */ ;
/*!50003 SET collation_connection  = @saved_col_connection */ ;
/*!50003 DROP PROCEDURE IF EXISTS `USP_VALIDATE_AGENT_LOGIN` */;
/*!50003 SET @saved_cs_client      = @@character_set_client */ ;
/*!50003 SET @saved_cs_results     = @@character_set_results */ ;
/*!50003 SET @saved_col_connection = @@collation_connection */ ;
/*!50003 SET character_set_client  = utf8mb4 */ ;
/*!50003 SET character_set_results = utf8mb4 */ ;
/*!50003 SET collation_connection  = utf8mb4_unicode_ci */ ;
/*!50003 SET @saved_sql_mode       = @@sql_mode */ ;
/*!50003 SET sql_mode              = 'ONLY_FULL_GROUP_BY,STRICT_TRANS_TABLES,NO_ZERO_IN_DATE,NO_ZERO_DATE,ERROR_FOR_DIVISION_BY_ZERO,NO_ENGINE_SUBSTITUTION' */ ;
DELIMITER ;;
CREATE DEFINER=`priority`@`%` PROCEDURE `USP_VALIDATE_AGENT_LOGIN`(
	pAgent_Code		Smallint
)
BEGIN

Declare	pBranch_Id		Int;
Declare	pExists_Count	Int;
Declare	pError_No		Int;
Declare	pError_Message	Varchar(100);
Declare	pOrg_Id			Smallint;
Declare	pOrg_Code		Varchar(4);
Declare	pOrg_Name		Varchar(255);
Declare	pBranch_Code	Varchar(6);
Declare	pBranch_Name	Varchar(100);
Declare	pYear_Desc		Varchar(100);
Declare	pYear_Start		Date;
Declare	pYear_End		Date;
Declare	pYear_Id		Int;
Declare	pGST_Have		Bit;
Declare	pGST_Type		Smallint;

Set pExists_Count = (Select Count(*) From mst_agent Where Agent_Code=pAgent_Code);

If(pExists_Count=0) Then
	Set pError_No = -1;
    Set pError_Message = 'Invalid Agent Code Entred !!';
Else
	Set pError_No = 0;
End If;

If(pError_No=0) Then
	Set pExists_Count = (Select Count(*) From mst_agent Where Agent_Code=pAgent_Code And Status_Cd=1);

    If(pExists_Count=0) Then
		Set pError_No = -2;
        Set pError_Message = 'Agent Is Not Active !!';
	Else
		Set pError_No = 0;
        Set pBranch_Id = (Select Branch_Id From mst_agent Where Agent_Code=pAgent_Code);
    End If;
End If;

If(pError_No=0) Then
	Select Year_Id,Year_Desc,Year_Start,Year_End Into pYear_Id,pYear_Desc,pYear_Start,pYear_End From smartinventory_core.mst_accountingyear Where Is_Active;
    Select m.Org_Id,m.Org_Code,m.Org_Name,b.Branch_Code,b.Branch_Name Into pOrg_Id,pOrg_Code,pOrg_Name,pBranch_Code,pBranch_Name From smartinventory_core.mst_organisation m Join smartinventory_core.mst_org_branch b On b.Org_Id=m.Org_Id Where b.Branch_Id=pBranch_Id;
    Select Is_GST,Gst_Type Into pGST_Have,pGST_Type From smartinventory_core.mst_org_config Where Org_Id=pOrg_Id;

    Select pError_No As Error_No,pError_Message As Message,Agent_Id,Agent_Code,Agent_Name,Branch_Id,LogIn_Pwd,pOrg_Id As Org_Id,pOrg_Code As Org_Code,pOrg_Name As Org_Name,pBranch_Code As Branch_Code,pBranch_Name As Branch_Name,pYear_Id As Year_Id,pYear_Desc As Year_Desc,pYear_Start As Year_Start,pYear_End As Year_End,pGST_Have As Gst_Have,pGST_Type As Gst_Type From mst_agent Where Agent_Code=pAgent_Code;
    Else
		Select pError_No As Error_No,pError_Message As Message;
End If;

END ;;
DELIMITER ;
/*!50003 SET sql_mode              = @saved_sql_mode */ ;
/*!50003 SET character_set_client  = @saved_cs_client */ ;
/*!50003 SET character_set_results = @saved_cs_results */ ;
/*!50003 SET collation_connection  = @saved_col_connection */ ;
/*!50003 DROP PROCEDURE IF EXISTS `USP_VALIDATE_USER_LOGIN` */;
/*!50003 SET @saved_cs_client      = @@character_set_client */ ;
/*!50003 SET @saved_cs_results     = @@character_set_results */ ;
/*!50003 SET @saved_col_connection = @@collation_connection */ ;
/*!50003 SET character_set_client  = utf8mb4 */ ;
/*!50003 SET character_set_results = utf8mb4 */ ;
/*!50003 SET collation_connection  = utf8mb4_unicode_ci */ ;
/*!50003 SET @saved_sql_mode       = @@sql_mode */ ;
/*!50003 SET sql_mode              = 'ONLY_FULL_GROUP_BY,STRICT_TRANS_TABLES,NO_ZERO_IN_DATE,NO_ZERO_DATE,ERROR_FOR_DIVISION_BY_ZERO,NO_ENGINE_SUBSTITUTION' */ ;
DELIMITER ;;
CREATE DEFINER=`priority`@`%` PROCEDURE `USP_VALIDATE_USER_LOGIN`(
	pUser_Name		Varchar(100)
)
BEGIN

Declare	pBranch_Id		Int;
Declare	pExists_Count	Int;
Declare	pError_No		Int;
Declare	pError_Message	Varchar(100);
Declare	pOrg_Id			Smallint;
Declare	pOrg_Code		Varchar(4);
Declare	pOrg_Name		Varchar(255);
Declare	pBranch_Code	Varchar(6);
Declare	pBranch_Name	Varchar(100);
Declare	pYear_Desc		Varchar(100);
Declare	pYear_Start		Date;
Declare	pYear_End		Date;
Declare	pYear_Id		Int;
Declare	pGST_Have		Bit;
Declare	pGST_Type		Smallint;

Set pExists_Count = (Select Count(*) From mst_user Where User_Name=pUser_Name);

If(pExists_Count=0) Then
	Set pError_No=-1;
    Set pError_Message = 'User Dosenot Exists !!';
Else
	Set pError_No = 0;
    Set pBranch_Id = (Select Branch_Id From mst_user Where User_Name=pUser_Name);
End If;

If(pError_No=0) Then
	
	Select Year_Id,Year_Desc,Year_Start,Year_End Into pYear_Id,pYear_Desc,pYear_Start,pYear_End From smartinventory_core.mst_accountingyear Where Is_Active;
	Select m.Org_Id,m.Org_Code,m.Org_Name,b.Branch_Code,b.Branch_Name Into pOrg_Id,pOrg_Code,pOrg_Name,pBranch_Code,pBranch_Name From smartinventory_core.mst_organisation m Join smartinventory_core.mst_org_branch b On b.Org_Id=m.Org_Id Where b.Branch_Id=pBranch_Id;
    Select Is_GST,Gst_Type Into pGST_Have,pGST_Type From smartinventory_core.mst_org_config Where Org_Id=pOrg_Id;
	Select pError_No As Error_No,pError_Message As Message,User_Id,User_Code,User_FullName,UGrp_Id,Branch_Id,User_Pwd,pOrg_Id As Org_Id,pOrg_Code As Org_Code,pOrg_Name As Org_Name,pBranch_Code As Branch_Code,pBranch_Name As Branch_Name,pYear_Id As Year_Id,pYear_Desc As Year_Desc,pYear_Start As Year_Start,pYear_End As Year_End,pGST_Have As Gst_Have,pGST_Type As Gst_Type From mst_user Where User_Name=pUser_Name;
    Else
		Select pError_No As Error_No,pError_Message As Message;
End If;

END ;;
DELIMITER ;
/*!50003 SET sql_mode              = @saved_sql_mode */ ;
/*!50003 SET character_set_client  = @saved_cs_client */ ;
/*!50003 SET character_set_results = @saved_cs_results */ ;
/*!50003 SET collation_connection  = @saved_col_connection */ ;
/*!40103 SET TIME_ZONE=@OLD_TIME_ZONE */;

/*!40101 SET SQL_MODE=@OLD_SQL_MODE */;
/*!40014 SET FOREIGN_KEY_CHECKS=@OLD_FOREIGN_KEY_CHECKS */;
/*!40014 SET UNIQUE_CHECKS=@OLD_UNIQUE_CHECKS */;
/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
/*!40111 SET SQL_NOTES=@OLD_SQL_NOTES */;

-- Dump completed on 2026-09-07 13:22:09
