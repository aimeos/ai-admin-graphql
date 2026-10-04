<?php

/**
 * @license LGPLv3, http://opensource.org/licenses/LGPL-3.0
 * @copyright Aimeos (aimeos.org), 2026
 */


namespace Aimeos\Admin\Customer;


class StandardTest extends \PHPUnit\Framework\TestCase
{
	private $context;
	private $manager;
	private $item;


	protected function setUp() : void
	{
		\Aimeos\MShop::cache( true );
		$this->context = \TestHelper::context();
		$this->context->config()->set( 'admin/graphql/debug', true );
		$this->manager = \Aimeos\MShop::create( $this->context, 'customer' );
		$this->item = $this->manager->find( 'test@example.com' );

		$view = \TestHelper::view( 'unittest', $this->context->config() );
		$view->addHelper( 'access', new \Aimeos\Base\View\Helper\Access\Standard( $view, ['editor'] ) );
		$this->context->setView( $view );
	}


	protected function tearDown() : void
	{
		$this->manager->save( $this->item );
		\Aimeos\MShop::cache( false );
	}


	public function testSaveCustomerEditor()
	{
		$this->context->setUserId( $this->item->getId() );

		$result = $this->execute( 'mutation { saveCustomer(input: {id: "' . $this->item->getId() . '", lastname: "graphql"}) { id } }' );

		$this->assertArrayNotHasKey( 'errors', $result );
		$this->assertEquals( 'graphql', $this->manager->get( $this->item->getId() )->get( 'customer.lastname' ) );
	}


	public function testSaveCustomerEditorNoUser()
	{
		$this->context->setUserId( null );

		$result = $this->execute( 'mutation { saveCustomer(input: {id: "' . $this->item->getId() . '", lastname: "graphql"}) { id } }' );

		$this->assertArrayNotHasKey( 'errors', $result );
		$this->assertEquals( $this->item->get( 'customer.lastname' ), $this->manager->get( $this->item->getId() )->get( 'customer.lastname' ) );
	}


	protected function execute( string $query ) : array
	{
		$request = new \Nyholm\Psr7\ServerRequest( 'POST', 'localhost', [], json_encode( ['query' => $query] ) );
		return json_decode( (string) \Aimeos\Admin\Graphql::execute( $this->context, $request )->getBody(), true );
	}
}
