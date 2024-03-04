<?php

/**
 * @license LGPLv3, http://opensource.org/licenses/LGPL-3.0
 * @copyright Aimeos (aimeos.org), 2022-2024
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

		return $list;
	}
}
