<?php

	/**
	 * Concatention Model, to combine JavaScript and CSS 
	 * files. Instead of a single file per page build, this
	 * model will build concatention files.
	 * 
	 * @author Iain Cambridge
	 * @copyright Iain Cambridge all rights reserved 2011 (c)
	 * @license http://backie.org/copyright/freebsd-license/ FreeBSD License
	 */

class Iain_Optimize_Concat {
	
	/**
	 * Contains the raw files with the pages they  are to be used with.
	 * @var array
	 */	
	public static $rawFiles = array( 'scripts' => array(), 'styles' => array() );
	
	/**
	 * Holds the build names for each page.
	 * @var array
	 */
	public static $pageBuilds = array( 'scripts' => array(), 'styles' => array() );
	
	/**
	 * Holds all the builds lists.
	 * @var array
	 */
	public static $buildPatterns = array( 'scripts' => array(), 'styles' => array() );
	
	/**
	 * Contains the actual build files.
	 * @var unknown_type
	 */
	public static $builds = array( 'scripts' => array(), 'styles' => array() );
	
	/**
	 * Handles adding CSS or JavaScript files to the self::$rawFiles[$fileType]
	 * array. 
	 *  
	 * @param String $fileType Is ethier scripts or styles.
	 * @param Array|String $controllers The name(s) of the controller(s) that the file(s) are for.
	 * @param Array|String $script The file(s) that are to be used.
	 * 
	 * @return Boolean true is successful, false is unsuccessful.
	 */
	public static function addFile($fileType,$controllers,$script){
		$fileType = strtolower($fileType);
		if ( $fileType != "scripts" && $fileType != "styles" ){
			return false;
		}
		
		if ( !is_array($controllers) ){
			$controllers = array($controllers);
		}
		
		foreach($controllers as $controller ){
			if ( !isset(self::$rawFiles[$fileType][$controller]) ){
				self::$rawFiles[$fileType][$controller] = array();
			}
			if ( !is_array($script) ){
				self::$rawFiles[$fileType][$controller][] = $script;
			} else {
				self::$rawFiles[$fileType][$controller] = array_merge(self::$rawFiles[$fileType][$controller],$script);
			}
		}
		
		return true;
	}
	
	/**
	 * Handles turning indiviual files into concentration patterns.
	 * 
	 * @param String $fileType
	 * 
	 * @return Array returns the build patterns
	 */
	protected static function process( $fileType ){
		$buildFiles = array();
		self::$buildPatterns = array();
		foreach ( self::$rawFiles[$fileType] as $controller => $controllerFiles ){
			
			foreach ( $controllerFiles as $file ){
				if ( !isset($buildFiles[$file]) || !is_array($buildFiles[$file]) ){
					$buildFiles[$file] = array();
				}
				$buildFiles[$file][] = $controller;
			}
			
		}
		
		foreach( $buildFiles as $file => $controllerArray ){
			
			sort($controllerArray);
			$key = implode("", $controllerArray);
			
			foreach ( $controllerArray as $controller ){
				if ( !isset(self::$pageBuilds[$fileType][$controller]) || !is_array(self::$pageBuilds[$fileType][$controller]) ){
					self::$pageBuilds[$fileType][$controller] = array();
				}
				self::$pageBuilds[$fileType][$controller][] = $key;	
			} 
			
			if ( !isset(self::$buildPatterns[$key]) || !is_array(self::$buildPatterns[$key]) ){
				self::$buildPatterns[$key] = array();	
			}
			self::$buildPatterns[$key][] = $file;
		}
		
		return self::$buildPatterns;
	}
	
	/**
	 * Fetches the build patterns. For testing purposes.
	 * 
	 * @param String $fileType
	 */
	public static function getBuildPatterns($fileType){

		return self::process($fileType);
		
	}
	
	
	/**
	 * Creates the builds for a specified controller or all the controllers.
	 * 
	 * @param String $fileType  Is ethier scripts or styles.
	 * @param String $buildLocation the directory where the builds should happen.
	 * @param String|Boolean $controller
	 */
	public static function createBuilds($fileType, $buildLocation, $controller = false){
	
		if ( $fileType != "scripts" && $fileType != "styles" ){
			return false;
		}
		
		$buildPatterns = self::process($fileType);
		$extension = ( $fileType == 'scripts' ) ? 'js' : 'css';

		$toBuild = ( $controller != false ) ? self::$pageBuilds[$fileType][$controller] : array_keys($buildPatterns); 
	
		foreach ( $toBuild as $build ){
			
			sort($buildPatterns[$build]);
			$buildFilename = $buildLocation.hash('md5',implode('|',$buildPatterns[$build])).".".$extension;
			$highestMtime = 0;
			
			foreach ( $buildPatterns[$build] as $filename ){
				if (filemtime($filename) > $highestMtime){
					$highestMtime = filemtime($filename);
				}	
			}
			
			$compressedBuildFilename = $buildFilename.".gz";
			
			if ( !file_exists($buildFilename) || $highestMtime > filemtime($buildFilename) ){
				
				$buildContents = "";
				
				foreach ( $buildPatterns[$build] as $filename ){					
					if ( !file_exists($filename) ){
						throw new Exception("File '".$filename."' doesn't exists");
					}					
					$buildContents .= file_get_contents($filename);					
				}
				
				// Uncompressed file for those weirdos who don't have gzip.
				$fp = fopen($buildFilename,"w+");
				fwrite($fp,$buildContents);
				fclose($fp);
				
				// Compressed file for usage on Amazon S3/CloudFront
				$fileResource = gzopen($compressedBuildFilename,'w9');				
				gzwrite($fileResource,$buildContents);
				gzclose($fileResource);
				
				self::$builds[$fileType][$build] = $buildFilename;
				
			}
			
		}
		
		ksort(self::$builds[$fileType],SORT_STRING);
		
		return true;
	}

	/**
	 * Returns the actual files for the controller in question.
	 * 
	 * @param string $fileType
	 * @param string $controller
	 * @throws Exception
	 */
	public function getFiles($fileType,$controller){
		
		if ( empty(self::$builds[$fileType]) ){
			self::createBuilds($fileType,$controller);
		}
		
		$returnValue = array();
		
		foreach( self::$pageBuilds[$fileType][$controller] as $build ){
			$returnValue[] =  self::$builds[$fileType][$build];
		}
		
		return $returnValue;
	}
	
}