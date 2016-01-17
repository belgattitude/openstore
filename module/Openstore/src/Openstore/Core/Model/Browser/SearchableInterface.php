<?php

namespace Openstore\Core\Model\Browser;

interface SearchableInterface
{
    /**
     * @return array
     */
    public function getSearchableParams();

    /**
     *
     * @param array|\Openstore\Core\Model\Browser\Search\Params $params
     * @return \Openstore\Core\Model\Browser\SearchableInterface
     */
    public function setSearchParams($params);

    /**
     * @return \Openstore\Core\Model\Browser\Search\Params
     */
    public function getSearchParams();
}
