<?php
/**
 * Zend Framework (http://framework.zend.com/)
 *
 * @link      http://github.com/zendframework/ZendSkeletonApplication for the canonical source repository
 * @copyright Copyright (c) 2005-2013 Zend Technologies USA Inc. (http://www.zend.com)
 * @license   http://framework.zend.com/license/new-bsd New BSD License
 */

namespace Openstore\Controller;

use Openstore\Entity;

use Zend\Mvc\Controller\AbstractActionController;
use Zend\View\Model\ViewModel;
use Zend\View\Model\JsonModel;

use Zend\Db\Sql\Sql;
use Zend\Db\Sql\Expression;


class IndexController extends AbstractActionController
{
    public function indexAction()
    {
		
		$config = $this->getServiceLocator()->get('Openstore/Config');
		$view = new ViewModel();
		$options = array(
			'filter'		=> $this->params()->fromRoute('filter', 'all'),
			'query'			=> $this->params()->fromQuery('query'),
			'categories'	=> $this->params()->fromRoute('categories'),
			'brands'		=> $this->params()->fromRoute('brands'),
			'page'			=> (int) $this->params()->fromRoute('page'),
			'limit'			=> (int) $this->params()->fromRoute('perPage', 20),
			'sortDir'		=> $this->params()->fromRoute('sortDir', 'ASC'),
			'sortBy'		=> $this->params()->fromRoute('sortBy')
		);
		//var_dump($this->params()->fromQuery());
		//var_dump($this->params()->fromRoute());
		//var_dump($options);
		
		$brands		= $this->getBrands($options);
		$categories = $this->getCategories($options);
		
		
		$products	= $this->getProducts($options);
		
		$view->brands		= $brands;
		
		$view->categories	= $categories;
		$view->products		= $products;
		$view->searchOptions= $options;
		
		$adapter      = $this->getServiceLocator()->get('Zend\Db\Adapter\Adapter');		
		$catBrowser	  = new \Openstore\Catalog\Browser\Category($adapter, $this->getFilter());		
		$view->category_breadcrumb = $catBrowser->getAncestors($options['category']);
		// Test with doctrine
		//$em = $this->getServiceLocator()->get('Doctrine\ORM\EntityManager');
		//$this->printCategories();
		
		//$profiler = $this->getServiceLocator()->get('Zend\Db\Adapter\Adapter')->getProfiler();
		//$queryProfiles = $profiler->getQueryProfiles();		
		
        return $view;
    }

	public function productAction()
	{
		echo 'cool';
		die();
	}
	
	function getFilter()
	{
		$pricelist = $this->params()->fromRoute('pricelist');
		$language  = $this->params()->fromRoute('ui_language');
		//var_dump($language . '_' . $pricelist);
		return new \Openstore\Catalog\Filter($pricelist, $language);
	}
	
	function getBrands($search_options)
	{
        $adapter      = $this->getServiceLocator()->get('Zend\Db\Adapter\Adapter');		
		$brandBrowser	  = new \Openstore\Catalog\Browser\Brand($adapter, $this->getFilter());

		
		
		return $brandBrowser->getData($options);
		
	}
	
	function getCategories($search_options)
	{
        $adapter      = $this->getServiceLocator()->get('Zend\Db\Adapter\Adapter');		
		 //$di = new \Zend\Di\Di();
		 //$di->newInstance('Openstore\Catalog\Browser\Category', array('filter' => $this->getFilter())	);
		 //die();
		$catBrowser	  = new \Openstore\Catalog\Browser\Category($adapter, $this->getFilter());

		//$em = $this->getServiceLocator()->get('Doctrine\ORM\EntityManager');
		//$catBrowser->test($em);
		
		
		$options = new \Openstore\Catalog\Browser\Search\Options\Category();		
		$options->setIncludeEmptyNodes($include_empty_nodes=false);
		$options->setDepth($depth=1);
		$options->setExpandedCategory($search_options['category']);
		$options->setBrand($search_options['brand']);
		//$options = new \Openstore\Catalog\Browser\Search\Options();
		return $catBrowser->getData($options);
	}
	
	
	function getProducts($search_options)
	{
        $adapter		= $this->getServiceLocator()->get('Zend\Db\Adapter\Adapter');		
		$productBrowser	= new \Openstore\Catalog\Browser\Product($adapter, $this->getFilter());
		
		$options = new \Openstore\Catalog\Browser\Search\Options\Product();
		$options->setKeywords($search_options['query']);
		$options->setBrand($search_options['brand']);
		$options->setCategory($search_options['category']);
		
		$store = $productBrowser->getStore($options);

		var_dump($search_options);
		$store->getOptions()->setLimit($search_options['limit'])
							->setOffset(($search_options['page'] - 1) * $search_options['limit']);
		
		$results = $store->getData();
		//var_dump($results->getTotalRows());
		return $results;
		
		//return $productBrowser->getData($options);
		
	}
	
	function getCategories2()
	{
/*
SELECT SQL_NO_CACHE
    parent.id, 
	parent.reference, 
	if (pc18.title is null, parent.title, pc18.title), 
	if(parent.rgt = (parent.lft+1), 1, 0) as is_leaf, 
	parent.parent_id as parent_id,	
	parent.lvl as lvl,
	parent.lft as lft,
	parent.rgt as rgt,
	COUNT(p.id) as count_product
FROM
    product_category parent
        LEFT OUTER JOIN
    product_category node ON node.lft BETWEEN parent.lft AND parent.rgt
        LEFT OUTER JOIN
    product_category_translation pc18 ON pc18.category_id = parent.id
        and pc18.lang = 'fr'
        LEFT OUTER JOIN
    (SELECT 
        p.id, p.category_id
    FROM
        product p
    INNER JOIN product_pricelist ppl ON ppl.product_id = p.id
        and p.flag_active = 1
    INNER JOIN pricelist pl ON pl.id = ppl.pricelist_id
        and pl.reference = 'NL'
	INNER JOIN product_brand pb on pb.id = p.brand_id
	WHERE 1=1
    -- pb.reference in ('REMO')
	-- AND p.title like '%R%'
    GROUP BY p.id, p.category_id) as p ON node.id = p.category_id
WHERE 1 = 1
-- AND parent.rgt = (parent.lft+1)
-- AND IF(pc18.title is null, parent.title, pc18.title) like '%ROTO%'
GROUP BY parent.reference
-- HAVING count(p.id) > 0
ORDER by parent.lft
 * 
SELECT SQL_NO_CACHE parent.reference, parent.title, COUNT(distinct p.id)
  FROM product_category parent
  LEFT OUTER JOIN product_category node 
    ON node.lft BETWEEN parent.lft AND parent.rgt
  LEFT OUTER JOIN product p
    ON node.id = p.category_id and p.flag_active = 1
  -- left outer join product_pricelist ppl on ppl.product_id = p.id
  -- left outer join pricelist pl on pl.id = ppl.pricelist_id and pl.reference = 'FR'
 GROUP BY parent.reference
 ORDER by parent.lft
 * 
 * 
SELECT SQL_NO_CACHE parent.id, parent.reference, if(parent.rgt = (parent.lft+1), 1, 0) as is_leaf, if (pc18.title is null, parent.title, pc18.title) as title, parent.parent_id, parent.lvl, COUNT(p.id) as count_product
FROM product_category AS node
     inner join product p on node.id = p.category_id
	 inner join product_pricelist ppl on ppl.product_id = p.id
	 inner join pricelist pl on pl.id = ppl.pricelist_id
	 inner join product_brand pb on pb.id = p.brand_id
     inner join product_category AS parent on node.lft BETWEEN parent.lft AND parent.rgt
     left outer join product_category_translation pc18 on pc18.category_id = parent.id
WHERE 1=1
and p.flag_active = 1	
and pb.reference in ('REMO')
and pl.reference = 'NL'
and pc18.lang = 'en'
GROUP BY parent.id
ORDER BY parent.lft; 
 * 
select SQL_NO_CACHE parent.id, parent.reference, parent.title, parent.total_product, count(p.id) from
(
SELECT parent.id, parent.lft, parent.rgt, parent.reference, parent.title, COUNT(p.id) as total_product
  FROM product_category parent
  LEFT OUTER JOIN product_category node 
    ON node.lft BETWEEN parent.lft AND parent.rgt
  LEFT OUTER JOIN product p
    ON node.id = p.category_id
 GROUP BY parent.reference
 ORDER by parent.lft
) as parent

  LEFT OUTER JOIN product_category node 
    ON node.lft BETWEEN parent.lft AND parent.rgt
  LEFT OUTER JOIN product p
    ON node.id = p.category_id and p.flag_active = 1
  LEFT OUTER JOIN product_pricelist ppl on ppl.product_id = p.id
  RIGHT JOIN pricelist pl on pl.id = ppl.pricelist_id and pl.reference = 'BE'
 GROUP BY parent.id
 ORDER by parent.lft
 * 
 * 
SELECT SQL_NO_CACHE parent.id, parent.reference, parent.title, pc18.title, parent.lvl, COUNT(p.id) as count_product
FROM product_category AS node 
     inner join product p on node.id = p.category_id
	 inner join product_pricelist ppl on ppl.product_id = p.id
	 inner join pricelist pl on pl.id = ppl.pricelist_id and pl.reference = 'NL'
	 inner join product_brand pb on pb.id = p.brand_id
     inner join product_category AS parent on node.lft BETWEEN parent.lft AND parent.rgt
     left outer join product_category_translation pc18 on pc18.category_id = parent.id and pc18.lang = 'nl'
WHERE 1=1
AND p.flag_active = 1	
and pb.reference in ('REMO')
GROUP BY parent.id
ORDER BY parent.root, parent.lft;  
 *  
 * 
SELECT parent.reference, parent.title, parent.lvl, COUNT(p.id) as count_product
FROM product_category AS node 
     inner join product p on node.id = p.category_id

     inner join product_category AS parent on node.lft BETWEEN parent.lft AND parent.rgt
     left outer join product_category_translation pc18 on pc18.id = parent.id and pc18.lang = 'fr'
WHERE 1=1
AND p.flag_active = 1	
GROUP BY parent.reference
ORDER BY parent.root, parent.lft;  
 * 
 * 
 * 
 * 10 secondes
SELECT parent.reference, COUNT(product.id) as count_product
FROM product_category AS node ,
        product_category AS parent,
        product
WHERE node.lft BETWEEN parent.lft AND parent.rgt
        AND node.id = product.category_id
GROUP BY parent.reference
ORDER BY parent.root, parent.lft; 
 
 * 
 * 
 */
		
/*
 * 
 * 
 * avec product count (2 secondes)
SELECT parent.reference, COUNT(product.id)
FROM product_category AS node ,
        product_category AS parent,
        product
WHERE node.lft BETWEEN parent.lft AND parent.rgt
        AND node.id = product.category_id
and node.root = 2 and parent.root = 2
GROUP BY 1
ORDER BY parent.lft;
 * 
 */		
		$lang = 'fr';
		$pricelist_id = 1;
		$serviceLocator = $this->getServiceLocator();
        $adapter      = $serviceLocator->get('Zend\Db\Adapter\Adapter');
		$db = "nuvolia";
		$sql = new Sql($adapter);
		$select = $sql->select();
		$select->from(array('pc' => 'product_category'),  array())
				->join(array('pc18' => 'product_category_translation'), 
							new Expression("pc18.id = pc.id and pc18.lang = '$lang'"), 
							array(), $select::JOIN_LEFT)
				
				->join(array('p' => 'product'), "p.category_id = pc.id", array(), $select::JOIN_LEFT)
				->where('p.flag_active = 1 or p.flag_active is null');

		$columns = array(
			'id'				=> new Expression('pc.id'),
			'reference'			=> new Expression('pc.reference'),
			'title'				=> new Expression('pc.title'),
			'level'				=> new Expression('pc.lvl'),	
			'is_leaf'			=> new Expression('pc.rgt = pc.lft + 1')
		);

		$select->columns(
				array_merge($columns, array(
					'count_product' => new Expression('count(p.id)')
				)), true);
		
		$select->group($columns);
		$select->order(array('pc.root' => $select::ORDER_ASCENDING, 'pc.lft' => $select::ORDER_ASCENDING));
		
		
		
	
		$sql_string = $sql->getSqlStringForSqlObject($select);
		//$sql_string = $sql->getSqlStringForSqlObject($select);
		//echo '<pre>';
		//var_dump($sql_string);die();
		
		$results = $adapter->query($sql_string, $adapter::QUERY_MODE_EXECUTE);		
		return $results;
		
		
	}
	
	
	
	function printCategories()
	{
		$em = $this->getServiceLocator()->get('Doctrine\ORM\EntityManager');

		$query = $em
			->createQueryBuilder()
			->select('node')
			->from('Openstore\Entity\ProductCategory', 'node')
			->orderBy('node.root, node.lft', 'ASC')
			->getQuery()
		;
		// set hint to translate nodes
		$treeDecorationOptions = array(
			'decorate' => true,
			'rootOpen' => '<ol>',
			'rootClose' => '</ol>',
			'childOpen' => '<li>',
			'childClose' => '</li>',
			'nodeDecorator' => function($node) {
				return str_repeat('-', $node['level']).$node['title'].PHP_EOL;
			}
		);
		$repository = $em->getRepository('Openstore\Entity\ProductCategory');
		// build tree in english
		echo $repository->buildTree($query->getArrayResult(), $treeDecorationOptions).PHP_EOL.PHP_EOL;		
	}
	
	
	function generateEntities()
	{
		die('cool');
		$yaml_dirs = array(
			realpath(dirname(__FILE__) . '/../../../config/entities') => '\\Application\\Entity'); 
		
		$config = \Doctrine\ORM\Tools\Setup::createConfiguration();
		$driver = new \Doctrine\ORM\Mapping\Driver\SimplifiedYamlDriver($yaml_dirs);
		$driver->setGlobalBasename('schema');
		$config->setMetadataDriverImpl($driver);
	  
		$conn = $this->getServiceLocator()->get('Doctrine\ORM\EntityManager')->getConnection();
	//	$a = new \Doctrine\ORM\EntityManager($conn, $config, $eventManager);
	//	$a->getConnection();
		 $em = \Doctrine\ORM\EntityManager::create($conn, $config);

		 // Getting metadata
		$cmf = new \Doctrine\ORM\Tools\DisconnectedClassMetadataFactory();
		$cmf->setEntityManager($em);  // we must set the EntityManager

		$driver = $em->getConfiguration()->getMetadataDriverImpl();

		$classes = $driver->getAllClassNames();
		$metadata = array();
		foreach ($classes as $class) {
		  //any unsupported table/schema could be handled here to exclude some classes
		  if (true) {
			$metadata[] = $cmf->getMetadataFor($class);
		  }
		}
		var_dump($metadata);	
		
		// Generating
		
		$generator = new \Doctrine\ORM\Tools\EntityGenerator();
		$generator->setUpdateEntityIfExists(true);    // only update if class already exists
		$generator->setRegenerateEntityIfExists(true);  // this will overwrite the existing classes
		$generator->setGenerateStubMethods(true);
		$generator->setGenerateAnnotations(true);
		$generator->generate($metadata, '/tmp/test');		
		 
		 
		
	}
	
	public function searchAction()
	{
		$options = array(
			'query' => $this->params()->fromQuery('query')
		);
		$products = $this->getProducts($options);
		$json = new JsonModel(array(
					'products'	 => $products->toArray()
                ));	
        return $json;
		
	}
	
		
}
