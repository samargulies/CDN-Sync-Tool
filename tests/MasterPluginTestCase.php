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
		define('DB_NAME', '');    // The name of the database
		define('DB_USER', '');     // Your MySQL username
		define('DB_PASSWORD', '); // ...and password
		define('DB_HOST', 'localhost');    // 99% chance you won't need to change this value
		define('DB_CHARSET', 'utf8');
		define('DB_COLLATE', '');
		define('WP_BLOG_TITLE', rand_str());
		define('WP_USER_NAME', rand_str());
		define('WP_USER_EMAIL', rand_str().'@example.com');
		// TODO check if remains right later
		define('ABSPATH', realpath('../../../..').'/');
		
		require_once ABSPATH.'wp-settings.php';
		
		wp_install(WP_BLOG_TITLE, WP_USER_NAME, WP_USER_EMAIL, true);
		
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
	
	}
	
}