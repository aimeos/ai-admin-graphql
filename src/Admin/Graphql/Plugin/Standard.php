<?php

/**
 * @license LGPLv3, http://opensource.org/licenses/LGPL-3.0
 * @copyright Aimeos (aimeos.org)2023-2025
 * @package Admin
 * @subpackage GraphQL
 */


namespace Aimeos\Admin\Graphql\Plugin;


/**
 * GraphQL class for special handling of plugins
 *
 * @package Admin
 * @subpackage GraphQL
 */
class Standard extends \Aimeos\Admin\Graphql\Standard
{
	use \Aimeos\Admin\Graphql\ProviderTrait;
}
