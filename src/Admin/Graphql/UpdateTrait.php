<?php

/**
 * @license LGPLv3, http://opensource.org/licenses/LGPL-3.0
 * @copyright Aimeos (aimeos.org), 2022-2026
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
	 * @param \Aimeos\MShop\Common\Item\AddressRef\Iface $item Item to update
	 * @param array $entries List of entries with key/value pairs of the address data
	 * @return \Aimeos\MShop\Common\Item\Iface Updated item
	 */
	protected function updateAddresses( \Aimeos\MShop\Common\Manager\Iface $manager,
		\Aimeos\MShop\Common\Item\AddressRef\Iface $item, array $entries ) : \Aimeos\MShop\Common\Item\Iface
	{
		$addressItems = $item->getAddressItems()->reverse();

		foreach( $entries as $subentry )
		{
			$address = $addressItems->pop() ?: $manager->createAddressItem();
			$item->addAddressItem( $address->fromArray( $subentry ) );
		}

		// @phpstan-ignore return.type
		return $item->deleteAddressItems( $addressItems );
	}


	/**
	 * Updates the item
	 *
	 * @param \Aimeos\MShop\Common\Manager\Iface $manager Manager object for the passed item
	 * @param \Aimeos\MShop\Common\Item\AddressRef\Iface $item Item to update
	 * @param array $entry Associative list of key/value pairs of the item data
	 * @return \Aimeos\MShop\Common\Item\Iface Updated item
	 */
	protected function updateItem( \Aimeos\MShop\Common\Manager\Iface $manager,
		\Aimeos\MShop\Common\Item\Iface $item, array $entry ) : \Aimeos\MShop\Common\Item\Iface
	{
		$item = $item->fromArray( $entry, true );

		if( isset( $entry['address'] ) && $item instanceof \Aimeos\MShop\Common\Item\AddressRef\Iface ) {
			$item = $this->updateAddresses( $manager, $item, (array) $entry['address'] );
		}

		if( isset( $entry['lists'] ) && $item instanceof \Aimeos\MShop\Common\Item\ListsRef\Iface ) {
			$item = $this->updateLists( $manager, $item, (array) $entry['lists'] );
		}

		if( isset( $entry['property'] ) && $item instanceof \Aimeos\MShop\Common\Item\PropertyRef\Iface ) {
			$item = $this->updateProperties( $manager, $item, (array) $entry['property'] );
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
			// Referenced items are created/updated with their own manager, so the caller
			// needs the same permission as for a direct write on that domain. Group
			// memberships are granted through list references, so linking a group is
			// privileged too and always requires the "save" permission.
			$perm = ( (string) $domain === 'group' ) ? 'save' : 'get';

			foreach( $list as $subentry )
			{
				if( isset( $subentry['item'] ) ) {
					$perm = 'save';
					break;
				}
			}

			$this->access( (string) $domain, $perm );

			$domainManager = \Aimeos\MShop::create( $this->context(), $domain );
			$listItems = $item->getListItems( $domain, null, null, false );
			$refItems = $item->getRefItems( $domain, null, null, false );

			foreach( $list as $subentry )
			{
				$refItem = null;
				$listId = $subentry[$resource . '.lists.id'] ?? '';
				$listType = $subentry[$resource . '.lists.type'] ?? 'default';
				$refId = $subentry['item'][$domain.'.id'] ?? $subentry[$resource . '.lists.refid'] ?? '';

				$listItem = $listItems->get( (string) $listId ) ?? $item->getListItem( $domain, (string) $listType, (string) $refId ) ?? $manager->createListItem();

				if ( isset( $subentry['item'] ) ) {
					$refBase = $listItem->getRefItem() ?? $refItems->get( (string) $refId ) ?? $domainManager->create();
					$refItem = $this->fromArrayRef( $refBase, (array) $subentry['item'], (string) $domain );
				}

				if( isset( $subentry['item']['address'] ) && $refItem instanceof \Aimeos\MShop\Common\Item\AddressRef\Iface ) {
					$refItem = $this->updateAddresses( $domainManager, $refItem, (array) $subentry['item']['address'] );
				}

				if( isset( $subentry['item']['lists'] ) && $refItem instanceof \Aimeos\MShop\Common\Item\ListsRef\Iface ) {
					$refItem = $this->updateLists( $domainManager, $refItem, (array) $subentry['item']['lists'] );
				}

				if( isset( $subentry['item']['property'] ) && $refItem instanceof \Aimeos\MShop\Common\Item\PropertyRef\Iface ) {
					$refItem = $this->updateProperties( $domainManager, $refItem, (array) $subentry['item']['property'] );
				}

				// @phpstan-ignore argument.type, argument.type
				$item->addListItem( $domain, $listItem->fromArray( $subentry, true ), $refItem );
				unset( $listItems[$listItem->getId()] );
			}

			$item->deleteListItems( $listItems );
		}

		return $item;
	}


	/**
	 * Updates a referenced item while enforcing the field-level permissions of privileged domains
	 *
	 * The generic nested writer stores referenced items in private mode, which unlocks
	 * privileged fields (e.g. customer password, group membership and account status).
	 * This method strips those fields unless the current user is allowed to change them,
	 * so editors cannot escalate privileges through nested list references. It's also
	 * used for customer items saved directly to apply the same rules.
	 *
	 * @param \Aimeos\MShop\Common\Item\Iface $item Referenced item to update
	 * @param array $entry Associative list of key/value pairs of the referenced item data
	 * @param string $domain Domain of the referenced item
	 * @return \Aimeos\MShop\Common\Item\Iface Updated referenced item
	 */
	protected function fromArrayRef( \Aimeos\MShop\Common\Item\Iface $item, array $entry, string $domain ) : \Aimeos\MShop\Common\Item\Iface
	{
		if( $item instanceof \Aimeos\MShop\Customer\Item\Iface && !$this->context()->view()->access( ['super', 'admin'] ) )
		{
			// Group membership, account status and verification are admin-only
			unset( $entry['customer.groups'], $entry['customer.status'], $entry['customer.dateverified'] );

			// Credentials and login code may only be changed for the own account
			if( $item->getId() === null || $item->getId() !== $this->context()->user()?->getId() ) {
				unset( $entry['customer.password'], $entry['customer.code'] );
			}
		}

		return $item->fromArray( $entry, true );
	}


	/**
	 * Updates the properties of the item
	 *
	 * @param \Aimeos\MShop\Common\Manager\Iface $manager Manager object for the passed item
	 * @param \Aimeos\MShop\Common\Item\PropertyRef\Iface $item Item to update
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
			// @phpstan-ignore argument.type
			$item->addPropertyItem( $propItem->fromArray( $subentry ) );
		}

		return $item->deletePropertyItems( $propItems );
	}
}
