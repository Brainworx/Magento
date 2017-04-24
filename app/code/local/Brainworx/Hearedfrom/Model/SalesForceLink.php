<?php
 
class Brainworx_Hearedfrom_Model_SalesForceLink extends Mage_Core_Model_Abstract
{
    protected function _construct()
    {
    	parent::_construct();
        $this->_init('hearedfrom/salesForceLink');
    }
    /**
     * Load hearedfrom salesforce record in array with column names as key
     * @param unknown $name
     */
    public function loadBySalesForce($id){
    	return $this->_getResource()->loadBySalesForce($id);
    }
    /**
     * load salesForceLink by linked salesforce id
     * Load salesForceLink record in array with column names as key
     */
    public function loadByLinkedSalesForceId($sfid){
    	return $this->_getResource()->loadByLinkedSalesForceId($sfid);
    }
    /**
     * load salesForceLink by customer id
     * Load salesForceLink record in array with column names as key
     */
    public function loadByLinkedCustid($custid){
    	return $this->_getResource()->loadByLinkedCustid($custid);
    }     
}