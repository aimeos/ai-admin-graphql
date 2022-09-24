<?php

namespace Aimeos\Admin\Graphql\Product;


use GraphQL\Type\Definition\Type;
use GraphQL\Type\Definition\ObjectType;
use GraphQL\Type\Definition\ResolveInfo;
use GraphQL\Type\Definition\InputObjectType;


class Standard extends \Aimeos\Admin\Graphql\Base
{
	public function mutation( string $domain ) : array
	{
		return [
			'save' . ucfirst( $domain ) => [
				'type' => $this->outputType( $domain, ucfirst( $domain ) . 'Type' ),
				'args' => [
					['name' => 'input', 'type' => $this->inputType( $domain, ucfirst( $domain ) . 'Input'  ), 'description' => 'Item object'],
				],
				'resolve' => $this->saveItem( $domain ),
			],
			'save' . ucfirst( $domain ) . 's' => [
				'type' => Type::listOf( $this->outputType( $domain, ucfirst( $domain ) . 'sType' ) ),
				'args' => [
					['name' => 'input', 'type' => Type::listOf( $this->inputType( $domain, ucfirst( $domain ) . 'sInput'  ) ), 'description' => 'Item objects'],
				],
				'resolve' => $this->saveItems( $domain ),
			]
		];
	}


	public function query( string $domain ) : array
	{
		return [
			$domain => [
				'type' => $this->outputType( $domain ),
				'args' => [
					['name' => 'id', 'type' => Type::string(), 'defaultValue' => '', 'description' => 'Unique ID'],
					['name' => 'code', 'type' => Type::string(), 'defaultValue' => '', 'description' => 'Unique code'],
				],
				'resolve' => $this->queryItem( $domain ),
			],
			$domain . 's' => [
				'type' => Type::listOf( $this->outputType( $domain, $domain . 's' ) ),
				'args' => [
					['name' => 'filter', 'type' => Type::string(), 'defaultValue' => '{}', 'description' => 'Filter conditions'],
					['name' => 'include', 'type' => Type::string(), 'defaultValue' => '', 'description' => 'Domains to include'],
					['name' => 'sort', 'type' => Type::string(), 'defaultValue' => '', 'description' => 'Sort keys'],
					['name' => 'offset', 'type' => Type::int(), 'defaultValue' => 0, 'description' => 'Slice offset'],
					['name' => 'limit', 'type' => Type::int(), 'defaultValue' => 100, 'description' => 'Slice size'],
				],
				'resolve' => $this->queryItems( $domain ),
			]
		];
	}


	protected function inputType( string $domain, string $name = null ) : InputObjectType
	{
		return new InputObjectType( [
			'name' => $name ?: $domain . 'Input',
			'fields' => function() use ( $domain ) {

				$attrs = \Aimeos\MShop::create( $this->context(), $domain )->getSearchAttributes( false );
				$list = [];

				foreach( $attrs as $attr )
				{
					$list[] = [
						'name' => $this->name( $attr->getCode() ),
						'type' => $this->type( $attr->getType() ),
						'description' => $attr->getLabel()
					];
				}

				return $list;
			},
			'parseValue' => function( array $values ) use ( $domain ) {
				return $this->prefix( $domain, $values );
			}
		] );
	}


	protected function outputType( string $domain, string $name = null ) : ObjectType
	{
		return new ObjectType( [
			'name' => $name ?: $domain,
			'fields' => function() use ( $domain ) {

				$attrs = \Aimeos\MShop::create( $this->context(), $domain )->getSearchAttributes( false );
				$list = [];

				foreach( $attrs as $attr ) {
					$list[] = [
						'name' => $this->name( $attr->getCode() ),
						'type' => $this->type( $attr->getType() ),
						'description' => $attr->getLabel()
					];
				}

				return $list;
			},
			'resolveField' => function( $item, $args, $context, ResolveInfo $info ) use ( $domain ) {

				$value = $item[$domain . '.' . $info->fieldName] ?? ( $item[$info->fieldName] ?? null );
				return is_scalar( $value ) || is_null( $value ) ? $value : json_encode( $value, JSON_FORCE_OBJECT );
			}
		] );
	}


	protected function queryItem( string $domain ) : \Closure
	{
		return function( $root, $args, $context ) use ( $domain ) {

			$manager = \Aimeos\MShop::create( $this->context(), $domain );

			if( $args['id'] ) {
				return $manager->get( $args['id'] )->toArray( true );
			}

			if( $args['code'] ) {
				return $manager->find( $args['code'] )->toArray( true );
			}

			throw new \Aimeos\Admin\Graphql\Exception( 'Missing ID or code' );
		};
	}


	protected function queryItems( string $domain ) : \Closure
	{
		return function( $root, $args, $context ) use ( $domain ) {

			$manager = \Aimeos\MShop::create( $this->context(), $domain );

			$filter = $manager->filter()->order( explode( ',', $args['sort'] ) )->slice( $args['offset'], $args['limit'] );
			$filter->add( $filter->parse( json_decode( $args['filter'], true ) ) );

			return $manager->search( $filter, array_filter( explode( ',', $args['include'] ) ) )->call( 'toArray', [true] )->all();
		};
	}


	protected function saveItem( string $domain ) : \Closure
	{
		return function( $root, $args, $context ) use ( $domain ) {

			$manager = \Aimeos\MShop::create( $this->context(), $domain );

			if( !empty( $entry = $args['input'] ?? null ) )
			{
				$entry = $this->prefix( $domain, $entry );
				return $manager->save( $manager->create()->fromArray( $entry, true ) );
			}

			throw new \Aimeos\Admin\Graphql\Exception( 'Missing "input" parameter' );
		};
	}


	protected function saveItems( string $domain ) : \Closure
	{
		return function( $root, $args, $context ) use ( $domain ) {

			$manager = \Aimeos\MShop::create( $this->context(), $domain );

			if( !empty( $entries = $args['input'] ?? [] ) )
			{
				$items = [];

				foreach( $entries as $entry )
				{
					$entry = $this->prefix( $domain, $entry );
					$items[] = $manager->create()->fromArray( $entry, true );
				}

				return $manager->save( $items );
			}

			throw new \Aimeos\Admin\Graphql\Exception( 'Missing "input" parameter' );
		};
	}
}