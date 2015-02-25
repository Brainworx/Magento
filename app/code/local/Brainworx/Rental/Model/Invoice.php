<?php
class Brainworx_Rental_Model_Invoice extends Mage_Sales_Model_Order_Pdf_Invoice
{
	public function getPdf($invoices = array())
	{
		$this->_beforeGetPdf();
		$this->_initRenderer('invoice');
		
		$pdf = new Zend_Pdf();
		$this->_setPdf($pdf);
		$style = new Zend_Pdf_Style();
		$this->_setFontBold($style, 10);
		
		foreach ($invoices as $invoice) {
			if ($invoice->getStoreId()) {
				Mage::app()->getLocale()->emulate($invoice->getStoreId());
				Mage::app()->setCurrentStore($invoice->getStoreId());
			}
			$page  = $this->newPage();
			$order = $invoice->getOrder();
			/* Add image */
			$this->insertLogo($page, $invoice->getStore());
			/* Add address */
			$this->insertAddress($page, $invoice->getStore());
			/* Add head */
			$this->insertOrder(
					$page,
					$order,
					Mage::getStoreConfigFlag(self::XML_PATH_SALES_PDF_INVOICE_PUT_ORDER_ID, $order->getStoreId())
			);
			/* Add document text and number */
			$this->insertDocumentNumber(
					$page,
					Mage::helper('sales')->__('Invoice # ') . $invoice->getIncrementId()
			);
			/* Add invoice date */
			$this->insertInvoiceDate($page, 
					Mage::helper('rental')->__('Invoice Date ') .
					Mage::helper('core')->formatDate($invoice->getCreatedAt(), 'medium', false));
								/* Add table */
			$this->_drawHeader($page);
			/* Add body */
			foreach ($invoice->getAllItems() as $item){
				if ($item->getOrderItem()->getParentItem()) {
					continue;
				}
				/* Draw item */
				$this->_drawItem($item, $page, $order);
				$page = end($pdf->pages);
			}
			/* Add totals */
			$this->insertTotals($page, $invoice);
			if ($invoice->getStoreId()) {
				Mage::app()->getLocale()->revert();
			}
		}
		$this->_afterGetPdf();
		return $pdf;
	}
	public function insertInvoiceDate(Zend_Pdf_Page $page, $text)
	{
		$page->setFillColor(new Zend_Pdf_Color_GrayScale(1));
		$this->_setFontRegular($page, 10);
		$docHeader = $this->getDocHeaderCoordinates();
		$page->drawText($text, 450, $docHeader[1] - 15, 'UTF-8');
	}
}