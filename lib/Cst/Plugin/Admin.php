<?php

require_once CST_DIR.'/lib/Cst/Sync.php';

	/**
	 * Class to handle all functionality within
	 * the admin dashboard.
	 * 
	 * @author Iain Cambridge
	 * @package CDN Sync Tool
	 */

class Cst_Plugin_Admin {

	/**
	 * Adds all of the hooks, required for 
	 * the plugin within the admin dashboard.
	 * 
	 * @since 0.1
	 */
	
	public function addHooks(){
		
		$extraActions = true;
		if ( isset($_GET['page']) && $_GET['page'] == CST_PAGE_MAIN ){
			$extraActions =	add_action("init", array($this, "addCssAndJs") );
		}
		
		Cst_Debug::addLog("Admin hooks attached");
		
		return add_action("admin_menu", array($this, "menu") ) &&
			   add_action("switch_theme", array($this, "switchTheme") ) &&
			   add_action("admin_init", array($this, "syncFiles")) && 
			   add_action("admin_init", array($this, "preAdminHead")) && 
			   add_filter("wp_generate_attachment_metadata", array($this, "uploadMedia" )) && 
			   add_action("admin_head",array($this, "head" ) ) &&
			   $extraActions;
	}
	
	public function preAdminHead(){
	
		if ( isset($_GET['removetheme']) && $_GET["removetheme"] == "yes"){
			 Cst_Debug::addLog("Removed theme header message");
			 update_option("cst_theme",false);
		}
		
		if ( isset($_GET['removedependency']) && $_GET["removedependency"] == "yes"){
			Cst_Debug::addLog("Removed dependency header message");
			update_option("cst_dependency",true);
		}
		
		if ( (!isset($_GET["page"]) || $_GET["page"] != "cst-main")
		  || (!isset($_GET["subpage"]) || $_GET["subpage"] != "js" ) ){
		  	return;
		  }
		
		$response = array();	
	
		try {
			if ( isset($_GET["type"]) ){
				require_once CST_DIR."/lib/Cdn/Provider.php";
				$provider = Cdn_Provider::getProvider($_GET["type"]);
				$provider->setAccessCredentials($_GET);
				$response["valid"] = $provider->login();
				Cst_Debug::addLog( "CDN Login Credentials check successful result was ".var_export($response["valid"],true) );
			} else {
				$response["valid"] = false;				
				Cst_Debug::addLog("CDN Login failed due to no type given");
			}
		}
		catch ( Exception $e ){
			Cst_Debug::addLog("CDN Login check failed due with '".$e->getMessage()."' message given.");
			$response["valid"] = false;
		}
		print json_encode($response);
		exit;
	}
		
	/**
	 * Handles the syncing of the files.
	 * 
	 * @since 0.1
	 */
	
	public function syncFiles(){
		
		global $wpdb;
		
		if ( 
			!isset($_GET["page"]) || $_GET["page"] != CST_PAGE_MAIN ||
			!isset($_GET["sync"]) || $_GET["sync"] != "yes"
		   ){
		   	// Not the sync'ing request so never mind. 
			return;
		}
		set_time_limit(0);
		Cst_Debug::addLog("Start of syncing, time limit set to 0");
		$fileArrays = Cst_Sync::getFiles();
		$i = 0;
		// Find out the number of items in the sub arrays
		$total = sizeof($fileArrays, COUNT_RECURSIVE) - sizeof($fileArrays);
		Cst_Debug::addLog("Files have been retrived, total count is ".$total);
		//
		$forceOverwrite = ( isset($_GET["force"]) && $_GET["force"] == "yes") ? true : false;
		Cst_Debug::addLog("The force mode for this sync is ".var_export($forceOverwrite,true));
		foreach ( $fileArrays as $key => $files ) {
			$media = ($key === 1) ? true : false; 
			foreach ( $files as $file ){
		
				$file = str_replace(ABSPATH,"",$file);	
				$count = (int)$wpdb->get_var(
								$wpdb->prepare("SELECT COUNT(*) FROM ".CST_TABLE_FILES." WHERE filename = %s AND transferred = 'yes'", 
											array($file))						
								);		
				print "Syncing [".++$i."/".$total."] ".$file;		
				flush();				
				
				if ( $count > 0 && !$forceOverwrite  ){
					print " skipped, already synced".PHP_EOL."<br />";
					Cst_Debug::addLog("File '".$file."' has already been synced so has been skipped");
					flush();
					continue;
				}
				
				$wpdb->query(
						$wpdb->prepare("INSERT INTO ".CST_TABLE_FILES."
										(filename,smushed,transferred) 
										VALUES 
										(%s,'no','no')", 
										array($file)));
					
				Cst_Sync::process($file,$media);
				Cst_Debug::addLog("File '".$file."' has been synced.");
				print " done".PHP_EOL."<br />";
				flush();
			}
		}
		Cst_Debug::addLog("File sync complete.");
		update_option("cst_theme",false);		
		
		// This is to popup and show in an overlay iframe. 
		// So we don't want the rest of the dashboard to load. 
		exit;
	}
	
	/**
	 * Adds the admin dasboard menu.
	 * 
	 * @since 0.1 
	 * @return Boolean true on success or false on failure.
	 * @todo 
	 */
	
	public function menu(){
		
		Cst_Debug::addLog("Adding CST Admin Menus");
		
		return add_menu_page('CDN Sync Tool', 'CDN Sync Tool', 'manage_options', CST_PAGE_MAIN, array($this->showPage("Main"), "display" ) ) &&
		   	   add_submenu_page( CST_PAGE_MAIN , 'Contact' , 'Contact' ,'manage_options' , CST_PAGE_CONTACT , array($this->showPage("Contact"), "display" ) ) && 
			  // add_submenu_page( CST_PAGE_MAIN , 'Help' , 'Help' ,'manage_options' , CST_PAGE_HELP,  array($this->showPage("Help"), "display" ) ) && 
			   add_submenu_page( CST_PAGE_MAIN , 'CatN PHP Experts' , 'CatN' , 'manage_options' , CST_PAGE_CATN, array($this->showPage("Catn"), "display" ) );
		
	}
	
	/**
	 * Adds the JavaScript and CSS files to the 
	 * enqueue system
	 * 
	 *  @since 0.1
	 *  @return True on success and false on failure.
	 */
	
	public function addCssAndJs(){
		
		Cst_Debug::addLog("Enqueuing the CSS and JS for the admin section.");
		// TODO remove pointless ones.
		// CSS files
		wp_enqueue_style("dashboard");
		wp_enqueue_style("thickbox");
		wp_enqueue_style("global");
		wp_enqueue_style("wp-admin");
		wp_enqueue_style("cst-admin", WP_CONTENT_URL."/plugins/".plugin_basename(CST_DIR)."/css/admin.css");
		// JavaScript Files
		wp_enqueue_script("postbox");
		wp_enqueue_script("dashboard");
		wp_enqueue_script("thickbox");
		wp_enqueue_script("media-upload");
		wp_enqueue_script("jquery");
		wp_enqueue_script("cst-admin", WP_CONTENT_URL."/plugins/".plugin_basename(CST_DIR). "/javascript/options.js.php");
		
		return true;
	}
	
	/**
	 * Handles the showing of admin pages.
	 * 
	 * @return Cst_Page|Boolean Returns ethier a Cst_Page object on success or false on failure.
	 * @since 0.1
	 */
	
	public function showPage( $pageName ){
		
		Cst_Debug::addLog("Starting page load for '".$pageName."'");
		require_once CST_DIR.'/lib/Cst/Page.php';
		if ( empty($pageName) ){
			return false;
		}
		
		if ( !is_readable(CST_DIR."/lib/Cst/Page/".$pageName.".php") ){
			Cst_Debug::addLog("Missing the Cst_Page class file for '".$pageName."'");
			return false;
		}
		
		require_once CST_DIR."/lib/Cst/Page/".$pageName.".php";
		
		$className = "Cst_Page_".$pageName;
		if ( !class_exists($className) ){			
			Cst_Debug::addLog("Missing the Cst_Page class code for '".$pageName."'");
			return false;
		}
		
		$pageObject = new $className();
		
		Cst_Debug::addLog("Successful page load for '".$pageName."'");
		return $pageObject;
	}
	
	/**
	 * Handles the displaying of error messages
	 * at the top of the admin page.
	 * 
	 * @return boolean
	 */
	public function head(){
			
		if ( get_option("cst_theme") ){
			Cst_Debug::addLog("System says we don't have the theme files sync'd.");
			echo '<div class="error">Looks like you don\'t have your theme files sync\'d. <a href="admin.php?page='.CST_PAGE_MAIN.'">Click here to Sync them</a>. Or <a href="admin.php?page='.CST_PAGE_MAIN.'&removetheme=yes">click here</a> to remove this message.</div>';
		}
		
		if ( !Cst_Plugin::checkDependices() && !get_option("cst_dependency")){
			Cst_Debug::addLog("System says we don't have WP Super Cache installed");
			echo '<div class="error">Plugin Dependices haven\'t been met, <a href="http://wordpress.org/extend/plugins/wp-super-cache/" target="_blank">WP Super Cache</a> is required. Or <a href="admin.php?page='.CST_PAGE_MAIN.'&removedependency=yes">click here</a> to remove this message.</div>';
		}
		
		true;
	}
	
	/**
	 * Changes the value of cst_theme option
	 * which will allow the head function to
	 * display a message alerting the user
	 * that the theme files haven't been sync'd.
	 * 
	 * @return boolean 
	 */
	public function switchTheme(){
		
		Cst_Debug::addLog("New upload's thumb file '".$size["file"]."' to be sync'd");
		return update_option("cst_theme",true);
		
	}
	
	/**
	 * Handles the new upload files and syncs
	 * them with the CDN.
	 * 
	 * @param array $meta
	 */
	public function uploadMedia($meta){
		
		Cst_Debug::addLog("New upload file '".$meta["file"]."' to be sync'd");
		
		Cst_Sync::process($meta["file"],true);
		
		if ( isset($meta["sizes"]) && is_array($meta["sizes"])
			 && !empty($meta["sizes"]) ){
				foreach ( $meta["sizes"] as $size ){
					
					$dirName = dirname($meta["file"])."/";
		
					Cst_Debug::addLog("New upload's thumb file '".$size["file"]."' to be sync'd");
					Cst_Sync::process($dirName.$size["file"],true);
				
				}	
		}	
		return $meta;
	}
	
}
