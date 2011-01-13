<?php

require_once 'WpMasterTestCase.php';

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

		$objCsPlugin = new Cst_Plugin();
		
	}
	
	public function testRandom(){
		$this->assertEquals(true,true);
	}
	
}