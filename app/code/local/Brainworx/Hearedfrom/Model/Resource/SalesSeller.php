<?php
 
class Brainworx_Hearedfrom_Model_Resource_SalesSeller extends Mage_Core_Model_Resource_Db_Abstract
{
    protected function _construct()
    {
        $this->_init('hearedfrom/salesSeller', 'entity_id');
    }
    public function loadByOrderId($order_id)
    {
    	$adapter = $this->_getReadAdapter();
    
    	$select = $adapter->select()
    	->from($this->getMainTable())
    	->where($this->getMainTable().'.'.'order_id'.'=?',$order_id);
    
    	return $adapter->fetchRow($select);
    }
    public function updateSellerDetails($entity_id,$sellercustid){
    	$adapter = $this->_getWriteAdapter();
    	
    	$adapter->update($this->getMainTable(), 
    			array("seller_cust_id" => $sellercustid),
    			"entity_id=".$entity_id);
    	
    	return true;
    }
}