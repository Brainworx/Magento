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
        
        //load values to be used in select box on hearedfrom.phtml template for onepagecheckout
        $_options = array();
        array_push($_options,Mage::helper('checkout')->__('Select'));
        $collection = Mage::getModel("hearedfrom/salesForce")->getCollection();
        $collection->addFieldToFilter( //last inv dt null or end_dt > today
			array('end_dt','end_dt'),
			array(
					array('gt'=>date('Y-m-d', strtotime('now'))),
					array('null' => true))
		);
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
        
        //When logged-in: check customer or mederi
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
