<?php

/**
 * @license LGPLv3, http://opensource.org/licenses/LGPL-3.0
 * @copyright Aimeos (aimeos.org)2023-2025
 */


namespace Aimeos\Admin\Rule;


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


	public function testGetRuleConfig()
	{
		$body = '{"query":"query {\n  getRuleConfig(provider: \"Percent,Category\", type: \"catalog\") {\n    code\n    type\n    label\n  }\n}\n","variables":{},"operationName":null}';
		$request = new \Nyholm\Psr7\ServerRequest( 'POST', 'localhost', [], $body );

		$response = \Aimeos\Admin\Graphql::execute( $this->context, $request );

		$this->assertStringContainsString( '"code":"percent"', (string) $response->getBody() );
		$this->assertStringContainsString( '"code":"category.code"', (string) $response->getBody() );
	}
}
