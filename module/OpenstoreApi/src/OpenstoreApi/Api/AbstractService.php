<?php

namespace OpenstoreApi\Api;

use Zend\Db\Adapter\Adapter;
use Zend\Db\Sql\Select;
use Zend\Db\Sql\Sql;
use Zend\Db\Adapter\AdapterAwareInterface;
use Zend\ServiceManager\ServiceLocatorInterface;
use Zend\ServiceManager\ServiceLocatorAwareInterface;
use Soluble\FlexStore\Store;
use Soluble\FlexStore\Source\Zend\SqlSource;
use Openstore\Store\Renderer\RowPictureRenderer;
use Soluble\FlexStore\Formatter;


abstract class AbstractService implements AdapterAwareInterface, ServiceLocatorAwareInterface {

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
    function __construct(ServiceLocatorInterface $serviceLocator = null, Adapter $adapter = null) {
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
    public function setDbAdapter(Adapter $adapter) {

        $this->adapter = $adapter;
        $this->sql = new Sql($this->adapter);
        return $this;
    }

    /**
     * 
     * @return Adapter
     */
    public function getDbAdapter() {
        return $this->adapter;
    }

    /**
     * Set service locator
     *
     * @param ServiceLocatorInterface $serviceLocator
     * @return \OpenstoreApi\Api\AbstractService
     */
    public function setServiceLocator(ServiceLocatorInterface $serviceLocator) {

        $this->serviceLocator = $serviceLocator;
        return $this;
    }

    /**
     * Get service locator
     *
     * @return ServiceLocatorInterface
     */
    public function getServiceLocator() {
        return $this->serviceLocator;
    }
    
    /**
     * 
     * @param Select $select
     * @return Store
     */
    public function getStore(Select $select=null)
    {
        return new Store(new SqlSource($this->getDbAdapter(), $select));
    }
    
    
    /**
     * Initialize column model
     * @param Store $store
     * @param array $params
     * @return void
     */
    protected function initListStoreColumnModel(Store $store, array $params)
    {
        $pricelist_reference = $params['pricelist'];

        //$currency
        $localeMap = array(
            'BE' => 'fr_BE',
            'FR' => 'fr_FR',
            'GB' => 'en_GB',
            'US' => 'en_US',
            'DE' => 'de_DE',
            'NL' => 'nl_NL',
            'ES' => 'es_ES',
        );
        
        if (array_key_exists($pricelist_reference, $localeMap)) {
            $locale = $localeMap[$pricelist_reference];
        } else {
            $locale = "en_US";
        }

        $cm = $store->getColumnModel();
        
        if ($pricelist_reference != '') {
            $currency = $this->getPricelistCurrency($pricelist_reference);

            $currF = Formatter::create(
                                Formatter::FORMATTER_CURRENCY, 
                                ['currency_code' => $currency, 'decimals' => 2, 'locale' => $locale]
                            );

            $cm->search()->in(['price', 'list_price', 'public_price', 'my_price'])->setFormatter($currF);
        }
        
        $discF = Formatter::create(
                            Formatter::FORMATTER_UNIT, 
                            ['decimals' => 2, 'unit' => '%', 'locale' => $locale]
                        );
        
        $cm->search()->regexp('/^discount\_/')->setFormatter($discF);

        $intF = Formatter::create(
                            Formatter::FORMATTER_NUMBER, 
                            ['decimals' => 0, 'locale' => $locale]
                        );
        
        $cm->search()->regexp('/available_stock$/')->setFormatter($intF);
        
        $cm->search()->regexp('/^pack\_qty/')->setFormatter($intF);
        
        $cm->search()->regexp('/(length|width|height)$/')->setFormatter($intF);
        if ($cm->exists('picture_media_id')) {

            $column = new \Soluble\FlexStore\Column\Column('picture_url');
            $column->setType('string');
            $cm->add($column);

            $pictureRenderer = new RowPictureRenderer('picture_media_id', 'picture_url', '1024x768', 90);
            $cm->addRowRenderer($pictureRenderer);

            $column = new \Soluble\FlexStore\Column\Column('picture_thumbnail_url');
            $column->setType('string');
            $cm->add($column);

            $thumbRenderer = new RowPictureRenderer('picture_media_id', 'picture_thumbnail_url', '170x200', 90);
            $cm->addRowRenderer($thumbRenderer);
            
        } elseif ($cm->exists('media_id')) {
            
            $column = new \Soluble\FlexStore\Column\Column('picture_url');
            $column->setType('string');
            $cm->add($column);

            $pictureRenderer = new RowPictureRenderer('media_id', 'picture_url', '1024x768', 90);
            $cm->addRowRenderer($pictureRenderer);

            $column = new \Soluble\FlexStore\Column\Column('picture_thumbnail_url');
            $column->setType('string');
            $cm->add($column);

            $thumbRenderer = new RowPictureRenderer('media_id', 'picture_thumbnail_url', '170x200', 90);
            $cm->addRowRenderer($thumbRenderer);
            
        }
        
        
    }
    
           
    
    /**
     * 
     * @param string $pricelist_reference
     * @return string|false
     */
    protected function getPricelistCurrency($pricelist_reference)
    {
        $select = $this->sql->select();
        $select->from(array('pl' => 'pricelist'))
               ->join(array('cu' => 'currency'), 'pl.currency_id = cu.currency_id', array('reference', 'symbol'))
               ->columns(array('pricelist_id'))
               ->where(array('pl.reference' => $pricelist_reference));
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
