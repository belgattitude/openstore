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


class Synchronizer implements ServiceLocatorAwareInterface, AdapterAwareInterface 
{
	
	/**
	 * @var Doctrine\Orm\EntityManager
	 */
	protected $em;
	
	/**
	 * mysqli connection
	 * @param \Doctrine\ORM\EntityManager $em
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
	 * @var \Zend\Db\Adapter\Adapter
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
	 * @param \Zend\Db\Adapter\Adapter $zendDb
	 */
	function __construct(\Doctrine\ORM\EntityManager $em, Adapter $zendDb)
	{
		$this->em = $em;

		$this->openstoreDb = $em->getConnection()->getDatabase();
		$this->mysqli = $em->getConnection()->getWrappedConnection()->getWrappedResourceHandle();
		$this->setDbAdapter($zendDb);
		$this->legacy_synchro_at = date('Y-m-d H:i:s');
		
	}
	
	function setConfiguration(array $config) {
		$this->akilia2Db	= $config['db_akilia2'];
		$this->akilia1Db	= $config['db_akilia1'];
		$this->akilia1lang	= $config['akilia1_language_map'];
	}
	
	
	function synchronizeAll()
	{
		
		/*
		$this->synchronizeCountry();
		$this->synchronizeCustomer();
		$this->synchronizePricelist();
		$this->synchronizeCustomerPricelist();
		$this->synchronizeProductGroup();
		$this->synchronizeProductBrand();
		$this->synchronizeProductCategory();
		$this->synchronizeProduct();
		$this->synchronizeProductTranslation();
		$this->synchronizeProductPricelist();
		$this->synchronizeProductStock();
		*/
		$this->synchronizeProductMedia();
		
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
	
	
	function synchronizeProductMedia()
	{
		$sl = $this->getServiceLocator();
		$configuration = $sl->get('Configuration');
		if (!is_array($configuration['akilia'])) {
			throw new \Exception("Cannot find akilia configuration, please see you global config files");
		}
		$configuration =  $configuration['akilia'];		
		$products = new Akilia1Products($configuration);
		$products->setServiceLocator($this->getServiceLocator());
		$products->setDbAdapter($this->getServiceLocator()->get('Zend\Db\Adapter\Adapter'));
		$list = $products->getProductPictures();
		
		$mediaManager = $this->getServiceLocator()->get('MMan/MediaManager');
		
		$count = count($list);
		for ($i = 0; ($i < 10 && $i < $count); $i++) {
			$infos = $list[$i];
			//var_dump($infos);
			$importElement = new \MMan\Import\Element();
			$importElement->setFilename($infos['filename']);
			$importElement->setLegacyMapping($infos['md5']);
			
			$mediaManager->import($importElement);
			
			
		}
		
	}
	
	function synchronizeProductMedia2() 
	{
	
		
		$sl = $this->getServiceLocator();
		$configuration = $sl->get('Configuration');
		if (!is_array($configuration['akilia'])) {
			throw new \Exception("Cannot find akilia configuration, please see you global config files");
		}
		$configuration =  $configuration['akilia'];		
		
		$products = new Akilia1Products($configuration);
		$products->setServiceLocator($this->getServiceLocator());
		$products->setDbAdapter($this->getServiceLocator()->get('Zend\Db\Adapter\Adapter'));
		
		
		/*
		$mman = $this->getMManManager();
		$fs = $mman->getFilesystem();
		try {
			$fs->write('/test/image.jpg', 'hello', true);
			var_dump($fs->listKeys());
			$a = $fs->read('image.jpg');
		} catch (GException\FileNotFound $e) {
			
		}
		var_dump($a);
		die();
		*/
		
		$typeRepository = $this->em->getRepository('Openstore\Entity\ProductMediaType');
		$types = array();
		foreach ($typeRepository->findAll() as $type) {
			$types[$type->getReference()] = $type->getTypeId();
		};
		
		$mediaRepository = $this->em->getRepository('Openstore\Entity\Media');
		$productRepository = $this->em->getRepository('Openstore\Entity\Product');
		
		$list = $products->getProductPictures();
		
		$folder = 'product';
		
		foreach($list as $basename => $infos) {
			
			$md5 = $infos['md5'];
			
			$media = $mediaRepository->findOneBy(array('legacy_mapping' => $md5));
			
			if (!$media) {
				
				$media = new Entity\Media();
				$media->setFilemtime($infos['filemtime'])
					  ->setLegacyMapping($md5)
					  ->setFilesize($infos['filesize'])
					  //->setTitle($infos['basename'])
					  ->setFilename($infos['basename']);
				$this->em->persist($media);
				//$this->em->flush();
				
				//$media_id = $media->getMediaId();
				
				$product_id = $infos['product_id'];
				$alternate_index = $infos['alternate_index'];
				$type_id = $alternate_index !== null ? $types['PICTURE'] : $types['ALTERNATE_PICTURE'];
				
				try {
					$product = $productRepository->find($product_id);
					if ($product) {
						
						
						$productMedia = new Entity\ProductMedia();
						//$productMedia->setMediaId($this->em->getReference('Openstore\Entity\Media', $media_id));
						$productMedia->setMediaId($media);
						$productMedia->setProductId($product);
						$productMedia->setTypeId($this->em->getReference('Openstore\Entity\ProductMediaType', $type_id));
						$productMedia->setSortIndex($alternate_index);
						
						$this->em->persist($productMedia);
						$this->em->flush();
						
					} else {
						
					}
				} catch (\Exception $e) {
					
					echo 'Removing media:' . $media_id . "\n";
					//$this->em->remove($media);
					//$this->em->flush();
					
				}
				
				
			} else if ($media->getFilemtime() != $infos['filemtime'])  {
				
				
			}
			
			/*
			echo str_pad($infos['product_id'], 10) . "\t" .  
				($infos['alternate_index'] === null ? "0" : $infos['alternate_index']) . "\t" . 
				($infos['product_active'] ? "        " : "ARCHIVED") . "\t" . 
				$infos['filename'] . "\n"; 
			*/
			
			
			//$media = new \MMan\MediaManager();
			//$media->add($infos['filename']);
		}		
		die();
	}
	
	function synchronizeCountry()
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
	
	
	function synchronizeCustomer()
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
	
	function synchronizeCustomerPricelist()
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
					pl.pricelist_id,
					c.customer_id,
					1 as flag_active,
				   '{$this->legacy_synchro_at}' as legacy_synchro_at
					
				from $akilia2db.base_customer_pricelist bcpl
				inner join $akilia2db.base_pricelist bpl on bcpl.pricelist_id = bpl.id
				inner join $db.pricelist	pl on pl.reference = bpl.reference
				inner join $db.customer c on c.legacy_mapping = bcpl.customer_id
				on duplicate key update
					   flag_active = 1,
					   legacy_synchro_at = '{$this->legacy_synchro_at}'
					 ";
		
		$this->executeSQL("Replace customer pricelists", $replace);

		// 2. Deleting - old links in case it changes
		$delete = "
		    delete from $db.customer_pricelist 
			where legacy_synchro_at <> '{$this->legacy_synchro_at}' and legacy_synchro_at is not null";

		$this->executeSQL("Delete eventual removed customer pricelists", $delete);		
		
	}
	
	
	function synchronizeProductPricelist()
	{
		$akilia1db = $this->akilia1Db;
		$db = $this->openstoreDb;

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
					activated_at,
					legacy_synchro_at
				)

				select at.id_article,
				       pl.pricelist_id,
					   at.prix_unit_ht,
					   if((at.flag_promo = 1 or at.flag_liquidation = 1) and at.remise1 > 0, at.remise1, null) as promo_discount,
					   null as promo_start_at,
					   null as promo_end_at,
					   at.flag_availability,
					   a.date_creation,
					'{$this->legacy_synchro_at}' as legacy_synchro_at
					
				from $akilia1db.art_tarif as at
				inner join $db.pricelist pl on at.id_pays = pl.legacy_mapping
				inner join $akilia1db.article a on at.id_article = a.id_article	
				where at.prix_unit_ht > 0
				on duplicate key update
						price = at.prix_unit_ht,
						promo_discount = if((at.flag_promo = 1 or at.flag_liquidation = 1) and at.remise1 > 0, at.remise1, null),
						promo_start_at = null,
						promo_end_at = null,
						
						flag_active = at.flag_availability,
						activated_at = a.date_creation,
						legacy_synchro_at = '{$this->legacy_synchro_at}'
					 ";
		
		$this->executeSQL("Replace product pricelist", $replace);

		// 2. Deleting - old links in case it changes
		$delete = "
		    delete from $db.product_pricelist 
			where legacy_synchro_at <> '{$this->legacy_synchro_at}' and legacy_synchro_at is not null";

		$this->executeSQL("Delete eventual removed product_pricelist", $delete);		
		
	}
	
	function synchronizeProductStock()
	{
		$akilia1db = $this->akilia1Db;
		$db = $this->openstoreDb;

		$stock_id = $this->default_stock_id;
		
		$replace = " insert
		             into $db.product_stock
					(
					product_id,
					stock_id,
					available_stock,
					theoretical_stock,
					legacy_synchro_at
				)

				select at.id_article,
				       1 as stock_id,
					   at.stock,
					   at.stock_theorique,
					'{$this->legacy_synchro_at}' as legacy_synchro_at
					
				from $akilia1db.art_tarif as at
				inner join $akilia1db.article a on at.id_article = a.id_article
				inner join $db.pricelist pl on at.id_pays = pl.legacy_mapping	
				on duplicate key update
						available_stock = at.stock,
						theoretical_stock = at.stock_theorique,
						legacy_synchro_at = '{$this->legacy_synchro_at}'
					 ";
		
		$this->executeSQL("Replace product stock", $replace);

		// 2. Deleting - old links in case it changes
		$delete = "
		    delete from $db.product_stock
			where legacy_synchro_at <> '{$this->legacy_synchro_at}' and legacy_synchro_at is not null";

		$this->executeSQL("Delete eventual removed product_stock", $delete);		
		
	}
	
	
	function synchronizePricelist()
	{
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
						stock_id = $stock_id,
						currency_id = {$this->default_currency_id},
						legacy_synchro_at = '{$this->legacy_synchro_at}'
					 ";
		
		$this->executeSQL("Replace pricelist", $replace);

		// 2. Deleting - old links in case it changes
		$delete = "
		    delete from $db.pricelist 
			where legacy_synchro_at <> '{$this->legacy_synchro_at}' and legacy_synchro_at is not null";

		$this->executeSQL("Delete eventual removed pricelist", $delete);		
		

	}
	
	function synchronizeProductCategory()
	{
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
	
		foreach($rows as $row) {

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
			$pc->setLegacyMapping($row['id_categorie']);
			//$pc->setCreatedAt($row['date_synchro']);
			
			
			$this->em->persist($pc);
			
			$categs[$row['id_categorie']] = $pc;
		}

		$this->em->flush();	
		
		
		$langs = $this->akilia1lang;
		foreach($langs as $lang => $sfx) {
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

	function synchronizeProductBrand()
	{
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
	
	function synchronizeProductGroup()
	{
		$akilia1Db = $this->akilia1Db;
		$db = $this->openstoreDb;

		$replace = "insert into $db.product_group
				(group_id, reference, title, legacy_mapping, legacy_synchro_at)
		        select null, TRIM(f.id_famille), f.libelle_1, f.id_famille, '{$this->legacy_synchro_at}'
			from $akilia1Db.famille f
			on duplicate key update
				reference = trim(f.id_famille),
				title = f.libelle_1, 
			    legacy_synchro_at = '{$this->legacy_synchro_at}'";
		
		$this->executeSQL("Replace product groups", $replace);

		// 2. Deleting - old links in case it changes
		$delete = "
		    delete from $db.product_group where
			legacy_synchro_at <> '{$this->legacy_synchro_at}' and legacy_synchro_at is not null";

		$this->executeSQL("Delete eventual removed groups", $delete);
	}
	

	
	function synchronizeProduct()
	{
		
		$akilia1db = $this->akilia1Db;
		$db = $this->openstoreDb;

		$replace = " insert
		             into $db.product
					(product_id, 
					brand_id,
					group_id,
					category_id,
					unit_id,
					type_id,
					reference, 
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
					
					barcode_ean13,
					
					activated_at,

					legacy_mapping, 
					legacy_synchro_at
				)

				select
					a.id_article as product_id,
					brand.brand_id as brand_id,
					product_group.group_id as group_id,
					category.category_id as category_id,
					{$this->default_unit_id} as unit_id,
					{$this->default_product_type_id} as type_id,
					upper(TRIM(a.reference)) as reference,
					null as slug,
					if(trim(i.libelle_1) = '', null, trim(i.libelle_1)) as title,
					if(trim(a.libelle_1) = '', null, trim(a.libelle_1)) as invoice_title,
					if(trim(i.desc_1) = '', null, trim(i.desc_1)) as description,
					if(trim(i.couleur_1) = '', null, trim(i.couleur_1)) as characteristic,
					
					if(a.flag_archive = 1, 0, 1) as flag_active,
					null as icon_class,
					a.volume as volume,
					a.poids as weight,
					null as length,
					null as height,
					null as width,
					a.barcode_ean13 as barcode_ean13,
					a.date_creation,
					a.id_article as legacy_mapping,
					'{$this->legacy_synchro_at}' as legacy_synchro_at
						
					
				from $akilia1db.article as a
				left outer join $akilia1db.cst_art_infos i on i.id_article = a.id_article	
				left outer join $db.product_brand as brand on brand.legacy_mapping = a.id_marque
				left outer join $db.product_group as product_group on product_group.legacy_mapping = a.id_famille
				left outer join $db.product_category as category on category.legacy_mapping = a.id_categorie
				
				on duplicate key update
						brand_id = brand.brand_id,
						group_id = product_group.group_id,
						unit_id = {$this->default_unit_id},
						category_id = category.category_id,
						reference = upper(a.reference),
						slug = null,
						type_id = {$this->default_product_type_id},
						title = if(trim(i.libelle_1) = '', null, trim(i.libelle_1)),
						invoice_title = if(trim(a.libelle_1) = '', null, trim(a.libelle_1)),
						description = if(trim(i.desc_1) = '', null, trim(i.desc_1)),
						characteristic = if(trim(i.couleur_1) = '', null, trim(i.couleur_1)),
						flag_active = if(a.flag_archive = 1, 0, 1),
						icon_class = null,
						volume = a.volume,
						weight = a.poids,
						length = null,
						height = null,
						width = null,
						barcode_ean13 = a.barcode_ean13,
						activated_at = a.date_creation,
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
	
	function synchronizeProductTranslation()
	{
		$akilia1db = $this->akilia1Db;
		$db = $this->openstoreDb;
		
		$langs = $this->akilia1lang;
		foreach($langs as $lang => $sfx) {
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
					if (trim(i.libelle$sfx) = '', null, trim(i.libelle$sfx)) as title,
					if (trim(a.libelle$sfx) = '', null, trim(a.libelle$sfx)) as invoice_title,	
					if (trim(i.desc$sfx) = '', null, trim(i.desc$sfx)) as description,		
					if (trim(i.couleur$sfx) = '', null, trim(i.couleur$sfx)) as characteristic,		
					'{$this->legacy_synchro_at}'	
				  from $akilia1db.article a
				  inner join $db.product p on p.legacy_mapping = a.id_article 	
				  left outer join $akilia1db.cst_art_infos i on i.id_article = a.id_article 	
			     on duplicate key update
				  title = if (trim(i.libelle$sfx) = '', null, trim(i.libelle$sfx)),
				  invoice_title = if (trim(a.libelle$sfx) = '', null, trim(a.libelle$sfx)),
				  description = if (trim(i.desc$sfx) = '', null, trim(i.desc$sfx)),
				  characteristic = if (trim(i.couleur$sfx) = '', null, trim(i.couleur$sfx)),
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
	protected function executeSQL($key, $query, $disable_foreign_key_checks=true)
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
	protected function log($message, $priority=null)
	{
	    echo "$message\n";
	}	
	
	
	/**
	 * 
	 * @param \Zend\Db\Adapter\Adapter $adapter
	 */
	public function setDbAdapter(Adapter $adapter) {
		$this->adapter = $adapter;
		return $this;
	}
	
	
	/**
	 * 
	 * @return Zend\Db\Adapter\Adapter
	 */
	function getDbAdapter()
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
	public function getMManManager() {
		return $this->getServiceLocator()->get('MMan\Manager');
				
	}
	
}

