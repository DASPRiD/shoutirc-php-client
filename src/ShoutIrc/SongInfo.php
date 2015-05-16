<?php
namespace ShoutIrc;

/**
 * Stream information container.
 */

class SongInfo
{
    /**
     * @var string
     */
    protected $file_id;

    /**
     * @var string
     */
    protected $fn;

    /**
     * @var int
     */
    protected $artist;

    /**
     * @var int
     */
    protected $album;

    /**
     * @var int
     */
    protected $title;
    protected $genre;
    protected $songLen;
    protected $is_request;
    protected $requested_by;
    protected $playback_length;
    protected $playback_position;



    /**
     * @param string $title
     * @param string $dj
     * @param int    $listeners
     * @param int    $peak
     * @param int    $max
     */
    public function __construct($file_id, $fn, $artist, $album, $title, $genre, $songLen, $is_request, $requested_by, $playback_position, $playback_length)
    {
        $this->file_id = $file_id;
        $this->fn = $fn;
        $this->artist=$artist;
        $this->album=$album;
        $this->title=$title;
        $this->genre=$genre;
        $this->songLen=$songLen;
        $this->is_request=$is_request;
        $this->requested_by=$requested_by;
        $this->playback_position=$playback_position;
        $this->playback_length=$playback_length;
    }

    /**
     * Gets the title of the currently playing song.
     *
     * @return string
     */
    public function getFileID()
    {
        return $this->file_id;
    }

    /**
     * Gets the currently active DJ.
     *
     * @return string
     */
    public function getFileName()
    {
        return $this->fn;
    }

    /**
     * Gets the number of connected listeners.
     *
     * @return int
     */
    public function getArtist()
    {
        return $this->artist;
    }

    /**
     * Gets the peak of connected listeners.
     *
     * @return int
     */
    public function getAlbum()
    {
        return $this->album;
    }

    /**
     * Gets the maximum number of allowed listeners.
     *
     * @return int
     */
    public function getTitle()
    {
        return $this->title;
    }

    public function getGenre()
    {
        return $this->genre;
    }

    public function getSongLength()
    {
        return $this->songLen;
    }

    public function getWasRequested()
    {
        return $this->is_request;
    }
    public function getRequester()
    {
        return $this->requested_by;
    }

    public function getPlayBackPosition()
    {
        return $this->playback_position;
    }
    public function getPlayBackLength()
    {
        return $this->playback_length;
    }
}
