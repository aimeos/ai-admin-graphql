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

		if( isset( $entry['stock'] ) && $item instanceof \Aimeos\MShop\Product\Item\Iface ) {
			$item = $this->updateStocks( $manager, $item, (array) $entry['stock'] );
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
			if( $domain === 'customergroup' ) {
				$domain = 'customer/group';
			}

			// Referenced items are created/updated with their own manager, so the caller
			// needs the same permission as for a direct write on that domain. Group
			// memberships are granted through list references, so linking a group is
			// privileged too and always requires the "save" permission.
			$perm = ( $domain === 'customer/group' ) ? 'save' : 'get';

			foreach( $list as $subentry )
			{
				if( isset( $subentry['item'] ) ) {
					$perm = 'save';
					break;
				}
			}

			$groups = $this->context()->config()->get( 'admin/graphql/resource/' . $domain . '/' . $perm, [] );

			if( $this->context()->view()->access( $groups ) !== true ) {
				throw new \Aimeos\Admin\Graphql\Exception( 'Forbidden', 403 );
			}

			$domainManager = \Aimeos\MShop::create( $this->context(), $domain );
			$listItems = $item->getListItems( $domain, null, null, false );

			foreach( $list as $subentry )
			{
				$listId = $subentry[$resource . '.lists.id'] ?? '';
				$listType = $subentry[$resource . '.lists.type'] ?? 'default';
				$refId = $subentry['item'][$domain.'.id'] ?? $subentry[$resource . '.lists.refid'] ?? '';

				$listItem = $listItems->get( $listId ) ?? $item->getListItem( $domain, $listType, $refId ) ?? $manager->createListItem();
				$refItem = $listItem->getRefItem() ?? $domainManager->create();

				if ( isset( $subentry['item'] ) ) {
					$refItem = $this->fromArrayRef( $refItem, (array) $subentry['item'], $domain );
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

			// In private mode "customer.id" re-points the item to another row when fromArray()
			// is applied below, so the ownership check must use the ID that will actually be
			// written and not the one the base item currently carries. Otherwise an editor
			// could pass the check with their own row and redirect the write to a foreign one.
			$target = array_key_exists( 'customer.id', $entry )
				? ( $entry['customer.id'] !== null ? (string) $entry['customer.id'] : null )
				: $item->getId();

			// Credentials, login code and login e-mail (the login identifier that getCode()
			// falls back to) may only be changed for the own account
			if( $target === null || $target !== $this->context()->user() ) {
				unset( $entry['customer.password'], $entry['customer.code'], $entry['customer.email'] );
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


	/**
	 * Updates the stock items of the product
	 *
	 * @param \Aimeos\MShop\Common\Manager\Iface $manager Product manager object
	 * @param \Aimeos\MShop\Product\Item\Iface $item Product item to update
	 * @param array $entries List of entries with key/value pairs of the stock data
	 * @return \Aimeos\MShop\Common\Item\Iface Updated item
	 */
	protected function updateStocks( \Aimeos\MShop\Common\Manager\Iface $manager,
		\Aimeos\MShop\Product\Item\Iface $item, array $entries ) : \Aimeos\MShop\Common\Item\Iface
	{
		$stockItems = [];

		// Only one stock item per product and stock type is allowed
		foreach( $item->getStockItems() as $stockItem ) {
			$stockItems[$stockItem->getType()] = $stockItem;
		}

		foreach( $entries as $subentry )
		{
			$type = (string) ( $subentry['stock.type'] ?? 'default' );
			$stockItem = $stockItems[$type] ?? $manager->createStockItem();
			unset( $stockItems[$type] );

			// @phpstan-ignore argument.type
			$item->addStockItem( $stockItem->fromArray( $subentry ) );
		}

		return $item->deleteStockItems( $stockItems );
	}
}
