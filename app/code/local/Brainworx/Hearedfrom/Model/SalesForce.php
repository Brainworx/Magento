<?php
 
class Brainworx_Hearedfrom_Model_SalesForce extends Mage_Core_Model_Abstract
{
    protected function _construct()
    {
    	parent::_construct();
        $this->_init('hearedfrom/salesForce');
    }
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
}