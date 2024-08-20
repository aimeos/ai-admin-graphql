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


	public function testAggregateOrders()
	{
		$body = '{"query":"query {\n  aggregateOrders(key: [\"order.price\"]) {\n    aggregates\n  }\n}\n","variables":{},"operationName":null}';
		$request = new \Nyholm\Psr7\ServerRequest( 'POST', 'localhost', [], $body );

		$response = \Aimeos\Admin\Graphql::execute( $this->context, $request );

		$this->assertStringContainsString( '"aggregates":"{\"13.50\":1,\"2400.00\":1,\"672.00\":1,\"53.50\":1}"', (string) $response->getBody() );
	}


	public function testAggregateOrdersNested()
	{
		$body = '{"query":"query {\n  aggregateOrders(key: [\"order.statuspayment\", \"order.price\"]) {\n    aggregates\n  }\n}\n","variables":{},"operationName":null}';
		$request = new \Nyholm\Psr7\ServerRequest( 'POST', 'localhost', [], $body );

		$response = \Aimeos\Admin\Graphql::execute( $this->context, $request );

		$this->assertStringContainsString( '"aggregates":"{\"5\":{\"13.50\":1},\"6\":{\"2400.00\":1,\"672.00\":1,\"53.50\":1}}"', (string) $response->getBody() );
	}


	public function testAggregateOrdersNestedSum()
	{
		$body = '{"query":"query {\n  aggregateOrders(key: [\"order.statuspayment\", \"order.price\"], value: \"order.price\", type: \"sum\") {\n    aggregates\n  }\n}\n","variables":{},"operationName":null}';
		$request = new \Nyholm\Psr7\ServerRequest( 'POST', 'localhost', [], $body );

		$response = \Aimeos\Admin\Graphql::execute( $this->context, $request );

		$this->assertStringContainsString( '"aggregates":"{\"5\":{\"13.50\":\"13.50\"},\"6\":{\"2400.00\":\"2400.00\",\"672.00\":\"672.00\",\"53.50\":\"53.50\"}}"', (string) $response->getBody() );
	}


	public function testSearchOrders()
	{
		$body = '{"query":"query {\n  searchOrders(filter: \"{}\", include: [\"order/address\",\"order/coupon\",\"order/product\",\"order/service\",\"order/status\"]) {\n    items {\n      id\n      sitecode\n      address {\n        id\n      }\n      coupon {\n        code\n      }\n      product {\n        id\n        attribute {\n          id\n        }\n        product {\n          id\n          attribute {\n            id\n          }\n        }\n      }\n      service {\n        id\n        attribute {\n          id\n        }\n        transaction {\n          id\n        }\n      }\n      status {\n        id\n      }\n    }\n  total\n  }\n}\n","variables":{},"operationName":null}';
		$request = new \Nyholm\Psr7\ServerRequest( 'POST', 'localhost', [], $body );

		$response = \Aimeos\Admin\Graphql::execute( $this->context, $request );
		$result = json_decode( (string) $response->getBody(), true );
		$items = $result['data']['searchOrders']['items'] ?? [];

		$this->assertEquals( $result['data']['searchOrders']['total'], count( $items ) );
		$this->assertEquals( 'unittest', $items[0]['sitecode'] );
		$this->assertEquals( 2, count( $items[0]['address'] ) );
		$this->assertEquals( 2, count( $items[0]['coupon'] ) );
		$this->assertEquals( 4, count( $items[0]['product'] ) );
		$this->assertEquals( 3, count( $items[0]['product'][0]['attribute'] ) );
		$this->assertEquals( 0, count( $items[0]['product'][0]['product'] ) );
		$this->assertEquals( 2, count( $items[0]['service'] ) );
		$this->assertEquals( 0, count( $items[0]['service'][0]['attribute'] ) );
		$this->assertEquals( 1, count( $items[0]['status'] ) );
	}


	public function testSaveOrder()
	{
		$stub = $this->getMockBuilder( '\\Aimeos\\MShop\\Order\\Manager\\Standard' )
			->setConstructorArgs( array( $this->context ) )
			->onlyMethods( ['save'] )
			->getMock();

		$stub->expects( $this->once() )->method( 'save' )->willReturn( $stub->create( ['order.id' => 123, 'order.channel' => 'unittest'] ) );

		\Aimeos\MShop::inject( '\\Aimeos\\MShop\\Order\\Manager\\Standard', $stub );

		$body = '{"query":"mutation {\n  saveOrder(input: {\n    channel: \"unittest\"\n  }) {\n    id\n    channel\n  }\n}\n","variables":{},"operationName":null}';
		$request = new \Nyholm\Psr7\ServerRequest( 'POST', 'localhost', [], $body );

		$response = \Aimeos\Admin\Graphql::execute( $this->context, $request );

		$this->assertStringContainsString( '"channel":"unittest"', (string) $response->getBody() );
	}
}
