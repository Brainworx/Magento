<?php
 
class Brainworx_Hearedfrom_Model_SalesForce extends Mage_Core_Model_Abstract
{
    protected function _construct()
    {
    	parent::_construct();
        $this->_init('hearedfrom/salesForce');
    }
    /**
     * Load hearedfrom salesforce record in array with column names as key
     * @param unknown $name
     */
    public function loadByUsername($name){
    	return $this->_getResource()->loadByUsername($name);
    }
    /**
     * Prepare list for overview grid
     */
    public function getUserNames(){
    	$userArray = array();
    	$i = 0;
    	foreach($this->getCollection()->addFieldToSelect("user_nm") as $usr){
    		$userArray[$i] = $usr->getUserNm();
    		$i = $i+1;
    	}
    	return $userArray;
    }
    /**
     * load hearedfrom by customer id
     * Load hearedfrom salesforce record in array with column names as key
     */
    public function loadByCustid($custid){
    	return $this->_getResource()->loadByCustomerid($custid);
    }
    /**
     * Loads the seller user_nm by customer id
     * @param unknown $custid
     * @return string|null if not found
     */
    public function loadSellerNameByCustid($custid){
    	$seller = $this->loadByCustid($custid);
    	if(empty($seller)){
    		return null;
    	}else {
    		return $seller['user_nm'];
    	}
    }
     
}