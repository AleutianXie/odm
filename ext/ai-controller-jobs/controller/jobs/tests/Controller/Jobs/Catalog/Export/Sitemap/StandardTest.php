<?php

/**
 * @license LGPLv3, http://opensource.org/licenses/LGPL-3.0
 * @copyright Aimeos (aimeos.org), 2015-2019
 */


namespace Aimeos\Controller\Jobs\Catalog\Export\Sitemap;


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

		$this->object = new \Aimeos\Controller\Jobs\Catalog\Export\Sitemap\Standard( $this->context, $this->aimeos );
	}


	protected function tearDown()
	{
		\Aimeos\MShop::cache( false );
		$this->object = null;
	}


	public function testGetName()
	{
		$this->assertEquals( 'Catalog site map', $this->object->getName() );
	}


	public function testGetDescription()
	{
		$text = 'Creates a catalog site map for search engines';
		$this->assertEquals( $text, $this->object->getDescription() );
	}


	public function testRun()
	{
		$this->context->getConfig()->set( 'controller/jobs/catalog/export/sitemap/max-items', 5 );
		$this->context->getConfig()->set( 'controller/jobs/catalog/export/sitemap/baseurl', 'https://www.yourshop.com/sitemaps/' );

		$this->object->run();

		$ds = DIRECTORY_SEPARATOR;
		$this->assertFileExists( 'tmp' . $ds . 'aimeos-catalog-sitemap-1.xml.gz' );
		$this->assertFileExists( 'tmp' . $ds . 'aimeos-catalog-sitemap-2.xml.gz' );
		$this->assertFileExists( 'tmp' . $ds . 'aimeos-catalog-sitemap-index.xml.gz' );

		$file1 = gzread( gzopen( 'tmp' . $ds . 'aimeos-catalog-sitemap-1.xml.gz', 'rb' ), 0x1000 );
		$file2 = gzread( gzopen( 'tmp' . $ds . 'aimeos-catalog-sitemap-2.xml.gz', 'rb' ), 0x1000 );
		$index = gzread( gzopen( 'tmp' . $ds . 'aimeos-catalog-sitemap-index.xml.gz', 'rb' ), 0x1000 );

		unlink( 'tmp' . $ds . 'aimeos-catalog-sitemap-1.xml.gz' );
		unlink( 'tmp' . $ds . 'aimeos-catalog-sitemap-2.xml.gz' );
		unlink( 'tmp' . $ds . 'aimeos-catalog-sitemap-index.xml.gz' );

		$this->assertContains( 'Kaffee', $file1 );
		$this->assertContains( 'Misc', $file2 );

		$this->assertContains( 'https://www.yourshop.com/sitemaps/aimeos-catalog-sitemap-1.xml.gz', $index );
		$this->assertContains( 'https://www.yourshop.com/sitemaps/aimeos-catalog-sitemap-2.xml.gz', $index );
	}

	public function testRunEmptyLocation()
	{
		$this->context->getConfig()->set( 'controller/jobs/catalog/export/sitemap/location', '' );

		$this->setExpectedException('\\Aimeos\\Controller\\Jobs\\Exception');

		$this->object->run();
	}

	public function testRunNoLocation()
	{
		$this->context->getConfig()->set( 'controller/jobs/catalog/export/sitemap/location', null );

		$this->setExpectedException('\\Aimeos\\Controller\\Jobs\\Exception');

		$this->object->run();
	}
}
