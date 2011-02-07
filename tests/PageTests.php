<?php

class PageTests extends PHPUnit_Framework_TestCase {
	
public function testMainPageSavesAwsSettingsProperly(){
	 	
		global $objCstPlugin;
	 	
		// Backup stuff
	 	$oldPost = $_POST;
	 	$files  = get_option("cst_files");
		$images = get_option("cst_images");
		$cdn    = get_option("cst_cdn");
		$general = get_option("cst_general");
		$cdnUrl = get_option("ossdl_off_cdn_url");
	 	
	 	$_POST = array( 'cdn_provider' => 'aws',
	 					'cdn_hostname' => 'random.aws.domain.com',
	 					'cdn_hotlinking' => 'no',
	 					'aws_access' => AWS_ACCESS,
	 					'aws_secret' => AWS_SECRET,
	 					'aws_bucket' => AWS_BUCKET,
	 					'aws_compression' => 'yes',
	 					'combine' => 'yes',
	 					'minify_engine' => 'none',
	 					'directory' => 'wp-content/uploads',
	 					'external' => 'yes',
	 					'exclude_css' => '',
	 					'exclude_js' => '',
	 					'js_location' => 'head',
	 					'smush' => 'yes',
	 					'compress' => 'yes',
	 					'powered_by' => 'yes'
	 					);
		
	 	ob_start();
	 	$return = $objCstPlugin->getObject()
	 						   ->showPage('Main')
	 						   ->display();
	 	ob_end_clean();
	 	
	 	// Restore stuff
	 	update_option("ossdl_off_cdn_url",$cdnUrl);							
		update_option("cst_files",$files);
		update_option("cst_images",$images);
		update_option("cst_cdn",$cdn);
		update_option("cst_general",$general);	
	 	$_POST = $oldPost;
	 	
	 	$this->assertEquals( $return['errorArray'], array() );
	 	
	}
	
	/*
	public function testMainPageAwsMissingBucketName(){
	 	
		global $objCstPlugin;
	 	
		// Backup stuff
	 	$oldPost = $_POST;
	 	$files  = get_option("cst_files");
		$images = get_option("cst_images");
		$cdn    = get_option("cst_cdn");
		$general = get_option("cst_general");
		$cdnUrl = get_option("ossdl_off_cdn_url");
	 	
	 	$_POST = array( 'cdn_provider' => 'aws',
	 					'cdn_hostname' => 'random.aws.domain.com',
	 					'cdn_hotlinking' => 'no',
	 					'aws_access' => AWS_ACCESS,
	 					'aws_secret' => AWS_SECRET,
	 					'aws_bucket' => '',
	 					'aws_compression' => 'yes',
	 					'combine' => 'yes',
	 					'minify_engine' => 'none',
	 					'directory' => 'wp-content/uploads',
	 					'external' => 'yes',
	 					'exclude_css' => '',
	 					'exclude_js' => '',
	 					'js_location' => 'head',
	 					'smush' => 'yes',
	 					'compress' => 'yes',
	 					'powered_by' => 'yes'
	 					);
		
	 	ob_start();
	 	$return = $objCstPlugin->getObject()->showPage('Main')->display();
	 	ob_end_clean();
	 	
	 	// Restore stuff
	 	update_option("ossdl_off_cdn_url",$cdnUrl);							
		update_option("cst_files",$files);
		update_option("cst_images",$images);
		update_option("cst_cdn",$cdn);
		update_option("cst_general",$general);	
	 	$_POST = $oldPost;
	 	
	 	$this->assertEquals( $return['errorArray'], array("S3 Bucket name is required") );
	 	
	}*/
	
	
}
