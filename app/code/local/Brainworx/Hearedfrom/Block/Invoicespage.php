<?php

class Brainworx_Hearedfrom_Block_Invoicespage extends Mage_Customer_Block_Account_Dashboard  
{
	
	protected function _prepareLayout()
	{
		parent::_prepareLayout();
	
		$pager = $this->getLayout()->createBlock('page/html_pager', 'invoicespage.pager')
		->setCollection($this->getInvoices()); //call your own collection getter here, name it something better than getCollection, please; *or* your call to getResourceModel()
		$pager->setAvailableLimit(array(10=>10,20=>20));
		$this->setChild('pager', $pager);
		
		return $this;
	}
	public function getPagerHtml()
	{
		return $this->getChildHtml('pager');
	}
		 
	function getInvoices(){
		
		$collection = Mage::getModel("sales/order_invoice")->getCollection();
		$select = $collection->getSelect();
    	$resource = Mage::getSingleton('core/resource');
    	
    	$select->joinLeft(
    			array('order' => Mage::getModel('core/resource')->getTableName('sales/order')), 
    			'order.entity_id=main_table.order_id', 
    			array('customer_id','order_i_id'=>'increment_id'));
    	
    	$customer = Mage::getSingleton('customer/session')->getCustomer();
    	$collection->addFieldToFilter('customer_id',$customer->getEntityId());
    	

    	$collection->setOrder('increment_id');
    	
    	$this->setCollection($collection);
		return $collection;
	}
	public function getViewInvoiceUrl($id)
	{
		return $this->getUrl('sales/order/invoice', array('order_id' => $id));
	}
	public function getViewOrderUrl($id)
	{
		return $this->getUrl('sales/order/view', array('order_id' => $id));
	}
	public function formatPrice($price, $addBrackets = false)
	{
		return $this->formatPricePrecision($price, 2, $addBrackets);
	}
	
	public function formatPricePrecision($price, $precision, $addBrackets = false)
	{
		$currency = Mage::getModel('directory/currency')->load(Mage::app()->getStore()->getCurrentCurrencyCode());
		return $currency->formatPrecision($price, $precision, array(), true, $addBrackets);
	}
	public function getStateLabel($state){
		$states = array(
				'1' => Mage::helper('core')->__('Open'),
				'2' => Mage::helper('core')->__('Paid'),
				'3' => Mage::helper('core')->__('Canceled'));
		return $states[$state];
	}
}