/*
SQLyog Full v13.2.0 (64 bit)
MySQL - 8.4.4 : Database - veterinaria
*********************************************************************
*/

/*!40101 SET NAMES utf8 */;

/*!40101 SET SQL_MODE=''*/;

/*!40014 SET @OLD_UNIQUE_CHECKS=@@UNIQUE_CHECKS, UNIQUE_CHECKS=0 */;
/*!40014 SET @OLD_FOREIGN_KEY_CHECKS=@@FOREIGN_KEY_CHECKS, FOREIGN_KEY_CHECKS=0 */;
/*!40101 SET @OLD_SQL_MODE=@@SQL_MODE, SQL_MODE='NO_AUTO_VALUE_ON_ZERO' */;
/*!40111 SET @OLD_SQL_NOTES=@@SQL_NOTES, SQL_NOTES=0 */;
CREATE DATABASE /*!32312 IF NOT EXISTS*/`veterinaria` /*!40100 DEFAULT CHARACTER SET latin1 */ /*!80016 DEFAULT ENCRYPTION='N' */;

USE `veterinaria`;

/*Table structure for table `cat_bancos` */

DROP TABLE IF EXISTS `cat_bancos`;

CREATE TABLE `cat_bancos` (
  `id` int NOT NULL AUTO_INCREMENT,
  `nombre_banco` varchar(255) NOT NULL,
  `clave_banco` varchar(3) DEFAULT NULL,
  `status` enum('activo','inactivo') DEFAULT 'activo',
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `clave_banco` (`clave_banco`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1;

/*Table structure for table `cat_bodegas` */

DROP TABLE IF EXISTS `cat_bodegas`;

CREATE TABLE `cat_bodegas` (
  `id_bodega` int NOT NULL AUTO_INCREMENT,
  `nombre_bodega` varchar(100) NOT NULL,
  `responsable` varchar(100) DEFAULT '1',
  `id_responsable` int DEFAULT '1',
  `estatus` tinyint DEFAULT '1',
  `id_proyecto` int DEFAULT NULL,
  PRIMARY KEY (`id_bodega`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1;

/*Table structure for table `cat_clientes` */

DROP TABLE IF EXISTS `cat_clientes`;

CREATE TABLE `cat_clientes` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `razon_social` varchar(255) DEFAULT NULL,
  `rfc` varbinary(14) DEFAULT NULL,
  `nombre_comercial` varchar(200) DEFAULT NULL,
  `calle` varchar(50) DEFAULT NULL,
  `num_ext` varchar(20) DEFAULT NULL,
  `num_int` varchar(20) DEFAULT NULL,
  `colonia` varchar(200) DEFAULT NULL,
  `cp` int DEFAULT NULL,
  `id_estado` int DEFAULT NULL,
  `id_municipio` int DEFAULT NULL,
  `localidad` varchar(200) DEFAULT NULL,
  `correo` varchar(50) DEFAULT NULL,
  `correo_factura` varchar(50) DEFAULT NULL,
  `compras_nombre` varchar(150) DEFAULT NULL,
  `compras_tel` varchar(20) DEFAULT NULL,
  `fecha_alta` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL,
  `estatus` int DEFAULT '1',
  `mapa` varchar(200) DEFAULT NULL,
  `cxc_nombre` varchar(200) DEFAULT NULL,
  `cxc_tel` varchar(20) DEFAULT NULL,
  `operaciones_nombre` varchar(200) DEFAULT NULL,
  `operaciones_tel` varchar(20) DEFAULT NULL,
  `id_residente` int DEFAULT NULL,
  `id_gerente` int DEFAULT NULL,
  `id_empresa` int DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1;

/*Table structure for table `cat_proveedores` */

DROP TABLE IF EXISTS `cat_proveedores`;

CREATE TABLE `cat_proveedores` (
  `id` int NOT NULL AUTO_INCREMENT,
  `id_empresa` int DEFAULT NULL,
  `especialidad` varchar(100) DEFAULT NULL,
  `razon_social` varchar(255) DEFAULT NULL,
  `nombre_Comercial` varchar(255) DEFAULT NULL,
  `tel` varchar(20) DEFAULT NULL,
  `correo` varchar(255) DEFAULT NULL,
  `contacto` varchar(255) DEFAULT NULL,
  `tel_contacto` varchar(50) DEFAULT NULL,
  `calle` varchar(50) DEFAULT NULL,
  `num_ext` varchar(20) DEFAULT NULL,
  `num_int` varchar(20) DEFAULT NULL,
  `colonia` varchar(150) DEFAULT NULL,
  `cp` int DEFAULT NULL,
  `id_estado` int DEFAULT NULL,
  `id_municipio` int DEFAULT NULL,
  `mapa` varchar(255) DEFAULT NULL,
  `estatus` int DEFAULT '1',
  PRIMARY KEY (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1;

/*Table structure for table `cat_puestos` */

DROP TABLE IF EXISTS `cat_puestos`;

CREATE TABLE `cat_puestos` (
  `id` int NOT NULL AUTO_INCREMENT,
  `puesto` varchar(150) DEFAULT NULL,
  `estatus` int DEFAULT '1',
  `hora_extra` decimal(14,2) DEFAULT '0.00',
  PRIMARY KEY (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1;

/*Table structure for table `codigos_postales` */

DROP TABLE IF EXISTS `codigos_postales`;

CREATE TABLE `codigos_postales` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `CP` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `slug` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `municipio_id` bigint unsigned NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=31854 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

/*Table structure for table `colonias` */

DROP TABLE IF EXISTS `colonias`;

CREATE TABLE `colonias` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `nombre` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `slug` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `tipo_asentamiento` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `codigo_postal_id` bigint unsigned NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=148954 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

/*Table structure for table `estados` */

DROP TABLE IF EXISTS `estados`;

CREATE TABLE `estados` (
  `id` int NOT NULL AUTO_INCREMENT,
  `clave` varchar(5) COLLATE utf8mb3_spanish_ci DEFAULT NULL,
  `nombre` varchar(45) COLLATE utf8mb3_spanish_ci DEFAULT NULL,
  `abrev` varchar(16) COLLATE utf8mb3_spanish_ci DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=33 DEFAULT CHARSET=utf8mb3 COLLATE=utf8mb3_spanish_ci;

/*Table structure for table `modulos` */

DROP TABLE IF EXISTS `modulos`;

CREATE TABLE `modulos` (
  `id` int NOT NULL AUTO_INCREMENT,
  `nombre` varchar(100) NOT NULL,
  `archivo` varchar(100) DEFAULT NULL,
  `icono` varchar(100) DEFAULT NULL,
  `modulo_padre` int DEFAULT NULL,
  `tipo_modulo` enum('padre','pagina','lista','submodulo','accion') DEFAULT NULL,
  `canal` enum('erp','app') NOT NULL DEFAULT 'erp',
  `app_id` varchar(50) DEFAULT NULL,
  `soporta_crear` tinyint(1) DEFAULT '1',
  `soporta_editar` tinyint(1) DEFAULT '1',
  `soporta_eliminar` tinyint(1) DEFAULT '1',
  `observaciones` text,
  `app_root_key` varchar(50) GENERATED ALWAYS AS ((case when ((`canal` = _latin1'app') and (`modulo_padre` is null)) then `app_id` else NULL end)) STORED,
  PRIMARY KEY (`id`),
  UNIQUE KEY `uq_modulos_app_root` (`app_root_key`),
  KEY `fk_modulo_padre` (`modulo_padre`),
  KEY `idx_modulos_canal` (`canal`),
  KEY `idx_modulos_canal_app` (`canal`,`app_id`)
) ENGINE=MyISAM AUTO_INCREMENT=2 DEFAULT CHARSET=latin1;

/*Table structure for table `municipios` */

DROP TABLE IF EXISTS `municipios`;

CREATE TABLE `municipios` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `nombre` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `slug` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `estado_id` bigint unsigned NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=2472 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

/*Table structure for table `permisos_rol_modulo` */

DROP TABLE IF EXISTS `permisos_rol_modulo`;

CREATE TABLE `permisos_rol_modulo` (
  `id` int NOT NULL AUTO_INCREMENT,
  `rol` int NOT NULL,
  `modulo` varchar(100) NOT NULL,
  `puede_ver` tinyint(1) DEFAULT '0',
  `puede_crear` tinyint(1) DEFAULT '0',
  `puede_editar` tinyint(1) DEFAULT '0',
  `puede_eliminar` tinyint(1) DEFAULT '0',
  PRIMARY KEY (`id`)
) ENGINE=MyISAM AUTO_INCREMENT=3 DEFAULT CHARSET=latin1;

/*Table structure for table `roles` */

DROP TABLE IF EXISTS `roles`;

CREATE TABLE `roles` (
  `id` int NOT NULL AUTO_INCREMENT,
  `nombre` varchar(50) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM AUTO_INCREMENT=2 DEFAULT CHARSET=latin1;

/*Table structure for table `usuarios` */

DROP TABLE IF EXISTS `usuarios`;

CREATE TABLE `usuarios` (
  `id` int NOT NULL AUTO_INCREMENT,
  `usr` varchar(50) DEFAULT NULL,
  `pwd` varchar(60) DEFAULT NULL,
  `id_personal` int DEFAULT NULL,
  `nombre` varchar(60) DEFAULT NULL,
  `apellido1` varchar(200) DEFAULT NULL,
  `apellido2` varchar(200) DEFAULT NULL,
  `clave` varchar(30) DEFAULT NULL,
  `id_puesto` int DEFAULT NULL,
  `modifica` int DEFAULT NULL,
  `nivel` int DEFAULT '1',
  `estatus` int DEFAULT '1',
  `empresa` int DEFAULT NULL,
  `foto` varchar(50) DEFAULT NULL,
  `fecha_alta` datetime DEFAULT NULL,
  `rol` int DEFAULT NULL,
  `fecha_modifica` datetime DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `uq_usuarios_id_personal` (`id_personal`)
) ENGINE=MyISAM AUTO_INCREMENT=3 DEFAULT CHARSET=latin1;

/*!40101 SET SQL_MODE=@OLD_SQL_MODE */;
/*!40014 SET FOREIGN_KEY_CHECKS=@OLD_FOREIGN_KEY_CHECKS */;
/*!40014 SET UNIQUE_CHECKS=@OLD_UNIQUE_CHECKS */;
/*!40111 SET SQL_NOTES=@OLD_SQL_NOTES */;
