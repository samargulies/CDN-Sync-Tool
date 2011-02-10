<?php

class CdnTest extends PHPUnit_Framework_TestCase {
	
	protected $_files = array('aws' => '');
	
	/**
	 * Test to ensure the Amazon S3 file upload is working.
	 * 
	 */
	public function testAmazonS3FileUploadWithoutCompression(){
				
		$objAws = Cdn_Provider::getProvider('aws');
		$objAws->setAccessCredentials( array('access' => AWS_ACCESS, 'secret' => AWS_SECRET, 'bucket_name' => AWS_BUCKET) );
		$this->assertTrue($objAws->login());
		
		$filename = $this->_createTestFile('testAmazonS3FileUploadNoCompression'.time());
		// todo improve
		$uploadFile = $objAws->uploadFile( array('location' => $filename, 
		 										 'uri' => basename($filename),
												 'overwrite' => 'no',
												 'compression_level' => '1',
												)									
										, true );
				
		$curl = curl_init( AWS_URL.$uploadFile );
		curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
		
		$awsContents = curl_exec($curl);		
		$localContents = file_get_contents($filename);
		
		$this->assertEquals($localContents,$awsContents,"AWS file contents don't match the local content");
		
		$objAws->getObject()->delete_object(AWS_BUCKET,$uploadFile);
		
		unlink($filename);
	}
	
	public function testAmazonS3FileUploadWithCompression(){
				
		$objAws = Cdn_Provider::getProvider('aws');
		$objAws->setAccessCredentials( array('access' => AWS_ACCESS, 'secret' => AWS_SECRET, 'bucket_name' => AWS_BUCKET, 'compression' => 'yes') );
		$this->assertTrue($objAws->login(), "lol");
		
		$filename = $this->_createTestFile('testAmazonS3FileUploadWithCompression'.time());
		// todo improve
		$uploadFile = $objAws->uploadFile( array('location' => $filename, 
		 										 'uri' => basename($filename),
												 'overwrite' => 'no',
												 'compression_level' => '1',
												), true );
				
		$curl = curl_init( AWS_URL.$uploadFile );
 		curl_setopt($curl, CURLOPT_ENCODING, 'gzip,deflate'); 
		curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
		
		$awsContents = curl_exec($curl);		
		$localContents = file_get_contents($filename);
		
		$this->assertEquals($localContents,$awsContents,"AWS file contents don't match the local content");
		
		$objAws->getObject()->delete_object(AWS_BUCKET,$uploadFile);
		
		unlink($filename);
	}

	public function testCloudFilesFileUploadWithoutCompression(){
		
		$objCf = Cdn_Provider::getProvider('cf');
		$objCf->setAccessCredentials( array('username' => CF_USERNAME, 'apikey' => CF_APIKEY, 'container' => CF_CONTAINER ) );
		
		$this->assertTrue( $objCf->login() , "Can't login to CloudFiles" );
				
		$filename = $this->_createTestFile('testCloudFilesFileUploadNoCompression'.time());
		$uploadFile = $objCf->uploadFile( array('location' => $filename, 
		 										 'uri' => basename($filename),
												 'overwrite' => 'no',
												 'compression_level' => '1',
												) , true );
				
		$curl = curl_init( CF_URL.$uploadFile );
		curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
		
		$cfContents = curl_exec($curl);		
		$localContents = file_get_contents($filename);
		
		$this->assertEquals( $localContents, $cfContents, "CF file contents don't match the local content");
		
		$objCf->getObject()->delete_object($uploadFile);
		unlink($filename);
	}
		
	/**
	 * Creates a file in the upload directory with 
	 * the contents of $sting and returns the 
	 * asboloute location of the file.
	 * 
	 * @param string $string
	 */
	
	protected function _createTestFile($string){
		
		$uploadDir = wp_upload_dir();
		$filename = $uploadDir["basedir"].'/'.time().'.js';
		 
		$fp = fopen($filename,'w+');
		fwrite($fp,$string);
		fclose($fp);
		
		return $filename;
		
	}
	
}