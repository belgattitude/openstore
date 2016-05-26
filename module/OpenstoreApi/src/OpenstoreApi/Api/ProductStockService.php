<?php

namespace OpenstoreApi\Api;

use Zend\Db\Sql\Select;
use Zend\Db\Sql\Expression;

class ProductStockService extends AbstractService
{
    use ApiTrait\StockRendererTrait;

    /**
     *
     * @param array $params
     * @throws \Exception
     */
    protected function checkListParams(array $params)
    {
        $required_params = [
            'pricelist',
        ];
        foreach ($required_params as $param) {
            if (!array_key_exists($param, $params)) {
                throw new \Exception("Missing required '$param' parameter");
            }
            if (trim($params[$param]) == '') {
                throw new \Exception("Parameter '$param' is empty");
            }
        }
    }

    /**
     * @param array $params [brands,pricelists]
     * @return \Soluble\FlexStore\FlexStore
     */
    public function getList(array $params = [])
    {
        $this->checkListParams($params);
        $select = new Select();
        $lang = 'en';
        $pricelist_reference = $params['pricelist'];

        $select->from(['p' => 'product'], [])
                ->join(['p18' => 'product_translation'], new Expression("p18.product_id = p.product_id and p18.lang='$lang'"), [], $select::JOIN_LEFT)
                ->join(['pb' => 'product_brand'], new Expression('pb.brand_id = p.brand_id'), [])
                ->join(['p2' => 'product'], new Expression('p2.product_id = p.parent_id'), [], $select::JOIN_LEFT)
                ->join(['pu' => 'product_unit'], new Expression('p.unit_id = pu.unit_id'), [], $select::JOIN_LEFT)
                ->join(['pm' => 'product_model'], new Expression('pm.model_id = p.model_id'), [], $select::JOIN_LEFT)
                ->join(['pc' => 'product_category'], new Expression('p.category_id = pc.category_id'), [], $select::JOIN_LEFT)
                ->join(['pg' => 'product_group'], new Expression('pg.group_id = p.group_id'), [], $select::JOIN_LEFT)
                ->join(['pg18' => 'product_group_translation'], new Expression("pg18.group_id = pg.group_id and pg18.lang='$lang'"), [], $select::JOIN_LEFT)
                ->join(['ppl' => 'product_pricelist'], new Expression("ppl.product_id = p.product_id"), [], $select::JOIN_LEFT)
                ->join(['pl' => 'pricelist'], new Expression("ppl.pricelist_id = pl.pricelist_id and pl.reference = '$pricelist_reference'"), [], $select::JOIN_LEFT)
                ->join(['pt' => 'product_type'], new Expression('p.type_id = pt.type_id'), [], $select::JOIN_LEFT)
                ->join(['c' => 'currency'], new Expression('c.currency_id = pl.currency_id'), [], $select::JOIN_LEFT)
                ->join(['ps' => 'product_stock'], new Expression('ps.stock_id = pl.stock_id and ps.product_id = p.product_id'), [], $select::JOIN_INNER)
                ->join(['pmed' => 'product_media'], new Expression("pmed.product_id = p.product_id and pmed.flag_primary=1"), [], $select::JOIN_LEFT)
                ->join(['pmt' => 'product_media_type'], new Expression("pmt.type_id = p.type_id and pmt.reference = 'PICTURE'"), [], $select::JOIN_LEFT);

        $max_stock = 30;

        /*
          Liquidation
          DateCreation
         */
        $columns = [
            'product_id' => new Expression('p.product_id'),
            'product_reference' => new Expression('p.reference'),
            'on_stock' => new Expression('if (ps.available_stock > 0, 1, 0)'),
            'available_stock' => new Expression("LEAST(GREATEST(ps.available_stock, 0), $max_stock)"),
            'next_available_stock_at' => new Expression('CAST(ps.next_available_stock_at as DATE)'),
            'next_available_stock' => new Expression("LEAST(GREATEST(ps.next_available_stock, 0), $max_stock)"),
            'stock_updated_at' => new Expression('ps.updated_at'),
            'product_barcode_ean13' => new Expression('p.barcode_ean13'),
            'product_barcode_upca' => new Expression('p.barcode_upca'),
            'pricelist_id' => new Expression('pl.pricelist_id'),
            'pricelist_reference' => new Expression('pl.reference'),
            'avg_monthly_sale_qty' => new Expression('ps.avg_monthly_sale_qty'),
            'stock_level' => new Expression("''"),
            'next_stock_level' => new Expression("''")

        ];

        $select->columns($columns, true);
        /*
          $select->columns(array_merge($columns, array(
          'count_picture' => new Expression('GROUP_CONCAT(distinct pl.reference)'),
          )), true); */

        $select->group($columns);

        $select->where('p.flag_active = 1');
        $select->where('ppl.flag_active = 1');



        if (array_key_exists('brands', $params)) {
            $select->where->in('pb.reference', explode(',', $params['brands']));
        }

        if (array_key_exists('groups', $params)) {
            $select->where->in('pg.reference', explode(',', $params['groups']));
        }
        //$select->limit(1000);
        /*
          $select->where("pl.reference = 'BE'");
         */

        $select->order(['p.product_id' => $select::ORDER_ASCENDING]);

        $store = $this->getStore($select);

        if (array_key_exists('limit', $params)) {
            $store->getSource()->getOptions()->setLimit($params['limit']);
        }
        if (array_key_exists('offset', $params)) {
            $store->getSource()->getOptions()->setOffset($params['offset']);
        }

        $store->getColumnModel()->exclude([
            'avg_monthly_sale_qty'
        ]);

        $this->initStoreFormatters($store, $params);

        $this->addStockLevelRenderer($store, 'stock_level', 'available_stock');
        $this->addStockLevelRenderer($store, 'next_stock_level', 'next_available_stock');

        $this->addNextAvailableStockAtRenderer($store, 'next_available_stock_at');

        return $store;
    }
}
