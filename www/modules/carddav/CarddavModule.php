<?php

namespace GO\Carddav;


class CarddavModule extends \GO\Base\Module{
	public function depends() {
		return array("dav","sync","addressbook");
	}

	
}
