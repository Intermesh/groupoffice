<?php

namespace go\core\fs\datareader;

/**
 * Read ID3Tags and thumbnails.
 *
 * @author Shubham Jain <shubham.jain.1@gmail.com>
 * @license MIT License
 */
class ID3Reader
{
    private $fileReader;
    public $data;
    private $validMp3 = TRUE;
	 static private $tags = [
		"AENC" => "Audio encryption",
		"APIC" => "Attached picture",
		"COMM" => "Comments",
		"COMR" => "Commercial frame",
		"ENCR" => "Encryption method registration",
		"EQUA" => "Equalization",
		"ETCO" => "Event timing codes",
		"GEOB" => "General encapsulated object",
		"GRID" => "Group identification registration",
		"IPLS" => "Involved people list",
		"LINK" => "Linked information",
		"MCDI" => "Music CD identifier",
		"MLLT" => "MPEG location lookup table",
		"OWNE" => "Ownership frame",
		"PRIV" => "Private frame",
		"PCNT" => "Play counter",
		"POPM" => "Popularimeter",
		"POSS" => "Position synchronisation frame",
		"RBUF" => "Recommended buffer size",
		"RVAD" => "Relative volume adjustment",
		"RVRB" => "Reverb",
		"SYLT" => "Synchronized lyric/text",
		"SYTC" => "Synchronized tempo codes",
		"TALB" => "Album/Movie/Show title",
		"TBPM" => "BPM (beats per minute)",
		"TCOM" => "Composer",
		"TCON" => "Content type",
		"TCOP" => "Copyright message",
		"TDAT" => "Date",
		"TDLY" => "Playlist delay",
		"TENC" => "Encoded by",
		"TEXT" => "Lyricist/Text writer",
		"TFLT" => "File type",
		"TIME" => "Time",
		"TIT1" => "Content group description",
		"TIT2" => "Title/songname/content description",
		"TIT3" => "Subtitle/Description refinement",
		"TKEY" => "Initial key",
		"TLAN" => "Language(s)",
		"TLEN" => "Length",
		"TMED" => "Media type",
		"TOAL" => "Original album/movie/show title",
		"TOFN" => "Original filename",
		"TOLY" => "Original lyricist(s)/text writer(s)",
		"TOPE" => "Original artist(s)/performer(s)",
		"TORY" => "Original release year",
		"TOWN" => "File owner/licensee",
		"TPE1" => "Lead performer(s)/Soloist(s)",
		"TPE2" => "Band/orchestra/accompaniment",
		"TPE3" => "Conductor/performer refinement",
		"TPE4" => "Interpreted, remixed, or otherwise modified by",
		"TPOS" => "Part of a set",
		"TPUB" => "Publisher",
		"TRCK" => "Track number/Position in set",
		"TRDA" => "Recording dates",
		"TRSN" => "Internet radio station name",
		"TRSO" => "Internet radio station owner",
		"TSIZ" => "Size",
		"TSRC" => "ISRC (international standard recording code)",
		"TSSE" => "Software/Hardware and settings used for encoding",
		"TYER" => "Year",
		"TXXX" => "User defined text information frame",
		"UFID" => "Unique file identifier",
		"USER" => "Terms of use",
		"USLT" => "Unsychronized lyric/text transcription",
		"WCOM" => "Commercial information",
		"WCOP" => "Copyright/Legal information",
		"WOAF" => "Official audio file webpage",
		"WOAR" => "Official artist/performer webpage",
		"WOAS" => "Official audio source webpage",
		"WORS" => "Official internet radio station homepage",
		"WPAY" => "Payment",
		"WPUB" => "Publishers official webpage",
		"WXXX" => "User defined URL link frame",
   ];
	 
   public function __construct($fileHandle) {
        $this->fileReader = new BinaryFileReader($fileHandle, array(
            "id3" => array(BinaryFileReader::FIXED, 3),
            "version" => array(BinaryFileReader::FIXED, 2),
            "flag" => array(BinaryFileReader::FIXED, 1),
            "sizeTag" => array(BinaryFileReader::FIXED, 4, BinaryFileReader::INT),
        ));
        $data = $this->fileReader->read();
        if( $data->id3 !== "ID3")
        {
            throw new \Exception("The MP3 file contains no valid ID3 Tags.");
            $this->validMp3 = FALSE;
        }
    }
	 
    public function readAllTags()
    {
        assert( $this->validMp3 === TRUE);
        $bytesPos = 10; //From headers
        $this->fileReader->setMap(array(
            "frameId" => array(BinaryFileReader::FIXED, 4),
            "size" => array(BinaryFileReader::FIXED, 4, BinaryFileReader::INT),
            "flag" => array(BinaryFileReader::FIXED, 2),
            "body" => array(BinaryFileReader::SIZE_OF, "size"),
        ));
        $id3Tags = self::$tags;
        while (($file_data = $this->fileReader->read())) {
            if (!in_array($file_data->frameId, array_keys($id3Tags))) {
                break;
            }
            $body = $file_data->body;
            // If frame is a text frame then we have to consider 
            // encoding as shown in spec section 4.2
            if( $file_data->frameId[0] === "T" )
            {
					// First character determines the encoding, 1 = ISO-8859-1, 0 = UTF - 16
					switch (intval(bin2hex($body[0]), 16))
					{
						case 0: //ISO-8859-1
							$body = mb_convert_encoding(substr($body, 1), 'UTF-8', 'ISO-8859-1'); 
							break;
						case 1: //UTF-16 BOM
							$body = mb_convert_encoding(substr($body, 1), 'UTF-8', 'UTF-16LE'); 
							break;
						// Convert UTF-16 to UTF-8 to compatible with current browsers
						case 2: //UTF-16BE
							$body = mb_convert_encoding(substr($body, 1), 'UTF-8', 'UTF-16BE');
							break;
						case 3: //UTF-8
							$body = substr($body, 1);
					}
            }
//            $this->id3Array[$file_data->frameId] = array(
//                "fullTagName" => $id3Tags[$file_data->frameId],
//                "position" => $bytesPos,
//                "size" => $file_data->size,
//                "body" => $body,
//            );
				$this->data[$file_data->frameId] = $body;
            $bytesPos += 4 + 4 + 2 + $file_data->size;
        }
        return $this;
    }

    public function getImage() {
        $fp = fopen('data://text/plain;base64,' . base64_encode($this->id3Array["APIC"]["body"]), 'rb'); //Create an artificial stream from Image data
        $fileReader = new BinaryFileReader($fp, array(
            //"textEncoding" => array(BinaryFileReader::FIXED, 1),
            "mimeType" => array(BinaryFileReader::NULL_TERMINATED),
            "fileName" => array(BinaryFileReader::NULL_TERMINATED),
            "contentDesc" => array(BinaryFileReader::NULL_TERMINATED),
            "binaryData" => array(BinaryFileReader::EOF_TERMINATED)
            )
        );
        $imageData = $fileReader->read();
        return array($imageData->mimeType, $imageData->binaryData);
    }
}
