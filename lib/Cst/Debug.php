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
	 * The error messages - not the ones from incorrect usage but ones from broken code.
	 * @var array
	 */
	private static $messages = array();
	
	/**
	 * Adds a message to the message array if
	 * WP_DEBUG is defined.
	 * 
	 * @param string $message
	 */
	public static function addMessage($message){
		if ( defined("WP_DEBUG") ){
			self::$messages[] = $message;
		}
		return true;
	}
	
	/**
	 * Adds a message to the log array if
	 * WP_DEBUG is defined.
	 * 
	 * @param string $log
	 */
	public static function addLog($log){
		if ( defined("WP_DEBUG") ){
			self::$debugLog[] = $log;
		}
		return true;
	}
	
	/**
	 * Returns an array.
	 */
	public static function getLog(){
		return self::$debugLog;
	}
	
	/**
	 * Returns an array.
	 */
	public static function getMessages(){
		return self::$messages;
	}
	
}