<?php

/**
 * @license LGPLv3, http://opensource.org/licenses/LGPL-3.0
 * @copyright Aimeos (aimeos.org), 2016-2018
 * @package Controller
 * @subpackage Frontend
 */


namespace Aimeos\Controller\Frontend\Basket\Decorator;


/**
 * Category check for basket controller
 *
 * @package Controller
 * @subpackage Frontend
 */
class Category
	extends Base
	implements \Aimeos\Controller\Frontend\Basket\Iface, \Aimeos\Controller\Frontend\Common\Decorator\Iface
{
	/**
	 * Adds a product to the basket of the customer stored in the session
	 *
	 * @param \Aimeos\MShop\Product\Item\Iface $product Product to add including texts, media, prices, attributes, etc.
	 * @param integer $quantity Amount of products that should by added
	 * @param array $variant List of variant-building attribute IDs that identify an article in a selection product
	 * @param array $config List of configurable attribute IDs the customer has chosen from
	 * @param array $custom Associative list of attribute IDs as keys and arbitrary values that will be added to the ordered product
	 * @param string $stocktype Unique code of the stock type to deliver the products from
	 * @param string|null $supplier Unique supplier code the product is from
	 * @return \Aimeos\Controller\Frontend\Basket\Iface Basket frontend object for fluent interface
	 * @throws \Aimeos\Controller\Frontend\Basket\Exception If the product isn't available
	 */
	public function addProduct( \Aimeos\MShop\Product\Item\Iface $product, $quantity = 1,
		array $variant = [], array $config = [], array $custom = [], $stocktype = 'default', $supplier = null )
	{
		$context = $this->getContext();
		$manager = \Aimeos\MShop::create( $context, 'catalog' );

		$expr = [];
		$search = $manager->createSearch( true )->setSlice( 0, 1 );

		$func = $search->createFunction( 'catalog:has', ['product', 'default', $product->getId()] );
		$expr[] = $search->compare( '!=', $func, null );
		$func = $search->createFunction( 'catalog:has', ['product', 'promotion', $product->getId()] );
		$expr[] = $search->compare( '!=', $func, null );

		$search->setConditions( $search->combine( '&&', [$search->getConditions(), $search->combine( '||', $expr )] ) );

		$result = $manager->searchItems( $search );

		if( reset( $result ) === false )
		{
			$msg = $context->getI18n()->dt( 'controller/frontend', 'Adding product with ID "%1$s" is not allowed' );
			throw new \Aimeos\Controller\Frontend\Basket\Exception( sprintf( $msg, $product->getId() ) );
		}

		$this->getController()->addProduct( $product, $quantity, $variant, $config, $custom, $stocktype, $supplier );

		return $this;
	}
}
