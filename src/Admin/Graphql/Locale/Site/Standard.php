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
				'type' => $this->types()->siteOutputType( $domain ),
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
				'type' => $this->types()->siteOutputType( $domain ),
				'args' => [
					['name' => 'input', 'type' => $this->types()->inputType( $domain ), 'description' => 'Item object'],
				],
				'resolve' => $this->saveItem( $domain ),
			],
			'save' . str_replace( '/', '', ucwords( $domain, '/' ) ) . 's' => [
				'type' => Type::listOf( $this->types()->siteOutputType( $domain ) ),
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
				'type' => $this->types()->siteOutputType( $domain ),
				'args' => [
					['name' => 'code', 'type' => Type::nonNull( Type::string() ), 'description' => 'Unique code'],
					['name' => 'include', 'type' => Type::listOf( Type::string() ), 'defaultValue' => [], 'description' => 'Domains to include'],
				],
				'resolve' => $this->findItem( $domain ),
			],
			'get' . str_replace( '/', '', ucwords( $domain, '/' ) ) => [
				'type' => $this->types()->siteOutputType( $domain ),
				'args' => [
					['name' => 'id', 'type' => Type::string(), 'description' => 'Unique ID'],
					['name' => 'include', 'type' => Type::listOf( Type::string() ), 'defaultValue' => [], 'description' => 'Domains to include'],
				],
				'resolve' => $this->getItem( $domain ),
			],
			'get' . str_replace( '/', '', ucwords( $domain, '/' ) ) . 'Path' => [
				'type' => Type::listOf( $this->types()->siteOutputType( $domain ) ),
				'args' => [
					['name' => 'id', 'type' => Type::nonNull( Type::string() ), 'description' => 'Unique site ID'],
					['name' => 'include', 'type' => Type::listOf( Type::string() ), 'defaultValue' => [], 'description' => 'Domains to include'],
				],
				'resolve' => $this->getPath( $domain ),
			],
			'get' . str_replace( '/', '', ucwords( $domain, '/' ) ) . 'Tree' => [
				'type' => $this->types()->siteOutputType( $domain ),
				'args' => [
					['name' => 'id', 'type' => Type::string(), 'defaultValue' => null, 'description' => 'Unique site ID'],
					['name' => 'level', 'type' => Type::int(), 'defaultValue' => 3, 'description' => '1 = node only, 2 = with children, 3 = whole subtree'],
					['name' => 'include', 'type' => Type::listOf( Type::string() ), 'defaultValue' => [], 'description' => 'Domains to include'],
				],
				'resolve' => $this->getTree( $domain ),
			],
			'search' . str_replace( '/', '', ucwords( $domain, '/' ) ) . 's' => [
				'type' => $this->types()->searchOutputType( $domain, fn() => $this->types( $path )->siteOutputType( $path ) ),
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
	 * Returns a closure for returning the nodes from the passed ID up to the root node
	 *
	 * @param string $domain Domain path of the manager
	 * @return \Closure Anonymous method returning one item
	 */
	protected function getPath( string $domain ) : \Closure
	{
		return function( $root, $args, $context ) use ( $domain ) {
			return \Aimeos\MShop::create( $this->context(), $domain )->getPath( $args['id'], $args['include'] );
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
			return \Aimeos\MShop::create( $this->context(), $domain )->getTree( $args['id'], $args['include'], $args['level'] );
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

			$manager = \Aimeos\MShop::create( $this->context(), $domain );
			$item = $this->updateItem( $manager, $manager->create(), $entry );

			return $manager->insert( $item, $args['parentid'], $args['refid'] );
		};
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
			\Aimeos\MShop::create( $this->context(), $domain )->move( $args['id'], $args['parentid'], $args['targetid'], $args['refid'] );
			return $args['id'];
		};
	}
}
