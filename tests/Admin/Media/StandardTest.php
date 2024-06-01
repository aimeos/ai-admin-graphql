<?php

/**
 * @license LGPLv3, http://opensource.org/licenses/LGPL-3.0
 * @copyright Aimeos (aimeos.org), 2024
 */


namespace Aimeos\Admin\Media;


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


	public function testInsertMedia()
	{
		$stub = $this->getMockBuilder( '\\Aimeos\\MShop\\Media\\Manager\\Standard' )
			->setConstructorArgs( array( $this->context ) )
			->onlyMethods( ['save'] )
			->getMock();

		$item = $stub->create();
		// $stub->expects( $this->once() )->method( 'save' )->willReturn( $item );

		// \Aimeos\MShop::inject( '\\Aimeos\\MShop\\Media\\Manager\\Standard', $stub );

		$body = '{"query":"mutation($file: Upload, $preview: Upload) {\n  saveMedia(input: {\n    label: \"test-graphql\"\n    file: $file\n    filepreview: $preview\n  }) {\n    id\n    label\n    url\n    preview\n  }\n}\n","variables":{ "file": null, "preview": null },"operationName":null}';
		$request = new \Nyholm\Psr7\ServerRequest( 'POST', 'localhost', [], $body );

		$response = \Aimeos\Admin\Graphql::execute( $this->context, $request );

		$this->assertStringContainsString( '"label":"test-graphql"', (string) $response->getBody() );
	}
}
