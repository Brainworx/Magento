<?php

class Brainworx_Productfileupload_Model_Resource_Productfileupload_Collection extends Mage_Core_Model_Resource_Db_Collection_Abstract
{
    public function _construct()
    {
        parent::_construct();
        $this->_init('productfileupload/productfileupload');
    }
}