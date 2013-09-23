<?php
/**
 * 
 * @author Vanvelthem Sébastien
 */
namespace Akilia;

class Synchronizer 
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
	protected $zendDb;
	
	protected $default_currency_id = 1;
	protected $default_stock_id = 1;
	protected $default_unit_id = 1;	
	protected $default_product_type_id = 1;
	
	protected $legacy_synchro_at;
	
	function __construct(\Doctrine\ORM\EntityManager $em, \Zend\Db\Adapter\Adapter $zendDb)
	{
		$this->em = $em;

		$this->openstoreDb = $em->getConnection()->getDatabase();
		$this->mysqli = $em->getConnection()->getWrappedConnection()->getWrappedResourceHandle();
		$this->zendDb = $zendDb;
		$this->legacy_synchro_at = date('Y-m-d H:i:s');
		
	}
	
	function setConfiguration(array $config) {
		$this->akilia2Db	= $config['db_akilia2'];
		$this->akilia1Db	= $config['db_akilia1'];
		$this->akilia1lang	= $config['akilia1_language_map'];
	}
	
	
	function synchronizeAll()
	{
		$this->synchronizeCountry();
		$this->synchronizeCustomer();
		$this->synchronizePricelist();
		$this->synchronizeProductGroup();
		$this->synchronizeProductBrand();
		$this->synchronizeProductCategory();
		$this->synchronizeProduct();
		$this->synchronizeProductTranslation();
		$this->synchronizeProductPricelist();
		$this->synchronizeProductStock();
		
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
		    delete from $db.country 
			where legacy_synchro_at <> '{$this->legacy_synchro_at}' and legacy_synchro_at is not null";

		$this->executeSQL("Delete eventual removed customers", $delete);		
		
		
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

		$stock_id = 1;
		
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
	
	
}

