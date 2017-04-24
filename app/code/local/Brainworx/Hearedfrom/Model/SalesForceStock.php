<?php
 
class Brainworx_Hearedfrom_Model_SalesForceStock extends Mage_Core_Model_Abstract
{
    protected function _construct()
    {
    	parent::_construct();
        $this->_init('hearedfrom/salesForceStock');
    }
    /**
     * Load hearedfrom SalesForceStock record in array with column names as key
     * @param unknown $name
     */
    public function loadBySalesForce($id){
    	return $this->_getResource()->loadBySalesForce($id);
    }
    /**
     * load SalesForceStock by product code
     * Load SalesForceStock record in array with column names as key
     */
    public function loadByProdCode($code){
    	return $this->_getResource()->loadByProdCode($sfid);
    }    
    /**
     * load SalesForceStock by product code and sales force
     * Load SalesForceStock record in array with column names as key
     */
    public function loadByProdCodeAndSalesForce($code, $id){
    	return $this->_getResource()->loadByProdCodeAndSalesForce($code,$id);
    }
}