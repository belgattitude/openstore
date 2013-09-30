<?php
namespace Openstore;

use Openstore\Entity;

use Doctrine\Common\Persistence\ObjectManager;
use Doctrine\Common\DataFixtures\FixtureInterface;
use Zend\Crypt\Password\Bcrypt;

class LoadUserData implements FixtureInterface
{
	protected $default_currency_id = 1;
	protected $default_stock_id = 1;
	protected $default_unit_id = 1;
	protected $default_product_type_id = 1;
	
    public function load(ObjectManager $manager)
    {
		
		$this->importMediaContainers($manager);
		$this->importProductMediaTypes($manager);
		
		$this->importLanguages($manager);
		
		$this->importCountries($manager);
		$this->importStock($manager);

		$this->importCurrencies($manager);
		
		$this->importPricelists($manager);
		
		
		$this->importRoles($manager);
		
		$this->importUser($manager);
		
        
		$this->importProductUnit($manager);
		
    }
	
	
	function importMediaContainers(ObjectManager $manager) {
		
		$containers = array(
			1 => array('reference' => 'PRODUCT_MEDIAS', 'folder' => '/product_medias', 'title' => 'Catalog product medias container'),
			2 => array('reference' => 'PRIVATE', 'folder' => '/private', 'title' => 'Private media container'),
		);
		
		
		
		foreach($containers as $id => $infos) {
			$container = new Entity\MediaContainer();
			$container->setContainerId($id);
			$container->setReference($infos['reference']);
			$container->setFolder($infos['folder']);
			$container->setTitle($infos['title']);
			$manager->persist($container);
		}
		
		
		$metadata = $manager->getClassMetaData(get_class($container));
		$metadata->setIdGeneratorType(\Doctrine\ORM\Mapping\ClassMetadata::GENERATOR_TYPE_NONE);
		
		$manager->flush();
	}
	
	
	function importProductMediaTypes(ObjectManager $manager) {
		
		$product_media_types = array(
			1 => array('reference' => 'PICTURE', 'title' => 'Official picture'),
			2 => array('reference' => 'ALTERNATE_PICTURE', 'title' => 'Alternate pictures'),
			3 => array('reference' => 'VIDEO', 'title' => 'Video'),
			4 => array('reference' => 'SOUND', 'title' => 'Sounds and recordings'),
			5 => array('reference' => 'DOCUMENT', 'title' => 'Documents'),
		);
		
		
		
		foreach($product_media_types as $id => $infos) {
			$type = new Entity\ProductMediaType();
			$type->setTypeId($id);
			$type->setReference($infos['reference']);
			$type->setTitle($infos['title']);
			$manager->persist($type);
		}
		
		
		$metadata = $manager->getClassMetaData(get_class($type));
		$metadata->setIdGeneratorType(\Doctrine\ORM\Mapping\ClassMetadata::GENERATOR_TYPE_NONE);
		
		$manager->flush();
	}

	function importStock(ObjectManager $manager) {
		
		$stock = new Entity\Stock();

		$stock->setStockId($this->default_stock_id);
		$stock->setReference('DEFAULT');
		$stock->setTitle('Default warehouse');
		$manager->persist($stock);
		
		$metadata = $manager->getClassMetaData(get_class($stock));
		$metadata->setIdGeneratorType(\Doctrine\ORM\Mapping\ClassMetadata::GENERATOR_TYPE_NONE);
		
		$manager->flush();		
	}
	
	
	function importPricelists(ObjectManager $manager) {
		$stock_id = $this->default_stock_id;
		$currency_id = $this->default_currency_id;
		$pricelists = array(
			1 => array('reference' => 'BE', 'title' => 'Belgium Pricelist', 'stock_id' => $stock_id, 'currency_id' => $currency_id),
			2 => array('reference' => 'FR', 'title' => 'French Pricelist', 'stock_id' => $stock_id, 'currency_id' => $currency_id),
			3 => array('reference' => 'NL', 'title' => 'NL Pricelist', 'stock_id' => $stock_id, 'currency_id' => $currency_id),
			
		);
		
		$manager->getConnection()->executeUpdate("DELETE FROM pricelist where pricelist_id in (" . join(',', array_keys($pricelists)) . ')');
		foreach($pricelists as $id => $infos) {
			$stock = $manager->getRepository('Openstore\Entity\Stock')->find($infos['stock_id']);
			
			$currency = $manager->getRepository('Openstore\Entity\Currency')->find($infos['currency_id']);
			
			$pricelist = new Entity\Pricelist();
			$pricelist->setPricelistId($id);
			$pricelist->setStock($stock);
			$pricelist->setCurrency($currency);
			
			//$pricelist->stock_id = $infos['stock_id'];
			//$pricelist->currency_id = $infos['currency_id'];
			
			$pricelist->setReference($infos['reference']);
			$pricelist->setLegacyMapping($infos['reference']);
			$pricelist->setTitle($infos['title']);
			$manager->persist($pricelist);
		}

		$metadata = $manager->getClassMetaData(get_class($pricelist));
		$metadata->setIdGeneratorType(\Doctrine\ORM\Mapping\ClassMetadata::GENERATOR_TYPE_NONE);
		
		$manager->flush();
		
		
	}
	
	function importRoles(ObjectManager $manager)
	{
		$roles = array(
			'guest' => array('parent_id' => null),
			'user' => array('parent_id' => 'guest'),
			'customer' => array('parent_id' => 'user'),
			'moderator' => array('parent_id' => 'user'),
			'admin' => array('parent_id' => 'moderator')
		);
		foreach($roles as $reference => $infos) {
			$role = new Entity\Role();
			$role->setReference($reference);
			
			if ($infos['parent_id'] !== null) {
				$role->setParent($roles[$infos['parent_id']]['roleobject']);
			}
			$manager->persist($role);
			$roles[$reference]['roleobject'] = $role;
		}
		$manager->flush();
	}
	
	
	function importUser(ObjectManager $manager) {
		$users = array(
			1 => array('username' => 'admin', 'email' => 's.vanvelthem@gmail.com', 'password' => 'intelart',
						'roles' => array(
							'admin'
						),
						'pricelists' => array(
							'BE', 'FR', 'NL'
						)),
			2 => array('username' => 'testcustomer', 'email' => 'sebastien@nuvolia.com', 'password' => 'intelart',
						'roles' => array(
							'customer'
						),
						'pricelists' => array(
							'BE'
						)),
			
		);
		
		$manager->getConnection()->executeUpdate("DELETE FROM user_role where user_id in (" . join(',', array_keys($users)) . ')');
		$manager->getConnection()->executeUpdate("DELETE FROM user where user_id in (" . join(',', array_keys($users)) . ')');
		
		$bcrypt = new Bcrypt();
		$bcrypt->setCost(14); // Needs to match password cost in ZfcUser options
		
		
		
		foreach($users as $id => $infos) {
			$user = new Entity\User();
			$user->setUserId($id);
			$user->setUsername($infos['username']);
			$user->setEmail($infos['email']);
			$roles = $infos['roles'];
			$pricelists = $infos['pricelists'];
			
			if (count($roles) > 0) {
				foreach($roles as $role_ref) {
					$role = $manager->getRepository('Openstore\Entity\Role')->findOneBy(array('reference' => $role_ref));
					if ($role) {
						$user->addRole($role);
					}
				}
			}

			$password = $infos['password'];
			
			$newPassword = $bcrypt->create($password);			
			
			$user->setPassword($newPassword);
			
			$manager->persist($user);
			
			if (count($pricelists) > 0) {
				foreach($pricelists as $pricelist_ref) {
					
					$pricelist = $manager->getRepository('Openstore\Entity\Pricelist')->findOneBy(array('reference' => $pricelist_ref));
					if ($pricelist) {
						//$user->addPricelist($pricelist);
						
						$user_pricelist = new Entity\UserPricelist();
						$user_pricelist->setUserId($user);
						$user_pricelist->setPricelistId($pricelist);
						$user_pricelist->setFlagActive(1);
						$manager->persist($user_pricelist);
						
					}
				}
			}
			
			
		}
		
		$metadata = $manager->getClassMetaData(get_class($user));
		$metadata->setIdGeneratorType(\Doctrine\ORM\Mapping\ClassMetadata::GENERATOR_TYPE_NONE);
		
		$manager->flush();
		
		
	}
	
	
	function importCountries(ObjectManager $manager) {
		$countries = array(
			1 => array('reference' => 'BE', 'name' => 'Belgium')
		);
		$manager->getConnection()->executeUpdate("DELETE FROM country where country_id in (" . join(',', array_keys($countries)) . ')');
		
		foreach($countries as $id => $infos) {
			$country = new Entity\Country();
			$country->setCountryId($id);
			$country->setReference($infos['reference']);
			$country->setName($infos['name']);
			$manager->persist($country);
		}
		
		$metadata = $manager->getClassMetaData(get_class($country));
		$metadata->setIdGeneratorType(\Doctrine\ORM\Mapping\ClassMetadata::GENERATOR_TYPE_NONE);
		
		$manager->flush();
	}

	function importProductTypes(ObjectManager $manager) {
		$product_types = array(
			$this->default_product_type_id => array('reference' => 'Product', 'title' => 'Sellable product'),
			2 => array('reference' => 'VIRTUAL', 'title' => 'Virtual product'),
			3 => array('reference' => 'SERIE', 'title' => 'Virtual product serie'),
		);
		
		
		foreach($product_types as $id => $infos) {
			$type = new Entity\ProductType();
			$type->setTypeId($id);
			$type->setReference($infos['reference']);
			$type->setTitle($infos['title']);
			$manager->persist($type);
		}
		
		$metadata = $manager->getClassMetaData(get_class($type));
		$metadata->setIdGeneratorType(\Doctrine\ORM\Mapping\ClassMetadata::GENERATOR_TYPE_NONE);
		
		$manager->flush();
		
	}
	
	
	function importCurrencies(ObjectManager $manager) {
		$currencies = array(
			$this->default_currency_id => array('reference' => 'EUR', 'title' => 'Euro'),
			2 => array('reference' => 'USD', 'title' => 'US Dollar'),
			3 => array('reference' => 'GBP', 'title' => 'British pound'),
		);
		
		$manager->getConnection()->executeUpdate("DELETE FROM currency where currency_id in (" . join(',', array_keys($currencies)) . ')');
		foreach($currencies as $id => $infos) {
			$currency = new Entity\Currency();
			$currency->setCurrencyId($id);
			$currency->setReference($infos['reference']);
			$currency->setTitle($infos['title']);
			$manager->persist($currency);
		}
		
		$metadata = $manager->getClassMetaData(get_class($currency));
		$metadata->setIdGeneratorType(\Doctrine\ORM\Mapping\ClassMetadata::GENERATOR_TYPE_NONE);
		
		$manager->flush();
		
	}
	
	
	
	function importProductUnit(ObjectManager $manager)
	{
		$units = array(
			$this->default_unit_id => array('reference' => 'PC', 'title' => 'Piece'),
			2 => array('reference' => 'm2', 'title' => 'Square meter'),
			3 => array('reference' => 'Kg', 'title' => 'Kilogram'),
		);
		
		foreach($units as $unit_id => $infos) {
			$unit = new Entity\ProductUnit();
			$unit->setUnitId($unit_id);
			$unit->setReference($infos['reference']);
			$unit->setTitle($infos['title']);
			$manager->persist($unit);
		}
		
		$metadata = $manager->getClassMetaData(get_class($unit));
		$metadata->setIdGeneratorType(\Doctrine\ORM\Mapping\ClassMetadata::GENERATOR_TYPE_NONE);
		
		$manager->flush();
	}
	
	function importLanguages(ObjectManager $manager)
	{
		// Languages
		$langs = array(
			'en' => array('title' => 'English'),
			'fr' => array('title' => 'Français'),
			'nl' => array('title' => 'Nederlands'),
			'de' => array('title' => 'German'),
			'it' => array('title' => 'Italiano'),
			'es' => array('title' => 'Spanish'),
		);
		
		foreach($langs as $code => $infos) {
			$lang = new Entity\Language();
			$lang->setTitle($infos['title']);
			$lang->setLang($code);
			$manager->persist($lang);
		}
		$manager->flush();
	}
	
}