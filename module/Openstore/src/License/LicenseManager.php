<?php

namespace License;

class LicenseManager
{
    /**
     *
     * @var array
     */
    protected $licenses;
    
    /**
     *
     * @param array $licenses
     */
    public function __construct(array $licenses)
    {
        $this->licenses = $licenses;
    }
    
    /**
     *
     * @param string $license
     * @return array|string|null
     */
    public function get($license)
    {
        return $this->licenses[$license];
    }
}
