<?php

namespace Openstore\Controller;

use Zend\Mvc\Controller\AbstractActionController;
use Zend\View\Model\ViewModel;
use Soluble\Media\BoxDimension;
use Soluble\Media\Converter\ImageConverter;

class MediaController extends AbstractActionController
{

	public function indexAction() {
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

	/**
	 * @return \Soluble\Normalist\SyntheticTable
	 */
	function getSyntheticTable() {
		return $syntheticTable = $this->getServiceLocator()->get('Soluble\Normalist\SyntheticTable');
	}

	function productpictureAction() {
		$table = $this->getSyntheticTable();
		$product_id = $this->params()->fromRoute('product_id');
		$product_medias = $table->select('product_media')
						->join(array('pmt' => 'product_media_type'), 'pmt.type_id = product_media.type_id')
						->where(array(
							'product_id' => $product_id,
							'flag_primary' => 1,
							'pmt.reference' => 'PICTURE'
						))->execute()->toArray();

		$media_id = $product_medias[0]['media_id'];
		if ($media_id == null) {
			$this->getResponse()->setStatusCode(404);
			return;
		}
		try {
			$width  = $this->params()->fromRoute('width');
			$height  = $this->params()->fromRoute('height');
			$quality = $this->params()->fromRoute('quality');
			$format  = $this->params()->fromRoute('format');

			$this->flushImagePreview($media_id, $width, $height, $quality, $format);
		} catch (\Exception $e) {
			$this->getResponse()->setStatusCode(500);
			return;
		}
	}

	function flushImagePreview($media_id, $width, $height, $quality, $format) {

		
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
