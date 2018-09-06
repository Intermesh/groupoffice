<?php
/***********************************************
* File      :   koesignatures.php
* Project   :   Z-Push
* Descr     :   Holds a list of signatures and signature options for KOE.
*
* Created   :   06.02.2017
*
* Copyright 2007 - 2017 Zarafa Deutschland GmbH
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
*************************************************/

class KoeSignatures {
    private $all = array();
    private $new_message;
    private $replyforward_message;
    private $hash;

    /**
     * Constructor
     *
     * @access public
     */
    public function __construct() {
        $this->GetHash();
    }

    /**
     * Loads data from an array.
     *
     * @param array $data
     *
     * @access public
     * @return void
     */
    public function LoadSignaturesFromData($data) {
        if (isset($data['all'])) {
            foreach ($data['all'] as $id => $signature) {
                $koeSig = KoeSignature::GetSignatureFromArray($id, $signature);
                $this->AddSignature($koeSig);
            }
        }
        if (isset($data['new_message'])) {
            $this->SetNewMessageSignatureId($data['new_message']);
        }
        if (isset($data['replyforward_message'])) {
            $this->SetReplyForwardSignatureId($data['replyforward_message']);
        }
        // update the hash
        $this->GetHash();
    }

    /**
     * Adds a KoeSignature object to the list.
     *
     * @param KoeSignature $koeSig
     *
     * @access public
     * @return void
     */
    public function AddSignature($koeSig) {
        $this->all[$koeSig->id] = $koeSig;
    }

    /**
     * Returns an array of KoeSignature objects.
     *
     * @access public
     * @return array
     */
    public function GetSignatures() {
        return $this->all;
    }

    /**
     * Returns a KoeSignature signature object or null if id is not available.
     *
     * @param KoeSignature $koeSig
     *
     * @access public
     * @return KoeSignature | null
     */
    public function GetSignature($id) {
        return (isset($this->all[$id]) ? $this->all[$id] : null);
    }

    /**
     * Sets the KoeSignature id to be used for new messages.
     * The id is not verified if present.
     *
     * @param string $id
     *
     * @access public
     * @return void
     */
    public function SetNewMessageSignatureId($id) {
        $this->new_message = $id;
    }

    /**
     * Gets the KoeSignature id to be used for new messages.
     *
     * @access public
     * @return string | null
     */
    public function GetNewMessageSignatureId() {
        return $this->new_message;
    }

    /**
     * Sets the KoeSignature id to be used when replying or forwarding.
     * The id is not verified if present.
     *
     * @param string $id
     *
     * @access public
     * @return void
     */
    public function SetReplyForwardSignatureId($id) {
        $this->replyforward_message = $id;
    }

    /**
     * Gets the KoeSignature id to be used when replying or forwarding.
     *
     * @access public
     * @return string | null
     */
    public function GetReplyForwardSignatureId() {
        return $this->replyforward_message;
    }

    /**
     * Returns the hash of the currently loaded data.
     *
     * @access public
     * @return string
     */
    public function GetHash() {
        $this->hash = sha1(json_encode($this->all).$this->new_message.$this->replyforward_message);
        return $this->hash;
    }
}

/**
 * Helper class holding a signature.
 */
class KoeSignature {
    public $id;
    public $name;
    public $content;
    public $isHTML;

    /**
     * Creates a new KoeSignature object from a data array.
     *
     * @param string $id
     * @param array $data
     *
     * @access public
     * @return KoeSignature
     */
    public static function GetSignatureFromArray($id, array $data) {
        $sig = new KoeSignature();
        $sig->id = $id;
        $sig->name = $data['name'];
        $sig->content = $data['content'];
        $sig->isHTML = (bool) $data['isHTML'];
        return $sig;
    }
}