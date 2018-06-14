<?php
 
class Brainworx_Hearedfrom_Model_Requesttype extends Mage_Core_Model_Abstract
{
    protected function _construct()
    {
    	parent::_construct();
        $this->_init('hearedfrom/requesttype');
    }
    public function loadByType($type){
    	return $this->_getResource()->loadByType($type);
    }
    /**
     * Prepare list of all types for overview form select box
     */
    public function getAllOptions(){
    	$userArray = array();
    	$userArray[0] = Mage::helper('hearedfrom')->__('Not Selected');
    	foreach($this->getCollection()->addFieldToSelect("*") as $type){
    		$userArray[$type->getEntityId()] = $type->getType();
    	}
    	return $userArray;
    }
}