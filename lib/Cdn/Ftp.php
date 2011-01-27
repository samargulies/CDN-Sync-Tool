<?php 

class Cdn_Ftp extends Cdn_Provider {

	public $resFtp;
	
	public function antiHotlinking(){
		
		return true;
	}
	
	public function setAccessCredentials($details){
		
		if ( !isset($details['username']) || empty($details['username']) ){
			throw new Exception("FTP Username is required");
		}
		
		if ( !isset($details["password"]) || empty($details["password"]) ){
			throw new Exception("FTP Password is required");
		}
		
		if ( !isset($details["server"]) || empty($details["server"]) ){
			throw new Exception("FTP Server is required");
		}
		
		if ( !isset($details["port"]) || !ctype_digit($details["port"]) ){
			throw new Exception("FTP Port is required");
		}
		
		if ( !isset($details["directory"]) || empty($details["directory"]) ){
			throw new Exception("FTP Directory is required");
		}
		
		$this->credentials = $details;
	}
	
	public function login() {
		
		if ( !$this->resFtp  = ftp_connect($this->credentials["hostname"],$this->credentials["port"]) ){	
			print "fail";
			return false;
		}
		
		if ( !ftp_login($this->resFtp,$this->credentials["username"],$this->credentials["password"]) ){
			print "login fail";
			return false;
		}
		
		return true;
	}
	
	public function uploadFile( $file , $media = true  ){
				
		list($fileLocation,$uploadFile) = $this->_getLocationInfo($file,$media);
		
		$fp = fopen($fileLocation,"r");
	
		if ( !ftp_alloc($this->resFtp,filesize($fileLocation)) ) {
			// Mabye throw an exception?!
			return false;
		}
		
		$uploadLocation = $this->credentials["directory"].'/'.$uploadFile;
		
		$uploadDir = dirname( ltrim($uploadLocation,'/') );
		$toCreate = array();
		
		do {
			if ( !is_dir('ftp://'.$this->credentials['username'].':'.$this->credentials["password"].'@'.$this->credentials["hostname"].':'.$this->credentials["port"].'/'.$uploadDir)) {
				$toCreate[] = basename($uploadDir);
				$uploadDir = dirname($uploadDir);
				$carryOn = true;
			} else {
				$carryOn = false;	
			}
		} while ($carryOn === true);
		
		krsort($toCreate);
		$uploadDir = '/'.$uploadDir;
		foreach( $toCreate as $dir ){
			$uploadDir = $uploadDir.'/'.$dir;
			ftp_mkdir($this->resFtp, $uploadDir);
		}
		
		ftp_fput( $this->resFtp, $uploadLocation, $fp, FTP_BINARY );
		
	}
	
	/**
	 * Closes the FTP resource.
	 * 
	 * @since 0.10
	 */
	
	public function __destruct(){
		
		ftp_close($this->resFtp);
	
	}
}