<?php
/**
 * Copyright Intermesh
 *
 * This file is part of Group-Office. You should have received a copy of the
 * Group-Office license along with Group-Office. See the file /LICENSE.TXT
 *
 * If you have questions write an e-mail to info@intermesh.nl
 *
 * @version $Id: ObjectTree.class.inc.php 6102 2010-11-04 15:39:10Z mschering $
 * @copyright Copyright Intermesh
 * @author Merijn Schering <mschering@intermesh.nl>
 */

namespace GO\Dav;


class ObjectTree extends \Sabre\DAV\ObjectTree{
	/**
     * Moves a file from one location to another
     *
     * @param string $sourcePath The path to the file which should be moved
     * @param string $destinationPath The full destination path, so not just the destination parent node
     * @return int
     */
    public function move($sourcePath, $destinationPath) {

			\GO::debug("ObjectTree::move($sourcePath, $destinationPath)");

			$moveable = $this->getNodeForPath($sourcePath);

			$destination = $this->getNodeForPath(dirname($destinationPath));
			$targetServerPath = $destination->getServerPath().'/'.\GO\Base\Fs\File::utf8Basename($destinationPath);

      $moveable->move($targetServerPath);
    }
}
