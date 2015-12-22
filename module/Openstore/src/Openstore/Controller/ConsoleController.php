<?php

namespace Openstore\Controller;

use OpenstoreSchema\Core\Entity;
use Zend\Mvc\Controller\AbstractActionController;
use Zend\View\Model\ViewModel;
use Zend\View\Model\JsonModel;
use Zend\Db\Sql\Sql;
use Zend\Db\Sql\Expression;
use Zend\Db\Adapter\Adapter;
use Zend\Console\Request as ConsoleRequest;
use Nv\Akilia;
use Zend\Console\Console;
use Zend\Console\ColorInterface;

class ConsoleController extends AbstractActionController
{
    /**
     *
     * @var Adapter
     */
    protected $adapter;

    public function onDispatch(\Zend\Mvc\MvcEvent $e)
    {
        //$this->config	= $this->getServiceLocator()->get('Openstore\Config');
        $this->adapter = $this->getServiceLocator()->get('Zend\Db\Adapter\Adapter');
        parent::onDispatch($e);
    }

    public function setupAction()
    {
        echo 'setup done';
    }

    public function clearcacheAction()
    {
        $serviceLocator = $this->getServiceLocator();
        if ($serviceLocator->has('Cache\SolubleDbMetadata')) {
            /** @var \Zend\Cache\Storage\StorageInterface */
            $cache = $serviceLocator->get('Cache\SolubleDbMetadata');
            $cache->flush();
        }
    }

    public function clearmediacacheAction()
    {
        $serviceLocator = $this->getServiceLocator();
        if ($serviceLocator->has('Cache\SolubleMediaConverter')) {
            /** @var \Zend\Cache\Storage\StorageInterface */
            $cache = $serviceLocator->get('Cache\SolubleMediaConverter');
            $cache->flush();
            //$cache->clearByNamespace('Cache\SolubleMediaConverter');
        }
    }

    /**
     * recreate db and load data fixtures
     *
     */
    public function updatedbAction()
    {
        $dir = realpath(__DIR__ . '/../../../../../');
        $php = "/usr/local/bin/php";


        $commands = array(
            "$php $dir/public/index.php orm:schema-tool:update --force",
            "$php $dir/public/index.php data-fixture:import",
        );

        foreach ($commands as $command) {
            echo "Executing $command\n";
            passthru($command);
        }
    }

    /**
     * recreate db and load data fixtures
     *
     */
    public function recreatedbAction()
    {
        $dir = realpath(__DIR__ . '/../../../../../');
        $php = "/usr/local/bin/php";

        $commands = array(
            "$php $dir/public/index.php orm:schema-tool:drop --force",
            "$php $dir/public/index.php orm:schema-tool:create",
            "$php $dir/public/index.php openstore recreatedbextra",
        );

        foreach ($commands as $command) {
            echo "Executing $command\n";
            passthru($command);
        }
    }

    public function updateproductslugAction()
    {
        $console = Console::getInstance();
        $queries = array();
        $queries[] = "
						update product_translation p18
						inner join product p on p18.product_id = p.product_id
						left outer join product_brand pb on pb.brand_id = p.brand_id 
						set p18.slug = slugify(CONCAT_WS(' ', COALESCE(pb.title, pb.reference, ''), ' ', p.reference, ' ', ' ', p.product_id, COALESCE(p18.title, p.title, '')))
						where p.flag_active = 1
					";
        $queries[] = "
						update product p
						left outer join product_brand pb on pb.brand_id = p.brand_id 
						set p.slug = slugify(CONCAT_WS(' ', COALESCE(pb.title, pb.reference, ''), ' ', p.reference, ' ', ' ', p.product_id, COALESCE(p.title, '')))
						where p.flag_active = 1;		
					";

        $console->writeLine(str_repeat('-', 80), ColorInterface::NORMAL);
        $console->writeLine('Recreate product slugs', ColorInterface::RED);
        $console->writeLine(str_repeat('-', 80), ColorInterface::NORMAL);

        foreach ($queries as $query) {
            $console->writeLine("[*] Executing query...");
            try {
                $result = $this->adapter->query($query, Adapter::QUERY_MODE_EXECUTE);
            } catch (\Exception $e) {
                $console->writeLine(str_repeat('-', 80), ColorInterface::RED);
                $console->writeLine("Error running query", ColorInterface::RED);
                $console->writeLine($query);
                $console->writeLine(str_repeat('-', 80), ColorInterface::RED);
                $console->writeLine($e->getMessage(), ColorInterface::NORMAL);
                $console->writeLine(str_repeat('-', 80), ColorInterface::RED);
                die();
            }
            $console->writeLine(" -> Success", ColorInterface::GREEN);
        }
    }

    public function recreatedbextraAction()
    {
        $console = Console::getInstance();

        $console->writeLine(str_repeat('-', 80), ColorInterface::NORMAL);
        $console->writeLine('Recreate database extra triggers, event, procedures and functions.', ColorInterface::RED);
        $console->writeLine(str_repeat('-', 80), ColorInterface::NORMAL);
        $config = include(__DIR__ . '/../../../config/dbextra.config.php');
        $stmts = $config['dbextra']['statements'];
        foreach ($stmts as $key => $stmt) {
            $console->writeLine("[*] $key...");
            try {
                $result = $this->adapter->query($stmt, Adapter::QUERY_MODE_EXECUTE);
            } catch (\Exception $e) {
                $console->writeLine(str_repeat('-', 80), ColorInterface::RED);
                $console->writeLine("Error running : $key", ColorInterface::RED);
                $console->writeLine($stmt);
                $console->writeLine(str_repeat('-', 80), ColorInterface::RED);
                $console->writeLine($e->getMessage(), ColorInterface::NORMAL);
                $console->writeLine(str_repeat('-', 80), ColorInterface::RED);
                die();
            }
            $console->writeLine(" -> Success", ColorInterface::GREEN);
        }
    }

    /**
     * recreate db and load data fixtures
     *
     */
    public function buildallreloadAction()
    {
        $dir = realpath(__DIR__ . '/../../../../../');

        $php = "/usr/local/bin/php";


        $commands = array(
            "$php $dir/public/index.php orm:schema-tool:drop --force",
            "$php $dir/public/index.php orm:schema-tool:create",
            "$php $dir/public/index.php data-fixture:import",
            "$php $dir/public/index.php openstore recreatedbextra",
        );

        foreach ($commands as $command) {
            echo "Executing $command\n";
            passthru($command);
        }

        $this->adapter->query("ALTER TABLE `product_search` ADD FULLTEXT(`keywords`)")->execute();
    }

    public function relocategroupcategAction()
    {
        $em = $this->getServiceLocator()->get('Doctrine\ORM\EntityManager');

        // Step 1: Adding categories

        $root_reference = 'ROOT';

        // If group categ does not exists
        $rootCategory = $em->getRepository('OpenstoreSchema\Core\Entity\ProductCategory')->findOneBy(array('reference' => $root_reference));
        if ($rootCategory === null) {
            $rootCategory = new \OpenstoreSchema\Core\Entity\ProductCategory();
            $rootCategory->setReference($root_reference);
            $rootCategory->setTitle('ROOT');
            $em->persist($rootCategory);
            $em->flush();
        }

        // Select all product groups
        $select = "
			select 
				pg.group_id,
				pg.reference,
				pg.title,
				pg.legacy_mapping,
				pc.category_id
			from product_group pg
			left outer join product_category pc on pc.legacy_mapping = pg.legacy_mapping			
		";

        $rows = $em->getConnection()->query($select)->fetchAll();

        foreach ($rows as $row) {
            if ($row['category_id'] === null) {
                $pc = new \OpenstoreSchema\Core\Entity\ProductCategory;
            } else {
                $pc = $em->find('OpenstoreSchema\Core\Entity\ProductCategory', $row['category_id']);
            }
            $pc->setParent($rootCategory);
            $pc->setTitle($row['title']);
            $pc->setReference($row['reference']);
            $pc->setLegacyMapping($row['legacy_mapping']);
            $em->persist($pc);
        }
        $em->flush();

        // Step 2, putting products in group

        $update = "
			update product p
			inner join product_group pg on pg.group_id = p.group_id
			inner join product_category pc on pc.legacy_mapping = pg.legacy_mapping
			set p.category_id = pc.category_id
		";

        $result = $em->getConnection()->query($update);
    }
}
