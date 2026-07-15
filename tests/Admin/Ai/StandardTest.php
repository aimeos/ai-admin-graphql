<?php

/**
 * @license LGPLv3, http://opensource.org/licenses/LGPL-3.0
 * @copyright Aimeos (aimeos.org), 2026
 */


namespace Aimeos\Admin\Ai;

use Aimeos\Prisma\Prisma;
use Aimeos\Prisma\Responses\FileResponse;
use Aimeos\Prisma\Responses\TextResponse;


class StandardTest extends \PHPUnit\Framework\TestCase
{
	private \Aimeos\MShop\ContextIface $context;


	protected function setUp() : void
	{
		\Aimeos\MShop::cache( true );
		$this->context = new \Aimeos\MShop\Context();
		$this->context->setConfig( new \Aimeos\Base\Config\PHPArray( [
			'admin' => ['graphql' => ['debug' => true, 'domains' => ['ai']]],
		] ) );

		$site = new \Aimeos\MShop\Locale\Item\Site\Standard( 'locale.site.', ['locale.site.config' => []] );
		$this->context->setLocale( new \Aimeos\MShop\Locale\Item\Standard( [], $site ) );

		$view = new \Aimeos\Base\View\Standard();
		$view->addHelper( 'access', new \Aimeos\Base\View\Helper\Access\All( $view ) );
		$this->context->setView( $view );
	}


	protected function tearDown() : void
	{
		Prisma::reset();
	}


	public function testWrite() : void
	{
		$this->context->config()->set( 'admin/ai/write', ['provider' => 'missing'] );
		$site = $this->context->locale()->getSiteItem()->setConfigValue( 'admin/ai/write', [
			'provider' => 'openai',
			'model' => 'gpt-4o-mini',
			'api_key' => 'test',
		] );
		$this->context->config()->apply( $site->getConfig() );
		$fake = Prisma::fake( [TextResponse::fake( 'Generated text' )] );

		$result = $this->execute( 'mutation($prompt: String!) { write(prompt: $prompt) }', ['prompt' => 'Input'] );

		$this->assertSame( 'Generated text', $result['data']['write'] );
		$this->assertSame(
			'You are a professional writer for product texts and blog articles. Create descriptions and articles in the language of the input without markup.',
			$this->systemPrompt( $fake )
		);
		$fake->assertCalled( 'write', fn( $args ) => $args[0] === 'Input' );
	}


	public function testWriteUnsupported() : void
	{
		$site = $this->context->locale()->getSiteItem()->setConfigValue( 'admin/ai/write', [
			'provider' => 'deepl',
			'api_key' => 'test',
		] );
		$this->context->config()->apply( $site->getConfig() );

		$result = $this->execute( 'mutation($prompt: String!) { write(prompt: $prompt) }', ['prompt' => 'Input'] );
		$message = $result['errors'][0]['message'] ?? null;

		$this->assertIsString( $message );
		$this->assertStringContainsString( 'does not implement "write"', $message );
	}


	public function testTranslate() : void
	{
		$this->context->config()->set( 'admin/ai/translate', [
			'provider' => 'deepl',
			'api_key' => 'test',
		] );
		$fake = Prisma::fake( [TextResponse::fake( ['Hallo', 'Welt'] )] );

		$result = $this->execute(
			'mutation($texts: [String!]!, $to: String!, $from: String) { translate(texts: $texts, to: $to, from: $from) }',
			['texts' => ['Hello', 'World'], 'to' => 'DE', 'from' => 'EN']
		);

		$this->assertSame( ['Hallo', 'Welt'], $result['data']['translate'] );
		$fake->assertCalled( 'translate', fn( $args ) => $args[0] === ['Hello', 'World'] && $args[1] === 'DE' && $args[2] === 'EN' );
	}


	public function testImagine() : void
	{
		$this->context->config()->set( 'admin/ai/imagine', [
			'provider' => 'openai',
			'model' => 'dall-e-3',
			'api_key' => 'test',
		] );
		$fake = Prisma::fake( [FileResponse::fromBinary( 'image', 'image/png' )] );

		$result = $this->execute(
			'mutation($prompt: String!, $size: String, $style: String) { imagine(prompt: $prompt, size: $size, style: $style) }',
			['prompt' => 'A shoe', 'size' => '1024x1024', 'style' => 'natural']
		);
		$data = json_decode( (string) $result['data']['imagine'], true, 512, JSON_THROW_ON_ERROR );

		$this->assertSame( base64_encode( 'image' ), $data['base64'] );
		$this->assertSame( 'image/png', $data['mimeType'] );
		$fake->assertCalled( 'imagine', fn( $args ) => is_string( $args[0] ) && str_contains( $args[0], 'A shoe' )
			&& $args[2] === ['size' => '1024x1024', 'style' => 'natural'] );
	}


	public function testIsolate() : void
	{
		$this->context->config()->set( 'admin/ai/isolate', [
			'provider' => 'removebg',
			'api_key' => 'test',
		] );
		$fake = Prisma::fake( [FileResponse::fromBinary( 'isolated', 'image/png' )] );
		$content = base64_decode( 'iVBORw0KGgoAAAANSUhEUgAAAAEAAAABCAQAAAC1HAwCAAAAC0lEQVR42mNk+A8AAQUBAScY42YAAAAASUVORK5CYII=', true );

		if( $content === false ) {
			$this->fail( 'Unable to decode the test image' );
		}

		$file = new \Nyholm\Psr7\UploadedFile( \Nyholm\Psr7\Stream::create( $content ), strlen( $content ), UPLOAD_ERR_OK, 'source.png', 'image/png' );
		$result = $this->executeUpload( $file );
		$data = json_decode( (string) $result['data']['isolate'], true, 512, JSON_THROW_ON_ERROR );

		$this->assertSame( base64_encode( 'isolated' ), $data['base64'] );
		$fake->assertCalled( 'isolate', fn( $args ) => $args[0]->binary() === $content
			&& $args[1] === ['crop' => true, 'size' => 'auto', 'format' => 'png'] );
	}


	public function testIsolateTooLarge() : void
	{
		$this->context->config()->set( 'admin/graphql/ai/isolate/max-size', 5 );
		$file = new \Nyholm\Psr7\UploadedFile( \Nyholm\Psr7\Stream::create( 'source' ), 6, UPLOAD_ERR_OK, 'source.png', 'image/png' );
		$result = $this->executeUpload( $file );

		$this->assertSame( 'Image upload exceeds the maximum allowed size', $result['errors'][0]['message'] ?? null );
	}


	private function execute( string $query, array $variables ) : array
	{
		$request = new \Nyholm\Psr7\ServerRequest( 'POST', 'localhost' );
		$request = $request->withParsedBody( ['query' => $query, 'variables' => $variables] );
		$response = \Aimeos\Admin\Graphql::execute( $this->context, $request );

		$result = json_decode( (string) $response->getBody(), true, 512, JSON_THROW_ON_ERROR );

		return is_array( $result ) ? $result : [];
	}


	private function executeUpload( \Psr\Http\Message\UploadedFileInterface $file ) : array
	{
		$body = json_encode( [
			'query' => 'mutation($image: Upload!) { isolate(image: $image) }',
			'variables' => ['image' => null],
		], JSON_THROW_ON_ERROR );
		$request = ( new \Nyholm\Psr7\ServerRequest( 'POST', 'localhost' ) )
			->withParsedBody( ['operations' => $body, 'map' => json_encode( [0 => ['variables.image']] )] )
			->withUploadedFiles( [0 => $file] );
		$response = \Aimeos\Admin\Graphql::execute( $this->context, $request );
		$result = json_decode( (string) $response->getBody(), true, 512, JSON_THROW_ON_ERROR );

		return is_array( $result ) ? $result : [];
	}


	private function systemPrompt( \Aimeos\Prisma\Providers\Fake $fake ) : string
	{
		$method = new \ReflectionMethod( $fake, 'systemPrompt' );

		return (string) $method->invoke( $fake );
	}
}
