<?php
/***********************************************
* File      :   user_identity.php
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
 * Returns the default email address.
 *
 * @return string
 */
function getDefaultEmailValue($username, $domain) {
    $v = "";

    if (defined('IMAP_DEFAULTFROM')) {
        switch (IMAP_DEFAULTFROM) {
            case 'username':
                $v = $username;
                break;
            case 'domain':
                $v = $domain;
                break;
            case 'ldap':
                $v = getIdentityFromLdap($username, $domain, IMAP_FROM_LDAP_EMAIL, false);
                break;
            case 'sql':
                $v = getIdentityFromSql($username, $domain, IMAP_FROM_SQL_EMAIL, false);
                break;
            case 'passwd':
                $v = getIdentityFromPasswd($username, $domain, 'EMAIL', false);
                break;
            default:
                $v = $username . IMAP_DEFAULTFROM;
                break;
        }
    }

    return $v;
}

/**
 * Returns the default value for "From"
 *
 * @return string
 */
function getDefaultFromValue($username, $domain) {
    $v = "";

    if (defined('IMAP_DEFAULTFROM')) {
        switch (IMAP_DEFAULTFROM) {
            case 'username':
                $v = $username;
                break;
            case 'domain':
                $v = $domain;
                break;
            case 'ldap':
                $v = getIdentityFromLdap($username, $domain, IMAP_FROM_LDAP_FROM, true);
                break;
            case 'sql':
                $v = getIdentityFromSql($username, $domain, IMAP_FROM_SQL_FROM, true);
                break;
            case 'passwd':
                $v = getIdentityFromPasswd($username, $domain, 'FROM', true);
                break;
            default:
                $v = $username . IMAP_DEFAULTFROM;
                break;
        }
    }

    return $v;
}

/**
 * Return the default value for "FullName"
 *
 * @param string     $username          Username
 * @return string
 */
function getDefaultFullNameValue($username, $domain) {
    $v = $username;

    if (defined('IMAP_DEFAULTFROM')) {
        switch (IMAP_DEFAULTFROM) {
            case 'ldap':
                $v = getIdentityFromLdap($username, $domain, IMAP_FROM_LDAP_FULLNAME, false);
                break;
            case 'sql':
                $v = getIdentityFromSql($username, $domain, IMAP_FROM_SQL_FULLNAME, false);
                break;
            case 'passwd':
                $v = getIdentityFromPasswd($username, $domain, 'FULLNAME', false);
                break;
        }
    }

    return $v;
}

/**
 * Generate the "From"/"FullName" value stored in a LDAP server
 *
 * @params string   $username    username value
 * @params string   $domain      domain value
 * @params string   $identity    pattern to fill with ldap values
 * @params boolean  $encode      if the result should be encoded as a header
 * @return string
 */
function getIdentityFromLdap($username, $domain, $identity, $encode = true) {
    $ret_value = $username;

    $ldap_conn = null;
    try {
        if (defined('IMAP_FROM_LDAP_SERVER_URI')) {
            $ldap_conn = ldap_connect(IMAP_FROM_LDAP_SERVER_URI);

            if ($ldap_conn) {
                ZLog::Write(LOGLEVEL_DEBUG, sprintf("BackendIMAP->getIdentityFromLdap() - Connected to LDAP"));
                ldap_set_option($ldap_conn, LDAP_OPT_PROTOCOL_VERSION, 3);
                ldap_set_option($ldap_conn, LDAP_OPT_REFERRALS, 0);
                $ldap_bind = ldap_bind($ldap_conn, IMAP_FROM_LDAP_USER, IMAP_FROM_LDAP_PASSWORD);

                if ($ldap_bind) {
                    ZLog::Write(LOGLEVEL_DEBUG, sprintf("BackendIMAP->getIdentityFromLdap() - Authenticated in LDAP"));
                    $filter = str_replace('#username', $username, str_replace('#domain', $domain, IMAP_FROM_LDAP_QUERY));
                    ZLog::Write(LOGLEVEL_DEBUG, sprintf("BackendIMAP->getIdentityFromLdap() - Searching From with filter: %s", $filter));
                    $search = ldap_search($ldap_conn, IMAP_FROM_LDAP_BASE, $filter, unserialize(IMAP_FROM_LDAP_FIELDS));
                    $items = ldap_get_entries($ldap_conn, $search);
                    if ($items['count'] > 0) {
                        $ret_value = $identity;
                        ZLog::Write(LOGLEVEL_DEBUG, sprintf("BackendIMAP->getIdentityFromLdap() - Found entry in LDAP. Generating From"));
                        // We get the first object. It's your responsability to make the query unique
                        foreach (unserialize(IMAP_FROM_LDAP_FIELDS) as $field) {
                            $ret_value = str_replace('#'.$field, $items[0][$field][0], $ret_value);
                        }
                        if ($encode) {
                            $ret_value = encodeFrom($ret_value);
                        }
                    }
                    else {
                        ZLog::Write(LOGLEVEL_DEBUG, sprintf("BackendIMAP->getIdentityFromLdap() - No entry found in LDAP"));
                    }
                }
                else {
                    ZLog::Write(LOGLEVEL_DEBUG, sprintf("BackendIMAP->getIdentityFromLdap() - Not authenticated in LDAP server"));
                }
            }
            else {
                ZLog::Write(LOGLEVEL_DEBUG, sprintf("BackendIMAP->getIdentityFromLdap() - Not connected to LDAP server"));
            }
        }
        else {
            ZLog::Write(LOGLEVEL_WARN, sprintf("BackendIMAP->getIdentityFromLdap() - No LDAP server URI defined"));
        }
    }
    catch(Exception $ex) {
        ZLog::Write(LOGLEVEL_WARN, sprintf("BackendIMAP->getIdentityFromLdap() - Error getting From value from LDAP server: %s", $ex));
    }

    if ($ldap_conn != null) {
        ldap_close($ldap_conn);
    }

    return $ret_value;
}


/**
 * Generate the "From" value stored in a SQL Database
 *
 * @params string   $username    username value
 * @params string   $domain      domain value
 * @return string
 */
function getIdentityFromSql($username, $domain, $identity, $encode = true) {
    $ret_value = $username;

    $dbh = $sth = $record = null;
    try {
        $dbh = new PDO(IMAP_FROM_SQL_DSN, IMAP_FROM_SQL_USER, IMAP_FROM_SQL_PASSWORD, unserialize(IMAP_FROM_SQL_OPTIONS));
        ZLog::Write(LOGLEVEL_DEBUG, sprintf("BackendIMAP->getIdentityFromSql() - Connected to SQL Database"));

        $sql = str_replace('#username', $username, str_replace('#domain', $domain, IMAP_FROM_SQL_QUERY));
        ZLog::Write(LOGLEVEL_DEBUG, sprintf("BackendIMAP->getIdentityFromSql() - Searching From with filter: %s", $sql));
        $sth = $dbh->prepare($sql);
        $sth->execute();
        $record = $sth->fetch(PDO::FETCH_ASSOC);
        if ($record) {
            ZLog::Write(LOGLEVEL_DEBUG, sprintf("BackendIMAP->getIdentityFromSql() - Found entry in SQL Database. Generating From"));
            $ret_value = $identity;
            foreach (unserialize(IMAP_FROM_SQL_FIELDS) as $field) {
                $ret_value = str_replace('#'.$field, $record[$field], $ret_value);
            }
            if ($encode) {
                $ret_value = encodeFrom($ret_value);
            }
        }
        else {
            ZLog::Write(LOGLEVEL_DEBUG, sprintf("BackendIMAP->getIdentityFromSql() - No entry found in SQL Database"));
        }
    }
    catch(PDOException $ex) {
        ZLog::Write(LOGLEVEL_WARN, sprintf("BackendIMAP->getIdentityFromSql() - Error getting From value from SQL Database: %s", $ex));
    }

    $dbh = $sth = $record = null;

    return $ret_value;
}

/**
 * Generate the "From" value from the local posix passwd database
 *
 * @params string   $username    username value
 * @params string   $domain      domain value
 * @return string
 */
function getIdentityFromPasswd($username, $domain, $identity, $encode = true) {
    $ret_value = $username;

    try {
        ZLog::Write(LOGLEVEL_DEBUG, sprintf("BackendIMAP->getIdentityFromPasswd() - Fetching info for user %s", $username));

        $local_user = posix_getpwnam($username);
        if ($local_user) {
            $tmp = $local_user['gecos'];
            $tmp = explode(',', $tmp);
            $name = $tmp[0];
            $email = $tmp[1];
            unset($tmp);

            switch ($identity) {
                case 'EMAIL':
                    $ret_value = sprintf("%s", $email);
                    break;
                case 'FROM':
                    $ret_value = sprintf("%s <%s>", $name, $email);
                    break;
                case 'FULLNAME':
                    $ret_value = sprintf("%s", $name);
                    break;
            }
            if ($encode) {
                $ret_value = encodeFrom($ret_value);
            }
        } else {
            ZLog::Write(LOGLEVEL_DEBUG, sprintf("BackendIMAP->getIdentityFromPasswd() - No entry found in Password database"));
        }
    }
    catch(Exception $ex) {
        ZLog::Write(LOGLEVEL_WARN, sprintf("BackendIMAP->getIdentityFromPasswd() - Error getting From value from passwd database: %s", $ex));
    }

    return $ret_value;
}


/**
 * Encode the From value as Base64
 *
 * @param string    $from   From value
 * @return string
 */
function encodeFrom($from) {
    $items = explode("<", $from);
    $name = trim($items[0]);
    return "=?UTF-8?B?" . base64_encode($name) . "?= <" . $items[1];
}
