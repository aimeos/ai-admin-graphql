<?php

/**
 * @license LGPLv3, http://opensource.org/licenses/LGPL-3.0
 * @copyright Aimeos (aimeos.org), 2022-2024
 * @package Admin
 * @subpackage GraphQL
 */


namespace Aimeos\Admin\Graphql\Service;

use GraphQL\Type\Definition\Type;


/**
 * GraphQL class for special handling of services
 *
 * @package Admin
 * @subpackage GraphQL
 */
class Standard extends \Aimeos\Admin\Graphql\Standard
{
	/**
	 * Returns GraphQL schema definition for the available queries
	 *
	 * @param string $domain Domain name of the responsible manager
	 * @return array GraphQL query schema definition
	 */
	public function query( string $domain ) : array
	{
		$list = parent::query( $domain );

		$list['findService'] = [
			'type' => $this->types()->outputType( $domain ),
			'args' => [
				['name' => 'code', 'type' => Type::string(), 'description' => 'Unique code'],
				['name' => 'include', 'type' => Type::listOf( Type::string() ), 'defaultValue' => [], 'description' => 'Domains to include'],
			],
			'resolve' => $this->findItem( $domain ),
		];

		$list['getServiceConfig'] = [
			'type' => Type::listOf( $this->types()->configOutputType( $domain ) ),
			'args' => [
				['name' => 'provider', 'type' => Type::string(), 'description' => 'Provider name with decorators separated by comma'],
				['name' => 'type', 'type' => Type::string(), 'description' => 'Provider type ("delivery" or "payment")'],
			],
			'resolve' => $this->getConfig( $domain ),
		];

		return $list;
	}


	/**
	 * Returns a closure for returning the provider configuration
	 *
	 * @param string $domain Domain path of the manager
	 * @return \Closure Anonymous method returning one item
	 */
	protected function getConfig( string $domain ) : \Closure
	{
		return function( $root, $args, $context ) use ( $domain ) {

			$context = $this->context();
			$groups = $context->config()->get( 'admin/graphql/resource/' . $domain . '/get', [] );

			if( $context->view()->access( $groups ) !== true ) {
				throw new \Aimeos\Admin\Graphql\Exception( 'Forbidden', 403 );
			}

			$manager = \Aimeos\MShop::create( $context, $domain );
			$item = $manager->create()->setProvider( $args['provider'] );

			return $manager->getProvider( $item, $args['type'] )->getConfigBE();
		};
	}
}
