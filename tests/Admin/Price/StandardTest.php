<?php

/**
 * @license LGPLv3, http://opensource.org/licenses/LGPL-3.0
 * @copyright Aimeos (aimeos.org), 2026
 */


namespace Aimeos\Admin\Price;


class StandardTest extends \PHPUnit\Framework\TestCase
{
	private $context;
	private $manager;
	private $productIds = [];
	private $priceIds = [];


	protected function setUp() : void
	{
		\Aimeos\MShop::cache( true );
		$this->context = \TestHelper::context();
		$this->context->config()->set( 'admin/graphql/debug', true );
		$this->context->setView( \TestHelper::view( 'unittest', $this->context->config() ) );
		$this->manager = \Aimeos\MShop::create( $this->context, 'price' );
	}


	protected function tearDown() : void
	{
		\Aimeos\MShop::create( $this->context, 'product' )->delete( $this->productIds );
		$this->manager->delete( $this->priceIds );
	}


	public function testSavePrice()
	{
		$item = $this->price( 'service' );

		$result = $this->execute( 'mutation { savePrice(input: {id: "' . $item->getId() . '", value: "13.37"}) { id value } }' );

		$this->assertArrayNotHasKey( 'errors', $result );
		$this->assertEquals( '13.37', $this->manager->get( $item->getId() )->getValue() );
	}


	public function testSavePriceEditor()
	{
		$item = $this->price( 'product' );
		$this->editor();

		$result = $this->execute( 'mutation { savePrice(input: {id: "' . $item->getId() . '", value: "13.37"}) { id value } }' );

		$this->assertArrayNotHasKey( 'errors', $result );
		$this->assertEquals( '13.37', $this->manager->get( $item->getId() )->getValue() );
	}


	public function testSavePriceEditorServiceOwned()
	{
		$item = $this->price( 'service' );
		$this->editor();

		$result = $this->execute( 'mutation { savePrice(input: {id: "' . $item->getId() . '", value: "13.37"}) { id value } }' );

		$this->assertEquals( 'Forbidden', $result['errors'][0]['message'] ?? null );
		$this->assertEquals( '10.00', $this->manager->get( $item->getId() )->getValue() );
	}


	public function testSavePriceEditorMoveToService()
	{
		$item = $this->price( 'product' );
		$this->editor();

		$result = $this->execute( 'mutation { savePrice(input: {id: "' . $item->getId() . '", domain: "service"}) { id } }' );

		$this->assertEquals( 'Forbidden', $result['errors'][0]['message'] ?? null );
		$this->assertEquals( 'product', $this->manager->get( $item->getId() )->getDomain() );
	}


	public function testSavePriceEditorCreateService()
	{
		$this->editor();

		$result = $this->execute( 'mutation { savePrice(input: {domain: "service", value: "13.37", currencyid: "EUR"}) { id } }' );

		$this->assertEquals( 'Forbidden', $result['errors'][0]['message'] ?? null );
	}


	public function testSavePricesEditorServiceOwned()
	{
		$item = $this->price( 'service' );
		$this->editor();

		$result = $this->execute( 'mutation { savePrices(input: [{id: "' . $item->getId() . '", value: "13.37"}]) { id value } }' );

		$this->assertEquals( 'Forbidden', $result['errors'][0]['message'] ?? null );
		$this->assertEquals( '10.00', $this->manager->get( $item->getId() )->getValue() );
	}


	public function testSavePricesEditorNew()
	{
		$this->editor();

		$result = $this->execute( 'mutation { savePrices(input: [{domain: "product", value: "13.37", currencyid: "EUR"}]) { id } }' );
		$this->priceIds[] = $id = $result['data']['savePrices'][0]['id'] ?? null;

		$this->assertArrayNotHasKey( 'errors', $result );
		$this->assertEquals( '13.37', $this->manager->get( $id )->getValue() );
	}


	public function testSaveProductEditorNestedNew()
	{
		$product = $this->product();
		$this->editor();

		$result = $this->execute( 'mutation { saveProduct(input: {id: "' . $product->getId() . '", lists: {price: [{'
			. 'type: "default", item: {domain: "product", value: "13.37", currencyid: "EUR"}'
			. '}]}}) { id } }' );

		$prices = \Aimeos\MShop::create( $this->context, 'product' )->get( $product->getId(), ['price'] )->getRefItems( 'price' );
		$this->priceIds = array_merge( $this->priceIds, $prices->keys()->all() );

		$this->assertArrayNotHasKey( 'errors', $result );
		$this->assertEquals( ['13.37'], $prices->getValue()->values()->all() );
	}


	public function testSavePricesUnknownId()
	{
		$result = $this->execute( 'mutation { savePrices(input: [{id: "-1", value: "13.37", currencyid: "EUR"}]) { id } }' );

		$this->assertStringContainsString( 'not found', $result['errors'][0]['message'] ?? '' );
	}


	public function testDeletePriceEditorServiceOwned()
	{
		$item = $this->price( 'service' );
		$this->editor();

		$result = $this->execute( 'mutation { deletePrice(id: "' . $item->getId() . '") }' );

		$this->assertEquals( 'Forbidden', $result['errors'][0]['message'] ?? null );
		$this->assertEquals( $item->getId(), $this->manager->get( $item->getId() )->getId() );
	}


	public function testDeletePricesEditorServiceOwned()
	{
		$product = $this->price( 'product' );
		$service = $this->price( 'service' );
		$this->editor();

		$result = $this->execute( 'mutation { deletePrices(id: ["' . $product->getId() . '", "' . $service->getId() . '"]) }' );

		$this->assertEquals( 'Forbidden', $result['errors'][0]['message'] ?? null );
		$this->assertEquals( 2, count( $this->manager->search( $this->manager->filter()->add( 'price.id', '==', $this->priceIds ) ) ) );
	}


	public function testSaveProductEditorNestedRepoint()
	{
		$item = $this->price( 'service' );
		$product = $this->product();
		$this->editor();

		// The ID of the nested item must not re-point the product price to the service price
		$result = $this->execute( 'mutation { saveProduct(input: {id: "' . $product->getId() . '", lists: {price: [{'
			. 'type: "default", item: {id: "' . $item->getId() . '", value: "13.37"}'
			. '}]}}) { id } }' );

		$this->assertEquals( 'Forbidden', $result['errors'][0]['message'] ?? null );
		$this->assertEquals( '10.00', $this->manager->get( $item->getId() )->getValue() );
	}


	public function testSaveProductEditorNestedServiceOwned()
	{
		$item = $this->price( 'service' );
		$product = $this->product( $item );
		$listId = $product->getListItems( 'price' )->firstKey();
		$this->editor();

		// A service price linked to a product must not be writable through the product
		$result = $this->execute( 'mutation { saveProduct(input: {id: "' . $product->getId() . '", lists: {price: [{'
			. 'id: "' . $listId . '", refid: "' . $item->getId() . '", type: "default", item: {value: "13.37"}'
			. '}]}}) { id } }' );

		$this->assertEquals( 'Forbidden', $result['errors'][0]['message'] ?? null );
		$this->assertEquals( '10.00', $this->manager->get( $item->getId() )->getValue() );
	}


	public function testSaveProductNestedUnknownId()
	{
		$product = $this->product();

		$result = $this->execute( 'mutation { saveProduct(input: {id: "' . $product->getId() . '", lists: {price: [{'
			. 'type: "default", item: {id: "-1", value: "13.37"}'
			. '}]}}) { id } }' );

		$this->assertStringContainsString( 'not found', $result['errors'][0]['message'] ?? '' );
	}


	protected function editor() : void
	{
		$view = \TestHelper::view( 'unittest', $this->context->config() );
		$view->addHelper( 'access', new \Aimeos\Base\View\Helper\Access\Standard( $view, ['editor'] ) );
		$this->context->setView( $view );
	}


	protected function execute( string $query ) : array
	{
		$request = new \Nyholm\Psr7\ServerRequest( 'POST', 'localhost', [], json_encode( ['query' => $query] ) );
		return json_decode( (string) \Aimeos\Admin\Graphql::execute( $this->context, $request )->getBody(), true );
	}


	protected function price( string $domain ) : \Aimeos\MShop\Price\Item\Iface
	{
		$item = $this->manager->create()->setDomain( $domain )->setValue( '10.00' )->setCurrencyId( 'EUR' );
		$item = $this->manager->save( $item );
		$this->priceIds[] = $item->getId();

		return $item;
	}


	protected function product( ?\Aimeos\MShop\Price\Item\Iface $price = null ) : \Aimeos\MShop\Product\Item\Iface
	{
		$manager = \Aimeos\MShop::create( $this->context, 'product' );
		$item = $manager->create()->setCode( 'graphql-price-test' )->setLabel( 'GraphQL price test' );

		if( $price ) {
			$item->addListItem( 'price', $manager->createListItem()->setType( 'default' )->setRefId( $price->getId() ) );
		}

		$item = $manager->save( $item );
		$this->productIds[] = $item->getId();

		return $manager->get( $item->getId(), ['price'] );
	}
}
