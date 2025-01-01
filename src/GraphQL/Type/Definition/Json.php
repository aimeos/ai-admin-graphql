<?php

/**
 * @license LGPLv3, http://opensource.org/licenses/LGPL-3.0
 * @copyright Aimeos (aimeos.org) 2023-2025
 * @package GraphQL
 * @subpackage Type
 */

namespace Aimeos\GraphQL\Type\Definition;


use GraphQL\Error\Error;
use GraphQL\Language\Printer;
use GraphQL\Type\Definition\ScalarType;


/**
 * JSON data type for GraphQL PHP library
 */
class Json extends ScalarType
{
	private static $object;

	public ?string $description = 'Arbitrary data encoded in JavaScript Object Notation (JSON)';


	/**
	 * Returns a singleton of the type object
	 *
	 * @return ScalarType Singleton of the type object
	 */
	public static function type() : ScalarType
	{
		if( !isset( self::$object ) ) {
			self::$object = new self();
		}

		return self::$object;
	}


	/**
	 * Returns the passed value serialized as JSON
	 *
	 * @param mixed $value Input value
	 * @return string Valued serialized as JSON
	 */
	public function serialize( $value ) : string
	{
		return json_encode( $value, JSON_THROW_ON_ERROR );
	}


	/**
	 * Returns the deserialized value from the passed JSON string
	 *
	 * @param mixed $value Input value
	 * @return string Deserialized value from JSON
	 */
	public function parseValue( $value )
	{
		return json_decode( $value, true, 512, JSON_THROW_ON_ERROR );
	}


	/**
	 * Returns the deserialized value from the passed GraphQL node
	 *
	 * @param mixed $node GraphQL node
	 * @param array|null $variables Additional variable data
	 * @return string Deserialized value from JSON
	 */
	public function parseLiteral( $node, ?array $variables = null )
	{
		if( !property_exists( $node, 'value' ) ) {
			throw new Error("Can not parse literals without a value: {" . Printer::doPrint( $node ) . "}.");
		}

		return json_decode( $node->value, true, 512, JSON_THROW_ON_ERROR );
	}
}
