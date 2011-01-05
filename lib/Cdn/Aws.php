<?php

require_once CST_DIR.'/lib/awssdk/sdk.class.php';

/**
 * The AWS class to
 * 
 * @author Iain Cambridge
 * @since 1.0
 */

class Cdn_Aws extends Cdn_Provider {
	
	/**
	 * S3 object.
	 * @var AmazonS3
	 */
	protected $s3;
	
	/**
	 * (non-PHPdoc)
	 * @see Cdn_Provider::login()
	 */
	
	public function login() {
		
		require_once dirname(dirname(__FILE__)).'/awssdk/sdk.class.php';
		
		try {
			$this->s3 = new AmazonS3(
							$this->credentials["access"],
							$this->credentials["secret"]
						);
					// Kinda flawed since even if we don't have 
					// permissions to it, we'll get a positive result.							
			return $this->s3->if_bucket_exists( (string)$this->credentials["bucket"]);
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
		$headers = array('expires' => date('D, j M Y H:i:s', time() + (86400 * 30)) . ' GMT');	
		if ( $media == true){
			$directory = ( (function_exists('is_multisite') && is_multisite()) && $blog_id != 1 ) ? 'files/' : 'wp-content/uploads/';
			$uploadFile = $directory.$file;
			$fileLocation = $uploadDir["basedir"]."/".$file;
		} else {
			$fileLocation = ABSPATH.$file;
			$uploadFile = $file;			
		}

		if ( !preg_match("~\.(css|js)$~isU",$file,$match) ){	
			$fileType = ($finfo != false) ? finfo_file($finfo,$fileLocation) : mime_content_type($fileLocation);
		} else {
			
			if (strtolower($match[1]) == "css"){
				$fileType = "text/css";
			} else {
				$fileType = "text/javascript";
			} 
			// Compress and add encoding
			$fileContents = file_get_contents($fileLocation);			
			$fileLocation = tempnam("/tmp", "gzfile");
			$fileResource = gzopen($fileLocation,'w9');				
			gzwrite($fileResource,$fileContents);
			gzclose($fileResource);
			
			$headers['Content-Encoding'] = 'gzip';
			
		}
		
		$uploadFile= trim($uploadFile, "/");
		$fileOptions = array(
					'fileUpload' => $fileLocation,
					'acl' => AmazonS3::ACL_PUBLIC,
					'contentType' => $fileType,
					'headers' => $headers
					);
		
		$this->s3->create_object(
						$this->credentials["bucket"],
						$uploadFile,
						$fileOptions);
		
	}
	
	/**
	 * (non-PHPdoc)
	 * @see Cdn_Provider::setAccessCredentials()
	 * @todo move 
	 */
	
	public function setAccessCredentials( $details ){

		if ( !isset($details["access"]) || empty($details["access"]) ){
			throw new Exception("access key required");
		} 

		if ( !isset($details["secret"]) || empty($details["secret"]) ){
			throw new Exception("secret key required");
		}
		
		if ( !isset($details["bucket"]) || empty($details["bucket"]) ){
			throw new Exception("bucket required");
		}
		
		$this->credentials = $details;
		
	}
	
}