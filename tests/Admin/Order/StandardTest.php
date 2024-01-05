<?php

/**
 * @license LGPLv3, http://opensource.org/licenses/LGPL-3.0
 * @copyright Aimeos (aimeos.org), 2024
 */


namespace Aimeos\Admin\Order;


class StandardTest extends \PHPUnit\Framework\TestCase
{
	private $context;


	protected function setUp() : void
	{
		\Aimeos\MShop::cache( true );
		$this->context = \TestHelper::context();
		$this->context->config()->set( 'admin/graphql/debug', true );
		$this->context->setView( \TestHelper::view( 'unittest', $this->context->config() ) );
	}


	public function testSearchOrders()
	{
		$body = '{"query":"query {\n  searchOrders(filter: \"{}\", include: [\"order/address\",\"order/product\",\"order/service\"]) {\n    items {\n      id\n      sitecode\n      address {\n        id\n      }\n      product {\n        id\n      }\n      service {\n        id\n      }\n    }\n  }\n}\n","variables":{},"operationName":null}';
		$request = new \Nyholm\Psr7\ServerRequest( 'POST', 'localhost', [], $body );

		$response = \Aimeos\Admin\Graphql::execute( $this->context, $request );

		$this->assertStringContainsString( '"sitecode":"unittest"', (string) $response->getBody() );
	}
}
