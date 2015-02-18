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
    	foreach($this->getCollection()as $user){
    		$userArray[$user->getEntityId()] = $user->getUserNm();    
    	}
    	return $userArray;
    }
}