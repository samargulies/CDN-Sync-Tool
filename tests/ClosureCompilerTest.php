<?php
require_once '../lib/closurecompiler.php';

/**
 * ClosureCompiler test case.
 * 
 * @author Iain Cambridge
 * @license GPL v2
 */

class ClosureCompilerTest extends PHPUnit_Framework_TestCase {
	
	/**
	 * @var ClosureCompiler
	 */
	private $ClosureCompiler;
	/**
	 * Simple JavaScript snippet that we already know what the output will be.
	 * @var string
	 */
	private $orignalJs;
	/**
     * Holds the known outputs of the JavaScript snippets.
     * @var array
	 */
	private $afterJs = array();
	
	
	/**
	 * Prepares the environment before running a test.
	 */
	protected function setUp() {
		parent::setUp ();
		
		
		$this->ClosureCompiler = new ClosureCompiler();
	
		// The before JS. Simple one (Look
		$this->orignalJs = "function hello(name) {".PHP_EOL;
		$this->orignalJs .= "  alert('Hello, ' + name);".PHP_EOL;
		$this->orignalJs .= "}".PHP_EOL;
		$this->orignalJs .= "hello('New user');".PHP_EOL;
		// After after JS. 
		$this->afterJs["whitespace"] = 'function hello(name){alert("Hello, "+name)}hello("New user");';
		$this->afterJs["simple"] = 'function hello(a){alert("Hello, "+a)}hello("New user");';
		$this->afterJs["advanced"] = 'alert("Hello, New user");';
		
	}
	
	/**
	 * Cleans up the environment after running a test.
	 */
	protected function tearDown() {
		// TODO Auto-generated ClosureCompilerTest::tearDown()
		

		$this->ClosureCompiler = null;
		
		parent::tearDown ();
	}
	
	/**
	 * Constructs the test case.
	 */
	public function __construct() {
		// TODO Auto-generated constructor
	}

	/**
	 * Checks
	 * 
	 */
	public function testTextWhitespaceReturn(){

		$this->ClosureCompiler->fetchCode($this->orignalJs, 
										array( "output_format" => ClosureCompiler::FORMAT_TEXT,
											   "output_info" => ClosureCompiler::INFO_CODE) );				
										
		$this->assertEquals($this->afterJs["whitespace"],
							$this->ClosureCompiler->compiledCode, 
							"Whitespace Plain text code doesn't match up");
		
		
	}
	
	public function testJsonWhitespaceReturn(){
		
		$this->ClosureCompiler->fetchCode($this->orignalJs, 
										array( "output_format" => ClosureCompiler::FORMAT_JSON,
											   "output_info" => ClosureCompiler::INFO_CODE) );
										
		$this->assertEquals($this->afterJs["whitespace"], 
							$this->ClosureCompiler->compiledCode,
							"Whitespace JSON code doesn't match up");
		
		return;
		
	}
	
	public function testXmlWhitespaceReturn(){
		
		$this->ClosureCompiler->fetchCode($this->orignalJs, 
										array( "output_format" => ClosureCompiler::FORMAT_XML,
											   "output_info" => ClosureCompiler::INFO_CODE) );
										
			
		$this->assertEquals($this->afterJs["whitespace"], 
							$this->ClosureCompiler->compiledCode,
							"Whitespace XML code doesn't match up");							
										
	}
	

	public function testXmlSimpleReturn(){
		
		$this->ClosureCompiler->fetchCode($this->orignalJs, 
										array( "output_format" => ClosureCompiler::FORMAT_XML,
											   "output_info" => ClosureCompiler::INFO_CODE,
											   "compilation_level" => ClosureCompiler::LEVEL_SIMPLE) );
										
			
		$this->assertEquals($this->afterJs["simple"], 
							$this->ClosureCompiler->compiledCode,
							"Whitespace XML code doesn't match up");	
		
	}
	
	public function testTextSimpleReturn(){
		
		$this->ClosureCompiler->fetchCode($this->orignalJs, 
										array( "output_format" => ClosureCompiler::FORMAT_TEXT,
											   "output_info" => ClosureCompiler::INFO_CODE,
											   "compilation_level" => ClosureCompiler::LEVEL_SIMPLE) );
										
			
		$this->assertEquals($this->afterJs["simple"], 
							$this->ClosureCompiler->compiledCode,
							"Whitespace XML code doesn't match up");	
		
	}
	
	public function testJsonSimpleReturn(){
		
		$this->ClosureCompiler->fetchCode($this->orignalJs, 
										array( "output_format" => ClosureCompiler::FORMAT_JSON,
											   "output_info" => ClosureCompiler::INFO_CODE,
											   "compilation_level" => ClosureCompiler::LEVEL_SIMPLE) );
										
		$this->assertEquals($this->afterJs["simple"], 
							$this->ClosureCompiler->compiledCode,
							"Whitespace XML code doesn't match up");	
		
	}
	
}

