<?php

/**
 * @license LGPLv3, http://opensource.org/licenses/LGPL-3.0
 * @copyright Aimeos (aimeos.org), 2022
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

	private $context;
	private $registry;


	/**
	 * Initializes the object
	 *
	 * @param \Aimeos\MShop\ContextIface $context Context object
	 * @param \Aimeos\Admin\Graphql\Registry Type registry object
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

			$context = $this->context();
			$groups = $context->config()->get( 'admin/graphql/resource/' . $domain . '/delete', [] );

			if( $context->view()->access( $groups ) !== true ) {
				throw new \Aimeos\Admin\Graphql\Exception( 'Forbidden', 403 );
			}

			$ids = (array) $args['id'];
			$manager = \Aimeos\MShop::create( $context, $domain );
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

			$context = $this->context();
			$groups = $context->config()->get( 'admin/graphql/resource/' . $domain . '/get', [] );

			if( $context->view()->access( $groups ) !== true ) {
				throw new \Aimeos\Admin\Graphql\Exception( 'Forbidden', 403 );
			}

			return \Aimeos\MShop::create( $context, $domain )->get( $args['id'], $args['include'] );
		};
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

			$context = $this->context();
			$groups = $context->config()->get( 'admin/graphql/resource/' . $domain . '/get', [] );

			if( $context->view()->access( $groups ) !== true ) {
				throw new \Aimeos\Admin\Graphql\Exception( 'Forbidden', 403 );
			}

			return \Aimeos\MShop::create( $context, $domain )->find( $args['code'], $args['include'] );
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

			$context = $this->context();
			$groups = $context->config()->get( 'admin/graphql/resource/' . $domain . '/get', [] );

			if( $context->view()->access( $groups ) !== true ) {
				throw new \Aimeos\Admin\Graphql\Exception( 'Forbidden', 403 );
			}

			return \Aimeos\MShop::create( $context, $domain )->find( $args['code'], [], $args['domain'] );
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

			$context = $this->context();
			$groups = $context->config()->get( 'admin/graphql/resource/' . $domain . '/get', [] );

			if( $context->view()->access( $groups ) !== true ) {
				throw new \Aimeos\Admin\Graphql\Exception( 'Forbidden', 403 );
			}

			$manager = \Aimeos\MShop::create( $context, $domain );

			// @phpstan-ignore argument.type
			$filter = $manager->filter()->order( $args['sort'] )->slice( (int) $args['offset'], (int) $args['limit'] );
			// @phpstan-ignore argument.type
			$filter->add( $filter->parse( json_decode( (string) $args['filter'], true ) ) );

			return $manager->search( $filter, $args['include'] )->all();
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

			$context = $this->context();
			$groups = $context->config()->get( 'admin/graphql/resource/' . $domain . '/save', [] );

			if( $context->view()->access( $groups ) !== true ) {
				throw new \Aimeos\Admin\Graphql\Exception( 'Forbidden', 403 );
			}

			if( empty( $entry = $args['input'] ) ) {
				throw new \Aimeos\Admin\Graphql\Exception( 'Parameter "input" must not be empty' );
			}

			$ref = $this->getRefs( $entry, $domain );
			$manager = \Aimeos\MShop::create( $context, $domain );

			if( isset( $entry[$domain . '.id'] ) ) {
				$item = $manager->get( $entry[$domain . '.id'], $ref );
			} else {
				$item = $manager->create();
			}

			return $manager->save( $this->updateItem( $manager, $item, $entry ) );
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

			$context = $this->context();
			$groups = $context->config()->get( 'admin/graphql/resource/' . $domain . '/save', [] );

			if( $context->view()->access( $groups ) !== true ) {
				throw new \Aimeos\Admin\Graphql\Exception( 'Forbidden', 403 );
			}

			if( empty( $entries = $args['input'] ) ) {
				throw new \Aimeos\Admin\Graphql\Exception( 'Parameter "input" must not be empty' );
			}

			$manager = \Aimeos\MShop::create( $context, $domain );

			$ids = array_filter( array_column( $entries, $domain . '.id' ) );
			$filter = $manager->filter()->add( $domain . '.id', '==', $ids )->slice( 0, count( $entries ) );

			$ref = [];
			foreach( $entries as $entry ) {
				$ref = array_merge( $ref, $this->getRefs( (array) $entry, $domain ) );
			}

			// @phpstan-ignore argument.type, argument.type
			$map = $manager->search( $filter, array_unique( $ref ) );
			$items = [];

			foreach( $entries as $entry )
			{
				if( empty( $entry[$domain . '.id'] ) ) {
					$item = $manager->create();
				} elseif( ( $item = $map->get( (string) $entry[$domain . '.id'] ) ) === null ) {
					throw new \Aimeos\Admin\Graphql\Exception( sprintf( 'Item with ID "%1$s" not found', (string) $entry[$domain . '.id'] ), 404 );
				}

				$items[] = $this->updateItem( $manager, $item, $entry );
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

		return array_unique( $ref );
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
