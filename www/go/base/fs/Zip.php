<?php

/**
 * Copyright Intermesh BV
 * 
 * This file is part of Group-Office. You should have received a copy of the
 * Group-Office license along with Group-Office. See the file /LICENSE.TXT
 * 
 * If you have questions write an e-mail to info@intermesh.nl
 * 
 * @package GO.base.fs
 * @version $Id: Zip.php 18000 2014-08-18 13:31:23Z wilmar1980 $
 * @author Merijn Schering <mschering@intermesh.nl>
 * @copyright Copyright Intermesh BV.
 */

/**
 * Static function to create a ZIP archive
 * 
 * @package GO.base.fs
 * @version $Id: Zip.php 18000 2014-08-18 13:31:23Z wilmar1980 $
 * @author Merijn Schering <mschering@intermesh.nl>
 * @copyright Copyright Intermesh BV.
 */


namespace GO\Base\Fs;


class Zip {

	/**
	 * Create a ZIP archive encoded in CP850 so that Windows will understand
	 * foreign characters
	 * 
	 * @param File $archiveFile
	 * @param Folder $workingFolder
	 * @param Base[] $sources
	 * @param boolean $utf8 Set to true to use UTF8 encoding. This is not supported by Windows explorer.
	 * @throws Exception
	 */
	public static function create(File $archiveFile, Folder $workingFolder, $sources, $utf8=false) {
	
		if (class_exists("\ZipArchive") && !$utf8) {
		
			\GO::debug("Using PHP ZipArchive");
			$zip = new \ZipArchive();
			$zip->open($archiveFile->path(), \ZipArchive::CREATE);
			for ($i = 0; $i < count($sources); $i++) {
				if ($sources[$i]->isFolder()) {
					self::_zipDir($sources[$i], $zip, str_replace($workingFolder->path() . '/', '', $sources[$i]->path()) . '/');
				} else {
					$name = str_replace($workingFolder->path() . '/', '', $sources[$i]->path());
				//$name = @iconv('UTF-8', 'CP850//TRANSLIT//IGNORE', $name);

					\GO::debug("Add file: ".$sources[$i]->path());
					$zip->addFile($sources[$i]->path(), $name);
				}
			}
			
			
			if(!$zip->close() || !$archiveFile->exists()){
				throw new \Exception($zip->getStatusString());
			}else
			{
				return true;
			}
		} else {
			
			\GO::debug("Using zip exec");

			chdir($workingFolder->path());

			$cmdSources = array();
			for ($i = 0; $i < count($sources); $i++) {
				$cmdSources[$i] = escapeshellarg(str_replace($workingFolder->path() . '/', '', $sources[$i]->path()));
			}

			$cmd = \GO::config()->cmd_zip . ' -r ' . escapeshellarg($archiveFile->path()) . ' ' . implode(' ', $cmdSources);

			exec($cmd, $output, $ret);

			if ($ret!=0 || !$archiveFile->exists()) {
				throw new \Exception('Command failed: ' . $cmd . "<br /><br />" . implode("<br />", $output));
			}
			
			return true;
		}
	}

	private static function _zipDir(Folder $dir, \ZipArchive $zip, $relative_path) {
		
		$items = $dir->ls();
		if(count($items)){
			foreach($items as $item){
				if ($item->isFile()) {
					$name = $relative_path . $item->name();
					//$name = @iconv('UTF-8', 'CP850//TRANSLIT//IGNORE', $name);					
					
					\GO::debug("Add file: ".$name);
					
					$zip->addFile($dir->path().'/'.$item->name(), $name);
				} else{
					self::_zipDir($item, $zip, $relative_path . $item->name() . '/');
				}
			}
		}  else {
			\GO::debug("Add empty dir: ".$relative_path);
			if(!$zip->addEmptyDir(rtrim($relative_path,'/')))
				throw new \Exception("Could not add emty directory ".$relative_path);
							
		}
		
	}

}
