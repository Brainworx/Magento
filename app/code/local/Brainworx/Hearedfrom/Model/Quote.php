<?php
/**
 * Magento
 *
 * 
 * @category    Mage
 * @package     Mage_Sales
 * @author      Magento Core Team <core@magentocommerce.com>
 */
class Brainworx_Hearedfrom_Model_Quote extends Mage_Sales_Model_Quote
{
    /**
     * Assign customer model to quote with billing and shipping address change
     *
     * @param  Mage_Customer_Model_Customer    $customer
     * @param  Mage_Sales_Model_Quote_Address  $billingAddress
     * @param  Mage_Sales_Model_Quote_Address  $shippingAddress
     * @return Mage_Sales_Model_Quote
     */
    public function assignCustomerWithAddressChange(
        Mage_Customer_Model_Customer    $customer,
        Mage_Sales_Model_Quote_Address  $billingAddress  = null,
        Mage_Sales_Model_Quote_Address  $shippingAddress = null,Mage_Sales_Model_Quote_Address  $patientAddress = null
    )
    {
        if ($customer->getId()) {
            $this->setCustomer($customer);

            if (!is_null($billingAddress)) {
                $this->setBillingAddress($billingAddress);
            } else {
                $defaultBillingAddress = $customer->getDefaultBillingAddress();
                if ($defaultBillingAddress && $defaultBillingAddress->getId()) {
                    $billingAddress = Mage::getModel('sales/quote_address')
                        ->importCustomerAddress($defaultBillingAddress);
                    $this->setBillingAddress($billingAddress);
                }
            }

            if (is_null($shippingAddress)) {
                $defaultShippingAddress = $customer->getDefaultShippingAddress();
                if ($defaultShippingAddress && $defaultShippingAddress->getId()) {
                    $shippingAddress = Mage::getModel('sales/quote_address')
                        ->importCustomerAddress($defaultShippingAddress);
                } else {
                    $shippingAddress = Mage::getModel('sales/quote_address');
                }
            }
            $this->setShippingAddress($shippingAddress);
            
            if (is_null($patientAddress)) {
                $defaultPatientAddress = $customer->getDefaultPatientAddress();
                if ($defaultPatientAddress && $defaultPatientAddress->getId()) {
                    $patientAddress = Mage::getModel('sales/quote_address')
                        ->importCustomerAddress($defaultPatientAddress);
                } else {
                    $patientAddress = Mage::getModel('sales/quote_address');
                }
            }
            $this->setPatientAddress($patientAddress);
        }

        return $this;
    }

    /**
     * Retrieve quote patient address
     *
     * @return Mage_Sales_Model_Quote_Address
     */
    public function getPatientAddress()
    {
        return $this->_getAddressByType('patient');
    }
    public function setPatientAddress(Mage_Sales_Model_Quote_Address $address)
    {
    	$old = $this->getPatientAddress();
    
    	if (!empty($old)) {
    		$old->addData($address->getData());
    	} else {
    		$this->addAddress($address->setAddressType('patient'));
    	}
    	return $this;
    }

    /**
     * Merge quotes
     *
     * @param   Mage_Sales_Model_Quote $quote
     * @return  Mage_Sales_Model_Quote
     */
    public function merge(Mage_Sales_Model_Quote $quote)
    {
        Mage::dispatchEvent(
            $this->_eventPrefix . '_merge_before',
            array(
                 $this->_eventObject=>$this,
                 'source'=>$quote
            )
        );

        foreach ($quote->getAllVisibleItems() as $item) {
            $found = false;
            foreach ($this->getAllItems() as $quoteItem) {
                if ($quoteItem->compare($item)) {
                    $quoteItem->setQty($quoteItem->getQty() + $item->getQty());
                    $found = true;
                    break;
                }
            }

            if (!$found) {
                $newItem = clone $item;
                $this->addItem($newItem);
                if ($item->getHasChildren()) {
                    foreach ($item->getChildren() as $child) {
                        $newChild = clone $child;
                        $newChild->setParentItem($newItem);
                        $this->addItem($newChild);
                    }
                }
            }
        }

        /**
         * Init shipping and billing address if quote is new
         */
        if (!$this->getId()) {
            $this->getShippingAddress();
            $this->getBillingAddress();
            $this->getPatientAddress();
        }

        if ($quote->getCouponCode()) {
            $this->setCouponCode($quote->getCouponCode());
        }

        Mage::dispatchEvent(
            $this->_eventPrefix . '_merge_after',
            array(
                 $this->_eventObject=>$this,
                 'source'=>$quote
            )
        );

        return $this;
    }
}
