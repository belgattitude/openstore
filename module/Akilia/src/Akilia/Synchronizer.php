<?php

/**
 *
 * @author Vanvelthem Sébastien
 */
namespace Akilia;

use Akilia\Utils\Akilia1Products;
use Zend\Db\Adapter\Adapter;
use Zend\ServiceManager\ServiceLocatorAwareInterface;
use Zend\ServiceManager\ServiceLocatorInterface;
use Zend\Db\Adapter\AdapterAwareInterface;
use Carbon\Carbon;

function convertMemorySize($size)
{
    $unit = [
        'b',
        'kb',
        'mb',
        'gb',
        'tb',
        'pb'
    ];
    return @round($size / pow(1024, ($i = floor(log($size, 1024)))), 2) . ' ' . $unit[$i];
};

class Synchronizer implements ServiceLocatorAwareInterface, AdapterAwareInterface
{
    /**
     * Whether to output logs
     * @var boolean
     */
    public $output_log = true;

    /**
     *
     * @var array
     */
    protected $configuration;

    /**
     *
     * @var Doctrine\Orm\EntityManager
     */
    protected $em;

    /**
     * mysqli connection
     *
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
    protected $intelaccessDb;

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

    protected $default_status_id = 20; // regular

    protected $default_product_type_id = 1;



    protected $legacy_synchro_at;

    protected $replace_dash_by_newline = false;

    protected $default_language;
    protected $default_language_sfx;

    /**
     *
     * @param \Doctrine\ORM\EntityManager $em
     * @param Adapter $zendDb
     */
    public function __construct(\Doctrine\ORM\EntityManager $em, Adapter $zendDb)
    {
        $this->em = $em;
        $this->openstoreDb = $em->getConnection()->getDatabase();
        $this->mysqli = $em->getConnection()
            ->getWrappedConnection()
            ->getWrappedResourceHandle();
        $this->setDbAdapter($zendDb);
        $this->legacy_synchro_at = date('Y-m-d H:i:s');
    }

    /**
     *
     * @param array $config
     * @return \Akilia\Synchronizer
     */
    public function setConfiguration(array $config)
    {
        $this->akilia2Db = $config['db_akilia2'];
        $this->akilia1Db = $config['db_akilia1'];
        $this->intelaccessDb = $config['db_intelaccess'];
        $this->akilia1lang = $config['akilia1_language_map'];
        $this->default_language = $config['default_language'];
        $this->default_language_sfx = $this->akilia1lang[$this->default_language];
        $this->configuration = $config;
        return $this;
    }

    public function synchronizeAkilia1ProductDescription()
    {
        /*
        SELECT p.product_id AS `product_id`,
               p.reference AS `reference`,
               p2.reference AS `parent_reference`,
               COALESCE(p.display_reference, p.reference) AS `display_reference`,
               pb.reference AS `brand_reference`, pb.title AS `brand_title`,
               pg.reference AS `group_reference`,
               pc.reference AS `category_reference`,
               COALESCE(pc18.title, pc.title) AS `category_title`,
               COALESCE(pc18.breadcrumb, pc.breadcrumb) AS `category_breadcrumb`,
               pst.reference AS `status_reference`,
               pst.flag_end_of_lifecycle AS `flag_end_of_lifecycle`,
               pst.flag_till_end_of_stock AS `flag_till_end_of_stock`,
               pm.media_id AS `picture_media_id`,
               DATE_FORMAT(p.created_at, '%Y-%m-%dT%TZ') AS `created_at`, DATE_FORMAT(p.available_at, '%Y-%m-%dT%TZ') AS `available_at`, COALESCE(MAX(if(p18.lang = 'en', p18.invoice_title, null)), '') AS `invoice_title_en`, COALESCE(MAX(if(p18.lang = 'en', p18.title, null)), '') AS `title_en`, COALESCE(MAX(if(p18.lang = 'en', p18.description, null)), '') AS `description_en`, COALESCE(MAX(if(p18.lang = 'en', p18.characteristic, null)), '') AS `characteristic_en`, DATE_FORMAT(MAX(if(p18.lang = 'en', p18.created_at, null)), '%Y-%m-%dT%TZ') AS `created_at_en`, DATE_FORMAT(MAX(if(p18.lang = 'en', p18.updated_at, null)), '%Y-%m-%dT%TZ') AS `updated_at_en`, MAX(if(p18.lang = 'en', p18.created_by, null)) AS `created_by_en`, MAX(if(p18.lang = 'en', p18.updated_by, null)) AS `updated_by_en`, MAX(if(p18.lang = 'en', p18.revision, null)) AS `revision_en`, COALESCE(MAX(if(p18.lang = 'fr', p18.invoice_title, null)), '') AS `invoice_title_fr`, COALESCE(MAX(if(p18.lang = 'fr', p18.title, null)), '') AS `title_fr`, COALESCE(MAX(if(p18.lang = 'fr', p18.description, null)), '') AS `description_fr`, COALESCE(MAX(if(p18.lang = 'fr', p18.characteristic, null)), '') AS `characteristic_fr`, DATE_FORMAT(MAX(if(p18.lang = 'fr', p18.created_at, null)), '%Y-%m-%dT%TZ') AS `created_at_fr`, DATE_FORMAT(MAX(if(p18.lang = 'fr', p18.updated_at, null)), '%Y-%m-%dT%TZ') AS `updated_at_fr`, MAX(if(p18.lang = 'fr', p18.created_by, null)) AS `created_by_fr`, MAX(if(p18.lang = 'fr', p18.updated_by, null)) AS `updated_by_fr`, MAX(if(p18.lang = 'fr', p18.revision, null)) AS `revision_fr`, COALESCE(MAX(if(p18.lang = 'zh', p18.invoice_title, null)), '') AS `invoice_title_zh`, COALESCE(MAX(if(p18.lang = 'zh', p18.title, null)), '') AS `title_zh`, COALESCE(MAX(if(p18.lang = 'zh', p18.description, null)), '') AS `description_zh`, COALESCE(MAX(if(p18.lang = 'zh', p18.characteristic, null)), '') AS `characteristic_zh`, DATE_FORMAT(MAX(if(p18.lang = 'zh', p18.created_at, null)), '%Y-%m-%dT%TZ') AS `created_at_zh`, DATE_FORMAT(MAX(if(p18.lang = 'zh', p18.updated_at, null)), '%Y-%m-%dT%TZ') AS `updated_at_zh`, MAX(if(p18.lang = 'zh', p18.created_by, null)) AS `created_by_zh`, MAX(if(p18.lang = 'zh', p18.updated_by, null)) AS `updated_by_zh`, MAX(if(p18.lang = 'zh', p18.revision, null)) AS `revision_zh`, COALESCE(MAX(if(p18.lang = 'de', p18.invoice_title, null)), '') AS `invoice_title_de`, COALESCE(MAX(if(p18.lang = 'de', p18.title, null)), '') AS `title_de`, COALESCE(MAX(if(p18.lang = 'de', p18.description, null)), '') AS `description_de`, COALESCE(MAX(if(p18.lang = 'de', p18.characteristic, null)), '') AS `characteristic_de`, DATE_FORMAT(MAX(if(p18.lang = 'de', p18.created_at, null)), '%Y-%m-%dT%TZ') AS `created_at_de`, DATE_FORMAT(MAX(if(p18.lang = 'de', p18.updated_at, null)), '%Y-%m-%dT%TZ') AS `updated_at_de`, MAX(if(p18.lang = 'de', p18.created_by, null)) AS `created_by_de`, MAX(if(p18.lang = 'de', p18.updated_by, null)) AS `updated_by_de`, MAX(if(p18.lang = 'de', p18.revision, null)) AS `revision_de`, COALESCE(MAX(if(p18.lang = 'it', p18.invoice_title, null)), '') AS `invoice_title_it`, COALESCE(MAX(if(p18.lang = 'it', p18.title, null)), '') AS `title_it`, COALESCE(MAX(if(p18.lang = 'it', p18.description, null)), '') AS `description_it`, COALESCE(MAX(if(p18.lang = 'it', p18.characteristic, null)), '') AS `characteristic_it`, DATE_FORMAT(MAX(if(p18.lang = 'it', p18.created_at, null)), '%Y-%m-%dT%TZ') AS `created_at_it`, DATE_FORMAT(MAX(if(p18.lang = 'it', p18.updated_at, null)), '%Y-%m-%dT%TZ') AS `updated_at_it`, MAX(if(p18.lang = 'it', p18.created_by, null)) AS `created_by_it`, MAX(if(p18.lang = 'it', p18.updated_by, null)) AS `updated_by_it`, MAX(if(p18.lang = 'it', p18.revision, null)) AS `revision_it`, COALESCE(MAX(if(p18.lang = 'nl', p18.invoice_title, null)), '') AS `invoice_title_nl`, COALESCE(MAX(if(p18.lang = 'nl', p18.title, null)), '') AS `title_nl`, COALESCE(MAX(if(p18.lang = 'nl', p18.description, null)), '') AS `description_nl`, COALESCE(MAX(if(p18.lang = 'nl', p18.characteristic, null)), '') AS `characteristic_nl`, DATE_FORMAT(MAX(if(p18.lang = 'nl', p18.created_at, null)), '%Y-%m-%dT%TZ') AS `created_at_nl`, DATE_FORMAT(MAX(if(p18.lang = 'nl', p18.updated_at, null)), '%Y-%m-%dT%TZ') AS `updated_at_nl`, MAX(if(p18.lang = 'nl', p18.created_by, null)) AS `created_by_nl`, MAX(if(p18.lang = 'nl', p18.updated_by, null)) AS `updated_by_nl`, MAX(if(p18.lang = 'nl', p18.revision, null)) AS `revision_nl`, COALESCE(MAX(if(p18.lang = 'es', p18.invoice_title, null)), '') AS `invoice_title_es`, COALESCE(MAX(if(p18.lang = 'es', p18.title, null)), '') AS `title_es`, COALESCE(MAX(if(p18.lang = 'es', p18.description, null)), '') AS `description_es`, COALESCE(MAX(if(p18.lang = 'es', p18.characteristic, null)), '') AS `characteristic_es`, DATE_FORMAT(MAX(if(p18.lang = 'es', p18.created_at, null)), '%Y-%m-%dT%TZ') AS `created_at_es`, DATE_FORMAT(MAX(if(p18.lang = 'es', p18.updated_at, null)), '%Y-%m-%dT%TZ') AS `updated_at_es`, MAX(if(p18.lang = 'es', p18.created_by, null)) AS `created_by_es`, MAX(if(p18.lang = 'es', p18.updated_by, null)) AS `updated_by_es`, MAX(if(p18.lang = 'es', p18.revision, null)) AS `revision_es`, MIN(COALESCE(p18.revision, 0)) AS `min_revision`, MAX(p18.updated_at) AS `max_updated_at`, MAX(COALESCE(p18.revision, 0)) AS `max_revision`, COUNT(distinct COALESCE(p18.revision, 9999999)) AS `nb_distinct_revision`, 'A' AS `relevance` FROM `product` AS `p` LEFT JOIN `product_translation` AS `p18` ON p18.product_id = p.product_id and p18.lang in ('en','fr','zh','de','it','nl','es') LEFT JOIN `product` AS `p2` ON p2.product_id = p.parent_id INNER JOIN `product_brand` AS `pb` ON pb.brand_id = p.brand_id LEFT JOIN `product_group` AS `pg` ON pg.group_id = p.group_id INNER JOIN `product_category` AS `pc` ON pc.category_id = p.category_id LEFT JOIN `product_category_translation` AS `pc18` ON pc.category_id = pc18.category_id and pc18.lang = 'es' LEFT JOIN `product_search` AS `psi` ON psi.product_id = p.product_id and psi.lang = 'es' LEFT JOIN `product_type` AS `pt` ON p.type_id = pt.type_id LEFT JOIN `product_status` AS `pst` ON pst.status_id = p.status_id LEFT JOIN `product_media` AS `pm` ON pm.product_id = p.product_id and pm.flag_primary=1 LEFT JOIN `product_media_type` AS `pmt` ON pmt.type_id = p.type_id and pmt.reference = 'PICTURE' INNER JOIN `product_pricelist` AS `ppl` ON ppl.product_id = p.product_id INNER JOIN `pricelist` AS `pl` ON pl.pricelist_id = ppl.pricelist_id WHERE p.flag_active = 1 AND `pl`.`reference` IN ('BE') AND ppl.flag_active = 1 AND `p`.`type_id` IN ('1') GROUP BY `product_id`, `reference`, `parent_reference`, `display_reference`, `brand_reference`, `brand_title`, `group_reference`, `category_reference`, `category_title`, `category_breadcrumb`, `status_reference`, `flag_end_of_lifecycle`, `flag_till_end_of_stock`, `picture_media_id`, `created_at`, `available_at` ORDER BY `relevance` DESC, `p`.`created_at`
        */
    }

    public function synchronizeAll()
    {
        $this->synchronizeProductStatTrend();

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
        $this->synchronizeProductPricelistStat();
        $this->synchronizeProductStock();
        $this->synchronizeProductPackaging();
        $this->synchronizeDiscountCondition();


        $this->rebuildCategoryBreadcrumbs();

        $this->synchronizeProductStubFromArtTete();

        $this->rebuildProductSearch();



        // This is emd
        $this->flagRankableCategories();
        $this->synchronizeProductStatTrend();


        $this->processGuessedDiametersAndFormat();


        /**
         * INSERT INTO `nuvolia`.`user_scope` (
         * `id` ,
         * `user_id` ,
         * `customer_id` ,
         * `flag_active` ,
         * `created_at` ,
         * `updated_at` ,
         * `created_by` ,
         * `updated_by` ,
         * `legacy_mapping` ,
         * `legacy_synchro_at`
         * )
         * VALUES (
         * NULL , '2', '3521', '1', NULL , NULL , NULL , NULL , NULL , NULL
         * );
         */
    }

    /**
     *
     * @return \Soluble\Normalist\Synthetic\TableManager
     */
    public function getTableManager()
    {
        return $this->getServiceLocator()->get('SolubleNormalist\TableManager');
    }


    /**
     * Guess diameter
     * @return array associative with product_id => diameter
     */
    public function processGuessedDiametersAndFormat()
    {
        $query = "select p.product_id, p.reference, p.invoice_title, p.title
                    from product p
                    where p.flag_active = 1
                    and title regexp '(([0-9\.]){1,5}\")'
                    ";

        $result = $this->mysqli->query($query);
        $affected_rows = $this->mysqli->affected_rows;
        $errors = [];
        $products = [];
        foreach ($result as $row) {
            $product_id = $row['product_id'];
            $products[$product_id] = [
                'diameter' => null,
                'format' => null
            ];
            $title = $row['title'];
            if (substr_count($title, '"')  == 1) {
                $match = preg_match_all('/(([1-3]?[0-9](\.[1-9])?)\ ?")/', $title, $matches);
                if ($match && $matches[2][0] > 0) {
                    $diameter = $matches[2][0];
                    // meters to inches = 1m * 39.3700787402
                    $products[$product_id]['diameter'] = $diameter * 0.0254;
                } else {
                    $errors[$product_id] = [
                        'reason' => 'Cannot grep diameter',
                        'title' => $title
                    ];
                }
            } elseif (substr_count($title, '"')  < 4) {
                $match = preg_match_all('/(((([1-3]?[0-9](\.[1-9])?)\ ?")\ ?)X(\ ?(([1-3]?[0-9](\.[1-9])?)\ ?")))/', strtoupper($title), $matches);
                if ($match) {
                    $format = str_replace(' ', '', strtolower($matches[1][0]));
                    $format = str_replace('x', ' x ', $format);
                    $products[$product_id]['format'] = $format;
                } else {
                    $errors[$product_id] = [
                        'reason' => 'Cannot grep format',
                        'title' => $title
                    ];
                }
            } else {
                $errors[$product_id] = [
                    'reason' => 'More than 3 inches found',
                    'title' => $title
                ];
            }
        }

        foreach ($products as $product_id => $infos) {
            $update = "update product set ";
            $values = [];
            foreach ($infos as $key => $value) {
                $values[] ="$key = " . (($value === null) ? 'null' : $this->adapter->platform->quoteValue($value));
            }


            $update .= implode(',', $values) . " where product_id = " . $product_id;

            $this->mysqli->query($update);
        }
    }

    public function synchronizeProductMedia()
    {
        ini_set('memory_limit', "1G");

        $sl = $this->getServiceLocator();
        $configuration = $sl->get('Configuration');
        if (! is_array($configuration['akilia'])) {
            throw new \Exception("Cannot find akilia configuration, please see you global config files");
        }
        $configuration = $configuration['akilia'];
        $products = new Akilia1Products($configuration);
        $products->setServiceLocator($this->getServiceLocator());
        $products->setDbAdapter($this->getServiceLocator()
            ->get('Zend\Db\Adapter\Adapter'));

        $list = $products->getProductPictures();

        $mediaManager = $this->getServiceLocator()->get('MMan/MediaManager');

        $tm = $this->getTableManager();
        $mcTable = $tm->table('media_container');
        $container = $mcTable->findOneBy([
            'reference' => 'PRODUCT_MEDIAS'
        ]);
        if (! $container) {
            throw new \Exception("Cannot find media container 'PRODUCT_MEDIAS'");
        }

        $pmtTable = $tm->table('product_media_type');
        $media_type_id = $pmtTable->findOneBy([
            'reference' => 'PICTURE'
        ])->type_id;

        if ($media_type_id == '') {
            throw new \Exception("Cannot find PICTURE product media type in your database");
        }

        $limit_to_import = 25000;
        $count = count($list);
        $productTable = $tm->table('product');
        $mediaTable = $tm->table('product_media');
        $product_ids = $productTable->search()
            ->columns([
            'product_id'
            ])
            ->toArrayColumn('product_id', 'product_id');
        for ($i = 0; ($i < $limit_to_import && $i < $count); $i ++) {
            $infos = $list[$i];
            // var_dump($infos);
            $importElement = new \MMan\Import\Element();

            $importElement->setFilename($infos['filename']);
            $importElement->setLegacyMapping($infos['md5']);

            $media_id = $mediaManager->import($importElement, $container['container_id']);

            if (array_key_exists($infos['product_id'], $product_ids)) {
                /*
                 * $product_id = $infos['product_id'];
                 * echo "- " . count($product_ids) . "\n";
                 * echo "- product_id:" . $product_ids[$product_id] . "\n";
                 * unset($product_ids[$product_id]);
                 * echo "- " . count($product_ids) . "\n";
                 * echo "- product_id:" . $product_ids[$product_id] . "\n";
                 * die();
                 */
                // unset($product_ids[$infos['product_id']]);
                $data = [
                    'media_id' => $media_id,
                    'product_id' => $infos['product_id'],
                    'flag_primary' => $infos['alternate_index'] == '' ? 1 : null,
                    'sort_index' => $infos['alternate_index'] == '' ? 0 : $infos['alternate_index'],
                    'type_id' => $media_type_id,
                    'updated_at' => date('Y-m-d H:i:s')
                ];
                try {
                    echo "[+] Importing product " . $infos['product_id'] . " as media_id $media_id [" . ($i + 1) . "/$count]\n";
                    $productMedia = $mediaTable->insertOnDuplicateKey($data, $duplicate_exclude = []);
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

    public function synchronizeApi()
    {
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

    public function synchronizeCountry()
    {
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

    public function synchronizeCustomer()
    {
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

    public function synchronizeCustomerPricelist()
    {
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


    public function synchronizeProductStock()
    {
        if (!isset($this->configuration['options']['product_stock'])) {
            throw new \Exception(__METHOD__ . " Error missing sync configuration key in akilia.local.php 'akilia/synchronizer/options/product_stock'");
        }
        if (!$this->configuration['options']['product_stock']['enabled']) {
            $this->log("Skipping product stock synchro [disabled by config]");
            return;
        }
        if (is_array($this->configuration['options']['product_stock']['elements'])) {
            $elements = $this->configuration['options']['product_stock']['elements'];
        } else {
            $elements = [
                'DEFAULT' => [
                    'akilia1db' => $this->akilia1Db,
                    'pricelist' => null
                ]
            ];
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




    public function synchronizeProductPricelist()
    {
        if (!isset($this->configuration['options']['product_pricelist'])) {
            throw new \Exception(__METHOD__ . " Error missing sync configuration key in akilia.local.php 'akilia/synchronizer/options/product_pricelist'");
        }
        if (!$this->configuration['options']['product_pricelist']['enabled']) {
            $this->log("Skipping product pricelist synchro [disabled by config]");
            return;
        }
        if (is_array($this->configuration['options']['product_pricelist']['elements'])) {
            $elements = $this->configuration['options']['product_pricelist']['elements'];
        } else {
            $elements = [
                'DEFAULT' => [
                    'akilia1db' => $this->akilia1Db,
                    'pricelists' => []
                ]
            ];
        }

        $db = $this->openstoreDb;

        foreach ($elements as $key => $element) {
            $akilia1Db = $element['akilia1db'];
            $pricelists_clause = "";
            if (count($element['pricelists']) > 0) {
                $pls = [];
                foreach ($element['pricelists'] as $pricelist) {
                    $pls[] = $this->adapter->getPlatform()->quoteValue($pricelist);
                }
                $pricelists_clause = "and t.id_pays in (" . implode(',', $pls) . ")";
            }

            $this->log("Product pricelist [$key] sync: taking prices " .  implode(',', $pls). " from database '$akilia1Db'");

            $replace = " 
                insert into $db.product_pricelist(
                    product_id,
                    pricelist_id,
                    status_id,
                    price,
                    list_price,
                    public_price,
                    map_price,
                    discount_1,
                    discount_2,
                    discount_3,
                    discount_4,
                    maximum_discount_1,                    
                    sale_minimum_qty,
                    is_promotional,
                    is_liquidation,
                    is_new,
                    promo_start_at,
                    promo_end_at,
                    flag_active,
                    available_at,
                    legacy_synchro_at
                )
                select 
                    p.product_id as product_id,
                    pl.pricelist_id as pricelist_id, 
                    ps.status_id as status_id,
                    ROUND( (t.prix_unit_ht * 
                                (1-(COALESCE(t.remise1, 0)/100)) * 
                                (1-(COALESCE(t.remise2, 0)/100)) * 
                                (1-(COALESCE(t.remise3, 0)/100)) * 
                                (1-(COALESCE(t.remise4, 0)/100))
                            ), 4) as price,
                    t.prix_unit_ht as list_price,
                    t.prix_unit_public as public_price,
                    t.map_price as map_price,

                    COALESCE(t.remise1, 0) as discount_1,
                    COALESCE(t.remise2, 0) as discount_2,
                    COALESCE(t.remise3, 0) as discount_3,
                    COALESCE(t.remise4, 0) as discount_4,
                    t.max_discount_1 as maximum_discount_1,

                    if(t.sale_min_qty > 0, t.sale_min_qty, null) as sale_minimum_qty,
                    if(t.flag_promo = 1 and (t.remise1 > 0 or t.remise2 > 0), 1, 0) as is_promotional,
                    if(t.flag_liquidation = 1 and (t.remise1 > 0 or t.remise2 > 0 ), 1, 0) as is_liquidation,
                    if(t.flag_new = 1 || t.flag_new_soon, 1, null) as is_new,
                    t.date_promo_start as promo_start_at,
                    t.date_promo_end as promo_end_at,
                    t.flag_availability as flag_active,
                    COALESCE(t.date_in_stock, p.created_at) as available_at,
                    '{$this->legacy_synchro_at}' as legacy_synchro_at
                from $akilia1Db.article a
                inner join 
                    $akilia1Db.art_tarif t on t.id_article = a.id_article 
                inner join 
                    $db.pricelist pl on pl.legacy_mapping = t.id_pays
                inner join
                    $db.product p on p.legacy_mapping = a.id_article    
                left outer join 
                    $db.product_status ps on ps.legacy_mapping = a.code_suivi
                where 
                    t.prix_unit_ht > 0
                    $pricelists_clause
                        
                on duplicate key update
                    product_id = p.product_id,
                    pricelist_id = pl.pricelist_id, 
                    status_id = ps.status_id,
                    price = ROUND( (t.prix_unit_ht * 
                                (1-(COALESCE(t.remise1, 0)/100)) * 
                                (1-(COALESCE(t.remise2, 0)/100)) * 
                                (1-(COALESCE(t.remise3, 0)/100)) * 
                                (1-(COALESCE(t.remise4, 0)/100))
                            ), 4),

                    list_price = t.prix_unit_ht ,
                    public_price = t.prix_unit_public,
                    map_price = t.map_price,

                    discount_1 = COALESCE(t.remise1, 0),
                    discount_2 = COALESCE(t.remise2, 0),
                    discount_3 = COALESCE(t.remise3, 0),
                    discount_4 = COALESCE(t.remise4, 0),
                    maximum_discount_1 = t.max_discount_1,
                    sale_minimum_qty = if(t.sale_min_qty > 0, t.sale_min_qty, null),
                    is_promotional = if(t.flag_promo = 1 and (t.remise1 > 0 or t.remise2 > 0), 1, 0),
                    is_liquidation = if(t.flag_liquidation = 1 and (t.remise1 > 0 or t.remise2 > 0), 1, 0),
                    is_new = if(t.flag_new = 1 || t.flag_new_soon, 1, null),
                    promo_start_at = t.date_promo_start,
                    promo_end_at = t.date_promo_end,
                    flag_active = t.flag_availability,
                    available_at = COALESCE(t.date_in_stock, p.created_at),
                    legacy_synchro_at = '{$this->legacy_synchro_at}'
            ";

            $this->executeSQL("Replace product pricelist [$key] ", $replace);
        }


        // 2. Deleting - old links in case it changes
        $delete = "
            delete from $db.product_pricelist
            where legacy_synchro_at < '{$this->legacy_synchro_at}' and legacy_synchro_at is not null";

        $this->executeSQL("Delete eventual removed product_pricelist", $delete);
    }

    public function synchronizeProductStatTrend()
    {
        if (!$this->configuration['options']['product_stat_trend']['enabled']) {
            $this->log("Skipping product_stat_trend synchro [disabled by config]");
            return;
        } else {
            $this->log("Creating product_stat_trend (may take a while)");
        }


        $akilia1db = $this->configuration['options']['product_stat_trend']['akilia1db'];

        $db = $this->openstoreDb;

        $date_column = 'c.date_commande';
        $trend_columns = [
            'nb_customers' => "COUNT(DISTINCT IF($date_column %period%, c.id_client, null))",
            'nb_sale_reps' => "COUNT(DISTINCT IF($date_column %period%, c.id_representant, null))",
            'nb_orders'    => "COUNT(DISTINCT IF($date_column %period%, c.id_commande, null))",
            'total_recorded_quantity' => "SUM(IF($date_column %period%, l.qty_commande, 0))",
            'total_recorded_turnover' => "SUM(IF($date_column %period%, l.total_ht, 0))"
        ];

        $now = Carbon::now(new \DateTimeZone('Europe/Brussels'));
        $today = $now->format('Y-m-d');

        $periods = [
            'last_month' => "BETWEEN '" . $now->copy()->subMonth(1)->format('Y-m-d') . "' AND '" . $today . "'",
            'last_2_months' => "BETWEEN '" . $now->copy()->subMonth(2)->format('Y-m-d') . "' AND '" . $today . "'",
            'last_3_months' => "BETWEEN '" . $now->copy()->subMonth(3)->format('Y-m-d') . "' AND '" . $today . "'",
            'last_4_months' => "BETWEEN '" . $now->copy()->subMonth(4)->format('Y-m-d') . "' AND '" . $today . "'",
            'last_5_months' => "BETWEEN '" . $now->copy()->subMonth(5)->format('Y-m-d') . "' AND '" . $today . "'",
            'last_6_months' => "BETWEEN '" . $now->copy()->subMonth(6)->format('Y-m-d') . "' AND '" . $today . "'",
            'last_7_months' => "BETWEEN '" . $now->copy()->subMonth(7)->format('Y-m-d') . "' AND '" . $today . "'",
            'last_8_months' => "BETWEEN '" . $now->copy()->subMonth(8)->format('Y-m-d') . "' AND '" . $today . "'",
            'last_9_months' => "BETWEEN '" . $now->copy()->subMonth(9)->format('Y-m-d') . "' AND '" . $today . "'",
            'last_10_months' => "BETWEEN '" . $now->copy()->subMonth(10)->format('Y-m-d') . "' AND '" . $today . "'",
            'last_11_months' => "BETWEEN '" . $now->copy()->subMonth(11)->format('Y-m-d') . "' AND '" . $today . "'",
            'last_12_months' => "BETWEEN '" . $now->copy()->subMonth(12)->format('Y-m-d') . "' AND '" . $today . "'",
        ];

        $periods = [
            'last_month' => "BETWEEN '" . $now->copy()->subMonth(1)->format('Y-m-d') . "' AND '" . $today . "'",
            'last_2_months' => "BETWEEN '" . $now->copy()->subMonth(2)->format('Y-m-d') . "' AND '" . $today . "'",
            'last_3_months' => "BETWEEN '" . $now->copy()->subMonth(3)->format('Y-m-d') . "' AND '" . $today . "'",
            'last_4_months' => "BETWEEN '" . $now->copy()->subMonth(4)->format('Y-m-d') . "' AND '" . $today . "'",
            'last_5_months' => "BETWEEN '" . $now->copy()->subMonth(5)->format('Y-m-d') . "' AND '" . $today . "'",
            'last_6_months' => "BETWEEN '" . $now->copy()->subMonth(6)->format('Y-m-d') . "' AND '" . $today . "'",
            'last_7_months' => "BETWEEN '" . $now->copy()->subMonth(7)->format('Y-m-d') . "' AND '" . $today . "'",
            'last_8_months' => "BETWEEN '" . $now->copy()->subMonth(8)->format('Y-m-d') . "' AND '" . $today . "'",
            'last_9_months' => "BETWEEN '" . $now->copy()->subMonth(9)->format('Y-m-d') . "' AND '" . $today . "'",
            'last_10_months' => "BETWEEN '" . $now->copy()->subMonth(10)->format('Y-m-d') . "' AND '" . $today . "'",
            'last_11_months' => "BETWEEN '" . $now->copy()->subMonth(11)->format('Y-m-d') . "' AND '" . $today . "'",
            'last_12_months' => "BETWEEN '" . $now->copy()->subMonth(12)->format('Y-m-d') . "' AND '" . $today . "'",
        ];


        $columns = [];
        foreach ($periods as $suffix => $period) {
            foreach ($trend_columns as $name => $cond) {
                $condition = str_replace('%period%', $period, $cond);
                $new_column = $name . '_' .  $suffix;
                $columns[$new_column] = $condition . ' AS ' . $new_column;
            }
        }

        $plstats_columns = implode(",\n", array_map(function ($col) { return "plstats.$col"; }, array_keys($columns)));
        $inner_columns = implode(",\n", array_values($columns));
        $stat_columns = implode(",\n", array_keys($columns));
        $update_columns = implode(",\n", array_map(function ($col) { return "$col = plstats.$col"; }, array_keys($columns)));


        $replace = "

                INSERT into $db.product_stat_trend(
                    product_id,
                    pricelist_id,
                    first_sale_recorded_at,
                    latest_sale_recorded_at,
                    nb_customers,
                    nb_sale_reps,
                    nb_orders,
                    total_recorded_quantity,
                    total_recorded_turnover,
                    $stat_columns,
                    legacy_synchro_at
                )
                (
                 SELECT 
                    p.product_id,
                    pl.pricelist_id,

                    plstats.first_sale_recorded_at,
                    plstats.latest_sale_recorded_at,

                    plstats.nb_customers,
                    plstats.nb_sale_reps,
                    plstats.nb_orders,
                    plstats.total_recorded_quantity,
                    plstats.total_recorded_turnover,

                    $plstats_columns,

                    '{$this->legacy_synchro_at}' AS legacy_synchro_at
                 FROM
                    (SELECT 
                        l.id_article,
                        c.code_tarif,
                        MIN(c.date_commande) AS first_sale_recorded_at,
                        MAX(c.date_commande) AS latest_sale_recorded_at,
                        COUNT(DISTINCT c.id_client) AS nb_customers,
                        COUNT(DISTINCT c.id_representant) AS nb_sale_reps,
                        COUNT(DISTINCT c.id_commande) AS nb_orders,
                        SUM(l.qty_commande) AS total_recorded_quantity,
                        SUM(l.total_ht) AS total_recorded_turnover,
                        $inner_columns

                    FROM
                        $akilia1db.commande c
                    INNER JOIN $akilia1db.ligne_commande l ON c.id_commande = l.id_commande
                    WHERE
                        1 = 1 
                    GROUP BY 1 , 2
                ) AS plstats
                
               INNER JOIN $db.product p ON p.legacy_mapping = plstats.id_article
               INNER JOIN $db.pricelist pl ON pl.legacy_mapping = plstats.code_tarif
               ORDER BY p.product_id, pl.pricelist_id
            )    
            ON DUPLICATE KEY UPDATE
                first_sale_recorded_at = plstats.first_sale_recorded_at,
                latest_sale_recorded_at = plstats.latest_sale_recorded_at,
                nb_customers = plstats.nb_customers,
                nb_sale_reps = plstats.nb_sale_reps,
                nb_orders = plstats.nb_orders,
                total_recorded_quantity = plstats.total_recorded_quantity,
                total_recorded_turnover = plstats.total_recorded_turnover,
                $update_columns,
                legacy_synchro_at = '{$this->legacy_synchro_at}'
        ";

        echo $replace;
        die();
        $this->executeSQL("Replace product stat trend ", $replace);

        $delete = "
            delete from $db.product_pricelist_stat
            where legacy_synchro_at < '{$this->legacy_synchro_at}' and legacy_synchro_at is not null";

        $this->executeSQL("Removing eventual product stat trends", $delete);
    }

    public function synchronizeProductPricelistStat()
    {
        if (!isset($this->configuration['options']['product_pricelist'])) {
            throw new \Exception(__METHOD__ . " Error missing sync configuration key in akilia.local.php 'akilia/synchronizer/options/product_pricelist'");
        }
        if (!$this->configuration['options']['product_pricelist']['enabled']) {
            $this->log("Skipping product pricelist synchro [disabled by config]");
            return;
        }
        if (is_array($this->configuration['options']['product_pricelist']['elements'])) {
            $elements = $this->configuration['options']['product_pricelist']['elements'];
        } else {
            $elements = [
                'DEFAULT' => [
                    'akilia1db' => $this->akilia1Db,
                    'pricelists' => []
                ]
            ];
        }

        $db = $this->openstoreDb;

        foreach ($elements as $key => $element) {
            $akilia1Db = $element['akilia1db'];

            $pricelists_clause = "";
            $code_tarif_clause = '';
            if (count($element['pricelists']) > 0) {
                $pls = [];
                foreach ($element['pricelists'] as $pricelist) {
                    $pls[] = $this->adapter->getPlatform()->quoteValue($pricelist);
                }
                $pricelists_clause = "and pl.legacy_mapping in (" . implode(',', $pls) . ")";
                $code_tarif_clause = "and c.code_tarif in (" . implode(',', $pls) . ")";
            }

            $replace = " 
                insert into $db.product_pricelist_stat(
                    product_pricelist_stat_id,
                    forecasted_monthly_sales,
                    legacy_synchro_at
                )
                SELECT 
                    ppl.product_pricelist_id,
                        t.moyenne_vente,
                    '{$this->legacy_synchro_at}' AS legacy_synchro_at
                FROM
                    $akilia1Db.article a
                        INNER JOIN
                    $akilia1Db.art_tarif t ON t.id_article = a.id_article
                        INNER JOIN
                    $db.product p ON p.legacy_mapping = a.id_article
                        INNER JOIN
                    $db.pricelist pl ON pl.legacy_mapping = t.id_pays
                        INNER JOIN
                    $db.product_pricelist ppl ON ppl.product_id = p.product_id
                        AND pl.pricelist_id = ppl.pricelist_id
                where 1=1 
                      $pricelists_clause
                on duplicate key update
                    forecasted_monthly_sales = t.moyenne_vente,
                    legacy_synchro_at = '{$this->legacy_synchro_at}'
            ";

            $this->executeSQL("Replace product pricelist stats for forecasted sales [$key] ", $replace);


            $replace = "
                insert into $db.product_pricelist_stat(
                    product_pricelist_stat_id,
                    first_sale_recorded_at,
                    latest_sale_recorded_at,
                    nb_customers,
                    nb_sale_reps,
                    nb_orders,
                    total_recorded_quantity,
                    total_recorded_turnover,
                    legacy_synchro_at
                )
                select 
                    ppl.product_pricelist_id, 
                    plstats.first_sale_recorded_at, 
                    plstats.latest_sale_recorded_at,
                    plstats.nb_customers,
                    plstats.nb_sale_reps,
                    plstats.nb_orders,
                    plstats.total_recorded_quantity,
                    plstats.total_recorded_turnover,
                    '{$this->legacy_synchro_at}' AS legacy_synchro_at

                from 
                    (SELECT 
                        l.id_article,
                        c.code_tarif,
                        min(c.date_commande) AS first_sale_recorded_at,
                        max(c.date_commande) AS latest_sale_recorded_at,
                        count(distinct c.id_client) as nb_customers,
                        count(distinct c.id_representant) as nb_sale_reps,
                        count(distinct c.id_commande) as nb_orders,
                        sum(l.qty_commande) as total_recorded_quantity,
                        sum(l.total_ht) as total_recorded_turnover
                    FROM
                        $akilia1Db.commande c
                        INNER JOIN $akilia1Db.ligne_commande l on c.id_commande = l.id_commande
                    WHERE 1=1
                          $code_tarif_clause
                    GROUP BY 1,2) 
                as plstats
                inner join $db.pricelist pl on pl.legacy_mapping = plstats.code_tarif
                inner join $db.product_pricelist ppl on ppl.product_id = plstats.id_article and pl.pricelist_id = ppl.pricelist_id
                on duplicate key update
                    first_sale_recorded_at = plstats.first_sale_recorded_at,
                    latest_sale_recorded_at = plstats.latest_sale_recorded_at,
                    nb_customers = plstats.nb_customers,
                    nb_sale_reps = plstats.nb_sale_reps,
                    nb_orders = plstats.nb_orders,
                    total_recorded_quantity = plstats.total_recorded_quantity,
                    total_recorded_turnover = plstats.total_recorded_turnover,
                    legacy_synchro_at = '{$this->legacy_synchro_at}'
            ";

            $this->executeSQL("Replace product pricelist stats for pricelist sales [$key] ", $replace);
        }


        // 2. Deleting - old forecasted monthly sales (only !!!)
        $update = "
            update $db.product_pricelist_stat
            set forecasted_monthly_sales = null
            where legacy_synchro_at < '{$this->legacy_synchro_at}' and legacy_synchro_at is not null";

        $this->executeSQL("Removing eventual product_pricelist_stat forecasts monthly sales", $update);
    }


    public function synchronizePricelist($use_akilia2 = true)
    {
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
                            title = if(pricelist.title is null, bp.reference, pricelist.title),
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

    public function synchronizeProductCategory()
    {
        $akilia1db = $this->akilia1Db;
        $db = $this->openstoreDb;
        $root_reference = 'ROOT';

        $default_lsfx = $this->default_language_sfx;

        $select = "
            select upper(c.id_categorie) as id_categorie, 
                substring( upper(c.id_categorie), 1, (length( c.id_categorie ) -2 )) AS parent_categorie, 
                c.sort_index, 
                c.global_sort_index,
                CONVERT(IF(c.libelle_1 is null OR c.libelle_1 = '', c.libelle$default_lsfx, c.libelle_1) USING utf8) as libelle_1,
                CONVERT(IF(c.libelle_2 is null OR c.libelle_2 = '', c.libelle$default_lsfx, c.libelle_2) USING utf8) as libelle_2,
                CONVERT(IF(c.libelle_3 is null OR c.libelle_3 = '', c.libelle$default_lsfx, c.libelle_3) USING utf8) as libelle_3,
                CONVERT(IF(c.libelle_4 is null OR c.libelle_4 = '', c.libelle$default_lsfx, c.libelle_4) USING utf8) as libelle_4,
                CONVERT(IF(c.libelle_5 is null OR c.libelle_5 = '', c.libelle$default_lsfx, c.libelle_5) USING utf8) as libelle_5,
                CONVERT(IF(c.libelle_6 is null OR c.libelle_6 = '', c.libelle$default_lsfx, c.libelle_6) USING utf8) as libelle_6,
                CONVERT(IF(c.libelle_7 is null OR c.libelle_7 = '', c.libelle$default_lsfx, c.libelle_7) USING utf8) as libelle_7, 
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
        $rows = $this->em->getConnection()
            ->query($select)
            ->fetchAll();
        $categs = [];

        $rootCategory = $this->em->getRepository('OpenstoreSchema\Core\Entity\ProductCategory')->findOneBy([
            'reference' => $root_reference
        ]);
        if ($rootCategory === null) {
            $rootCategory = new \OpenstoreSchema\Core\Entity\ProductCategory();
            $rootCategory->setReference($root_reference);
            $rootCategory->setTitle('ROOT');
            $this->em->persist($rootCategory);
            $this->em->flush();
        }

        foreach ($rows as $row) {
            if ($row['category_id'] === null) {
                $pc = new \OpenstoreSchema\Core\Entity\ProductCategory();
            } else {
                $pc = $this->em->find('OpenstoreSchema\Core\Entity\ProductCategory', $row['category_id']);
            }

            if ($row['parent_categorie'] != null) {
                $pc->setParent($categs[$row['parent_categorie']]);
            } else {
                $pc->setParent($rootCategory);
            }

            $pc->setTitle($row["libelle$default_lsfx"]);

            $pc->setReference($row['id_categorie']);
            $pc->setSortIndex($row['sort_index']);
            $pc->setGlobalSortIndex($row['global_sort_index']);
            $pc->setAltMappingReference($row['alt_mapping_id']);
            $pc->setLegacyMapping($row['id_categorie']);
            $pc->setLegacySynchroAt(new \DateTime($this->legacy_synchro_at));
            // $pc->setCreatedAt($row['date_synchro']);

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
                    IF(c.libelle$sfx = '' OR c.libelle$sfx is null, c.libelle$default_lsfx, c.libelle$sfx) as title,
                    '{$this->legacy_synchro_at}'    
                  from $akilia1db.categories c
                  inner join $db.product_category pc on pc.legacy_mapping = c.id_categorie     
                 on duplicate key update
                  title = IF(c.libelle$sfx = '' OR c.libelle$sfx is null, c.libelle$default_lsfx, c.libelle$sfx),
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

    /**
     * Utility method to set rankable product categories
     */
    public function flagRankableCategories()
    {
        $update = "
            
            update product_category 
            left outer join (
                    select distinct pc2.category_id, pc2.reference from
                    product_category pc
                    inner join product_category pc2 on pc2.reference = 
                            IF(SUBSTRING(pc.reference FROM 1 FOR 4) in ('PIAC'), SUBSTRING(pc.reference FROM 1 FOR 8),
                                    -- categories on level 8 (only pianos)
                                    IF(SUBSTRING(pc.reference FROM 1 FOR 4) IN (
                                            'ACCB',
                                            'DRCG',
                                            'ACCA',
                                            'ACBG',
                                            'GTSG',
                                            'DRRH',
                                            'ACST',
                                            'PIAC',
                                            'GTAC',
                                            'GTAT',
                                            'GTEL'), 
                                            -- categories on level 6
                                            SUBSTRING(pc.reference FROM 1 FOR 6),

                                            -- by default all categories on level 2
                                            SUBSTRING(pc.reference FROM 1 FOR 4))

                            )
                    inner join product p on p.category_id = pc.category_id
            ) as rankable_category on product_category.category_id = rankable_category.category_id			
            set product_category.flag_rankable = if (rankable_category.category_id is null, null, 1)

        ";
        $this->executeSQL("Create product category rank", $update);
    }

    public function synchronizeProductModel()
    {
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

    public function synchronizeProductBrand()
    {
        $akilia1Db = $this->akilia1Db;
        $db = $this->openstoreDb;

        $replace = "
                insert into $db.product_brand
                    (reference, title, url, 
                    flag_active,
                    legacy_mapping, legacy_synchro_at)
                select TRIM(m.id_marque), 
                if(m.libelle = '', m.id_marque, m.libelle), 
                m.url, 
                if (m.flag_public_hidden = 1, 0, 1),
                m.id_marque, '{$this->legacy_synchro_at}'
            from $akilia1Db.marque m
            on duplicate key update
                reference = trim(m.id_marque),
                title = if(m.libelle = '', m.id_marque, m.libelle), 
                url = m.url,
                flag_active = if (m.flag_public_hidden = 1, 0, 1),
                legacy_synchro_at = '{$this->legacy_synchro_at}'";

        $this->executeSQL("Replace product brands", $replace);

        // 2. Deleting - old links in case it changes
        $delete = "
            delete from $db.product_brand where
            legacy_synchro_at <> '{$this->legacy_synchro_at}' and legacy_synchro_at is not null";

        $this->executeSQL("Delete eventual removed brands", $delete);
    }

    public function synchronizeProductGroup()
    {
        $akilia1Db = $this->akilia1Db;
        $db = $this->openstoreDb;

        $default_lsfx = $this->default_language_sfx;

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
                       f.libelle$default_lsfx as title, 
                       $group_ref_clause as legacy_mapping, 
                       '{$this->legacy_synchro_at}'
            from $akilia1Db.famille f
            on duplicate key update
                reference = $group_ref_clause,
                title = f.libelle$default_lsfx, 
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

    public function synchronizeProductPackaging()
    {
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

    public function synchronizeProduct()
    {
        $akilia1db = $this->akilia1Db;
        $akilia2db = $this->akilia2Db;
        $db = $this->openstoreDb;

        $default_lang = $this->default_language;
        $default_lsfx = $this->default_language_sfx;

        $rep = "REPLACE(REPLACE(i.desc$default_lsfx, '–', '-'), '\\n ', '\\n')";
        //$rep = "REPLACE($rep, '\\n\\n', '\\n')";
        //$rep = "REPLACE($rep, '  ', ' ')";
        //$rep = "REPLACE($rep, '\\n ', '\\n')";
        if ($this->replace_dash_by_newline) {
            $rep = "REPLACE($rep, ' - ', '\\n- ')";
        }




        $description = "if(trim(COALESCE(i.desc$default_lsfx, '')) = '', null, $rep)";

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
                    created_at,
                    updated_at,
                    trade_code_intrastat,
                    trade_code_hts,
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
                                        
                    if(ps.status_id is null, {$this->default_status_id}, ps.status_id) as status_id,    
                    if (i.id_art_tete <> 0 and i.id_art_tete <> '' and i.id_art_tete is not null, i.id_art_tete, null) as parent_id,     
                    upper(TRIM(a.reference)) as reference,
                                        upper(TRIM(a.reference)) as display_reference,
                                        get_searchable_reference(a.reference) as search_reference,                                        
                    null as slug,
                    if(trim(i.libelle$default_lsfx) = '', null, trim(i.libelle$default_lsfx)) as title,
                    if(trim(a.libelle$default_lsfx) = '', null, trim(a.libelle$default_lsfx)) as invoice_title,
                    $description as description,
                    if(trim(i.couleur$default_lsfx) = '', null, trim(i.couleur$default_lsfx)) as characteristic,
                    
                    if(a.flag_archive = 1, 0, 1) as flag_active,
                    null as icon_class,
                    a.volume as volume,
                    a.poids as weight,
                    -- dimensions are not yet supported
                    null as length,
                    null as height,
                    null as width,
                    
                    
                    COALESCE(if(a.qty_emballage=0, null, a.qty_emballage), a.qty_carton) as pack_qty_box,
                    COALESCE(if(a.qty_carton=0, null, a.qty_carton), qty_master_carton) as pack_qty_carton,
                    if(a.qty_master_carton=0, null, a.qty_master_carton) as pack_qty_master_carton,
                    
                    a.barcode_ean13 as barcode_ean13,
                    a.barcode_upca as barcode_upca,
                    if (a.code_tri_marque_famille REGEXP '^[0-9]+$', a.code_tri_marque_famille, 
                              CAST(CONV(hex(a.code_tri_marque_famille), 16, 10) as unsigned)
                            ) as sort_index ,
                    a.date_creation as updated_at,
                    a.date_creation as created_at,
                    null as updated_at,
                    a.intrastat_trade_code,
                    a.hts_trade_code,
                    a.id_article as legacy_mapping,
                    '{$this->legacy_synchro_at}' as legacy_synchro_at
                        
                    
                from $akilia1db.article as a
                left outer join $db.product p on p.legacy_mapping = a.id_article
                left outer join $akilia1db.cst_art_infos i on i.id_article = a.id_article    
                left outer join $db.product_brand as brand on brand.legacy_mapping = a.id_marque
                left outer join $db.product_group as product_group on product_group.legacy_mapping = a.id_famille
                left outer join $db.product_category as category on category.legacy_mapping = a.id_categorie
                left outer join $db.product_model as pm on pm.legacy_mapping = a.id_modele
                left outer join $db.product_status ps on ps.legacy_mapping = a.code_suivi
                left outer join $db.product_type pt on pt.legacy_mapping = a.product_type COLLATE 'utf8_general_ci'
                left outer join $db.product_translation p18 on p18.product_id = p.product_id and p18.lang = '$default_lang'
                
                
                where a.flag_archive = 0
                order by i.id_art_tete desc, a.id_article
                
                on duplicate key update
                        model_id = pm.model_id,
                        brand_id = brand.brand_id,
                        type_id = {$this->default_product_type_id},
                        group_id = product_group.group_id,
                        unit_id = {$this->default_unit_id},
                        parent_id = if (i.id_art_tete <> 0 and i.id_art_tete <> '' and i.id_art_tete is not null, i.id_art_tete, null),     
                        status_id = if(ps.status_id is null, {$this->default_status_id}, ps.status_id),        
                        category_id = category.category_id,
                        reference = upper(TRIM(a.reference)),
                        display_reference = upper(TRIM(a.reference)),
                        search_reference = get_searchable_reference(a.reference),                                                                                        
                        slug = null,
                        sort_index = if (a.code_tri_marque_famille REGEXP '^[0-9]+$', a.code_tri_marque_famille, 
                              CAST(CONV(hex(a.code_tri_marque_famille), 16, 10) as unsigned)
                            ) ,
                        type_id = COALESCE(pt.type_id, {$this->default_product_type_id}),
                        title = if(trim(i.libelle$default_lsfx) = '', p18.title, trim(i.libelle$default_lsfx)),
                        invoice_title = if(trim(a.libelle$default_lsfx) = '', null, trim(a.libelle$default_lsfx)),
                        description = $description,
                        characteristic = if(trim(i.couleur$default_lsfx) = '', null, trim(i.couleur$default_lsfx)),
                        flag_active = if(a.flag_archive = 1, 0, 1),
                        icon_class = null,
                        volume = a.volume,
                        weight = a.poids,
                        -- Dimensions are not yet supported
                        length = null,
                        height = null,
                        width = null,
                        
                        -- Qty carton has been deprecated, use pack_qty_carton
                        pack_qty_box = COALESCE(if(a.qty_emballage=0, null, a.qty_emballage), a.qty_carton),
                        pack_qty_carton = COALESCE(if(a.qty_carton=0, null, a.qty_carton), qty_master_carton),
                        pack_qty_master_carton = if(a.qty_master_carton=0, null, a.qty_master_carton),
                        
                        barcode_ean13 = a.barcode_ean13,
                        barcode_upca = a.barcode_upca,
                        available_at = a.date_creation,
                        created_at = a.date_creation,
                        updated_at = null,                        
                        trade_code_intrastat = a.intrastat_trade_code,
                        trade_code_hts = a.hts_trade_code,
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
    protected function hackUtf8TranslationColumn($column_name)
    {
        return "CONVERT(CONVERT(CONVERT($column_name USING latin1) using binary) USING utf8)";
    }

    /**
     * Really specific to akilia1/openstore bridge
     */
    public function synchronizeParentAssociation()
    {
        $akilia1db = $this->akilia1Db;
        $db = $this->openstoreDb;

        $langs = $this->akilia1lang;

        $update = " 
            update $db.product
                   inner join 
                        $akilia1db.cst_art_infos i on product.product_id = i.id_article
                   left outer join
                        $akilia1db.cst_art_infos i2 on i2.id_article = i.id_art_tete
            set parent_id = if (i2.id_article <> 0 and i2.id_article <> '' and i2.id_article is not null, i2.id_article, null)
            where 1=1
        ";
        //dump($update);
        $this->executeSQL("Update parent association...", $update);
    }

    /**
     * Create product stubs from cst_art_infos.id_art_tete link
     */
    protected function createProductStubFromArtTete()
    {
        $akilia1db = $this->akilia1Db;
        $db = $this->openstoreDb;

        $replace = "
                INSERT INTO $db.product_stub (
                        reference,
                        created_by,
                        legacy_mapping,
                        legacy_synchro_at
                ) 
                (SELECT * FROM
                        (
                                SELECT 
                                        CONCAT(CONVERT(get_searchable_reference(a_parent.reference) using 'utf8'), '_', REPLACE(a_parent.id_marque, ' ', ''), '_', a_parent.id_article) as stub_reference,
                                        'akilia-sync' as created_by,
                                        CONCAT(a_parent.reference,  ':', a_parent.id_marque, ':', a_parent.id_article, '_stub') as stub_legacy_mapping, 
                                        '{$this->legacy_synchro_at}' as legacy_synchro_at
                                FROM $akilia1db.cst_art_infos cai_parent
                                INNER JOIN $akilia1db.article a_parent on a_parent.id_article = cai_parent.id_article
                                LEFT OUTER JOIN $akilia1db.cst_art_infos cai_child on cai_child.id_art_tete = cai_parent.id_article
                                LEFT OUTER JOIN $akilia1db.article a_child on a_child.id_article = cai_child.id_article
                        WHERE (a_parent.flag_archive = 0 and a_child.flag_archive=0)
                                GROUP BY stub_reference, stub_legacy_mapping
                                HAVING count(a_child.reference) > 0
                    ) as inner_tbl
                )
                ON DUPLICATE KEY UPDATE
                    reference = inner_tbl.stub_reference,
                    updated_by = 'akilia-sync',
                    legacy_mapping = inner_tbl.stub_legacy_mapping,
                    legacy_synchro_at = '{$this->legacy_synchro_at}'
           ";

        $this->executeSQL("Create product stubs ", $replace);


        // 2. Deleting - old links in case it changes
        $delete = "
            delete from $db.product_stub 
            where legacy_synchro_at <> '{$this->legacy_synchro_at}' and legacy_synchro_at is not null";

        $this->executeSQL("Delete eventual removed product stubs", $delete);
    }

    protected function createProductStubLinksFromAkilia()
    {
        $akilia1db = $this->akilia1Db;
        $db = $this->openstoreDb;

        $update = "
                UPDATE product  
                inner join
                (
                        SELECT p.product_id, p.reference, stub.product_stub_id, stub.reference as stub_reference 
                        FROM (
                                (
                                                -- all children
                                                SELECT 
                                                        p.product_id as product_id,
                                                        CONCAT(a_parent.reference,  ':', a_parent.id_marque, ':', a_parent.id_article, '_stub') as stub_legacy_mapping, 
                                                        COUNT(a_parent.reference) as nb_members
                                                FROM emd00.cst_art_infos cai_parent
                                                INNER JOIN emd00.article a_parent on a_parent.id_article = cai_parent.id_article
                                                LEFT OUTER JOIN emd00.cst_art_infos cai_child on cai_child.id_art_tete = cai_parent.id_article
                                                LEFT OUTER JOIN emd00.article a_child on (a_child.id_article = cai_child.id_article)
                                                INNER JOIN $db.product p on p.legacy_mapping = a_child.id_article

                                                WHERE (a_parent.flag_archive = 0 and a_child.flag_archive=0 )
                                                GROUP BY product_id, stub_legacy_mapping

                                )  union distinct (

                                                -- all products being parent or out of family
                                                SELECT 
                                                        p.product_id as product_id,
                                                        CONCAT(a_parent.reference,  ':', a_parent.id_marque, ':', a_parent.id_article, '_stub') as stub_legacy_mapping, 
                                                        COUNT(a_child.reference) as nb_members
                                                FROM emd00.cst_art_infos cai_parent
                                                INNER JOIN emd00.article a_parent on a_parent.id_article = cai_parent.id_article
                                                INNER JOIN $db.product p on p.legacy_mapping = a_parent.id_article
                                                LEFT OUTER JOIN emd00.cst_art_infos cai_child on cai_child.id_art_tete = cai_parent.id_article
                                                LEFT OUTER JOIN emd00.article a_child on (a_child.id_article = cai_child.id_article)
                                                WHERE (a_parent.flag_archive = 0 and a_child.flag_archive=0 or a_child.reference is null)
                                                GROUP BY product_id, stub_legacy_mapping

                                ) 
                        ) as linked_stubs
                        inner join $db.product p on p.product_id = linked_stubs.product_id
                        left outer join $db.product_stub stub on stub.legacy_mapping = linked_stubs.stub_legacy_mapping
                        -- where linked_stubs.nb_members > 1
                ) as tbl on tbl.product_id = product.product_id
                set product.product_stub_id = tbl.product_stub_id        
            ";
        $this->executeSQL("Create product stubs ", $update);
    }

    /**
     * Create product stubs from cst_art_infos.id_art_tete link
     */
    protected function createProductStubTranslationsFromArtTete()
    {
        $akilia1db = $this->akilia1Db;
        $db = $this->openstoreDb;

        $replace = "
            insert into $db.product_stub_translation 
            (
                product_stub_id,
                lang,
                description_header,
                created_by,
                updated_by,
                created_at,
                updated_at,
                legacy_mapping,
                legacy_synchro_at
            )
            select 
                ps.product_stub_id,
                p18.lang,
                p18.description,
                p18.created_by,
                p18.updated_by,
                p18.created_at,
                p18.updated_at,
                CONCAT(ps.product_stub_id, ':', p18.lang) as legacy_mapping,
                '{$this->legacy_synchro_at}' as legacy_synchro_at
            from $db.product p
            inner join $db.product_translation p18 on p18.product_id = p.product_id
            inner join $db.product_stub ps on ps.product_stub_id = p.product_stub_id
            where p.parent_id is null and p18.description is not null
            on duplicate key update
                    product_stub_id = ps.product_stub_id,
                    lang = p18.lang,
                    description_header = p18.description,
                    updated_by = p18.updated_by,
                    updated_at = p18.updated_at,
                    legacy_mapping = CONCAT(ps.product_stub_id, ':', p18.lang),
                    legacy_synchro_at = '{$this->legacy_synchro_at}' 
           ";

        $this->executeSQL("Create product stubs translation ", $replace);


        // 2. Deleting - old links in case it changes
        $delete = "
            delete from $db.product_stub_translation
            where legacy_synchro_at <> '{$this->legacy_synchro_at}' and legacy_synchro_at is not null";

        $this->executeSQL("Delete eventual removed product stubs translations", $delete);
    }


    /**
     * Tranform parent association into a product stub
     */
    public function synchronizeProductStubFromArtTete()
    {
        if (true) {
            $this->createProductStubFromArtTete();
            $this->createProductStubLinksFromAkilia();
            $this->createProductStubTranslationsFromArtTete();
        }
    }

    /**
     *
     * @param array $product_ids restrict sync to the following products id's
     */
    public function synchronizeProductTranslation(array $product_ids = null)
    {
        if (!$this->configuration['options']['product_translation']['enabled']) {
            $this->log("Skipping product translation synchro [disabled by config]");
            return;
        }


        $akilia1db = $this->akilia1Db;
        $db = $this->openstoreDb;
        $intelaccessDb = $this->intelaccessDb;

        $langs = $this->akilia1lang;


        if ($product_ids !== null) {
            $product_clause = " and ("
                    . "i.id_article in (" . implode(',', $product_ids) . ') or '
                    . "i.id_art_tete in (" . implode(',', $product_ids) . ') or '
                    . "i2.id_article in (" . implode(',', $product_ids) . ') or '
                    . "i2.id_art_tete in (". implode(',', $product_ids) . ') )';
        } else {
            $product_clause = "";
        }

        foreach ($langs as $lang => $sfx) {
            $lang = strtolower($lang);

            if ($lang == 'zh') {
                // Handle a double encoding bug in chinese only
                $title = "if (trim(i.libelle$sfx) = '', null, trim(" . $this->hackUtf8TranslationColumn("i.libelle$sfx") . "))";
                $invoice_title = "if (trim(a.libelle$sfx) = '', null, trim(" . $this->hackUtf8TranslationColumn("a.libelle$sfx") . "))";
                $description = "if (i2.id_article is not null, 
                                    null,            
                                    if (trim(i.desc$sfx) = '', null, trim(" . $this->hackUtf8TranslationColumn("i.desc$sfx") . "))
                                )
                ";
                $characteristic = "if (trim(i.couleur$sfx) = '', null, trim(" . $this->hackUtf8TranslationColumn("i.couleur$sfx") . "))";
            } else {
                $title = "if (trim(i.libelle$sfx) = '', null, trim(i.libelle$sfx))";
                $invoice_title = "if (trim(a.libelle$sfx) = '', null, trim(a.libelle$sfx))";
                $description = "if (i2.id_article is not null, 
                                    null,    
                                    if (trim(i.desc$sfx) = '', null, trim(i.desc$sfx))
                                )
                ";
                $characteristic = "if (trim(i.couleur$sfx) = '', null, trim(i.couleur$sfx))";
            }

            $rep = "REPLACE($description, '–', '-')";
            //$rep = "REPLACE($rep, '\\t', ' ')";
            //$rep = "REPLACE($rep, '\\r', '')";
            //$rep = "REPLACE($rep, '\\n\\n', '\\n')";
            //$rep = "REPLACE($rep, '  ', ' ')";
            //$rep = "REPLACE($rep, '\\n ', '\\n')";
            if ($this->replace_dash_by_newline) {
                $rep = "REPLACE($rep, ' - ', '\\n- ')";
            }

            $description = $rep;

            $replace = "insert into product_translation 
                 ( product_id,
                   lang,
                   title,
                   invoice_title,
                   description,
                   characteristic,
                   revision,
                   created_at,
                   updated_at,
                   created_by,
                   updated_by,                
                   legacy_synchro_at
                  )
                  select
                    p.product_id as product_id, 
                    '$lang' as lang,
                    $title as title,
                    REPLACE($invoice_title, '–', '-') as invoice_title,    
                    $description as description,
                    $characteristic as characteristic,  
                    if (
                        (
                            CHAR_LENGTH(coalesce(trim(i.libelle$sfx), '')) +
                            CHAR_LENGTH(coalesce(trim(i.desc$sfx), '')) + 
                            CHAR_LENGTH(coalesce(trim(i.couleur$sfx), ''))
                        ) > 0,
                            1,
                            0
                        )
                      as revision,    
                    i.date_maj$sfx,
                    i.date_maj$sfx,
                    u.login,
                    u.login,
                    '{$this->legacy_synchro_at}'    
                  from $akilia1db.article a
                  inner join $db.product p on p.legacy_mapping = a.id_article     
                  left outer join $akilia1db.cst_art_infos i on i.id_article = a.id_article
                  left outer join $akilia1db.cst_art_infos i2 on 
                      (i.id_art_tete = i2.id_article and i.id_art_tete <> 0 and i.id_art_tete <> '')
                  left outer join $intelaccessDb.users u
                        on u.id_user = i.id_user$sfx

                 where a.flag_archive = 0
                 $product_clause
                     
                 on duplicate key update
                  lang = '$lang',
                  title = $title,
                  invoice_title = REPLACE($invoice_title, '–', '-'),
                  description = $description,
                  characteristic = REPLACE($characteristic, '–', '-'),
                  revision = 
                    if (revision is null, 
                        if(
                        (
                            CHAR_LENGTH(coalesce(trim(i.libelle$sfx), '')) +
                            CHAR_LENGTH(coalesce(trim(i.desc$sfx), '')) + 
                            CHAR_LENGTH(coalesce(trim(i.couleur$sfx), ''))
                        ) > 0,
                            1,
                            0
                        )
                    , revision)
                        
                        ,    
                  created_at = if(product_translation.created_at is null, if(i.date_maj$sfx = 0, null, i.date_maj$sfx), product_translation.created_at),
                  updated_at = if (i.date_maj$sfx = 0, null, i.date_maj$sfx),
                  created_by = if(product_translation.created_by is null, u.login, product_translation.created_by),
                  updated_by = u.login,
                  legacy_synchro_at = '{$this->legacy_synchro_at}'      
            ";

            $this->executeSQL("Replace product translations for lang: $lang", $replace);
        }
        //die();
        // 2. Deleting - old links in case it changes only when product id is no specified

        if ($product_ids === null) {
            $delete = "
                delete from $db.product_translation 
                where legacy_synchro_at <> '{$this->legacy_synchro_at}' and legacy_synchro_at is not null";

            $this->executeSQL("Delete eventual removed product translations", $delete);
        } else {
            $this->log("Skipping deleteing eventual removed product translations because product ids specified");
        }
    }

    /**
     * Synchronize discount conditions
     */
    public function synchronizeDiscountCondition()
    {
        $akilia1Db = $this->akilia1Db;
        $db = $this->openstoreDb;

        $replace = "
            insert into $db.discount_condition(
                pricelist_id,
                customer_id,
                customer_group_id,
                brand_id,
                product_group_id,
                category_id,
                model_id,
                product_id,
                fixed_price,
                discount_1,
                discount_2,
                discount_3,
                discount_4,
                valid_from,
                valid_till,
                legacy_mapping,
                legacy_synchro_at
            )
            select DISTINCT
             pl.pricelist_id,
             c.customer_id,
             cg.group_id as customer_group_id,
             pb.brand_id,
             pg.group_id as product_group_id,
             null as category_id,
             null as model_id,
             p.product_id,
             null as fixed_price,
             COALESCE(r.remise1, 0) as discount_1,
             COALESCE(r.remise2, 0) as discount_2,
             COALESCE(r.remise3, 0) as discount_3,
             COALESCE(r.remise4, 0) as discount_4,
             null as valid_from,
             null as valid_till,
             CONCAT_WS('&',
                    CONCAT('pl:',   COALESCE(pl.pricelist_id, '*')), -- no pricelist
                CONCAT('c:',    COALESCE(c.customer_id  , '*')),  -- no customer
                CONCAT('cg:',   COALESCE(cg.group_id    , '*')), -- no customer_group
                CONCAT('pb:',   COALESCE(pb.brand_id    , '*')), -- no product_brand
                CONCAT('pg:',   COALESCE(pg.group_id    , '*')), -- no product_group
                CONCAT('pc:',   COALESCE(null           , '*')), -- no product_category
                CONCAT('pm:',   COALESCE(null           , '*')), -- no product_model
                CONCAT('p:',    COALESCE(p.product_id   , '*')),  -- no product
                CONCAT('from:', COALESCE(null           , '*')),  -- valid_from
                CONCAT('till:', COALESCE(null           , '*'))   -- valid_till
             ) as legacy_mapping,
             '{$this->legacy_synchro_at}' as legacy_synchro_at

            from $akilia1Db.remises r
            inner join $db.customer c on c.legacy_mapping = r.id_client
            left outer join $db.product_brand pb on pb.legacy_mapping = r.id_marque
            left outer join $db.product_group pg on pg.legacy_mapping = r.id_famille
            left outer join $db.product p on p.legacy_mapping = r.id_article
            left outer join $db.pricelist pl on pl.legacy_mapping = r.code_tarif
            left outer join $db.customer_group cg on cg.legacy_mapping = r.id_groupe_client
            where (pl.flag_enable_discount_condition = 1 or pl.flag_enable_discount_condition is null)
            order by cg.group_id, c.customer_id, pl.pricelist_id, pb.brand_id, pg.group_id, p.product_id
            on duplicate key update
                discount_1 = COALESCE(r.remise1, 0),
                discount_2 = COALESCE(r.remise2, 0),
                discount_3 = COALESCE(r.remise3, 0),
                discount_4 = COALESCE(r.remise4, 0),
                fixed_price = null, -- not supported yet
                legacy_synchro_at = '{$this->legacy_synchro_at}'
        ";

        $this->executeSQL("Replace discount conditions", $replace);

        // 2. Deleting - old links in case it changes
        $delete = "
            delete from $db.discount_condition where
            legacy_synchro_at <> '{$this->legacy_synchro_at}' and legacy_synchro_at is not null";

        $this->executeSQL("Delete eventual removed discount conditions", $delete);
    }

    public function rebuildProductSearch()
    {
        $query = "CALL rebuild_catalog_search()";
        $this->executeSQL('Rebuild catalog search', $query);
    }

    public function rebuildCategoryBreadcrumbs()
    {
        $query = "CALL rebuild_category_breadcrumbs()";
        $this->executeSQL('Rebuild category breadcrumbs', $query);
    }

    /**
     * Execute a query on the database and logs it
     *
     * @throws Exception
     *
     * @param string $key
     *            name of the query
     * @param string $query
     * @param boolean $disable_foreign_key_checks
     * @return void
     */
    protected function executeSQL($key, $query, $disable_foreign_key_checks = true)
    {
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

        if (! $result) {
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
     *
     * @param string $message
     * @param int $priority
     * @return void
     */
    protected function log($message, $priority = null)
    {
        if ($this->output_log) {
            echo "$message\n";
        }
    }

    /**
     *
     * @param \Zend\Db\Adapter\Adapter $adapter
     * @return Synchronizer
     */
    public function setDbAdapter(Adapter $adapter)
    {
        $this->adapter = $adapter;
        return $this;
    }

    /**
     *
     * @return Zend\Db\Adapter\Adapter
     */
    public function getDbAdapter()
    {
        return $this->adapter;
    }

    /**
     *
     * @param \Zend\ServiceManager\ServiceLocatorInterface $serviceLocator
     */
    public function setServiceLocator(ServiceLocatorInterface $serviceLocator)
    {
        $this->serviceLocator = $serviceLocator;
        return $this;
    }

    /**
     *
     * @return \Zend\ServiceManager\ServiceLocatorInterface
     */
    public function getServiceLocator()
    {
        return $this->serviceLocator;
    }

    /**
     *
     * @return \MMan\Service\Manager
     */
    public function getMManManager()
    {
        return $this->getServiceLocator()->get('MMan\Manager');
    }
}
