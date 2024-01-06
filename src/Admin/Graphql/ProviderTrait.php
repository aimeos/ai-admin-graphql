<?php

/**
 * @license LGPLv3, http://opensource.org/licenses/LGPL-3.0
 * @copyright Aimeos (aimeos.org)2023-2024
 * @package Admin
 * @subpackage GraphQL
 */


namespace Aimeos\Admin\Graphql;

use GraphQL\Type\Definition\Type;


/**
 * Trait providing the methods for retrieving provider configuration
 *
 * @package Admin
 * @subpackage GraphQL
 */
trait ProviderTrait
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

		$list['get' . str_replace( '/', '', ucwords( $domain, '/' ) ) . 'Config'] = [
			'type' => Type::listOf( $this->types()->configOutputType( $domain ) ),
			'args' => [
				['name' => 'provider', 'type' => Type::string(), 'description' => 'Provider name with decorators separated by comma'],
				['name' => 'type', 'type' => Type::string(), 'description' => 'Provider type'],
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
