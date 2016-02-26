<?php

namespace OpenstoreApi\Api;

use Zend\Db\Adapter\Adapter;
use Zend\Db\Sql\Select;
use Zend\Db\Sql\Sql;
use Zend\Db\Adapter\AdapterAwareInterface;
use Zend\ServiceManager\ServiceLocatorInterface;
use Zend\ServiceManager\ServiceLocatorAwareInterface;
use Soluble\FlexStore\FlexStore;
use Soluble\FlexStore\Source\Zend\SqlSource;
use Openstore\Store\Renderer\RowPictureRenderer;
use Openstore\Store\Renderer\DateMinRenderer;
use Soluble\FlexStore\Formatter;
use Soluble\FlexStore\Column\Column;
use Soluble\FlexStore\Column\ColumnModel;
use Soluble\FlexStore\Column\ColumnType;

abstract class AbstractService implements AdapterAwareInterface, ServiceLocatorAwareInterface
{
    /**
     * @var ServiceLocatorInterface
     */
    protected $serviceLocator;

    /**
     *
     * @var Adapter
     */
    protected $adapter;

    /**
     *
     * @var Sql
     */
    protected $sql;

    /**
     *
     * @param ServiceLocatorInterface $serviceLocator
     * @param Adapter $adapter
     */
    public function __construct(ServiceLocatorInterface $serviceLocator = null, Adapter $adapter = null)
    {
        if ($serviceLocator !== null) {
            $this->setServiceLocator($serviceLocator);
        }
        if ($adapter !== null) {
            $this->setDbAdapter($adapter);
        }
    }

    /**
     * Set db adapter
     *
     * @param Adapter $adapter
     * @return \OpenstoreApi\Api\AbstractService
     */
    public function setDbAdapter(Adapter $adapter)
    {
        $this->adapter = $adapter;
        $this->sql = new Sql($this->adapter);
        return $this;
    }

    /**
     *
     * @return Adapter
     */
    public function getDbAdapter()
    {
        return $this->adapter;
    }

    /**
     * Set service locator
     *
     * @param ServiceLocatorInterface $serviceLocator
     * @return \OpenstoreApi\Api\AbstractService
     */
    public function setServiceLocator(ServiceLocatorInterface $serviceLocator)
    {
        $this->serviceLocator = $serviceLocator;
        return $this;
    }

    /**
     * Get service locator
     *
     * @return ServiceLocatorInterface
     */
    public function getServiceLocator()
    {
        return $this->serviceLocator;
    }

    /**
     *
     * @param Select $select
     * @return Store
     */
    public function getStore(Select $select = null)
    {
        return new FlexStore(new SqlSource($this->getDbAdapter(), $select));
    }


    /**
     * Prevent next_available_stock_at to be in the past
     *
     * @param FlexStore $store
     */
    protected function addNextAvailableStockAtRenderer(FlexStore $store, $date_column = 'next_available_stock_at')
    {
        $cm = $store->getColumnModel();

        if ($cm->exists($date_column)) {
            //$col = $cm->get($date_column);
            $dateMinRenderer = new DateMinRenderer($date_column);

            $cm->addRowRenderer($dateMinRenderer);
        }
    }

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


    /**
     * Initialize column model
     * @param FlexStore $store
     * @param array $params
     * @return void
     */
    protected function initStoreFormatters(FlexStore $store, array $params)
    {
        $pricelist_reference = isset($params['pricelist']) ? $params['pricelist'] : '';
        $customer_id = isset($params['customer_id']) ? $params['customer_id'] : null;

        //$currency
        $localeMap = [
            'BE' => 'fr_BE',
            'FR' => 'fr_FR',
            'GB' => 'en_GB',
            'US' => 'en_US',
            'DE' => 'de_DE',
            'NL' => 'nl_NL',
            'ES' => 'es_ES',
        ];

        if (array_key_exists($pricelist_reference, $localeMap)) {
            $locale = $localeMap[$pricelist_reference];
        } else {
            $locale = "en_US";
        }

        $cm = $store->getColumnModel();

        // Common formatters
        if ($pricelist_reference != '') {
            $currency = $this->getPricelistCurrency($pricelist_reference);

            $currF = Formatter::create(
                Formatter::FORMATTER_CURRENCY,
                ['currency_code' => $currency, 'decimals' => 2, 'locale' => $locale]
            );

            $cm->search()->in(['price', 'map_price', 'list_price', 'public_price', 'my_price'])->setFormatter($currF);
        }

        $discF = Formatter::create(
            Formatter::FORMATTER_UNIT,
            ['decimals' => 2, 'unit' => '%', 'locale' => $locale]
        );

        $cm->search()->regexp('/^discount\_/')->setFormatter($discF);
        $cm->search()->regexp('/my_discount\_/')->setFormatter($discF);

        $intF = Formatter::create(
            Formatter::FORMATTER_NUMBER,
            ['decimals' => 0, 'locale' => $locale]
        );

        $cm->search()->regexp('/available_stock$/')->setFormatter($intF);

        $cm->search()->regexp('/^pack\_qty/')->setFormatter($intF);

        $cm->search()->regexp('/(length|width|height)$/')->setFormatter($intF);
    }



    /**
     *
     * @param string $pricelist_reference
     * @return string|false
     */
    protected function getPricelistCurrency($pricelist_reference)
    {
        $select = $this->sql->select();
        $select->from(['pl' => 'pricelist'])
               ->join(['cu' => 'currency'], 'pl.currency_id = cu.currency_id', ['reference', 'symbol'])
               ->columns(['pricelist_id'])
               ->where(['pl.reference' => $pricelist_reference]);
        //$str = $this->sql->getSqlStringForSqlObject($select);
        //var_dump($str);
        //die();
        $sql_string = $this->sql->getSqlStringForSqlObject($select);

        $result = $this->adapter->query($sql_string, Adapter::QUERY_MODE_EXECUTE);
        if ($result->count() == 0) {
            return false;
        }
        $currency_reference =  $result->current()['reference'];
        return $currency_reference;
    }
}
