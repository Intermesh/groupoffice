<?php
/**
 * Description of SpellChecker
 *
 * @author Shaun Forsyth <shaun@rpm-solutions.co.uk>
 * @author Michal Charvát <michal.charvat@zdeno.net>
 */


namespace GO\Base\Util;


class SpellChecker {

    private static $_pLink;

    public static function check($text, $language) {

        self::$_pLink = pspell_new($language, "", "", "utf-8", PSPELL_FAST);
				
				if(!self::$_pLink)
					throw new \Exception("Could not initialize pspell for language ".$language);

        $words = self::_getWords($text);
        $checkspelling = self::_checkWords($words);
        if (!empty($checkspelling)) {
            return $checkspelling;
        }else{
            return array();
        }
    }

    public static function replaceMisspeltWords($mispeltwords, $text) {
        $tokens = preg_split('/(<|>)/', $text, NULL, PREG_SPLIT_DELIM_CAPTURE);
        $inhtml = false;
        $ignorCheck = 0;

        foreach ($tokens as $key => $token) {

            if ($token == '>') {
                $inhtml = false;
                continue;
            }

            if ($token == '<') {
                $inhtml = true;
                continue;
            }

            if ($inhtml) {
                if (strstr($token, '/blockquote') > -1) {
                    --$ignorCheck;
                    continue;
                }
                if (strstr($token, 'blockquote') > -1) {
                    ++$ignorCheck;
                    continue;
                }
            }

            if (!$inhtml && !$ignorCheck) {
                foreach ($mispeltwords as $word => $sugestions){
                    //not sure how to fix this in one go so will use another regex to add another space between repeat words
                    $tokens[$key] = preg_replace('/(\b(\w+)(\b\s)*\2\b)/','\2\3\3\2',$tokens[$key]);
                    $tokens[$key] = mb_ereg_replace(
                        '(^|[._,\'"-]|&lt;|\s)'.preg_quote($word).'(\s|[._,@\'"-]|&gt;|$)','\1'.
                        self::_inlineSpellSystem($word,$sugestions,'\2').'\2',$tokens[$key], "m"
                    );
                }
            }
        }

        return implode('',$tokens);
    }

    private static function _inlineSpellSystem($word, $sugestions, $endDelem) {
        return '<span class="spelling" ieAfterObject="'.htmlentities($endDelem).'">'.$word.'<ul>'.self::_wraparray('<li>','</li>',$sugestions).'</ul></span>';
    }

    private static function _wraparray($before, $after, $sugestions) {
        $out = '';
        if (is_array($sugestions) && !empty($sugestions)){
            foreach ($sugestions as $sugestion){
                $out .= $before . $sugestion . $after;
            }
        }else{
            $out .= $before. \GO::t('No Sugestions') . $after;
        }
        return $out;
    }

    private static function _getWords($text) {
        //Add some space to the html
        $text = str_replace('<',' <',$text);
        //Remove Signature if found
        //this was a custom hack I had already applied to fix
        //signature changing problems, which turned out to be a entitiy problem, not location
        $text = preg_replace('/<div id=(")?EmailSignature(")?.*?<\/div>/si','',$text);
        //Assume there might be HTML so remove it;
        $text = strip_tags($text);
        //Decode HTML Entities
        $text = html_entity_decode($text, ENT_QUOTES);
        //Remove any email addresses (this could be stronger!)
        $text = preg_replace('/\w+@[a-zA-Z0-9-.]+\.(com|edu|gov|mil|net|org|biz|info|name|museum|us|ca|uk)/si',' ',$text);
        //Remove any web address (this could be stronger)
        $text = preg_replace('/(https?:\/\/)?\w+\.[a-zA-Z0-9-_.]+\.(co\.uk|com|edu|gov|mil|net|org|biz|info|name|museum|us|ca|uk)[a-zA-Z0-9-_.\/]*?(\s|$)/si',' ',$text);
        //Remove numbers
        $text = preg_replace('/[0-9.,]+/sm',' ',$text);
        //Replace any characters which should be splitters
        $text = mb_ereg_replace("[".preg_quote('!"\'#$%&()*+,-.:;<=>?@[]^_{|}§©«®±¶·¸»¼½¾\\¿×÷¤/','/')."]", '', $text, "m");
        //Fix MultiSpace
        $text = preg_replace('/\s+/',' ',$text);

        return array_unique(explode(' ',$text));
    }

    private static function _checkWords($words) {

        $words = array_filter($words);

        if (is_array($words) and !empty($words)) {
            $badwords = array();
            foreach ($words as $word){
                if (!pspell_check(self::$_pLink, $word)) {
                    $badwords[$word] = array_slice(
                        pspell_suggest(self::$_pLink, $word),0,21
                    );
                }
            }
            return $badwords;
        }else{
            return array();
        }
    }
}
