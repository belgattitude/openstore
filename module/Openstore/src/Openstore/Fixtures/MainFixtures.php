<?php

namespace Openstore;

use Openstore\Entity;
use Doctrine\Common\Persistence\ObjectManager;
use Doctrine\Common\DataFixtures\FixtureInterface;
use Zend\Crypt\Password\Bcrypt;

class LoadUserData implements FixtureInterface {

    protected $default_currency_id = 1;
    protected $default_stock_id = 1;
    protected $default_unit_id = 1;
    protected $default_product_type_id = 1;

    public function load(ObjectManager $manager) {

        $this->importMediaContainers($manager);
        $this->importProductMediaTypes($manager);

        $this->importLanguages($manager);

        $this->importCountries($manager);
        $this->importStock($manager);

        $this->importCurrencies($manager);

        $this->importPricelists($manager);


        $this->importRoles($manager);

        $this->importUser($manager);

        $this->importProductType($manager);
        $this->importProductStatus($manager);
        $this->importPackagingType($manager);

        $this->importProductUnit($manager);


        $this->importOrderInfos($manager);
    }

    function importOrderInfos($manager) {

        // step 1 adding order statuses
        $statuses = array(
            100 => array('reference' => 'CREATED', 'title' => 'Initial status / order created', 'flag_default' => true, 'flag_readonly' => false),
            200 => array('reference' => 'CONFIRMED', 'title' => 'Confirmed order', 'flag_default' => null, 'flag_readonly' => false),
            400 => array('reference' => 'IMPORTED', 'title' => 'Imported in legacy system', 'flag_default' => null, 'flag_readonly' => false),
            500 => array('reference' => 'WAITING_APPROVAL', 'title' => 'Waiting approval', 'flag_default' => null, 'flag_readonly' => false),
            600 => array('reference' => 'APPROVED', 'title' => 'Approved', 'flag_default' => null, 'flag_readonly' => false),
            1000 => array('reference' => 'FULLY_DELIVERED', 'title' => 'Fully delivered', 'flag_default' => null, 'flag_readonly' => true),
            2000 => array('reference' => 'FULLY_INVOICED', 'title' => 'Fully invoiced', 'flag_default' => null, 'flag_readonly' => true),
            5000 => array('reference' => 'COMPLETE', 'title' => 'Complete', 'flag_default' => null, 'flag_readonly' => true),
            9000 => array('reference' => 'CANCELLED', 'title' => 'Cancelled', 'flag_default' => null, 'flag_readonly' => true),
        );

        foreach ($statuses as $id => $infos) {
            $orderstatus = new Entity\SaleOrderStatus();
            $orderstatus->setStatusId($id);
            $orderstatus->setFlagDefault($infos['flag_default']);
            $orderstatus->setFlagReadOnly($infos['flag_readonly']);
            $orderstatus->setReference($infos['reference']);
            $orderstatus->setTitle($infos['title']);
            $manager->persist($orderstatus);
        }
        $metadata = $manager->getClassMetaData(get_class($orderstatus));
        $metadata->setIdGeneratorType(\Doctrine\ORM\Mapping\ClassMetadata::GENERATOR_TYPE_NONE);
        $manager->flush();


        // step 2 adding line statuses
        $line_statuses = array(
            100 => array('reference' => 'CREATED', 'title' => 'Created', 'flag_default' => true, 'flag_readonly' => false),
            120 => array('reference' => 'PICKED', 'title' => 'Picked, ready for delivery', 'flag_default' => null, 'flag_readonly' => false),
            200 => array('reference' => 'DELIVERED', 'title' => 'Delivered', 'flag_default' => null, 'flag_readonly' => true),
            300 => array('reference' => 'INVOICED', 'title' => 'Invoiced', 'flag_default' => null, 'flag_readonly' => true),
            900 => array('reference' => 'Cancelled', 'title' => 'Cancelled', 'flag_default' => null, 'flag_readonly' => true)
        );

        foreach ($line_statuses as $id => $infos) {
            $orderline = new Entity\SaleOrderLineStatus();
            $orderline->setStatusId($id);
            $orderline->setFlagDefault($infos['flag_default']);
            $orderline->setFlagReadOnly($infos['flag_readonly']);
            $orderline->setReference($infos['reference']);
            $orderline->setTitle($infos['title']);
            $manager->persist($orderline);
        }
        $metadata = $manager->getClassMetaData(get_class($orderline));
        $metadata->setIdGeneratorType(\Doctrine\ORM\Mapping\ClassMetadata::GENERATOR_TYPE_NONE);
        $manager->flush();



        // step 3 adding order types
        $types = array(
            1000 => array('reference' => 'QUOTE', 'title' => 'Quote'),
            2000 => array('reference' => 'REGULAR', 'title' => 'Regular order'),
            5000 => array('reference' => 'WEB', 'title' => 'Web order'),
            9000 => array('reference' => 'SHOPCART', 'title' => 'Shopcart in progress'),
            20000 => array('reference' => 'DEPOSIT', 'title' => 'Deposit')
        );

        foreach ($types as $id => $infos) {
            $ordertype = new Entity\SaleOrderType();
            $ordertype->setTypeId($id);
            $ordertype->setReference($infos['reference']);
            $ordertype->setTitle($infos['title']);
            $manager->persist($ordertype);
        }
        $metadata = $manager->getClassMetaData(get_class($ordertype));
        $metadata->setIdGeneratorType(\Doctrine\ORM\Mapping\ClassMetadata::GENERATOR_TYPE_NONE);
        $manager->flush();
    }

    function importMediaContainers(ObjectManager $manager) {

        $containers = array(
            1 => array('reference' => 'PRODUCT_MEDIAS', 'folder' => '/product_medias', 'title' => 'Catalog product medias container'),
            2 => array('reference' => 'PRIVATE', 'folder' => '/private', 'title' => 'Private media container'),
        );



        foreach ($containers as $id => $infos) {
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



        foreach ($product_media_types as $id => $infos) {
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

    function importProductType(ObjectManager $manager) {

        $product_types = array(
            1 => array('reference' => 'REGULAR', 'title' => 'Regular product', 'description' => 'Regular product', 'flag_active' => 1),
            2 => array('reference' => 'SPAREPART', 'title' => 'Spare part', 'description' => 'Spare part', 'flag_active' => null),
            3 => array('reference' => 'VIRTUAL', 'title' => 'Virtual product', 'description' => 'Virtual/digital asset product/license, generally with no stock', 'flag_active' => null),
            4 => array('reference' => 'COMPOSED', 'title' => 'Composed product', 'description' => 'Composed product', 'flag_active' => null),
            5 => array('reference' => 'OFFER', 'title' => 'Offer', 'description' => 'Generated product from a combination of products, having a special price', 'flag_active' => null),
        );



        foreach ($product_types as $id => $infos) {
            $type = new Entity\ProductType();
            $type->setTypeId($id);
            $type->setReference($infos['reference']);
            $type->setTitle($infos['title']);
            $type->setDescription($infos['description']);
            $type->setFlagActive($infos['flag_active']);
            $manager->persist($type);
        }


        $metadata = $manager->getClassMetaData(get_class($type));
        $metadata->setIdGeneratorType(\Doctrine\ORM\Mapping\ClassMetadata::GENERATOR_TYPE_NONE);

        $manager->flush();
    }

    function importPackagingType(ObjectManager $manager) {

        $pack_types = array(
            1 => array('reference' => 'UNIT', 'title' => 'UNIT', 'description' => 'Unit'),
            2 => array('reference' => 'BOX', 'title' => 'BOX', 'description' => 'Box'),
            3 => array('reference' => 'CARTON', 'title' => 'CARTON', 'description' => 'Carton'),
            4 => array('reference' => 'MASTERCARTON', 'title' => 'MASTERCARTON', 'description' => 'Mastercarton'),
        );

        foreach ($pack_types as $id => $infos) {
            $type = new Entity\PackagingType();
            $type->setTypeId($id);
            $type->setReference($infos['reference']);
            $type->setTitle($infos['title']);
            $type->setDescription($infos['description']);
            $manager->persist($type);
        }

        $metadata = $manager->getClassMetaData(get_class($type));
        $metadata->setIdGeneratorType(\Doctrine\ORM\Mapping\ClassMetadata::GENERATOR_TYPE_NONE);

        $manager->flush();
    }

    function importProductStatus(ObjectManager $manager) {

        $statuses = array(
            10 => array('reference' => 'IN_DEVELOPMENT', 'title' => 'In development', 'description' => 'In development', 'flag_default' => null),
            20 => array('reference' => 'NORMAL', 'title' => 'Normal', 'description' => 'Regular sellable product', 'flag_default' => 1),
            30 => array('reference' => 'END_LIFECYCLE', 'title' => 'End of lyfecycle', 'description' => 'End of lyfecycle', 'flag_default' => null),
            40 => array('reference' => 'OBSOLETE', 'title' => 'Obsolete', 'description' => 'Obsolete or replaced by another one', 'flag_default' => null),
            40 => array('reference' => 'ARCHIVE', 'title' => 'Archive', 'description' => 'Archived product', 'flag_product_archived' => 1, 'flag_default' => null),
        );


        foreach ($statuses as $id => $infos) {
            $status = new Entity\ProductStatus();
            $status->setStatusId($id);
            $status->setReference($infos['reference']);
            $status->setTitle($infos['title']);
            $status->setDescription($infos['description']);
            $status->setFlagDefault($infos['flag_default']);
            if ($infos['flag_product_archived'] == 1) {
                $status->setFlagProductArchived(1);
            }
            $manager->persist($status);
        }


        $metadata = $manager->getClassMetaData(get_class($status));
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
        foreach ($pricelists as $id => $infos) {
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
            $pricelist->setFlagPublic(true);
            $manager->persist($pricelist);
        }

        $metadata = $manager->getClassMetaData(get_class($pricelist));
        $metadata->setIdGeneratorType(\Doctrine\ORM\Mapping\ClassMetadata::GENERATOR_TYPE_NONE);

        $manager->flush();
    }

    function importRoles(ObjectManager $manager) {
        $roles = array(
            'guest' => array('parent_id' => null),
            'member' => array('parent_id' => null),
            'customer' => array('parent_id' => 'member'),
            'moderator' => array('parent_id' => 'member'),
            'sales_affiliate' => array('parent_id' => 'member'),
            'sales_rep' => array('parent_id' => 'member'),
            'sales_manager' => array('parent_id' => 'sales_rep'),
            'content_editor' => array('parent_id' => 'member'),
            'content_admin' => array('parent_id' => 'content_editor'),
            'product_editor' => array('parent_id' => 'member'),
            'product_manager' => array('parent_id' => 'member'),
            'admin' => array('parent_id' => 'member')
        );
        foreach ($roles as $name => $infos) {
            $role = new Entity\Role();
            $role->setName($name);

            if ($infos['parent_id'] !== null) {
                $role->setParent($roles[$infos['parent_id']]['roleobject']);
            }
            $manager->persist($role);
            $roles[$name]['roleobject'] = $role;
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



        foreach ($users as $id => $infos) {
            $user = new Entity\User();
            $user->setUserId($id);
            $user->setUsername($infos['username']);
            $user->setEmail($infos['email']);
            $roles = $infos['roles'];
            $pricelists = $infos['pricelists'];

            if (count($roles) > 0) {
                foreach ($roles as $role_name) {
                    $role = $manager->getRepository('Openstore\Entity\Role')->findOneBy(array('name' => $role_name));
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
                foreach ($pricelists as $pricelist_ref) {

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

        foreach ($countries as $id => $infos) {
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


    function importCurrencies(ObjectManager $manager) {
        $currencies = array(
            $this->default_currency_id => array('reference' => 'EUR', 'title' => 'Euro', 'symbol' => '€'),
            2 => array('reference' => 'USD', 'title' => 'US Dollar', 'symbol' => '$'),
            3 => array('reference' => 'GBP', 'title' => 'British pound', 'symbol' => '£'),
            4 => array('reference' => 'CAD', 'title' => 'Canadian dollar', 'symbol' => 'C$'),
            5 => array('reference' => 'CNY', 'title' => 'Chinese Yuan', 'symbol' => '¥'),
        );

        $manager->getConnection()->executeUpdate("DELETE FROM currency where currency_id in (" . join(',', array_keys($currencies)) . ')');
        foreach ($currencies as $id => $infos) {
            $currency = new Entity\Currency();
            $currency->setCurrencyId($id);
            $currency->setReference($infos['reference']);
            $currency->setTitle($infos['title']);
            $currency->setSymbol($infos['symbol']);
            $manager->persist($currency);
        }

        $metadata = $manager->getClassMetaData(get_class($currency));
        $metadata->setIdGeneratorType(\Doctrine\ORM\Mapping\ClassMetadata::GENERATOR_TYPE_NONE);

        $manager->flush();
    }

    function importProductUnit(ObjectManager $manager) {
        $units = array(
            $this->default_unit_id => array('reference' => 'PC', 'title' => 'Piece'),
            2 => array('reference' => 'm2', 'title' => 'Square meter'),
            3 => array('reference' => 'Kg', 'title' => 'Kilogram'),
        );

        foreach ($units as $unit_id => $infos) {
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

    function importLanguages(ObjectManager $manager) {
        // Languages
        $langs = array(
            'en' => array('title' => 'English'),
            'fr' => array('title' => 'Français'),
            'nl' => array('title' => 'Nederlands'),
            'de' => array('title' => 'German'),
            'it' => array('title' => 'Italiano'),
            'es' => array('title' => 'Spanish'),
            'zh' => array('title' => 'Chinese'),
        );

        foreach ($langs as $code => $infos) {
            $lang = new Entity\Language();
            $lang->setTitle($infos['title']);
            $lang->setLang($code);
            $manager->persist($lang);
        }
        $manager->flush();
    }

}
