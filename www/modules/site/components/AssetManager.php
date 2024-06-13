<?php
/**
 * Group-Office
 * 
 * Copyright Intermesh BV. 
 * This file is part of Group-Office. You should have received a copy of the
 * Group-Office license along with Group-Office. See the file /LICENSE.TXT
 *
 * If you have questions write an e-mail to info@intermesh.nl
 * 
 * @license AGPL/Proprietary http://www.group-office.com/LICENSE.TXT
 * @link http://www.group-office.com
 * @copyright Copyright Intermesh BV
 * @version $Id Assets.php 2012-07-25 16:51:19 mdhart $
 * @author Michael de Hart <mdehart@intermesh.nl> 
 * @package GO.sites
 */

/**
 * Asset Component is a Web application component that manages private files (called assets) and makes them accessible by Web clients.
 *
 * It achieves this goal by copying assets to a Web-accessible directory
 * and returns the corresponding URL for accessing them.
 *
 * @package GO.site.components
 * @copyright Copyright Intermesh
 * @version $Id AssetManager.php 2012-07-25 16:51:19 mdhart $ 
 * @author Michael de Hart <mdehart@intermesh.nl> 
 */

namespace GO\Site\Components;


class AssetManager
{
	/**
	 * @var boolean whether to use symbolic link to publish asset files. Defaults to false, meaning
	 * asset files are copied to public folders. Using symbolic links has the benefit that the published
	 * assets will always be consistent with the source assets. This is especially useful during development.
	 *
	 * However, there are special requirements for hosting environments in order to use symbolic links.
	 * In particular, symbolic links are supported only on Linux/Unix, and Windows Vista/2008 or greater.
	 * The latter requires PHP 5.3 or greater.
	 *
	 * Moreover, some Web servers need to be properly configured so that the linked assets are accessible
	 * to Web users. For example, for Apache Web server, the following configuration directive should be added
	 * for the Web folder:
	 * <pre>
	 * Options FollowSymLinks
	 * </pre>
	 */
	public $linkAssets=false;
	/**
	 * @var array list of directories and files which should be excluded from the publishing process.
	 * Defaults to exclude '.svn' files only. This option has no effect if {@link linkAssets} is enabled.
	 **/
	public $excludeFiles=array('.svn');
	/**
	 * @var integer the permission to be set for newly generated asset files.
	 * This value will be used by PHP chmod function.
	 * Defaults to 0666, meaning the file is read-writable by all users.
	 */
	public $newFileMode=0666;
	/**
	 * @var integer the permission to be set for newly generated asset directories.
	 * This value will be used by PHP chmod function.
	 * Defaults to 0777, meaning the directory can be read, written and executed by all users.
	 */
	public $newDirMode=0777;
	/**
	 * @var StringHelper base web accessible path for storing private files
	 */
	private $_basePath;
	/**
	 * @var StringHelper base URL for accessing the publishing directory.
	 */
	private $_baseUrl;
	/**
	 * @var array published assets
	 */
	private $_published=array();

	/**
	 * @return string the root directory storing the published asset files. Defaults to 'WebRoot/assets'.
	 */
	public function getBasePath()
	{
		if($this->_basePath===null){
			$basePath= new \GO\Base\Fs\Folder(\Site::model()->getPublicPath().'assets');					
			$basePath->create();
			
			$this->_basePath=$basePath->path();
		}
		
		return $this->_basePath;
	}


	/**
	 * @return string the base url that the published asset files can be accessed.
	 * Note, the ending slashes are stripped off. Defaults to '/AppBaseUrl/assets'.
	 */
	public function getBaseUrl()
	{
		if($this->_baseUrl===null)
		{
			$this->_baseUrl=\Site::model()->getPublicUrl().'assets';
		}
		return $this->_baseUrl;
	}

	
	/**
	 * Publishes a file or a directory.
	 * This method will copy the specified asset to a web accessible directory
	 * and return the URL for accessing the published asset.
	 * <ul>
	 * <li>If the asset is a file, its file modification time will be checked
	 * to avoid unnecessary file copying;</li>
	 * <li>If the asset is a directory, all files and subdirectories under it will
	 * be published recursively. Note, the method only checks the
	 * existence of the target directory to avoid repetitive copying.</li>
	 * </ul>
	 *
	 * @param string $path the asset (file or directory) to be published
	 * @param boolean $hashByName whether the published directory should be named as the hashed basename.
	 * If false, the name will be the hash taken from dirname of the path being published and path mtime.
	 * Defaults to false. Set true if the path being published is shared among
	 * different extensions.
	 * @return string an absolute URL to the published asset
	 * @throws Exception if the asset to be published does not exist.
	 */
	public function publish($path,$hashByName=false)
	{
		if(isset($this->_published[$path]))
			return $this->_published[$path];
		else if(($src=realpath($path))!==false)
		{
			if(is_file($src))
			{
				$dir=$this->hash($hashByName ? basename($src) : dirname($src).filemtime($src));
				$fileName=basename($src);
				$dstDir=$this->getBasePath().DIRECTORY_SEPARATOR.$dir;
				$dstFile=$dstDir.DIRECTORY_SEPARATOR.$fileName;

				if($this->linkAssets)
				{
					if(!is_file($dstFile))
					{
						if(!is_dir($dstDir))
						{
							mkdir($dstDir);
							@chmod($dstDir, $this->newDirMode);
						}
						symlink($src,$dstFile);
					}
				}
				else if(@filemtime($dstFile)<@filemtime($src))
				{
					if(!is_dir($dstDir))
					{
						mkdir($dstDir);
						@chmod($dstDir, $this->newDirMode);
					}
					copy($src,$dstFile);
					@chmod($dstFile, $this->newFileMode);
				}

				return $this->_published[$path]=$this->getBaseUrl()."/$dir/$fileName";
			}
			else if(is_dir($src))
			{
				$dir=$this->hash($hashByName ? basename($src) : $src.filemtime($src));
				$dstDir=$this->getBasePath().DIRECTORY_SEPARATOR.$dir;

				if($this->linkAssets)
				{
					if(!is_dir($dstDir))
						symlink($src,$dstDir);
				}
				else if(!is_dir($dstDir))
				{
					$dstF = new \GO\Base\Fs\Folder($dstDir);

					$folder = new \GO\Base\Fs\Folder($src);
					$folder->copy($dstF);
				}

				return $this->_published[$path]=$this->getBaseUrl().'/'.$dir;
			}
		}
		throw new \Exception('The asset "'.$path.'" to be published does not exist.');
	}

	/**
	 * Returns the published path of a file path.
	 * This method does not perform any publishing. It merely tells you
	 * if the file or directory is published, where it will go.
	 * @param string $path directory or file path being published
	 * @param boolean $hashByName whether the published directory should be named as the hashed basename.
	 * If false, the name will be the hash taken from dirname of the path being published and path mtime.
	 * Defaults to false. Set true if the path being published is shared among
	 * different extensions.
	 * @return string the published file path. False if the file or directory does not exist
	 */
	public function getPublishedPath($path,$hashByName=false)
	{
		if(($path=realpath($path))!==false)
		{
			$base=$this->getBasePath().DIRECTORY_SEPARATOR;
			if(is_file($path))
				return $base . $this->hash($hashByName ? basename($path) : dirname($path).filemtime($path)) . DIRECTORY_SEPARATOR . basename($path);
			else
				return $base . $this->hash($hashByName ? basename($path) : $path.filemtime($path));
		}
		else
			return false;
	}

	/**
	 * Returns the URL of a published file path.
	 * This method does not perform any publishing. It merely tells you
	 * if the file path is published, what the URL will be to access it.
	 * @param string $path directory or file path being published
	 * @param boolean $hashByName whether the published directory should be named as the hashed basename.
	 * If false, the name will be the hash taken from dirname of the path being published and path mtime.
	 * Defaults to false. Set true if the path being published is shared among
	 * different extensions.
	 * @return string the published URL for the file or directory. False if the file or directory does not exist.
	 */
	public function getPublishedUrl($path,$hashByName=false)
	{
		if(isset($this->_published[$path]))
			return $this->_published[$path];
		if(($path=realpath($path))!==false)
		{
			if(is_file($path))
				return $this->getBaseUrl().'/'.$this->hash($hashByName ? basename($path) : dirname($path).filemtime($path)).'/'.basename($path);
			else
				return $this->getBaseUrl().'/'.$this->hash($hashByName ? basename($path) : $path.filemtime($path));
		}
		else
			return false;
	}

	/**
	 * Generate a CRC32 hash for the directory path. Collisions are higher
	 * than MD5 but generates a much smaller hash string.
	 * @param string $path string to be hashed.
	 * @return string hashed string.
	 */
	protected function hash($path)
	{
		return sprintf('%x',crc32($path.\GO::config()->version));
	}
}
?>
