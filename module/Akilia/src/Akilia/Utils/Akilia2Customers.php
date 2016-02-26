<?php

namespace Akilia\Utils;

use Zend\Db\Adapter\Adapter;
use Zend\ServiceManager\ServiceLocatorAwareInterface;
use Zend\ServiceManager\ServiceLocatorInterface;
use Zend\Db\Adapter\AdapterAwareInterface;
use Zend\Db\Sql\Select;
use Zend\Db\Sql\Where;
use Zend\Db\Sql\Expression;
use Soluble\FlexStore\Source\Zend\SqlSource;
use Soluble\FlexStore\FlexStore;

class Akilia2Customers implements ServiceLocatorAwareInterface, AdapterAwareInterface
{
    /**
     * @var ServiceLocatorInterface
     */
    protected $serviceLocator;

    /**
     *
     * @var \Zend\Db\Adapter\Adapter
     */
    protected $adapter;


    public function __construct(array $configuration)
    {
        $this->configuration = $configuration;
    }


    /**
     *
     * @param int $days_threshold days threshold
     * @param int $ca_threshold turnover threshold
     * @param int $limit
     * @return type
     */
    public function getCustomerGeo($days_threshold = 300, $ca_threshold = 1000, $min_accuracy = 6, $limit = 1000)
    {
        $akilia2db  = $this->configuration['synchronizer']['db_akilia2'];
        $select = new Select();
        $bcg = new \Zend\Db\Sql\TableIdentifier('base_customer_geo', $akilia2db);
        $bc  = new \Zend\Db\Sql\TableIdentifier('base_customer', $akilia2db);
        $bs  = new \Zend\Db\Sql\TableIdentifier('base_state', $akilia2db);
        $bco  = new \Zend\Db\Sql\TableIdentifier('base_country', $akilia2db);
        $so = new \Zend\Db\Sql\TableIdentifier('sal_order', $akilia2db);
        $sol = new \Zend\Db\Sql\TableIdentifier('sal_order_line', $akilia2db);
        $select->from(["bc" => $bc], [])
                ->join(['bcg' => $bcg], "bc.id = bcg.customer_id", [], Select::JOIN_LEFT)
                ->join(['bs' => $bs], "bs.id = bc.state_id", [], Select::JOIN_LEFT)
                ->join(['bco' => $bco], "bco.id = bc.country_id", [], Select::JOIN_LEFT)
                ->join(['so' => $so], "bc.id = so.customer_id", [], Select::JOIN_INNER)
                ->join(['sol' => $sol], "so.id = sol.order_id", [], Select::JOIN_INNER)
               ->where('bc.flag_archived <> 1');

        $columns = [
                'customer_id'    => new Expression('bc.id'),
                'name'            => new Expression('bc.name'),
                'street'        => new Expression('bc.street'),
                'street_2'        => new Expression('bc.street_2'),
                'street_number'    => new Expression('bc.street_number'),
                'state_reference'    => new Expression('bs.reference'),
                'state_name'    => new Expression('bs.name'),
                'zipcode'        => new Expression('bc.zipcode'),
                'city'            => new Expression('bc.city'),
                'country'            => new Expression('bco.name'),
                'accuracy'    => new Expression('bcg.accuracy'),
                'latitude'    => new Expression('bcg.latitude'),
                'longitude'    => new Expression('bcg.longitude'),

            ];

        $select->columns(array_merge($columns, [
            'total_net' => new Expression('sum(sol.price_total_net)')
        ]), true);


        $select->group($columns);
        $select->having("sum(sol.price_total_net) > $ca_threshold");
        $select->where(function (Where $where) use ($min_accuracy) {
            //$where->greaterThan('so.date_order', '2012-12-31');


            $where->notLike('bc.name', '%FINISHED%');
            $where->nest
                    ->lessThan('accuracy', $min_accuracy)
                    ->or
                    ->isNull('accuracy')
                   ->unnest;
        });
        $select->where(new Expression("(TO_DAYS(NOW()) - TO_DAYS(so.date_order)) < $days_threshold"));
        if ($limit > 0) {
            $select->limit($limit);
        }


        $store = $this->getStore($select);

        $data = $store->getData()->toArray();
        return $data;
    }
        /**
         *
         * @param Select $select
         * @return Store
         */
    protected function getStore(Select $select = null)
    {
        return new FlexStore(new SqlSource($this->getDbAdapter(), $select));
    }


    /**
     *
     * @param \Zend\Db\Adapter\Adapter $adapter
     */
    public function setDbAdapter(Adapter $adapter)
    {
        $this->adapter = $adapter;
        return $this;
    }


    /**
     *
     * @return Zend\Db\Adapter\Adapter
     */
    public function getDbAdapter()
    {
        return $this->adapter;
    }

    /**
     *
     * @param \Zend\ServiceManager\ServiceLocatorInterface $serviceLocator
     */
    public function setServiceLocator(ServiceLocatorInterface $serviceLocator)
    {
        $this->serviceLocator = $serviceLocator;
        return $this;
    }

    /**
     *
     * @return \Zend\ServiceManager\ServiceLocatorInterface
     */
    public function getServiceLocator()
    {
        return $this->serviceLocator;
    }
}
