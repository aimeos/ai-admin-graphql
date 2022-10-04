<?php

/**
 * @license LGPLv3, http://opensource.org/licenses/LGPL-3.0
 * @copyright Aimeos (aimeos.org), 2022
 */


namespace Aimeos\Admin;


class GraphqlTest extends \PHPUnit\Framework\TestCase
{
	private $context;


	protected function setUp() : void
	{
		\Aimeos\MShop::cache( true );
		$this->context = \TestHelper::context();
		$this->context->config()->set( 'admin/graphql/debug', true );
	}


	public function testGetProduct()
	{
		$id = \Aimeos\MShop::create( $this->context, 'product' )->find( 'CNC' );

		$body = '{"query":"query {\n  getProduct(id: \"' . $id . '\") {\n    id\n    code\n  }\n}\n","variables":{},"operationName":null}';
		$request = new \Nyholm\Psr7\ServerRequest( 'POST', 'localhost', [], $body );

		$response = \Aimeos\Admin\Graphql::execute( $this->context, $request );

		$this->assertStringContainsString( '"code":"CNC"', (string) $response->getBody() );
	}


	public function testFindProduct()
	{
		$body = '{"query":"query {\n  findProduct(code: \"CNC\") {\n    id\n    code\n  }\n}\n","variables":{},"operationName":null}';
		$request = new \Nyholm\Psr7\ServerRequest( 'POST', 'localhost', [], $body );

		$response = \Aimeos\Admin\Graphql::execute( $this->context, $request );

		$this->assertStringContainsString( '"code":"CNC"', (string) $response->getBody() );
	}


	public function testSearchProducts()
	{
		$manager = \Aimeos\MShop::create( $this->context, 'product' );
		$filter = $manager->filter()->add( 'product.code', '==', ['CNC', 'CNE'] );
		$ids = $manager->search( $filter )->keys()->all();

		$search = addslashes( addslashes( json_encode( ['==' => ['product.id' => $ids]] ) ) );
		$body = '{"query":"query {\n  searchProducts(filter: \"' . $search . '\") {\n    id\n    code\n  }\n}\n","variables":{},"operationName":null}';
		$request = new \Nyholm\Psr7\ServerRequest( 'POST', 'localhost', [], $body );

		$response = \Aimeos\Admin\Graphql::execute( $this->context, $request );

		$this->assertStringContainsString( '"code":"CNC"', (string) $response->getBody() );
		$this->assertStringContainsString( '"code":"CNE"', (string) $response->getBody() );
	}


	public function testSaveProduct()
	{
		$stub = $this->getMockBuilder( '\\Aimeos\\MShop\\Product\\Manager\\Standard' )
			->setConstructorArgs( array( $this->context ) )
			->setMethods( ['save'] )
			->getMock();

		$item = $stub->create( ['product.id' => 123, 'product.code' => 'test-graphql'] );
		$stub->expects( $this->once() )->method( 'save' )->will( $this->returnValue( $item ) );

		\Aimeos\MShop::inject( '\\Aimeos\\MShop\\Product\\Manager\\Standard', $stub );

		$body = '{"query":"mutation {\n  saveProduct(input: {\n    code: \"test-graphql\"\n  }) {\n    id\n    code\n  }\n}\n","variables":{},"operationName":null}';
		$request = new \Nyholm\Psr7\ServerRequest( 'POST', 'localhost', [], $body );

		$response = \Aimeos\Admin\Graphql::execute( $this->context, $request );

		$this->assertStringContainsString( '"code":"test-graphql"', (string) $response->getBody() );
	}


	public function testSaveProducts()
	{
		$stub = $this->getMockBuilder( '\\Aimeos\\MShop\\Product\\Manager\\Standard' )
			->setConstructorArgs( array( $this->context ) )
			->setMethods( ['save'] )
			->getMock();

		$item = $stub->create( ['product.id' => 123, 'product.code' => 'test-graphql'] );
		$stub->expects( $this->once() )->method( 'save' )->will( $this->returnValue( [$item] ) );

		\Aimeos\MShop::inject( '\\Aimeos\\MShop\\Product\\Manager\\Standard', $stub );

		$body = '{"query":"mutation {\n  saveProducts(input: [{\n    code: \"test-graphql\"\n  }]) {\n    id\n    code\n  }\n}\n","variables":{},"operationName":null}';
		$request = new \Nyholm\Psr7\ServerRequest( 'POST', 'localhost', [], $body );

		$response = \Aimeos\Admin\Graphql::execute( $this->context, $request );

		$this->assertStringContainsString( '"code":"test-graphql"', (string) $response->getBody() );
	}
}
