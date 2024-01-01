<?php

/**
 * @license LGPLv3, http://opensource.org/licenses/LGPL-3.0
 * @copyright Aimeos (aimeos.org), 2022-2023
 * @package Admin
 * @subpackage GraphQL
 */


namespace Aimeos\Admin\Graphql;

use GraphQL\Type\Definition\Type;
use GraphQL\Type\Definition\ObjectType;
use GraphQL\Type\Definition\ResolveInfo;
use GraphQL\Type\Definition\InputObjectType;
use Aimeos\MShop\Common\Item\Iface as ItemIface;


/**
 * Type registry for defining the GraphQL types
 *
 * @package Admin
 * @subpackage GraphQL
 */
class Registry
{
	private \Aimeos\MShop\ContextIface $context;
	private array $types = [];


	/**
	 * Initializes the object
	 *
	 * @param \Aimeos\MShop\ContextIface $context Context object
	 */
	public function __construct( \Aimeos\MShop\ContextIface $context )
	{
		$this->context = $context;
	}


	/**
	 * Defines the GraphQL input types
	 *
	 * @param string $path Path of the domain manager
	 * @return \GraphQL\Type\Definition\InputObjectType Input type definition
	 */
	public function inputType( string $path ) : InputObjectType
	{
		$name = str_replace( '/', '', $path ) . 'Input';

		if( isset( $this->types[$name] ) ) {
			return $this->types[$name];
		}

		return $this->types[$name] = new InputObjectType( [
			'name' => $name,
			'fields' => function() use ( $path ) {

				$manager = \Aimeos\MShop::create( $this->context, $path );
				$list = $this->fields( $manager->getSearchAttributes( false ) );
				$item = $manager->create();

				if( $item instanceof \Aimeos\MShop\Common\Item\AddressRef\Iface ) {
					$list['lists'] = $this->addressInputType( $path . '/address' );
				}

				if( $item instanceof \Aimeos\MShop\Common\Item\ListsRef\Iface ) {
					$list['lists'] = $this->listsInputType( $path . '/lists' );
				}

				if( $item instanceof \Aimeos\MShop\Common\Item\PropertyRef\Iface ) {
					$list['property'] = Type::listOf( $this->inputType( $path . '/property' ) );
				}

				return $list;
			},
			'parseValue' => function( array $values ) use ( $path ) {
				return $this->prefix( $path, $values );
			}
		] );
	}


	/**
	 * Defines the GraphQL address input type
	 *
	 * @param string $path Path of the domain manager
	 * @return \GraphQL\Type\Definition\InputObjectType Input type definition
	 */
	public function addressInputType( string $path ) : InputObjectType
	{
		$name = str_replace( '/', '', $path ) . 'Input';

		if( isset( $this->types[$name] ) ) {
			return $this->types[$name];
		}

		return $this->types[$name] = new InputObjectType( [
			'name' => $name,
			'fields' => function() use ( $path ) {

				$manager = \Aimeos\MShop::create( $this->context, $path );
				return $this->fields( $manager->getSearchAttributes( false ) );
			},
			'parseValue' => function( array $values ) use ( $path ) {
				return $this->prefix( $path, $values );
			}
		] );
	}


	/**
	 * Defines the GraphQL lists input type
	 *
	 * @param string $path Path of the domain manager
	 * @return \GraphQL\Type\Definition\InputObjectType Input type definition
	 */
	public function listsInputType( string $path ) : InputObjectType
	{
		$name = str_replace( '/', '', $path ) . 'refInput';

		if( isset( $this->types[$name] ) ) {
			return $this->types[$name];
		}

		return $this->types[$name] = new InputObjectType( [
			'name' => $name,
			'fields' => function() use ( $path ) {

				if( $domains = $this->context->config()->get( 'admin/graphql/lists-domains', [] ) )
				{
					foreach( $domains as $domain ) {
						$list[str_replace( '/', '', $domain )] = Type::listOf( $this->listsRefInputType( $path, $domain ) );
					}
				}

				return $list;
			}
		] );
	}


	/**
	 * Defines the GraphQL lists input types referenced by lists
	 *
	 * @param string $path Path of the domain manager
	 * @param string $domain Domain name of the referenced item
	 * @return \GraphQL\Type\Definition\InputObjectType Input type definition
	 */
	public function listsRefInputType( string $path, string $domain ) : InputObjectType
	{
		$name = str_replace( '/', '', $path . $domain ) . 'Input';

		if( isset( $this->types[$name] ) ) {
			return $this->types[$name];
		}

		return $this->types[$name] = new InputObjectType( [
			'name' => $name,
			'fields' => function() use ( $path, $domain ) {

				$manager = \Aimeos\MShop::create( $this->context, $path );

				$list = $this->fields( $manager->getSearchAttributes( false ) );
				$list['item'] = $this->inputType( $domain );

				return $list;
			},
			'parseValue' => function( array $values ) use ( $path ) {
				return $this->prefix( $path, $values );
			}
		] );
	}


	/**
	 * Defines the GraphQL property input type
	 *
	 * @param string $path Path of the domain manager
	 * @return \GraphQL\Type\Definition\InputObjectType Input type definition
	 */
	public function propertyInputType( string $domain ) : ObjectType
	{
		$name = str_replace( '/', '', $domain ) . 'Input';

		if( isset( $this->types[$name] ) ) {
			return $this->types[$name];
		}

		return $this->types[$name] = new ObjectType( [
			'name' => $name,
			'fields' => function() use ( $domain ) {

				$manager = \Aimeos\MShop::create( $this->context, $domain );
				return $this->fields( $manager->getSearchAttributes( false ) );
			}
		] );
	}


	/**
	 * Defines the GraphQL output types
	 *
	 * @param string $path Path of the domain manager
	 * @return \GraphQL\Type\Definition\ObjectType Output type definition
	 */
	public function outputType( string $domain ) : ObjectType
	{
		$name = str_replace( '/', '', $domain ) . 'Output';

		if( isset( $this->types[$name] ) ) {
			return $this->types[$name];
		}

		return $this->types[$name] = new ObjectType( [
			'name' => $name,
			'fields' => function() use ( $domain ) {

				$manager = \Aimeos\MShop::create( $this->context, $domain );
				$list = $this->fields( $manager->getSearchAttributes( false ) );
				$item = $manager->create();

				if( $item instanceof \Aimeos\MShop\Common\Item\AddressRef\Iface ) {
					$list['address'] = Type::listOf( $this->addressOutputType( $domain . '/address' ) );
				}

				if( $item instanceof \Aimeos\MShop\Common\Item\Tree\Iface ) {
					$list['children'] = Type::listOf( $this->treeOutputType( $domain ) );
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
						'type' => $this->listsOutputType( $domain . '/lists' ),
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


	/**
	 * Defines the GraphQL address output type
	 *
	 * @param string $path Path of the domain manager
	 * @return \GraphQL\Type\Definition\ObjectType Output type definition
	 */
	public function addressOutputType( string $domain ) : ObjectType
	{
		$name = str_replace( '/', '', $domain ) . 'Output';

		if( isset( $this->types[$name] ) ) {
			return $this->types[$name];
		}

		return $this->types[$name] = new ObjectType( [
			'name' => $name,
			'fields' => function() use ( $domain ) {

				$manager = \Aimeos\MShop::create( $this->context, $domain );
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


	/**
	 * Defines the GraphQL config output type
	 *
	 * @param string $domain Name of the domain to retrieve the configuration
	 * @return \GraphQL\Type\Definition\ObjectType Output type definition
	 */
	public function configOutputType( string $domain ) : ObjectType
	{
		$name = str_replace( '/', '', $domain ) . 'ConfigOutput';

		if( isset( $this->types[$name] ) ) {
			return $this->types[$name];
		}

		return $this->types[$name] = new ObjectType( [
			'name' => $name,
			'fields' => function() {
				return [
					'code' => [
						'name' => 'code',
						'type' => Type::String(),
					],
					'label' => [
						'name' => 'label',
						'type' => Type::String(),
					],
					'type' => [
						'name' => 'type',
						'type' => Type::String(),
					],
					'required' => [
						'name' => 'required',
						'type' => Type::Boolean(),
					],
					'default' => [
						'name' => 'default',
						'type' => Type::String(),
					],
				];
			},
			'resolveField' => function( $item, array $args, $context, ResolveInfo $info ) use ( $domain ) {
				switch( $info->fieldName ) {
					case 'code': return $item->getCode();
					case 'label': return $item->getLabel();
					case 'type': return $item->getType();
					case 'required': return $item->isRequired();
					case 'default': return (string) $item->getDefault();
				}
			}
		] );
	}


	/**
	 * Defines the GraphQL list reference output type
	 *
	 * @param string $path Path of the domain manager
	 * @return \GraphQL\Type\Definition\ObjectType Output type definition
	 */
	public function listsOutputType( string $path ) : ObjectType
	{
		$name = str_replace( '/', '', $path ) . 'refOutput';

		if( isset( $this->types[$name] ) ) {
			return $this->types[$name];
		}

		return $this->types[$name] = new ObjectType( [
			'name' => $name,
			'fields' => function() use ( $path ) {

				if( $domains = $this->context->config()->get( 'admin/graphql/lists-domains', [] ) )
				{
					foreach( $domains as $domain )
					{
						$list[str_replace( '/', '', $domain )] = [
							'type' => Type::listOf( $this->listsRefOutputType( $path, $domain ) ),
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


	/**
	 * Defines the GraphQL lists output type
	 *
	 * @param string $path Path of the domain manager
	 * @param string $domain Domain name of the referenced item
	 * @return \GraphQL\Type\Definition\ObjectType Output type definition
	 */
	public function listsRefOutputType( string $path, string $domain ) : ObjectType
	{
		$name = str_replace( '/', '', $path ) . $domain . 'Output';

		if( isset( $this->types[$name] ) ) {
			return $this->types[$name];
		}

		return $this->types[$name] = new ObjectType( [
			'name' => $name,
			'fields' => function() use ( $path, $domain ) {

				$manager = \Aimeos\MShop::create( $this->context, $path );

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


	/**
	 * Defines the GraphQL property output type
	 *
	 * @param string $domain Name of the domain which is using the property item
	 * @return \GraphQL\Type\Definition\ObjectType Output type definition
	 */
	public function propertyOutputType( string $domain ) : ObjectType
	{
		$name = str_replace( '/', '', $domain ) . 'Output';

		if( isset( $this->types[$name] ) ) {
			return $this->types[$name];
		}

		return $this->types[$name] = new ObjectType( [
			'name' => $name,
			'fields' => function() use ( $domain ) {

				$manager = \Aimeos\MShop::create( $this->context, $domain );
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


	/**
	 * Defines the GraphQL search output types
	 *
	 * @param string $path Path of the domain manager
	 * @return \GraphQL\Type\Definition\ObjectType Output type definition
	 */
	public function searchOutputType( string $domain ) : ObjectType
	{
		$name = 'search' . str_replace( '/', '', ucwords( $domain ) ) . 'Output';

		if( isset( $this->types[$name] ) ) {
			return $this->types[$name];
		}

		return $this->types[$name] = new ObjectType( [
			'name' => $name,
			'fields' => function() use ( $domain ) {
				return [
					'items' => [
						'name' => 'items',
						'description' => 'List of items',
						'type' => Type::listOf( $this->outputType( $domain ) ),
					],
					'total' => [
						'name' => 'total',
						'description' => 'Total number of items',
						'type' => Type::int(),
					]
				];
			},
			'resolveField' => function( array $map, array $args, $context, ResolveInfo $info ) {
				return $map[$info->fieldName] ?? null;
			}
		] );
	}


	/**
	 * Defines the GraphQL tree output type
	 *
	 * @param string $path Path of the domain manager
	 * @return \GraphQL\Type\Definition\ObjectType Output type definition
	 */
	public function treeOutputType( string $domain ) : ObjectType
	{
		$name = str_replace( '/', '', $domain ) . 'TreeOutput';

		if( isset( $this->types[$name] ) ) {
			return $this->types[$name];
		}

		return $this->types[$name] = new ObjectType( [
			'name' => $name,
			'fields' => function() use ( $domain ) {

				$manager = \Aimeos\MShop::create( $this->context, $domain );

				$list = $this->fields( $manager->getSearchAttributes( false ) );
				$list['children'] = Type::listOf( $this->treeOutputType( $domain ) );

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


	/**
	 * Returns the field types for the passed search attributes
	 *
	 * @param array $attrs List of search attribute items implementing \Aimeos\Base\Criteria\Attribute\Iface
	 * @return array Associative list of codes as keys and entries defining the field as values
	 */
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


	/**
	 * Returns the name of the field without prefix
	 *
	 * @param string $value Search property name
	 * @return string Field name without prefix
	 */
	protected function name( string $value ) : string
	{
		$pos = strrpos( $value, '.' );
		return substr( $value, $pos ? $pos + 1 : 0 );
	}


	/**
	 * Adds the prefix for the passed domain
	 *
	 * @param string $domain Domain name of the item the entry is for
	 * @param array $entry Associative list of key/value pairs of the item
	 * @return array Associative list of prefixed key/value pairs of the item
	 */
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


	/**
	 * Returns the field value for the passed item, domain and name
	 *
	 * @param \Aimeos\MShop\Common\Item\Iface $item Item which contains the requested value
	 * @param string $domain Domain name of the item
	 * @param string $name Name of the requested value
	 * @return string|null Requested value
	 */
	protected function resolve( ItemIface $item, string $domain, string $name )
	{
		return $item->get( str_replace( '/', '.', $domain ) . '.' . $name ) ?? $item->get( $name );
	}


	/**
	 * Returns the GraphQL type for passed Aimeos search attribute type
	 *
	 * @param string $name Name of the Aimeos type
	 * @return \GraphQL\Type\Definition\Type GraphQL type
	 */
	protected function type( string $name ) : Type
	{
		switch( $name )
		{
			case 'bool':
			case 'boolean': return Type::boolean();
			case 'float': return Type::float();
			case 'int':
			case 'integer': return Type::int();
			case 'json': return \Aimeos\GraphQL\Type\Definition\Json::type();
		}

		return Type::string();
	}
}
