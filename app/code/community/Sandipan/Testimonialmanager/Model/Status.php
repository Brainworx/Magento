<?php
class Sandipan_Testimonialmanager_Model_Status extends Varien_Object
{
    const STATUS_NOT_APPROVED	= 1;
    const STATUS_APPROVED	= 2;
    const STATUS_PENDING	= 3;

    static public function getOptionArray()
    {
        return array(
            self::STATUS_NOT_APPROVED    => Mage::helper('testimonialmanager')->__('Not Approved'),
            self::STATUS_APPROVED   => Mage::helper('testimonialmanager')->__('Approved'),
            self::STATUS_PENDING    => Mage::helper('testimonialmanager')->__('Pending')
        );
    }
}