<?php

/**
 * @license LGPLv3, http://opensource.org/licenses/LGPL-3.0
 * @copyright Aimeos (aimeos.org), 2024
 */


namespace Aimeos\Admin\Locale\Site;


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


	public function testFindLocaleSite()
	{
		$body = '{"query":"query {\n  findLocaleSite(code: \"unittest\") {\n    id\n    code\n    hasChildren\n  }\n}\n","variables":{},"operationName":null}';
		$request = new \Nyholm\Psr7\ServerRequest( 'POST', 'localhost', [], $body );

		$response = \Aimeos\Admin\Graphql::execute( $this->context, $request );

		$this->assertStringContainsString( '"code":"unittest"', (string) $response->getBody() );
	}


	public function testGetLocaleSitePath()
	{
		$id = \Aimeos\MShop::create( $this->context, 'locale/site' )->find( 'unittest' );

		$body = '{"query":"query {\n  getLocaleSitePath(id: \"' . $id . '\") {\n    id\n    code\n  }\n}\n","variables":{},"operationName":null}';
		$request = new \Nyholm\Psr7\ServerRequest( 'POST', 'localhost', [], $body );

		$response = \Aimeos\Admin\Graphql::execute( $this->context, $request );

		$this->assertStringContainsString( '"code":"unittest"', (string) $response->getBody() );
	}


	public function testGetLocaleSiteTree()
	{
		$id = \Aimeos\MShop::create( $this->context, 'locale/site' )->find( 'unittest' );

		$body = '{"query":"query {\n  getLocaleSiteTree(id: \"' . $id . '\") {\n id\n code\n children {\n id\n code\n }\n}\n}\n","variables":{},"operationName":null}';
		$request = new \Nyholm\Psr7\ServerRequest( 'POST', 'localhost', [], $body );

		$response = \Aimeos\Admin\Graphql::execute( $this->context, $request );

		$this->assertStringContainsString( '"code":"unittest"', (string) $response->getBody() );
	}


	public function testGetLocaleSiteTreeLevel()
	{
		$id = \Aimeos\MShop::create( $this->context, 'locale/site' )->find( 'unittest' );

		$body = '{"query":"query {\n  getLocaleSiteTree(id: \"' . $id . '\", level: 2) {\n id\n code\n children {\n id\n code\n }\n}\n}\n","variables":{},"operationName":null}';
		$request = new \Nyholm\Psr7\ServerRequest( 'POST', 'localhost', [], $body );

		$response = \Aimeos\Admin\Graphql::execute( $this->context, $request );

		$this->assertStringContainsString( '"code":"unittest"', (string) $response->getBody() );
	}


	public function testInsertLocaleSite()
	{
		$stub = $this->getMockBuilder( '\\Aimeos\\MShop\\Locale\\Manager\\Site\\Standard' )
			->setConstructorArgs( array( $this->context ) )
			->onlyMethods( ['insert'] )
			->getMock();

		$item = $stub->create( ['locale.site.code' => 'test-graphql'] );
		$stub->expects( $this->once() )->method( 'insert' )->willReturn( $item );

		\Aimeos\MShop::inject( '\\Aimeos\\MShop\\Locale\\Manager\\Site\\Standard', $stub );
		\Aimeos\MShop::inject( '\\Aimeos\\MShop\\Locale\\Manager\\Site\\Sites', $stub );

		$body = '{"query":"mutation {\n  insertLocaleSite(input: {\n    code: \"test-graphql\"\n  }) {\n    id\n    code\n  }\n}\n","variables":{},"operationName":null}';
		$request = new \Nyholm\Psr7\ServerRequest( 'POST', 'localhost', [], $body );

		$response = \Aimeos\Admin\Graphql::execute( $this->context, $request );

		$this->assertStringContainsString( '"code":"test-graphql"', (string) $response->getBody() );
	}


	public function testMoveLocaleSite()
	{
		$stub = $this->getMockBuilder( '\\Aimeos\\MShop\\Locale\\Manager\\Site\\Standard' )
			->setConstructorArgs( array( $this->context ) )
			->onlyMethods( ['move'] )
			->getMock();

		$stub->expects( $this->once() )->method( 'move' );

		\Aimeos\MShop::inject( '\\Aimeos\\MShop\\Locale\\Manager\\Site\\Standard', $stub );
		\Aimeos\MShop::inject( '\\Aimeos\\MShop\\Locale\\Manager\\Site\\Sites', $stub );

		$body = '{"query":"mutation {\n  moveLocaleSite(id: \"1\", parentid: null)\n}\n","variables":{},"operationName":null}';
		$request = new \Nyholm\Psr7\ServerRequest( 'POST', 'localhost', [], $body );

		$response = \Aimeos\Admin\Graphql::execute( $this->context, $request );

		$this->assertStringContainsString( '"moveLocaleSite":"1"', (string) $response->getBody() );
	}


	public function testSearchLocaleSite()
	{
		$search = addslashes( addslashes( json_encode( ['==' => ['locale.site.code' => 'unittest']] ) ) );
		$body = '{"query":"query {\n  searchLocaleSites(filter: \"' . $search . '\") {\n    items {\n      id\n      code\n    }\n    total\n  }\n}\n","variables":{},"operationName":null}';
		$request = new \Nyholm\Psr7\ServerRequest( 'POST', 'localhost', [], $body );

		$response = \Aimeos\Admin\Graphql::execute( $this->context, $request );

		$this->assertStringContainsString( '"code":"unittest"', (string) $response->getBody() );
	}

}
