<?php
class Brainworx_Hearedfrom_Block_Order_Recent extends Mage_Sales_Block_Order_Recent
{

	public function __construct()
	{
		parent::__construct();

		//TODO: add full name logic
		$orders = Mage::getResourceModel('sales/order_collection')
		->addAttributeToSelect('*')
		->joinAttribute('shipping_firstname', 'order_address/firstname', 'shipping_address_id', null, 'left')
		->joinAttribute('shipping_lastname', 'order_address/lastname', 'shipping_address_id', null, 'left')
		->addAttributeToFilter('customer_id', Mage::getSingleton('customer/session')->getCustomer()->getId())
		->addAttributeToFilter('state', array('in' => Mage::getSingleton('sales/order_config')->getVisibleOnFrontStates()))
		->addAttributeToFilter('status', array('nlike' => 'canceled'))
		->addAttributeToSort('created_at', 'desc')
		->setPageSize('5')
		->load()
		;

		$this->setOrders($orders);
	}
	
}