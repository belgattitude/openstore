<?php
namespace Openstore;

use Zend\Stdlib\AbstractOptions;

class Options extends AbstractOptions
{

    /**
     * @var array
     */
    protected $profiler = array(
        'enabled'     => false,
    );


    /**
     * Overloading Constructor.
     *
     * @param  array|Traversable|null $options
     * @throws \Zend\Stdlib\Exception\InvalidArgumentException
     */
    public function __construct($options = null)
    {
        parent::__construct($options);
    }

    /**
     * Sets Profiler options.
     *
     * @param array $options
     */
    public function setProfiler(array $options)
    {
        if (isset($options['enabled'])) {
            $this->profiler['enabled'] = (bool) $options['enabled'];
        }
    }

}
