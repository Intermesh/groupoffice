<?php
/** 
 * Copyright Intermesh
 * 
 * This file is part of Group-Office. You should have received a copy of the
 * Group-Office license along with Group-Office. See the file /LICENSE.TXT
 * 
 * If you have questions write an e-mail to info@intermesh.nl
 * 
 * @version $Id: filesystem.class.inc 7752 2011-07-26 13:48:43Z mschering $
 * @copyright Copyright Intermesh
 * @author Merijn Schering <mschering@intermesh.nl>
 */

/** 
 * This class contains functions for filesytem operations
 * 
 * @copyright Copyright Intermesh
 * @version $Id: filesystem.class.inc 7752 2011-07-26 13:48:43Z mschering $
 * @author Merijn Schering <mschering@intermesh.nl>
 * @package go.utils
 *
 * @uses db
 */

class filesystem extends db
{
	/**
   * The results of a search operation are stored in this var
   *
   * @var     Array
   * @access  private
   */
	var $search_results = array();

	/**
   * Holds an error message if one occured.
   *
   * @var     mixed
   * @access  private
   */
	var $action_result = false;
	
	 /**
	 * Creates a directory recursively
	 *
	 * @param	StringHelper $path
	 * @access public
	 * @return bool True on success
	 */
	
	function mkdir_recursive($path) {
	  return File::mkdir($path);
	}


	/**
   * Get the users quota of the local filesystem
   *
   * @param StringHelper $username The OS system username
   *
   * @access public
   * @return Array with keys used and total size on disk.
   */
	function get_quota($username=null)
	{
		global $GO_CONFIG;

		if(!empty($GLOBALS['GO_CONFIG']->cmd_quota))
		{
			if(!isset($username))
			{
				$username = str_replace(strstr($_SESSION['GO_SESSION']['username'], "@"), "", $_SESSION['GO_SESSION']['username']);
			}
			exec(escapeshellcmd($GLOBALS['GO_CONFIG']->cmd_sudo.' '.$GLOBALS['GO_CONFIG']->cmd_quota.' '.$username), $quota_out);
			if(isset($quota_out[0]))
			{
				foreach($quota_out as $line)
				{
					if (ereg(")*none", $line))
					{
						return false;
					}else
					{
						while(strpos($line, "  "))
						{
							$line = ereg_replace("  "," ", $line);
						}

						$numbers = explode(" ", $line);
						foreach($numbers as $number)
						{
							if(is_numeric($number))
							{
								if(!isset($arr['used']))
								{
									$arr['used'] = $number*1024;
								}else {
									$arr['total'] = $number*1024;
									return $arr;
								}
							}
						}
					}
				}
			}
		}
		return false;
	}


	/**
   * Check if a path is a subdirectory of another path.
   *
   * @param StringHelper $sub_path The subdirectory path to check
   * @param StringHelper $parent_path The parent path
   *
   * @access public
   * @return bool 
   */
	function is_sub_dir($sub_path, $parent_path)
	{
		$parent_path = $this->check_trailing_slash($parent_path);
		$sub_path =  $this->check_trailing_slash($sub_path);

		if(strpos($sub_path, $parent_path)===0)
		{
			return true;
		}else
		{
			return false;
		}
	}
	
	/**
	 * Make sure path ends with a slash
	 *
	 * @param unknown_type $path
	 */
	function check_trailing_slash($path)
	{
		if(substr($path,-1,1)!='/')
		{
			$path.='/';
		}
		return $path;
	}
	
	/**
	 * Calculate the size of a folder
	 *
	 * @param StringHelper $path
	 * @return int The size in bytes
	 */

	function size($path)
	{
		if (is_dir($path))
		{
			$size = 0;
			$children = $this->get_folders($path);
			while ($child = array_shift($children))
			{
				$size += $this->size($child['path']);
			}

			$files = $this->get_files($path);
			while ($file = array_shift($files))
			{
				$size += $file['size'];
			}
			return $size;
		}else
		{
			return filesize($path);
		}
	}
	
	/**
	 * Move a file or folder
	 *
	 * @param StringHelper $source_path
	 * @param StringHelper $destination_path
	 * @return bool true on success
	 */

	function move($source_path, $destination_path, $uploaded_file=false)
	{
		if(empty($source_path) || empty($destination_path)){
			throw new Exception('Empty arguments in move function');
		}
		$source_path = dirname($source_path).'/'.utf8_basename($source_path);
		$destination_path = dirname($destination_path).'/'.utf8_basename($destination_path);


		global $GO_CONFIG, $GO_MODULES;
		
		if (strpos($destination_path, $source_path.'/')===0)
		{
			throw new Exception('The destination is located inside the source directory');
		}elseif($source_path == $destination_path)
		{
			return true;
		}else
		{
			if (is_dir($source_path))
			{
				if (!File::mkdir($destination_path))
				{
					throw new Exception('Failed to create direcory');					
				}	
				
				$files = $this->get_files($source_path, true);
				while ($file = array_shift($files))
				{
					$this->move($file['path'], $destination_path.'/'.$file['name']);				
				}
				$children = $this->get_folders($source_path, true);
				while ($child = array_shift($children))
				{
					$this->move($child['path'], $destination_path.'/'.$child['name']);
				}
				
				if(!rmdir($source_path)){
					throw new Exception('Failed to rmdir');
				}
				return true;
			}else
			{
				if($uploaded_file)
				{
					if(!move_uploaded_file($source_path, $destination_path))
					{
						throw new Exception('Failed to move uploaded file');
					}
					return true;
				}	else
				{

					if(!rename($source_path, $destination_path)){
						throw new Exception('Failed to rename file');
					}
					return true;

					//rename fails when moving accross partitions
					/*if(isset($_SESSION['GO_SESSION']['username']) && strstr($source_path, $GLOBALS['GO_CONFIG']->file_storage_path.'users/'.$_SESSION['GO_SESSION']['username'].'/') &&
					strstr($destination_path, $GLOBALS['GO_CONFIG']->file_storage_path.'users/'.$_SESSION['GO_SESSION']['username'].'/')
					)
					{
						if(!rename($source_path, $destination_path)){
							throw new Exception('Failed to rename file');
						}
						return true;
					}else
					{
						if($this->copy($source_path, $destination_path)){
							if(!$this->delete($source_path)){
								throw new Exception('Failed to delete source path');
							}
							return true;
						}else
						{
							throw new Exception('Failed to create file');
						}
					}*/
				}
			}
		}
	}
	
	
	
	/**
	 * Copy a file or folder
	 *
	 * @param StringHelper $source_path
	 * @param StringHelper $destination_path
	 * @return true on success
	 */

	function copy($source_path, $destination_path)
	{
		$source_path = dirname($source_path).'/'.utf8_basename($source_path);
		$destination_path = dirname($destination_path).'/'.utf8_basename($destination_path);
		
		

		global $GO_CONFIG;
		
		if($source_path == $destination_path)
		{
			return true;
		}elseif (strpos($destination_path, $source_path.'/') === 0)
		{
			throw new Exception("Destination path is inside the source path");
		}else
		{
			if (is_dir($source_path))
			{
				if (!file_exists($destination_path))
				{
					if (!mkdir($destination_path, $GLOBALS['GO_CONFIG']->folder_create_mode))
					{
						throw new Exception('Failed to create direcory');
					}
				}
				$files = $this->get_files($source_path);
				while ($file = array_shift($files))
				{
					$this->copy($file['path'], $destination_path.'/'.$file['name']);
				}
				$children = $this->get_folders($source_path);
				while ($child = array_shift($children))
				{
					$this->copy($child['path'], $destination_path.'/'.$child['name']);
				}
				return true;
			}else
			{
				if(!copy($source_path, $destination_path))
				{
					throw new Exception('Copy failed');
				}
				chmod($destination_path, $GLOBALS['GO_CONFIG']->file_create_mode);
				
				return true;
			}
		}
	}
	
	/**
	 * Get the diskspace used by a folder
	 *
	 * @param StringHelper $path
	 * @return int Usage in bytes
	 */

	function get_usedspace($path)
	{
		global $GO_CONFIG;
		if ( strstr( $path, $GLOBALS['GO_CONFIG']->file_storage_path.'users/' ) )
		{
			$dest = substr( $path, strlen( $GLOBALS['GO_CONFIG']->file_storage_path.'users/' ) );
			$dest = substr( $dest, 0, strpos( $dest, "/" ) );
			//echo "du ".$GLOBALS['GO_CONFIG']->file_storage_path.'users/'.$dest." -s";
			exec("du ".$GLOBALS['GO_CONFIG']->file_storage_path.'users/'.$dest." -s", $retval );

			list($usedspace) = sscanf( $retval[0], "%d");
			return $usedspace*1024;

		}
		return false;
	}
	
	/**
	 * Delete a file or folder
	 *
	 * @param String $path
	 * @return bool true on success
	 */
	function delete($path)
	{
		$path = dirname($path).'/'.utf8_basename($path);

		global $GO_SECURITY, $GO_MODULES;

		if (is_dir($path))
		{
			
			$children = $this->get_folders($path,true);
			
			while ($child = array_shift($children))
			{
				if (!$this->delete($child['path']))
				{
					return false;
				}
			}

			$files = $this->get_files($path,true);
			while ($file = array_shift($files))
			{
				if (!$this->delete($file['path']))
				{
					return false;
				}
			}
			if ($this->has_write_permission($GLOBALS['GO_SECURITY']->user_id, $path))
			{
				return rmdir($path);
			}else
			{
				return false;
			}
		}elseif(file_exists($path))
		{	
				
			if ($this->has_write_permission($GLOBALS['GO_SECURITY']->user_id, $path))
			{
				return unlink($path);
			}else
			{
				return false;
			}
		}
	}
	
	
	/**
	 * Get the parent path of a file or folder
	 *
	 * @param StringHelper $path
	 * @return mixed parent path or false
	 */

	function get_parent_path($path)
	{
		$path = dirname($path).'/'.utf8_basename($path);

		$last_folder_pos = strrpos($path, '/');
		if (is_integer($last_folder_pos))
		{
			if ($last_folder_pos === 0)
			{
				return '/';
			}else
			{
				return substr($path, 0, $last_folder_pos);
			}
		}else
		{
			return false;
		}
	}

	/**
	 * Get an array of folders
	 *
	 * @param StringHelper $path
	 * @param bool $gethidden true if you want hidden files (eg. .svn)
	 * @return array with folders (path,name, mtime,size,type)
	 */
	function get_folders($path, $gethidden=false)
	{
		$path = dirname($path).'/'.utf8_basename($path).'/';

		global $GO_CONFIG;

		$folders = array();
		if($dir = @opendir($path))
		{
			while($item=readdir($dir))
			{
				$folder_path = $path.$item;
				if (is_dir($folder_path) && $item != "." && $item != ".." &&
				($gethidden || !(strpos($item,".") === 0) ))
				{
					$folder['path'] = $folder_path;
					$folder['name'] = utf8_basename($folder_path);
					$folder['mtime'] = filemtime($folder_path);
					$folder['size'] = filesize($folder_path);
					$folder['type'] = 'folder';
					$folders[] = $folder;
				}
			}
			closedir($dir);
		}
		return $folders;
	}

	#returns all subfolders of a folder sorted based on the result of a function
	#passed that is performed on the pathname. (For example filesize();)
	
	/**
	 * returns all subfolders of a folder sorted based on the result of a function
	 * passed that is performed on the pathname. (For example filesize();)
	 *
	 * @param StringHelper $path
	 * @param sort_file A php function performed on the path used for sorting. eg. filesize, basename
	 * @param sort_direction ASC or DESC
	 * @param bool $gethidden true if you want hidden files (eg. .svn)
	 * @return array with folders (path,name, mtime,size,type)
	 */
	function get_folders_sorted($path,$sort_field='utf8_basename',$sort_direction='ASC', $gethidden=false)
	{
		$path = dirname($path).'/'.utf8_basename($path).'/';

		global $GO_CONFIG;
		
		$folders=array();

		if(strstr($path, $GLOBALS['GO_CONFIG']->root_path))
		{
			$url = str_replace($GLOBALS['GO_CONFIG']->root_path, $GLOBALS['GO_CONFIG']->host, $path);
		}
		$sort_field = function_exists($sort_field) ? $sort_field : 'utf8_basename';
		if (is_dir($path))
		{
			$sorted_list = array();

			if(@$dir = opendir($path))
			{
				while($item=readdir($dir))
				{
					$folder_path = $path.$item;

					if (is_dir($folder_path) && $item != "." && $item != ".." &&
					($gethidden || !(strpos($item,".")===0) ))
					{
						$key_id = 0;
						$first_key = strtolower($sort_field($folder_path));
						$key = $first_key;
						while (array_key_exists($key, $sorted_list))
						{
							$key = $first_key.'_'.$key_id;
							$key_id++;
						}
						$sorted_list[$key] = $folder_path;
					}
				}
				closedir($dir);

				if ($sort_direction == 'ASC')
				{
					ksort($sorted_list);
				}else
				{
					krsort($sorted_list);
				}

				while ($item=array_shift($sorted_list))
				{
					$folder = array();
					$folder['path'] = $item;
					$folder['name'] = utf8_basename($item);
					$folder['mtime'] = filemtime($item);
					$folder['size'] = filesize($item);
					$folder['type'] = 'folder';
					if(isset($url))
					{
						$folder['url'] = $url.$folder['name'];
					}
					$folders[] = $folder;
				}
			}
		}
		return $folders;
	}

	/**
	 * Get an array of files in a folder
	 *
	 * @param StringHelper $path
	 * @param bool $gethidden
	 * @return unknown
	 */
	function get_files($path, $gethidden=false)
	{
		$path = dirname($path).'/'.utf8_basename($path).'/';

		global $GO_CONFIG;

		$files = array();
		if($dir = @opendir($path))
		{
			while($item=readdir($dir))
			{
				$file_path = $path.$item;
				if (!is_dir($file_path) && ($gethidden || !(strpos($item,".") === 0)))
				{
					$file['path'] = $file_path;
					$file['name'] = utf8_basename($file_path);
					$file['size'] = filesize($file_path);
					$file['mtime'] = filemtime($file_path);
					$file['type'] = File::get_mime($file_path);

					$files[] = $file;
				}
			}
			closedir($dir);
		}
		return $files;
	}

	/**
	 * returns all files of a folder sorted based on the result of a function
	 * passed that is performed on the pathname. (For example filesize();)
	 *
	 * @param StringHelper $path
	 * @param sort_file A php function performed on the path used for sorting. eg. filesize, basename
	 * @param sort_direction ASC or DESC
	 * @param bool $gethidden true if you want hidden files (eg. .svn)
	 * @return array with folders (path,name, mtime,size,type)
	 */
	function get_files_sorted($path,$sort_field='utf8_basename',$sort_direction='ASC', $gethidden=false)
	{
		$path = dirname($path).'/'.utf8_basename($path).'/';

		global $GO_CONFIG;
		$files = array();


		if(strstr($path, $GLOBALS['GO_CONFIG']->root_path))
		{
			$url = str_replace($GLOBALS['GO_CONFIG']->root_path, $GLOBALS['GO_CONFIG']->host, $path);
		}
		
		if($pos = strpos($sort_field, '::'))
		{
			$class = substr($sort_field,0, $pos);
			$method = substr($sort_field,$pos+2);
			$sort_function = method_exists($class, $method) ? array($class, $method) : 'utf8_basename';
		}else
		{
			$sort_function = function_exists($sort_field) ? $sort_field : 'utf8_basename';
		}
		
		
		
		if (is_dir($path))
		{
			$sorted_list = array();

			if($dir = @opendir($path))
			{
				while($item=readdir($dir))
				{
					$file = $path.$item;
					if (!is_dir($file) &&
					($gethidden || !(strpos($item,".") === 0)))
					{
						$key_id = 0;
						$first_key = strtolower(call_user_func($sort_function, $file));
						$key = $first_key;
						while (array_key_exists($key, $sorted_list))
						{
							$key = $first_key.'_'.$key_id;
							$key_id++;
						}
						$sorted_list[$key] = $file;
					}
				}
				closedir($dir);

				if ($sort_direction == 'ASC')
				{
					ksort($sorted_list);
				}else
				{
					krsort($sorted_list);
				}

				while ($item=array_shift($sorted_list))
				{
					$file = array();
					$file['path'] = $item;
					$file['name'] = utf8_basename($item);
					$file['mtime'] = filemtime($item);
					$file['size'] = filesize($item);
					$file['type'] = File::get_mime($item);

					if(isset($url))
					{
						$file['url'] = $url.$file['name'];
					}
					$files[] = $file;
				}
			}

		}
		return $files;
	}
	
	/**
	 * Returns true if the path is writable
	 *
	 * @param int $user_id
	 * @param StringHelper $path
	 * @return bool
	 */
	
	function has_write_permission($user_id, $path)
	{
		return is_writable($path);
	}
	
	/**
	 * Returns true if the path is readable
	 *
	 * @param int $user_id
	 * @param StringHelper $path
	 * @return bool
	 */
	
	function has_read_permission($user_id, $path)
	{
		return is_readable($path);
	}	
}
