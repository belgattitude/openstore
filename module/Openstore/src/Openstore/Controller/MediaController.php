<?php

namespace Openstore\Controller;

use Zend\Mvc\Controller\AbstractActionController;
use Zend\View\Model\ViewModel;

use Soluble\Media\BoxDimension;
use Soluble\Media\Converter\ImageConverter;



class MediaController extends AbstractActionController
{
    public function indexAction()
    {
		$view = new ViewModel();
        return $view;
    }
	
	/**
	 * 
	 * @return ImageConverter
	 */
	function getImageConverter() {
		$converter = $this->getServiceLocator()->get('Soluble\Media\Converter');
		$imageConverter = $converter->createConverter('image');
		return $imageConverter;
		
	}
	
	function mediaAction() {
		
		$mediaManager = $this->getServiceLocator()->get('MMan\MediaManager');
		
		$media_id = $this->params()->fromRoute('media_id');
		
		$width = 170;
		$height = 200;
		$quality = 90;
		$format = 'jpg';
		
		try {
			$imageConverter = $this->getImageConverter();
			$box = new BoxDimension($width, $height);
			$media = $mediaManager->get($media_id);
			$filename = $media->getPath();
			
			
			$imageConverter->getThumbnail($filename, $box, $format, $quality);
			die();
		} catch (\Exception $e) {
			throw $e;
		}
		
	}
}
