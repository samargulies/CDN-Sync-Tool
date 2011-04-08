<?php

	/**
	 * 
	 * Enter description here ...
	 * @author Iain Cambridge
	 * @copyright Fubra Limited 2011
	 * @license GNU GPLv2 
	 */

class Cst_Page_Main extends Cst_Page {

	
	protected function _wipeJsCss(){
		$files  = get_option("cst_files");
		$fileCache = array_merge( glob(ABSPATH.$files['directory'] .'/*.js'), glob(ABSPATH.$files['directory'] . '/*.css') );
			
		foreach ( $fileCache as $file ){
			unlink($file);
		}
		?>
		<div class="updated">The JavaScript and CSS file cache has been wiped.</div>
		<?php 
		return;
		
	}
	
	protected function _showSync(){
		$getVars = "";
		foreach ( array("directory","theme","media","wpinclude","wpplugin","force","cstcssjs") as $var	 ){
			if ( isset($_POST[$var]) ){
				$getVars .= "&".$var."=".$_POST[$var];
			}
		}
		
		Cst_Debug::addLog("Show sync page with vars '".$getVars."'");
		require_once CST_DIR.'/pages/main/sync.html';
	}
	
	public function display($test = false){
	
		
		if ( isset($_POST["showsync"]) && $_POST["showsync"] == "yes" ){
			$this->_showSync();
			return;
		}
		if ( isset($_POST["wipe_js"]) && $_POST["wipe_js"] == "yes" ){
			$this->_wipeJsCss();
		}
		
		$errorArray = array();
		
		if ( !empty($_POST) && !isset($_POST['wipe_js']) ){
					
			if ( $_POST['cdn_provider'] == "aws" ){		
				//*********************************
				// AWS Data for S3/CloudFront
				//*********************************
				if ( !isset($_POST["aws_access"]) || empty($_POST["aws_access"]) ) {
					$errorArray[] = "AWS access key is required";
				}
	
				if ( !isset($_POST["aws_secret"]) || empty($_POST["aws_secret"]) ) {
					$errorArray[] = "AWS secret code is required";
				}
			
				if ( !isset($_POST["aws_bucket"]) || empty($_POST["aws_bucket"]) ){
					$errorArray[] = "S3 Bucket name is required";
				}
				
				if ( !isset($_POST["aws_compression"]) || empty($_POST["aws_compression"]) ){
					$errorArray[] = "GZIP compression is response required";
				}
				
			} elseif ( $_POST['cdn_provider'] == "cf" ){
				//***********************************
				// Cloudfiles Data
				//***********************************
				
				if ( !isset($_POST["cf_username"]) || empty($_POST["cf_username"]) ){
					$errorArray[] = "CloudFiles Username is required";
				}
				
				if ( !isset($_POST["cf_apikey"]) || empty($_POST["cf_apikey"]) ){
					$errorArray[] = "CloudFiles API key is required";
				}
				
				if ( !isset($_POST["cf_container"]) || empty($_POST["cf_container"]) ){
					$errorArray[] = "CloudFiles Container is required";
				}
				
			} elseif ( $_POST['cdn_provider'] == "ftp" ){
				//************************************
				// FTP Data
				//************************************

				if ( !isset($_POST["ftp_username"]) || empty($_POST["ftp_username"]) ){
					$errorArray[] = "FTP Username is required";
				}
				
				if ( !isset($_POST["ftp_password"]) || empty($_POST["ftp_password"]) ){
					$errorArray[] = "FTP Password is required";
				}
				
				if ( !isset($_POST["ftp_server"]) || empty($_POST["ftp_server"]) ){
					$errorArray[] = "FTP Server is required";					
				}
				
				if ( !isset($_POST["ftp_port"]) || !ctype_digit($_POST["ftp_port"]) ){
					$errorArray[] = "FTP Port is required";
				}
				
				if ( !isset($_POST["ftp_directory"]) || empty($_POST["ftp_directory"]) ){
					$errorArray[] = "FTP Directory is required";
				}
				
			}
					
			if ( !isset($_POST["combine"]) || empty($_POST["combine"]) ){
				$errorArray[] = "Combine JS/CSS is required";	
			} elseif ( $_POST["combine"] != 'yes' && $_POST["combine"] != 'no' ){
				$errorArray[] = "Combine JS/CSS isn't a valid reponse";
			}
			
			if ( !isset($_POST["minify_engine"]) || empty($_POST["minify_engine"]) ){
				$errorArray[] = "Minify is required";	
			} 
			
			if ( !isset($_POST["js_location"]) || empty($_POST["js_location"]) ){
				$errorArray[] = "JS Location is required";	
			} 
			
			if ( !isset($_POST["smush"]) || empty($_POST["smush"]) ){
				$errorArray[] = "Smush files is required";	
			} elseif ( $_POST["smush"] != 'yes' && $_POST["smush"] != 'no' ){
				$errorArray[] = "Smush files isn't a valid reponse";
			}
			
			$cdnUrl  = (!empty($_POST["cdn_hostname"])) ? $_POST["cdn_hostname"] : '';
			$cdn = array();		
			
			if ( isset($_POST["cdn_provider"]) && !empty($_POST["cdn_provider"]) ){	
				
				$cdn["provider"]   = $_POST["cdn_provider"];
				$cdn["hotlinking"] = $_POST["cdn_hotlinking"];
				$cdn["absolute"] = $_POST["cdn_absolute"];
				$cdn["hostname"]   = $cdnUrl;
				if ( $cdn["provider"] == "aws"){
					$cdn["access"]      = $_POST['aws_access'];
					$cdn["secret"]      = $_POST["aws_secret"];
					$cdn["bucket_name"] = $_POST["aws_bucket"];
					$cdn["compression"] = $_POST["aws_compression"];
					$cdn["reduced"] = (isset($_POST["aws_reduced"])) ? $_POST["aws_reduced"] : 'no' ;
				} elseif ( $cdn["provider"] == "cf" ){
					$cdn["username"]  = $_POST["cf_username"];
					$cdn["apikey"]    = $_POST["cf_apikey"];
					$cdn["container"] = $_POST["cf_container"];	
					$cdn["authurl"] = $_POST["cf_authurl"];	
				} elseif ( $cdn["provider"] == "ftp" ){
					$cdn["username"]  = $_POST["ftp_username"];
					$cdn["password"]  = $_POST["ftp_password"];
					$cdn["server"]    = $_POST["ftp_server"];
					$cdn["port"]      = $_POST["ftp_port"];
					$cdn["directory"] = $_POST["ftp_directory"];	
				}
				
				// 
				if ( ($cdn["hotlinking"] == "yes" || isset($_POST['create_bucket']) ) && empty($errorArray) ){
					try {
						require_once CST_DIR.'/lib/Cdn/Provider.php';
						
						$objCdn = Cdn_Provider::getProvider($cdn["provider"]);
						$objCdn->setAccessCredentials($cdn);
						$objCdn->login();	
						
						if ( $cdn["hotlinking"] == "yes" ){
							$objCdn->antiHotlinking();
						}
						
					} catch(Exception $e){
						$errorArray[] = $e->getMessage();
					}
				}
			}
			
				
			$files = array();
			$files["directory"] = $_POST["directory"];
			$files["combine"] = $_POST["combine"];
			$files["external"] = $_POST["external"];
			$files["exclude_js"] = $_POST["exclude_js"];
			$files["exclude_css"] = $_POST["exclude_css"];
			$files["minify_engine"] = $_POST["minify_engine"];
			$files["location"] = $_POST["js_location"];
			if ( $_POST["minify_engine"] == "google" ){
				$files["minify_level"] = $_POST["google_level"];		
			}
			$images = array();
			$images["smush"] = $_POST["smush"];
			$images["compress"] = $_POST["compress"];
			$images["overwrite"] = $_POST["overwrite"];
			$images["compression_level"] = $_POST["compression_level"];
			$general = array();
			$general["powered_by"] =  $_POST["powered_by"];			
			
			if ( empty($errorArray) ){
				
				update_option("ossdl_off_cdn_url",$cdnUrl);							
				update_option("cst_files",$files);
				update_option("cst_images",$images);
				update_option("cst_cdn",$cdn);
				update_option("cst_general",$general);	
				Cst_Debug::addLog("Form submission sucessful with values saved");
				
			} else {
				Cst_Debug::addLog("Form submission failed with ".print_r($errorArray,true));
			}
		} else {
		
			$files  = get_option("cst_files");
			$images = get_option("cst_images");
			$cdn    = get_option("cst_cdn");
			$general = get_option("cst_general");
			$cdnUrl = get_option("ossdl_off_cdn_url");
			
		}
		
		require_once CST_DIR."/pages/main/index.html";
		
		return compact(	$errorArray, $files, $images, $cdn, $general, $cdnUrl );
		
	}
	
}