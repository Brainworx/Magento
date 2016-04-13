<?php

class Sandipan_Testimonialmanager_Model_Mysql4_Testimonialmanager_Collection extends Mage_Core_Model_Mysql4_Collection_Abstract
{
    public function _construct()
    {
        parent::_construct();
        $this->_init('testimonialmanager/testimonialmanager');
    }
}