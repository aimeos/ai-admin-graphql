<?php

/**
 * @license LGPLv3, http://opensource.org/licenses/LGPL-3.0
 * @copyright Aimeos (aimeos.org), 2024
 * @package Admin
 * @subpackage GraphQL
 */


namespace Aimeos\Admin\Graphql\Order;

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
	 * Returns GraphQL schema definition for the available mutations
	 *
	 * @param string $domain Domain name of the responsible manager
	 * @return array GraphQL mutation schema definition
	 */
	public function mutation( string $domain ) : array
	{
		return [
			'delete' . str_replace( '/', '', ucwords( $domain, '/' ) ) => [
				'type' => Type::string(),
				'args' => [
					['name' => 'id', 'type' => Type::string(), 'description' => 'Item ID'],
				],
				'resolve' => $this->deleteItems( $domain ),
			],
			'delete' . str_replace( '/', '', ucwords( $domain, '/' ) ) . 's' => [
				'type' => Type::listOf( Type::string() ),
				'args' => [
					['name' => 'id', 'type' => Type::listOf( Type::string() ), 'description' => 'List of item IDs'],
				],
				'resolve' => $this->deleteItems( $domain ),
			],
			'save' . str_replace( '/', '', ucwords( $domain, '/' ) ) => [
				'type' => $this->types()->orderOutputType( $domain ),
				'args' => [
					['name' => 'input', 'type' => $this->types()->inputType( $domain ), 'description' => 'Item object'],
				],
				'resolve' => $this->saveItem( $domain ),
			],
			'save' . str_replace( '/', '', ucwords( $domain, '/' ) ) . 's' => [
				'type' => Type::listOf( $this->types()->orderOutputType( $domain ) ),
				'args' => [
					['name' => 'input', 'type' => Type::listOf( $this->types()->inputType( $domain ) ), 'description' => 'Item objects'],
				],
				'resolve' => $this->saveItems( $domain ),
			]
		];
	}


	/**
	 * Returns GraphQL schema definition for the available queries
	 *
	 * @param string $domain Domain name of the responsible manager
	 * @return array GraphQL query schema definition
	 */
	public function query( string $domain ) : array
	{
		return [
			'aggregate' . str_replace( '/', '', ucwords( $domain, '/' ) ) . 's' => [
				'type' => $this->types()->aggregateOutputType( $domain ),
				'args' => [
					['name' => 'key', 'type' => Type::listOf( Type::string() ), 'description' => 'Aggregation key to group results by, e.g. ["order.status", "order.price"]'],
					['name' => 'value', 'type' => Type::string(), 'defaultValue' => null, 'description' => 'Aggregate values from that column, e.g "order.price" (optional, only if type is passed)'],
					['name' => 'type', 'type' => Type::string(), 'defaultValue' => null, 'description' => 'Type of aggregation like "sum" or "avg" (default: null for count)'],
					['name' => 'filter', 'type' => Type::string(), 'defaultValue' => '{}', 'description' => 'Filter conditions'],
					['name' => 'sort', 'type' => Type::listOf( Type::string() ), 'defaultValue' => [], 'description' => 'Sort keys'],
					['name' => 'limit', 'type' => Type::int(), 'defaultValue' => 10000, 'description' => 'Slice size'],
				],
				'resolve' => $this->aggregateItems( $domain ),
			],
			'get' . str_replace( '/', '', ucwords( $domain, '/' ) ) => [
				'type' => $this->types()->orderOutputType( $domain ),
				'args' => [
					['name' => 'id', 'type' => Type::string(), 'description' => 'Unique ID'],
					['name' => 'include', 'type' => Type::listOf( Type::string() ), 'defaultValue' => [], 'description' => 'Domains to include'],
				],
				'resolve' => $this->getItem( $domain ),
			],
			'search' . str_replace( '/', '', ucwords( $domain, '/' ) ) . 's' => [
				'type' => $this->types()->searchOutputType( $domain, 'orderOutputType' ),
				'args' => [
					['name' => 'filter', 'type' => Type::string(), 'defaultValue' => '{}', 'description' => 'Filter conditions'],
					['name' => 'include', 'type' => Type::listOf( Type::string() ), 'defaultValue' => [], 'description' => 'Domains to include'],
					['name' => 'sort', 'type' => Type::listOf( Type::string() ), 'defaultValue' => [], 'description' => 'Sort keys'],
					['name' => 'offset', 'type' => Type::int(), 'defaultValue' => 0, 'description' => 'Slice offset'],
					['name' => 'limit', 'type' => Type::int(), 'defaultValue' => 100, 'description' => 'Slice size'],
				],
				'resolve' => $this->searchItems( $domain ),
			]
		];
	}
}
