<?php

namespace Openstore\Controller;

use Zend\Mvc\Controller\AbstractActionController;
use Zend\View\Model\ViewModel;
use Soluble\Media\BoxDimension;
use Soluble\Media\Converter\ImageConverter;
use Soluble\Normalist\Synthetic\TableManager;
use Intervention\Image\Image;
use Intervention\Image\ImageManager;
use Symfony\Component\Finder\Finder;

class MediaController extends AbstractActionController
{
    public function indexAction()
    {
        $view = new ViewModel();
        return $view;
    }


    /**
     * @return TableManager
     */
    public function getTableManager()
    {
        return $this->getServiceLocator()->get('SolubleNormalist\TableManager');
    }

        /**
         * Parse picture options
         *
         * @throws Exception
         * @param string $options
         * @return array
         */
    protected function parsePictureOptions($options)
    {
        $regexp = '/^(([0-9]{1,4})x([0-9]{1,4}))\-([0-9]{2})$/';
        if (!preg_match($regexp, $options)) {
            throw new \Exception("Preview options '$options' are invalid.");
        }

        preg_match_all($regexp, $options, $matches);
        $params = array();
        $params['width'] = $matches[2][0];
        $params['height'] = $matches[3][0];
        $params['quality'] = $matches[4][0];
        $params['resolution'] = $matches[1][0];

        return $params;
    }

    public function previewAction()
    {
        $p          = $this->params();
        $type       = $p->fromRoute('type');
        $prefix     = $p->fromRoute('prefix');
        $media_id   = $p->fromRoute('media_id');
        $options    = $p->fromRoute('options');
        $mtime      = $p->fromRoute('filemtime');
        $format     = $p->fromRoute('format');

        $mediaManager = $this->getServiceLocator()->get('MMan\MediaManager');

        try {
            // First ensure prefix is correct
            $last_media_id = str_pad(substr($media_id, -2), 2, "0", STR_PAD_LEFT);
            if ($prefix !== $last_media_id) {
                throw new \Exception("Prefix part is not correct '$prefix', should be the 2 last chars of media_id '$last_media_id'");
            }
            $display_filename = null;
            $id = null;
            switch ($type) {
                case 'productpicture':
                    // In this case the media_id contains the product picture id
                    $id = $this->getProductPictureMediaId($media_id);
                    // no break;
                case 'picture':
                    if ($id === null) {
                        $id = $media_id;
                    }

                    // parse options;
                    if ($display_filename === null) {
                        $display_filename = "$media_id.$format";
                    }

                    $params = $this->parsePictureOptions($options);

                    // test params;
                    $resolutions = $this->getAcceptedResolutions();
                    if (!in_array($params['resolution'], $resolutions)) {
                        throw new \Exception("Invalid resolution requested, only supported: " . join(',', $resolutions));
                    }
                    $formats = $this->getAcceptedFormats();
                    if (!in_array($format, $formats)) {
                        throw new \Exception("Invalid format requested, only supported: " . join(',', $formats));
                    }
                    $qualities = $this->getAcceptedQualities();
                    if (!in_array($params['quality'], $qualities)) {
                        throw new \Exception("Invalid quality requested, only supported: " . join(',', $qualities));
                    }


                    $imageManager = new ImageManager(array('driver' => 'imagick'));
                    //var_dump($params); die();
                    $media = $mediaManager->get($id);
                    $filename = $media->getPath();
                    $latest_media_filemtime = $media->getFilemtime();


                    $image = $imageManager->make($filename);
                    $image->resize($params['width'], $params['height'], function ($constraint) {
                         $constraint->aspectRatio();
                    });
                        $response = $image->encode($format, $params['quality']);

                        // Step 2: try to cache resulting image

                        //$cache_path = realpath(dirname(__FILE__) . '/../../../../../public/media/preview/');
                        $base_cache_path = dirname(__FILE__) . '/../../../../../data/media';

                    if (realpath($base_cache_path) == '') {
                        throw new \Exception("Base cache path does not exists: $base_cache_path");
                    }
                        $cache_path = realpath($base_cache_path) .
                                DIRECTORY_SEPARATOR . 'preview' .
                                DIRECTORY_SEPARATOR . $type .
                                DIRECTORY_SEPARATOR . $options .
                                DIRECTORY_SEPARATOR . $prefix;



                        // Check if there's already some previews in the directory
                        $obsolete_previews = array();
                        $links_to_create = array();

                    if (is_dir($cache_path)) {
                        $finder = new Finder();
                        $finder->files()->in($cache_path)->name("/^$media_id(\_[0-9]+){0,1}\.jpg$/");
                        foreach ($finder as $file) {
                            $obsolete_previews[] = $file->getRealpath();
                        }
                    }

                        $latest_preview_file = $cache_path . DIRECTORY_SEPARATOR . $media_id . '_' . $latest_media_filemtime .  '.' . $format;

                    if ($mtime === null) {
                        $links_to_create[] = $cache_path . DIRECTORY_SEPARATOR . $media_id .  '.' . $format;
                    } elseif ($mtime != $latest_media_filemtime) {
                        $links_to_create[] = $cache_path . DIRECTORY_SEPARATOR . $media_id . '_' . $mtime .  '.' . $format;
                    }


                        $links_to_create = array_unique(array_merge($links_to_create, $obsolete_previews));

                    if (($key = array_search($latest_preview_file, $links_to_create)) !== false) {
                        unset($links_to_create[$key]);
                    }


                        $old_umask = umask(0);

                    try {
                        // Create eventually the cache directory
                        if (!file_exists($cache_path)) {
                            $ret = @mkdir($cache_path, $mode = 0777, $recursive = true);
                            if ($ret === false) {
                                throw new \Exception("Cannot create cache path: $cache_path");
                            }
                        }

                        if (!is_dir($cache_path)) {
                            throw new \Exception("Invalid cache path, it's not a directory: $cache_path");
                        }


                        // Save preview on disk for future use
                            $ret = @file_put_contents($latest_preview_file, $response, LOCK_EX);
                        if ($ret === false) {
                            throw new \Exception("Cannot save resized picture in $latest_preview_file");
                        }

                            // Remove and update old links
                        foreach ($links_to_create as $link) {
                            try {
                                if (file_exists($link)) {
                                    if (!unlink($link)) {
                                        throw new \Exception("Cannot remove obsolete preview '$link'");
                                    }
                                }
                                if (!symlink($latest_preview_file, $link)) {
                                    // todo log
                                        throw new \Exception("Cannot create preview link '$link' towards '$latest_preview_file'");
                                }
                            } catch (\Exception $e) {
                                if ($this->serviceLocator->has('Application\Log')) {
                                    $msg = "Media preview links failed: " . $e->getMessage() . ", see: " . __METHOD__;
                                    $this->serviceLocator->get('Application\Log')->warn($msg);
                                };
                            }
                        }
                    } catch (\Exception $e) {
                        // Do nothing
                        if ($this->serviceLocator->has('Application\Log')) {
                            $msg = "Media preview failed: " . $e->getMessage() . ", see: " . __METHOD__;
                            $this->serviceLocator->get('Application\Log')->err($msg);
                        };
                    }

                        umask($old_umask);
                        $this->outputResponse($format, $response, "$media_id.$format");

                    break;
                default:
                    throw new \Exception("Does not handle format '$type'");
            }
        } catch (\Exception $e) {
            $this->notFoundAction();
            return array('message' => $e->getMessage());
        }
    }

    protected function outputResponse($format, $response, $display_filename)
    {
        $content_type = '';
        switch ($format) {
            case 'jpg':
                $content_type = 'image/jpeg';
                break;
            case 'png':
                $content_type = 'image/png';
                break;
            case 'gif':
                $content_type = 'image/gif';
                break;
            default:
                throw new \Exception("Unsupported format '$format'");
        }

        header("Content-type: $content_type", true);
        header("Accept-Ranges: bytes", true);
        header("Cache-control: max-age=2592000, public", true);
        header("Content-Disposition: inline; filename=\"$display_filename\";", true);
        //header('Last-Modified: '. gmdate('D, d M Y H:i:s', filemtime($filename)).' GMT', true);
        header('Expires: ' . gmdate('D, d M Y H:i:s', strtotime('+1 years')) . ' GMT', true);
        //header('Content-Disposition: attachment; filename="downloaded.pdf"');
        header('Pragma: cache', true);
        echo $response;
        die();
    }

        /**
         * Return primary product picture media id
         *
         * @param int $product_id
         * @return int
         * @throws \Exception
         */
    protected function getProductPictureMediaId($product_id)
    {
        $tm = $this->getTableManager();
        $pmTable = $tm->table('product_media');
        $search = $pmTable->search('pm')
                    ->join(array('pmt' => 'product_media_type'), 'pmt.type_id = pm.type_id')
                    ->where(array(
                                    'pm.product_id' => $product_id,
                                    'pm.flag_primary' => 1,
                                    'pmt.reference' => 'PICTURE'
                      ))->execute();
        if ($search->count() > 0) {
            $media_id = $search->current()->media_id;
        } else {
            throw new \Exception("Product id '$product_id' does not have an associated image.");
        }
        return $media_id;
    }



    public function pictureAction()
    {
        $tm = $this->getTableManager();

        $type = $this->params()->fromRoute('type');
        $id   = $this->params()->fromRoute('id');
        switch ($type) {
            case 'product':
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
                //echo $media_id; die();
                break;


            case '':
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
            } elseif (!in_array($resolution, $this->getAcceptedResolutions())) {
                $valid = join(',', $this->getAcceptedResolutions());
                throw new \Exception("Requested resolution '$resolution' is forbidden, supported: '$valid'.");
            }
            // Quality
            $quality = $this->params()->fromRoute('quality');
            if ($quality == '') {
                $quality = 90;
            } elseif (!in_array($quality, $this->getAcceptedQualities())) {
                $valid = join(',', $this->getAcceptedQualities());
                throw new \Exception("Requested quality '$quality' is forbidden, supported: '$valid'.");
            }
            // Format
            $format = $this->params()->fromRoute('format');
            if ($format == '') {
                $format = 'jpg';
            } elseif (!in_array($format, $this->getAcceptedFormats())) {
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
    protected function getImageConverter()
    {
        $converter = $this->getServiceLocator()->get('Soluble\Media\Converter');
        $params = array('backend' => 'imagick');
        $imageConverter = $converter->createConverter('image', $params);
        return $imageConverter;
    }

    protected function getAcceptedFormats()
    {
        $accepted = array(
        'jpg', 'png'
        );
        return $accepted;
    }

    protected function getAcceptedResolutions()
    {
        $accepted =  array(
        '30x30',        // for typeahead
        '40x40',        // for emdmusic.com typeahed (mini)
        '65x90',        // for old emdmusic.com website 'small pictures' and browse
        '170x200',        // for openstore browse
        '250x750',        // for old emdmusic.com website 'thumbnails'
        '800x800',
        '1024x768',        // for emdmusic.com lightbox
        '1280x1024',    // for emdmusic.com info page
        '1200x1200',
        '3000x3000'        // for printing in high resolution
        );
        return $accepted;
    }

    protected function getAcceptedQualities()
    {
        $accepted = array(
        80, 85, 90, 95
        );
        return $accepted;
    }
}
