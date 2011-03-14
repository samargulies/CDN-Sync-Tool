<?php

global $wpdb;

define( "CST_TABLE_FILES" , $wpdb->get_blog_prefix().'cst_files' );
define( "CST_TABLE_JSCSS" , $wpdb->get_blog_prefix().'cst_jscss' );

define( "CST_PAGE_MAIN", "cst-main" );
define( "CST_PAGE_CONTACT", "cst-contact" );
define( "CST_PAGE_CATN" , "cst-catn" );
define( "CST_PAGE_HELP" , "cst-help" );

define( "CST_VERSION" , "1.9"  );
define( "CST_DIR", dirname(dirname(__FILE__)) );
define( "CST_CONTACT_EMAIL", "support@catn.com" );