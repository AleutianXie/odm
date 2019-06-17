<?php

/**
 * @license LGPLv3, http://opensource.org/licenses/LGPL-3.0
 * @copyright Aimeos (aimeos.org), 2015-2018
 */


namespace Aimeos\Admin\JQAdm\Product\Category;


class StandardTest extends \PHPUnit\Framework\TestCase
{
	private $context;
	private $object;
	private $view;


	protected function setUp()
	{
		$this->view = \TestHelperJqadm::getView();
		$this->context = \TestHelperJqadm::getContext();

		$this->object = new \Aimeos\Admin\JQAdm\Product\Category\Standard( $this->context );
		$this->object = new \Aimeos\Admin\JQAdm\Common\Decorator\Page( $this->object, $this->context );
		$this->object->setAimeos( \TestHelperJqadm::getAimeos() );
		$this->object->setView( $this->view );
	}


	protected function tearDown()
	{
		unset( $this->object, $this->view, $this->context );
	}


	public function testCreate()
	{
		$manager = \Aimeos\MShop::create( $this->context, 'product' );

		$this->view->item = $manager->createItem();
		$result = $this->object->create();

		$this->assertContains( 'item-category', $result );
		$this->assertNull( $this->view->get( 'errors' ) );
	}


	public function testCopy()
	{
		$manager = \Aimeos\MShop::create( $this->context, 'product' );

		$this->view->item = $manager->findItem( 'CNC' );
		$result = $this->object->copy();

		$this->assertNull( $this->view->get( 'errors' ) );
		$this->assertRegexp( '/&quot;catalog.label&quot;:\[.*&quot;Internet&quot;.*\]/', $result );
		$this->assertRegexp( '/&quot;catalog.label&quot;:\[.*&quot;Kaffee&quot;.*\]/', $result );
		$this->assertRegexp( '/&quot;catalog.label&quot;:\[.*&quot;Neu&quot;.*\]/', $result );
	}


	public function testDelete()
	{
		$result = $this->object->delete();

		$this->assertNull( $this->view->get( 'errors' ) );
		$this->assertNull( $result );
	}


	public function testGet()
	{
		$manager = \Aimeos\MShop::create( $this->context, 'product' );

		$this->view->item = $manager->findItem( 'CNC' );
		$result = $this->object->get();

		$this->assertNull( $this->view->get( 'errors' ) );
		$this->assertRegexp( '/&quot;catalog.label&quot;:\[.*&quot;Internet&quot;.*\]/', $result );
		$this->assertRegexp( '/&quot;catalog.label&quot;:\[.*&quot;Kaffee&quot;.*\]/', $result );
		$this->assertRegexp( '/&quot;catalog.label&quot;:\[.*&quot;Neu&quot;.*\]/', $result );
	}


	public function testSave()
	{
		$manager = \Aimeos\MShop::create( $this->context, 'catalog' );
		$productManager = \Aimeos\MShop::create( $this->context, 'product' );

		$item = $manager->findItem( 'root' );
		$item->setCode( 'jqadm-test-root' );
		$item->setId( null );

		$manager->insertItem( $item );


		$param = array(
			'site' => 'unittest',
			'category' => array(
				'catalog.lists.id' => array( '' ),
				'catalog.lists.type' => array( 'default' ),
				'catalog.id' => array( $item->getId() ),
			),
		);

		$helper = new \Aimeos\MW\View\Helper\Param\Standard( $this->view, $param );
		$this->view->addHelper( 'param', $helper );
		$this->view->item = $productManager->createItem();

		$result = $this->object->save();

		$item = $manager->getItem( $item->getId(), array( 'product' ) );
		$manager->deleteItem( $item->getId() );

		$this->assertNull( $this->view->get( 'errors' ) );
		$this->assertNull( $result );
		$this->assertEquals( 1, count( $item->getListItems( 'product' ) ) );
	}


	public function testSavePromotion()
	{
		$manager = \Aimeos\MShop::create( $this->context, 'catalog' );
		$productManager = \Aimeos\MShop::create( $this->context, 'product' );

		$item = $manager->findItem( 'root' );
		$item->setCode( 'jqadm-test-root' );
		$item->setId( null );

		$manager->insertItem( $item );


		$param = array(
			'category' => array(
				'catalog.lists.id' => array( '' ),
				'catalog.lists.type' => array( 'promotion' ),
				'catalog.id' => array( $item->getId() ),
			),
		);

		$helper = new \Aimeos\MW\View\Helper\Param\Standard( $this->view, $param );
		$this->view->addHelper( 'param', $helper );
		$this->view->item = $productManager->createItem();

		$result = $this->object->save();

		$item = $manager->getItem( $item->getId(), array( 'product' ) );
		$listItems = $item->getListItems( 'product' );
		$manager->deleteItem( $item->getId() );

		$this->assertNull( $this->view->get( 'errors' ) );
		$this->assertNull( $result );
		$this->assertEquals( 1, count( $listItems ) );
	}


	public function testSaveException()
	{
		$object = $this->getMockBuilder( \Aimeos\Admin\JQAdm\Product\Category\Standard::class )
			->setConstructorArgs( array( $this->context, \TestHelperJqadm::getTemplatePaths() ) )
			->setMethods( array( 'fromArray' ) )
			->getMock();

		$object->expects( $this->once() )->method( 'fromArray' )
			->will( $this->throwException( new \RuntimeException() ) );

		$this->view = \TestHelperJqadm::getView();
		$this->view->item = \Aimeos\MShop::create( $this->context, 'product' )->createItem();

		$object->setView( $this->view );

		$this->setExpectedException( \Aimeos\Admin\JQAdm\Exception::class );
		$object->save();
	}


	public function testSaveMShopException()
	{
		$object = $this->getMockBuilder( \Aimeos\Admin\JQAdm\Product\Category\Standard::class )
			->setConstructorArgs( array( $this->context, \TestHelperJqadm::getTemplatePaths() ) )
			->setMethods( array( 'fromArray' ) )
			->getMock();

		$object->expects( $this->once() )->method( 'fromArray' )
			->will( $this->throwException( new \Aimeos\MShop\Exception() ) );

		$this->view = \TestHelperJqadm::getView();
		$this->view->item = \Aimeos\MShop::create( $this->context, 'product' )->createItem();

		$object->setView( $this->view );

		$this->setExpectedException( \Aimeos\Admin\JQAdm\Exception::class );
		$object->save();
	}


	public function testSearch()
	{
		$this->assertNull( $this->object->search() );
	}


	public function testGetSubClient()
	{
		$this->setExpectedException( \Aimeos\Admin\JQAdm\Exception::class );
		$this->object->getSubClient( 'unknown' );
	}
}
