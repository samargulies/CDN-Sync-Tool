<?php
	
	/**
	 * Handles the compression of the images
	 * before syncing with CDN. 
     *
	 * @author Iain Cambridge
	 */

class Cst_Image {
	
	public static function gdCompression( $fileArray ){
		
		// Use URI as it'll be the original name 
		// and use location as that's what we'll 
		// be working with.
		
		$path = wp_upload_dir();
		
		if ( !preg_match("~\.(jpe?g|png)$~isU",$fileArray['uri']) || !is_writable($fileArray['location']) ){
			Cst_Debug::addLog("Invalid filetype sent to GD Compression '".$fileArray['uri']."'");
			return $fileArray;
		}

		if ( $fileArray['overwrite'] != 'yes' ){
			$writeLocation = tempnam($path['basedir'], 'SYNCCOMPRESS');
		} else {
			$writeLocation = $fileArray['location'];
		}
		
		if ( preg_match("~.jpe?g$~isU",$fileArray['uri']) ){
			$imageRes = imagecreatefromjpeg($fileArray['location']);
			imagejpeg($imageRes,$writeLocation,
				( 100 - ($fileArray['compression_level'] - 1) * 10 )		
			);
		} else {
			$imageRes = imagecreatefrompng($fileArray['location']);
			imagepng(
				$imageRes,$writeLocation,
				($fileArray['compression_level'] - 1)
			);
		}
		
		$fileArray['location'] = $writeLocation;
		
		Cst_Debug::addLog("GD Compression successfully done on '".$fileArray['uri']."'");
		return $fileArray;
	}
	
	public static function smushIt( $fileArray ){
					
		require_once CST_DIR."/lib/smushit.php";
		
		if ( !preg_match("~\.(jpe?g|png|gif)$~isU",$fileArray['uri']) || !is_writable($fileArray['location']) ){
			Cst_Debug::addLog("Invalid filetype sent to Smush.IT Compression '".$fileArray['uri']."'");
			return $fileArray;
		}
		
	
		$path = wp_upload_dir();
		if ( $fileArray['overwrite'] != 'yes' ){
			$writeLocation = tempnam($path['basedir'], 'SYNCCOMPRESS');
		} else {
			$writeLocation = $fileArray['location'];
		}
		
		$smushit = new SmushIt($fileArray['location']);
		
		if ( !$smushit->savings ){
			return $fileArray;
		}
		
		
		$tempFile = tempnam( $path['basedir'] , 'cst');
		$fp = fopen($tempFile, "w+");
		$ch = curl_init($smushit->compressedUrl);
		curl_setopt($ch, CURLOPT_FILE, $fp);
		curl_exec($ch);
		curl_close($ch);
		
		if ( is_readable($tempFile) && filesize($tempFile) == $smushit->compressedSize){
				Cst_Debug::addLog("File smushed successfully '".$fileArray['uri']."'");
				copy($tempFile,$writeLocation);
		}
		
		unlink($tempFile);
		fclose($fp);
		
		$fileArray['location'] = $writeLocation;
		
		return $fileArray;
	
	}
}