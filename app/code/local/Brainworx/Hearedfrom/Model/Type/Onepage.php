<?php


class Brainworx_Hearedfrom_Model_Type_Onepage extends Mage_Checkout_Model_Type_Onepage
{
    public function initCheckout()
    {
        $checkout = $this->getCheckout();
        if (is_array($checkout->getStepData())) {
            foreach ($checkout->getStepData() as $step=>$data) {
                if (!($step==='login'
                    || Mage::getSingleton('customer/session')->isLoggedIn() && $step==='billing')) {
                    $checkout->setStepData($step, 'allow', false);
                }
            }
        }

        $checkout->setStepData('hearedfrom', 'allow', true);

        /*
        * want to laod the correct customer information by assiging to address
        * instead of just loading from sales/quote_address
        */
        $customer = Mage::getSingleton('customer/session')->getCustomer();
        if ($customer) {
            $this->getQuote()->assignCustomer($customer);
        }
        if ($this->getQuote()->getIsMultiShipping()) {
            $this->getQuote()->setIsMultiShipping(false);
            $this->getQuote()->save();
        }
        return $this;
    }
    /**
     * Save billing address information to quote
     * This method is called by One Page Checkout JS (AJAX) while saving the billing information.
     *
     * @param   array $data
     * @param   int $customerAddressId
     * @return  Mage_Checkout_Model_Type_Onepage
     */
    public function saveBilling($data, $customerAddressId)
    {
    	if (empty($data)) {
    		return array('error' => -1, 'message' => Mage::helper('checkout')->__('Invalid data.'));
    	}
    
    	$address = $this->getQuote()->getBillingAddress();
    	/* @var $addressForm Mage_Customer_Model_Form */
    	$addressForm = Mage::getModel('customer/form');
    	$addressForm->setFormCode('customer_address_edit')
    	->setEntityType('customer_address')
    	->setIsAjaxRequest(Mage::app()->getRequest()->isAjax());
    
    	if (!empty($customerAddressId)) {
    		$customerAddress = Mage::getModel('customer/address')->load($customerAddressId);
    		if ($customerAddress->getId()) {
    			if ($customerAddress->getCustomerId() != $this->getQuote()->getCustomerId()) {
    				return array('error' => 1,
    						'message' => Mage::helper('checkout')->__('Customer Address is not valid.')
    				);
    			}
    
    			$address->importCustomerAddress($customerAddress)->setSaveInAddressBook(0);
    			$addressForm->setEntity($address);
    			$addressErrors  = $addressForm->validateData($address->getData());
    			if ($addressErrors !== true) {
    				return array('error' => 1, 'message' => $addressErrors);
    			}
    		}
    	} else {
    		$addressForm->setEntity($address);
    		// emulate request object
    		$addressData    = $addressForm->extractData($addressForm->prepareRequest($data));
    		$addressErrors  = $addressForm->validateData($addressData);
    		if ($addressErrors !== true) {
    			return array('error' => 1, 'message' => array_values($addressErrors));
    		}
    		$addressForm->compactData($addressData);
    		//unset billing address attributes which were not shown in form
    		foreach ($addressForm->getAttributes() as $attribute) {
    			if (!isset($data[$attribute->getAttributeCode()])) {
    				$address->setData($attribute->getAttributeCode(), NULL);
    			}
    		}
    		$address->setCustomerAddressId(null);
    		// Additional form data, not fetched by extractData (as it fetches only attributes)
    		$address->setSaveInAddressBook(empty($data['save_in_address_book']) ? 0 : 1);
    	}
    
    	// set email for newly created user
    	if (!$address->getEmail() && $this->getQuote()->getCustomerEmail()) {
    		$address->setEmail($this->getQuote()->getCustomerEmail());
    	}
    
    	// validate billing address
    	if (($validateRes = $address->validate()) !== true) {
    		return array('error' => 1, 'message' => $validateRes);
    	}
    
    	$address->implodeStreetAddress();
    
    	if (true !== ($result = $this->_validateCustomerData($data))) {
    		return $result;
    	}
    
    	if (!$this->getQuote()->getCustomerId() && self::METHOD_REGISTER == $this->getQuote()->getCheckoutMethod()) {
    		if ($this->_customerEmailExists($address->getEmail(), Mage::app()->getWebsite()->getId())) {
    			return array('error' => 1, 'message' => $this->_customerEmailExistsMessage);
    		}
    	}
    
    	
    
    	$this->getQuote()->collectTotals();
    	$this->getQuote()->save();
    
    	if (!$this->getQuote()->isVirtual() && $this->getCheckout()->getStepData('shipping', 'complete') == true) {
    		//Recollect Shipping rates for shipping methods
    		$this->getQuote()->getShippingAddress()->setCollectShippingRates(true);
    	}
    
    	$this->getCheckout()
    	->setStepData('billing', 'allow', true)
    	->setStepData('billing', 'complete', true)
    	->setStepData('shipping', 'allow', true)
    	->setStepData('shipping_method', 'allow', true); //she added
    
    	return array();
    }
    /**
     * Specify quote shipping method
     *
     * @param   string $shippingMethod
     * @return  array
     */
    public function saveShippingMethod($shippingMethod,$_usebillingaddress=0)
    {
    	if (empty($shippingMethod)) {
    		return array('error' => -1, 'message' => Mage::helper('checkout')->__('Invalid shipping method.'));
    	}
    	$rate = $this->getQuote()->getShippingAddress()->getShippingRateByCode($shippingMethod);
    	if (!$rate) {
    		return array('error' => -1, 'message' => Mage::helper('checkout')->__('Invalid shipping method.'));
    	}
    	
    	//added
    	if (!$this->getQuote()->isVirtual()) {
    		/**
    		 * Billing address using otions
    		 */
    	
    		switch ($_usebillingaddress) {
    			case 0:
    				$shipping = $this->getQuote()->getShippingAddress();
    				$shipping->setSameAsBilling(0);
    				break;
    			case 1:
    				$address = $this->getQuote()->getBillingAddress();
    				$billing = clone $address;
    				$billing->unsAddressId()->unsAddressType();
    				$shipping = $this->getQuote()->getShippingAddress();
    				$shippingMethod = $shipping->getShippingMethod();
    	
    				// Billing address properties that must be always copied to shipping address
    				$requiredBillingAttributes = array('customer_address_id');
    	
    				// don't reset original shipping data, if it was not changed by customer
    				foreach ($shipping->getData() as $shippingKey => $shippingValue) {
    					if (!is_null($shippingValue) && !is_null($billing->getData($shippingKey))
    							&& !isset($data[$shippingKey]) && !in_array($shippingKey, $requiredBillingAttributes)
    					) {
    						$billing->unsetData($shippingKey);
    					}
    				}
    				$shipping->addData($billing->getData())
    				->setSameAsBilling(1)
    				->setSaveInAddressBook(0)
    				->setShippingMethod($shippingMethod)
    				->setCollectShippingRates(true);
    				$this->getCheckout()->setStepData('shipping', 'complete', true);
    				break;
    		}
    	}
    	//untill
    	
    	
    	$this->getQuote()->getShippingAddress()
    	->setShippingMethod($shippingMethod);
    
    	$this->getCheckout()
    	->setStepData('shipping_method', 'complete', true)
    	->setStepData('shipping', 'allow', true)
    	->setStepData('payment', 'allow', true);
    
    	return array();
    }
    
}
