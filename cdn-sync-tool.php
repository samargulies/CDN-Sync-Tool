<?php

/*
Plugin Name: CDN Sync Tool
Plugin URI: http://catn.com/
Description: Syncs static files to a CDN
Author: Fubra Limited
Author URI: http://www.catn.com
Version: 1.8
*/

/*
 * Copyright (C) 2010  Fubra Limited
 * 
 * This program is free software; you can redistribute it and/or
 * modify it under the terms of the GNU General Public License
 * as published by the Free Software Foundation; either version 2
 * of the License, or (at your option) any later version.Gert
 * 
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 * 
 * You should have received a copy of the GNU General Public License
 * along with this program; if not, write to the Free Software
 * Foundation, Inc., 51 Franklin Street, Fifth Floor, Boston, MA  02110-1301, USA.
 */ 


require_once 'etc/constants.php';
require_once CST_DIR.'/lib/Cst/Debug.php';
require_once CST_DIR.'/lib/Cst/Sync.php';
require_once CST_DIR.'/lib/Cst/Plugin.php';

function cst_install(){
		global $wpdb;	
		$oldVersion = get_option("cst_version");	
		update_option("cst_version", CST_VERSION);
		
		
		if ( $oldVersion !== false ){
			// If not current update!
			// Otherwise exit.
			if ( $oldVersion != CST_VERSION ){
				cst_upgrade($oldVersion);	
			} 
			Cst_Debug::addLog("CST upgraded successfully");
			return;
		}	
		
		$wpdb->query("CREATE TABLE IF NOT EXISTS ".CST_TABLE_FILES." (
					  `id` int(11) NOT NULL AUTO_INCREMENT,
					  `filename` varchar(255) NOT NULL,
					  `smushed` varchar(11) NOT NULL,
					  `transferred` varchar(11) NOT NULL,
					  `timestamp` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
					  `hash` varchar(32) DEFAULT NULL,
					  `file_location` varchar(255) DEFAULT NULL,
					   `media` int(11) DEFAULT NULL,
						  PRIMARY KEY (`id`)
						) ENGINE = MYISAM ;");
		$wpdb->query("CREATE TABLE IF NOT EXISTS `".CST_TABLE_JSCSS."` (
							`id` INT NOT NULL ,
							`filename` INT NOT NULL ,
							`template` INT NOT NULL ,
							`timestamp` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP
							`type` varchar(3) NOT NULL
							) ENGINE = MYISAM ;
									");
		update_option("cst_theme",true);		
		wp_schedule_event(time(), 'hourly', 'cst_cron_hourly');
		Cst_Debug::addLog("CST installed successfully");
}

function cst_upgrade($oldVersion){
	
	global $wpdb;

	if ( $oldVersion <= "1.2" ){		
		wp_schedule_event(time(), 'hourly', 'cst_cron_hourly');
	}
	
	if ( $oldVersion <= "0.8" ){
		$wpdb->query("ALTER TABLE `".CST_TABLE_FILES."` ADD `hash` VARCHAR( 32 ) NULL");
		$wpdb->query("CREATE TABLE IF NOT EXISTS `".CST_TABLE_JSCSS."` ( 
									`id` int(11) NOT NULL,
									`filename` varchar(255) NOT NULL,
									`template` varchar(255) NOT NULL,
									`timestamp` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
									`tAWS_SECRET_KEype` varchar(3) NOT NULL
								) ENGINE = MYISAM ;
										");
	} 
	
	if ( $oldVersion <= "0.9" ){
		$wpdb->query("ALTER TABLE  `".CST_TABLE_JSCSS."` ADD  `type` VARCHAR( 255 ) NOT NULL");
	} 
	
	if ( $oldVersion <= "1.1" ){
		$wpdb->query("ALTER TABLE `".CST_TABLE_FILES."` ADD `media` INT(1) NULL");
		$wpdb->query("ALTER TABLE `".CST_TABLE_FILES."` ADD `file_location` varchar(255) DEFAULT NULL,");
	}
	
	
	return true;
}

function cst_cron_hourly(){
	
	global $wpdb;
	
	Cst_Debug::addLog('Hourly cron has run');		

	$fileHashes = $wpdb->get_results( "SELECT * FROM `".CST_TABLE_FILES."` GROUP BY filename",ARRAY_A );
		
	foreach( $fileHashes as $file ){
		if ( $file['hash'] != hash_file('md5',$file['file_location']) ){
			Cst_Sync::process($file['filename'],$file['media']);
		}
	}
	
}

register_activation_hook( __FILE__, "cst_install" );
$objCstPlugin = new Cst_Plugin();