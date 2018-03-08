<?php
/**
 * Copyright Intermesh
 *
 * This file is part of Group-Office. You should have received a copy of the
 * Group-Office license along with Group-Office. See the file /LICENSE.TXT
 *
 * If you have questions write an e-mail to info@intermesh.nl
 *
 * @copyright Copyright Intermesh
 * @version $Id: renameconfirm.php 18042 2014-01-27 10:18:47Z mschering $
 * @author Merijn Schering <mschering@intermesh.nl>
 */
//require_once(\GO::config()->root_path."Group-Office.php");

extract($data);


$this->render('externalHeader');
?>

<p><?php echo \GO::t('oldOfficeFile','googledrive') ; ?></p>

<button onclick="self.close()"><?php echo \GO::t('cmdCancel') ; ?></button>

<button onclick="document.location.href='<?php echo $continueUrl; ?>';"><?php echo \GO::t('cmdOk') ; ?></button>

<?php
$this->render('externalFooter');

