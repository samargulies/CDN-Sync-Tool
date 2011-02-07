<?php

class Cst_Page_Contact extends Cst_Page {
	
	public function display($test = false){
			
		global $wp_version;
		
		if ( !empty($_POST) ){
			
			
			$errorArray = array();
			if ( !isset($_POST['email']) || empty($_POST['email'])){
				$errorArray[] = 'Email is required';
			} elseif ( !is_email($_POST['email']) ){
				$errorArray[] = 'Invalid from email';
			}
			
			if ( !isset($_POST['name']) || empty($_POST['name']) ){
				$errorArray[] = 'Name is required';
			}
			
			if ( !isset($_POST['message']) || empty($_POST['message']) ){
				$errorArray[] = 'Message is required';
			}
			
			if ( !isset($_POST['reason']) || empty($_POST['reason']) ){
				$errorArray[] = 'Reason is required';
				// Tho this should never be blank or empty!
			} elseif ( $_POST['reason'] != "Bug" && $_POST['reason'] != 'Suggestion' 
			 		&& $_POST['reason'] != 'You guys rock!' && $_POST['reason'] != 'You guys are the suck!'
			 		&& $_POST['reason'] != 'Moving to CatN') {
				$errorArray[] = 'Invalid reason';
				// Definetly something a miss here
			}
			
			if ( empty($errorArray) ){
				$fromEmail = get_option('admin_email');
				
	   			$headers = 'From: CST Contact Form <'.$fromEmail.'>' . "\r\n";
	   			$headers .= 'Reply-To: '.trim($_POST['name']).' <'.$_POST['email'].'>\r\n';
	   			
	   			$message = 'From: '.trim($_POST['name']).' <'.$fromEmail.'>'.PHP_EOL;
	   			$message .= 'CST Version: '.CST_VERSION.PHP_EOL;
	   			$message .= 'PHP Version: '.PHP_VERSION.PHP_EOL;
	   			$message .= 'WordPress Version: '.$wp_version.PHP_EOL;
	   			$message .= 'Site URL: '.get_bloginfo('url').PHP_EOL;
	   			$message .= 'Message: '.htmlentities($_POST['message']).PHP_EOL;
	   			   			
				if ( !wp_mail(CST_CONTACT_EMAIL,$_POST['reason'], $message, $headers) ){
					Cst_Debug::addLog('Unable to send email, please check wordpress settings');
				} else {
					$successMessage = 'Email sent! Thank you for reponse';
					Cst_Debug::addLog('Email sent!');
				}		
				
			} else {
				Cst_Debug::addLog("Email Errors : ".print_r($errorArray,true));
			}
			
		}
		
		require_once CST_DIR."/pages/misc/contact.html";
	}
	
}