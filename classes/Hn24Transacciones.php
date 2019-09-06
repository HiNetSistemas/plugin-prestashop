<?php
/**
 * Clase en donde se guardan las transacciones
 */

class Hn24Transacciones extends ObjectModel{

    /** @var string Name */
    public $id_orden;
    public $fecha_emision;
    public $cae;
    public $fecha_cae;
    public $codigo_error;
    public $mensaje_error;
    public $fecha_transaccion;
    public $estado;
    public $subtotal_12;
    public $subtotal_0;
    public $iva;
    public $sincronizada;
    public $tipo_pago;
    public $total_iva;
    public $total;

    /**
     * @see ObjectModel::$definition
     */
    public static $definition = array(
		'table' => 'hn24_transacciones',
		'primary' => 'id_hn24_transacciones',
		'fields' => array(
                    'id_hn24_transacciones' => array('type' => self::TYPE_INT, 'required' => false),
                    'id_orden' => array('type' => self::TYPE_INT, 'required' => true),
                    'fecha_emision' => array('type' => self::TYPE_DATE, 'required' => false),
                    'cae' => array('type' => self::TYPE_STRING, 'required' => false),
                    'fecha_cae' => array('type' => self::TYPE_STRING, 'required' => false),
                    'codigo_error' => array('type' => self::TYPE_STRING, 'required' => false),
                    'mensaje_error' => array('type' => self::TYPE_STRING, 'required' => false),
                    'fecha_transaccion' => array('type' => self::TYPE_DATE, 'required' => false),
                    'estado' => array('type' => self::TYPE_STRING, 'required' => false),
			        'subtotal_12' => array('type' => self::TYPE_FLOAT, 'required' => false),
		        	'subtotal_0' => array('type' => self::TYPE_FLOAT, 'required' => false),
                    'iva' => array('type' => self::TYPE_FLOAT, 'required' => false),
                    'sincronizada' => array('type' => self::TYPE_INT, 'required' => false),
			        'tipo_pago' => array('type' => self::TYPE_STRING, 'required' => false),
                    'total_iva' => array('type' => self::TYPE_FLOAT, 'required' => false),
                    'total' => array('type' => self::TYPE_FLOAT, 'required' => false),
                ),
    );
    
    /**
	 * Guarda los detalles de una transaccion
	 * @param array $data
	 */
	public static function agregar($data)
	{
        $registro = new Hn24Transacciones();
        $registro ->id_orden = $data['id_orden'];
        $registro ->fecha_emision = $data['fecha_emision'];
        $registro ->cae = $data['cae'];
        $registro ->codigo_error = $data['codigo_error'];
        $registro ->mensaje_error = $data['mensaje_error'];
        $registro ->fecha_cae = $data['fecha_cae'];
        $registro ->fecha_transaccion = $data['fecha_transaccion'];
        $registro ->estado = $data['estado'];
        $registro ->subtotal_12 = $data['subtotal_12'];
        $registro ->subtotal_0 = $data['subtotal_0'];
        $registro ->iva = $data['iva'];
        $registro ->sincronizada = $data['sincronizada'];
        $registro ->tipo_pago = $data['tipo_pago'];
        $registro ->total_iva = $data['total_iva'];
        $registro ->total = $data['total'];
		$registro->add();
    }
    
    public static function actualizar($id_hn24_transacciones, $data)
	{ 
        $registro = new Hn24Transacciones($id_hn24_transacciones);
        $registro ->id_orden = $data['id_orden'];
        $registro ->fecha_emision = $data['fecha_emision'];
        $registro ->cae = $data['cae'];
        $registro ->codigo_error = $data['codigo_error'];
        $registro ->mensaje_error = $data['mensaje_error'];
        $registro ->fecha_cae = $data['fecha_cae'];
        $registro ->fecha_transaccion = $data['fecha_transaccion'];
        $registro ->estado = $data['estado'];
        $registro ->subtotal_12 = $data['subtotal_12'];
        $registro ->subtotal_0 = $data['subtotal_0'];
        $registro ->iva = $data['iva'];
        $registro ->sincronizada = $data['sincronizada'];
        $registro ->tipo_pago = $data['tipo_pago'];
        $registro ->total_iva = $data['total_iva'];
        $registro ->total = $data['total'];
		$registro->update();
	}
	
	public static function existe($id_hn24_transacciones)
	{
		$sql = 'SELECT COUNT(*) FROM '._DB_PREFIX_.Hn24Transacciones::$definition['table'].' WHERE '.Hn24Transacciones::$definition['primary'].'='.$id_hn24_transacciones;
		
		if (\Db::getInstance()->getValue($sql) > 0)
			return true;
		return false;
    }
    
    public static function eliminar($id_hn24_transacciones)
	{
		$registro = new Hn24Transacciones($id_hn24_transacciones);
		$registro->delete();
    }
}