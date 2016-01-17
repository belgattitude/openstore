<?php
namespace Openstore\Store\Renderer;

use Soluble\FlexStore\Renderer\RowRendererInterface;
use ArrayObject;

class RowPictureRenderer implements RowRendererInterface
{
    /**
     *
     * @var string
     */
    protected $resolution;


    /**
     *
     * @var string
     */
    protected $source_column;

    /**
     *
     * @var string
     */
    protected $target_column;

    /**
     *
     * @var string
     */
    protected $base_url;

    /**
     *
     * @var string
     */
    protected $url;


    /**
     *
     * @var string|null
     */
    protected $filemtime_column;


    /**
     *
     * @param string $source_column column containing the media_id
     * @param string $target_column column to store the URL
     * @param string $resolution
     * @param int $quality only for jpg
     * @param string $base_url
     * @param string $filemtime_column column containing the file modification time of the media
     *
     */
    public function __construct($source_column, $target_column, $resolution = '1024x768', $quality = "90", $base_url = null, $filemtime_column = null)
    {
        $this->source_column = $source_column;
        $this->target_column = $target_column;

        if ($base_url === null) {
            $base_url = 'http://api.emdmusic.com/media/preview/picture';
        }
        $this->base_url = $base_url;

        $this->url = $base_url . '/' . $resolution . "-" . $quality . "/";
        $this->filemtime_column = $filemtime_column;
    }



    /**
     *
     * @param ArrayObject $row
     */
    public function apply(ArrayObject $row)
    {
        if (!$row->offsetExists($this->source_column)) {
            throw new \Exception(__METHOD__ . " Source column '{$this->source_column} does not exists in row.");
        }
        /*
        if (!$row->offsetExists($this->target_column)) {
            throw new \Exception(__METHOD__ . " Target column '{$this->target_column} does not exists in row.");
        } */

        $media_id = $row[$this->source_column];
        if ($media_id != '') {
            $prefix = str_pad(substr($media_id, -2), 2, "0", STR_PAD_LEFT);
            if ($this->filemtime_column !== null && $row[$this->filemtime_column] != '') {
                $filename = $media_id . '_' . $row[$this->filemtime_column] . ".jpg";
            } else {
                $filename = $media_id . ".jpg";
            }
            $row[$this->target_column] = $this->url .  $prefix . '/' . $filename;
        }
    }


    /**
     * Return the list of columns required in order to use this renderer
     * @return array
     */
    public function getRequiredColumns()
    {
        return [$this->source_column, $this->target_column];
    }
}
