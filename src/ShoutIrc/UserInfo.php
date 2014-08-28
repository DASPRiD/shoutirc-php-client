<?php
namespace ShoutIrc;

/**
 * User information container.
 */
class UserInfo
{
    /**
     * @var string
     */
    protected $nickname;

    /**
     * @var string
     */
    protected $password;

    /**
     * @var int
     */
    protected $flags;

    /**
     * @var array
     */
    protected $hostmasks;

    /**
     * @param string $nickname
     * @param string $password
     * @param int    $flags
     * @param array  $hostmasks
     */
    public function __construct($nickname, $password, $flags, array $hostmasks)
    {
        $this->nickname  = $nickname;
        $this->password  = $password;
        $this->flags     = $flags;
        $this->hostmasks = $hostmasks;
    }

    /**
     * Gets the nickname of the user.
     *
     * @return string
     */
    public function getNickname()
    {
        return $this->nickname;
    }

    /**
     * Gets the password of the user.
     *
     * @return string
     */
    public function getPassword()
    {
        return $this->password;
    }

    /**
     * Gets the flags of the user.
     *
     * @return int
     */
    public function getFlags()
    {
        return $this->flags;
    }

    /**
     * Gets all hostnames of the user.
     *
     * @return array
     */
    public function getHostmasks()
    {
        return $this->hostmasks;
    }
}
