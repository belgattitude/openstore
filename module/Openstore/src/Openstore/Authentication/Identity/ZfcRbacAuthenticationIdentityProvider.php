<?php

namespace Openstore\Authentication\Identity;

use Zend\Authentication\AuthenticationService;
use ZfcRbac\Exception;
use ZfcRbac\Identity\IdentityProviderInterface;
use Doctrine\ORM\EntityManager;

class ZfcRbacAuthenticationIdentityProvider implements IdentityProviderInterface
{
    /**
     * @var AuthenticationService
     */
    protected $authenticationService;


    /**
     *
     * @var Doctrine\ORM\EntityManager
     */
    protected $em;
    /**
     * Constructor
     *
     * @param AuthenticationService $authenticationService
     */
    public function __construct(AuthenticationService $authenticationService, EntityManager $em)
    {
        $this->authenticationService = $authenticationService;
        $this->em = $em;
    }

    /**
     * {@inheritDoc}
     */
    public function getIdentity()
    {
        $identity = $this->authenticationService->getIdentity();

        if ($identity !== null) {
            $user_id = $identity->getId();
            $user =  $this->em->find('Openstore\Entity\User', $user_id);

            return $user;
        } else {
            return null;
        }
    }
}
