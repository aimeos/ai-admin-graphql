<?php

/**
 * @license LGPLv3, http://opensource.org/licenses/LGPL-3.0
 * @copyright Aimeos (aimeos.org), 2022-2025
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
		$resource = $item->getResourceType();

		foreach( $entries as $domain => $list )
		{
			$domainManager = \Aimeos\MShop::create( $this->context(), $domain );
			$listItems = $item->getListItems( $domain, null, null, false );
			$refItems = $item->getRefItems( $domain, null, null, false );

			foreach( $list as $subentry )
			{
				$refItem = null;
				$listId = $subentry[$resource . '.lists.id'] ?? '';
				$listType = $subentry[$resource . '.lists.type'] ?? 'default';
				$refId = $subentry['item'][$domain.'.id'] ?? $subentry[$resource . '.lists.refid'] ?? '';

				$listItem = $listItems->get( $listId ) ?? $item->getListItem( $domain, $listType, $refId ) ?? $manager->createListItem();

				if ( isset( $subentry['item'] ) ) {
					$refItem = ( $listItem->getRefItem() ?? $refItems->get( $refId ) ?? $domainManager->create() )->fromArray( $subentry['item'], true );
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

				$item->addListItem( $domain, $listItem->fromArray( $subentry, true ), $refItem );
				unset( $listItems[$listItem->getId()] );
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
