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
	
		$oldContent = $content ;
		
		global $wpdb;
				
		Cst_Debug::addLog("Starting consolidation");
		
		if ( $fileType == "js" ){
			preg_match_all('~<script.*(type="["\']text/javascript["\'].*)?src=["\'](.*)["\'].*(type=["\']text/javascript["\'].*)?></script>~iU',$content,$matches);
			$files = $matches[2];
		} else {
			preg_match_all('~<link.*rel=[""\']stylesheet["\'].*href=["\'](.*)["\'].*(?!rel\=["\'].*["\']).*(/>|</link>)~isU',$content,$matchesOne);
			preg_match_all('~<link.*(?!rel\=["\'].*["\']).*href=["\'](.*)["\'].*rel=[""\']stylesheet["\'].*(/>|</link>)>~isU',$content,$matchesTwo);
			$files = array();
			$matches = array(0 => array());
			if ( isset($matchesOne[1]) ){
				foreach( $matchesOne[1] as $key => $match ){
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
		
		Cst_Debug::addLog( "Files Found ".sizeof($files) );
		
		for ( $i = 0; $i < sizeof($files); $i++){
			$file = $files[$i];
			
			$urlRegex = "~^".get_bloginfo("url")."/(.*\.(css|js))(\?.*)?$~isU";
			
			if ( (!preg_match($urlRegex,$file,$match) && !isset($match[1]) ) && (preg_match("~^https?://~isU",$file )) ){
			
				if ( $filesConfig["external"] == "no"){
					Cst_Debug::addLog("File '".$file."' is external while external is not to be combined");
					continue;
				}
					
				$filesContent .= file_get_contents($file);
				
			} else {
				
				if ( isset($match[1]) ){
					Cst_Debug::addLog("Match file is : ".$file);
					
					$fileLocation = ABSPATH.str_ireplace(get_option("ossdl_off_cdn_url").'/', '', $match[1]);
				} else {
					$fileLocation = $file;
				}
				
				Cst_Debug::addLog("File location : ". $fileLocation );
				
				if ( !is_readable($fileLocation) ){
					Cst_Debug::addLog("File '".$fileLocation."' doesn't exist");
					// Ignore this non existant file.
					// - May cause issues later on.
					continue;
				}
				
				if ( in_array($file, explode("\n",$filesConfig["exclude_js"])) || 
					 in_array($file, explode("\n",$filesConfig["exclude_css"])) ){
						Cst_Debug::addLog("File '".$fileLocation."' is in exclude list");
					 	continue;
				}
				
				Cst_Debug::addLog("Remove '".$matches[0][$i]."'");
				$content = str_replace($matches[0][$i], "" , $content);
				$rawContent = file_get_contents($fileLocation);
				
				if ( $fileType == "css" ){
					
					$dirLocation = str_ireplace(ABSPATH, '' , dirname($fileLocation));
					$urlMatches = array();
					
					preg_match_all("~url\((.*)\)~isU", $rawContent,$urlMatches[0]);

					foreach ( $urlMatches as $singleUrlMatches ){
						for ( $urlCount = 0; $urlCount < sizeof($singleUrlMatches[0]); $urlCount++ ){
														
							Cst_Debug::addLog("Quote ".$urlCount." : ".$singleUrlMatches[0][$urlCount].",url : ".$singleUrlMatches[1][$urlCount]);
							if ( preg_match("~^http[s]?://|data:~i",$singleUrlMatches[1][$urlCount]) ){
								continue;
							}
							$newUrl = get_option("ossdl_off_cdn_url").'/'.$dirLocation.'/'.trim($singleUrlMatches[1][$urlCount],"'\"");
							Cst_Debug::addLog("URL : ".$newUrl);
							$rawContent = str_replace($singleUrlMatches[0][$urlCount], "url('".$newUrl."')", $rawContent);						
						}
					}
				}
				$templateName = self::getTemplateName();
				
				$filesContent .= $rawContent;
				$filesHashes .= hash("md5",$fileLocation);	
				
			}
		}
		
		if ( empty($filesContent) ){
			return $content;
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
					// 
					$filesContent = $closureCompiler->compiledCode;
				
			}
			
			$fp = fopen(ABSPATH.$newFile, "w+");
			fwrite($fp, $filesContent);
			fclose($fp);
			
			if ( is_array($cdn) && isset($cdn["provider"]) && !empty($cdn["provider"]) ){				
				Cst_Debug::addLog("Uploading consolidated file");
				require_once CST_DIR.'/lib/Cst/Sync.php';
				if (!Cst_Sync::process($newFile, false)){
					return $oldContent;
				} 
			}
		}
		
		
		if ( $fileType == "js" ){
			
			if ($filesConfig['location'] == "body"){		
				$replace = '<script type="text/javascript" src="'.get_option("ossdl_off_cdn_url").'/'.$newFile.'"></script></body>';		
				$content = preg_replace("~</body>~iU", $replace, $content);
			} else {			
				$replace = '<head><script type="text/javascript" src="'.get_option("ossdl_off_cdn_url").'/'.$newFile.'"></script>';
				$content = preg_replace("~<head.*>~iU", $replace, $content);
			}
			
		} else {			
			$replace = '<head><link rel="stylesheet" href="'.get_option("ossdl_off_cdn_url").'/'.$newFile.'" type="text/css" />';
			$content = preg_replace("~<head.*>~iU", $replace, $content);
		}
		
		return $content;
	}
	
	
}