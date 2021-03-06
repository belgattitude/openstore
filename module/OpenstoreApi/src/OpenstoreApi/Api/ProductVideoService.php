<?php

namespace OpenstoreApi\Api;

use Zend\Db\Sql\Select;
use Zend\Db\Sql\Expression;

class ProductVideoService extends AbstractService
{
    public static $default_types = [
        'VIMEO_VIDEO',
        'VIDEO'
    ];

    /**
     * @param array $params [types,brands,pricelists]
     * @return \Soluble\FlexStore\FlexStore
     */
    public function getList(array $params = [])
    {
        $select = new Select();

        $select->from(['pm' => 'product_media'], [])
                ->join(['m' => 'media'], new Expression('m.media_id = pm.media_id'), [])

                // THE JOIN_LEFT with media_container
                // allows to get medias that are not
                // in a container type (see hosted VIDEOS...)
                ->join(['mc' => 'media_container'], new Expression('mc.container_id = m.container_id'), [],
                    Select::JOIN_LEFT)

                ->join(['p' => 'product'], new Expression('p.product_id = pm.product_id'), [])
                ->join(['pb' => 'product_brand'], new Expression('pb.brand_id = p.brand_id'), [])
                ->join(['pmt' => 'product_media_type'], new Expression('pmt.type_id = pm.type_id'), [])
                ->join(['pg' => 'product_group'], new Expression('pg.group_id = p.group_id'), [], $select::JOIN_LEFT)
                ->join(['ppl' => 'product_pricelist'], new Expression('ppl.product_id = p.product_id'), [], $select::JOIN_LEFT)
                ->join(['pl' => 'pricelist'], new Expression('ppl.pricelist_id = pl.pricelist_id'), [], $select::JOIN_LEFT);

        $columns = [
            'product_id' => new Expression('p.product_id'),
            'product_reference' => new Expression('p.reference'),
            'product_barcode_ean13' => new Expression('p.barcode_ean13'),
            'product_barcode_upca' => new Expression('p.barcode_upca'),
            'brand_reference' => new Expression('pb.reference'),
            'group_reference' => new Expression('pg.reference'),
            'media_type' => new Expression('pmt.reference'),
            'media_id' => new Expression('m.media_id'),

            'remote_media_id'  => 'm.remote_media_id',
            'remote_media_url' => 'm.remote_media_url',



        ];


        $select->columns(array_merge($columns, [
            'active_pricelists' => new Expression('GROUP_CONCAT(distinct pl.reference)'),
                ]), false);

        $select->group($columns);

        $select->where('p.flag_active = 1');
        $select->where('ppl.flag_active = 1');

        $type_filter_enabled = false;

        if (array_key_exists('type', $params)) {
            $type_filter_enabled = true;
            $select->where(['pmt.reference' => $params['type']]);
        }

        if (array_key_exists('types', $params)) {

            $type_filter_enabled = true;
            $select->where->in('pmt.reference', explode(',', $params['types']));
        }

        if (!$type_filter_enabled) {
            // default to picture types
            $select->where->in('pmt.reference', self::$default_types);
        }


        if (array_key_exists('product_id', $params)) {
            $select->where(['p.product_id' => $params['product_id']]);
        }


        if (array_key_exists('pricelists', $params)) {
            $select->where->in('pl.reference', explode(',', $params['pricelists']));
        }

        if (array_key_exists('brands', $params)) {
            $select->where->in('pb.reference', explode(',', $params['brands']));
        }


        $select->having('active_pricelists is not null');
        $select->order(['p.product_id' => $select::ORDER_ASCENDING]);

        $store = $this->getStore($select);

        if (array_key_exists('limit', $params)) {
            $store->getSource()->getOptions()->setLimit($params['limit']);
        }
        if (array_key_exists('offset', $params)) {
            $store->getSource()->getOptions()->setOffset($params['offset']);
        }


        // Initialize column model
        //$this->addStorePictureRenderer($store, 'media_id', 'filemtime', 'filemtime');
        $this->initStoreFormatters($store, $params);

        return $store;
    }
}
