<?php

/*
Plugin Name: CDN Sync Tool
Plugin URI: http://catn.com/
Description: Syncs static files to a CDN
Author: Fubra Limited
Author URI: http://www.catn.com
Version: 1.12
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
		if(is_multisite()) {
			exit('Unfortunately this plugin isn\'t currently compatible with multisite. We apologise');
		}
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
	
	if ( version_compare($oldVersion, '1.12') < 0 ){
		$wpdb->query("DROP TABLE IF EXISTS `".CST_TABLE_JSCSS."`");
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
// Stolen from http://www.php.net/manual/en/function.mime-content-type.php#87856
if(!function_exists('mime_content_type')) {

    function mime_content_type($filename) {

        $mime_types = array(

            'txt' => 'text/plain',
            'htm' => 'text/html',
            'html' => 'text/html',
            'php' => 'text/html',
            'css' => 'text/css',
            'js' => 'application/javascript',
            'json' => 'application/json',
            'xml' => 'application/xml',
            'swf' => 'application/x-shockwave-flash',
            'flv' => 'video/x-flv',

            // images
            'png' => 'image/png',
            'jpe' => 'image/jpeg',
            'jpeg' => 'image/jpeg',
            'jpg' => 'image/jpeg',
            'gif' => 'image/gif',
            'bmp' => 'image/bmp',
            'ico' => 'image/vnd.microsoft.icon',
            'tiff' => 'image/tiff',
            'tif' => 'image/tiff',
            'svg' => 'image/svg+xml',
            'svgz' => 'image/svg+xml',

            // archives
            'zip' => 'application/zip',
            'rar' => 'application/x-rar-compressed',
            'exe' => 'application/x-msdownload',
            'msi' => 'application/x-msdownload',
            'cab' => 'application/vnd.ms-cab-compressed',

            // audio/video
            'mp3' => 'audio/mpeg',
            'qt' => 'video/quicktime',
            'mov' => 'video/quicktime',

            // adobe
            'pdf' => 'application/pdf',
            'psd' => 'image/vnd.adobe.photoshop',
            'ai' => 'application/postscript',
            'eps' => 'application/postscript',
            'ps' => 'application/postscript',

            // ms office
            'doc' => 'application/msword',
            'rtf' => 'application/rtf',
            'xls' => 'application/vnd.ms-excel',
            'ppt' => 'application/vnd.ms-powerpoint',

            // open office
            'odt' => 'application/vnd.oasis.opendocument.text',
            'ods' => 'application/vnd.oasis.opendocument.spreadsheet',
        );

        $ext = strtolower(array_pop(explode('.',$filename)));
        if (array_key_exists($ext, $mime_types)) {
            return $mime_types[$ext];
        }
        elseif (function_exists('finfo_open')) {
            $finfo = finfo_open(FILEINFO_MIME);
            $mimetype = finfo_file($finfo, $filename);
            finfo_close($finfo);
            return $mimetype;
        }
        else {
            return 'application/octet-stream';
        }
    }
}
register_activation_hook( __FILE__, "cst_install" );
$objCstPlugin = new Cst_Plugin();

function cst_wp_update_attachment_metadata($data) {

    Cst_Plugin_Admin::uploadMedia($data);

    return $data;
}
add_filter('wp_update_attachment_metadata', 'cst_wp_update_attachment_metadata');