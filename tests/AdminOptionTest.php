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
		
		$_SERVER['SERVER_PROTOCOL'] = 'HTTP/1.0';
		$_SERVER['SERVER_PORT'] = "80";
		$_SERVER['HTTP_HOST'] = "iain.fubradev.vc.catn.com";
		$_SERVER['REQUEST_URI'] = "/2011/01/10/super-cache-multiple-cname/";
		
		$this->setRequestParam("g", "ref", "random");
		parent::setUp ();

		$objCsPlugin = new Cst_Plugin();
		
	}
	
	public function testRandom(){
		$this->assertEquals(true,true);
	}
	
}