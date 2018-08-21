<?php
namespace go\core\data\convert;

class JSON extends AbstractConverter {	
	public function from($data) {
		return json_decode($data, true);
	}

	public function to($properties) {
		return json_encode($properties, JSON_PRETTY_PRINT);
	}

	public function getFileExtension(): string {
		return "json";
	}

	public function getStart() {
		return "[\n";
	}
	
	public function getBetween() {
		return "\n,\n";
	}
	
	public function getEnd() {
		return "\n]\n";
	}
}
