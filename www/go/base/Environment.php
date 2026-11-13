<?php

namespace GO\Base;

class Environment {
	
	/**
	 * Check if we run on the command line
	 *
	 * Web requests never count as command line, also not when they come from the server itself: CLI skips admin checks.
	 */
	public function isCli(){
		
		return go()->getEnvironment()->isCli();
		
	}
	
	
}
