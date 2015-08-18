<?php

namespace MMan;

use MMan\MediaManager;
use ArrayObject;

class Media
{
    /**
     * @var \ArrayObject
     */
    protected $properties;

    /**
     * @var \MMan\MediaManager
     */
    protected $mediaManager;

    public function __construct(MediaManager $mediaManager)
    {
        $this->mediaManager = $mediaManager;
    }

    /**
     *
     * @param array|ArrayObject $properties
     * @return \MMan\Media
     */
    public function setProperties($properties)
    {
        if (is_array($properties)) {
            $properties = new ArrayObject($properties);
        }
        if (!$properties instanceof ArrayObject) {
            throw new \Exception("Invalid usage, setPorperties needs an array or ArrayObject as argument");
        }
        $this->properties = $properties;
        return $this;
    }

    /**
     *
     * @return string
     */
    public function getFilename()
    {
        return $this->properties->offsetGet('filename');
    }

    /**
     *
     * @return string
     */
    public function getFilemtime()
    {
        return $this->properties->offsetGet('filemtime');
    }

    /**
     * @return string
     */
    public function getPath()
    {
        $basePath = $this->mediaManager->getStorage()->getAdapterOptions()['basePath'];
        $file = str_replace('//', '/', $basePath . '/' . $this->properties['folder'] . '/' . $this->properties['location']);
        if (!file_exists($file)) {
            throw new \Exception("File '$file' does not exists");
        }
        if (!is_readable($file)) {
            throw new \Exception("File '$file' is not readable");
        }
        if (!is_file($file)) {
            throw new \Exception("File '$file' is not a file");
        }
        $path = realpath($file);
        return $path;
    }
}
