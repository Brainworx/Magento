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
				$this->addLink(
						Mage::helper('customer')->__('Deliveries'),
						"customer/deliveriespage/",
						Mage::helper('customer')->__('Deliveries')
				);
		}
	}
	/**
	 * Add link to deliveries for salesforce members
	 */
	public function addLinkToPatientOrdersNav() {
		$customer = Mage::getSingleton('customer/session')->getCustomer();
		if(!empty($customer)){
			$salesforce = Mage::getModel('hearedfrom/salesForce')->loadByCustid($customer->getEntityId());
			if(!empty($salesforce)){
				$this->addLink(
						Mage::helper('customer')->__('Patient Orders'),
						"customer/patientorderpage/",
						Mage::helper('customer')->__('Patient Orders')
				);
			}
		}
	}
	/**
	 * Add link to invoices for everyone
	 */
	public function addLinkToInvoicesNav() {
		$customer = Mage::getSingleton('customer/session')->getCustomer();
		if(!empty($customer)){
			$this->addLink(
						Mage::helper('customer')->__('My Invoices'),
						"customer/invoicespage/",
						Mage::helper('customer')->__('My Invoices')
				);
			
		}
	}
	/**
	 * Add link to ristorno for salesforceStock (consignation) items
	 */
	public function addLinkToStockNav() {
		$customer = Mage::getSingleton('customer/session')->getCustomer();
		if(!empty($customer)){
			$salesforce = Mage::getModel('hearedfrom/salesForce')->loadByCustid($customer->getEntityId());
			if(!empty($salesforce)){
				$this->addLink(
						Mage::helper('customer')->__('Stock'),
						"customer/stockrequestpage/",
						Mage::helper('customer')->__('Stock')
				);
			}
		}
	}
}