<?php

namespace Aimeos\Admin;


use GraphQL\GraphQL;
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

			$result = GraphQL::executeQuery(
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

		return ( new Psr17Factory )->createResponse()->withBody( json_encode( $result ) );
	}


	/**
	 * Returns the GraphQL schema
	 *
	 * @param \Aimeos\MShop\ContextIface $context Context object
	 * @return array List of GraphQL query schema definitions
	 */
	protected static function schema( \Aimeos\MShop\ContextIface $context ) : array
	{
		$types = [];
		$domains = $context->config()->get( 'admin/graphql/domains', ['product'] );

		foreach( $domains as $domain )
		{
			$name = $context->config()->get( 'admin/graphql/' . $domain . '/name', 'Standard' );

			$classname = '\Aimeos\Graphql\\' . ucfirst( $domain ) . '\\Standard';
			$object = new $classname( $context );

			$types[$domain] = $object->type();
		}

		return new Schema([
			'query' => new ObjectType( [
				'name' => 'query',
				'fields' => $types
			] ),
		] );
	}
}