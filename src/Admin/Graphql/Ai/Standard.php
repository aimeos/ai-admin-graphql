<?php

/**
 * @license LGPLv3, http://opensource.org/licenses/LGPL-3.0
 * @copyright Aimeos (aimeos.org), 2026
 * @package Admin
 * @subpackage GraphQL
 */


namespace Aimeos\Admin\Graphql\Ai;

use Aimeos\GraphQL\Type\Definition\Json;
use Aimeos\GraphQL\Type\Definition\Upload;
use Aimeos\Prisma\Files\Image;
use Aimeos\Prisma\Prisma;
use Aimeos\Prisma\Responses\FileResponse;
use Aimeos\Prisma\Responses\TextResponse;
use GraphQL\Type\Definition\Type;
use Psr\Http\Message\UploadedFileInterface;


/**
 * GraphQL mutations for AI operations.
 *
 * @package Admin
 * @subpackage GraphQL
 */
class Standard extends \Aimeos\Admin\Graphql\Base
{
	/**
	 * Returns the available AI mutations.
	 *
	 * @param string $domain Domain name
	 * @return array GraphQL mutation schema definition
	 */
	public function mutation( string $domain ) : array
	{
		return [
			'write' => [
				'type' => Type::nonNull( Type::string() ),
				'args' => [
					['name' => 'prompt', 'type' => Type::nonNull( Type::string() ), 'description' => 'Text prompt'],
				],
				'resolve' => $this->write(),
			],
			'translate' => [
				'type' => Type::nonNull( Type::listOf( Type::string() ) ),
				'args' => [
					['name' => 'texts', 'type' => Type::nonNull( Type::listOf( Type::nonNull( Type::string() ) ) ), 'description' => 'Texts to translate'],
					['name' => 'to', 'type' => Type::nonNull( Type::string() ), 'description' => 'Target language'],
					['name' => 'from', 'type' => Type::string(), 'description' => 'Source language'],
				],
				'resolve' => $this->translate(),
			],
			'imagine' => [
				'type' => Type::nonNull( Json::type() ),
				'args' => [
					['name' => 'prompt', 'type' => Type::nonNull( Type::string() ), 'description' => 'Image prompt'],
					['name' => 'size', 'type' => Type::string(), 'description' => 'Image dimensions'],
					['name' => 'style', 'type' => Type::string(), 'description' => 'Image style'],
				],
				'resolve' => $this->imagine(),
			],
			'isolate' => [
				'type' => Type::nonNull( Json::type() ),
				'args' => [
					['name' => 'image', 'type' => Type::nonNull( Upload::type() ), 'description' => 'Image upload'],
				],
				'resolve' => $this->isolate(),
			],
		];
	}


	/**
	 * AI doesn't expose queries.
	 *
	 * @param string $domain Domain name
	 * @return array Empty query schema definition
	 */
	public function query( string $domain ) : array
	{
		return [];
	}


	/**
	 * Returns the configured Prisma provider.
	 *
	 * @param string $type Prisma media type
	 * @param string $action AI operation
	 * @return \Aimeos\Prisma\Contracts\Provider Prisma provider
	 */
	protected function provider( string $type, string $action ) : \Aimeos\Prisma\Contracts\Provider
	{
		/** @var array<string, mixed> $settings */
		$settings = (array) $this->context()->config()->get( 'admin/ai/' . $action, [] );
		$name = (string) ( $settings['provider'] ?? '' );
		$model = isset( $settings['model'] ) ? (string) $settings['model'] : null;

		unset( $settings['provider'], $settings['model'] );
		$settings = array_filter( $settings, fn( $value ) => $value !== '' && $value !== null );

		return Prisma::type( $type )->using( $name, $settings )->model( $model );
	}


	/**
	 * Renders an extension-overridable AI prompt template.
	 *
	 * @param string $name Template name
	 * @return string Rendered prompt
	 */
	protected function prompt( string $name ) : string
	{
		$theme = $this->context()->locale()->getSiteItem()->getTheme();
		$paths = ( new \Aimeos\Bootstrap() )->getTemplatePaths( 'admin/graphql/templates', $theme );
		$view = new \Aimeos\Base\View\Standard( $paths );

		return trim( $view->render( 'ai/' . $name ) );
	}


	/**
	 * Converts an image response into a GraphQL-safe value.
	 *
	 * @param FileResponse $response Prisma file response
	 * @return array Image data
	 */
	protected function response( FileResponse $response ) : array
	{
		return [
			'base64' => $response->base64(),
			'mimeType' => $response->mimeType(),
			'description' => $response->description(),
		];
	}


	/**
	 * Returns the write resolver.
	 */
	protected function write() : \Closure
	{
		return function( $root, $args ) {
			$this->access( 'ai', 'write' );

			try
			{
				$provider = $this->provider( 'text', 'write' );
				$provider->withSystemPrompt( $this->prompt( 'write' ) );
				/** @var TextResponse $response */
				$response = $provider->ensure( 'write' )->write( (string) $args['prompt'] ); // @phpstan-ignore method.notFound

				return trim( (string) $response->text() );
			}
			catch( \Aimeos\Prisma\Exceptions\PrismaException $e )
			{
				throw new \Aimeos\Admin\Graphql\Exception( $e->getMessage(), (int) $e->getCode(), $e );
			}
		};
	}


	/**
	 * Returns the translate resolver.
	 */
	protected function translate() : \Closure
	{
		return function( $root, $args ) {
			$this->access( 'ai', 'translate' );

			try
			{
				$provider = $this->provider( 'text', 'translate' );
				$texts = array_map( fn( $text ) => (string) $text, (array) $args['texts'] );
				$from = isset( $args['from'] ) ? (string) $args['from'] : null;
				$params = [$texts, (string) $args['to'], $from];
				/** @var TextResponse $response */
				$response = $provider->ensure( 'translate' )->translate( ...$params ); // @phpstan-ignore method.notFound

				return array_map( fn( $text ) => (string) $text, $response->texts() );
			}
			catch( \Aimeos\Prisma\Exceptions\PrismaException $e )
			{
				throw new \Aimeos\Admin\Graphql\Exception( $e->getMessage(), (int) $e->getCode(), $e );
			}
		};
	}


	/**
	 * Returns the imagine resolver.
	 */
	protected function imagine() : \Closure
	{
		return function( $root, $args ) {
			$this->access( 'ai', 'imagine' );

			try
			{
				$options = array_filter( [
					'size' => $args['size'] ?? null,
					'style' => $args['style'] ?? null,
				], fn( $value ) => $value !== '' && $value !== null );
				$prompt = $this->prompt( 'imagine' ) . "\n\n" . (string) $args['prompt'];
				$provider = $this->provider( 'image', 'imagine' );
				/** @var FileResponse $response */
				$response = $provider->ensure( 'imagine' )->imagine( $prompt, [], $options ); // @phpstan-ignore method.notFound

				return $this->response( $response );
			}
			catch( \Aimeos\Prisma\Exceptions\PrismaException $e )
			{
				throw new \Aimeos\Admin\Graphql\Exception( $e->getMessage(), (int) $e->getCode(), $e );
			}
		};
	}


	/**
	 * Returns the isolate resolver.
	 */
	protected function isolate() : \Closure
	{
		return function( $root, $args ) {
			$this->access( 'ai', 'isolate' );

			try
			{
				/** @var UploadedFileInterface $upload */
				$upload = $args['image'];

				if( $upload->getError() !== UPLOAD_ERR_OK ) {
					throw new \Aimeos\Admin\Graphql\Exception( 'Invalid image upload', 400 );
				}

				/** admin/graphql/ai/isolate/max-size
				 * Maximum size of images uploaded for background removal
				 *
				 * @type int Maximum image size in bytes
				 * @since 2026.10
				 */
				$maxSize = max( 1, (int) $this->context()->config()->get( 'admin/graphql/ai/isolate/max-size', 10485760 ) );

				if( $upload->getSize() !== null && $upload->getSize() > $maxSize ) {
					throw new \Aimeos\Admin\Graphql\Exception( 'Image upload exceeds the maximum allowed size', 413 );
				}

				$image = Image::fromStream( $upload->getStream()->detach(), $upload->getClientMediaType() );
				$provider = $this->provider( 'image', 'isolate' );
				$options = [
					'crop' => true,
					'size' => 'auto',
					'format' => 'png',
				];
				/** @var FileResponse $response */
				$response = $provider->ensure( 'isolate' )->isolate( $image, $options ); // @phpstan-ignore method.notFound

				return $this->response( $response );
			}
			catch( \Aimeos\Prisma\Exceptions\PrismaException $e )
			{
				throw new \Aimeos\Admin\Graphql\Exception( $e->getMessage(), (int) $e->getCode(), $e );
			}
		};
	}
}
