<?php
require_once('../../GO.php');
global $cms;
switch($co->file['type'])
{
	case 'contact':
		if($_SERVER['REQUEST_METHOD']=='POST')
		{
			try
			{
				require_once($GO_CONFIG->root_path.'modules/formprocessor/classes/formprocessor.class.inc.php');
				$fp = new formprocessor();
				$fp->process_form();
				$this->assign('success',true);
			}
			catch(Exception $e)
			{
				$this->assign('feedback', $e->getMessage());
			}

		}
		break;
}
?>
