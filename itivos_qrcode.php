<?php
/**
 * @author Bernardo fuentes
 * @since 12/02/25
 */
class ItivosQRcode extends Modules
{	
	public function __construct()
	{
		$this->name ='itivos_qrcode';
		$this->displayName = $this->l('QRGen');
        $this->description = $this->l('Crea QR de cualquier enlace y mide su rendimiento.');
		$this->category  ='front_office_features';
		$this->version ='1.0.0';
		$this->author ='Bernardo Fuentes';
		$this->versions_compliancy = array('min'=>'1.0', 'max'=> __SYSTEM_VERSION__);
        $this->confirmUninstall = $this->l('Are you sure about removing these details?');
		$this->template_dir = __DIR_MODULES__."itivos_qrcode/views/back/";
		$this->template_dir_front = __DIR_MODULES__."itivos_qrcode/views/front/";
		parent::__construct();
		$this->key_module = "f2c05b66221022267afcc62fe5f50f64";
	}
	public function install()
	{	
		if(!$this->registerHook("displayBottom") ||
		   !$this->registerHook("displayHead") ||
		   !$this->installTab(
		   		"ItivosQRcode", 
		   		"QR's", 
		   		"ItivosQRcode", 
		   		"link", 
		   		"main", 
		   		"qr_code") ||
		   !$this->installDb()) {
			return false;
		}
		return true;
	}
	public function uninstall()
    {
    	return self::uninstallDB();
    }
    public function installDb()
	{
		$return = true;
        $return &= connect::execute('
            CREATE TABLE IF NOT EXISTS `'.__DB_PREFIX__.'itivos_qrcode_codes` (
			  `id` INT(11) NOT NULL AUTO_INCREMENT,
			  `isin` varchar(12) NOT NULL,
			  `created_date` datetime NULL DEFAULT CURRENT_TIMESTAMP,
			  `created_by` varchar(650) NULL DEFAULT NULL,
			  `name` varchar(650) NOT NULL,
			  `link` longtext NOT NULL,
			  `path_code` longtext NULL DEFAULT NULL,
			  `status_db` set("enabled", "deleted") DEFAULT "enabled",
			  PRIMARY KEY (id)
			) ENGINE ='.__MYSQL_ENGINE__.' DEFAULT CHARSET=utf8 ;'
        );
        $return &= connect::execute('
            CREATE TABLE IF NOT EXISTS `'.__DB_PREFIX__.'itivos_qrcode_statistics` (
			  `id` INT(11) NOT NULL AUTO_INCREMENT,
			  `id_qr` INT(11) NOT NULL,
			  `created_date` datetime NULL DEFAULT CURRENT_TIMESTAMP,
			  `refer` longtext NOT NULL,
			  `country` varchar(650) NOT NULL,
			  `state` varchar(650) NOT NULL,
			  `ip` varchar(650) NOT NULL,
			  `os` varchar(650) NOT NULL,
			  `status_db` set("enabled", "deleted") DEFAULT "enabled",
			  PRIMARY KEY (id)
			) ENGINE ='.__MYSQL_ENGINE__.' DEFAULT CHARSET=utf8 ;'
		);
        return $return;
	}
	public function uninstallDB($drop_table = true)
    {   
        $return = true;
        if ($drop_table) {
            $return &= connect::execute('DROP TABLE IF EXISTS ' . __DB_PREFIX__. 'itivos_qrcode_codes');
            $return &= connect::execute('DROP TABLE IF EXISTS ' . __DB_PREFIX__. 'itivos_qrcode_statistics');
        }
        return $return;
    }
    public function hookDisplayHead()
    {
    	$this->addJS($this->template_dir_front."js/itivos_qrcode_front.js");
    }
}