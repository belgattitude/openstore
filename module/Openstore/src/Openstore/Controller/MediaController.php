<?php

namespace Openstore\Controller;

use Zend\Mvc\Controller\AbstractActionController;
use Zend\View\Model\ViewModel;

class MediaController extends AbstractActionController
{
    public function indexAction()
    {
		$view = new ViewModel();
        return $view;
    }
	
	function mediaAction() {
		
		$media_id = $this->params()->fromRoute('media_id');
		$mediaManager = $this->getServiceLocator()->get('MMan\MediaManager');
		$mediaManager->get($media_id);
		
		
		die('cool');
		
	}
	
}
