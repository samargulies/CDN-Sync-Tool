<?php

	/**
	 * The master plugin class
	 * 
	 * @author Iain Cambridge
	 * @package CDN Sync Tool
	 */

class Cst_Plugin {

	protected $object;
	
	
	
	public static function checkDependices(){
		
		$activePlugins = self::getActivePlugins();
		
		$status = false;
		foreach( $activePlugins as $plugin ){
			// First check is for WP Super Cache
			if ( preg_match("~wp\-cache\.php$~isU",$plugin) ){
				Cst_Debug::addLog("WP Super Cache install found");
				if ( defined("WP_CACHE") && WP_CACHE == true ){
					$status = true;
				} 
				break;
							
			}
		}
		
		return $status;
		
	}
	
	public static function getActivePlugins(){
		
		global $wpdb;
		$activePlugins = (is_array(get_site_option("active_sitewide_plugins")) === true) ? array_keys(get_site_option("active_sitewide_plugins")) : array();
		$activePlugins = array_merge( $activePlugins , get_option("active_plugins") );	
		$activePlugins = array_merge( $activePlugins , array_keys(get_mu_plugins()));	
		
		return $activePlugins;
	}
	
	public function __construct(){
		
		if ( is_admin() ){
			require_once CST_DIR.'/lib/Cst/Plugin/Admin.php';
			$this->object = new Cst_Plugin_Admin();
		} else {
			require_once CST_DIR.'/lib/Cst/Plugin/Site.php';
			$this->object = new Cst_Plugin_Site();
		}
	
		$this->object->addHooks();
	
	}
	
}

?>