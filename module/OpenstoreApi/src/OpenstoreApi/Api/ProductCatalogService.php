<?php

namespace OpenstoreApi\Api;

use Zend\Db\Sql\Select;
use Zend\Db\Sql\Expression;
use Soluble\FlexStore\FlexStore;

class ProductCatalogService extends AbstractService {

    /**
     * 
     * @param array $params
     * @throws \Exception
     */
    protected function checkListParams(array $params) {
        $required_params = array(
            'pricelist',
            'language');
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
    function getList(array $params = array()) {
        $this->checkListParams($params);
        $select = new Select();
        $lang = $params['language'];

        $pricelist_reference = $params['pricelist'];


        // Step 1: Inner select packaging selection
        $packSelect = new Select();
        $packSelect->from(array('pp' => 'product_packaging'), array())
                ->join(array('pt' => 'packaging_type'), new Expression("pp.type_id = pt.type_id"), array());
        $packSelect->columns(
                array(
            'product_id' => new Expression('pp.product_id'),
            'pack_unit_qty' => new Expression("MAX(if (pt.reference = 'UNIT', pp.quantity, null))"),
            'pack_unit_barcode_ean' => new Expression("MAX(if (pt.reference = 'UNIT', pp.barcode_ean, null))"),
            'pack_unit_barcode_upc' => new Expression("MAX(if (pt.reference = 'UNIT', pp.barcode_upc, null))"),
            'pack_unit_volume' => new Expression("MAX(if (pt.reference = 'UNIT', pp.volume, null))"),
            'pack_unit_weight' => new Expression("MAX(if (pt.reference = 'UNIT', pp.weight, null))"),
            'pack_unit_length' => new Expression("MAX(if (pt.reference = 'UNIT', pp.length, null))"),
            'pack_unit_width' => new Expression("MAX(if (pt.reference = 'UNIT', pp.width, null))"),
            'pack_unit_height' => new Expression("MAX(if (pt.reference = 'UNIT', pp.height, null))"),
            'pack_carton_qty' => new Expression("MAX(if (pt.reference = 'CARTON', pp.quantity, null))"),
            'pack_carton_barcode_ean' => new Expression("MAX(if (pt.reference = 'CARTON', pp.barcode_ean, null))"),
            'pack_carton_barcode_upc' => new Expression("MAX(if (pt.reference = 'CARTON', pp.barcode_upc, null))"),
            'pack_carton_volume' => new Expression("MAX(if (pt.reference = 'CARTON', pp.volume, null))"),
            'pack_carton_weight' => new Expression("MAX(if (pt.reference = 'CARTON', pp.weight, null))"),
            'pack_carton_length' => new Expression("MAX(if (pt.reference = 'CARTON', pp.length, null))"),
            'pack_carton_width' => new Expression("MAX(if (pt.reference = 'CARTON', pp.width, null))"),
            'pack_carton_height' => new Expression("MAX(if (pt.reference = 'CARTON', pp.height, null))"),
            'pack_mastercarton_qty' => new Expression("MAX(if (pt.reference = 'MASTERCARTON', pp.quantity, null))"),
            'pack_mastercarton_barcode_ean' => new Expression("MAX(if (pt.reference = 'MASTERCARTON', pp.barcode_ean, null))"),
            'pack_mastercarton_barcode_upc' => new Expression("MAX(if (pt.reference = 'MASTERCARTON', pp.barcode_upc, null))"),
            'pack_mastercarton_volume' => new Expression("MAX(if (pt.reference = 'MASTERCARTON', pp.volume, null))"),
            'pack_mastercarton_weight' => new Expression("MAX(if (pt.reference = 'MASTERCARTON', pp.weight, null))"),
            'pack_mastercarton_length' => new Expression("MAX(if (pt.reference = 'MASTERCARTON', pp.length, null))"),
            'pack_mastercarton_width' => new Expression("MAX(if (pt.reference = 'MASTERCARTON', pp.width, null))"),
            'pack_mastercarton_height' => new Expression("MAX(if (pt.reference = 'MASTERCARTON', pp.height, null))"),
                )
                , true);
        $packSelect->group(array('product_id'));
        //echo $packSelect->getSqlString($this->adapter->getPlatform());
        //die();


        $select->from(array('p' => 'product'), array())
                ->join(array('p18' => 'product_translation'), new Expression("p18.product_id = p.product_id and p18.lang='$lang'"), array(), $select::JOIN_LEFT)
                ->join(array('psi' => 'product_search'), new Expression("psi.product_id = p.product_id and psi.lang = '$lang'"), array(), $select::JOIN_LEFT)
                ->join(array('pb' => 'product_brand'), new Expression('pb.brand_id = p.brand_id'), array())
                ->join(array('p2' => 'product'), new Expression('p2.product_id = p.parent_id'), array(), $select::JOIN_LEFT)
                ->join(array('p2_18' => 'product_translation'), new Expression("p2.product_id = p2_18.product_id and p2_18.lang='$lang'"), array(), $select::JOIN_LEFT)
                ->join(array('pu' => 'product_unit'), new Expression('p.unit_id = pu.unit_id'), array(), $select::JOIN_LEFT)
                ->join(array('pm' => 'product_model'), new Expression('pm.model_id = p.model_id'), array(), $select::JOIN_LEFT)
                ->join(array('pc' => 'product_category'), new Expression('p.category_id = pc.category_id'), array(), $select::JOIN_LEFT)
                ->join(array('pc18' => 'product_category_translation'), new Expression("pc.category_id = pc18.category_id and pc18.lang='$lang'"), array(), $select::JOIN_LEFT)
                ->join(array('pg' => 'product_group'), new Expression('pg.group_id = p.group_id'), array(), $select::JOIN_LEFT)
                ->join(array('pg18' => 'product_group_translation'), new Expression("pg18.group_id = pg.group_id and pg18.lang='$lang'"), array(), $select::JOIN_LEFT)
                ->join(array('ppl' => 'product_pricelist'), new Expression("ppl.product_id = p.product_id"), array(), $select::JOIN_LEFT)
                ->join(array('pl' => 'pricelist'), new Expression("ppl.pricelist_id = pl.pricelist_id and pl.reference = '$pricelist_reference'"), array(), $select::JOIN_LEFT)
                ->join(array('pt' => 'product_type'), new Expression('p.type_id = pt.type_id'), array(), $select::JOIN_LEFT)
                ->join(array('c' => 'currency'), new Expression('c.currency_id = pl.currency_id'), array(), $select::JOIN_LEFT)
                ->join(array('ps' => 'product_stock'), new Expression('ps.stock_id = pl.stock_id and ps.product_id = p.product_id'), array(), $select::JOIN_INNER)
                ->join(array('pst' => 'product_status'), new Expression('pst.status_id = ppl.status_id'), array(), $select::JOIN_LEFT)
                ->join(array('pmed' => 'product_media'), new Expression("pmed.product_id = p.product_id and pmed.flag_primary=1"), array(), $select::JOIN_LEFT)
                ->join(array('pmt' => 'product_media_type'), new Expression("pmt.type_id = p.type_id and pmt.reference = 'PICTURE'"), array(), $select::JOIN_LEFT)
                ->join(array('packs' => $packSelect), new Expression("packs.product_id = p.product_id"), array(), $select::JOIN_LEFT);

        $max_stock = 30;

        /*
          Liquidation
          DateCreation
         */
        $columns = array(
            'product_id' => new Expression('p.product_id'),
            'product_reference' => new Expression('p.reference'),
            'product_title' => new Expression('COALESCE(p18.title, p18.invoice_title, p.title, p.invoice_title)'),
            'product_invoice_title' => new Expression('COALESCE(p18.invoice_title, p.invoice_title)'),
            'product_description' => new Expression('if (p2.product_id is null, COALESCE(p18.description, p.description), COALESCE(p2_18.description, p.description) )'),
            'product_characteristic' => new Expression('COALESCE(p18.characteristic, p.characteristic)'),
            'price' => new Expression('ROUND(ppl.price, 2)'),
            'list_price' => new Expression('ppl.list_price'),
            'public_price' => new Expression('ppl.public_price'),
            'discount_1' => new Expression('ppl.discount_1'),
            'discount_2' => new Expression('ppl.discount_2'),
            'discount_3' => new Expression('ppl.discount_3'),
            'discount_4' => new Expression('ppl.discount_4'),
            'is_liquidation' => new Expression('ppl.is_liquidation'),
            'is_promotional' => new Expression('ppl.is_promotional'),
            'is_bestseller' => new Expression('ppl.is_bestseller'),
            'is_hot' => new Expression('ppl.is_hot'),
            'is_bestvalue' => new Expression('ppl.is_bestvalue'),
            'is_new' => new Expression('ppl.is_new'),
            'sale_minimum_qty' => new Expression('ppl.sale_minimum_qty'),
            'on_stock' => new Expression('if (ps.available_stock > 0, 1, 0)'),
            'available_stock' => new Expression("LEAST(GREATEST(ps.available_stock, 0), $max_stock)"),
            'next_available_stock_at' => new Expression('ps.next_available_stock_at'),
            'next_available_stock' => new Expression("LEAST(GREATEST(ps.next_available_stock, 0), $max_stock)"),
            'stock_updated_at' => new Expression('ps.updated_at'),
            'product_barcode_ean13' => new Expression('p.barcode_ean13'),
            'product_barcode_upca' => new Expression('p.barcode_upca'),
            'brand_id' => new Expression('pb.brand_id'),
            'brand_reference' => new Expression('pb.reference'),
            'brand_title' => new Expression('pb.title'),
            'group_id' => new Expression('pg.group_id'),
            'group_reference' => new Expression('pg.reference'),
            'group_title' => new Expression('COALESCE(pg18.title, pg.title)'),
            'category_id' => new Expression('p.category_id'),
            'category_parent_id' => new Expression('pc.parent_id'),
            'category_reference' => new Expression('pc.reference'),
            'category_title' => new Expression('pc.title'),
            'model_id' => new Expression('p.model_id'),
            'model_reference' => new Expression('pm.reference'),
            'parent_id' => new Expression('p.parent_id'),
            'parent_reference' => new Expression('p2.reference'),
            'unit_id' => new Expression('p.unit_id'),
            'unit_reference' => new Expression('pu.reference'),
            'currency_id' => new Expression('c.currency_id'),
            'currency_reference' => new Expression('c.reference'),
            'pricelist_id' => new Expression('pl.pricelist_id'),
            'pricelist_reference' => new Expression('pl.reference'),
            'type_id' => new Expression('pt.type_id'),
            'type_reference' => new Expression('pt.reference'),
            'weight' => new Expression('p.weight'),
            'volume' => new Expression('p.volume'),
            'length' => new Expression('p.length'),
            'width' => new Expression('p.width'),
            'height' => new Expression('p.height'),
            'pack_qty_box' => new Expression('p.pack_qty_box'),
            'pack_qty_carton' => new Expression('p.pack_qty_carton'),
            'pack_qty_master_carton' => new Expression('p.pack_qty_master_carton'),
            'picture_media_id' => new Expression('pmed.media_id'),
            // 
            'pack_unit_volume' => new Expression("packs.pack_unit_volume"),
            'pack_unit_weight' => new Expression("packs.pack_unit_weight"),
            'pack_unit_length' => new Expression("packs.pack_unit_length"),
            'pack_unit_width' => new Expression("packs.pack_unit_width"),
            'pack_unit_height' => new Expression("packs.pack_unit_height"),
            //'pack_box_qty'           => new Expression("packs.pack_box_qty"),
            'pack_carton_barcode_ean' => new Expression("packs.pack_carton_barcode_ean"),
            'pack_carton_barcode_upc' => new Expression("packs.pack_carton_barcode_upc"),
            'pack_carton_volume' => new Expression("packs.pack_carton_volume"),
            'pack_carton_weight' => new Expression("packs.pack_carton_weight"),
            'pack_carton_length' => new Expression("packs.pack_carton_length"),
            'pack_carton_width' => new Expression("packs.pack_carton_width"),
            'pack_carton_height' => new Expression("packs.pack_carton_height"),
            //'pack_mastercarton_qty'  => new Expression("packs.pack_mastercarton_qty"),
            'pack_mastercarton_barcode_ean' => new Expression("packs.pack_mastercarton_barcode_ean"),
            'pack_mastercarton_barcode_upc' => new Expression("packs.pack_mastercarton_barcode_upc"),
            'pack_mastercarton_volume' => new Expression("packs.pack_mastercarton_volume"),
            'pack_mastercarton_weight' => new Expression("packs.pack_mastercarton_weight"),
            'pack_mastercarton_length' => new Expression("packs.pack_mastercarton_length"),
            'pack_mastercarton_width' => new Expression("packs.pack_mastercarton_width"),
            'pack_mastercarton_height' => new Expression("packs.pack_mastercarton_height"),
            'category_breadcrumb' => new Expression('if (pc18.breadcrumb is null, pc.breadcrumb, pc18.breadcrumb)'),
            'flag_till_end_of_stock' => new Expression('pst.flag_till_end_of_stock'),
            'flag_end_of_lifecycle' => new Expression('pst.flag_end_of_lifecycle'),
            'available_at' => new Expression('COALESCE(ppl.available_at, p.available_at)'),
        );

        $select->columns($columns, true);
        /*
          $select->columns(array_merge($columns, array(
          'count_picture' => new Expression('GROUP_CONCAT(distinct pl.reference)'),
          )), true); */

        $select->group($columns);

        $select->where('p.flag_active = 1');
        $select->where('ppl.flag_active = 1');

        $relevance = "1";        

        if (array_key_exists('query', $params)) {
            $query = trim($params['query']);
            if ($query != "") {

                $platform = $this->adapter->getPlatform();
                $quoted = $platform->quoteValue($query);
                $searchable_ref = $this->getSearchableReference($query);

                // 1. TEST PART WITH 
                // BARCODE, SEARCH_REFERENCE, PRODUCT_ID,

                $matches = array();
                if (is_numeric($query) && strlen($query) < 20) {

                    // Can be a barcode or a product_id, 
                    $matches[1000000000] = "p.product_id = $query";

                    if (strlen($query) > 10) {
                        $matches[1000000000] = "p.barcode_ean13 = '$query'";
                        $matches[100000000] = "p.barcode_upca = '$query'";
                    }
                }

                $splitted = explode(' ', preg_replace('!\s+!', ' ', $query));

                // test title in order

                $matches[10000000] = "p.search_reference like " . $platform->quoteValue($searchable_ref . '%');
                $matches[1000000] = "p.search_reference like " . $platform->quoteValue('%' . $searchable_ref . '%');
                //echo "p.search_reference like CONCAT('%', get_searchable_reference($quoted), '%')";
                //die();
                if (strlen($query) > 3) {
                    $matches[1000000] = 'p18.title like ' . $platform->quoteValue('%' . join('%', $splitted) . '%');
                    $matches[100000] = 'p.title like ' . $platform->quoteValue('%' . join('%', $splitted) . '%');
                }
                if (strlen($query) > 5) {
                    $matches[10000] = 'psi.keywords like ' . $platform->quoteValue('%' . join('%', $splitted) . '%');
                }

                $matches[0] = 'MATCH (psi.keywords) AGAINST (' . $platform->quoteValue(join(' ', $splitted)) . ' IN NATURAL LANGUAGE MODE)';

                $relevance = '';
                $i = 0;
                foreach ($matches as $weight => $condition) {
                    if ($weight > 0) {
                        $relevance .= "if ($condition, $weight, ";
                    } else {
                        $relevance .= $condition;
                    }
                }
                $relevance .= str_repeat(')', count($matches) - 1);

                $select->where("(" . join(' or ', array_values($matches)) . ")");
            } 
            
            
        }


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

        //$select->order(array('p.product_id' => $select::ORDER_ASCENDING));

        $select->order(array(new Expression($relevance . ' desc'), 'pc.global_sort_index', 'p.sort_index', 'p.display_reference'));                        
        /**
         * 
         */
        //echo $select->getSqlString($this->adapter->getPlatform());
        //die();

        
        
        $parameters = array(
            'adapter' => $this->adapter,
            'select' => $select
        );
        $store = new FlexStore('zend\select', $parameters);
        if (array_key_exists('limit', $params)) {
            $store->getSource()->getOptions()->setLimit($params['limit']);
        }
        if (array_key_exists('offset', $params)) {
            $store->getSource()->getOptions()->setOffset($params['offset']);
        }

        
        
        //$store->getSource()->getData();
        //var_dump($store->getSource()->getData()->toArray());
        //die();
        return $store;
    }

    /**
     * Return quoted searchable reference from a keyword
     * @param string $reference
     * @return string
     */
    protected function getSearchableReference($reference, $wildcards_starts_at_char = 4, $max_reference_length = 20) {
        $reference = substr($reference, 0, $max_reference_length);
        $quoted = $this->adapter->getPlatform()->quoteValue($reference);
        $ref = $this->adapter->query("select get_searchable_reference($quoted) as ref")->execute()->current()['ref'];
        $out = '';
        foreach (str_split($ref) as $idx => $c) {
            if ($idx >= $wildcards_starts_at_char) {
                $out .= '%';
            }
            $out .= $c;
        }
        return $out;
    }

}
