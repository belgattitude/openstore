<?php

namespace Openstore\View\Helper;

use Zend\Mvc\Router\RouteMatch;
use Zend\Mvc\Router\RouteStackInterface;
use Zend\View\Helper\AbstractHelper;
use Zend\View\Exception;

/**
 * Helper for retrieving route params
 */
class RouteParams extends AbstractHelper
{
    /**
     * RouteStackInterface instance.
     *
     * @var RouteStackInterface
     */
    protected $router;

    /**
     * RouteInterface match returned by the router.
     *
     * @var RouteMatch.
     */
    protected $routeMatch;


    /**
     *
     * @var \Zend\Mvc\MvcEvent
     */
    protected $mvcEvent;

    public function __construct(\Zend\Mvc\MvcEvent $mvcEvent)
    {
        // injecting the mvc event, since $mvcEvent->getRouteMatch() may be null
        $this->mvcEvent = $mvcEvent;
        $this->setRouter($mvcEvent->getRouter());
        $this->setRouteMatch($mvcEvent->getRouteMatch());
    }

    /**
     * Get the route parameter value
     *
     * @param  string               $name               Name of the route
     * @throws Exception\RuntimeException         If no RouteMatch was provided
     * @throws Exception\RuntimeException         If RouteMatch didn't contain a matched route name
     */
    public function __invoke($name = null)
    {
        if (null === $this->router) {
            throw new Exception\RuntimeException('No RouteStackInterface instance provided');
        }

        if ($this->routeMatch === null) {
            throw new Exception\RuntimeException('No RouteMatch instance provided');
        }


        if ($name === null) {
            return $this->routeMatch->getParams();
        }

        return $this->routeMatch->getParam($name);
    }

    /**
     * Set the router to use for assembling.
     *
     * @param RouteStackInterface $router
     * @return Url
     */
    public function setRouter(RouteStackInterface $router)
    {
        $this->router = $router;
        return $this;
    }

    /**
     * Set route match returned by the router.
     *
     * @param  RouteMatch $routeMatch
     * @return Url
     */
    public function setRouteMatch(RouteMatch $routeMatch)
    {
        $this->routeMatch = $routeMatch;
        return $this;
    }
}
