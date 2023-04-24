<?php

/**
 * @license LGPLv3, http://opensource.org/licenses/LGPL-3.0
 * @copyright Aimeos (aimeos.org), 2022-2023
 * @package Admin
 * @subpackage GraphQL
 */


namespace Aimeos\Admin\Graphql;


/**
 * Trait providing the methods for updating the items
 *
 * @package Admin
 * @subpackage GraphQL
 */
trait UpdateTrait
{
	/**
	 * Updates the addresses of the item
	 *
	 * @param \Aimeos\MShop\Common\Manager\Iface $manager Manager object for the passed item
	 * @param \Aimeos\MShop\Common\Item\AdddressRef\Iface $item Item to update
	 * @param array $entries List of entries with key/value pairs of the address data
	 * @return \Aimeos\MShop\Common\Item\Iface Updated item
	 */
	protected function updateAddresses( \Aimeos\MShop\Common\Manager\Iface $manager,
		\Aimeos\MShop\Common\Item\AdddressRef\Iface $item, array $entries ) : \Aimeos\MShop\Common\Item\Iface
	{
		$addressItems = $item->getAddresses()->reverse();

		foreach( $entries as $subentry )
		{
			$address = $addressItems->pop() ?: $manager->createAddressItem();
			$item->addAddressItem( $address->fromArray( $subentry ) );
		}

		return $item->deleteAddressItems( $addressItems );
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

		if( isset( $entry['address'] ) && $item instanceof \Aimeos\MShop\Common\Item\AddressRef\Iface ) {
			$item = $this->updateAddresses( $manager, $item, $entry['address'] );
		}

		if( isset( $entry['lists'] ) && $item instanceof \Aimeos\MShop\Common\Item\ListsRef\Iface ) {
			$item = $this->updateLists( $manager, $item, $entry['lists'] );
		}

		if( isset( $entry['property'] ) && $item instanceof \Aimeos\MShop\Common\Item\PropertyRef\Iface ) {
			$item = $this->updateProperties( $manager, $item, $entry['property'] );
		}

		return $item;
	}


	/**
	 * Updates the list references of the item
	 *
	 * @param \Aimeos\MShop\Common\Manager\Iface $manager Manager object for the passed item
	 * @param \Aimeos\MShop\Common\Item\ListsRef\Iface $item Item to update
	 * @param array $entries List of entries with key/value pairs of the reference data
	 * @return \Aimeos\MShop\Common\Item\Iface Updated item
	 */
	protected function updateLists( \Aimeos\MShop\Common\Manager\Iface $manager,
		\Aimeos\MShop\Common\Item\ListsRef\Iface $item, array $entries ) : \Aimeos\MShop\Common\Item\Iface
	{
		foreach( $entries as $domain => $list )
		{
			$domainManager = \Aimeos\MShop::create( $this->context(), $domain );
			$listItems = $item->getListItems( $domain )->reverse();

			foreach( $list as $subentry )
			{
				$listItem = $listItems->find( function ( $value ) use ( $subentry, $domain ) {
				    return ( $subentry['item'][$domain.'.id'] ?? '' ) === $value->getRefId();
				}, $manager->createListItem() );
				$listItems->remove( [$listItem->getId()] );

				$refItem = isset( $subentry['item'] ) ? $domainManager->create()->fromArray( $subentry['item'], true ) : null;
				if ( $oldRefItem = $listItem->getRefItem() ) {
				    foreach ( $oldRefItem->getListItems() as $subListItem )
				    {
					$refItem->addListItem($subListItem->getDomain(), $subListItem, $subListItem->getRefItem());
				    }
				}

				if( isset( $subentry['item']['address'] ) && $refItem instanceof \Aimeos\MShop\Common\Item\AddressRef\Iface ) {
					$refItem = $this->updateAddresses( $domainManager, $refItem, $subentry['item']['address'] );
				}

				if( isset( $subentry['item']['lists'] ) && $refItem instanceof \Aimeos\MShop\Common\Item\ListsRef\Iface ) {
					$refItem = $this->updateLists( $domainManager, $refItem, $subentry['item']['lists'] );
				}

				if( isset( $subentry['item']['property'] ) && $refItem instanceof \Aimeos\MShop\Common\Item\PropertyRef\Iface ) {
					$refItem = $this->updateProperties( $domainManager, $refItem, $subentry['item']['property'] );
				}

				$item->addListItem( $domain, $listItem->fromArray( $subentry ), $refItem );
			}

			$item->deleteListItems( $listItems );
		}

		return $item;
	}


	/**
	 * Updates the properties of the item
	 *
	 * @param \Aimeos\MShop\Common\Manager\Iface $manager Manager object for the passed item
	 * @param \Aimeos\MShop\Common\Item\ListsRef\Iface $item Item to update
	 * @param array $entries List of entries with key/value pairs of the property data
	 * @return \Aimeos\MShop\Common\Item\Iface Updated item
	 */
	protected function updateProperties( \Aimeos\MShop\Common\Manager\Iface $manager,
		\Aimeos\MShop\Common\Item\PropertyRef\Iface $item, array $entries ) : \Aimeos\MShop\Common\Item\Iface
	{
		$propItems = $item->getPropertyItems()->reverse();

		foreach( $entries as $subentry )
		{
			$propItem = $propItems->pop() ?: $manager->createPropertyItem();
			$item->addPropertyItem( $propItem->fromArray( $subentry ) );
		}

		return $item->deletePropertyItems( $propItems );
	}
}
