<?php
require_once CST_DIR.'/lib/Cst/Image.php';
require_once CST_DIR.'/lib/Cdn/Provider.php';

	/**
	 * The class to handle teh syncing of the 
	 * files to the CDN.
	 * @author Iain Cambridge
	 *
	 */

class Cst_Sync {

	protected static $_files = array();
	
	public static function getFiles(){
		
		if ( isset($_GET["theme"]) && $_GET["theme"] = "yes" ) {
			self::$_files[0] = self::getDirectoryFiles( array(get_template_directory(),get_stylesheet_directory()),true ) 	;
			
		}
		
		if ( isset($_GET["media"]) && $_GET["media"] == "yes" ){
			self::$_files[1] = self::getMediaFiles();
		}
		
		if ( isset($_GET["directory"]) && !empty($_GET["directory"]) && file_exists(ABSPATH.$_GET["directory"]) ){
			self::$_files[2] = self::getDirectoryFiles( array(ABSPATH.$_GET["directory"]), true );
		}
		
		if ( isset($_GET["wpinclude"]) && $_GET["wpinclude"] == "yes" ){
			self::$_files[3] = self::getDirectoryFiles( array(ABSPATH."wp-includes"), false );
		}
		
		if ( isset($_GET['wpplugin']) && $_GET["wpplugin"] == "yes" ){
			$activePlugins = Cst_Plugin::getActivePlugins();
			
			foreach ( $activePlugins as $i => $plugin ) {
				$activePlugins[$i] = dirname(WP_PLUGIN_DIR."/".$plugin);
			}
			
			self::$_files[4] = self::getDirectoryFiles($activePlugins);
		}
		
		if ( isset($_GET['cstcssjs']) && $_GET['cstcssjs'] == "yes" ){			
			$files  = get_option("cst_files");
			self::$_files[5] = array_merge( glob(ABSPATH.$files['directory'] .'/*.js'), glob(ABSPATH.$files['directory'] . '/*.css') );
		}
		return self::$_files;
	}
	
	/**
	 * Fetches the static file from the directories 
	 * defined in the $dirs array. May be used elsewhere
	 * than this class so is a public static function.
	 * 
	 * @param array $dirs
	 * @param boolean $sync
	 * @since 0.1
	 * @return array Full of absolute path of the files.
	 */
	
	public static function getDirectoryFiles( array $dirs,  $sync = false){
		$files = array();
		$images = get_option("cst_images");
		do {
			$newDirs = array();
			foreach($dirs as $dir){
				$myDirectory = opendir($dir);
	
				while($entryName = readdir($myDirectory)) {
					// If is current directory link or level below link ignore.
					if ($entryName == "." || $entryName == ".."){
						continue;
					}
	
					$entryName = $dir."/".$entryName;
	
					if ( is_dir($entryName) ){
						$newDirs[] = $entryName;
					} elseif ( preg_match("~\.(css|js|jpe?g|gif|png)$~isU",$entryName) ){
						// Is a CSS,JS,Jpeg,Gif,Png file
						if ( !is_writable($entryName) && $images['overwrite'] == 'yes'  && $sync === true ){
							print "Error : ".$entryName." is not writable. It must be writable to proceed.<br />";
							print "Try executing: chmod -R 777 ".$dir;
							return $files;		
						} else {
							$files[] = $entryName;
						}
					}
				}
	
				closedir($myDirectory);
			}
			$dirs = $newDirs;
			unset($newDirs);
		} while( !empty($dirs) );
		
		return $files;
	}
	
	/**
	 * Fetches the files that are in the media library.
	 * May at some point want to use this method else 
	 * where so it's a public static function.
	 * 
	 * @return array Full of asbolute paths to the files.
	 * @since 0.1
	 */
	
	public static function getMediaFiles(){
		
		global $wpdb;
		
		$files = $wpdb->get_results("SELECT pmo.meta_value AS filename , pmt.meta_value AS meta 
									 FROM ".$wpdb->postmeta." as pmo 
									 INNER JOIN ".$wpdb->postmeta." as pmt 
									 ON pmt.post_id = pmo.post_id 
									 AND pmt.meta_key = '_wp_attachment_metadata'   
									 WHERE pmo.meta_key = '_wp_attached_file'",ARRAY_A );
				
		$actualFiles = array();		
		foreach ( $files as $file ){			
			$actualFiles[] = $file['filename'];
			$filemeta = unserialize($file['meta']);		
			$dirName = dirname($file['filename'])."/";
			if ( isset($filemeta["sizes"]) && is_array($filemeta["sizes"])
			  && !empty($filemeta["sizes"]) ){
				foreach ( $filemeta["sizes"] as $thumbFile ){
					$actualFiles[] = $dirName.$thumbFile["file"];
				}  	
			}
		}	
		
		return $actualFiles;
	}
	
	/**
	 * Starts the upload process for the file.
	 * 
	 * @param string $file The location of the file. If not from the media library, it should be an absolute path.
	 * @param boolean $media if true then modifies the file string to add the upload directory before the filename. Otherwise leaves it alone.
	 */
	public static function process( $file , $media = false ){

		global $wpdb;
	
		$cdn = get_option("cst_cdn");
		$images = get_option("cst_images");
	
		$uploadCdn = ( isset($cdn["provider"]) && !empty($cdn["provider"]) ) ? true : false;
		$smushImages = ( isset($images["smush"]) && $images["smush"] == "yes" ) ? true : false;
		$compressImages = ( isset($images["compress"]) && $images["compress"] == "yes" ) ? true : false;
	
		if ( $media === true ){
			$uploadDir = wp_upload_dir();
			$fileLocation = $uploadDir["basedir"]."/".$file;
		} else {		
			$fileLocation = ABSPATH.$file;
		}
		$synced = "no";
		$smushedImage = "no";
		$gdCompression = "no";
	
		require_once CST_DIR."/lib/Cdn/Provider.php";
		
		$fileArray = array(
						'location' => $fileLocation, 
						'uri' => $file, 
						'overwrite' => $images['overwrite'],
						'compression_level' => intval($images['compression_level'])
					);
					
		try {				
			
			if ( $compressImages ){
				$gdCompression = "yes";
				$fileArray = Cst_Image::gdCompression($fileArray);
			}
			
			if ( $smushImages ){
				$fileArray = Cst_Image::smushIt($fileArray);
				$smushedImage = "yes";
			}		
			
			if ( $uploadCdn ){
				$objCdn = Cdn_Provider::getProvider($cdn["provider"]);
				$objCdn->setAccessCredentials($cdn);
				$objCdn->login();
				$objCdn->uploadFile($fileArray,$media);
				$synced = "yes";
			}
			
			// Debug Info
			Cst_Debug::addLog("File Sync : ".$file.", Image Smushed : ".$smushedImage.
							  ", GD Compression : ".$gdCompression.", Timestamp : ".time() );
			
			$wpdb->query("UPDATE ".CST_TABLE_FILES." SET `smushed` = '".$smushedImage."',#
						 `file_location`='".$fileLocation."',`media`='".$media."',
						 `transferred` = '".$synced."',hash='".hash_file("md5", $fileLocation)."' WHERE filename = '".$file."'");
			
		} catch ( Exception $e ){
			print $e->getMessage();
			if ( is_admin() ){ exit; }
			else{ return false; }
		}
		return true;
	}
	
}