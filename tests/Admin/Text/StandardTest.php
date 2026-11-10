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
		$this->manager = \Aimeos\MShop::create( \TestHelper::context(), 'text' );

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

		// items of domains the user isn't allowed to access can't be changed
		$this->assertArrayHasKey( 'errors', $result );
		$this->assertEquals( 'test', $this->manager->get( $item->getId() )->getContent() );
	}


	public function testDeleteTextEditorServiceOwned()
	{
		$item = $this->text( 'service' );

		$result = $this->execute( 'mutation { deleteText(id: "' . $item->getId() . '") }' );

		$this->assertArrayHasKey( 'errors', $result );
		$this->assertEquals( $item->getId(), $this->manager->get( $item->getId() )->getId() );
	}


	public function testGetTextEditor()
	{
		$item = $this->text( 'product' );

		$result = $this->execute( 'query { getText(id: "' . $item->getId() . '") { id content } }' );

		$this->assertArrayNotHasKey( 'errors', $result );
		$this->assertEquals( 'test', $result['data']['getText']['content'] ?? null );
	}


	public function testGetTextEditorServiceOwned()
	{
		$item = $this->text( 'service' );

		$result = $this->execute( 'query { getText(id: "' . $item->getId() . '") { id content } }' );

		// shared items can contain sensitive data of the domain owning them
		// reported like missing items
		$this->assertEquals( 'Internal server error', $result['errors'][0]['message'] ?? null );
	}


	public function testSearchTextsEditorServiceOwned()
	{
		$product = $this->text( 'product' );
		$service = $this->text( 'service' );

		$filter = addslashes( json_encode( ['==' => ['text.id' => [$product->getId(), $service->getId()]]] ) );
		$result = $this->execute( 'query { searchTexts(filter: "' . $filter . '") { items { id } total } }' );

		// shared items of domains the user isn't allowed to access aren't returned
		$this->assertArrayNotHasKey( 'errors', $result );
		$this->assertEquals( 1, $result['data']['searchTexts']['total'] ?? null );
		$this->assertEquals( $product->getId(), $result['data']['searchTexts']['items'][0]['id'] ?? null );
	}


	protected function execute( string $query ) : array
	{
		$request = new \Nyholm\Psr7\ServerRequest( 'POST', 'localhost', [], json_encode( ['query' => $query] ) );
		return json_decode( (string) \Aimeos\Admin\Graphql::execute( $this->context, $request )->getBody(), true );
	}


	protected function text( string $domain ) : \Aimeos\MShop\Text\Item\Iface
	{
		$item = $this->manager->save( $this->manager->create()->setDomain( $domain )->setContent( 'test' ) );
		$this->ids[] = $item->getId();

		return $item;
	}
}
