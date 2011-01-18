<?php

  /**
   * Simple PHP interface for Google's
   * Closure Compiler. To optimize 
   * JavaScript files to reduce bandwidth
   * and increase proformance.
   * 
   * @author Iain Cambridge
   * @copyright Fubra Limited 2010 (c) 
   * @license GPLv2 
   */


class ClosureCompiler {
	
	/**
	 * The URL for Closure Compiler's RESTful API.
	 * @var string
	 */
	const API_URL = "http://closure-compiler.appspot.com/compile";
	/**
	 * The compression level of just remove the whitespace.
	 * @var string
	 */
	const LEVEL_WHITESPACE = "WHITESPACE_ONLY";
	/**
	 * The compression level of simple optimizations
	 * @var string
	 */
	const LEVEL_SIMPLE = "SIMPLE_OPTIMIZATIONS";
	/**
	 * The compression level of advanced optimizations.
	 * @var string
	 */
	CONST LEVEL_ADVANCED = "ADVANCED_OPTIMIZATIONS";
	/**
	 * The return value will be given in JSON
	 * @var string
	 */
	const FORMAT_JSON = "json";
	/**
	 * The return value will be given in XML
	 * @var string
	 */
	const FORMAT_XML = "xml";
	/**
	 * The return value will be given in plain text
	 * @var string
	 */
	const FORMAT_TEXT = "text";
	/**
	 * Just fetch the compiled code.
	 * @var string
	 */
	const INFO_CODE = "compiled_code";
	/**
	 * Fetches warnings
	 * @var string
	 */
	const INFO_WARNINGS = "warnings";
	/**
	 * Fetches errors
	 * @var string
	 */
	const INFO_ERRORS = "errors";
	/**
	 * Fetches stats
	 * @var string
	 */
	const INFO_STATS = "statistics";
	
	/**
	 * The paramaters for the current API call.
	 * @var array 
	 */
	protected $params;
	/**
	 * Array containing the values for the last API call.
	 * @var array
	 */
	protected $returnData = array();
	
	/**
	 * The constructor method, optional paramaters
	 * to execute an API call upon creation.
	 * 
	 * @param string $javascript The URL location or JavaScript code that is to be optimized
	 * @param array $params The paramaters to be passed to the API
	 */
	
	public function __construct($javascript = false, $params = array()){
		
		if ( !preg_match("~^https?://~isU",$javascript) ){
			$this->fetchCode($javascript, $params);	
		} elseif ( !empty($javascript) ) {
			$this->fetchFile($javascript, $params);
		}
		// do nothing.
		return;
		
	}
	
	/**
	 * The method to call the execution of an API
	 * call to optimize an URL.Swee
	 * 
	 * @param string $filename The URL location of the JavaScript code that is to be optimized
	 * @param array $params The paramaters to be passed to the API
	 */
	public function fetchFile($filename, $params = array()){
		
		$postVars = $this->_params($params);
		$postVars .= "code_url=".urlencode($filename); 
		$this->_curl($postVars);
		
	}
	
	/**
	 * Method to call an execution of an API call
	 * to optimize the JavaScript code.
	 * 
	 * @param unknown_type $code
	 * @param unknown_type $params
	 */
	
	public function fetchCode($code, $params = array()){
		
		$postVars = $this->_params($params);
		$postVars .= "js_code=".urlencode($code);
		$this->_curl($postVars);
		
	}
	
	/**
	 * Executes the curl request handles the return data.
	 * 
	 * @param string $postVars
	 */
	
	protected function _curl($postVars){
		
		$this->returnData = array();
		$ch = curl_init();
		
		curl_setopt($ch, CURLOPT_URL, self::API_URL);
		curl_setopt($ch, CURLOPT_POST, 1);
		curl_setopt($ch, CURLOPT_POSTFIELDS, $postVars);
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
		$returnData = curl_exec($ch);
		
		switch($this->params["output_format"]){
			
			case 'xml':
				
				$simpleXml = new SimpleXMLElement($returnData);
				$this->returnData["compiledCode"] = (string)$simpleXml->compiledCode;
				break;
			case 'json':
				$this->returnData = (array)json_decode($returnData);
				break;
			
			default:
				$this->returnData["compiledCode"] = $returnData;
				break;
				
		}
		
	}
	
	/**
	 * Turns the array into a post field value for
	 * curl.
	 * 
	 * @param array $params
	 * 
	 * @return string 
	 */
	protected function _params($params){
		
		$postVar = "";
		if ( !is_array($params) ){
			$params = array();	
		}

		if ( !isset($params["output_info"]) ){
			$params["output_info"] = self::INFO_CODE;
		}
		
		if ( !isset($params["output_format"]) ){
			$params["output_format"] = self::FORMAT_TEXT;
		}
		
		if ( !isset($params["compilation_level"]) ){
			$params["compilation_level"] = self::LEVEL_WHITESPACE;
		}
		
		foreach ( $params as $key => $value ){
			if ( is_array($value) ){
				foreach($value as $subvalue){
					$postVar .= $key."=".$subvalue."&";
				}
			} else {
				$postVar .= $key."=".$value."&";
			}
		}
		
		
		$this->params = $params;
		
		return $postVar;
		
	}
	
	/**
	 * Returns the data from the API call.
	 * @param mixed $key
	 */
	
	public function __get($key){
		
		return trim($this->returnData[$key]);
		
	}
	
	/**
	 * Alerts to people to the variable being set.
	 * @param mixed $key
	 */
	public function __isset($key){
		
		return isset($this->returnData[$key]);
		
	}
}