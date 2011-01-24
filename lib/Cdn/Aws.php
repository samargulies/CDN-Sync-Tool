<?php

require_once CST_DIR.'/lib/awssdk/sdk.class.php';

/**
 * The AWS class to
 * 
 * @author Iain Cambridge
 * @since 0.1
 */

class Cdn_Aws extends Cdn_Provider {
	
	/**
	 * S3 object.
	 * @var AmazonS3
	 */
	protected $s3;
	
	
	public function antiHotlinking(){
		
		if ( $this->checkSame("hotlinking") ){
			return true;	
		}
		
		// TODO write bucket policy
		if ( $this->credentials["hotlinking"] == "yes" ){
			$site = get_bloginfo("url");
			$policy = '{
						"Version":"2008-10-17",
						"Id":"http referer policy example",
						"Statement":[{
								"Sid":"hotlink",
								"Effect":"Allow",
								"Principal":"*",
								"Action":"s3:GetObject",
								"Resource":"arn:aws:s3:::'.$this->credentials["bucket"].'/*",
								"Condition":{
									"StringLike":{
										"aws:Referer":["'.$site.'",
											"'.$site.'/*"
										]
									}
								}
							}
						]
					}';
			$objPolicy = new CFPolicy($this->s3, $policy);
			$this->s3->set_bucket_policy($this->credentials["bucket"], $objPolicy);
		} else {
			$this->s3->delete_bucket_policy($this->credentials["bucket"]);
		}
		
		return true;
		
	}
	
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
					
					if ( isset($_POST['create_bucket']) && $_POST["create_bucket"] == "yes" ){
						
						$response = $this->s3->create_bucket( $this->credentials["bucket"] , AmazonS3::REGION_US_E1 );
						
						if ( (string)$response->status != '200' ){
							Cst_Debug::addLog("AWS Create bucket response : ".var_export($response,true));
							return false;
						}
						
					}
						
						
			return $this->s3->if_bucket_exists( (string)$this->credentials["bucket"]);
		} catch ( Exception $e ){
			return false;
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
		$headers = array('expires' => date('D, j M Y H:i:s', time() + (86400 * 352 * 10)) . ' GMT');	
			
		list($fileLocation,$uploadFile) = $this->_getLocationInfo($file,$media);
		
		if ( !preg_match("~\.(css|js)$~isU",$file,$match) ){	
			$fileType = ($finfo != false) ? finfo_file($finfo,$fileLocation) : mime_content_type($fileLocation);
		} else {
			// TODO DRY this properly
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
		
		$acl =  ( $this->credentials["hotlinking"] == "no" ) ? AmazonS3::ACL_PUBLIC : AmazonS3::ACL_PRIVATE;
		$uploadFile= trim($uploadFile, "/");
		$fileOptions = array(
					'acl' => $acl,
					'headers' => $headers,
					'contentType' => $fileType,
					'fileUpload' => $fileLocation
					);
					
		$this->s3->create_object(
						$this->credentials["bucket"],
						$uploadFile,
						$fileOptions);
		
	}
	
	/**
	 * (non-PHPdoc)
	 * @see Cdn_Provider::setAccessCredentials()
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