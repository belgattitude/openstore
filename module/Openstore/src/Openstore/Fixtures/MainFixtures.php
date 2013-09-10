<?php
namespace Openstore;

use Openstore\Entity;

use Doctrine\Common\Persistence\ObjectManager;
use Doctrine\Common\DataFixtures\FixtureInterface;

class LoadUserData implements FixtureInterface
{
    public function load(ObjectManager $manager)
    {
		$this->importStock($manager);
        $this->importLanguages($manager);
		$this->importProductUnit($manager);
		$this->importUserRoles($manager);
    }
	
	function importStock(ObjectManager $manager) {
		
		$stock = new Entity\Stock();
		$stock->setId(1);
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
			'PC' => array('title' => 'Piece'),
			'm2' => array('title' => 'Square meter'),
			'Kg' => array('title' => 'Kilogram'),
		);
		
		foreach($units as $reference => $infos) {
			$unit = new Entity\ProductUnit();
			$unit->setReference($reference);
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