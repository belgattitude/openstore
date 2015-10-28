<?php

namespace Openstore\Model\Browser;

use Openstore\Core\Model\Browser\AbstractBrowser;
//use Openstore\Catalog\Browser\SearchParams\SearchParamsAbstract as SearchParams;
//use Openstore\Catalog\Browser\ProductFilter;
use Zend\Db\Sql\Sql;
use Zend\Db\Sql\Select;
use Zend\Db\Adapter\Adapter;
use Zend\Db\Sql\Expression;

class BrandBrowser extends AbstractBrowser
{
    /**
     * @return array
     */
    public function getSearchableParams()
    {
        return array(
            'language' => array('required' => true),
            'pricelist' => array('required' => true),
            'query' => array('required' => false),
            'categories' => array('required' => false),
            'id' => array('required' => false)
        );
    }

    /**
     *
     * @return \Zend\Db\Sql\Select
     */
    public function getSelect()
    {
        $params = $this->getSearchParams();

        $lang = $params->get('language');
        $pricelist = $params->get('pricelist');


        $select = new Select();
        $select->from(array('pb' => 'product_brand'), array())
                ->join(array('pb18' => 'product_brand_translation'), new Expression("pb.brand_id = pb18.brand_id and pb18.lang = '$lang'"), array(), $select::JOIN_LEFT)
                ->join(array('p' => 'product'), new Expression("p.brand_id = pb.brand_id"), array())
                ->join(array('ppl' => 'product_pricelist'), new Expression('ppl.product_id = p.product_id'), array())
                ->join(array('pl' => 'pricelist'), new Expression('pl.pricelist_id = ppl.pricelist_id'), array())
                ->join(array('ps' => 'product_stock'), new Expression('ps.stock_id = pl.stock_id and ps.product_id = p.product_id'), array())
                ->join(array('pc' => 'product_category'), new Expression('pc.category_id = p.category_id'), array())
                ->where('p.flag_active = 1')
                ->where('ppl.flag_active = 1')
                ->where("pl.reference = '$pricelist'");

        $this->assignFilters($select);

        $columns = array(
            'brand_id' => new Expression('pb.brand_id'),
            'reference' => new Expression('pb.reference'),
            'title' => new Expression('pb.title'),
            'description' => new Expression('COALESCE(pb18.description, pb.description)'),
        );

        $select->columns(array_merge($columns, array(
            'count_product' => new Expression('count(p.product_id)')
                )), true);

        $select->group($columns);

        $select->order(array('pb.title' => $select::ORDER_ASCENDING));

        if (($categories = $params->get('categories')) !== null) {
            $sql = new Sql($this->adapter);
            $category_clauses = array();
            foreach ($categories as $category_reference) {
                $spb = new Select();
                $spb->from('product_category')
                        ->columns(array('category_id', 'lft', 'rgt'))
                        ->where(array('reference' => $category_reference))
                        ->limit(1);

                $sql_string = $sql->getSqlStringForSqlObject($spb);
                $results = $this->adapter->query($sql_string, Adapter::QUERY_MODE_EXECUTE)->toArray();
                if (count($results) > 0) {
                    $category_clauses[] = 'pc.lft between ' . $results[0]['lft'] . ' and ' . $results[0]['rgt'];
                }
            }
            $select->where('(' . join(' or ', $category_clauses) . ')');
        }

        if (($query = trim($params->get('query'))) != "") {
            $query = str_replace(' ', '%', trim($query));
            $q = $this->adapter->getPlatform()->quoteValue('%' . $query . '%');
            $select->where("pb.title like $q");
        }


        return $select;
    }
}
