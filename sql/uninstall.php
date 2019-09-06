<?php
//elimina las tablas creadas en el script de instalacion install.sql
$sql = array(
		'DROP TABLE '._DB_PREFIX_.'hn24_transacciones',
		'DROP TABLE '._DB_PREFIX_.'hn24_productos',
);

foreach ($sql as $query)
	if (Db::getInstance()->execute($query) == false)
		return false;
