<?php
class Brainworx_Rental_Model_Pdf_Invoice extends Mage_Sales_Model_Order_Pdf_Invoice
{
	//TODO add translation
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
					Mage::helper('sales')->__('Invoice Date ') .
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
			/*SHE add footer*/
			$this->insertFooter($page, $invoice->getStore());
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
	/**
	 * Insert footer
	 */
	private function insertFooter(&$page, $store = null) {
		//$this->_setFontBold($page);
		$this->_setFontRegular($page, 10);
	
		$startX = 50;
		$startY = 100;//80
		$columnWidth = 125;
		$lineY = 12;
	
		$name = Mage::getStoreConfig('general/store_information/name');
		// Footer title
		$page->setFillColor(new Zend_Pdf_Color_GrayScale(0.25));//0.25
	
		$text = "We danken je voor het vertrouwen in ".$name.".";
	
		$page->drawText($text, $startX, $startY, 'UTF-8');
	
		$startY = $startY - $lineY - $lineY;
	
		// Columns
		$page->setFillColor(new Zend_Pdf_Color_GrayScale(0.25));//0.5
		
		//Above
		$x = $startX;
		$y = $startY + 80;
		
		$this->_setFontBold($page,10);
		$page->drawText('Gelieve het saldo binnen de 10 dagen te betalen op bankrekening:', $x, $y, 'UTF-8');
		$y -= $lineY;
		$iban =  Mage::getModel('core/variable')->setStoreId(Mage::app()->getStore()->getId())->loadByCode('IBAN')->getValue('text');
		$page->drawText('IBAN '.$iban, $x, $y, 'UTF-8');
		$y -= $lineY;
		$bicc =  Mage::getModel('core/variable')->setStoreId(Mage::app()->getStore()->getId())->loadByCode('BICC')->getValue('text');		
		$page->drawText('BIC '.$bicc, $x, $y, 'UTF-8');
		$y -= $lineY;
		$page->drawText('OMG/mededeling: factuur #', $x, $y, 'UTF-8');
		
		
		// Column 1
		$x = $startX;
		$y = $startY;
	
		 
		$page->drawText($name, $x, $y, 'UTF-8');
		$y -= $lineY;
		$page->drawText(Mage::getStoreConfig('general/store_information/address'), $x, $y, 'UTF-8');
		$y -= $lineY;
		$page->drawText('BTW '.Mage::getStoreConfig('general/store_information/merchant_vat_number'), $x, $y, 'UTF-8');
		
		//$page->drawText('3360 Bierbeeck', $x, $y, 'UTF-8');
	
		// Column 2
		$x += $columnWidth;
		$y = $startY;
	
		$page->drawText(Mage::getStoreConfig('general/store_information/phone'), $x, $y, 'UTF-8');
		$y -= $lineY;
		 
		$page->drawText(Mage::getStoreConfig('trans_email/ident_general/email'), $x, $y, 'UTF-8');
		$y -= $lineY;
		 
		$page->drawText(Mage::getStoreConfig('web/unsecure/base_url'), $x, $y, 'UTF-8');
	
// 		// Column 3
// 		$x += $columnWidth;
// 		$y = $startY;
	
// 		//$this->_setFontBold($page,10);
// 		$page->drawText('Gelieve het saldo binnen de 10 dagen te betalen op onderstaande bankrekening.', $x, $y, 'UTF-8');
// 		$y -= $lineY;
// 		$page->drawText('IBAN BE12 1234 1234 1234', $x, $y, 'UTF-8');
// 		$y -= $lineY;
// 		$page->drawText('BIC BEDDCCEE', $x, $y, 'UTF-8');
	
		// Column 3
// 		$x += $columnWidth;
// 		$y = $startY;
	
// 		$page->drawText($store->getFrontendName(), $x, $y, 'UTF-8');
	}
}