<?php

/**
 * 
 * @author Vanvelthem Sébastien
 */

namespace Akilia;

use Openstore\Entity;
use Akilia\Utils\Akilia1Products;
use MMan\Service\Manager as MManManager;
use Zend\Db\Adapter\Adapter;
use Zend\ServiceManager\ServiceLocatorAwareInterface;
use Zend\ServiceManager\ServiceLocatorInterface;
use Zend\Db\Adapter\AdapterAwareInterface;
use Gaufrette\Exception as GException;

function convertMemorySize($size) {
    $unit = array('b', 'kb', 'mb', 'gb', 'tb', 'pb');
    return @round($size / pow(1024, ($i = floor(log($size, 1024)))), 2) . ' ' . $unit[$i];
}

;

class Synchronizer implements ServiceLocatorAwareInterface, AdapterAwareInterface {

    /**
     *
     * @var array
     */
    protected $configuration;

    /**
     * @var Doctrine\Orm\EntityManager
     */
    protected $em;

    /**
     * mysqli connection
     * @param Mysqli
     */
    protected $mysqli;

    /**
     *
     * @var string
     */
    protected $openstoreDb;

    /**
     *
     * @var string
     */
    protected $akilia2Db;

    /**
     *
     * @var string
     */
    protected $akilia1Db;

    /**
     *
     * @var Adapter
     */
    protected $adapter;
    protected $default_currency_id = 1;
    protected $default_stock_id = 1;
    protected $default_unit_id = 1;
    protected $default_product_type_id = 1;
    protected $legacy_synchro_at;

    /**
     * 
     * @param \Doctrine\ORM\EntityManager $em
     * @param Adapter $zendDb
     */
    function __construct(\Doctrine\ORM\EntityManager $em, Adapter $zendDb) {
        $this->em = $em;

        $this->openstoreDb = $em->getConnection()->getDatabase();
        $this->mysqli = $em->getConnection()->getWrappedConnection()->getWrappedResourceHandle();
        $this->setDbAdapter($zendDb);
        $this->legacy_synchro_at = date('Y-m-d H:i:s');
    }

    /**
     * 
     * @param array $config
     * @return \Akilia\Synchronizer
     */
    function setConfiguration(array $config) {
        $this->akilia2Db = $config['db_akilia2'];
        $this->akilia1Db = $config['db_akilia1'];
        $this->akilia1lang = $config['akilia1_language_map'];
        $this->configuration = $config;
        return $this;
    }

    function synchronizeAll() {

        $this->synchronizeCountry();
        $this->synchronizeCustomer();
        $this->synchronizeApi();
        $this->synchronizePricelist();
        $this->synchronizeCustomerPricelist();
        $this->synchronizeProductGroup();
        $this->synchronizeProductBrand();
        $this->synchronizeProductCategory();
        $this->synchronizeProductModel();
        $this->synchronizeProduct();
        $this->synchronizeProductTranslation();
        $this->synchronizeProductPricelist();
        $this->synchronizeProductStock();
        $this->synchronizeProductPackaging();

        $this->rebuildCategoryBreadcrumbs();
        $this->rebuildProductSearch();


        /**

          INSERT INTO `nuvolia`.`user_scope` (
          `id` ,
          `user_id` ,
          `customer_id` ,
          `flag_active` ,
          `created_at` ,
          `updated_at` ,
          `created_by` ,
          `updated_by` ,
          `legacy_mapping` ,
          `legacy_synchro_at`
          )
          VALUES (
          NULL , '2', '3521', '1', NULL , NULL , NULL , NULL , NULL , NULL
          );

         */
    }

    /**
     * @return \Soluble\Normalist\Synthetic\TableManager
     */
    function getTableManager() {
        return $this->getServiceLocator()->get('SolubleNormalist\TableManager');
    }

    function synchronizeProductMedia() {
        ini_set('memory_limit', "1G");

        $sl = $this->getServiceLocator();
        $configuration = $sl->get('Configuration');
        if (!is_array($configuration['akilia'])) {
            throw new \Exception("Cannot find akilia configuration, please see you global config files");
        }
        $configuration = $configuration['akilia'];
        $products = new Akilia1Products($configuration);
        $products->setServiceLocator($this->getServiceLocator());
        $products->setDbAdapter($this->getServiceLocator()->get('Zend\Db\Adapter\Adapter'));

        $list = $products->getProductPictures();

        $mediaManager = $this->getServiceLocator()->get('MMan/MediaManager');

        $tm = $this->getTableManager();
        $mcTable = $tm->table('media_container');
        $container = $mcTable->findOneBy(array('reference' => 'PRODUCT_MEDIAS'));
        if (!$container) {
            throw new \Exception("Cannot find media container 'PRODUCT_MEDIAS'");
        }

        $pmtTable = $tm->table('product_media_type');
        $media_type_id = $pmtTable->findOneBy(array('reference' => 'PICTURE'))->type_id;

        if ($media_type_id == '') {
            throw new \Exception("Cannot find PICTURE product media type in your database");
        }


        $limit_to_import = 25000;
        $count = count($list);
        $productTable = $tm->table('product');
        $mediaTable = $tm->table('product_media');
        $product_ids = $productTable->search()->columns(array('product_id'))->toArrayColumn('product_id', 'product_id');
        for ($i = 0; ($i < $limit_to_import && $i < $count); $i++) {
            $infos = $list[$i];
            //var_dump($infos);
            $importElement = new \MMan\Import\Element();

            $importElement->setFilename($infos['filename']);
            $importElement->setLegacyMapping($infos['md5']);

            $media_id = $mediaManager->import($importElement, $container['container_id']);


            if (array_key_exists($infos['product_id'], $product_ids)) {
                /*
                  $product_id = $infos['product_id'];
                  echo "- " . count($product_ids) . "\n";
                  echo "- product_id:" . $product_ids[$product_id] . "\n";
                  unset($product_ids[$product_id]);
                  echo "- " . count($product_ids) . "\n";
                  echo "- product_id:" . $product_ids[$product_id] . "\n";
                  die();
                 */
                //unset($product_ids[$infos['product_id']]);
                $data = array(
                    'media_id' => $media_id,
                    'product_id' => $infos['product_id'],
                    'flag_primary' => $infos['alternate_index'] == '' ? 1 : null,
                    'sort_index' => $infos['alternate_index'] == '' ? 0 : $infos['alternate_index'],
                    'type_id' => $media_type_id,
                    'updated_at' => date('Y-m-d H:i:s')
                );
                try {
                    echo "[+] Importing product " . $infos['product_id'] . " as media_id $media_id [" . ($i + 1) . "/$count]\n";
                    $productMedia = $mediaTable->insertOnDuplicateKey($data, $duplicate_exclude = array());
                } catch (\Exception $e) {
                    echo "[Error] Cannot insert : \n";
                    var_dump($data);
                    echo "\n";
                    throw $e;
                }
            } else {
                echo "[+] Warning product '" . $infos['product_id'] . "' does not exists in database\n";
            }

            if (($i % 500) == 0) {
                echo "-----------------------------------------------------------\n";
                echo "Memory: " . convertMemorySize(memory_get_usage($real_usage = true)) . "\n";
                echo "-----------------------------------------------------------\n";
            }
        }

        echo "-----------------------------------------------------------\n";
        echo "Memory: " . convertMemorySize(memory_get_usage($real_usage = true)) . "\n";
        echo "-----------------------------------------------------------\n";
    }

    function synchronizeApi() {
        $akilia2db = $this->akilia2Db;
        $db = $this->openstoreDb;

        // Step 1: let's synchronize the api services

        $replace = " insert
                     into $db.api_service
                    (
                    service_id,    reference,    description,
                    legacy_synchro_at
                )
                select id, reference, description,
                       '{$this->legacy_synchro_at}' as legacy_synchro_at
                from $akilia2db.api_service apis
                on duplicate key update
                        reference = apis.reference,
                        description = apis.description,
                        legacy_synchro_at = '{$this->legacy_synchro_at}'
                     ";
        $this->executeSQL("Replace api_service", $replace);
        // 2. Deleting - old links in case it changes
        $delete = "
            delete from $db.api_service 
            where legacy_synchro_at <> '{$this->legacy_synchro_at}' and legacy_synchro_at is not null";
        $this->executeSQL("Delete eventual removed api_service", $delete);


        // Step 2: let' synchronize the api keys

        $replace = " insert
                     into $db.api_key
                    (
                    api_id,    api_key, flag_active,
                    legacy_synchro_at
                )
                select id, api_key, is_active,
                       '{$this->legacy_synchro_at}' as legacy_synchro_at
                from $akilia2db.auth_api aa
                on duplicate key update
                        api_key = aa.api_key,
                        flag_active = aa.is_active,
                        legacy_synchro_at = '{$this->legacy_synchro_at}'
                     ";
        $this->executeSQL("Replace api_key", $replace);
        // 2. Deleting - old links in case it changes
        $delete = "
            delete from $db.api_key 
            where legacy_synchro_at <> '{$this->legacy_synchro_at}' and legacy_synchro_at is not null";
        $this->executeSQL("Delete eventual removed api_key", $delete);

        // Step 3: api_key_services

        $replace = " insert
                     into $db.api_key_service
                    (
                    id, api_id,    service_id,
                    legacy_synchro_at
                )
                select id, api_id, service_id,
                       '{$this->legacy_synchro_at}' as legacy_synchro_at
                from $akilia2db.auth_api_service aas
                on duplicate key update
                        legacy_synchro_at = '{$this->legacy_synchro_at}'
                     ";
        $this->executeSQL("Replace api_key_service", $replace);
        // 2. Deleting - old links in case it changes
        $delete = "
            delete from $db.api_key_service 
            where legacy_synchro_at <> '{$this->legacy_synchro_at}' and legacy_synchro_at is not null";
        $this->executeSQL("Delete eventual removed api_key_service", $delete);


        // Step 4: api_key_customers
        $replace = " insert
                     into $db.api_key_customer
                    (
                    id, api_id,    customer_id,
                    legacy_synchro_at
                )
                select distinct id, api_id, customer_id,
                       '{$this->legacy_synchro_at}' as legacy_synchro_at
                from $akilia2db.auth_api_customer aac
                on duplicate key update
                        legacy_synchro_at = '{$this->legacy_synchro_at}'
                     ";
        $this->executeSQL("Replace api_key_customer", $replace);
        // 2. Deleting - old links in case it changes
        $delete = "
            delete from $db.api_key_customer 
            where legacy_synchro_at <> '{$this->legacy_synchro_at}' and legacy_synchro_at is not null";
        $this->executeSQL("Delete eventual removed api_key_customer", $delete);



        // Resync customer pricelists access
        $this->synchronizeCustomerPricelist();
    }

    function synchronizeCountry() {
        $akilia2db = $this->akilia2Db;
        $db = $this->openstoreDb;

        $replace = " insert
                     into $db.country
                    (
                    country_id,
                    reference,
                    name,
                    legacy_synchro_at
                )

                select id,
                       iso_3166_1,
                       name,
                        '{$this->legacy_synchro_at}' as legacy_synchro_at
                    
                from $akilia2db.base_country co
                on duplicate key update
                        reference = co.iso_3166_1,
                        name = co.name,
                        legacy_synchro_at = '{$this->legacy_synchro_at}'
                     ";

        $this->executeSQL("Replace countries", $replace);

        // 2. Deleting - old links in case it changes
        $delete = "
            delete from $db.country 
            where legacy_synchro_at <> '{$this->legacy_synchro_at}' and legacy_synchro_at is not null";

        $this->executeSQL("Delete eventual removed countries", $delete);
    }

    function synchronizeCustomer() {

        $akilia2db = $this->akilia2Db;
        $db = $this->openstoreDb;

        $replace = " insert
                     into $db.customer
                    (
                    customer_id,
                    reference,
                    name,
                    first_name,
                    flag_active,
                    street,
                    street_2,
                    street_number,
                    zipcode,
                    city,
                    country_id,
                    legacy_mapping,
                    legacy_synchro_at
                )

                select bc.id,
                       bc.reference,
                       bc.name,
                       bc.first_name,
                       if (bc.flag_archived = 1, 0, 1) as flag_active,
                       bc.street,
                       bc.street_2,
                       bc.street_number,
                       bc.zipcode,
                       bc.city,
                       bc.country_id,
                       bc.id as legacy_mapping,
                       '{$this->legacy_synchro_at}' as legacy_synchro_at
                    
                from $akilia2db.base_customer bc
                on duplicate key update
                       reference = bc.reference,
                       name = bc.name,
                       first_name = bc.first_name,
                       flag_active = if (bc.flag_archived = 1, 0, 1),
                       street = bc.street,
                       street_2 = bc.street_2,
                       street_number = bc.street_number,
                       zipcode = bc.zipcode,
                       city = bc.city,
                       country_id = bc.country_id,                
                       legacy_synchro_at = '{$this->legacy_synchro_at}'
                     ";

        $this->executeSQL("Replace customers", $replace);

        // 2. Deleting - old links in case it changes
        $delete = "
            delete from $db.customer
            where legacy_synchro_at <> '{$this->legacy_synchro_at}' and legacy_synchro_at is not null";

        $this->executeSQL("Delete eventual removed customers", $delete);
    }

    function synchronizeCustomerPricelist() {
        $akilia2db = $this->akilia2Db;
        $db = $this->openstoreDb;

        $replace = " insert
                     into $db.customer_pricelist
                    (
                    pricelist_id,
                    customer_id,
                    flag_active,
                    legacy_synchro_at
                )


                                select 
                                    pricelist_id, customer_id, flag_active, legacy_synchro_at
                                from
                                    ((select distinct
                                        pl.pricelist_id,
                                            c.customer_id,
                                            c.flag_active,
                                            '{$this->legacy_synchro_at}' as legacy_synchro_at
                                    FROM
                                        $akilia2db.auth_user_pricelist aup
                                    inner join $akilia2db.auth_user au ON aup.user_id = au.id
                                    inner join $akilia2db.auth_user_customer auc ON auc.user_id = au.id
                                    inner join $akilia2db.base_pricelist bpl ON aup.pricelist_id = bpl.id
                                    inner join $db.pricelist pl ON pl.reference = bpl.reference
                                    inner join $db.customer c ON c.legacy_mapping = auc.customer_id) 
                                union distinct (
                                        select distinct
                                        pl.pricelist_id,
                                            c.customer_id,
                                            c.flag_active,
                                            '{$this->legacy_synchro_at}' as legacy_synchro_at
                                    from
                                        $akilia2db.base_customer_pricelist bcpl
                                    inner join $akilia2db.base_pricelist bpl ON bcpl.pricelist_id = bpl.id
                                    inner join $db.pricelist pl ON pl.reference = bpl.reference
                                    inner join $db.customer c ON c.legacy_mapping = bcpl.customer_id)) 
                                as u

                on duplicate key update
                       flag_active = u.flag_active,
                       legacy_synchro_at = '{$this->legacy_synchro_at}'
                     ";

        $this->executeSQL("Replace customer pricelists", $replace);

        // 2. Deleting - old links in case it changes
        $delete = "
            delete from $db.customer_pricelist 
            where legacy_synchro_at <> '{$this->legacy_synchro_at}' and legacy_synchro_at is not null";

        $this->executeSQL("Delete eventual removed customer pricelists", $delete);
    }

    function synchronizeProductPricelist($use_akilia2 = true) {
        $db = $this->openstoreDb;

        if ($use_akilia2) {

            $akilia2db = $this->akilia2Db;
            $replace = " insert
                         into $db.product_pricelist
                        (
                        product_id,
                        pricelist_id,
                                                status_id,
                        price,
                        list_price,
                        public_price,
                        discount_1,
                        discount_2,
                        discount_3,
                        discount_4,
                        sale_minimum_qty,
                        is_promotional,
                        is_liquidation,
                        promo_start_at,
                        promo_end_at,
                        flag_active,
                        available_at,
                        legacy_synchro_at
                    )

                select 
                    bpp.product_id,
                    pl.pricelist_id,
                                        ps.status_id as status_id,
                    (bpp.price_sale * (1-(bpp.discount_1/100)) * (1-(bpp.discount_2/100)) * (1-(bpp.discount_3/100)) * (1-(bpp.discount_4/100))) as price,
                    bpp.price_sale as list_price,
                    bpp.price_sale_public as public_price,
                    bpp.discount_1 as discount_1,
                    bpp.discount_2 as discount_2,
                    bpp.discount_3 as discount_3,
                    bpp.discount_4 as discount_4,
                    if (bpp.sale_min_qty > 0, bpp.sale_min_qty, null) as sale_min_qty,
                    bpp.is_promotionnal as is_promotional,
                    bpp.is_liquidation as is_liquidation,
                    null as promo_start_at,
                    null as promo_end_at,
                    bpp.is_active,
                    p.created_at,
                    '{$this->legacy_synchro_at}' as legacy_synchro_at

                    from
                    $akilia2db.base_product_price as bpp
                        inner join
                    $akilia2db.base_pricelist bp ON bpp.pricelist_id = bp.id
                        inner join
                    $db.pricelist pl ON pl.legacy_mapping = bp.legacy_mapping
                        inner join
                    $db.product p on p.product_id = bpp.product_id
                                                left outer join
                                        $db.product_status ps on ps.legacy_mapping = bpp.status_code
                    where bpp.price_sale > 0
                    on duplicate key update
                            price = (bpp.price_sale * (1-(bpp.discount_1/100)) * (1-(bpp.discount_2/100)) * (1-(bpp.discount_3/100)) * (1-(bpp.discount_4/100))),
                            list_price = bpp.price_sale,
                            public_price = bpp.price_sale_public,
                            discount_1 = bpp.discount_1,
                                                        status_id = ps.status_id,
                            discount_2 = bpp.discount_2,
                            discount_3 = bpp.discount_3,
                            discount_4 = bpp.discount_4,
                            sale_minimum_qty = if (bpp.sale_min_qty > 0, bpp.sale_min_qty, null),
                            is_promotional = bpp.is_promotionnal,
                            is_liquidation = bpp.is_liquidation,
                            promo_start_at = null,
                            promo_end_at = null,

                            flag_active = bpp.is_active,
                            available_at = p.created_at,
                            legacy_synchro_at = '{$this->legacy_synchro_at}'
                         ";

            $this->executeSQL("Replace product pricelist", $replace);
        } else {
            $akilia1db = $this->akilia1Db;
            $replace = " insert
                         into $db.product_pricelist
                        (
                        product_id,
                        pricelist_id,

                        price,
                        promo_discount,
                        promo_start_at,
                        promo_end_at,
                        flag_active,
                        available_at,
                        legacy_synchro_at
                    )

                    select at.id_article,
                           pl.pricelist_id,
                           at.prix_unit_ht,
                           if((at.flag_promo = 1 or at.flag_liquidation = 1) and at.remise1 > 0, at.remise1, null) as promo_discount,
                           null as promo_start_at,
                           null as promo_end_at,
                           at.flag_availability,
                           a.date_creation as available_at,
                        '{$this->legacy_synchro_at}' as legacy_synchro_at

                    from $akilia1db.art_tarif as at
                    inner join $db.pricelist pl on at.id_pays = pl.legacy_mapping
                    inner join $akilia1db.article a on at.id_article = a.id_article    
                    where 
                                                at.prix_unit_ht > 0
                                        and a.flag_archive = 0
                                        
                    on duplicate key update
                            price = at.prix_unit_ht,
                            promo_discount = if((at.flag_promo = 1 or at.flag_liquidation = 1) and at.remise1 > 0, at.remise1, null),
                            promo_start_at = null,
                            promo_end_at = null,

                            flag_active = at.flag_availability,
                            available_at = a.date_creation,
                            legacy_synchro_at = '{$this->legacy_synchro_at}'
                         ";

            $this->executeSQL("Replace product pricelist", $replace);
        }


        // 2. Deleting - old links in case it changes
        $delete = "
            delete from $db.product_pricelist 
            where legacy_synchro_at <> '{$this->legacy_synchro_at}' and legacy_synchro_at is not null";

        $this->executeSQL("Delete eventual removed product_pricelist", $delete);
    }

    function synchronizeProductStock() {
        if (array_key_exists('options', $this->configuration) &&
                is_array($this->configuration['options']['product_stock']) &&
                is_array($this->configuration['options']['product_stock']['stocks'])) {

            $elements = $this->configuration['options']['product_stock']['stocks'];
        } else {
            $elements = array(
                'DEFAULT' => array(
                    'akilia1db' => $this->akilia1Db,
                    'pricelist' => null
                )
            );
        }



        $db = $this->openstoreDb;

        foreach ($elements as $key => $element) {
            $akilia1Db = $element['akilia1db'];
            if ($element['pricelist'] != '') {
                $pricelist_clause = "and t.id_pays = '" . $element['pricelist'] . "'";
            } else {
                $pricelist_clause = '';
            }

            $replace = " insert
                         into $db.product_stock
                        (
                        product_id,
                        stock_id,
                        available_stock,
                        theoretical_stock,
                        legacy_synchro_at,
                        updated_at
                    )

                    select distinct t.id_article,
                           pl.stock_id as stock_id,
                           t.stock,
                           t.stock_theorique,
                           '{$this->legacy_synchro_at}' as legacy_synchro_at,
                           t.date_synchro

                    from $akilia1Db.art_tarif as t
                    inner join $akilia1Db.article a on t.id_article = a.id_article
                    inner join $db.pricelist pl on t.id_pays = pl.legacy_mapping
                        $pricelist_clause
                                                    
                                        where a.flag_archive = 0

                    on duplicate key update
                            available_stock = if(product_stock.updated_at > t.date_synchro, product_stock.available_stock, t.stock),
                            theoretical_stock = if(product_stock.updated_at > t.date_synchro, product_stock.theoretical_stock, t.stock_theorique),
                            legacy_synchro_at = '{$this->legacy_synchro_at}',
                            updated_at = if(product_stock.updated_at > t.date_synchro, product_stock.updated_at, t.date_synchro)                        

                         ";

            $this->executeSQL("Replace product stock [$key] ", $replace);
        }


        // 2. Deleting - old links in case it changes
        $delete = "
            delete from $db.product_stock
            where legacy_synchro_at < '{$this->legacy_synchro_at}' and legacy_synchro_at is not null";

        $this->executeSQL("Delete eventual removed product_stock", $delete);
    }

    function synchronizePricelist($use_akilia2 = true) {
        if ($use_akilia2) {
            $akilia2db = $this->akilia2Db;
            $db = $this->openstoreDb;

            $stock_id = $this->default_stock_id;

            $replace = " insert
                         into $db.pricelist
                        (
                        reference,
                        currency_id,
                        stock_id,
                        legacy_mapping, 
                        legacy_synchro_at
                    )

                    select 
                    
                        bp.reference,
                        {$this->default_currency_id} as currency_id,
                        $stock_id as stock_id,
                        bp.legacy_mapping as legacy_mapping,
                        '{$this->legacy_synchro_at}' as legacy_synchro_at

                    from $akilia2db.base_pricelist as bp
                                            
                    on duplicate key update
                            stock_id = if(pricelist.stock_id is null, $stock_id, pricelist.stock_id),
                            currency_id = if(pricelist.currency_id is null, {$this->default_currency_id}, pricelist.currency_id),

                            legacy_synchro_at = '{$this->legacy_synchro_at}'
                         ";

            $this->executeSQL("Replace pricelist", $replace);
        } else {

            $akilia1db = $this->akilia1Db;
            $db = $this->openstoreDb;

            $stock_id = $this->default_stock_id;

            $replace = " insert
                         into $db.pricelist
                        (
                        reference,
                        currency_id,
                        stock_id,
                        legacy_mapping, 
                        legacy_synchro_at
                    )

                    select 
                        distinct at.id_pays,
                        {$this->default_currency_id} as currency_id,
                        $stock_id as stock_id,
                        at.id_pays as legacy_mapping,
                        '{$this->legacy_synchro_at}' as legacy_synchro_at

                    from $akilia1db.art_tarif as at
                    on duplicate key update
                            stock_id = if(bp.stock_id is null, $stock_id, bp.stock_id),                    
                            stock_id = if(pricelist.stock_id is null, $stock_id, pricelist.stock_id),
                            currency_id = if(pricelist.currency_id is null, {$this->default_currency_id}, pricelist.currency_id),
                            legacy_synchro_at = '{$this->legacy_synchro_at}'
                         ";

            $this->executeSQL("Replace pricelist", $replace);
        }

        // 2. Deleting - old links in case it changes
        $delete = "
            delete from $db.pricelist 
            where legacy_synchro_at <> '{$this->legacy_synchro_at}' and legacy_synchro_at is not null";

        $this->executeSQL("Delete eventual removed pricelist", $delete);
    }

    function synchronizeProductCategory() {
        $akilia1db = $this->akilia1Db;
        $db = $this->openstoreDb;
        $root_reference = 'ROOT';

        $select = "
            select upper(c.id_categorie) as id_categorie, 
                substring( upper(c.id_categorie), 1, (length( c.id_categorie ) -2 )) AS parent_categorie, 
                c.sort_index, 
                CONVERT(c.libelle_1 USING utf8) as libelle_1,
                CONVERT(c.libelle_2 USING utf8) as libelle_2,
                CONVERT(c.libelle_3 USING utf8) as libelle_3,
                CONVERT(c.libelle_4 USING utf8) as libelle_4,
                CONVERT(c.libelle_5 USING utf8) as libelle_5,
                c.date_synchro,
                category.category_id as category_id,
                                c.alt_mapping_id,
                                c.global_sort_index,
                count(*) as doubled_categs
                
            from $akilia1db.categories c
            left outer join $db.product_category category on category.legacy_mapping = c.id_categorie     
            group by 1
            order by length( c.id_categorie ), c.sort_index
        ";
        $rows = $this->em->getConnection()->query($select)->fetchAll();
        $categs = array();

        $rootCategory = $this->em->getRepository('Openstore\Entity\ProductCategory')->findOneBy(array('reference' => $root_reference));
        if ($rootCategory === null) {
            $rootCategory = new \Openstore\Entity\ProductCategory();
            $rootCategory->setReference($root_reference);
            $rootCategory->setTitle('ROOT');
            $this->em->persist($rootCategory);
            $this->em->flush();
        }

        foreach ($rows as $row) {

            if ($row['category_id'] === null) {
                $pc = new \Openstore\Entity\ProductCategory;
            } else {
                $pc = $this->em->find('Openstore\Entity\ProductCategory', $row['category_id']);
            }

            if ($row['parent_categorie'] != null) {
                $pc->setParent($categs[$row['parent_categorie']]);
            } else {
                $pc->setParent($rootCategory);
            }


            $pc->setTitle($row['libelle_1']);

            $pc->setReference($row['id_categorie']);
            $pc->setSortIndex($row['sort_index']);
            $pc->setGlobalSortIndex($row['global_sort_index']);
            $pc->setAltMappingReference($row['alt_mapping_id']);
            $pc->setLegacyMapping($row['id_categorie']);
            $pc->setLegacySynchroAt(new \DateTime($this->legacy_synchro_at));
            //$pc->setCreatedAt($row['date_synchro']);


            $this->em->persist($pc);

            $categs[$row['id_categorie']] = $pc;
        }

        $this->em->flush();


        // 2. Deleting - old links in case it changes
        $delete = "
            delete from $db.product_category 
            where legacy_synchro_at <> '{$this->legacy_synchro_at}' and legacy_synchro_at is not null";

        $this->executeSQL("Delete eventual removed categories", $delete);

        $langs = $this->akilia1lang;
        foreach ($langs as $lang => $sfx) {
            $replace = "insert into product_category_translation 
                 ( category_id,
                   lang,
                   title,
                   legacy_synchro_at
                   )
                  select
                    pc.category_id as category_id, 
                    '$lang' as lang,
                    c.libelle$sfx as title,
                    '{$this->legacy_synchro_at}'    
                  from $akilia1db.categories c
                  inner join $db.product_category pc on pc.legacy_mapping = c.id_categorie     
                 on duplicate key update
                  title = c.libelle$sfx,
                  legacy_synchro_at = '{$this->legacy_synchro_at}'      
            ";

            $this->executeSQL("Replace categories translations", $replace);
        }
        // 2. Deleting - old links in case it changes
        $delete = "
            delete from $db.product_category_translation 
            where legacy_synchro_at <> '{$this->legacy_synchro_at}' and legacy_synchro_at is not null";

        $this->executeSQL("Delete eventual removed categories translations", $delete);
    }

    function synchronizeProductModel() {

        $akilia1Db = $this->akilia1Db;
        $db = $this->openstoreDb;

        $replace = "insert into $db.product_model
                (reference, brand_id, title, legacy_mapping, legacy_synchro_at)
                select 
                trim(m.reference) as reference,
                pb.brand_id as brand_id,
                m.libelle_1 as title,
                m.id_modele as legacy_mapping,
                '{$this->legacy_synchro_at}'
                from $akilia1Db.modele m
                left outer join $db.product_brand pb on pb.legacy_mapping = m.id_marque                
            on duplicate key update
                reference = trim(m.reference),
                brand_id = pb.brand_id,
                title = m.libelle_1, 
                legacy_synchro_at = '{$this->legacy_synchro_at}'";

        $this->executeSQL("Replace product model", $replace);

        // 2. Deleting - old links in case it changes
        $delete = "
            delete from $db.product_model where
            legacy_synchro_at <> '{$this->legacy_synchro_at}' and legacy_synchro_at is not null";

        $this->executeSQL("Delete eventual removed product models", $delete);
    }

    function synchronizeProductBrand() {
        $akilia2db = $this->akilia2Db;
        $db = $this->openstoreDb;

        $replace = "insert into $db.product_brand
                (brand_id, reference, title, url, legacy_mapping, legacy_synchro_at)
                select bpb.id, TRIM(bpb.reference), bpb.name, bpb.url, bpb.legacy_mapping, '{$this->legacy_synchro_at}'
            from $akilia2db.base_product_brand bpb
            on duplicate key update
                reference = trim(bpb.reference),
                title = bpb.name, 
                url = bpb.url,
                legacy_synchro_at = '{$this->legacy_synchro_at}'";

        $this->executeSQL("Replace product brands", $replace);

        // 2. Deleting - old links in case it changes
        $delete = "
            delete from $db.product_brand where
            legacy_synchro_at <> '{$this->legacy_synchro_at}' and legacy_synchro_at is not null";

        $this->executeSQL("Delete eventual removed brands", $delete);
    }

    function synchronizeProductGroup() {
        $akilia1Db = $this->akilia1Db;
        $db = $this->openstoreDb;

        $use_upper = false;
        if ($use_upper) {
            $group_ref_clause = "UPPER(TRIM(f.id_famille))";
        } else {
            $group_ref_clause = "TRIM(f.id_famille)";
        }

        $replace = "insert into $db.product_group
                (group_id, reference, title, legacy_mapping, legacy_synchro_at)
                select null, 
                       $group_ref_clause, 
                       f.libelle_1 as title, 
                       $group_ref_clause as legacy_mapping, 
                       '{$this->legacy_synchro_at}'
            from $akilia1Db.famille f
            on duplicate key update
                reference = $group_ref_clause,
                title = f.libelle_1, 
                legacy_synchro_at = '{$this->legacy_synchro_at}'";
        $this->executeSQL("Replace product groups", $replace);

        // 2. Deleting - old links in case it changes
        $delete = "
            delete from $db.product_group where
            legacy_synchro_at <> '{$this->legacy_synchro_at}' and legacy_synchro_at is not null";

        $this->executeSQL("Delete eventual removed groups", $delete);

        // 3. Group translations

        $langs = $this->akilia1lang;
        foreach ($langs as $lang => $sfx) {
            $replace = "insert into product_group_translation 
                 ( group_id,
                   lang,
                   title,
                   legacy_synchro_at
                   )
                  
                select 
                        pg.group_id, 
                        '$lang' as lang, 
                        f.libelle$sfx as title, 
                        '{$this->legacy_synchro_at}'
                    from $akilia1Db.famille f
                    inner join $db.product_group pg on pg.legacy_mapping =     TRIM(f.id_famille)
                on duplicate key update
                    title = f.libelle$sfx, 
                    legacy_synchro_at = '{$this->legacy_synchro_at}'";


            $this->executeSQL("Replace product group translations", $replace);
        }
        // 2. Deleting - old links in case it changes
        $delete = "
            delete from $db.product_group_translation 
            where legacy_synchro_at <> '{$this->legacy_synchro_at}' and legacy_synchro_at is not null";

        $this->executeSQL("Delete eventual removed product group translations", $delete);
    }

    function synchronizeProductPackaging() {
        $akilia1db = $this->akilia1Db;

        $db = $this->openstoreDb;


        $replace = "
                    insert into $db.product_packaging (
                            product_id, 
                            type_id, 
                            quantity,
                            barcode_ean, 
                            barcode_upc, 
                            volume, 
                            weight, 
                            length, 
                            height, 
                            width, 
                            legacy_synchro_at
                    )
                    select 
                        packs.product_id,
                        pt.type_id,
                        packs.quantity,
                        packs.barcode_ean,
                        packs.barcode_upc,
                        packs.volume,
                        packs.weight,
                        packs.length,
                        packs.height,
                        packs.width,
                        '{$this->legacy_synchro_at}' as legacy_synchro_at
                    from
                        ((select 
                            id_article as product_id,
                            'CARTON' as packaging_reference,
                            qty_carton as quantity,
                            barcode_pack_box_ean as barcode_ean,
                            barcode_pack_box_upc as barcode_upc,
                            (volume * qty_carton) as volume,
                            (poids * qty_carton) as weight,
                            -- We can only trust length, width and height for
                            -- carton having the same quantity as master carton (or no quantity in master)
                            if ((qty_carton >= qty_master_carton), pack_length * qty_carton, null) as length,
                            if ((qty_carton >= qty_master_carton), pack_height * qty_carton, null) as height,
                            if ((qty_carton >= qty_master_carton), pack_width * qty_carton, null) as width
                        from
                            $akilia1db.article
                        where
                            qty_carton > 0 ) 
                        union (select 
                            id_article as product_id,
                                'MASTERCARTON' as packaging_reference,
                                qty_master_carton as quantity,
                                barcode_pack_master_ean as barcode_ean,
                                barcode_pack_master_upc as barcode_upc,
                                (volume * qty_master_carton) as volume,
                                (poids * qty_master_carton) as weight,
                                (pack_length * qty_master_carton) as length,
                                (pack_height * qty_master_carton) as height,
                                (pack_width * qty_master_carton) as width
                        from
                            $akilia1db.article
                        where
                            qty_master_carton > 0
                        ) 
                        union (select 
                            id_article as product_id,
                                'UNIT' as packaging_reference,
                                1 as quantity,
                                barcode_ean13 as barcode_ean,
                                barcode_upca as barcode_upc,
                                (volume * 1) as volume,
                                (poids * 1) as weight,
                                -- We can only trust length, width and height for
                                -- products not packaged in master carton or box
                                if ((qty_carton=1 and qty_master_carton=1), pack_length, null) as length,
                                if ((qty_carton=1 and qty_master_carton=1), pack_height, null) as height,
                                if ((qty_carton=1 and qty_master_carton=1), (pack_width), null) as width
                        from
                            $akilia1db.article)) as packs
                            inner join
                        $db.packaging_type pt ON packs.packaging_reference = pt.reference
                             inner join
                        $db.product p on p.product_id = packs.product_id
                    order by packs.product_id , pt.type_id
                    ON DUPLICATE KEY update
                            quantity = packs.quantity,
                            barcode_ean = packs.barcode_ean,
                            barcode_upc = packs.barcode_upc,
                            volume = packs.volume,
                            weight = packs.weight,
                            length = packs.length,
                            height = packs.height,
                            width = packs.width,
                            legacy_synchro_at = '{$this->legacy_synchro_at}'
                ";

        $this->executeSQL("Replace product packagings", $replace);

        // 2. Deleting - old links in case it changes
        $delete = "
            delete from $db.product_packaging 
            where legacy_synchro_at <> '{$this->legacy_synchro_at}' and legacy_synchro_at is not null";

        $this->executeSQL("Delete eventual removed product packagings", $delete);
    }

    function synchronizeProduct() {

        $akilia1db = $this->akilia1Db;
        $akilia2db = $this->akilia2Db;
        $db = $this->openstoreDb;

        $replace = " insert
                     into $db.product
                    (product_id,
                    model_id,
                    brand_id,
                    group_id,
                    category_id,
                    unit_id,
                    type_id,
                                        status_id,
                    parent_id,
                    reference, 
                    display_reference,
                    search_reference,
                    slug,
                    title,
                    invoice_title,
                    description,
                    characteristic,
                    
                    flag_active,
                    icon_class,
                    
                    volume,
                    weight,
                    length,
                    height,
                    width,
                    
                    pack_qty_box,
                    pack_qty_carton,
                    pack_qty_master_carton,
                    
                    barcode_ean13,
                    barcode_upca,
                    
                    sort_index,    

                    available_at,

                    legacy_mapping, 
                    legacy_synchro_at
                )

                select
                    a.id_article as product_id,
                    pm.model_id as model_id,
                    brand.brand_id as brand_id,
                    product_group.group_id as group_id,
                    category.category_id as category_id,
                    {$this->default_unit_id} as unit_id,
                    COALESCE(pt.type_id, {$this->default_product_type_id}) as type_id,
                                        
                    ps.status_id as status_id,    
                    if (i.id_art_tete <> 0 and i.id_art_tete <> '' and i.id_art_tete is not null, i.id_art_tete, null) as parent_id,     
                    upper(TRIM(a.reference)) as reference,
                                        upper(TRIM(a.reference)) as display_reference,
                                        get_searchable_reference(a.reference) as search_reference,                                        
                    null as slug,
                    if(trim(i.libelle_1) = '', null, trim(i.libelle_1)) as title,
                    if(trim(a.libelle_1) = '', null, trim(a.libelle_1)) as invoice_title,
                    if(trim(i.desc_1) = '', null, trim(i.desc_1)) as description,
                    if(trim(i.couleur_1) = '', null, trim(i.couleur_1)) as characteristic,
                    
                    if(a.flag_archive = 1, 0, 1) as flag_active,
                    null as icon_class,
                    a.volume as volume,
                    a.poids as weight,
                    -- dimensions are not yet supported
                    null as length,
                    null as height,
                    null as width,
                        -- Qty box has been deprecated, use pack_qty_carton
                    bp.pack_qty_box,
                                        
                    bp.pack_qty_box as pack_qty_carton,
                    bp.pack_qty_master_carton,
                    
                    a.barcode_ean13 as barcode_ean13,
                    a.barcode_upca as barcode_upca,
                                        a.code_tri_marque_famille as sort_index,
                    a.date_creation,
                    a.id_article as legacy_mapping,
                    '{$this->legacy_synchro_at}' as legacy_synchro_at
                        
                    
                from $akilia1db.article as a
                left outer join $akilia2db.base_product bp on bp.legacy_mapping = a.id_article    
                left outer join $akilia1db.cst_art_infos i on i.id_article = a.id_article    
                left outer join $db.product_brand as brand on brand.legacy_mapping = a.id_marque
                left outer join $db.product_group as product_group on product_group.legacy_mapping = a.id_famille
                left outer join $db.product_category as category on category.legacy_mapping = a.id_categorie
                left outer join $db.product_model as pm on pm.legacy_mapping = a.id_modele
                left outer join $db.product_status ps on ps.legacy_mapping = a.code_suivi
                left outer join $db.product_type pt on pt.legacy_mapping = a.product_type COLLATE 'utf8_general_ci'
                
                where a.flag_archive = 0

                on duplicate key update
                        model_id = pm.model_id,
                        brand_id = brand.brand_id,
                        type_id = {$this->default_product_type_id},
                        group_id = product_group.group_id,
                        unit_id = {$this->default_unit_id},
                        parent_id = if (i.id_art_tete <> 0 and i.id_art_tete <> '' and i.id_art_tete is not null, i.id_art_tete, null),     
                        status_id = ps.status_id,        
                        category_id = category.category_id,
                        reference = upper(TRIM(a.reference)),
                        display_reference = upper(TRIM(a.reference)),
                        search_reference = get_searchable_reference(a.reference),                                                                                        
                        slug = null,
                        sort_index = a.code_tri_marque_famille,
                        type_id = COALESCE(pt.type_id, {$this->default_product_type_id}),
                        title = if(trim(i.libelle_1) = '', null, trim(i.libelle_1)),
                        invoice_title = if(trim(a.libelle_1) = '', null, trim(a.libelle_1)),
                        description = if(trim(i.desc_1) = '', null, trim(i.desc_1)),
                        characteristic = if(trim(i.couleur_1) = '', null, trim(i.couleur_1)),
                        flag_active = if(a.flag_archive = 1, 0, 1),
                        icon_class = null,
                        volume = a.volume,
                        weight = a.poids,
                        -- Dimensions are not yet supported
                        length = null,
                        height = null,
                        width = null,
                        
                        -- Qty carton has been deprecated, use pack_qty_carton
                        pack_qty_box = bp.pack_qty_box,
                        
                        pack_qty_carton = bp.pack_qty_box,
                        pack_qty_master_carton = bp.pack_qty_master_carton,
                        
                        barcode_ean13 = a.barcode_ean13,
                        barcode_upca = a.barcode_upca,
                        available_at = a.date_creation,
                        legacy_mapping = a.id_article,
                        legacy_synchro_at = '{$this->legacy_synchro_at}'
                     ";
        $this->executeSQL("Replace product", $replace);

        // 2. Deleting - old links in case it changes
        $delete = "
            delete from $db.product 
            where legacy_synchro_at <> '{$this->legacy_synchro_at}' and legacy_synchro_at is not null";

        $this->executeSQL("Delete eventual removed products", $delete);
    }

    /**
     * 
     * @param string $column_name
     * @return string
     */
    protected function hackUtf8TranslationColumn($column_name) {
        return "CONVERT(CONVERT(CONVERT($column_name USING latin1) using binary) USING utf8)";
    }

    function synchronizeProductTranslation() {
        $akilia1db = $this->akilia1Db;
        $db = $this->openstoreDb;

        $langs = $this->akilia1lang;


        foreach ($langs as $lang => $sfx) {

            if ($lang == 'zh') {
                // Handle a double encoding bug in chinese only
                $title = "if (trim(i.libelle$sfx) = '', null, trim(" . $this->hackUtf8TranslationColumn("i.libelle$sfx") . "))";
                $invoice_title = "if (trim(a.libelle$sfx) = '', null, trim(" . $this->hackUtf8TranslationColumn("a.libelle$sfx") . "))";
                $description = "if (i2.id_article is not null, 
                                    -- if (trim(i2.desc$sfx) = '', null, trim(" . $this->hackUtf8TranslationColumn("i2.desc$sfx") . ")),        
                                    null,            
                                    if (trim(i.desc$sfx) = '', null, trim(" . $this->hackUtf8TranslationColumn("i.desc$sfx") . "))
                                )
                ";
                $characteristic = "if (trim(i.couleur$sfx) = '', null, trim(" . $this->hackUtf8TranslationColumn("i.couleur$sfx") . "))";
            } else {
                $title = "if (trim(i.libelle$sfx) = '', null, trim(i.libelle$sfx))";
                $invoice_title = "if (trim(a.libelle$sfx) = '', null, trim(a.libelle$sfx))";
                $description = "if (i2.id_article is not null, 
                                    -- if (trim(i2.desc$sfx) = '', null, trim(i2.desc$sfx)),        
                                    null,    
                                    if (trim(i.desc$sfx) = '', null, trim(i.desc$sfx))
                                )
                ";
                $characteristic = "if (trim(i.couleur$sfx) = '', null, trim(i.couleur$sfx))";
            }

            $replace = "insert into product_translation 
                 ( product_id,
                   lang,
                   title,
                   invoice_title,
                   description,
                   characteristic,
                   legacy_synchro_at
                   )
                  select
                    p.product_id as product_id, 
                    '$lang' as lang,
                    $title as title,
                    $invoice_title as invoice_title,    
                    $description as description,
                    $characteristic as characteristic,        
                    '{$this->legacy_synchro_at}'    
                  from $akilia1db.article a
                  inner join $db.product p on p.legacy_mapping = a.id_article     
                  left outer join $akilia1db.cst_art_infos i on i.id_article = a.id_article
                  left outer join $akilia1db.cst_art_infos i2 on 
                      (i.id_art_tete = i2.id_article and i.id_art_tete <> 0 and i.id_art_tete <> '')
                 where 
                                        a.flag_archive = 0
                                        and

                    CHAR_LENGTH(coalesce(trim(a.libelle$sfx), '')) + CHAR_LENGTH(coalesce(trim(i.libelle$sfx), '')) +
                    CHAR_LENGTH(coalesce(trim(i.desc$sfx), '')) + CHAR_LENGTH(coalesce(trim(i.couleur$sfx), '')) +
                    CHAR_LENGTH(coalesce(trim(i2.desc$sfx), '')) > 0
                                        
                 on duplicate key update
                  title = $title,
                  invoice_title = $invoice_title,
                  description = $description,
                  characteristic = $characteristic,
                  legacy_synchro_at = '{$this->legacy_synchro_at}'      
            ";



            $this->executeSQL("Replace product translations", $replace);
        }
        // 2. Deleting - old links in case it changes
        $delete = "
            delete from $db.product_translation 
            where legacy_synchro_at <> '{$this->legacy_synchro_at}' and legacy_synchro_at is not null";

        $this->executeSQL("Delete eventual removed product translations", $delete);
    }

    function rebuildProductSearch() {

        $query = "CALL rebuild_product_search()";
        $this->executeSQL('Rebuild product search', $query);
    }

    function rebuildCategoryBreadcrumbs() {

        $query = "CALL rebuild_category_breadcrumbs()";
        $this->executeSQL('Rebuild category breadcrumbs', $query);
    }

    /**
     * Execute a query on the database and logs it
     * 
     * @throws Exception
     * 
     * @param string $key name of the query
     * @param string $query 
     * @param boolean $disable_foreign_key_checks
     * @return void
     */
    protected function executeSQL($key, $query, $disable_foreign_key_checks = true) {
        $this->log("Sync::executeSQL '$key'...\n");

        $total_time_start = microtime(true);

        if ($disable_foreign_key_checks) {
            $time_start = microtime(true);
            $this->mysqli->query('set foreign_key_checks=0');
            $time_stop = microtime(true);
            $time = number_format(($time_stop - $time_start), 2);
            $this->log("  * Disabling foreign key checks (in time $time sec(s))\n");
        }


        $time_start = microtime(true);
        $result = $this->mysqli->query($query);
        $affected_rows = $this->mysqli->affected_rows;
        $time_stop = microtime(true);
        $time = number_format(($time_stop - $time_start), 2);
        $this->log("  * Querying database (in time $time sec(s))\n");
        $formatted_query = preg_replace('/(\n)|(\r)|(\t)/', ' ', $query);
        $formatted_query = preg_replace('/(\ )+/', ' ', $formatted_query);

        $this->log("  * " . substr($formatted_query, 0, 60));

        if (!$result) {
            $msg = "Error running query ({$this->mysqli->error}) : \n--------------------\n$query\n------------------\n";
            $this->log("[+] $msg\n");
            if ($disable_foreign_key_checks) {
                $this->log("[Error] Error restoring foreign key checks\n");
                $this->mysqli->query('set foreign_key_checks=1');
            }
            throw new \Exception($msg);
        }

        if ($disable_foreign_key_checks) {
            $time_start = microtime(true);
            $this->mysqli->query('set foreign_key_checks=1');
            $time_stop = microtime(true);
            $time = number_format(($time_stop - $time_start), 2);
            $this->log("  * RESTORING foreign key checks  (in time $time sec(s))\n");
        }
        $time_stop = microtime(true);
        $time = number_format(($time_stop - $total_time_start), 2);
        $this->log(" [->] Success in ExecuteSQL '$key' in total $time secs, affected rows $affected_rows.\n");
    }

    /**
     * Log message
     * @param string $message
     * @param int $priority
     * @return void
     */
    protected function log($message, $priority = null) {
        echo "$message\n";
    }

    /**
     * 
     * @param \Zend\Db\Adapter\Adapter $adapter
     * @return Synchronizer
     */
    public function setDbAdapter(Adapter $adapter) {
        $this->adapter = $adapter;
        return $this;
    }

    /**
     * 
     * @return Zend\Db\Adapter\Adapter
     */
    function getDbAdapter() {
        return $this->adapter;
    }

    /**
     * 
     * @param \Zend\ServiceManager\ServiceLocatorInterface $serviceLocator
     */
    public function setServiceLocator(ServiceLocatorInterface $serviceLocator) {
        $this->serviceLocator = $serviceLocator;
        return $this;
    }

    /**
     * 
     * @return \Zend\ServiceManager\ServiceLocatorInterface
     */
    public function getServiceLocator() {
        return $this->serviceLocator;
    }

    /**
     * 
     * @return \MMan\Service\Manager
     */
    public function getMManManager() {
        return $this->getServiceLocator()->get('MMan\Manager');
    }

}
