<?php

require_once 'MasterPluginTestCase.php';

  /**
   * CDN Sync Tool Admin Options page.
   * @author Iain Cambridge
   * @version 1.0
   */

class AdminOptionTest extends WpMasterTestCase {
	
	/**
	 * 
	 * 
	 * @var Cst_Plugin
	 */
	
	protected $objPlugin;
		
	public function setUp() {
		parent::setUp ();

		
	}
	
	
	
	public function testHooksAreHookedProperly(){
		
		global $wp_actions,$wp_filters,$merged_filters;
		
		$wp_actions     = array();
		$wp_filters     = array();
		$merged_filters = array();
		
		$objCsPlugin = new Cst_Plugin();
		
		$this->assertContains("admin_init", array_keys($wp_filters) );
	
	}
	
}