<?php
namespace ShoutIrc;

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
     * @return string
     */
    public function getNickname()
    {
        return $this->nickname;
    }

    /**
     * @return string
     */
    public function getPassword()
    {
        return $this->password;
    }

    /**
     * @return int
     */
    public function getFlags()
    {
        return $this->flags;
    }

    /**
     * @return array
     */
    public function getHostmasks()
    {
        return $this->hostmasks;
    }
}
