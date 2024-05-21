<?php

/**
 * @license LGPLv3, http://opensource.org/licenses/LGPL-3.0
 * @copyright Aimeos (aimeos.org), 2022
 * @package Admin
 * @subpackage GraphQL
 */


namespace Aimeos\Admin\Graphql\Customer;

use GraphQL\Type\Definition\Type;


/**
 * GraphQL class for special handling of customers
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

		$list['findCustomer'] = [
			'type' => $this->types()->outputType( $domain ),
			'args' => [
				['name' => 'code', 'type' => Type::string(), 'description' => 'Unique code'],
				['name' => 'include', 'type' => Type::listOf( Type::string() ), 'defaultValue' => [], 'description' => 'Domains to include'],
			],
			'resolve' => $this->findItem( $domain ),
		];

		return $list;
	}


	/**
	 * Updates the item
	 *
	 * @param \Aimeos\MShop\Common\Manager\Iface $manager Manager object for the passed item
	 * @param \Aimeos\MShop\Common\Item\AdddressRef\Iface $item Item to update
	 * @param array $entry Associative list of key/value pairs of the item data
	 * @return \Aimeos\MShop\Common\Item\Iface Updated item
	 */
	protected function updateItem( \Aimeos\MShop\Common\Manager\Iface $manager,
		\Aimeos\MShop\Common\Item\Iface $item, array $entry ) : \Aimeos\MShop\Common\Item\Iface
	{
		$view = $this->context()->view();
		$item = $item->fromArray( $entry );

		if( $view->access( ['super', 'admin'] ) ) {
			$item->setGroups( array_unique( $entry['groups'] ?? [] ) );
		}

		if( $view->access( ['super', 'admin'] ) || $item->getId() === $this->context()->user() )
		{
			!isset( $entry['customer.password'] ) ?: $item->setPassword( $entry['customer.password'] );
			!isset( $entry['customer.code'] ) ?: $item->setCode( $entry['customer.code'] );
		}

		if( isset( $entry['address'] ) && $item instanceof \Aimeos\MShop\Common\Item\AddressRef\Iface ) {
			$item = $this->updateAddresses( $manager, $item, $entry['address'] );
		}

		if( isset( $entry['lists'] ) && $item instanceof \Aimeos\MShop\Common\Item\ListsRef\Iface ) {
			$item = $this->updateLists( $manager, $item, $entry['lists'] );
		}

		if( isset( $entry['property'] ) && $item instanceof \Aimeos\MShop\Common\Item\PropertyRef\Iface ) {
			$item = $this->updateProperties( $manager, $item, $entry['property'] );
		}

		return $item;
	}
}
