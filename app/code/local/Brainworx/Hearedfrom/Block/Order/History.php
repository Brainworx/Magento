<?php
class Brainworx_Hearedfrom_Block_Order_History extends Mage_Sales_Block_Order_History
{
	public function __construct()
	{
		parent::__construct();
		$this->setTemplate('sales/order/history.phtml');
	
		//Add filter on caceled accounts
		
		$orders = Mage::getResourceModel('sales/order_collection')
		->addFieldToSelect('*')
		->addFieldToFilter('customer_id', Mage::getSingleton('customer/session')->getCustomer()->getId())
		->addFieldToFilter('state', array('in' => Mage::getSingleton('sales/order_config')->getVisibleOnFrontStates()))
		->addFieldToFilter('status', array('nlike' => 'canceled')) 
		->setOrder('created_at', 'desc')
		;
	
		$this->setOrders($orders);
	
		Mage::app()->getFrontController()->getAction()->getLayout()->getBlock('root')->setHeaderTitle(Mage::helper('sales')->__('My Orders'));
	}
}