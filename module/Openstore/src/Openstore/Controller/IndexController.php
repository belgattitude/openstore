<?php
namespace Openstore\Controller;

use Zend\Mvc\Controller\AbstractActionController;
use Zend\View\Model\ViewModel;

class IndexController extends AbstractActionController
{
    /**
     *
     * @var Openstore\Service
     */
    protected $service;

    public function onDispatch(\Zend\Mvc\MvcEvent $e)
    {
        parent::onDispatch($e);
    }

    /**
     *
     * @return \Doctrine\ORM\EntityManager
     */
    protected function getEntityManager()
    {
        return $this->getServiceLocator()->get('Doctrine\ORM\EntityManager');
    }

    public function indexAction()
    {
        /*
        $em = $this->getEntityManager();
        $user = $em->find('OpenstoreSchema\Core\Entity\User', 1);
        var_dump(get_class($user));
        var_dump($user->getEmail());
        var_dump(gettype($user->getRoles()));
        var_dump($user->getRoles()[0]->getName());
        die('cool');
        $service = $this->getServiceLocator()->get('Openstore\Service');

        $userContext = $this->getServiceLocator()->get('Openstore\UserContext');
*/
        /*
        $capabilities = $this->getServiceLocator()->get('Openstore\UserCapabilities');
//		echo '<pre>';
        var_dump($capabilities->getPricelists());
        var_dump($capabilities->getCustomers());

        */
        /*
        echo '<pre>';
        var_dump($_SESSION);
        die();
        */
        $view = new ViewModel();

        $view->test        = 'hello';

        /*
        echo '<pre>';
        var_dump(unserialize(file_get_contents('/tmp/aaaa.txt')));
        echo '</pre>';
        */
        return $view;
    }


    public function createAction()
    {
        die('cool');
    }
}
