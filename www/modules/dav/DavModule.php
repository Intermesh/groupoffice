<?php

namespace GO\Dav;


class DavModule extends \GO\Base\Module{
	public function autoInstall()
	{
		return true;
	}

	public function getCategory(): string
	{
		return go()->t("Files", $this->getPackage(), $this->getName());
	}

	public function getDocumentationURL(): ?string
	{
		return "https://www.group-office.com/docs/latest/using/webdav/webdav.html";
	}
}
