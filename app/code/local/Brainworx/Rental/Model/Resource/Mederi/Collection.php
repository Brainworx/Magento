<?php
 
class Brainworx_Rental_Model_Resource_Mederi_Collection extends Mage_Core_Model_Resource_Db_Collection_Abstract
{
    protected function _construct() {
    	parent::_construct();
       	$this->_init('rental/mederi');
    }
}