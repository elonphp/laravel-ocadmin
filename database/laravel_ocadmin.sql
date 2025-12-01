/*
 Navicat Premium Dump SQL

 Source Server         : LocalMariaDbRoot
 Source Server Type    : MySQL
 Source Server Version : 110802 (11.8.2-MariaDB)
 Source Host           : localhost:3306
 Source Schema         : laravel_ocadmin

 Target Server Type    : MySQL
 Target Server Version : 110802 (11.8.2-MariaDB)
 File Encoding         : 65001

 Date: 02/12/2025 01:53:44
*/

SET NAMES utf8mb4;
SET FOREIGN_KEY_CHECKS = 0;

-- ----------------------------
-- Table structure for cache
-- ----------------------------
DROP TABLE IF EXISTS `cache`;
CREATE TABLE `cache`  (
  `key` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `value` mediumtext CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `expiration` int NOT NULL,
  PRIMARY KEY (`key`) USING BTREE
) ENGINE = InnoDB CHARACTER SET = utf8mb4 COLLATE = utf8mb4_unicode_ci ROW_FORMAT = Dynamic;

-- ----------------------------
-- Records of cache
-- ----------------------------

-- ----------------------------
-- Table structure for cache_locks
-- ----------------------------
DROP TABLE IF EXISTS `cache_locks`;
CREATE TABLE `cache_locks`  (
  `key` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `owner` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `expiration` int NOT NULL,
  PRIMARY KEY (`key`) USING BTREE
) ENGINE = InnoDB CHARACTER SET = utf8mb4 COLLATE = utf8mb4_unicode_ci ROW_FORMAT = Dynamic;

-- ----------------------------
-- Records of cache_locks
-- ----------------------------

-- ----------------------------
-- Table structure for countries
-- ----------------------------
DROP TABLE IF EXISTS `countries`;
CREATE TABLE `countries`  (
  `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT,
  `name` varchar(128) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL COMMENT '國家名稱',
  `native_name` varchar(128) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NULL DEFAULT NULL COMMENT '本地名稱',
  `iso_code_2` varchar(2) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL COMMENT 'ISO 3166-1 alpha-2',
  `iso_code_3` varchar(3) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL COMMENT 'ISO 3166-1 alpha-3',
  `address_format` varchar(1000) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NULL DEFAULT NULL COMMENT '地址格式範本',
  `postcode_required` tinyint(1) NOT NULL DEFAULT 0 COMMENT '郵遞區號是否必填',
  `is_active` tinyint(1) NOT NULL DEFAULT 1 COMMENT '啟用狀態',
  `sort_order` int NOT NULL DEFAULT 0 COMMENT '排序',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`) USING BTREE,
  UNIQUE INDEX `countries_iso_code_2_unique`(`iso_code_2` ASC) USING BTREE,
  INDEX `countries_is_active_index`(`is_active` ASC) USING BTREE,
  INDEX `countries_sort_order_index`(`sort_order` ASC) USING BTREE,
  INDEX `countries_iso_code_3_index`(`iso_code_3` ASC) USING BTREE
) ENGINE = InnoDB AUTO_INCREMENT = 255 CHARACTER SET = utf8mb4 COLLATE = utf8mb4_unicode_ci ROW_FORMAT = Dynamic;

-- ----------------------------
-- Records of countries
-- ----------------------------
INSERT INTO `countries` VALUES (1, 'Aaland Islands', 'Åland', 'AX', 'ALA', NULL, 0, 1, 0, '2024-09-15 18:55:06', '2024-09-15 18:55:06');
INSERT INTO `countries` VALUES (2, 'Afghanistan', 'افغانستان', 'AF', 'AFG', NULL, 0, 1, 0, '2024-09-15 18:55:06', '2024-09-15 18:55:06');
INSERT INTO `countries` VALUES (3, 'Albania', 'Shqipëria', 'AL', 'ALB', NULL, 0, 1, 0, '2024-09-15 18:55:06', '2024-09-15 18:55:06');
INSERT INTO `countries` VALUES (4, 'Algeria', 'ﺮﺌﺎﺰﺠﻠﺍ', 'DZ', 'DZA', NULL, 0, 1, 0, '2024-09-15 18:55:06', '2024-09-15 18:55:06');
INSERT INTO `countries` VALUES (5, 'American Samoa', 'American Samoa', 'AS', 'ASM', NULL, 0, 1, 0, '2024-09-15 18:55:06', '2024-09-15 18:55:06');
INSERT INTO `countries` VALUES (6, 'Andorra', 'Andorra', 'AD', 'AND', NULL, 0, 1, 0, '2024-09-15 18:55:06', '2024-09-15 18:55:06');
INSERT INTO `countries` VALUES (7, 'Angola', 'Angola', 'AO', 'AGO', NULL, 0, 1, 0, '2024-09-15 18:55:06', '2024-09-15 18:55:06');
INSERT INTO `countries` VALUES (8, 'Anguilla', 'Anguilla', 'AI', 'AIA', NULL, 0, 1, 0, '2024-09-15 18:55:06', '2024-09-15 18:55:06');
INSERT INTO `countries` VALUES (9, 'Antarctica', 'Antarctica', 'AQ', 'ATA', NULL, 0, 1, 0, '2024-09-15 18:55:06', '2024-09-15 18:55:06');
INSERT INTO `countries` VALUES (10, 'Antigua and Barbuda', 'Antigua and Barbuda', 'AG', 'ATG', NULL, 0, 1, 0, '2024-09-15 18:55:06', '2024-09-15 18:55:06');
INSERT INTO `countries` VALUES (11, 'Argentina', 'Argentina', 'AR', 'ARG', NULL, 0, 1, 0, '2024-09-15 18:55:06', '2024-09-15 18:55:06');
INSERT INTO `countries` VALUES (12, 'Armenia', 'Հայdelays', 'AM', 'ARM', NULL, 0, 1, 0, '2024-09-15 18:55:06', '2024-09-15 18:55:06');
INSERT INTO `countries` VALUES (13, 'Aruba', 'Aruba', 'AW', 'ABW', NULL, 0, 1, 0, '2024-09-15 18:55:06', '2024-09-15 18:55:06');
INSERT INTO `countries` VALUES (14, 'Ascension Island (British)', 'Ascension Island', 'AC', 'ASC', NULL, 0, 1, 0, '2024-09-15 18:55:06', '2024-09-15 18:55:06');
INSERT INTO `countries` VALUES (15, 'Australia', 'Australia', 'AU', 'AUS', NULL, 0, 1, 0, '2024-09-15 18:55:06', '2024-09-15 18:55:06');
INSERT INTO `countries` VALUES (16, 'Austria', 'Österreich', 'AT', 'AUT', NULL, 0, 1, 0, '2024-09-15 18:55:06', '2024-09-15 18:55:06');
INSERT INTO `countries` VALUES (17, 'Azerbaijan', 'Azərbaycan', 'AZ', 'AZE', NULL, 0, 1, 0, '2024-09-15 18:55:06', '2024-09-15 18:55:06');
INSERT INTO `countries` VALUES (18, 'Bahamas', 'Bahamas', 'BS', 'BHS', NULL, 0, 1, 0, '2024-09-15 18:55:06', '2024-09-15 18:55:06');
INSERT INTO `countries` VALUES (19, 'Bahrain', 'ﻦﻴﺮﺤﺐﻠﺍ', 'BH', 'BHR', NULL, 0, 1, 0, '2024-09-15 18:55:06', '2024-09-15 18:55:06');
INSERT INTO `countries` VALUES (20, 'Bangladesh', 'বাংলাদেশ', 'BD', 'BGD', NULL, 0, 1, 0, '2024-09-15 18:55:06', '2024-09-15 18:55:06');
INSERT INTO `countries` VALUES (21, 'Barbados', 'Barbados', 'BB', 'BRB', NULL, 0, 1, 0, '2024-09-15 18:55:06', '2024-09-15 18:55:06');
INSERT INTO `countries` VALUES (22, 'Belarus', 'Беларусь', 'BY', 'BLR', NULL, 0, 1, 0, '2024-09-15 18:55:06', '2024-09-15 18:55:06');
INSERT INTO `countries` VALUES (23, 'Belgium', 'België', 'BE', 'BEL', NULL, 0, 1, 0, '2024-09-15 18:55:06', '2024-09-15 18:55:06');
INSERT INTO `countries` VALUES (24, 'Belize', 'Belize', 'BZ', 'BLZ', NULL, 0, 1, 0, '2024-09-15 18:55:06', '2024-09-15 18:55:06');
INSERT INTO `countries` VALUES (25, 'Benin', 'Bénin', 'BJ', 'BEN', NULL, 0, 1, 0, '2024-09-15 18:55:06', '2024-09-15 18:55:06');
INSERT INTO `countries` VALUES (26, 'Bermuda', 'Bermuda', 'BM', 'BMU', NULL, 0, 1, 0, '2024-09-15 18:55:06', '2024-09-15 18:55:06');
INSERT INTO `countries` VALUES (27, 'Bhutan', 'འབྲུག', 'BT', 'BTN', NULL, 0, 1, 0, '2024-09-15 18:55:06', '2024-09-15 18:55:06');
INSERT INTO `countries` VALUES (28, 'Bolivia', 'Bolivia', 'BO', 'BOL', NULL, 0, 1, 0, '2024-09-15 18:55:06', '2024-09-15 18:55:06');
INSERT INTO `countries` VALUES (29, 'Bonaire, Sint Eustatius and Saba', 'Bonaire', 'BQ', 'BES', NULL, 0, 1, 0, '2024-09-15 18:55:06', '2024-09-15 18:55:06');
INSERT INTO `countries` VALUES (30, 'Bosnia and Herzegovina', 'Bosna i Hercegovina', 'BA', 'BIH', NULL, 0, 1, 0, '2024-09-15 18:55:06', '2024-09-15 18:55:06');
INSERT INTO `countries` VALUES (31, 'Botswana', 'Botswana', 'BW', 'BWA', NULL, 0, 1, 0, '2024-09-15 18:55:06', '2024-09-15 18:55:06');
INSERT INTO `countries` VALUES (32, 'Bouvet Island', 'Bouvet Island', 'BV', 'BVT', NULL, 0, 1, 0, '2024-09-15 18:55:06', '2024-09-15 18:55:06');
INSERT INTO `countries` VALUES (33, 'Brazil', 'Brasil', 'BR', 'BRA', NULL, 0, 1, 0, '2024-09-15 18:55:06', '2024-09-15 18:55:06');
INSERT INTO `countries` VALUES (34, 'British Indian Ocean Territory', 'British Indian Ocean Territory', 'IO', 'IOT', NULL, 0, 1, 0, '2024-09-15 18:55:06', '2024-09-15 18:55:06');
INSERT INTO `countries` VALUES (35, 'Brunei Darussalam', 'Brunei', 'BN', 'BRN', NULL, 0, 1, 0, '2024-09-15 18:55:06', '2024-09-15 18:55:06');
INSERT INTO `countries` VALUES (36, 'Bulgaria', 'България', 'BG', 'BGR', NULL, 0, 1, 0, '2024-09-15 18:55:06', '2024-09-15 18:55:06');
INSERT INTO `countries` VALUES (37, 'Burkina Faso', 'Burkina Faso', 'BF', 'BFA', NULL, 0, 1, 0, '2024-09-15 18:55:06', '2024-09-15 18:55:06');
INSERT INTO `countries` VALUES (38, 'Burundi', 'Burundi', 'BI', 'BDI', NULL, 0, 1, 0, '2024-09-15 18:55:06', '2024-09-15 18:55:06');
INSERT INTO `countries` VALUES (39, 'Cambodia', 'កម្ពុជា', 'KH', 'KHM', NULL, 0, 1, 0, '2024-09-15 18:55:06', '2024-09-15 18:55:06');
INSERT INTO `countries` VALUES (40, 'Cameroon', 'Cameroun', 'CM', 'CMR', NULL, 0, 1, 0, '2024-09-15 18:55:06', '2024-09-15 18:55:06');
INSERT INTO `countries` VALUES (41, 'Canada', 'Canada', 'CA', 'CAN', NULL, 0, 1, 0, '2024-09-15 18:55:06', '2024-09-15 18:55:06');
INSERT INTO `countries` VALUES (42, 'Canary Islands', 'Islas Canarias', 'IC', 'ICA', NULL, 0, 1, 0, '2024-09-15 18:55:06', '2024-09-15 18:55:06');
INSERT INTO `countries` VALUES (43, 'Cape Verde', 'Cabo Verde', 'CV', 'CPV', NULL, 0, 1, 0, '2024-09-15 18:55:06', '2024-09-15 18:55:06');
INSERT INTO `countries` VALUES (44, 'Cayman Islands', 'Cayman Islands', 'KY', 'CYM', NULL, 0, 1, 0, '2024-09-15 18:55:06', '2024-09-15 18:55:06');
INSERT INTO `countries` VALUES (45, 'Central African Republic', 'Centrafrique', 'CF', 'CAF', NULL, 0, 1, 0, '2024-09-15 18:55:06', '2024-09-15 18:55:06');
INSERT INTO `countries` VALUES (46, 'Chad', 'Tchad', 'TD', 'TCD', NULL, 0, 1, 0, '2024-09-15 18:55:06', '2024-09-15 18:55:06');
INSERT INTO `countries` VALUES (47, 'Chile', 'Chile', 'CL', 'CHL', NULL, 0, 1, 0, '2024-09-15 18:55:06', '2024-09-15 18:55:06');
INSERT INTO `countries` VALUES (48, 'China', '中国(中华人民共和国)', 'CN', 'CHN', NULL, 0, 1, 0, '2024-09-15 18:55:06', '2024-09-15 18:55:06');
INSERT INTO `countries` VALUES (49, 'Christmas Island', 'Christmas Island', 'CX', 'CXR', NULL, 0, 1, 0, '2024-09-15 18:55:06', '2024-09-15 18:55:06');
INSERT INTO `countries` VALUES (50, 'Cocos (Keeling) Islands', 'Cocos (Keeling) Islands', 'CC', 'CCK', NULL, 0, 1, 0, '2024-09-15 18:55:06', '2024-09-15 18:55:06');
INSERT INTO `countries` VALUES (51, 'Colombia', 'Colombia', 'CO', 'COL', NULL, 0, 1, 0, '2024-09-15 18:55:06', '2024-09-15 18:55:06');
INSERT INTO `countries` VALUES (52, 'Comoros', 'Komori', 'KM', 'COM', NULL, 0, 1, 0, '2024-09-15 18:55:06', '2024-09-15 18:55:06');
INSERT INTO `countries` VALUES (53, 'Congo', 'Congo', 'CG', 'COG', NULL, 0, 1, 0, '2024-09-15 18:55:06', '2024-09-15 18:55:06');
INSERT INTO `countries` VALUES (54, 'Cook Islands', 'Cook Islands', 'CK', 'COK', NULL, 0, 1, 0, '2024-09-15 18:55:06', '2024-09-15 18:55:06');
INSERT INTO `countries` VALUES (55, 'Costa Rica', 'Costa Rica', 'CR', 'CRI', NULL, 0, 1, 0, '2024-09-15 18:55:06', '2024-09-15 18:55:06');
INSERT INTO `countries` VALUES (56, 'Cote D\'Ivoire', 'Côte d\'Ivoire', 'CI', 'CIV', NULL, 0, 1, 0, '2024-09-15 18:55:06', '2024-09-15 18:55:06');
INSERT INTO `countries` VALUES (57, 'Croatia', 'Hrvatska', 'HR', 'HRV', NULL, 0, 1, 0, '2024-09-15 18:55:06', '2024-09-15 18:55:06');
INSERT INTO `countries` VALUES (58, 'Cuba', 'Cuba', 'CU', 'CUB', NULL, 0, 1, 0, '2024-09-15 18:55:06', '2024-09-15 18:55:06');
INSERT INTO `countries` VALUES (59, 'Curacao', 'Curaçao', 'CW', 'CUW', NULL, 0, 1, 0, '2024-09-15 18:55:06', '2024-09-15 18:55:06');
INSERT INTO `countries` VALUES (60, 'Cyprus', 'Κύπρος', 'CY', 'CYP', NULL, 0, 1, 0, '2024-09-15 18:55:06', '2024-09-15 18:55:06');
INSERT INTO `countries` VALUES (61, 'Czech Republic', 'Česká republika', 'CZ', 'CZE', NULL, 0, 1, 0, '2024-09-15 18:55:06', '2024-09-15 18:55:06');
INSERT INTO `countries` VALUES (62, 'Democratic Republic of Congo', 'Congo-Kinshasa', 'CD', 'COD', NULL, 0, 1, 0, '2024-09-15 18:55:06', '2024-09-15 18:55:06');
INSERT INTO `countries` VALUES (63, 'Denmark', 'Danmark', 'DK', 'DNK', NULL, 0, 1, 0, '2024-09-15 18:55:06', '2024-09-15 18:55:06');
INSERT INTO `countries` VALUES (64, 'Djibouti', 'Djibouti', 'DJ', 'DJI', NULL, 0, 1, 0, '2024-09-15 18:55:06', '2024-09-15 18:55:06');
INSERT INTO `countries` VALUES (65, 'Dominica', 'Dominica', 'DM', 'DMA', NULL, 0, 1, 0, '2024-09-15 18:55:06', '2024-09-15 18:55:06');
INSERT INTO `countries` VALUES (66, 'Dominican Republic', 'República Dominicana', 'DO', 'DOM', NULL, 0, 1, 0, '2024-09-15 18:55:06', '2024-09-15 18:55:06');
INSERT INTO `countries` VALUES (67, 'East Timor', 'Timor-Leste', 'TL', 'TLS', NULL, 0, 1, 0, '2024-09-15 18:55:06', '2024-09-15 18:55:06');
INSERT INTO `countries` VALUES (68, 'Ecuador', 'Ecuador', 'EC', 'ECU', NULL, 0, 1, 0, '2024-09-15 18:55:06', '2024-09-15 18:55:06');
INSERT INTO `countries` VALUES (69, 'Egypt', 'ﺮﺼﻣ', 'EG', 'EGY', NULL, 0, 1, 0, '2024-09-15 18:55:06', '2024-09-15 18:55:06');
INSERT INTO `countries` VALUES (70, 'El Salvador', 'El Salvador', 'SV', 'SLV', NULL, 0, 1, 0, '2024-09-15 18:55:06', '2024-09-15 18:55:06');
INSERT INTO `countries` VALUES (71, 'Equatorial Guinea', 'Guinea Ecuatorial', 'GQ', 'GNQ', NULL, 0, 1, 0, '2024-09-15 18:55:06', '2024-09-15 18:55:06');
INSERT INTO `countries` VALUES (72, 'Eritrea', 'ኤርትራ', 'ER', 'ERI', NULL, 0, 1, 0, '2024-09-15 18:55:06', '2024-09-15 18:55:06');
INSERT INTO `countries` VALUES (73, 'Estonia', 'Eesti', 'EE', 'EST', NULL, 0, 1, 0, '2024-09-15 18:55:06', '2024-09-15 18:55:06');
INSERT INTO `countries` VALUES (74, 'Ethiopia', 'ኢትዮጵያ', 'ET', 'ETH', NULL, 0, 1, 0, '2024-09-15 18:55:06', '2024-09-15 18:55:06');
INSERT INTO `countries` VALUES (75, 'Falkland Islands (Malvinas)', 'Falkland Islands', 'FK', 'FLK', NULL, 0, 1, 0, '2024-09-15 18:55:06', '2024-09-15 18:55:06');
INSERT INTO `countries` VALUES (76, 'Faroe Islands', 'Føroyar', 'FO', 'FRO', NULL, 0, 1, 0, '2024-09-15 18:55:06', '2024-09-15 18:55:06');
INSERT INTO `countries` VALUES (77, 'Fiji', 'Fiji', 'FJ', 'FJI', NULL, 0, 1, 0, '2024-09-15 18:55:06', '2024-09-15 18:55:06');
INSERT INTO `countries` VALUES (78, 'Finland', 'Suomi', 'FI', 'FIN', NULL, 0, 1, 0, '2024-09-15 18:55:06', '2024-09-15 18:55:06');
INSERT INTO `countries` VALUES (79, 'France, Metropolitan', 'France', 'FR', 'FRA', NULL, 0, 1, 0, '2024-09-15 18:55:06', '2024-09-15 18:55:06');
INSERT INTO `countries` VALUES (80, 'French Guiana', 'Guyane française', 'GF', 'GUF', NULL, 0, 1, 0, '2024-09-15 18:55:06', '2024-09-15 18:55:06');
INSERT INTO `countries` VALUES (81, 'French Polynesia', 'Polynésie française', 'PF', 'PYF', NULL, 0, 1, 0, '2024-09-15 18:55:06', '2024-09-15 18:55:06');
INSERT INTO `countries` VALUES (82, 'French Southern Territories', 'Terres australes françaises', 'TF', 'ATF', NULL, 0, 1, 0, '2024-09-15 18:55:06', '2024-09-15 18:55:06');
INSERT INTO `countries` VALUES (83, 'FYROM', 'Северна Македонија', 'MK', 'MKD', NULL, 0, 1, 0, '2024-09-15 18:55:06', '2024-09-15 18:55:06');
INSERT INTO `countries` VALUES (84, 'Gabon', 'Gabon', 'GA', 'GAB', NULL, 0, 1, 0, '2024-09-15 18:55:06', '2024-09-15 18:55:06');
INSERT INTO `countries` VALUES (85, 'Gambia', 'Gambia', 'GM', 'GMB', NULL, 0, 1, 0, '2024-09-15 18:55:06', '2024-09-15 18:55:06');
INSERT INTO `countries` VALUES (86, 'Georgia', 'საქართველო', 'GE', 'GEO', NULL, 0, 1, 0, '2024-09-15 18:55:06', '2024-09-15 18:55:06');
INSERT INTO `countries` VALUES (87, 'Germany', 'Deutschland', 'DE', 'DEU', NULL, 0, 1, 0, '2024-09-15 18:55:06', '2024-09-15 18:55:06');
INSERT INTO `countries` VALUES (88, 'Ghana', 'Ghana', 'GH', 'GHA', NULL, 0, 1, 0, '2024-09-15 18:55:06', '2024-09-15 18:55:06');
INSERT INTO `countries` VALUES (89, 'Gibraltar', 'Gibraltar', 'GI', 'GIB', NULL, 0, 1, 0, '2024-09-15 18:55:06', '2024-09-15 18:55:06');
INSERT INTO `countries` VALUES (90, 'Greece', 'Ελλάδα', 'GR', 'GRC', NULL, 0, 1, 0, '2024-09-15 18:55:06', '2024-09-15 18:55:06');
INSERT INTO `countries` VALUES (91, 'Greenland', 'Kalaallit Nunaat', 'GL', 'GRL', NULL, 0, 1, 0, '2024-09-15 18:55:06', '2024-09-15 18:55:06');
INSERT INTO `countries` VALUES (92, 'Grenada', 'Grenada', 'GD', 'GRD', NULL, 0, 1, 0, '2024-09-15 18:55:06', '2024-09-15 18:55:06');
INSERT INTO `countries` VALUES (93, 'Guadeloupe', 'Guadeloupe', 'GP', 'GLP', NULL, 0, 1, 0, '2024-09-15 18:55:06', '2024-09-15 18:55:06');
INSERT INTO `countries` VALUES (94, 'Guam', 'Guam', 'GU', 'GUM', NULL, 0, 1, 0, '2024-09-15 18:55:06', '2024-09-15 18:55:06');
INSERT INTO `countries` VALUES (95, 'Guatemala', 'Guatemala', 'GT', 'GTM', NULL, 0, 1, 0, '2024-09-15 18:55:06', '2024-09-15 18:55:06');
INSERT INTO `countries` VALUES (96, 'Guernsey', 'Guernsey', 'GG', 'GGY', NULL, 0, 1, 0, '2024-09-15 18:55:06', '2024-09-15 18:55:06');
INSERT INTO `countries` VALUES (97, 'Guinea', 'Guinée', 'GN', 'GIN', NULL, 0, 1, 0, '2024-09-15 18:55:06', '2024-09-15 18:55:06');
INSERT INTO `countries` VALUES (98, 'Guinea-Bissau', 'Guiné-Bissau', 'GW', 'GNB', NULL, 0, 1, 0, '2024-09-15 18:55:06', '2024-09-15 18:55:06');
INSERT INTO `countries` VALUES (99, 'Guyana', 'Guyana', 'GY', 'GUY', NULL, 0, 1, 0, '2024-09-15 18:55:06', '2024-09-15 18:55:06');
INSERT INTO `countries` VALUES (100, 'Haiti', 'Haïti', 'HT', 'HTI', NULL, 0, 1, 0, '2024-09-15 18:55:06', '2024-09-15 18:55:06');
INSERT INTO `countries` VALUES (101, 'Heard and Mc Donald Islands', 'Heard Island and McDonald Islands', 'HM', 'HMD', NULL, 0, 1, 0, '2024-09-15 18:55:06', '2024-09-15 18:55:06');
INSERT INTO `countries` VALUES (102, 'Honduras', 'Honduras', 'HN', 'HND', NULL, 0, 1, 0, '2024-09-15 18:55:06', '2024-09-15 18:55:06');
INSERT INTO `countries` VALUES (103, 'Hong Kong', '香港', 'HK', 'HKG', NULL, 0, 1, 0, '2024-09-15 18:55:06', '2024-09-15 18:55:06');
INSERT INTO `countries` VALUES (104, 'Hungary', 'Magyarország', 'HU', 'HUN', NULL, 0, 1, 0, '2024-09-15 18:55:06', '2024-09-15 18:55:06');
INSERT INTO `countries` VALUES (105, 'Iceland', 'Ísland', 'IS', 'ISL', NULL, 0, 1, 0, '2024-09-15 18:55:06', '2024-09-15 18:55:06');
INSERT INTO `countries` VALUES (106, 'India', 'भारत', 'IN', 'IND', NULL, 0, 1, 0, '2024-09-15 18:55:06', '2024-09-15 18:55:06');
INSERT INTO `countries` VALUES (107, 'Indonesia', 'Indonesia', 'ID', 'IDN', NULL, 0, 1, 0, '2024-09-15 18:55:06', '2024-09-15 18:55:06');
INSERT INTO `countries` VALUES (108, 'Iran (Islamic Republic of)', 'ایران', 'IR', 'IRN', NULL, 0, 1, 0, '2024-09-15 18:55:06', '2024-09-15 18:55:06');
INSERT INTO `countries` VALUES (109, 'Iraq', 'العراق', 'IQ', 'IRQ', NULL, 0, 1, 0, '2024-09-15 18:55:06', '2024-09-15 18:55:06');
INSERT INTO `countries` VALUES (110, 'Ireland', 'Ireland', 'IE', 'IRL', NULL, 0, 1, 0, '2024-09-15 18:55:06', '2024-09-15 18:55:06');
INSERT INTO `countries` VALUES (111, 'Isle of Man', 'Isle of Man', 'IM', 'IMN', NULL, 0, 1, 0, '2024-09-15 18:55:06', '2024-09-15 18:55:06');
INSERT INTO `countries` VALUES (112, 'Israel', 'לארשי', 'IL', 'ISR', NULL, 0, 1, 0, '2024-09-15 18:55:06', '2024-09-15 18:55:06');
INSERT INTO `countries` VALUES (113, 'Italy', 'Italia', 'IT', 'ITA', NULL, 0, 1, 0, '2024-09-15 18:55:06', '2024-09-15 18:55:06');
INSERT INTO `countries` VALUES (114, 'Jamaica', 'Jamaica', 'JM', 'JAM', NULL, 0, 1, 0, '2024-09-15 18:55:06', '2024-09-15 18:55:06');
INSERT INTO `countries` VALUES (115, 'Japan', '日本', 'JP', 'JPN', NULL, 0, 1, 0, '2024-09-15 18:55:06', '2024-09-15 18:55:06');
INSERT INTO `countries` VALUES (116, 'Jersey', 'Jersey', 'JE', 'JEY', NULL, 0, 1, 0, '2024-09-15 18:55:06', '2024-09-15 18:55:06');
INSERT INTO `countries` VALUES (117, 'Jordan', 'ﻦﺪﺮﺄﻠﺍ', 'JO', 'JOR', NULL, 0, 1, 0, '2024-09-15 18:55:06', '2024-09-15 18:55:06');
INSERT INTO `countries` VALUES (118, 'Kazakhstan', 'Қазақстан', 'KZ', 'KAZ', NULL, 0, 1, 0, '2024-09-15 18:55:06', '2024-09-15 18:55:06');
INSERT INTO `countries` VALUES (119, 'Kenya', 'Kenya', 'KE', 'KEN', NULL, 0, 1, 0, '2024-09-15 18:55:06', '2024-09-15 18:55:06');
INSERT INTO `countries` VALUES (120, 'Kiribati', 'Kiribati', 'KI', 'KIR', NULL, 0, 1, 0, '2024-09-15 18:55:06', '2024-09-15 18:55:06');
INSERT INTO `countries` VALUES (121, 'Kosovo, Republic of', 'Kosovë', 'XK', 'UNK', NULL, 0, 1, 0, '2024-09-15 18:55:06', '2024-09-15 18:55:06');
INSERT INTO `countries` VALUES (122, 'Kuwait', 'ﺖﻴﻮﻜﻠﺍ', 'KW', 'KWT', NULL, 0, 1, 0, '2024-09-15 18:55:06', '2024-09-15 18:55:06');
INSERT INTO `countries` VALUES (123, 'Kyrgyzstan', 'Кыргызстан', 'KG', 'KGZ', NULL, 0, 1, 0, '2024-09-15 18:55:06', '2024-09-15 18:55:06');
INSERT INTO `countries` VALUES (124, 'Lao People\'s Democratic Republic', 'ລາວ', 'LA', 'LAO', NULL, 0, 1, 0, '2024-09-15 18:55:06', '2024-09-15 18:55:06');
INSERT INTO `countries` VALUES (125, 'Latvia', 'Latvija', 'LV', 'LVA', NULL, 0, 1, 0, '2024-09-15 18:55:06', '2024-09-15 18:55:06');
INSERT INTO `countries` VALUES (126, 'Lebanon', 'ﻦﺎﻨﺐﻟ', 'LB', 'LBN', NULL, 0, 1, 0, '2024-09-15 18:55:06', '2024-09-15 18:55:06');
INSERT INTO `countries` VALUES (127, 'Lesotho', 'Lesotho', 'LS', 'LSO', NULL, 0, 1, 0, '2024-09-15 18:55:06', '2024-09-15 18:55:06');
INSERT INTO `countries` VALUES (128, 'Liberia', 'Liberia', 'LR', 'LBR', NULL, 0, 1, 0, '2024-09-15 18:55:06', '2024-09-15 18:55:06');
INSERT INTO `countries` VALUES (129, 'Libyan Arab Jamahiriya', 'ليبيا', 'LY', 'LBY', NULL, 0, 1, 0, '2024-09-15 18:55:06', '2024-09-15 18:55:06');
INSERT INTO `countries` VALUES (130, 'Liechtenstein', 'Liechtenstein', 'LI', 'LIE', NULL, 0, 1, 0, '2024-09-15 18:55:06', '2024-09-15 18:55:06');
INSERT INTO `countries` VALUES (131, 'Lithuania', 'Lietuva', 'LT', 'LTU', NULL, 0, 1, 0, '2024-09-15 18:55:06', '2024-09-15 18:55:06');
INSERT INTO `countries` VALUES (132, 'Luxembourg', 'Luxembourg', 'LU', 'LUX', NULL, 0, 1, 0, '2024-09-15 18:55:06', '2024-09-15 18:55:06');
INSERT INTO `countries` VALUES (133, 'Macau', '澳門', 'MO', 'MAC', NULL, 0, 1, 0, '2024-09-15 18:55:06', '2024-09-15 18:55:06');
INSERT INTO `countries` VALUES (134, 'Madagascar', 'Madagasikara', 'MG', 'MDG', NULL, 0, 1, 0, '2024-09-15 18:55:06', '2024-09-15 18:55:06');
INSERT INTO `countries` VALUES (135, 'Malawi', 'Malawi', 'MW', 'MWI', NULL, 0, 1, 0, '2024-09-15 18:55:06', '2024-09-15 18:55:06');
INSERT INTO `countries` VALUES (136, 'Malaysia', 'Malaysia', 'MY', 'MYS', NULL, 0, 1, 0, '2024-09-15 18:55:06', '2024-09-15 18:55:06');
INSERT INTO `countries` VALUES (137, 'Maldives', 'ދިވެހިރާއްޖެ', 'MV', 'MDV', NULL, 0, 1, 0, '2024-09-15 18:55:06', '2024-09-15 18:55:06');
INSERT INTO `countries` VALUES (138, 'Mali', 'Mali', 'ML', 'MLI', NULL, 0, 1, 0, '2024-09-15 18:55:06', '2024-09-15 18:55:06');
INSERT INTO `countries` VALUES (139, 'Malta', 'Malta', 'MT', 'MLT', NULL, 0, 1, 0, '2024-09-15 18:55:06', '2024-09-15 18:55:06');
INSERT INTO `countries` VALUES (140, 'Marshall Islands', 'Marshall Islands', 'MH', 'MHL', NULL, 0, 1, 0, '2024-09-15 18:55:06', '2024-09-15 18:55:06');
INSERT INTO `countries` VALUES (141, 'Martinique', 'Martinique', 'MQ', 'MTQ', NULL, 0, 1, 0, '2024-09-15 18:55:06', '2024-09-15 18:55:06');
INSERT INTO `countries` VALUES (142, 'Mauritania', 'موريتانيا', 'MR', 'MRT', NULL, 0, 1, 0, '2024-09-15 18:55:06', '2024-09-15 18:55:06');
INSERT INTO `countries` VALUES (143, 'Mauritius', 'Mauritius', 'MU', 'MUS', NULL, 0, 1, 0, '2024-09-15 18:55:06', '2024-09-15 18:55:06');
INSERT INTO `countries` VALUES (144, 'Mayotte', 'Mayotte', 'YT', 'MYT', NULL, 0, 1, 0, '2024-09-15 18:55:06', '2024-09-15 18:55:06');
INSERT INTO `countries` VALUES (145, 'Mexico', 'México', 'MX', 'MEX', NULL, 0, 1, 0, '2024-09-15 18:55:06', '2024-09-15 18:55:06');
INSERT INTO `countries` VALUES (146, 'Micronesia, Federated States of', 'Micronesia', 'FM', 'FSM', NULL, 0, 1, 0, '2024-09-15 18:55:06', '2024-09-15 18:55:06');
INSERT INTO `countries` VALUES (147, 'Moldova, Republic of', 'Moldova', 'MD', 'MDA', NULL, 0, 1, 0, '2024-09-15 18:55:06', '2024-09-15 18:55:06');
INSERT INTO `countries` VALUES (148, 'Monaco', 'Monaco', 'MC', 'MCO', NULL, 0, 1, 0, '2024-09-15 18:55:06', '2024-09-15 18:55:06');
INSERT INTO `countries` VALUES (149, 'Mongolia', 'Монгол', 'MN', 'MNG', NULL, 0, 1, 0, '2024-09-15 18:55:06', '2024-09-15 18:55:06');
INSERT INTO `countries` VALUES (150, 'Montenegro', 'Crna Gora', 'ME', 'MNE', NULL, 0, 1, 0, '2024-09-15 18:55:06', '2024-09-15 18:55:06');
INSERT INTO `countries` VALUES (151, 'Montserrat', 'Montserrat', 'MS', 'MSR', NULL, 0, 1, 0, '2024-09-15 18:55:06', '2024-09-15 18:55:06');
INSERT INTO `countries` VALUES (152, 'Morocco', 'ﺔﻴﺐﺮﻐﻤﻠﺍ ﺔﻜﻠﻤﻤﻠﺍ', 'MA', 'MAR', NULL, 0, 1, 0, '2024-09-15 18:55:06', '2024-09-15 18:55:06');
INSERT INTO `countries` VALUES (153, 'Mozambique', 'Moçambique', 'MZ', 'MOZ', NULL, 0, 1, 0, '2024-09-15 18:55:06', '2024-09-15 18:55:06');
INSERT INTO `countries` VALUES (154, 'Myanmar', 'မြန်မာ', 'MM', 'MMR', NULL, 0, 1, 0, '2024-09-15 18:55:06', '2024-09-15 18:55:06');
INSERT INTO `countries` VALUES (155, 'Namibia', 'Namibia', 'NA', 'NAM', NULL, 0, 1, 0, '2024-09-15 18:55:06', '2024-09-15 18:55:06');
INSERT INTO `countries` VALUES (156, 'Nauru', 'Nauru', 'NR', 'NRU', NULL, 0, 1, 0, '2024-09-15 18:55:06', '2024-09-15 18:55:06');
INSERT INTO `countries` VALUES (157, 'Nepal', 'नेपाल', 'NP', 'NPL', NULL, 0, 1, 0, '2024-09-15 18:55:06', '2024-09-15 18:55:06');
INSERT INTO `countries` VALUES (158, 'Netherlands', 'Nederland', 'NL', 'NLD', NULL, 0, 1, 0, '2024-09-15 18:55:06', '2024-09-15 18:55:06');
INSERT INTO `countries` VALUES (159, 'Netherlands Antilles', 'Nederlandse Antillen', 'AN', 'ANT', NULL, 0, 1, 0, '2024-09-15 18:55:06', '2024-09-15 18:55:06');
INSERT INTO `countries` VALUES (160, 'New Caledonia', 'Nouvelle-Calédonie', 'NC', 'NCL', NULL, 0, 1, 0, '2024-09-15 18:55:06', '2024-09-15 18:55:06');
INSERT INTO `countries` VALUES (161, 'New Zealand', 'New Zealand', 'NZ', 'NZL', NULL, 0, 1, 0, '2024-09-15 18:55:06', '2024-09-15 18:55:06');
INSERT INTO `countries` VALUES (162, 'Nicaragua', 'Nicarágua', 'NI', 'NIC', NULL, 0, 1, 0, '2024-09-15 18:55:06', '2024-09-15 18:55:06');
INSERT INTO `countries` VALUES (163, 'Niger', 'Niger', 'NE', 'NER', NULL, 0, 1, 0, '2024-09-15 18:55:06', '2024-09-15 18:55:06');
INSERT INTO `countries` VALUES (164, 'Nigeria', 'Nigeria', 'NG', 'NGA', NULL, 0, 1, 0, '2024-09-15 18:55:06', '2024-09-15 18:55:06');
INSERT INTO `countries` VALUES (165, 'Niue', 'Niue', 'NU', 'NIU', NULL, 0, 1, 0, '2024-09-15 18:55:06', '2024-09-15 18:55:06');
INSERT INTO `countries` VALUES (166, 'Norfolk Island', 'Norfolk Island', 'NF', 'NFK', NULL, 0, 1, 0, '2024-09-15 18:55:06', '2024-09-15 18:55:06');
INSERT INTO `countries` VALUES (167, 'North Korea', '조선', 'KP', 'PRK', NULL, 0, 1, 0, '2024-09-15 18:55:06', '2024-09-15 18:55:06');
INSERT INTO `countries` VALUES (168, 'Northern Mariana Islands', 'Northern Mariana Islands', 'MP', 'MNP', NULL, 0, 1, 0, '2024-09-15 18:55:06', '2024-09-15 18:55:06');
INSERT INTO `countries` VALUES (169, 'Norway', 'Norge', 'NO', 'NOR', NULL, 0, 1, 0, '2024-09-15 18:55:06', '2024-09-15 18:55:06');
INSERT INTO `countries` VALUES (170, 'Oman', 'ﻦﺎﻤﻋ', 'OM', 'OMN', NULL, 0, 1, 0, '2024-09-15 18:55:06', '2024-09-15 18:55:06');
INSERT INTO `countries` VALUES (171, 'Pakistan', 'پاکستان', 'PK', 'PAK', NULL, 0, 1, 0, '2024-09-15 18:55:06', '2024-09-15 18:55:06');
INSERT INTO `countries` VALUES (172, 'Palau', 'Palau', 'PW', 'PLW', NULL, 0, 1, 0, '2024-09-15 18:55:06', '2024-09-15 18:55:06');
INSERT INTO `countries` VALUES (173, 'Palestinian Territory, Occupied', 'فلسطين', 'PS', 'PSE', NULL, 0, 1, 0, '2024-09-15 18:55:06', '2024-09-15 18:55:06');
INSERT INTO `countries` VALUES (174, 'Panama', 'Panamá', 'PA', 'PAN', NULL, 0, 1, 0, '2024-09-15 18:55:06', '2024-09-15 18:55:06');
INSERT INTO `countries` VALUES (175, 'Papua New Guinea', 'Papua New Guinea', 'PG', 'PNG', NULL, 0, 1, 0, '2024-09-15 18:55:06', '2024-09-15 18:55:06');
INSERT INTO `countries` VALUES (176, 'Paraguay', 'Paraguay', 'PY', 'PRY', NULL, 0, 1, 0, '2024-09-15 18:55:06', '2024-09-15 18:55:06');
INSERT INTO `countries` VALUES (177, 'Peru', 'Perú', 'PE', 'PER', NULL, 0, 1, 0, '2024-09-15 18:55:06', '2024-09-15 18:55:06');
INSERT INTO `countries` VALUES (178, 'Philippines', 'Pilipinas', 'PH', 'PHL', NULL, 0, 1, 0, '2024-09-15 18:55:06', '2024-09-15 18:55:06');
INSERT INTO `countries` VALUES (179, 'Pitcairn', 'Pitcairn', 'PN', 'PCN', NULL, 0, 1, 0, '2024-09-15 18:55:06', '2024-09-15 18:55:06');
INSERT INTO `countries` VALUES (180, 'Poland', 'Polska', 'PL', 'POL', NULL, 0, 1, 0, '2024-09-15 18:55:06', '2024-09-15 18:55:06');
INSERT INTO `countries` VALUES (181, 'Portugal', 'Portugal', 'PT', 'PRT', NULL, 0, 1, 0, '2024-09-15 18:55:06', '2024-09-15 18:55:06');
INSERT INTO `countries` VALUES (182, 'Puerto Rico', 'Puerto Rico', 'PR', 'PRI', NULL, 0, 1, 0, '2024-09-15 18:55:06', '2024-09-15 18:55:06');
INSERT INTO `countries` VALUES (183, 'Qatar', 'ﺮﻄﻗ', 'QA', 'QAT', NULL, 0, 1, 0, '2024-09-15 18:55:06', '2024-09-15 18:55:06');
INSERT INTO `countries` VALUES (184, 'Reunion', 'Réunion', 'RE', 'REU', NULL, 0, 1, 0, '2024-09-15 18:55:06', '2024-09-15 18:55:06');
INSERT INTO `countries` VALUES (185, 'Romania', 'România', 'RO', 'ROM', NULL, 0, 1, 0, '2024-09-15 18:55:06', '2024-09-15 18:55:06');
INSERT INTO `countries` VALUES (186, 'Russia', 'Россия', 'RU', 'RUS', NULL, 0, 1, 0, '2024-09-15 18:55:06', '2024-09-15 18:55:06');
INSERT INTO `countries` VALUES (187, 'Rwanda', 'Rwanda', 'RW', 'RWA', NULL, 0, 1, 0, '2024-09-15 18:55:06', '2024-09-15 18:55:06');
INSERT INTO `countries` VALUES (188, 'Saint Kitts and Nevis', 'Saint Kitts and Nevis', 'KN', 'KNA', NULL, 0, 1, 0, '2024-09-15 18:55:06', '2024-09-15 18:55:06');
INSERT INTO `countries` VALUES (189, 'Saint Lucia', 'Saint Lucia', 'LC', 'LCA', NULL, 0, 1, 0, '2024-09-15 18:55:06', '2024-09-15 18:55:06');
INSERT INTO `countries` VALUES (190, 'Saint Vincent and the Grenadines', 'Saint Vincent and the Grenadines', 'VC', 'VCT', NULL, 0, 1, 0, '2024-09-15 18:55:06', '2024-09-15 18:55:06');
INSERT INTO `countries` VALUES (191, 'Samoa', 'Samoa', 'WS', 'WSM', NULL, 0, 1, 0, '2024-09-15 18:55:06', '2024-09-15 18:55:06');
INSERT INTO `countries` VALUES (192, 'San Marino', 'San Marino', 'SM', 'SMR', NULL, 0, 1, 0, '2024-09-15 18:55:06', '2024-09-15 18:55:06');
INSERT INTO `countries` VALUES (193, 'Sao Tome and Principe', 'São Tomé e Príncipe', 'ST', 'STP', NULL, 0, 1, 0, '2024-09-15 18:55:06', '2024-09-15 18:55:06');
INSERT INTO `countries` VALUES (194, 'Saudi Arabia', 'ﺔﻴﺪﻮﻌﺴﻠﺍ ﺔﻴﺐﺮﻌﻠﺍ ﺔﻜﻠﻤﻤﻠﺍ', 'SA', 'SAU', NULL, 0, 1, 0, '2024-09-15 18:55:06', '2024-09-15 18:55:06');
INSERT INTO `countries` VALUES (195, 'Senegal', 'Sénégal', 'SN', 'SEN', NULL, 0, 1, 0, '2024-09-15 18:55:06', '2024-09-15 18:55:06');
INSERT INTO `countries` VALUES (196, 'Serbia', 'Srbija', 'RS', 'SRB', NULL, 0, 1, 0, '2024-09-15 18:55:06', '2024-09-15 18:55:06');
INSERT INTO `countries` VALUES (197, 'Seychelles', 'Seychelles', 'SC', 'SYC', NULL, 0, 1, 0, '2024-09-15 18:55:06', '2024-09-15 18:55:06');
INSERT INTO `countries` VALUES (198, 'Sierra Leone', 'Sierra Leone', 'SL', 'SLE', NULL, 0, 1, 0, '2024-09-15 18:55:06', '2024-09-15 18:55:06');
INSERT INTO `countries` VALUES (199, 'Singapore', '新加坡', 'SG', 'SGP', NULL, 0, 1, 0, '2024-09-15 18:55:06', '2024-09-15 18:55:06');
INSERT INTO `countries` VALUES (200, 'Slovak Republic', 'Slovenská republika', 'SK', 'SVK', NULL, 0, 1, 0, '2024-09-15 18:55:06', '2024-09-15 18:55:06');
INSERT INTO `countries` VALUES (201, 'Slovenia', 'Slovenija', 'SI', 'SVN', NULL, 0, 1, 0, '2024-09-15 18:55:06', '2024-09-15 18:55:06');
INSERT INTO `countries` VALUES (202, 'Solomon Islands', 'Solomon Islands', 'SB', 'SLB', NULL, 0, 1, 0, '2024-09-15 18:55:06', '2024-09-15 18:55:06');
INSERT INTO `countries` VALUES (203, 'Somalia', 'Soomaaliya', 'SO', 'SOM', NULL, 0, 1, 0, '2024-09-15 18:55:06', '2024-09-15 18:55:06');
INSERT INTO `countries` VALUES (204, 'South Africa', 'South Africa', 'ZA', 'ZAF', NULL, 0, 1, 0, '2024-09-15 18:55:06', '2024-09-15 18:55:06');
INSERT INTO `countries` VALUES (205, 'South Georgia &amp; South Sandwich Islands', 'South Georgia', 'GS', 'SGS', NULL, 0, 1, 0, '2024-09-15 18:55:06', '2024-09-15 18:55:06');
INSERT INTO `countries` VALUES (206, 'South Korea', '대한민국', 'KR', 'KOR', NULL, 0, 1, 0, '2024-09-15 18:55:06', '2024-09-15 18:55:06');
INSERT INTO `countries` VALUES (207, 'South Sudan', 'South Sudan', 'SS', 'SSD', NULL, 0, 1, 0, '2024-09-15 18:55:06', '2024-09-15 18:55:06');
INSERT INTO `countries` VALUES (208, 'Spain', 'Espainia', 'ES', 'ESP', NULL, 0, 1, 0, '2024-09-15 18:55:06', '2024-09-15 18:55:06');
INSERT INTO `countries` VALUES (209, 'Sri Lanka', 'ශ්‍රී ලංකාව', 'LK', 'LKA', NULL, 0, 1, 0, '2024-09-15 18:55:06', '2024-09-15 18:55:06');
INSERT INTO `countries` VALUES (210, 'St. Barthelemy', 'Saint-Barthélemy', 'BL', 'BLM', NULL, 0, 1, 0, '2024-09-15 18:55:06', '2024-09-15 18:55:06');
INSERT INTO `countries` VALUES (211, 'St. Helena', 'Saint Helena', 'SH', 'SHN', NULL, 0, 1, 0, '2024-09-15 18:55:06', '2024-09-15 18:55:06');
INSERT INTO `countries` VALUES (212, 'St. Martin (French part)', 'Saint-Martin', 'MF', 'MAF', NULL, 0, 1, 0, '2024-09-15 18:55:06', '2024-09-15 18:55:06');
INSERT INTO `countries` VALUES (213, 'St. Pierre and Miquelon', 'Saint-Pierre-et-Miquelon', 'PM', 'SPM', NULL, 0, 1, 0, '2024-09-15 18:55:06', '2024-09-15 18:55:06');
INSERT INTO `countries` VALUES (214, 'Sudan', 'السودان', 'SD', 'SDN', NULL, 0, 1, 0, '2024-09-15 18:55:06', '2024-09-15 18:55:06');
INSERT INTO `countries` VALUES (215, 'Suriname', 'Suriname', 'SR', 'SUR', NULL, 0, 1, 0, '2024-09-15 18:55:06', '2024-09-15 18:55:06');
INSERT INTO `countries` VALUES (216, 'Svalbard and Jan Mayen Islands', 'Svalbard og Jan Mayen', 'SJ', 'SJM', NULL, 0, 1, 0, '2024-09-15 18:55:06', '2024-09-15 18:55:06');
INSERT INTO `countries` VALUES (217, 'Swaziland', 'Eswatini', 'SZ', 'SWZ', NULL, 0, 1, 0, '2024-09-15 18:55:06', '2024-09-15 18:55:06');
INSERT INTO `countries` VALUES (218, 'Sweden', 'Sverige', 'SE', 'SWE', NULL, 0, 1, 0, '2024-09-15 18:55:06', '2024-09-15 18:55:06');
INSERT INTO `countries` VALUES (219, 'Switzerland', 'Suisse', 'CH', 'CHE', NULL, 0, 1, 0, '2024-09-15 18:55:06', '2024-09-15 18:55:06');
INSERT INTO `countries` VALUES (220, 'Syrian Arab Republic', 'ﺎﻴﺮﻮﺳ', 'SY', 'SYR', NULL, 0, 1, 0, '2024-09-15 18:55:06', '2024-09-15 18:55:06');
INSERT INTO `countries` VALUES (221, 'Taiwan', '台灣', 'TW', 'TWN', NULL, 0, 1, 0, '2024-09-15 18:55:06', '2024-09-15 18:55:06');
INSERT INTO `countries` VALUES (222, 'Tajikistan', 'Тоҷикистон', 'TJ', 'TJK', NULL, 0, 1, 0, '2024-09-15 18:55:06', '2024-09-15 18:55:06');
INSERT INTO `countries` VALUES (223, 'Tanzania, United Republic of', 'Tanzania', 'TZ', 'TZA', NULL, 0, 1, 0, '2024-09-15 18:55:06', '2024-09-15 18:55:06');
INSERT INTO `countries` VALUES (224, 'Thailand', 'ไทย', 'TH', 'THA', NULL, 0, 1, 0, '2024-09-15 18:55:06', '2024-09-15 18:55:06');
INSERT INTO `countries` VALUES (225, 'Togo', 'Togo', 'TG', 'TGO', NULL, 0, 1, 0, '2024-09-15 18:55:06', '2024-09-15 18:55:06');
INSERT INTO `countries` VALUES (226, 'Tokelau', 'Tokelau', 'TK', 'TKL', NULL, 0, 1, 0, '2024-09-15 18:55:06', '2024-09-15 18:55:06');
INSERT INTO `countries` VALUES (227, 'Tonga', 'Tonga', 'TO', 'TON', NULL, 0, 1, 0, '2024-09-15 18:55:06', '2024-09-15 18:55:06');
INSERT INTO `countries` VALUES (228, 'Trinidad and Tobago', 'Trinidad and Tobago', 'TT', 'TTO', NULL, 0, 1, 0, '2024-09-15 18:55:06', '2024-09-15 18:55:06');
INSERT INTO `countries` VALUES (229, 'Tristan da Cunha', 'Tristan da Cunha', 'TA', 'SHN', NULL, 0, 1, 0, '2024-09-15 18:55:06', '2024-09-15 18:55:06');
INSERT INTO `countries` VALUES (230, 'Tunisia', 'ﺲﻨﻮﺗ', 'TN', 'TUN', NULL, 0, 1, 0, '2024-09-15 18:55:06', '2024-09-15 18:55:06');
INSERT INTO `countries` VALUES (231, 'Turkey', 'Türkiye', 'TR', 'TUR', NULL, 0, 1, 0, '2024-09-15 18:55:06', '2024-09-15 18:55:06');
INSERT INTO `countries` VALUES (232, 'Turkmenistan', 'Türkmenistan', 'TM', 'TKM', NULL, 0, 1, 0, '2024-09-15 18:55:06', '2024-09-15 18:55:06');
INSERT INTO `countries` VALUES (233, 'Turks and Caicos Islands', 'Turks and Caicos Islands', 'TC', 'TCA', NULL, 0, 1, 0, '2024-09-15 18:55:06', '2024-09-15 18:55:06');
INSERT INTO `countries` VALUES (234, 'Tuvalu', 'Tuvalu', 'TV', 'TUV', NULL, 0, 1, 0, '2024-09-15 18:55:06', '2024-09-15 18:55:06');
INSERT INTO `countries` VALUES (235, 'Uganda', 'Uganda', 'UG', 'UGA', NULL, 0, 1, 0, '2024-09-15 18:55:06', '2024-09-15 18:55:06');
INSERT INTO `countries` VALUES (236, 'Ukraine', 'Україна', 'UA', 'UKR', NULL, 0, 1, 0, '2024-09-15 18:55:06', '2024-09-15 18:55:06');
INSERT INTO `countries` VALUES (237, 'United Arab Emirates', 'ﺔﺪﺤﺘﻤﻠﺍ ﺔﻴﺐﺮﻌﻠﺍ ﺖﺎﺮﺎﻤﺈﻠﺍ', 'AE', 'ARE', NULL, 0, 1, 0, '2024-09-15 18:55:06', '2024-09-15 18:55:06');
INSERT INTO `countries` VALUES (238, 'United Kingdom', 'United Kingdom', 'GB', 'GBR', NULL, 0, 1, 0, '2024-09-15 18:55:06', '2024-09-15 18:55:06');
INSERT INTO `countries` VALUES (239, 'United States', 'United States', 'US', 'USA', NULL, 0, 1, 0, '2024-09-15 18:55:06', '2024-09-15 18:55:06');
INSERT INTO `countries` VALUES (240, 'United States Minor Outlying Islands', 'United States Minor Outlying Islands', 'UM', 'UMI', NULL, 0, 1, 0, '2024-09-15 18:55:06', '2024-09-15 18:55:06');
INSERT INTO `countries` VALUES (241, 'Uruguay', 'Uruguay', 'UY', 'URY', NULL, 0, 1, 0, '2024-09-15 18:55:06', '2024-09-15 18:55:06');
INSERT INTO `countries` VALUES (242, 'Uzbekistan', 'Oʻzbekiston', 'UZ', 'UZB', NULL, 0, 1, 0, '2024-09-15 18:55:06', '2024-09-15 18:55:06');
INSERT INTO `countries` VALUES (243, 'Vanuatu', 'Vanuatu', 'VU', 'VUT', NULL, 0, 1, 0, '2024-09-15 18:55:06', '2024-09-15 18:55:06');
INSERT INTO `countries` VALUES (244, 'Vatican City State (Holy See)', 'Città del Vaticano', 'VA', 'VAT', NULL, 0, 1, 0, '2024-09-15 18:55:06', '2024-09-15 18:55:06');
INSERT INTO `countries` VALUES (245, 'Venezuela', 'Venezuela', 'VE', 'VEN', NULL, 0, 1, 0, '2024-09-15 18:55:06', '2024-09-15 18:55:06');
INSERT INTO `countries` VALUES (246, 'Viet Nam', 'Việt Nam', 'VN', 'VNM', NULL, 0, 1, 0, '2024-09-15 18:55:06', '2024-09-15 18:55:06');
INSERT INTO `countries` VALUES (247, 'Virgin Islands (British)', 'British Virgin Islands', 'VG', 'VGB', NULL, 0, 1, 0, '2024-09-15 18:55:06', '2024-09-15 18:55:06');
INSERT INTO `countries` VALUES (248, 'Virgin Islands (U.S.)', 'U.S. Virgin Islands', 'VI', 'VIR', NULL, 0, 1, 0, '2024-09-15 18:55:06', '2024-09-15 18:55:06');
INSERT INTO `countries` VALUES (249, 'Wallis and Futuna Islands', 'Wallis-et-Futuna', 'WF', 'WLF', NULL, 0, 1, 0, '2024-09-15 18:55:06', '2024-09-15 18:55:06');
INSERT INTO `countries` VALUES (250, 'Western Sahara', 'الصحراء الغربية', 'EH', 'ESH', NULL, 0, 1, 0, '2024-09-15 18:55:06', '2024-09-15 18:55:06');
INSERT INTO `countries` VALUES (251, 'Yemen', 'ﻦﻤﻴﻠﺍ', 'YE', 'YEM', NULL, 0, 1, 0, '2024-09-15 18:55:06', '2024-09-15 18:55:06');
INSERT INTO `countries` VALUES (252, 'Zambia', 'Zambia', 'ZM', 'ZMB', NULL, 0, 1, 0, '2024-09-15 18:55:06', '2024-09-15 18:55:06');
INSERT INTO `countries` VALUES (253, 'Zimbabwe', 'Zimbabwe', 'ZW', 'ZWE', NULL, 0, 1, 0, '2024-09-15 18:55:06', '2024-09-15 18:55:06');
INSERT INTO `countries` VALUES (254, 'test', '測試', 'ZT', 'ZTS', NULL, 0, 1, 0, '2025-11-29 08:30:42', '2025-11-29 08:30:42');

-- ----------------------------
-- Table structure for divisions
-- ----------------------------
DROP TABLE IF EXISTS `divisions`;
CREATE TABLE `divisions`  (
  `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT,
  `code` varchar(32) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NULL DEFAULT NULL,
  `country_code` varchar(2) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL COMMENT 'iso_code_2',
  `parent_id` bigint UNSIGNED NULL DEFAULT NULL,
  `level` tinyint NOT NULL,
  `name` varchar(128) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `native_name` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NULL DEFAULT NULL,
  `is_active` tinyint(1) NOT NULL DEFAULT 1,
  `sort_order` int NOT NULL DEFAULT 0,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`) USING BTREE,
  INDEX `divisions_country_code_is_active_index`(`country_code` ASC, `is_active` ASC) USING BTREE,
  INDEX `divisions_sort_order_index`(`sort_order` ASC) USING BTREE,
  CONSTRAINT `divisions_country_code_foreign` FOREIGN KEY (`country_code`) REFERENCES `countries` (`iso_code_2`) ON DELETE CASCADE ON UPDATE RESTRICT
) ENGINE = InnoDB AUTO_INCREMENT = 397 CHARACTER SET = utf8mb4 COLLATE = utf8mb4_unicode_ci ROW_FORMAT = Dynamic;

-- ----------------------------
-- Records of divisions
-- ----------------------------
INSERT INTO `divisions` VALUES (1, 'KEE', 'TW', 0, 1, '基隆市', '基隆市', 1, 1, NULL, NULL);
INSERT INTO `divisions` VALUES (2, 'TPE', 'TW', 0, 1, '臺北市', '臺北市', 1, 2, NULL, NULL);
INSERT INTO `divisions` VALUES (3, 'NWT', 'TW', 0, 1, '新北市', '新北市', 1, 3, NULL, NULL);
INSERT INTO `divisions` VALUES (4, 'KHH', 'TW', 0, 1, '桃園市', '桃園市', 1, 4, NULL, NULL);
INSERT INTO `divisions` VALUES (5, 'HSZ', 'TW', 0, 1, '新竹市', '新竹市', 1, 5, NULL, NULL);
INSERT INTO `divisions` VALUES (6, 'HSQ', 'TW', 0, 1, '新竹縣', '新竹縣', 1, 6, NULL, NULL);
INSERT INTO `divisions` VALUES (7, 'MIA', 'TW', 0, 1, '苗栗縣', '苗栗縣', 1, 7, NULL, NULL);
INSERT INTO `divisions` VALUES (8, 'TXG', 'TW', 0, 1, '臺中市', '臺中市', 1, 8, NULL, NULL);
INSERT INTO `divisions` VALUES (9, 'CHA', 'TW', 0, 1, '彰化縣', '彰化縣', 1, 9, NULL, NULL);
INSERT INTO `divisions` VALUES (10, 'NAN', 'TW', 0, 1, '南投縣', '南投縣', 1, 10, NULL, NULL);
INSERT INTO `divisions` VALUES (11, 'YUN', 'TW', 0, 1, '雲林縣', '雲林縣', 1, 11, NULL, NULL);
INSERT INTO `divisions` VALUES (12, 'CYI', 'TW', 0, 1, '嘉義市', '嘉義市', 1, 12, NULL, NULL);
INSERT INTO `divisions` VALUES (13, 'CYQ', 'TW', 0, 1, '嘉義縣', '嘉義縣', 1, 13, NULL, NULL);
INSERT INTO `divisions` VALUES (14, 'TNN', 'TW', 0, 1, '臺南市', '臺南市', 1, 14, NULL, NULL);
INSERT INTO `divisions` VALUES (15, 'KHH', 'TW', 0, 1, '高雄市', '高雄市', 1, 15, NULL, NULL);
INSERT INTO `divisions` VALUES (16, 'PIF', 'TW', 0, 1, '屏東縣', '屏東縣', 1, 16, NULL, NULL);
INSERT INTO `divisions` VALUES (17, 'TTT', 'TW', 0, 1, '臺東縣', '臺東縣', 1, 17, NULL, NULL);
INSERT INTO `divisions` VALUES (18, 'HUA', 'TW', 0, 1, '花蓮縣', '花蓮縣', 1, 18, NULL, NULL);
INSERT INTO `divisions` VALUES (19, 'ILA', 'TW', 0, 1, '宜蘭縣', '宜蘭縣', 1, 19, NULL, NULL);
INSERT INTO `divisions` VALUES (20, 'PEN', 'TW', 0, 1, '澎湖縣', '澎湖縣', 1, 20, NULL, NULL);
INSERT INTO `divisions` VALUES (21, 'KIN', 'TW', 0, 1, '金門縣', '金門縣', 1, 21, NULL, NULL);
INSERT INTO `divisions` VALUES (22, 'LIE', 'TW', 0, 1, '連江縣', '連江縣', 1, 22, NULL, NULL);
INSERT INTO `divisions` VALUES (23, '', 'TW', 1, 2, '仁愛區', '仁愛區', 1, 1, NULL, NULL);
INSERT INTO `divisions` VALUES (24, '', 'TW', 1, 2, '信義區', '信義區', 1, 2, NULL, NULL);
INSERT INTO `divisions` VALUES (25, '', 'TW', 1, 2, '中正區', '中正區', 1, 3, NULL, NULL);
INSERT INTO `divisions` VALUES (26, '', 'TW', 1, 2, '中山區', '中山區', 1, 4, NULL, NULL);
INSERT INTO `divisions` VALUES (27, '', 'TW', 1, 2, '安樂區', '安樂區', 1, 5, NULL, NULL);
INSERT INTO `divisions` VALUES (28, '', 'TW', 1, 2, '暖暖區', '暖暖區', 1, 6, NULL, NULL);
INSERT INTO `divisions` VALUES (29, '', 'TW', 1, 2, '七堵區', '七堵區', 1, 7, NULL, NULL);
INSERT INTO `divisions` VALUES (30, '', 'TW', 2, 2, '中正區', '中正區', 1, 1, NULL, NULL);
INSERT INTO `divisions` VALUES (31, '', 'TW', 2, 2, '大同區', '大同區', 1, 2, NULL, NULL);
INSERT INTO `divisions` VALUES (32, '', 'TW', 2, 2, '中山區', '中山區', 1, 3, NULL, NULL);
INSERT INTO `divisions` VALUES (33, '', 'TW', 2, 2, '松山區', '松山區', 1, 4, NULL, NULL);
INSERT INTO `divisions` VALUES (34, '', 'TW', 2, 2, '大安區', '大安區', 1, 5, NULL, NULL);
INSERT INTO `divisions` VALUES (35, '', 'TW', 2, 2, '萬華區', '萬華區', 1, 6, NULL, NULL);
INSERT INTO `divisions` VALUES (36, '', 'TW', 2, 2, '信義區', '信義區', 1, 7, NULL, NULL);
INSERT INTO `divisions` VALUES (37, '', 'TW', 2, 2, '士林區', '士林區', 1, 8, NULL, NULL);
INSERT INTO `divisions` VALUES (38, '', 'TW', 2, 2, '北投區', '北投區', 1, 9, NULL, NULL);
INSERT INTO `divisions` VALUES (39, '', 'TW', 2, 2, '內湖區', '內湖區', 1, 10, NULL, NULL);
INSERT INTO `divisions` VALUES (40, '', 'TW', 2, 2, '南港區', '南港區', 1, 11, NULL, NULL);
INSERT INTO `divisions` VALUES (41, '', 'TW', 2, 2, '文山區', '文山區', 1, 12, NULL, NULL);
INSERT INTO `divisions` VALUES (42, '', 'TW', 3, 2, '萬里區', '萬里區', 1, 1, NULL, NULL);
INSERT INTO `divisions` VALUES (43, '', 'TW', 3, 2, '金山區', '金山區', 1, 2, NULL, NULL);
INSERT INTO `divisions` VALUES (44, '', 'TW', 3, 2, '板橋區', '板橋區', 1, 3, NULL, NULL);
INSERT INTO `divisions` VALUES (45, '', 'TW', 3, 2, '汐止區', '汐止區', 1, 4, NULL, NULL);
INSERT INTO `divisions` VALUES (46, '', 'TW', 3, 2, '深坑區', '深坑區', 1, 5, NULL, NULL);
INSERT INTO `divisions` VALUES (47, '', 'TW', 3, 2, '石碇區', '石碇區', 1, 6, NULL, NULL);
INSERT INTO `divisions` VALUES (48, '', 'TW', 3, 2, '瑞芳區', '瑞芳區', 1, 7, NULL, NULL);
INSERT INTO `divisions` VALUES (49, '', 'TW', 3, 2, '平溪區', '平溪區', 1, 8, NULL, NULL);
INSERT INTO `divisions` VALUES (50, '', 'TW', 3, 2, '雙溪區', '雙溪區', 1, 9, NULL, NULL);
INSERT INTO `divisions` VALUES (51, '', 'TW', 3, 2, '貢寮區', '貢寮區', 1, 10, NULL, NULL);
INSERT INTO `divisions` VALUES (52, '', 'TW', 3, 2, '新店區', '新店區', 1, 11, NULL, NULL);
INSERT INTO `divisions` VALUES (53, '', 'TW', 3, 2, '坪林區', '坪林區', 1, 12, NULL, NULL);
INSERT INTO `divisions` VALUES (54, '', 'TW', 3, 2, '烏來區', '烏來區', 1, 13, NULL, NULL);
INSERT INTO `divisions` VALUES (55, '', 'TW', 3, 2, '永和區', '永和區', 1, 14, NULL, NULL);
INSERT INTO `divisions` VALUES (56, '', 'TW', 3, 2, '中和區', '中和區', 1, 15, NULL, NULL);
INSERT INTO `divisions` VALUES (57, '', 'TW', 3, 2, '土城區', '土城區', 1, 16, NULL, NULL);
INSERT INTO `divisions` VALUES (58, '', 'TW', 3, 2, '三峽區', '三峽區', 1, 17, NULL, NULL);
INSERT INTO `divisions` VALUES (59, '', 'TW', 3, 2, '樹林區', '樹林區', 1, 18, NULL, NULL);
INSERT INTO `divisions` VALUES (60, '', 'TW', 3, 2, '鶯歌區', '鶯歌區', 1, 19, NULL, NULL);
INSERT INTO `divisions` VALUES (61, '', 'TW', 3, 2, '三重區', '三重區', 1, 20, NULL, NULL);
INSERT INTO `divisions` VALUES (62, '', 'TW', 3, 2, '新莊區', '新莊區', 1, 21, NULL, NULL);
INSERT INTO `divisions` VALUES (63, '', 'TW', 3, 2, '泰山區', '泰山區', 1, 22, NULL, NULL);
INSERT INTO `divisions` VALUES (64, '', 'TW', 3, 2, '林口區', '林口區', 1, 23, NULL, NULL);
INSERT INTO `divisions` VALUES (65, '', 'TW', 3, 2, '蘆洲區', '蘆洲區', 1, 24, NULL, NULL);
INSERT INTO `divisions` VALUES (66, '', 'TW', 3, 2, '五股區', '五股區', 1, 25, NULL, NULL);
INSERT INTO `divisions` VALUES (67, '', 'TW', 3, 2, '八里區', '八里區', 1, 26, NULL, NULL);
INSERT INTO `divisions` VALUES (68, '', 'TW', 3, 2, '淡水區', '淡水區', 1, 27, NULL, NULL);
INSERT INTO `divisions` VALUES (69, '', 'TW', 3, 2, '三芝區', '三芝區', 1, 28, NULL, NULL);
INSERT INTO `divisions` VALUES (70, '', 'TW', 3, 2, '石門區', '石門區', 1, 29, NULL, NULL);
INSERT INTO `divisions` VALUES (71, '', 'TW', 4, 2, '中壢區', '中壢區', 1, 1, NULL, NULL);
INSERT INTO `divisions` VALUES (72, '', 'TW', 4, 2, '平鎮區', '平鎮區', 1, 2, NULL, NULL);
INSERT INTO `divisions` VALUES (73, '', 'TW', 4, 2, '龍潭區', '龍潭區', 1, 3, NULL, NULL);
INSERT INTO `divisions` VALUES (74, '', 'TW', 4, 2, '楊梅區', '楊梅區', 1, 4, NULL, NULL);
INSERT INTO `divisions` VALUES (75, '', 'TW', 4, 2, '新屋區', '新屋區', 1, 5, NULL, NULL);
INSERT INTO `divisions` VALUES (76, '', 'TW', 4, 2, '觀音區', '觀音區', 1, 6, NULL, NULL);
INSERT INTO `divisions` VALUES (77, '', 'TW', 4, 2, '桃園區', '桃園區', 1, 7, NULL, NULL);
INSERT INTO `divisions` VALUES (78, '', 'TW', 4, 2, '龜山區', '龜山區', 1, 8, NULL, NULL);
INSERT INTO `divisions` VALUES (79, '', 'TW', 4, 2, '八德區', '八德區', 1, 9, NULL, NULL);
INSERT INTO `divisions` VALUES (80, '', 'TW', 4, 2, '大溪區', '大溪區', 1, 10, NULL, NULL);
INSERT INTO `divisions` VALUES (81, '', 'TW', 4, 2, '復興區', '復興區', 1, 11, NULL, NULL);
INSERT INTO `divisions` VALUES (82, '', 'TW', 4, 2, '大園區', '大園區', 1, 12, NULL, NULL);
INSERT INTO `divisions` VALUES (83, '', 'TW', 4, 2, '蘆竹區', '蘆竹區', 1, 13, NULL, NULL);
INSERT INTO `divisions` VALUES (84, '', 'TW', 5, 2, '東區', '東區', 1, 1, NULL, NULL);
INSERT INTO `divisions` VALUES (85, '', 'TW', 5, 2, '北區', '北區', 1, 2, NULL, NULL);
INSERT INTO `divisions` VALUES (86, '', 'TW', 5, 2, '香山區', '香山區', 1, 3, NULL, NULL);
INSERT INTO `divisions` VALUES (87, '', 'TW', 6, 2, '竹北市', '竹北市', 1, 1, NULL, NULL);
INSERT INTO `divisions` VALUES (88, '', 'TW', 6, 2, '湖口鄉', '湖口鄉', 1, 2, NULL, NULL);
INSERT INTO `divisions` VALUES (89, '', 'TW', 6, 2, '新豐鄉', '新豐鄉', 1, 3, NULL, NULL);
INSERT INTO `divisions` VALUES (90, '', 'TW', 6, 2, '新埔鎮', '新埔鎮', 1, 4, NULL, NULL);
INSERT INTO `divisions` VALUES (91, '', 'TW', 6, 2, '關西鎮', '關西鎮', 1, 5, NULL, NULL);
INSERT INTO `divisions` VALUES (92, '', 'TW', 6, 2, '芎林鄉', '芎林鄉', 1, 6, NULL, NULL);
INSERT INTO `divisions` VALUES (93, '', 'TW', 6, 2, '寶山鄉', '寶山鄉', 1, 7, NULL, NULL);
INSERT INTO `divisions` VALUES (94, '', 'TW', 6, 2, '竹東鎮', '竹東鎮', 1, 8, NULL, NULL);
INSERT INTO `divisions` VALUES (95, '', 'TW', 6, 2, '五峰鄉', '五峰鄉', 1, 9, NULL, NULL);
INSERT INTO `divisions` VALUES (96, '', 'TW', 6, 2, '橫山鄉', '橫山鄉', 1, 10, NULL, NULL);
INSERT INTO `divisions` VALUES (97, '', 'TW', 6, 2, '尖石鄉', '尖石鄉', 1, 11, NULL, NULL);
INSERT INTO `divisions` VALUES (98, '', 'TW', 6, 2, '北埔鄉', '北埔鄉', 1, 12, NULL, NULL);
INSERT INTO `divisions` VALUES (99, '', 'TW', 6, 2, '峨眉鄉', '峨眉鄉', 1, 13, NULL, NULL);
INSERT INTO `divisions` VALUES (100, '', 'TW', 7, 2, '竹南鎮', '竹南鎮', 1, 1, NULL, NULL);
INSERT INTO `divisions` VALUES (101, '', 'TW', 7, 2, '頭份市', '頭份市', 1, 2, NULL, NULL);
INSERT INTO `divisions` VALUES (102, '', 'TW', 7, 2, '三灣鄉', '三灣鄉', 1, 3, NULL, NULL);
INSERT INTO `divisions` VALUES (103, '', 'TW', 7, 2, '南庄鄉', '南庄鄉', 1, 4, NULL, NULL);
INSERT INTO `divisions` VALUES (104, '', 'TW', 7, 2, '獅潭鄉', '獅潭鄉', 1, 5, NULL, NULL);
INSERT INTO `divisions` VALUES (105, '', 'TW', 7, 2, '後龍鎮', '後龍鎮', 1, 6, NULL, NULL);
INSERT INTO `divisions` VALUES (106, '', 'TW', 7, 2, '通霄鎮', '通霄鎮', 1, 7, NULL, NULL);
INSERT INTO `divisions` VALUES (107, '', 'TW', 7, 2, '苑裡鎮', '苑裡鎮', 1, 8, NULL, NULL);
INSERT INTO `divisions` VALUES (108, '', 'TW', 7, 2, '苗栗市', '苗栗市', 1, 9, NULL, NULL);
INSERT INTO `divisions` VALUES (109, '', 'TW', 7, 2, '造橋鄉', '造橋鄉', 1, 10, NULL, NULL);
INSERT INTO `divisions` VALUES (110, '', 'TW', 7, 2, '頭屋鄉', '頭屋鄉', 1, 11, NULL, NULL);
INSERT INTO `divisions` VALUES (111, '', 'TW', 7, 2, '公館鄉', '公館鄉', 1, 12, NULL, NULL);
INSERT INTO `divisions` VALUES (112, '', 'TW', 7, 2, '大湖鄉', '大湖鄉', 1, 13, NULL, NULL);
INSERT INTO `divisions` VALUES (113, '', 'TW', 7, 2, '泰安鄉', '泰安鄉', 1, 14, NULL, NULL);
INSERT INTO `divisions` VALUES (114, '', 'TW', 7, 2, '銅鑼鄉', '銅鑼鄉', 1, 15, NULL, NULL);
INSERT INTO `divisions` VALUES (115, '', 'TW', 7, 2, '三義鄉', '三義鄉', 1, 16, NULL, NULL);
INSERT INTO `divisions` VALUES (116, '', 'TW', 7, 2, '西湖鄉', '西湖鄉', 1, 17, NULL, NULL);
INSERT INTO `divisions` VALUES (117, '', 'TW', 7, 2, '卓蘭鎮', '卓蘭鎮', 1, 18, NULL, NULL);
INSERT INTO `divisions` VALUES (118, '', 'TW', 8, 2, '中區', '中區', 1, 1, NULL, NULL);
INSERT INTO `divisions` VALUES (119, '', 'TW', 8, 2, '東區', '東區', 1, 2, NULL, NULL);
INSERT INTO `divisions` VALUES (120, '', 'TW', 8, 2, '南區', '南區', 1, 3, NULL, NULL);
INSERT INTO `divisions` VALUES (121, '', 'TW', 8, 2, '西區', '西區', 1, 4, NULL, NULL);
INSERT INTO `divisions` VALUES (122, '', 'TW', 8, 2, '北區', '北區', 1, 5, NULL, NULL);
INSERT INTO `divisions` VALUES (123, '', 'TW', 8, 2, '北屯區', '北屯區', 1, 6, NULL, NULL);
INSERT INTO `divisions` VALUES (124, '', 'TW', 8, 2, '西屯區', '西屯區', 1, 7, NULL, NULL);
INSERT INTO `divisions` VALUES (125, '', 'TW', 8, 2, '南屯區', '南屯區', 1, 8, NULL, NULL);
INSERT INTO `divisions` VALUES (126, '', 'TW', 8, 2, '太平區', '太平區', 1, 9, NULL, NULL);
INSERT INTO `divisions` VALUES (127, '', 'TW', 8, 2, '大里區', '大里區', 1, 10, NULL, NULL);
INSERT INTO `divisions` VALUES (128, '', 'TW', 8, 2, '霧峰區', '霧峰區', 1, 11, NULL, NULL);
INSERT INTO `divisions` VALUES (129, '', 'TW', 8, 2, '烏日區', '烏日區', 1, 12, NULL, NULL);
INSERT INTO `divisions` VALUES (130, '', 'TW', 8, 2, '豐原區', '豐原區', 1, 13, NULL, NULL);
INSERT INTO `divisions` VALUES (131, '', 'TW', 8, 2, '后里區', '后里區', 1, 14, NULL, NULL);
INSERT INTO `divisions` VALUES (132, '', 'TW', 8, 2, '石岡區', '石岡區', 1, 15, NULL, NULL);
INSERT INTO `divisions` VALUES (133, '', 'TW', 8, 2, '東勢區', '東勢區', 1, 16, NULL, NULL);
INSERT INTO `divisions` VALUES (134, '', 'TW', 8, 2, '和平區', '和平區', 1, 17, NULL, NULL);
INSERT INTO `divisions` VALUES (135, '', 'TW', 8, 2, '新社區', '新社區', 1, 18, NULL, NULL);
INSERT INTO `divisions` VALUES (136, '', 'TW', 8, 2, '潭子區', '潭子區', 1, 19, NULL, NULL);
INSERT INTO `divisions` VALUES (137, '', 'TW', 8, 2, '大雅區', '大雅區', 1, 20, NULL, NULL);
INSERT INTO `divisions` VALUES (138, '', 'TW', 8, 2, '神岡區', '神岡區', 1, 21, NULL, NULL);
INSERT INTO `divisions` VALUES (139, '', 'TW', 8, 2, '大肚區', '大肚區', 1, 22, NULL, NULL);
INSERT INTO `divisions` VALUES (140, '', 'TW', 8, 2, '沙鹿區', '沙鹿區', 1, 23, NULL, NULL);
INSERT INTO `divisions` VALUES (141, '', 'TW', 8, 2, '龍井區', '龍井區', 1, 24, NULL, NULL);
INSERT INTO `divisions` VALUES (142, '', 'TW', 8, 2, '梧棲區', '梧棲區', 1, 25, NULL, NULL);
INSERT INTO `divisions` VALUES (143, '', 'TW', 8, 2, '清水區', '清水區', 1, 26, NULL, NULL);
INSERT INTO `divisions` VALUES (144, '', 'TW', 8, 2, '大甲區', '大甲區', 1, 27, NULL, NULL);
INSERT INTO `divisions` VALUES (145, '', 'TW', 8, 2, '外埔區', '外埔區', 1, 28, NULL, NULL);
INSERT INTO `divisions` VALUES (146, '', 'TW', 8, 2, '大安區', '大安區', 1, 29, NULL, NULL);
INSERT INTO `divisions` VALUES (147, '', 'TW', 9, 2, '彰化市', '彰化市', 1, 1, NULL, NULL);
INSERT INTO `divisions` VALUES (148, '', 'TW', 9, 2, '芬園鄉', '芬園鄉', 1, 2, NULL, NULL);
INSERT INTO `divisions` VALUES (149, '', 'TW', 9, 2, '花壇鄉', '花壇鄉', 1, 3, NULL, NULL);
INSERT INTO `divisions` VALUES (150, '', 'TW', 9, 2, '秀水鄉', '秀水鄉', 1, 4, NULL, NULL);
INSERT INTO `divisions` VALUES (151, '', 'TW', 9, 2, '鹿港鎮', '鹿港鎮', 1, 5, NULL, NULL);
INSERT INTO `divisions` VALUES (152, '', 'TW', 9, 2, '福興鄉', '福興鄉', 1, 6, NULL, NULL);
INSERT INTO `divisions` VALUES (153, '', 'TW', 9, 2, '線西鄉', '線西鄉', 1, 7, NULL, NULL);
INSERT INTO `divisions` VALUES (154, '', 'TW', 9, 2, '和美鎮', '和美鎮', 1, 8, NULL, NULL);
INSERT INTO `divisions` VALUES (155, '', 'TW', 9, 2, '伸港鄉', '伸港鄉', 1, 9, NULL, NULL);
INSERT INTO `divisions` VALUES (156, '', 'TW', 9, 2, '員林市', '員林市', 1, 10, NULL, NULL);
INSERT INTO `divisions` VALUES (157, '', 'TW', 9, 2, '社頭鄉', '社頭鄉', 1, 11, NULL, NULL);
INSERT INTO `divisions` VALUES (158, '', 'TW', 9, 2, '永靖鄉', '永靖鄉', 1, 12, NULL, NULL);
INSERT INTO `divisions` VALUES (159, '', 'TW', 9, 2, '埔心鄉', '埔心鄉', 1, 13, NULL, NULL);
INSERT INTO `divisions` VALUES (160, '', 'TW', 9, 2, '溪湖鎮', '溪湖鎮', 1, 14, NULL, NULL);
INSERT INTO `divisions` VALUES (161, '', 'TW', 9, 2, '大村鄉', '大村鄉', 1, 15, NULL, NULL);
INSERT INTO `divisions` VALUES (162, '', 'TW', 9, 2, '埔鹽鄉', '埔鹽鄉', 1, 16, NULL, NULL);
INSERT INTO `divisions` VALUES (163, '', 'TW', 9, 2, '田中鎮', '田中鎮', 1, 17, NULL, NULL);
INSERT INTO `divisions` VALUES (164, '', 'TW', 9, 2, '北斗鎮', '北斗鎮', 1, 18, NULL, NULL);
INSERT INTO `divisions` VALUES (165, '', 'TW', 9, 2, '田尾鄉', '田尾鄉', 1, 19, NULL, NULL);
INSERT INTO `divisions` VALUES (166, '', 'TW', 9, 2, '埤頭鄉', '埤頭鄉', 1, 20, NULL, NULL);
INSERT INTO `divisions` VALUES (167, '', 'TW', 9, 2, '溪州鄉', '溪州鄉', 1, 21, NULL, NULL);
INSERT INTO `divisions` VALUES (168, '', 'TW', 9, 2, '竹塘鄉', '竹塘鄉', 1, 22, NULL, NULL);
INSERT INTO `divisions` VALUES (169, '', 'TW', 9, 2, '二林鎮', '二林鎮', 1, 23, NULL, NULL);
INSERT INTO `divisions` VALUES (170, '', 'TW', 9, 2, '大城鄉', '大城鄉', 1, 24, NULL, NULL);
INSERT INTO `divisions` VALUES (171, '', 'TW', 9, 2, '芳苑鄉', '芳苑鄉', 1, 25, NULL, NULL);
INSERT INTO `divisions` VALUES (172, '', 'TW', 9, 2, '二水鄉', '二水鄉', 1, 26, NULL, NULL);
INSERT INTO `divisions` VALUES (173, '', 'TW', 10, 2, '南投市', '南投市', 1, 1, NULL, NULL);
INSERT INTO `divisions` VALUES (174, '', 'TW', 10, 2, '中寮鄉', '中寮鄉', 1, 2, NULL, NULL);
INSERT INTO `divisions` VALUES (175, '', 'TW', 10, 2, '草屯鎮', '草屯鎮', 1, 3, NULL, NULL);
INSERT INTO `divisions` VALUES (176, '', 'TW', 10, 2, '國姓鄉', '國姓鄉', 1, 4, NULL, NULL);
INSERT INTO `divisions` VALUES (177, '', 'TW', 10, 2, '埔里鎮', '埔里鎮', 1, 5, NULL, NULL);
INSERT INTO `divisions` VALUES (178, '', 'TW', 10, 2, '仁愛鄉', '仁愛鄉', 1, 6, NULL, NULL);
INSERT INTO `divisions` VALUES (179, '', 'TW', 10, 2, '名間鄉', '名間鄉', 1, 7, NULL, NULL);
INSERT INTO `divisions` VALUES (180, '', 'TW', 10, 2, '集集鎮', '集集鎮', 1, 8, NULL, NULL);
INSERT INTO `divisions` VALUES (181, '', 'TW', 10, 2, '水里鄉', '水里鄉', 1, 9, NULL, NULL);
INSERT INTO `divisions` VALUES (182, '', 'TW', 10, 2, '魚池鄉', '魚池鄉', 1, 10, NULL, NULL);
INSERT INTO `divisions` VALUES (183, '', 'TW', 10, 2, '信義鄉', '信義鄉', 1, 11, NULL, NULL);
INSERT INTO `divisions` VALUES (184, '', 'TW', 10, 2, '竹山鎮', '竹山鎮', 1, 12, NULL, NULL);
INSERT INTO `divisions` VALUES (185, '', 'TW', 10, 2, '鹿谷鄉', '鹿谷鄉', 1, 13, NULL, NULL);
INSERT INTO `divisions` VALUES (186, '', 'TW', 11, 2, '斗南鎮', '斗南鎮', 1, 1, NULL, NULL);
INSERT INTO `divisions` VALUES (187, '', 'TW', 11, 2, '大埤鄉', '大埤鄉', 1, 2, NULL, NULL);
INSERT INTO `divisions` VALUES (188, '', 'TW', 11, 2, '虎尾鎮', '虎尾鎮', 1, 3, NULL, NULL);
INSERT INTO `divisions` VALUES (189, '', 'TW', 11, 2, '土庫鎮', '土庫鎮', 1, 4, NULL, NULL);
INSERT INTO `divisions` VALUES (190, '', 'TW', 11, 2, '褒忠鄉', '褒忠鄉', 1, 5, NULL, NULL);
INSERT INTO `divisions` VALUES (191, '', 'TW', 11, 2, '東勢鄉', '東勢鄉', 1, 6, NULL, NULL);
INSERT INTO `divisions` VALUES (192, '', 'TW', 11, 2, '臺西鄉', '臺西鄉', 1, 7, NULL, NULL);
INSERT INTO `divisions` VALUES (193, '', 'TW', 11, 2, '崙背鄉', '崙背鄉', 1, 8, NULL, NULL);
INSERT INTO `divisions` VALUES (194, '', 'TW', 11, 2, '麥寮鄉', '麥寮鄉', 1, 9, NULL, NULL);
INSERT INTO `divisions` VALUES (195, '', 'TW', 11, 2, '斗六市', '斗六市', 1, 10, NULL, NULL);
INSERT INTO `divisions` VALUES (196, '', 'TW', 11, 2, '林內鄉', '林內鄉', 1, 11, NULL, NULL);
INSERT INTO `divisions` VALUES (197, '', 'TW', 11, 2, '古坑鄉', '古坑鄉', 1, 12, NULL, NULL);
INSERT INTO `divisions` VALUES (198, '', 'TW', 11, 2, '莿桐鄉', '莿桐鄉', 1, 13, NULL, NULL);
INSERT INTO `divisions` VALUES (199, '', 'TW', 11, 2, '西螺鎮', '西螺鎮', 1, 14, NULL, NULL);
INSERT INTO `divisions` VALUES (200, '', 'TW', 11, 2, '二崙鄉', '二崙鄉', 1, 15, NULL, NULL);
INSERT INTO `divisions` VALUES (201, '', 'TW', 11, 2, '北港鎮', '北港鎮', 1, 16, NULL, NULL);
INSERT INTO `divisions` VALUES (202, '', 'TW', 11, 2, '水林鄉', '水林鄉', 1, 17, NULL, NULL);
INSERT INTO `divisions` VALUES (203, '', 'TW', 11, 2, '口湖鄉', '口湖鄉', 1, 18, NULL, NULL);
INSERT INTO `divisions` VALUES (204, '', 'TW', 11, 2, '四湖鄉', '四湖鄉', 1, 19, NULL, NULL);
INSERT INTO `divisions` VALUES (205, '', 'TW', 11, 2, '元長鄉', '元長鄉', 1, 20, NULL, NULL);
INSERT INTO `divisions` VALUES (206, '', 'TW', 12, 2, '東區', '東區', 1, 1, NULL, NULL);
INSERT INTO `divisions` VALUES (207, '', 'TW', 12, 2, '西區', '西區', 1, 2, NULL, NULL);
INSERT INTO `divisions` VALUES (208, '', 'TW', 13, 2, '番路鄉', '番路鄉', 1, 1, NULL, NULL);
INSERT INTO `divisions` VALUES (209, '', 'TW', 13, 2, '梅山鄉', '梅山鄉', 1, 2, NULL, NULL);
INSERT INTO `divisions` VALUES (210, '', 'TW', 13, 2, '竹崎鄉', '竹崎鄉', 1, 3, NULL, NULL);
INSERT INTO `divisions` VALUES (211, '', 'TW', 13, 2, '阿里山鄉', '阿里山鄉', 1, 4, NULL, NULL);
INSERT INTO `divisions` VALUES (212, '', 'TW', 13, 2, '中埔鄉', '中埔鄉', 1, 5, NULL, NULL);
INSERT INTO `divisions` VALUES (213, '', 'TW', 13, 2, '大埔鄉', '大埔鄉', 1, 6, NULL, NULL);
INSERT INTO `divisions` VALUES (214, '', 'TW', 13, 2, '水上鄉', '水上鄉', 1, 7, NULL, NULL);
INSERT INTO `divisions` VALUES (215, '', 'TW', 13, 2, '鹿草鄉', '鹿草鄉', 1, 8, NULL, NULL);
INSERT INTO `divisions` VALUES (216, '', 'TW', 13, 2, '太保市', '太保市', 1, 9, NULL, NULL);
INSERT INTO `divisions` VALUES (217, '', 'TW', 13, 2, '朴子市', '朴子市', 1, 10, NULL, NULL);
INSERT INTO `divisions` VALUES (218, '', 'TW', 13, 2, '東石鄉', '東石鄉', 1, 11, NULL, NULL);
INSERT INTO `divisions` VALUES (219, '', 'TW', 13, 2, '六腳鄉', '六腳鄉', 1, 12, NULL, NULL);
INSERT INTO `divisions` VALUES (220, '', 'TW', 13, 2, '新港鄉', '新港鄉', 1, 13, NULL, NULL);
INSERT INTO `divisions` VALUES (221, '', 'TW', 13, 2, '民雄鄉', '民雄鄉', 1, 14, NULL, NULL);
INSERT INTO `divisions` VALUES (222, '', 'TW', 13, 2, '大林鎮', '大林鎮', 1, 15, NULL, NULL);
INSERT INTO `divisions` VALUES (223, '', 'TW', 13, 2, '溪口鄉', '溪口鄉', 1, 16, NULL, NULL);
INSERT INTO `divisions` VALUES (224, '', 'TW', 13, 2, '義竹鄉', '義竹鄉', 1, 17, NULL, NULL);
INSERT INTO `divisions` VALUES (225, '', 'TW', 13, 2, '布袋鎮', '布袋鎮', 1, 18, NULL, NULL);
INSERT INTO `divisions` VALUES (226, '', 'TW', 14, 2, '中西區', '中西區', 1, 1, NULL, NULL);
INSERT INTO `divisions` VALUES (227, '', 'TW', 14, 2, '東區', '東區', 1, 2, NULL, NULL);
INSERT INTO `divisions` VALUES (228, '', 'TW', 14, 2, '南區', '南區', 1, 3, NULL, NULL);
INSERT INTO `divisions` VALUES (229, '', 'TW', 14, 2, '北區', '北區', 1, 4, NULL, NULL);
INSERT INTO `divisions` VALUES (230, '', 'TW', 14, 2, '安平區', '安平區', 1, 5, NULL, NULL);
INSERT INTO `divisions` VALUES (231, '', 'TW', 14, 2, '安南區', '安南區', 1, 6, NULL, NULL);
INSERT INTO `divisions` VALUES (232, '', 'TW', 14, 2, '永康區', '永康區', 1, 7, NULL, NULL);
INSERT INTO `divisions` VALUES (233, '', 'TW', 14, 2, '歸仁區', '歸仁區', 1, 8, NULL, NULL);
INSERT INTO `divisions` VALUES (234, '', 'TW', 14, 2, '新化區', '新化區', 1, 9, NULL, NULL);
INSERT INTO `divisions` VALUES (235, '', 'TW', 14, 2, '左鎮區', '左鎮區', 1, 10, NULL, NULL);
INSERT INTO `divisions` VALUES (236, '', 'TW', 14, 2, '玉井區', '玉井區', 1, 11, NULL, NULL);
INSERT INTO `divisions` VALUES (237, '', 'TW', 14, 2, '楠西區', '楠西區', 1, 12, NULL, NULL);
INSERT INTO `divisions` VALUES (238, '', 'TW', 14, 2, '南化區', '南化區', 1, 13, NULL, NULL);
INSERT INTO `divisions` VALUES (239, '', 'TW', 14, 2, '仁德區', '仁德區', 1, 14, NULL, NULL);
INSERT INTO `divisions` VALUES (240, '', 'TW', 14, 2, '關廟區', '關廟區', 1, 15, NULL, NULL);
INSERT INTO `divisions` VALUES (241, '', 'TW', 14, 2, '龍崎區', '龍崎區', 1, 16, NULL, NULL);
INSERT INTO `divisions` VALUES (242, '', 'TW', 14, 2, '官田區', '官田區', 1, 17, NULL, NULL);
INSERT INTO `divisions` VALUES (243, '', 'TW', 14, 2, '麻豆區', '麻豆區', 1, 18, NULL, NULL);
INSERT INTO `divisions` VALUES (244, '', 'TW', 14, 2, '佳里區', '佳里區', 1, 19, NULL, NULL);
INSERT INTO `divisions` VALUES (245, '', 'TW', 14, 2, '西港區', '西港區', 1, 20, NULL, NULL);
INSERT INTO `divisions` VALUES (246, '', 'TW', 14, 2, '七股區', '七股區', 1, 21, NULL, NULL);
INSERT INTO `divisions` VALUES (247, '', 'TW', 14, 2, '將軍區', '將軍區', 1, 22, NULL, NULL);
INSERT INTO `divisions` VALUES (248, '', 'TW', 14, 2, '學甲區', '學甲區', 1, 23, NULL, NULL);
INSERT INTO `divisions` VALUES (249, '', 'TW', 14, 2, '北門區', '北門區', 1, 24, NULL, NULL);
INSERT INTO `divisions` VALUES (250, '', 'TW', 14, 2, '新營區', '新營區', 1, 25, NULL, NULL);
INSERT INTO `divisions` VALUES (251, '', 'TW', 14, 2, '後壁區', '後壁區', 1, 26, NULL, NULL);
INSERT INTO `divisions` VALUES (252, '', 'TW', 14, 2, '白河區', '白河區', 1, 27, NULL, NULL);
INSERT INTO `divisions` VALUES (253, '', 'TW', 14, 2, '東山區', '東山區', 1, 28, NULL, NULL);
INSERT INTO `divisions` VALUES (254, '', 'TW', 14, 2, '六甲區', '六甲區', 1, 29, NULL, NULL);
INSERT INTO `divisions` VALUES (255, '', 'TW', 14, 2, '下營區', '下營區', 1, 30, NULL, NULL);
INSERT INTO `divisions` VALUES (256, '', 'TW', 14, 2, '柳營區', '柳營區', 1, 31, NULL, NULL);
INSERT INTO `divisions` VALUES (257, '', 'TW', 14, 2, '鹽水區', '鹽水區', 1, 32, NULL, NULL);
INSERT INTO `divisions` VALUES (258, '', 'TW', 14, 2, '善化區', '善化區', 1, 33, NULL, NULL);
INSERT INTO `divisions` VALUES (259, '', 'TW', 14, 2, '大內區', '大內區', 1, 34, NULL, NULL);
INSERT INTO `divisions` VALUES (260, '', 'TW', 14, 2, '山上區', '山上區', 1, 35, NULL, NULL);
INSERT INTO `divisions` VALUES (261, '', 'TW', 14, 2, '新市區', '新市區', 1, 36, NULL, NULL);
INSERT INTO `divisions` VALUES (262, '', 'TW', 14, 2, '安定區', '安定區', 1, 37, NULL, NULL);
INSERT INTO `divisions` VALUES (263, '', 'TW', 15, 2, '新興區', '新興區', 1, 1, NULL, NULL);
INSERT INTO `divisions` VALUES (264, '', 'TW', 15, 2, '前金區', '前金區', 1, 2, NULL, NULL);
INSERT INTO `divisions` VALUES (265, '', 'TW', 15, 2, '苓雅區', '苓雅區', 1, 3, NULL, NULL);
INSERT INTO `divisions` VALUES (266, '', 'TW', 15, 2, '鹽埕區', '鹽埕區', 1, 4, NULL, NULL);
INSERT INTO `divisions` VALUES (267, '', 'TW', 15, 2, '鼓山區', '鼓山區', 1, 5, NULL, NULL);
INSERT INTO `divisions` VALUES (268, '', 'TW', 15, 2, '旗津區', '旗津區', 1, 6, NULL, NULL);
INSERT INTO `divisions` VALUES (269, '', 'TW', 15, 2, '前鎮區', '前鎮區', 1, 7, NULL, NULL);
INSERT INTO `divisions` VALUES (270, '', 'TW', 15, 2, '三民區', '三民區', 1, 8, NULL, NULL);
INSERT INTO `divisions` VALUES (271, '', 'TW', 15, 2, '楠梓區', '楠梓區', 1, 9, NULL, NULL);
INSERT INTO `divisions` VALUES (272, '', 'TW', 15, 2, '小港區', '小港區', 1, 10, NULL, NULL);
INSERT INTO `divisions` VALUES (273, '', 'TW', 15, 2, '左營區', '左營區', 1, 11, NULL, NULL);
INSERT INTO `divisions` VALUES (274, '', 'TW', 15, 2, '仁武區', '仁武區', 1, 12, NULL, NULL);
INSERT INTO `divisions` VALUES (275, '', 'TW', 15, 2, '大社區', '大社區', 1, 13, NULL, NULL);
INSERT INTO `divisions` VALUES (276, '', 'TW', 15, 2, '東沙群島', '東沙群島', 1, 14, NULL, NULL);
INSERT INTO `divisions` VALUES (277, '', 'TW', 15, 2, '南沙群島', '南沙群島', 1, 15, NULL, NULL);
INSERT INTO `divisions` VALUES (278, '', 'TW', 15, 2, '岡山區', '岡山區', 1, 16, NULL, NULL);
INSERT INTO `divisions` VALUES (279, '', 'TW', 15, 2, '路竹區', '路竹區', 1, 17, NULL, NULL);
INSERT INTO `divisions` VALUES (280, '', 'TW', 15, 2, '阿蓮區', '阿蓮區', 1, 18, NULL, NULL);
INSERT INTO `divisions` VALUES (281, '', 'TW', 15, 2, '田寮區', '田寮區', 1, 19, NULL, NULL);
INSERT INTO `divisions` VALUES (282, '', 'TW', 15, 2, '燕巢區', '燕巢區', 1, 20, NULL, NULL);
INSERT INTO `divisions` VALUES (283, '', 'TW', 15, 2, '橋頭區', '橋頭區', 1, 21, NULL, NULL);
INSERT INTO `divisions` VALUES (284, '', 'TW', 15, 2, '梓官區', '梓官區', 1, 22, NULL, NULL);
INSERT INTO `divisions` VALUES (285, '', 'TW', 15, 2, '彌陀區', '彌陀區', 1, 23, NULL, NULL);
INSERT INTO `divisions` VALUES (286, '', 'TW', 15, 2, '永安區', '永安區', 1, 24, NULL, NULL);
INSERT INTO `divisions` VALUES (287, '', 'TW', 15, 2, '湖內區', '湖內區', 1, 25, NULL, NULL);
INSERT INTO `divisions` VALUES (288, '', 'TW', 15, 2, '鳳山區', '鳳山區', 1, 26, NULL, NULL);
INSERT INTO `divisions` VALUES (289, '', 'TW', 15, 2, '大寮區', '大寮區', 1, 27, NULL, NULL);
INSERT INTO `divisions` VALUES (290, '', 'TW', 15, 2, '林園區', '林園區', 1, 28, NULL, NULL);
INSERT INTO `divisions` VALUES (291, '', 'TW', 15, 2, '鳥松區', '鳥松區', 1, 29, NULL, NULL);
INSERT INTO `divisions` VALUES (292, '', 'TW', 15, 2, '大樹區', '大樹區', 1, 30, NULL, NULL);
INSERT INTO `divisions` VALUES (293, '', 'TW', 15, 2, '旗山區', '旗山區', 1, 31, NULL, NULL);
INSERT INTO `divisions` VALUES (294, '', 'TW', 15, 2, '美濃區', '美濃區', 1, 32, NULL, NULL);
INSERT INTO `divisions` VALUES (295, '', 'TW', 15, 2, '六龜區', '六龜區', 1, 33, NULL, NULL);
INSERT INTO `divisions` VALUES (296, '', 'TW', 15, 2, '內門區', '內門區', 1, 34, NULL, NULL);
INSERT INTO `divisions` VALUES (297, '', 'TW', 15, 2, '杉林區', '杉林區', 1, 35, NULL, NULL);
INSERT INTO `divisions` VALUES (298, '', 'TW', 15, 2, '甲仙區', '甲仙區', 1, 36, NULL, NULL);
INSERT INTO `divisions` VALUES (299, '', 'TW', 15, 2, '桃源區', '桃源區', 1, 37, NULL, NULL);
INSERT INTO `divisions` VALUES (300, '', 'TW', 15, 2, '那瑪夏區', '那瑪夏區', 1, 38, NULL, NULL);
INSERT INTO `divisions` VALUES (301, '', 'TW', 15, 2, '茂林區', '茂林區', 1, 39, NULL, NULL);
INSERT INTO `divisions` VALUES (302, '', 'TW', 15, 2, '茄萣區', '茄萣區', 1, 40, NULL, NULL);
INSERT INTO `divisions` VALUES (303, '', 'TW', 16, 2, '屏東市', '屏東市', 1, 1, NULL, NULL);
INSERT INTO `divisions` VALUES (304, '', 'TW', 16, 2, '三地門鄉', '三地門鄉', 1, 2, NULL, NULL);
INSERT INTO `divisions` VALUES (305, '', 'TW', 16, 2, '霧臺鄉', '霧臺鄉', 1, 3, NULL, NULL);
INSERT INTO `divisions` VALUES (306, '', 'TW', 16, 2, '瑪家鄉', '瑪家鄉', 1, 4, NULL, NULL);
INSERT INTO `divisions` VALUES (307, '', 'TW', 16, 2, '九如鄉', '九如鄉', 1, 5, NULL, NULL);
INSERT INTO `divisions` VALUES (308, '', 'TW', 16, 2, '里港鄉', '里港鄉', 1, 6, NULL, NULL);
INSERT INTO `divisions` VALUES (309, '', 'TW', 16, 2, '高樹鄉', '高樹鄉', 1, 7, NULL, NULL);
INSERT INTO `divisions` VALUES (310, '', 'TW', 16, 2, '鹽埔鄉', '鹽埔鄉', 1, 8, NULL, NULL);
INSERT INTO `divisions` VALUES (311, '', 'TW', 16, 2, '長治鄉', '長治鄉', 1, 9, NULL, NULL);
INSERT INTO `divisions` VALUES (312, '', 'TW', 16, 2, '麟洛鄉', '麟洛鄉', 1, 10, NULL, NULL);
INSERT INTO `divisions` VALUES (313, '', 'TW', 16, 2, '竹田鄉', '竹田鄉', 1, 11, NULL, NULL);
INSERT INTO `divisions` VALUES (314, '', 'TW', 16, 2, '內埔鄉', '內埔鄉', 1, 12, NULL, NULL);
INSERT INTO `divisions` VALUES (315, '', 'TW', 16, 2, '萬丹鄉', '萬丹鄉', 1, 13, NULL, NULL);
INSERT INTO `divisions` VALUES (316, '', 'TW', 16, 2, '潮州鎮', '潮州鎮', 1, 14, NULL, NULL);
INSERT INTO `divisions` VALUES (317, '', 'TW', 16, 2, '泰武鄉', '泰武鄉', 1, 15, NULL, NULL);
INSERT INTO `divisions` VALUES (318, '', 'TW', 16, 2, '來義鄉', '來義鄉', 1, 16, NULL, NULL);
INSERT INTO `divisions` VALUES (319, '', 'TW', 16, 2, '萬巒鄉', '萬巒鄉', 1, 17, NULL, NULL);
INSERT INTO `divisions` VALUES (320, '', 'TW', 16, 2, '崁頂鄉', '崁頂鄉', 1, 18, NULL, NULL);
INSERT INTO `divisions` VALUES (321, '', 'TW', 16, 2, '新埤鄉', '新埤鄉', 1, 19, NULL, NULL);
INSERT INTO `divisions` VALUES (322, '', 'TW', 16, 2, '南州鄉', '南州鄉', 1, 20, NULL, NULL);
INSERT INTO `divisions` VALUES (323, '', 'TW', 16, 2, '林邊鄉', '林邊鄉', 1, 21, NULL, NULL);
INSERT INTO `divisions` VALUES (324, '', 'TW', 16, 2, '東港鎮', '東港鎮', 1, 22, NULL, NULL);
INSERT INTO `divisions` VALUES (325, '', 'TW', 16, 2, '琉球鄉', '琉球鄉', 1, 23, NULL, NULL);
INSERT INTO `divisions` VALUES (326, '', 'TW', 16, 2, '佳冬鄉', '佳冬鄉', 1, 24, NULL, NULL);
INSERT INTO `divisions` VALUES (327, '', 'TW', 16, 2, '新園鄉', '新園鄉', 1, 25, NULL, NULL);
INSERT INTO `divisions` VALUES (328, '', 'TW', 16, 2, '枋寮鄉', '枋寮鄉', 1, 26, NULL, NULL);
INSERT INTO `divisions` VALUES (329, '', 'TW', 16, 2, '枋山鄉', '枋山鄉', 1, 27, NULL, NULL);
INSERT INTO `divisions` VALUES (330, '', 'TW', 16, 2, '春日鄉', '春日鄉', 1, 28, NULL, NULL);
INSERT INTO `divisions` VALUES (331, '', 'TW', 16, 2, '獅子鄉', '獅子鄉', 1, 29, NULL, NULL);
INSERT INTO `divisions` VALUES (332, '', 'TW', 16, 2, '車城鄉', '車城鄉', 1, 30, NULL, NULL);
INSERT INTO `divisions` VALUES (333, '', 'TW', 16, 2, '牡丹鄉', '牡丹鄉', 1, 31, NULL, NULL);
INSERT INTO `divisions` VALUES (334, '', 'TW', 16, 2, '恆春鎮', '恆春鎮', 1, 32, NULL, NULL);
INSERT INTO `divisions` VALUES (335, '', 'TW', 16, 2, '滿州鄉', '滿州鄉', 1, 33, NULL, NULL);
INSERT INTO `divisions` VALUES (336, '', 'TW', 17, 2, '臺東市', '臺東市', 1, 1, NULL, NULL);
INSERT INTO `divisions` VALUES (337, '', 'TW', 17, 2, '綠島鄉', '綠島鄉', 1, 2, NULL, NULL);
INSERT INTO `divisions` VALUES (338, '', 'TW', 17, 2, '蘭嶼鄉', '蘭嶼鄉', 1, 3, NULL, NULL);
INSERT INTO `divisions` VALUES (339, '', 'TW', 17, 2, '延平鄉', '延平鄉', 1, 4, NULL, NULL);
INSERT INTO `divisions` VALUES (340, '', 'TW', 17, 2, '卑南鄉', '卑南鄉', 1, 5, NULL, NULL);
INSERT INTO `divisions` VALUES (341, '', 'TW', 17, 2, '鹿野鄉', '鹿野鄉', 1, 6, NULL, NULL);
INSERT INTO `divisions` VALUES (342, '', 'TW', 17, 2, '關山鎮', '關山鎮', 1, 7, NULL, NULL);
INSERT INTO `divisions` VALUES (343, '', 'TW', 17, 2, '海端鄉', '海端鄉', 1, 8, NULL, NULL);
INSERT INTO `divisions` VALUES (344, '', 'TW', 17, 2, '池上鄉', '池上鄉', 1, 9, NULL, NULL);
INSERT INTO `divisions` VALUES (345, '', 'TW', 17, 2, '東河鄉', '東河鄉', 1, 10, NULL, NULL);
INSERT INTO `divisions` VALUES (346, '', 'TW', 17, 2, '成功鎮', '成功鎮', 1, 11, NULL, NULL);
INSERT INTO `divisions` VALUES (347, '', 'TW', 17, 2, '長濱鄉', '長濱鄉', 1, 12, NULL, NULL);
INSERT INTO `divisions` VALUES (348, '', 'TW', 17, 2, '太麻里鄉', '太麻里鄉', 1, 13, NULL, NULL);
INSERT INTO `divisions` VALUES (349, '', 'TW', 17, 2, '金峰鄉', '金峰鄉', 1, 14, NULL, NULL);
INSERT INTO `divisions` VALUES (350, '', 'TW', 17, 2, '大武鄉', '大武鄉', 1, 15, NULL, NULL);
INSERT INTO `divisions` VALUES (351, '', 'TW', 17, 2, '達仁鄉', '達仁鄉', 1, 16, NULL, NULL);
INSERT INTO `divisions` VALUES (352, '', 'TW', 18, 2, '花蓮市', '花蓮市', 1, 1, NULL, NULL);
INSERT INTO `divisions` VALUES (353, '', 'TW', 18, 2, '新城鄉', '新城鄉', 1, 2, NULL, NULL);
INSERT INTO `divisions` VALUES (354, '', 'TW', 18, 2, '秀林鄉', '秀林鄉', 1, 3, NULL, NULL);
INSERT INTO `divisions` VALUES (355, '', 'TW', 18, 2, '吉安鄉', '吉安鄉', 1, 4, NULL, NULL);
INSERT INTO `divisions` VALUES (356, '', 'TW', 18, 2, '壽豐鄉', '壽豐鄉', 1, 5, NULL, NULL);
INSERT INTO `divisions` VALUES (357, '', 'TW', 18, 2, '鳳林鎮', '鳳林鎮', 1, 6, NULL, NULL);
INSERT INTO `divisions` VALUES (358, '', 'TW', 18, 2, '光復鄉', '光復鄉', 1, 7, NULL, NULL);
INSERT INTO `divisions` VALUES (359, '', 'TW', 18, 2, '豐濱鄉', '豐濱鄉', 1, 8, NULL, NULL);
INSERT INTO `divisions` VALUES (360, '', 'TW', 18, 2, '瑞穗鄉', '瑞穗鄉', 1, 9, NULL, NULL);
INSERT INTO `divisions` VALUES (361, '', 'TW', 18, 2, '萬榮鄉', '萬榮鄉', 1, 10, NULL, NULL);
INSERT INTO `divisions` VALUES (362, '', 'TW', 18, 2, '玉里鎮', '玉里鎮', 1, 11, NULL, NULL);
INSERT INTO `divisions` VALUES (363, '', 'TW', 18, 2, '卓溪鄉', '卓溪鄉', 1, 12, NULL, NULL);
INSERT INTO `divisions` VALUES (364, '', 'TW', 18, 2, '富里鄉', '富里鄉', 1, 13, NULL, NULL);
INSERT INTO `divisions` VALUES (365, '', 'TW', 19, 2, '宜蘭市', '宜蘭市', 1, 1, NULL, NULL);
INSERT INTO `divisions` VALUES (366, '', 'TW', 19, 2, '頭城鎮', '頭城鎮', 1, 2, NULL, NULL);
INSERT INTO `divisions` VALUES (367, '', 'TW', 19, 2, '礁溪鄉', '礁溪鄉', 1, 3, NULL, NULL);
INSERT INTO `divisions` VALUES (368, '', 'TW', 19, 2, '壯圍鄉', '壯圍鄉', 1, 4, NULL, NULL);
INSERT INTO `divisions` VALUES (369, '', 'TW', 19, 2, '員山鄉', '員山鄉', 1, 5, NULL, NULL);
INSERT INTO `divisions` VALUES (370, '', 'TW', 19, 2, '羅東鎮', '羅東鎮', 1, 6, NULL, NULL);
INSERT INTO `divisions` VALUES (371, '', 'TW', 19, 2, '三星鄉', '三星鄉', 1, 7, NULL, NULL);
INSERT INTO `divisions` VALUES (372, '', 'TW', 19, 2, '大同鄉', '大同鄉', 1, 8, NULL, NULL);
INSERT INTO `divisions` VALUES (373, '', 'TW', 19, 2, '五結鄉', '五結鄉', 1, 9, NULL, NULL);
INSERT INTO `divisions` VALUES (374, '', 'TW', 19, 2, '冬山鄉', '冬山鄉', 1, 10, NULL, NULL);
INSERT INTO `divisions` VALUES (375, '', 'TW', 19, 2, '蘇澳鎮', '蘇澳鎮', 1, 11, NULL, NULL);
INSERT INTO `divisions` VALUES (376, '', 'TW', 19, 2, '南澳鄉', '南澳鄉', 1, 12, NULL, NULL);
INSERT INTO `divisions` VALUES (377, '', 'TW', 19, 2, '釣魚臺', '釣魚臺', 1, 13, NULL, NULL);
INSERT INTO `divisions` VALUES (378, '', 'TW', 20, 2, '馬公市', '馬公市', 1, 1, NULL, NULL);
INSERT INTO `divisions` VALUES (379, '', 'TW', 20, 2, '西嶼鄉', '西嶼鄉', 1, 2, NULL, NULL);
INSERT INTO `divisions` VALUES (380, '', 'TW', 20, 2, '望安鄉', '望安鄉', 1, 3, NULL, NULL);
INSERT INTO `divisions` VALUES (381, '', 'TW', 20, 2, '七美鄉', '七美鄉', 1, 4, NULL, NULL);
INSERT INTO `divisions` VALUES (382, '', 'TW', 20, 2, '白沙鄉', '白沙鄉', 1, 5, NULL, NULL);
INSERT INTO `divisions` VALUES (383, '', 'TW', 20, 2, '湖西鄉', '湖西鄉', 1, 6, NULL, NULL);
INSERT INTO `divisions` VALUES (384, '', 'TW', 21, 2, '金沙鎮', '金沙鎮', 1, 1, NULL, NULL);
INSERT INTO `divisions` VALUES (385, '', 'TW', 21, 2, '金湖鎮', '金湖鎮', 1, 2, NULL, NULL);
INSERT INTO `divisions` VALUES (386, '', 'TW', 21, 2, '金寧鄉', '金寧鄉', 1, 3, NULL, NULL);
INSERT INTO `divisions` VALUES (387, '', 'TW', 21, 2, '金城鎮', '金城鎮', 1, 4, NULL, NULL);
INSERT INTO `divisions` VALUES (388, '', 'TW', 21, 2, '烈嶼鄉', '烈嶼鄉', 1, 5, NULL, NULL);
INSERT INTO `divisions` VALUES (389, '', 'TW', 21, 2, '烏坵鄉', '烏坵鄉', 1, 6, NULL, NULL);
INSERT INTO `divisions` VALUES (390, '', 'TW', 22, 2, '南竿鄉', '南竿鄉', 1, 1, NULL, NULL);
INSERT INTO `divisions` VALUES (391, '', 'TW', 22, 2, '北竿鄉', '北竿鄉', 1, 2, NULL, NULL);
INSERT INTO `divisions` VALUES (392, NULL, 'TW', 22, 2, '莒光鄉', '莒光鄉', 1, 3, NULL, '2025-12-01 11:25:44');
INSERT INTO `divisions` VALUES (393, '', 'TW', 22, 2, '東引鄉', '東引鄉', 1, 4, NULL, NULL);
INSERT INTO `divisions` VALUES (396, '', 'TW', 0, 2, '自取區', '自取區', 1, 38, NULL, NULL);

-- ----------------------------
-- Table structure for failed_jobs
-- ----------------------------
DROP TABLE IF EXISTS `failed_jobs`;
CREATE TABLE `failed_jobs`  (
  `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT,
  `uuid` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `connection` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `queue` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `payload` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `exception` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `failed_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`) USING BTREE,
  UNIQUE INDEX `failed_jobs_uuid_unique`(`uuid` ASC) USING BTREE
) ENGINE = InnoDB AUTO_INCREMENT = 1 CHARACTER SET = utf8mb4 COLLATE = utf8mb4_unicode_ci ROW_FORMAT = Dynamic;

-- ----------------------------
-- Records of failed_jobs
-- ----------------------------

-- ----------------------------
-- Table structure for job_batches
-- ----------------------------
DROP TABLE IF EXISTS `job_batches`;
CREATE TABLE `job_batches`  (
  `id` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `name` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `total_jobs` int NOT NULL,
  `pending_jobs` int NOT NULL,
  `failed_jobs` int NOT NULL,
  `failed_job_ids` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `options` mediumtext CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NULL,
  `cancelled_at` int NULL DEFAULT NULL,
  `created_at` int NOT NULL,
  `finished_at` int NULL DEFAULT NULL,
  PRIMARY KEY (`id`) USING BTREE
) ENGINE = InnoDB CHARACTER SET = utf8mb4 COLLATE = utf8mb4_unicode_ci ROW_FORMAT = Dynamic;

-- ----------------------------
-- Records of job_batches
-- ----------------------------

-- ----------------------------
-- Table structure for jobs
-- ----------------------------
DROP TABLE IF EXISTS `jobs`;
CREATE TABLE `jobs`  (
  `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT,
  `queue` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `payload` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `attempts` tinyint UNSIGNED NOT NULL,
  `reserved_at` int UNSIGNED NULL DEFAULT NULL,
  `available_at` int UNSIGNED NOT NULL,
  `created_at` int UNSIGNED NOT NULL,
  PRIMARY KEY (`id`) USING BTREE,
  INDEX `jobs_queue_index`(`queue` ASC) USING BTREE
) ENGINE = InnoDB AUTO_INCREMENT = 1 CHARACTER SET = utf8mb4 COLLATE = utf8mb4_unicode_ci ROW_FORMAT = Dynamic;

-- ----------------------------
-- Records of jobs
-- ----------------------------

-- ----------------------------
-- Table structure for meta_keys
-- ----------------------------
DROP TABLE IF EXISTS `meta_keys`;
CREATE TABLE `meta_keys`  (
  `id` smallint UNSIGNED NOT NULL AUTO_INCREMENT,
  `name` varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL COMMENT '欄位名稱',
  `table_name` varchar(30) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NULL DEFAULT NULL COMMENT '所屬資料表',
  `description` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NULL DEFAULT NULL COMMENT '欄位說明',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`) USING BTREE,
  UNIQUE INDEX `meta_keys_name_unique`(`name` ASC) USING BTREE,
  INDEX `meta_keys_table_name_index`(`table_name` ASC) USING BTREE
) ENGINE = InnoDB AUTO_INCREMENT = 2 CHARACTER SET = utf8mb4 COLLATE = utf8mb4_unicode_ci ROW_FORMAT = Dynamic;

-- ----------------------------
-- Records of meta_keys
-- ----------------------------
INSERT INTO `meta_keys` VALUES (1, 'birthdays', 'users', '生日', '2025-11-29 17:22:09', '2025-11-29 17:22:09');

-- ----------------------------
-- Table structure for migrations
-- ----------------------------
DROP TABLE IF EXISTS `migrations`;
CREATE TABLE `migrations`  (
  `id` int UNSIGNED NOT NULL AUTO_INCREMENT,
  `migration` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `batch` int NOT NULL,
  PRIMARY KEY (`id`) USING BTREE
) ENGINE = InnoDB AUTO_INCREMENT = 17 CHARACTER SET = utf8mb4 COLLATE = utf8mb4_unicode_ci ROW_FORMAT = Dynamic;

-- ----------------------------
-- Records of migrations
-- ----------------------------
INSERT INTO `migrations` VALUES (1, '0001_01_01_000000_create_users_table', 1);
INSERT INTO `migrations` VALUES (2, '0001_01_01_000001_create_cache_table', 1);
INSERT INTO `migrations` VALUES (3, '0001_01_01_000002_create_jobs_table', 1);
INSERT INTO `migrations` VALUES (4, '2025_11_26_124024_create_personal_access_tokens_table', 1);
INSERT INTO `migrations` VALUES (5, '2025_11_26_124341_create_permission_tables', 1);
INSERT INTO `migrations` VALUES (7, '2100_01_01_000012_create_settings_table', 2);
INSERT INTO `migrations` VALUES (9, '2100_01_01_000020_create_countries_table', 3);
INSERT INTO `migrations` VALUES (10, '2100_01_01_000021_create_divisions_table', 4);
INSERT INTO `migrations` VALUES (11, '2100_01_01_000002_create_meta_keys_table', 5);
INSERT INTO `migrations` VALUES (12, '2100_01_01_000003_create_accounts_table', 6);
INSERT INTO `migrations` VALUES (13, '2100_01_01_000004_create_account_metas_table', 7);
INSERT INTO `migrations` VALUES (14, '2100_01_01_000003_create_user_metas_table', 8);
INSERT INTO `migrations` VALUES (15, '2025_12_01_000001_create_taxonomy_term_tables', 9);
INSERT INTO `migrations` VALUES (16, '2025_11_26_200000_alter_permission_tables', 10);

-- ----------------------------
-- Table structure for model_has_permissions
-- ----------------------------
DROP TABLE IF EXISTS `model_has_permissions`;
CREATE TABLE `model_has_permissions`  (
  `permission_id` bigint UNSIGNED NOT NULL,
  `model_type` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `model_id` bigint UNSIGNED NOT NULL,
  PRIMARY KEY (`permission_id`, `model_id`, `model_type`) USING BTREE,
  INDEX `model_has_permissions_model_id_model_type_index`(`model_id` ASC, `model_type` ASC) USING BTREE,
  CONSTRAINT `model_has_permissions_permission_id_foreign` FOREIGN KEY (`permission_id`) REFERENCES `permissions` (`id`) ON DELETE CASCADE ON UPDATE RESTRICT
) ENGINE = InnoDB CHARACTER SET = utf8mb4 COLLATE = utf8mb4_unicode_ci ROW_FORMAT = Dynamic;

-- ----------------------------
-- Records of model_has_permissions
-- ----------------------------

-- ----------------------------
-- Table structure for model_has_roles
-- ----------------------------
DROP TABLE IF EXISTS `model_has_roles`;
CREATE TABLE `model_has_roles`  (
  `role_id` bigint UNSIGNED NOT NULL,
  `model_type` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `model_id` bigint UNSIGNED NOT NULL,
  PRIMARY KEY (`role_id`, `model_id`, `model_type`) USING BTREE,
  INDEX `model_has_roles_model_id_model_type_index`(`model_id` ASC, `model_type` ASC) USING BTREE,
  CONSTRAINT `model_has_roles_role_id_foreign` FOREIGN KEY (`role_id`) REFERENCES `roles` (`id`) ON DELETE CASCADE ON UPDATE RESTRICT
) ENGINE = InnoDB CHARACTER SET = utf8mb4 COLLATE = utf8mb4_unicode_ci ROW_FORMAT = Dynamic;

-- ----------------------------
-- Records of model_has_roles
-- ----------------------------
INSERT INTO `model_has_roles` VALUES (1, 'App\\Models\\Identity\\User', 49);
INSERT INTO `model_has_roles` VALUES (2, 'App\\Models\\Identity\\User', 49);

-- ----------------------------
-- Table structure for password_reset_tokens
-- ----------------------------
DROP TABLE IF EXISTS `password_reset_tokens`;
CREATE TABLE `password_reset_tokens`  (
  `email` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `token` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`email`) USING BTREE
) ENGINE = InnoDB CHARACTER SET = utf8mb4 COLLATE = utf8mb4_unicode_ci ROW_FORMAT = Dynamic;

-- ----------------------------
-- Records of password_reset_tokens
-- ----------------------------

-- ----------------------------
-- Table structure for permissions
-- ----------------------------
DROP TABLE IF EXISTS `permissions`;
CREATE TABLE `permissions`  (
  `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT,
  `parent_id` bigint UNSIGNED NULL DEFAULT NULL,
  `name` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `guard_name` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `title` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NULL DEFAULT NULL COMMENT '顯示名稱',
  `description` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NULL COMMENT '權限說明',
  `sort_order` int NOT NULL DEFAULT 0 COMMENT '排序',
  `type` enum('menu','action') CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'menu' COMMENT '類型：menu=選單, action=功能',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`) USING BTREE,
  UNIQUE INDEX `permissions_name_guard_name_unique`(`name` ASC, `guard_name` ASC) USING BTREE,
  INDEX `permissions_parent_id_index`(`parent_id` ASC) USING BTREE,
  INDEX `permissions_type_index`(`type` ASC) USING BTREE,
  INDEX `permissions_sort_order_index`(`sort_order` ASC) USING BTREE,
  CONSTRAINT `permissions_parent_id_foreign` FOREIGN KEY (`parent_id`) REFERENCES `permissions` (`id`) ON DELETE CASCADE ON UPDATE RESTRICT
) ENGINE = InnoDB AUTO_INCREMENT = 5 CHARACTER SET = utf8mb4 COLLATE = utf8mb4_unicode_ci ROW_FORMAT = Dynamic;

-- ----------------------------
-- Records of permissions
-- ----------------------------
INSERT INTO `permissions` VALUES (1, NULL, 'test', 'web', '測試', NULL, 1, 'menu', '2025-12-01 16:51:36', '2025-12-01 16:51:36');
INSERT INTO `permissions` VALUES (2, NULL, 'test2', 'web', '測試2', NULL, 0, 'menu', '2025-12-01 16:52:01', '2025-12-01 16:52:01');
INSERT INTO `permissions` VALUES (3, NULL, 'test3', 'web', 'test3', NULL, 0, 'menu', '2025-12-01 16:52:20', '2025-12-01 16:52:20');
INSERT INTO `permissions` VALUES (4, NULL, 'test4', 'web', 'test4', NULL, 0, 'menu', '2025-12-01 17:03:17', '2025-12-01 17:03:17');

-- ----------------------------
-- Table structure for personal_access_tokens
-- ----------------------------
DROP TABLE IF EXISTS `personal_access_tokens`;
CREATE TABLE `personal_access_tokens`  (
  `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT,
  `tokenable_type` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `tokenable_id` bigint UNSIGNED NOT NULL,
  `name` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `token` varchar(64) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `abilities` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NULL,
  `last_used_at` timestamp NULL DEFAULT NULL,
  `expires_at` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`) USING BTREE,
  UNIQUE INDEX `personal_access_tokens_token_unique`(`token` ASC) USING BTREE,
  INDEX `personal_access_tokens_tokenable_type_tokenable_id_index`(`tokenable_type` ASC, `tokenable_id` ASC) USING BTREE,
  INDEX `personal_access_tokens_expires_at_index`(`expires_at` ASC) USING BTREE
) ENGINE = InnoDB AUTO_INCREMENT = 1 CHARACTER SET = utf8mb4 COLLATE = utf8mb4_unicode_ci ROW_FORMAT = Dynamic;

-- ----------------------------
-- Records of personal_access_tokens
-- ----------------------------

-- ----------------------------
-- Table structure for role_has_permissions
-- ----------------------------
DROP TABLE IF EXISTS `role_has_permissions`;
CREATE TABLE `role_has_permissions`  (
  `permission_id` bigint UNSIGNED NOT NULL,
  `role_id` bigint UNSIGNED NOT NULL,
  PRIMARY KEY (`permission_id`, `role_id`) USING BTREE,
  INDEX `role_has_permissions_role_id_foreign`(`role_id` ASC) USING BTREE,
  CONSTRAINT `role_has_permissions_permission_id_foreign` FOREIGN KEY (`permission_id`) REFERENCES `permissions` (`id`) ON DELETE CASCADE ON UPDATE RESTRICT,
  CONSTRAINT `role_has_permissions_role_id_foreign` FOREIGN KEY (`role_id`) REFERENCES `roles` (`id`) ON DELETE CASCADE ON UPDATE RESTRICT
) ENGINE = InnoDB CHARACTER SET = utf8mb4 COLLATE = utf8mb4_unicode_ci ROW_FORMAT = Dynamic;

-- ----------------------------
-- Records of role_has_permissions
-- ----------------------------
INSERT INTO `role_has_permissions` VALUES (1, 1);
INSERT INTO `role_has_permissions` VALUES (2, 1);
INSERT INTO `role_has_permissions` VALUES (3, 1);
INSERT INTO `role_has_permissions` VALUES (4, 1);
INSERT INTO `role_has_permissions` VALUES (1, 2);
INSERT INTO `role_has_permissions` VALUES (2, 2);
INSERT INTO `role_has_permissions` VALUES (3, 2);
INSERT INTO `role_has_permissions` VALUES (4, 2);

-- ----------------------------
-- Table structure for roles
-- ----------------------------
DROP TABLE IF EXISTS `roles`;
CREATE TABLE `roles`  (
  `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT,
  `name` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `guard_name` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `title` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NULL DEFAULT NULL COMMENT '顯示名稱',
  `description` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NULL COMMENT '角色說明',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`) USING BTREE,
  UNIQUE INDEX `roles_name_guard_name_unique`(`name` ASC, `guard_name` ASC) USING BTREE
) ENGINE = InnoDB AUTO_INCREMENT = 3 CHARACTER SET = utf8mb4 COLLATE = utf8mb4_unicode_ci ROW_FORMAT = Dynamic;

-- ----------------------------
-- Records of roles
-- ----------------------------
INSERT INTO `roles` VALUES (1, 'staff', 'web', '後台管理員', '後台使用者必須有此角色。', '2025-12-01 17:10:40', '2025-12-01 17:43:18');
INSERT INTO `roles` VALUES (2, 'sales_order_operator', 'web', '銷售訂單管理員', NULL, '2025-12-01 17:44:14', '2025-12-01 17:44:14');

-- ----------------------------
-- Table structure for sessions
-- ----------------------------
DROP TABLE IF EXISTS `sessions`;
CREATE TABLE `sessions`  (
  `id` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `user_id` bigint UNSIGNED NULL DEFAULT NULL,
  `ip_address` varchar(45) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NULL DEFAULT NULL,
  `user_agent` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NULL,
  `payload` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `last_activity` int NOT NULL,
  PRIMARY KEY (`id`) USING BTREE,
  INDEX `sessions_user_id_index`(`user_id` ASC) USING BTREE,
  INDEX `sessions_last_activity_index`(`last_activity` ASC) USING BTREE
) ENGINE = InnoDB CHARACTER SET = utf8mb4 COLLATE = utf8mb4_unicode_ci ROW_FORMAT = Dynamic;

-- ----------------------------
-- Records of sessions
-- ----------------------------
INSERT INTO `sessions` VALUES ('Sx9rfnEZKkIKuVIgniu9swuG1wZOZVjdgqUp4x74', 1, '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:145.0) Gecko/20100101 Firefox/145.0', 'YTo1OntzOjY6Il90b2tlbiI7czo0MDoiNWc2QmNrWkladVpXd2FjTlVtM09ia0xNTFhyNEVwYXA2cGdSaWNMMyI7czozOiJ1cmwiO2E6MDp7fXM6OToiX3ByZXZpb3VzIjthOjI6e3M6MzoidXJsIjtzOjQ4OiJodHRwOi8vMTI3LjAuMC4xOjgwMDAvemgtaGFudC9vY2FkbWluL3N5c3RlbS9sb2ciO3M6NToicm91dGUiO3M6Mjk6Imxhbmcub2NhZG1pbi5zeXN0ZW0ubG9nLmluZGV4Ijt9czo2OiJfZmxhc2giO2E6Mjp7czozOiJvbGQiO2E6MDp7fXM6MzoibmV3IjthOjA6e319czo1MDoibG9naW5fd2ViXzU5YmEzNmFkZGMyYjJmOTQwMTU4MGYwMTRjN2Y1OGVhNGUzMDk4OWQiO2k6MTt9', 1764611592);

-- ----------------------------
-- Table structure for settings
-- ----------------------------
DROP TABLE IF EXISTS `settings`;
CREATE TABLE `settings`  (
  `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT,
  `locale` varchar(10) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '' COMMENT '語言代碼',
  `group` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NULL DEFAULT NULL COMMENT '群組',
  `code` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `content` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NULL,
  `type` enum('text','line','json','serialized','bool','int','float','array') CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'text' COMMENT '設定值類型',
  `note` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NULL DEFAULT NULL COMMENT '備註',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`) USING BTREE,
  UNIQUE INDEX `settings_unique_code`(`locale` ASC, `code` ASC) USING BTREE
) ENGINE = InnoDB AUTO_INCREMENT = 2 CHARACTER SET = utf8mb4 COLLATE = utf8mb4_unicode_ci ROW_FORMAT = Dynamic;

-- ----------------------------
-- Records of settings
-- ----------------------------
INSERT INTO `settings` VALUES (1, '', 'config', 'config_admin_limit', '10', 'text', '一頁幾筆', '2025-11-27 12:43:36', '2025-11-27 12:43:36');

-- ----------------------------
-- Table structure for taxonomies
-- ----------------------------
DROP TABLE IF EXISTS `taxonomies`;
CREATE TABLE `taxonomies`  (
  `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT,
  `code` varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `sort_order` int NOT NULL DEFAULT 0,
  `is_active` tinyint(1) NOT NULL DEFAULT 1,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`) USING BTREE,
  UNIQUE INDEX `taxonomies_code_unique`(`code` ASC) USING BTREE,
  INDEX `taxonomies_is_active_index`(`is_active` ASC) USING BTREE,
  INDEX `taxonomies_sort_order_index`(`sort_order` ASC) USING BTREE
) ENGINE = InnoDB AUTO_INCREMENT = 4 CHARACTER SET = utf8mb4 COLLATE = utf8mb4_unicode_ci ROW_FORMAT = Dynamic;

-- ----------------------------
-- Records of taxonomies
-- ----------------------------
INSERT INTO `taxonomies` VALUES (3, 'order_status', 0, 1, '2025-12-01 10:36:34', '2025-12-01 10:36:34');

-- ----------------------------
-- Table structure for taxonomy_translations
-- ----------------------------
DROP TABLE IF EXISTS `taxonomy_translations`;
CREATE TABLE `taxonomy_translations`  (
  `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT,
  `taxonomy_id` bigint UNSIGNED NOT NULL,
  `locale` varchar(10) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `name` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  PRIMARY KEY (`id`) USING BTREE,
  UNIQUE INDEX `taxonomy_translations_taxonomy_id_locale_unique`(`taxonomy_id` ASC, `locale` ASC) USING BTREE,
  CONSTRAINT `taxonomy_translations_taxonomy_id_foreign` FOREIGN KEY (`taxonomy_id`) REFERENCES `taxonomies` (`id`) ON DELETE CASCADE ON UPDATE RESTRICT
) ENGINE = InnoDB AUTO_INCREMENT = 3 CHARACTER SET = utf8mb4 COLLATE = utf8mb4_unicode_ci ROW_FORMAT = Dynamic;

-- ----------------------------
-- Records of taxonomy_translations
-- ----------------------------
INSERT INTO `taxonomy_translations` VALUES (1, 3, 'zh_Hant', '訂單狀態');
INSERT INTO `taxonomy_translations` VALUES (2, 3, 'en', 'Order Status');

-- ----------------------------
-- Table structure for term_metas
-- ----------------------------
DROP TABLE IF EXISTS `term_metas`;
CREATE TABLE `term_metas`  (
  `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT,
  `term_id` bigint UNSIGNED NOT NULL,
  `key_id` smallint UNSIGNED NOT NULL,
  `value` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NULL,
  PRIMARY KEY (`id`) USING BTREE,
  UNIQUE INDEX `term_metas_term_id_key_id_unique`(`term_id` ASC, `key_id` ASC) USING BTREE,
  INDEX `term_metas_term_id_index`(`term_id` ASC) USING BTREE,
  INDEX `term_metas_key_id_index`(`key_id` ASC) USING BTREE,
  CONSTRAINT `term_metas_key_id_foreign` FOREIGN KEY (`key_id`) REFERENCES `meta_keys` (`id`) ON DELETE CASCADE ON UPDATE RESTRICT,
  CONSTRAINT `term_metas_term_id_foreign` FOREIGN KEY (`term_id`) REFERENCES `terms` (`id`) ON DELETE CASCADE ON UPDATE RESTRICT
) ENGINE = InnoDB AUTO_INCREMENT = 1 CHARACTER SET = utf8mb4 COLLATE = utf8mb4_unicode_ci ROW_FORMAT = Dynamic;

-- ----------------------------
-- Records of term_metas
-- ----------------------------

-- ----------------------------
-- Table structure for term_translations
-- ----------------------------
DROP TABLE IF EXISTS `term_translations`;
CREATE TABLE `term_translations`  (
  `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT,
  `term_id` bigint UNSIGNED NOT NULL,
  `locale` varchar(10) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `name` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `short_name` varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NULL DEFAULT NULL,
  PRIMARY KEY (`id`) USING BTREE,
  UNIQUE INDEX `term_translations_term_id_locale_unique`(`term_id` ASC, `locale` ASC) USING BTREE,
  CONSTRAINT `term_translations_term_id_foreign` FOREIGN KEY (`term_id`) REFERENCES `terms` (`id`) ON DELETE CASCADE ON UPDATE RESTRICT
) ENGINE = InnoDB AUTO_INCREMENT = 9 CHARACTER SET = utf8mb4 COLLATE = utf8mb4_unicode_ci ROW_FORMAT = Dynamic;

-- ----------------------------
-- Records of term_translations
-- ----------------------------
INSERT INTO `term_translations` VALUES (1, 1, 'zh_Hant', '待處理', NULL);
INSERT INTO `term_translations` VALUES (2, 1, 'en', 'Pending', NULL);
INSERT INTO `term_translations` VALUES (3, 2, 'zh_Hant', '處理中', NULL);
INSERT INTO `term_translations` VALUES (4, 2, 'en', 'Processing', NULL);
INSERT INTO `term_translations` VALUES (5, 3, 'zh_Hant', '已付款', NULL);
INSERT INTO `term_translations` VALUES (6, 3, 'en', 'Paid', NULL);
INSERT INTO `term_translations` VALUES (7, 4, 'zh_Hant', '已出貨', NULL);
INSERT INTO `term_translations` VALUES (8, 4, 'en', 'Shipped', NULL);

-- ----------------------------
-- Table structure for terms
-- ----------------------------
DROP TABLE IF EXISTS `terms`;
CREATE TABLE `terms`  (
  `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT,
  `taxonomy_id` bigint UNSIGNED NOT NULL,
  `parent_id` bigint UNSIGNED NULL DEFAULT NULL,
  `code` varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `sort_order` int NOT NULL DEFAULT 0,
  `is_active` tinyint(1) NOT NULL DEFAULT 1,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`) USING BTREE,
  UNIQUE INDEX `terms_taxonomy_id_code_unique`(`taxonomy_id` ASC, `code` ASC) USING BTREE,
  INDEX `terms_taxonomy_id_index`(`taxonomy_id` ASC) USING BTREE,
  INDEX `terms_parent_id_index`(`parent_id` ASC) USING BTREE,
  INDEX `terms_is_active_index`(`is_active` ASC) USING BTREE,
  INDEX `terms_sort_order_index`(`sort_order` ASC) USING BTREE,
  CONSTRAINT `terms_parent_id_foreign` FOREIGN KEY (`parent_id`) REFERENCES `terms` (`id`) ON DELETE SET NULL ON UPDATE RESTRICT,
  CONSTRAINT `terms_taxonomy_id_foreign` FOREIGN KEY (`taxonomy_id`) REFERENCES `taxonomies` (`id`) ON DELETE CASCADE ON UPDATE RESTRICT
) ENGINE = InnoDB AUTO_INCREMENT = 5 CHARACTER SET = utf8mb4 COLLATE = utf8mb4_unicode_ci ROW_FORMAT = Dynamic;

-- ----------------------------
-- Records of terms
-- ----------------------------
INSERT INTO `terms` VALUES (1, 3, NULL, 'pending', 1, 1, '2025-12-01 10:37:32', '2025-12-01 10:37:32');
INSERT INTO `terms` VALUES (2, 3, NULL, 'processing', 2, 1, '2025-12-01 10:44:48', '2025-12-01 11:31:40');
INSERT INTO `terms` VALUES (3, 3, NULL, 'paid', 3, 1, '2025-12-01 11:31:10', '2025-12-01 11:31:10');
INSERT INTO `terms` VALUES (4, 3, NULL, 'shipped', 4, 1, '2025-12-01 11:31:34', '2025-12-01 11:31:34');

-- ----------------------------
-- Table structure for user_metas
-- ----------------------------
DROP TABLE IF EXISTS `user_metas`;
CREATE TABLE `user_metas`  (
  `user_id` bigint UNSIGNED NOT NULL,
  `key_id` smallint UNSIGNED NOT NULL,
  `value` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NULL,
  PRIMARY KEY (`user_id`, `key_id`) USING BTREE,
  INDEX `user_metas_key_id_foreign`(`key_id` ASC) USING BTREE,
  CONSTRAINT `user_metas_key_id_foreign` FOREIGN KEY (`key_id`) REFERENCES `meta_keys` (`id`) ON DELETE CASCADE ON UPDATE RESTRICT,
  CONSTRAINT `user_metas_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `users_bak` (`id`) ON DELETE CASCADE ON UPDATE RESTRICT
) ENGINE = InnoDB CHARACTER SET = utf8mb4 COLLATE = utf8mb4_unicode_ci ROW_FORMAT = Dynamic;

-- ----------------------------
-- Records of user_metas
-- ----------------------------

-- ----------------------------
-- Table structure for users
-- ----------------------------
DROP TABLE IF EXISTS `users`;
CREATE TABLE `users`  (
  `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT,
  `uuid` char(36) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NULL DEFAULT NULL,
  `code` varchar(20) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NULL DEFAULT NULL,
  `user_serial` bigint NULL DEFAULT NULL,
  `username` varchar(30) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NULL DEFAULT NULL,
  `email` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NULL DEFAULT NULL,
  `name` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NULL DEFAULT NULL,
  `short_name` varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NULL DEFAULT NULL,
  `first_name` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NULL DEFAULT NULL,
  `last_name` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NULL DEFAULT NULL,
  `gender` char(1) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NULL DEFAULT '' COMMENT '1=男 2=女 3=其它',
  `mobile` varchar(20) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NULL DEFAULT NULL,
  `display_name` varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NULL DEFAULT NULL,
  `email_verified_at` timestamp NULL DEFAULT NULL,
  `password` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NULL DEFAULT NULL,
  `require_password_reset` tinyint(1) NULL DEFAULT NULL,
  `two_factor_enabled` tinyint(1) NULL DEFAULT 0 COMMENT 'Whether 2FA is enabled for user (admin controlled)',
  `last_two_factor_at` timestamp NULL DEFAULT NULL COMMENT 'Last time user completed 2FA',
  `two_factor_settings` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin NULL COMMENT '2FA preferences and settings',
  `preferred_2fa_method` enum('email','sms') CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'email' COMMENT 'Preferred 2FA method: email, sms',
  `status` enum('active','pending','suspended','banned') CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NULL DEFAULT 'active',
  `password_reset_required` tinyint(1) NULL DEFAULT NULL,
  `is_active` tinyint(1) NOT NULL DEFAULT 1,
  `last_seen_at` timestamp NULL DEFAULT NULL,
  `remember_token` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NULL DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`) USING BTREE,
  UNIQUE INDEX `users_code_unique`(`code` ASC) USING BTREE,
  UNIQUE INDEX `users_username_unique`(`username` ASC) USING BTREE,
  UNIQUE INDEX `idx_uuid`(`uuid` ASC) USING BTREE
) ENGINE = InnoDB AUTO_INCREMENT = 51 CHARACTER SET = utf8mb4 COLLATE = utf8mb4_unicode_ci ROW_FORMAT = DYNAMIC;

-- ----------------------------
-- Records of users
-- ----------------------------
INSERT INTO `users` VALUES (1, NULL, NULL, NULL, 'admin', 'admin@example.com', 'Admin', NULL, NULL, NULL, '', NULL, '管理員', NULL, '$2y$12$0xPuqmBgUWRJJG6RH2YZ4OtM9TsqmLttmziwrl0/tZKgDR9N/3Fwy', NULL, 0, NULL, NULL, 'email', 'active', NULL, 1, NULL, NULL, '2025-11-30 18:26:27', '2025-11-30 18:26:27');
INSERT INTO `users` VALUES (2, NULL, NULL, NULL, 'test', 'test@example.com', 'Test', NULL, NULL, NULL, '', NULL, '測試員', NULL, '$2y$12$v3y2/YGc5v5eRdGagnYYQetsUpsx/Ru.lP2p8oZchAm4VOUddgrAe', NULL, 0, NULL, NULL, 'email', 'active', NULL, 1, NULL, NULL, '2025-11-30 18:26:27', '2025-11-30 18:26:27');
INSERT INTO `users` VALUES (3, NULL, NULL, NULL, 'elonphp', 'elonphp@gmail.com', 'Elon', NULL, NULL, NULL, '', NULL, 'Elon PHP', NULL, '$2y$12$KgAMVQOfw8GQexXuT7q64eyFmGC6hOWSrAKG.kP5IYTuy3dhu9vK6', NULL, 0, NULL, NULL, 'email', 'active', NULL, 1, NULL, NULL, '2025-11-30 18:26:28', '2025-11-30 18:26:28');
INSERT INTO `users` VALUES (4, NULL, NULL, NULL, 'demo', 'demo@example.com', 'Demo', NULL, NULL, NULL, '', NULL, '展示員', NULL, '$2y$12$Evk816TNXTShPXygIbtuluA.uFWhwaDsn1UFr7dmQHHDgoXKxODOu', NULL, 0, NULL, NULL, 'email', 'active', NULL, 1, NULL, NULL, '2025-11-30 18:26:28', '2025-11-30 18:26:28');
INSERT INTO `users` VALUES (5, NULL, NULL, NULL, 'erong', 'uhuai@example.com', '后慧馨', NULL, NULL, NULL, '', NULL, NULL, NULL, NULL, NULL, 0, NULL, NULL, 'email', 'active', NULL, 1, NULL, NULL, '2025-11-30 18:26:28', '2025-11-30 18:26:28');
INSERT INTO `users` VALUES (6, NULL, NULL, NULL, 'ru.mei', 'yu06@example.org', '酆思心', NULL, NULL, NULL, '', '(02)60566555', NULL, NULL, NULL, NULL, 0, NULL, NULL, 'email', 'active', NULL, 1, NULL, NULL, '2025-11-30 18:26:28', '2025-11-30 18:26:28');
INSERT INTO `users` VALUES (7, NULL, NULL, NULL, 'yu.yijuan', 'ting35@example.org', '戚瑜玲', NULL, NULL, NULL, '', NULL, '南宮蓉', NULL, NULL, NULL, 0, NULL, NULL, 'email', 'active', NULL, 1, NULL, NULL, '2025-11-30 18:26:28', '2025-11-30 18:26:28');
INSERT INTO `users` VALUES (8, NULL, NULL, NULL, 'xinhua.ha', 'yanjun.zhongli@example.org', '葉信', NULL, NULL, NULL, '', '(01)4479657', '松廷', NULL, NULL, NULL, 0, NULL, NULL, 'email', 'active', NULL, 1, NULL, NULL, '2025-11-30 18:26:28', '2025-11-30 18:26:28');
INSERT INTO `users` VALUES (9, NULL, NULL, NULL, 'meiting32', 'pgong@example.net', '吳建', NULL, NULL, NULL, '', '+886-995-881-919', '符傑', NULL, NULL, NULL, 0, NULL, NULL, 'email', 'active', NULL, 1, NULL, NULL, '2025-11-30 18:26:28', '2025-11-30 18:26:28');
INSERT INTO `users` VALUES (10, NULL, NULL, NULL, 'ihuang', 'rangsi.tingxian@example.org', '柴偉', NULL, NULL, NULL, '', '(08)5487962', NULL, NULL, NULL, NULL, 0, NULL, NULL, 'email', 'active', NULL, 1, NULL, NULL, '2025-11-30 18:26:28', '2025-11-30 18:26:28');
INSERT INTO `users` VALUES (11, NULL, NULL, NULL, 'ting.yu', 'lei.yifen@example.net', '年華', NULL, NULL, NULL, '', NULL, NULL, NULL, NULL, NULL, 0, NULL, NULL, 'email', 'active', NULL, 1, NULL, NULL, '2025-11-30 18:26:28', '2025-11-30 18:26:28');
INSERT INTO `users` VALUES (12, NULL, NULL, NULL, 'rong.lingjing', 'shuai.si@example.org', '沈郁佩', NULL, NULL, NULL, '', '(02)21851091', NULL, NULL, NULL, NULL, 0, NULL, NULL, 'email', 'active', NULL, 1, NULL, NULL, '2025-11-30 18:26:28', '2025-11-30 18:26:28');
INSERT INTO `users` VALUES (13, NULL, NULL, NULL, 'yihui.dou', 'tingwan34@example.org', '第五雅', NULL, NULL, NULL, '', '(02)2913-4757', '談佳', NULL, NULL, NULL, 0, NULL, NULL, 'email', 'active', NULL, 1, NULL, NULL, '2025-11-30 18:26:28', '2025-11-30 18:26:28');
INSERT INTO `users` VALUES (14, NULL, NULL, NULL, 'wei56', 'guan.bai@example.org', '東郭玲儀', NULL, NULL, NULL, '', '(01)133-8915', NULL, NULL, NULL, NULL, 0, NULL, NULL, 'email', 'active', NULL, 1, NULL, NULL, '2025-11-30 18:26:28', '2025-11-30 18:26:28');
INSERT INTO `users` VALUES (15, NULL, NULL, NULL, 'jwei', 'ping.gong@example.org', '石哲瑋', NULL, NULL, NULL, '', NULL, NULL, NULL, NULL, NULL, 0, NULL, NULL, 'email', 'active', NULL, 1, NULL, NULL, '2025-11-30 18:26:28', '2025-11-30 18:26:28');
INSERT INTO `users` VALUES (16, NULL, NULL, NULL, 'ying.rong', 'dbao@example.org', '孔欣文', NULL, NULL, NULL, '', '(024)666833', NULL, NULL, NULL, NULL, 0, NULL, NULL, 'email', 'active', NULL, 1, NULL, NULL, '2025-11-30 18:26:28', '2025-11-30 18:26:28');
INSERT INTO `users` VALUES (17, NULL, NULL, NULL, 'yisi50', 'jiexian.xiang@example.org', '甘霖', NULL, NULL, NULL, '', '0936431753', NULL, NULL, NULL, NULL, 0, NULL, NULL, 'email', 'active', NULL, 1, NULL, NULL, '2025-11-30 18:26:28', '2025-11-30 18:26:28');
INSERT INTO `users` VALUES (18, NULL, NULL, NULL, 'feng.han', 'wan79@example.net', '禹思傑', NULL, NULL, NULL, '', '0901789591', NULL, NULL, NULL, NULL, 0, NULL, NULL, 'email', 'active', NULL, 1, NULL, NULL, '2025-11-30 18:26:28', '2025-11-30 18:26:28');
INSERT INTO `users` VALUES (19, NULL, NULL, NULL, 'hanxin59', 'jianming.chi@example.com', '范欣', NULL, NULL, NULL, '', NULL, NULL, NULL, NULL, NULL, 0, NULL, NULL, 'email', 'active', NULL, 1, NULL, NULL, '2025-11-30 18:26:28', '2025-11-30 18:26:28');
INSERT INTO `users` VALUES (20, NULL, NULL, NULL, 'ting31', 'weiyu.rong@example.com', '姚冠', NULL, NULL, NULL, '', '(072)018-616', '席宜', NULL, NULL, NULL, 0, NULL, NULL, 'email', 'active', NULL, 1, NULL, NULL, '2025-11-30 18:26:28', '2025-11-30 18:26:28');
INSERT INTO `users` VALUES (21, NULL, NULL, NULL, 'yuping.xu', 'yi65@example.com', '康銘', NULL, NULL, NULL, '', '0915-384-175', '桓君', NULL, NULL, NULL, 0, NULL, NULL, 'email', 'active', NULL, 1, NULL, NULL, '2025-11-30 18:26:28', '2025-11-30 18:26:28');
INSERT INTO `users` VALUES (22, NULL, NULL, NULL, 'qdan', 'pei.huo@example.org', '游雅淑', NULL, NULL, NULL, '', NULL, NULL, NULL, NULL, NULL, 0, NULL, NULL, 'email', 'active', NULL, 1, NULL, NULL, '2025-11-30 18:26:28', '2025-11-30 18:26:28');
INSERT INTO `users` VALUES (23, NULL, NULL, NULL, 'cxun', 'gongxi.yingsi@example.com', '薊嘉心', NULL, NULL, NULL, '', NULL, NULL, NULL, NULL, NULL, 0, NULL, NULL, 'email', 'active', NULL, 1, NULL, NULL, '2025-11-30 18:26:28', '2025-11-30 18:26:28');
INSERT INTO `users` VALUES (24, NULL, NULL, NULL, 'xjing', 'qiu.huisi@example.com', '薄文', NULL, NULL, NULL, '', '(05)670-1063', NULL, NULL, NULL, NULL, 0, NULL, NULL, 'email', 'active', NULL, 1, NULL, NULL, '2025-11-30 18:26:28', '2025-11-30 18:26:28');
INSERT INTO `users` VALUES (25, NULL, NULL, NULL, 'pei.tao', 'fensi71@example.net', '萬如華', NULL, NULL, NULL, '', NULL, NULL, NULL, NULL, NULL, 0, NULL, NULL, 'email', 'active', NULL, 1, NULL, NULL, '2025-11-30 18:26:28', '2025-11-30 18:26:28');
INSERT INTO `users` VALUES (26, NULL, NULL, NULL, 'jiaya.er', 'rmo@example.net', '從穎', NULL, NULL, NULL, '', '(00)4287038', '安芳宜', NULL, NULL, NULL, 0, NULL, NULL, 'email', 'active', NULL, 1, NULL, NULL, '2025-11-30 18:26:28', '2025-11-30 18:26:28');
INSERT INTO `users` VALUES (27, NULL, NULL, NULL, 'zhicheng.cui', 'rong.shu@example.org', '桓琬娟', NULL, NULL, NULL, '', '0993-217-238', NULL, NULL, NULL, NULL, 0, NULL, NULL, 'email', 'active', NULL, 1, NULL, NULL, '2025-11-30 18:26:28', '2025-11-30 18:26:28');
INSERT INTO `users` VALUES (28, NULL, NULL, NULL, 'jin.jiaxuan', 'xiaoyu.song@example.net', '危宏', NULL, NULL, NULL, '', NULL, NULL, NULL, NULL, NULL, 0, NULL, NULL, 'email', 'active', NULL, 1, NULL, NULL, '2025-11-30 18:26:28', '2025-11-30 18:26:28');
INSERT INTO `users` VALUES (29, NULL, NULL, NULL, 'gong.jialing', 'ji.xinan@example.net', '廣文', NULL, NULL, NULL, '', NULL, NULL, NULL, NULL, NULL, 0, NULL, NULL, 'email', 'active', NULL, 0, NULL, NULL, '2025-11-30 18:26:28', '2025-11-30 18:26:28');
INSERT INTO `users` VALUES (30, NULL, NULL, NULL, 'ming97', 'ting77@example.org', '孟詩宏', NULL, NULL, NULL, '', '(083)388-036', '苗宗', NULL, NULL, NULL, 0, NULL, NULL, 'email', 'active', NULL, 1, NULL, NULL, '2025-11-30 18:26:28', '2025-11-30 18:26:28');
INSERT INTO `users` VALUES (31, NULL, NULL, NULL, 'guanwei.jiang', 'meizhe.niu@example.net', '皇甫翰宏', NULL, NULL, NULL, '', '0961270511', NULL, NULL, NULL, NULL, 0, NULL, NULL, 'email', 'active', NULL, 1, NULL, NULL, '2025-11-30 18:26:28', '2025-11-30 18:26:28');
INSERT INTO `users` VALUES (32, NULL, NULL, NULL, 'mei.gai', 'yuyi09@example.com', '空筱', NULL, NULL, NULL, '', '+886-954-997-374', '聞如', NULL, NULL, NULL, 0, NULL, NULL, 'email', 'active', NULL, 1, NULL, NULL, '2025-11-30 18:26:28', '2025-11-30 18:26:28');
INSERT INTO `users` VALUES (33, NULL, NULL, NULL, 'pying', 'bji@example.org', '沈賢', NULL, NULL, NULL, '', '(088)603-900', NULL, NULL, NULL, NULL, 0, NULL, NULL, 'email', 'active', NULL, 1, NULL, NULL, '2025-11-30 18:26:28', '2025-11-30 18:26:28');
INSERT INTO `users` VALUES (34, NULL, NULL, NULL, 'pgou', 'nhou@example.net', '和樺', NULL, NULL, NULL, '', '0902-350-119', NULL, NULL, NULL, NULL, 0, NULL, NULL, 'email', 'active', NULL, 1, NULL, NULL, '2025-11-30 18:26:28', '2025-11-30 18:26:28');
INSERT INTO `users` VALUES (35, NULL, NULL, NULL, 'yang.lin', 'wei43@example.net', '晏嘉君', NULL, NULL, NULL, '', '+886909416929', '成芬欣', NULL, NULL, NULL, 0, NULL, NULL, 'email', 'active', NULL, 1, NULL, NULL, '2025-11-30 18:26:28', '2025-11-30 18:26:28');
INSERT INTO `users` VALUES (36, NULL, NULL, NULL, 'jiayi76', 'yan.baixian@example.org', '蒼穎', NULL, NULL, NULL, '', '(02)7350-6774', NULL, NULL, NULL, NULL, 0, NULL, NULL, 'email', 'active', NULL, 1, NULL, NULL, '2025-11-30 18:26:28', '2025-11-30 18:26:28');
INSERT INTO `users` VALUES (37, NULL, NULL, NULL, 'xuan82', 'clong@example.com', '荀宜', NULL, NULL, NULL, '', NULL, NULL, NULL, NULL, NULL, 0, NULL, NULL, 'email', 'active', NULL, 0, NULL, NULL, '2025-11-30 18:26:28', '2025-11-30 18:26:28');
INSERT INTO `users` VALUES (38, NULL, NULL, NULL, 'qin.lin', 'lingfang53@example.net', '戚萍', NULL, NULL, NULL, '', NULL, NULL, NULL, NULL, NULL, 0, NULL, NULL, 'email', 'active', NULL, 1, NULL, NULL, '2025-11-30 18:26:28', '2025-11-30 18:26:28');
INSERT INTO `users` VALUES (39, NULL, NULL, NULL, 'yawen57', 'an94@example.com', '鄔彥霖', NULL, NULL, NULL, '', NULL, NULL, NULL, NULL, NULL, 0, NULL, NULL, 'email', 'active', NULL, 1, NULL, NULL, '2025-11-30 18:26:28', '2025-11-30 18:26:28');
INSERT INTO `users` VALUES (40, NULL, NULL, NULL, 'weixian.zhao', 'huang.xin@example.com', '鮮于婉', NULL, NULL, NULL, '', NULL, NULL, NULL, NULL, NULL, 0, NULL, NULL, 'email', 'active', NULL, 1, NULL, NULL, '2025-11-30 18:26:28', '2025-11-30 18:26:28');
INSERT INTO `users` VALUES (41, NULL, NULL, NULL, 'tpang', 'zluo@example.com', '歐鈺', NULL, NULL, NULL, '', '+886968109916', '貢惠依', NULL, NULL, NULL, 0, NULL, NULL, 'email', 'active', NULL, 0, NULL, NULL, '2025-11-30 18:26:28', '2025-11-30 18:26:28');
INSERT INTO `users` VALUES (42, NULL, NULL, NULL, 'yi.peixin', 'mei46@example.org', '馮承', NULL, NULL, NULL, '', '+886928380070', '容安', NULL, NULL, NULL, 0, NULL, NULL, 'email', 'active', NULL, 1, NULL, NULL, '2025-11-30 18:26:28', '2025-11-30 18:26:28');
INSERT INTO `users` VALUES (43, NULL, NULL, NULL, 'jia77', 'guliang.fen@example.org', '巫馬宜', NULL, NULL, NULL, '', NULL, '麴庭瑋', NULL, NULL, NULL, 0, NULL, NULL, 'email', 'active', NULL, 1, NULL, NULL, '2025-11-30 18:26:28', '2025-11-30 18:26:28');
INSERT INTO `users` VALUES (44, NULL, NULL, NULL, 'hanming.she', 'xuan.long@example.com', '國美涵', NULL, NULL, NULL, '', '0964857169', NULL, NULL, NULL, NULL, 0, NULL, NULL, 'email', 'active', NULL, 1, NULL, NULL, '2025-11-30 18:26:28', '2025-11-30 18:26:28');
INSERT INTO `users` VALUES (45, NULL, NULL, NULL, 'sijia53', 'jiazong.gou@example.net', '封筑', NULL, NULL, NULL, '', '(074)962-417', NULL, NULL, NULL, NULL, 0, NULL, NULL, 'email', 'active', NULL, 1, NULL, NULL, '2025-11-30 18:26:28', '2025-11-30 18:26:28');
INSERT INTO `users` VALUES (46, NULL, NULL, NULL, 'yan13', 'xinguan.gongliang@example.com', '金萍怡', NULL, NULL, NULL, '', NULL, '秋欣', NULL, NULL, NULL, 0, NULL, NULL, 'email', 'active', NULL, 1, NULL, NULL, '2025-11-30 18:26:28', '2025-11-30 18:26:28');
INSERT INTO `users` VALUES (47, NULL, NULL, NULL, 'bai.fang', 'nxun@example.net', '秋宏偉', NULL, NULL, NULL, '', '(02)2569-5137', NULL, NULL, NULL, NULL, 0, NULL, NULL, 'email', 'active', NULL, 1, NULL, NULL, '2025-11-30 18:26:28', '2025-11-30 18:26:28');
INSERT INTO `users` VALUES (48, NULL, NULL, NULL, 'wanqi.hong', 'vzhuansun@example.org', '東門建志', NULL, NULL, NULL, '', NULL, '卜建偉', NULL, NULL, NULL, 0, NULL, NULL, 'email', 'active', NULL, 1, NULL, NULL, '2025-11-30 18:26:28', '2025-11-30 18:26:28');
INSERT INTO `users` VALUES (49, NULL, NULL, NULL, 'qi48', 'ya23@example.org', '佴宇詩', NULL, NULL, NULL, '', NULL, NULL, NULL, NULL, NULL, 0, NULL, NULL, 'email', 'active', NULL, 1, NULL, NULL, '2025-11-30 18:26:28', '2025-11-30 18:26:28');
INSERT INTO `users` VALUES (50, NULL, NULL, NULL, 'jlong', 'zongzheng.zhenying@example.org', '仇琬', NULL, NULL, NULL, '', NULL, '莊慧', NULL, NULL, NULL, 0, NULL, NULL, 'email', 'active', NULL, 0, NULL, NULL, '2025-11-30 18:26:28', '2025-11-30 18:26:28');

-- ----------------------------
-- Table structure for users_bak
-- ----------------------------
DROP TABLE IF EXISTS `users_bak`;
CREATE TABLE `users_bak`  (
  `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT,
  `type` enum('account','staff') CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NULL DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`) USING BTREE
) ENGINE = InnoDB AUTO_INCREMENT = 1 CHARACTER SET = utf8mb4 COLLATE = utf8mb4_unicode_ci ROW_FORMAT = Dynamic;

-- ----------------------------
-- Records of users_bak
-- ----------------------------

SET FOREIGN_KEY_CHECKS = 1;
