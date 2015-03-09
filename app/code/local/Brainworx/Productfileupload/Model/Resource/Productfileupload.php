<?php

class Brainworx_Productfileupload_Model_Resource_Productfileupload extends Mage_Core_Model_Resource_Db_Abstract
{
    public function _construct()
    {    
        // Note that the id refers to the key field in your database table.
        $this->_init('productfileupload/productfileupload', 'fid');
    }
}