<?php

/**
 * @license LGPLv3, http://opensource.org/licenses/LGPL-3.0
 * @copyright Aimeos (aimeos.org), 2022
 * @package Admin
 * @subpackage GraphQL
 */


namespace Aimeos\Admin;


use GraphQL\GraphQL as GraphQLBase;
use GraphQL\Type\Schema;
use GraphQL\Type\Definition\ObjectType;
use Nyholm\Psr7\Factory\Psr17Factory;


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
			$input = json_decode( (string) $request->getBody(), true);

			$result = GraphQLBase::executeQuery(
				self::schema( $context ),
				$input['query'] ?? null,
				[], // root
				null, // context
				$input['variables'] ?? null,
				$input['operationName'] ?? null
			)->toArray();
		}
		catch( \Throwable $t )
		{
			$result = [
				'errors' => [[
					'message' => $t->getMessage()
				]]
			];
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
		$types = [];
		$domains = $context->config()->get( 'admin/graphql/domains', ['product'] );

		foreach( $domains as $domain )
		{
			$name = $context->config()->get( 'admin/graphql/' . $domain . '/name', 'Standard' );

			$classname = '\Aimeos\Admin\Graphql\\' . ucfirst( $domain ) . '\\' . $name;
			$object = new $classname( $context );

			$types[$domain] = $object->types();
		}

		return new Schema([
			'query' => new ObjectType( [
				'name' => 'query',
				'fields' => $types
			] ),
		] );
	}
}