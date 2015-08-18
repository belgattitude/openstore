<?php

/**
 * Zend Framework (http://framework.zend.com/)
 *
 * @link      http://github.com/zendframework/zf2 for the canonical source repository
 * @copyright Copyright (c) 2005-2013 Zend Technologies USA Inc. (http://www.zend.com)
 * @license   http://framework.zend.com/license/new-bsd New BSD License
 */

namespace Openstore;

use Openstore\Configuration;

interface ConfigurationAwareInterface
{
    /**
     * Set configuration
     *
     * @param \Openstore\Configuration $configuration
     * @return ConfigurationAwareInterface
     */
    public function setConfiguration(Configuration $configuration);

    /**
     * @return \Openstore\Configuration
     */
    public function getConfiguration();
}
