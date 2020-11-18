<?
    // use tabstop=4

    /*
        Rich Text Format - Parsing Class
        ================================

        (c) 2000 Markus Fischer
           https://github.com/mfn/rtfclass

        License: GNU AGPLv3

        Specification:
            http://msdn.microsoft.com/en-us/library/aa140277%28v=office.10%29.aspx

        General Notes:
        ==============
        Unknown or unspupported control symbols are silently ignored

        Group stacking is still not supported :(
            group stack logic implemented; however not really used yet

        Example on how to use this class:
        =================================

        $r = new rtf( stripslashes( $rtf));
        $r->output( "xml");
        $r->parse();
        if( count( $r->err) == 0) // no errors detected
            echo $r->out;

        History:
        ========
        Sat Nov 25 09:52:12 CET 2000    mfischer
            First version which has useable but only well-formed xml output; rtf
            data structure is only logically rebuild, no real parsing yet

        Mon Nov 27 16:17:18 CET 2000    mfischer
            Wrote handler for \plain control word (thanks to Peter Kursawe for this
            one)

        Tue Nov 28 02:22:16 CET 2000    mfischer
            Implemented alignment (left, center, right) with HTML <DIV .. tags
            Also implemented translation for < and > character when outputting html or xml

        Sun Dec 18 16:36:00 CET 2011    mfischer
            Switch to GNU AGPLv3 (http://www.gnu.org/licenses/agpl-3.0.html) and
            update links.

        Sun Jan 01 14:30:00 CET 2012    dw2412
            Replaced ereg with preg_match function since ereg is deprecated with PHP 5.3
            Introduced fonttbl_want_fcharset watch to prevent nasty message about uninitialized var

        Remarks:
        ========
        This class and all work done here is dedicated to Tatjana.
    */

    /* was just a brainlag suggestion of my inner link; don't know if I'll use it */
    class rtfState {
        var $bold;
        var $italic;
        var $underlined;
    }

    class rtf {
        var $rtf;        // rtf core stream
        var $len;        // length in characters of the stream (get performace due avoiding calling strlen everytime)
        var $err = array();        // array of error message, no entities on no error

        var $wantXML;    // convert to XML
        var $wantHTML;    // convert to HTML

        // the only variable which should be accessed from the outside
        var $out;        // output data stream (depends on which $wantXXXXX is set to true
        var $outstyles;    // htmlified styles (generated after parsing if wantHTML
        var $styles;    // if wantHTML, stylesheet definitions are put in here

        // internal parser variables --------------------------------
        // control word variables
        var $cword;        // holds the current (or last) control word, depending on $cw
        var $cw;        // are we currently parsing a control word ?
        var $cfirst;    // could this be the first character ? so watch out for control symbols

        var $flags = array();        // parser flags

        var $queue;        // every character which is no sepcial char, not belongs to a control word/symbol; is generally considered being 'plain'

        var $stack = array();    // group stack

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
            "spectspecifygen(-?[0-9]+)?"
            );

        var $charset_table = array(
            "0"    =>    "ANSI",
            "1"    =>    "Default",
            "2"    =>    "Symbol",
            "77" =>    "Mac",
            "128" =>    "Shift Jis",
            "129" =>    "Hangul",
            "130" =>    "Johab",
            "134" =>    "GB2312",
            "136" =>    "Big5",
            "161" =>    "Greek",
            "162" =>    "Turkish",
            "163" =>    "Vietnamese",
            "177" =>    "Hebrew",
            "178" =>    "Arabic",
            "179" =>    "Arabic Traditional",
            "180" =>    "Arabic user",
            "181" =>    "Hebrew user",
            "186" =>    "Baltic",
            "204" =>    "Russion",
            "222" =>    "Thai",
            "238" =>    "Eastern European",
            "255" =>    "PC 437",
            "255" =>    "OEM"
        );

        /* note: the only conversion table used */
        var $fontmodifier_table = array(
            "bold"    =>    "b",
            "italic"    => "i",
            "underlined"    => "u",
            "strikethru"    => "strike"
        );

        /*
            Class Constructor:
            Takes as argument the raw RTF stream
            (Note under certain circumstances the stream has to be stripslash'ed before handling over)
            Initialises some class-global variables
        */
        function rtf( $data) {
            $this->len = strlen( $data);
            $this->rtf = $data;

            $this->wantXML = false;
            $this->wantHTML = false;

            $this->out = "";
            $this->outstyles = "";
            $this->styles = array();
            $this->text = "";

            if( $this->len == 0)
                array_push( $this->err, "No data in stream found");
        }

        function parserInit() {
            /*
                Default values according to the specs
            */
            $this->flags = array(
                "fontsize"    =>    24,
                "beginparagraph"    => true
            );
        }

        /*
            Sets the output type
        */
        function output( $typ) {
            switch( $typ) {
                case "xml": $this->wantXML = true; break;
                case "html": $this->wantHTML = true; break;
                default: break;
            }
        }

        function parseControl( $control, $parameter) {
            switch( $control) {
                // font table definition start
                case "fonttbl":
                    $this->flags["fonttbl"] = true;    // signal fonttable control words they are allowed to behave as expected
                    break;
                // define or set font
                case "f":
                    if( $this->flags["fonttbl"]) {    // if its set, the fonttable definition is written to; else its read from
                        $this->flags["fonttbl_current_write"] = $parameter;
                    } else {
                        $this->flags["fonttbl_current_read"] = $parameter;
                    }
                    break;
                case "fcharset":
                    // this is for preparing flushQueue; it then moves the Queue to $this->fonttable .. instead to formatted output
                    $this->flags["fonttbl_want_fcharset"] = $parameter;
                    break;
                case "fs":
                    // sets the current fontsize; is used by stylesheets (which are therefore generated on the fly
                    $this->flags["fontsize"] = $parameter;
                    break;
                // handle alignment
                case "qc":
                    $this->flags["alignment"] = "center";
                    break;
                case "qr":
                    $this->flags["alignment"] = "right";
                    break;
                // reset paragraph settings ( only alignment)
                case "pard":
                    $this->flags["alignment"] = "";
                    break;
                // define new paragraph (for now, thats a simple break in html)
                case "par":
                    // begin new line
                    $this->flags["beginparagraph"] = true;
                    if( $this->wantHTML) {
                        $this->out .= "</div>";
                    }
                    break;
                // bold
                case "bnone":
                    $parameter = "0";
                case "b":
                    // haven'y yet figured out WHY I need a (string)-cast here ... hm
                    if( (string)$parameter == "0")
                        $this->flags["bold"] = false;
                    else
                        $this->flags["bold"] = true;
                    break;

                // underlined
                case "ulnone":
                    $parameter = "0";
                case "ul":
                    if( (string)$parameter == "0")
                        $this->flags["underlined"] = false;
                    else
                        $this->flags["underlined"] = true;
                    break;

                // italic
                case "inone":
                    $parameter = "0";
                case "i":
                    if( (string)$parameter == "0")
                        $this->flags["italic"] = false;
                    else
                        $this->flags["italic"] = true;
                    break;

                // strikethru
                case "strikenone":
                    $parameter = "0";
                case "strike":
                    if( (string)$parameter == "0")
                        $this->flags["strikethru"] = false;
                    else
                        $this->flags["strikethru"] = true;
                    break;

                // reset all font modifiers and fontsize to 12
                case "plain":
                    $this->flags["bold"] = false;
                    $this->flags["italic"] = false;
                    $this->flags["underlined"] = false;
                    $this->flags["strikethru"] = false;
                    $this->flags["fontsize"] = 12;

                    $this->flags["subscription"] = false;
                    $this->flags["superscription"] = false;
                    break;

                // sub and superscription
                case "subnone":
                    $parameter = "0";
                case "sub":
                    if( (string)$parameter == "0")
                        $this->flags["subscription"] = false;
                    else
                        $this->flags["subscription"] = true;
                    break;

                case "supernone":
                    $parameter = "0";
                case "super":
                    if( (string)$parameter == "0")
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
            if( preg_match( "/^([A-Za-z]+)(-?[0-9]*) ?$/", $this->cword, $match)) {

                $this->parseControl( $match[1], $match[2]);

                if( $this->wantXML) {
                    $this->out.="<control word=\"".$match[1]."\"";
                    if( strlen( $match[2]) > 0)
                        $this->out.=" param=\"".$match[2]."\"";
                    $this->out.="/>";
                }
            }
        }

        /*
            If output stream supports comments, dispatch it
        */
        function flushComment( $comment) {
            if( $this->wantXML || $this->wantHTML) {
                $this->out.="<!-- ".$comment." -->";
            }
        }

        /*
            Dispatch start/end of logical rtf groups
            (not every output type needs it; merely debugging purpose)
        */
        function flushGroup( $state) {
            if( $state == "open") {

                /* push onto the stack */
                array_push( $this->stack, $this->flags);

                if( $this->wantXML)
                    $this->out.="<group>";
            }
            if( $state == "close") {

                /* pop from the stack */
                $this->last_flags = $this->flags;
                $this->flags = array_pop( $this->stack);

                $this->flags["fonttbl_current_write"] = ""; // on group close, no more fontdefinition will be written to this id
                                                            // this is not really the right way to do it !
                                                            // of course a '}' not necessarily donates a fonttable end; a fonttable
                                                            // group at least *can* contain sub-groups
                                                            // therefore an stacked approach is heavily needed
                $this->flags["fonttbl"] = false; // no matter what you do, if a group closes, its fonttbl definition is closed too

                if( $this->wantXML)
                    $this->out.="</group>";
            }
        }

        function flushHead() {
            if( $this->wantXML)
                $this->out.="<rtf>";
        }

        function flushBottom() {
            if( $this->wantXML)
                $this->out.="</rtf>";
        }


        function checkHtmlSpanContent( $command) {
            reset( $this->fontmodifier_table);
            while( list( $rtf, $html) = each( $this->fontmodifier_table)) {
                if( $this->flags[$rtf] == true) {
                    if( $command == "start")
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
            if( strlen( $this->queue)) {
                // processing logic
                if( isset($this->flags["fonttbl_want_fcharset"]) &&
                    preg_match( "/^[0-9]+$/", $this->flags["fonttbl_want_fcharset"])) {
                    $this->fonttable[$this->flags["fonttbl_want_fcharset"]]["charset"] = $this->queue;
                    $this->flags["fonttbl_want_fcharset"] = "";
                    $this->queue = "";
                }

                // output logic
                if( strlen( $this->queue)) {
                    /*
                        Everything which passes this is (or, at leat, *should*) be only outputted plaintext
                        Thats why we can safely add the css-stylesheet when using wantHTML
                    */
                    if( $this->wantXML)
                        $this->out.= "<plain>".$this->queue."</plain>";

                    if( $this->wantHTML) {
                        // only output html if a valid (for now, just numeric;) fonttable is given
                        if( preg_match( "/^[0-9]+$/", $this->flags["fonttbl_current_read"])) {

                            if( $this->flags["beginparagraph"] == true) {
                                $this->flags["beginparagraph"] = false;
                                $this->out .= "<div align=\"";
                                switch( $this->flags["alignment"]) {
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
                            $this->checkHtmlSpanContent( "start");
                            /* write span content */
                            $this->out .= $this->queue;
                            /* close modifiers */
                            $this->checkHtmlSpanContent( "stop");
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
        function flushSpecial( $special) {
            if( strlen( $special) == 2) {
                if( $this->wantXML)
                    $this->out .= "<special value=\"".$special."\"/>";
            }
        }

        /*
            Output errors at end
        */
        function flushErrors() {
            if( count( $this->err) > 0) {
                if( $this->wantXML) {
                        $this->out .= "<errors>";
                        while( list($num,$value) = each( $this->err)) {
                            $this->out .= "<message>".$value."</message>";
                        }
                        $this->out .= "</errors>";
                }
            }
        }

        function makeStyles() {
            $this->outstyles = "<style type=\"text/css\"><!--\n";
            reset( $this->styles);
            while( list( $stylename, $styleattrib) = each( $this->styles)) {
                $this->outstyles .= ".".$stylename." { ".$styleattrib." }\n";
            }
            $this->outstyles .= "--></style>\n";
        }

        /*
            finally ..

            How this parser (is supposed) to work:
            ======================================
            This parse simple starts at the beginning of the rtf core stream, catches every
            controlling character {,} and \, automatically builds control words and control
            symbols during his livetime, trashes every other character into the plain text
            queue
        */
        function parse() {

            $this->parserInit();

            $i = 0;
            $this->cw= false;    // flag if control word is currently parsed
            $this->cfirst = false;// first control character ?
            $this->cword = "";    // last or current control word ( depends on $this->cw

            $this->queue = "";        // plain text data found during parsing

            $this->flushHead();

            while( $i < $this->len) {
                switch( $this->rtf[$i]) {
                    case "{":    if( $this->cw) {
                                    $this->flushControl();
                                    $this->cw= false; $this->cfirst = false;
                                } else
                                    $this->flushQueue();

                                $this->flushGroup( "open");
                                break;
                    case "}":    if( $this->cw) {
                                    $this->flushControl();
                                    $this->cw= false; $this->cfirst = false;
                                } else
                                    $this->flushQueue();

                                $this->flushGroup( "close");
                                break;
                    case "\\":    if( $this->cfirst) {    // catches '\\'
                                    $this->queue .= '\\';
                                    $this->cfirst = false;
                                    $this->cw= false;
                                    break;
                                }
                                if( $this->cw) {
                                    $this->flushControl();
                                } else
                                    $this->flushQueue();
                                $this->cw = true;
                                $this->cfirst = true;
                                $this->cword = "";
                                break;
                    default:
                                if( (ord( $this->rtf[$i]) == 10) || (ord($this->rtf[$i]) == 13)) break; // eat line breaks
                                if( $this->cw) {    // active control word ?
                                    /*
                                        Watch the RE: there's an optional space at the end which IS part of
                                        the control word (but actually its ignored by flushControl)
                                    */
                                    if( preg_match( "/^[a-zA-Z0-9-]?$/", $this->rtf[$i])) { // continue parsing
                                        $this->cword .= $this->rtf[$i];
                                        $this->cfirst = false;
                                    } else {
                                        /*
                                            Control word could be a 'control symbol', like \~ or \* etc.
                                        */
                                        $specialmatch = false;
                                        if( $this->cfirst) {
                                            if( $this->rtf[$i] == '\'') { // expect to get some special chars
                                                $this->flushQueue();
                                                $this->flushSpecial( $this->rtf[$i+1].$this->rtf[$i+2]);
                                                $i+=2;
                                                $specialmatch = true;
                                                $this->cw = false; $this->cfirst = false; $this->cword = "";
                                            } else
                                            if( preg_match( "/^[{}\*]$/", $this->rtf[$i])) {
                                                $this->flushComment( "control symbols not yet handled");
                                                $specialmatch = true;
                                            }
                                            $this->cfirst = false;
                                        } else {
                                            if( $this->rtf[$i] == ' ') {    // space delimtes control words, so just discard it and flush the controlword
                                                $this->cw = false;
                                                $this->flushControl();
                                                break;
                                            }
                                        }
                                        if( ! $specialmatch) {
                                            $this->flushControl();
                                            $this->cw = false; $this->cfirst = false;
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
                                    if( ($this->wantHTML) || ($this->wantXML)) {
                                        switch( $this->rtf[$i]) {
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

            if( $this->wantHTML) {
                $this->makeStyles();
            }
        }
    }
