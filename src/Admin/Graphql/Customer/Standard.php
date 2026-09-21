<?php

/**
 * @license LGPLv3, http://opensource.org/licenses/LGPL-3.0
 * @copyright Aimeos (aimeos.org), 2022-2026
 * @package Admin
 * @subpackage GraphQL
 */


namespace Aimeos\Admin\Graphql\Customer;

use GraphQL\Type\Definition\Type;
use GraphQL\Type\Definition\ObjectType;
use GraphQL\Type\Definition\ResolveInfo;
use Aimeos\MShop\Common\Item\Iface as ItemIface;


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

		$list['aggregateCustomers'] = [
			'type' => $this->types()->aggregateOutputType( $domain ),
			'args' => [
				['name' => 'key', 'type' => Type::listOf( Type::string() ), 'description' => 'Aggregation key to group results by, e.g. "customer.status"'],
				['name' => 'value', 'type' => Type::string(), 'defaultValue' => null, 'description' => 'Aggregate values from that column, e.g "customer.status" (optional, only if type is passed)'],
				['name' => 'type', 'type' => Type::string(), 'defaultValue' => null, 'description' => 'Type of aggregation like "sum" or "avg" (default: null for count)'],
				['name' => 'filter', 'type' => Type::string(), 'defaultValue' => '{}', 'description' => 'Filter conditions'],
				['name' => 'sort', 'type' => Type::listOf( Type::string() ), 'defaultValue' => [], 'description' => 'Sort keys'],
				['name' => 'limit', 'type' => Type::int(), 'defaultValue' => 10000, 'description' => 'Slice size'],
			],
			'resolve' => $this->aggregateItems( $domain ),
		];

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
	 * Recursively collect all referenced domains
	 *
	 * @param array $entry Entry or subentry with input data
	 * @param string $domain Domain of subentry
	 * @return array Array with all domains collected
	 */
	protected function getRefs( array $entry, string $domain ) : array
	{
		$ref = parent::getRefs( $entry, $domain );

		// Existing group memberships must be loaded, otherwise they are added again
		if( isset( $entry['customer.groups'] ) ) {
			$ref[] = 'group';
		}

		return $ref;
	}


	/**
	 * Updates the item
	 *
	 * @param \Aimeos\MShop\Common\Manager\Iface $manager Manager object for the passed item
	 * @param \Aimeos\MShop\Common\Item\AddressRef\Iface $item Item to update
	 * @param array $entry Associative list of key/value pairs of the item data
	 * @return \Aimeos\MShop\Common\Item\Iface Updated item
	 */
	protected function updateItem( \Aimeos\MShop\Common\Manager\Iface $manager,
		\Aimeos\MShop\Common\Item\Iface $item, array $entry ) : \Aimeos\MShop\Common\Item\Iface
	{
		$view = $this->context()->view();
		$siteId = (string) $this->context()->user()?->getSiteId();

		if( $view->access( ['super'] ) || strlen( $siteId ) > 0 && !strncmp( $item->getSiteId(), $siteId, strlen( $siteId ) ) )
		{
			$item = $this->fromArrayRef( $item, $entry, 'customer' );

			if( isset( $entry['address'] ) && $item instanceof \Aimeos\MShop\Common\Item\AddressRef\Iface ) {
				$item = $this->updateAddresses( $manager, $item, (array) $entry['address'] );
			}

			if( isset( $entry['lists'] ) && $item instanceof \Aimeos\MShop\Common\Item\ListsRef\Iface )
			{
				$lists = (array) $entry['lists'];

				// Group membership is privileged and must not be writable through nested lists by editors
				if( !$view->access( ['super', 'admin'] ) ) {
					unset( $lists['group'] );
				}

				$item = $this->updateLists( $manager, $item, $lists );
			}

			if( isset( $entry['property'] ) && $item instanceof \Aimeos\MShop\Common\Item\PropertyRef\Iface ) {
				$item = $this->updateProperties( $manager, $item, (array) $entry['property'] );
			}
		}

		return $item;
	}
}
