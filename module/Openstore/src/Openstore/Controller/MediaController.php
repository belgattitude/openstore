<?php

namespace Openstore\Controller;

use Zend\Mvc\Controller\AbstractActionController;
use Zend\View\Model\ViewModel;
use Soluble\Media\BoxDimension;
use Soluble\Media\Converter\ImageConverter;
use Soluble\Normalist\Synthetic\TableManager;

class MediaController extends AbstractActionController
{

	public function indexAction() {
		$view = new ViewModel();
		return $view;
	}


	/**
	 * @return TableManager
	 */
	function getTableManager() {
		return $this->getServiceLocator()->get('SolubleNormalist\TableManager');
	}
        
        
        function previewAction()
        {
            $params     = $this->params();
            $type       = $params->fromRoute('type');
            $media_id   = $params->fromRoute('media_id');            
            $resolution = $params->fromRoute('resolution');            
            $quality    = $params->fromRoute('quality');            
            $format     = $params->fromRoute('format');            
            
            echo $quality;
            
            die('cool');
        }

	function pictureAction() 
	{
		
		$tm = $this->getTableManager();
		
		$type = $this->params()->fromRoute('type');
		$id   = $this->params()->fromRoute('id');
		switch ($type) {
			case 'product' :
				$pmTable = $tm->table('product_media');
				$search = $pmTable->search('pm')
						->join(array('pmt' => 'product_media_type'), 'pmt.type_id = pm.type_id')
						->where(array(
									'pm.product_id' => $id,
									'pm.flag_primary' => 1,
									'pmt.reference' => 'PICTURE'
								))
						->execute();
				if ($search->count() > 0) {
					$media_id = $search->current()->media_id;
				}
				
				break;

			case '' :
				$media_id = $id;
				break;
			
			default:
				
				die('not supported type');
			
		}
		
		if ($media_id == null) {
			$this->getResponse()->setStatusCode(404);
			return;
		}

		// Testing resolution
		try {
			// Resolution
			$resolution = $this->params()->fromRoute('resolution');
			if ($resolution == '') {
				$resolution = '1200x1200';
			} else if (!in_array($resolution, $this->getAcceptedResolutions())) {
				$valid = join(',', $this->getAcceptedResolutions());
				throw new \Exception("Requested resolution '$resolution' is forbidden, supported: '$valid'.");
			}
			// Quality
			$quality = $this->params()->fromRoute('quality');
			if ($quality == '') {
				$quality = 90;
			} else if (!in_array($quality, $this->getAcceptedQualities())) {
				$valid = join(',', $this->getAcceptedQualities());
				throw new \Exception("Requested quality '$quality' is forbidden, supported: '$valid'.");
			}
			// Format
			$format = $this->params()->fromRoute('format');
			if ($format == '') {
				$format = 'jpg';
			} else if (!in_array($format, $this->getAcceptedFormats())) {
				$valid = join(',', $this->getAcceptedFormats());
				throw new \Exception("Requested format '$quality' is forbidden, supported: '$valid'.");
			}
			
		} catch (\Exception $e) {
			$this->getResponse()->setStatusCode(403);
			$this->getResponse()->setContent($e->getMessage());
			return $this->getResponse();
		}
		
		try {
			$size = explode('x', $resolution);
			$width = $size[0];
			$height = $size[1];
			$this->flushImagePreview($media_id, $width, $height, $quality, $format);
		} catch (\Exception $e) {
			$this->getResponse()->setStatusCode(500);
			$this->getResponse()->setContent($e->getMessage());
			return $this->getResponse();
		}
	}

	protected function flushImagePreview($media_id, $width, $height, $quality, $format) 
	{

		$mediaManager = $this->getServiceLocator()->get('MMan\MediaManager');
		try {
			$imageConverter = $this->getImageConverter();
			$box = new BoxDimension($width, $height);
			$media = $mediaManager->get($media_id);
			$filename = $media->getPath();
			$imageConverter->getThumbnail($filename, $box, $format, $quality);
			die();
		} catch (\Exception $e) {
			var_dump(get_class($e));
			var_dump($e->getMessage());
			die();
			throw $e;
		}
	}


	/**
	 * 
	 * @return ImageConverter
	 */
	protected function getImageConverter() {
		$converter = $this->getServiceLocator()->get('Soluble\Media\Converter');
		$params = array('backend' => 'imagick');
		$imageConverter = $converter->createConverter('image', $params);
		return $imageConverter;
	}

	protected function getAcceptedFormats() {
		$accepted = array(
			'jpg', 'png'
		);
		return $accepted;
	}
	
	protected function getAcceptedResolutions() {
		$accepted =  array(
			'30x30',		// for typeahead
			'40x40',		// for emdmusic.com typeahed (mini)
			'65x90',		// for old emdmusic.com website 'small pictures' and browse
			'170x200',		// for openstore browse
			'250x750',		// for old emdmusic.com website 'thumbnails'
			'800x800', 
			'1024x768',		// for emdmusic.com lightbox
			'1280x1024',	// for emdmusic.com info page
			'1200x1200',
			'3000x3000'		// for printing in high resolution
			); 
		return $accepted;
	}
	
	protected function getAcceptedQualities() {
		$accepted = array(
			80, 85, 90, 95
		);
		return $accepted;
	}
	
}
