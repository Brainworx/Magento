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
						Mage::helper('customer')->__('Ristorno'),
						"customer/ristornopage/",
						Mage::helper('customer')->__('Ristorno')
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
						Mage::helper('customer')->__('Deliveries'),
						"customer/deliveriespage/",
						Mage::helper('customer')->__('Deliveries')
				);
			}
		}
	}
}