<?php
namespace ShoutIrc;

/**
 * Simple client for ShoutIRC.
 *
 * This client supports all commands but those which require to actively become
 * the current DJ.
 */
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
    const RCMD_SRC_RELAY     = 0x39;
    const RCMD_SRC_GET_SONG_INFO = 0x3A;
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

        $response = $this->sendCommand(self::RCMD_LOGIN, sprintf("%s\xFE%s\xFE\x18", $username, $password));

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
     * Gets flags of the user you are authenticated with.
     *
     * @return int
     */
    public function getUserFlags()
    {
        return $this->userFlags;
    }

    /**
     * Queries for stream informations.
     *
     * @return SongInfo
     * @throws Exception\UnexpectedResponseException
     */
    public function querySong()
    {
        $response = $this->sendCommand(self::RCMD_SRC_GET_SONG_INFO);
        if ($response->getCode() !== Response::RCMD_SONG_INFO) {
            throw Exception\UnexpectedResponseException::fromResponse($response);
        }


        list($file_id, $fn, $artist, $album, $title, $genre, $songLen, $is_request, $requested_by, $playback_position, $playback_length) = array_values(
            unpack('Ifile_id/a1024fn/a256artist/a256album/a256title/a128genre/IsongLen/Vis_request/a64requested_by/Vplayback_position/Vplayback_length', $response->getData())
        );

        return new SongInfo(
            $file_id,
            rtrim($fn, "\0"),
            rtrim($artist, "\0"),
            rtrim($album, "\0"),
            rtrim($title, "\0"),
            rtrim($genre, "\0"),
            $songLen,
            $is_request,
            rtrim($requested_by, "\0"),
            $playback_position,
            $playback_length
        );
    }
    /**
     * Queries for stream informations.
     *
     * @return StreamInfo
     * @throws Exception\UnexpectedResponseException
     */
    public function queryStream()
    {
        $response = $this->sendCommand(self::RCMD_QUERY_STREAM);

        if ($response->getCode() !== Response::RCMD_STREAM_INFO) {
            throw Exception\UnexpectedResponseException::fromResponse($response);
        }

        list($title, $dj, $listeners, $peak, $max) = array_values(
            unpack('a64title/a64dj/Vlisteners/Vpeak/Vmax', $response->getData())
        );

        return new StreamInfo(
            rtrim($title, "\0"),
            rtrim($dj, "\0"),
            $listeners,
            $peak,
            $max
        );
    }

    /**
     * Gets information about a specific user.
     *
     * @param  string $usernameOrHostmask
     * @return UserInfo|null
     * @throws Exception\UnexpectedResponseException
     */
    public function getUserInfo($usernameOrHostmask)
    {
        $response = $this->sendCommand(self::RCMD_GETUSERINFO, $usernameOrHostmask);

        if ($response->getCode() === Response::RCMD_USERNOTFOUND) {
            return null;
        }

        if ($response->getCode() !== Response::RCMD_USERINFO) {
            throw Exception\UnexpectedResponseException::fromResponse($response);
        }

        list($nickname, $password, , $flags) = array_values(
            unpack('a128nickname/a128password/Vnull/Vflags', $response->getData())
        );

        $hostmasksData = substr($response->getData(), 268);
        $numHostmasks  = strlen($hostmasksData) / 128;
        $hostmasks     = [];

        for ($i = 0; $i < $numHostmasks; ++$i) {
            // For some reason the supplied number of hostmasks is not correct,
            // so we rely on the actually supplied data.
            list($hostmask) = array_values(unpack('a128hostmask', substr($hostmasksData, $i * 128, 128)));
            $hostmask = rtrim($hostmask, "\0");

            if ($hostmask !== '') {
                $hostmasks[] = $hostmask;
            }
        }

        return new UserInfo(
            rtrim($nickname, "\0"),
            rtrim($password, "\0"),
            $flags,
            $hostmasks
        );
    }

    /**
     * Gets the currently logged in DJ.
     *
     * @return string|null
     */
    public function getCurrentDj()
    {
        $response = $this->sendCommand(static::RCMD_REQ_CURRENT);

        if ($response->getCode() === Response::RCMD_GENERIC_MSG) {
            return $response->getData();
        }

        return null;
    }

    /**
     * Sends a request with a given text or filename.
     *
     * Returns true when the request could be fulfilled, false otherwise.
     *
     * @param  string $query
     * @return bool
     */
    public function sendRequest($query)
    {
        $response = $this->sendCommand(static::RCMD_REQ, $query);
        return $response->getCode() !== Response::RCMD_GENERIC_ERROR;
    }

    /**
     * Relay a stream, URL or filename.
     *
     * Returns true when the request could be fulfilled, false otherwise.
     *
     * @param  string $query
     * @return bool
     */
    public function autodjRelay($query)
    {
        $response = $this->sendCommand(static::RCMD_SRC_RELAY, $query);
        dd($response);
        return $response->getCode() !== Response::RCMD_GENERIC_ERROR;
    }

    /**
     * Toggles the DoSpam flag on the bot, so messages can be broadcasted.
     *
     * Returns true when the flag is switched to "On", false otherwise.
     *
     * @return bool
     */
    public function toggleDoSpamFlag()
    {
        $response = $this->sendCommand(static::RCMD_DOSPAM);
        return $response->getData() === 'Spamming is now: On';
    }

    /**
     * Kills the bot.
     */
    public function killBot()
    {
        $this->sendCommand(static::RCMD_DIE);
    }

    /**
     * Broadcasts a message to all channels.
     *
     * This command requires the DoSpam flag to be enabled. If it is not
     * enabled or no message has been specified, the method will return false.
     *
     * @see    self::toggleDoSpamFlag()
     * @param  string $message
     * @return bool
     */
    public function broadcastMessage($message)
    {
        $response = $this->sendCommand(static::RCMD_BROADCAST_MSG, $message);
        return $response->getData() === 'Broadcast Sent!';
    }

    /**
     * Makes the source plugin count you in.
     *
     * This eventually stops the source plugin playing after the current song.
     */
    public function countdownSource()
    {
        $this->sendCommand(static::RCMD_SRC_COUNTDOWN);
    }

    /**
     * Restarts the bot.
     */
    public function restartBot()
    {
        $this->sendCommand(static::RCMD_RESTART);
    }

    /**
     * Makes the source plugin stop immediately.
     */
    public function forceSourceOff()
    {
        $this->sendCommand(static::RCMD_SRC_FORCE_OFF);
    }

    /**
     * Makes the source plugin start immediately.
     */
    public function forceSourceOn()
    {
        $this->sendCommand(static::RCMD_SRC_FORCE_ON);
    }

    /**
     * Skips the currently playing song played by the source plugin.
     */
    public function skipSourceSong()
    {
        $this->sendCommand(static::RCMD_SRC_NEXT);
    }

    /**
     * Reloads the source plugin.
     */
    public function reloadSource()
    {
        $this->sendCommand(static::RCMD_SRC_RELOAD);
    }

    /**
     * Rates a given song.
     *
     * Returns false if the given filename does not exist.
     *
     * @param  string $nickname
     * @param  int    $rating
     * @param  string $filename
     * @return bool
     * @throws Exception\OutOfRangeException
     */
    public function rateSourceSong($nickname, $rating, $filename)
    {
        $rating = (int) $rating;

        if ($rating < 0 || $rating > 5) {
            throw new Exception\OutOfRangeException(sprintf(
                'Rating must be an integer between 0 and 5, %s given',
                $rating
            ));
        }

        $response = $this->sendCommand(
            static::RCMD_SRC_RATE_SONG,
            sprintf("%s\xFE%s\xFE%s", $nickname, $rating, $filename)
        );

        return $response->getCode() !== Response::RCMD_GENERIC_ERROR;
    }

    /**
     * Gets the song filename which is currently played by the source plugin.
     *
     * @return string|null
     */
    public function getSourceSong()
    {
        $response = $this->sendCommand(static::RCMD_SRC_GET_SONG);

        if ($response->getCode() === Response::RCMD_GENERIC_MSG) {
            return $response->getData();
        }

        return null;
    }

    /**
     * Gets the status of the source plugin.
     *
     * Possible string responses are:
     *  - playing
     *  - connecting
     *  - stoppped
     *
     * @return string|null
     */
    public function getSourceStatus()
    {
        $response = $this->sendCommand(static::RCMD_SRC_STATUS);

        if ($response->getCode() === Response::RCMD_GENERIC_MSG) {
            return $response->getData();
        }

        return null;
    }

    /**
     * Gets the name of the source plugin.
     *
     * @return string|null
     */
    public function getSourceName()
    {
        $response = $this->sendCommand(static::RCMD_SRC_GET_NAME);

        if ($response->getCode() === Response::RCMD_GENERIC_MSG) {
            return $response->getData();
        }

        return null;
    }

    /**
     * Sends any command to the server.
     *
     * @param  int    $command
     * @param  string $data
     * @return Response
     */
    protected function sendCommand($command, $data = '')
    {
        fwrite($this->socket, pack('VV', $command, strlen($data)) . $data);
        return $this->receiveResponse();
    }

    /**
     * Receives and unpacks the response after sending a command.
     *
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

        $buffer = '';

        do {
            $buffer .= ($data = $length ? fread($this->socket, $length) : '');
            $length -= strlen($data);
        } while ($data !== false && $length > 0);

        if ($length > 0) {
            throw new Exception\SocketException('Not enough data received');
        }

        return new Response($code, $buffer);
    }
}
