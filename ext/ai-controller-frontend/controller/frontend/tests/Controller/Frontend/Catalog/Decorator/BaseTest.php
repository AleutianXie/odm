<?php

/**
 * @license LGPLv3, http://opensource.org/licenses/LGPL-3.0
 * @copyright Aimeos (aimeos.org), 2017-2018
 */


namespace Aimeos\Controller\Frontend\Catalog\Decorator;


class BaseTest extends \PHPUnit\Framework\TestCase
{
	private $context;
	private $object;
	private $stub;


	protected function setUp()
	{
		$this->context = \TestHelperFrontend::getContext();

		$this->stub = $this->getMockBuilder( \Aimeos\Controller\Frontend\Catalog\Standard::class )
			->disableOriginalConstructor()
			->getMock();

		$this->object = $this->getMockBuilder( \Aimeos\Controller\Frontend\Catalog\Decorator\Base::class )
			->setConstructorArgs( [$this->stub, $this->context] )
			->getMockForAbstractClass();
	}


	protected function tearDown()
	{
		unset( $this->context, $this->object, $this->stub );
	}


	public function testConstructException()
	{
		$stub = $this->getMockBuilder( \Aimeos\Controller\Frontend\Iface::class )->getMock();

		$this->setExpectedException( \Aimeos\MW\Common\Exception::class );

		$this->getMockBuilder( \Aimeos\Controller\Frontend\Catalog\Decorator\Base::class )
			->setConstructorArgs( [$stub, $this->context] )
			->getMockForAbstractClass();
	}


	public function testCall()
	{
		$stub = $this->getMockBuilder( \Aimeos\Controller\Frontend\Catalog\Standard::class )
			->disableOriginalConstructor()
			->setMethods( ['invalid'] )
			->getMock();

		$object = $this->getMockBuilder( \Aimeos\Controller\Frontend\Catalog\Decorator\Base::class )
			->setConstructorArgs( [$stub, $this->context] )
			->getMockForAbstractClass();

		$stub->expects( $this->once() )->method( 'invalid' )->will( $this->returnValue( true ) );

		$this->assertTrue( $object->invalid() );
	}


	public function testCompare()
	{
		$this->assertSame( $this->object, $this->object->compare( '==', 'catalog.code', 'test' ) );
	}


	public function testFind()
	{
		$item = \Aimeos\MShop::create( $this->context, 'catalog' )->createItem();
		$expected = \Aimeos\MShop\Catalog\Item\Iface::class;

		$this->stub->expects( $this->once() )->method( 'find' )
			->will( $this->returnValue( $item ) );

		$this->assertInstanceOf( $expected, $this->object->find( 'test', ['text'] ) );
	}


	public function testGet()
	{
		$item = \Aimeos\MShop::create( $this->context, 'catalog' )->createItem();
		$expected = \Aimeos\MShop\Catalog\Item\Iface::class;

		$this->stub->expects( $this->once() )->method( 'get' )
			->will( $this->returnValue( $item ) );

		$this->assertInstanceOf( $expected, $this->object->get( 1, ['text'] ) );
	}


	public function testParse()
	{
		$this->assertSame( $this->object, $this->object->parse( [] ) );
	}


	public function testGetPath()
	{
		$this->stub->expects( $this->once() )->method( 'getPath' )
			->will( $this->returnValue( [] ) );

		$this->assertEquals( [], $this->object->getPath( -1 ) );
	}


	public function testGetTree()
	{
		$catItem = \Aimeos\MShop::create( $this->context, 'catalog' )->createItem();

		$this->stub->expects( $this->once() )->method( 'getTree' )
			->will( $this->returnValue( $catItem ) );

		$this->assertInstanceOf( \Aimeos\MShop\Catalog\Item\Iface::class, $this->object->getTree() );
	}


	public function testRoot()
	{
		$this->assertSame( $this->object, $this->object->root( -1 ) );
	}


	public function testUses()
	{
		$this->assertSame( $this->object, $this->object->uses( ['text'] ) );
	}


	public function testVisible()
	{
		$this->assertSame( $this->object, $this->object->visible( [1, 2] ) );
	}


	public function testGetController()
	{
		$result = $this->access( 'getController' )->invokeArgs( $this->object, [] );

		$this->assertSame( $this->stub, $result );
	}


	protected function access( $name )
	{
		$class = new \ReflectionClass( \Aimeos\Controller\Frontend\Catalog\Decorator\Base::class );
		$method = $class->getMethod( $name );
		$method->setAccessible( true );

		return $method;
	}
}
