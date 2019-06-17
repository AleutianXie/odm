<?php

/**
 * @license LGPLv3, http://opensource.org/licenses/LGPL-3.0
 * @copyright Aimeos (aimeos.org), 2018
 */


namespace Aimeos\Controller\Frontend\Supplier;


class StandardTest extends \PHPUnit\Framework\TestCase
{
	private $object;
	private $context;


	protected function setUp()
	{
		$this->context = \TestHelperFrontend::getContext();
		$this->object = new \Aimeos\Controller\Frontend\Supplier\Standard( $this->context );
	}


	protected function tearDown()
	{
		unset( $this->object, $this->context );
	}


	public function testCompare()
	{
		$this->assertEquals( 2, count( $this->object->compare( '=~', 'supplier.label', 'unit' )->search() ) );
	}


	public function testFind()
	{
		$item = $this->object->uses( ['product'] )->find( 'unitCode001' );

		$this->assertInstanceOf( \Aimeos\MShop\Supplier\Item\Iface::class, $item );
		$this->assertEquals( 2, count( $item->getRefItems( 'product' ) ) );
	}


	public function testGet()
	{
		$item = \Aimeos\MShop::create( $this->context, 'supplier' )->findItem( 'unitCode001' );
		$item = $this->object->uses( ['product'] )->get( $item->getId() );

		$this->assertInstanceOf( \Aimeos\MShop\Supplier\Item\Iface::class, $item );
		$this->assertEquals( 2, count( $item->getRefItems( 'product' ) ) );
	}


	public function testParse()
	{
		$cond = ['&&' => [['==' => ['supplier.status' => 1]], ['=~' => ['supplier.label' => 'unit']]]];
		$this->assertEquals( 2, count( $this->object->parse( $cond )->search() ) );
	}


	public function testSearch()
	{
		$total = 0;
		$items = $this->object->uses( ['product'] )->compare( '=~', 'supplier.code', 'unit' )
			->sort( 'supplier.code' )->search( $total );

		$this->assertGreaterThanOrEqual( 2, count( $items ) );
		$this->assertGreaterThanOrEqual( 2, $total );
		$this->assertEquals( 2, count( current( $items )->getRefItems( 'product' ) ) );
	}


	public function testSlice()
	{
		$this->assertEquals( 1, count( $this->object->slice( 0, 1 )->search() ) );
	}


	public function testSort()
	{
		$this->assertGreaterThanOrEqual( 2, count( $this->object->sort()->search() ) );
	}


	public function testSortGeneric()
	{
		$this->assertGreaterThanOrEqual( 2, count( $this->object->sort( 'supplier.label' )->search() ) );
	}


	public function testSortMultiple()
	{
		$this->assertGreaterThanOrEqual( 2, count( $this->object->sort( 'supplier.label,-supplier.id' )->search() ) );
	}


	public function testUses()
	{
		$this->assertSame( $this->object, $this->object->uses( ['text'] ) );
	}
}
