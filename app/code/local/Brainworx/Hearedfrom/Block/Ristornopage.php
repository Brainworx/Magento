<?php

class Brainworx_Hearedfrom_Block_Ristornopage extends Mage_Customer_Block_Account_Dashboard  
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
	
		//TODO check: met deze pager is nog een probleem door de group by neemt hij voor elke record een page - tijdelijk niet gebruikt in template 
		$pager = $this->getLayout()->createBlock('page/html_pager', 'ristornopage.pager')
		->setCollection($this->getMonthlyRistornos()); //call your own collection getter here, name it something better than getCollection, please; *or* your call to getResourceModel()
		$pager->setAvailableLimit(array(10=>10,20=>20));
		$this->setChild('mainpager', $pager);
		
		$detailpager = $this->getLayout()->createBlock('page/html_pager', 'ristornodetailpage.pager')
		->setCollection($this->getRistornos()); //call your own collection getter here, name it something better than getCollection, please; *or* your call to getResourceModel()
		$detailpager->setAvailableLimit(array(10=>10,20=>20));
		$this->setChild('pager', $detailpager);
		
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
		 
	function getRistornos(){
		
		/*query
		 * SELECT `main_table`.*, `order`.`increment_id`, `item`.`name` AS `product`, `item`.`sku` FROM `hearedfrom_salescommission` AS `main_table`
 		INNER JOIN `sales_flat_order` AS `order` ON main_table.orig_order_id = order.entity_id
 		INNER JOIN `sales_flat_order_item` AS `item` ON main_table.order_item_id = item.item_id WHERE (user_id = '2')
		 */
		$customer = Mage::getSingleton('customer/session')->getCustomer();
		
		$collection = Mage::getModel('hearedfrom/salesCommission')->getCollection();
		
		$collection->addFieldToFilter('user_id', 
				Mage::getModel('hearedfrom/salesForce')->loadByCustid($customer->getEntityId())['entity_id']);
			
		// add joined data to the collection
			
		$select = $collection->getSelect();
		$resource = Mage::getSingleton('core/resource');
		
		$select->join(
				array('order' => $resource->getTableName('sales/order')),
				'main_table.orig_order_id = order.entity_id',
				array('increment_id')
		);
		$select->join(
				array('item' => $resource->getTableName('sales/order_item')),
				'main_table.order_item_id = item.item_id',
				array('product' => 'name', 'sku')
		);

		$collection->setOrder('increment_id');
		
		return $collection;
	}
	function getMonthlyRistornos(){
		$customer = Mage::getSingleton('customer/session')->getCustomer();
	
		$collection = Mage::getModel('hearedfrom/salesCommission')->getCollection();
	
		
		/*query
		SELECT `main_table`.*, DATE_FORMAT(create_dt,'%Y-%m') AS `date`, COUNT(*) AS `qty`, SUM(net_amount) AS `total_net`, SUM(ristorno) AS `total_ristorno` FROM `hearedfrom_salescommission` AS `main_table` WHERE (user_id = '2') GROUP BY DATE_FORMAT(create_dt,'%Y-%m')
		*/
		
		$collection->addFieldToFilter('user_id', 
				Mage::getModel('hearedfrom/salesForce')->loadByCustid($customer->getEntityId())['entity_id']);
		
		$select = $collection->getSelect();
		
		$select->columns("DATE_FORMAT(create_dt,'%Y-%m') as date")
		->columns('COUNT(*) AS qty')
		->columns('SUM(net_amount) AS total_net')
		->columns('SUM(ristorno) AS total_ristorno')
		->group("DATE_FORMAT(create_dt,'%Y-%m')");
				
    	$collection->setOrder('date');
    	
		return $collection;
	}
	
	function getDetailurl(){
		return $this->getUrl('customer/ristornopage/viewdetails');
	}
	function getMainurl(){
		return $this->getUrl('customer/ristornopage/');
	}
	public function getViewOrderUrl($id)
	{
		return $this->getUrl('sales/order/view', array('order_id' => $id));
	}
}
