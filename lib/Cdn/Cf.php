<?php

/**
 * The class to handle interfacing with Rackspace's cloudfiles API.
 * 
 * @author Iain Cambridge
 * @since 0.4
 */

class Cdn_Cf extends Cdn_Provider {
	
	/**
	 * The cloudfiles connection object.
	 * @var CF_Connection
	 */
	protected $cloudfiles; 
	
	/**
     * The Cloudfiles container object.
     * @var CF_Container
	 */
	protected $container;
	
	public function antiHotlinking(){
		
		if ( $this->checkSame("hotlinking") ){
			return true;	
		}
		 
	    $url = ( $this->credentials["hotlinking"] == "yes" ) ? get_bloginfo("url") : '';
		var_dump($url);
	    $this->container->acl_referrer( $url );
		print $this->container;
		return true;
		
	}
	
	public function login() {
		
		require_once dirname(dirname(__FILE__)).'/cloudfiles/cloudfiles.php';
		
		try {
			$auth = new CF_Authentication(
							$this->credentials["username"],
							$this->credentials["apikey"]
						);
						
			//$auth->ssl_use_cabundle(); // if breaks try removing.
			
			if ( $auth->authenticate() ) {
				$this->cloudfiles = new CF_Connection($auth);
				$this->container = $this->cloudfiles->get_container($this->credentials["container"]);
			} else {
				return false;
			}												
	
		} catch ( Exception $e ){
			return $e->getMessage();
		}
		
	}	
	
	/**
	 * (non-PHPdoc)
	 * @see Cdn_Provider::uploadFile()
	 */
	public function uploadFile( $file , $media = true ){
		
		global $blog_id;
		
		$uploadDir = wp_upload_dir();
		$finfo = function_exists('finfo_open') ? finfo_open(FILEINFO_MIME_TYPE) : false;	
		if ( $media == true){
			$directory = ( (function_exists('is_multisite') && is_multisite()) && $blog_id != 1 ) ? 'files/' : 'wp-content/uploads/';
			$uploadFile = $directory.$file;
			$fileLocation = $uploadDir["basedir"]."/".$file;
		} else {
			$fileLocation = ABSPATH.$file;
			$uploadFile = $file;			
		}

		$object = $this->container->create_object($uploadFile);
		$object->metadata = array('expires' => date('D, j M Y H:i:s', time() + (86400 * 30)) . ' GMT');
		
		if ( !preg_match("~\.(css|js)$~isU",$file,$match) ){	
			$object->content_type = ($finfo != false) ? finfo_file($finfo,$fileLocation) : mime_content_type($fileLocation);
		} else {
			
			if (strtolower($match[1]) == "css"){
				$object->content_type = "text/css";
			} else {
				$object->content_type = "text/javascript";
			} 
			// TODO Add GZip support
			// Compress and add encoding
			//$fileContents = file_get_contents($fileLocation);			
 
			//$object->metadata['Content-Encoding'] = 'gzip';
			
		}
		
		$object->load_from_filename($fileLocation);
		
	}
	
	/**
	 * (non-PHPdoc)
	 * @see Cdn_Provider::setAccessCredentials()
	 * @todo move 
	 */
	
	public function setAccessCredentials( $details ){

		if ( !isset($details["apikey"]) || empty($details["apikey"]) ){
			throw new Exception("API key required");
		} 

		if ( !isset($details["username"]) || empty($details["username"]) ){
			throw new Exception("Username required");
		}
		
		if ( !isset($details["container"]) || empty($details["container"]) ){
			throw new Exception("Container required");
		}
		
		$this->credentials = $details;
		
	}
}