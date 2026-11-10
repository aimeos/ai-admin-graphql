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
		$this->manager = \Aimeos\MShop::create( \TestHelper::context(), 'price' );
	}


	protected function tearDown() : void
	{
		\Aimeos\MShop::create( \TestHelper::context(), 'product' )->delete( $this->productIds );
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

		// items of domains the user isn't allowed to access can't be changed
		$this->assertStringContainsString( 'Permission "save" for "service" is required', $result['errors'][0]['message'] ?? '' );
		$this->assertEquals( '10.00', $this->manager->get( $item->getId() )->getValue() );
	}


	public function testSavePriceEditorMoveToService()
	{
		$item = $this->price( 'product' );
		$this->editor();

		$result = $this->execute( 'mutation { savePrice(input: {id: "' . $item->getId() . '", domain: "service"}) { id } }' );

		$this->assertArrayHasKey( 'errors', $result );
		$this->assertEquals( 'product', $this->manager->get( $item->getId() )->getDomain() );
	}


	public function testSavePriceEditorCreateService()
	{
		$this->editor();

		$result = $this->execute( 'mutation { savePrice(input: {domain: "service", value: "13.37", currencyid: "EUR"}) { id } }' );

		$this->assertArrayHasKey( 'errors', $result );
		$this->assertNull( $result['data']['savePrice'] ?? null );
	}


	public function testSavePricesEditorServiceOwned()
	{
		$item = $this->price( 'service' );
		$this->editor();

		$result = $this->execute( 'mutation { savePrices(input: [{id: "' . $item->getId() . '", value: "13.37"}]) { id value } }' );

		$this->assertArrayHasKey( 'errors', $result );
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

		$this->assertStringContainsString( 'Permission "save" for "service" is required', $result['errors'][0]['message'] ?? '' );
		$this->assertEquals( $item->getId(), $this->manager->get( $item->getId() )->getId() );
	}


	public function testDeletePricesEditorServiceOwned()
	{
		$product = $this->price( 'product' );
		$service = $this->price( 'service' );
		$this->editor();

		$result = $this->execute( 'mutation { deletePrices(id: ["' . $product->getId() . '", "' . $service->getId() . '"]) }' );

		// nothing is deleted if one of the items can't be deleted
		$this->assertArrayHasKey( 'errors', $result );
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

		$this->assertArrayHasKey( 'errors', $result );
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

		$this->assertArrayHasKey( 'errors', $result );
		$this->assertEquals( '10.00', $this->manager->get( $item->getId() )->getValue() );
	}


	public function testSaveProductEditorLink()
	{
		$item = $this->price( 'product' );
		$product = $this->product();
		$this->editor();

		$result = $this->execute( 'mutation { saveProduct(input: {id: "' . $product->getId() . '", lists: {price: [{'
			. 'refid: "' . $item->getId() . '", type: "default"'
			. '}]}}) { id } }' );

		$this->assertArrayNotHasKey( 'errors', $result );
		$this->assertEquals( 1, count( $this->products( $product->getId() )->getListItems( 'price' ) ) );
	}


	public function testSaveProductEditorLinkServiceOwned()
	{
		$item = $this->price( 'service' );
		$product = $this->product();
		$this->editor();

		// Service prices can be linked but not changed
		$result = $this->execute( 'mutation { saveProduct(input: {id: "' . $product->getId() . '", lists: {price: [{'
			. 'refid: "' . $item->getId() . '", type: "default"'
			. '}]}}) { id } }' );

		$this->assertArrayNotHasKey( 'errors', $result );
		$this->assertEquals( 1, count( $this->products( $product->getId() )->getListItems( 'price' ) ) );
		$this->assertEquals( '10.00', $this->manager->get( $item->getId() )->getValue() );
	}


	public function testSaveProductEditorUnlinkServiceOwned()
	{
		$service = $this->price( 'service' );
		$product = $this->product( $service );
		$this->editor();

		// Service prices can be unlinked but not deleted
		$result = $this->execute( 'mutation { saveProduct(input: {id: "' . $product->getId() . '", lists: {price: []}}) { id } }' );

		$this->assertArrayNotHasKey( 'errors', $result );
		$this->assertEquals( 0, count( $this->products( $product->getId() )->getListItems( 'price' ) ) );
		$this->assertEquals( $service->getId(), $this->manager->get( $service->getId() )->getId() );
	}


	public function testSaveProductNestedUnknownId()
	{
		$product = $this->product();

		$result = $this->execute( 'mutation { saveProduct(input: {id: "' . $product->getId() . '", lists: {price: [{'
			. 'type: "default", item: {id: "-1", value: "13.37"}'
			. '}]}}) { id } }' );

		$this->assertStringContainsString( 'not found', $result['errors'][0]['message'] ?? '' );
	}


	public function testGetPriceEditorServiceOwned()
	{
		$item = $this->price( 'service' );
		$this->editor();

		$result = $this->execute( 'query { getPrice(id: "' . $item->getId() . '") { id value } }' );

		// shared items can contain sensitive data of the domain owning them
		// reported like missing items
		$this->assertEquals( 'Internal server error', $result['errors'][0]['message'] ?? null );
	}


	public function testGetPriceEditor()
	{
		$item = $this->price( 'product' );
		$this->editor();

		$result = $this->execute( 'query { getPrice(id: "' . $item->getId() . '") { id value } }' );

		$this->assertArrayNotHasKey( 'errors', $result );
		$this->assertEquals( '10.00', $result['data']['getPrice']['value'] ?? null );
	}


	public function testSearchPricesEditorServiceOwned()
	{
		$product = $this->price( 'product' );
		$service = $this->price( 'service' );
		$this->editor();

		$filter = addslashes( json_encode( ['==' => ['price.id' => [$product->getId(), $service->getId()]]] ) );
		$result = $this->execute( 'query { searchPrices(filter: "' . $filter . '") { items { id } total } }' );

		// shared items of domains the user isn't allowed to access aren't returned
		$this->assertArrayNotHasKey( 'errors', $result );
		$this->assertEquals( 1, $result['data']['searchPrices']['total'] ?? null );
		$this->assertEquals( $product->getId(), $result['data']['searchPrices']['items'][0]['id'] ?? null );
	}


	public function testSearchPricesServiceOwned()
	{
		$product = $this->price( 'product' );
		$service = $this->price( 'service' );

		$filter = addslashes( json_encode( ['==' => ['price.id' => [$product->getId(), $service->getId()]]] ) );
		$result = $this->execute( 'query { searchPrices(filter: "' . $filter . '") { items { id } total } }' );

		$this->assertArrayNotHasKey( 'errors', $result );
		$this->assertEquals( 2, $result['data']['searchPrices']['total'] ?? null );
	}


	public function testSavePriceEditorUnconfigured()
	{
		// Domains without configured user groups don't restrict changing their items
		$item = $this->price( 'unconfigured' );
		$this->editor();

		$result = $this->execute( 'mutation { savePrice(input: {id: "' . $item->getId() . '", value: "13.37"}) { id } }' );

		$this->assertArrayNotHasKey( 'errors', $result );
		$this->assertEquals( '13.37', $this->manager->get( $item->getId() )->getValue() );
	}


	public function testGetProductEditorServiceOwnedPrice()
	{
		$product = $this->product( $this->price( 'service' ) );
		$this->editor();

		$result = $this->execute( 'query { getProduct(id: "' . $product->getId() . '", include: ["price"]) { id lists { price { refid item { id } } } } }' );

		$this->assertArrayNotHasKey( 'errors', $result );
		$this->assertEquals( 1, count( $result['data']['getProduct']['lists']['price'] ?? [] ) );
	}


	public function testSearchProductsEditorIncludeDenied()
	{
		$product = $this->product( $this->price( 'product' ) );
		$this->context->config()->set( 'admin/graphql/resource/price/get', ['admin'] );
		$this->editor();

		$filter = addslashes( json_encode( ['==' => ['product.id' => $product->getId()]] ) );
		$result = $this->execute( 'query { searchProducts(filter: "' . $filter . '", include: ["price"]) { items { id lists { price { refid } } } } }' );

		$this->assertArrayNotHasKey( 'errors', $result );
		$this->assertEquals( [], $result['data']['searchProducts']['items'][0]['lists']['price'] ?? null );
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


	protected function products( string $id ) : \Aimeos\MShop\Product\Item\Iface
	{
		return \Aimeos\MShop::create( \TestHelper::context(), 'product' )->get( $id, ['price'] );
	}


	protected function product( \Aimeos\MShop\Price\Item\Iface ...$prices ) : \Aimeos\MShop\Product\Item\Iface
	{
		$manager = \Aimeos\MShop::create( \TestHelper::context(), 'product' );
		$item = $manager->create()->setCode( 'graphql-price-test' )->setLabel( 'GraphQL price test' );

		foreach( $prices as $price ) {
			$item->addListItem( 'price', $manager->createListItem()->setType( 'default' )->setRefId( $price->getId() ) );
		}

		$item = $manager->save( $item );
		$this->productIds[] = $item->getId();

		return $manager->get( $item->getId(), ['price'] );
	}
}
