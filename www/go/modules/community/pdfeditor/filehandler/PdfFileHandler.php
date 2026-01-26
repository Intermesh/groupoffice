<?php

namespace go\modules\community\pdfeditor\filehandler;

use GO\Files\Filehandler\FilehandlerInterface;

class PdfFileHandler implements FilehandlerInterface {
	public function getName()
	{
		return go()->t("Pdf editor", "pdfeditor");
	}

	public function isDefault(\GO\Files\Model\File $file)
	{
		return strtolower($file->extension) == 'pdf';
	}

	public function getIconCls() {
		return '';
	}

	public function fileIsSupported(\GO\Files\Model\File $file)
	{
		return strtolower($file->extension) == 'pdf';
	}

	public function getHandler(\GO\Files\Model\File $file)
	{
		return 'go.modules.community.pdfeditor.openPDF(' . $file->id . ');';
	}
}