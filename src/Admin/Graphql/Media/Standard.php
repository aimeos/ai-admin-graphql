<?php

/**
 * @license LGPLv3, http://opensource.org/licenses/LGPL-3.0
 * @copyright Aimeos (aimeos.org), 2024
 * @package Admin
 * @subpackage GraphQL
 */


namespace Aimeos\Admin\Graphql\Media;

use GraphQL\Type\Definition\Type;
use GraphQL\Type\Definition\ObjectType;
use GraphQL\Type\Definition\InputObjectType;
use Aimeos\GraphQL\Type\Definition\Upload;


/**
 * GraphQL class for special handling of media files
 *
 * @package Admin
 * @subpackage GraphQL
 */
class Standard extends \Aimeos\Admin\Graphql\Standard
{
	private $type;


	/**
	 * Returns GraphQL schema definition for the available mutations
	 *
	 * @param string $domain Domain name of the responsible manager
	 * @return array GraphQL mutation schema definition
	 */
	public function mutation( string $domain ) : array
	{
		$list = parent::mutation( $domain );

		$list['saveMedia'] = [
			'type' => $this->types()->outputType( $domain ),
			'args' => [
				['name' => 'input', 'type' => $this->mediaInputType( $domain ), 'description' => 'Item object'],
			],
			'resolve' => $this->saveItem( $domain ),
		];
		$list['saveMedias'] = [
			'type' => Type::listOf( $this->types()->outputType( $domain ) ),
			'args' => [
				['name' => 'input', 'type' => Type::listOf( $this->mediaInputType( $domain ) ), 'description' => 'Item objects'],
			],
			'resolve' => $this->saveItems( $domain ),
		];

		return $list;
	}


	/**
	 * Defines the GraphQL media input type
	 *
	 * @param string $path Path of the domain manager
	 * @return \GraphQL\Type\Definition\InputObjectType Input type definition
	 */
	public function mediaInputType( string $path ) : InputObjectType
	{
		$name = 'mediaInput';

		if( isset( $this->type ) ) {
			return $this->type;
		}

		return $this->type = new InputObjectType( [
			'name' => $name,
			'fields' => function() use ( $path ) {

				$manager = \Aimeos\MShop::create( $this->context(), $path );
				$list = $this->types()->fields( $manager->getSearchAttributes( false ) );
				$item = $manager->create();

				if( $item instanceof \Aimeos\MShop\Common\Item\ListsRef\Iface ) {
					$list['lists'] = $this->types()->listsInputType( $path . '/lists' );
				}

				if( $item instanceof \Aimeos\MShop\Common\Item\PropertyRef\Iface ) {
					$list['property'] = Type::listOf( $this->types()->inputType( $path . '/property' ) );
				}

				$list['file'] = ['type' => Upload::type(), 'description' => 'File upload'];
				$list['filepreview'] = ['type' => Upload::type(), 'description' => 'Preview file upload'];

				return $list;
			},
			'parseValue' => function( array $values ) use ( $path ) {
				return $this->types()->prefix( $path, $values );
			}
		] );
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
		$item = $item->fromArray( $entry, true );

		if( isset( $entry['file'] ) ) {
			$item = $manager->upload( $item, $entry['file'], $entry['filepreview'] ?? null );
		}

		if( isset( $entry['lists'] ) && $item instanceof \Aimeos\MShop\Common\Item\ListsRef\Iface ) {
			$item = $this->updateLists( $manager, $item, $entry['lists'] );
		}

		if( isset( $entry['property'] ) && $item instanceof \Aimeos\MShop\Common\Item\PropertyRef\Iface ) {
			$item = $this->updateProperties( $manager, $item, $entry['property'] );
		}

		return $item;
	}
}
