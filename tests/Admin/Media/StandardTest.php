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


	public function testSaveMedia()
	{
		$content = file_get_contents( __DIR__ . '/upload.gif' );
		$stream = \Nyholm\Psr7\Stream::create( $content );
		$file = new \Nyholm\Psr7\UploadedFile( $stream, strlen( $content ), \UPLOAD_ERR_OK, 'upload.gif' );

		$stub = $this->getMockBuilder( '\\Aimeos\\MShop\\Media\\Manager\\Standard' )
			->setConstructorArgs( [$this->context] )
			->onlyMethods( ['save', 'type'] )
			->getMock();

		$stub->method( 'type' )->willReturn( ['media'] );
		$stub->expects( $this->once() )->method( 'save' )->willReturnArgument( 0 );

		\Aimeos\MShop::inject( '\\Aimeos\\MShop\\Media\\Manager\\Standard', $stub );

		$body = '{"query":"mutation($file: Upload, $preview: Upload) {\n  saveMedia(input: {\n    domain: \"product\"\n    file: $file\n    filepreview: $preview\n  }) {\n    id\n    label\n    url\n    preview\n  }\n}\n","variables":{ "file": null, "preview": null },"operationName":null}';
		$request = new \Nyholm\Psr7\ServerRequest( 'POST', 'localhost' );
		$request = $request->withParsedBody( ['operations' => $body, 'map' => json_encode( [1 => ['variables.file']] )] );
		$request = $request->withUploadedFiles( [1 => $file] );

		$response = \Aimeos\Admin\Graphql::execute( $this->context, $request );

		$this->assertStringContainsString( '"label":"upload.gif"', (string) $response->getBody() );
	}
}
