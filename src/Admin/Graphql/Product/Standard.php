<?php

namespace Aimeos\Admin\Graphql\Product;

use GraphQL\Type\Definition\Type;


class Standard extends \Aimeos\Admin\Graphql\Base
{
	public function mutation( string $domain ) : array
	{
		return [
			'delete' . ucfirst( $domain ) => [
				'type' => Type::string(),
				'args' => [
					['name' => 'id', 'type' => Type::string(), 'description' => 'Item ID'],
				],
				'resolve' => $this->deleteItems( $domain ),
			],
			'delete' . ucfirst( $domain ) . 's' => [
				'type' => Type::listOf( Type::string() ),
				'args' => [
					['name' => 'id', 'type' => Type::listOf( Type::string() ), 'description' => 'List of item IDs'],
				],
				'resolve' => $this->deleteItems( $domain ),
			],
			'save' . ucfirst( $domain ) => [
				'type' => $this->outputType( $domain ),
				'args' => [
					['name' => 'input', 'type' => $this->inputType( $domain ), 'description' => 'Item object'],
				],
				'resolve' => $this->saveItem( $domain ),
			],
			'save' . ucfirst( $domain ) . 's' => [
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
			'get' . ucfirst( $domain ) => [
				'type' => $this->outputType( $domain ),
				'args' => [
					['name' => 'id', 'type' => Type::string(), 'defaultValue' => '', 'description' => 'Unique ID'],
				],
				'resolve' => $this->getItem( $domain ),
			],
			'find' . ucfirst( $domain ) => [
				'type' => $this->outputType( $domain ),
				'args' => [
					['name' => 'code', 'type' => Type::string(), 'defaultValue' => '', 'description' => 'Unique code'],
				],
				'resolve' => $this->findItem( $domain ),
			],
			'search' . ucfirst( $domain ) . 's' => [
				'type' => Type::listOf( $this->outputType( $domain ) ),
				'args' => [
					['name' => 'filter', 'type' => Type::string(), 'defaultValue' => '{}', 'description' => 'Filter conditions'],
					['name' => 'include', 'type' => Type::string(), 'defaultValue' => '', 'description' => 'Domains to include'],
					['name' => 'sort', 'type' => Type::string(), 'defaultValue' => '', 'description' => 'Sort keys'],
					['name' => 'offset', 'type' => Type::int(), 'defaultValue' => 0, 'description' => 'Slice offset'],
					['name' => 'limit', 'type' => Type::int(), 'defaultValue' => 100, 'description' => 'Slice size'],
				],
				'resolve' => $this->searchItems( $domain ),
			]
		];
	}
}