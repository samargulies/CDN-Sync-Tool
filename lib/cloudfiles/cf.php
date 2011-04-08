<?php
ini_set('display_errors', 1);
error_reporting(E_ALL);
require 'cloudfiles.php';
print "test";

        $auth = new CF_Authentication(
                        "ryanjwilliams",
                        "79c577e0d64da1fe7d833aab3ae20228",
                        NULL,
                        UK_AUTHURL);
        //$auth->ssl_use_cabundle(); // if breaks try removing.

if ( $auth->authenticate() ) {
        $cloudfiles = new CF_Connection($auth);
        $container = $cloudfiles->get_container('testing');
        if ( !is_a($container,'CF_Container') ){
                return false;
        }
        return true;
} else {
        return false;
}
