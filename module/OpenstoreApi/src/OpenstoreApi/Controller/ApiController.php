<?php

namespace Openstore\Controller;

use Zend\Mvc\Controller\AbstractActionController;
use Zend\View\Model\ViewModel;



class ApiController extends AbstractActionController
{
    public function indexAction()
    {
		$view = new ViewModel();
        return $view;
    }
	
	
	public function productPictureAction() 
	{
	
select p.product_id, 
		p.reference, 
		p.barcode_ean13, 
		pb.reference as brand_reference, 
		pg.reference as group_reference,
		pmt.reference as type_reference,
		pmt.type_id,
		m.media_id,
		pm.flag_primary, 
		pm.sort_index, 
		m.filename as original_filename,
		m.filesize as original_filesize,
		m.filemtime,
		GROUP_CONCAT(distinct pl.reference)
		
from product_media pm
inner join media m on m.media_id = pm.media_id
inner join media_container mc on mc.container_id = m.container_id
inner join product p on p.product_id = pm.product_id
left outer join product_group pg on pg.group_id = p.group_id
inner join product_brand pb on pb.brand_id = p.brand_id
inner join product_media_type pmt on pmt.type_id = pm.type_id 
left outer join product_pricelist ppl on p.product_id = ppl.product_id
left outer join pricelist pl on pl.pricelist_id = ppl.pricelist_id

where p.flag_active = 1
and ppl.flag_active = 1
and pl.reference = 'BE'
group by 1
		
		
		
		
	}
	
}
