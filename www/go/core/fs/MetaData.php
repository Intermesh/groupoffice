<?php

namespace go\core\fs;

use go\core\orm;

class MetaData extends orm\Property {
	/**
	 * @var Blob
	 */
	private $blob;
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
	public $thumbnail; // blobId of cached thumbnail image (resized original / extracted from ID3)
	
	public $data1;
	public $data2;
	public $data3;
	public $data4;
	public $data5;
	public $data6;
	public $data7;
	public $data8; //reserved
	
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
	const CAMERA = 'data1';
	const ORIENTATION = 'data2';
	const LATITUDE = 'data5';
	const LONGITUDE = 'data6';
	const COMPRESSION = 'data7';

	protected static function defineMapping() {
		return parent::defineMapping()->addTable('core_blob_metadata', 'md');
	}
	
	public function extract(Blob $blob) {
		$this->blob = $blob;
		switch($this->blob->contentType) {
			case 'image/jpeg':
				$this->extractExif();
			case 'image/png':
			case 'image/gif':
			case 'image/bmp':
			case 'image/webp':
				$this->createThumbnail();
				break;
			case 'audio/mp3':
				$this->extractID3();
			default: return false;
		}
	}
	
//	private function reader() {
//		if(!isset($this->reader)) {
//			if (!file_exists($filename) || !is_readable($filename) || ($fd = fopen($filename, 'rb')) === false) {
//            throw new \Exception('IOError: Unable to open file for reading: ' . $filename);
//			}
//		
//			if (!is_resource($fd) || !in_array(get_resource_type($fd), array('stream'))) {
//				throw new \Exception('IOError: Invalid resource type (only resources of type stream are supported)');
//			}
//			$this->reader = $fd;
//			$offset = ftell($this->reader);
//			fseek($this->reader, 0, SEEK_END);
//			$this->size = ftell($this->reader);
//			fseek($this->reader, $offset);
//		}
//		return $this->reader;
//	}
//	
	private function extractExif() {
		$path = $this->blob->path();
		try {$exif = exif_read_data($path,0,true);}
		catch (\Exception $e) { 
			return;
		}
		$camera = [];
		foreach ($exif as $key => $section) {
			foreach ($section as $name => $val) {
				switch ($name) {
					case 'OwnerName': $this->author = $val; break;
					case 'DateTime': $this->date = new \DateTime($val); break;
					case 'DateTimeOriginal': $this->date = new \DateTime($val); break;
					case 'Copyright': $this->copyright = $val; break;
					//case 'MakerNote': $this->description = $val; break;
					case 'ColorSpace': $this->encoding = $val; break;
					case 'Software': $this->creator = $val; break;
					case 'ExifImageWidth': $this->{self::WIDTH} = $val; break;
					case 'ExifImageHeight': $this->{self::HEIGHT} = $val; break;
					case 'Orientation': $this->{self::ORIENTATION} = $val; break;
					case 'Compression': $this->{self::COMPRESSION} = $val; break;
					//camera
					case 'Make': $camera['make'] = $val; break;
					case 'FNumber': $camera['f_number'] = $val; break;
					case 'MeteringMode': $camera['metering_mode'] = $val; break;
					case 'FocalLength': $camera['focal_length'] = $val; break;
					case 'ExposureProgram': $camera['exposure_program'] = $val; break;
					case 'ExposureTime': $camera['exposure_time'] = $val; break;
				}
			}
		}
		if(!empty($camera)) {
			$this->{self::CAMERA} = json_encode($camera);
		}
		return $this;
	}
	
	private function createThumbnail($maxw = 240, $maxh = 240) {
		$path = $this->blob->path();
		
		switch($this->blob->contentType) {
			case 'image/jpeg': $src = imagecreatefromjpeg($path); break;
			case 'image/png': $src = imagecreatefrompng($path); break;
			case 'image/gif': $src = imagecreatefromgif($path); break;
			case 'image/bmp': $src = imagecreatefrombmp($path); break;
			case 'image/webp': $src = imagecreatefromwebp($path); break;
		}
		list($width, $height) = getimagesize($path);
		$this->{self::WIDTH} = $width;
		$this->{self::HEIGHT} = $height;
		$ratio = $width/$height;
		if ($maxw/$maxh > $ratio) {
			$maxw = $maxh*$ratio;
		} else {
			$maxh = $maxw/$ratio;
		}
		$image = imagecreatetruecolor($maxw, $maxh);
		imagecopyresampled($image, $src, 0, 0, 0, 0, $maxw, $maxh, $width, $height);
		$dest = GO()->getDataFolder()->getPath() . '/tmp/thumb_'.$this->blob->name;
		imagejpeg($image, $dest, 60);
		$blob = Blob::fromTmp($dest);
		$blob->contentType = 'image/jpeg';
		$blob->modified = time();
		$blob->disableMetaData();
		$blob->name = 'thumb_'.$this->blob->name;
		if($blob->save()){
			$this->thumbnail = $blob->id;
		}
	}
	
	private function extractID3() {
		$path = $this->blob->path();
		$id3 = new datareader\ID3Reader(fopen($path, "rb"));
		$id3->readAllTags(); //Calling this is necesarry before others
		foreach($id3->data as $tag => $value) {
			switch($tag) {
				case 'TCOP': $this->copyright = $value; break;
				case 'TIT2': $this->title = $value; break;
				case 'COMM': $this->description = $value; break;
				case 'TPE1': $this->author = $value; break;
				case 'AENC': $this->encoding = $value; break;
				case 'TDAT': $this->date = $value; break;
				case 'WXXX': $this->uri = $value; break;
				case 'TPE3': $this->creator = $value; break;
				case 'TALB': $this->{self::ALBUM} = $value; break;
				case 'TYER': $this->{self::YEAR} = $value; break;
				case 'TCON': $this->{self::GENRE} = $value; break;
			}
		}
		return $this;
	}

	
}
