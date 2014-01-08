<?php
namespace Akilia\Controller;

use Zend\Mvc\Controller\AbstractActionController;

use Zend\Console\Request as ConsoleRequest;
use Zend\Console\Console;
use Zend\Console\ColorInterface;
use Zend\Db\Adapter\Adapter;

use Akilia\Utils\Akilia1Products;
use Akilia;

class ConsoleController extends AbstractActionController
{

    public function syncdbAction()
	{
		$configuration = $this->getAkiliaConfiguration();
		if (!is_array($configuration['synchronizer'])) {
			throw new \Exception("Cannot find akilia synchronize configuration, please see you global config files");
		}

		$em = $this->getServiceLocator()->get('Doctrine\ORM\EntityManager');
		$zendDb      = $this->getServiceLocator()->get('Zend\Db\Adapter\Adapter');
		$synchronizer = new Akilia\Synchronizer($em, $zendDb);
		$synchronizer->setServiceLocator($this->getServiceLocator());
		$synchronizer->setConfiguration($configuration['synchronizer']);
		$synchronizer->synchronizeAll();	
    }
	

    public function syncstockAction()
	{
		$configuration = $this->getAkiliaConfiguration();
		if (!is_array($configuration['synchronizer'])) {
			throw new \Exception("Cannot find akilia synchronize configuration, please see you global config files");
		}

		$em = $this->getServiceLocator()->get('Doctrine\ORM\EntityManager');
		$zendDb      = $this->getServiceLocator()->get('Zend\Db\Adapter\Adapter');
		$synchronizer = new Akilia\Synchronizer($em, $zendDb);
		$synchronizer->setServiceLocator($this->getServiceLocator());
		$synchronizer->setConfiguration($configuration['synchronizer']);
		$synchronizer->synchronizeProductStock();
    }
	
	
    public function syncapiAction()
	{
		$configuration = $this->getAkiliaConfiguration();
		if (!is_array($configuration['synchronizer'])) {
			throw new \Exception("Cannot find akilia synchronize configuration, please see you global config files");
		}

		$em = $this->getServiceLocator()->get('Doctrine\ORM\EntityManager');
		$zendDb      = $this->getServiceLocator()->get('Zend\Db\Adapter\Adapter');
		$synchronizer = new Akilia\Synchronizer($em, $zendDb);
		$synchronizer->setServiceLocator($this->getServiceLocator());
		$synchronizer->setConfiguration($configuration['synchronizer']);
		$synchronizer->synchronizeApi();	
    }	
	
    public function syncmediaAction()
	{
		$configuration = $this->getAkiliaConfiguration();
		if (!is_array($configuration['synchronizer'])) {
			throw new \Exception("Cannot find akilia synchronize configuration, please see you global config files");
		}

		$em = $this->getServiceLocator()->get('Doctrine\ORM\EntityManager');
		$zendDb      = $this->getServiceLocator()->get('Zend\Db\Adapter\Adapter');
		$synchronizer = new Akilia\Synchronizer($em, $zendDb);
		$synchronizer->setServiceLocator($this->getServiceLocator());
		$synchronizer->setConfiguration($configuration['synchronizer']);
		$synchronizer->synchronizeProductMedia();	
    }

	public function checkSynchroAction() 
	{
		
		$configuration = $this->getAkiliaConfiguration();
		if (!is_array($configuration['synchronizer'])) {
			throw new \Exception("Cannot find akilia synchronizer configuration, please see you global config files");
		}
		
		if (!is_array($configuration['checker'])) {
			throw new \Exception("Cannot find akilia checker configuration, please see you global config files");
		}
		
		$pricelists = $configuration['checker']['pricelists'];
		
		$db = $this->getServiceLocator()->get('Doctrine\ORM\EntityManager')->getConnection()->getDatabase();
		$adapter = $this->getServiceLocator()->get('Zend\Db\Adapter\Adapter');
		
		
		foreach ($pricelists as $pricelist => $options) {
			echo "[+] Checking pricelist '$pricelist'\n";
			
			$query = $this->getSQLPricelistChecker($pricelist, $db, $options['database']);
			$result = $adapter->query($query, Adapter::QUERY_MODE_EXECUTE)->toArray();
			$nb_errors = count($result);
			if ($nb_errors > 0) {
				echo " -> [Error] $nb_errors returned :\n";
				echo "\t" . join("\t", array_keys($result[0])) . "\n";
				foreach($result as $row) {
					echo "\t" . join("\t", $row) . "\n";
				}
				echo " Check with query : " . preg_replace("/(\n)(\t)/", ' ', $query) . "\n";
			} else {
				echo " -> Success !!!\n";
			}
			
			
		}
		
		
		
		
		
	}
	
	public function listproductpicturesAction() {
		$sl = $this->getServiceLocator();
		$configuration = $this->getAkiliaConfiguration();
		
		$products = new Akilia1Products($configuration);
		$products->setServiceLocator($this->getServiceLocator());
		$products->setDbAdapter($this->getServiceLocator()->get('Zend\Db\Adapter\Adapter'));
		
		$list = $products->getProductPictures();
		
		foreach($list as $infos) {
			echo str_pad($infos['product_id'], 10) . "\t" .  
				($infos['alternate_index'] === null ? "0" : $infos['alternate_index']) . "\t" . 
				($infos['product_active'] ? "        " : "ARCHIVED") . "\t" . 
				$infos['filename'] . "\n"; 
		}
	}
	
	
	public function archiveproductpicturesAction() {
		
		
		$configuration = $this->getAkiliaConfiguration();
		$products = new Akilia1Products($configuration);
		$products->setServiceLocator($this->getServiceLocator());
		$products->setDbAdapter($this->getServiceLocator()->get('Zend\Db\Adapter\Adapter'));
		
		$list = $products->getProductPictures();

		$archive_picture_path = $configuration['archive_product_picture_path'];
		if (!is_dir($archive_picture_path)) {
			throw new \Exception("archive_product_picture_path '$archive_picture_path' is not a valid directory, correct your config");
		} elseif (!is_writable($archive_picture_path)) {
			throw new \Exception("archive_product_picture_path '$archive_picture_path' is not writable");
		}

		
		$archivable = array();
		foreach($list as $basename => $infos) {
			if (!$infos['product_active']) {
				$archivable[] = $infos['filename'];
			}
		}
		
		$console = Console::getInstance();
		
		if (count($archivable) == 0) {
			$console->writeLine("Nothing to do (no archivable product picture found)", ColorInterface::GREEN);
			exit(0);
		} else {
			foreach($archivable as $file) {
				echo "$file\n";
			}
			$count = count($archivable);
			$console->writeLine('---------------------------------------------------');
			$console->writeLine("Will move $count file(s) into new directory :");
			$console->writeLine(" - $archive_picture_path");
			$console->writeLine('---------------------------------------------------');
			$console->writeLine("Are you okay ? (CTRL-C to abort)");
			$console->readLine();
			try {
				foreach($archivable as $file) {
					$dest_file = $archive_picture_path . "/" . basename($file);
					$ret = copy($file, $dest_file);
					if (!$ret) {
						throw new \Exception("Cannot copy '$file' to '$dest_file'");
					}
					$ret = unlink($file);
					if (!$ret) {
						throw new \Exception("Cannot remove '$file'");
					}
					
				}
			} catch (\Exception $e) {
				$console->writeLine("Error: {$e->getMessage()}", ColorInterface::RED);
			}
			$console->writeLine("Success, $count files moved to $archive_picture_path", ColorInterface::GREEN);
			
		}

		
		
	}
	

	/**
	 * 
	 * @return array
	 * @throws Exception
	 */
	protected function getAkiliaConfiguration() {
		$sl = $this->getServiceLocator();
		$configuration = $sl->get('Configuration');
		if (!is_array($configuration['akilia'])) {
			throw new \Exception("Cannot find akilia configuration, please see you global config files");
		}
		return $configuration['akilia'];
		
	}
	
	
	/**
	 * 
	 * @param string $pricelist
	 * @param string $db openstore db name
	 * @param string $akilia1_db
	 * @return string
	 */
	protected function getSQLPricelistChecker($pricelist, $db, $akilia1_db, $limit=10)
	{
		
		$sql = "select 
			a.id_article,
			a.reference,
			m.id_marque, pb.reference as brand_ref,
			c.id_categorie, pc.reference as categ_ref,
			f.id_famille, pg.reference as group_ref,
			t.prix_unit_ht, ppl.list_price,
			(t.prix_unit_ht * (1-(t.remise1/100)) * (1-(t.remise2/100)) * (1-(t.remise3/100)) * (1-(t.remise4/100))) as discounted_price, 
			ppl.price,
			t.prix_unit_public, ppl.public_price,
			t.stock, ps.available_stock,
			t.flag_availability, ppl.flag_active

		from
			$akilia1_db.article a
				inner join
			$akilia1_db.art_tarif t ON t.id_pays = '$pricelist' and a.id_article = t.id_article
				left outer join 
			$akilia1_db.famille f on f.id_famille = a.id_famille
				left outer join 
			$akilia1_db.categories c on c.id_categorie = a.id_categorie
				left outer join 
			$akilia1_db.marque m on m.id_marque = a.id_marque
				left outer join
			$db.product p ON p.legacy_mapping = a.id_article
				left outer join
			$db.product_group pg on pg.legacy_mapping = f.id_famille
				left outer join
			$db.product_category pc on pc.legacy_mapping = c.id_categorie
				left outer join
			$db.product_brand pb on pb.legacy_mapping = m.id_marque
				left outer join 
			$db.pricelist pl on pl.legacy_mapping = '$pricelist'
				left outer join
			$db.product_pricelist ppl on ppl.pricelist_id = pl.pricelist_id and p.product_id = ppl.product_id
				left outer join
			$db.stock s on s.stock_id = pl.stock_id
				left outer join
			$db.product_stock ps on ps.stock_id = s.stock_id and p.product_id = ps.product_id 
		where 1 = 1
		and ( m.id_marque <> pb.reference
		or a.reference <> p.reference
		or c.id_categorie <> pc.reference
		or f.id_famille <> pg.reference
		or t.prix_unit_ht <> ppl.list_price 
		or (t.prix_unit_ht * (1-(t.remise1/100)) * (1-(t.remise2/100)) * (1-(t.remise3/100)) * (1-(t.remise4/100))) <> ppl.price
		or t.remise1 <> ppl.discount_1
		or t.remise2 <> ppl.discount_2
		or t.remise3 <> ppl.discount_3
		or t.remise4 <> ppl.discount_4
		or t.stock <> ps.available_stock
		or t.flag_availability <> ppl.flag_active
		or t.flag_new <> ppl.is_new
		or t.flag_liquidation <> ppl.is_liquidation
		or t.flag_promo <> ppl.is_promotional
		)
		LIMIT $limit";
		return $sql;
	}
}
