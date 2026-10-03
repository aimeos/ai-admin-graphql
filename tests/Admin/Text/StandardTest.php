<?php

/**
 * @license LGPLv3, http://opensource.org/licenses/LGPL-3.0
 * @copyright Aimeos (aimeos.org), 2026
 */


namespace Aimeos\Admin\Text;


class StandardTest extends \PHPUnit\Framework\TestCase
{
	private $context;
	private $manager;
	private $ids = [];


	protected function setUp() : void
	{
		\Aimeos\MShop::cache( true );
		$this->context = \TestHelper::context();
		$this->context->config()->set( 'admin/graphql/debug', true );
		$this->context->setView( \TestHelper::view( 'unittest', $this->context->config() ) );
		$this->manager = \Aimeos\MShop::create( $this->context, 'text' );

		$view = \TestHelper::view( 'unittest', $this->context->config() );
		$view->addHelper( 'access', new \Aimeos\Base\View\Helper\Access\Standard( $view, ['editor'] ) );
		$this->context->setView( $view );
	}


	protected function tearDown() : void
	{
		$this->manager->delete( $this->ids );
	}


	public function testSaveTextEditor()
	{
		$item = $this->text( 'product' );

		$result = $this->execute( 'mutation { saveText(input: {id: "' . $item->getId() . '", content: "changed"}) { id } }' );

		$this->assertArrayNotHasKey( 'errors', $result );
		$this->assertEquals( 'changed', $this->manager->get( $item->getId() )->getContent() );
	}


	public function testSaveTextEditorServiceOwned()
	{
		$item = $this->text( 'service' );

		$result = $this->execute( 'mutation { saveText(input: {id: "' . $item->getId() . '", content: "changed"}) { id } }' );

		$this->assertEquals( 'Forbidden', $result['errors'][0]['message'] ?? null );
		$this->assertEquals( 'test', $this->manager->get( $item->getId() )->getContent() );
	}


	public function testDeleteTextEditorServiceOwned()
	{
		$item = $this->text( 'service' );

		$result = $this->execute( 'mutation { deleteText(id: "' . $item->getId() . '") }' );

		$this->assertEquals( 'Forbidden', $result['errors'][0]['message'] ?? null );
		$this->assertEquals( $item->getId(), $this->manager->get( $item->getId() )->getId() );
	}


	protected function execute( string $query ) : array
	{
		$request = new \Nyholm\Psr7\ServerRequest( 'POST', 'localhost', [], json_encode( ['query' => $query] ) );
		return json_decode( (string) \Aimeos\Admin\Graphql::execute( $this->context, $request )->getBody(), true );
	}


	protected function text( string $domain ) : \Aimeos\MShop\Text\Item\Iface
	{
		$item = $this->manager->save( $this->manager->create()->setDomain( $domain )->setType( 'name' )->setContent( 'test' ) );
		$this->ids[] = $item->getId();

		return $item;
	}
}
