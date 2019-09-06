<?php
/**
 * Clase en donde se guardan las productos
 */

class Hn24Productos extends ObjectModel{
	
	/** @var string Name */
        public $codigo;
        public $Descrip1;
		public $Descrip2;
		public $Descrip3;
		public $Grupo;
		public $imagen;
		public $stock;
		public $precio;
		public $iva;
		public $categoria_id;
		public $empresa_id;
		public $estado;
		public $id_product;

	/**
	 * @see ObjectModel::$definition
	 */
	public static $definition = array(
		'table' => 'hn24_productos',
		'primary' => 'id_hn24_productos',
		'fields' => array(
					'codigo' =>           array('type' => self::TYPE_STRING, 'validate' => 'isString', 'required' => false),
		            'Descrip1' =>         array('type' => self::TYPE_STRING, 'validate' => 'isString', 'required' => false),
					'Descrip2' => 	  	  array('type' => self::TYPE_STRING, 'validate' => 'isString', 'required' => false),
					'Descrip3' =>   	  array('type' => self::TYPE_STRING, 'validate' => 'isString', 'required' => false),
					'grupo' =>			  array('type' => self::TYPE_STRING, 'validate' => 'isString', 'required' => false),
					'imagen' => 		  array('type' => self::TYPE_STRING, 'validate' => 'isString', 'required' => false),
					'stock' =>			  array('type' => self::TYPE_INT, 'validate' => 'isUnsignedInt', 'required' => false),
					'precio' => 		  array('type' => self::TYPE_FLOAT , 'validate' => 'isPrice', 'required' => false),
					'iva' => 			  array('type' => self::TYPE_INT, 'validate' => 'isUnsignedInt', 'required' => false),
					'categoria_id' => 	  array('type' => self::TYPE_INT, 'validate' => 'isUnsignedInt', 'required' => false),
					'empresa_id' => 	  array('type' => self::TYPE_INT, 'validate' => 'isUnsignetInt', 'required' => false),
					'estado' => 		  array('type' => self::TYPE_INT, 'validate' => 'isUnsignedInt', 'required' => false),
					'id_product' => 	  array('type' => self::TYPE_INT, 'validate' => 'isUnsignedInt', 'required' => false),
                ),
	);
	
	/**
	 * Guarda los detalles de una transaccion
	 * @param int $idCart
	 * @param array $options
	 */
	public static function agregar($data)
	{
		$registro = new Hn24Productos();
		$registro ->codigo = $data['codigo'];
		$registro ->Descrip1 = $data['Descrip1'];
		$registro ->Descrip2 = $data['Descrip2'];
		$registro ->Descrip3 = $data['Descrip3'];
		$registro ->Grupo = $data['Grupo'];
		$registro ->imagen = $data['imagen'];
		$registro ->stock = (int)$data['stock'];;
		$registro ->precio = $data['precio'];
		$registro ->iva = $data['iva'];
		$registro ->categoria_id = $data['categoria_id'];
		$registro ->empresa_id = $data['empresa_id'];
		$registro ->estado = $data['estado'];
		$registro ->id_product = $data['id_product'];
		$registro->add();
	}
	
	public static function actualizar($id_producto,$data)
	{ 
		$registro = new Hn24Productos($id_producto);
		$registro ->codigo = $data['codigo'];
		$registro ->Descrip1 = $data['Descrip1'];
		$registro ->Descrip2 = $data['Descrip2'];
		$registro ->Descrip3 = $data['Descrip3'];
		$registro ->Grupo = $data['Grupo'];
		$registro ->imagen = $data['imagen'];
		$registro ->stock = (int)$data['stock'];;
		$registro ->precio = $data['precio'];
		$registro ->iva = $data['iva'];
		$registro ->categoria_id = $data['categoria_id'];
		$registro ->empresa_id = $data['empresa_id'];
		$registro ->estado = $data['estado'];
		$registro ->id_product = $data['id_product'];
		$registro->update();
	}
	
	public static function existe($id_producto)
	{
		$sql = 'SELECT COUNT(*) FROM '._DB_PREFIX_.Hn24Productos::$definition['table'].' WHERE '.Hn24Productos::$definition['primary'].'='.$id_producto;
		
		if (\Db::getInstance()->getValue($sql) > 0)
			return true;
		return false;
	}
	
	public static function existe_hn24($id_producto)
	{
		$sql = 'SELECT COUNT(*) FROM '._DB_PREFIX_.Hn24Productos::$definition['table'].' WHERE codigo =\''.$id_producto.'\'';
		
		if (\Db::getInstance()->getValue($sql) > 0)
			return true;
		return false;
	}
	
	public static function eliminar($id_producto)
	{
		$eliminar = new Hn24Productos($id_producto);
		$eliminar->delete();
	}
}