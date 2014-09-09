<?php

namespace Openstore;

use Zend\Stdlib\AbstractOptions;

class Configuration extends AbstractOptions {

    /**
     * @var array
     */
    protected $profiler = array(
        'enabled' => 'hh',
        'cool' => 'o'
    );

    /**
     * Overloading Constructor.
     *
     * @param  array|Traversable $options
     * @throws \Zend\Stdlib\Exception\InvalidArgumentException
     */
    public function __construct($options = null) {

        parent::__construct($options);
    }

    /**
     * Sets Profiler options.
     *
     * @param array $options
     */
    public function setProfiler(array $options) {
        if (isset($options['enabled'])) {
            $this->profiler['enabled'] = (bool) $options['enabled'];
        }
        if (isset($options['cool'])) {
            $this->profiler['cool'] = $options['cool'];
        }
    }

}
