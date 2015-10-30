<?php

class Brainworx_Depot_Block_Deliveriespage extends Mage_Customer_Block_Account_Dashboard  
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
	
		$pager = $this->getLayout()->createBlock('page/html_pager', 'deliveriespage.pager')
		->setCollection($this->getDeliveries()); //call your own collection getter here, name it something better than getCollection, please; *or* your call to getResourceModel()
		$pager->setAvailableLimit(array(10=>10,20=>20));
		$this->setChild('pager', $pager);
		
		return $this;
	}
	public function getPagerHtml()
	{
		return $this->getChildHtml('pager');
	}
		 
	function getDeliveries(){
		
		/*query:
		 SELECT `main_table`.*, `shipment`.`created_at`, `shipment`.`entity_id` AS `shipment_id`,
		 `shipment`.`shipping_name`, `shipment`.`order_increment_id`, `shipment`.`order_created_at`,
		  `shipment`.`increment_id`, `order`.`shipping_address_id`,`order`.`comment_to_zorgpunt`, `seller`.`user_id`,
		   CONCAT(street," ", postcode," ",city, " ",telephone) AS `address` 
		   FROM `sales_flat_shipment_track` AS `main_table` 
		   INNER JOIN `sales_flat_shipment_grid` AS `shipment` ON main_table.parent_id = shipment.entity_id 
		   INNER JOIN `sales_flat_order` AS `order` ON shipment.order_id = order.entity_id 
		   INNER JOIN `hearedfrom_salesseller` AS `seller` ON order.increment_id = seller.order_id 
		   INNER JOIN `sales_flat_order_address` AS `address` ON order.shipping_address_id = address.entity_id 
		   WHERE (user_id = '4')
		   */
		
		$collection = Mage::getModel('sales/order_shipment_track')->getCollection();
    	$select = $collection->getSelect();
    	$resource = Mage::getSingleton('core/resource');
    	
    	$select->join(
    			array('shipment' => $resource->getTableName('sales/shipment_grid')),
    			'main_table.parent_id = shipment.entity_id',
    			array('created_at','shipment_id' => 'entity_id','shipping_name','order_increment_id','order_created_at','increment_id')
    	);
    	$select->join(array('order' => $resource->getTableName('sales/order')),
    			'shipment.order_id = order.entity_id',
    			array('shipping_address_id','order_id'=>'entity_id','status'));
    	$select->join(array('seller' => $resource->getTableName('hearedfrom/salesSeller')),
    			'order.increment_id = seller.order_id',
    			array('user_id'));
    	$select->join(array('address' => $resource->getTableName('sales/order_address')),
    			'order.shipping_address_id = address.entity_id',
    			array('address' => 'CONCAT(street," ", postcode," ",city, " ",telephone)'));
    	
    	$customer = Mage::getSingleton('customer/session')->getCustomer();
    	$seller= Mage::getModel('hearedfrom/salesForce')->loadByCustid($customer->getEntityId());
    	$id;
    	if(empty($seller)){
    		$id = $customer->getEntityId();
    	}else{
    		$id=$seller['entity_id'];
    	}
    	$collection->addFieldToFilter('user_id',$id);
    	$collection->addFieldToFilter('order.status',array('neq'=>'canceled'));
    	

    	$collection->setOrder('order_created_at');
    	
    	$this->setCollection($collection);
		return $collection;
	}
	public function getViewOrderUrl($id)
	{
		return $this->getUrl('sales/order/view', array('order_id' => $id));
	}
}