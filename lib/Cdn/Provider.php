<?php 
define('LIB_DIR', dirname(dirname(__FILE__)));

	/**
	 * CDN super class with factory method for creating objects.
	 * 
	 * @author Iain Cambridge
	 * @since 0.1
	 */

abstract class Cdn_Provider {


	
	/**
	 * The login credentials for the CDN requests
	 * @var array
	 */
	protected $credentials;
	
	/**
	 * Provider object.
	 * @var Cst_Provider
	 */
	private static $provider;
	
	/**
	 * Gets the provider object. 
	 *  
	 * @param string $providerName
	 * @throws Exception
	 * @return Cdn_Provider
	 * @since 0.1
	 */
	public static function getProvider($providerName){
		
		if ( empty(self::$provider[$providerName]) ){
			if ( is_readable( LIB_DIR.'/Cdn/'.ucfirst($providerName).'.php' ) ){
				require_once ( LIB_DIR.'/Cdn/'.ucfirst($providerName).'.php' );
				$className = "Cdn_".ucfirst($providerName);
				self::$provider[$providerName] = new $className();			
			} else {
				throw new Exception("Invalid provider");
			}			
		} 

		return self::$provider[$providerName];
	}
	
	/**
	 * Checks to see if current credentials are the 
	 * same as the old ones.
	 * @param string $field
	 * @since 0.4
	 */
	
	protected function checkSame($field){
	
		$oldCdn = get_option("cst_cdn");
		
		if ( $oldCdn[$field] == $this->credentials[$field] ){
			return true;	
		}
		
		return false;
	}
	
	
	
	/**
	 * Single interfaction for setting login credentials. Will vary
	 * with each different service. 
	 * 
	 * @TODO think about better solution.
	 * 
	 * @return boolean True if successful, false if failed.
	 * @since 0.1
	 */	
	abstract public function setAccessCredentials( $details );
	
	/**
	 * Does the the access credentials checking.
	 * 
	 * @return boolean|string Returns true if successful or error message if failed.
	 * @since 0.1
	 */
	
	abstract public function login();
	
	/**
	 * Handles the uploading of files to the selected CDN provider. 
	 *
	 * @param string $file The location of the file to be uploaded.
	 * @param boolean $media If the file is from the media library
	 * @return boolean|string Returns true if successfule otherwise error message.
	 * @since 0.1
	 */
	abstract public function uploadFile( $file , $media = true );
	
	
	/**
	 * Enables the anti hotlinking ability. Only really 
	 * works with S3. 
	 * 
	 * @since 0.4
	 */
	abstract public function antiHotlinking();
	
	/**
	 * Simple DRY method to work out the file upload location and the file
	 * 
	 * <code>
	 * list($fileLocation,$uploadFile) = $this->_getLocationInfo($file);
	 * </code>
	 * 
	 * @param string $file
	 * @since 0.10
	 */
	protected function _getLocationInfo($file, $media){
		
		global $blog_id;
		
		$cdn = get_option("cst_cdn");
		
		if ( $media == true){
			$uploadDir = wp_upload_dir();
			$directory = ((function_exists('is_multisite') && is_multisite() ) && $blog_id != 1 ) ?  'wp-content/blogs.dir/'.$blog_id.'/files/' : 'wp-content/uploads/';
			$fileLocation = $uploadDir["basedir"]."/".$file['uri'];
			$uploadFile = $directory.$file['uri'];
		} else {
			$uploadFile = $file['uri'];			
		}
		
		if ( $cdn['absolute'] !== "yes" ){
			// no multisite check since only multisite sites should have it.
			$uploadFile = str_replace('wp-content/blogs.dir/'.$blog_id.'/', '', $uploadFile);		
		}
		 
		$fileLocation = $file['location'];
		
		return array($fileLocation,$uploadFile);
	}
}