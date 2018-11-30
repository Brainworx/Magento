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
    	$username=$name;
    	//strip the zip and city from the name
    	if (($pos = strpos($name, "*")) !== FALSE) {
    		$username = trim(substr($name, $pos+1));
    	}
    	return $this->_getResource()->loadByUsername($username);
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
     * Prepare list for overview form select box, returns only salesforce not linked to any other salesforce
     */
    public function getUserNamesOptions(){
    	$userArray = array();
    	$userArray[0] = Mage::helper('hearedfrom')->__('Not Selected');
    	foreach($this->getCollection()->addFieldToSelect("*")
    			->addFieldToFilter('linked_to', array("eq" => 0)) as $usr){
    		$userArray[$usr->getEntityId()] = $usr->getZipCd().' '.$usr->getCity().' * '.$usr->getUserNm();
    	}
    	return $userArray;
    }
    /**
     * Prepare list of all salesforces for overview form select box
     */
    public function getAllUserNamesOptions(){
    	$userArray = array();
    	$userArray[0] = Mage::helper('hearedfrom')->__('Not Selected');
    	foreach($this->getCollection()->addFieldToSelect("*") as $usr){
    		$userArray[$usr->getEntityId()] = $usr->getZipCd().' '.$usr->getCity().' * '.$usr->getUserNm();
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
     * Loads the seller user_nm by customer id - to set autoselected value in select
     * @param unknown $custid
     * @return string|null if not found
     */
    public function loadSellerNameByCustid($custid){
    	$seller = $this->loadByCustid($custid);
    	if(empty($seller)){
    		return null;
    	}else {
    		return $seller['zip_cd'].' '.$seller['city'].' * '.$seller['user_nm'];
    	}
    }     
    /**
     * Load the seller user_nm by unique zorgpunt session id - to set autoselected value in select
     * @param unknown $sessionid <timestampxid>
     * @return string|null if not found
     */
    public function loadSellerNameByZorgpuntID($sessionid){
    	$zorgpuntid;
    	if (($pos = strpos($sessionid, "x")) !== FALSE) {
    		$zorgpuntid = trim(substr($sessionid, $pos+1));
    	}
    	$seller = $this->load($zorgpuntid);
    	if(empty($seller)){
    		return null;
    	}else {
    		return $seller['zip_cd'].' '.$eller['city'].' * '.$seller['user_nm'];
    	}
    }
    public function getUsernameForSelect(){
    	return $this->getZipCd().' '.$this->getCity().' * '.$this->getUserNm();
    }
}