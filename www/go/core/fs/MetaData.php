<?php

namespace go\core\fs;

class MetaData extends \go\core\orm\Property {
	
	protected $blob;
	public $blobId;
	public $title;
	public $author; // authors | publisher | artists
	public $description; // comments
	public $keywords; // (semi-column sepatate)
	public $copyright;
	public $uri;
	public $creator;
	public $date; // (picture taken / document version)
	public $encoding; // codec / color profile / pdf(GNU Ghostscript 7.05)
	
	private $data1;
	private $data2;
	private $data3;
	private $data5;
	private $data6;
	private $data7;
	private $data8; //reserved
	
	private $reader;
	private $size;
	
	//Audio
	const CODEC = 'data1'; 
	const DURATION = 'data2';
	const YEAR = 'data3';
	const GENRE = 'data4';
	const ALBUM = 'data5';
	const CHANNELS = 'data6';
	const BITRATE = 'data7';
	//Video
	const WIDTH = 'data3';
	const HEIGHT = 'data4';
	//Image
	const COLOR_PROFILE = 'data1';
	const ORIENTATION = 'data2';
	const LATITUDE = 'data5';
	const LONGITUDE = 'data6';

	public function __construct($blob) {
		$this->blob = $blob;
	}
	
	private function extractMetaData() {
		switch($this->mimeType) {
			case 'image/jpeg':
			case 'image/png':
			case 'image/gif':
				$this->extractExif();
				break;
			case 'audio/mp3':
				$this->extractID3();
		}
	}
	
	private function reader() {
		if(!isset($this->reader)) {
			if (!file_exists($filename) || !is_readable($filename) || ($fd = fopen($filename, 'rb')) === false) {
            throw new \Exception('IOError: Unable to open file for reading: ' . $filename);
			}
		
			if (!is_resource($fd) || !in_array(get_resource_type($fd), array('stream'))) {
				throw new \Exception('IOError: Invalid resource type (only resources of type stream are supported)');
			}
			$this->reader = $fd;
			$offset = ftell($this->reader);
			fseek($this->reader, 0, SEEK_END);
			$this->size = ftell($this->reader);
			fseek($this->reader, $offset);
		}
		return $this->reader;
	}
	
	private function extractExif() {
		
	}
	
	public function extractID3($path) {

		$id3 = new Id3TagsReader(fopen($path, "rb"));
		$id3->readAllTags(); //Calling this is necesarry before others
		return $id3->getId3Array();
		$this->copyright = $tag['copyright'];
		$this->title = $tag['title'];
		$this->author = $tag['artist'];
		$this->{self::ALBUM} = $tag['album'];
		$this->{self::YEAR} = $tag['year'];
		$this->{self::GENRE} = $tag['genre'];
	}

	
}