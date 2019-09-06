<?php
require_once(dirname(__FILE__).'/../../../config/config.inc.php');

class Hn24Forms {
	/**
	 * Genera los form fields necesarios para crear un formulario
	 */
	public static function getFormFields($titulo, $inputs)
	{

		//solo para las credenciales
		//mejorar este codigo

		$elements = array(
			'form' => array(
				'legend' => array(
						'title' => $titulo,//titulo del form
						'icon' => 'icon-cogs',//icono
				),
				'input' =>$inputs,
				'submit' => array(
						'title' => 'Guardar',
						'class' => 'button'
				)
			)
		);

		return $elements;
	}

	/**
	 * @return un array con los campos del formulario
	 */
	public static function getConfigFormInputs()
	{
            
		return array(
				array(
						'type' => 'switch',
						'label' =>'Activo',
						'name' =>  'status',
						'desc' => 'Activa y desactiva la integracion con Hn24',
						'is_bool' => true,
						'values' => array(
								array(
										'id' => 'active_on',
										'value' => true,
										'label' =>'SI'
								),
								array(
										'id' => 'active_off',
										'value' => false,
										'label' =>'NO'
								)
						),
						'required' => false
				),
				array(
						'type' => 'text',
						'label' => 'Api Key',
						'name' => 'api_key',
						'desc' => 'Api Key Otorgado por Hn24.',
						'required' => true
				),
				array(
						'type' => 'text',
						'label' => 'Url Api',
						'name' => 'api_url',
						'desc' => 'Url para Conectar a la Api de Hn24.',
						'required' => true
				),
				array(
						'type' => 'text',
						'label' => 'Punto Emision',
						'name' => 'punto_emision',
						'desc' => 'Punto de Venta para Emision de Factura.',
						'required' => true
				
				),
				array(
						'type' => 'text',
						'label' => 'ID Empresa',
						'name' => 'id_empresa',
						'desc' => 'Id Empresa Otorgado por Hn24',
						'required' => true
				),
				array(
						'type' => 'switch',
						'label' =>'Sincronizar Productos automaticamente con Hn24.',
						'name' =>  'sincro_productos',
						'is_bool' => true,
						'values' => array(
								array(
										'id' => 'on',
										'value' => true,
										'label' =>'SI'
								),
								array(
										'id' => 'off',
										'value' => false,
										'label' =>'NO'
								)
						),
						'required' => true
				),
				array(
						'type' => 'switch',
						'label' => 'Enviar a Facturar automaticamente al realizar un Pedido con Pago Aprobado a Hn24.',
						'name' => 'factura_automatica',
						'is_bool' => false,
						'values' => array(
								array(
										'id' => 'on',
										'value' => true,
										'label' => 'SI'
								),
								array(
										'id' => 'off',
										'value' => false,
										'label' => 'NO'
								)
						),
						'required' => true
				)
		);
	}

	/**
	 * Devuelve los nombres de los inputs que existen en el form
	 * @param array $inputs campos de un formulario
	 * @return un array con los nombres
	 */
	public static function getFormInputsNames($inputs)
	{
		$nombres=array();

		foreach ($inputs as $campo)
		{
			if (array_key_exists('name', $campo))
			{
				$nombres[] = $campo['name'];
			}
		}

		return $nombres;
	}

	/**
	 * Escribe en la base de datos los valores de tablas de configuraciones
	 * @param string $prefijo prefijo con el que se identifica al formulario en la tabla de configuraciones. Ejemplo: DECIDIR_TEST
	 * @param array $inputsName resultado de la funcion getFormInputsNames
	 */
	public static function postProcessFormularioConfigs($prefijo, $inputsName)
	{
		foreach ($inputsName as $nombre)
		{
			//mejorarlo este codigo
			if($nombre == "authorization"){

				$auth = \Tools::getValue($nombre);
				if(json_decode($auth) == NULL) {
					//armo json de autorization
					$autorizationId = new \stdClass();
					$autorizationId->Authorization = $auth;
					$auth = json_encode($autorizationId);
				}

				$valueField = $auth;

			}else{
				$valueField = \Tools::getValue($nombre);
			}

			\Configuration::updateValue( $prefijo.'_'.strtoupper( $nombre ), $valueField);

		}
	}

	/**
	 * Trae de los valores de configuracion del modulo, listos para ser usados como fields_value en un form
	 * @param string $prefijo prefijo con el que se identifica al formulario en la tabla de configuraciones. Ejemplo: DECIDIR_TEST
	 * @param array $inputsName resultado de la funcion getFormInputsNames
	 */
	public static function getConfigs($prefijo, $inputsName)
	{
		$configs = array();

		foreach ($inputsName as $nombre)
		{
			$configs[$nombre] = \Configuration::get( $prefijo.'_'.strtoupper( $nombre ));
		}

		return $configs;
	}
	
}
