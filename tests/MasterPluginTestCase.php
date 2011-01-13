<?php

	/**
	 * Master class for PHPUnit tests relating to 
	 * WordPress plugins. Not to be confused with
	 * Offical WordPress PHPUnit tests.
	 * 
	 * @author Iain Cambridge
	 * @copyright Fubra Limited 2011 (C) all rights reserved.
	 * @license GNU GPLv2
	 */

class WpMasterTestCase extends PHPUnit_Framework_TestCase {
	
	/**
	 * Tells us if some request params have been set before 
	 * the setup is called. This is to ensure that request 
	 * params have been set before including the wp-load.php
	 * 
	 * @var boolean 
	 */
	protected $requestParamSet = false;
	/**
	 * The html output of WordPress.
	 * 
	 * @var string
	 */
	protected $wpOutput;
	
	/**
	 * 
	 * @return boolean true if successful.
	 */
	
	public function setup(){
		
		define("WP_INSTALLING", 1);
		define('DB_NAME', 'putyourdbnamehere');    // The name of the database
		define('DB_USER', 'usernamehere');     // Your MySQL username
		define('DB_PASSWORD', 'yourpasswordhere'); // ...and password
		define('DB_HOST', 'localhost');    // 99% chance you won't need to change this value
		define('DB_CHARSET', 'utf8');
		define('DB_COLLATE', '');
		// TODO check if remains right later
		define('ABSPATH', realpath(DIR_WP).'/');
		
		return true;
	}
	
	/**
	 * Clears the Database of *ALL* Tables. Then populates the database
	 * with WordPress install data. To replica a new WordPress install.
	 * 
	 * @return boolean true if successful.
	 */
	protected function freshInstall(){
		
		drop_tables();
		define('WP_BLOG_TITLE', rand_str());
		define('WP_USER_NAME', rand_str());
		define('WP_USER_EMAIL', rand_str().'@example.com');
		wp_install(WP_BLOG_TITLE, WP_USER_NAME, WP_USER_EMAIL, true);
	
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
	 * @param string $value Stored as a string, as PHP treats all request variables as strings.
	 * 
	 * @return 
	 */
	public function setRequestParam($type,$name,$value){
		
		$type = strtoupper($type);
		
		if ( $type != "COOKIE" && $type != "POST" && $type != "GET" ){
			return false;	
		}
				
		${'_'.$type}[$name] = (string) $value;
		$_REQUEST[$name] = (string) $value;
		// So self::setUp() can successfully run.
		$this->requestParamSet = true;
		
		return true;
		
	}
	
}