<?php

/**
 * @license LGPLv3, http://opensource.org/licenses/LGPL-3.0
 * @copyright Aimeos (aimeos.org) 2024
 * @package GraphQL
 * @subpackage Type
 */

namespace Aimeos\GraphQL\Type\Definition;


use GraphQL\Error\Error;
use GraphQL\Utils\Utils;
use GraphQL\Language\Printer;
use GraphQL\Error\InvariantViolation;
use GraphQL\Type\Definition\ScalarType;
use Psr\Http\Message\UploadedFileInterface;


/**
 * Upload data type for GraphQL PHP library
 */
class Upload extends ScalarType
{
	private static $object;

	public ?string $description = 'File upload type';


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
		throw new InvariantViolation( '"Upload" cannot be serialized, it can only be used as an argument.' );
	}


	/**
	 * Returns the deserialized value from the passed JSON string
	 *
	 * @param mixed $value Input value
	 * @return UploadedFileInterface PSR-7 uploaded file object
	 */
	public function parseValue( $value )
	{
		if( !( $value instanceof UploadedFileInterface ) )
		{
			$notUploadedFile = Utils::printSafe($value);
			throw new Error( "Could not get uploaded file, be sure to conform to GraphQL multipart request specification: https://github.com/jaydenseric/graphql-multipart-request-spec. Instead got: {$notUploadedFile}." );
		}

		return $value;
	}


	/**
	 * Returns the deserialized value from the passed GraphQL node
	 *
	 * @param mixed $node GraphQL node
	 * @param array|null $variables Additional variable data
	 * @return string Deserialized value from JSON
	 */
	public function parseLiteral( $node, array $variables = null )
	{
		throw new Error( '"Upload" cannot be hardcoded in a query. Be sure to conform to the GraphQL multipart request specification: https://github.com/jaydenseric/graphql-multipart-request-spec.' );
	}
}
