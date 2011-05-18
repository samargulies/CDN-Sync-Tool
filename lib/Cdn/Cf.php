<?php

/**
 * The class to handle interfacing with Rackspace's cloudfiles API.
 *
 * @author Iain Cambridge
 * @since 0.4
 */

class Cdn_Cf extends Cdn_Provider {

	protected $loggedIn;
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

	/**
	 *
	 * @return CF_Container
	 */
	public function getObject(){
		return $this->container;
	}

	public function antiHotlinking(){

		if ( $this->checkSame("hotlinking") ){
			return true;
		}

		$url = ( $this->credentials["hotlinking"] == "yes" ) ? get_bloginfo("url") : '';
		var_dump($url);
		$this->container->acl_referrer( $url );
		return true;

	}

	public function login() {


		if ( $this->loggedIn === NULL ){
			require_once dirname(dirname(__FILE__)).'/cloudfiles/cloudfiles.php';

				$auth = new CF_Authentication(
					$this->credentials["username"],
					$this->credentials["apikey"],
					NULL,
					constant($this->credentials["authurl"])
				);

				$auth->ssl_use_cabundle(); // if breaks try removing.

				if ( $auth->authenticate() ) {
					$this->cloudfiles = new CF_Connection($auth);
					$this->container = $this->cloudfiles->get_container($this->credentials["container"]);
					if ( !is_a($this->container,'CF_Container') ){
						throw new Exception( " Loggin Failure.  " );
					}
					$this->loggedIn = true;
				} else {
					$this->loggedIn = false;
				}
		}

		return $this->loggedIn;

	}

	/**
	 * (non-PHPdoc)
	 * @see Cdn_Provider::uploadFile()
	 */
	public function uploadFile( $fileArray , $media = true ){

		global $blog_id;

		$finfo = function_exists('finfo_open') ? finfo_open(FILEINFO_MIME_TYPE) : false;
		list($fileLocation,$uploadFile) = $this->_getLocationInfo($fileArray,$media);

		$object = $this->container->create_object($uploadFile);
		$object->metadata = array('expires' => date('D, d M Y H:i:s', time() + (86400 * 30)) . ' GMT');

		if ( !preg_match("~\.(css|js)$~isU",$fileArray['uri'],$match) ){
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

		return $uploadFile;
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