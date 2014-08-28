<?php
namespace ShoutIrc;

/**
 * Stream information container.
 */
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
     * Gets the title of the currently playing song.
     *
     * @return string
     */
    public function getTitle()
    {
        return $this->title;
    }

    /**
     * Gets the currently active DJ.
     *
     * @return string
     */
    public function getDj()
    {
        return $this->dj;
    }

    /**
     * Gets the number of connected listeners.
     *
     * @return int
     */
    public function getListeners()
    {
        return $this->listeners;
    }

    /**
     * Gets the peak of connected listeners.
     *
     * @return int
     */
    public function getPeak()
    {
        return $this->peak;
    }

    /**
     * Gets the maximum number of allowed listeners.
     *
     * @return int
     */
    public function getMax()
    {
        return $this->max;
    }
}
