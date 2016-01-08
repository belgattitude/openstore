<?php

namespace Openstore\Model\Browser;

use Openstore\Core\Model\Browser\AbstractBrowser;
//use Openstore\Catalog\Browser\SearchParams\SearchParamsAbstract as SearchParams;
//use Openstore\Catalog\Browser\ProductFilter;
use Zend\Db\Sql\Sql;
use Soluble\Db\Sql\Select;
use Zend\Db\Adapter\Adapter;
use Zend\Db\Sql\Expression;
use Patchwork\Utf8 as u;

class ProductBrowser extends AbstractBrowser
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
            'brands' => array('required' => false),
            'categories' => array('required' => false),
            'id' => array('required' => false)
        );
    }

    /**
     *
     * @return array
     */
    protected function getPackagingColumns()
    {
        $columns = array(
            'pack_unit_qty' => new Expression("packs.pack_unit_qty"),
            'pack_unit_volume' => new Expression("packs.pack_unit_volume"),
            'pack_unit_weight' => new Expression("packs.pack_unit_weight"),
            'pack_unit_length' => new Expression("packs.pack_unit_length"),
            'pack_unit_width' => new Expression("packs.pack_unit_width"),
            'pack_unit_height' => new Expression("packs.pack_unit_height"),
            'pack_carton_qty' => new Expression("packs.pack_carton_qty"),
            'pack_carton_barcode_ean' => new Expression("packs.pack_carton_barcode_ean"),
            'pack_carton_barcode_upc' => new Expression("packs.pack_carton_barcode_upc"),
            'pack_carton_volume' => new Expression("packs.pack_carton_volume"),
            'pack_carton_weight' => new Expression("packs.pack_carton_weight"),
            'pack_carton_length' => new Expression("packs.pack_carton_length"),
            'pack_carton_width' => new Expression("packs.pack_carton_width"),
            'pack_carton_height' => new Expression("packs.pack_carton_height"),
            'pack_mastercarton_qty' => new Expression("packs.pack_mastercarton_qty"),
            'pack_mastercarton_barcode_ean' => new Expression("packs.pack_mastercarton_barcode_ean"),
            'pack_mastercarton_barcode_upc' => new Expression("packs.pack_mastercarton_barcode_upc"),
            'pack_mastercarton_volume' => new Expression("packs.pack_mastercarton_volume"),
            'pack_mastercarton_weight' => new Expression("packs.pack_mastercarton_weight"),
            'pack_mastercarton_length' => new Expression("packs.pack_mastercarton_length"),
            'pack_mastercarton_width' => new Expression("packs.pack_mastercarton_width"),
            'pack_mastercarton_height' => new Expression("packs.pack_mastercarton_height")
        );
        return $columns;
    }

    /**
     * Return inner select for getting packaging extended information
     *
     * @return Select
     */
    protected function getPackagingInnerSelect()
    {
        // Step 1: Inner select packaging selection
        $packSelect = new Select();
        $packSelect->from(array('pp' => 'product_packaging'), array())
                ->join(array('pt' => 'packaging_type'), new Expression("pp.type_id = pt.type_id"), array());
        $packSelect->columns(
            array(
            'product_id' => new Expression('pp.product_id'),
            'pack_unit_qty' => new Expression("MAX(if (pt.reference = 'UNIT', pp.quantity, 1))"),
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
                ),
            true
        );
        $packSelect->group(array('product_id'));
        return $packSelect;
    }

    /**
     *
     * @return Select
     */
    public function getSelect()
    {
        $params = $this->getSearchParams();

        $enable_packaging_columns = ($params['enable_packaging_columns'] === true);

        $lang = $params->get('language');
        $pricelist = $params->get('pricelist');

        $select = new Select();
        $select->setDbAdapter($this->adapter);

        $select->from(array('p' => 'product'), array())
                ->join(array('p18' => 'product_translation'), new Expression("p18.product_id = p.product_id and p18.lang = '$lang'"), array(), $select::JOIN_LEFT)
                ->join(array('pstub' => 'product_stub'), new Expression('pstub.product_stub_id = p.product_stub_id'), array(), $select::JOIN_LEFT)
                ->join(array('pstub18' => 'product_stub_translation'), new Expression("pstub.product_stub_id = pstub18.product_stub_id and pstub18.lang='$lang'"), array(), $select::JOIN_LEFT)
                
                // to remove when product_model is ready
                //->join(array('p2' => 'product'), new Expression('p2.product_id = p.parent_id'), array(), $select::JOIN_LEFT)
                //->join(array('p2_18' => 'product_translation'), new Expression("p2.product_id = p2_18.product_id and p2_18.lang='$lang'"), array(), $select::JOIN_LEFT)
                // end of to remove
                ->join(array('psi' => 'product_search'), new Expression("psi.product_id = p.product_id and psi.lang = '$lang'"), array(), $select::JOIN_LEFT)
                ->join(array('ppl' => 'product_pricelist'), new Expression('ppl.product_id = p.product_id'), array())
                ->join(array('pl' => 'pricelist'), new Expression('pl.pricelist_id = ppl.pricelist_id'), array())
                ->join(array('pt' => 'product_type'), new Expression('p.type_id = pt.type_id'), array(), $select::JOIN_LEFT)
                ->join(array('c' => 'currency'), new Expression('c.currency_id = pl.currency_id'), array(), $select::JOIN_LEFT)
                ->join(array('pu' => 'product_unit'), new Expression('pu.unit_id = p.unit_id'), array(), $select::JOIN_LEFT)
                ->join(array('ps' => 'product_stock'), new Expression('ps.stock_id = pl.stock_id and ps.product_id = p.product_id'), array())
                ->join(array('pb' => 'product_brand'), new Expression('pb.brand_id = p.brand_id'), array())
                ->join(array('pg' => 'product_group'), new Expression('pg.group_id = p.group_id'), array(), $select::JOIN_LEFT)
                ->join(array('pst' => 'product_status'), new Expression('p.status_id = pst.status_id'), array(), $select::JOIN_LEFT)
                ->join(array('pc' => 'product_category'), new Expression('pc.category_id = p.category_id'), array())
                ->join(array('pc18' => 'product_category_translation'), new Expression("pc.category_id = pc18.category_id and pc18.lang = '$lang'"), array(), $select::JOIN_LEFT)
                ->join(array('pm' => 'product_media'), new Expression("pm.product_id = p.product_id"), array(), $select::JOIN_LEFT)
                ->join(array('m' => 'media'), new Expression('pm.media_id = m.media_id'), array(), $select::JOIN_LEFT)
                ->join(array('pmt' => 'product_media_type'), new Expression("pmt.type_id = p.type_id and pmt.reference = 'PICTURE'"), array(), $select::JOIN_LEFT)
                ->join(array('ppls' => 'product_pricelist_stat'), new Expression("ppls.product_pricelist_stat_id = ppl.product_pricelist_id"), array(), $select::JOIN_LEFT)
                ->join(array('primary_color' => 'color'), new Expression("primary_color.color_id = p.primary_color_id"), array(), $select::JOIN_LEFT)
                ->where('p.flag_active = 1')
                ->where('ppl.flag_active = 1')
                ->where("pl.reference = '$pricelist'");


        if ($enable_packaging_columns) {
            $packSelect = $this->getPackagingInnerSelect();
            $select->join(array('packs' => $packSelect), new Expression("packs.product_id = p.product_id"), array(), $select::JOIN_LEFT);
        }

        $this->assignFilters($select);


        $now = new \DateTime();
        $flag_new_min_date = $now->sub(new \DateInterval('P180D'))->format('Y-m-d'); // 180 days
        //$flag_new_min_date = date('2013-11')
        //$flag_new_min_date = ProductFilter::getParam('flag_new_minimum_date');

        if ($this->columns !== null && is_array($this->columns)) {
            $columns = $this->columns;
        } else {
            $columns = array(
                'product_id' => new Expression('p.product_id'),
                'status_id' => new Expression('pst.status_id'),
                'status_reference' => new Expression('pst.reference'),
                'pricelist_reference' => new Expression('pl.reference'),
                'type_id' => new Expression('p.type_id'),
                'reference' => new Expression('p.reference'),
                'display_reference' => new Expression('COALESCE(p.display_reference, p.reference)'),
                'brand_id' => new Expression('p.brand_id'),
                'brand_reference' => new Expression('pb.reference'),
                'brand_title' => new Expression('pb.title'),
                'group_id' => new Expression('pg.group_id'),
                'group_reference' => new Expression('pg.reference'),
                'category_id' => new Expression('pc.category_id'),
                'category_reference' => new Expression('pc.reference'),
                //'category_title' => new Expression('COALESCE(pc18.title, pc.title)'),
                'category_breadcrumb' => new Expression('COALESCE(pc18.breadcrumb, pc.breadcrumb)'),
                'title' => new Expression('COALESCE(p18.title, p.title, p18.invoice_title, p.invoice_title)'),
                'invoice_title' => new Expression('COALESCE(p18.invoice_title, p.invoice_title)'),
                'description' => new Expression('COALESCE(p18.description, p.description)'),
                //'description' => new Expression('if(p2.product_id is null, COALESCE(p18.description, p.description), COALESCE(p2_18.description, p2.description, p.description) )'),
// @killparent, when the id_art_tete will be fully removed, use the second commented column instead of this one
// This hack allows to not include twice the parent description            
'description' => new Expression('
        CONCAT_WS("\n",
            pstub18.description_header,
            if ((pstub.product_stub_id is not null and p.parent_id is null), 
                    null, 
                    if (p.product_stub_id is null,
                        COALESCE(p18.description, p.description),
                        p18.description
                    )
                ),
            pstub18.description_footer
        )    
        '),                      
/*            
'description' => new Expression('
        CONCAT_WS("\n",
            pstub18.description_header,
            COALESCE(p18.description, p.description)
            pstub18.description_footer
        )    
        '),                      
*/
                'description_header' => new Expression('pstub18.description_header'),
                'description_footer' => new Expression('pstub18.description_footer'),
                'characteristic' => new Expression('COALESCE(p18.characteristic, p.characteristic)'),
                'keywords' => new Expression('COALESCE(p18.keywords, p.keywords)'),
                'price' => new Expression('ppl.price'),
                'list_price' => new Expression('ppl.list_price'),
                'public_price' => new Expression('ppl.public_price'),
                'map_price' => new Expression('ppl.map_price'),
                'barcode_ean' => new Expression('p.barcode_ean13'),
                'barcode_upc' => new Expression('p.barcode_upca'),
                'flag_new' => new Expression("(COALESCE(pl.new_product_min_date, '$flag_new_min_date') <= COALESCE(ppl.available_at, p.available_at))"),
                'discount_1' => new Expression('ppl.discount_1'),
                'discount_2' => new Expression('ppl.discount_2'),
                'discount_3' => new Expression('ppl.discount_3'),
                'discount_4' => new Expression('ppl.discount_4'),
                'is_promotional' => new Expression('ppl.is_promotional'),
                'is_liquidation' => new Expression('ppl.is_liquidation'),
                'is_bestseller' => new Expression('ppl.is_bestseller'),
                'is_bestvalue' => new Expression('ppl.is_bestvalue'),
                'is_hot' => new Expression('ppl.is_hot'),
                'is_new' => new Expression('ppl.is_new'),
                'available_stock' => new Expression('ps.available_stock'),
                'next_available_stock' => new Expression('ps.next_available_stock'),
                'next_available_stock_at' => new Expression('ps.next_available_stock_at'),
                'theoretical_stock' => new Expression('ps.theoretical_stock'),
                'next_theoretical_stock' => new Expression('ps.theoretical_stock + (ps.available_stock - ps.next_available_stock)'),
                'next_theoretical_stock_at' => new Expression('ps.next_available_stock_at'),
                'stock_updated_at' => new Expression('ps.updated_at'),
                'sale_minimum_qty' => new Expression('ppl.sale_minimum_qty'),
                'forecasted_monthly_sales' => new Expression('ppls.forecasted_monthly_sales'),
                'weight' => new Expression('p.weight'),
                'volume' => new Expression('p.volume'),
                'length' => new Expression('p.length'),
                'width' => new Expression('p.width'),
                'height' => new Expression('p.height'),
                'diameter' => new Expression('p.diameter'),
                'format' => new Expression('p.format'),
                'currency_reference' => new Expression('c.reference'),
                'currency_symbol' => new Expression('c.symbol'),
                'unit_reference' => new Expression('pu.reference'),
                'type_reference' => new Expression('pt.reference'),
                'flag_till_end_of_stock' => new Expression('pst.flag_till_end_of_stock'),
                'flag_end_of_lifecycle' => new Expression('pst.flag_end_of_lifecycle'),
                'available_at' => new Expression('COALESCE(ppl.available_at, p.available_at)'),
                'status_reference' => new Expression('pst.reference'),
                'status_title' => new Expression('pst.title'),
                'status_legacy_mapping' => new Expression('pst.legacy_mapping'),
                'primary_color_id' => new Expression('p.primary_color_id'),
                'primary_color_name' => new Expression('primary_color.name'),
                'primary_color_hex_code' => new Expression('primary_color.hex_code')
            );

            if ($enable_packaging_columns) {
                $columns = array_merge($columns, $this->getPackagingColumns());
            }
        }


        $select->columns($columns, true);

        $product_id = $params->get('id');
        if ($product_id != '') {
            $select->where("p.product_id = $product_id");
        }

        $brands = $params->get('brands');
        if (count($brands) > 0) {
            $brand_clauses = array();
            foreach ($brands as $brand_reference) {
                $brand_clauses[] = "pb.reference = '$brand_reference'";
            }
            $select->where('(' . join(' OR ', $brand_clauses) . ')');
        }

        $categories = $params->get('categories');
        if ($categories !== null && count($categories) > 0) {
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
            if (count($category_clauses) > 0) {
                $select->where('(' . join(' or ', $category_clauses) . ')');
            }
        }

        if (($query = trim($params->get('query'))) != "") {
            $platform = $this->adapter->getPlatform();


            $quoted = $platform->quoteValue($query);

            $searchable_ref = $this->getSearchableReference($query);

            // 1. TEST PART WITH
            // BARCODE, SEARCH_REFERENCE, PRODUCT_ID,

            $matches = array();
            if (is_numeric($query) && u::strlen($query) < 20) {
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
            if (u::strlen($query) > 3) {
                $matches[1000000] = 'p18.title like ' . $platform->quoteValue('%' . join('%', $splitted) . '%');
                $matches[1000001] = 'p18.keywords like ' . $platform->quoteValue('%' . join('%', $splitted) . '%');
                $matches[100000] = '(p18.title is null and p.title like ' . $platform->quoteValue('%' . join('%', $splitted) . '%') . ")";
            }

            if (u::strlen($query) > 5) {
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
        } else {
            $relevance = "'A'";  // Constant to sort on;
        }

        // Automatically add relevance column
        $columns = array_merge($select->getRawState(Select::COLUMNS), array('relevance' => new Expression($relevance)));

        // Add medias id

        $group_columns = [
            'picture_media_id' => new Expression('MAX(if(pm.flag_primary = 1, pm.media_id, null))'),
            'picture_media_filemtime' => new Expression('MAX(if(pm.flag_primary = 1, m.filemtime, null))'),
            'alternate_medias' => new Expression("GROUP_CONCAT(if(pm.flag_primary is null, CONCAT(pm.media_id, ':' ,m.filemtime), null))")
        ];

        $select->columns(array_merge($columns, $group_columns));
        $select->group(array_keys($columns));

        $select->order(array('relevance desc', 'pc.global_sort_index', 'p.sort_index', 'p.display_reference'));

        return $select;
    }

    /**
     * Return quoted searchable reference from a keyword
     * @param string $reference
     * @return string
     */
    protected function getSearchableReference($reference, $wildcards_starts_at_char = 4, $max_reference_length = 20)
    {
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
