<?php

class Cst_Page_Main extends Cst_Page {

	protected function _showSync(){
		
		$getVars = "";
		foreach ( array("directory","theme","media","wpinclude","wpplugin","force") as $var	 ){
			if ( isset($_POST[$var]) ){
				$getVars .= "&".$var."=".$_POST[$var];
			}
		}
		require_once CST_DIR.'/pages/main/sync.html';
	}
	
	public function display(){
		
		if ( isset($_POST["showsync"]) && $_POST["showsync"] == "yes" ){
			$this->_showSync();
			return;
		}
			
		if ( !empty($_POST) ){
			$errorArray = array();
					
			if ( $_POST['cdn_provider'] == "s3" ){		
				if ( !isset($_POST["aws_access"]) || empty($_POST["aws_access"]) ) {
					$errorArray[] = "AWS access key is required";
				}
	
				if ( !isset($_POST["aws_secret"]) || empty($_POST["aws_secret"]) ) {
					$errorArray[] = "AWS secret code is required";
				}
				
				if ( !isset($_POST["aws_bucket"]) || empty($_POST["aws_bucket"]) ){
					$errorArray[] = "S3 Bucket name is required";
				}
			}
		
			if ( !isset($_POST["combine"]) || empty($_POST["combine"]) ){
				$errorArray[] = "Combine JS/CSS is required";	
			} elseif ( $_POST["combine"] != 'yes' && $_POST["combine"] != 'no' ){
				$errorArray[] = "Combine JS/CSS isn't a valid reponse";
			}
			if ( !isset($_POST["whitespace"]) || empty($_POST["whitespace"]) ){
				$errorArray[] = "Whitespace Removal is required";	
			} elseif ( $_POST["whitespace"] != 'yes' && $_POST["whitespace"] != 'no' ){
				$errorArray[] = "Whitespace Removal isn't a valid reponse";
			}
			
			if ( !isset($_POST["smush"]) || empty($_POST["smush"]) ){
				$errorArray[] = "Smush files is required";	
			} elseif ( $_POST["smush"] != 'yes' && $_POST["smush"] != 'no' ){
				$errorArray[] = "Smush files isn't a valid reponse";
			}
			
			$cdn = array();
				
			if ( isset($_POST["cdn_provider"]) && !empty($_POST["cdn_provider"]) ){	
						
				$cdn["provider"] = $_POST["cdn_provider"];
				$cdn["hostname"] = $_POST["cdn_hostname"];
				
				if ( $cdn["provider"] == "aws"){
					$cdn["access"] = $_POST['aws_access'];
					$cdn["secret"] = $_POST["aws_secret"];
					$cdn["bucket"] = $_POST["aws_bucket"];
				}
			}
				
			$files = array();
			$files["directory"] = $_POST["directory"];
			$files["combine"] = $_POST["combine"];
			$files["external"] = $_POST["external"];
			$files["exclude_js"] = $_POST["exclude_js"];
			$files["exclude_css"] = $_POST["exclude_css"];
			$files["whitespace"] = $_POST["whitespace"];
			$images = array();
			$images["smush"] = $_POST["smush"];
			$images["compress"] = $_POST["compress"];
			$general = array();
			$general["powered_by"] =  $_POST["powered_by"];
			
			
			if ( empty($errorArray) ){
				
				// Updates WP Super Cache's CDN url value
				if ( defined("WP_CACHE") && WP_CACHE === true ){
					update_option("ossdl_off_cdn_url",$cdn["hostname"]);
				} 
							
				update_option("cst_files",$files);
				update_option("cst_images",$images);
				update_option("cst_cdn",$cdn);
				update_option("cst_general",$general);	
				
			}
			
		} else {
		
			$files  = get_option("cst_files");
			$images = get_option("cst_images");
			$cdn    = get_option("cst_cdn");
			$general = get_option("cst_general");
			
		}
		
		require_once CST_DIR."/pages/main/index.html";
	}
	
}