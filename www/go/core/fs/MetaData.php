<?php

namespace go\core\fs;

use go\core\data\Model;

class MetaData extends Model {
	
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
	public $thumbnail; // blobId of cached thumbnail image (resized origional / extracted from ID3)
	
	public $data1;
	public $data2;
	public $data3;
	public $data4;
	public $data5;
	public $data6;
	public $data7;
	public $data8; //reserved
	
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
	
	public  function extractExif($path) {
		$exif = exif_read_data($path, 'IFD0');
		echo $exif===false ? "No header data found.<br />\n" : "Image contains headers<br />\n";

		$exif = exif_read_data($path, 0, true);
		echo "test.jpg:<br />\n";
		foreach ($exif as $key => $section) {
			 foreach ($section as $name => $val) {
				  echo "$key.$name: $val<br />\n";
			 }
		}
	}
	
	public function extractID3($path) {

		$biem = \id3_get_tag($path);
		return $biem;
		$id3 = new datareader\ID3Reader(fopen($path, "rb"));
		$id3->readAllTags(); //Calling this is necesarry before others
		foreach($id3->data as $tag => $value) {
			if($tag === 'TCOP') $this->copyright = $value;
			if($tag === 'TIT2') $this->title = $value;
			if($tag === 'COMM') $this->description = $value;
			if($tag === 'TPE1') $this->author = $value;
			if($tag === 'AENC') $this->encoding = $value;
			if($tag === 'TDAT') $this->date = $value;
			if($tag === 'WXXX') $this->uri = $value;
			if($tag === 'TPE3') $this->creator = $value;
			if($tag === 'TALB') $this->{self::ALBUM} = $value;
			if($tag === 'TYER') $this->{self::YEAR} = $value;
			if($tag === 'TCON') $this->{self::GENRE} = $value;
		}
		return $this;
	}


}
