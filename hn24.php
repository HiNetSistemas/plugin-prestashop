<?php
/**
* 2019 Hinet Sistemas
*
*
*/
if (!defined('_PS_VERSION_')) {
    exit;
}

require_once(dirname(__FILE__).'/classes/ConectionClass.php');
require_once(dirname(__FILE__).'/classes/Hn24Forms.php');
require_once(dirname(__FILE__).'/classes/Hn24Productos.php');
require_once(dirname(__FILE__).'/classes/Hn24Transacciones.php');

class hn24 extends Module{

	public $cron = false;

	public function __construct()
	{
		$this->name = 'hn24'; //nombre del módulo el mismo que la carpeta y la clase.
		$this->tab = 'administration'; // pestaña en la que se encuentra en el backoffice.
		$this->version = '1.1.0'; //versión del módulo
		$this->author ='HiNet Sistemas'; // autor del módulo
		$this->bootstrap = true;
		$this->is_eu_compatible = 0;
		$this->need_instance = 1; //si no necesita cargar la clase en la página módulos,1 si fuese necesario.
		$this->ps_versions_compliancy = array('min' => '1.6', 'max' => _PS_VERSION_); //las versiones con las que el módulo es compatible.
		$this->display = 'view';
		
		parent::__construct();

		$this->displayName = $this->l('Hn24');
		$this->description = $this->l('Integracion con la api de Hn24.');
		$this->confirmUninstall = $this->l('Realmente quiere desinstalar este modulo?');
		
    }

    public function install() {
		$this->createConfigVariables();
		include(dirname(__FILE__).'/sql/install.php');//script sql con la creacion de la tabla transaction
		
		if(!(int)Tab::getIdFromClassName('AdminHn24')) {
            $parent_tab = new Tab();
			$parent_tab->active = 1;
            // Need a foreach for the language
            foreach (Language::getLanguages(true) as $language)
				$parent_tab->name[$language['id_lang']] = $this->l('Hn24');
                $parent_tab->class_name = 'AdminHn24';
                $parent_tab->id_parent = 0; // Home tab
                $parent_tab->module = $this->name;
                $parent_tab->add();
        }

		$tab = new Tab();
		// Need a foreach for the language
		foreach (Language::getLanguages(true) as $language)		
		$tab->name[$language['id_lang']] = $this->l('Manejo de Productos');
		$tab->class_name = 'AdminProductos';
		$tab->id_parent = (int)Tab::getIdFromClassName('AdminHn24');
		$tab->module = $this->name;
		$tab->add();
		
		$tab2 = new Tab();
		// Need a foreach for the language
		foreach (Language::getLanguages(true) as $language)
		$tab2->name[$language['id_lang']] = $this->l('Manejo de Ordenes');
		$tab2->class_name = 'AdminTransacciones';
		$tab2->id_parent = (int)Tab::getIdFromClassName('AdminHn24');
		$tab2->module = $this->name;
		$tab2->add();
		
		
		return parent::install() &&
               $this->registerHook('displayBackOfficeHeader') &&
			   $this->registerHook('displayAdminOrderTabOrder') &&
			   $this->registerHook('displayAdminOrderContentOrder') &&
			   $this->registerHook('displayPaymentReturn') &&
			   $this->registerHook('actionPaymentConfirmation') &&
			   $this->registerHook('actionValidateOrder');

		return true;
    }
    
    public function uninstall() {
		$this->deleteConfigVariables();
		include(dirname(__FILE__).'/sql/uninstall.php');
		
		$tab = new Tab((int)Tab::getIdFromClassName('AdminHn24'));
		$tab->delete();
        $tab = new Tab((int)Tab::getIdFromClassName('AdminProductos'));
        $tab->delete();
        $tab = new Tab((int)Tab::getIdFromClassName('AdminTransacciones'));
        $tab->delete();
		
		return parent::uninstall();
	}
	
	public function getPrefijo($nombre)
	{
		$prefijo = 'HN24';
		$variables = parse_ini_file('config.ini');

		if ( strcasecmp($nombre, 'PREFIJO_CONFIG') == 0)
			return $prefijo;

		foreach($variables as $key => $value){
			if ( strcasecmp($key, $nombre) == 0 )
				return $prefijo.'_'.$value;
		}
		return '';
	}
	
	/**
	 * Crea las variables de configuracion, asi se encuentran todas juntas en la base de datos
	 */
	public function createConfigVariables()
	{
		$prefijo = 'HN24';
		
		foreach ( Hn24Forms::getFormInputsNames( Hn24Forms::getConfigFormInputs() ) as $nombre)
		{
			Configuration::updateValue($prefijo.'_'.strtoupper( $nombre ),'');
		}
	}

	/**
	 * Borra las variables de configuracion de la base de datos
	 */
	public function deleteConfigVariables()
	{
		Db::getInstance()->delete(Configuration::$definition['table'],'name LIKE \'%'.$this->getPrefijo('PREFIJO_CONFIG').'%\'');
	}

    /**
	 * Carga el formulario de configuration del modulo.
	 */
	public function getContent()
	{
		$this->_postProcess();

		$store_url = $this->context->link->getBaseLink();

		$this->context->smarty->assign(array(
			'module_dir' 	 	  => $this->_path,
			'version'    	 	  => $this->version,
			'url_base'			  => "//".Tools::getHttpHost(false).__PS_BASE_URI__,
			'config_general' 	  => $this->renderConfigForms(),
			'hn24_cron'	  => $store_url . 'modules/hn24/hn24-cron.php?token=' . Tools::substr(Tools::encrypt('hn24/cron'), 0, 10) . '&id_shop=' . $this->context->shop->id,
		));
		
		$output = $this->context->smarty->fetch($this->local_path.'views/templates/admin/configure.tpl');//recupero el template de configuracion

		return $output;
	}
	
	/**
	 * @return el html de todos los formularios
	 */
	public function renderConfigForms()
	{
		return $this->renderForm('config');
    }
    
    /**
	 * Crea las opciones para un select
	 * @param array $opciones
	 */
	public function getOptions($opciones)
	{
		$rta = array();

		foreach ($opciones as $item)
		{
				$rta[] = array(
					'id_option' => strtolower($item),
					'name' => $item
				);
		}

		return $rta;
	}
	
	/**
	 * 	Genera el  formulario que corresponda segun la tabla ingresada
	 * @param string $tabla nombre de la tabla
	 * @param array $fields_value
	 */
	public function renderForm($tabla)
	{
		$form_fields = '';

		switch ($tabla)
		{
			case 'config':
				$prefijo = $this->getPrefijo('PREFIJO_CONFIG');
				$form_fields = Hn24Forms::getFormFields('general ', Hn24Forms::getConfigFormInputs());
				break;
		}

		if (isset($prefijo))
			$fields_value= Hn24Forms::getConfigs($prefijo, Hn24Forms::getFormInputsNames($form_fields['form']['input']));


		return $this->getHelperForm($tabla,$fields_value)->generateForm(array($form_fields));
	}
	
	/**
	 * Genera un formulario
	 * @param String $tabla nombre de la tabla que se usa para generar el formulario
	 */
	public function getHelperForm($tabla, $fields_value=NULL)
	{
		$helper = new HelperForm();

		$helper->show_toolbar = false;//no mostrar el toolbar
		$helper->table = $this->table;
		$helper->module = $this;
		$helper->default_form_language = $this->context->language->id;//el idioma por defecto es el que esta configurado en prestashop
		$helper->allow_employee_form_lang = Configuration::get('PS_BO_ALLOW_EMPLOYEE_FORM_LANG', 0);

		$helper->identifier = $this->identifier;
		$helper->submit_action = 'btnSubmit'.ucfirst($tabla);//nombre del boton de submit. Util al momento de procesar el formulario

		//mejorar este codigo, solo para el form de login de credenciales remueve la url y token de action
		if($tabla != "login"){
			$helper->currentIndex = $this->context->link->getAdminLink('AdminModules', false)
			.'&configure='.$this->name.'&tab_module='.$this->tab.'&module_name='.$this->name;
			$helper->token = Tools::getAdminTokenLite('AdminModules');
		}else{
			$helper->currentIndex = "#";
			$helper->token = "";
		}

		if($tabla == "login")
			$fields_value['id_user'] = " ";


		$helper->tpl_vars = array(
				'fields_value' => $fields_value,
				'languages' => $this->context->controller->getLanguages(),
				'id_language' => $this->context->language->id
		);

		return $helper;
	}
	
	/**
	 * recupero y guardo los valores ingresados en el formulario
	 */
	protected function _postProcess()
	{

		if (Tools::isSubmit('btnSubmitConfig'))
		{
			Hn24Forms::postProcessFormularioConfigs($this->getPrefijo('PREFIJO_CONFIG'), Hn24Forms::getFormInputsNames( Hn24Forms::getConfigFormInputs() ) );
		}
	}
	
	/**
	 * Verifica si el modulo esta activo para el usuario final
	 */
	public function isActivo()
	{
		return (boolean)Configuration::get($this->getPrefijo('PREFIJO_CONFIG').'_STATUS');
	}
	
	public function hookDisplayBackOfficeHeader()
	{
		$this->context->controller->addCSS($this->local_path.'css/back.css', 'all');
		$this->context->controller->addJS($this->local_path.'js/back.js', 'all');
    }
    
    /**
	 * Se ejecuta cuando se quiere acceder a la orden desde el backoffice
	 * @param $params un array con los siguientes objetos: order, products y customer
	 */
	public function hookDisplayAdminOrderTabOrder($params)
	{
		$order_id = $params['order']->id_cart;
		if(ContificoTransaccion::existe($order_id)) {
			return $this->display(__FILE__, 'views/templates/admin/order-tab.tpl');//indico la template a utilizar
		}
		return ;
	}
	
	/**
	 * Se ejecuta cuando se quiere acceder a la orden desde el backoffice
	 * @param $params un array con los siguientes objetos: order, products y customer
	 */
	public function hookDisplayAdminOrderContentOrder($params)
	{
		$order_id = $params['order']->id_cart;
		if(!ContificoTransaccion::existe($order_id)) {
			return '';
		}

		return $this->display(__FILE__, 'views/templates/admin/order-content.tpl');//indico la template a utilizar
	}

	public function hookActionValidateOrder($params)
	{
		if (!$this->active || !$this->isActivo()) {
	        return;
		}
		error_log('ORDER_RETURN['.json_encode($params).']',0);

		$order = $params['order'];
		$customer = $params['customer'];
		$orderStatus = $params['orderStatus'];

		$product_list = $order->product_list;

		$detallesOrden = array(
			'Order' => $order,
			'Customer' => $customer,
			'OrderStatus' => $orderStatus
		);

		$total_iva = $order->total_paid - $order->total_paid_tax_excl;
		
		$data = array(
				'id_orden' => $order->id_cart,
				'fecha_emision' => $order->date_add,
				'numero_autorizacion' => '',
				'codigo_error' => '',
				'mensaje_error' => '',
				'codigo_autorizacion' => '',
				'fecha_transaccion' => $order->date_add,
				'estado' => 'PG',
				'subtotal_12' => $order->total_paid_tax_incl,
				'subtotal_0' => $order->total_paid_tax_excl,
				'iva' => $order->carrier_tax_rate,
				'intereses' => 0,
				'total_intereses' => 0,
				'sincronizada' => 0,
				'tipo_pago' => 'TAC',
				'total_iva' => $total_iva,
				'xml' => '',
				'ride' => '',
			);

		if(Configuration::get($this->getPrefijo('PREFIJO_CONFIG').'_FACTURA_AUTOMATICA') == 1){
			
			$address = new Address($order->id_address_invoice);
			$productos = array();
			foreach($product_list as $prod)
			{

				$dbquery = new DbQuery();
				$dbquery->select('codigo')
				->from('hn24_productos')
				->where('id_product = \''.$prod['id_product'].'\'');
				$id_producto = Db::getInstance()->getValue($dbquery);

				$dbquery1 = new DbQuery();
				$dbquery1->Select('iva')
				->from('hn24_productos')
				->where('id_product = \''.$prod['id_product'].'\'');
				$iva = Db::getInstance()->getValue($dbquery1);
				
				$productos[] = array(
					"producto_id" => $id_producto,
					"cantidad" => $prod['cart_quantity'],
					"precio" => $prod['price'],
					"porcentaje_iva" => $iva,
					"porcentaje_descuento" => 0.00,
					"base_cero" => 0.00,
					"base_gravable" => number_format($prod['total'],2,'.',''),
					"base_no_gravable" => 0.00
				);
			}

			$num = 0;
			$dbquery2 = new DbQuery();
			$dbquery2->select('number')
			->from('order_invoice')
			->where('id_order = \''.$order->id_cart.'\'');
			$number = Db::getInstance()->getValue($dbquery2);
			if($number != ''){
				$num = $number;
			}else{
				$dbquery3 = new DbQuery();
    			$dbquery3->select('MAX(number+1) as num')
    			->from('order_invoice');
    			$number = Db::getInstance()->getValue($dbquery3);
    			$num = $number;
			}

			if($order->module == 'ps_wirepayment')
			{
				$cobros = array(
                    'forma_cobro' => "TRA",
					'monto' => number_format($order->total_paid,2,'.',''),
					'fecha' => date('d/m/Y',strtotime($order->date_add)),
					'numero_comprobante' => "",
					'cuenta_bancaria_id' => ""
                );
			}else if($order->module == 'ps_checkpayment'){
				$cobros = array(
                    'forma_cobro' => "CH",
					'monto' => number_format($order->total_paid,2,'.',''),
					'fecha' => date('d/m/Y',strtotime($order->date_add)),
					'numero_cheque' => ""
                );
			}else{
				$cobros = array(
                    'forma_cobro' => "TC",
					'monto' => number_format($order->total_paid,2,'.',''),
					'fecha' => date('d/m/Y',strtotime($order->date_add)),
					'tipo_ping' => "D"
                );
			}
			
			$ivaval = $order->total_paid - $order->total_paid_tax_excl;
            $documento = array(
				'pos' => Configuration::get($this->getPrefijo('PREFIJO_CONFIG').'_API_TOKEN'),
                'fecha_emision' => date("d/m/Y",strtotime($order->date_add)),
                'tipo_documento' => 'FAC',
                'documento' => Configuration::get($this->getPrefijo('PREFIJO_CONFIG').'_ESTABLECIMIENTO').'-'.Configuration::get($this->getPrefijo('PREFIJO_CONFIG').'_PUNTO_EMISION').'-'.sprintf("%08s", $num),
                'estado' => 'P',
                'autorizacion' => '',
				'electronico' => true,
				'caja_id' => null,
                'cliente' => array(
                    'ruc' => '',
                    'cedula' => $customer->cedula,
                    'razon_social' => $customer->firstname.' '.$customer->lastname,
                    'telefonos' => $address->phone,
                    'direccion' => $address->address1,
                    'tipo' => 'N',
                    'email' => $customer->email,
                    'es_extranjero' => false,
                ),
                'descripcion' => Configuration::get('PS_INVOICE_PREFIX').'-'.sprintf("%08s", $num),
                'subtotal_0' => 0.00,
                'subtotal_12' => number_format($order->total_paid_tax_excl,2,'.',''),
                'iva' => number_format($ivaval,2,'.',''),
                'servicio' => 0.00,
                'total' => number_format($order->total_paid,2,'.',''),
                'adicional1' => '',
                'adicional2' => '',
                'detalles' => $productos,
                'cobros' => array($cobros),
			);
			
			ConectionClass::setAuthorization(Configuration::get('CONTIFICO_API_KEY'));

			$result = ConectionClass::post('/documento/',$documento,array());
			if($result['httpCode'] == 201 && isset($result['body']->id)){
				$id_contifico =$result['body']->id;
				error_log('RESULT['.json_encode($result).']',0);
			}else{
				error_log('SEND_DOC['.json_encode($documento).']',0);
				error_log('RESULT['.json_encode($result).']',0);
				$id_contifico = null;
			}

		}

	}

	public function hookActionSetInvoice($params)
	{
		if (!$this->active || !$this->isActivo()) {
			return;
		}

		error_log('INVOICE['.json_encode($params).']',0);

	}
	
	public function hookActionPaymentConfirmation($params)
	{
		if (!$this->active || !$this->isActivo()) {
			error_log('PAYMENT_RETURN['.json_encode($params).']',0);
	        return;
		}
		
		error_log('PAYMENT_RETURN['.json_encode($params).']',0);
		
		return;
	}

	public function hookDisplayPaymentReturn($params)
	{
	    
	    if (!$this->active || !$this->isActivo()) {
			error_log('PAYMENT_RETURN['.json_encode($params).']',0);
	        return;
	    }
		
		error_log('PAYMENT_RETURN['.json_encode($params).']',0);

        $order = $params['order'];

		if(Configuration::get($this->getPrefijo('PREFIJO_CONFIG').'_FACTURA_AUTOMATICA') == 1){

            $customer = new Customer($order->id_customer);
            
            $address = new Address($order->id_address_invoice);

		}
		
		return;
	}
	
	private function compare_presta(){
        return version_compare(_PS_VERSION_, '1.7.0.0');
	}
	
	public function cron_job() {

		if(Configuracion::get('HN24_SINCRO_PRODUCTOS') == 1)
		{

			ConectionClass::setAuthorization(Configuration::get('HN24_API_KEY'));
		
			$result = ConectionClass::get('/producto/');

		}

		return true;
	}

	public static function seo_friendly_url2($string){
        //De nombre del producto a url, limpieza
        $string = str_replace(array('[\', \']'), '', $string);
        $string = preg_replace('/\[.*\]/U', '', $string);
        $string = preg_replace('/&(amp;)?#?[a-z0-9]+;/i', '-', $string);
        $string = htmlentities($string, ENT_COMPAT, 'utf-8');
        $string = preg_replace('/&([a-z])(acute|uml|circ|grave|ring|cedil|slash|tilde|caron|lig|quot|rsquo);/i', '\\1', $string );
        $string = preg_replace(array('/[^a-z0-9]/i', '/[-]+/') , '-', $string);
        return strtolower(trim($string, '-'));
	}
	
	public static function copyImg22($id_entity, $id_image, $url, $entity = 'products', $regenerate = true) 
	{
		$tmpfile = tempnam(_PS_TMP_IMG_DIR_, 'ps_import');
		$watermark_types = explode(',', Configuration::get('WATERMARK_TYPES'));


		switch ($entity) {
			default:
			case 'products':
				$image_obj = new Image($id_image);
				$path = $image_obj->getPathForCreation();
				break;
			case 'categories':
				$path = _PS_CAT_IMG_DIR_ . (int) $id_entity;
				break;
			case 'manufacturers':
				$path = _PS_MANU_IMG_DIR_ . (int) $id_entity;
				break;
			case 'suppliers':
				$path = _PS_SUPP_IMG_DIR_ . (int) $id_entity;
				break;
		}
		$url = str_replace(' ', '%20', trim($url));


		// Evaluate the memory required to resize the image: if it's too much, you can't resize it.
		if (!ImageManager::checkImageMemoryLimit($url))
			return false;


		// 'file_exists' doesn't work on distant file, and getimagesize makes the import slower.
		// Just hide the warning, the processing will be the same.
		if (Tools::copy($url, $tmpfile)) {
			ImageManager::resize($tmpfile, $path . '.jpg');
			$images_types = ImageType::getImagesTypes($entity);


			if ($regenerate)
				foreach ($images_types as $image_type) {
					ImageManager::resize($tmpfile, $path . '-' . stripslashes($image_type['name']) . '.jpg', $image_type['width'], $image_type['height']);
					if (in_array($image_type['id_image_type'], $watermark_types))
						Hook::exec('actionWatermark', array('id_image' => $id_image, 'id_product' => $id_entity));
				}
		}
		else {
			unlink($tmpfile);
			return false;
		}
		unlink($tmpfile);
		return true;
	}
    
}