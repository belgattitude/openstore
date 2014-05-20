<?php
/*
 * THIS SOFTWARE IS PROVIDED BY THE COPYRIGHT HOLDERS AND CONTRIBUTORS
 * "AS IS" AND ANY EXPRESS OR IMPLIED WARRANTIES, INCLUDING, BUT NOT
 * LIMITED TO, THE IMPLIED WARRANTIES OF MERCHANTABILITY AND FITNESS FOR
 * A PARTICULAR PURPOSE ARE DISCLAIMED. IN NO EVENT SHALL THE COPYRIGHT
 * OWNER OR CONTRIBUTORS BE LIABLE FOR ANY DIRECT, INDIRECT, INCIDENTAL,
 * SPECIAL, EXEMPLARY, OR CONSEQUENTIAL DAMAGES (INCLUDING, BUT NOT
 * LIMITED TO, PROCUREMENT OF SUBSTITUTE GOODS OR SERVICES; LOSS OF USE,
 * DATA, OR PROFITS; OR BUSINESS INTERRUPTION) HOWEVER CAUSED AND ON ANY
 * THEORY OF LIABILITY, WHETHER IN CONTRACT, STRICT LIABILITY, OR TORT
 * (INCLUDING NEGLIGENCE OR OTHERWISE) ARISING IN ANY WAY OUT OF THE USE
 * OF THIS SOFTWARE, EVEN IF ADVISED OF THE POSSIBILITY OF SUCH DAMAGE.
 *
 * This software consists of voluntary contributions made by many individuals
 * and is licensed under the MIT license.
 */

/**
 * Copy-paste this file to your config/autoload folder (don't forget to remove the .dist extension!)
 */

return [
    'zfc_rbac' => [
        /**
         * Key that is used to fetch the identity provider
         *
         * Please note that when an identity is found, it MUST implements the ZfcRbac\Identity\IdentityProviderInterface
         * interface, otherwise it will throw an exception.
         */
        //'identity_provider' => 'ZfcRbac\Identity\AuthenticationIdentityProvider',
		'identity_provider' => 'MyCustomAuthenticationIdentityProvider',
		/*
		'identity_provider' => function($a) { 
	
			var_dump(get_class($a));
			die();
		
		},*/
        /**
         * Set the guest role
         *
         * This role is used by the authorization service when the authentication service returns no identity
         */
        'guest_role' => 'guest',

        /**
         * Set the guards
         *
         * You must comply with the various options of guards. The format must be of the following format:
         *
         *      'guards' => [
         *          'ZfcRbac\Guard\RouteGuard' => [
         *              // options
         *          ]
         *      ]
         */
			
		'guards' => [
            'ZfcRbac\Guard\RouteGuard' => [
                'admin*' => ['admin'],
				'zfcuser' => ['guest'],
				'zfcuser/login' => ['guest'],
				'zfcuser/logout' => ['member'],
				'zfcuser/register' => ['guest'],
				//'home' => ['guest', 'member'],
				'home' => ['*'],
				'store*' => ['customer', 'admin'],
				
				//store/browse, store/search, store/product
				'shopcart*' => ['customer', 'admin'],
				'media*' => ['*'],
				// console
				'openstore-recreatedb' => ['*'],
				'openstore-updatedb' => ['*'],
				'doctrine_orm_module_yuml' => ['member'],
				'api/restful' => ['guest'],
				'zf-apigility*' => ['*'],
            ]
			
			
        ],

        /**
         * As soon as one rule for either route or controller is specified, a guard will be automatically
         * created and will start to hook into the MVC loop.
         *
         * If the protection policy is set to DENY, then any route/controller will be denied by
         * default UNLESS it is explicitly added as a rule. On the other hand, if it is set to ALLOW, then
         * not specified route/controller will be implicitly approved.
         *
         * DENY is the most secure way, but it is more work for the developer
         */
        'protection_policy' => \ZfcRbac\Guard\GuardInterface::POLICY_DENY,

        /**
         * Configuration for role provider
         *
         * It must be an array that contains configuration for the role provider. The provider config
         * must follow the following format:
         *
         *      'ZfcRbac\Role\InMemoryRoleProvider' => [
         *          'role1' => [
         *              'children'    => ['children1', 'children2'], // OPTIONAL
         *              'permissions' => ['edit', 'read'] // OPTIONAL
         *          ]
         *      ]
         *
         * Supported options depend of the role provider, so please refer to the official documentation
         */
		/*
        'role_provider' => [
            'ZfcRbac\Role\InMemoryRoleProvider' => [
                'admin' => [
                    'children'    => ['member'],
                    'permissions' => ['delete']
                ],
                'member' => [
                    'permissions' => ['edit']
                ]
            ]
        ],*/
		

		
		'role_provider' => [
            'ZfcRbac\Role\ObjectRepositoryRoleProvider' => [
                'object_manager'     => 'doctrine.entitymanager.orm_default',
                'class_name'         => 'Openstore\Entity\Role',
                'role_name_property' => 'name'
            ]
        ],		
        
        /**
         * Configure the unauthorized strategy. It is used to render a template whenever a user is unauthorized
         */
        'unauthorized_strategy' => [
            /**
             * Set the template name to render
             */
            'template' => 'error/403'
        ],

        /**
         * Configure the redirect strategy. It is used to redirect the user to another route when a user is
         * unauthorized
         */
        'redirect_strategy' => [
            /**
             * Enable redirection when the user is connected
             */
             'redirect_when_connected' => false,

            /**
             * Set the route to redirect when user is connected (of course, it must exist!)
             */
            'redirect_to_route_connected' => 'home',

            /**
             * Set the route to redirect when user is disconnected (of course, it must exist!)
             */
            'redirect_to_route_disconnected' => 'zfcuser/login',
			//'redirect_to_route_disconnected' => 'home',

            /**
             * If a user is unauthorized and redirected to another route (login, for instance), should we
             * append the previous URI (the one that was unauthorized) in the query params?
             */
            'append_previous_uri' => false,

            /**
             * If append_previous_uri option is set to true, this option set the query key to use when
             * the previous uri is appended
             */
            'previous_uri_query_key' => 'redirectTo'
        ],

        /**
         * Various plugin managers for guards and role providers. Each of them must follow a common
         * plugin manager config format, and can be used to create your custom objects
         */
        // 'guard_manager'               => [],
        // 'role_provider_manager'       => []
    ]
];
