<?php

namespace Openstore;

class Configuration
{
    /**
     *
     * @var array
     */
    protected $options;
    
    /**
     * Overloading Constructor.
     *
     * @param  array|Traversable $options
     * @throws \Zend\Stdlib\Exception\InvalidArgumentException
     */
    public function __construct($options = null)
    {
        $this->options = $options;
    }
    
    /**
     *
     * @param string $key
     * @return mixed
     */
    public function getConfigKey($key)
    {
        $parts = explode('.', $key);
        
        $ref = $this->options;
        foreach ($parts as $part) {
            if (!isset($ref[$part])) {
                throw new \Exception(__METHOD__ . " Cannot locate configuration key '$key', failed at part '$part'");
            }
            $ref = $ref[$part];
        }
        return $ref;
    }
    
    
    public function getOptions()
    {
        return $this->options;
    }
}
