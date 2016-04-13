<?php

class Sandipan_Testimonialmanager_Model_Mysql4_Testimonialmanager extends Mage_Core_Model_Mysql4_Abstract
{
    public function _construct()
    {    
        $this->_init('testimonialmanager/testimonialmanager', 'testimonial_id');
    }
}