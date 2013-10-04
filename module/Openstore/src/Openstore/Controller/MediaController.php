<?php

namespace Openstore\Controller;

use Zend\Mvc\Controller\AbstractActionController;
use Zend\View\Model\ViewModel;

use Soluble\Media\BoxDimension;
use Soluble\Media\Converter\ImageConverter;

use Imagine\Imagick\Imagine;
//use Imagine\Gd\Imagine;
use Imagine\Image\ImageInterface;
use Imagine\Image\Box;
use Zend\Cache\StorageFactory;
use Zend\Cache\Storage\Adapter\AdapterOptions as CacheOptions;


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
	
	function mediaOldAction() {
		
		$cache  = StorageFactory::adapterFactory('filesystem');
		$cache->setOptions(array(
			'ttl' => 0,
			'cache_dir' => '/web/tmp/cache',
			'dir_level' => 3,
			'dir_permission' => 0777,
			'file_permission' => 0666
		));
		$plugin = StorageFactory::pluginFactory('exception_handler', array(
			'throw_exceptions' => false,
		));
		$cache->addPlugin($plugin);	
		
		$media_id = $this->params()->fromRoute('media_id');
		
		$cache_enabled = false;
		$width = 170;
		$height = 200;
		$quality = 90	;
		
		
		
		// media 2171 = product 14610
		$format = 'jpg';
		
		$cache_key = md5("$media_id/$width/$height/$quality/$format");
		
		$mediaManager = $this->getServiceLocator()->get('MMan\MediaManager');
		try {
			$media = $mediaManager->get($media_id);
			$cacheMd = $cache->getMetadata($cache_key);
			if ($cache_enabled && $cache->hasItem($cache_key) && $cacheMd !== false && $cacheMd['mtime'] > $media->getFilemtime()) {

				// DO nothing it's in cache
				
			} else {

				$path = $media->getPath();
				$imagine = new Imagine();
				
				if ($imagine instanceof Imagine\Imagick\Imagine) {
					$filter = ImageInterface::FILTER_MITCHELL;
					/**
					 * BESSEL : 53k
					 * LANCZOS: 54.5k
					 * GAUSSIAN: 52k
					 * MITCHELL: 53k
					 jpegtran -optimize 14610.jpg > 14610_test.jpg
					 */
					
				} else {
					$filter = ImageInterface::FILTER_UNDEFINED;
				}
				
				$image = $imagine->open($path);
				
				
				// Get dimension by keeping proportions
				$size = $image->getSize();
				$ratio_x = $size->getWidth() / $width;
				$ratio_y = $size->getHeight() / $height;
				$max_ratio = max($ratio_x, $ratio_y);
				$width 	= (int) ($size->getWidth() / $max_ratio);
				$height = (int) ($size->getHeight() / $max_ratio);
				
				$newSize = new Box($width, $height);
				
				//$image->flipVertically();
				//$image->rotate(270);
				// For size it's good, but quality of colors need to be checked
				$image->strip();
				/*
				             ImageInterface::INTERLACE_NONE      => \Imagick::INTERLACE_NO,
            ImageInterface::INTERLACE_LINE      => \Imagick::INTERLACE_LINE,
            ImageInterface::INTERLACE_PLANE     => \Imagick::INTERLACE_PLANE,
            ImageInterface::INTERLACE_PARTITION => \Imagick::INTERLACE_PARTITION,

				 */
				$image->interlace(ImageInterface::INTERLACE_LINE);
				//$image->strip();
				
				$image->resize($newSize, $filter);
				$options = array(
					'quality' => $quality,
					'flatten' => true,
					//'resolution-units' => ImageInterface::RESOLUTION_PIXELSPERINCH,
					//'resolution-y' => 72,
					//'resolution-x' => 72,
				);
				
				$cache->setItem($cache_key, $image->get($format, $options));
				
			}

			$value = $cache->getItem($cache_key);
			switch ($format) {
				case 'jpg' :
					$content_type = 'image/jpeg';
				case 'png':
					$content_type = 'image/png';
					break;
				case 'gif':
					$content_type = 'image/gif';
					break;
				default:
					throw new \Exception("Unsupported format '$format'");
			}
//die('cool');			
			header("Content-type: $content_type", true);
			header("Accept-Ranges: bytes", true);
			header("Cache-control: max-age=2592000, public", true);
			header("Content-Disposition: inline; filename=\"{$media->getFilename()}\";", true);
			header('Last-Modified: '. gmdate('D, d M Y H:i:s', $media->getFilemtime()).' GMT', true);
			//header('Date: ' . );
			header('Expires: ' . gmdate('D, d M Y H:i:s', strtotime('+10 years')) . ' GMT');
			//header('Content-Disposition: attachment; filename="downloaded.pdf"');
			echo $value;
			die();
			
			var_dump($path);
			die();
		} catch (\Exception $e) {
			// ERROR 403 ?
			//var_dump($e);
			//die();
			throw $e;
			
		}	
	}
}
