<?php
 
class Brainworx_Hearedfrom_Model_SalesCommission extends Mage_Core_Model_Abstract
{
    protected function _construct()
    {
    	parent::_construct();
        $this->_init('hearedfrom/salesCommission');
    }
//     /**
//      * Prepare list of available order ids to use in dropdown filter options for the grid
//      * @return multitype:NULL
//      */
//     public function getOrderIds() {
    
//     	$orderArray = array();
//     	foreach($this->getCollection() as $rental){
//     		$orderArray[$rental->getOrigOrderId()] = $rental->getOrigOrderId();
    
//     	}
//     	return $orderArray;
    
//     }
//     /**
//      * Prepare the list of increment id's for the admin grid filter <bestelling #>
//      * @return multitype:mixed
//      */
//     public function getIncrementIds() {
    
//     	$collection = $this->getCollection();
//     	$select = $collection->getSelect();
//     	$resource = Mage::getSingleton('core/resource');
//     	$select->join(
//     			array('order' => $resource->getTableName('sales/order')),
//     			'main_table.orig_order_id = order.entity_id',
//     			array('increment_id')
//     	);
    	 
//     	$orderArray = array();
//     	foreach($collection as $order){
//     		$orderArray[$order->getData('increment_id')] = $order->getData('increment_id');
    
//     	}
//     	return $orderArray;
    
//     }
}