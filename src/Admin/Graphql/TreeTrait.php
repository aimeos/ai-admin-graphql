<?php

/**
 * @license LGPLv3, http://opensource.org/licenses/LGPL-3.0
 * @copyright Aimeos (aimeos.org), 2026
 * @package Admin
 * @subpackage GraphQL
 */


namespace Aimeos\Admin\Graphql;


/**
 * Trait providing the GraphQL resolvers of tree managers which differ from list managers
 *
 * Tree items are retrieved as paths or (sub-)trees and inserted at a position in the
 * tree instead of being saved like list items. The retrieved items are limited to the
 * ones the user is allowed to retrieve, e.g. sites outside the site tree of the user
 * aren't returned.
 *
 * @package Admin
 * @subpackage GraphQL
 */
trait TreeTrait
{
	/**
	 * Ensures that the user has access to the given domain and action
	 *
	 * @param string $domain Domain path of the manager
	 * @param string $action Action name
	 * @return bool Always TRUE because an exception is thrown if access isn't allowed
	 */
	abstract protected function access( string $domain, string $action ) : bool;


	/**
	 * Returns the context object
	 *
	 * @return \Aimeos\MShop\ContextIface Context object
	 */
	abstract protected function context() : \Aimeos\MShop\ContextIface;


	/**
	 * Returns the item if the user is allowed to retrieve it
	 *
	 * @param \Aimeos\MShop\Common\Item\Iface $item Item to check
	 * @return \Aimeos\MShop\Common\Item\Iface Passed item
	 */
	abstract protected function filter( \Aimeos\MShop\Common\Item\Iface $item ) : \Aimeos\MShop\Common\Item\Iface;


	/**
	 * Returns the items if not removed for security reasons
	 *
	 * @param iterable $items List of items to check
	 * @return iterable List of items not removed
	 */
	abstract protected function filters( iterable $items ) : iterable;


	/**
	 * Returns the domains of the related items the user is allowed to retrieve
	 *
	 * @param mixed $include Domain name or list of domain names whose items should be fetched too
	 * @return array List of domain names the user is allowed to retrieve
	 */
	abstract protected function included( $include ) : array;


	/**
	 * Updates the item
	 *
	 * @param \Aimeos\MShop\Common\Manager\Iface $manager Manager object for the passed item
	 * @param \Aimeos\MShop\Common\Item\Iface $item Item to update
	 * @param array $entry Associative list of key/value pairs of the item data
	 * @return \Aimeos\MShop\Common\Item\Iface Updated item
	 */
	abstract protected function updateItem( \Aimeos\MShop\Common\Manager\Iface $manager,
		\Aimeos\MShop\Common\Item\Iface $item, array $entry ) : \Aimeos\MShop\Common\Item\Iface;


	/**
	 * Returns the tree of parents including the given items as leaf nodes
	 *
	 * @param \Aimeos\MShop\Common\Manager\Iface $manager Manager for the tree items
	 * @param \Aimeos\Map $items List of items (with numeric indexes)
	 * @param array $refs List of domains to fetch in addition
	 * @return \Aimeos\Map List of parent items
	 */
	protected function getParents( \Aimeos\MShop\Common\Manager\Iface $manager, \Aimeos\Map $items, array $refs ) : \Aimeos\Map
	{
		if( ( $parentIds = $items->getParentId()->filter() )->isEmpty() ) {
			return $items;
		}

		$prefix = join( '.', $manager->type() );

		$filter = $manager->filter()
			->add( $prefix . '.id', '==', $parentIds->unique() )
			->order( ['-' . $prefix . '.level', 'sort:' . $prefix . ':position'] )
			->slice( 0, 0x7fffffff );
		\Aimeos\Admin\Graphql::restrict( $this->context(), $manager, $filter );

		// parents the user isn't allowed to retrieve are skipped, e.g. sites outside the user's site tree
		// @phpstan-ignore argument.type
		if( ( $parents = $manager->search( $filter, $refs ) )->isEmpty() ) {
			return $items;
		}

		$indexes = $parentIds->unique()->flip();
		$itemkeys = $items->getId()->flip();

		foreach( $parents as $pid => $parent )
		{
			if( isset( $itemkeys[$pid] ) ) {
				$items[$itemkeys[$pid]]->addChild( $items[$indexes[$pid]] );
				unset( $items[$indexes[$pid]] );
			} else {
				$items[$indexes[$pid]] = $parent->addChild( $items[$indexes[$pid]] );
			}
		}

		return $this->getParents( $manager, $items, $refs );
	}


	/**
	 * Returns a closure for returning the nodes from the passed ID up to the root node
	 *
	 * @param string $domain Domain path of the manager
	 * @return \Closure Anonymous method returning one item
	 */
	protected function getPath( string $domain ) : \Closure
	{
		return function( $root, $args, $context ) use ( $domain ) {
			$this->access( $domain, 'get' );
			// @phpstan-ignore argument.type
			$items = \Aimeos\MShop::create( $this->context(), $domain )->getPath( $args['id'], $this->included( $args['include'] ) );

			// the path contains the parent nodes the user may not be allowed to retrieve, e.g. sites
			return $this->filters( $items->filter( fn( $item ) => \Aimeos\Admin\Graphql::visible( $this->context(), $item ) ) );
		};
	}


	/**
	 * Returns a closure for returning the node tree
	 *
	 * @param string $domain Domain path of the manager
	 * @return \Closure Anonymous method returning one item
	 */
	protected function getTree( string $domain ) : \Closure
	{
		return function( $root, $args, $context ) use ( $domain ) {
			$this->access( $domain, 'get' );
			// @phpstan-ignore argument.type
			return $this->filter( \Aimeos\MShop::create( $this->context(), $domain )->getTree( $args['id'], $this->included( $args['include'] ), $args['level'] ) );
		};
	}


	/**
	 * Returns a closure for inserting a new node into the tree
	 *
	 * @param string $domain Domain path of the manager
	 * @param string $action Action the user must be allowed to execute, e.g. "insert"
	 * @return \Closure Anonymous method returning the inserted item
	 */
	protected function insertItem( string $domain, string $action = 'insert' ) : \Closure
	{
		return function( $root, $args, $context ) use ( $domain, $action ) {

			if( empty( $entry = $args['input'] ) ) {
				throw new \Aimeos\Admin\Graphql\Exception( 'Parameter "input" must not be empty' );
			}

			$this->access( $domain, $action );
			$manager = \Aimeos\MShop::create( $this->context(), $domain );
			$item = $this->updateItem( $manager, $manager->create(), (array) $entry );

			return $manager->insert( $item, $args['parentid'], $args['refid'] );
		};
	}


	/**
	 * Returns a closure for searching the tree
	 *
	 * @param string $domain Domain path of the manager
	 * @return \Closure Anonymous method returning one item
	 */
	protected function searchTree( string $domain ) : \Closure
	{
		return function( $root, $args, $context ) use ( $domain ) {

			$this->access( $domain, 'get' );
			$manager = \Aimeos\MShop::create( $this->context(), $domain );
			$prefix = join( '.', $manager->type() );

			$filter = $manager->filter()->order( ['-' . $prefix . '.level', 'sort:' . $prefix . ':position'] );
			$filter->add( $filter->parse( (array) json_decode( (string) $args['filter'], true ) ) );
			\Aimeos\Admin\Graphql::restrict( $this->context(), $manager, $filter );

			$include = $this->included( $args['include'] );
			// @phpstan-ignore argument.type
			$items = $manager->search( $filter->slice( 0, (int) $args['limit'] ), $include );

			foreach( $items as $key => $item )
			{
				if( isset( $items[$item->getParentId()] ) ) {
					$items[$item->getParentId()]->addChild( $item );
					unset( $items[$key] );
				}
			}

			return $this->getParents( $manager, $items->values(), $include );
		};
	}
}
