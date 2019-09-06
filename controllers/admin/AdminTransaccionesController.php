<?php

require_once(dirname(__FILE__).'../../../classes/ConectionClass.php');
require_once(dirname(__FILE__).'../../../classes/Hn24Transacciones.php');

class AdminTransaccionesController extends ModuleAdminController
{
    public function __construct()
    {
        $this->table = 'hn24_transacciones';
		$this->className = 'Hn24Transaccion';
		$this->bootstrap = true;
		$this->deleted = false;
        $this->colorOnBackground = false;
        
        parent::__construct();

		$this->bulk_actions = array(
			'delete' => array(
                'text' => $this->l('Delete selected'),
                'icon' => 'icon-trash',
                'confirm' => $this->l('Delete selected items?')
            )
        );
        
        $sincroniza = Tools::getValue('envia_ordenes');
		if($sincroniza == 1){
			$this->envia_ordenes();
		}
		
        Shop::addTableAssociation($this->table, array('type' => ''));
		$this->context = Context::getContext();
        
    }

    public function renderList() {
            
        $this->addRowAction('edit');
        $this->addRowAction('delete');
        $this->bulk_actions = array(
                'delete' => array(
                    'text' => $this->l('Delete selected'),
                    'confirm' => $this->l('Delete selected items?')
                )
        );

        $this->fields_list = array(
                'id_hn24_transacciones' => array(
                    'title' => $this->l('ID'),
                    'align' => 'center',
                    'width' => 25
                ),
                'fecha_transaccion' => array(
                    'title' => $this->l('Fecha Transaccion'),
                    'width' => 40,
                ),
                'numero_autorizacion' => array(
                    'title' => $this->l('Autorizacion'),
                    'width' => 40,
                ),
                'estado' => array(
                    'title' => $this->l('Estado'),
                    'width' => 20,
                ),
				'subtotal_12' => array(
                    'title' => $this->l('SubTotal'),
                    'width' => 30
                ),
                'iva' => array(
                    'title' => $this->l('Iva'),
                    'width' => 20
                )
        );
		
		$lists = parent::renderList();
        parent::initToolbar();

        return $lists;
	}
    
    public function initPageHeaderToolbar()
    {
        if (empty($this->display)) {
            $this->page_header_toolbar_btn['envia_ordenes'] = array(
                    'href' => self::$currentIndex.'&envia_ordenes=1&token='.$this->token,
                    'desc' => $this->l('Enviar Ordenes', null, null, false),
                    'icon' => 'process-icon-export'
            );
        }
        parent::initPageHeaderToolbar();
    }


    public static function envia_ordenes()
    {
        $sql = new DbQuery();
        $sql->select('id_cart,detalle,documento')
        ->from('contifico_transacciones')
        ->innerJoin('contifico_transaccion','t','t.id_orden = id_cart')
        ->where('id_contifico = \'\'');
        $transacciones = Db::getInstance()->executeS($sql);
    
        foreach($transacciones as $trans)
        {

            $detalle = json_decode($trans['detalle'],true);
            $documento = json_decode($trans['documento'],true);

            $order = new Order($detalle['Order']['id']);

            $customer = new Customer($detalle['Order']['id_customer']);

            $address = new Address($detalle['Order']['id_address_invoice']);
            
            $product_list = $detalle['Order']['product_list'];
            
            $productos = array();
			foreach($product_list as $prod)
			{

				$dbquery = new DbQuery();
				$dbquery->select('contifico_id')
				->from('contifico_productos')
				->where('id_product = \''.$prod['id_product'].'\'');
				$id_producto = Db::getInstance()->getValue($dbquery);

				$dbquery1 = new DbQuery();
				$dbquery1->Select('iva')
				->from('contifico_productos')
				->where('id_product = \''.$prod['id_product'].'\'');
				$iva = Db::getInstance()->getValue($dbquery1);
				
				$productos[] = array(
					"producto_id" => $id_producto,
					"cantidad" => number_format($prod['cart_quantity'],2,'.',''),
					"precio" => number_format($prod['price'],2,'.',''),
					"porcentaje_iva" => number_format($iva,2,'.',''),
					"porcentaje_descuento" => 0.00,
					"base_cero" => 0.00,
					"base_gravable" => number_format($prod['total'],2,'.',''),
					"base_no_gravable" => 0.00
				);
			}
            
            $num = $order->invoice_number;
            
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
				'pos' => Configuration::get('CONTIFICO_API_TOKEN'),
                'fecha_emision' => date("d/m/Y",strtotime($order->date_add)),
                'tipo_documento' => 'FAC',
                'documento' => Configuration::get('CONTIFICO_ESTABLECIMIENTO').'-'.Configuration::get('CONTIFICO_PUNTO_EMISION').'-'.sprintf("%08s", $num),
                'estado' => 'P',
                'electronico' => true,
                'autorizacion' => '',
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
                'descripcion' => 'FACTURA '.Configuration::get('PS_INVOICE_PREFIX').'-'.sprintf("%08s", $num),
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
        
        $sql1 = new DbQuery();
        $sql1->select('id_cart,id_contifico')
        ->from('contifico_transacciones')
        ->innerJoin('contifico_transaccion','t','t.id_orden = id_cart')
        ->where('id_contifico != \'\'');
        $transacciones1 = Db::getInstance()->executeS($sql1);
        
        foreach($transacciones1 as $tra)
        {
            $result = ConectionClass::get('/documento/'.$tra['id_contifico']);
            
            if($result['httpCode'] == 200 && isset($result['body']->id)){

                $sql_up = "UPDATE `"._DB_PREFIX_."contifico_transaccion` SET xml = '".$result['body']->xml."', ride = '".$result['body']->ride."', sincronizada = 1 WHERE id_orden = '".$tra['id_cart']."' ";
                Db::getInstance()->Execute($sql_up);
                error_log('RESULT['.json_encode($result).']',0);

            }else{
                error_log('RESULT['.json_encode($result).']',0);

            }
        }

    }
}