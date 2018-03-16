<?php
 
class Brainworx_Hearedfrom_Model_Resource_InvoicesView_Collection extends Mage_Core_Model_Resource_Db_Collection_Abstract
{
    protected function _construct() {
    	parent::_construct();
       	$this->_init('hearedfrom/invoicesView');
    }
}