<?php

namespace Aimeos\Admin\Graphql\Product;


use GraphQL\Type\Definition\ObjectType;
use GraphQL\Type\Definition\ResolveInfo;


class Standard extends \Aimeos\Admin\Graphql\Base
{
	public function args() : array
	{
		return [
			['name' => 'filter', 'type' => Type::string(), 'defaultValue' => '{}', 'description' => 'Filter conditions'],
			['name' => 'include', 'type' => Type::string(), 'defaultValue' => '', 'description' => 'Domains to include'],
			['name' => 'sort', 'type' => Type::string(), 'defaultValue' => '', 'description' => 'Sort keys'],
			['name' => 'offset', 'type' => Type::int(), 'defaultValue' => 0, 'description' => 'Slice offset'],
			['name' => 'limit', 'type' => Type::int(), 'defaultValue' => 100, 'description' => 'Slice size'],
		];
	}


	public function resolve() : \Closure
	{
		return function( $root, $args, $context, ResolveInfo $info ) {
			return [];
		};
	}


	public function type() : array
	{
		$list = [];
		$attrs = \Aimeos\MShop::create( $this->context(), 'product' )->getSearchAttributes( false );

		foreach( $attrs as $attr ) {
			$list[] = ['name' => $attr->getCode(), 'type' => $this->type( $attr->getType() ), 'description' => $attr->getLabel()];
		}

		return new ObjectType( [
			'name' => 'product',
			'fields' => function() use ( $registry ) {
				return $list;
			}
		] );
	}
}