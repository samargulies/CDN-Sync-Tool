<?php

	/**
	 * Quick test to ensure everything hooked in properly.
	 * 
	 * @author Iain Cambridge
	 * @license GPL v2
	 * @copyright Fubra Limited all rights reserved 2011 (c)
	 */

class HooksTest extends PHPUnit_Framework_TestCase {
	
	public function testActionHooks(){
		
		global $wp_filter;
		
		$adminMenu = $wp_filter['admin_menu'];
		$pass = false;
		foreach ( $adminMenu[10] as $filter ){
			
			if ( is_array($filter['function'])  ){
				if ( is_a($filter['function'][0],'Cst_Plugin_Admin') ){
					$pass = true;
					break;
				}
			} 
			
		}		
		$this->assertTrue($pass);
		u
		
	}
	
	
	
	
	
}