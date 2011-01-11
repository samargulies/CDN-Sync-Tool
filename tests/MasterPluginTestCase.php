<?php

	/**
	 * Master class for WordPress plugin 
	 * development unit tests.
	 * 
	 * @author Iain Cambridge
	 * @copyright Fubra Limited 2011 (c) all rights reserved.
	 * @license GNU GPL v2
	 */

class MasterPluginTestCase extends PHPUnit_Framework_TestCase {
	
	/**
	 * Tells us if some request params have been set before 
	 * the setup is called. This is to ensure that request 
	 * params have been set before including the wp-load.php
	 * 
	 * @var boolean 
	 */
	protected $requestParamSet = false;
	
	/**
	 * Includes the wp-load.php to be done after the request params 
	 * have been set. Checks that request params have been set before 
	 * including wp-load.php.
	 * 
	 * If wp-load.php can't be found or request params haven't been 
	 * set before this is called the function calls the exit function.
	 * 
	 * @return boolean true if successful.
	 */
	
	public function setup(){
		
		if ( $this->requestParamSet === false ){
			print "Child test must set request params first.".PHP_EOL;
			exit;
		}
		
		$wpLoad = realpath('../../../../wp-load.php');
		//TODO find neater solution!
		if ( !file_exists( $wpLoad ) ){
			print "Unable to load the wp-load.php file. Test ending.".PHP_EOL;
			exit;
		}
		
		require_once($wpLoad);
		
		return true;
	}

	/**
	 * Sets request variables for both the super global for the single
	 * variable type and in the request variable.
	 * 
	 * for example
	 * <code>
	 * $this->setRequestParam("post","submit","true");
	 * // will result in
	 * $_REQUEST["submit"] = "true";
	 * $_POST["submit"] = "true";
	 * </code>
	 * 
	 * @param string $type Ethier COOKIE,POST,GET
	 * @param string $name stored as a string, as PHP treats all request variable names as strings
	 * @param string $value Stored as a string, as PHP treats all request variables as strings
	 * 
	 * @return 
	 */
	public function setRequestParam($type,$name,$value){
		
	}
	
}