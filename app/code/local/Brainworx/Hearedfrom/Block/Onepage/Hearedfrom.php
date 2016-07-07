<?php

class Brainworx_Hearedfrom_Block_Onepage_Hearedfrom extends Mage_Checkout_Block_Onepage_Abstract
{
    protected function _construct()
    {    	
        $this->getCheckout()->setStepData('hearedfrom', array(
            'label'     => Mage::helper('checkout')->__('Additional info'),
            'is_show'   => true
        ));
        //load values to be used in select box on hearedfrom.phtml template for onepagecheckout
        $_options = array();
        array_push($_options,Mage::helper('checkout')->__('Select'));
        $collection = Mage::getModel("hearedfrom/salesForce")->getCollection();
        foreach($collection as $salesForce){
        	array_push($_options,$salesForce->getUserNm());
        }
        $this->setHearedFromValues($_options);
        //set the current seller
        $cid='';
        if(Mage::getSingleton('customer/session')->isLoggedIn()){
        	$cid = Mage::getSingleton('customer/session')->getCustomer()->getEntityId();
        }
        $this->setSellerValue(Mage::getModel("hearedfrom/salesForce")->loadSellerNameByCustid($cid));

        parent::_construct();
    }
}