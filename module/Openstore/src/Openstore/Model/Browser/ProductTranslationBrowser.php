<?php

namespace Openstore\Model\Browser;

use Openstore\Core\Model\Browser\AbstractBrowser;
use Openstore\Model\Util\ProductSearchableReference;
//use Openstore\Catalog\Browser\SearchParams\SearchParamsAbstract as SearchParams;
//use Openstore\Catalog\Browser\ProductFilter;
use Zend\Db\Sql\Sql;
use Soluble\Db\Sql\Select;
use Zend\Db\Adapter\Adapter;
use Zend\Db\Sql\Expression;
use Patchwork\Utf8 as u;

class ProductTranslationBrowser extends AbstractBrowser
{
    /**
     * @return array
     */
    public function getSearchableParams()
    {
        return [
            'master_language' => ['required' => true],
            'target_languages' => ['required' => true],
            'pricelists' => ['required' => false],
            'query' => ['required' => false],
            'brands' => ['required' => false],
            'categories' => ['required' => false],
            'id' => ['required' => false],
            'product_id' => ['required' => false],
            'exclusions' => ['required' => false],
            'updated_by' => ['required' => false]
        ];
    }

    /**
     *
     * @return Select
     */
    public function getSelect()
    {


        $params = $this->getSearchParams();

        $pricelist = $params->get('pricelist');

        $select = new Select();
        $select->setDbAdapter($this->adapter);


        $master_language = $params['master_language'];
        if (isset($params['target_languages']) && $params['target_languages'] != '') {
            $languages  = $params['target_languages'];
        } else {
            $languages = [];
        }

        if (isset($params['pricelists']) && $params['pricelists'] != '') {
            $pricelists = $params['pricelists'];
        } else {
            $pricelists = [];
        }

        $updated_by_logins = [];
        if (isset($params['updated_by']) && $params['updated_by'] != '') {
            $updated_by_logins = $params['updated_by'];
        }


        if (isset($params['brands']) && $params['brands'] != '') {
            $brands = $params['brands'];
        } else {
            $brands = [];
        }


        if (isset($params['types']) && $params['types'] != '') {
            $types = $params['types'];
        } else {
            $types = [];
        }


        $lang_clause = '(' . implode(',', array_map(function ($lang) {
            return "'" . $lang . "'";
        }, $languages)) . ")";

        foreach ($languages as $lang) {
            $inner_columns["invoice_title_$lang"] = new Expression("COALESCE(MAX(if(p18.lang = '$lang', p18.invoice_title, null)), '')");
            $inner_columns["title_$lang"] = new Expression("COALESCE(MAX(if(p18.lang = '$lang', p18.title, null)), '')");
            $inner_columns["description_$lang"] = new Expression("COALESCE(MAX(if(p18.lang = '$lang', p18.description, null)), '')");
            $inner_columns["characteristic_$lang"] = new Expression("COALESCE(MAX(if(p18.lang = '$lang', p18.characteristic, null)), '')");
            $inner_columns["keywords_$lang"] = new Expression("COALESCE(MAX(if(p18.lang = '$lang', p18.keywords, null)), '')");
            $inner_columns["created_at_$lang"] = new Expression("DATE_FORMAT(MAX(if(p18.lang = '$lang', p18.created_at, null)), '%Y-%m-%dT%H:%i:%s')");
            $inner_columns["updated_at_$lang"] = new Expression("DATE_FORMAT(MAX(if(p18.lang = '$lang', p18.updated_at, null)), '%Y-%m-%dT%H:%i:%s')");
            $inner_columns["created_by_$lang"] = new Expression("MAX(if(p18.lang = '$lang', p18.created_by, null))");
            $inner_columns["updated_by_$lang"] = new Expression("MAX(if(p18.lang = '$lang', p18.updated_by, null))");
            // useful for testing multiple windows save
            //$inner_columns["version_timestamp_$lang"] = new Expression("UNIX_TIMESTAMP(MAX(IF(p18.lang = '$lang', if(p18.updated_at is null, p18.created_at, p18.updated_at), null)))");
            $inner_columns["version_timestamp_$lang"] = new Expression("MAX(IF(p18.lang = '$lang', if(p18.updated_at is null, p18.created_at, p18.updated_at), null))");
            $inner_columns["revision_$lang"] = new Expression("MAX(if(p18.lang = '$lang', p18.revision, null))");
            $inner_columns["count_chars_$lang"] = new Expression("MAX(if(p18.lang = '$lang', CHAR_LENGTH(CONCAT(COALESCE(p18.title, ''), COALESCE(p18.description, ''), COALESCE(p18.characteristic, ''))), null))");
            $inner_columns["usp_$lang"] = new Expression("COALESCE(MAX(if(p18.lang = '$lang', p18.usp, null)), '')");
            $inner_columns["additional_description_$lang"] = new Expression("COALESCE(MAX(if(p18.lang = '$lang', p18.additional_description, null)), '')");
        }

        $select->from(['p' => 'product'], [])
                //->join(['tr' => $innerSelect], 'tr.product_id = p.product_id')
                ->join(
                    ['p18' => 'product_translation'],
                    new Expression("p18.product_id = p.product_id and p18.lang in $lang_clause"),
                    [],
                    $select::JOIN_LEFT
                )
                ->join(
                    ['p2' => 'product'],
                    new Expression('p2.product_id = p.parent_id'),
                    [],
                    $select::JOIN_LEFT
                )
                ->join(['pb' => 'product_brand'], new Expression('pb.brand_id = p.brand_id'), [])
                ->join(['pg' => 'product_group'], new Expression('pg.group_id = p.group_id'), [], $select::JOIN_LEFT)
                ->join(['pc' => 'product_category'], new Expression('pc.category_id = p.category_id'), [])
                ->join(['pc18' => 'product_category_translation'], new Expression("pc.category_id = pc18.category_id and pc18.lang = '$lang'"), [], $select::JOIN_LEFT)
                ->join(
                    ['psi' => 'product_search'],
                    new Expression("psi.product_id = p.product_id and psi.lang = '$lang'"),
                    [],
                    $select::JOIN_LEFT
                )

                ->join(['pt' => 'product_type'], new Expression('p.type_id = pt.type_id'), [], $select::JOIN_LEFT)
                //->join(array('c' => 'currency'), new Expression('c.currency_id = pl.currency_id'), array(), $select::JOIN_LEFT)
                //->join(array('pu' => 'product_unit'), new Expression('pu.unit_id = p.unit_id'), array(), $select::JOIN_LEFT)
                //->join(array('ps' => 'product_stock'), new Expression('ps.stock_id = pl.stock_id and ps.product_id = p.product_id'), array())
                ->join(['pst' => 'product_status'], new Expression('pst.status_id = p.status_id'), [], $select::JOIN_LEFT)
                ->join(['pm' => 'product_media'], new Expression("pm.product_id = p.product_id and pm.flag_primary=1"), [], $select::JOIN_LEFT)
                ->join(['m' => 'media'], new Expression('pm.media_id = m.media_id'), [], $select::JOIN_LEFT)
                ->join(['pmt' => 'product_media_type'], new Expression("pmt.type_id = p.type_id and pmt.reference = 'PICTURE'"), [], $select::JOIN_LEFT);



        $select->where('p.flag_active = 1');



        // Adding languages and pricelists selections
        if (count($pricelists) > 0) {
            $select->join(['ppl' => 'product_pricelist'], new Expression('ppl.product_id = p.product_id'), [])
                   ->join(['pl' => 'pricelist'], new Expression('pl.pricelist_id = ppl.pricelist_id'), []);
            $select->where(['pl.reference' => $pricelists]);
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
                'created_at' => new Expression("DATE_FORMAT(p.created_at, '%Y-%m-%dT%H:%i:%s')"),
                'available_at' => new Expression("DATE_FORMAT(p.available_at, '%Y-%m-%dT%H:%i:%s')"),
                'picture_media_filemtime' => new Expression('m.filemtime')

        ];

        if (count($updated_by_logins) > 0) {
            $ubls = [];
            foreach ($updated_by_logins as $login) {
                $ubls[] = $this->adapter->getPlatform()->quoteValue($login);
            }
            $ubl = implode(',', $ubls);
            $filter_updated_clause = new Expression("SUM(if(p18.updated_by in ($ubl), 1, 0))");
            $having_updated_clause = "filter_updated_by > 0";
        } else {
            $filter_updated_clause = new Expression("1");
            $having_updated_clause = "";
        }

        // Processing filters

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
            $category_clauses = [];
            foreach ($categories as $category_reference) {
                $spb = new Select();
                $spb->from('product_category')
                        ->columns(['category_id', 'lft', 'rgt'])
                        ->where(['reference' => $category_reference])
                        ->limit(1);
                $sql_string = $sql->getSqlStringForSqlObject($spb);
                $results = $this->adapter->query($sql_string, Adapter::QUERY_MODE_EXECUTE)->toArray();
                if (count($results) > 0) {
                    $category_clauses[] = 'pc.lft between ' . $results[0]['lft'] . ' and ' . $results[0]['rgt'];
                }
            }
            if (count($category_clauses) > 0) {
                $select->where('(' . implode(' or ', $category_clauses) . ')');
            }
        }

        if (($query = trim($params->get('query'))) != "") {
            $platform = $this->adapter->getPlatform();

            $quoted = $platform->quoteValue($query);

            $searchable_ref = $this->getSearchableReference($query);



            // 1. TEST PART WITH
            // BARCODE, SEARCH_REFERENCE, PRODUCT_ID,

            $matches = [];
            if (is_numeric($query) && u::strlen($query) < 20) {
                // Can be a barcode or a product_id,
                $matches[1000000000] = "p.product_id = $query";

                if (u::strlen($query) > 10) {
                    $matches[1000000000] = "p.barcode_ean13 = '$query'";
                    $matches[100000000] = "p.barcode_upca = '$query'";
                }
            }

            $splitted = explode(' ', preg_replace('!\s+!', ' ', $query));

            // test title in order
            if ($searchable_ref != "") {
                $matches[10000000] = "p.search_reference like " . $platform->quoteValue($searchable_ref . '%');
                $matches[1000000] = "p.search_reference like " . $platform->quoteValue('%' . $searchable_ref . '%');
            }

            // ALL MATCHES from product table are added to the
            // where clause
            // to avoid limiting results from left joined table
            // product_translation

            $product_where_clauses = $matches;

            //echo "p.search_reference like CONCAT('%', get_searchable_reference($quoted), '%')";
            //die();
            if (u::strlen($query) > 3) {
                $matches[1000000] = 'p18.title like ' . $platform->quoteValue('%' . implode('%', $splitted) . '%');
                $matches[1000001] = 'p18.keywords like ' . $platform->quoteValue('%' . implode('%', $splitted) . '%');
                $matches[100000] = '(p18.title is null and p.title like ' . $platform->quoteValue('%' . implode('%', $splitted) . '%') . ")";
            }
            if (u::strlen($query) > 3) {
                $matches[10000] = 'psi.keywords like ' . $platform->quoteValue('%' . implode('%', $splitted) . '%');
            }

            $matches[0] = 'MATCH (psi.keywords) AGAINST (' . $platform->quoteValue(implode(' ', $splitted)) . ' IN NATURAL LANGUAGE MODE)';

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

            //$select->where("(" . join(' or ', array_values($product_where_clauses)) . ")");

            $keyword_matches_clause = new Expression(
                'sum(' .
                'if (' .
                implode(' or ', array_values($matches)) .
                ', 1, 0))'
            );
            $having_keywords_clause = 'keywords_matches > 0';
        } else {
            $relevance = "'A'";  // Constant to sort on;
            $keyword_matches_clause = new Expression('1');
            $having_keywords_clause = false;
        }




        //
        $full_columns = array_merge(
            $columns,
            $inner_columns,
            [
                'min_revision' => new Expression('MIN(COALESCE(p18.revision, 0))'),
                'max_updated_at' => new Expression('MAX(p18.updated_at)'),
                'max_revision' => new Expression('MAX(COALESCE(p18.revision, 0))'),
                'nb_distinct_revision' => new Expression('COUNT(distinct COALESCE(p18.revision, 9999999))'),
                'filter_updated_by' => $filter_updated_clause,
                'keywords_matches' => $keyword_matches_clause,
                'relevance' => new Expression($relevance)
            ]
        );

        $select->columns($full_columns, true);
        $select->group(array_keys($columns));


        // Automatically add relevance column
        //$columns = array_merge($select->getRawState(Select::COLUMNS), array('relevance' => new Expression($relevance)));
        //$select->columns($columns);

        $exclusions = $params['exclusions'];
        if (is_array($exclusions) && count($exclusions) > 0) {
            foreach ($exclusions as $exclusion) {
                switch ($exclusion) {
                    case 'till_end_of_stock':
                        $select->where('COALESCE(pst.flag_till_end_of_stock, 0) <> 1');
                        break;
                    case 'end_of_lifecycle':
                        $select->where('COALESCE(pst.flag_end_of_lifecycle, 0) <> 1');
                        break;
                }
            }
        }


        $filters = $params['filters'];

        $filter_having_clauses = [];
        if (is_array($filters) && count($filters) > 0) {
            foreach ($filters as $filter) {
                switch ($filter) {
                    case 'untranslated':
                        if (count($languages) > 1) {
                            $clauses = [];
                            foreach ($languages as $lang) {
                                if ($lang != $master_language) {
                                    $clauses[] = "count_chars_$lang = 0";
                                }
                            }
                            $filter_having_clauses[] = "(count_chars_$master_language > 0 and (" . implode(' or ', $clauses) . '))';
                        } else {
                            $filter_having_clauses[] = "count_chars_$master_language = 0";
                        }
                        break;
                    case 'revised':
                        if (count($languages) > 1) {
                            //$filter_having_clauses[] = 'nb_distinct_revision > 1';
                            $clauses = [];
                            foreach ($languages as $lang) {
                                if ($lang != $master_language) {
                                    $clauses[] = "count_chars_$lang > 0";
                                }
                            }
                            $filter_having_clauses[] = "(nb_distinct_revision > 1 and (count_chars_$master_language > 0 and (" . implode(' or ', $clauses) . ')))';
                        }
                        break;
                }
            }
        }

        $havings = [];
        if (count($filter_having_clauses) > 0) {
            $havings[] = "(" . implode(' or ', $filter_having_clauses)  . ")";
        }
        if ($having_updated_clause != '') {
            $havings[] = $having_updated_clause;
        }

        if ($having_keywords_clause != '') {
            $havings[] = $having_keywords_clause;
        }


        if (count($havings) > 0) {
            $select->having(implode(' and ', $havings));
        }

        $order_columns = ['relevance desc'];
        if ($params['order']) {
            $order_columns = array_merge($order_columns, $params['order']);
        }

        $select->order($order_columns);
        //echo $select->getSql(); die();
        return $select;
    }

    /**
     * Return quoted searchable reference from a keyword
     * @param string $reference
     * @return string
     */
    protected function getSearchableReference($reference, $wildcards_starts_at_char = 3, $max_reference_length = 20)
    {
        $psr = new ProductSearchableReference([
            'ref_min_length' => 1,
            'ref_max_length' => $max_reference_length,
            'ref_validation_regexp' => '/^%?([A-Za-z0-9)([A-Za-z0-9\ \_\-\/\*])+$/',
            'sql_wildcards_starts_at_char' => $wildcards_starts_at_char
        ]);
        return $psr->getReferenceSqlSearch($reference);
    }
}
