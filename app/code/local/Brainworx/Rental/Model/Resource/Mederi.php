<?php
 
class Brainworx_Rental_Model_Resource_Mederi extends Mage_Core_Model_Resource_Db_Abstract
{
    protected function _construct()
    {
        $this->_init('rental/mederi', 'entity_id');
    }
    /**
     * Optional method to load entity by mederi id
     * @param varchar $id
     * @return multitype:mederi array of renteditem with column as keys
     */
    public function loadByMederiId($id)
    {
    	$adapter = $this->_getReadAdapter();
    
    	$select = $adapter->select()
    	->from($this->getMainTable())
    	->where($this->getMainTable().'.'.'mederi_id'.'=?',$id)
    	->where($this->getMainTable().'.'.'enabled'.'=?',1);
    
    	return $adapter->fetchRow($select);
    }
}