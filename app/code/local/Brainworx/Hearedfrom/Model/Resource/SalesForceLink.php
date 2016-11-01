<?php
 
class Brainworx_Hearedfrom_Model_Resource_SalesForceLink extends Mage_Core_Model_Resource_Db_Abstract
{
    protected function _construct()
    {
        $this->_init('hearedfrom/salesForceLink', 'entity_id');
    }
    /**
     * Optional method to load entity by sales force
     * @param varchar $username
     * @return multitype:salesforce array of salesforce with column as keys
     */
    public function loadBySalesForce($id)
    {
        $adapter = $this->_getReadAdapter();
    
        $select = $adapter->select()
            ->from($this->getMainTable())
            ->where($this->getMainTable().'.'.'force_id'.'=?',$id);

        return $adapter->fetchRow($select);
    }
    /**
     * Optional method to load entity by linked sales force
     * @param int $id
     * @return multitype:salesforce  array of salesforce with column as keys
     */
    public function loadByLinkedSalesForceId($id)
    {
    	$adapter = $this->_getReadAdapter();
    
    	$select = $adapter->select()
    	->from($this->getMainTable())
    	->where($this->getMainTable().'.'.'linked_force_id'.'=?',$id);
    
    	return $adapter->fetchRow($select);
    }
    /**
     * Optional method to load entity by customerid
     * @param int $id
     * @return multitype:salesforce  array of salesforce with column as keys
     */
    public function loadByLinkedCustid($id)
    {
    	$adapter = $this->_getReadAdapter();
    
    	$select = $adapter->select()
    	->from($this->getMainTable())
    	->where($this->getMainTable().'.'.'linked_cust_id'.'=?',$id);
    
    	return $adapter->fetchRow($select);
    }
}