<?php
/***********************************************
* File      :   search.php
* Project   :   Z-Push
* Descr     :   Provides the SEARCH command
*
* Created   :   16.02.2012
*
* Copyright 2007 - 2016 Zarafa Deutschland GmbH
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

class Search extends RequestProcessor {

    /**
     * Handles the Search command
     *
     * @param int       $commandCode
     *
     * @access public
     * @return boolean
     */
    public function Handle($commandCode) {
        $searchrange = '0';
        $searchpicture = false;
        $cpo = new ContentParameters();

        if(!self::$decoder->getElementStartTag(SYNC_SEARCH_SEARCH))
            return false;

        // TODO check: possible to search in other stores?
        if(!self::$decoder->getElementStartTag(SYNC_SEARCH_STORE))
            return false;

        if(!self::$decoder->getElementStartTag(SYNC_SEARCH_NAME))
            return false;
        $searchname = strtoupper(self::$decoder->getElementContent());
        if(!self::$decoder->getElementEndTag())
            return false;

        if(!self::$decoder->getElementStartTag(SYNC_SEARCH_QUERY))
            return false;

        // check if it is a content of an element (= GAL search)
        // or a starttag (= mailbox or documentlibrary search)
        $searchquery = self::$decoder->getElementContent();
        if($searchquery && !self::$decoder->getElementEndTag())
            return false;

        if ($searchquery === false) {
            $cpo->SetSearchName($searchname);
            if (self::$decoder->getElementStartTag(SYNC_SEARCH_AND)) {
                if (self::$decoder->getElementStartTag(SYNC_FOLDERID)) {
                    $searchfolderid = self::$decoder->getElementContent();
                    $cpo->SetSearchFolderid($searchfolderid);
                    if(!self::$decoder->getElementEndTag()) // SYNC_FOLDERTYPE
                    return false;
                }


                if (self::$decoder->getElementStartTag(SYNC_FOLDERTYPE)) {
                    $searchclass = self::$decoder->getElementContent();
                    $cpo->SetSearchClass($searchclass);
                    if(!self::$decoder->getElementEndTag()) // SYNC_FOLDERTYPE
                        return false;
                }

                if (self::$decoder->getElementStartTag(SYNC_FOLDERID)) {
                    $searchfolderid = self::$decoder->getElementContent();
                    $cpo->SetSearchFolderid($searchfolderid);
                    if(!self::$decoder->getElementEndTag()) // SYNC_FOLDERTYPE
                    return false;
                }

                if (self::$decoder->getElementStartTag(SYNC_SEARCH_FREETEXT)) {
                    $searchfreetext = self::$decoder->getElementContent();
                    $cpo->SetSearchFreeText($searchfreetext);
                    if(!self::$decoder->getElementEndTag()) // SYNC_SEARCH_FREETEXT
                    return false;
                }

                //TODO - review
                if (self::$decoder->getElementStartTag(SYNC_SEARCH_GREATERTHAN)) {
                    if(self::$decoder->getElementStartTag(SYNC_POOMMAIL_DATERECEIVED)) {
                        $datereceivedgreater = true;
                        if (($dam = self::$decoder->getElementContent()) !== false) {
                            $datereceivedgreater = true;
                            if(!self::$decoder->getElementEndTag()) {
                                return false;
                            }
                        }
                        $cpo->SetSearchDateReceivedGreater($datereceivedgreater);
                    }

                    if(self::$decoder->getElementStartTag(SYNC_SEARCH_VALUE)) {
                        $searchvalue = self::$decoder->getElementContent();
                        $cpo->SetSearchValueGreater($searchvalue);
                        if(!self::$decoder->getElementEndTag()) // SYNC_SEARCH_VALUE
                            return false;
                    }

                    if(!self::$decoder->getElementEndTag()) // SYNC_SEARCH_GREATERTHAN
                        return false;
                }

                if (self::$decoder->getElementStartTag(SYNC_SEARCH_LESSTHAN)) {
                    if(self::$decoder->getElementStartTag(SYNC_POOMMAIL_DATERECEIVED)) {
                        $datereceivedless = true;
                        if (($dam = self::$decoder->getElementContent()) !== false) {
                            $datereceivedless = true;
                            if(!self::$decoder->getElementEndTag()) {
                                return false;
                            }
                        }
                        $cpo->SetSearchDateReceivedLess($datereceivedless);
                    }

                    if(self::$decoder->getElementStartTag(SYNC_SEARCH_VALUE)) {
                        $searchvalue = self::$decoder->getElementContent();
                        $cpo->SetSearchValueLess($searchvalue);
                        if(!self::$decoder->getElementEndTag()) // SYNC_SEARCH_VALUE
                         return false;
                    }

                    if(!self::$decoder->getElementEndTag()) // SYNC_SEARCH_LESSTHAN
                        return false;
                }

                if (self::$decoder->getElementStartTag(SYNC_SEARCH_FREETEXT)) {
                    $searchfreetext = self::$decoder->getElementContent();
                    $cpo->SetSearchFreeText($searchfreetext);
                    if(!self::$decoder->getElementEndTag()) // SYNC_SEARCH_FREETEXT
                    return false;
                }

                if(!self::$decoder->getElementEndTag()) // SYNC_SEARCH_AND
                    return false;
            }
            elseif (self::$decoder->getElementStartTag(SYNC_SEARCH_EQUALTO)) {
                    // linkid can be an empty tag as well as have value
                    if(self::$decoder->getElementStartTag(SYNC_DOCUMENTLIBRARY_LINKID)) {
                        if (($linkId = self::$decoder->getElementContent()) !== false) {
                            $cpo->SetLinkId($linkId);
                            if(!self::$decoder->getElementEndTag()) { // SYNC_DOCUMENTLIBRARY_LINKID
                                return false;
                            }
                        }
                    }

                    if(self::$decoder->getElementStartTag(SYNC_SEARCH_VALUE)) {
                        $searchvalue = self::$decoder->getElementContent();
                        $cpo->SetSearchValueEqualTo($searchvalue);
                        if(!self::$decoder->getElementEndTag()) // SYNC_SEARCH_VALUE
                            return false;
                    }

                    if(!self::$decoder->getElementEndTag()) // SYNC_SEARCH_EQUALTO
                        return false;
                }

            if(!self::$decoder->getElementEndTag()) // SYNC_SEARCH_QUERY
                return false;

        }

        if(self::$decoder->getElementStartTag(SYNC_SEARCH_OPTIONS)) {
            WBXMLDecoder::ResetInWhile("searchOptions");
            while(WBXMLDecoder::InWhile("searchOptions")) {
                if(self::$decoder->getElementStartTag(SYNC_SEARCH_RANGE)) {
                    $searchrange = self::$decoder->getElementContent();
                    $cpo->SetSearchRange($searchrange);
                    if(!self::$decoder->getElementEndTag())
                        return false;
                }


                if(self::$decoder->getElementStartTag(SYNC_SEARCH_REBUILDRESULTS)) {
                    $rebuildresults = true;
                    if (($dam = self::$decoder->getElementContent()) !== false) {
                        $rebuildresults = true;
                        if(!self::$decoder->getElementEndTag()) {
                            return false;
                        }
                    }
                    $cpo->SetSearchRebuildResults($rebuildresults);
                }

                if(self::$decoder->getElementStartTag(SYNC_SEARCH_DEEPTRAVERSAL)) {
                    $deeptraversal = true;
                    if (($dam = self::$decoder->getElementContent()) !== false) {
                        $deeptraversal = true;
                        if(!self::$decoder->getElementEndTag()) {
                            return false;
                        }
                    }
                    $cpo->SetSearchDeepTraversal($deeptraversal);
                }

                if(self::$decoder->getElementStartTag(SYNC_MIMESUPPORT)) {
                    $cpo->SetMimeSupport(self::$decoder->getElementContent());
                    if(!self::$decoder->getElementEndTag())
                    return false;
                }

                //TODO body preferences
                while (self::$decoder->getElementStartTag(SYNC_AIRSYNCBASE_BODYPREFERENCE)) {
                    if(self::$decoder->getElementStartTag(SYNC_AIRSYNCBASE_TYPE)) {
                        $bptype = self::$decoder->getElementContent();
                        $cpo->BodyPreference($bptype);
                        if(!self::$decoder->getElementEndTag()) {
                            return false;
                        }
                    }

                    if(self::$decoder->getElementStartTag(SYNC_AIRSYNCBASE_TRUNCATIONSIZE)) {
                        $cpo->BodyPreference($bptype)->SetTruncationSize(self::$decoder->getElementContent());
                        if(!self::$decoder->getElementEndTag())
                            return false;
                    }

                    if(self::$decoder->getElementStartTag(SYNC_AIRSYNCBASE_ALLORNONE)) {
                        $cpo->BodyPreference($bptype)->SetAllOrNone(self::$decoder->getElementContent());
                        if(!self::$decoder->getElementEndTag())
                            return false;
                    }

                    if(self::$decoder->getElementStartTag(SYNC_AIRSYNCBASE_PREVIEW)) {
                        $cpo->BodyPreference($bptype)->SetPreview(self::$decoder->getElementContent());
                        if(!self::$decoder->getElementEndTag())
                            return false;
                    }

                    if(!self::$decoder->getElementEndTag())
                        return false;
                }

                if (self::$decoder->getElementStartTag(SYNC_AIRSYNCBASE_BODYPARTPREFERENCE)) {
                    if (self::$decoder->getElementStartTag(SYNC_AIRSYNCBASE_TYPE)) {
                        $bpptype = self::$decoder->getElementContent();
                        $cpo->BodyPartPreference($bpptype);
                        if (!self::$decoder->getElementEndTag()) {
                            return false;
                        }
                    }

                    if (self::$decoder->getElementStartTag(SYNC_AIRSYNCBASE_TRUNCATIONSIZE)) {
                        $cpo->BodyPartPreference($bpptype)->SetTruncationSize(self::$decoder->getElementContent());
                        if(!self::$decoder->getElementEndTag())
                            return false;
                    }

                    if (self::$decoder->getElementStartTag(SYNC_AIRSYNCBASE_ALLORNONE)) {
                        $cpo->BodyPartPreference($bpptype)->SetAllOrNone(self::$decoder->getElementContent());
                        if(!self::$decoder->getElementEndTag())
                            return false;
                    }

                    if (self::$decoder->getElementStartTag(SYNC_AIRSYNCBASE_PREVIEW)) {
                        $cpo->BodyPartPreference($bpptype)->SetPreview(self::$decoder->getElementContent());
                        if(!self::$decoder->getElementEndTag())
                            return false;
                    }

                    if (!self::$decoder->getElementEndTag())
                        return false;
                }

                if(self::$decoder->getElementStartTag(SYNC_RIGHTSMANAGEMENT_SUPPORT)) {
                    $cpo->SetRmSupport(self::$decoder->getElementContent());
                    if(!self::$decoder->getElementEndTag())
                        return false;
                }

                if(self::$decoder->getElementStartTag(SYNC_SEARCH_PICTURE)) { // TODO - do something with maxsize and maxpictures in the backend
                    $searchpicture = new SyncResolveRecipientsPicture();
                    if(self::$decoder->getElementStartTag(SYNC_SEARCH_MAXSIZE)) {
                        $searchpicture->maxsize = self::$decoder->getElementContent();
                        if(!self::$decoder->getElementEndTag())
                            return false;
                    }

                    if(self::$decoder->getElementStartTag(SYNC_SEARCH_MAXPICTURES)) {
                        $searchpicture->maxpictures = self::$decoder->getElementContent();
                        if(!self::$decoder->getElementEndTag())
                            return false;
                    }

                    // iOs devices send empty picture tag: <Search:Picture/>
                    if (($sp = self::$decoder->getElementContent()) !== false) {
                        if(!self::$decoder->getElementEndTag()) {
                            return false;
                        }
                    }
                }

                $e = self::$decoder->peek();
                if($e[EN_TYPE] == EN_TYPE_ENDTAG) {
                    self::$decoder->getElementEndTag();
                    break;
                }
            }
        }
        if(!self::$decoder->getElementEndTag()) //store
            return false;

        if(!self::$decoder->getElementEndTag()) //search
            return false;

        // get SearchProvider
        $searchprovider = ZPush::GetSearchProvider();
        $status = SYNC_SEARCHSTATUS_SUCCESS;
        $rows = array();

        // TODO support other searches
        if ($searchprovider->SupportsType($searchname)) {
            $storestatus = SYNC_SEARCHSTATUS_STORE_SUCCESS;
            try {
                if ($searchname == ISearchProvider::SEARCH_GAL) {
                    //get search results from the searchprovider
                    $rows = $searchprovider->GetGALSearchResults($searchquery, $searchrange, $searchpicture);
                }
                elseif ($searchname == ISearchProvider::SEARCH_MAILBOX) {
                    $backendFolderId = self::$deviceManager->GetBackendIdForFolderId($cpo->GetSearchFolderid());
                    $cpo->SetSearchFolderid($backendFolderId);
                    $rows = $searchprovider->GetMailboxSearchResults($cpo);
                }
            }
            catch (StatusException $stex) {
                $storestatus = $stex->getCode();
            }
        }
        else {
            $rows = array('searchtotal' => 0);
            $status = SYNC_SEARCHSTATUS_SERVERERROR;
            ZLog::Write(LOGLEVEL_WARN, sprintf("Searchtype '%s' is not supported.", $searchname));
            self::$topCollector->AnnounceInformation(sprintf("Unsupported type '%s''", $searchname), true);
        }
        $searchprovider->Disconnect();

        self::$topCollector->AnnounceInformation(sprintf("'%s' search found %d results", $searchname, (isset($rows['searchtotal']) ? $rows['searchtotal'] : 0) ), true);

        self::$encoder->startWBXML();
        self::$encoder->startTag(SYNC_SEARCH_SEARCH);

            self::$encoder->startTag(SYNC_SEARCH_STATUS);
            self::$encoder->content($status);
            self::$encoder->endTag();

            if ($status == SYNC_SEARCHSTATUS_SUCCESS) {
                self::$encoder->startTag(SYNC_SEARCH_RESPONSE);
                self::$encoder->startTag(SYNC_SEARCH_STORE);

                    self::$encoder->startTag(SYNC_SEARCH_STATUS);
                    self::$encoder->content($storestatus);
                    self::$encoder->endTag();

                    if (isset($rows['range'])) {
                        $searchrange = $rows['range'];
                        unset($rows['range']);
                    }
                    if (isset($rows['searchtotal'])) {
                        $searchtotal = $rows['searchtotal'];
                        unset($rows['searchtotal']);
                    }
                    if ($searchname == ISearchProvider::SEARCH_GAL) {
                        if (is_array($rows) && !empty($rows)) {
                            foreach ($rows as $u) {
                                self::$encoder->startTag(SYNC_SEARCH_RESULT);
                                    self::$encoder->startTag(SYNC_SEARCH_PROPERTIES);

                                        self::$encoder->startTag(SYNC_GAL_DISPLAYNAME);
                                        self::$encoder->content((isset($u[SYNC_GAL_DISPLAYNAME]))?$u[SYNC_GAL_DISPLAYNAME]:"No name");
                                        self::$encoder->endTag();

                                        if (isset($u[SYNC_GAL_PHONE])) {
                                            self::$encoder->startTag(SYNC_GAL_PHONE);
                                            self::$encoder->content($u[SYNC_GAL_PHONE]);
                                            self::$encoder->endTag();
                                        }

                                        if (isset($u[SYNC_GAL_OFFICE])) {
                                            self::$encoder->startTag(SYNC_GAL_OFFICE);
                                            self::$encoder->content($u[SYNC_GAL_OFFICE]);
                                            self::$encoder->endTag();
                                        }

                                        if (isset($u[SYNC_GAL_TITLE])) {
                                            self::$encoder->startTag(SYNC_GAL_TITLE);
                                            self::$encoder->content($u[SYNC_GAL_TITLE]);
                                            self::$encoder->endTag();
                                        }

                                        if (isset($u[SYNC_GAL_COMPANY])) {
                                            self::$encoder->startTag(SYNC_GAL_COMPANY);
                                            self::$encoder->content($u[SYNC_GAL_COMPANY]);
                                            self::$encoder->endTag();
                                        }

                                        if (isset($u[SYNC_GAL_ALIAS])) {
                                            self::$encoder->startTag(SYNC_GAL_ALIAS);
                                            self::$encoder->content($u[SYNC_GAL_ALIAS]);
                                            self::$encoder->endTag();
                                        }

                                        // Always send the firstname, even empty. Nokia needs this to display the entry
                                        self::$encoder->startTag(SYNC_GAL_FIRSTNAME);
                                        self::$encoder->content((isset($u[SYNC_GAL_FIRSTNAME]))?$u[SYNC_GAL_FIRSTNAME]:"");
                                        self::$encoder->endTag();

                                        self::$encoder->startTag(SYNC_GAL_LASTNAME);
                                        self::$encoder->content((isset($u[SYNC_GAL_LASTNAME]))?$u[SYNC_GAL_LASTNAME]:"No name");
                                        self::$encoder->endTag();

                                        if (isset($u[SYNC_GAL_HOMEPHONE])) {
                                            self::$encoder->startTag(SYNC_GAL_HOMEPHONE);
                                            self::$encoder->content($u[SYNC_GAL_HOMEPHONE]);
                                            self::$encoder->endTag();
                                        }

                                        if (isset($u[SYNC_GAL_MOBILEPHONE])) {
                                            self::$encoder->startTag(SYNC_GAL_MOBILEPHONE);
                                            self::$encoder->content($u[SYNC_GAL_MOBILEPHONE]);
                                            self::$encoder->endTag();
                                        }

                                        self::$encoder->startTag(SYNC_GAL_EMAILADDRESS);
                                        self::$encoder->content((isset($u[SYNC_GAL_EMAILADDRESS]))?$u[SYNC_GAL_EMAILADDRESS]:"");
                                        self::$encoder->endTag();


                                        if (isset($u[SYNC_GAL_PICTURE])) {
                                            self::$encoder->startTag(SYNC_GAL_PICTURE);
                                                self::$encoder->startTag(SYNC_GAL_STATUS);
                                                self::$encoder->content(SYNC_SEARCHSTATUS_PICTURE_SUCCESS); //FIXME: status code
                                                self::$encoder->endTag(); // SYNC_SEARCH_STATUS

                                                self::$encoder->startTag(SYNC_GAL_DATA);
                                                self::$encoder->contentStream($u[SYNC_GAL_PICTURE], false, true);
                                                self::$encoder->endTag(); // SYNC_GAL_DATA
                                            self::$encoder->endTag(); // SYNC_GAL_PICTURE
                                        }

                                    self::$encoder->endTag();//result
                                self::$encoder->endTag();//properties
                            }
                        }
                    }
                    elseif ($searchname == ISearchProvider::SEARCH_MAILBOX) {
                        foreach ($rows as $u) {
                            $folderid = self::$deviceManager->GetFolderIdForBackendId($u['folderid']);

                            self::$encoder->startTag(SYNC_SEARCH_RESULT);
                                self::$encoder->startTag(SYNC_FOLDERTYPE);
                                self::$encoder->content($u['class']);
                                self::$encoder->endTag();
                                self::$encoder->startTag(SYNC_SEARCH_LONGID);
                                self::$encoder->content($u['longid']);
                                self::$encoder->endTag();
                                self::$encoder->startTag(SYNC_FOLDERID);
                                self::$encoder->content($folderid);
                                self::$encoder->endTag();

                                self::$encoder->startTag(SYNC_SEARCH_PROPERTIES);
                                    $tmp = explode(":", $u['longid']);
                                    $message = self::$backend->Fetch($u['folderid'], $tmp[1], $cpo);
                                    $message->Encode(self::$encoder);

                                self::$encoder->endTag();//result
                            self::$encoder->endTag();//properties
                        }
                    }
                    // it seems that android 4 requires range and searchtotal
                    // or it won't display the search results
                    if (isset($searchrange)) {
                        self::$encoder->startTag(SYNC_SEARCH_RANGE);
                        self::$encoder->content($searchrange);
                        self::$encoder->endTag();
                    }
                    if (isset($searchtotal) && $searchtotal > 0) {
                        self::$encoder->startTag(SYNC_SEARCH_TOTAL);
                        self::$encoder->content($searchtotal);
                        self::$encoder->endTag();
                    }

                self::$encoder->endTag();//store
                self::$encoder->endTag();//response
            }
        self::$encoder->endTag();//search

        return true;
    }
}
