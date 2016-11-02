<?php

namespace OpenstoreApi\Api;

use Zend\Db\Sql\Select;
use Zend\Db\Sql\Expression;

class ProductCategoryService extends AbstractService
{
    /**
     * @param array $params [brands,pricelists]
     * @return \Soluble\FlexStore\FlexStore
     */
    public function getList(array $params = [])
    {
        $lang = $params['language'];
        $qlang = $this->adapter->getPlatform()->quoteValue($lang);

        $select = new Select();

        $select->from(['pc' => 'product_category'], [])
                ->join(['pc18' => 'product_category_translation'],
                        new Expression("pc18.category_id = pc.category_id and pc18.lang=$qlang"),
                        [], Select::JOIN_LEFT)
                ->join(['p' => 'product'], new Expression('pc.category_id = p.category_id'), [])
                ->join(['ppl' => 'product_pricelist'], new Expression('ppl.product_id = p.product_id'), [], $select::JOIN_LEFT)
                ->join(['pl' => 'pricelist'], new Expression('ppl.pricelist_id = pl.pricelist_id'), [], $select::JOIN_LEFT);

        $columns = [
            'category_id' => new Expression('pc.category_id'),
            'category_reference' => new Expression('pc.reference'),
            'namm_categ_code' => new Expression('pc.alt_mapping_reference'),
            'category_breadcrumb' => new Expression('pc.breadcrumb'),
            'title' => new Expression('pc.title'),
            'global_sort_index' => new Expression('pc.global_sort_index'),
            'nested_level' => new Expression('pc.lvl'),
            'nested_left' => new Expression('pc.lft'),
            'nested_right' => new Expression('pc.rgt'),
            'parent_category_id' => new Expression('pc.parent_id')
        ];


        $select->columns(array_merge($columns, [
            'count_products' => new Expression('COUNT(p.product_id)'),
            //'active_pricelists' => new Expression('GROUP_CONCAT(distinct pl.reference)')
        ]), true);

        $select->group($columns);

        $select->where('p.flag_active = 1');
        $select->where('ppl.flag_active = 1');

        if (array_key_exists('pricelists', $params)) {
            $select->where->in('pl.reference', explode(',', $params['pricelists']));
        }

        if (array_key_exists('brands', $params)) {
            $select->where->in('pb.reference', explode(',', $params['brands']));
        }

        //$select->having('active_pricelists is not null');
        $select->order(['pc.global_sort_index' => $select::ORDER_ASCENDING]);

        $store = $this->getStore($select);
        if (array_key_exists('limit', $params)) {
            $store->getSource()->getOptions()->setLimit($params['limit']);
        }
        if (array_key_exists('offset', $params)) {
            $store->getSource()->getOptions()->setOffset($params['offset']);
        }


        $this->initStoreFormatters($store, $params);
        return $store;
    }
}
