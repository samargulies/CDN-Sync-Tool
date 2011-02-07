<?php

	/**
	 * The master plugin class
	 * 
	 * @author Iain Cambridge
	 * @package CDN Sync Tool
	 */

class Cst_Plugin {

	protected $object;
	
	/**
	 * Returns true if WP Super Cache is installed 
	 * or false if it's not.
	 * 
	 * @return Boolean
	 */
	
	public static function checkDependices(){
		
		$activePlugins = self::getActivePlugins();
		
		$status = false;
		foreach( $activePlugins as $plugin ){
			// First check is for WP Super Cache
			if ( preg_match("~wp\-cache\.php$~isU",$plugin) ){
				if ( defined("WP_CACHE") && WP_CACHE == true ){
					
					$status = true;
					Cst_Debug::addLog("WP Super Cache install found");
				
				} 
				break;
							
			}
		}
		
		return $status;
		
	}
	
	/**
	 * Getter function to allow tests 
	 * access to the Plugin Object.
	 * 
	 * @since 1.3
	 */
	
	public function getObject(){
		return $this->object;
	}
	
	/**
	 * Fetches the current active plugins from network wide activated,
	 * site activated and must use (mu-plugins).
	 * 
	 * @return Array
	 */
	
	public static function getActivePlugins(){
		
		global $wpdb;
		$activePlugins = (is_array(get_site_option("active_sitewide_plugins")) === true) ? array_keys(get_site_option("active_sitewide_plugins")) : array();
		$activePlugins = array_merge( $activePlugins , get_option("active_plugins") );	
		$activePlugins = array_merge( $activePlugins , array_keys(get_mu_plugins()));	
		
		Cst_Debug::addLog("Active plugins found are ".print_r($activePlugins,true));
		
		return $activePlugins;
	}
	
	/**
	 * Creates the plugin object. A Cst_Plugin_Admin if we're
	 * in the admin dashboard. Cst_Plugin_Site if we're not.
	 * 
	 */
	
	public function __construct(){
		
		if ( is_admin() ){
			
			require_once CST_DIR.'/lib/Cst/Plugin/Admin.php';
			$this->object = new Cst_Plugin_Admin();
			Cst_Debug::addLog("Admin object created");
			
		} else {
			
			require_once CST_DIR.'/lib/Cst/Plugin/Site.php';
			$this->object = new Cst_Plugin_Site();
			Cst_Debug::addLog("Main site object created");
		}
	
		$this->object->addHooks();
		
		return;
	}
	
}

?>