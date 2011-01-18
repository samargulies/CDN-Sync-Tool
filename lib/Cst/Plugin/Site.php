<?php 

	/**
	 * Handles the plugin's actions in the public 
	 * site's section.
	 * 
	 * @author Iain Cambridge
	 * @package CDN Sync Tool
	 */

class Cst_Plugin_Site {
	
	
	/**
	 * Adds all of the hooks for the site. 
	 * 
	 * @since 0.1
	 */
	
	public function addHooks(){
		
		Cst_Debug::addLog("Action hooked for main site actions");
		
		return add_filter('wpsupercache_buffer', array($this, 'handleBuffer') ) &&
			   add_action('wp_footer', array($this, "showFooter"));
		
	}
	
	/**
	 * Handles the displaying of the support Link
	 * 
	 * @since 0.1
	 */
	public function showFooter(){
		
		Cst_Debug::addLog("Adding footer details");
		echo "<!--- CDN Sync Tool ".CST_VERSION." Developed by iain.cambridge at fubra.com --->";
	
		$general = get_option('cst_general');
		
		if ( is_array($general) && isset($general["support"]) && $general["support"] == "yes"){
			echo '<p style="text-align: center;">Powered by CDN Sync Tools developed by <a href="http://catn.com/">PHP Hosting Experts CatN</a></p>';
		}
	
	}
	
	/**
	 * 
	 * Enter description here ...
	 */
	public function handleBuffer($buffer){
		
			require_once CST_DIR.'/lib/Cst/JsCss.php';
			$buffer = Cst_JsCss::doCombine($buffer,"js");
			$buffer = Cst_JsCss::doCombine($buffer,"css");
			
			return $buffer;
	}
	
}