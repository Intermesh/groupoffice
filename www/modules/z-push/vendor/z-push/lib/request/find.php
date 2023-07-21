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

class Find extends RequestProcessor {

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


	    if(!self::$decoder->getElementStartTag("Find:Find")) {
				ZLog::Write(LOGLEVEL_DEBUG, "ERROR: No find tag");
		    return false;
	    }


	    if(!self::$decoder->getElementStartTag("Find:SearchId"))
		    return false;
	    $searchId = strtoupper(self::$decoder->getElementContent());
	    ZLog::Write(LOGLEVEL_DEBUG, "SearchId: $searchId");
	    if(!self::$decoder->getElementEndTag())
		    return false;

			//SearchId
	    //ExecuteSearch
	      //MailBoxSearchCriterion
	        //Query
	          //FolderType
	          //FolderId m/Inbox
	          //FreeText  to:"Testing1234" OR cc:"Testing1234" OR from:"Testing1234" OR subject:"Testing1234" OR "Testing1234"
	        //Options
	          //Range 0-99
	          //DeepTraversal


	    if(!self::$decoder->getElementStartTag("Find:ExecuteSearch"))
		    return false;

		    if(!self::$decoder->getElementStartTag("Find:MailBoxSearchCriterion"))
			    return false;

			    if(!self::$decoder->getElementStartTag("Find:Query"))
				    return false;

				    if (self::$decoder->getElementStartTag("FolderType")) {
					    $searchclass = self::$decoder->getElementContent();

					    ZLog::Write(LOGLEVEL_DEBUG, "FolderType: $searchclass");
					    $cpo->SetSearchClass($searchclass);
					    if(!self::$decoder->getElementEndTag()) {// SYNC_FOLDERTYPE
						    ZLog::Write(LOGLEVEL_DEBUG, "ERROR: No end tag");
						    return false;
					    }
				    }

				    if (self::$decoder->getElementStartTag("FolderId")) {
					    $searchfolderid = self::$decoder->getElementContent();
					    ZLog::Write(LOGLEVEL_DEBUG, "FolderId: $searchfolderid");
					    $cpo->SetSearchFolderid($searchfolderid);
					    if(!self::$decoder->getElementEndTag()) {// SYNC_FOLDERTYPE
						    ZLog::Write(LOGLEVEL_DEBUG, "ERROR: No end tag");
						    return false;
					    }
				    }

				    if (self::$decoder->getElementStartTag("Find:FreeText")) {
					    $searchfreetext = self::$decoder->getElementContent();

					    ZLog::Write(LOGLEVEL_DEBUG, "FreeText: $searchfreetext");
					    $cpo->SetSearchFreeText($searchfreetext);
					    if(!self::$decoder->getElementEndTag()) {// SYNC_FOLDERTYPE
						    ZLog::Write(LOGLEVEL_DEBUG, "ERROR: No end tag");
						    return false;
					    }
				    }

			    if(!self::$decoder->getElementEndTag()) // SYNC_SEARCH_QUERY
				    return false;


			    if(self::$decoder->getElementStartTag("Find:Options")) {

				    if (self::$decoder->getElementStartTag("Find:Range")) {
					    $searchrange = self::$decoder->getElementContent();
					    ZLog::Write(LOGLEVEL_DEBUG, "Range: $searchrange");
					    $cpo->SetSearchRange($searchrange);
					    if (!self::$decoder->getElementEndTag())
						    return false;
				    }

				    if (self::$decoder->getElementStartTag("Find:DeepTraversal")) {
					    $deeptraversal = true;
					    if (($dam = self::$decoder->getElementContent()) !== false) {
						    $deeptraversal = true;
						    if (!self::$decoder->getElementEndTag()) {
							    return false;
						    }
					    }
					    $cpo->SetSearchDeepTraversal($deeptraversal);
				    }
				    if (!self::$decoder->getElementEndTag())
					    return false;
			    }

				if(!self::$decoder->getElementEndTag()) // MailBoxSearchCriterion
		      return false;

	    if(!self::$decoder->getElementEndTag()) // ExecuteSearch
		    return false;

	    if(!self::$decoder->getElementEndTag()) //find
		    return false;

			ZLog::Write(LOGLEVEL_DEBUG, var_export($cpo->GetDataArray(), true));




	    $searchprovider = ZPush::GetSearchProvider();
	    $status = SYNC_SEARCHSTATUS_SUCCESS;
	    $searchtotal = 0;
	    $rows = array();

	    // TODO support other searches

	    switch($searchclass) {
		    case "Email":
					try {
						if ($searchprovider->SupportsType(ISearchProvider::SEARCH_MAILBOX)) {
							$backendFolderId = self::$deviceManager->GetBackendIdForFolderId($cpo->GetSearchFolderid());
							$cpo->SetSearchFolderid($backendFolderId);
							$rows = $searchprovider->GetMailboxSearchResults($cpo);
						} else {
							$rows = array('searchtotal' => 0);
							$status = SYNC_SEARCHSTATUS_SERVERERROR;
							ZLog::Write(LOGLEVEL_WARN, sprintf("Searchtype '%s' is not supported.", $searchclass));
							self::$topCollector->AnnounceInformation(sprintf("Unsupported type '%s''", $searchclass), true);
						}
					} catch(StatusException $stex) {
						$storestatus = $stex->getCode();
					}
					break;

		    case "GAL":
					//TODO
//			    $rows = $searchprovider->GetGALSearchResults($searchquery, $searchrange, $searchpicture);
					break;
	    }

	    if (isset($rows['range'])) {
		    $searchrange = $rows['range'];
		    unset($rows['range']);
	    }
	    if (isset($rows['searchtotal'])) {
		    $searchtotal = $rows['searchtotal'];
		    unset($rows['searchtotal']);
	    }


	    self::$encoder->startWBXML();
	    self::$encoder->startTag("Find:Find");

	    self::$encoder->startTag("Find:Status");
	      self::$encoder->content($status);
	    self::$encoder->endTag();

	      self::$encoder->startTag("Find:Response");

	        self::$encoder->startTag("ItemOperations:Store");

				    self::$encoder->startTag("Find:Status");
				      self::$encoder->content($status);
				    self::$encoder->endTag();



				    foreach ($rows as $u) {
					    $folderid = self::$deviceManager->GetFolderIdForBackendId($u['folderid']);

					    $tmp = explode(":", $u['longid']);
					    $message = self::$backend->Fetch($u['folderid'], $tmp[1], $cpo);
							
							/** @var SyncMail $message */

					    self::$encoder->startTag("Find:Result");
						    self::$encoder->startTag("FolderType"); //Class
						    self::$encoder->content($u['class']);
						    self::$encoder->endTag();
						    self::$encoder->startTag("ServerEntryId"); //ServerId
						    self::$encoder->content($tmp[1]);
						    self::$encoder->endTag();
						    self::$encoder->startTag("FolderId"); //CollectionId
						    self::$encoder->content($folderid);
						    self::$encoder->endTag();

						    self::$encoder->startTag("Find:Properties");

//							    $message->Encode(self::$encoder);
					      self::$encoder->startTag("POOMMAIL:Subject");
						    self::$encoder->content($message->subject);
						    self::$encoder->endTag();
					      self::$encoder->startTag("POOMMAIL:DateReceived");
						    self::$encoder->content(gmstrftime("%Y-%m-%dT%H:%M:%S.000Z", $message->datereceived));
						    self::$encoder->endTag();
					      self::$encoder->startTag("POOMMAIL:DisplayTo");
						    self::$encoder->content($message->displayto);
						    self::$encoder->endTag();
						    self::$encoder->startTag("Find:DisplayCc");
						    self::$encoder->content(implode(', ', $message->cc));
						    self::$encoder->endTag();
						    self::$encoder->startTag("Find:DisplayBcc");
						    self::$encoder->content("");
						    self::$encoder->endTag();
						    self::$encoder->startTag("POOMMAIL:Importance");
						    self::$encoder->content($message->importance);
						    self::$encoder->endTag();
						    self::$encoder->startTag("POOMMAIL:Read");
						    self::$encoder->content($message->read);
						    self::$encoder->endTag();
					      self::$encoder->startTag("POOMMAIL2:IsDraft");
					      self::$encoder->content("0");
					      self::$encoder->endTag();
						    self::$encoder->startTag("Find:Preview");
						    self::$encoder->content("test 123 preview...");
						    self::$encoder->endTag();
						    self::$encoder->startTag("Find:HasAttachments");
						    self::$encoder->content("0");
						    self::$encoder->endTag();
						    self::$encoder->startTag("POOMMAIL:From");
						    self::$encoder->content($message->from);
						    self::$encoder->endTag();




					      self::$encoder->endTag();//properties

					    self::$encoder->endTag();//result

				    }

				    self::$encoder->startTag("Find:Range");
				    self::$encoder->content($searchrange);
				    self::$encoder->endTag();

				    self::$encoder->startTag("Find:Total");
				    self::$encoder->content($searchtotal);
				    self::$encoder->endTag();

					self::$encoder->endTag(); // Store

	      self::$encoder->endTag(); // Response

	    self::$encoder->endTag(); //Find

	    return true;
    }
}
