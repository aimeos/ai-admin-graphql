<?php

/**
 * @license LGPLv3, https://opensource.org/licenses/LGPL-3.0
 * @copyright Aimeos (aimeos.org), 2026
 */


namespace Aimeos\Admin;


class GraphqlTest extends \PHPUnit\Framework\TestCase
{
	public function testExecutePolicyDefault()
	{
		$context = \TestHelper::context()->setPolicy( null );
		$request = ( new \Nyholm\Psr7\ServerRequest( 'POST', 'localhost' ) )->withParsedBody( ['query' => '{ __typename }'] );

		$response = \Aimeos\Admin\Graphql::execute( $context, $request );

		// the shared context must not be changed, only the copy used by the interface
		$this->assertEquals( 200, $response->getStatusCode() );
		$this->assertNull( $context->policy() );
	}


	public function testAllowed()
	{
		$context = $this->context();

		$this->assertTrue( \Aimeos\Admin\Graphql::allowed( $context, 'order', 'get' ) );
		$this->assertFalse( \Aimeos\Admin\Graphql::allowed( $context, 'order/address', 'get' ) );
	}


	public function testAllowedInherit()
	{
		$context = $this->context();

		$this->assertTrue( \Aimeos\Admin\Graphql::allowed( $context, 'order/address', 'get', true ) );
		$this->assertFalse( \Aimeos\Admin\Graphql::allowed( $context, 'order/service', 'get', true ) );
		$this->assertFalse( \Aimeos\Admin\Graphql::allowed( $context, 'unknown/address', 'get', true ) );
	}


	protected function context() : \Aimeos\MShop\ContextIface
	{
		$view = new \Aimeos\Base\View\Standard();
		$view->addHelper( 'access', new \Aimeos\Base\View\Helper\Access\Standard( $view, ['editor'] ) );

		return \TestHelper::context()->setView( $view )->setPolicy( new class implements \Aimeos\MShop\PolicyIface {
			public function allowed( \Aimeos\MShop\ContextIface $context, string $resource, string $action ) : ?bool {
				return ['order' => true, 'order/service' => false][$resource] ?? null;
			}
		} );
	}


	public function testForbidden()
	{
		$previous = new \Aimeos\MShop\PolicyException( 'test' );
		$e = \Aimeos\Admin\Graphql::forbidden( 'test', $previous );

		$this->assertInstanceOf( \Aimeos\Admin\Graphql\Exception::class, $e );
		$this->assertSame( $previous, $e->getPrevious() );
		$this->assertEquals( 403, $e->getCode() );
	}


	public function testPolicy()
	{
		// the default policy uses the user groups configured for the interface
		$context = \TestHelper::context();
		$context->config()->set( 'admin/graphql/resource/product/save', ['editor'] );
		$context->config()->set( 'admin/graphql/resource/service/save', ['admin'] );

		$view = new \Aimeos\Base\View\Standard();
		$view->addHelper( 'access', new \Aimeos\Base\View\Helper\Access\Standard( $view, ['editor'] ) );
		$context->setView( $view );

		$policy = \Aimeos\Admin\Graphql::policy();

		$this->assertTrue( $policy->allowed( $context, 'product', 'save' ) );
		$this->assertFalse( $policy->allowed( $context, 'service', 'save' ) );
		$this->assertNull( $policy->allowed( $context, 'unknown', 'save' ) );
	}
}
