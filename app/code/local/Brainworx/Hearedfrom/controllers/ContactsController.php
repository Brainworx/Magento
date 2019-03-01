<?php
/**
 * Magento override of Mage_Contacts
*
* @category    Brainworx
* @package     Brainworx_Hearedfrom
* @copyright  Copyright (c) Brainworx.be
* @license    http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
*/

/**
 * Contacts index controller Mage_Contacts_IndexController
*
* @category    Brainworx
* @package     Brainworx_Hearedfrom
* @author      info@brainworx.be
*/

require_once 'Mage/Contacts/controllers/IndexController.php';

class Brainworx_Hearedfrom_ContactsController extends Mage_Contacts_IndexController
{
	public function postAction()
	{
		$post = $this->getRequest()->getPost();
		if ( $post ) {
			Mage::log("In contact form");
			//BOTTRAP: check bot submit
			$_bot = $this->getRequest()->getPost('terms');
			if(isset($_bot)){
				Mage::getSingleton('customer/session')->addSuccess(Mage::helper('contacts')->__('Your inquiry was submitted.'));
				Mage::log("Bot Trap - default contact form");
				$this->_redirectSuccess(Mage::getUrl('', array('_secure'=>true)));
				return;
			}
			 
			$translate = Mage::getSingleton('core/translate');
			/* @var $translate Mage_Core_Model_Translate */
			$translate->setTranslateInline(false);
			try {
				$postObject = new Varien_Object();
				$postObject->setData($post);

				$error = false;

				if (!Zend_Validate::is(trim($post['name']) , 'NotEmpty')) {
					$error = true;
				}

				if (!Zend_Validate::is(trim($post['comment']) , 'NotEmpty')) {
					$error = true;
				}

				if (!Zend_Validate::is(trim($post['email']), 'EmailAddress')) {
					$error = true;
				}

				if (Zend_Validate::is(trim($post['hideit']), 'NotEmpty')) {
					$error = true;
				}

				if ($error) {
					throw new Exception();
				}
				$mailTemplate = Mage::getModel('core/email_template');
				/* @var $mailTemplate Mage_Core_Model_Email_Template */
				$mailTemplate->setDesignConfig(array('area' => 'frontend'))
				->setReplyTo($post['email'])
				->sendTransactional(
						Mage::getStoreConfig(self::XML_PATH_EMAIL_TEMPLATE),
						Mage::getStoreConfig(self::XML_PATH_EMAIL_SENDER),
						Mage::getStoreConfig(self::XML_PATH_EMAIL_RECIPIENT),
						null,
						array('data' => $postObject)
				);

				if (!$mailTemplate->getSentSuccess()) {
					throw new Exception();
				}

				$translate->setTranslateInline(true);

				Mage::getSingleton('customer/session')->addSuccess(Mage::helper('contacts')->__('Your inquiry was submitted and will be responded to as soon as possible. Thank you for contacting us.'));
				$this->_redirect('*/*/');

				return;
			} catch (Exception $e) {
				$translate->setTranslateInline(true);

				Mage::getSingleton('customer/session')->addError(Mage::helper('contacts')->__('Unable to submit your request. Please, try again later'));
				$this->_redirect('*/*/');
				return;
			}

		} else {
			$this->_redirect('*/*/');
		}
	}

}
