<?php
/**
 * Magento
 *
 * 
 * @category    Mage
 * @package     Mage_Sales
 * @author      Magento Core Team <core@magentocommerce.com>
 */
class Brainworx_Hearedfrom_Model_Order extends Mage_Sales_Model_Order
{
    

    /**
     * Retrieve quote patient address
     *
     * @return Mage_Sales_Model_Quote_Address
     */
    public function getPatientAddress()
    {
        foreach ($this->getAddressesCollection() as $address) {
            if ($address->getAddressType()=='patient' && !$address->isDeleted()) {
                return $address;
            }
        }
        return false;
    }
    public function setPatientAddress(Mage_Sales_Model_Order_Address $address)
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
     * Queue email with order update information
     *
     * @param boolean $notifyCustomer
     * @param string $comment
     * @param bool $forceMode if true then email will be sent regardless of the fact that it was already sent previously
     *
     * @return Mage_Sales_Model_Order
     */
    public function queueOrderUpdateEmail($notifyCustomer = true, $comment = '', $forceMode = false)
    {
    	$storeId = $this->getStore()->getId();
    
    	if (!Mage::helper('sales')->canSendOrderCommentEmail($storeId)) {
    		return $this;
    	}
    	// Get the destination email addresses to send copies to
    	$copyTo = $this->_getEmails(self::XML_PATH_UPDATE_EMAIL_COPY_TO);
    	$copyMethod = Mage::getStoreConfig(self::XML_PATH_UPDATE_EMAIL_COPY_METHOD, $storeId);
    	// Check if at least one recipient is found
    	if (!$notifyCustomer && !$copyTo) {
    		return $this;
    	}
    
    	// Retrieve corresponding email template id and customer name
    	if ($this->getCustomerIsGuest()) {
    		$templateId = Mage::getStoreConfig(self::XML_PATH_UPDATE_EMAIL_GUEST_TEMPLATE, $storeId);
    		$customerName = $this->getPatientAddress()->getName();
    	} else {
    		$templateId = Mage::getStoreConfig(self::XML_PATH_UPDATE_EMAIL_TEMPLATE, $storeId);
    		$customerName = $this->getCustomerName();
    	}
    
    	/** @var $mailer Mage_Core_Model_Email_Template_Mailer */
    	$mailer = Mage::getModel('core/email_template_mailer');
    	if ($notifyCustomer) {
    		/** @var $emailInfo Mage_Core_Model_Email_Info */
    		$emailInfo = Mage::getModel('core/email_info');
    		$emailInfo->addTo($this->getCustomerEmail(), $customerName);
    		if ($copyTo && $copyMethod == 'bcc') {
    			// Add bcc to customer email
    			foreach ($copyTo as $email) {
    				$emailInfo->addBcc($email);
    			}
    		}
    		$mailer->addEmailInfo($emailInfo);
    	}
    
    	// Email copies are sent as separated emails if their copy method is
    	// 'copy' or a customer should not be notified
    	if ($copyTo && ($copyMethod == 'copy' || !$notifyCustomer)) {
    		foreach ($copyTo as $email) {
    			$emailInfo = Mage::getModel('core/email_info');
    			$emailInfo->addTo($email);
    			$mailer->addEmailInfo($emailInfo);
    		}
    	}
    
    	// Set all required params and send emails
    	$mailer->setSender(Mage::getStoreConfig(self::XML_PATH_UPDATE_EMAIL_IDENTITY, $storeId));
    	$mailer->setStoreId($storeId);
    	$mailer->setTemplateId($templateId);
    	$mailer->setTemplateParams(array(
    			'order'   => $this,
    			'comment' => $comment,
    			'billing' => $this->getBillingAddress()
    	)
    	);
    
    	/** @var $emailQueue Mage_Core_Model_Email_Queue */
    	$emailQueue = Mage::getModel('core/email_queue');
    	$emailQueue->setEntityId($this->getId())
    	->setEntityType(self::ENTITY)
    	->setEventType(self::EMAIL_EVENT_NAME_UPDATE_ORDER)
    	->setIsForceCheck(!$forceMode);
    	$mailer->setQueue($emailQueue)->send();
    
    	return $this;
    }
    
}
