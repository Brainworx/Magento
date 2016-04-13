<?php

class Sandipan_Testimonialmanager_Model_Testimonialmanager extends Mage_Core_Model_Abstract
{
    public function _construct()
    {
        parent::_construct();
        $this->_init('testimonialmanager/testimonialmanager');
    }

}