<?php

/**
 * @license LGPLv3, http://opensource.org/licenses/LGPL-3.0
 * @copyright Metaways Infosystems GmbH, 2014
 * @copyright Aimeos (aimeos.org), 2015-2018
 * @package Controller
 * @subpackage Frontend
 */


namespace Aimeos\Controller\Frontend\Order;


/**
 * Default implementation of the order frontend controller.
 *
 * @package Controller
 * @subpackage Frontend
 */
class Standard
	extends \Aimeos\Controller\Frontend\Base
	implements Iface, \Aimeos\Controller\Frontend\Common\Iface
{
	private $conditions = [];
	private $manager;
	private $filter;
	private $item;


	/**
	 * Initializes the controller
	 *
	 * @param \Aimeos\MShop\Context\Item\Iface $context Common MShop context object
	 */
	public function __construct( \Aimeos\MShop\Context\Item\Iface $context )
	{
		parent::__construct( $context );

		$this->manager = \Aimeos\MShop::create( $context, 'order' );
		$this->item = $this->manager->createItem();

		$this->filter = $this->manager->createSearch( true );
		$this->conditions[] = $this->filter->compare( '==', 'order.base.customerid', $context->getUserId() );
		$this->conditions[] = $this->filter->getConditions();
	}


	/**
	 * Clones objects in controller and resets values
	 */
	public function __clone()
	{
		$this->item = clone $this->item;
	}


	/**
	 * Adds the values to the order object (not yet stored)
	 *
	 * @param string $baseId ID of the stored basket
	 * @param array $values Values added to the order item (new or existing) like "order.type"
	 * @return \Aimeos\Controller\Frontend\Order\Iface Order controller for fluent interface
	 * @since 2019.04
	 */
	public function add( $baseId, array $values = [] )
	{
		$this->item = $this->item->fromArray( $values )->setBaseId( $baseId );
		return $this;
	}


	/**
	 * Adds generic condition for filtering orders
	 *
	 * @param string $operator Comparison operator, e.g. "==", "!=", "<", "<=", ">=", ">", "=~", "~="
	 * @param string $key Search key defined by the order manager, e.g. "order.type"
	 * @param array|string $value Value or list of values to compare to
	 * @return \Aimeos\Controller\Frontend\Order\Iface Order controller for fluent interface
	 * @since 2019.04
	 */
	public function compare( $operator, $key, $value )
	{
		$this->conditions[] = $this->filter->compare( $operator, $key, $value );
		return $this;
	}


	/**
	 * Returns the order for the given order ID
	 *
	 * @param string $id Unique order ID
	 * @param boolean $default Use default criteria to limit orders
	 * @return \Aimeos\MShop\Order\Item\Iface Order item object
	 * @since 2019.04
	 */
	public function get( $id, $default = true )
	{
		return $this->manager->getItem( $id, [], $default );
	}


	/**
	 * Parses the given array and adds the conditions to the list of conditions
	 *
	 * @param array $conditions List of conditions, e.g. ['&&' => [['>' => ['order.statuspayment' => 0]], ['==' => ['order.type' => 'web']]]]
	 * @return \Aimeos\Controller\Frontend\Order\Iface Order controller for fluent interface
	 * @since 2019.04
	 */
	public function parse( array $conditions )
	{
		$this->conditions[] = $this->filter->toConditions( $conditions );
		return $this;
	}


	/**
	 * Updates the given order item in the storage
	 *
	 * @param \Aimeos\MShop\Order\Item\Iface $orderItem Order item object
	 * @return \Aimeos\MShop\Order\Item\Iface $orderItem Saved order item object
	 * @since 2019.04
	 */
	public function save( \Aimeos\MShop\Order\Item\Iface $orderItem )
	{
		return $this->manager->saveItem( $orderItem );
	}

	/**
	 * Returns the orders filtered by the previously assigned conditions
	 *
	 * @param string[] $domains Domain names of items that are associated with the orders and that should be fetched too
	 * @return \Aimeos\MShop\Order\Item\Iface[] Ordered list of order items
	 * @since 2019.04
	 */
	public function search( &$total = null )
	{
		$this->filter->setConditions( $this->filter->combine( '&&', $this->conditions ) );
		return $this->manager->searchItems( $this->filter, [], $total );
	}


	/**
	 * Sets the start value and the number of returned orders for slicing the list of found orders
	 *
	 * @param integer $start Start value of the first order in the list
	 * @param integer $limit Number of returned orders
	 * @return \Aimeos\Controller\Frontend\Order\Iface Order controller for fluent interface
	 * @since 2019.04
	 */
	public function slice( $start, $limit )
	{
		$this->filter->setSlice( $start, $limit );
		return $this;
	}


	/**
	 * Sets the sorting of the result list
	 *
	 * @param string|null $key Sorting of the result list like "-order.id", null for no sorting
	 * @return \Aimeos\Controller\Frontend\Order\Iface Order controller for fluent interface
	 * @since 2019.04
	 */
	public function sort( $key = null )
	{
		$sort = [];
		$list = ( $key ? explode( ',', $key ) : [] );

		foreach( $list as $sortkey )
		{
			$direction = ( $sortkey[0] === '-' ? '-' : '+' );
			$sort[] = $this->filter->sort( $direction, ltrim( $sortkey, '+-' ) );
		}

		$this->filter->setSortations( $sort );
		return $this;
	}


	/**
	 * Saves the modified order item in the storage and blocks the stock and coupon codes
	 *
	 * @return \Aimeos\MShop\Order\Item\Iface New or updated order item object
	 * @since 2019.04
	 */
	public function store()
	{
		$this->checkLimit( $this->item->getBaseId() );

		$cntl = \Aimeos\Controller\Common\Order\Factory::create( $this->getContext() );
		$this->item = $this->manager->saveItem( $this->item );

		return $cntl->block( $this->item );
	}


	/**
	 * Checks if more orders than allowed have been created by the user
	 *
	 * @param string $baseId Unique ID of the order base item (basket)
	 * @return \Aimeos\Controller\Frontend\Order\Iface Order controller for fluent interface
	 * @throws \Aimeos\Controller\Frontend\Order\Exception If limit is exceeded
	 */
	protected function checkLimit( $baseId )
	{
		/** controller/frontend/order/limit-seconds
		 * Order limitation time frame in seconds
		 *
		 * Creating new orders is limited to avoid abuse and mitigate denial of
		 * service attacks. Within the configured time frame, only one order
		 * item can be created per order base item. All orders for the order
		 * base item within the last X seconds are counted.  If there's already
		 * one available, an error message will be shown to the user instead of
		 * creating the new order item.
		 *
		 * @param integer Number of seconds to check order items within
		 * @since 2017.05
		 * @category Developer
		 * @see controller/frontend/basket/limit-count
		 * @see controller/frontend/basket/limit-seconds
		 */
		$seconds = $this->getContext()->getConfig()->get( 'controller/frontend/order/limit-seconds', 300 );

		$search = $this->manager->createSearch()->setSlice( 0, 0 );
		$search->setConditions( $search->combine( '&&', [
			$search->compare( '==', 'order.baseid', $baseId ),
			$search->compare( '>=', 'order.ctime', date( 'Y-m-d H:i:s', time() - $seconds ) ),
		] ) );

		$total = 0;
		$this->manager->searchItems( $search, [], $total );

		if( $total > 0 ) {
			throw new \Aimeos\Controller\Frontend\Order\Exception( sprintf( 'The order has already been created' ) );
		}

		return $this;
	}
}
