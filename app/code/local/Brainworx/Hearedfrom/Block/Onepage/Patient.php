<?php

class Brainworx_Hearedfrom_Block_Onepage_Patient extends Mage_Checkout_Block_Onepage_Abstract
{
    protected function _construct()
    {    	
        $this->getCheckout()->setStepData('patient', array(
            'label'     => Mage::helper('checkout')->__('Patient information'),
            'is_show'   => true
        ));
        if ($this->isCustomerLoggedIn()) {
        	$this->getCheckout()->setStepData('patient', 'allow', true);
        }
       
        parent::_construct();
    }
}