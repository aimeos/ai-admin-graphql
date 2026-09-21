<?php

/**
 * @license LGPLv3, http://opensource.org/licenses/LGPL-3.0
 * @copyright Aimeos (aimeos.org), 2024
 */


namespace Aimeos\Admin\Customer;


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
		$this->manager = \Aimeos\MShop::create( $this->context, 'customer' );
	}


	protected function tearDown() : void
	{
		$this->manager->delete( $this->ids );
	}


	public function testFindCustomer()
	{
		$body = '{"query":"query {\n  findCustomer(code: \"test@example.com\") {\n    id\n    code    groups\n  }\n}\n","variables":{},"operationName":null}';
		$request = new \Nyholm\Psr7\ServerRequest( 'POST', 'localhost', [], $body );

		$response = \Aimeos\Admin\Graphql::execute( $this->context, $request );

		$this->assertStringContainsString( '"code":"test@example.com"', (string) $response->getBody() );
	}


	public function testAggregateCustomers()
	{
		$body = '{"query":"query {\n  aggregateCustomers(key: [\"customer.status\"]) {\n    aggregates\n  }\n}\n","variables":{},"operationName":null}';
		$request = new \Nyholm\Psr7\ServerRequest( 'POST', 'localhost', [], $body );

		$response = \Aimeos\Admin\Graphql::execute( $this->context, $request );

		$this->assertStringContainsString( '{\"0\":1,\"1\":2}', (string) $response->getBody() );
	}


	public function testSaveCustomer()
	{
		$groupId = $this->group( 'unitgroup' );
		$input = 'code: "graphql-new", password: "secret", status: 0, groups: ["' . $groupId . '"], address: [{firstname: "Test"}]';

		$data = $this->save( $input );
		$item = $this->manager->get( $data['id'], ['customer/address', 'group'] );

		$this->assertEquals( 'graphql-new', $item->getCode() );
		$this->assertTrue( $this->context->password()->verify( 'secret', $this->password( $data['id'] ) ) );
		$this->assertEquals( 0, $item->getStatus() );
		$this->assertEquals( [$groupId], $item->getGroups() );
		$this->assertEquals( ['Test'], $item->getAddressItems()->getFirstname()->values()->all() );
		$this->assertEquals( [['firstname' => 'Test']], $data['address'] );
	}


	public function testSaveCustomerUpdate()
	{
		$groupId = $this->group( 'unitgroup' );
		$groupId2 = $this->group( 'unitgroup2' );
		$item = $this->customer( 'graphql-update', [$groupId] );

		$this->save( 'id: "' . $item->getId() . '", groups: ["' . $groupId . '", "' . $groupId2 . '"], address: {firstname: "Test"}' );
		$result = $this->manager->get( $item->getId(), ['customer/address', 'group'] );

		$this->assertEquals( [$groupId, $groupId2], $result->getGroups() );
		$this->assertEquals( ['Test'], $result->getAddressItems()->getFirstname()->values()->all() );
		$this->assertTrue( $this->context->password()->verify( 'old', $this->password( $item->getId() ) ) );
	}


	public function testSaveCustomerEditor()
	{
		$user = $this->customer( 'graphql-editor' );
		$item = $this->customer( 'graphql-other' );
		$this->editor( $user );

		$input = 'id: "' . $item->getId() . '", code: "graphql-changed", password: "secret", status: 0, groups: ["' . $this->group( 'unitgroup' ) . '"]';
		$this->save( $input );
		$result = $this->manager->get( $item->getId(), ['group'] );

		$this->assertEquals( 'graphql-other', $result->getCode() );
		$this->assertTrue( $this->context->password()->verify( 'old', $this->password( $item->getId() ) ) );
		$this->assertEquals( 1, $result->getStatus() );
		$this->assertEquals( [], $result->getGroups() );
	}


	public function testSaveCustomerEditorOwn()
	{
		$user = $this->customer( 'graphql-editor' );
		$this->editor( $user );

		$input = 'id: "' . $user->getId() . '", code: "graphql-changed", password: "secret", status: 0, groups: ["' . $this->group( 'unitgroup' ) . '"]';
		$this->save( $input );
		$result = $this->manager->get( $user->getId(), ['group'] );

		$this->assertEquals( 'graphql-changed', $result->getCode() );
		$this->assertTrue( $this->context->password()->verify( 'secret', $this->password( $user->getId() ) ) );
		$this->assertEquals( 1, $result->getStatus() );
		$this->assertEquals( [], $result->getGroups() );
	}


	public function testSaveCustomerEditorNestedRepoint()
	{
		$user = $this->customer( 'graphql-editor' );
		$victim = $this->customer( 'graphql-victim' );
		$this->editor( $user );

		// Create a self-referencing customer list row so the base item for the nested
		// write is the editor's own account (whose ID passes the ownership check)
		$this->save( 'id: "' . $user->getId() . '", lists: {customer: [{'
			. 'refid: "' . $user->getId() . '", type: "default", '
			. 'item: {id: "' . $user->getId() . '", label: "editor"}'
			. '}]}' );

		$listId = $this->manager->get( $user->getId(), ['customer'] )
			->getListItems( 'customer', 'default' )->firstKey();

		// Reuse that row but point the nested item at the victim with new credentials.
		// The ID re-point via "customer.id" must not let the credential write land there.
		$this->save( 'id: "' . $user->getId() . '", lists: {customer: [{'
			. 'id: "' . $listId . '", refid: "' . $user->getId() . '", type: "default", '
			. 'item: {id: "' . $victim->getId() . '", code: "victim-changed", email: "attacker@example.com", password: "pwned"}'
			. '}]}' );

		$result = $this->manager->get( $victim->getId() );

		$this->assertEquals( 'graphql-victim', $result->getCode() );
		$this->assertEquals( '', $result->getPaymentAddress()->getEmail() );
		$this->assertTrue( $this->context->password()->verify( 'old', $this->password( $victim->getId() ) ) );
	}


	public function testSearchCustomersPassword()
	{
		$body = json_encode( ['query' => 'query { searchCustomers(filter: "{}") { items { id password } } }'] );
		$request = new \Nyholm\Psr7\ServerRequest( 'POST', 'localhost', [], $body );

		$result = json_decode( (string) \Aimeos\Admin\Graphql::execute( $this->context, $request )->getBody(), true );

		$this->assertStringContainsString( 'Cannot query field "password"', $result['errors'][0]['message'] ?? '' );
		$this->assertArrayNotHasKey( 'data', $result );
	}


	public function testSearchCustomersPasswordFilter()
	{
		$filter = json_encode( ['=~' => ['customer.password' => '$2y$']] );
		$body = json_encode( ['query' => 'query { searchCustomers(filter: ' . json_encode( $filter ) . ') { items { id } } }'] );
		$request = new \Nyholm\Psr7\ServerRequest( 'POST', 'localhost', [], $body );

		$result = json_decode( (string) \Aimeos\Admin\Graphql::execute( $this->context, $request )->getBody(), true );

		$this->assertStringContainsString( 'Invalid name', $result['errors'][0]['extensions']['debugMessage'] ?? '' );
		$this->assertNull( $result['data']['searchCustomers'] );
	}


	protected function customer( string $code, array $groups = [] ) : \Aimeos\MShop\Customer\Item\Iface
	{
		$item = $this->manager->create()->setCode( $code )->setPassword( 'old' )->setStatus( 1 )->setGroups( $groups );
		$item = $this->manager->save( $item );

		$this->ids[] = $item->getId();
		return $item;
	}


	protected function editor( \Aimeos\MShop\Customer\Item\Iface $user ) : void
	{
		$view = \TestHelper::view( 'unittest', $this->context->config() );
		$view->addHelper( 'access', new \Aimeos\Base\View\Helper\Access\Standard( $view, ['editor'] ) );
		$this->context->setView( $view )->setUser( $user );
	}


	protected function group( string $code ) : string
	{
		return \Aimeos\MShop::create( $this->context, 'group' )->find( $code )->getId();
	}


	protected function save( string $input ) : array
	{
		$body = json_encode( ['query' => 'mutation { saveCustomer(input: {' . $input . '}) { id address { firstname } } }'] );
		$request = new \Nyholm\Psr7\ServerRequest( 'POST', 'localhost', [], $body );

		$result = json_decode( (string) \Aimeos\Admin\Graphql::execute( $this->context, $request )->getBody(), true );
		$this->assertArrayNotHasKey( 'errors', $result, json_encode( $result['errors'] ?? [] ) );

		$this->ids[] = $result['data']['saveCustomer']['id'];
		return $result['data']['saveCustomer'];
	}


	protected function password( string $id ) : string
	{
		$stmt = $this->context->db( 'db-customer' )->create( 'SELECT "password" FROM "mshop_customer" WHERE "id" = ?' );
		$row = $stmt->bind( 1, $id, \Aimeos\Base\DB\Statement\Base::PARAM_INT )->execute()->fetch();
		return (string) ( $row['password'] ?? '' );
	}
}
