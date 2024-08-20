<?php

/**
 * @license LGPLv3, http://opensource.org/licenses/LGPL-3.0
 * @copyright Aimeos (aimeos.org), 2022-2024
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
		$name = str_replace( '/', '', ucwords( $path, '/' ) ) . 'Input';

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
		$name = str_replace( '/', '', ucwords( $path, '/' ) ) . 'Input';

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
		$name = str_replace( '/', '', ucwords( $path, '/' ) ) . 'refInput';

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
		$name = str_replace( '/', '', ucwords( $path . '/' . $domain, '/' ) ) . 'Input';

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
	public function propertyInputType( string $path ) : ObjectType
	{
		$name = str_replace( '/', '', ucwords( $path, '/' ) ) . 'Input';

		if( isset( $this->types[$name] ) ) {
			return $this->types[$name];
		}

		return $this->types[$name] = new ObjectType( [
			'name' => $name,
			'fields' => function() use ( $path ) {

				$manager = \Aimeos\MShop::create( $this->context, $path );
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
	public function outputType( string $path ) : ObjectType
	{
		$name = str_replace( '/', '', ucwords( $path, '/' ) ) . 'Output';

		if( isset( $this->types[$name] ) ) {
			return $this->types[$name];
		}

		return $this->types[$name] = new ObjectType( [
			'name' => $name,
			'fields' => function() use ( $path ) {

				$manager = \Aimeos\MShop::create( $this->context, $path );
				$list = $this->fields( $manager->getSearchAttributes( false ) );
				$item = $manager->create();

				if( $item instanceof \Aimeos\MShop\Customer\Item\Iface )
				{
					$list['groups'] = [
						'type' => Type::listOf( Type::String() ),
						'description' => 'List of group IDs assigned to the account',
						'resolve' => function( $item, $args ) {
							return $item->getGroups();
						}
					];
				}

				if( $item instanceof \Aimeos\MShop\Common\Item\AddressRef\Iface ) {
					$list['address'] = Type::listOf( $this->addressOutputType( $path . '/address' ) );
				}

				if( $item instanceof \Aimeos\MShop\Common\Item\Tree\Iface ) {
					$list['children'] = Type::listOf( $this->treeOutputType( $path ) );
				}

				if( $item instanceof \Aimeos\MShop\Common\Item\PropertyRef\Iface )
				{
					$list['property'] = [
						'type' => Type::listOf( $this->propertyOutputType( $path . '/property' ) ),
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
						'type' => $this->listsOutputType( $path . '/lists' ),
						'resolve' => function( ItemIface $item, array $args ) {
							return $item;
						}
					];
				}

				return $list;
			},
			'resolveField' => function( ItemIface $item, array $args, $context, ResolveInfo $info ) use ( $path ) {
				return $this->resolve( $item, $path, $info->fieldName );
			}
		] );
	}


	/**
	 * Defines the GraphQL address output type
	 *
	 * @param string $path Path of the domain manager
	 * @return \GraphQL\Type\Definition\ObjectType Output type definition
	 */
	public function addressOutputType( string $path ) : ObjectType
	{
		$name = str_replace( '/', '', ucwords( $path, '/' ) ) . 'Output';

		if( isset( $this->types[$name] ) ) {
			return $this->types[$name];
		}

		return $this->types[$name] = new ObjectType( [
			'name' => $name,
			'fields' => function() use ( $path ) {

				$manager = \Aimeos\MShop::create( $this->context, $path );
				return $this->fields( $manager->getSearchAttributes( false ) );
			},
			'resolveField' => function( ItemIface $item, array $args, $context, ResolveInfo $info ) use ( $path ) {

				if( $info->fieldName === 'address' && $item instanceof \Aimeos\MShop\Common\Item\AddressRef\Iface ) {
					return $item->getAddressItems();
				}

				return $this->resolve( $item, $path, $info->fieldName );
			}
		] );
	}


	/**
	 * Defines the GraphQL tree output type
	 *
	 * @param string $path Path of the domain manager
	 * @return \GraphQL\Type\Definition\ObjectType Output type definition
	 */
	public function aggregateOutputType( string $path ) : ObjectType
	{
		$name = str_replace( '/', '', ucwords( $path, '/' ) ) . 'AggregateOutput';

		if( isset( $this->types[$name] ) ) {
			return $this->types[$name];
		}

		return $this->types[$name] = new ObjectType( [
			'name' => $name,
			'fields' => function() use ( $path ) {
				return [
					'aggregates' => Type::string()
				];
			},
			'resolveField' => function( array $entry, array $args, $context, ResolveInfo $info ) use ( $path ) {
				return json_encode( $entry, JSON_FORCE_OBJECT );
			}
		] );
	}


	/**
	 * Defines the GraphQL config output type
	 *
	 * @param string $path Path of the domain to retrieve the configuration
	 * @return \GraphQL\Type\Definition\ObjectType Output type definition
	 */
	public function configOutputType( string $path ) : ObjectType
	{
		$name = str_replace( '/', '', ucwords( $path, '/' ) ) . 'ConfigOutput';

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
						'type' => \Aimeos\GraphQL\Type\Definition\Json::type(),
					],
				];
			},
			'resolveField' => function( $item, array $args, $context, ResolveInfo $info ) use ( $path ) {
				switch( $info->fieldName ) {
					case 'code': return $item->getCode();
					case 'label': return $item->getLabel();
					case 'type': return $item->getType();
					case 'required': return $item->isRequired();
					case 'default': return $item->getDefault();
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
		$name = str_replace( '/', '', ucwords( $path, '/' ) ) . 'refOutput';

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
		$name = str_replace( '/', '', ucwords( $path . '/' . $domain, '/' ) ) . 'Output';

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
	 * @param string $path Path of the manager which is using the property item
	 * @return \GraphQL\Type\Definition\ObjectType Output type definition
	 */
	public function propertyOutputType( string $path ) : ObjectType
	{
		$name = str_replace( '/', '', ucwords( $path, '/' ) ) . 'Output';

		if( isset( $this->types[$name] ) ) {
			return $this->types[$name];
		}

		return $this->types[$name] = new ObjectType( [
			'name' => $name,
			'fields' => function() use ( $path ) {

				$manager = \Aimeos\MShop::create( $this->context, $path );
				return $this->fields( $manager->getSearchAttributes( false ) );
			},
			'resolveField' => function( ItemIface $item, array $args, $context, ResolveInfo $info ) use ( $path ) {

				if( $info->fieldName === 'property' && $item instanceof \Aimeos\MShop\Common\Item\PropertyRef\Iface ) {
					return $item->getPropertyItems();
				}

				return $this->resolve( $item, $path, $info->fieldName );
			}
		] );
	}


	/**
	 * Defines the GraphQL search output types
	 *
	 * @param string $path Path of the domain manager
	 * @param Closure|null Output type method (default: outputType())
	 * @return \GraphQL\Type\Definition\ObjectType Output type definition
	 */
	public function searchOutputType( string $path, \Closure $method = null ) : ObjectType
	{
		$name = 'search' . str_replace( '/', '', ucwords( $path ) ) . 'Output';

		if( isset( $this->types[$name] ) ) {
			return $this->types[$name];
		}

		return $this->types[$name] = new ObjectType( [
			'name' => $name,
			'fields' => function() use ( $path, $method ) {
				return [
					'items' => [
						'name' => 'items',
						'description' => 'List of items',
						'type' => Type::listOf( $method ? $method( $path ) : $this->outputType( $path ) ),
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
	 * Defines the GraphQL locale site output types
	 *
	 * @return \GraphQL\Type\Definition\ObjectType Output type definition
	 */
	public function siteOutputType() : ObjectType
	{
		$name = 'siteOutputType';

		if( isset( $this->types[$name] ) ) {
			return $this->types[$name];
		}

		return $this->types[$name] = new ObjectType( [
			'name' => $name,
			'fields' => function() {
				$manager = \Aimeos\MShop::create( $this->context, 'locale/site' );

				$list = $this->fields( $manager->getSearchAttributes( false ) );
				$list['children'] = Type::listOf( $this->siteOutputType() );
				$list['hasChildren'] = [
					'name' => 'hasChildren',
					'description' => 'If node has children',
					'type' => Type::boolean(),
				];

				return $list;
			},
			'resolveField' => function( ItemIface $item, array $args, $context, ResolveInfo $info ) {

				if( $info->fieldName === 'children' && $item instanceof \Aimeos\MShop\Common\Item\Tree\Iface ) {
					return $item->getChildren();
				}

				return $this->resolve( $item, 'locale/site', $info->fieldName );
			}
		] );
	}


	/**
	 * Defines the GraphQL tree output type
	 *
	 * @param string $path Path of the domain manager
	 * @return \GraphQL\Type\Definition\ObjectType Output type definition
	 */
	public function treeOutputType( string $path ) : ObjectType
	{
		$name = str_replace( '/', '', ucwords( $path, '/' ) ) . 'TreeOutput';

		if( isset( $this->types[$name] ) ) {
			return $this->types[$name];
		}

		return $this->types[$name] = new ObjectType( [
			'name' => $name,
			'fields' => function() use ( $path ) {

				$manager = \Aimeos\MShop::create( $this->context, $path );
				$item = $manager->create();

				$list = $this->fields( $manager->getSearchAttributes( false ) );
				$list['children'] = Type::listOf( $this->treeOutputType( $path ) );

				if( $item instanceof \Aimeos\MShop\Common\Item\ListsRef\Iface )
				{
					$list['lists'] = [
						'type' => $this->listsOutputType( $path . '/lists' ),
						'resolve' => function( ItemIface $item, array $args ) {
							return $item;
						}
					];
				}

				return $list;
			},
			'resolveField' => function( ItemIface $item, array $args, $context, ResolveInfo $info ) use ( $path ) {

				if( $info->fieldName === 'children' && $item instanceof \Aimeos\MShop\Common\Item\Tree\Iface ) {
					return $item->getChildren();
				}

				return $this->resolve( $item, $path, $info->fieldName );
			}
		] );
	}


	/**
	 * Returns the field types for the passed search attributes
	 *
	 * @param array $attrs List of search attribute items implementing \Aimeos\Base\Criteria\Attribute\Iface
	 * @return array Associative list of codes as keys and entries defining the field as values
	 */
	public function fields( array $attrs ) : array
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
	 * Adds the prefix for the passed domain
	 *
	 * @param string $domain Domain name of the item the entry is for
	 * @param array $entry Associative list of key/value pairs of the item
	 * @return array Associative list of prefixed key/value pairs of the item
	 */
	public function prefix( string $domain, array $entry ) : array
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
	public function resolve( ItemIface $item, string $domain, string $name )
	{
		return $item->get( $name ) ?? $item->get( str_replace( '/', '.', $domain ) . '.' . $name );
	}


	/**
	 * Returns the GraphQL type for passed Aimeos search attribute type
	 *
	 * @param string $name Name of the Aimeos type
	 * @return \GraphQL\Type\Definition\Type GraphQL type
	 */
	public function type( string $name ) : Type
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
}
