<?php

class Brainworx_Hearedfrom_Block_Requestform extends Mage_Customer_Block_Account_Dashboard  
{
	public function getRequestInfo($type){
        $requesttype = Mage::getModel("hearedfrom/requesttype")->loadByType($type);
        return ($requesttype["type"]." - ".$requesttype["description"]);
	}
	//returns request type id from database using the requesttype as provided from cms block config
	public function getRequestTypeId($type){
		Mage::log('loading contact request type for '.$type);
		$requesttype = Mage::getModel("hearedfrom/requesttype")->loadByType($type);
		return ($requesttype["entity_id"]);
	}
	/**For collections with group by statements*/
	public function getHearedFromValues()
	{
		//load values to be used in select box on hearedfrom.phtml template for onepagecheckout
    	$_options = array();
    	array_push($_options,Mage::helper('checkout')->__('Select'));
    	$collection = Mage::getModel("hearedfrom/salesForce")->getCollection();
    	foreach($collection as $salesForce){
    		array_push($_options,$salesForce->getUserNameForSelect());
    	}
    	$this->setHearedFromValues($_options);
    	//set the current seller
    	$cid='';
    	if(Mage::getSingleton('customer/session')->isLoggedIn()){
    		$cid = Mage::getSingleton('customer/session')->getCustomer()->getEntityId();
    		$groupId = explode(",",Mage::getSingleton('customer/session')->getCustomerGroupId());
    		if(in_array(Mage::getModel('core/variable')->setStoreId(Mage::app()->getStore()->getId())->loadByCode('MEDERI_GID')->getValue('text'),$groupId)) {
    			$mederisellerid = Mage::getModel('core/variable')->setStoreId(Mage::app()->getStore()->getId())->loadByCode('MEDERI_FORCE_ID')->getValue('text');
    			$this->setSellerValue(Mage::getModel("hearedfrom/salesForce")->load($mederisellerid)['user_nm']);
    		}else{
    			$this->setSellerValue(Mage::getModel("hearedfrom/salesForce")->loadSellerNameByCustid($cid));
    		}
    	}
    	return $_options;
	}
	protected function _prepareLayout()
	{
		parent::_prepareLayout();
				
		return $this;
	}
}