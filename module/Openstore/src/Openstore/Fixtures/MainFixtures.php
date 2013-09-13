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
		$this->importRoles($manager);
		$this->importUser($manager);
		
		$this->importCountries($manager);
		$this->importStock($manager);
        $this->importLanguages($manager);
		$this->importProductUnit($manager);
		
		$this->importCurrencies($manager);
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
						)),
			2 => array('username' => 'testcustomer', 'email' => 'sebastien@nuvolia.com', 'password' => 'intelart',
						'roles' => array(
							'customer'
						)),
			
		);
		
		$bcrypt = new Bcrypt();
		$bcrypt->setCost(14); // Needs to match password cost in ZfcUser options
		
		foreach($users as $id => $infos) {
			$user = new Entity\User();
			$user->setUserId($id);
			$user->setUsername($infos['username']);
			$user->setEmail($infos['email']);
			$roles = $infos['roles'];
			
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
		}
		$manager->flush();
		
		
	}
	
	
	function importCountries(ObjectManager $manager) {
		$countries = array(
			1 => array('reference' => 'BE', 'name' => 'Belgium')
		);
		foreach($countries as $id => $infos) {
			$country = new Entity\Country();
			$country->setReference($infos['reference']);
			$country->setName($infos['name']);
			$manager->persist($country);
		}
		$manager->flush();
	}

	function importProductTypes(ObjectManager $manager) {
		$product_types = array(
			$this->default_product_type_id => array('PRODUCT' => 'Product', 'title' => 'Sellable product'),
			2 => array('reference' => 'VIRTUAL', 'title' => 'Virtual product'),
			3 => array('reference' => 'SERIE', 'title' => 'Virtual product serie'),
		);
		
		
		foreach($product_types as $id => $infos) {
			$type = new Entity\ProductType();
			$type->setTypeId($symbol);
			$type->setReference($infos['reference']);
			$type->setTitle($infos['title']);
			$manager->persist($type);
		}
		
		$manager->flush();
		
	}
	
	
	function importCurrencies(ObjectManager $manager) {
		$currencies = array(
			$this->default_currency_id => array('reference' => 'EUR', 'title' => 'Euro'),
			2 => array('reference' => 'USD', 'title' => 'US Dollar'),
			3 => array('reference' => 'GBP', 'title' => 'British pound'),
		);
		
		
		foreach($currencies as $id => $infos) {
			$currency = new Entity\Currency();
			$currency->setCurrencyId($id);
			$currency->setReference($infos['reference']);
			$currency->setTitle($infos['title']);
			$manager->persist($currency);
		}
		
		$manager->flush();
		
	}
	
	function importStock(ObjectManager $manager) {
		
		$stock = new Entity\Stock();
		$stock->setStockId($this->default_stock_id);
		$stock->setReference('DEFAULT');
		$stock->setTitle('Default warehouse');
		$manager->persist($stock);
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