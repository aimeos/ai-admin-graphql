<?php

/**
 * @license LGPLv3, http://opensource.org/licenses/LGPL-3.0
 * @copyright Aimeos (aimeos.org), 2024-2025
 */


namespace Aimeos\Admin\Product;


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


	public function testGetProduct()
	{
		$id = \Aimeos\MShop::create( $this->context, 'product' )->find( 'CNC' );

		$body = '{"query":"query {\n  getProduct(id: \"' . $id . '\", include: [\"stock\"]) {\n  id\n  code\n  stock {\n    type\n  }\n  }\n}\n","variables":{},"operationName":null}';
		$request = new \Nyholm\Psr7\ServerRequest( 'POST', 'localhost', [], $body );

		$response = \Aimeos\Admin\Graphql::execute( $this->context, $request );

		$this->assertStringContainsString( '"code":"CNC"', (string) $response->getBody() );
		$this->assertStringContainsString( '"stock":[{"type":"default"}]', (string) $response->getBody() );
	}


	public function testFindProduct()
	{
		$body = '{"query":"query {\n  findProduct(code: \"CNC\") {\n  id\n  code\n  }\n}\n","variables":{},"operationName":null}';
		$request = new \Nyholm\Psr7\ServerRequest( 'POST', 'localhost', [], $body );

		$response = \Aimeos\Admin\Graphql::execute( $this->context, $request );

		$this->assertStringContainsString( '"code":"CNC"', (string) $response->getBody() );
	}


	public function testFindProductType()
	{
		$body = '{"query":"query {\n  findProductType(code: \"default\", domain: \"product\") {\n  id\n  code\n  }\n}\n","variables":{},"operationName":null}';
		$request = new \Nyholm\Psr7\ServerRequest( 'POST', 'localhost', [], $body );

		$response = \Aimeos\Admin\Graphql::execute( $this->context, $request );

		$this->assertStringContainsString( '"code":"default"', (string) $response->getBody() );
	}


	public function testSearchProducts()
	{
		$manager = \Aimeos\MShop::create( $this->context, 'product' );
		$filter = $manager->filter()->add( 'product.code', '==', ['CNC', 'CNE'] );
		$ids = $manager->search( $filter )->keys()->all();

		$search = addslashes( addslashes( json_encode( ['==' => ['product.id' => $ids]] ) ) );
		$body = '{"query":"query {\n  searchProducts(filter: \"' . $search . '\", include: [\"stock\"]) {\n  items {\n    id\n    code\n  stock {\n    type\n  }\n  }\n  total\n  }\n}\n","variables":{},"operationName":null}';
		$request = new \Nyholm\Psr7\ServerRequest( 'POST', 'localhost', [], $body );

		$response = \Aimeos\Admin\Graphql::execute( $this->context, $request );

		$this->assertStringContainsString( '"code":"CNC"', (string) $response->getBody() );
		$this->assertStringContainsString( '"code":"CNE"', (string) $response->getBody() );
		$this->assertStringContainsString( '"stock":[{"type":"default"}]', (string) $response->getBody() );
	}


	public function testSaveProduct()
	{
		$stockStub = $this->getMockBuilder( '\\Aimeos\\MShop\\Stock\\Manager\\Standard' )
			->setConstructorArgs( array( $this->context ) )
			->onlyMethods( ['save'] )
			->getMock();

		$stockStub->expects( $this->once() )->method( 'save' )->willReturnCallback( fn( $item ) => $item->setId( 123 ) );

		\Aimeos\MShop::inject( '\\Aimeos\\MShop\\Stock\\Manager\\Standard', $stockStub );

		$body = '{"query":"mutation {\n  saveProduct(input: {\n  code: \"test-graphql\"\n, stock: {\n    type: \"default\"    stocklevel: 100\n}\n  }) {\n  id\n  code\n  stock {\n    id\n    type\n    stocklevel\n  }\n  }\n}\n","variables":{},"operationName":null}';
		$request = new \Nyholm\Psr7\ServerRequest( 'POST', 'localhost', [], $body );

		$response = \Aimeos\Admin\Graphql::execute( $this->context, $request );
		$result = json_decode( (string) $response->getBody(), true );

		$this->assertTrue( isset( $result['data']['saveProduct']['id'] ) );
		\Aimeos\MShop::create( $this->context, 'product' )->delete( $result['data']['saveProduct']['id'] );

		$this->assertStringContainsString( '"code":"test-graphql"', (string) $response->getBody() );
		$this->assertStringContainsString( '"id":"123"', (string) $response->getBody() );
		$this->assertStringContainsString( '"type":"default"', (string) $response->getBody() );
		$this->assertStringContainsString( '"stocklevel":100', (string) $response->getBody() );
	}


	public function testSaveProducts()
	{
		$stub = $this->getMockBuilder( '\\Aimeos\\MShop\\Product\\Manager\\Standard' )
			->setConstructorArgs( array( $this->context ) )
			->onlyMethods( ['save', 'type'] )
			->getMock();

		$stub->expects( $this->once() )->method( 'save' )->willReturnArgument( 0 );
		$stub->method( 'type' )->willReturn( ['product'] );

		\Aimeos\MShop::inject( '\\Aimeos\\MShop\\Product\\Manager\\Standard', $stub );

		$body = '{"query":"mutation {\n  saveProducts(input: [{\n  code: \"test-graphql\"\n  }]) {\n  id\n  code\n  }\n}\n","variables":{},"operationName":null}';
		$request = new \Nyholm\Psr7\ServerRequest( 'POST', 'localhost', [], $body );

		$response = \Aimeos\Admin\Graphql::execute( $this->context, $request );

		$this->assertStringContainsString( '"code":"test-graphql"', (string) $response->getBody() );
	}


	public function testSaveProductLists()
	{
		$stub = $this->getMockBuilder( '\\Aimeos\\MShop\\Product\\Manager\\Standard' )
			->setConstructorArgs( array( $this->context ) )
			->onlyMethods( ['save', 'type'] )
			->getMock();

		$stub->expects( $this->once() )->method( 'save' )->willReturnArgument( 0 );
		$stub->method( 'type' )->willReturn( ['product'] );
		$stub = new \Aimeos\MShop\Common\Manager\Decorator\Lists( $stub, $this->context );

		\Aimeos\MShop::inject( '\\Aimeos\\MShop\\Product\\Manager\\Standard', $stub );

		$body = '{"query":"mutation {\n saveProduct(input: {\n  code: \"test-graphql\"\n  lists: {\n   group: {\n  id: \"123\"\n  item: {\n   id: \"1\"\n   code: \"test-group\"\n  }\n   }\n  }\n }) {\n id\n code\n lists {\n  group {\n   id\n  item {\n   id\n   code\n  }\n   }\n  }\n }\n}\n","variables":{},"operationName":null}';
		$request = new \Nyholm\Psr7\ServerRequest( 'POST', 'localhost', [], $body );

		$body = (string) \Aimeos\Admin\Graphql::execute( $this->context, $request )->getBody();

		$this->assertStringContainsString( '"code":"test-graphql"', $body );
		$this->assertStringContainsString( '"code":"test-group"', $body );
		$this->assertStringContainsString( '"id":"123"', $body );
		$this->assertStringContainsString( '"id":"1"', $body );
	}
}
