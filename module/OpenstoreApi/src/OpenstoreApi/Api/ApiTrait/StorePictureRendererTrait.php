<?php

namespace OpenstoreApi\Api\ApiTrait;

use Soluble\FlexStore\FlexStore;
use Soluble\FlexStore\Column\Column;
use Soluble\FlexStore\Column\ColumnType;
use Soluble\FlexStore\Column\ColumnModel;
use Openstore\Store\Renderer\RowPictureRenderer;


trait StorePictureRendererTrait
{

    /**
     * Get service locator
     *
     * @return \Zend\ServiceManager\ServiceLocatorInterface
     */
    abstract public function getServiceLocator();


    /**
     *
     * @param FlexStore $store
     * @param string $media_column column name containing the media_id
     * @param string $insert_after insert after column name
     * @param string $filemtime_column column containing the file modification time of the picture
     */
    protected function addStorePictureRenderer(FlexStore $store, $media_column, $insert_after, $filemtime_column = null)
    {
        $cm = $store->getColumnModel();

        // Adding picture urls
        if ($filemtime_column !== null) {
            if (!$cm->exists($filemtime_column)) {
                throw new \Exception(__METHOD__ . ": Filemtime column '$filemtime_column' does not exists in column model");
            }
        }

        if ($cm->exists($media_column)) {
            $column = new Column('picture_url');
            $column->setType(ColumnType::TYPE_STRING);
            $cm->add($column, $insert_after, ColumnModel::ADD_COLUMN_AFTER);


            $configuration = $this->getServiceLocator()->get('Openstore\Configuration');
            $base_url = $configuration->getConfigKey('media_library.preview.base_url');

            //'picture_media_filemtime' => new Expression('m.filemtime')

            $pictureRenderer = new RowPictureRenderer($media_column, 'picture_url', '1024x768', 95, $base_url, $filemtime_column);
            $cm->addRowRenderer($pictureRenderer);

            $column = new Column('picture_thumbnail_url');
            $column->setType(ColumnType::TYPE_STRING);
            $cm->add($column, 'picture_url', ColumnModel::ADD_COLUMN_AFTER);

            $thumbRenderer = new RowPictureRenderer($media_column, 'picture_thumbnail_url', '170x200', 95, $base_url, $filemtime_column);
            $cm->addRowRenderer($thumbRenderer);
        }
    }
}
