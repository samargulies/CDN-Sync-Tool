<?php
	
	/**
	 * Handles the compression of the images
	 * before syncing with CDN. 
     *
	 * @author Iain Cambridge
	 */

class Cst_Image {
	
	public static function gdCompression( $filename ){
		if ( !preg_match("~\.(jpe?g|png)$~isU",$filename) || !is_writable($filename) ){
			return;
		}
			
		if ( preg_match("~.jpe?g$~isU",$filename) ){
			$imageRes = imagecreatefromjpeg($filename);
			imagejpeg($imageRes,$filename,50);
		} else {
			$imageRes = imagecreatefrompng($filename);
			imagepng($imageRes,$filename,7);
		}
	}
	
	public static function smushIt( $file ){
					
		require_once CST_DIR."/lib/smushit.php";
		
		if ( !preg_match("~\.(jpe?g|png)$~isU",$file) || !is_writable($file) ){
			return;
		}
		
		$smushit = new SmushIt($file);
		
		if ( !$smushit->savings ){
			return;
		}
		
		$tempFile = tempnam('/tmp', 'cst');
		$fp = fopen($tempFile, "w+");
		$ch = curl_init($smushit->compressedUrl);
		curl_setopt($ch, CURLOPT_FILE, $fp);
		curl_exec($ch);
		curl_close($ch);
		
		if ( is_readable($tempFile) && filesize($tempFile) == $smushit->compressedSize){
				copy($tempFile,$file);
		}
		
		unlink($tempFile);
		fclose($fp);
	
	}
}