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
        $zorgpuntuser;
        foreach($collection as $salesForce){
        	if($salesForce->getUserNm()!="Zorgpunt")
        		array_push($_options,$salesForce->getUserNameForSelect());
        	else 
        		$zorgpuntuser = $salesForce->getUserNameForSelect();
        }
        array_splice( $_options, 1, 0, $zorgpuntuser );
        $this->setHearedFromValues($_options);
        $this->setSellerChangePossible(true);
        //set the current seller - can be based on customerID, set zorgpuntsessionid or mederi
        //logged-in or guest: check for set zorgpuntcheck session
        $cookies = Mage::getSingleton('core/cookie')->get();
        $zorgpunt=null;
        if(array_key_exists('zorgpuntcheck',$cookies)){
        	$zorgpuntcheck = $cookies['zorgpuntcheck'];
        	if(!empty($zorgpuntcheck)){
        		$zorgpunt = Mage::getModel("hearedfrom/salesForce")->loadSellerNameByZorgpuntID($zorgpuntcheck);
        		if(!empty($zorgpunt)){
        			$this->setSellerValue($zorgpunt);
        			$this->setSellerChangePossible(false);
        		}
        	}
        }
        
        //Alwasy logged-in: check customer or mederi
        $cid='';
        if(Mage::getSingleton('customer/session')->isLoggedIn()){
        	$cid = Mage::getSingleton('customer/session')->getCustomer()->getEntityId();
        	$groupId = explode(",",Mage::getSingleton('customer/session')->getCustomerGroupId());
        	if(in_array(Mage::getModel('core/variable')->setStoreId(Mage::app()->getStore()->getId())->loadByCode('MEDERI_GID')->getValue('text'),$groupId)) {
        		$mederisellerid = Mage::getModel('core/variable')->setStoreId(Mage::app()->getStore()->getId())->loadByCode('MEDERI_FORCE_ID')->getValue('text');
        		$this->setSellerValue(Mage::getModel("hearedfrom/salesForce")->load($mederisellerid)['user_nm']);        		
        	}else{
        		if(empty($zorgpunt)){
        			$this->setSellerValue(Mage::getModel("hearedfrom/salesForce")->loadSellerNameByCustid($cid));
        		}
        	}
        	$this->setVaphOrder(Mage::getSingleton('core/session')->getVaphOrder());
        }
        
        
        parent::_construct();
    }
}