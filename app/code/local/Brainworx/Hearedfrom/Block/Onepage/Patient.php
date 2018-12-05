<?php

class Brainworx_Hearedfrom_Block_Onepage_Patient extends Mage_Checkout_Block_Onepage_Abstract
{
    protected function _construct()
    {    	
        $this->getCheckout()->setStepData('patient', array(
            'label'     => Mage::helper('checkout')->__('Patient information'),
            'is_show'   => true
        ));
        if ($this->isCustomerLoggedIn()) {
        	$this->getCheckout()->setStepData('patient', 'allow', true);
        }
       
        parent::_construct();
    }
    public function getAddressesHtmlSelect($type)
    {
    	if($type == "patient"){
    		if ($this->isCustomerLoggedIn()) {
    			$options = array();
    			$options[] = array(
    					'value' =>'',
    					'label' =>Mage::helper('checkout')->__('New Address')
    			);
    			foreach ($this->getCustomer()->getAddresses() as $address) {
    				$options[] = array(
    						'value' => $address->getId(),
    						'label' => $address->format('oneline')
    				);
    			}
    
    			$addressId = $this->getAddress()->getCustomerAddressId();
    			if (empty($addressId)) {
    				if ($type=='patient') {
    					$address = $this->getCustomer()->getPrimaryBillingAddress();
    				} else {
    					$address = $this->getCustomer()->getPrimaryShippingAddress();
    				}
    				if ($address) {
    					$addressId = $address->getId();
    				}
    			}
    
    			$select = $this->getLayout()->createBlock('core/html_select')
    			->setName($type.'_address_id')
    			->setId($type.'-address-select')
    			->setClass('address-select')
    			->setExtraParams('onchange="'.$type.'.newAddress(!this.value)"')
    			->setValue('')
    			->setOptions($options);
    
    
    			return $select->getHtml();
    		}
    	}else{
    		return parent::getAddressesHtmlSelect($type);
    	}
    	return '';
    }
    /**
     * Return Sales Quote Address model
     *
     * @return Mage_Sales_Model_Quote_Address
     */
    public function getAddress()
    {
    	if (is_null($this->_address)) {
    		if ($this->isCustomerLoggedIn()) {
    			$this->_address = $this->getQuote()->getPatientAddress();
    			if(!$this->_address->getFirstname()) {
    				$this->_address->setFirstname($this->getQuote()->getCustomer()->getFirstname());
    			}
    			if(!$this->_address->getLastname()) {
    				$this->_address->setLastname($this->getQuote()->getCustomer()->getLastname());
    			}
    		} else {
    			$this->_address = Mage::getModel('sales/quote_address');
    		}
    	}
    
    	return $this->_address;
    }
    /**
     * Return Customer Address First Name
     * If Sales Quote Address First Name is not defined - return Customer First Name
     *
     * @return string
     */
    public function getFirstname()
    {
    	$firstname = $this->getAddress()->getFirstname();
    	if (empty($firstname) && $this->getQuote()->getCustomer()) {
    		return $this->getQuote()->getCustomer()->getFirstname();
    	}
    	return $firstname;
    }
    
    /**
     * Return Customer Address Last Name
     * If Sales Quote Address Last Name is not defined - return Customer Last Name
     *
     * @return string
     */
    public function getLastname()
    {
    	$lastname = $this->getAddress()->getLastname();
    	if (empty($lastname) && $this->getQuote()->getCustomer()) {
    		return $this->getQuote()->getCustomer()->getLastname();
    	}
    	return $lastname;
    }
    
}