<?php
 
class Brainworx_Hearedfrom_Model_SalesSeller extends Mage_Core_Model_Abstract
{
    protected function _construct()
    {
    	parent::_construct();
        $this->_init('hearedfrom/salesSeller');
    }
    public function loadByOrderId($id){
    	return $this->_getResource()->loadByOrderId($id);
    }
}