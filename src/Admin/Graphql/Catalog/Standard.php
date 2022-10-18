<?php

/**
 * @license LGPLv3, http://opensource.org/licenses/LGPL-3.0
 * @copyright Aimeos (aimeos.org), 2022
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
	/**
	 * Returns GraphQL schema definition for the available mutations
	 *
	 * @param string $domain Domain name of the responsible manager
	 * @return array GraphQL mutation schema definition
	 */
	public function mutation( string $domain ) : array
	{
		$list = parent::mutation( $domain );

		$list['insertCatalog'] = [
			'type' => $this->outputType( $domain ),
			'args' => [
				['name' => 'input', 'type' => Type::nonNull( $this->inputType( $domain ) ), 'description' => 'Item object'],
				['name' => 'parentid', 'type' => Type::string(), 'defaultValue' => null, 'description' => 'ID of the parent category'],
				['name' => 'refid', 'type' => Type::string(), 'defaultValue' => null, 'description' => 'Category ID the new item should be inserted before'],
			],
			'resolve' => $this->insertItem( $domain ),
		];

		$list['moveCatalog'] = [
			'type' => Type::String(),
			'args' => [
				['name' => 'id', 'type' => Type::nonNull( Type::string() ), 'description' => 'ID of the category to move'],
				['name' => 'parentid', 'type' => Type::string(), 'description' => 'ID of the old parent category'],
				['name' => 'targetid', 'type' => Type::string(), 'defaultValue' => null, 'description' => 'ID of the new parent category'],
				['name' => 'refid', 'type' => Type::string(), 'defaultValue' => null, 'description' => 'Category ID the new item should be inserted before'],
			],
			'resolve' => $this->moveItem( $domain ),
		];

		return $list;
	}


	/**
	 * Returns GraphQL schema definition for the available queries
	 *
	 * @param string $domain Domain name of the responsible manager
	 * @return array GraphQL query schema definition
	 */
	public function query( string $domain ) : array
	{
		$list = parent::query( $domain );

		$list['getCatalogPath'] = [
			'type' => Type::listOf( $this->outputType( $domain ) ),
			'args' => [
				['name' => 'id', 'type' => Type::nonNull( Type::string() ), 'description' => 'Unique category ID'],
				['name' => 'include', 'type' => Type::listOf( Type::string() ), 'defaultValue' => [], 'description' => 'Domains to include'],
			],
			'resolve' => $this->getPath( $domain ),
		];

		$list['getCatalogTree'] = [
			'type' => $this->treeOutputType( $domain ),
			'args' => [
				['name' => 'id', 'type' => Type::string(), 'defaultValue' => null, 'description' => 'Unique category ID'],
				['name' => 'level', 'type' => Type::int(), 'defaultValue' => 3, 'description' => '1 = node only, 2 = with children, 3 = whole subtree'],
				['name' => 'include', 'type' => Type::listOf( Type::string() ), 'defaultValue' => [], 'description' => 'Domains to include'],
			],
			'resolve' => $this->getTree( $domain ),
		];

		$list['findCatalog'] = [
			'type' => $this->outputType( $domain ),
			'args' => [
				['name' => 'code', 'type' => Type::nonNull( Type::string() ), 'description' => 'Unique code'],
				['name' => 'include', 'type' => Type::listOf( Type::string() ), 'defaultValue' => [], 'description' => 'Domains to include'],
			],
			'resolve' => $this->findItem( $domain ),
		];

		return $list;
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
