<?php

namespace GO\Carddav;


class CarddavModule extends \GO\Base\Module{
	public function depends() {
		return array("dav","sync","addressbook");
	}

	

	public function getDocumentationURL(): ?string
	{
		return "https://www.group-office.com/docs/latest/using/connect-a-device/connect-a-device.html";
	}
}
