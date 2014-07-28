<?php
namespace ShoutIrc;

class StreamInfo
{
    /**
     * @var string
     */
    protected $title;

    /**
     * @var string
     */
    protected $dj;

    /**
     * @var int
     */
    protected $listeners;

    /**
     * @var int
     */
    protected $peak;

    /**
     * @var int
     */
    protected $max;

    /**
     * @param string $title
     * @param string $dj
     * @param int    $listeners
     * @param int    $peak
     * @param int    $max
     */
    public function __construct($title, $dj, $listeners, $peak, $max)
    {
        $this->title     = $title;
        $this->dj        = $dj;
        $this->listeners = $listeners;
        $this->peak      = $peak;
        $this->max       = $max;
    }

    /**
     * @return string
     */
    public function getTitle()
    {
        return $this->title;
    }

    /**
     * @return string
     */
    public function getDj()
    {
        return $this->dj;
    }

    /**
     * @return int
     */
    public function getListeners()
    {
        return $this->listeners;
    }

    /**
     * @return int
     */
    public function getPeak()
    {
        return $this->peak;
    }

    /**
     * @return int
     */
    public function getMax()
    {
        return $this->max;
    }
}
