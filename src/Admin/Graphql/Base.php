<?php

namespace Aimeos\Admin\Graphql;

use GraphQL\Type\Definition\Type;
use GraphQL\Type\Definition\ObjectType;
use GraphQL\Type\Definition\ResolveInfo;
use GraphQL\Type\Definition\InputObjectType;
use Aimeos\MShop\Common\Item\Iface as ItemIface;


abstract class Base
{
	static private $types = [];


	public function __construct( \Aimeos\MShop\ContextIface $context )
	{
		$this->context = $context;
	}


	protected function context() : \Aimeos\MShop\ContextIface
	{
		return $this->context;
	}


	protected function deleteItems( string $domain ) : \Closure
	{
		return function( $root, $args, $context ) use ( $domain ) {
			\Aimeos\MShop::create( $this->context(), $domain )->delete( $args['id'] );
			return $args['id'];
		};
	}


	protected function getItem( string $domain ) : \Closure
	{
		return function( $root, $args, $context ) use ( $domain ) {
			return \Aimeos\MShop::create( $this->context(), $domain )->get( $args['id'], $args['include'] );
		};
	}


	protected function findItem( string $domain ) : \Closure
	{
		return function( $root, $args, $context ) use ( $domain ) {
			return \Aimeos\MShop::create( $this->context(), $domain )->find( $args['code'], $args['include'] );
		};
	}


	protected function searchItems( string $domain ) : \Closure
	{
		return function( $root, $args, $context ) use ( $domain ) {

			$manager = \Aimeos\MShop::create( $this->context(), $domain );

			$filter = $manager->filter()->order( $args['sort'] )->slice( $args['offset'], $args['limit'] );
			$filter->add( $filter->parse( json_decode( $args['filter'], true ) ) );

			return $manager->search( $filter, $args['include'] )->all();
		};
	}


	protected function saveItem( string $domain ) : \Closure
	{
		return function( $root, $args, $context ) use ( $domain ) {

			if( empty( $entry = $args['input'] ) ) {
				throw new \Aimeos\Admin\Graphql\Exception( 'Parameter "input" must not be empty' );
			}

			$manager = \Aimeos\MShop::create( $this->context(), $domain );
			$entry = $this->prefix( $domain, $entry );

			return $manager->save( $manager->create()->fromArray( $entry, true ) );
	};
	}


	protected function saveItems( string $domain ) : \Closure
	{
		return function( $root, $args, $context ) use ( $domain ) {

			if( empty( $entries = $args['input'] ) ) {
				throw new \Aimeos\Admin\Graphql\Exception( 'Parameter "input" must not be empty' );
			}

			$items = [];
			$manager = \Aimeos\MShop::create( $this->context(), $domain );

			foreach( $entries as $entry ) {
				$entry = $this->prefix( $domain, $entry );
				$items[] = $manager->create()->fromArray( $entry, true );
			}

			return $manager->save( $items );
		};
	}


	protected function inputType( string $domain ) : InputObjectType
	{
		$name = str_replace( '/', '', $domain );

		if( isset( self::$types[$name . 'Input'] ) ) {
			return self::$types[$name . 'Input'];
		}

		return self::$types[$name . 'Input'] = new InputObjectType( [
			'name' => $name . 'Input',
			'fields' => function() use ( $domain ) {

				$manager = \Aimeos\MShop::create( $this->context(), $domain );
				$list = $this->fields( $manager->getSearchAttributes( false ) );
				$item = $manager->create();

				if( $item instanceof \Aimeos\MShop\Common\Item\PropertyRef\Iface ) {
					$list['property'] = Type::listOf( $this->inputType( $domain . '/property' ) );
				}

				return $list;
			},
			'parseValue' => function( array $values ) use ( $domain ) {
				return $this->prefix( $domain, $values );
			}
		] );
	}


	protected function outputType( string $domain ) : ObjectType
	{
		$name = str_replace( '/', '', $domain );

		if( isset( self::$types[$name . 'Output'] ) ) {
			return self::$types[$name . 'Output'];
		}

		return self::$types[$name . 'Output'] = new ObjectType( [
			'name' => $name . 'Output',
			'fields' => function() use ( $domain ) {

				$manager = \Aimeos\MShop::create( $this->context(), $domain );
				$list = $this->fields( $manager->getSearchAttributes( false ) );
				$item = $manager->create();

				if( $item instanceof \Aimeos\MShop\Common\Item\Tree\Iface ) {
					$list['children'] = Type::listOf( $this->treeOutputType( $domain ) );
				}

				if( $item instanceof \Aimeos\MShop\Common\Item\AddressRef\Iface ) {
					$list['address'] = Type::listOf( $this->addressOutputType( $domain . '/address' ) );
				}

				if( $item instanceof \Aimeos\MShop\Common\Item\PropertyRef\Iface )
				{
					$list['property'] = [
						'type' => Type::listOf( $this->propertyOutputType( $domain . '/property' ) ),
						'args' => [
							'type' => Type::listOf( Type::String() ),
						],
						'resolve' => function( $item, $args ) {
							return $item->getPropertyItems( $args['type'] ?? null, false );
						}
					];
				}

				if( $item instanceof \Aimeos\MShop\Common\Item\ListsRef\Iface )
				{
					$list['lists'] = [
						'type' => $this->listsRefOutputType( $domain . '/lists' ),
						'resolve' => function( ItemIface $item, array $args ) {
							return $item;
						}
					];
				}

				return $list;
			},
			'resolveField' => function( ItemIface $item, array $args, $context, ResolveInfo $info ) use ( $domain ) {
				return $this->resolve( $item, $domain, $info->fieldName );
			}
		] );
	}


	protected function addressOutputType( string $domain ) : ObjectType
	{
		$name = str_replace( '/', '', $domain );

		if( isset( self::$types[$name . 'Output'] ) ) {
			return self::$types[$name . 'Output'];
		}

		return self::$types[$name . 'Output'] = new ObjectType( [
			'name' => $name . 'Output',
			'fields' => function() use ( $domain ) {

				$manager = \Aimeos\MShop::create( $this->context(), $domain );
				return $this->fields( $manager->getSearchAttributes( false ) );
			},
			'resolveField' => function( ItemIface $item, array $args, $context, ResolveInfo $info ) use ( $domain ) {

				if( $info->fieldName === 'address' && $item instanceof \Aimeos\MShop\Common\Item\AddressRef\Iface ) {
					return $item->getAddressItems();
				}

				return $this->resolve( $item, $domain, $info->fieldName );
			}
		] );
	}


	protected function listsOutputType( string $path, string $domain ) : ObjectType
	{
		$name = str_replace( '/', '', $path ) . $domain . 'Output';

		if( isset( self::$types[$name] ) ) {
			return self::$types[$name];
		}

		return self::$types[$name] = new ObjectType( [
			'name' => $name . 'Output',
			'fields' => function() use ( $path, $domain ) {

				$manager = \Aimeos\MShop::create( $this->context(), $path );

				$list = $this->fields( $manager->getSearchAttributes( false ) );
				$list['item'] = $this->outputType( $domain );

				return $list;
			},
			'resolveField' => function( ItemIface $item, array $args, $context, ResolveInfo $info ) use ( $path ) {

				if( $info->fieldName === 'item' && $item instanceof \Aimeos\MShop\Common\Item\Lists\Iface ) {
					return $item->getRefItem();
				}

				return $this->resolve( $item, $path, $info->fieldName );
			}
		] );
	}


	protected function listsRefOutputType( string $path ) : ObjectType
	{
		$name = str_replace( '/', '', $path ) . 'refOutput';

		if( isset( self::$types[$name] ) ) {
			return self::$types[$name];
		}

		return self::$types[$name] = new ObjectType( [
			'name' => $name,
			'fields' => function() use ( $path ) {

				if( $domains = $this->context()->config()->get( 'admin/graphql/lists-domains', [] ) )
				{
					foreach( $domains as $domain )
					{
						$list[$domain] = [
							'type' => Type::listOf( $this->listsOutputType( $path, $domain ) ),
							'args' => [
								'listtype' => Type::listOf( Type::String() ),
								'type' => Type::listOf( Type::String() ),
							],
							'resolve' => function( $item, $args ) use ( $domain ) {
								return $item->getListItems( $domain, $args['listtype'] ?? null, $args['type'] ?? null, false );
							}
						];
					}
				}

				return $list;
			},
		] );
	}


	protected function propertyInputType( string $domain ) : ObjectType
	{
		$name = str_replace( '/', '', $domain );

		if( isset( self::$types[$name . 'Input'] ) ) {
			return self::$types[$name . 'Input'];
		}

		return self::$types[$name . 'Input'] = new ObjectType( [
			'name' => $name . 'Input',
			'fields' => function() use ( $domain ) {

				$manager = \Aimeos\MShop::create( $this->context(), $domain );
				return $this->fields( $manager->getSearchAttributes( false ) );
			}
		] );
	}


	protected function propertyOutputType( string $domain ) : ObjectType
	{
		$name = str_replace( '/', '', $domain );

		if( isset( self::$types[$name . 'Output'] ) ) {
			return self::$types[$name . 'Output'];
		}

		return self::$types[$name . 'Output'] = new ObjectType( [
			'name' => $name . 'Output',
			'fields' => function() use ( $domain ) {

				$manager = \Aimeos\MShop::create( $this->context(), $domain );
				return $this->fields( $manager->getSearchAttributes( false ) );
			},
			'resolveField' => function( ItemIface $item, array $args, $context, ResolveInfo $info ) use ( $domain ) {

				if( $info->fieldName === 'property' && $item instanceof \Aimeos\MShop\Common\Item\PropertyRef\Iface ) {
					return $item->getPropertyItems();
				}

				return $this->resolve( $item, $domain, $info->fieldName );
			}
		] );
	}


	protected function treeOutputType( string $domain ) : ObjectType
	{
		$name = str_replace( '/', '', $domain );

		if( isset( self::$types[$name . 'Output'] ) ) {
			return self::$types[$name . 'Output'];
		}

		return self::$types[$name . 'Output'] = new ObjectType( [
			'name' => $name . 'Output',
			'fields' => function() use ( $domain ) {

				$manager = \Aimeos\MShop::create( $this->context(), $domain );
				$list = $this->fields( $manager->getSearchAttributes( false ) );
				$item = $manager->create();

				if( $item instanceof \Aimeos\MShop\Common\Item\Tree\Iface ) {
					$list['children'] = Type::listOf( $this->treeOutputType( $domain ) );
				}

				return $list;
			},
			'resolveField' => function( ItemIface $item, array $args, $context, ResolveInfo $info ) use ( $domain ) {

				if( $info->fieldName === 'children' && $item instanceof \Aimeos\MShop\Common\Item\Tree\Iface ) {
					return $item->getChildren();
				}

				return $this->resolve( $item, $domain, $info->fieldName );
			}
		] );
	}


	protected function fields( array $attrs ) : array
	{
		$list = [];

		foreach( $attrs as $attr )
		{
			if( strpos( $attr->getCode(), ':' ) === false )
			{
				$code = $this->name( $attr->getCode() );

				$list[$code] = [
					'name' => $code,
					'description' => $attr->getLabel(),
					'type' => $code !== 'id' ? $this->type( $attr->getType() ) : Type::String(),
				];
			}
		}

		return $list;
	}


	protected function name( string $value ) : string
	{
		$pos = strrpos( $value, '.' );
		return substr( $value, $pos ? $pos + 1 : 0 );
	}


	protected function prefix( string $domain, array $entry ) : array
	{
		$map = [];

		foreach( $entry as $key => $value ) {
			$map[$domain . '.' . $key] = $value;
		}

		return $map;
	}


	protected function resolve( ItemIface $item, string $domain, string $name ) : ?string
	{
		$value = $item->get( str_replace( '/', '.', $domain ) . '.' . $name ) ?: $item->get( $name );
		return is_scalar( $value ) || is_null( $value ) ? $value : json_encode( $value, JSON_FORCE_OBJECT );
	}


	protected function type( string $value ) : Type
	{
		switch( $value )
		{
			case 'boolean': return Type::boolean();
			case 'float': return Type::float();
			case 'integer': return Type::int();
		}

		return Type::string();
	}
}