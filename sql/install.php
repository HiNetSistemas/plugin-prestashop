<?php

$sql = array(
		'CREATE TABLE IF NOT EXISTS `'._DB_PREFIX_.'hn24_productos'.'`(
			    `id_hn24_productos` INT NOT NULL AUTO_INCREMENT,
				`codigo` varchar(50) COLLATE utf8_unicode_ci NOT NULL,
				`Descrip1` varchar(300) COLLATE utf8_unicode_ci NOT NULL,
				`Descrip2` varchar(300) COLLATE utf8_unicode_ci NULL,
				`Descrip3` varchar(300) COLLATE utf8_unicode_ci NULL,
				`Grupo` varchar(6) COLLATE utf8_unicode_ci  NULL,
				`imagen` varchar(100) COLLATE utf8_unicode_ci DEFAULT NULL,
				`stock` int(11) NOT NULL,
				`precio` decimal(10,2) NOT NULL,
				`iva` int(11) DEFAULT NULL,
				`categoria_id` int(10) UNSIGNED NOT NULL,
				`created_at` timestamp NOT NULL,
				`updated_at` timestamp NOT NULL,
				`empresa_id` int(10) UNSIGNED NOT NULL,
				`estado` tinyint(1) DEFAULT 1,
				`id_product` INT NULL,
			    PRIMARY KEY (`id_hn24_productos`)
			)',
		'CREATE TABLE IF NOT EXISTS `'._DB_PREFIX_.'hn24_transacciones'.'` (
			  `id_hn24_transacciones` INT NOT NULL AUTO_INCREMENT,
			  `id_orden` INT NULL,
			  `fecha_emision` datetime DEFAULT NULL,
			  `cae` varchar(100) COLLATE utf8_unicode_ci DEFAULT NULL,
			  `fecha_cae` varchar(50) COLLATE utf8_unicode_ci DEFAULT NULL,
			  `codigo_error` varchar(10) COLLATE utf8_unicode_ci DEFAULT NULL,
			  `mensaje_error` varchar(10) COLLATE utf8_unicode_ci DEFAULT NULL,
			  `fecha_transaccion` datetime DEFAULT NULL,
			  `estado` varchar(5) COLLATE utf8_unicode_ci DEFAULT NULL,
			  `subtotal_12` decimal(10,2) DEFAULT NULL,
			  `subtotal_0` decimal(10,2) DEFAULT NULL,
			  `iva` decimal(10,2) DEFAULT NULL,
			  `sincronizada` tinyint(4) DEFAULT NULL,
			  `tipo_pago` enum(\'TAC\',\'EFE\',\'POS\') COLLATE utf8_unicode_ci DEFAULT \'TAC\',
			  `total_iva` decimal(10,2) DEFAULT NULL,
			  `total` decimal(10,2) DEFAULT NULL,
			  PRIMARY KEY (`id_hn24_transacciones`)
		)'
);

foreach ($sql as $query)
	if (Db::getInstance()->execute($query) == false)
		return false;
