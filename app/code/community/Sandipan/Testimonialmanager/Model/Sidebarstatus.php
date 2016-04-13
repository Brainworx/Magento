<?php
class Sandipan_Testimonialmanager_Model_Sidebarstatus extends Varien_Object
{
    const STATUS_YES = 1;
    const STATUS_NO	= 2;

    static public function getOptionArray()
    {
        return array(
            self::STATUS_YES    => Mage::helper('testimonialmanager')->__('Yes'),
            self::STATUS_NO   => Mage::helper('testimonialmanager')->__('No'),
        );
    }
}