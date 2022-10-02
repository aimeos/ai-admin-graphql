<?php

namespace Aimeos\Admin\Graphql\Product;

use GraphQL\Type\Definition\Type;


class Standard extends \Aimeos\Admin\Graphql\Standard
{
	public function query( string $domain ) : array
	{
		$list = parent::query( $domain );

		$list['findProduct'] = [
			'type' => $this->outputType( $domain ),
			'args' => [
				['name' => 'code', 'type' => Type::string(), 'description' => 'Unique code'],
				['name' => 'include', 'type' => Type::string(), 'defaultValue' => '', 'description' => 'Domains to include'],
			],
			'resolve' => $this->findItem( $domain ),
		];

		return $list;
	}
}