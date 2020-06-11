<?php
/**
 * Copyright Intermesh
 *
 * This file is part of Group-Office. You should have received a copy of the
 * Group-Office license along with Group-Office. See the file /LICENSE.TXT
 *
 * If you have questions write an e-mail to info@intermesh.nl
 *
 * @version $Id: Shared_Directory.class.inc.php 7752 2011-07-26 13:48:43Z mschering $
 * @copyright Copyright Intermesh
 * @author Merijn Schering <mschering@intermesh.nl>
 */

namespace GO\Dav\Fs;
use GO;
use Sabre;

class RootDirectory extends Sabre\DAV\FS\Directory{

	public function __construct($path="") {
		parent::__construct(GO::config()->file_storage_path);
	}
	public function getName() {
		return "root";
	}	

	/**
	 * Returns an array with all the child nodes
	 *
	 * @return Sabre\DAV\INode[]
	 */
	public function getChildren() {
		
		$children = array();
		$children[] = new Directory('users/' . GO::user()->username);
		$children[] = new SharedDirectory();
	
		
		if(GO::modules()->addressbook && is_dir(GO::config()->file_storage_path . 'addressbook'))
			$children[] = new Directory('addressbook');

		if(GO::modules()->projects2)
			$children[] = new Directory('projects2');
		
		if(GO::modules()->tickets)
			$children[] = new Directory('tickets');
		
		if(GO::modules()->billing)
			$children[] = new Directory('billing');
		
		return $children;
	}
	
	/**
     * Returns a specific child node, referenced by its name 
     * 
     * @param StringHelper $name 
     * @throws Sabre\DAV\Exception\NotFound
     * @return Sabre\DAV\INode 
     */
    public function getChild($name) {
			
			switch($name){
				case GO::user()->username:
					return new Directory('users/' . GO::user()->username);
					break;
				
				case 'Shared':
						return new SharedDirectory();
					break;
				case 'tickets':
					if(GO::modules()->tickets)
						return new Directory('tickets');
					break;
					
				case 'billing':
					if(GO::modules()->billing)
						return new Directory('billing');
					break;
					
				case 'projects2':
					if(GO::modules()->projects2)
						return new Directory('projects2');
					break;
					
				case 'addressbook':
					if(GO::modules()->addressbook)
						return new Directory('addressbook');
					break;
			}
			
			throw new Sabre\DAV\Exception\NotFound("$name not found in the root");
		}

	/**
	 * Creates a new file in the directory
	 *
	 * data is a readable stream resource
	 *
	 * @param StringHelper $name Name of the file
	 * @param resource $data Initial payload
	 * @return void
	 */
	public function createFile($name, $data = null) {

		throw new Sabre\DAV\Exception\Forbidden();
	}

	/**
	 * Creates a new subdirectory
	 *
	 * @param StringHelper $name
	 * @return void
	 */
	public function createDirectory($name) {

		throw new Sabre\DAV\Exception\Forbidden();
	}

	/**
	 * Deletes all files in this directory, and then itself
	 *
	 * @return void
	 */
	public function delete() {

		throw new Sabre\DAV\Exception\Forbidden();
	}

	/**
	 * Returns available diskspace information
	 *
	 * @return array
	 */
	public function getQuotaInfo() {
		$user = go()->getAuthState()->getUser();
		$free = $user->getStorageFreeSpace();
		
		return array(
				$user->getStorageQuota() - $free,
				$free
		);		
	}

	/**
	 * Returns the last modification time, as a unix timestamp
	 *
	 * @return int
	 */
	public function getLastModified() {

		return filemtime(GO::config()->file_storage_path);
	}

}
