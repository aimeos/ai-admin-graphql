<?php

/**
 * @license LGPLv3, http://opensource.org/licenses/LGPL-3.0
 * @copyright Aimeos (aimeos.org), 2022-2026
 * @package Admin
 * @subpackage GraphQL
 */


namespace Aimeos\Admin;


use GraphQL\GraphQL as GraphQLBase;
use GraphQL\Error\DebugFlag;
use GraphQL\Type\Schema;
use GraphQL\Type\Definition\ObjectType;
use GraphQL\Validator\DocumentValidator;
use GraphQL\Validator\Rules\QueryComplexity;
use GraphQL\Validator\Rules\QueryDepth;
use Nyholm\Psr7\Factory\Psr17Factory;


/**
 * Central GraphQL class for handling requests
 *
 * @package Admin
 * @subpackage GraphQL
 */
class Graphql
{
	/**
	 * Executes the GraphQL request
	 *
	 * @param \Aimeos\MShop\ContextIface $context Context object
	 * @param \Psr\Http\Message\ServerRequestInterface $request Request object
	 * @return \Psr\Http\Message\ResponseInterface Response object containing the generated output
	 */
	public static function execute( \Aimeos\MShop\ContextIface $context,
		\Psr\Http\Message\ServerRequestInterface $request ) : \Psr\Http\Message\ResponseInterface
	{
		$debug = false;

		try
		{
			/** admin/graphql/debug
			 * Send debug information withing responses to clients if an error occurrs
			 *
			 * By default, the Aimeos admin GraphQL API won't send any details
			 * besides the error message to the client if an error occurred. This
			 * prevents leaking sensitive information to attackers. For debugging
			 * your requests it's helpful to see the stack strace. If you set this
			 * configuration option to true, the stack trace will be returned too.
			 *
			 * @param boolean True to return the stack trace in response, false for error message only
			 * @since 2022.10
			 * @category Developer
			 */
			$debug = $context->config()->get( 'admin/graphql/debug', false );

			if( !is_array( $input = $request->getParsedBody() ) )
			{
				if( empty( $input = json_decode( $request->getBody()->getContents(), true ) ) ) {
					throw new \Aimeos\Admin\Graphql\Exception( 'Invalid input' );
				}
			}

			if( isset( $input['operations'] ) )
			{
				$map = json_decode( (string) $input['map'], true );
				$operations = json_decode( (string) $input['operations'], true );
				$operations = self::files( $request->getUploadedFiles(), (array) $operations, (array) $map );

				$opname = $operations['operationName'] ?? null;
				$variables = $operations['variables'] ?? [];
				$query = $operations['query'] ?? '';
			}
			else
			{
				$opname = $input['operationName'] ?? null;
				$variables = $input['variables'] ?? [];
				$query = $input['query'] ?? '';
			}

			/** admin/graphql/max-query-depth
			 * Maximum nesting level of the fields in GraphQL queries
			 *
			 * Deeply nested queries can be very expensive to resolve and may be
			 * used to overload the server. Requests with queries nested deeper
			 * than this value are rejected with an error. Increase the value if
			 * your clients need to fetch more levels of referenced items.
			 *
			 * @param integer Number of levels
			 * @since 2026.04
			 * @category Developer
			 * @see admin/graphql/max-query-complexity
			 */
			$maxDepth = (int) $context->config()->get( 'admin/graphql/max-query-depth', 10 );

			/** admin/graphql/max-query-complexity
			 * Maximum complexity of GraphQL queries
			 *
			 * The complexity of a query is the number of fields it requests,
			 * including all fields of nested items. Requests with queries more
			 * complex than this value are rejected with an error to protect the
			 * server against expensive requests.
			 *
			 * @param integer Maximum complexity score
			 * @since 2026.04
			 * @category Developer
			 * @see admin/graphql/max-query-depth
			 */
			$maxComplexity = (int) $context->config()->get( 'admin/graphql/max-query-complexity', 500 );

			$validationRules = array_merge( DocumentValidator::allRules(), [
				QueryDepth::class => new QueryDepth( $maxDepth ),
				QueryComplexity::class => new QueryComplexity( $maxComplexity ),
			] );

			$result = GraphQLBase::executeQuery(
				self::schema( $context ),
				// @phpstan-ignore argument.type
				$query,
				[], // root
				null, // context
				// @phpstan-ignore argument.type
				$variables,
				// @phpstan-ignore argument.type
				$opname,
				null, // fieldResolver
				$validationRules
			)->toArray( $debug ? DebugFlag::INCLUDE_DEBUG_MESSAGE | DebugFlag::INCLUDE_TRACE : 0 );
		}
		catch( \Throwable $t )
		{
			$error = ['message' => $t->getMessage()];

			if( $debug ) {
				$error['locations'] = $t->getTrace();
			}

			$result = ['errors' => [$error]];
		}

		// @phpstan-ignore argument.type
		$body = \Nyholm\Psr7\Stream::create( json_encode( $result ) );
		return ( new Psr17Factory )->createResponse()->withBody( $body );
	}


	/**
	 * Maps the files to the corresponding variables
	 *
	 * @param array $files List of \Psr\Http\Message\UploadedFileInterface objects
	 * @param array $operations GraphQL operations with "variables" section
	 * @param array $map GraphQL variable mapping
	 * @return array Mapped operations array
	 * @see https://github.com/Ecodev/graphql-upload
	 */
	protected static function files( array $files, array $operations, array $map ) : array
	{
		foreach( $map as $fileKey => $locations )
		{
			foreach( $locations as $location )
			{
				$items = &$operations;

				foreach( explode( '.', (string) $location ) as $key )
				{
					if( !isset( $items[$key] ) || !is_array( $items[$key] ) ) {
						$items[$key] = [];
					}

					$items = &$items[$key];
				}

				if( !array_key_exists( $fileKey, $files ) )
				{
					throw new \Aimeos\Admin\Graphql\Exception(
						'GraphQL query declared an upload in `' . $location . '`, but no corresponding file were actually uploaded'
					);
				}

				$items = $files[$fileKey];
			}
		}

		return (array) $operations;
	}


	/**
	 * Returns the GraphQL schema
	 *
	 * @param \Aimeos\MShop\ContextIface $context Context object
	 * @return \GraphQL\Type\Schema List of GraphQL query schema definitions
	 */
	protected static function schema( \Aimeos\MShop\ContextIface $context ) : \GraphQL\Type\Schema
	{
		$query = $mutation = [];
		$config = $context->config();
		$registry = new \Aimeos\Admin\Graphql\Registry( $context );

		/** admin/graphql/name
		 * Class name of the GraphQL implementation used for all domains
		 *
		 * The GraphQL queries and mutations of each domain are provided by the
		 * class configured in "admin/graphql/<domain>/name", e.g.
		 *
		 *  admin/graphql/product/name = Myproduct
		 *
		 * uses \Aimeos\Admin\Graphql\Product\Myproduct. For sub-domains like
		 * "product/lists/type", the class is \Aimeos\Admin\Graphql\Product\Lists\Type\...
		 * If no such class exists, the class configured by this setting is used
		 * instead, which is \Aimeos\Admin\Graphql\Standard by default.
		 *
		 * The value is the last part of your own class name and it's case
		 * sensitive, so take care that the configuration value is exactly named
		 * like the last part of the class name.
		 *
		 * @param string Last part of the class name
		 * @since 2022.10
		 * @see admin/graphql/domains
		 */
		$stdname = $config->get( 'admin/graphql/name', 'Standard' );
		$domains = $config->get( 'admin/graphql/domains', [] );

		foreach( $domains as $domain )
		{
			$name = $config->get( 'admin/graphql/' . $domain . '/name', 'Standard' );
			$classname = '\Aimeos\Admin\Graphql\\' . str_replace( '/', '\\', ucwords( (string) $domain, '/' ) ) . '\\' . $name;

			if( !class_exists( $classname ) ) {
				$classname = '\Aimeos\Admin\Graphql\\' . $stdname;
			}

			$object = new $classname( $context, $registry );

			// @phpstan-ignore argument.type, method.notFound
			$mutation = array_replace_recursive( $mutation, $object->mutation( $domain ) );
			// @phpstan-ignore argument.type, method.notFound
			$query = array_replace_recursive( $query, $object->query( $domain ) );
		}

		return new Schema([
			'query' => new ObjectType( [
				'name' => 'query',
				'fields' => $query
			] ),
			'mutation' => new ObjectType( [
				'name' => 'mutation',
				'fields' => $mutation
			] ),
		] );
	}
}
