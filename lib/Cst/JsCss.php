<?php 
require_once CST_DIR.'/lib/closurecompiler.php';

	/**
	 * handles the JavaScript and CSS
	 * combining and usage of Google's
	 * Closure Compiler
	 *
	 * @author Iain Cambridge
	 * @package CDN Sync Tool
	 */

class Cst_JsCss {
	
	public static function getTemplateName(){
		
		if ( is_single() ){
			$name = "single";
		} elseif ( is_home() ){
			$name = "home";
		} elseif ( is_category() ){
			$name = "category";
		} elseif ( is_page() ){
			$name = "page";
		} elseif ( is_search() ){
			$name = "search";
		} elseif ( is_home() || is_front_page() ){
			$name = "index";
		} elseif ( is_404() ){
			$name = "404";
		} elseif ( is_archive() ){
			$name = "archive";
		} elseif ( is_attachment() ){
			$name = "attachment";
		}
		
		return $name;
	}
	

	public static function doCombine( $content , $fileType ){
		
		global $wpdb;
				
		Cst_Debug::addLog("Starting consolidation");
		
		if ( $fileType == "js" ){
			preg_match_all('~<script.*(type="["\']text/javascript["\'].*)?src=["\'](.*)["\'].*(type=["\']text/javascript["\'].*)?></script>~iU',$content,$matches);
			$files = $matches[2];
		} else {
			preg_match_all('~<link.*href=["\'](.*)["\'].*rel=["\']stylesheet["\'].*/>~iUs',$content,$matchesOne);
			preg_match_all('~<link.*rel=["\']stylesheet["\'].*href=["\'](.*)["\'].*/>~iUs',$content,$matchesTwo);
			$files = array();
			$matches = array(0 => array());
			if ( isset($matchesOne[1]) ){
				foreach( $matchesOnes[1] as $key => $match ){
					Cst_Debug::addLog($key.':'.$match);
				}				
				$matches[0] = array_merge($matches[0],$matchesOne[0]);
				$files = array_merge($files,$matchesOne[1]);
			}
			
			if ( isset($matchesTwo[1]) ){
				foreach( $matchesTwo[1] as $key => $match ){
					Cst_Debug::addLog($key.':'.$match);
				}
				$matches[0] = array_merge($matches[0],$matchesTwo[0]);
				$files = array_merge($files,$matchesTwo[1]);
			}
			
			
		}
			
	
		$filesContent = "";
		$filesHashes = "";
		$filesConfig = get_option("cst_files");	
		$cdn = get_option("cst_cdn");
		
	//	Cst_Debug::addLog("Files found are : ".var_export($files,true));
		foreach ( $files as $i => $file ){
			
			$urlRegex = "~^".get_option("ossdl_off_cdn_url")."/(.*\.(css|js))(\?ver=.*)?$~isU";
			
			if ( !preg_match($urlRegex, $file,$match) && $filesConfig["external"] == "yes" ){
				
				// TODO check if include external files in enabled.			
				$filesContent .= file_get_contents($file);
				
			} else {
				Cst_Debug::addLog("Match file is : ".$match[1]);
				$fileLocation = ABSPATH.str_ireplace(get_option("ossdl_off_cdn_url").'/', '', $match[1]);
				Cst_Debug::addLog("File location : ". $fileLocation );
				if ( !is_readable($fileLocation) ){
					Cst_Debug::addLog("File '".$fileLocation."' doesn't exist");
					// Ignore this non existant file.
					// - May cause issues later on.
					continue;
				}
				
				if ( in_array($file, explode("\n",$filesConfig["exclude_js"])) || 
					 in_array($file, explode("\n",$filesConfig["exclude_css"])) ){
					 	continue;
				}
				
				$rawContent = file_get_contents($fileLocation);
				
				if ( $fileType == "css" ){
					$dirLocation = dirname($match[1]);
					$rawContent = preg_replace("~url\([\'\"]?(.*)[\'\"]?\)~isU", "url('/".$dirLocation."/$1')", $rawContent);
				}
				$templateName = self::getTemplateName();
				
				$wpdb->query($wpdb->prepare("INSERT INTO `".CST_TABLE_JSCSS."` (filename,template,type) VALUES (%s,%s,%s)",array($file,$templateName,$fileType)));
				
				$filesContent .= $rawContent;
				$filesHashes .= hash("md5",$fileLocation);	
				
			}
			
			$content = str_replace($matches[0][$i], "" , $content);
		}
		
		Cst_Debug::addLog("consolidated content collected");
		$filesHashes .= hash("md5",$filesContent);
		$newFile = trim($filesConfig["directory"],"/")."/".hash("md5",$filesHashes).".".$fileType;
		if ( !is_readable($newFile) ){
			
			if ( $fileType == "js" && 
				isset($filesConfig["minify_engine"]) &&
				$filesConfig["minify_engine"] == "google" ){
					
					Cst_Debug::addLog("Minifaction using Google Closure Compiler");
					
					if ( !isset($filesConfig["minify_level"]) 
					  || $filesConfig["minify_level"] == "whitespace" )	{
						$level = ClosureCompiler::LEVEL_WHITESPACE;
					} elseif ( $filesConfig["minify_level"] == "simple" ){
						$level = ClosureCompiler::LEVEL_SIMPLE;
					} elseif ( $filesConfig["minify_level"] == "advance" ){
						$level = ClosureCompiler::LEVEL_ADVANCED;
					}
					
					$closureCompiler = new ClosureCompiler();
					$closureCompiler->fetchCode($filesContent, 
											array( "output_format" => ClosureCompiler::FORMAT_TEXT,
												   "output_info" => ClosureCompiler::INFO_CODE,
												   "compilation_level" => $level ) );											
		
					$filesContent = $closureCompiler->compiledCode;
				
			}
			
			$fp = fopen(ABSPATH.$newFile, "w+");
			fwrite($fp, $filesContent);
			fclose($fp);
			if ( is_array($cdn) && isset($cdn["provider"]) && !empty($cdn["provider"]) ){
				
				Cst_Debug::addLog("Uploading consolidated file");
				require_once CST_DIR.'/lib/Cst/Sync.php';
				Cst_Sync::process($newFile, false);	
			}
		}
		/*
		if ( substr_count($_SERVER['HTTP_ACCEPT_ENCODING'], 'gzip') ){
			$newFile .= ".gz";
		}
		*/
		if ( $fileType == "js" ){
			$content .= '<script type="text/javascript" src="'.get_bloginfo("url").'/'.$newFile.'"></script>';
		} else {			
			$replace = '<link rel="stylesheet" href="'.get_bloginfo("url").'/'.$newFile.'" type="text/css" />'.PHP_EOL.'</head>';
			$content = str_ireplace("</head>", $replace, $content);
		}
		
		return $content;
	}
	
	
}