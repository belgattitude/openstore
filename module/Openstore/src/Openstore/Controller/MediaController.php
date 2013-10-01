<?php

namespace Openstore\Controller;

use Zend\Mvc\Controller\AbstractActionController;
use Zend\View\Model\ViewModel;

use Imagine\Imagick\Imagine;
//use Imagine\Gd\Imagine;
use Imagine\Image\ImageInterface;
use Imagine\Image\Box;
use Zend\Cache\StorageFactory;
use Zend\Cache\Storage\Adapter\AdapterOptions as CacheOptions;

			/*
			 resize
             ImageInterface::FILTER_UNDEFINED => \Imagick::FILTER_UNDEFINED,
            ImageInterface::FILTER_BESSEL    => \Imagick::FILTER_BESSEL,
            ImageInterface::FILTER_BLACKMAN  => \Imagick::FILTER_BLACKMAN,
            ImageInterface::FILTER_BOX       => \Imagick::FILTER_BOX,
            ImageInterface::FILTER_CATROM    => \Imagick::FILTER_CATROM,
            ImageInterface::FILTER_CUBIC     => \Imagick::FILTER_CUBIC,
            ImageInterface::FILTER_GAUSSIAN  => \Imagick::FILTER_GAUSSIAN,
            ImageInterface::FILTER_HANNING   => \Imagick::FILTER_HANNING,
            ImageInterface::FILTER_HAMMING   => \Imagick::FILTER_HAMMING,
            ImageInterface::FILTER_HERMITE   => \Imagick::FILTER_HERMITE,
            ImageInterface::FILTER_LANCZOS   => \Imagick::FILTER_LANCZOS,
            ImageInterface::FILTER_MITCHELL  => \Imagick::FILTER_MITCHELL,
            ImageInterface::FILTER_POINT     => \Imagick::FILTER_POINT,
            ImageInterface::FILTER_QUADRATIC => \Imagick::FILTER_QUADRATIC,
            ImageInterface::FILTER_SINC      => \Imagick::FILTER_SINC,
            ImageInterface::FILTER_TRIANGLE  => \Imagick::FILTER_TRIANGLE

			 * 
			 */


class MediaController extends AbstractActionController
{
    public function indexAction()
    {
		$view = new ViewModel();
        return $view;
    }
	
	function mediaAction() {
		
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
		$width = 1000;
		$height = 1000;
		$quality = 90	;
		
		$filter = ImageInterface::FILTER_MITCHELL;
		//$filter = ImageInterface::FILTER_UNDEFINED;
		
		/**
		 * BESSEL : 53k
		 * LANCZOS: 54.5k
		 * GAUSSIAN: 52k
		 * MITCHELL: 53k
		 jpegtran -optimize 14610.jpg > 14610_test.jpg
		 * 
		 * 
		 */
		
		// media 2171 = product 14610
		$format = 'jpg';
		
		$cache_key = md5("$media_id/$width/$height/$quality/$filter/$format");
		
		$mediaManager = $this->getServiceLocator()->get('MMan\MediaManager');
		try {
			$media = $mediaManager->get($media_id);
			$cacheMd = $cache->getMetadata($cache_key);
			if ($cache_enabled && $cache->hasItem($cache_key) && $cacheMd !== false && $cacheMd['mtime'] > $media->getFilemtime()) {

				// DO nothing it's in cache
				
			} else {

				$path = $media->getPath();
				$imagine = new Imagine();
				$image = $imagine->open($path);
				
				
				// Get dimension by keeping proportions
				$size = $image->getSize();
				$ratio_x = $size->getWidth() / $width;
				$ratio_y = $size->getHeight() / $height;
				$max_ratio = max($ratio_x, $ratio_y);
				$width 	= (int) ($size->getWidth() / $max_ratio);
				$height = (int) ($size->getHeight() / $max_ratio);
				
				$newSize = new Box($width, $height);
				
				// For size it's good, but quality of colors need to be checked
				//$image->strip();
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
