<?php 
define('LIB_DIR', dirname(dirname(__FILE__)));

	/**
	 * CDN super class with factory method for creating objects.
	 * 
	 * @author Iain Cambridge
	 * @since 1.0
	 */

abstract class Cdn_Provider {
	
	/**
	 * The login credentials for the CDN requests
	 * @var array
	 */
	protected $credentials;
	
	/**
	 * Gets the provider object. 
	 *  
	 * @param string $providerName
	 * @throws Exception
	 * @return Cdn_Provider
	 * @since 1.0
	 */
	public static function getProvider($providerName){
		
		if ( is_readable( LIB_DIR.'/Cdn/'.ucfirst($providerName).'.php' ) ){
			require_once ( LIB_DIR.'/Cdn/'.ucfirst($providerName).'.php' );
			$className = "Cdn_".ucfirst($providerName);
			return new $className();			
		} else {
			throw new Exception("Invalid provider");
		}			
		
	}
	
	/**
	 * Single interfaction for setting login credentials. Will vary
	 * with each different service. 
	 * 
	 * @TODO think about better solution.
	 * 
	 * @return boolean True if successful, false if failed.
	 * @since 1.0
	 */	
	abstract public function setAccessCredentials( $details );
	
	/**
	 * Does the the access credentials checking.
	 * 
	 * @return boolean|string Returns true if successful or error message if failed.
	 * @since 1.0
	 */
	
	abstract public function login();
	
	/**
	 * Handles the uploading of files to the selected CDN provider. 
	 *
	 * @param string $file The location of the file to be uploaded.
	 * @param boolean $media If the file is from the media library
	 * @return boolean|string Returns true if successfule otherwise error message.
	 * @since 1.0
	 */
	abstract public function uploadFile( $file , $media = true );
	
}
