<?php
class Brainworx_Rental_Model_Session extends Mage_Checkout_Model_Session {

	/**
	 * Clears old cart items after login
	 *
	 * @return object currently added cart items
	 */
	public function loadCustomerQuote() {
		
		if (!Mage::getSingleton('customer/session')->getCustomerId()) {
			return $this;
		}
		
		Mage::dispatchEvent('load_customer_quote_before', array('checkout_session' => $this));
		
		$customerQuote = Mage::getModel('sales/quote')
		->setStoreId(Mage::app()->getStore()->getId())
		->loadByCustomer(Mage::getSingleton('customer/session')->getCustomerId());

		if ($customerQuote->getId() && $this->getQuoteId() != $customerQuote->getId()) {
			// Removing old cart items of the customer.
			//Instead of merging the old cart items, we remove them and keep current card only
			foreach ($customerQuote->getAllItems() as $item) {
				$item->isDeleted(true);
				if ($item->getHasChildren()) {
					foreach ($item->getChildren() as $child) {
						$child->isDeleted(true);
					}
				}
			}
			
			if ($this->getQuoteId()) {
				$customerQuote->merge($this->getQuote())
				->collectTotals()
				->save();
			}else{
				$customerQuote->collectTotals()->save();
			}
			
			$this->setQuoteId($customerQuote->getId());
			
			if ($this->_quote) {
				$this->_quote->delete();
			}
			$this->_quote = $customerQuote;
			
		} else {

			$this->getQuote()->getBillingAddress();
			$this->getQuote()->getShippingAddress();
			$this->getQuote()->setCustomer(Mage::getSingleton('customer/session')->getCustomer())
			->setTotalsCollectedFlag(false)
			->collectTotals()
			->save();
		}
		return $this;
	}

}
?>