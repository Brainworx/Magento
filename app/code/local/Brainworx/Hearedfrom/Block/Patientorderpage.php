<?php

class Brainworx_Hearedfrom_Block_Patientorderpage extends Mage_Customer_Block_Account_Dashboard  
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
	
		$pager = $this->getLayout()->createBlock('page/html_pager', 'patientorderpage.pager')
		->setCollection($this->getOrders()); //call your own collection getter here, name it something better than getCollection, please; *or* your call to getResourceModel()
		$pager->setAvailableLimit(array(10=>10,20=>20));
		$this->setChild('pager', $pager);		
		
		return $this;
	}
	public function getPagerHtml()
	{
		return $this->getChildHtml('pager');
	}
		 
// 	function getOrders(){		
// 		/*query
// 		 * */
// 		$customer = Mage::getSingleton('customer/session')->getCustomer();
// 		if(!empty($customer)){
// 			$salesforce = Mage::getModel('hearedfrom/salesForce')->loadByCustid($customer->getEntityId());
// 			if(!empty($salesforce)){
		
// 				$collection = Mage::getModel('sales/order')->getCollection();
					
// 				// add joined data to the collection
					
// 				$select = $collection->getSelect();
// 		    	$resource = Mage::getSingleton('core/resource');
		    	
// 		    	$select->join(array('seller' => $resource->getTableName('hearedfrom/salesSeller')),
// 		    			'main_table.increment_id = seller.order_id',
// 		    			array('user_id'));
		    	
// 		    	$collection->addFieldToFilter('user_id',
// 		    			Mage::getModel('hearedfrom/salesForce')->loadByCustid($customer->getEntityId())['entity_id']);
// 		    	//fiter out cancelled orders
// 		    	$collection->addFieldToFilter('status', array('nlike' => 'canceled'));
		    	 
// 		    	$collection->setOrder('increment_id');
// 			}else{
    	
// 		    	//normal customer
// 		    	$collection = Mage::getResourceModel('sales/order_collection')
// 		    	->addFieldToSelect('*')
// 		    	->addFieldToFilter('customer_id', Mage::getSingleton('customer/session')->getCustomer()->getId())
// 		    	->addFieldToFilter('state', array('in' => Mage::getSingleton('sales/order_config')->getVisibleOnFrontStates()))
// 		    	->setOrder('created_at', 'desc')
// 		    	;
// 			}
// 		}
// 		return $collection;
// 	}
	public function getViewOrderUrl($order)
	{
		return $this->getUrl('sales/order/view', array('order_id' => $order->getId()));
	}
	
}