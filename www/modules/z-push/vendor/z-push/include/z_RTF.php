<?php
/*
    This class contains code from rtfclass.php that was written by Markus Fischer and placed by him under
    GPLv2 License.

    =======================================NOTES FROM ORIGINAL AUTHOR====================================
                Rich Text Format - Parsing Class
                ================================

                (c) 2000 Markus Fischer
                <mfischer@josefine.ben.tuwien.ac.at>
                   http://josefine.ben.tuwien.ac.at/~mfischer/

                Latest versions of this class can always be found at
                        http://josefine.ben.tuwien.ac.at/~mfischer/developing/php/rtf/rtfclass.phps
                Testing suite is available at
                        http://josefine.ben.tuwien.ac.at/~mfischer/developing/php/rtf/

                License: GPLv2

                Specification:
                        http://msdn.microsoft.com/library/default.asp?URL=/library/specs/rtfspec.htm

                General Notes:
                ==============
                Unknown or unspupported control symbols are silently gnored

                Group stacking is still not supported :(
                        group stack logic implemented; however not really used yet
    =====================================================================================================

    It was modified by me (Andreas Brodowski) to allow compressed RTF being uncompressed by code I ported from
    Java to PHP and adapted according the needs of Z-Push.

    Currently it is being used to detect empty RTF Streams from Nokia Phones in MfE Clients

    It needs to be used by other backend writers that needs to have notes in calendar, appointment or tasks
    objects to be written to their databases since devices send them usually in RTF Format... With Zarafa
    you can write them directly to DB and Zarafa is doing the conversion job. Other Groupware systems usually
    don't have this possibility...

*/

class rtf {
    var $LZRTF_HDR_DATA = "{\\rtf1\\ansi\\mac\\deff0\\deftab720{\\fonttbl;}{\\f0\\fnil \\froman \\fswiss \\fmodern \\fscript \\fdecor MS Sans SerifSymbolArialTimes New RomanCourier{\\colortbl\\red0\\green0\\blue0\n\r\\par \\pard\\plain\\f0\\fs20\\b\\i\\u\\tab\\tx";
    var $LZRTF_HDR_LEN = 207;
    var $CRC32_TABLE = array(     0x00000000,0x77073096,0xEE0E612C,0x990951BA,0x076DC419,0x706AF48F,0xE963A535,0x9E6495A3,
                                  0x0EDB8832,0x79DCB8A4,0xE0D5E91E,0x97D2D988,0x09B64C2B,0x7EB17CBD,0xE7B82D07,0x90BF1D91,
                                  0x1DB71064,0x6AB020F2,0xF3B97148,0x84BE41DE,0x1ADAD47D,0x6DDDE4EB,0xF4D4B551,0x83D385C7,
                                  0x136C9856,0x646BA8C0,0xFD62F97A,0x8A65C9EC,0x14015C4F,0x63066CD9,0xFA0F3D63,0x8D080DF5,
                                  0x3B6E20C8,0x4C69105E,0xD56041E4,0xA2677172,0x3C03E4D1,0x4B04D447,0xD20D85FD,0xA50AB56B,
                                  0x35B5A8FA,0x42B2986C,0xDBBBC9D6,0xACBCF940,0x32D86CE3,0x45DF5C75,0xDCD60DCF,0xABD13D59,
                                  0x26D930AC,0x51DE003A,0xC8D75180,0xBFD06116,0x21B4F4B5,0x56B3C423,0xCFBA9599,0xB8BDA50F,
                                  0x2802B89E,0x5F058808,0xC60CD9B2,0xB10BE924,0x2F6F7C87,0x58684C11,0xC1611DAB,0xB6662D3D,
                                  0x76DC4190,0x01DB7106,0x98D220BC,0xEFD5102A,0x71B18589,0x06B6B51F,0x9FBFE4A5,0xE8B8D433,
                                  0x7807C9A2,0x0F00F934,0x9609A88E,0xE10E9818,0x7F6A0DBB,0x086D3D2D,0x91646C97,0xE6635C01,
                                  0x6B6B51F4,0x1C6C6162,0x856530D8,0xF262004E,0x6C0695ED,0x1B01A57B,0x8208F4C1,0xF50FC457,
                                  0x65B0D9C6,0x12B7E950,0x8BBEB8EA,0xFCB9887C,0x62DD1DDF,0x15DA2D49,0x8CD37CF3,0xFBD44C65,
                                  0x4DB26158,0x3AB551CE,0xA3BC0074,0xD4BB30E2,0x4ADFA541,0x3DD895D7,0xA4D1C46D,0xD3D6F4FB,
                                  0x4369E96A,0x346ED9FC,0xAD678846,0xDA60B8D0,0x44042D73,0x33031DE5,0xAA0A4C5F,0xDD0D7CC9,
                                  0x5005713C,0x270241AA,0xBE0B1010,0xC90C2086,0x5768B525,0x206F85B3,0xB966D409,0xCE61E49F,
                                  0x5EDEF90E,0x29D9C998,0xB0D09822,0xC7D7A8B4,0x59B33D17,0x2EB40D81,0xB7BD5C3B,0xC0BA6CAD,
                                  0xEDB88320,0x9ABFB3B6,0x03B6E20C,0x74B1D29A,0xEAD54739,0x9DD277AF,0x04DB2615,0x73DC1683,
                                  0xE3630B12,0x94643B84,0x0D6D6A3E,0x7A6A5AA8,0xE40ECF0B,0x9309FF9D,0x0A00AE27,0x7D079EB1,
                                  0xF00F9344,0x8708A3D2,0x1E01F268,0x6906C2FE,0xF762575D,0x806567CB,0x196C3671,0x6E6B06E7,
                                  0xFED41B76,0x89D32BE0,0x10DA7A5A,0x67DD4ACC,0xF9B9DF6F,0x8EBEEFF9,0x17B7BE43,0x60B08ED5,
                                  0xD6D6A3E8,0xA1D1937E,0x38D8C2C4,0x4FDFF252,0xD1BB67F1,0xA6BC5767,0x3FB506DD,0x48B2364B,
                                  0xD80D2BDA,0xAF0A1B4C,0x36034AF6,0x41047A60,0xDF60EFC3,0xA867DF55,0x316E8EEF,0x4669BE79,
                                  0xCB61B38C,0xBC66831A,0x256FD2A0,0x5268E236,0xCC0C7795,0xBB0B4703,0x220216B9,0x5505262F,
                                  0xC5BA3BBE,0xB2BD0B28,0x2BB45A92,0x5CB36A04,0xC2D7FFA7,0xB5D0CF31,0x2CD99E8B,0x5BDEAE1D,
                                  0x9B64C2B0,0xEC63F226,0x756AA39C,0x026D930A,0x9C0906A9,0xEB0E363F,0x72076785,0x05005713,
                                  0x95BF4A82,0xE2B87A14,0x7BB12BAE,0x0CB61B38,0x92D28E9B,0xE5D5BE0D,0x7CDCEFB7,0x0BDBDF21,
                                  0x86D3D2D4,0xF1D4E242,0x68DDB3F8,0x1FDA836E,0x81BE16CD,0xF6B9265B,0x6FB077E1,0x18B74777,
                                  0x88085AE6,0xFF0F6A70,0x66063BCA,0x11010B5C,0x8F659EFF,0xF862AE69,0x616BFFD3,0x166CCF45,
                                  0xA00AE278,0xD70DD2EE,0x4E048354,0x3903B3C2,0xA7672661,0xD06016F7,0x4969474D,0x3E6E77DB,
                                  0xAED16A4A,0xD9D65ADC,0x40DF0B66,0x37D83BF0,0xA9BCAE53,0xDEBB9EC5,0x47B2CF7F,0x30B5FFE9,
                                  0xBDBDF21C,0xCABAC28A,0x53B39330,0x24B4A3A6,0xBAD03605,0xCDD70693,0x54DE5729,0x23D967BF,
                                  0xB3667A2E,0xC4614AB8,0x5D681B02,0x2A6F2B94,0xB40BBE37,0xC30C8EA1,0x5A05DF1B,0x2D02EF8D,
                               );

    var $rtf;                   // rtf core stream
    var $rtf_len;               // length in characters of the stream (get performace due avoiding calling strlen everytime)
    var $err = array();         // array of error message, no entities on no error

    var $wantXML = false;       // convert to XML
    var $wantHTML = false;      // convert to HTML
    var $wantASCII = false;     // convert to HTML

                                // the only variable which should be accessed from the outside
    var $out;                   // output data stream (depends on which $wantXXXXX is set to true
    var $outstyles;             // htmlified styles (generated after parsing if wantHTML
    var $styles;                // if wantHTML, stylesheet definitions are put in here

                                // internal parser variables --------------------------------
                                // control word variables
    var $cword;                 // holds the current (or last) control word, depending on $cw
    var $cw;                    // are we currently parsing a control word ?
    var $cfirst;                // could this be the first character ? so watch out for control symbols

    var $flags = array();       // parser flags

    var $queue;                 // every character which is no sepcial char, not belongs to a control word/symbol; is generally considered being 'plain'

    var $stack = array();       // group stack

    /* keywords which don't follw the specification (used by Word '97 - 2000) */
    // not yet used
    var $control_exception = array(
        "clFitText",
        "clftsWidth(-?[0-9]+)?",
        "clNoWrap(-?[0-9]+)?",
        "clwWidth(-?[0-9]+)?",
        "tdfrmtxtBottom(-?[0-9]+)?",
        "tdfrmtxtLeft(-?[0-9]+)?",
        "tdfrmtxtRight(-?[0-9]+)?",
        "tdfrmtxtTop(-?[0-9]+)?",
        "trftsWidthA(-?[0-9]+)?",
        "trftsWidthB(-?[0-9]+)?",
        "trftsWidth(-?[0-9]+)?",
        "trwWithA(-?[0-9]+)?",
        "trwWithB(-?[0-9]+)?",
        "trwWith(-?[0-9]+)?",
        "spectspecifygen(-?[0-9]+)?",
    );

    var $charset_table = array(
        "0"     =>      "ANSI",
        "1"     =>      "Default",
        "2"     =>      "Symbol",
        "77"    =>      "Mac",
        "128"   =>      "Shift Jis",
        "129"   =>      "Hangul",
        "130"   =>      "Johab",
        "134"   =>      "GB2312",
        "136"   =>      "Big5",
        "161"   =>      "Greek",
        "162"   =>      "Turkish",
        "163"   =>      "Vietnamese",
        "177"   =>      "Hebrew",
        "178"   =>      "Arabic",
        "179"   =>      "Arabic Traditional",
        "180"   =>      "Arabic user",
        "181"   =>      "Hebrew user",
        "186"   =>      "Baltic",
        "204"   =>      "Russian",
        "222"   =>      "Thai",
        "238"   =>      "Eastern European",
        "255"   =>      "PC 437",
        "255"   =>      "OEM",
    );

    /* note: the only conversion table used */
    var $fontmodifier_table = array(
        "bold"          => "b",
        "italic"        => "i",
        "underlined"    => "u",
        "strikethru"    => "strike",
    );


    function __construct() {
        $this->rtf_len = 0;
        $this->rtf = '';

        $this->out = '';
    }

    // loadrtf - load the raw rtf data to be converted by this class
    // data = the raw rtf
    function loadrtf($data) {
        if (($this->rtf = $this->uncompress($data))) {
            $this->rtf_len = strlen($this->rtf);
        };
        if($this->rtf_len == 0) {
            debugLog("No data in stream found");
            return false;
        };
        return true;
    }

    function output($typ) {
        switch($typ) {
            case "ascii": $this->wantASCII = true; break;
            case "xml": $this->wantXML = true; break;
            case "html": $this->wantHTML = true; break;
            default: break;
        }
    }

    // uncompress - uncompress compressed rtf data
    // src = the compressed raw rtf in LZRTF format
    function uncompress($src) {
        $header = unpack("LcSize/LuSize/Lmagic/Lcrc32",substr($src,0,16));
        $in = 16;
        if ($header['cSize'] != strlen($src)-4) {
            debugLog("Stream too short");
            return false;
        }

        if ($header['crc32'] != $this->LZRTFCalcCRC32($src,16,(($header['cSize']+4))-16)) {
            debugLog("CRC MISMATCH");
            return false;
        }

        if ($header['magic'] == 0x414c454d) {                   // uncompressed RTF - return as is.
            $dest = substr($src,$in,$header['uSize']);
        } else if ($header['magic'] == 0x75465a4c) {            // compressed RTF - uncompress.
            $dst = $this->LZRTF_HDR_DATA;
            $out = $this->LZRTF_HDR_LEN;
            $oblen = $this->LZRTF_HDR_LEN + $header['uSize'];
            $flagCount = 0;
            $flags = 0;
            while ($out<$oblen) {
                $flags = ($flagCount++ % 8 == 0) ? ord($src{$in++}) : $flags >> 1;
                if (($flags & 1) == 1) {
                    $offset = ord($src{$in++});
                    $length = ord($src{$in++});
                    $offset = ($offset << 4) | ($length >> 4);
                    $length = ($length & 0xF) + 2;
                    $offset = (int)($out / 4096) * 4096 + $offset;
                    if ($offset >= $out) $offset -= 4096;
                    $end = $offset + $length;
                    while ($offset < $end) {
                        $dst{$out++} = $dst{$offset++};
                    };
                } else {
                    $dst{$out++} = $src{$in++};
                }
            }
            $src = $dst;
            $dest = substr($src,$this->LZRTF_HDR_LEN,$header['uSize']);
        } else {                                                // unknown magic - returfn false (please report if this ever happens)
            debugLog("Unknown Magic");
            return false;
        }

        return $dest;
    }

    // LZRTFCalcCRC32 - calculates the CRC32 of the LZRTF data part
    // buf = the whole rtf data part
    // off = start point of crc calculation
    // len = length of data to calculate CRC for
    // function is necessary since in RTF there is no XOR 0xffffffff being done (said to be 0x00 unsafe CRC32 calculation
    function LZRTFCalcCRC32($buf, $off, $len) {
        $c=0;
        $end = $off + $len;
        for($i=$off;$i < $end;$i++) {
            $c=$this->CRC32_TABLE[($c ^ ord($buf{$i})) & 0xFF] ^ (($c >> 8) & 0x00ffffff);
        }
        return $c;
    }

    function parserInit() { /* Default values according to the specs */
        $this->flags = array(
            "fontsize"          => 24,
            "beginparagraph"    => true,
        );
    }

    function parseControl($control, $parameter) {
        switch ($control) {
            case "fonttbl":             // font table definition start
                $this->flags["fonttbl"] = true; // signal fonttable control words they are allowed to behave as expected
                break;
            case "f":                   // define or set font
                if($this->flags["fonttbl"]) {   // if its set, the fonttable definition is written to; else its read from
                    $this->flags["fonttbl_current_write"] = $parameter;
                } else {
                    $this->flags["fonttbl_current_read"] = $parameter;
                }
                break;
            case "fcharset":            // this is for preparing flushQueue; it then moves the Queue to $this->fonttable .. instead to formatted output
                $this->flags["fonttbl_want_fcharset"] = $parameter;
                break;
            case "fs":                  // sets the current fontsize; is used by stylesheets (which are therefore generated on the fly
                $this->flags["fontsize"] = $parameter;
                break;

            case "qc":                  // handle center alignment
                $this->flags["alignment"] = "center";
                break;
            case "qr":                  // handle right alignment
                $this->flags["alignment"] = "right";
                break;

            case "pard":                // reset paragraph settings (only alignment)
                $this->flags["alignment"] = "";
                break;
            case "par":                 // define new paragraph (for now, thats a simple break in html) begin new line
                $this->flags["beginparagraph"] = true;
                if($this->wantHTML) {
                    $this->out .= "</div>";
                }
                if($this->wantASCII) {
                    $this->out .= "\n";
                }
                break;
            case "bnone":               // bold
                $parameter = "0";
            case "b":
                // haven'y yet figured out WHY I need a (string)-cast here ... hm
                if((string)$parameter == "0")
                    $this->flags["bold"] = false;
                else
                    $this->flags["bold"] = true;
                break;
            case "ulnone":              // underlined
                $parameter = "0";
            case "ul":
                if((string)$parameter == "0")
                    $this->flags["underlined"] = false;
                else
                    $this->flags["underlined"] = true;
                break;
            case "inone":               // italic
                $parameter = "0";
            case "i":
                if((string)$parameter == "0")
                    $this->flags["italic"] = false;
                else
                    $this->flags["italic"] = true;
                break;
            case "strikenone":          // strikethru
                $parameter = "0";
            case "strike":
                if((string)$parameter == "0")
                    $this->flags["strikethru"] = false;
                else
                    $this->flags["strikethru"] = true;
                break;
            case "plain":               // reset all font modifiers and fontsize to 12
                $this->flags["bold"] = false;
                $this->flags["italic"] = false;
                $this->flags["underlined"] = false;
                $this->flags["strikethru"] = false;
                $this->flags["fontsize"] = 12;

                $this->flags["subscription"] = false;
                $this->flags["superscription"] = false;
                break;
            case "subnone":             // subscription
                $parameter = "0";
            case "sub":
                if((string)$parameter == "0")
                    $this->flags["subscription"] = false;
                else
                    $this->flags["subscription"] = true;
                break;
            case "supernone":           // superscription
                $parameter = "0";
            case "super":
                if((string)$parameter == "0")
                    $this->flags["superscription"] = false;
                else
                    $this->flags["superscription"] = true;
                break;
        }
    }

    /*
        Dispatch the control word to the output stream
    */

    function flushControl() {
        if(preg_match("/^([A-Za-z]+)(-?[0-9]*) ?$/", $this->cword, $match)) {
            $this->parseControl($match[1], $match[2]);
            if($this->wantXML) {
                $this->out.="<control word=\"".$match[1]."\"";
                if(strlen($match[2]) > 0)
                    $this->out.=" param=\"".$match[2]."\"";
                $this->out.="/>";
            }
        }
    }

    /*
        If output stream supports comments, dispatch it
    */

    function flushComment($comment) {
        if($this->wantXML || $this->wantHTML) {
            $this->out.="<!-- ".$comment." -->";
        }
    }

    /*
        Dispatch start/end of logical rtf groups (not every output type needs it; merely debugging purpose)
    */

    function flushGroup($state) {
        if($state == "open") { /* push onto the stack */
            array_push($this->stack, $this->flags);

            if($this->wantXML)
                $this->out.="<group>";
        }
        if($state == "close") { /* pop from the stack */
            $this->last_flags = $this->flags;
            $this->flags = array_pop($this->stack);

            $this->flags["fonttbl_current_write"] = ""; // on group close, no more fontdefinition will be written to this id
                                                        // this is not really the right way to do it !
                                                        // of course a '}' not necessarily donates a fonttable end; a fonttable
                                                        // group at least *can* contain sub-groups
                                                        // therefore an stacked approach is heavily needed
            $this->flags["fonttbl"] = false;            // no matter what you do, if a group closes, its fonttbl definition is closed too

            if($this->wantXML)
                $this->out.="</group>";
        }
    }

    function flushHead() {
        if($this->wantXML)
            $this->out.="<rtf>";
    }

    function flushBottom() {
        if($this->wantXML)
            $this->out.="</rtf>";
    }

    function checkHtmlSpanContent($command) {
        reset($this->fontmodifier_table);
        while(list($rtf, $html) = each($this->fontmodifier_table)) {
            if($this->flags[$rtf] == true) {
                if($command == "start")
                    $this->out .= "<".$html.">";
                else
                    $this->out .= "</".$html.">";
            }
        }
    }

    /*
        flush text in queue
    */
    function flushQueue() {
        if(strlen($this->queue)) {
            // processing logic
            if (isset($this->flags["fonttbl_want_fcharset"]) &&
                preg_match("/^[0-9]+$/", $this->flags["fonttbl_want_fcharset"])) {
                $this->fonttable[$this->flags["fonttbl_want_fcharset"]]["charset"] = $this->queue;
                $this->flags["fonttbl_want_fcharset"] = "";
                $this->queue = "";
            }

            // output logic
            if (strlen($this->queue)) {
                /*
                    Everything which passes this is (or, at leat, *should*) be only outputted plaintext
                    Thats why we can safely add the css-stylesheet when using wantHTML
                */
                if($this->wantXML)
                    $this->out.= "<plain>".$this->queue."</plain>";
                else if($this->wantHTML) {
                    // only output html if a valid (for now, just numeric;) fonttable is given
					if (!isset($this->flags["fonttbl_current_read"])) $this->flags["fonttbl_current_read"] = "";
                    if(preg_match("/^[0-9]+$/", $this->flags["fonttbl_current_read"])) {
                        if($this->flags["beginparagraph"] == true) {
                            $this->flags["beginparagraph"] = false;
                            $this->out .= "<div align=\"";
                            switch($this->flags["alignment"]) {
                                case "right":
                                    $this->out .= "right";
                                    break;
                                case "center":
                                    $this->out .= "center";
                                    break;
                                case "left":
                                default:
                                    $this->out .= "left";
                            }
                            $this->out .= "\">";
                        }

                        /* define new style for that span */
                        $this->styles["f".$this->flags["fonttbl_current_read"]."s".$this->flags["fontsize"]] = "font-family:".$this->fonttable[$this->flags["fonttbl_current_read"]]["charset"]." font-size:".$this->flags["fontsize"].";";
                        /* write span start */
                        $this->out .= "<span class=\"f".$this->flags["fonttbl_current_read"]."s".$this->flags["fontsize"]."\">";

                        /* check if the span content has a modifier */
                        $this->checkHtmlSpanContent("start");
                        /* write span content */
                        $this->out .= $this->queue;
                        /* close modifiers */
                        $this->checkHtmlSpanContent("stop");
                        /* close span */
                        "</span>";
                    }
                }
                $this->queue = "";
            }
        }
    }

    /*
        handle special charactes like \'ef
    */

    function flushSpecial($special) {
        if(strlen($special) == 2) {
            if($this->wantASCII)
                $this->out .= chr(hexdec('0x'.$special));
            else if($this->wantXML)
                $this->out .= "<special value=\"".$special."\"/>";
            else if($this->wantHTML){
                $this->out .= "<special value=\"".$special."\"/>";
                switch($special) {
                    case "c1": $this->out .= "&Aacute;"; break;
                    case "e1": $this->out .= "&aacute;"; break;
                    case "c0": $this->out .= "&Agrave;"; break;
                    case "e0": $this->out .= "&agrave;"; break;
                    case "c9": $this->out .= "&Eacute;"; break;
                    case "e9": $this->out .= "&eacute;"; break;
                    case "c8": $this->out .= "&Egrave;"; break;
                    case "e8": $this->out .= "&egrave;"; break;
                    case "cd": $this->out .= "&Iacute;"; break;
                    case "ed": $this->out .= "&iacute;"; break;
                    case "cc": $this->out .= "&Igrave;"; break;
                    case "ec": $this->out .= "&igrave;"; break;
                    case "d3": $this->out .= "&Oacute;"; break;
                    case "f3": $this->out .= "&oacute;"; break;
                    case "d2": $this->out .= "&Ograve;"; break;
                    case "f2": $this->out .= "&ograve;"; break;
                    case "da": $this->out .= "&Uacute;"; break;
                    case "fa": $this->out .= "&uacute;"; break;
                    case "d9": $this->out .= "&Ugrave;"; break;
                    case "f9": $this->out .= "&ugrave;"; break;
                    case "80": $this->out .= "&#8364;"; break;
                    case "d1": $this->out .= "&Ntilde;"; break;
                    case "f1": $this->out .= "&ntilde;"; break;
                    case "c7": $this->out .= "&Ccedil;"; break;
                    case "e7": $this->out .= "&ccedil;"; break;
                    case "dc": $this->out .= "&Uuml;"; break;
                    case "fc": $this->out .= "&uuml;"; break;
                    case "bf": $this->out .= "&#191;"; break;
                    case "a1": $this->out .= "&#161;"; break;
                    case "b7": $this->out .= "&middot;"; break;
                    case "a9": $this->out .= "&copy;"; break;
                    case "ae": $this->out .= "&reg;"; break;
                    case "ba": $this->out .= "&ordm;"; break;
                    case "aa": $this->out .= "&ordf;"; break;
                    case "b2": $this->out .= "&sup2;"; break;
                    case "b3": $this->out .= "&sup3;"; break;
                }
            }
        }
    }

    /*
        Output errors at end
    */
    function flushErrors() {
        if(count($this->err) > 0) {
            if($this->wantXML) {
                $this->out .= "<errors>";
                while(list($num,$value) = each($this->err)) {
                    $this->out .= "<message>".$value."</message>";
                }
                $this->out .= "</errors>";
            }
        }
    }

    function makeStyles() {
        $this->outstyles = "<style type=\"text/css\"><!--\n";
        reset($this->styles);
        while(list($stylename, $styleattrib) = each($this->styles)) {
            $this->outstyles .= ".".$stylename." { ".$styleattrib." }\n";
        }
        $this->outstyles .= "--></style>\n";
    }

    function parse() {

        $this->parserInit();

        $i = 0;
        $this->cw= false;       // flag if control word is currently parsed
        $this->cfirst = false;  // first control character ?
        $this->cword = "";      // last or current control word (depends on $this->cw

        $this->queue = "";      // plain text data found during parsing

        $this->flushHead();

        while($i < $this->rtf_len) {
            switch($this->rtf[$i]) {
                case "{":
                    if($this->cw) {
                        $this->flushControl();
                        $this->cw = false;
                        $this->cfirst = false;
                    } else
                        $this->flushQueue();

                    $this->flushGroup("open");
                    break;
                case "}":
                    if($this->cw) {
                        $this->flushControl();
                        $this->cw = false;
                        $this->cfirst = false;
                    } else
                        $this->flushQueue();

                    $this->flushGroup("close");
                    break;
                case "\\":
                    if($this->cfirst) { // catches '\\'
                        $this->queue .= "\\"; // replaced single quotes
                        $this->cfirst = false;
                        $this->cw = false;
                        break;
                    }
                    if($this->cw) {
                        $this->flushControl();
                    } else
                        $this->flushQueue();
                    $this->cw = true;
                    $this->cfirst = true;
                    $this->cword = "";
                    break;
                default:
                    if((ord($this->rtf[$i]) == 10) || (ord($this->rtf[$i]) == 13)) break; // eat line breaks
                    if($this->cw) {     // active control word ?
                        /*
                            Watch the RE: there's an optional space at the end which IS part of
                            the control word (but actually its ignored by flushControl)
                        */
                        if(preg_match("/^[a-zA-Z0-9-]?$/", $this->rtf[$i])) { // continue parsing
                            $this->cword .= $this->rtf[$i];
                            $this->cfirst = false;
                        } else {
                            /*
                                Control word could be a 'control symbol', like \~ or \* etc.
                            */
                            $specialmatch = false;
                            if($this->cfirst) {
                                if($this->rtf[$i] == '\'') { // expect to get some special chars
                                    $this->flushQueue();
                                    $this->flushSpecial($this->rtf[$i+1].$this->rtf[$i+2]);
                                    $i+=2;
                                    $specialmatch = true;
                                    $this->cw = false;
                                    $this->cfirst = false;
                                    $this->cword = "";
                                } else
                                    if(preg_match("/^[{}\*]$/", $this->rtf[$i])) {
                                        $this->flushComment("control symbols not yet handled");
                                        $specialmatch = true;
                                    }
                                $this->cfirst = false;
                            } else {
                                if($this->rtf[$i] == ' ') {     // space delimtes control words, so just discard it and flush the controlword
                                    $this->cw = false;
                                    $this->flushControl();
                                    break;
                                }
                            }
                            if(!$specialmatch) {
                                $this->flushControl();
                                $this->cw = false;
                                $this->cfirst = false;
                                /*
                                    The current character is a delimeter, but is NOT
                                    part of the control word so we hop one step back
                                    in the stream and process it again
                                */
                                $i--;
                            }
                        }
                    } else {
                        // < and > need translation before putting into queue when XML or HTML is wanted
                        if(($this->wantHTML) || ($this->wantXML)) {
                            switch($this->rtf[$i]) {
                                case "<":
                                    $this->queue .= "&lt;";
                                    break;
                                case ">":
                                    $this->queue .= "&gt;";
                                    break;
                                default:
                                    $this->queue .= $this->rtf[$i];
                                    break;
                            }
                        } else
                            $this->queue .= $this->rtf[$i];
                    }

            }
            $i++;
        }
        $this->flushQueue();
        $this->flushErrors();
        $this->flushBottom();

        if($this->wantHTML) {
            $this->makeStyles();
        }
    }
}