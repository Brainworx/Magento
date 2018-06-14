<?php
 
class Brainworx_Hearedfrom_Model_Resource_Requesttype extends Mage_Core_Model_Resource_Db_Abstract
{
    protected function _construct()
    {
        $this->_init('hearedfrom/requesttype', 'entity_id');
    }
    public function loadByType($type)
    {
    	$adapter = $this->_getReadAdapter();
    
    	$select = $adapter->select()
    	->from($this->getMainTable())
    	->where($this->getMainTable().'.'.'type'.'=?',$type)
    	->where($this->getMainTable().'.'.'end_dt'.' is null OR '.$this->getMainTable().'.'.'end_dt'.'>?',date('Y-m-d'))
    	->order($this->getMainTable().'.'.'entity_id DESC');
    
    	return $adapter->fetchRow($select);
    }
}