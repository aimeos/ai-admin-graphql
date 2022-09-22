<?php

namespace Aimeos\Admin;


use GraphQL\GraphQL;
use GraphQL\Type\Schema;
use GraphQL\Type\Definition\ObjectType;


class Graphql
{
	public static function execute( \Aimeos\MShop\ContextIface $context ) : string
    {
        try
        {
            $request = $context->view()->request();
            $input = json_decode( $request->getBody(), true);

            return GraphQL::executeQuery(
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
            return [
                'errors' => [[
                    'message' => $t->getMessage()
                ]]
            ];
        }
    }


    protected static function schema( \Aimeos\MShop\ContextIface $context ) : array
    {
        $types = [];
        $domains = $context->config()->get( 'admin/graphql/domains', ['product'] );

        foreach( $domains as $domain )
        {
            $name = $context->config()->get( 'admin/graphql/' . $domain . '/name', 'Standard' );

            $classname = '\Aimeos\Graphql\\' . \ucfirst( $domain ) . '\\Standard';
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