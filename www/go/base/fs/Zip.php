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
				// Don't add what a link points to. It can be anywhere on the server.
				if ($sources[$i]->isLink()) {
					continue;
				}
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
				// "./" so names starting with "-" are not read as options
				$cmdSources[$i] = escapeshellarg('./' . str_replace($workingFolder->path() . '/', '', $sources[$i]->path()));
			}

			// -y stores links as links instead of adding what they point to
			$cmd = \GO::config()->cmd_zip . ' -r -y ' . escapeshellarg($archiveFile->path()) . ' ' . implode(' ', $cmdSources);

			exec($cmd, $output, $ret);

			if ($ret!=0 || !$archiveFile->exists()) {
				throw new \Exception('Command failed: ' . $cmd . "\n\n" . implode("\n", $output));
			}
			
			return true;
		}
	}

	/**
	 * Extract a ZIP archive into a folder.
	 *
	 * Archives come from users, so archives with entries outside the folder, like "../file" or "/file", or with
	 * symbolic links are refused. Otherwise they could make the server read or write files outside the folder.
	 *
	 * @param File $archiveFile
	 * @param Folder $destination
	 * @throws \Exception
	 */
	public static function extract(File $archiveFile, Folder $destination): void
	{
		$zip = new \ZipArchive();
		if ($zip->open($archiveFile->path()) !== true) {
			throw new \Exception("Could not open archive " . $archiveFile->name());
		}

		try {
			for ($i = 0; $i < $zip->numFiles; $i++) {
				$name = $zip->getNameIndex($i);
				if ($name === false || !self::isSafeEntryName($name) || self::isLinkEntry($zip, $i)) {
					throw new \Exception(go()->t("The archive contains links or files outside its folder and can't be extracted"));
				}
			}

			if (!$zip->extractTo($destination->path())) {
				throw new \Exception("Could not extract archive " . $archiveFile->name() . ": " . $zip->getStatusString());
			}
		} finally {
			$zip->close();
		}
	}

	private static function isSafeEntryName(string $name): bool
	{
		$name = str_replace('\\', '/', $name);
		return $name !== '' && $name[0] !== '/' && !preg_match('/^[a-z]:/i', $name) && Base::checkPathInput($name);
	}

	private static function isLinkEntry(\ZipArchive $zip, int $index): bool
	{
		if (!$zip->getExternalAttributesIndex($index, $opsys, $attr)) {
			return false;
		}
		// The upper 16 bits hold the Unix file mode
		return $opsys == \ZipArchive::OPSYS_UNIX && (($attr >> 16) & 0170000) == 0120000;
	}

	private static function _zipDir(Folder $dir, \ZipArchive $zip, $relative_path) {

		// Include hidden files like the zip command does. Office documents need _rels/.rels
		$items = $dir->ls(true);
		if(count($items)){
			foreach($items as $item){
				// Don't add what a link points to. It can be anywhere on the server.
				if ($item->isLink()) {
					continue;
				}
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
