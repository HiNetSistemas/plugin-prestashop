<?php

require_once(dirname(__FILE__).'../../../classes/ConectionClass.php');
require_once(dirname(__FILE__).'../../../classes/Hn24Productos.php');

class AdminProductosController extends ModuleAdminController
{
    public function __construct()
    {
		$this->table = 'hn24_productos';
		$this->className = 'Hn24Productos';
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
		
		$sincroniza = Tools::getValue('sincroniza_productos');
		if($sincroniza == 1){
			$this->sincroniza_productos();
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
                'id_hn24_productos' => array(
                    'title' => $this->l('ID'),
                    'align' => 'center',
                    'width' => 10,
                ),
                'codigo' => array(
                    'title' => $this->l('Codigo'),
                    'width' => 90,
                ),
				'Descrip1' => array(
					'title' => $this->l('Descripcion'),
					'width' => 70,
				),
				'stock' => array(
					'title' => $this->l('Stock'),
					'width' => 30,
				),
				'precio' => array(
					'title' => $this->l('Precio'),
					'width' => 30
				),
				'iva' => array(
					'title' => $this->l('Iva'),
					'width' => 30,
				),
                'estado' => array(
                    'title' => $this->l('Estado'),
                    'width' => 10,
					'align' => 'center', 
					'active' => 'active', 
					'type' => 'bool',
                ),
				
        );
		
		$lists = parent::renderList();
        parent::initToolbar();

        return $lists;
	}
	
	public function initPageHeaderToolbar()
    {
        if (empty($this->display)) {
            $this->page_header_toolbar_btn['sincroniza_productos'] = array(
                    'href' => self::$currentIndex.'&sincroniza_productos=1&token='.$this->token,
                    'desc' => $this->l('Sincronizar', null, null, false),
                    'icon' => 'process-icon-refresh'
                );
        }
        parent::initPageHeaderToolbar();
    }
	
	public static function sincroniza_productos()
	{
		//set_time_limit(600);
		ConectionClass::setAuthorization(Configuration::get('HN24_API_KEY'));
		
		$result = ConectionClass::get('/productos/'.Configuration::get('HN24_ID_EMPRESA'));
		//print_r($result);
		$resultado = $result['body']->data;
		$id_lang = (int)(Configuration::get('PS_LANG_DEFAULT')); // buscamos el ID del idioma
		$categorias = array();
		foreach($resultado as $r){
			if(!Hn24Productos::existe_hn24($r->Codigo)){
				if(!empty($r->Descrip1)){
					
					$id_category = 2;
					$categorias = array($id_category);
					
					$product = new Product(); //añadimos un nuevo producto
					$product->name = array($id_lang => substr(str_replace("#","-",html_entity_decode($r->Descrip1)),0,128));
					$seo = self::seo_friendly_url($r->Descrip1); // función externa para convertir el nombre en formato URL 
					$product->link_rewrite = array($id_lang => $seo);
					
					$product->id_category = $categorias;
					$product->id_category_default = $id_category;
					$product->minimal_quantity = 1;
					$product->price = ((float)str_replace(',','.',$r->Precio));
					$product->tax_rate = $r->iva;

					$product->description_short = array($id_lang => $r->Descrip1.''.$r->Descrip2);
					$product->description = array($id_lang => $r->Descrip1.' '.$r->Descrip2);
					$product->show_price = 1;
					$product->unit_price = $product->Precio;
					$product->quantity = 0;
					$product->wholesale_price = '0.000000';
					$product->reference = $r->Codigo;
					$product->on_sale = 0;
					$product->available_for_order = 1;
					$product->meta_keywords = $r->Descrip1;
					$product->redirect_type = '404';
					$product->active = 1; // dejamos el producto NO activado, mejor activar despues manualmente				
					
					$product->add();
					$product->addToCategories($categorias);
					
					if($r->imagen != ''){
						$shops = Shop::getShops(true, null, true);    
						$image = new Image();
						$image->id_product = $product->id;
						$image->position = Image::getHighestPosition($product->id) + 1;
						$image->cover = true;  
						if (($image->validateFields(false, true)) === true && ($image->validateFieldsLang(false, true)) === true && $image->add())
						{
							$image->associateTo($shops);
							if (!self::copyImg2($product->id, $image->id, $r->imagen, 'products', false))
							{
							  $image->delete();
							}
						} 
					}
					
					$estado = 1;
					$stock = 0;
					error_log('ADD_PROD['.json_encode($r).']',0);
					Hn24Productos::agregar($r);
				}
			}else{
				if(!empty($r->nombre)){
					
					$dbquery = new DbQuery();
					$dbquery->select('id_contifico_productos')
					->from('contifico_productos')
					->where('contifico_id = \''.$r->id.'\'');
					$id_contifico_productos = Db::getInstance()->getValue($dbquery);
					
					$dbquery0 = new DbQuery();
					$dbquery0->select('id_category')
					->from('contifico_categorias')
					->where('contifico_id = \''.$r->categoria_id.'\'');
					$id_category = Db::getInstance()->getValue($dbquery0);
					$dbquery1 = new DbQuery();
					$dbquery1->select('padre_id')
					->from('contifico_categorias')
					->where('contifico_id = \''.$r->categoria_id.'\'');
					$padre_id = Db::getInstance()->getValue($dbquery1);
					if($padre_id != 0){
						$dbquery2 = new DbQuery();
						$dbquery2->select('id_category')
						->from('contifico_categorias')
						->where('id_contifico_categorias = \''.$padre_id.'\'');
						$id_category2 = Db::getInstance()->getValue($dbquery2);
						$dbquery3 = new DbQuery();
						$dbquery3->select('padre_id')
						->from('contifico_categorias')
						->where('id_contifico_categorias = \''.$padre_id.'\'');
						$padre_id1 = Db::getInstance()->getValue($dbquery3);
						if($padre_id1 != 0){
							$dbquery3 = new DbQuery();
							$dbquery3->select('id_category')
							->from('contifico_categorias')
							->where('id_contifico_categorias = \''.$padre_id1.'\'');
							$id_category3 = Db::getInstance()->getValue($dbquery3);
							$dbquery4 = new DbQuery();
							$dbquery4->select('padre_id')
							->from('contifico_categorias')
							->where('id_contifico_categorias = \''.$padre_id1.'\'');
							$padre_id2 = Db::getInstance()->getValue($dbquery4);
							if($padre_id2 != 0){
								$dbquery5 = new DbQuery();
								$dbquery5->select('id_category')
								->from('contifico_categorias')
								->where('id_contifico_categorias = \''.$padre_id1.'\'');
								$id_category4 = Db::getInstance()->getValue($dbquery5);
								$dbquery6 = new DbQuery();
								$dbquery6->select('padre_id')
								->from('contifico_categorias')
								->where('id_contifico_categorias = \''.$padre_id1.'\'');
								$padre_id3 = Db::getInstance()->getValue($dbquery6);
								if($padre_id3 == 0){
									$categorias = array($id_category,$id_category2,$id_category3,$id_category4);
								}else{
									$categorias = array($id_category,$id_category2,$id_category3,$id_category4);
								}
							}else{
								$categorias = array($id_category,$id_category2,$id_category3);
							}
						}else{
							$categorias = array($id_category,$id_category2);
						}
					}else{
						$categorias = array($id_category);
					}
					
					
					$dbquery10 = new DbQuery();
					$dbquery10->select('id_product')
					->from('contifico_productos')
					->where('contifico_id = \''.$r->id.'\'');
					$id_product = Db::getInstance()->getValue($dbquery10);
					
					$product1 = new Product($id_product);
					$product1->name = array($id_lang => substr(str_replace("#","-",html_entity_decode($r->nombre)),0,128));
					$product1->description_short = array($id_lang => $r->descripcion);
					$product1->description = array($id_lang => $r->descripcion);
					$seo = self::seo_friendly_url($r->nombre); // función externa para convertir el nombre en formato URL 
					$product1->link_rewrite = array($id_lang =>  $seo);
					$product1->price = ((float)str_replace(',','.',$r->pvp1));
					$product1->tax_rate = $r->porcentaje_iva;
					$product1->quantity = $r->cantidad_stock;
					$product1->id_category = $categorias;
					$product1->id_category_default = $id_category;
					$product1->meta_keywords = $r->nombre;
					$product1->reference = $r->codigo;
					
					if($r->estado == 'A'){
					$product1->active = 1; // dejamos el producto NO activado, mejor activar despues manualmente				
					}else{
					$product1->active = 0;
					}
					
					$product1->updateCategories($categorias);
					StockAvailable::setQuantity((int)$product1->id, 0, $product1->quantity, Context::getContext()->shop->id);
					$product1->update();
					
					
					if($r->estado == 'A'){
						$estado = 1;
					}else{
						$estado = 0;
					}
					
					if($r->cantidad_stock < 0){
						$stock = 0;
					}else{
						$stock = $r->cantidad_stock;
					}
					error_log('UPD_PROD['.json_encode($r).']',0);
					ContificoProductos::actualizar($id_contifico_productos,$r->nombre,$r->descripcion,$r->codigo,$r->tipo_producto,$r->imagen,(int)$stock,$r->pvp1,$r->id,'',$r->porcentaje_iva,$id_category,$estado,$id_product);
				}
			}*/
		}
	}
	
	public static function seo_friendly_url($string)
	{
        //De nombre del producto a url, limpieza
        $string = str_replace(array('[\', \']'), '', $string);
        $string = preg_replace('/\[.*\]/U', '', $string);
        $string = preg_replace('/&(amp;)?#?[a-z0-9]+;/i', '-', $string);
        $string = htmlentities($string, ENT_COMPAT, 'utf-8');
        $string = preg_replace('/&([a-z])(acute|uml|circ|grave|ring|cedil|slash|tilde|caron|lig|quot|rsquo);/i', '\\1', $string );
        $string = preg_replace(array('/[^a-z0-9]/i', '/[-]+/') , '-', $string);
        return strtolower(trim($string, '-'));
    }
	
	public static function copyImg2($id_entity, $id_image, $url, $entity = 'products', $regenerate = true) 
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