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
	 * @type \Aimeos\Admin\Graphql\Registry Type registry object
	 */
	public function __construct( \Aimeos\MShop\ContextIface $context, Registry $registry )
	{
		$this->context = $context;
		$this->registry = $registry;
	}


	/**
	 * Checks if the user has access to the given domain and action
	 *
	 * @param string $domain Domain path of the manager
	 * @param string $action Action name
	 * @return bool True if access is allowed, false if not
	 */
	protected function access( string $domain, string $action ) : bool
	{
		$groups = $this->context->config()->get( 'admin/graphql/resource/' . $domain . '/' . $action, [] );

		// @phpstan-ignore argument.type
		if( $this->context->view()->access( $groups ) === true ) {
			return true;
		}

		throw new \Aimeos\Admin\Graphql\Exception( 'Forbidden', 403 );
	}


	/**
	 * Checks if the user has access to the domain the item belongs to
	 *
	 * Items shared between domains (e.g. prices, texts and media) are owned by the
	 * domain stored in their "<type>.domain" field. Changing them requires the same
	 * permission as changing the owning item, so they can't be modified via their
	 * own resource if the user isn't allowed to modify the owning domain. For list
	 * items, the field contains the referenced domain instead, which requires the
	 * same permission as for the referenced items themselves.
	 *
	 * @param \Aimeos\MShop\Common\Item\Iface $item Item to check
	 * @param string $action Action name
	 */
	protected function permit( \Aimeos\MShop\Common\Item\Iface $item, string $action ) : void
	{
		if( $domain = $item->get( str_replace( '/', '.', $item->getResourceType() ) . '.domain' ) ) {
			$this->access( (string) $domain, $action );
		}
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

			$ids = (array) $args['id'];
			$manager = \Aimeos\MShop::create( $this->context(), $domain );
			$filter = $manager->filter()->add( str_replace( '/', '.', $domain ) . '.id', '==', $ids )->slice( 0, count( $ids ) );

			$items = $manager->search( $filter );

			foreach( $items as $item ) {
				// @phpstan-ignore argument.type
				$this->permit( $item, 'delete' );
			}

			// Only the checked items are deleted
			$manager->delete( $items->keys()->all() );
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
			return $this->filter( \Aimeos\MShop::create( $this->context(), $domain )->get( (string) $args['id'], (array) $args['include'] ) );
		};
	}


	/**
	 * Returns the item if not removed for security reasons
	 *
	 * @param \Aimeos\MShop\Common\Item\Iface $item Item to check
	 * @return \Aimeos\MShop\Common\Item\Iface Item if not removed
	 */
	protected function filter( \Aimeos\MShop\Common\Item\Iface $item ) : \Aimeos\MShop\Common\Item\Iface
	{
		return $item;
	}


	/**
	 * Returns the items if not removed for security reasons
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
			return $this->filter( \Aimeos\MShop::create( $this->context(), $domain )->find( $args['code'], $args['include'] ) );
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

			$total = 0;
			// @phpstan-ignore argument.type
			$items = $this->filters( $manager->search( $filter, (array) $args['include'], $total )->toArray() );

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
				$ref = array_merge( $ref, $this->getRefs( (array) $subItem['item'], (string) $listDomain ) );
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
