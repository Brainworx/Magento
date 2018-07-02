<?php
class Brainworx_Hearedfrom_Helper_Data extends Mage_Checkout_Helper_Data
{
	public function getDocMenuBlockId(){
		$customer = Mage::getSingleton('customer/session')->getCustomer();
		if(!empty($customer)){
			$salesforce = Mage::getModel('hearedfrom/salesForce')->loadByCustid($customer->getEntityId());
			if(!empty($salesforce)){
				return "doc_menu";
			}
		}
	}
}