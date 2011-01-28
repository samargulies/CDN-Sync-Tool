<?php

	/**
	 * Origin pull mock class
	 * 
	 * @author Iain Cambridge
	 * @license GPL v2
	 * @copyright Fubra Limited 2011 all rights reserved (c).
	 */

class Cdn_Origin extends Cdn_Provider {
	
	protected $resSftp;
	
	public function antiHotlinking(){
		
		return true;
	}
	
	public function login(){
		
		return true;
	}
	
	public function setAccessCredentials($details){
		
		return true;
	}
	
	
	public function uploadFile($file, $media = true){
		
		return true;
		
	}
	
}