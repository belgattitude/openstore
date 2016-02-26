<?php

namespace OpenstoreApi\Api;

use Zend\Db\Sql\Select;
use Zend\Db\Sql\Expression;

class ProductBrandService extends AbstractService
{
    /**
     * @param array $params [brands,pricelists]
     * @return \Soluble\FlexStore\FlexStore
     */
    public function getList(array $params = [])
    {
        $select = new Select();

        $select->from(['pb' => 'product_brand'], [])
                ->join(['p' => 'product'], new Expression('pb.brand_id = p.brand_id'), [])
                ->join(['ppl' => 'product_pricelist'], new Expression('ppl.product_id = p.product_id'), [], $select::JOIN_LEFT)
                ->join(['pl' => 'pricelist'], new Expression('ppl.pricelist_id = pl.pricelist_id'), [], $select::JOIN_LEFT);

        $columns = [
            'brand_id' => new Expression('pb.brand_id'),
            'brand_reference' => new Expression('pb.reference'),
            'title' => new Expression('pb.title'),
            'url' => new Expression('pb.url')
        ];

        $select->columns(array_merge($columns, [
            'active_pricelists' => new Expression('GROUP_CONCAT(distinct pl.reference)'),
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

        $select->having('active_pricelists is not null');
        //$select->order(array('p.product_id' => $select::ORDER_ASCENDING));

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
