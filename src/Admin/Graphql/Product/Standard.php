<?php

namespace Aimeos\Admin\Graphql\Product;


use GraphQL\Type\Definition\Type;
use GraphQL\Type\Definition\ObjectType;
use GraphQL\Type\Definition\ResolveInfo;


class Standard extends \Aimeos\Admin\Graphql\Base
{
	public function schema() : array
	{
		return [
			'product' => [
				'type' => $this->types( 'product' ),
				'args' => [
					['name' => 'id', 'type' => Type::string(), 'defaultValue' => '', 'description' => 'Product ID'],
					['name' => 'code', 'type' => Type::string(), 'defaultValue' => '', 'description' => 'Product code'],
				],
				'resolve' => $this->resolve(),
			],
			'products' => [
				'type' => Type::listOf( $this->types( 'products' ) ),
				'args' => [
					['name' => 'filter', 'type' => Type::string(), 'defaultValue' => '{}', 'description' => 'Filter conditions'],
					['name' => 'include', 'type' => Type::string(), 'defaultValue' => '', 'description' => 'Domains to include'],
					['name' => 'sort', 'type' => Type::string(), 'defaultValue' => '', 'description' => 'Sort keys'],
					['name' => 'offset', 'type' => Type::int(), 'defaultValue' => 0, 'description' => 'Slice offset'],
					['name' => 'limit', 'type' => Type::int(), 'defaultValue' => 100, 'description' => 'Slice size'],
				],
				'resolve' => $this->resolveList(),
			]
		];
	}


	public function resolve() : \Closure
	{
		return function( $root, $args, $context ) {

			$manager = \Aimeos\MShop::create( $this->context(), 'product' );

			if( $args['id'] ) {
				return $manager->get( $args['id'] )->toArray();
			} elseif( $args['code'] ) {
				return $manager->find( $args['code'] )->toArray();
			}

			throw new \Aimeos\Admin\Graphql\Exception( 'Missing ID or code' );
		};
	}


	public function resolveList() : \Closure
	{
		return function( $root, $args, $context ) {

			$manager = \Aimeos\MShop::create( $this->context(), 'product' );

			$filter = $manager->filter()->order( explode( ',', $args['sort'] ) )->slice( $args['offset'], $args['limit'] );
			$filter->add( $filter->parse( json_decode( $args['filter'], true ) ) );

			return $manager->search( $filter, array_filter( explode( ',', $args['include'] ) ) )->call( 'toArray' )->all();
		};
	}


	public function types( string $name ) : ObjectType
	{
		return new ObjectType( [
			'name' => $name,
			'fields' => function() {

				$attrs = \Aimeos\MShop::create( $this->context(), 'product' )->getSearchAttributes( false );
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
			'resolveField' => function( $item, $args, $context, ResolveInfo $info ) {
				return $item[rtrim( $info->parentType->name, 's' ) . '.' . $info->fieldName] ?? null;
			}
		] );
	}
}