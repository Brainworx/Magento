<?php
class Brainworx_Rental_Model_Order_Invoice extends Mage_Sales_Model_Order_Invoice
{
	const STATE_INCASSO      = 11;
	/*
	 * Overriding sendEmail in order to be able to force the sending of emails 
	 */
	public function sendEmail($notifyCustomer = true, $comment = '',$force = false)
	{
		$order = $this->getOrder();
		$storeId = $order->getStore()->getId();
	
		if (!$force && !Mage::helper('sales')->canSendNewInvoiceEmail($storeId)) {
			return $this;
		}
		
		if($force){
			Mage::log('Invoice '.$this->getIncrementId().' for order '.$order->getIncrementId().' sent via button on console.');
		}
		// Get the destination email addresses to send copies to
		$copyTo = $this->_getEmails(self::XML_PATH_EMAIL_COPY_TO);
		$copyMethod = Mage::getStoreConfig(self::XML_PATH_EMAIL_COPY_METHOD, $storeId);
		// Check if at least one recepient is found
		if (!$notifyCustomer && !$copyTo) {
			return $this;
		}
	
		// Start store emulation process
		$appEmulation = Mage::getSingleton('core/app_emulation');
		$initialEnvironmentInfo = $appEmulation->startEnvironmentEmulation($storeId);
	
		try {
			// Retrieve specified view block from appropriate design package (depends on emulated store)
			$paymentBlock = Mage::helper('payment')->getInfoBlock($order->getPayment())
			->setIsSecureMode(true);
			$paymentBlock->getMethod()->setStore($storeId);
			$paymentBlockHtml = $paymentBlock->toHtml();
		} catch (Exception $exception) {
			// Stop store emulation process
			$appEmulation->stopEnvironmentEmulation($initialEnvironmentInfo);
			throw $exception;
		}
	
		// Stop store emulation process
		$appEmulation->stopEnvironmentEmulation($initialEnvironmentInfo);
	
		// Retrieve corresponding email template id and customer name
		if ($order->getCustomerIsGuest()) {
			$templateId = Mage::getStoreConfig(self::XML_PATH_EMAIL_GUEST_TEMPLATE, $storeId);
			$customerName = $order->getBillingAddress()->getName();
		} else {
			$templateId = Mage::getStoreConfig(self::XML_PATH_EMAIL_TEMPLATE, $storeId);
			$customerName = $order->getCustomerName();
		}
	
		$mailer = Mage::getModel('core/email_template_mailer');
		if ($notifyCustomer) {
			$emailInfo = Mage::getModel('core/email_info');
			$emailInfo->addTo($order->getCustomerEmail(), $customerName);
			if ($copyTo && $copyMethod == 'bcc') {
				// Add bcc to customer email
				foreach ($copyTo as $email) {
					$emailInfo->addBcc($email);
				}
			}
			$mailer->addEmailInfo($emailInfo);
		}
	
		// Email copies are sent as separated emails if their copy method is 'copy' or a customer should not be notified
		if ($copyTo && ($copyMethod == 'copy' || !$notifyCustomer)) {
			foreach ($copyTo as $email) {
				$emailInfo = Mage::getModel('core/email_info');
				$emailInfo->addTo($email);
				$mailer->addEmailInfo($emailInfo);
			}
		}
	
		// Determin seller
		$seller='';
		$sellerObject = Mage::getModel("hearedfrom/salesSeller")->loadByOrderId($order->getIncrementId());
		if(!empty($sellerObject)){
			$seller = Mage::getModel("hearedfrom/salesForce")->load($sellerObject['user_id'])->getData("user_nm");
		}
		if(empty($seller) || $seller == 'Zorgpunt'){
			$seller = '';
		}	
		// Set all required params and send emails
		$mailer->setSender(Mage::getStoreConfig(self::XML_PATH_EMAIL_IDENTITY, $storeId));
		$mailer->setStoreId($storeId);
		$mailer->setTemplateId($templateId);
		$mailer->setTemplateParams(array(
				'order'        => $order,
				'invoice'      => $this,
				'comment'      => $comment,
				'billing'      => $order->getBillingAddress(),
				'payment_html' => $paymentBlockHtml,
				'seller'	   => $seller,
				'invoicedt'	   => Mage::helper('core')->formatDate($this->getCreatedAt(), 'medium', false)
		)
		);
		$mailer->send();
		$this->setEmailSent(true);
		$this->_getResource()->saveAttribute($this, 'email_sent');
	
		return $this;
	}
	
	/**
	 * Retrieve invoice states array
	 *
	 * @return array
	 */
	public static function getStates()
	{
		
		if (is_null(self::$_states)) {
			self::$_states = array(
					self::STATE_OPEN       => Mage::helper('sales')->__('Pending'),
					self::STATE_INCASSO       => Mage::helper('sales')->__('Incasso'),
					self::STATE_PAID       => Mage::helper('sales')->__('Paid'),
					self::STATE_CANCELED   => Mage::helper('sales')->__('Canceled'),
			);
		}
		return self::$_states;
	}
	/**
	 * Retrieve invoice state name by state identifier
	 *
	 * @param   int $stateId
	 * @return  string
	 */
	public function getStateName($stateId = null)
	{
		 
		if (is_null($stateId)) {
			$stateId = $this->getState();
		}
	
		if (is_null(self::$_states)) {
			self::getStates();
		}
		if (isset(self::$_states[$stateId])) {
			return self::$_states[$stateId];
		}
		return Mage::helper('sales')->__('Unknown State');
	}

	
}