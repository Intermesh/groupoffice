<?php
/** vim: set expandtab softtabstop=4 tabstop=4 shiftwidth=4: */
// +----------------------------------------------------------------------+
// | PHP Version 5 and 7                                                  |
// +----------------------------------------------------------------------+
// | Copyright (c) 1997-2017 Jon Parise and Chuck Hagenbuch               |
// | All rights reserved.                                                 |
// |                                                                      |
// | Redistribution and use in source and binary forms, with or without   |
// | modification, are permitted provided that the following conditions   |
// | are met:                                                             |
// |                                                                      |
// | 1. Redistributions of source code must retain the above copyright    |
// |    notice, this list of conditions and the following disclaimer.     |
// |                                                                      |
// | 2. Redistributions in binary form must reproduce the above copyright |
// |    notice, this list of conditions and the following disclaimer in   |
// |    the documentation and/or other materials provided with the        |
// |    distribution.                                                     |
// |                                                                      |
// | THIS SOFTWARE IS PROVIDED BY THE COPYRIGHT HOLDERS AND CONTRIBUTORS  |
// | "AS IS" AND ANY EXPRESS OR IMPLIED WARRANTIES, INCLUDING, BUT NOT    |
// | LIMITED TO, THE IMPLIED WARRANTIES OF MERCHANTABILITY AND FITNESS    |
// | FOR A PARTICULAR PURPOSE ARE DISCLAIMED. IN NO EVENT SHALL THE       |
// | COPYRIGHT HOLDER OR CONTRIBUTORS BE LIABLE FOR ANY DIRECT, INDIRECT, |
// | INCIDENTAL, SPECIAL, EXEMPLARY, OR CONSEQUENTIAL DAMAGES (INCLUDING, |
// | BUT NOT LIMITED TO, PROCUREMENT OF SUBSTITUTE GOODS OR SERVICES;     |
// | LOSS OF USE, DATA, OR PROFITS; OR BUSINESS INTERRUPTION) HOWEVER     |
// | CAUSED AND ON ANY THEORY OF LIABILITY, WHETHER IN CONTRACT, STRICT   |
// | LIABILITY, OR TORT (INCLUDING NEGLIGENCE OR OTHERWISE) ARISING IN    |
// | ANY WAY OUT OF THE USE OF THIS SOFTWARE, EVEN IF ADVISED OF THE      |
// | POSSIBILITY OF SUCH DAMAGE.                                          |
// +----------------------------------------------------------------------+
// | Authors: Chuck Hagenbuch <chuck@horde.org>                           |
// |          Jon Parise <jon@php.net>                                    |
// |          Damian Alejandro Fernandez Sosa <damlists@cnba.uba.ar>      |
// +----------------------------------------------------------------------+


/**
 * Z-Push changes
 *
 * removed PEAR dependency by implementing own raiseError()
 *
 * Reference implementation used:
 * http://download.pear.php.net/package/Net_SMTP-1.6.2.tgz
 * https://github.com/pear/Net_SMTP Commit 558b92f5c2ecbb857094a3926a100e51211a08c2 2014/03/09
 *
 *
 */

//require_once 'PEAR.php';
//require_once 'PEAR/Exception.php';

/**
 * Provides an implementation of the SMTP protocol using PEAR's
 * Net_Socket:: class.
 *
 * @package Net_SMTP
 * @author  Chuck Hagenbuch <chuck@horde.org>
 * @author  Jon Parise <jon@php.net>
 * @author  Damian Alejandro Fernandez Sosa <damlists@cnba.uba.ar>
 * @license http://opensource.org/licenses/bsd-license.php BSD-2-Clause
 *
 * @example basic.php   A basic implementation of the Net_SMTP package.
 */
class Net_SMTP
{
    /**
     * The server to connect to.
     *
     * @var string
     */
    public $host = 'localhost';

    /**
     * The port to connect to.
     *
     * @var int
     */
    public $port = 25;

    /**
     * The value to give when sending EHLO or HELO.
     *
     * @var string
     */
    public $localhost = 'localhost';

    /**
     * List of supported authentication methods, in preferential order.
     *
     * @var array
     */
    public $auth_methods = array();

    /**
     * Use SMTP command pipelining (specified in RFC 2920) if the SMTP
     * server supports it.
     *
     * When pipeling is enabled, rcptTo(), mailFrom(), sendFrom(),
     * somlFrom() and samlFrom() do not wait for a response from the
     * SMTP server but return immediately.
     *
     * @var bool
     */
    public $pipelining = false;

    /**
     * Number of pipelined commands.
     *
     * @var int
     */
    protected $_pipelined_commands = 0;

    /**
     * Should debugging output be enabled?
     *
     * @var boolean
     */
    protected $_debug = false;

    /**
     * Debug output handler.
     *
     * @var callback
     */
    protected $_debug_handler = null;

    /**
     * The socket resource being used to connect to the SMTP server.
     *
     * @var resource
     */
    protected $_socket = null;

    /**
     * Array of socket options that will be passed to Net_Socket::connect().
     *
     * @see stream_context_create()
     *
     * @var array
     */
    protected $_socket_options = null;

    /**
     * The socket I/O timeout value in seconds.
     *
     * @var int
     */
    protected $_timeout = 0;

    /**
     * The most recent server response code.
     *
     * @var int
     */
    protected $_code = -1;

    /**
     * The most recent server response arguments.
     *
     * @var array
     */
    protected $_arguments = array();

    /**
     * Stores the SMTP server's greeting string.
     *
     * @var string
     */
    protected $_greeting = null;

    /**
     * Stores detected features of the SMTP server.
     *
     * @var array
     */
    protected $_esmtp = array();

    /**
     * Require verification of SSL certificate used.
     *
     * @var bool
     */
    protected $_verify_peer;

    /**
     * Require verification of peer name
     *
     * @var bool
     */
    protected $_verify_peer_name;

    /**
     * Allow self-signed certificates. Requires verify_peer
     *
     * @var bool
     */
    protected $_allow_self_signed;

    /**
     * Instantiates a new Net_SMTP object, overriding any defaults
     * with parameters that are passed in.
     *
     * If you have SSL support in PHP, you can connect to a server
     * over SSL using an 'ssl://' prefix:
     *
     *   // 465 is a common smtps port.
     *   $smtp = new Net_SMTP('ssl://mail.host.com', 465);
     *   $smtp->connect();
     *
     * @param string  $host       The server to connect to.
     * @param integer $port       The port to connect to.
     * @param string  $localhost  The value to give when sending EHLO or HELO.
     * @param boolean $pipeling   Use SMTP command pipelining
     * @param integer $timeout    Socket I/O timeout in seconds.
     * @param array   $socket_options Socket stream_context_create() options.
     * @param boolean $verify_peer Require verification of SSL certificate used
     * @param boolean $verify_peer_name Require verification of peer name
     * @param boolean $allow_self_signed Allow self-signed certificates. Requires verify_peer
     */
    public function __construct($host = null, $port = null, $localhost = null,
                                $pipelining = false, $timeout = 0,
                                $socket_options = null,
                                $verify_peer = true, $verify_peer_name = true, $allow_self_signed = false)
    {
        if (isset($host)) {
            $this->host = $host;
        }
        if (isset($port)) {
            $this->port = $port;
        }
        if (isset($localhost)) {
            $this->localhost = $localhost;
        }
        $this->pipelining = $pipelining;

        $this->_socket = new Net_Socket();
        $this->_socket_options = $socket_options;

        // SSL connection, we need to modify the socket_options
        if (strpos($this->host, "ssl://") === 0) {
            if ($this->_socket_options == null)
                $this->_socket_options = array();

            if (!array_key_exists('ssl', $this->_socket_options))
                $this->_socket_options['ssl'] = array();

            $this->_socket_options['ssl']['verify_peer'] = $verify_peer;
            $this->_socket_options['ssl']['allow_self_signed'] = $allow_self_signed;
            // This option was introduced in 5.6
            if (version_compare(phpversion(), "5.6.0", ">="))
                $this->_socket_options['ssl']['verify_peer_name'] = $verify_peer_name;
        }

        $this->_timeout = $timeout;

        // We also need this for use in the STARTTLS command
        $this->_verify_peer = $verify_peer;
        $this->_verify_peer_name = $verify_peer_name;
        $this->_allow_self_signed = $allow_self_signed;

        /* Include the Auth_SASL package.  If the package is available, we
         * enable the authentication methods that depend upon it. */
        $this->setAuthMethod('CRAM-MD5', array($this, '_authCram_MD5'));
        $this->setAuthMethod('DIGEST-MD5', array($this, '_authDigest_MD5'));

        /* These standard authentication methods are always available. */
        $this->setAuthMethod('LOGIN', array($this, '_authLogin'), false);
        $this->setAuthMethod('PLAIN', array($this, '_authPlain'), false);
    }

    /**
     * Set the socket I/O timeout value in seconds plus microseconds.
     *
     * @param integer $seconds       Timeout value in seconds.
     * @param integer $microseconds  Additional value in microseconds.
     */
    public function setTimeout($seconds, $microseconds = 0)
    {
        return $this->_socket->setTimeout($seconds, $microseconds);
    }

    /**
     * Set the value of the debugging flag.
     *
     * @param boolean $debug  New value for the debugging flag.
     */
    public function setDebug($debug, $handler = null)
    {
        $this->_debug = $debug;
        $this->_debug_handler = $handler;
    }

    /**
     * Write the given debug text to the current debug output handler.
     *
     * @param string $message  Debug message text.
     */
    protected function _debug($message)
    {
        if ($this->_debug) {
            if ($this->_debug_handler) {
                call_user_func_array($this->_debug_handler,
                                     array(&$this, $message));
            } else {
                ZLog::Write(LOGLEVEL_DEBUG, "Net_SMTP DEBUG: ". $message);
            }
        }
    }

    /**
     * Send the given string of data to the server.
     *
     * @param string $data  The string of data to send.
     *
     * @return integer  The number of bytes that were actually written.
     * @throws PEAR_Exception
     */
    protected function _send($data)
    {
        $this->_debug("Send: $data");

        $result = $this->_socket->write($data);
        if ($result === false) {
//            return Net_SMTP::raiseError('Failed to write to socket: ' . $result->getMessage(),
//                                     $result);
            return Net_SMTP::raiseError('Failed to write to socket: ');
        }

        return $result;
    }

    /**
     * Send a command to the server with an optional string of
     * arguments.  A carriage return / linefeed (CRLF) sequence will
     * be appended to each command string before it is sent to the
     * SMTP server - an error will be thrown if the command string
     * already contains any newline characters. Use _send() for
     * commands that must contain newlines.
     *
     * @param string $command  The SMTP command to send to the server.
     * @param string $args     A string of optional arguments to append
     *                         to the command.
     *
     * @return integer  The number of bytes that were actually written.
     * @throws PEAR_Exception
     */
    protected function _put($command, $args = '')
    {
        if (!empty($args)) {
            $command .= ' ' . $args;
        }

        if (strcspn($command, "\r\n") !== strlen($command)) {
            return Net_SMTP::raiseError('Commands cannot contain newlines');
        }

        return $this->_send($command . "\r\n");
    }

    /**
     * Read a reply from the SMTP server.  The reply consists of a response
     * code and a response message.
     *
     * @see getResponse
     *
     * @param mixed $valid  The set of valid response codes.  These
     *                      may be specified as an array of integer
     *                      values or as a single integer value.
     * @param bool $later   Do not parse the response now, but wait
     *                      until the last command in the pipelined
     *                      command group
     *
     * @throws PEAR_Exception
     */
    protected function _parseResponse($valid, $later = false)
    {
        $this->_code = -1;
        $this->_arguments = array();

        if ($later) {
            ++$this->_pipelined_commands;
            return;
        }

        for ($i = 0; $i <= $this->_pipelined_commands; ++$i) {
            while ($line = $this->_socket->readLine()) {
                $this->_debug("Recv: $line");

                /* If we receive an empty line, the connection was closed. */
                if (empty($line)) {
                    $this->disconnect();
                    return Net_SMTP::raiseError('Connection was closed',
//                                            null, PEAR_ERROR_RETURN);
                                            null, 1);
                }

                /* Read the code and store the rest in the arguments array. */
                $code = substr($line, 0, 3);
                $this->_arguments[] = trim(substr($line, 4));

                /* Check the syntax of the response code. */
                if (is_numeric($code)) {
                    $this->_code = (int)$code;
                } else {
                    $this->_code = -1;
                    break;
                }

                /* If this is not a multiline response, we're done. */
                if (substr($line, 3, 1) != '-') {
                    break;
                }
            }
        }

        $this->_pipelined_commands = 0;

        /* Compare the server's response code with the valid code/codes. */
        if ((is_int($valid) && ($this->_code === $valid)) ||
            (is_array($valid) && in_array($this->_code, $valid, true))) {
            return;
        }

        return Net_SMTP::raiseError('Invalid response code received from server',
//                                $this->_code, PEAR_ERROR_RETURN);
                                $this->_code, 1);
    }

    /**
     * Issue an SMTP command and verify its response.
     *
     * @param string $command  The SMTP command string or data.
     * @param mixed $valid     The set of valid response codes.  These
     *                         may be specified as an array of integer
     *                         values or as a single integer value.
     *
     * @throws PEAR_Exception
     */
    public function command($command, $valid)
    {
        //if (PEAR::isError($error = $this->_put($command))) {
        if (($error = $this->_put($command)) === false) {
            return $error;
        }
        //if (PEAR::isError($error = $this->_parseResponse($valid))) {
        if (($error = $this->_parseResponse($valid)) === false) {
            return $error;
        }

        return true;
    }

    /**
     * Return a 2-tuple containing the last response from the SMTP server.
     *
     * @return array  A two-element array: the first element contains the
     *                response code as an integer and the second element
     *                contains the response's arguments as a string.
     */
    public function getResponse()
    {
        return array($this->_code, join("\n", $this->_arguments));
    }

    /**
     * Return the SMTP server's greeting string.
     *
     * @return  string  A string containing the greeting string, or null if a
     *                  greeting has not been received.
     */
    public function getGreeting()
    {
        return $this->_greeting;
    }

    /**
     * Attempt to connect to the SMTP server.
     *
     * @param int $timeout      The timeout value (in seconds) for the
     *                          socket connection attempt.
     * @param bool $persistent  Should a persistent socket connection
     *                          be used?
     *
     * @throws PEAR_Exception
     */
    public function connect($timeout = null, $persistent = false)
    {
        $this->_greeting = null;
        $result = $this->_socket->connect($this->host, $this->port,
                                          $persistent, $timeout,
                                          $this->_socket_options);
        //if (PEAR::isError($result)) {
        if ($result === false) {
//            return Net_SMTP::raiseError('Failed to connect socket: ' .
//                                    $result->getMessage());
            return Net_SMTP::raiseError('Failed to connect socket: ');
        }

        /*
         * Now that we're connected, reset the socket's timeout value for
         * future I/O operations.  This allows us to have different socket
         * timeout values for the initial connection (our $timeout parameter)
         * and all other socket operations.
         */
        if ($this->_timeout > 0) {
            //if (PEAR::isError($error = $this->setTimeout($this->_timeout))) {
            if (($error = $this->setTimeout($this->_timeout)) === false) {
                return $error;
            }
        }

        //if (PEAR::isError($error = $this->_parseResponse(220))) {
        if (($error = $this->_parseResponse(220)) === false) {
            return $error;
        }

        /* Extract and store a copy of the server's greeting string. */
        list(, $this->_greeting) = $this->getResponse();

        //if (PEAR::isError($error = $this->_negotiate())) {
        if (($error = $this->_negotiate()) === false) {
            return $error;
        }

        return true;
    }

    /**
     * Attempt to disconnect from the SMTP server.
     *
     * @throws PEAR_Exception
     */
    public function disconnect()
    {
        //if (PEAR::isError($error = $this->_put('QUIT'))) {
        if (($error = $this->_put('QUIT')) === false) {
            return $error;
        }
        //if (PEAR::isError($error = $this->_parseResponse(221))) {
        if (($error = $this->_parseResponse(221)) === false) {
            return $error;
        }
        //if (PEAR::isError($error = $this->_socket->disconnect())) {
        if (($error = $this->_socket->disconnect()) === false) {
            return Net_SMTP::raiseError('Failed to disconnect socket: ' .
                                    $error->getMessage());
        }

        return true;
    }

    /**
     * Attempt to send the EHLO command and obtain a list of ESMTP
     * extensions available, and failing that just send HELO.
     *
     * @throws PEAR_Exception
     */
    protected function _negotiate()
    {
        //if (PEAR::isError($error = $this->_put('EHLO', $this->localhost))) {
        if (($error = $this->_put('EHLO', $this->localhost)) === false) {
            return $error;
        }

        //if (PEAR::isError($this->_parseResponse(250))) {
        if (($this->_parseResponse(250)) === false) {
            /* If the EHLO failed, try the simpler HELO command. */
            //if (PEAR::isError($error = $this->_put('HELO', $this->localhost))) {
            if (($error = $this->_put('HELO', $this->localhost)) === false) {
                return $error;
            }
            //if (PEAR::isError($this->_parseResponse(250))) {
            if (($this->_parseResponse(250)) === false) {
                return Net_SMTP::raiseError('HELO was not accepted: ', $this->_code,
//                                        PEAR_ERROR_RETURN);
                                        1);
            }

            return true;
        }

        foreach ($this->_arguments as $argument) {
            $verb = strtok($argument, ' ');
            $arguments = substr($argument, strlen($verb) + 1,
                                strlen($argument) - strlen($verb) - 1);
            $this->_esmtp[$verb] = $arguments;
        }

        if (!isset($this->_esmtp['PIPELINING'])) {
            $this->pipelining = false;
        }

        return true;
    }

    /**
     * Returns the name of the best authentication method that the server
     * has advertised.
     *
     * @return mixed    Returns a string containing the name of the best
     *                  supported authentication method.
     * @throws PEAR_Exception
     */
    protected function _getBestAuthMethod()
    {
        $available_methods = explode(' ', $this->_esmtp['AUTH']);

        foreach ($this->auth_methods as $method => $callback) {
            if (in_array($method, $available_methods)) {
                return $method;
            }
        }

        return Net_SMTP::raiseError('No supported authentication methods',
//                                null, PEAR_ERROR_RETURN);
                                null, 1);
    }

    /**
     * Attempt to do SMTP authentication.
     *
     * @param string $uid     The userid to authenticate as.
     * @param string $pwd     The password to authenticate with.
     * @param string $method  The requested authentication method.  If none is
     *                        specified, the best supported method will be
     *                        used.
     * @param bool $tls       Flag indicating whether or not TLS should be
     *                        attempted.
     * @param string $authz   An optional authorization identifier.  If
     *                        specified, this identifier will be used as the
     *                        authorization proxy.
     *
     * @throws PEAR_Exception
     */
    public function auth($uid, $pwd, $method = '', $tls = true, $authz = '')
    {
        /* We can only attempt a TLS connection if one has been requested,
         * we're running PHP 5.1.0 or later, have access to the OpenSSL
         * extension, are connected to an SMTP server which supports the
         * STARTTLS extension, and aren't already connected over a secure
         * (SSL) socket connection. */
        if ($tls && version_compare(PHP_VERSION, '5.1.0', '>=') &&
            extension_loaded('openssl') && isset($this->_esmtp['STARTTLS']) &&
            strncasecmp($this->host, 'ssl://', 6) !== 0) {
            /* Start the TLS connection attempt. */
            //if (PEAR::isError($result = $this->_put('STARTTLS'))) {
            if (($result = $this->_put('STARTTLS')) === false) {
                return $result;
            }
            //if (PEAR::isError($result = $this->_parseResponse(220))) {
            if (($result = $this->_parseResponse(220)) === false) {
                return $result;
            }
            //if (PEAR::isError($result = $this->_socket->enableCrypto(true, STREAM_CRYPTO_METHOD_TLS_CLIENT))) {
            if (($result = $this->_socket->enableCrypto(true, STREAM_CRYPTO_METHOD_TLS_CLIENT, $this->_verify_peer, $this->_verify_peer_name, $this->_allow_self_signed)) === false) {
                return $result;
            } elseif ($result !== true) {
                return Net_SMTP::raiseError('STARTTLS failed');
            }

            /* Send EHLO again to recieve the AUTH string from the
             * SMTP server. */
            $this->_negotiate();
        }

        if (empty($this->_esmtp['AUTH'])) {
            return Net_SMTP::raiseError('SMTP server does not support authentication');
        }

        /* If no method has been specified, get the name of the best
         * supported method advertised by the SMTP server. */
        if (empty($method)) {
            //if (PEAR::isError($method = $this->_getBestAuthMethod())) {
            if (($method = $this->_getBestAuthMethod()) === false) {
                return $method;
            }
        } else {
            $method = strtoupper($method);
        }

        if (!array_key_exists($method, $this->auth_methods)) {
            return Net_SMTP::raiseError("$method is not a supported authentication method");
        }

        if (!is_callable($this->auth_methods[$method], false)) {
            return Net_SMTP::raiseError("$method authentication method cannot be called");
        }

        if (is_array($this->auth_methods[$method])) {
            list($object, $method) = $this->auth_methods[$method];
            $result = $object->{$method}($uid, $pwd, $authz, $this);
        } else {
            $func =  $this->auth_methods[$method];
            $result = $func($uid, $pwd, $authz, $this);
         }

        /* If an error was encountered, return the PEAR_Error object. */
        //if (PEAR::isError($result)) {
        if ($result === false) {
            return $result;
        }

        return true;
    }

    /**
     * Add a new authentication method.
     *
     * @param string $name     The authentication method name (e.g. 'PLAIN')
     * @param mixed $callback  The authentication callback (given as the name
     *                         of a function or as an (object, method name)
     *                         array).
     * @param bool $prepend    Should the new method be prepended to the list
     *                         of available methods?  This is the default
     *                         behavior, giving the new method the highest
     *                         priority.
     *
     * @throws PEAR_Exception
     */
    public function setAuthMethod($name, $callback, $prepend = true)
    {
        if (!is_string($name)) {
            return Net_SMTP::raiseError('Method name is not a string');
        }

        if (!is_string($callback) && !is_array($callback)) {
            return Net_SMTP::raiseError('Method callback must be string or array');
        }

        if (is_array($callback) &&
            (!is_object($callback[0]) || !is_string($callback[1]))) {
            return Net_SMTP::raiseError('Bad mMethod callback array');
        }

        if ($prepend) {
            $this->auth_methods = array_merge(array($name => $callback),
                                              $this->auth_methods);
        } else {
            $this->auth_methods[$name] = $callback;
        }

        return true;
    }

    /**
     * Authenticates the user using the DIGEST-MD5 method.
     *
     * @param string $uid    The userid to authenticate as.
     * @param string $pwd    The password to authenticate with.
     * @param string $authz  The optional authorization proxy identifier.
     *
     * @throws PEAR_Exception
     */
    protected function _authDigest_MD5($uid, $pwd, $authz = '')
    {
        //if (PEAR::isError($error = $this->_put('AUTH', 'DIGEST-MD5'))) {
        if (($error = $this->_put('AUTH', 'DIGEST-MD5')) === false) {
            return $error;
        }

        /* 334: Continue authentication request */
        //if (PEAR::isError($error = $this->_parseResponse(334))) {
        if (($error = $this->_parseResponse(334)) === false) {
            /* 503: Error: already authenticated */
            if ($this->_code === 503) {
                return true;
            }
            return $error;
        }

        $challenge = base64_decode($this->_arguments[0]);
        $digest = Auth_SASL::factory('digest-md5');
        $auth_str = base64_encode($digest->getResponse($uid, $pwd, $challenge,
                                                       $this->host, "smtp",
                                                       $authz));

        //if (PEAR::isError($error = $this->_put($auth_str))) {
        if (($error = $this->_put($auth_str)) === false) {
            return $error;
        }

        /* 334: Continue authentication request */
        //if (PEAR::isError($error = $this->_parseResponse(334))) {
        if (($error = $this->_parseResponse(334)) === false) {
            return $error;
        }

        /* We don't use the protocol's third step because SMTP doesn't
         * allow subsequent authentication, so we just silently ignore
         * it. */
        //if (PEAR::isError($error = $this->_put(''))) {
        if (($error = $this->_put('')) === false) {
            return $error;
        }

        /* 235: Authentication successful */
        //if (PEAR::isError($error = $this->_parseResponse(235))) {
        if (($error = $this->_parseResponse(235)) === false) {
            return $error;
        }

        return true;
    }

    /**
     * Authenticates the user using the CRAM-MD5 method.
     *
     * @param string $uid    The userid to authenticate as.
     * @param string $pwd    The password to authenticate with.
     * @param string $authz  The optional authorization proxy identifier.
     *
     * @throws PEAR_Exception
     */
    protected function _authCRAM_MD5($uid, $pwd, $authz = '')
    {
        //if (PEAR::isError($error = $this->_put('AUTH', 'CRAM-MD5'))) {
        if (($error = $this->_put('AUTH', 'CRAM-MD5')) === false) {
            return $error;
        }

        /* 334: Continue authentication request */
        //if (PEAR::isError($error = $this->_parseResponse(334))) {
        if (($error = $this->_parseResponse(334)) === false) {
            /* 503: Error: already authenticated */
            if ($this->_code === 503) {
                return true;
            }
            return $error;
        }

        $challenge = base64_decode($this->_arguments[0]);
        $cram = Auth_SASL::factory('cram-md5');
        $auth_str = base64_encode($cram->getResponse($uid, $pwd, $challenge));

        //if (PEAR::isError($error = $this->_put($auth_str))) {
        if (($error = $this->_put($auth_str)) === false) {
            return $error;
        }

        /* 235: Authentication successful */
        //if (PEAR::isError($error = $this->_parseResponse(235))) {
        if (($error = $this->_parseResponse(235)) === false) {
            return $error;
        }

        return true;
    }

    /**
     * Authenticates the user using the LOGIN method.
     *
     * @param string $uid    The userid to authenticate as.
     * @param string $pwd    The password to authenticate with.
     * @param string $authz  The optional authorization proxy identifier.
     *
     * @throws PEAR_Exception
     */
    protected function _authLogin($uid, $pwd, $authz = '')
    {
        //if (PEAR::isError($error = $this->_put('AUTH', 'LOGIN'))) {
        if (($error = $this->_put('AUTH', 'LOGIN')) === false) {
            return $error;
        }

        /* 334: Continue authentication request */
        //if (PEAR::isError($error = $this->_parseResponse(334))) {
        if (($error = $this->_parseResponse(334)) === false) {
            /* 503: Error: already authenticated */
            if ($this->_code === 503) {
                return true;
            }
            return $error;
        }

        //if (PEAR::isError($error = $this->_put(base64_encode($uid)))) {
        if (($error = $this->_put(base64_encode($uid))) === false) {
            return $error;
        }

        /* 334: Continue authentication request */
        //if (PEAR::isError($error = $this->_parseResponse(334))) {
        if (($error = $this->_parseResponse(334)) === false) {
            return $error;
        }

        //if (PEAR::isError($error = $this->_put(base64_encode($pwd)))) {
        if (($error = $this->_put(base64_encode($pwd))) === false) {
            return $error;
        }

        /* 235: Authentication successful */
        //if (PEAR::isError($error = $this->_parseResponse(235))) {
        if (($error = $this->_parseResponse(235)) === false) {
            return $error;
        }

        return true;
    }

    /**
     * Authenticates the user using the PLAIN method.
     *
     * @param string $uid    The userid to authenticate as.
     * @param string $pwd    The password to authenticate with.
     * @param string $authz  The optional authorization proxy identifier.
     *
     * @throws PEAR_Exception
     */
    protected function _authPlain($uid, $pwd, $authz = '')
    {
        //if (PEAR::isError($error = $this->_put('AUTH', 'PLAIN'))) {
        if (($error = $this->_put('AUTH', 'PLAIN')) === false) {
            return $error;
        }
        /* 334: Continue authentication request */
        //if (PEAR::isError($error = $this->_parseResponse(334))) {
        if (($error = $this->_parseResponse(334)) === false) {
            /* 503: Error: already authenticated */
            if ($this->_code === 503) {
                return true;
            }
            return $error;
        }

        $auth_str = base64_encode($authz . chr(0) . $uid . chr(0) . $pwd);

        //if (PEAR::isError($error = $this->_put($auth_str))) {
        if (($error = $this->_put($auth_str)) === false) {
            return $error;
        }

        /* 235: Authentication successful */
        //if (PEAR::isError($error = $this->_parseResponse(235))) {
        if (($error = $this->_parseResponse(235)) === false) {
            return $error;
        }

        return true;
    }

    /**
     * Send the HELO command.
     *
     * @param string The domain name to say we are.
     *
     * @throws PEAR_Exception
     */
    public function helo($domain)
    {
        //if (PEAR::isError($error = $this->_put('HELO', $domain))) {
        if (($error = $this->_put('HELO', $domain)) === false) {
            return $error;
        }
        //if (PEAR::isError($error = $this->_parseResponse(250))) {
        if (($error = $this->_parseResponse(250)) === false) {
            return $error;
        }

        return true;
    }

    /**
     * Return the list of SMTP service extensions advertised by the server.
     *
     * @return array The list of SMTP service extensions.
     */
    public function getServiceExtensions()
    {
        return $this->_esmtp;
    }

    /**
     * Send the MAIL FROM: command.
     *
     * @param string $sender  The sender (reverse path) to set.
     * @param string $params  String containing additional MAIL parameters,
     *                        such as the NOTIFY flags defined by RFC 1891
     *                        or the VERP protocol.
     *
     * @throws PEAR_Exception
     */
    public function mailFrom($sender, $params = null)
    {
        $args = "FROM:<$sender>";
        if (is_string($params) && strlen($params)) {
            $args .= ' ' . $params;
        }

        //if (PEAR::isError($error = $this->_put('MAIL', $args))) {
        if (($error = $this->_put('MAIL', $args)) === false) {
            return $error;
        }
        //if (PEAR::isError($error = $this->_parseResponse(250, $this->pipelining))) {
        if (($error = $this->_parseResponse(250, $this->pipelining)) === false) {
            return $error;
        }

        return true;
    }

    /**
     * Send the RCPT TO: command.
     *
     * @param string $recipient  The recipient (forward path) to add.
     * @param string $params     String containing additional RCPT parameters,
     *                           such as the NOTIFY flags defined by RFC 1891.
     *
     * @throws PEAR_Exception
     */
    public function rcptTo($recipient, $params = null)
    {
        $args = "TO:<$recipient>";
        if (is_string($params) && strlen($params)) {
            $args .= ' ' . $params;
        }

        //if (PEAR::isError($error = $this->_put('RCPT', $args))) {
        if (($error = $this->_put('RCPT', $args)) === false) {
            return $error;
        }
        //if (PEAR::isError($error = $this->_parseResponse(array(250, 251), $this->pipelining))) {
        if (($error = $this->_parseResponse(array(250, 251), $this->pipelining)) === false) {
            return $error;
        }

        return true;
    }

    /**
     * Quote the data so that it meets SMTP standards.
     *
     * This is provided as a separate public function to facilitate
     * easier overloading for the cases where it is desirable to
     * customize the quoting behavior.
     *
     * @param string &$data  The message text to quote. The string must be
     *                       passed by reference, and the text will be
     *                       modified in place.
     */
    public function quotedata(&$data)
    {
        /* Because a single leading period (.) signifies an end to the
         * data, legitimate leading periods need to be "doubled" ('..').
         * Also: change Unix (\n) and Mac (\r) linefeeds into CRLF's
         * (\r\n). */
        $data = preg_replace(
            array('/^\./m', '/(?:\r\n|\n|\r(?!\n))/'),
            array('..', "\r\n"),
            $data
        );
    }

    /**
     * Send the DATA command.
     *
     * @param mixed $data      The message data, either as a string or an open
     *                         file resource.
     * @param string $headers  The message headers. If $headers is provided,
     *                         $data is assumed to contain only body data.
     *
     * @throws PEAR_Exception
     */
    public function data($data, $headers = null)
    {
        /* Verify that $data is a supported type. */
        if (!is_string($data) && !is_resource($data)) {
            return Net_SMTP::raiseError('Expected a string or file resource');
        }

        /* Start by considering the size of the optional headers string.  We
         * also account for the addition 4 character "\r\n\r\n" separator
         * sequence. */
        $size = is_null($headers) ? 0 : strlen($headers) + 4;

        if (is_resource($data)) {
            $stat = fstat($data);
            if ($stat === false) {
                return Net_SMTP::raiseError('Failed to get file size');
            }
            $size += $stat['size'];
        } else {
            $size += strlen($data);
        }

        /* RFC 1870, section 3, subsection 3 states "a value of zero indicates
         * that no fixed maximum message size is in force".  Furthermore, it
         * says that if "the parameter is omitted no information is conveyed
         * about the server's fixed maximum message size". */
        $limit = isset($this->_esmtp['SIZE']) ? $this->_esmtp['SIZE'] : 0;
        if ($limit > 0 && $size >= $limit) {
            $this->disconnect();
            return Net_SMTP::raiseError('Message size exceeds server limit');
        }

        /* Initiate the DATA command. */
        //if (PEAR::isError($error = $this->_put('DATA'))) {
        if (($error = $this->_put('DATA')) === false) {
            return $error;
        }
        //if (PEAR::isError($error = $this->_parseResponse(354))) {
        if (($error = $this->_parseResponse(354)) === false) {
            return $error;
        }

        /* If we have a separate headers string, send it first. */
        if (!is_null($headers)) {
            $this->quotedata($headers);
            //if (PEAR::isError($result = $this->_send($headers . "\r\n\r\n"))) {
            if (($result = $this->_send($headers . "\r\n\r\n")) === false) {
                return $result;
            }
        }

        /* Now we can send the message body data. */
        if (is_resource($data)) {
            /* Stream the contents of the file resource out over our socket
             * connection, line by line.  Each line must be run through the
             * quoting routine. */
            while (strlen($line = fread($data, 8192)) > 0) {
                /* If the last character is an newline, we need to grab the
                 * next character to check to see if it is a period. */
                while (!feof($data)) {
                    $char = fread($data, 1);
                    $line .= $char;
                    if ($char != "\n") {
                        break;
                    }
                }
                $this->quotedata($line);
                //if (PEAR::isError($result = $this->_send($line))) {
                if (($result = $this->_send($line)) === false) {
                    return $result;
                }
            }
        } else {
            /* Break up the data by sending one chunk (up to 512k) at a time.
             * This approach reduces our peak memory usage. */
            for ($offset = 0; $offset < $size;) {
                $end = $offset + 512000;

                /* Ensure we don't read beyond our data size or span multiple
                 * lines.  quotedata() can't properly handle character data
                 * that's split across two line break boundaries. */
                if ($end >= $size) {
                    $end = $size;
                } else {
                    for (; $end < $size; $end++) {
                        if ($data[$end] != "\n") {
                            break;
                        }
                    }
                }

                /* Extract our chunk and run it through the quoting routine. */
                $chunk = substr($data, $offset, $end - $offset);
                $this->quotedata($chunk);

                /* If we run into a problem along the way, abort. */
                //if (PEAR::isError($result = $this->_send($chunk))) {
                if (($result = $this->_send($chunk)) === false) {
                    return $result;
                }

                /* Advance the offset to the end of this chunk. */
                $offset = $end;
            }
        }

        /* Finally, send the DATA terminator sequence. */
        //if (PEAR::isError($result = $this->_send("\r\n.\r\n"))) {
        if (($result = $this->_send("\r\n.\r\n")) === false) {
            return $result;
        }

        /* Verify that the data was successfully received by the server. */
        //if (PEAR::isError($error = $this->_parseResponse(250, $this->pipelining))) {
        if (($error = $this->_parseResponse(250, $this->pipelining)) === false) {
            return $error;
        }

        return true;
    }

    /**
     * Send the SEND FROM: command.
     *
     * @param string $path  The reverse path to send.
     *
     * @throws PEAR_Exception
     */
    public function sendFrom($path)
    {
        //if (PEAR::isError($error = $this->_put('SEND', "FROM:<$path>"))) {
        if (($error = $this->_put('SEND', "FROM:<$path>")) === false) {
            return $error;
        }
        //if (PEAR::isError($error = $this->_parseResponse(250, $this->pipelining))) {
        if (($error = $this->_parseResponse(250, $this->pipelining)) === false) {
            return $error;
        }

        return true;
    }

    /**
     * Send the SOML FROM: command.
     *
     * @param string $path  The reverse path to send.
     *
     * @throws PEAR_Exception
     */
    public function somlFrom($path)
    {
        //if (PEAR::isError($error = $this->_put('SOML', "FROM:<$path>"))) {
        if (($error = $this->_put('SOML', "FROM:<$path>")) === false) {
            return $error;
        }
        //if (PEAR::isError($error = $this->_parseResponse(250, $this->pipelining))) {
        if (($error = $this->_parseResponse(250, $this->pipelining)) === false) {
            return $error;
        }

        return true;
    }

    /**
     * Send the SAML FROM: command.
     *
     * @param string $path  The reverse path to send.
     *
     * @throws PEAR_Exception
     */
    public function samlFrom($path)
    {
        //if (PEAR::isError($error = $this->_put('SAML', "FROM:<$path>"))) {
        if (($error = $this->_put('SAML', "FROM:<$path>")) === false) {
            return $error;
        }
        //if (PEAR::isError($error = $this->_parseResponse(250, $this->pipelining))) {
        if (($error = $this->_parseResponse(250, $this->pipelining)) === false) {
            return $error;
        }

        return true;
    }

    /**
     * Send the RSET command.
     *
     * @throws PEAR_Exception
     */
    public function rset()
    {
        //if (PEAR::isError($error = $this->_put('RSET'))) {
        if (($error = $this->_put('RSET')) === false) {
            return $error;
        }
        //if (PEAR::isError($error = $this->_parseResponse(250, $this->pipelining))) {
        if (($error = $this->_parseResponse(250, $this->pipelining)) === false) {
            return $error;
        }

        return true;
    }

    /**
     * Send the VRFY command.
     *
     * @param string $string  The string to verify
     *
     * @throws PEAR_Exception
     */
    public function vrfy($string)
    {
        /* Note: 251 is also a valid response code */
        //if (PEAR::isError($error = $this->_put('VRFY', $string))) {
        if (($error = $this->_put('VRFY', $string)) === false) {
            return $error;
        }
        //if (PEAR::isError($error = $this->_parseResponse(array(250, 252)))) {
        if (($error = $this->_parseResponse(array(250, 252))) === false) {
            return $error;
        }

        return true;
    }

    /**
     * Send the NOOP command.
     *
     * @throws PEAR_Exception
     */
    public function noop()
    {
        //if (PEAR::isError($error = $this->_put('NOOP'))) {
        if (($error = $this->_put('NOOP')) === false) {
            return $error;
        }
        //if (PEAR::isError($error = $this->_parseResponse(250))) {
        if (($error = $this->_parseResponse(250)) === false) {
            return $error;
        }

        return true;
    }

    /**
     * Z-Push helper for error logging
     * removing PEAR dependency
     *
     * @param  string  debug message
     * @return boolean always false as there was an error
     * @access private
     */
    static function raiseError($message) {
        ZLog::Write(LOGLEVEL_ERROR, "Net_SMTP error: ". $message);
        return false;
    }
}
