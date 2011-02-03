<?php

	/**
	 * Quick test to ensure everything hooked in properly.
	 * 
	 * @author Iain Cambridge
	 * @license GPL v2
	 * @copyright Fubra Limited all rights reserved 2011 (c)
	 */

class HooksTest extends PHPUnit_Framework_TestCase {
	
	/**
	 * Checks to see if the menu is properly assembled.
	 */
	
	public function testMenuIsset(){
		
		global $menu, $submenu;
		
		
		// Check to see if the menu item exists
		
		$pass = false;
		foreach( $menu as $menuItem ){
			if ( $menuItem[0] == "CDN Sync Tool" && $menuItem[2] == "cst-main" ){
				$pass = true;
			} 			
		}
		$this->assertTrue($pass, "Menu item doesn't exist");
		
		// Check to see if the sub menu exists
		// and that it contains all the elements
		// it's meant to contain.
		
		$this->assertContains("cst-main", array_keys($submenu), "Submenu array doesn't exist");
		
		$this->assertEquals( 
						array( 
						  array(
						  	"CDN Sync Tool","manage_options","cst-main",
						  	"CDN Sync Tool","menu-top toplevel_page_cst-main",
						    "toplevel_page_cst-main","http://iain.fubradev.vc.catn.com/wp-admin/images/generic.png"
						  ),
						  array(
						  	"Contact","manage_options","cst-contact","Contact"
						  ),
						  array(
						  	"CatN","manage_options","cst-catn","CatN PHP Experts"
						  )
						),
						$submenu["cst-main"],
						"Sub Menu isn't what expected"
					);
							
	}
	
	public function testAdminHooksAreHooked() {
		
		global $wp_filter;
		
		// Check to see if hooks are attached
		/*
		 	Example hook check.
					array(
						"hook" => "", 
						"class_name" => "Cst_Plugin_Admin", 
						"method_name" => "", 
						"message" => ""
					),
		 */
		$items = array (
					array(
						"hook" => "switch_theme", 
						"class_name" => "Cst_Plugin_Admin", 
						"method_name" => "switchTheme", 
						"message" => "Switch theme hook isn't attached!"
					),
					array(
						"hook" => "admin_init", 
						"class_name" => "Cst_Plugin_Admin", 
						"method_name" => "syncFiles", 
						"message" => "The sync files hook isn't attached!"
					),
					array(
						"hook" => "admin_init", 
						"class_name" => "Cst_Plugin_Admin", 
						"method_name" => "preAdminHead", 
						"message" => "The preAdminHead hook isn't attached!"
					),
					array(
						"hook" => "wp_generate_attachment_metadata", 
						"class_name" => "Cst_Plugin_Admin", 
						"method_name" => "uploadMedia", 
						"message" => "The uploadMedia hook isn't attached!"
					),
					array(
						"hook" => "admin_head", 
						"class_name" => "Cst_Plugin_Admin", 
						"method_name" => "head", 
						"message" => "The head hook isn't attached!"
					),
					array(
						"hook" => "admin_menu", 
						"class_name" => "Cst_Plugin_Admin", 
						"method_name" => "menu", 
						"message" => "Menu hook isn't attached!"
					),
					
				);
		
		// Loop through the filters above		
				
		foreach ( $items as $item  ){
						
			$pass = false;
			$filters = $wp_filter[ $item["hook"] ];
			$pass = false;
			foreach ( $filters[10] as $filter ){			
				if ( is_array($filter['function'])  ){
					if ( is_a($filter['function'][0],$item["class_name"])
					  && $filter['function'][1] == $item["method_name"] ){
						$pass = true;
						break;
					}
				} 			
			}		
			$this->assertTrue($pass,$item["message"]);
		
		}
			
	}
}