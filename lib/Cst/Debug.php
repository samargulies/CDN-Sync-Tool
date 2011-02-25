<?php

	/**
	 * Debug class containing a central place for storing and retriving the 
	 * debug information from the current runtime.
	 * 
	 * @author Iain Cambridge
	 * @copyright All rights reserved 2011.
	 * @license GNU GPLv2
	 */

class Cst_Debug {

	/**
	 * The debug log - actions taken, variables used, etc.
	 * @var array
	 */
	private static $debugLog = array();
		
	/**
	 * Adds a message to the log array if
	 * WP_DEBUG is defined.
	 * 
	 * @param string $log
	 */
	public static function addLog($log){
		if ( defined("WP_DEBUG") && WP_DEBUG == true ){
			error_log(microtime(true)." ".$log);
			self::$debugLog[] = microtime(true)." ".$log;
		}
		return true;
	}
	
	/**
	 * Returns an array.
	 */
	public static function getLog(){
		return self::$debugLog;
	}
	
}