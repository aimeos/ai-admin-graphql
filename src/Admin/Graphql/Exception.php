<?php

namespace Aimeos\Admin\Graphql;


/**
 * GraphQL exceptions which can be shown to the client
 */
class Exception extends \Exception implements \GraphQL\Error\ClientAware
{
	/**
	 * Returns if exception can be shown to the client
	 *
	 * @return bool TRUE if exception can be shown to the client, FALSE if not
	 */
	public function isClientSafe() : bool
	{
		return true;
	}
}
