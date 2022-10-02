<?php

namespace Aimeos\Admin\Graphql\Product;

use GraphQL\Type\Definition\Type;


class Standard extends \Aimeos\Admin\Graphql\Standard
{
	public function query( string $domain ) : array
	{
		$list = parent::query( $domain );

		$list['find' . ucfirst( $domain )] = [
			'type' => $this->outputType( $domain ),
			'args' => [
				['name' => 'code', 'type' => Type::string(), 'description' => 'Unique code'],
			],
			'resolve' => $this->findItem( $domain ),
		];

		return $list;
	}
}