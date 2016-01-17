<?php

namespace Openstore;

use Zend\ServiceManager\FactoryInterface;
use Zend\ServiceManager\ServiceLocatorInterface;

class ConfigurationFactory implements FactoryInterface
{
    /**
     * @param ServiceLocatorInterface $sl
     * @return \Openstore\Service
     */
    public function createService(ServiceLocatorInterface $sl)
    {
        $config = $sl->get('Configuration');
        $cfg = isset($config['openstore']) ? $config['openstore'] : null;
        /*
          if ($cfg === null || empty($cfg)) {
          throw new \Exception('Cannot find a configuration');
          } */
        //$cfg = array('hello' => 'cool');
        //$cfg = array();
        $options = new Configuration($cfg);

        return $options;
    }
}
