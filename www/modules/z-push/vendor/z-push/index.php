<?php
/***********************************************
* File      :   index.php
* Project   :   Z-Push
* Descr     :   This is the entry point
*               through which all requests
*               are processed.
*
* Created   :   01.10.2007
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

ob_start(null, 1048576);

// ignore user abortions because this can lead to weird errors - see ZP-239
ignore_user_abort(true);

require_once 'vendor/autoload.php';

if (!defined('ZPUSH_CONFIG')) define('ZPUSH_CONFIG', 'config.php');
include_once(ZPUSH_CONFIG);


    // Attempt to set maximum execution time
    ini_set('max_execution_time', SCRIPT_TIMEOUT);
    set_time_limit(SCRIPT_TIMEOUT);

    try {
        // check config & initialize the basics
        ZPush::CheckConfig();
        Request::Initialize();
        ZLog::Initialize();

        ZLog::Write(LOGLEVEL_DEBUG,"-------- Start");
        ZLog::Write(LOGLEVEL_DEBUG,
                sprintf("cmd='%s' devType='%s' devId='%s' getUser='%s' from='%s' version='%s' method='%s'",
                        Request::GetCommand(), Request::GetDeviceType(), Request::GetDeviceID(), Request::GetGETUser(), Request::GetRemoteAddr(), @constant('ZPUSH_VERSION'), Request::GetMethod() ));

        // always request the authorization header
        if (! Request::HasAuthenticationInfo() || !Request::GetGETUser())
            throw new AuthenticationRequiredException("Access denied. Please send authorisation information");

        ZPush::CheckAdvancedConfig();

        // Process request headers and look for AS headers
        Request::ProcessHeaders();

        // Stop here if this is an OPTIONS request
        if (Request::IsMethodOPTIONS()) {
            RequestProcessor::Authenticate();
            throw new NoPostRequestException("Options request", NoPostRequestException::OPTIONS_REQUEST);
        }

        // Check required GET parameters
        if(Request::IsMethodPOST() && (Request::GetCommandCode() === false || !Request::GetDeviceID() || !Request::GetDeviceType()))
            throw new FatalException("Requested the Z-Push URL without the required GET parameters");

        // Load the backend
        $backend = ZPush::GetBackend();

        // check the provisioning information
        if (PROVISIONING === true && Request::IsMethodPOST() && ZPush::CommandNeedsProvisioning(Request::GetCommandCode()) &&
            ((Request::WasPolicyKeySent() && Request::GetPolicyKey() == 0) || ZPush::GetDeviceManager()->ProvisioningRequired(Request::GetPolicyKey())) &&
            (LOOSE_PROVISIONING === false ||
            (LOOSE_PROVISIONING === true && Request::WasPolicyKeySent())))
            //TODO for AS 14 send a wbxml response
            throw new ProvisioningRequiredException();

        // most commands require an authenticated user
        if (ZPush::CommandNeedsAuthentication(Request::GetCommandCode()))
            RequestProcessor::Authenticate();

        // Do the actual processing of the request
        if (Request::IsMethodGET())
            throw new NoPostRequestException("This is the Z-Push location and can only be accessed by Microsoft ActiveSync-capable devices", NoPostRequestException::GET_REQUEST);

        // Do the actual request
        header(ZPush::GetServerHeader());

        if (RequestProcessor::isUserAuthenticated()) {
            header("X-Z-Push-Version: ". @constant('ZPUSH_VERSION'));
        }

        // announce the supported AS versions (if not already sent to device)
        if (ZPush::GetDeviceManager()->AnnounceASVersion()) {
            $versions = ZPush::GetSupportedProtocolVersions(true);
            ZLog::Write(LOGLEVEL_INFO, sprintf("Announcing latest AS version to device: %s", $versions));
            header("X-MS-RP: ". $versions);
        }

        RequestProcessor::Initialize();
        RequestProcessor::HandleRequest();

        // eventually the RequestProcessor wants to send other headers to the mobile
        foreach (RequestProcessor::GetSpecialHeaders() as $header) {
            ZLog::Write(LOGLEVEL_DEBUG, sprintf("Special header: %s", $header));
            header($header);
        }

        // stream the data
        $len = ob_get_length();
        $data = ob_get_contents();
        ob_end_clean();

        // log amount of data transferred
        // TODO check $len when streaming more data (e.g. Attachments), as the data will be send chunked
        ZPush::GetDeviceManager()->SentData($len);

        // Unfortunately, even though Z-Push can stream the data to the client
        // with a chunked encoding, using chunked encoding breaks the progress bar
        // on the PDA. So the data is de-chunk here, written a content-length header and
        // data send as a 'normal' packet. If the output packet exceeds 1MB (see ob_start)
        // then it will be sent as a chunked packet anyway because PHP will have to flush
        // the buffer.
        if(!headers_sent())
            header("Content-Length: $len");

        // send vnd.ms-sync.wbxml content type header if there is no content
        // otherwise text/html content type is added which might break some devices
        if (!headers_sent() && $len == 0)
            header("Content-Type: application/vnd.ms-sync.wbxml");

        print $data;

        // destruct backend after all data is on the stream
        $backend->Logoff();
    }

    catch (NoPostRequestException $nopostex) {
        if ($nopostex->getCode() == NoPostRequestException::OPTIONS_REQUEST) {
            header(ZPush::GetServerHeader());
            header(ZPush::GetSupportedProtocolVersions());
            header(ZPush::GetSupportedCommands());
            ZLog::Write(LOGLEVEL_INFO, $nopostex->getMessage());
        }
        else if ($nopostex->getCode() == NoPostRequestException::GET_REQUEST) {
            if (Request::GetUserAgent())
                ZLog::Write(LOGLEVEL_INFO, sprintf("User-agent: '%s'", Request::GetUserAgent()));
            if (!headers_sent() && $nopostex->showLegalNotice())
                ZPush::PrintZPushLegal('GET not supported', $nopostex->getMessage());
        }
    }

    catch (Exception $ex) {
        // Extract any previous exception message for logging purpose.
        $exclass = get_class($ex);
        $exception_message = $ex->getMessage();
        if($ex->getPrevious()){
            do {
                $current_exception = $ex->getPrevious();
                $exception_message .= ' -> ' . $current_exception->getMessage();
            } while($current_exception->getPrevious());
        }

        if (Request::GetUserAgent())
            ZLog::Write(LOGLEVEL_INFO, sprintf("User-agent: '%s'", Request::GetUserAgent()));

        ZLog::Write(LOGLEVEL_FATAL, sprintf('Exception: (%s) - %s', $exclass, $exception_message));

        if(!headers_sent()) {
            if ($ex instanceof ZPushException) {
                header('HTTP/1.1 '. $ex->getHTTPCodeString());
                foreach ($ex->getHTTPHeaders() as $h)
                    header($h);
            }
            // something really unexpected happened!
            else
                header('HTTP/1.1 500 Internal Server Error');
        }

        if ($ex instanceof AuthenticationRequiredException) {
            // Only print ZPush legal message for GET requests because
            // some devices send unauthorized OPTIONS requests
            // and don't expect anything in the response body
            if (Request::IsMethodGET()) {
                ZPush::PrintZPushLegal($exclass, sprintf('<pre>%s</pre>',$ex->getMessage()));
            }

            // log the failed login attemt e.g. for fail2ban
            if (defined('LOGAUTHFAIL') && LOGAUTHFAIL != false)
                ZLog::Write(LOGLEVEL_WARN, sprintf("IP: %s failed to authenticate user '%s'",  Request::GetRemoteAddr(), Request::GetAuthUser()? Request::GetAuthUser(): Request::GetGETUser() ));
        }

        // This could be a WBXML problem.. try to get the complete request
        else if ($ex instanceof WBXMLException) {
            ZLog::Write(LOGLEVEL_FATAL, "Request could not be processed correctly due to a WBXMLException. Please report this including the 'WBXML debug data' logged. Be aware that the debug data could contain confidential information.");
        }

        // Try to output some kind of error information. This is only possible if
        // the output had not started yet. If it has started already, we can't show the user the error, and
        // the device will give its own (useless) error message.
        else if (!($ex instanceof ZPushException) || $ex->showLegalNotice()) {
            $cmdinfo = (Request::GetCommand())? sprintf(" processing command <i>%s</i>", Request::GetCommand()): "";
            $extrace = $ex->getTrace();
            $trace = (!empty($extrace))? "\n\nTrace:\n". print_r($extrace,1):"";
            ZPush::PrintZPushLegal($exclass . $cmdinfo, sprintf('<pre>%s</pre>',$ex->getMessage() . $trace));
        }

        // Announce exception to process loop detection
        if (ZPush::GetDeviceManager(false))
            ZPush::GetDeviceManager()->AnnounceProcessException($ex);

        // Announce exception if the TopCollector if available
        ZPush::GetTopCollector()->AnnounceInformation(get_class($ex), true);
    }

    // save device data if the DeviceManager is available
    if (ZPush::GetDeviceManager(false))
        ZPush::GetDeviceManager()->Save();

    // end gracefully
    ZLog::Write(LOGLEVEL_INFO,
            sprintf("cmd='%s' memory='%s/%s' time='%ss' devType='%s' devId='%s' getUser='%s' from='%s' idle='%ss' version='%s' method='%s' httpcode='%s'",
                    Request::GetCommand(), Utils::FormatBytes(memory_get_peak_usage(false)), Utils::FormatBytes(memory_get_peak_usage(true)),
                    number_format(microtime(true) - $_SERVER["REQUEST_TIME_FLOAT"], 2),
                    Request::GetDeviceType(), Request::GetDeviceID(), Request::GetGETUser(), Request::GetRemoteAddr(),
                    RequestProcessor::GetWaitTime(), @constant('ZPUSH_VERSION'), Request::GetMethod(), http_response_code() ));

    ZLog::Write(LOGLEVEL_DEBUG, "-------- End");

