<?php

class Brainworx_Hearedfrom_Block_Stockpage extends Mage_Customer_Block_Account_Dashboard  
{
	/**For collections with group by statements*/
	public function getSelectCountSql()
	{
		$countSelect = parent::getSelectCountSql();
		$countSelect->reset(Zend_Db_Select::GROUP);
		return $countSelect;
	}
	protected function _prepareLayout()
	{
		parent::_prepareLayout();
	
		$pager = $this->getLayout()->createBlock('page/html_pager', 'stockpage.pager')
		->setCollection($this->getStock()); //call your own collection getter here, name it something better than getCollection, please; *or* your call to getResourceModel()
		$pager->setAvailableLimit(array(10=>10,20=>20));
		$this->setChild('mainpager', $pager);
		
		return $this;
	}
	public function getPagerHtml()
	{
		return $this->getChildHtml('pager');
	}
	public function getMainPagerHtml()
	{
		return $this->getChildHtml('mainpager');
	}
		 
	function getStock(){
		
		$customer = Mage::getSingleton('customer/session')->getCustomer();
		$salesforce = Mage::getModel('hearedfrom/salesForce')->loadByCustid($customer->getEntityId());
		
		$collection = Mage::getModel('hearedfrom/salesForceStock')->getCollection();		
		$collection->addFieldToFilter('force_id', $salesforce['entity_id']);
		$collection->addFieldToFilter('enabled', 1);
			
		// add joined data to the collection
			
		//$select = $collection->getSelect();
		//$resource = Mage::getSingleton('core/resource');

		//$collection->setOrder('increment_id');
		
		$this->setCollection($collection);
		return $collection;
	}
	
	function getMainurl(){
		return $this->getUrl('customer/ristornopage/');
	}
	function getRequesturl(){
		return $this->getUrl('customer/stockrequestpage/request');
	}
	function getRequestsurl(){
		return $this->getUrl('customer/stockrequestpage/');
	}
}