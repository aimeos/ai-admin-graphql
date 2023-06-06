<?php

/**
 * @license LGPLv3, http://opensource.org/licenses/LGPL-3.0
 * @copyright Aimeos (aimeos.org), 2023
 */


namespace Aimeos\Admin\Service;


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


	public function testFindService()
	{
		$body = '{"query":"query {\n  findService(code: \"unitpaymentcode\") {\n    id\n    code\n  }\n}\n","variables":{},"operationName":null}';
		$request = new \Nyholm\Psr7\ServerRequest( 'POST', 'localhost', [], $body );

		$response = \Aimeos\Admin\Graphql::execute( $this->context, $request );

		$this->assertStringContainsString( '"code":"unitpaymentcode"', (string) $response->getBody() );
	}


	public function testGetServiceConfig()
	{
		$body = '{"query":"query {\n  getServiceConfig(provider: \"Xml,BasketValues\", type: \"delivery\") {\n    code\n    type\n    label\n  }\n}\n","variables":{},"operationName":null}';
		$request = new \Nyholm\Psr7\ServerRequest( 'POST', 'localhost', [], $body );

		$response = \Aimeos\Admin\Graphql::execute( $this->context, $request );

		$this->assertStringContainsString( '"code":"xml.exportpath"', (string) $response->getBody() );
		$this->assertStringContainsString( '"code":"basketvalues.total-value-min"', (string) $response->getBody() );
	}
}
