<?php

/***********************************************
* File      :   mime_encode.php
* Project   :   Z-Push
* Descr     :   Functions for using within the IMAP backend
*
* Created   :   2014
*
* Copyright 2014 - 2016 Zarafa Deutschland GmbH
*
* This program is free software: you can redistribute it and/or modify
* it under the terms of the GNU Affero General Public License, version 3,
* as published by the Free Software Foundation.
*
* This program is distributed in the hope that it will be useful,
* but WITHOUT ANY WARRANTY; without even the implied warranty of
* MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
* GNU Affero General Public License for more details.
*
* You should have received a copy of the GNU Affero General Public License
* along with this program.  If not, see <http://www.gnu.org/licenses/>.
*
* Consult LICENSE file for details
************************************************/

/**
 * Add extra parts (not text; inlined or attached parts) to a mimepart object.
 *
 * @param Mail_mimePart $email reference to the object
 * @param array $parts array of parts
 *
 * @return void
 */
function add_extra_sub_parts(&$email, $parts) {
    if (isset($parts)) {
        foreach ($parts as $part) {
            $new_part = null;
            // Only if it's an attachment we will add the text parts, because all the inline/no disposition have been already added
            if (isset($part->disposition) && $part->disposition == "attachment") {
                // it's an attachment
                $new_part = add_sub_part($email, $part);
            }
            else {
                if (isset($part->ctype_primary) && $part->ctype_primary != "text" && $part->ctype_primary != "multipart") {
                    // it's not a text part or a multipart
                    $new_part = add_sub_part($email, $part);
                }
            }
            if (isset($part->parts)) {
                // We add sub-parts to the new part (if any), not to the main message. Recursive calling
                if ($new_part === null) {
                    add_extra_sub_parts($email, $part->parts);
                }
                else {
                    add_extra_sub_parts($new_part, $part->parts);
                }
            }
        }
    }
}

/**
 * Add a subpart to a mimepart object.
 *
 * @param Mail_mimePart $email reference to the object
 * @param object $part message part
 *
 * @return void
 */
function add_sub_part(&$email, $part) {
    //http://tools.ietf.org/html/rfc4021
    $new_part = null;
    $params = array();
    $params['content_type'] = '';
    if (isset($part) && isset($email)) {
        if (isset($part->ctype_primary)) {
            $params['content_type'] = $part->ctype_primary;
            if (isset($part->ctype_secondary)) {
                $params['content_type'] .= '/' . $part->ctype_secondary;
            }
        }
        if (isset($part->ctype_parameters)) {
            foreach ($part->ctype_parameters as $k => $v) {
                if(strcasecmp($k, 'boundary') != 0) {
                    $params['content_type'] .= '; ' . $k . '=' . $v;
                }
            }
        }
        if (isset($part->disposition)) {
            $params['disposition'] = $part->disposition;
        }
        //FIXME: dfilename => filename
        if (isset($part->d_parameters)) {
            $params['headers_charset'] = 'utf-8';
            foreach ($part->d_parameters as $k => $v) {
                $params[$k] = $v;
            }
        }
        foreach ($part->headers as $k => $v) {
            switch($k) {
                case "content-description":
                    $params['description'] = $v;
                    break;
                case "content-type":
                case "content-disposition":
                case "content-transfer-encoding":
                    // Do nothing, we already did
                    break;
                case "content-id":
                    $params['cid'] = str_replace('<', '', str_replace('>', '', $v));
                    break;
                default:
                    $params[$k] = $v;
                    break;
            }
        }

        // If not exist body, the part will be multipart/alternative, so we don't add encoding
        if (!isset($params['encoding']) && isset($part->body)) {
            $params['encoding'] = 'base64';
        }
        // We could not have body; recursive messages
        $new_part = $email->addSubPart(isset($part->body) ? $part->body : "", $params);
        unset($params);
    }

    // return the new part
    return $new_part;
}

/**
 * Add a subpart to a mimepart object.
 *
 * @param Mail_mimePart $email reference to the object
 * @param object $part message part
 *
 * @return void
 */
function change_charset_and_add_subparts(&$email, $part) {
    if (isset($part)) {
        $new_part = null;
        if (isset($part->ctype_parameters['charset'])) {
            $part->ctype_parameters['charset'] = 'UTF-8';

            Utils::CheckAndFixEncoding($part->body);

            $new_part = add_sub_part($email, $part);
        }
        else {
            // We don't add the charset because it could be a non-text part
            $new_part = add_sub_part($email, $part);
        }

        if (isset($part->parts)) {
            foreach ($part->parts as $subpart) {
                // Subparts are added to the part, not the main message
                change_charset_and_add_subparts($new_part, $subpart);
            }
        }
    }
}

/**
 * Creates a MIME message from a decoded MIME message, reencoding and fixing the text.
 *
 * @param array $message array returned from Mail_mimeDecode->decode
 *
 * @return string MIME message
 */
function build_mime_message($message) {
    $finalEmail = new Mail_mimePart(isset($message->body) ? $message->body : "", array('headers' => $message->headers));
    if (isset($message->parts)) {
        foreach ($message->parts as $part) {
            change_charset_and_add_subparts($finalEmail, $part);
        }
    }

    $mimeHeaders = Array();
    $mimeHeaders['headers'] = Array();
    $is_mime = false;
    foreach ($message->headers as $key => $value) {
        switch($key) {
            case 'content-type':
                $new_value = $message->ctype_primary . "/" . $message->ctype_secondary;
                $is_mime = (strcasecmp($message->ctype_primary, 'multipart') == 0);

                if (isset($message->ctype_parameters)) {
                    foreach ($message->ctype_parameters as $ckey => $cvalue) {
                        switch($ckey) {
                            case 'charset':
                                $new_value .= '; charset="UTF-8"';
                                break;
                            case 'boundary':
                                // Do nothing, we are encoding also the headers
                                break;
                            default:
                                $new_value .= '; ' . $ckey . '="' . $cvalue . '"';
                                break;
                        }
                    }
                }

                $mimeHeaders['content_type'] = $new_value;
                break;
            case 'content-transfer-encoding':
                if (strcasecmp($value, "base64") == 0 || strcasecmp($value, "binary") == 0) {
                    $mimeHeaders['encoding'] = "base64";
                }
                else {
                    $mimeHeaders['encoding'] = "8bit";
                }
                break;
            case 'content-id':
                $mimeHeaders['cid'] = $value;
                break;
            case 'content-location':
                $mimeHeaders['location'] = $value;
                break;
            case 'content-disposition':
                $mimeHeaders['disposition'] = $value;
                break;
            case 'content-description':
                $mimeHeaders['description'] = $value;
                break;
            default:
                if (is_array($value)) {
                    foreach($value as $v) {
                        $mimeHeaders['headers'][$key] = $v;
                    }
                }
                else {
                    $mimeHeaders['headers'][$key] = $value;
                }
                break;
        }
    }

    Utils::CheckAndFixEncoding($message->body);

    $finalEmail = new Mail_mimePart(isset($message->body) ? $message->body : "", $mimeHeaders);
    unset($mimeHeaders);

    if (isset($message->parts)) {
        foreach ($message->parts as $part) {
            change_charset_and_add_subparts($finalEmail, $part);
        }
    }

    $boundary = '=_' . md5(rand() . microtime());
    $finalEmail = $finalEmail->encode($boundary);

    $headers = "";
    $mimePart = new Mail_mimePart();
    foreach ($finalEmail['headers'] as $key => $value) {
        if (is_array($value)) {
            foreach ($values as $ikey => $ivalue) {
                $headers .= $key . ": " . $mimePart->encodeHeader($key, $ivalue, "utf-8", "base64") . "\n";
            }
        }
        else {
            $headers .= $key . ": " . $mimePart->encodeHeader($key, $value, "utf-8", "base64") . "\n";
        }
    }
    unset($mimePart);


    if ($is_mime) {
        $built_message = "$headers\nThis is a multi-part message in MIME format.\n".$finalEmail['body'];
    }
    else {
        $built_message = "$headers\n".$finalEmail['body'];
    }
    unset($headers);
    unset($finalEmail);

    return $built_message;
}


/**
 * Detect if the message-part is SMIME
 *
 * @param Mail_mimeDecode $message
 * @return boolean
 */
function is_smime($message) {
    $res = false;

    if (isset($message->ctype_primary) && isset($message->ctype_secondary)) {
        $smime_types = array(array("multipart", "signed"), array("application", "pkcs7-mime"), array("application", "x-pkcs7-mime"), array("multipart", "encrypted"));
        for ($i = 0; $i < count($smime_types) && !$res; $i++) {
            $res = ($message->ctype_primary == $smime_types[$i][0] && $message->ctype_secondary == $smime_types[$i][1]);
        }
    }

    return $res;
}


/**
 * Detect if the message-part is SMIME, encrypted but not signed
 * #190, KD 2015-06-04
 *
 * @param Mail_mimeDecode $message
 * @return boolean
 */
function is_encrypted($message) {
    $res = false;

    if (is_smime($message) && !($message->ctype_primary == "multipart" && $message->ctype_secondary == "signed")) {
        $res = true;
    }

    return $res;
}


/**
 * Detect if the message is multipart.
 * #198, KD 2015-06-15
 *
 * @param Mail_mimeDecode $message
 * @return boolean
 */
function is_multipart($message) {
    return isset($message->ctype_primary) && $message->ctype_primary == "multipart";
}