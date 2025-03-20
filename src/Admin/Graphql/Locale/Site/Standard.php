<?php

/**
 * @license LGPLv3, http://opensource.org/licenses/LGPL-3.0
 * @copyright Aimeos (aimeos.org), 2024
 * @package Admin
 * @subpackage GraphQL
 */


namespace Aimeos\Admin\Graphql\Locale\Site;

use GraphQL\Type\Definition\Type;


/**
 * GraphQL class for special handling of locale sites
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
			'delete' . str_replace( '/', '', ucwords( $domain, '/' ) ) => [
				'type' => Type::string(),
				'args' => [
					['name' => 'id', 'type' => Type::string(), 'description' => 'Item ID'],
				],
				'resolve' => $this->deleteItems( $domain ),
			],
			'delete' . str_replace( '/', '', ucwords( $domain, '/' ) ) . 's' => [
				'type' => Type::listOf( Type::string() ),
				'args' => [
					['name' => 'id', 'type' => Type::listOf( Type::string() ), 'description' => 'List of item IDs'],
				],
				'resolve' => $this->deleteItems( $domain ),
			],
			'insert' . str_replace( '/', '', ucwords( $domain, '/' ) ) => [
				'type' => $this->types()->siteOutputType(),
				'args' => [
					['name' => 'input', 'type' => Type::nonNull( $this->types()->inputType( $domain ) ), 'description' => 'Item object'],
					['name' => 'parentid', 'type' => Type::string(), 'defaultValue' => null, 'description' => 'ID of the parent site'],
					['name' => 'refid', 'type' => Type::string(), 'defaultValue' => null, 'description' => 'Site ID the new item should be inserted before'],
				],
				'resolve' => $this->insertItem( $domain ),
			],
			'move' . str_replace( '/', '', ucwords( $domain, '/' ) ) => [
				'type' => Type::String(),
				'args' => [
					['name' => 'id', 'type' => Type::nonNull( Type::string() ), 'description' => 'ID of the site to move'],
					['name' => 'parentid', 'type' => Type::string(), 'description' => 'ID of the old parent site'],
					['name' => 'targetid', 'type' => Type::string(), 'defaultValue' => null, 'description' => 'ID of the new parent site'],
					['name' => 'refid', 'type' => Type::string(), 'defaultValue' => null, 'description' => 'Site ID the new item should be inserted before'],
				],
				'resolve' => $this->moveItem( $domain ),
			],
			'save' . str_replace( '/', '', ucwords( $domain, '/' ) ) => [
				'type' => $this->types()->siteOutputType(),
				'args' => [
					['name' => 'input', 'type' => $this->types()->inputType( $domain ), 'description' => 'Item object'],
				],
				'resolve' => $this->saveItem( $domain ),
			],
			'save' . str_replace( '/', '', ucwords( $domain, '/' ) ) . 's' => [
				'type' => Type::listOf( $this->types()->siteOutputType() ),
				'args' => [
					['name' => 'input', 'type' => Type::listOf( $this->types()->inputType( $domain ) ), 'description' => 'Item objects'],
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
			'find' . str_replace( '/', '', ucwords( $domain, '/' ) ) => [
				'type' => $this->types()->siteOutputType(),
				'args' => [
					['name' => 'code', 'type' => Type::nonNull( Type::string() ), 'description' => 'Unique code'],
					['name' => 'include', 'type' => Type::listOf( Type::string() ), 'defaultValue' => [], 'description' => 'Domains to include'],
				],
				'resolve' => $this->findItem( $domain ),
			],
			'get' . str_replace( '/', '', ucwords( $domain, '/' ) ) => [
				'type' => $this->types()->siteOutputType(),
				'args' => [
					['name' => 'id', 'type' => Type::string(), 'description' => 'Unique ID'],
					['name' => 'include', 'type' => Type::listOf( Type::string() ), 'defaultValue' => [], 'description' => 'Domains to include'],
				],
				'resolve' => $this->getItem( $domain ),
			],
			'get' . str_replace( '/', '', ucwords( $domain, '/' ) ) . 'Path' => [
				'type' => Type::listOf( $this->types()->siteOutputType() ),
				'args' => [
					['name' => 'id', 'type' => Type::nonNull( Type::string() ), 'description' => 'Unique site ID'],
					['name' => 'include', 'type' => Type::listOf( Type::string() ), 'defaultValue' => [], 'description' => 'Domains to include'],
				],
				'resolve' => $this->getPath( $domain ),
			],
			'get' . str_replace( '/', '', ucwords( $domain, '/' ) ) . 'Tree' => [
				'type' => $this->types()->siteOutputType(),
				'args' => [
					['name' => 'id', 'type' => Type::string(), 'defaultValue' => null, 'description' => 'Unique site ID'],
					['name' => 'level', 'type' => Type::int(), 'defaultValue' => 3, 'description' => '1 = node only, 2 = with children, 3 = whole subtree'],
					['name' => 'include', 'type' => Type::listOf( Type::string() ), 'defaultValue' => [], 'description' => 'Domains to include'],
				],
				'resolve' => $this->getTree( $domain ),
			],
			'search' . str_replace( '/', '', ucwords( $domain, '/' ) ) . 's' => [
				'type' => $this->types()->searchOutputType( $domain, fn( $path ) => $this->types()->siteOutputType() ),
				'args' => [
					['name' => 'filter', 'type' => Type::string(), 'defaultValue' => '{}', 'description' => 'Filter conditions'],
					['name' => 'include', 'type' => Type::listOf( Type::string() ), 'defaultValue' => [], 'description' => 'Domains to include'],
					['name' => 'sort', 'type' => Type::listOf( Type::string() ), 'defaultValue' => [], 'description' => 'Sort keys'],
					['name' => 'offset', 'type' => Type::int(), 'defaultValue' => 0, 'description' => 'Slice offset'],
					['name' => 'limit', 'type' => Type::int(), 'defaultValue' => 100, 'description' => 'Slice size'],
				],
				'resolve' => $this->searchItems( $domain ),
			],
			'search' . str_replace( '/', '', ucwords( $domain, '/' ) ) . 'Tree' => [
				'type' => Type::listOf( $this->types()->siteOutputType() ),
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
	 * Returns the item if not removed for security reasons
	 *
	 * @param \Aimeos\MShop\Common\Item\Iface $item Item to check
	 * @return \Aimeos\MShop\Common\Item\Iface Item if not removed
	 */
	protected function filter( \Aimeos\MShop\Common\Item\Iface $item ) : \Aimeos\MShop\Common\Item\Iface
	{
		$siteid = (string) $this->context()->user()?->getSiteId();

		if( $item->getSiteId() && strncmp( $item->getSiteId(), $siteid, strlen( $siteid ) ) ) {
			throw new \Aimeos\Admin\Graphql\Exception( 'Forbidden', 403 );
		}

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
		$list = [];
		$siteid = (string) $this->context()->user()?->getSiteId();

		foreach( $items as $id => $item )
		{
			if( !( $item->getSiteId() && strncmp( $item->getSiteId(), $siteid, strlen( $siteid ) ) ) ) {
				$list[$id] = $item;
			}
		}

		return $list;
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
			->add( 'locale.site.siteid', '=~', (string) $this->context()->user()?->getSiteId() )
			->add( 'locale.site.id', '==', $parentIds->unique() )
			->order( ['-locale.site.level', 'sort:locale.site:position'] )
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
			return $this->filters( $this->manager()->getPath( $args['id'], $args['include'] ) );
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
			return $this->filter( $this->manager()->getTree( $args['id'], $args['include'], $args['level'] ) );
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

			$this->access( $domain, 'insert' );
			$manager = $this->manager();
			$item = $manager->create()->fromArray( $entry, true );

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
			$this->manager = \Aimeos\MShop::create( $this->context(), 'locale/site' );
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

			$this->access( $domain, 'move' );
			$this->manager()->move( $args['id'], $args['parentid'], $args['targetid'], $args['refid'] );

			return $args['id'];
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
			$prefix = str_replace( '/', '.', $domain );

			$filter = $manager->filter()->order( $args['sort'] )->slice( $args['offset'], $args['limit'] );
			$filter->add( $prefix . '.siteid', '=~', (string) $this->context()->user()?->getSiteId() );
			$filter->add( $filter->parse( json_decode( $args['filter'], true ) ) );

			$total = 0;
			$items = $manager->search( $filter, $args['include'], $total )->toArray();

			return [
				'items' => $items,
				'total' => $total
			];
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

			$filter = $manager->filter()->order( ['-locale.site.level', 'sort:locale.site:position'] );
			$filter->add( 'locale.site.siteid', '=~', (string) $this->context()->user()?->getSiteId() );
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


	/**
	 * Updates the item
	 *
	 * @param \Aimeos\MShop\Common\Manager\Iface $manager Manager object for the passed item
	 * @param \Aimeos\MShop\Common\Item\AdddressRef\Iface $item Item to update
	 * @param array $entry Associative list of key/value pairs of the item data
	 * @return \Aimeos\MShop\Common\Item\Iface Updated item
	 */
	protected function updateItem( \Aimeos\MShop\Common\Manager\Iface $manager,
		\Aimeos\MShop\Common\Item\Iface $item, array $entry ) : \Aimeos\MShop\Common\Item\Iface
	{
		$siteid = (string) $this->context()->user()?->getSiteId();

		if( !$siteid || !$item->getSiteId() || strncmp( $item->getSiteId(), $siteid, strlen( $siteid ) ) ){
			throw new \Aimeos\Admin\Graphql\Exception( 'Forbidden', 403 );
		}

		return $item->fromArray( $entry, true );
	}
}
