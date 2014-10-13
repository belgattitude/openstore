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
     * @param string $source_column column containing the media_id
     * @param string $target_column column to store the URL
     * @param string $resolution
     */
    function __construct($source_column, $target_column, $resolution='1024x768', $quality="90", $base_url=null)
    {
        $this->source_column = $source_column;
        $this->target_column = $target_column;
        if ($base_url === null) {
            $base_url = 'http://api.emdmusic.com/media/preview/picture';
        }
        $this->base_url = $base_url;
        
        $this->url = $base_url . '/' . $resolution . "-" . $quality . "/"; 
               
    }
    
    /**
     * 
     * @param ArrayObject $row
     */
    function apply(ArrayObject $row) {
        
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
            $row[$this->target_column] = $this->url .  $prefix . '/' . $media_id . ".jpg";
        } 
    }
    
}
