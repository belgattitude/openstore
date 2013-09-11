<?php
namespace Openstore;

use Openstore\Entity;

use Doctrine\Common\Persistence\ObjectManager;
use Doctrine\Common\DataFixtures\FixtureInterface;

class LoadUserData implements FixtureInterface
{
	protected $default_currency_id = 1;
	protected $default_stock_id = 1;
	protected $default_unit_id = 1;
	protected $default_product_type_id = 1;
	
    public function load(ObjectManager $manager)
    {
		$this->importStock($manager);
        $this->importLanguages($manager);
		$this->importProductUnit($manager);
		$this->importUserRoles($manager);
		$this->importCurrencies($manager);
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
	
	function importUserRoles(ObjectManager $manager)
	{
		$roles = array(
			'guest' => array('parent_id' => null),
			'user' => array('parent_id' => 'guest'),
			'moderator' => array('parent_id' => 'user'),
			'administrator' => array('parent_id' => 'moderator')
		);
		
		foreach($roles as $reference => $infos) {
			$role = new Entity\Role();
			$role->setRoleId($reference);
			
			if ($infos['parent_id'] !== null) {
				$role->setParent($roles[$infos['parent_id']]['roleobject']);
			}
			$manager->persist($role);
			$roles[$reference]['roleobject'] = $role;
			
			
		}
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