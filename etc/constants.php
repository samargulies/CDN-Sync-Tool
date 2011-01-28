<?php

global $wpdb;

define( "CST_TABLE_FILES" , $wpdb->prefix.'cst_files' );
define( "CST_TABLE_JSCSS" , $wpdb->prefix.'cst_jscss' );

define( "CST_PAGE_MAIN", "cst-main" );
define( "CST_PAGE_CONTACT", "cst-contact" );
define( "CST_PAGE_CATN" , "cst-catn" );
define( "CST_PAGE_HELP" , "cst-help" );

define( "CST_VERSION" , "1.0"  );
define( "CST_DIR", dirname(dirname(__FILE__)) );
define( "CST_CONTACT_EMAIL", "iain.cambridge@fubra.com" );