<?php

namespace GO\Tools;


class ToolsModule extends \GO\Base\Module{
	public function autoInstall() {
		return true;
	}
	public function adminModule() {
		return true;
	}
}
