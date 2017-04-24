<?php
 
class Brainworx_Rental_Model_RentedItem extends Mage_Core_Model_Abstract
{
    protected function _construct()
    {
    	parent::_construct();
        $this->_init('rental/rentedItem');
    }
    /**
     * Prepare list of available order ids to use in dropdown filter options for the grid
     * @return multitype:NULL
     */
    public function getOrderIds() {
    
    	$orderArray = array();
    	foreach($this->getCollection() as $rental){
    		$orderArray[$rental->getOrigOrderId()] = $rental->getOrigOrderId();
    
    	}
    	return $orderArray;
    
    }
    /**
     * Prepare the list of increment id's for the admin grid filter <bestelling #>
     * @return multitype:mixed
     */
    public function getIncrementIds() {
    
    	$collection = $this->getCollection();
    	$select = $collection->getSelect();
    	$resource = Mage::getSingleton('core/resource');
    	$select->join(
    			array('order' => $resource->getTableName('sales/order')),
    			'main_table.orig_order_id = order.entity_id',
    			array('increment_id')
    	);
    	 
    	$orderArray = array();
    	foreach($collection as $order){
    		$orderArray[$order->getData('increment_id')] = $order->getData('increment_id');
    
    	}
    	return $orderArray;
    
    }
    /**
     * Update the stock level for the product of this rentedItem.
     * To be used when a rentedItem has been returned.
     */
   	public function updateStock(){
   		try{
	   		 $item = Mage::getModel("sales/order_item")->load($this->getOrderItemId());
	   		 $productId = $item->getProductId();
	   		 
	   		 $stockItem = Mage::getModel('cataloginventory/stock_item')->loadByProduct($productId);
	   		 $stockItemId = $stockItem->getId();
	   		 $stockQty = $this->getQuantity() + $stockItem->getQty();
	   		    		 
	   		 $stockItem->setData('qty', $stockQty);
	   		 $stockItem->setData('is_in_stock',1);		 
	   		 
	   		 $stockItem->save();
	   		 
	   		 Mage::getSingleton('cataloginventory/stock_status')->updateStatus($productId);
	   		 
   		}catch ( Exception $e ) {
			Mage::getSingleton ( 'adminhtml/session' )->addError ( $e->getMessage () );
			return;
		}
   	
   	}
   	/**
   	 * Load renteditem record in array with column names as key
   	 * @param unknown $name
   	 */
   	public function loadByOrderItem($itemid){
   		return $this->_getResource()->loadByOrderItem($itemid);
   	}
   	public function isOpenRental($orderItemId){
   		$rental = self::loadByOrderItem($orderItemId);
   		if(!empty($rental)){
   			return !isset($rental["end_dt"]);
   		}else{
   			return false;
   		}
   	}
   	public function getEndDateRental($orderItemId){
   		$rental = self::loadByOrderItem($orderItemId);
   		if(!empty($rental)){
   			return $rental["end_dt"];
   		}else{
   			return null;
   		}
   	}
}