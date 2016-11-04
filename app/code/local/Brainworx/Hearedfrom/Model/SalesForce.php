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
     * Prepare list for overview grid filter
     */
    public function getUserNames(){
    	$userArray = array();
    	foreach($this->getCollection()->addFieldToSelect("user_nm") as $usr){
    		$userArray[$usr->getUserNm()] = $usr->getUserNm();
    	}
    	return $userArray;
    }
    /**
     * Prepare list for overview form select box
     */
    public function getUserNamesOptions(){
    	$userArray = array();
    	$userArray[0] = Mage::helper('hearedfrom')->__('Not Selected');
    	foreach($this->getCollection()->addFieldToSelect("*")
    			->addFieldToFilter('linked_to', array("eq" => 0)) as $usr){
    		$userArray[$usr->getEntityId()] = $usr->getUserNm();
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