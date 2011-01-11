<?php

/**
 * Static test suite.
 */
class WpPluginTestSuite extends PHPUnit_Framework_TestSuite {
	
	/**
	 * Constructs the test suite handler.
	 */
	public function __construct() {
		$this->setName ( 'testsSuite' );
	
	}
	
	/**
	 * Sets up WordPress.
	 */
	public function setUp() {
		parent::setUp();
		
		
		
		
		
		require_once($wpLoad);
		
	}
	
	/**
	 * Creates the suite.
	 */
	public static function suite() {
		return new self ();
	}	

	/**
	 * Checks
	 * 
	 */
	public function testXmlReturn(){

		$this->fail("Failed");
		
	}			
}

