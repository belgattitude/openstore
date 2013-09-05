<?php
/**
 * 
 * @author Vanvelthem Sébastien
 */


namespace Nv\Akilia;



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
	
	protected $legacy_synchro_at;
	
	function __construct(\Doctrine\ORM\EntityManager $em, \Zend\Db\Adapter\Adapter $zendDb)
	{
		$this->em = $em;
		$this->akilia2Db = 'akilia2_emd';
		$this->akilia1Db = 'emd00';
		$this->akilia1lang = array(
			'fr' => '_1',
			'en' => '_3',
			'nl' => '_2',
			'de' => '_4'
		);
		
		$this->openstoreDb = $em->getConnection()->getDatabase();
		$this->mysqli = $em->getConnection()->getWrappedConnection()->getWrappedResourceHandle();
		$this->zendDb = $zendDb;
		$this->legacy_synchro_at = date('Y-m-d H:i:s');
		
		
		
		
	}
	
	
	function synchronizeAll()
	{
		$this->synchronizePricelist();
		$this->synchronizeProductGroup();
		$this->synchronizeProductBrand();
		$this->synchronizeProductCategory();
		$this->synchronizeProduct();
		$this->synchronizeProductTranslation();
		$this->synchronizeProductPricelist();
		
		
		
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
					legacy_synchro_at
				)

				select at.id_article,
				       pl.pricelist_id,
					   at.prix_unit_ht,
					'{$this->legacy_synchro_at}' as legacy_synchro_at
					
				from $akilia1db.art_tarif as at
				inner join $db.pricelist pl on at.id_pays = pl.legacy_mapping
				where at.flag_availability = 1
				and at.prix_unit_ht > 0
				on duplicate key update
						price = at.prix_unit_ht,
						legacy_synchro_at = '{$this->legacy_synchro_at}'
					 ";
		
		$this->executeSQL("Replace product pricelist", $replace);

		// 2. Deleting - old links in case it changes
		$delete = "
		    delete from $db.product_pricelist 
			where legacy_synchro_at <> '{$this->legacy_synchro_at}' and legacy_synchro_at is not null";

		$this->executeSQL("Delete eventual removed product_pricelist", $delete);		
		
	}
	
	function synchronizePricelist()
	{
		$akilia1db = $this->akilia1Db;
		$db = $this->openstoreDb;

		$replace = " insert
		             into $db.pricelist
					(
					reference,
					legacy_mapping, 
					legacy_synchro_at
				)

				select distinct at.id_pays,
					at.id_pays as legacy_mapping,
					'{$this->legacy_synchro_at}' as legacy_synchro_at
					
				from $akilia1db.art_tarif as at
				on duplicate key update
						
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
		        select bpb.id, bpb.reference, bpb.name, bpb.url, bpb.legacy_mapping, '{$this->legacy_synchro_at}'
			from $akilia2db.base_product_brand bpb
			on duplicate key update
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
		        select null, f.id_famille, f.libelle_1, f.id_famille, '{$this->legacy_synchro_at}'
			from $akilia1Db.famille f
			on duplicate key update
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
					
					

					legacy_mapping, 
					legacy_synchro_at
				)

				select
					a.id_article as product_id,
					brand.brand_id as brand_id,
					product_group.group_id as group_id,
					category.category_id as category_id,
					null as unit_id,
					upper(a.reference) as reference,
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
						unit_id = null,
						category_id = category.category_id,
						reference = upper(a.reference),
						slug = null,
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
