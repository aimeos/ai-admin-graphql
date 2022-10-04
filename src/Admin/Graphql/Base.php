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

			$ref = array_keys( $entry['lists'] ?? [] );
			$manager = \Aimeos\MShop::create( $this->context(), $domain );

			if( $entry[$domain . '.id'] ) {
				$item = $manager->get( $entry[$domain . '.id'], $ref );
			} else {
				$item = $manager->create();
			}

			return $manager->save( $this->updateItem( $manager, $item, $entry ) );
	};
	}


	protected function saveItems( string $domain ) : \Closure
	{
		return function( $root, $args, $context ) use ( $domain ) {

			if( empty( $entries = $args['input'] ) ) {
				throw new \Aimeos\Admin\Graphql\Exception( 'Parameter "input" must not be empty' );
			}

			$manager = \Aimeos\MShop::create( $this->context(), $domain );

			$ids = array_column( $entries, $domain . '.id' );
			$filter = $manager->filter()->add( $domain . '.id', '==', $ids )->slice( 0, count( $entries ) );

			$products = $manager->search( $filter, array_keys( $entry['lists'] ?? [] ) );

			$items = [];
			foreach( $entries as $entry )
			{
				$item = $products->get( $entry[$domain . '.id'] ) ?: $manager->create();
				$items[] = $this->updateItem( $manager, $item, $entry );
			}

			return $manager->save( $items );
		};
	}


	protected function inputType( string $path ) : InputObjectType
	{
		$name = str_replace( '/', '', $path ) . 'Input';

		if( isset( self::$types[$name] ) ) {
			return self::$types[$name];
		}

		return self::$types[$name] = new InputObjectType( [
			'name' => $name,
			'fields' => function() use ( $path ) {

				$manager = \Aimeos\MShop::create( $this->context(), $path );
				$list = $this->fields( $manager->getSearchAttributes( false ) );
				$item = $manager->create();

				if( $item instanceof \Aimeos\MShop\Common\Item\PropertyRef\Iface ) {
					$list['property'] = Type::listOf( $this->inputType( $path . '/property' ) );
				}

				if( $item instanceof \Aimeos\MShop\Common\Item\ListsRef\Iface ) {
					$list['lists'] = $this->listsRefInputType( $path . '/lists' );
				}

				return $list;
			},
			'parseValue' => function( array $values ) use ( $path ) {
				return $this->prefix( $path, $values );
			}
		] );
	}


	protected function listsRefInputType( string $path ) : InputObjectType
	{
		$name = str_replace( '/', '', $path ) . 'refInput';

		if( isset( self::$types[$name] ) ) {
			return self::$types[$name];
		}

		return self::$types[$name] = new InputObjectType( [
			'name' => $name,
			'fields' => function() use ( $path ) {

				if( $domains = $this->context()->config()->get( 'admin/graphql/lists-domains', [] ) )
				{
					foreach( $domains as $domain ) {
						$list[$domain] = Type::listOf( $this->listsInputType( $path, $domain ) );
					}
				}

				return $list;
			}
		] );
	}


	protected function listsInputType( string $path, string $domain ) : InputObjectType
	{
		$name = str_replace( '/', '', $path ) . $domain . 'Input';

		if( isset( self::$types[$name] ) ) {
			return self::$types[$name];
		}

		return self::$types[$name] = new InputObjectType( [
			'name' => $name,
			'fields' => function() use ( $path, $domain ) {

				$manager = \Aimeos\MShop::create( $this->context(), $path );

				$list = $this->fields( $manager->getSearchAttributes( false ) );
				$list['item'] = $this->inputType( $domain );

				return $list;
			},
			'parseValue' => function( array $values ) use ( $path ) {
				return $this->prefix( $path, $values );
			}
		] );
	}


	protected function outputType( string $domain ) : ObjectType
	{
		$name = str_replace( '/', '', $domain ) . 'Output';

		if( isset( self::$types[$name] ) ) {
			return self::$types[$name];
		}

		return self::$types[$name] = new ObjectType( [
			'name' => $name,
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
		$name = str_replace( '/', '', $domain ) . 'Output';

		if( isset( self::$types[$name] ) ) {
			return self::$types[$name];
		}

		return self::$types[$name] = new ObjectType( [
			'name' => $name,
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
			'name' => $name,
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
		$name = str_replace( '/', '', $domain ) . 'Input';

		if( isset( self::$types[$name] ) ) {
			return self::$types[$name];
		}

		return self::$types[$name] = new ObjectType( [
			'name' => $name,
			'fields' => function() use ( $domain ) {

				$manager = \Aimeos\MShop::create( $this->context(), $domain );
				return $this->fields( $manager->getSearchAttributes( false ) );
			}
		] );
	}


	protected function propertyOutputType( string $domain ) : ObjectType
	{
		$name = str_replace( '/', '', $domain ) . 'Output';

		if( isset( self::$types[$name] ) ) {
			return self::$types[$name];
		}

		return self::$types[$name] = new ObjectType( [
			'name' => $name,
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
		$name = str_replace( '/', '', $domain ) . 'Output';

		if( isset( self::$types[$name] ) ) {
			return self::$types[$name];
		}

		return self::$types[$name] = new ObjectType( [
			'name' => $name,
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
		$domain = str_replace( '/', '.', $domain );

		foreach( $entry as $key => $value )
		{
			if( !in_array( $key, ['property', 'lists', 'item'] ) ) {
				$map[$domain . '.' . $key] = $value;
			} else {
				$map[$key] = $value;
			}
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


	protected function updateAddresses( \Aimeos\MShop\Common\Manager\Iface $manager,
		\Aimeos\MShop\Common\Item\AdddressRef\Iface $item, array $entries ) : \Aimeos\MShop\Common\Item\Iface
	{
		$addressItems = $item->getAddresses()->reverse();

		foreach( $entries as $subentry )
		{
			$address = $addressItems->pop() ?: $manager->createAddressItem();
			$item->addAddressItem( $address->fromArray( $subentry ) );
		}

		return $item->deleteAddressItems( $addressItems );
	}


	protected function updateItem( \Aimeos\MShop\Common\Manager\Iface $manager,
		\Aimeos\MShop\Common\Item\Iface $item, array $entry ) : \Aimeos\MShop\Common\Item\Iface
	{
		$item = $item->fromArray( $entry, true );

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


	protected function updateLists( \Aimeos\MShop\Common\Manager\Iface $manager,
		\Aimeos\MShop\Common\Item\ListsRef\Iface $item, array $entries ) : \Aimeos\MShop\Common\Item\Iface
	{
		foreach( $entries as $domain => $list )
		{
			$domainManager = \Aimeos\MShop::create( $this->context(), $domain );
			$listItems = $item->getListItems( $domain )->reverse();

			foreach( $list as $subentry )
			{
				$listItem = $listItems->pop() ?: $manager->createListItem();
				$refItem = isset( $subentry['item'] ) ? $domainManager->create()->fromArray( $subentry['item'] ) : null;

				if( isset( $subentry['item']['address'] ) && $refItem instanceof \Aimeos\MShop\Common\Item\AddressRef\Iface ) {
					$refItem = $this->updateAddresses( $domainManager, $refItem, $subentry['item']['address'] );
				}

				if( isset( $subentry['item']['lists'] ) && $refItem instanceof \Aimeos\MShop\Common\Item\ListsRef\Iface ) {
					$refItem = $this->updateLists( $domainManager, $refItem, $subentry['item']['lists'] );
				}

				if( isset( $subentry['item']['property'] ) && $refItem instanceof \Aimeos\MShop\Common\Item\PropertyRef\Iface ) {
					$refItem = $this->updateProperties( $domainManager, $refItem, $subentry['item']['property'] );
				}

				$item->addListItem( $domain, $listItem->fromArray( $subentry ), $refItem );
			}

			$item->deleteListItems( $listItems );
		}

		return $item;
	}


	protected function updateProperties( \Aimeos\MShop\Common\Manager\Iface $manager,
		\Aimeos\MShop\Common\Item\PropertyRef\Iface $item, array $entries ) : \Aimeos\MShop\Common\Item\Iface
	{
		$propItems = $item->getPropertyItems()->reverse();

		foreach( $entries as $subentry )
		{
			$propItem = $propItems->pop() ?: $manager->createPropertyItem();
			$item->addPropertyItem( $propItem->fromArray( $subentry ) );
		}

		return $item->deletePropertyItems( $propItems );
	}
}