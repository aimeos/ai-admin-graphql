<?php

/**
 * @license LGPLv3, http://opensource.org/licenses/LGPL-3.0
 * @copyright Aimeos (aimeos.org), 2022-2025
 */


namespace Aimeos\Admin\Catalog;


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


	public function testFindCatalog()
	{
		$body = '{"query":"query {\n  findCatalog(code: \"cafe\") {\n    id\n    code\n  }\n}\n","variables":{},"operationName":null}';
		$request = new \Nyholm\Psr7\ServerRequest( 'POST', 'localhost', [], $body );

		$response = \Aimeos\Admin\Graphql::execute( $this->context, $request );

		$this->assertStringContainsString( '"code":"cafe"', (string) $response->getBody() );
	}


	public function testGetCatalogPath()
	{
		$id = \Aimeos\MShop::create( $this->context, 'catalog' )->find( 'cafe' );

		$body = '{"query":"query {\n  getCatalogPath(id: \"' . $id . '\") {\n    id\n    code\n  }\n}\n","variables":{},"operationName":null}';
		$request = new \Nyholm\Psr7\ServerRequest( 'POST', 'localhost', [], $body );

		$response = \Aimeos\Admin\Graphql::execute( $this->context, $request );

		$this->assertStringContainsString( '"code":"root"', (string) $response->getBody() );
		$this->assertStringContainsString( '"code":"cafe"', (string) $response->getBody() );
	}


	public function testGetCatalogTree()
	{
		$body = '{"query":"query {\n  getCatalogTree {\n id\n code\n children {\n id\n code\n children {\n id\n code\n}\n}\n}\n}\n","variables":{},"operationName":null}';
		$request = new \Nyholm\Psr7\ServerRequest( 'POST', 'localhost', [], $body );

		$response = \Aimeos\Admin\Graphql::execute( $this->context, $request );

		$this->assertStringContainsString( '"code":"root"', (string) $response->getBody() );
		$this->assertStringContainsString( '"code":"cafe"', (string) $response->getBody() );
	}


	public function testGetCatalogTreeId()
	{
		$id = \Aimeos\MShop::create( $this->context, 'catalog' )->find( 'categories' );

		$body = '{"query":"query {\n  getCatalogTree(id: \"' . $id . '\") {\n id\n code\n children {\n id\n code\n }\n}\n}\n","variables":{},"operationName":null}';
		$request = new \Nyholm\Psr7\ServerRequest( 'POST', 'localhost', [], $body );

		$response = \Aimeos\Admin\Graphql::execute( $this->context, $request );

		$this->assertStringContainsString( '"code":"categories"', (string) $response->getBody() );
		$this->assertStringContainsString( '"code":"cafe"', (string) $response->getBody() );
	}


	public function testGetCatalogTreeLevel()
	{
		$body = '{"query":"query {\n  getCatalogTree(level: 2) {\n id\n code\n children {\n id\n code\n }\n}\n}\n","variables":{},"operationName":null}';
		$request = new \Nyholm\Psr7\ServerRequest( 'POST', 'localhost', [], $body );

		$response = \Aimeos\Admin\Graphql::execute( $this->context, $request );

		$this->assertStringContainsString( '"code":"root"', (string) $response->getBody() );
		$this->assertStringContainsString( '"code":"categories"', (string) $response->getBody() );
	}


	public function testInsertCatalog()
	{
		$stub = $this->getMockBuilder( '\\Aimeos\\MShop\\Catalog\\Manager\\Standard' )
			->setConstructorArgs( array( $this->context ) )
			->onlyMethods( ['insert'] )
			->getMock();

		$item = $stub->create( ['catalog.code' => 'test-graphql'] );
		$stub->expects( $this->once() )->method( 'insert' )->willReturn( $item );

		\Aimeos\MShop::inject( '\\Aimeos\\MShop\\Catalog\\Manager\\Standard', $stub );

		$body = '{"query":"mutation {\n  insertCatalog(input: {\n    code: \"test-graphql\"\n  }) {\n    id\n    code\n  }\n}\n","variables":{},"operationName":null}';
		$request = new \Nyholm\Psr7\ServerRequest( 'POST', 'localhost', [], $body );

		$response = \Aimeos\Admin\Graphql::execute( $this->context, $request );

		$this->assertStringContainsString( '"code":"test-graphql"', (string) $response->getBody() );
	}


	public function testMoveCatalog()
	{
		$stub = $this->getMockBuilder( '\\Aimeos\\MShop\\Catalog\\Manager\\Standard' )
			->setConstructorArgs( array( $this->context ) )
			->onlyMethods( ['move'] )
			->getMock();

		$stub->expects( $this->once() )->method( 'move' );

		\Aimeos\MShop::inject( '\\Aimeos\\MShop\\Catalog\\Manager\\Standard', $stub );

		$body = '{"query":"mutation {\n  moveCatalog(id: \"1\", parentid: null)\n}\n","variables":{},"operationName":null}';
		$request = new \Nyholm\Psr7\ServerRequest( 'POST', 'localhost', [], $body );

		$response = \Aimeos\Admin\Graphql::execute( $this->context, $request );

		$this->assertStringContainsString( '"moveCatalog":"1"', (string) $response->getBody() );
	}

}
