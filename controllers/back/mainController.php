<?php 
/**
 * @author Bernardo Fuentes
 * @since 12/02/2025
 */

require_once(__DIR_MODULES__."itivos_qrcode/classes/itivos_qrcode_codes.php");
require_once("libs/phpqrcode/qrlib.php");

class mainController extends ModulesBackControllers
{
	function __construct()
	{
		$this->is_logged = true;
		$this->ajax_anabled = true;
		$this->type_controller = "backend";
		parent::__construct();
		
		$this->view->assign('page', $this->l("QR Generator"));
	}
	public function index()
	{
		if ($_SERVER['REQUEST_URI'] == "/".__ADMIN__."/module/itivos_qrcode/main") {
			header("Location: ".__URI__.__ADMIN__."/module/itivos_qrcode/main?");
		}
		$this->renderTable(self::protectGenerateTable());
	}
	public function protectGenerateTable()
	{
		$order_by = "id";
		$sort = null;
		$page = 1;
		$show_per_page = 10;
		$search = null;
		$page = 1;
		if (isIsset('order_by')) {
			$order_by = getValue('order_by');
		}
		if (isIsset('sort')) {
			$sort = getValue('sort');
		}
		if (isIsset('page')) {
			$page = getValue('page');
			if (empty($page)) {
				$page = 1;
			}
		}
		if (isIsset('show_per_page')) {
			$show_per_page = getValue('show_per_page');
		}
		if (isIsset('search')) {
			$search = getValue('search');
		}
		$list = itivosQrcodeCodes::getlist($page, $order_by, $sort, $show_per_page, $search);
		if (!empty($list)) {
			$orders = $list['data'];
			unset($list['data']);
		}
		$pagination = $list;
		$page = 1;
		if (isIsset('page')) {
			$page = getValue('page'); 
		}
		if (isIsset('show_per_page')) {
			$uri_show =  __URI__.__ADMIN__."/module/itivos_qrcode/main/add?page=".$page."&show_per_page=".getValue('show_per_page')."&id=";
		}else {
			$uri_show =  __URI__.__ADMIN__."/module/itivos_qrcode/main/add?page=".$page."&id=";
		}
		$this->table = array(
                'table' => array(
                	'extends' => "back",
                    'legend' => array(
                    	'title' => "Listado de QR generados",
                    	'icon' => 'article',
                    ),
                    'buttons_header' => array(
                        array(
                            "key_row" => "id",
                            "class" => "loading_full_screen_enable",
                            "label" => "Agregar",
                            "icon" => "add",
                            "uri" => __URI__.__ADMIN__."/module/".$this->name."/main/add",
                        ),
                    ),
                    'titles' => array(
                    	array(
                    		"label" => "id",
                    		"key" => "id",
                    		"class" => "table-left",
                    	),
                    	array(
                    		"label" => "Nombre",
                    		"key" => "name",
                    		"class" => "table-center",
                    	),
                    	array(
                    		"label" => "Fecha de creación",
                    		"key" => "created",
                    		"class" => "table-center",
                    	),
                    	array(
                    		"label" => "Creado por",
                    		"key" => "create_by",
                    		"class" => "table-center",
                    	),
                    	array(
                    		"label" => "Visitas",
                    		"key" => "views",
                    		"class" => "table-center",
                    	),
                    ),
                    'buttons_row' => array(
                    	array(
                    		"key_row" => "id",
                    		"class" => "button button-secondary loading_full_screen_enable",
                    		"icon" => "edit",
                    		"label" => "",
                    		"uri" => $uri_show,
                    	),
                    	array(
                    		"key_row" => "id",
                    		"class" => "button button-danger confirm_link",
                    		"label" => "",
                    		"attr" => array(
                    			"message_es" => "¿Reaelmente desea eliminar este registro?",
                    			"message_en" => "¿Do you really want to delete this customer?",
                    		),
                    		"icon" => "delete_outline",
                    		"uri" => __URI__.__ADMIN__."/module/itivos_qrcode/main/delete?id=",
                    	),
                    ),
                    'data' => $orders,
                    'pagination' => $pagination,
                    'search' => getValue('search'),
                ),
            );
		return $this->table;
	}
	public function add()
	{
		if (isset($_POST['save_plan'])) {
			self::protectProcessForm();
		}
		$data = array();
		if (isIsset('id')) {
			$data = (array) New itivosQrcodeCodes( (int) getValue('id'));
		}
		$this->form = self::protectGenerateFrom($data);
		$this->html = 
		"
		<div class='menu_app'>
			<nav>
				<ul>
					<li>
						<a href='".__URI__.__ADMIN__."/module/itivos_qrcode/main' class='loading_full_screen_enable'>
						   <i class='material-icons'>arrow_left</i>
							Volver al listado
						</a>
					</li>
				</ul>
			</nav>
		</div>
		";
		if (isIsset('id')) {
			if (!empty($data['path_code'])) {
				$this->html .=
				"
					<div class='main_app_trans'>
						<p>El QR esta Disponible, <a href='".__URI__.__ADMIN__."/module/itivos_qrcode/main/downloadQR?isin={$data['isin']}'>descargar ahora.</a></p>
					</div>
				";
			}
		}
		$this->renderForm();
	}
	public function protectGenerateFrom($data)
	{
		$form = array(
                'form' => array(
                	'type' => "inline",
                	'method' => "POST",
                    'legend' => array(
                    	'title' => $this->l('Información básica'),
                    	'icon' => 'icon-cogs',
                    ),
                    'extends' => "back",
                    'inputs' => array(
                        array(
	                        "type" => "text",
	                        "label" => "Nombre interno",
	                        "required" => true,
	                        "name" => "name",
	                    ),
	                    array(
	                        "type" => "textarea",
	                        "label" => "Enlace",
	                        "name" => "link",
	                        "rows" => 6,
	                    ),
                    ),
                    'values' => $data,
                    'submit' => array(
                        'title' => $this->l('guardar cambios'),
                        'action' => "save_plan"
                    ),
                ),
            );
		return $form;
	}
	public function delete()
	{
		$qr_obj = New itivosQrcodeCodes( (int) getValue('id'));
		if (!empty($qr_obj->id)) {
			if($qr_obj->set_delete()){
				$_SESSION['type_message'] = "success";
		    	$_SESSION['message'] = "Cambios guardados correctamente";
	    	}else {
	    		$_SESSION['type_message'] = "success";
		    	$_SESSION['message'] = "No se realizó ningún cambio";
	    	}
		}
		header("Location: ".__URI__.__ADMIN__."/module/itivos_qrcode/main");
	}
	public function downloadQR()
	{
		$isin = getValue('isin');
		$qr_obj = New itivosQrcodeCodes( getValue('isin'));
		if (empty($qr_obj->id)) {
			http_response_code(404);
	        die('Archivo no encontrado.');
		}
		$path_code = __DIR_UPLOAD__.$qr_obj->path_code;
	    if (!file_exists($path_code)) {
	        http_response_code(404);
	        die('Archivo no encontrado.');
	    }

	    // Obtener el nombre del archivo
	    $filename = basename($path_code);

	    // Configurar los headers para forzar la descarga
	    header('Content-Type: application/octet-stream');
	    header('Content-Disposition: attachment; filename="' . $filename . '"');
	    header('Content-Length: ' . filesize($path_code));
	    header('Cache-Control: no-cache, no-store, must-revalidate');
	    header('Pragma: no-cache');
	    header('Expires: 0');

	    // Leer y enviar el archivo al usuario
	    readfile($path_code);
	    exit;
	}
	public function genQr($isin, $download = false)
	{
		if (file_exists(__DIR_UPLOAD__."/_qrcode_".$isin.".png")) {
			unlink(__DIR_UPLOAD__."/_qrcode_".$isin.".png");
		}
		$path_code = __DIR_UPLOAD__."/_qrcode_".$isin.".png";

		$url = __URI__."es/module/".$this->name."/view_qr?isin={$isin}";
		
		QRcode::png($url, $path_code, QR_ECLEVEL_L, 10);

		if ($download) {
			// Configurar los headers para forzar la descarga
			header('Content-Type: application/octet-stream');
			header('Content-Disposition: attachment; filename="qrcode.png"');
			header('Cache-Control: no-cache, no-store, must-revalidate');
			header('Pragma: no-cache');
			header('Expires: 0');
			// Generar el QR y enviarlo al navegador
			QRcode::png($url);
			exit;

		}
		return str_replace(__DIR_UPLOAD__, "", $path_code);
	}
	public function protectProcessForm()
    {
	    $path_code = "";
    	if (isIsset('id')) {
	    	$qr_obj = New itivosQrcodeCodes( (int) getValue('id'));
    	}else {
	    	$qr_obj = New itivosQrcodeCodes();
	    	$qr_obj->isin = isin();
	    	$user_name = $this->data_login_employee['firstname']. " ". $this->data_login_employee['lastname'];
	    	$qr_obj->created_by = $user_name;
    	}
    	$qr_obj->loadPropertyValues($_POST);
    	$qr_obj->path_code = self::genQr($qr_obj->isin);
    	if( $qr_obj->save() ){
	    	$_SESSION['type_message'] = "success";
	    	$_SESSION['message'] = "Cambios guardados correctamente";
    	}else {
    		$_SESSION['type_message'] = "success";
	    	$_SESSION['message'] = "No se realizó ningun cambio";
    	}
    	if ( !empty($qr_obj->id) ) {
			header("Location: ".__URI__.__ADMIN__."/module/itivos_qrcode/main/add?id=".$qr_obj->id."");
    	}else {
			header("Location: ".__URI__.__ADMIN__."/module/itivos_qrcode/main/add");
    	}
		die();
    }
}
