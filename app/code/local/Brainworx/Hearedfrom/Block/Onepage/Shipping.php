<?php
/**
 * Magento
 *
*/

/**
 * One page checkout status
 *
 * @author      Magento Core Team <core@magentocommerce.com>
 */
class Brainworx_Hearedfrom_Block_Onepage_Shipping extends Mage_Checkout_Block_Onepage_Shipping
{
	public function getAddressesHtmlSelect($type)
	{
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
			->setValue($addressId)
			->setOptions($options);
	
			return $select->getHtml();
		}
		return '';
	}
}
