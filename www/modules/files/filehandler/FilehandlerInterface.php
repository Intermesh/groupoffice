<?php

namespace GO\Files\Filehandler;


interface FilehandlerInterface{
	//public function supportedExtensions();
	
	/**
	 * @return StringHelper Name of the handler
	 */
	public function getName();
	
	/**
	 * Return true if it's the default handler for a file.
	 * 
	 * @param \GO\Files\Model\File $file
	 * @return boolean
	 */
	public function isDefault(\GO\Files\Model\File $file);
	
	/**
	 * Check if the file is supported by this handler
	 * 
	 * @param \GO\Files\Model\File $file
	 * @return boolean
	 */
	public function fileIsSupported(\GO\Files\Model\File $file);
	
	/**
	 * Return javascript that will be eval'd by the view to open a file.
	 * 
	 * @param \GO\Files\Model\File $file
	 * @return StringHelper
	 */
	public function getHandler(\GO\Files\Model\File $file);
}
