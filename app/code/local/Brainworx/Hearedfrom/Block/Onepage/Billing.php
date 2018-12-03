<?php
class Brainworx_Hearedfrom_Block_Onepage_Billing extends Mage_Checkout_Block_Onepage_Billing
{
	protected function _construct()
	{
		$this->getCheckout()->setStepData('billing', array(
				'label'     => Mage::helper('checkout')->__('Billing Information'),
				'is_show'   => $this->isShow()
		));
		
		$this->getCheckout()->setStepData('billing', 'allow', false);
	
		parent::_construct();
	}
	
	public function getAddressesHtmlSelect($type)
	{
		if($type == "billing"){
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
					if ($type=='billing') {
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
}
