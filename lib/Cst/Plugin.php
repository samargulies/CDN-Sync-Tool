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
				
				if ( defined("WP_CACHE") && WP_CACHE == true ){
					$status = true;
				} 
				break;
							
			}
			
			if ( preg_match("~w3\-total\-cache\.php$~isU",$plugin) ){
				// Just make sure
				// by checking for a valid constant
				if ( defined("W3TC_VERSION") ){
					$status = true;
				}			
			}	
		}
		
		return $status;
		
	}
	
	public static function getActivePlugins(){
		
		global $wpdb;
		$activePlugins = array_keys(get_site_option("active_sitewide_plugins"));
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