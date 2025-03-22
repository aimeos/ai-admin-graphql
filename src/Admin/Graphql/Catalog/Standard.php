<?php

/**
 * @license LGPLv3, http://opensource.org/licenses/LGPL-3.0
 * @copyright Aimeos (aimeos.org), 2022-2025
 * @package Admin
 * @subpackage GraphQL
 */


namespace Aimeos\Admin\Graphql\Catalog;

use GraphQL\Type\Definition\Type;


/**
 * GraphQL class for special handling of categories
 *
 * @package Admin
 * @subpackage GraphQL
 */
class Standard extends \Aimeos\Admin\Graphql\Standard
{
	private \Aimeos\MShop\Common\Manager\Iface $manager;


	/**
	 * Returns GraphQL schema definition for the available mutations
	 *
	 * @param string $domain Domain name of the responsible manager
	 * @return array GraphQL mutation schema definition
	 */
	public function mutation( string $domain ) : array
	{
		return [
			'deleteCatalog' => [
				'type' => Type::string(),
				'args' => [
					['name' => 'id', 'type' => Type::string(), 'description' => 'Item ID'],
				],
				'resolve' => $this->deleteItems( $domain ),
			],
			'deleteCatalogs' => [
				'type' => Type::listOf( Type::string() ),
				'args' => [
					['name' => 'id', 'type' => Type::listOf( Type::string() ), 'description' => 'List of item IDs'],
				],
				'resolve' => $this->deleteItems( $domain ),
			],
			'saveCatalog' => [
				'type' => $this->types()->treeOutputType( $domain ),
				'args' => [
					['name' => 'input', 'type' => $this->types()->inputType( $domain ), 'description' => 'Item object'],
				],
				'resolve' => $this->saveItem( $domain ),
			],
			'saveCatalogs' => [
				'type' => Type::listOf( $this->types()->treeOutputType( $domain ) ),
				'args' => [
					['name' => 'input', 'type' => Type::listOf( $this->types()->inputType( $domain ) ), 'description' => 'Item objects'],
				],
				'resolve' => $this->saveItems( $domain ),
			],
			'insertCatalog' => [
				'type' => $this->types()->treeOutputType( $domain ),
				'args' => [
					['name' => 'input', 'type' => Type::nonNull( $this->types()->inputType( $domain ) ), 'description' => 'Item object'],
					['name' => 'parentid', 'type' => Type::string(), 'defaultValue' => null, 'description' => 'ID of the parent category'],
					['name' => 'refid', 'type' => Type::string(), 'defaultValue' => null, 'description' => 'Category ID the new item should be inserted before'],
				],
				'resolve' => $this->insertItem( $domain ),
			],
			'moveCatalog' => [
				'type' => Type::String(),
				'args' => [
					['name' => 'id', 'type' => Type::nonNull( Type::string() ), 'description' => 'ID of the category to move'],
					['name' => 'parentid', 'type' => Type::string(), 'description' => 'ID of the old parent category'],
					['name' => 'targetid', 'type' => Type::string(), 'defaultValue' => null, 'description' => 'ID of the new parent category'],
					['name' => 'refid', 'type' => Type::string(), 'defaultValue' => null, 'description' => 'Category ID the new item should be inserted before'],
				],
				'resolve' => $this->moveItem( $domain ),
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
			'getCatalog' => [
				'type' => $this->types()->treeOutputType( $domain ),
				'args' => [
					['name' => 'id', 'type' => Type::string(), 'description' => 'Unique ID'],
					['name' => 'include', 'type' => Type::listOf( Type::string() ), 'defaultValue' => [], 'description' => 'Domains to include'],
				],
				'resolve' => $this->getItem( $domain ),
			],
			'getCatalogPath' => [
				'type' => Type::listOf( $this->types()->treeOutputType( $domain ) ),
				'args' => [
					['name' => 'id', 'type' => Type::nonNull( Type::string() ), 'description' => 'Unique category ID'],
					['name' => 'include', 'type' => Type::listOf( Type::string() ), 'defaultValue' => [], 'description' => 'Domains to include'],
				],
				'resolve' => $this->getPath( $domain ),
			],
			'getCatalogTree' => [
				'type' => $this->types()->treeOutputType( $domain ),
				'args' => [
					['name' => 'id', 'type' => Type::string(), 'defaultValue' => null, 'description' => 'Unique category ID'],
					['name' => 'level', 'type' => Type::int(), 'defaultValue' => 3, 'description' => '1 = node only, 2 = with children, 3 = whole subtree'],
					['name' => 'include', 'type' => Type::listOf( Type::string() ), 'defaultValue' => [], 'description' => 'Domains to include'],
				],
				'resolve' => $this->getTree( $domain ),
			],
			'findCatalog' => [
				'type' => $this->types()->treeOutputType( $domain ),
				'args' => [
					['name' => 'code', 'type' => Type::nonNull( Type::string() ), 'description' => 'Unique code'],
					['name' => 'include', 'type' => Type::listOf( Type::string() ), 'defaultValue' => [], 'description' => 'Domains to include'],
				],
				'resolve' => $this->findItem( $domain ),
			],
			'searchCatalogs' => [
				'type' => $this->types()->searchOutputType( $domain, fn( $path ) => $this->types()->treeOutputType( $path ) ),
				'args' => [
					['name' => 'filter', 'type' => Type::string(), 'defaultValue' => '{}', 'description' => 'Filter conditions'],
					['name' => 'include', 'type' => Type::listOf( Type::string() ), 'defaultValue' => [], 'description' => 'Domains to include'],
					['name' => 'sort', 'type' => Type::listOf( Type::string() ), 'defaultValue' => [], 'description' => 'Sort keys'],
					['name' => 'offset', 'type' => Type::int(), 'defaultValue' => 0, 'description' => 'Slice offset'],
					['name' => 'limit', 'type' => Type::int(), 'defaultValue' => 100, 'description' => 'Slice size'],
				],
				'resolve' => $this->searchItems( $domain ),
			],
			'searchCatalogTree' => [
				'type' => Type::listOf( $this->types()->treeOutputType( $domain ) ),
				'args' => [
					['name' => 'filter', 'type' => Type::string(), 'defaultValue' => '{}', 'description' => 'Filter conditions'],
					['name' => 'include', 'type' => Type::listOf( Type::string() ), 'defaultValue' => [], 'description' => 'Domains to include'],
					['name' => 'limit', 'type' => Type::int(), 'defaultValue' => 100, 'description' => 'Slice size'],
				],
				'resolve' => $this->searchTree( $domain ),
			]
		];
	}


	/**
	 * Returns the tree of parents including the given items as leaf nodes
	 *
	 * @param \Aimeos\Map $items List of items (with numeric indexes)
	 * @param array $refs List of domains to fetch in addition
	 * @return \Aimeos\Map List of parent items
	 */
	protected function getParents( \Aimeos\Map $items, array $refs ) : \Aimeos\Map
	{
		if( ( $parentIds = $items->getParentId()->filter() )->isEmpty() ) {
			return $items;
		}

		$manager = $this->manager();
		$filter = $manager->filter()
			->add( 'catalog.id', '==', $parentIds->unique() )
			->order( ['-catalog.level', 'sort:catalog:position'] )
			->slice( 0, 0x7fffffff );

		$parents = $manager->search( $filter, $refs );
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

		return $this->getParents( $items, $refs );
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
			return $this->manager()->getPath( $args['id'], $args['include'] );
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
			return $this->manager()->getTree( $args['id'], $args['include'], $args['level'] );
		};
	}


	/**
	 * Returns a closure for inserting a new node into the tree
	 *
	 * @param string $domain Domain path of the manager
	 * @return \Closure Anonymous method returning one item
	 */
	protected function insertItem( string $domain ) : \Closure
	{
		return function( $root, $args, $context ) use ( $domain ) {

			if( empty( $entry = $args['input'] ) ) {
				throw new \Aimeos\Admin\Graphql\Exception( 'Parameter "input" must not be empty' );
			}

			$manager = $this->manager();
			$item = $this->updateItem( $manager, $manager->create(), $entry );

			return $manager->insert( $item, $args['parentid'], $args['refid'] );
		};
	}


	/**
	 * Returns the manager for the site items
	 *
	 * @return \Aimeos\MShop\Common\Manager\Iface Manager object
	 */
	protected function manager() : \Aimeos\MShop\Common\Manager\Iface
	{
		if( !isset( $this->manager ) ) {
			$this->manager = \Aimeos\MShop::create( $this->context(), 'catalog' );
		}

		return $this->manager;
	}


	/**
	 * Returns a closure for moving a node within the tree
	 *
	 * @param string $domain Domain path of the manager
	 * @return \Closure Anonymous method returning one item
	 */
	protected function moveItem( string $domain ) : \Closure
	{
		return function( $root, $args, $context ) use ( $domain ) {
			$this->manager()->move( $args['id'], $args['parentid'], $args['targetid'], $args['refid'] );
			return $args['id'];
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
			$manager = $this->manager();

			$filter = $manager->filter()->order( ['-catalog.level', 'sort:catalog:position'] );
			$filter->add( $filter->parse( json_decode( $args['filter'], true ) ) );

			$items = $manager->search( $filter->slice( 0, $args['limit'] ), $args['include'] );

			foreach( $items as $key => $item )
			{
				if( isset( $items[$item->getParentId()] ) ) {
					$items[$item->getParentId()]->addChild( $item );
					unset( $items[$key] );
				}
			}

			return $this->getParents( $items->values(), $args['include'] );
		};
	}
}
