<?php

/**
 * @license LGPLv3, http://opensource.org/licenses/LGPL-3.0
 * @copyright Aimeos (aimeos.org), 2022-2023
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
	 * @param \Aimeos\Admin\Graphql\Registry Type registry object
	 */
	public function __construct( \Aimeos\MShop\ContextIface $context, Registry $registry )
	{
		$this->context = $context;
		$this->registry = $registry;
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

			\Aimeos\MShop::create( $context, $domain )->delete( $args['id'] );
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

			$filter = $manager->filter()->order( $args['sort'] )->slice( $args['offset'], $args['limit'] );
			$filter->add( $filter->parse( json_decode( $args['filter'], true ) ) );

			$total = 0;
			$items = $manager->search( $filter, $args['include'], $total )->all();

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

			$entries = (array) $args['input'];
			$manager = \Aimeos\MShop::create( $context, $domain );

			$ids = array_filter( array_column( $entries, $domain . '.id' ) );
			$filter = $manager->filter()->add( $domain . '.id', '==', $ids )->slice( 0, count( $entries ) );

			$ref = [];
			foreach( $entries as $entry ) {
				$ref = array_merge( $ref, $this->getRefs( $entry, $domain ) );
			}

			$products = $manager->search( $filter, array_unique( $ref ) );
			$items = [];

			foreach( $entries as $entry )
			{
				$item = $products->get( $entry[$domain . '.id'] ?? null ) ?: $manager->create();
				$items[] = $this->updateItem( $manager, $item, $entry );
			}

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
		$ref = array_keys( $entry['lists'] ?? [] );

		foreach( $entry['lists'] ?? [] as $listDomain => $subentry )
		{
			foreach( $subentry ?? [] as $subItem ) {
				$ref = array_merge( $ref, $this->getRefs( $subItem['item'] ?? [], $listDomain ) );
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
