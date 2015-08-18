<?php

namespace MMan\Import;

class Element
{
    protected $filename;

    public function __construct()
    {
    }

    public function setFilename($filename)
    {
        $this->filename = $filename;
        return $this;
    }

    public function getFilename()
    {
        return $this->filename;
    }

    public function getFilesize()
    {
        return filesize($this->filename);
    }

    public function getFilemtime()
    {
        return filemtime($this->filename);
    }

    public function getLegacyMapping()
    {
        return $this->legacy_mapping;
    }

    public function setLegacyMapping($legacy_mapping)
    {
        $this->legacy_mapping = $legacy_mapping;
        return $this;
    }
}
