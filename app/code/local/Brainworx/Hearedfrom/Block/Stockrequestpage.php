<?php

class Brainworx_Hearedfrom_Block_Stockrequestpage extends Mage_Customer_Block_Account_Dashboard  
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
	
		$pager = $this->getLayout()->createBlock('page/html_pager', 'stockrequestpage.pager')
		->setCollection($this->getStockRequests()); //call your own collection getter here, name it something better than getCollection, please; *or* your call to getResourceModel()
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
		 
	function getStockRequests(){
		
		$customer = Mage::getSingleton('customer/session')->getCustomer();
		$salesforce = Mage::getModel('hearedfrom/salesForce')->loadByCustid($customer->getEntityId());
		
		$collection = Mage::getModel('hearedfrom/salesForceStockRequest')->getCollection();		
		$collection->addFieldToFilter('force_id', $salesforce['entity_id']);
		
		$collection->setOrder('entity_id','DESC');
		
		$this->setCollection($collection);
		return $collection;
	}
	
	function getMainurl(){
		return $this->getUrl('customer/stockrequestpage/');
	}
	function getRequesturl(){
		return $this->getUrl('customer/stockrequestpage/request');
	}
	
	function getAction(){
		return $this->getUrl('customer/stockrequestpage/formPost');
	}
	function getRequestsurl(){
		return $this->getUrl('customer/stockrequestpage/');
	}
	/**
	 * returs an array with product items [ sku - name ] available for stock request
	 * @return multitype:NULL multitype:string NULL  Ambigous <string, string, multitype:>
	 */
	function getStockItems(){
		$catstock = Mage::getModel('core/variable')->setStoreId(Mage::app()->getStore()->getId())->loadByCode('CAT_RENT')->getValue('text');
		/**
		 * If you want to display products from any specific category
		 */
		$categoryId = Mage::getModel('core/variable')->setStoreId(Mage::app()->getStore()->getId())->loadByCode('CAT_CONSIG')->getValue('text');
		$category = Mage::getModel('catalog/category')->load($categoryId);
		
		/**
		 * Getting product collection for a particular category
		*/
		$prodCollection = Mage::getResourceModel('catalog/product_collection')
		->addCategoryFilter($category)
		->addAttributeToSelect('*');
		
		/**
		 * Applying status and visibility filter to the product collection
		 * i.e. only fetching visible and enabled products
		*/
		Mage::getSingleton('catalog/product_status')
		->addVisibleFilterToCollection($prodCollection);
		
		$options[0] = Mage::helper('hearedfrom')->__('Select');
		$type = "";
		foreach ($prodCollection as $val) {
			$type = (in_array($catstock, $val->getCategoryIds()))?
				Mage::helper('hearedfrom')->__('Verhuur'): Mage::helper('hearedfrom')->__('Verkoop');
			$options[$val->getSku()]= $type.' - '.$val->getName();//.' ('.$val->getSku().')';
		}
		
		
		return $options;
	}
	
}