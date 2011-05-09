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
		
		$cdn = get_option("cst_cdn");
		
		if ( $cdn['absolute'] == "yes" ){
			add_filter("wpsupercache_buffer", array($this,"changeAbsolutePath"));
		}
		
		return add_action("wp_loaded", array($this, "startObCache") ,9999) &&
			   add_action("wp_footer", array($this, "stopObCache") ,9999) &&
			   add_action('wp_footer', array($this, "showFooter"));
		
	}
	
	public function changeAbsolutePath($html){
		
		global $blog_id;
		
		return preg_replace("~=[\'\"](http://.*/)files/(.*)[\'\"]~isU", "<img src=\"$1/wp-content/blogs.dir/".$blog_id."/files/$2\"",$html);
		
	}
	
	/**
	 * Start output buffering
	 * 
	 * @since 0.1
	 */
	
	public function startObCache(){
		
		 Cst_Debug::addLog("Starting output buffering cache");
		 ob_start( array($this, "callbackObCache") );
		
	}
	
	/**
	 * Handles the combining of the Javascript 
	 * and CSS files.
	 * 
	 * @param string $buffer
	 * @since 0.1
	 */
	public function callbackObCache($buffer){
		
		$files = get_option("cst_files");
		
		if ( isset($files["combine"]) && $files["combine"] == "yes" ){
			require_once CST_DIR.'/lib/Cst/JsCss.php';
			$buffer = Cst_JsCss::doCombine($buffer,"js");
			$buffer = Cst_JsCss::doCombine($buffer,"css");
		}	
		return $buffer;
	}
	
	/**
	 * Stops the caching and flushes 
	 * the content.
	 * 
	 * @since 0.1
	 */
	public function stopObCache(){
		
		ob_end_flush();
		Cst_Debug::addLog("Output buffering stopped");
		return true;
	}
	
	/**
	 * Handles the displaying of the support Link
	 * 
	 * @since 0.1
	 */
	public function showFooter(){
		
		Cst_Debug::addLog("Adding footer details");
		echo "<!-- CDN Sync Tool ".CST_VERSION." Developed by iain.cambridge at fubra.com -->";
	
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
			
			$files = get_option("cst_files");
			if ($files["combine"] !== "yes"){
				return $buffer;
			}
			require_once CST_DIR.'/lib/Cst/JsCss.php';
			$buffer = Cst_JsCss::doCombine($buffer,"css");
			$buffer = Cst_JsCss::doCombine($buffer,"js");
			
			return $buffer;
	}
	
}