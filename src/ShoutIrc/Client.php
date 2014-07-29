<?php
namespace ShoutIrc;

use ShoutIrc\Exception;

class Client
{
    /**
     * Disable SSL.
     */
    const SSL_DISABLE = 0;

    /**
     * Enable SSL, but fall back to unencrypted connection.
     */
    const SSL_ENABLE  = 1;

    /**
     * Require SSL, else fail.
     */
    const SSL_REQUIRE = 3;

    /**#@+
     * Remote command codes.
     */
    const RCMD_LOGIN         = 0x00;
    const RCMD_QUERY_STREAM  = 0x02;
    const RCMD_ENABLE_SSL    = 0x03;
    const RCMD_GET_VERSION   = 0x04;
    const RCMD_REQ_LOGOUT    = 0x10;
    const RCMD_REQ_LOGIN     = 0x11;
    const RCMD_REQ_CURRENT   = 0x12;
    const RCMD_SEND_REQ      = 0x13;
    const RCMD_REQ           = 0x14;
    const RCMD_SEND_DED      = 0x15;
    const RCMD_FIND_RESULTS  = 0x16;
    const RCMD_DOSPAM        = 0x20;
    const RCMD_DIE           = 0x21;
    const RCMD_BROADCAST_MSG = 0x22;
    const RCMD_RESTART       = 0x23;
    const RCMD_REHASH        = 0x26;
    const RCMD_SRC_COUNTDOWN = 0x30;
    const RCMD_SRC_FORCE_OFF = 0x31;
    const RCMD_SRC_FORCE_ON  = 0x32;
    const RCMD_SRC_NEXT      = 0x33;
    const RCMD_SRC_RELOAD    = 0x34;
    const RCMD_SRC_GET_SONG  = 0x35;
    const RCMD_SRC_RATE_SONG = 0x36;
    const RCMD_SRC_STATUS    = 0x37;
    const RCMD_SRC_GET_NAME  = 0x38;
    const RCMD_GETUSERINFO   = 0x40;
    /**#@-*/

    /**
     * @var resource
     */
    protected $socket;

    /**
     * @var int
     */
    protected $userFlags;

    /**
     * @param  string $host
     * @param  int    $port
     * @param  string $username
     * @param  string $password
     * @param  int    $ssl
     * @throws Exception\SocketException
     * @throws Exception\SslException
     * @throws Exception\LoginException
     */
    public function __construct($host, $port, $username, $password, $ssl = self::SSL_DISABLE)
    {
        $errno  = null;
        $errstr = null;

        $this->socket = stream_socket_client(sprintf('tcp://%s:%d', $host, $port), $errno, $errstr, 10);

        if ($this->socket === false) {
            throw new Exception\SocketException($errstr);
        }

        if ($ssl & self::SSL_ENABLE) {
            if (!extension_loaded('openssl')) {
                throw new Exception\SslException('OpenSSL extension is not loaded');
            }

            $response = $this->sendCommand(self::RCMD_ENABLE_SSL);

            if ($response->getCode() === Response::RCMD_ENABLE_SSL_ACK) {
                stream_socket_enable_crypto($this->socket, true, STREAM_CRYPTO_METHOD_TLS_CLIENT);
            } elseif ($ssl & self::SSL_REQUIRE) {
                throw new Exception\SslException('SSL could not be enabled, but is required');
            }
        }

        $response = $this->sendCommand(self::RCMD_LOGIN, sprintf("%s\xFE%s\xFE\x17", $username, $password));

        if ($response->getCode() !== Response::RCMD_LOGIN_OK) {
            throw new Exception\LoginException('Invalid credentials provided');
        }

        list(, $this->userFlags) = array_values(
            unpack('Cnull/Vflags', $response->getData())
        );
    }

    public function __destruct()
    {
        fclose($this->socket);
    }

    /**
     * @return int
     */
    public function getUserFlags()
    {
        return $this->userFlags;
    }

    /**
     * @return StreamInfo
     * @throws Exception\UnexpectedResponseException
     */
    public function queryStream()
    {
        $response = $this->sendCommand(self::RCMD_QUERY_STREAM);

        if ($response->getCode() !== Response::RCMD_STREAM_INFO) {
            throw new Exception\UnexpectedResponseException(sprintf(
                'Received unexpected response: %d',
                $response->getCode()
            ));
        }

        list($title, $dj, $listeners, $peak, $max) = array_values(
            unpack('c64title/c64dj/Vlisteners/Vpeak/Vmax', $response->getData())
        );

        return new StreamInfo($title, $dj, $listeners, $peak, $max);
    }

    /**
     * @param int    $command
     * @param string $data
     */
    protected function sendCommand($command, $data = '')
    {
        fwrite($this->socket, pack('VV', $command, strlen($data)) . $data);
        return $this->receiveResponse();
    }

    /**
     * @return Response
     * @throws Exception\SocketException
     */
    protected function receiveResponse()
    {
        $metaData = fread($this->socket, 8);

        if ($metaData === false || strlen($metaData) < 8) {
            throw new Exception\SocketException('Socket closed unexpectedly');
        }

        list($code, $length) = array_values(unpack('Vcode/Vlength', $metaData));

        $data = $length ? fread($this->socket, $length) : '';

        if ($data === false || strlen($data) < $length) {
            throw new Exception\SocketException('Socket closed unexpectedly');
        }

        return new Response($code, $data);
    }
}
