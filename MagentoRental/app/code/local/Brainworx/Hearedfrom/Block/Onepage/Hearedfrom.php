<?php

class Brainworx_Hearedfrom_Block_Onepage_Hearedfrom extends Mage_Checkout_Block_Onepage_Abstract
{
    protected function _construct()
    {    	
        $this->getCheckout()->setStepData('hearedfrom', array(
            'label'     => Mage::helper('checkout')->__('Who told you about us'),
            'is_show'   => true
        ));
        //load values to be used in select box on hearedfrom.phtml template for onepagecheckout
        $_options = array();
        $collection = Mage::getModel("hearedfrom/salesForce")->getCollection();
        foreach($collection as $salesForce){
        	array_push($_options,$salesForce->getUserNm());
        }
        $this->setHearedFromValues($_options);
        parent::_construct();
    }
}