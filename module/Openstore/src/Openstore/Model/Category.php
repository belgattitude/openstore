<?php
namespace Openstore\Model;

use Openstore\Core\Model\AbstractModel;
use Openstore\Core\Model\BrowsableInterface;
use Openstore\Model\Browser\CategoryBrowser;
use Zend\Db\Sql\Sql;
use Zend\Db\Sql\Expression;

class Category extends AbstractModel implements BrowsableInterface
{
    /**
     * @return \Openstore\Model\Browser\CategoryBrowser
     */
    public function getBrowser()
    {
        return new CategoryBrowser($this);
    }



    /**
     *
     * @param type $category
     * @param type $language
     * @return type
     */
    public function getAncestors($category, $language)
    {
        $adapter = $this->adapter;

        $sql = new Sql($adapter);

        $select = $sql->select();

        $select->from(array('pc1' => 'product_category'), array())
                ->join(
                    array('pc2' => 'product_category'),
                    new Expression("pc1.lft BETWEEN pc2.lft AND pc2.rgt"),
                    array(),
                    $select::JOIN_LEFT
                )
                ->join(
                    array('pc18' => 'product_category_translation'),
                    new Expression("pc18.category_id = pc2.category_id and pc18.lang = '$language'"),
                    array(),
                    $select::JOIN_LEFT
                );


        $select->columns(array(
            'category_id'    => new Expression('pc2.category_id'),
            'parent_id'        => new Expression('pc2.parent_id'),
            'reference'        => new Expression('pc2.reference'),
            'title'            => new Expression('if (pc18.title is null, pc2.title, pc18.title)'),
            'is_leaf'        => new Expression('if(pc2.rgt = (pc2.lft+1), 1, 0)'),
            'lft'            => new Expression('pc2.lft'),
            'rgt'            => new Expression('pc2.rgt'),
            'lvl'            => new Expression('pc2.lvl'),
        ));

        $select->where(array('pc1.reference' => $category));
        $select->where(array('pc2.lvl > 0'));
        $select->order(array('pc2.lvl' => $select::ORDER_ASCENDING));

        $sql_string = $sql->getSqlStringForSqlObject($select);

        //echo '<pre>';
        //var_dump($sql_string);die();

        $results = $adapter->query($sql_string, $adapter::QUERY_MODE_EXECUTE);

        //var_dump($results->toArray());
        //die();
        return $results;
    }

    public function getParent($category, $language)
    {
        $adapter = $this->adapter;

        $sql = new Sql($adapter);

        $select = $sql->select();

        $select->from(array('pc' => 'product_category'), array())
                ->join(
                    array('pc18' => 'product_category_translation'),
                    new Expression("pc18.category_id = pc.id and pc18.lang = '$language'"),
                    array(),
                    $select::JOIN_LEFT
                );


        $select->columns(array(
            'category_id'    => new Expression('pc.category_id'),
            'parent_id'        => new Expression('pc.parent_id'),
            'reference'        => new Expression('pc.reference'),
            'title'            => new Expression('if (pc18.title is null, pc.title, pc18.title)'),
            'is_leaf'        => new Expression('if(pc.rgt = (pc.lft+1), 1, 0)'),
            'lvl'            => new Expression('pc.lvl'),
            'lft'            => new Expression('pc.lft'),
            'rgt'            => new Expression('pc.rgt'),

        ));

        $select->where(array('pc.reference' => $category));



        $sql_string = $sql->getSqlStringForSqlObject($select);

        //echo '<pre>';
        //var_dump($sql_string);die();
        //die();
        $results = $adapter->query($sql_string, $adapter::QUERY_MODE_EXECUTE)->toArray();
        $parent = $results[0];
        //die();
        return $parent;
    }
}
