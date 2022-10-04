<?php

/**
 * @license LGPLv3, http://opensource.org/licenses/LGPL-3.0
 * @copyright Aimeos (aimeos.org), 2022
 * @package Admin
 * @subpackage GraphQL
 */


namespace Aimeos\Admin;


use GraphQL\GraphQL as GraphQLBase;
use GraphQL\Error\DebugFlag;
use GraphQL\Type\Schema;
use GraphQL\Type\Definition\ObjectType;
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

			if( empty( $input = json_decode( (string) $request->getBody(), true ) ) ) {
				throw new \Aimeos\Admin\Graphql\Exception( 'Invalid input' );
			}

			$result = GraphQLBase::executeQuery(
				self::schema( $context ),
				$input['query'] ?? null,
				[], // root
				null, // context
				$input['variables'] ?? null,
				$input['operationName'] ?? null
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

		$body = \Nyholm\Psr7\Stream::create( json_encode( $result ) );
		return ( new Psr17Factory )->createResponse()->withBody( $body );
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
		$stdname = $context->config()->get( 'admin/graphql/name', 'Standard' );
		$domains = $context->config()->get( 'admin/graphql/domains', [] );

		foreach( $domains as $domain )
		{
			$name = $context->config()->get( 'admin/graphql/' . $domain . '/name', 'Standard' );
			$classname = '\Aimeos\Admin\Graphql\\' . ucfirst( $domain ) . '\\' . $name;

			if( !class_exists( $classname ) ) {
				$classname = '\Aimeos\Admin\Graphql\\' . $stdname;
			}

			$object = new $classname( $context );

			$mutation = array_replace_recursive( $mutation, $object->mutation( $domain ) );
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