<?php 
/**
 * @author Bernardo Fuentes
 * @since 12/02/2025
 */

require_once(__DIR_MODULES__."itivos_qrcode/classes/itivos_qrcode_codes.php");
require_once(__DIR_MODULES__."itivos_qrcode/classes/itivos_qrcode_statistics.php");

require_once("libs/phpqrcode/qrlib.php");

class view_qrController extends ModulesFrontControllers
{
	function __construct()
	{
		$this->is_logged = false;
		$this->type_controller = "frontend";
		parent::__construct();
		
		$this->view->assign('page', $this->l("QR Generator"));
	}
	public function index()
	{
		$qr_obj = new itivosQrcodeCodes(getValue('isin'));
		if (empty($qr_obj->id)) {
			header("Location: ".__URI__."");
		}else {
			$ip = isset($_SERVER['HTTP_CLIENT_IP']) 
					? $_SERVER['HTTP_CLIENT_IP'] 
					: (isset($_SERVER['HTTP_X_FORWARDED_FOR']) 
					  ? $_SERVER['HTTP_X_FORWARDED_FOR'] 
					  : $_SERVER['REMOTE_ADDR']);
			$browser = $_SERVER['HTTP_USER_AGENT'];

			$ip_data ="http://ip-api.com/json/".$ip;

			$ch = curl_init();
			curl_setopt($ch, CURLOPT_URL,$ip_data);
			curl_setopt($ch, CURLOPT_TIMEOUT, 3); //timeout after 30 seconds
			curl_setopt($ch, CURLOPT_RETURNTRANSFER,1);
			curl_exec ($ch);
			$result = json_decode(curl_exec ($ch),true);

			if ($result['status'] == "success") {
				$refer = "";

				// Si existe HTTP_REFERER, lo asignamos
				if (!empty($_SERVER['HTTP_REFERER'])) {
				    $refer = $_SERVER['HTTP_REFERER'];
				}

				// Si en el link existe "refer", obtenemos su valor
				if (isset($qr_obj->link) && str_contains($qr_obj->link, "refer=")) {
				    parse_str(parse_url($qr_obj->link, PHP_URL_QUERY), $queryParams);
				    if (isset($queryParams['refer'])) {
				        $refer = $queryParams['refer'];
				    }
				}

				$itivos_qrcode_statistics = new itivos_qrcode_statistics();
				$itivos_qrcode_statistics->id_qr = $qr_obj->id;
				$itivos_qrcode_statistics->refer = $refer;
				$itivos_qrcode_statistics->country = $result['country'] ."|".$result['countryCode'];
				$itivos_qrcode_statistics->state = $result['city']."|".$result['region'];
				$itivos_qrcode_statistics->ip = $ip;
				$itivos_qrcode_statistics->os = $browser;
				$itivos_qrcode_statistics->save();
			}
			header("Location: ".$qr_obj->link."");
		}
	}
}