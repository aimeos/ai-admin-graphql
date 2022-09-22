<?php

namespace Aimeos\Admin\Graphql;

use GraphQL\Type\Definition\Type;


abstract class Base
{
	public function __construct( \Aimeos\MShop\ContextIface $context )
	{
		$this->context = $context;
	}


	protected function context() : \Aimeos\MShop\ContextIface
	{
		return $this->context;
	}


	protected function name( string $value ) : string
	{
		$pos = strrpos( $value, '.' );
		return substr( $value, $pos ? $pos + 1 : 0 );
	}


	protected function type( string $value ) : Type
	{
		switch( $value )
		{
			case 'boolean': return Type::boolean();
			case 'float': return Type::float();
			case 'integer': return Type::int();
		}

		return Type::string();
	}
}