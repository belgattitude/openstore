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
    function __construct(array $licenses) {
        $this->licenses = $licenses;
    }
    
    /**
     * 
     * @param string $license
     * @return array|string|null
     */
    function get($license)
    {
        
        return $this->licenses[$license]; 
    }
    
    
}


