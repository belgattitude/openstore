<?php

namespace Openstore\Model\Browser;

use Openstore\Core\Model\Browser\AbstractBrowser;
//use Openstore\Catalog\Browser\SearchParams\SearchParamsAbstract as SearchParams;
//use Openstore\Catalog\Browser\ProductFilter;
use Zend\Db\Sql\Sql;
use Zend\Db\Sql\Select;
use Zend\Db\Sql\Expression;

class CategoryBrowser extends AbstractBrowser
{

    /**
     *
     * @var array;
     */
    protected $options;

    public function getDefaultOptions()
    {
        return [
            'expanded_category' => null,
            'depth' => 4,
            'include_empty_nodes' => false,
        ];
    }

    /**
     *
     * @param string $key
     * @param string $value
     * @return \Openstore\Model\Browser\CategoryBrowser
     */
    public function setOption($key, $value)
    {
        if ($this->options === null) {
            $this->options = [];
        }
        $this->options[$key] = $value;
        return $this;
    }

    public function getOptions()
    {
        if ($this->options === null) {
            return $this->getDefaultOptions();
        }
        return $this->options;
    }

    /**
     * @return array
     */
    public function getSearchableParams()
    {
        return [
            'language' => ['required' => true],
            'pricelist' => ['required' => true],
            'query' => ['required' => false],
            'brands' => ['required' => false],
            'categories' => ['required' => false],
            'id' => ['required' => false],
        ];
    }

    /**
     *
     * @return \Zend\Db\Sql\Select
     */
    public function getSelect()
    {
        $params = $this->getSearchParams();
        $options = $this->getOptions();

        $lang = $params->get('language');
        $pricelist = $params->get('pricelist');



        $subselect = new Select();
        $subselect->from(['p' => 'product'], [])
                ->join(['ppl' => 'product_pricelist'], new Expression('ppl.product_id = p.product_id'), [])
                ->join(['pl' => 'pricelist'], new Expression('pl.pricelist_id = ppl.pricelist_id'), [])
                ->join(['ps' => 'product_stock'], new Expression('ps.stock_id = pl.stock_id and ps.product_id = p.product_id'), [])
                ->join(['pb' => 'product_brand'], new Expression('pb.brand_id = p.brand_id'), [])
                ->where('p.flag_active = 1')
                ->where('ppl.flag_active = 1')
                ->where("pl.reference = '$pricelist'")
                ->columns([
                    'product_id' => new Expression('p.product_id'),
                    'category_id' => new Expression('p.category_id'),
                ])
                ->group(['p.product_id', 'p.category_id']);

        $this->assignFilters($subselect);

        $brands = $params->get('brands');
        if ($brands != '' && count($brands) > 0) {
            $brand_clauses = [];
            foreach ($brands as $brand_reference) {
                $brand_clauses[] = "pb.reference = '$brand_reference'";
            }

            $subselect->where('(' . implode(' OR ', $brand_clauses) . ')');
        }

        $select = new Select();

        if (($expanded_category = $options['expanded_category']) !== null) {
            $open_categories = [];
            $ancestors = $this->model->getAncestors($expanded_category, $lang);
            foreach ($ancestors as $ancestor) {
                $open_categories[$ancestor['category_id']] = $ancestor['reference'];
            }
            $open_categories = "(" . implode(',', array_keys($open_categories)) . ")";
        } else {
            $open_categories = '(null)';
        }


        $select->from(['parent' => 'product_category'], [])
                ->join(['node' => 'product_category'], new Expression("node.lft BETWEEN parent.lft AND parent.rgt"), [], $select::JOIN_LEFT)
                ->join(['pc18' => 'product_category_translation'], new Expression("pc18.category_id = parent.category_id and pc18.lang = '$lang'"), [], $select::JOIN_LEFT)
                ->join(['p' => $subselect], new Expression("node.category_id = p.category_id"), [], $select::JOIN_LEFT);


        $columns = [
            'id' => new Expression('parent.category_id'),
            'reference' => new Expression('parent.reference'),
            'title' => new Expression('COALESCE(pc18.title, parent.title)'),
            //'title' => new Expression('parent.title'),
            //'is_leaf' => new Expression('if(parent.rgt = (parent.lft+1), 1, 0)'),
            'is_leaf' => new Expression('CASE WHEN parent.rgt = (parent.lft+1) THEN 1 ELSE 0 END'),
            'parent_id' => new Expression('parent.parent_id'),
            'lvl' => new Expression('parent.lvl'),
            'lft' => new Expression('parent.lft'),
            'rgt' => new Expression('parent.rgt'),
            'is_expanded' => new Expression("parent.category_id in $open_categories"),
            'sort_index' => new Expression('parent.sort_index')
        ];

        $select->columns(
            array_merge($columns, [
            'count_product' => new Expression('COUNT(p.product_id)'),
            'count_subcategs' => new Expression('GROUP_CONCAT(distinct if(node.lvl = parent.lvl+1, node.reference, null))')
                ]),
            true
        );

        $select->group($columns);

        if (($depth = $options['depth']) != 0) {
            if ($expanded_category != '') {
                $ancestors = $this->model->getAncestors($expanded_category, $lang)->toArray();

                $clauses = ['parent.lvl = 1'];
                foreach ($ancestors as $idx => $ancestor) {
                    //if ($idx < 4) {
                    $clauses[$ancestor['reference']] = "(parent.lft between " . $ancestor['lft'] . " and " . $ancestor['rgt'] . ' and parent.lvl = ' . ($ancestor['lvl'] + 1) . ')';
                    //}
                }
                $select->where('(' . implode(' or ', $clauses) . ')');
            } else {
                $select->where("(parent.lvl <= $depth)");
            }
        }


        if (!$options['include_empty_nodes']) {
            $select->having('count_product > 0');
        }


        $select->order(['parent.lft' => $select::ORDER_ASCENDING, 'parent.sort_index' => $select::ORDER_ASCENDING]);

        $adapter = $this->adapter;
        $sql = new Sql($adapter);
        $sql_string = $sql->buildSqlString($select);

        return $select;
    }
}
