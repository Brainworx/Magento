<?php
 
class Brainworx_Hearedfrom_Model_Resource_SalesCommission extends Mage_Core_Model_Resource_Db_Abstract
{
    protected function _construct()
    {
        $this->_init('hearedfrom/salesCommission', 'entity_id');
    }
    /**
     * Optional method to load 1 entity by parameters
     * @param int $id
     * @return multitype:salescommission  array of salescommission data with column as keys
     */
    public function loadByLastCommission($order_id,$item_id)
    {
    	$adapter = $this->_getReadAdapter();
    
    	$select = $adapter->select()
    	->from($this->getMainTable())
    	->where($this->getMainTable().'.'.'orig_order_id'.'=?',$order_id)
    	->where($this->getMainTable().'.'.'order_item_id'.'=?',$item_id)
        ->order($this->getMainTable().'.'.'entity_id DESC');
    
    	return $adapter->fetchRow($select);
    }
}
