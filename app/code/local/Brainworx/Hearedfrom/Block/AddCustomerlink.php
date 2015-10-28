<?php

class Brainworx_Hearedfrom_Block_AddCustomerlink extends Mage_Customer_Block_Account_Navigation   
{
	/**
	 * Add link to ristorno for salesforce members
	 */
	public function addLinkToUserNav() {
		$customer = Mage::getSingleton('customer/session')->getCustomer();		
		if(!empty($customer)){
			$salesforce = Mage::getModel('hearedfrom/salesForce')->loadByCustid($customer->getEntityId());
			if(!empty($salesforce)){
				$this->addLink(
						"Ristorno",
						"customer/ristornopage/",
						"Ristorno"
				);
			}
		}
	}
	/**
	 * Add link to deliveries for salesforce members
	 */
	public function addLinkToDeliveriesNav() {
		$customer = Mage::getSingleton('customer/session')->getCustomer();
		if(!empty($customer)){
			$salesforce = Mage::getModel('hearedfrom/salesForce')->loadByCustid($customer->getEntityId());
			if(!empty($salesforce)){
				$this->addLink(
						"Deliveries",
						"customer/deliveriespage/",
						"Deliveries"
				);
			}
		}
	}
}