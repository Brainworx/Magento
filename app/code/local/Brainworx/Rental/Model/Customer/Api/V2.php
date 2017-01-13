<?php
/* Model for Soap v2 api*/
class Brainworx_Rental_Model_Customer_Api_V2 extends Mage_Customer_Model_Customer_Api_V2
{
	/*
	 * login customer and retrieve sessionID for frontend
	 */
	public function login($email){
		try{
			Mage::app()->setCurrentStore(Mage::app()->getWebsite(true)->getDefaultGroup()->getDefaultStoreId());
			// Init a Magento session. This is super ultra important
			Mage::getSingleton('core/session');
			
			// $customer Mage_Customer_Model_Customer
			// We get an instance of the customer model for the actual website
			$customer = Mage::getModel('customer/customer')
			->setWebsiteId(Mage::app()->getStore()->getWebsiteId());
			
			// Load the client with the appropriate email
			$customer->loadByEmail($email);
			
			// Get a customer session
			$session = Mage::getSingleton('customer/session');
			
			$session->loginById($customer->getId());
			if ($session->isLoggedIn()) {
				return $session->getSessionId();
			} 
		} catch (Mage_Core_Exception $e) {
            $this->_fault('data_invalid', $e->getMessage());
        }
        return null;
	}
}