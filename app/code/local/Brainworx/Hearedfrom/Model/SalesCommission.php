<?php
 
class Brainworx_Hearedfrom_Model_SalesCommission extends Mage_Core_Model_Abstract
{
    protected function _construct()
    {
    	parent::_construct();
        $this->_init('hearedfrom/salesCommission');
    }
    /**
     * Prepare list of available types for dropdown filter options for the grid
     * @return multitype:NULL
     */
    public function getTypes() {
    
    	$array = array();
    	foreach($this->getCollection()->addFieldToSelect("type")->distinct(true) as $comm){
    		$array[$comm->getType()] = $comm->getType();
    	}
    	return $array;
    }
}