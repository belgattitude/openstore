<?php

namespace MMan;

use MMan\Service\Storage;
use MMan\Import\Element as ImportElement;
use Soluble\Normalist\Synthetic\TableManager;

class MediaManager
{
    /**
     * @var \MMan\Service\Storage
     */
    protected $storage;

    /**
     *
     * @var TableManager
     */
    protected $tm;

    public function __construct()
    {
    }

    /**
     *
     * @param integer $media_id
     * @return Media
     */
    public function get($media_id)
    {
        $tm = $this->getTableManager();
        $select = $tm->select();
        $select->from(['m' => 'media'])
                ->join(['mc' => 'media_container'], 'm.container_id = mc.container_id', [
                    'folder'
                ])
                ->columns([
                    'filename', 'filesize', 'mimetype', 'location',
                    'title', 'description', 'filemtime', 'created_at',
                    'updated_at', 'container_id'
                ])
                ->where(['media_id' => $media_id]);
        $resultset = $select->execute();
        if ($resultset->count() != 1) {
            throw new \Exception("Cannot locate media '$media_id'");
        }

        $media = new Media($this);
        $media->setProperties($resultset->current());

        return $media;
    }

    /**
     *
     * @param ImportElement $element
     * @param int $container_id
     * @param boolean $overwrite
     */
    public function import(ImportElement $element, $container_id, $overwrite = true)
    {
        $fs = $this->storage->getFilesystem();

        // STEP 1 : Adding into database

        $tm = $this->getTableManager();
        $mediaTable = $tm->table('media');

        $media = $mediaTable->findOneBy(['legacy_mapping' => $element->getLegacyMapping()]);

        $unchanged = false;
        if ($media !== false) {
            // test if media has changed
            if ($media['filemtime'] == $element->getFilemtime() &&
                    $media['filesize'] == $element->getFilesize()) {
                $unchanged = true;
            }
        }

        if (!$unchanged) {
            // File have changed

            $tm->transaction()->start();


            $filename = $element->getFilename();
            $data = [
                'filename' => basename($filename),
                'filemtime' => $element->getFilemtime(),
                'filesize' => $element->getFilesize(),
                'container_id' => $container_id,
                'legacy_mapping' => $element->getLegacyMapping()
            ];


            $media = $mediaTable->insertOnDuplicateKey($data, $duplicate_exclude = ['legacy_mapping']);
            $media_id = $media['media_id'];

            // Step 3 : Generate media manager filename

            $mediaLocation = $this->getMediaLocation($container_id, $media_id, $filename);

            // Step 2 : Adding into filesystem
            try {
                //echo 'Writing file';
                $fs->write($mediaLocation['filename'], file_get_contents($filename), $overwrite);
            } catch (\Exception $e) {
                // If something goes wrong throw an exception
                $tm->transaction()->rollback();
                throw $e;
            }

            // @todo make a super try catch ;)
            /*
             * Relative location of file
             */
            $media['location'] = $mediaLocation['location'];
            $media->save();

            $tm->transaction()->commit();
        }

        return $media['media_id'];
    }

    /**
     * @param int $container_id
     * @param int $media_id
     * @param string $filename
     * @return array
     */
    public function getMediaLocation($container_id, $media_id, $filename)
    {
        $tm = $this->getTableManager();

        $mcTable = $tm->table('media_container');
        $container = $mcTable->find($container_id);
        if ($container === false) {
            throw new \Exception("Cannot locate container '$container_id'");
        }
        $container_folder = $container['folder'];

        if ($media_id == '') {
            throw new \Exception("Cannot create media location, media_id '$media_id' is required");
        }
        $pathinfo = pathinfo($filename);

        if ($pathinfo['extension'] == '') {
            $ext = '';
        } else {
            $ext = '.' . $pathinfo['extension'];
        }

        // Should better be handled by iconv with translate but need a dependency
        $qf = preg_replace('/[^A-Za-z0-9_\-\.]/', '_', $pathinfo['filename']);

        $media_directory = $this->getMediaDirectory($media_id);
        $media_location = $media_directory . '/' . "$media_id-" . substr($qf, 0, 40) . $ext;
        $media_filename = $container_folder . '/' . $media_location;

        $location = [
            'filename' => $media_filename,
            'location' => $media_location
        ];

        return $location;
    }

    /**
     *
     * @param int $media_id
     * @return string
     */
    protected function getMediaDirectory($media_id)
    {
        $dirs = [];
        $dirs[] = str_pad(substr($media_id, 0, 2), 2, 0, STR_PAD_LEFT);
        $dirs[] = str_pad(substr($media_id, 2, 4), 2, 0, STR_PAD_LEFT);
        $dir = implode('/', $dirs);
        return $dir;
    }

    /**
     * \MMan\Service\Storage $storage
     * @return
     */
    public function getStorage()
    {
        return $this->storage;
    }

    /**
     *
     * @param \MMan\Service\Storage $storage
     * @return \MMan\MediaManager
     */
    public function setStorage(Storage $storage)
    {
        $this->storage = $storage;
        return $this;
    }

    /**
     * @param TableManager $tm
     * @return MediaManager
     */
    public function setTableManager(TableManager $tm)
    {
        $this->tm = $tm;
        return $this;
    }

    public function getTableManager()
    {
        if ($this->tm === null) {
            throw new \Exception(__METHOD__ . " No table manager instance set");
        }
        return $this->tm;
    }
}
