<?php

/**
 * @license LGPLv3, http://opensource.org/licenses/LGPL-3.0
 * @copyright Aimeos (aimeos.org), 2022-2026
 * @package Admin
 * @subpackage GraphQL
 */


namespace Aimeos\Admin\Graphql;


/**
 * GraphQL base class for all domains
 *
 * @package Admin
 * @subpackage GraphQL
 */
abstract class Base
{
	use UpdateTrait;

	private \Aimeos\MShop\ContextIface $context;
	private Registry $registry;


	/**
	 * Initializes the object
	 *
	 * @param \Aimeos\MShop\ContextIface $context Context object
	 * @param \Aimeos\Admin\Graphql\Registry $registry Type registry object
	 */
	public function __construct( \Aimeos\MShop\ContextIface $context, Registry $registry )
	{
		$this->context = $context;
		$this->registry = $registry;
	}


	/**
	 * Ensures that the user has access to the given domain and action
	 *
	 * @param string $domain Domain path of the manager
	 * @param string $action Action name
	 * @return bool Always TRUE because an exception is thrown if access isn't allowed
	 * @throws \Aimeos\Admin\Graphql\Exception If access isn't allowed
	 */
	protected function access( string $domain, string $action ) : bool
	{
		\Aimeos\Admin\Graphql::check( $this->context, $domain, $action );
		return true;
	}


	/**
	 * Returns the domains of the related items the user is allowed to retrieve
	 *
	 * Sub-domains without own permissions (e.g. "order/address") inherit the
	 * permission of their parent domain, domains without any configured
	 * permission are removed.
	 *
	 * @param mixed $include Domain name or list of domain names whose items should be fetched too
	 * @return array List of domain names the user is allowed to retrieve
	 */
	protected function included( $include ) : array
	{
		return \Aimeos\Admin\Graphql::permitted( $this->context, (array) $include, 'get', inherit: true );
	}


	/**
	 * Returns a closure for aggregating items
	 *
	 * @param string $domain Domain path of the manager
	 * @return \Closure Anonymous method aggregating several items
	 */
	protected function aggregateItems( string $domain ) : \Closure
	{
		return function( $root, $args, $context ) use ( $domain ) {

			$this->access( $domain, 'get' );
			$manager = \Aimeos\MShop::create( $this->context(), $domain );

			// @phpstan-ignore argument.type
			$filter = $manager->filter()->order( $args['sort'] )->slice( 0, (int) $args['limit'] );
			// @phpstan-ignore argument.type
			$filter->add( $filter->parse( json_decode( (string) $args['filter'], true ) ) );
			\Aimeos\Admin\Graphql::restrict( $this->context(), $manager, $filter );

			// @phpstan-ignore argument.type, argument.type, argument.type
			return $manager->aggregate( $filter, $args['key'], $args['value'], $args['type'] )->all();
		};
	}


	/**
	 * Returns the context object
	 *
	 * @return \Aimeos\MShop\ContextIface Context object
	 */
	protected function context() : \Aimeos\MShop\ContextIface
	{
		return $this->context;
	}


	/**
	 * Returns a closure for deleting items
	 *
	 * @param string $domain Domain path of the manager
	 * @return \Closure Anonymous method deleting one or more items
	 */
	protected function deleteItems( string $domain ) : \Closure
	{
		return function( $root, $args, $context ) use ( $domain ) {

			$this->access( $domain, 'delete' );
			// @phpstan-ignore argument.type
			\Aimeos\MShop::create( $this->context(), $domain )->delete( $args['id'] );
			return $args['id'];
		};
	}


	/**
	 * Returns a closure for returning a single item by its ID
	 *
	 * @param string $domain Domain path of the manager
	 * @return \Closure Anonymous method returning one item
	 */
	protected function getItem( string $domain ) : \Closure
	{
		return function( $root, $args, $context ) use ( $domain ) {

			$this->access( $domain, 'get' );
			// @phpstan-ignore argument.type
			return $this->filter( \Aimeos\MShop::create( $this->context(), $domain )->get( (string) $args['id'], $this->included( $args['include'] ) ) );
		};
	}


	/**
	 * Returns the item if the user is allowed to retrieve it
	 *
	 * Shared items (e.g. prices, texts and media) require the read permission of the owning domain.
	 * Subclasses adding their own checks must call this method too.
	 *
	 * @param \Aimeos\MShop\Common\Item\Iface $item Item to check
	 * @return \Aimeos\MShop\Common\Item\Iface Passed item
	 * @throws \Aimeos\MShop\Exception If the user isn't allowed to retrieve the item, same as for missing items
	 */
	protected function filter( \Aimeos\MShop\Common\Item\Iface $item ) : \Aimeos\MShop\Common\Item\Iface
	{
		return \Aimeos\Admin\Graphql::readable( $this->context(), $item );
	}


	/**
	 * Returns the items if not removed for security reasons
	 *
	 * Search results don't need the checks of filter() because restrict() already limits
	 * the filter to the items the user is allowed to retrieve.
	 *
	 * @param iterable $items List of items to check
	 * @return iterable List of items not removed
	 */
	protected function filters( iterable $items ) : iterable
	{
		return $items;
	}


	/**
	 * Returns a closure for returning a single item by its code
	 *
	 * @param string $domain Domain path of the manager
	 * @return \Closure Anonymous method returning one item
	 */
	protected function findItem( string $domain ) : \Closure
	{
		return function( $root, $args, $context ) use ( $domain ) {

			$this->access( $domain, 'get' );
			// @phpstan-ignore argument.type
			return $this->filter( \Aimeos\MShop::create( $this->context(), $domain )->find( $args['code'], $this->included( $args['include'] ) ) );
		};
	}


	/**
	 * Returns a closure for returning a single type item by its code
	 *
	 * @param string $domain Domain path of the manager
	 * @return \Closure Anonymous method returning one item
	 */
	protected function findTypeItem( string $domain ) : \Closure
	{
		return function( $root, $args, $context ) use ( $domain ) {

			$this->access( $domain, 'get' );
			// @phpstan-ignore argument.type
			return $this->filter( \Aimeos\MShop::create( $this->context(), $domain )->find( $args['code'], [], $args['domain'] ) );
		};
	}


	/**
	 * Returns a closure for returning several items
	 *
	 * @param string $domain Domain path of the manager
	 * @return \Closure Anonymous method returning several items
	 */
	protected function searchItems( string $domain ) : \Closure
	{
		return function( $root, $args, $context ) use ( $domain ) {

			$this->access( $domain, 'get' );
			$manager = \Aimeos\MShop::create( $this->context(), $domain );

			// @phpstan-ignore argument.type
			$filter = $manager->filter()->order( $args['sort'] )->slice( (int) $args['offset'], (int) $args['limit'] );
			// @phpstan-ignore argument.type
			$filter->add( $filter->parse( json_decode( (string) $args['filter'], true ) ) );
			\Aimeos\Admin\Graphql::restrict( $this->context(), $manager, $filter );

			$total = 0;
			// @phpstan-ignore argument.type
			$items = $this->filters( $manager->search( $filter, $this->included( $args['include'] ), $total )->toArray() );

			return [
				'items' => $items,
				'total' => $total
			];
		};
	}


	/**
	 * Returns a closure for saving one item
	 *
	 * @param string $domain Domain path of the manager
	 * @return \Closure Anonymous method returning one item
	 */
	protected function saveItem( string $domain ) : \Closure
	{
		return function( $root, $args, $context ) use ( $domain ) {

			if( empty( $entry = $args['input'] ) ) {
				throw new \Aimeos\Admin\Graphql\Exception( 'Parameter "input" must not be empty' );
			}

			$this->access( $domain, 'save' );
			$ref = $this->getRefs( (array) $entry, $domain );
			$manager = \Aimeos\MShop::create( $this->context(), $domain );
			$key = str_replace( '/', '.', $domain ) . '.id';

			if( !empty( $entry[$key] ) ) {
				// @phpstan-ignore argument.type, argument.type
				$item = $manager->get( (string) $entry[$key], array_unique( $ref ) );
			} else {
				$item = $manager->create();
			}

			return $manager->save( $this->updateItem( $manager, $item, (array) $entry ) );
		};
	}


	/**
	 * Returns a closure for saving several items
	 *
	 * @param string $domain Domain path of the manager
	 * @return \Closure Anonymous method saving several items
	 */
	protected function saveItems( string $domain ) : \Closure
	{
		return function( $root, $args, $context ) use ( $domain ) {

			if( empty( $entries = (array) $args['input'] ) ) {
				throw new \Aimeos\Admin\Graphql\Exception( 'Parameter "input" must not be empty' );
			}

			$this->access( $domain, 'save' );
			$manager = \Aimeos\MShop::create( $this->context(), $domain );
			$key = str_replace( '/', '.', $domain ) . '.id';

			$ids = array_filter( array_column( $entries, $key ) );
			$filter = $manager->filter()->add( $key, '==', $ids )->slice( 0, count( $entries ) );

			$ref = [];
			foreach( $entries as $entry ) {
				$ref = array_merge( $ref, $this->getRefs( (array) $entry, $domain ) );
			}

			// @phpstan-ignore argument.type, argument.type
			$map = $manager->search( $filter, array_unique( $ref ) );
			$items = [];

			foreach( $entries as $entry )
			{
				if( empty( $entry[$key] ) ) {
					$item = $manager->create();
				} elseif( ( $item = $map->get( (string) $entry[$key] ) ) === null ) {
					throw new \Aimeos\Admin\Graphql\Exception( sprintf( 'Item with ID "%1$s" not found', (string) $entry[$key] ), 404 );
				}

				// @phpstan-ignore argument.type
				$items[] = $this->updateItem( $manager, $item, (array) $entry );
			}

			// @phpstan-ignore argument.type
			return $manager->save( $items );
		};
	}


	/**
	 * Recursively collect all referenced domains
	 * @param array $entry Entry or subentry with input data
	 * @param  string $domain Domain of subentry
	 * @return array Array with all domains collected
	 */
	protected function getRefs( array $entry, string $domain ): array
	{
		$ref = array_keys( (array) ( $entry['lists'] ?? [] ) );

		foreach( $entry['lists'] ?? [] as $listDomain => $subentry )
		{
			foreach( $subentry ?? [] as $subItem ) {
				$ref = array_merge( $ref, $this->getRefs( (array) ( $subItem['item'] ?? [] ), (string) $listDomain ) );
			}
		}

		if( isset( $entry['property'] ) ) {
			$ref[] = $domain . '/property';
		}

		if( isset( $entry['stock'] ) ) {
			$ref[] = 'stock';
		}

		return $ref;
	}


	/**
	 * Returns the types registry
	 *
	 * @return \Aimeos\Admin\Graphql\Registry Type registry object
	 */
	protected function types() : Registry
	{
		return $this->registry;
	}
}
