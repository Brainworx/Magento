<?php
/*Model for Soap V1 api*/

class Brainworx_Rental_Customer_Model_Customer_Api extends Mage_Customer_Model_Customer_Api
{

	/**
	 * Create new customer
	 *
	 * @param array $customerData
	 * @return int
	 */
	public function login($email)
	{
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