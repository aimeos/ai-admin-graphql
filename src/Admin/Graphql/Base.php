<?php

namespace Aimeos\Admin\Graphql;


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


	protected function type( string $value ) : string
	{
		switch( $value )
		{
			case 'boolean': return 'Boolean';
			case 'float': return 'Float';
			case 'integer': return 'Int';
		}

		return 'String';
	}
}