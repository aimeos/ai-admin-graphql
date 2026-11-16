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


	public function testFindLocaleSiteOtherSite()
	{
		$this->admin( \Aimeos\MShop::create( $this->context, 'locale/site' )->find( 'unittest' )->getSiteId() );

		$body = '{"query":"query {\n  findLocaleSite(code: \"default\") {\n    id\n    code\n  }\n}\n","variables":{},"operationName":null}';
		$request = new \Nyholm\Psr7\ServerRequest( 'POST', 'localhost', [], $body );

		$result = json_decode( (string) \Aimeos\Admin\Graphql::execute( $this->context, $request )->getBody(), true );

		$this->assertNull( $result['data']['findLocaleSite'] ?? null );
		$this->assertArrayHasKey( 'errors', $result );
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
			->onlyMethods( ['insert', 'type'] )
			->getMock();

		$stub->method( 'type' )->willReturn( ['locale', 'site'] );
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
		$body = '{"query":"mutation {\n  moveLocaleSite(id: \\"1\\", parentid: null)\n}\n","variables":{},"operationName":null}';
		$request = new \Nyholm\Psr7\ServerRequest( 'POST', 'localhost', [], $body );

		$response = \Aimeos\Admin\Graphql::execute( $this->context, $request );

		// moving sites isn't supported
		$this->assertStringContainsString( 'Cannot query field \\"moveLocaleSite\\"', (string) $response->getBody() );
	}


	public function testSearchLocaleSite()
	{
		$search = addslashes( addslashes( json_encode( ['==' => ['locale.site.code' => 'unittest']] ) ) );
		$body = '{"query":"query {\n  searchLocaleSites(filter: \"' . $search . '\") {\n    items {\n      id\n      code\n    }\n    total\n  }\n}\n","variables":{},"operationName":null}';
		$request = new \Nyholm\Psr7\ServerRequest( 'POST', 'localhost', [], $body );

		$response = \Aimeos\Admin\Graphql::execute( $this->context, $request );

		$this->assertStringContainsString( '"code":"unittest"', (string) $response->getBody() );
	}


	public function testSearchLocaleSiteAdmin()
	{
		$this->admin( \Aimeos\MShop::create( $this->context, 'locale/site' )->find( 'unittest' )->getSiteId() );

		// the "default" site is outside the site tree of the user
		$search = addslashes( addslashes( json_encode( ['==' => ['locale.site.code' => ['default', 'unittest']]] ) ) );
		$body = '{"query":"query {\n  searchLocaleSites(filter: \"' . $search . '\") {\n    items {\n      id\n      code\n    }\n    total\n  }\n}\n","variables":{},"operationName":null}';
		$request = new \Nyholm\Psr7\ServerRequest( 'POST', 'localhost', [], $body );

		$result = json_decode( (string) \Aimeos\Admin\Graphql::execute( $this->context, $request )->getBody(), true );
		$codes = array_column( $result['data']['searchLocaleSites']['items'], 'code' );

		$this->assertEquals( ['unittest'], $codes );
	}


	public function testSearchLocaleSiteNoUserSite()
	{
		$this->admin( '' );

		$body = '{"query":"query {\n  searchLocaleSites {\n    items {\n      id\n      code\n    }\n    total\n  }\n}\n","variables":{},"operationName":null}';
		$request = new \Nyholm\Psr7\ServerRequest( 'POST', 'localhost', [], $body );

		$result = json_decode( (string) \Aimeos\Admin\Graphql::execute( $this->context, $request )->getBody(), true );

		$this->assertNull( $result['data']['searchLocaleSites'] ?? null );
		$this->assertArrayHasKey( 'errors', $result );
	}


	public function testSearchLocaleSiteTree()
	{
		$search = addslashes( addslashes( json_encode( ['==' => ['locale.site.code' => 'unittest']] ) ) );
		$body = '{"query":"query {\n  searchLocaleSiteTree(filter: \"' . $search . '\") {\n    id\n    code\n  }\n}\n","variables":{},"operationName":null}';
		$request = new \Nyholm\Psr7\ServerRequest( 'POST', 'localhost', [], $body );

		$response = \Aimeos\Admin\Graphql::execute( $this->context, $request );
		$result = json_decode( (string) $response->getBody(), true );

		$this->assertEquals( 1, count( $result['data']['searchLocaleSiteTree'] ) );
		$this->assertEquals( 'unittest', $result['data']['searchLocaleSiteTree'][0]['code'] );
	}


	public function testSearchLocaleSiteTreeAdmin()
	{
		$this->admin( \Aimeos\MShop::create( $this->context, 'locale/site' )->find( 'unittest' )->getSiteId() );

		// the "default" site is outside the site tree of the user
		$search = addslashes( addslashes( json_encode( ['==' => ['locale.site.code' => ['default', 'unittest']]] ) ) );
		$body = '{"query":"query {\n  searchLocaleSiteTree(filter: \"' . $search . '\") {\n    id\n    code\n  }\n}\n","variables":{},"operationName":null}';
		$request = new \Nyholm\Psr7\ServerRequest( 'POST', 'localhost', [], $body );

		$result = json_decode( (string) \Aimeos\Admin\Graphql::execute( $this->context, $request )->getBody(), true );

		$this->assertEquals( ['unittest'], array_column( $result['data']['searchLocaleSiteTree'], 'code' ) );
	}


	protected function admin( string $siteid ) : void
	{
		$view = $this->context->view();
		$view->addHelper( 'access', new \Aimeos\Base\View\Helper\Access\Standard( $view, ['admin'] ) );

		$user = \Aimeos\MShop::create( $this->context, 'customer' )->create( ['customer.siteid' => $siteid] );
		$this->context->setView( $view )->setUser( $user );
	}
}
