<?php

/**
 * @license LGPLv3, http://opensource.org/licenses/LGPL-3.0
 * @copyright Aimeos (aimeos.org), 2024
 * @package Admin
 * @subpackage GraphQL
 */


namespace Aimeos\Admin\Graphql\Order;

use GraphQL\Type\Definition\Type;
use GraphQL\Type\Definition\ObjectType;
use GraphQL\Type\Definition\ResolveInfo;
use GraphQL\Type\Definition\InputObjectType;


/**
 * GraphQL class for special handling of customers
 *
 * @package Admin
 * @subpackage GraphQL
 */
class Standard extends \Aimeos\Admin\Graphql\Standard
{
	private array $types = [];


	/**
	 * Returns GraphQL schema definition for the available mutations
	 *
	 * @param string $domain Domain name of the responsible manager
	 * @return array GraphQL mutation schema definition
	 */
	public function mutation( string $domain ) : array
	{
		return [
			'save' . str_replace( '/', '', ucwords( $domain, '/' ) ) => [
				'type' => $this->orderOutputType( $domain ),
				'args' => [
					['name' => 'input', 'type' => $this->orderInputType( $domain ), 'description' => 'Item object'],
				],
				'resolve' => $this->saveItem( $domain ),
			],
			'save' . str_replace( '/', '', ucwords( $domain, '/' ) ) . 's' => [
				'type' => Type::listOf( $this->orderOutputType( $domain ) ),
				'args' => [
					['name' => 'input', 'type' => Type::listOf( $this->orderInputType( $domain ) ), 'description' => 'Item objects'],
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
				'type' => $this->orderOutputType( $domain ),
				'args' => [
					['name' => 'id', 'type' => Type::string(), 'description' => 'Unique ID'],
					['name' => 'include', 'type' => Type::listOf( Type::string() ), 'defaultValue' => [], 'description' => 'Domains to include'],
				],
				'resolve' => $this->getItem( $domain ),
			],
			'search' . str_replace( '/', '', ucwords( $domain, '/' ) ) . 's' => [
				'type' => $this->types()->searchOutputType( $domain, fn( $path ) => $this->orderOutputType( $path ) ),
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


	/**
	 * Defines the GraphQL order input type
	 *
	 * @param string $path Path of the domain manager
	 * @return \GraphQL\Type\Definition\InputObjectType Input type definition
	 */
	public function orderInputType( string $path ) : InputObjectType
	{
		$name = 'orderInput';

		if( isset( $this->types[$name] ) ) {
			return $this->types[$name];
		}

		return $this->types[$name] = new InputObjectType( [
			'name' => $name,
			'fields' => function() use ( $path ) {

				$manager = \Aimeos\MShop::create( $this->context(), $path );
				$list = $this->types()->fields( $manager->getSearchAttributes( false ) );

				$list['address'] = Type::listOf( $this->types()->inputType( $path . '/address' ) );
				$list['product'] = Type::listOf( $this->orderProductInputType( $path . '/product' ) );
				$list['service'] = Type::listOf( $this->orderServiceInputType( $path . '/service' ) );

				return $list;
			},
			'parseValue' => function( array $values ) use ( $path ) {
				return $this->types()->prefix( $path, $values );
			}
		] );
	}


	/**
	 * Defines the GraphQL order product input type
	 *
	 * @param string $path Path of the domain manager
	 * @return \GraphQL\Type\Definition\InputObjectType Input type definition
	 */
	public function orderProductInputType( string $path ) : InputObjectType
	{
		$name = 'orderProductInput';

		if( isset( $this->types[$name] ) ) {
			return $this->types[$name];
		}

		return $this->types[$name] = new InputObjectType( [
			'name' => $name,
			'fields' => function() use ( $path ) {

				$manager = \Aimeos\MShop::create( $this->context(), $path );
				$list = $this->types()->fields( $manager->getSearchAttributes( false ) );

				$list['product'] = Type::listOf( $this->orderSubProductInputType( $path ) );
				$list['attribute'] = Type::listOf( $this->types()->inputType( $path . '/attribute' ) );

				return $list;
			},
			'parseValue' => function( array $values ) use ( $path ) {
				return $this->types()->prefix( $path, $values );
			}
		] );
	}


	/**
	 * Defines the GraphQL order sub-product input type
	 *
	 * @param string $path Path of the domain manager
	 * @return \GraphQL\Type\Definition\InputObjectType Input type definition
	 */
	public function orderSubProductInputType( string $path ) : InputObjectType
	{
		$name = 'orderSubProductInput';

		if( isset( $this->types[$name] ) ) {
			return $this->types[$name];
		}

		return $this->types[$name] = new InputObjectType( [
			'name' => $name,
			'fields' => function() use ( $path ) {

				$manager = \Aimeos\MShop::create( $this->context(), $path );

				$list = $this->types()->fields( $manager->getSearchAttributes( false ) );
				$list['attribute'] = Type::listOf( $this->types()->inputType( $path . '/attribute' ) );

				return $list;
			},
			'parseValue' => function( array $values ) use ( $path ) {
				return $this->types()->prefix( $path, $values );
			}
		] );
	}


	/**
	 * Defines the GraphQL order service input type
	 *
	 * @param string $path Path of the domain manager
	 * @return \GraphQL\Type\Definition\InputObjectType Input type definition
	 */
	public function orderServiceInputType( string $path ) : InputObjectType
	{
		$name = 'orderServiceInput';

		if( isset( $this->types[$name] ) ) {
			return $this->types[$name];
		}

		return $this->types[$name] = new InputObjectType( [
			'name' => $name,
			'fields' => function() use ( $path ) {

				$manager = \Aimeos\MShop::create( $this->context(), $path );

				$list = $this->types()->fields( $manager->getSearchAttributes( false ) );
				$list['attribute'] = Type::listOf( $this->types()->inputType( $path . '/attribute' ) );
				$list['transaction'] = Type::listOf( $this->types()->inputType( $path . '/transaction' ) );

				return $list;
			},
			'parseValue' => function( array $values ) use ( $path ) {
				return $this->types()->prefix( $path, $values );
			}
		] );
	}


	/**
	 * Defines the GraphQL order output types
	 *
	 * @return \GraphQL\Type\Definition\ObjectType Output type definition
	 */
	public function orderOutputType() : ObjectType
	{
		$name = 'orderOutputType';

		if( isset( $this->types[$name] ) ) {
			return $this->types[$name];
		}

		return $this->types[$name] = new ObjectType( [
			'name' => $name,
			'fields' => function() {

				$manager = \Aimeos\MShop::create( $this->context(), 'order' );
				$list = $this->types()->fields( $manager->getSearchAttributes( false ) );

				$list['address'] = [
					'type' => Type::listOf( $this->orderAddressOutputType() ),
					'resolve' => function( $item ) {
						return $item->getAddresses()->flat( 1 );
					}
				];

				$list['coupon'] = [
					'type' => Type::listOf( $this->orderCouponOutputType() ),
					'resolve' => function( $item ) {
						return $item->getCoupons()->keys()->all();
					}
				];

				$list['product'] = [
					'type' => Type::listOf( $this->orderProductOutputType() ),
					'resolve' => function( $item ) {
						return $item->getProducts();
					}
				];

				$list['service'] = [
					'type' => Type::listOf( $this->orderServiceOutputType() ),
					'resolve' => function( $item ) {
						return $item->getServices()->flat( 1 );
					}
				];

				$list['status'] = [
					'type' => Type::listOf( $this->orderStatusOutputType() ),
					'resolve' => function( $item ) {
						return $item->getStatuses()->flat( 1 );
					}
				];

				return $list;
			},
			'resolveField' => function( \Aimeos\MShop\Order\Item\Iface $item, array $args, $context, ResolveInfo $info ) {
				return $this->types()->resolve( $item, 'order', $info->fieldName );
			}
		] );
	}


	/**
	 * Defines the GraphQL order address output types
	 *
	 * @return \GraphQL\Type\Definition\ObjectType Output type definition
	 */
	public function orderAddressOutputType() : ObjectType
	{
		$name = 'orderAddressOutput';

		if( isset( $this->types[$name] ) ) {
			return $this->types[$name];
		}

		return $this->types[$name] = new ObjectType( [
			'name' => $name,
			'fields' => function() {
				$manager = \Aimeos\MShop::create( $this->context(), 'order/address' );
				return $this->types()->fields( $manager->getSearchAttributes( false ) );
			},
			'resolveField' => function( \Aimeos\MShop\Order\Item\Address\Iface $item, array $args, $context, ResolveInfo $info ) {
				return $this->types()->resolve( $item, 'order/address', $info->fieldName );
			}
		] );
	}


	/**
	 * Defines the GraphQL order coupon output types
	 *
	 * @return \GraphQL\Type\Definition\ObjectType Output type definition
	 */
	public function orderCouponOutputType() : ObjectType
	{
		$name = 'orderCouponOutput';

		if( isset( $this->types[$name] ) ) {
			return $this->types[$name];
		}

		return $this->types[$name] = new ObjectType( [
			'name' => $name,
			'fields' => function() {
				return [
					'code' => [
						'name' => 'code',
						'description' => 'Coupon codes',
						'type' => Type::String(),
					],
				];
			},
			'resolveField' => function( $codes, array $args, $context, ResolveInfo $info ) {
				return (string) $codes;
			}
		] );
	}


	/**
	 * Defines the GraphQL order product output types
	 *
	 * @return \GraphQL\Type\Definition\ObjectType Output type definition
	 */
	public function orderProductOutputType() : ObjectType
	{
		$name = 'orderProductOutput';

		if( isset( $this->types[$name] ) ) {
			return $this->types[$name];
		}

		return $this->types[$name] = new ObjectType( [
			'name' => $name,
			'fields' => function() {
				$manager = \Aimeos\MShop::create( $this->context(), 'order/product' );
				$list = $this->types()->fields( $manager->getSearchAttributes( false ) );

				$list['product'] = [
					'type' => Type::listOf( $this->orderSubProductOutputType() ),
					'resolve' => function( $item ) {
						return $item->getProducts();
					}
				];

				$list['attribute'] = [
					'type' => Type::listOf( $this->orderProductAttributeOutputType() ),
					'args' => [
						'type' => Type::String(),
					],
					'resolve' => function( $item, $args ) {
						return $item->getAttributeItems( $args['type'] ?? null );
					}
				];

				return $list;
			},
			'resolveField' => function( \Aimeos\MShop\Order\Item\Product\Iface $item, array $args, $context, ResolveInfo $info ) {
				return $this->types()->resolve( $item, 'order/product', $info->fieldName );
			}
		] );
	}


	/**
	 * Defines the GraphQL order sub-product output types
	 *
	 * @return \GraphQL\Type\Definition\ObjectType Output type definition
	 */
	public function orderSubProductOutputType() : ObjectType
	{
		$name = 'orderSubProductOutput';

		if( isset( $this->types[$name] ) ) {
			return $this->types[$name];
		}

		return $this->types[$name] = new ObjectType( [
			'name' => $name,
			'fields' => function() {
				$manager = \Aimeos\MShop::create( $this->context(), 'order/product' );
				$list = $this->types()->fields( $manager->getSearchAttributes( false ) );

				$list['attribute'] = [
					'type' => Type::listOf( $this->orderProductAttributeOutputType() ),
					'args' => [
						'type' => Type::String(),
					],
					'resolve' => function( $item, $args ) {
						return $item->getAttributeItems( $args['type'] ?? null );
					}
				];

				return $list;
			},
			'resolveField' => function( \Aimeos\MShop\Order\Item\Product\Iface $item, array $args, $context, ResolveInfo $info ) {
				return $this->types()->resolve( $item, 'order/product', $info->fieldName );
			}
		] );
	}


	/**
	 * Defines the GraphQL order product attribute output types
	 *
	 * @return \GraphQL\Type\Definition\ObjectType Output type definition
	 */
	public function orderProductAttributeOutputType() : ObjectType
	{
		$name = 'orderProductAttributeOutput';

		if( isset( $this->types[$name] ) ) {
			return $this->types[$name];
		}

		return $this->types[$name] = new ObjectType( [
			'name' => $name,
			'fields' => function() {
				$manager = \Aimeos\MShop::create( $this->context(), 'order/product/attribute' );
				return $this->types()->fields( $manager->getSearchAttributes( false ) );
			},
			'resolveField' => function( \Aimeos\MShop\Order\Item\Product\Attribute\Iface $item, array $args, $context, ResolveInfo $info ) {
				return $this->types()->resolve( $item, 'order/product/attribute', $info->fieldName );
			}
		] );
	}


	/**
	 * Defines the GraphQL order service output types
	 *
	 * @return \GraphQL\Type\Definition\ObjectType Output type definition
	 */
	public function orderServiceOutputType() : ObjectType
	{
		$name = 'orderServiceOutput';

		if( isset( $this->types[$name] ) ) {
			return $this->types[$name];
		}

		return $this->types[$name] = new ObjectType( [
			'name' => $name,
			'fields' => function() {
				$manager = \Aimeos\MShop::create( $this->context(), 'order/service' );
				$list = $this->types()->fields( $manager->getSearchAttributes( false ) );

				$list['attribute'] = [
					'type' => Type::listOf( $this->orderServiceAttributeOutputType() ),
					'args' => [
						'type' => Type::String(),
					],
					'resolve' => function( $item, $args ) {
						return $item->getAttributeItems( $args['type'] ?? null );
					}
				];

				$list['transaction'] = [
					'type' => Type::listOf( $this->orderServiceTransactionOutputType() ),
					'args' => [
						'type' => Type::String(),
					],
					'resolve' => function( $item, $args ) {
						return $item->getTransactions( $args['type'] ?? null );
					}
				];

				return $list;
			},
			'resolveField' => function( \Aimeos\MShop\Order\Item\Service\Iface $item, array $args, $context, ResolveInfo $info ) {
				return $this->types()->resolve( $item, 'order/service', $info->fieldName );
			}
		] );
	}


	/**
	 * Defines the GraphQL order service attribute output types
	 *
	 * @return \GraphQL\Type\Definition\ObjectType Output type definition
	 */
	public function orderServiceAttributeOutputType() : ObjectType
	{
		$name = 'orderServiceAttributeOutput';

		if( isset( $this->types[$name] ) ) {
			return $this->types[$name];
		}

		return $this->types[$name] = new ObjectType( [
			'name' => $name,
			'fields' => function() {
				$manager = \Aimeos\MShop::create( $this->context(), 'order/service/attribute' );
				return $this->types()->fields( $manager->getSearchAttributes( false ) );
			},
			'resolveField' => function( \Aimeos\MShop\Order\Item\Service\Attribute\Iface $item, array $args, $context, ResolveInfo $info ) {
				return $this->types()->resolve( $item, 'order/service/attribute', $info->fieldName );
			}
		] );
	}


	/**
	 * Defines the GraphQL order service transaction output types
	 *
	 * @return \GraphQL\Type\Definition\ObjectType Output type definition
	 */
	public function orderServiceTransactionOutputType() : ObjectType
	{
		$name = 'orderServiceTransactionOutput';

		if( isset( $this->types[$name] ) ) {
			return $this->types[$name];
		}

		return $this->types[$name] = new ObjectType( [
			'name' => $name,
			'fields' => function() {
				$manager = \Aimeos\MShop::create( $this->context(), 'order/service/transaction' );
				return $this->types()->fields( $manager->getSearchAttributes( false ) );
			},
			'resolveField' => function( \Aimeos\MShop\Order\Item\Service\Transaction\Iface $item, array $args, $context, ResolveInfo $info ) {
				return $this->types()->resolve( $item, 'order/service/transaction', $info->fieldName );
			}
		] );
	}


	/**
	 * Defines the GraphQL order status output types
	 *
	 * @return \GraphQL\Type\Definition\ObjectType Output type definition
	 */
	public function orderStatusOutputType() : ObjectType
	{
		$name = 'orderStatusOutput';

		if( isset( $this->types[$name] ) ) {
			return $this->types[$name];
		}

		return $this->types[$name] = new ObjectType( [
			'name' => $name,
			'fields' => function() {
				$manager = \Aimeos\MShop::create( $this->context(), 'order/status' );
				return $this->types()->fields( $manager->getSearchAttributes( false ) );
			},
			'resolveField' => function( \Aimeos\MShop\Order\Item\Status\Iface $item, array $args, $context, ResolveInfo $info ) {
				return $this->types()->resolve( $item, 'order/status', $info->fieldName );
			}
		] );
	}
}
