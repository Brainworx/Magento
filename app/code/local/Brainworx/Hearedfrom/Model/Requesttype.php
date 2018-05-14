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
}