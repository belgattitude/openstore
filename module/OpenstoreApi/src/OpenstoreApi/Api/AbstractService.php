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
use Soluble\FlexStore\Formatter;

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
     * @return \Soluble\Flexstore\FlexStore
     */
    public function getStore(Select $select = null)
    {
        return new FlexStore(new SqlSource($this->getDbAdapter(), $select));
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

        $dimensionF = Formatter::create(
            Formatter::FORMATTER_UNIT,
            ['decimals' => 3, 'locale' => $locale, 'unit' => 'm']
        );

        $cm->search()->regexp('/(length|width|height)$/')->setFormatter($dimensionF);

        $volumeF = Formatter::create(
            Formatter::FORMATTER_UNIT,
            ['decimals' => 3, 'locale' => $locale, 'unit' => 'm³']
        );

        $cm->search()->regexp('/volume$/')->setFormatter($volumeF);


        $weightF = Formatter::create(
            Formatter::FORMATTER_UNIT,
            ['decimals' => 3, 'locale' => $locale, 'unit' => 'Kg']
        );

        $cm->search()->regexp('/weight$/')->setFormatter($weightF);
        $cm->search()->regexp('/^weight/')->setFormatter($weightF);


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
