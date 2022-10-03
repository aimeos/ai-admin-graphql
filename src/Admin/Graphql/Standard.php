<?php

namespace Aimeos\Admin\Graphql;

use GraphQL\Type\Definition\Type;


class Standard extends Base
{
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
				'type' => $this->outputType( $domain ),
				'args' => [
					['name' => 'input', 'type' => $this->inputType( $domain ), 'description' => 'Item object'],
				],
				'resolve' => $this->saveItem( $domain ),
			],
			'save' . str_replace( '/', '', ucwords( $domain, '/' ) ) . 's' => [
				'type' => Type::listOf( $this->outputType( $domain ) ),
				'args' => [
					['name' => 'input', 'type' => Type::listOf( $this->inputType( $domain ) ), 'description' => 'Item objects'],
				],
				'resolve' => $this->saveItems( $domain ),
			]
		];
	}


	public function query( string $domain ) : array
	{
		return [
			'get' . str_replace( '/', '', ucwords( $domain, '/' ) ) => [
				'type' => $this->outputType( $domain ),
				'args' => [
					['name' => 'id', 'type' => Type::string(), 'description' => 'Unique ID'],
					['name' => 'include', 'type' => Type::listOf( Type::string() ), 'defaultValue' => [], 'description' => 'Domains to include'],
				],
				'resolve' => $this->getItem( $domain ),
			],
			'search' . str_replace( '/', '', ucwords( $domain, '/' ) ) . 's' => [
				'type' => Type::listOf( $this->outputType( $domain ) ),
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