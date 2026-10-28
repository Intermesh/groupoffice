<?php

namespace go\modules\community\syncfusion\filehandler;

class Syncfusion implements \GO\Files\Filehandler\FilehandlerInterface
{
	/**
	 * @var string[]
	 */
	private $supportedExtensions = ['docx', 'doc', 'dotx', 'rtf', 'txt', 'xlsx', 'xls', 'csv'];

	/**
	 * @param \GO\Files\Model\File $file
	 * @return bool
	 */
	public function isDefault(\GO\Files\Model\File $file)
	{
		return false;
	}

	/**
	 * @return string
	 */
	public function getName()
	{
		return 'Syncfusion';
	}

	/**
	 * @param \GO\Files\Model\File $file
	 * @return bool
	 */
	public function fileIsSupported(\GO\Files\Model\File $file)
	{
		return in_array(strtolower($file->extension), $this->supportedExtensions);
	}

	/**
	 * @return string
	 */
	public function getIconCls()
	{
		return 'ic-edit';
	}

	/**
	 * @param \GO\Files\Model\File $file
	 * @return string
	 */
	public function getHandler(\GO\Files\Model\File $file)
	{
		return 'go.modules.community.syncfusion.openFile('
			. $file->id . ','
			. json_encode($file->name) . ','
			. json_encode(strtolower($file->extension)) . ','
			. (int)$file->folder_id
			. ');';
	}
}
