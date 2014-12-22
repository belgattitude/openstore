<?php

namespace Openstore\Model\Browser;

use Openstore\Core\Model\Browser\AbstractBrowser;
//use Openstore\Catalog\Browser\SearchParams\SearchParamsAbstract as SearchParams; 
//use Openstore\Catalog\Browser\ProductFilter;
use Zend\Db\Sql\Sql;
use Soluble\Db\Sql\Select;
use Zend\Db\Adapter\Adapter;
use Zend\Db\Sql\Expression;


class ProductTranslationBrowser extends AbstractBrowser {

    /**
     * @return array
     */
    function getSearchableParams() {
        return array(
            'primary_language' => array('required' => false),
            'languages' => array('required' => true),
            'pricelists' => array('required' => false),
            'query' => array('required' => false),
            'brands' => array('required' => false),
            'categories' => array('required' => false),
            'id' => array('required' => false),
            'product_id' => array('required' => false)
        );
    }

    /**
     * 
     * @return Select
     */
    function getSelect() {
        
        $params = $this->getSearchParams();

        $pricelist = $params->get('pricelist');

        $select = new Select();
        $select->setDbAdapter($this->adapter);


        $lang = $params['primary_language'];
        if (isset($params['languages']) && $params['languages'] != '') {
            $languages  = $params['languages'];
        } else {
            $languages = array();
        }
        
        if (isset($params['pricelists']) && $params['pricelists'] != '') {
            $pricelists = $params['pricelists'];
        } else {
            $pricelists = array();
        } 

        if (isset($params['brands']) && $params['brands'] != '') {
            $brands = $params['brands'];
        } else {
            $brands = array();
        } 
        
        
        if (isset($params['types']) && $params['types'] != '') {
            $types = $params['types'];
        } else {
            $types = array();
        }         

        
        $lang_clause = '(' . join(',', array_map(function($lang) { return "'" . $lang . "'"; }, $languages)) . ")";
        
        foreach($languages as $lang) {
            $inner_columns["invoice_title_$lang"] = new Expression("COALESCE(MAX(if(p18.lang = '$lang', p18.invoice_title, null)), '')");
            $inner_columns["title_$lang"] = new Expression("COALESCE(MAX(if(p18.lang = '$lang', p18.title, null)), '')");
            $inner_columns["description_$lang"] = new Expression("COALESCE(MAX(if(p18.lang = '$lang', p18.description, null)), '')");
            $inner_columns["characteristic_$lang"] = new Expression("COALESCE(MAX(if(p18.lang = '$lang', p18.characteristic, null)), '')");
            $inner_columns["created_at_$lang"] = new Expression("DATE_FORMAT(MAX(if(p18.lang = '$lang', p18.created_at, null)), '%Y-%m-%dT%TZ')");
            $inner_columns["updated_at_$lang"] = new Expression("DATE_FORMAT(MAX(if(p18.lang = '$lang', p18.updated_at, null)), '%Y-%m-%dT%TZ')");
            $inner_columns["created_by_$lang"] = new Expression("MAX(if(p18.lang = '$lang', p18.created_by, null))");
            $inner_columns["updated_by_$lang"] = new Expression("MAX(if(p18.lang = '$lang', p18.updated_by, null))");
            $inner_columns["revision_$lang"] = new Expression("MAX(if(p18.lang = '$lang', p18.revision, null))");
        }
        
        $select->from(array('p' => 'product'), array())
                //->join(['tr' => $innerSelect], 'tr.product_id = p.product_id')
                ->join(['p18' => 'product_translation'], 
                        new Expression("p18.product_id = p.product_id and p18.lang in $lang_clause"),
                        array(), $select::JOIN_LEFT)
                ->join(['p2' => 'product'], 
                        new Expression('p2.product_id = p.parent_id'),
                        array(), $select::JOIN_LEFT)
                ->join(array('pb' => 'product_brand'), new Expression('pb.brand_id = p.brand_id'), array())
                ->join(array('pg' => 'product_group'), new Expression('pg.group_id = p.group_id'), array(), $select::JOIN_LEFT)
                ->join(array('pc' => 'product_category'), new Expression('pc.category_id = p.category_id'), array())
                ->join(array('pc18' => 'product_category_translation'), new Expression("pc.category_id = pc18.category_id and pc18.lang = '$lang'"), array(), $select::JOIN_LEFT)                
                ->join(array('psi' => 'product_search'), 
                        new Expression("psi.product_id = p.product_id and psi.lang = '$lang'"), 
                        array(), 
                        $select::JOIN_LEFT)
                
                ->join(array('pt' => 'product_type'), new Expression('p.type_id = pt.type_id'), array(), $select::JOIN_LEFT)
                //->join(array('c' => 'currency'), new Expression('c.currency_id = pl.currency_id'), array(), $select::JOIN_LEFT)
                //->join(array('pu' => 'product_unit'), new Expression('pu.unit_id = p.unit_id'), array(), $select::JOIN_LEFT)
                //->join(array('ps' => 'product_stock'), new Expression('ps.stock_id = pl.stock_id and ps.product_id = p.product_id'), array())
                ->join(array('pst' => 'product_status'), new Expression('pst.status_id = p.status_id'), array(), $select::JOIN_LEFT)
                ->join(array('pm' => 'product_media'), new Expression("pm.product_id = p.product_id and pm.flag_primary=1"), array(), $select::JOIN_LEFT)
                ->join(array('pmt' => 'product_media_type'), new Expression("pmt.type_id = p.type_id and pmt.reference = 'PICTURE'"), array(), $select::JOIN_LEFT);
        
        
        
        $select->where('p.flag_active = 1');
                
        // Adding languages and pricelists selections
        if (count($pricelists) > 0) {
            $select->join(array('ppl' => 'product_pricelist'), new Expression('ppl.product_id = p.product_id'), array())
                   ->join(array('pl' => 'pricelist'), new Expression('pl.pricelist_id = ppl.pricelist_id'), array());
            $select->where(array('pl.reference' => $pricelists));
            $select->where('ppl.flag_active = 1');
        }


        $columns = [
                'product_id' => new Expression('p.product_id'),
                'reference' => new Expression('p.reference'),
                'parent_reference' => new Expression('p2.reference'),
                'display_reference' => new Expression('COALESCE(p.display_reference, p.reference)'),
                'brand_reference' => new Expression('pb.reference'),
                'brand_title' => new Expression('pb.title'),
                'group_reference' => new Expression('pg.reference'),
                'category_reference' => new Expression('pc.reference'),
                'category_title' => new Expression('COALESCE(pc18.title, pc.title)'),
                'category_breadcrumb' => new Expression('COALESCE(pc18.breadcrumb, pc.breadcrumb)'),
                'status_reference' => new Expression('pst.reference'),
                'flag_end_of_lifecycle' => new Expression('pst.flag_end_of_lifecycle'),
                'flag_till_end_of_stock' => new Expression('pst.flag_till_end_of_stock'),
                'picture_media_id' => new Expression('pm.media_id'),
                'created_at' => new Expression("DATE_FORMAT(p.created_at, '%Y-%m-%dT%TZ')"),
                'available_at' => new Expression("DATE_FORMAT(p.available_at, '%Y-%m-%dT%TZ')"),
            
        ];
        
        $select->columns(array_merge(
                $columns, 
                $inner_columns,
                [   
                    'max_revision' => new Expression('MAX(p18.revision)'),
                    'nb_distinct_revision' => new Expression('COUNT(distinct p18.revision)')
                ]
                ), true);
        $select->group(array_keys($columns));
        
        $product_id = $params->get('product_id');
        if ($product_id != '') {
            $select->where("p.product_id = $product_id");
        }
        
        if (count($types) > 0) {
            $select->where(['p.type_id' => $types]);
        }        
        
        if (count($brands) > 0) {
            $select->where(['pb.reference' => $brands]);
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
                $matches[100000] = '(p18.title is null and p.title like ' . $platform->quoteValue('%' . join('%', $splitted) . '%') . ")";
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
        } else {
            $relevance = "'A'";  // Constant to sort on;
        }

        // Automatically add relevance column
        $columns = array_merge($select->getRawState(Select::COLUMNS), array('relevance' => new Expression($relevance)));
        $select->columns($columns);

        $select->order(array('relevance desc', 'pc.global_sort_index', 'p.sort_index', 'p.display_reference'));
        
        
        $filters = $params['filters'];
        if (is_array($filters) && count($filters) > 0) {
            

            foreach($filters as $filter) {
                
                switch($filter) {
                    case 'untranslated' :
                        $select->where('COALESCE(p18.revision, 0) = 0');
                        break;

                    case 'revised' :
                        //foreach()
                        $select->having('nb_distinct_revision > 1');
                        break;
                    
                }
                
                
            }
            
        }
        
        /*
          echo '<pre>';

          echo $select->getSql();
          die();
         * 
         */
        //$select->order($relevance);



        return $select;
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
