<?php

/**
 * @license LGPLv3, http://opensource.org/licenses/LGPL-3.0
 * @copyright Aimeos (aimeos.org), 2024
 */


namespace Aimeos\Admin\Index;


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


	public function testAggregateIndex()
	{
		$body = '{"query":"query {\n  aggregateIndex(key: [\"index.catalog.id\"]) {\n    aggregates\n  }\n}\n","variables":{},"operationName":null}';
		$request = new \Nyholm\Psr7\ServerRequest( 'POST', 'localhost', [], $body );

		$response = \Aimeos\Admin\Graphql::execute( $this->context, $request );

		$this->assertStringContainsString( '"aggregates":', (string) $response->getBody() );
		$this->assertStringContainsString( ':4,', (string) $response->getBody() );
		$this->assertStringContainsString( ':3,', (string) $response->getBody() );
		$this->assertStringContainsString( ':2,', (string) $response->getBody() );
	}


	public function testSearchIndex()
	{
		$id = \Aimeos\MShop::create( $this->context, 'catalog' )->find( 'internet' )->getId;

		$search = addslashes( addslashes( json_encode( ['==' => ['index.catalog.id' => $id]] ) ) );
		$body = '{"query":"query {\n  searchIndex(filter: \"' . $search . '\", sort: [\"sort:index.catalog:position()\"]) {\n    items {\n      id\n      code\n    }\n    total\n  }\n}\n","variables":{},"operationName":null}';
		$request = new \Nyholm\Psr7\ServerRequest( 'POST', 'localhost', [], $body );

		$response = \Aimeos\Admin\Graphql::execute( $this->context, $request );

		$this->assertStringContainsString( '"code":"EFGH"', (string) $response->getBody() );
		$this->assertStringContainsString( '"total":20', (string) $response->getBody() );
	}
}
