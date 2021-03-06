<?php

namespace Openstore;

use OpenstoreSchema\Core\Entity;
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

        $this->importProductType($manager);
        $this->importProductStatus($manager);
        $this->importPackagingType($manager);

        $this->importProductUnit($manager);


        $this->importOrderInfos($manager);
    }

    public function importOrderInfos($manager)
    {

        // step 1 adding order statuses
        $statuses = [
            100 => ['reference' => 'CREATED', 'title' => 'Initial status / order created', 'flag_default' => true, 'flag_readonly' => false],
            200 => ['reference' => 'CONFIRMED', 'title' => 'Confirmed order', 'flag_default' => null, 'flag_readonly' => false],
            400 => ['reference' => 'IMPORTED', 'title' => 'Imported in legacy system', 'flag_default' => null, 'flag_readonly' => false],
            500 => ['reference' => 'WAITING_APPROVAL', 'title' => 'Waiting approval', 'flag_default' => null, 'flag_readonly' => false],
            600 => ['reference' => 'APPROVED', 'title' => 'Approved', 'flag_default' => null, 'flag_readonly' => false],
            1000 => ['reference' => 'FULLY_DELIVERED', 'title' => 'Fully delivered', 'flag_default' => null, 'flag_readonly' => true],
            2000 => ['reference' => 'FULLY_INVOICED', 'title' => 'Fully invoiced', 'flag_default' => null, 'flag_readonly' => true],
            5000 => ['reference' => 'COMPLETE', 'title' => 'Complete', 'flag_default' => null, 'flag_readonly' => true],
            9000 => ['reference' => 'CANCELLED', 'title' => 'Cancelled', 'flag_default' => null, 'flag_readonly' => true],
        ];

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
        $line_statuses = [
            100 => ['reference' => 'CREATED', 'title' => 'Created', 'flag_default' => true, 'flag_readonly' => false],
            120 => ['reference' => 'PICKED', 'title' => 'Picked, ready for delivery', 'flag_default' => null, 'flag_readonly' => false],
            200 => ['reference' => 'DELIVERED', 'title' => 'Delivered', 'flag_default' => null, 'flag_readonly' => true],
            300 => ['reference' => 'INVOICED', 'title' => 'Invoiced', 'flag_default' => null, 'flag_readonly' => true],
            900 => ['reference' => 'CANCELLED', 'title' => 'Cancelled', 'flag_default' => null, 'flag_readonly' => true]
        ];

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
        $types = [
            1000 => ['reference' => 'QUOTE', 'title' => 'Quote'],
            2000 => ['reference' => 'REGULAR', 'title' => 'Regular order'],
            5000 => ['reference' => 'WEB', 'title' => 'Web order'],
            9000 => ['reference' => 'SHOPCART', 'title' => 'Shopcart in progress'],
            20000 => ['reference' => 'DEPOSIT', 'title' => 'Deposit']
        ];

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

    public function importMediaContainers(ObjectManager $manager)
    {
        $containers = [
            1 => ['reference' => 'PRODUCT_MEDIAS', 'folder' => '/product_medias', 'title' => 'Catalog product medias container'],
            2 => ['reference' => 'PRIVATE', 'folder' => '/private', 'title' => 'Private media container'],
        ];



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

    public function importProductMediaTypes(ObjectManager $manager)
    {
        $product_media_types = [
            1 => ['reference' => 'PICTURE', 'title' => 'Official picture'],
            2 => ['reference' => 'ALTERNATE_PICTURE', 'title' => 'Alternate pictures'],
            3 => ['reference' => 'VIDEO', 'title' => 'Video'],
            4 => ['reference' => 'SOUND', 'title' => 'Sounds and recordings'],
            5 => ['reference' => 'DOCUMENT', 'title' => 'Documents'],
        ];



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

    public function importProductType(ObjectManager $manager)
    {
        $product_types = [
            1 => ['reference' => 'REGULAR', 'title' => 'Regular product', 'description' => 'Regular product', 'flag_active' => 1, 'fedc' => 1, 'flag_default' => 1],
            2 => ['reference' => 'SPAREPART', 'title' => 'Spare part', 'description' => 'Spare part', 'flag_active' => null, 'fedc' => 1],
            3 => ['reference' => 'VIRTUAL', 'title' => 'Virtual product', 'description' => 'Virtual/digital asset product/license, generally with no stock', 'flag_active' => null, 'fedc' => 0],
            4 => ['reference' => 'COMPOSED', 'title' => 'Composed product', 'description' => 'Composed product', 'flag_active' => null, 'fedc' => 0],
            5 => ['reference' => 'OFFER', 'title' => 'Offer', 'description' => 'Generated product from a combination of products, having a special price', 'flag_active' => null, 'fedc' => 0],
        ];



        foreach ($product_types as $id => $infos) {
            $type = new Entity\ProductType();
            $type->setTypeId($id);
            $type->setReference($infos['reference']);
            $type->setTitle($infos['title']);
            $type->setDescription($infos['description']);
            $type->setFlagActive($infos['flag_active']);
            if (isset($infos['flag_default'])) {
                $type->setFlagDefault($infos['flag_default']);
            }
            $type->setLegacyMapping($infos['reference']);
            $type->setFlagEnableDiscountCondition($infos['fedc']);
            $manager->persist($type);
        }


        $metadata = $manager->getClassMetaData(get_class($type));
        $metadata->setIdGeneratorType(\Doctrine\ORM\Mapping\ClassMetadata::GENERATOR_TYPE_NONE);

        $manager->flush();
    }

    public function importPackagingType(ObjectManager $manager)
    {
        $pack_types = [
            1 => ['reference' => 'UNIT', 'title' => 'Unit', 'description' => 'Unit'],
            2 => ['reference' => 'BOX', 'title' => 'Box', 'description' => 'Box'],
            3 => ['reference' => 'CARTON', 'title' => 'Carton', 'description' => 'Carton'],
            4 => ['reference' => 'MASTERCARTON', 'title' => 'Mastercarton', 'description' => 'Mastercarton'],
            5 => ['reference' => 'PALET', 'title' => 'Palet', 'description' => 'Palet'],
        ];

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

    public function importProductStatus(ObjectManager $manager)
    {
        $statuses = [
            10 => ['reference' => 'IN_DEVELOPMENT', 'teos' => null, 'eol' => null, 'title' => 'In development', 'description' => 'In development', 'flag_default' => null],
            20 => ['reference' => 'NORMAL', 'teos' => null, 'eol' => null, 'title' => 'Normal', 'description' => 'Regular sellable product', 'flag_default' => 1],
            30 => ['reference' => 'END_LIFECYCLE', 'teos' => true, 'eol' => true, 'title' => 'End of lyfecycle', 'description' => 'End of lyfecycle', 'flag_default' => null],
            40 => ['reference' => 'OBSOLETE', 'teos' => null, 'eol' => null, 'title' => 'Obsolete', 'description' => 'Obsolete or replaced by another one', 'flag_default' => null],
            40 => ['reference' => 'ARCHIVE', 'teos' => null, 'eol' => null, 'title' => 'Archive', 'description' => 'Archived product', 'flag_product_archived' => 1, 'flag_default' => null],
        ];


        foreach ($statuses as $id => $infos) {
            $status = new Entity\ProductStatus();
            $status->setStatusId($id);
            $status->setReference($infos['reference']);
            $status->setTitle($infos['title']);
            $status->setDescription($infos['description']);
            $status->setFlagDefault($infos['flag_default']);
            $status->setFlagTillEndOfStock($infos['teos']);
            $status->setFlagEndOfLifecycle($infos['eol']);
            if ($infos['flag_product_archived'] == 1) {
                $status->setFlagProductArchived(1);
            }
            $manager->persist($status);
        }


        $metadata = $manager->getClassMetaData(get_class($status));
        $metadata->setIdGeneratorType(\Doctrine\ORM\Mapping\ClassMetadata::GENERATOR_TYPE_NONE);

        $manager->flush();
    }



    public function importStock(ObjectManager $manager)
    {
        $stock = new Entity\Stock();

        $stock->setStockId($this->default_stock_id);
        $stock->setReference('DEFAULT');
        $stock->setTitle('Default warehouse');
        $manager->persist($stock);

        $metadata = $manager->getClassMetaData(get_class($stock));
        $metadata->setIdGeneratorType(\Doctrine\ORM\Mapping\ClassMetadata::GENERATOR_TYPE_NONE);

        $manager->flush();
    }

    public function importPricelists(ObjectManager $manager)
    {
        $stock_id = $this->default_stock_id;
        $currency_id = $this->default_currency_id;
        $pricelists = [
            1 => ['reference' => 'BE', 'title' => 'Belgium Pricelist', 'stock_id' => $stock_id, 'currency_id' => $currency_id],
            2 => ['reference' => 'FR', 'title' => 'French Pricelist', 'stock_id' => $stock_id, 'currency_id' => $currency_id],
            3 => ['reference' => 'NL', 'title' => 'NL Pricelist', 'stock_id' => $stock_id, 'currency_id' => $currency_id],
        ];

        $manager->getConnection()->executeUpdate("DELETE FROM pricelist where pricelist_id in (" . implode(',', array_keys($pricelists)) . ')');
        foreach ($pricelists as $id => $infos) {
            $stock = $manager->getRepository('OpenstoreSchema\Core\Entity\Stock')->find($infos['stock_id']);

            $currency = $manager->getRepository('OpenstoreSchema\Core\Entity\Currency')->find($infos['currency_id']);

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
            $pricelist->setFlagEnableDiscountCondition(1);
            $manager->persist($pricelist);
        }

        $metadata = $manager->getClassMetaData(get_class($pricelist));
        $metadata->setIdGeneratorType(\Doctrine\ORM\Mapping\ClassMetadata::GENERATOR_TYPE_NONE);

        $manager->flush();
    }

    public function importRoles(ObjectManager $manager)
    {
        $roles = [
            'guest' => ['parent_id' => null],
            'member' => ['parent_id' => null],
            'customer' => ['parent_id' => 'member'],
            'moderator' => ['parent_id' => 'member'],
            'sales_affiliate' => ['parent_id' => 'member'],
            'sales_rep' => ['parent_id' => 'member'],
            'sales_manager' => ['parent_id' => 'sales_rep'],
            'content_editor' => ['parent_id' => 'member'],
            'content_admin' => ['parent_id' => 'content_editor'],
            'product_editor' => ['parent_id' => 'member'],
            'product_manager' => ['parent_id' => 'member'],
            'admin' => ['parent_id' => 'member']
        ];
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

    public function importUser(ObjectManager $manager)
    {
        $users = [
            1 => ['username' => 'admin', 'email' => 's.vanvelthem@gmail.com', 'password' => 'changeme',
                'roles' => [
                    'admin'
                ],
                'pricelists' => [
                    'BE', 'FR', 'NL'
                ]],
                2 => ['username' => 'testcustomer', 'email' => 'sebastien@nuvolia.com', 'password' => 'changeme',
                'roles' => [
                    'customer'
                ],
                'pricelists' => [
                    'BE'
                ]],
        ];

        $manager->getConnection()->executeUpdate("DELETE FROM user_role where user_id in (" . implode(',', array_keys($users)) . ')');
        $manager->getConnection()->executeUpdate("DELETE FROM user where user_id in (" . implode(',', array_keys($users)) . ')');

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
                    $role = $manager->getRepository('OpenstoreSchema\Core\Entity\Role')->findOneBy(['name' => $role_name]);
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
                    $pricelist = $manager->getRepository('OpenstoreSchema\Core\Entity\Pricelist')->findOneBy(['reference' => $pricelist_ref]);
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

    public function importCountries(ObjectManager $manager)
    {
        $countries = [
            1 => ['reference' => 'BE', 'name' => 'Belgium']
        ];
        $manager->getConnection()->executeUpdate("DELETE FROM country where country_id in (" . implode(',', array_keys($countries)) . ')');

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


    public function importCurrencies(ObjectManager $manager)
    {
        $currencies = [
            $this->default_currency_id => ['reference' => 'EUR', 'title' => 'Euro', 'symbol' => '€'],
            2 => ['reference' => 'USD', 'title' => 'US Dollar', 'symbol' => '$', 'display_decimals' => 2],
            3 => ['reference' => 'GBP', 'title' => 'British pound', 'symbol' => '£', 'display_decimals' => 2],
            4 => ['reference' => 'CAD', 'title' => 'Canadian dollar', 'symbol' => 'C$', 'display_decimals' => 2],
            5 => ['reference' => 'CNY', 'title' => 'Chinese Yuan', 'symbol' => '¥', 'display_decimals' => 2],
        ];

        $manager->getConnection()->executeUpdate("DELETE FROM currency where currency_id in (" . implode(',', array_keys($currencies)) . ')');

        foreach ($currencies as $id => $infos) {
            $currency = new Entity\Currency();
            $currency->setCurrencyId($id);
            $currency->setReference($infos['reference']);
            $currency->setTitle($infos['title']);
            $currency->setSymbol($infos['symbol']);
            $currency->setDisplayDecimals($infos['display_decimals']);
            $manager->persist($currency);
        }

        $metadata = $manager->getClassMetaData(get_class($currency));
        $metadata->setIdGeneratorType(\Doctrine\ORM\Mapping\ClassMetadata::GENERATOR_TYPE_NONE);

        $manager->flush();
    }

    public function importProductUnit(ObjectManager $manager)
    {
        $units = [
            $this->default_unit_id => ['reference' => 'PC', 'title' => 'Piece', 'display_decimals' => 0, 'symbol' => ''],
            2 => ['reference' => 'M', 'title' => 'Meter', 'display_decimals' => 2, 'symbol' => 'm'],
            3 => ['reference' => 'M2', 'title' => 'Square meter', 'display_decimals' => 2, 'symbol' => 'm²'],
            4 => ['reference' => 'M3', 'title' => 'Square meter', 'display_decimals' => 2, 'symbol' => 'm³'],
            5 => ['reference' => 'KG', 'title' => 'Kilogram', 'display_decimals' => 1, 'symbol' => 'kg'],
            6 => ['reference' => 'T', 'title' => 'Ton', 'display_decimals' => 3, 'symbol' => 'T'],
        ];

        foreach ($units as $unit_id => $infos) {
            $unit = new Entity\ProductUnit();
            $unit->setUnitId($unit_id);
            $unit->setReference($infos['reference']);
            $unit->setTitle($infos['title']);
            $unit->setDisplayDecimals($infos['display_decimals']);
            $unit->setSymbol($infos['symbol']);
            $manager->persist($unit);
        }

        $metadata = $manager->getClassMetaData(get_class($unit));
        $metadata->setIdGeneratorType(\Doctrine\ORM\Mapping\ClassMetadata::GENERATOR_TYPE_NONE);

        $manager->flush();
    }

    public function importLanguages(ObjectManager $manager)
    {
        // Languages
        $langs = [
            'en' => ['title' => 'English'],
            'fr' => ['title' => 'Français'],
            'nl' => ['title' => 'Nederlands'],
            'de' => ['title' => 'German'],
            'it' => ['title' => 'Italiano'],
            'es' => ['title' => 'Spanish'],
            'zh' => ['title' => 'Chinese'],
        ];

        foreach ($langs as $code => $infos) {
            $lang = new Entity\Language();
            $lang->setTitle($infos['title']);
            $lang->setLang($code);
            $manager->persist($lang);
        }
        $manager->flush();
    }
}
