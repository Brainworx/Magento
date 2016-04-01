<?php
require_once 'Mage/Adminhtml/controllers/Sales/Order/InvoiceController.php';
class Brainworx_Rental_Adminhtml_Sales_Order_InvoiceController  extends Mage_Adminhtml_Sales_Order_InvoiceController
{	
	/**
	 * Override emailaction so email from button action get sent even when email are not sent automatically
	 * Notify user override
	 */
	public function emailAction()
	{
		if ($invoiceId = $this->getRequest()->getParam('invoice_id')) {
			if ($invoice = Mage::getModel('sales/order_invoice')->load($invoiceId)) {
				//forcing the store to send the mail
				$invoice->sendEmail(true,'',true);
				$historyItem = Mage::getResourceModel('sales/order_status_history_collection')
				->getUnnotifiedForInstance($invoice, Mage_Sales_Model_Order_Invoice::HISTORY_ENTITY_NAME);
				if ($historyItem) {
					$historyItem->setIsCustomerNotified(1);
					$historyItem->save();
				}
				$this->_getSession()->addSuccess(Mage::helper('sales')->__('The message has been sent.'));
				$this->_redirect('*/sales_invoice/view', array(
						'order_id'  => $invoice->getOrder()->getId(),
						'invoice_id'=> $invoiceId,
				));
			}
		}
	}
}