<?php

/**
 * @license LGPLv3, http://opensource.org/licenses/LGPL-3.0
 * @copyright Aimeos (aimeos.org), 2015-2018
 */


namespace Aimeos\Controller\Jobs\Catalog\Import\Xml;


class StandardTest extends \PHPUnit\Framework\TestCase
{
	private $object;
	private $context;
	private $aimeos;


	protected function setUp()
	{
		\Aimeos\MShop::cache( true );

		$this->context = \TestHelperJobs::getContext();
		$this->aimeos = \TestHelperJobs::getAimeos();

		$config = $this->context->getConfig();
		$config->set( 'controller/jobs/catalog/import/xml/location', __DIR__ . '/_testfiles' );

		$this->object = new \Aimeos\Controller\Jobs\Catalog\Import\Xml\Standard( $this->context, $this->aimeos );
	}


	protected function tearDown()
	{
		\Aimeos\MShop::cache( false );
		unset( $this->object, $this->context, $this->aimeos );
	}


	public function testGetName()
	{
		$this->assertEquals( 'Catalog import XML', $this->object->getName() );
	}


	public function testGetDescription()
	{
		$text = 'Imports new and updates existing categories from XML files';
		$this->assertEquals( $text, $this->object->getDescription() );
	}


	public function testRun()
	{
		$this->object->run();

		$manager = \Aimeos\MShop::create( $this->context, 'catalog' );
		$tree = $manager->getTree( $manager->findItem( 'unittest-xml' )->getId(), ['media', 'product', 'text'] );
		$manager->deleteItem( $tree->getId() );

		$this->assertEquals( 'Test catalog', $tree->getLabel() );
		$this->assertEquals( 2, count( $tree->getChildren() ) );
		$this->assertEquals( 1, count( $tree->getRefItems( 'text', null, null, false ) ) );
		$this->assertEquals( 'Test sub-category 3', $tree->getChild( 0 )->getLabel() );
		$this->assertEquals( 2, count( $tree->getChild( 0 )->getRefItems( 'product' ) ) );
		$this->assertEquals( 'Test sub-category 3-1', $tree->getChild( 0 )->getChild( 0 )->getLabel() );
	}
}
