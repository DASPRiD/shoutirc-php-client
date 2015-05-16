<?php
namespace ShoutIrc;

/**
 * Response container.
 */
class Response
{
    /**#@+
     * Remote response codes.
     */
    const RCMD_LOGIN_FAILED   = 0x00;
    const RCMD_LOGIN_OK       = 0x01;
    const RCMD_ENABLE_SSL_ACK = 0x03;
    const RCMD_IRCBOT_VERSION = 0x04;
    const RCMD_REQ_LOGOUT_ACK = 0x10;
    const RCMD_REQ_LOGIN_ACK  = 0x11;
    const RCMD_REQ_INCOMING   = 0x12;
    const RCMD_STREAM_INFO    = 0x13;
    const RCMD_FIND_QUERY     = 0x14;
    const RCMD_SONG_INFO      = 0x30;
    const RCMD_USERINFO       = 0x40;
    const RCMD_USERNOTFOUND   = 0x41;
    const RCMD_GENERIC_MSG    = 0xFE;
    const RCMD_GENERIC_ERROR  = 0xFF;
    /**#@-*/

    /**
     * @var int
     */
    protected $code;

    /**
     * @var string
     */
    protected $data;

    /**
     * @param int    $code
     * @param string $data
     */
    public function __construct($code, $data)
    {
        $this->code = $code;
        $this->data = $data;
    }

    /**
     * Gets the response code.
     *
     * @return int
     */
    public function getCode()
    {
        return $this->code;
    }

    /**
     * Gets the data send with the response.
     *
     * @return string
     */
    public function getData()
    {
        return $this->data;
    }
}
