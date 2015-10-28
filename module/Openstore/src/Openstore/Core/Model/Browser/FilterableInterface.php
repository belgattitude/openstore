<?php

namespace Openstore\Core\Model\Browser;

use Openstore\Core\Model\Browser\Filter\FilterInterface;

interface FilterableInterface
{
    /**
     * @return array
     */
    public function getFilters();

    /**
     *
     * @param \Openstore\Core\Model\Browser\Filter\FilterInterface $filter
     * @return \Openstore\Core\Model\Browser\FilterableInterface
     */
    public function addFilter(FilterInterface $filter);
}
