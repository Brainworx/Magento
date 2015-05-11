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
					Mage::helper('sales')->__('Invoice Date: ') .
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
	/**
	 * overridde Insert totals to pdf page to round the total
	 *
	 * @param  Zend_Pdf_Page $page
	 * @param  Mage_Sales_Model_Abstract $source
	 * @return Zend_Pdf_Page
	 */
	protected function insertTotals($page, $source){
		$order = $source->getOrder();
		$totals = $this->_getTotalsList($source);
		$lineBlock = array(
				'lines'  => array(),
				'height' => 15
		);
		foreach ($totals as $total) {
			$total->setOrder($order)
			->setSource($source);
	
			if ($total->canDisplay()) {
				$total->setFontSize(10);
				//hierin komt label BE-04(6.0000%): t array 7 title BE-06 percent 6.0000 amount en label bE06(6.0000%)
				//Mage_Sales_Model_Order_Pdf_Total_Default
				//public function getFullTaxInfo()
				foreach ($total->getTotalsForDisplay() as $totalData) {
					$lineBlock['lines'][] = array(
							array(
									'text'      => $totalData['label'],
									'feed'      => 475,
									'align'     => 'right',
									'font_size' => $totalData['font_size'],
									'font'      => 'bold'
							),
							array(
									'text'      => $totalData['amount'],
									'feed'      => 565,
									'align'     => 'right',
									'font_size' => $totalData['font_size'],
									'font'      => 'bold'
							),
					);
				}
			}
		}
	
		$this->y -= 20;
		$page = $this->drawLineBlocks($page, array($lineBlock));
		return $page;
	}
	
	/**
	 * insert the invoice date in at the top of the page
	 * @param Zend_Pdf_Page $page
	 * @param unknown $text
	 */
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
		$startY = 80;
		$columnWidth = 165; //120
		$lineY = 12;
	
		$name = Mage::getStoreConfig('general/store_information/name');
		// Footer title
		$page->setFillColor(new Zend_Pdf_Color_GrayScale(0.25));//0.25
	
		//$text = "We danken je voor het vertrouwen in ".$name.".";
		$text = Mage::helper('sales')->__('Thank you for trusting ').$name."."; 
	
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
	protected function insertOrder(&$page, $obj, $putOrderId = true)
	{
		if ($obj instanceof Mage_Sales_Model_Order) {
			$shipment = null;
			$order = $obj;
		} elseif ($obj instanceof Mage_Sales_Model_Order_Shipment) {
			$shipment = $obj;
			$order = $shipment->getOrder();
		}
	
		$this->y = $this->y ? $this->y : 815;
		$top = $this->y;
	
		$page->setFillColor(new Zend_Pdf_Color_GrayScale(0.45));
		$page->setLineColor(new Zend_Pdf_Color_GrayScale(0.45));
		$page->drawRectangle(25, $top, 570, $top - 55);
		$page->setFillColor(new Zend_Pdf_Color_GrayScale(1));
		$this->setDocHeaderCoordinates(array(25, $top, 570, $top - 55));
		$this->_setFontRegular($page, 10);
	
		if ($putOrderId) {
			$page->drawText(
					Mage::helper('sales')->__('Order # ') . $order->getRealOrderId(), 35, ($top -= 30), 'UTF-8'
			);
		}
		//SHE move order date to right side
		$page->drawText(
				Mage::helper('sales')->__('Order Date: ') . Mage::helper('core')->formatDate(
						$order->getCreatedAtStoreDate(), 'medium', false
				),
				450,//35
				($top),//15
				'UTF-8'
		);
	
		$top -= 10;
		$page->setFillColor(new Zend_Pdf_Color_Rgb(0.93, 0.92, 0.92));
		$page->setLineColor(new Zend_Pdf_Color_GrayScale(0.5));
		$page->setLineWidth(0.5);
		$page->drawRectangle(25, $top, 275, ($top - 25));
		$page->drawRectangle(275, $top, 570, ($top - 25));
	
		/* Calculate blocks info */
	
		/* Billing Address */
		$billingAddress = $this->_formatAddress($order->getBillingAddress()->format('pdf'));
	
		/* Payment */
		$paymentInfo = Mage::helper('payment')->getInfoBlock($order->getPayment())
		->setIsSecureMode(true)
		->toPdf();
		$paymentInfo = htmlspecialchars_decode($paymentInfo, ENT_QUOTES);
		$payment = explode('{{pdf_row_separator}}', $paymentInfo);
		foreach ($payment as $key=>$value){
			if (strip_tags(trim($value)) == '') {
				unset($payment[$key]);
			}
		}
		reset($payment);
	
		/* Shipping Address and Method */
		if (!$order->getIsVirtual()) {
			/* Shipping Address */
			$shippingAddress = $this->_formatAddress($order->getShippingAddress()->format('pdf'));
			$shippingMethod  = $order->getShippingDescription();
		}
	
		$page->setFillColor(new Zend_Pdf_Color_GrayScale(0));
		$this->_setFontBold($page, 12);
		//SHE override position of billingaddress to rigth
		$page->drawText(Mage::helper('sales')->__('Sold to:'), 285, ($top - 15), 'UTF-8');
	
		if (!$order->getIsVirtual()) {
			$page->drawText(Mage::helper('sales')->__('Ship to:'), 35, ($top - 15), 'UTF-8');
		} else {
			$page->drawText(Mage::helper('sales')->__('Payment Method:'), 285, ($top - 15), 'UTF-8');
		}
	
		$addressesHeight = $this->_calcAddressHeight($billingAddress);
		if (isset($shippingAddress)) {
			$addressesHeight = max($addressesHeight, $this->_calcAddressHeight($shippingAddress));
		}
	
		$page->setFillColor(new Zend_Pdf_Color_GrayScale(1));
		$page->drawRectangle(25, ($top - 25), 570, $top - 33 - $addressesHeight);
		$page->setFillColor(new Zend_Pdf_Color_GrayScale(0));
		$this->_setFontRegular($page, 10);
		$this->y = $top - 40;
		$addressesStartY = $this->y;
	
		foreach ($billingAddress as $value){
			if ($value !== '') {
				$text = array();
				foreach (Mage::helper('core/string')->str_split($value, 45, true, true) as $_value) {
					$text[] = $_value;
				}
				foreach ($text as $part) {
					$page->drawText(strip_tags(ltrim($part)), 35, $this->y, 'UTF-8');
					$this->y -= 15;
				}
			}
		}
	
		$addressesEndY = $this->y;
	
		if (!$order->getIsVirtual()) {
			$this->y = $addressesStartY;
			foreach ($shippingAddress as $value){
				if ($value!=='') {
					$text = array();
					foreach (Mage::helper('core/string')->str_split($value, 45, true, true) as $_value) {
						$text[] = $_value;
					}
					foreach ($text as $part) {
						$page->drawText(strip_tags(ltrim($part)), 285, $this->y, 'UTF-8');
						$this->y -= 15;
					}
				}
			}
	
			$addressesEndY = min($addressesEndY, $this->y);
			$this->y = $addressesEndY;
	
			$page->setFillColor(new Zend_Pdf_Color_Rgb(0.93, 0.92, 0.92));
			$page->setLineWidth(0.5);
			$page->drawRectangle(25, $this->y, 275, $this->y-25);
			$page->drawRectangle(275, $this->y, 570, $this->y-25);
	
			$this->y -= 15;
			$this->_setFontBold($page, 12);
			$page->setFillColor(new Zend_Pdf_Color_GrayScale(0));
			$page->drawText(Mage::helper('sales')->__('Payment Method'), 35, $this->y, 'UTF-8');
			$page->drawText(Mage::helper('sales')->__('Shipping Method:'), 285, $this->y , 'UTF-8');
	
			$this->y -=10;
			$page->setFillColor(new Zend_Pdf_Color_GrayScale(1));
	
			$this->_setFontRegular($page, 10);
			$page->setFillColor(new Zend_Pdf_Color_GrayScale(0));
	
			$paymentLeft = 35;
			$yPayments   = $this->y - 15;
		}
		else {
			$yPayments   = $addressesStartY;
			$paymentLeft = 285;
		}
	
		foreach ($payment as $value){
			if (trim($value) != '') {
				//Printing "Payment Method" lines
				$value = preg_replace('/<br[^>]*>/i', "\n", $value);
				foreach (Mage::helper('core/string')->str_split($value, 45, true, true) as $_value) {
					$page->drawText(strip_tags(trim($_value)), $paymentLeft, $yPayments, 'UTF-8');
					$yPayments -= 15;
				}
			}
		}
	
		if ($order->getIsVirtual()) {
			// replacement of Shipments-Payments rectangle block
			$yPayments = min($addressesEndY, $yPayments);
			$page->drawLine(25,  ($top - 25), 25,  $yPayments);
			$page->drawLine(570, ($top - 25), 570, $yPayments);
			$page->drawLine(25,  $yPayments,  570, $yPayments);
	
			$this->y = $yPayments - 15;
		} else {
			$topMargin    = 15;
			$methodStartY = $this->y;
			$this->y     -= 15;
	
			foreach (Mage::helper('core/string')->str_split($shippingMethod, 45, true, true) as $_value) {
				$page->drawText(strip_tags(trim($_value)), 285, $this->y, 'UTF-8');
				$this->y -= 15;
			}
	
			$yShipments = $this->y;
			$totalShippingChargesText = "(" . Mage::helper('sales')->__('Total Shipping Charges') . " "
					. $order->formatPriceTxt($order->getShippingAmount()) . ")";
	
			$page->drawText($totalShippingChargesText, 285, $yShipments - $topMargin, 'UTF-8');
			$yShipments -= $topMargin + 10;
	
			$tracks = array();
			if ($shipment) {
				$tracks = $shipment->getAllTracks();
			}
			if (count($tracks)) {
				$page->setFillColor(new Zend_Pdf_Color_Rgb(0.93, 0.92, 0.92));
				$page->setLineWidth(0.5);
				$page->drawRectangle(285, $yShipments, 510, $yShipments - 10);
				$page->drawLine(400, $yShipments, 400, $yShipments - 10);
				//$page->drawLine(510, $yShipments, 510, $yShipments - 10);
	
				$this->_setFontRegular($page, 9);
				$page->setFillColor(new Zend_Pdf_Color_GrayScale(0));
				//$page->drawText(Mage::helper('sales')->__('Carrier'), 290, $yShipments - 7 , 'UTF-8');
				$page->drawText(Mage::helper('sales')->__('Title'), 290, $yShipments - 7, 'UTF-8');
				$page->drawText(Mage::helper('sales')->__('Number'), 410, $yShipments - 7, 'UTF-8');
	
				$yShipments -= 20;
				$this->_setFontRegular($page, 8);
				foreach ($tracks as $track) {
	
					$CarrierCode = $track->getCarrierCode();
					if ($CarrierCode != 'custom') {
						$carrier = Mage::getSingleton('shipping/config')->getCarrierInstance($CarrierCode);
						$carrierTitle = $carrier->getConfigData('title');
					} else {
						$carrierTitle = Mage::helper('sales')->__('Custom Value');
					}
	
					//$truncatedCarrierTitle = substr($carrierTitle, 0, 35) . (strlen($carrierTitle) > 35 ? '...' : '');
					$maxTitleLen = 45;
					$endOfTitle = strlen($track->getTitle()) > $maxTitleLen ? '...' : '';
					$truncatedTitle = substr($track->getTitle(), 0, $maxTitleLen) . $endOfTitle;
					//$page->drawText($truncatedCarrierTitle, 285, $yShipments , 'UTF-8');
					$page->drawText($truncatedTitle, 292, $yShipments , 'UTF-8');
					$page->drawText($track->getNumber(), 410, $yShipments , 'UTF-8');
					$yShipments -= $topMargin - 5;
				}
			} else {
				$yShipments -= $topMargin - 5;
			}
	
			$currentY = min($yPayments, $yShipments);
	
			// replacement of Shipments-Payments rectangle block
			$page->drawLine(25,  $methodStartY, 25,  $currentY); //left
			$page->drawLine(25,  $currentY,     570, $currentY); //bottom
			$page->drawLine(570, $currentY,     570, $methodStartY); //right
	
			$this->y = $currentY;
			$this->y -= 15;
		}
	}
}