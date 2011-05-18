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

	public function getObject(){
		return $this->s3;
	}

	public function antiHotlinking(){

		if ( $this->checkSame("hotlinking") ){
			return true;
		}

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
		if ( empty($this->s3) ){
			$this->s3 = new AmazonS3(
				$this->credentials["access"],
				$this->credentials["secret"]
			);
			// Kinda flawed since even if we don't have
			// permissions to it, we'll get a positive result.

			if ( isset($_POST['create_bucket']) && $_POST["create_bucket"] == "yes" ){

				$response = $this->s3->create_bucket( $this->credentials["bucket_name"] , AmazonS3::REGION_US_E1 );

				if ( (string)$response->status != '200' ){
					Cst_Debug::addLog("AWS Create bucket response : ".var_export($response,true));
					return false;
				}

			}

			return true;
		}

		return true;

	}

	/**
	 * (non-PHPdoc)
	 * @see Cdn_Provider::uploadFile()
	 */
	public function uploadFile( $fileArray , $media = true ){


		$uploadDir = wp_upload_dir();
		$finfo = function_exists('finfo_open') ? finfo_open(FILEINFO_MIME_TYPE) : false;
		$headers = array('expires' => date('D, d M Y H:i:s', time() + (86400 * 352 * 10)) . ' GMT');
		$headers['Cache-Control'] = 'max-age=31536000';

		list($fileLocation,$uploadFile) = $this->_getLocationInfo($fileArray,$media);


		if ( !preg_match("~\.(css|js)$~isU",$fileArray['uri'],$match) ){
			$fileType = ($finfo != false) ? finfo_file($finfo,$fileLocation) : mime_content_type($fileLocation);
		} else {
			// TODO DRY this properly
			if (strtolower($match[1]) == "css"){
				$fileType = "text/css";
			} else {
				$fileType = "text/javascript";
			}

			if ( isset($this->credentials["compression"])
				&& $this->credentials["compression"] == "yes" ){
				// Compress and add encoding
				$fileContents = file_get_contents($fileLocation);
				$fileLocation = tempnam("/tmp", "gzfile");
				$fileResource = gzopen($fileLocation,'w9');
				gzwrite($fileResource,$fileContents);
				gzclose($fileResource);

				$headers['Content-Encoding'] = 'gzip';
			}

		}


		$acl =  ( !isset($this->credentials["hotlinking"]) || $this->credentials["hotlinking"] == "no" ) ? AmazonS3::ACL_PUBLIC : AmazonS3::ACL_PRIVATE;
		$uploadFile= trim($uploadFile, "/");
		$fileOptions = array(
			'acl' => $acl,
			'headers' => $headers,
			'contentType' => $fileType,
			'fileUpload' => $fileLocation,
			'storage' => ( $this->credentials['reduced'] == "yes" ) ? AmazonS3::STORAGE_REDUCED : AmazonS3::STORAGE_STANDARD
		);
		$this->s3->create_object(
			$this->credentials["bucket_name"],
			$uploadFile,
			$fileOptions);

		return $uploadFile;

	}

	/**
	 * (non-PHPdoc)
	 * @see Cdn_Provider::setAccessCredentials()
	 */

	public function setAccessCredentials( $details ){

		if ( !isset($details["access"]) || empty($details["access"]) ){
			throw new Exception("access key credential required");
		}

		if ( !isset($details["secret"]) || empty($details["secret"]) ){
			throw new Exception("secret key credential required");
		}

		if ( !isset($details["bucket_name"]) || empty($details["bucket_name"]) ){
			throw new Exception("bucket credential required ");
		}

		$this->credentials = $details;

	}


}
