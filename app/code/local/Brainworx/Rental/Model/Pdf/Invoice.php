<?php
class Brainworx_Rental_Model_Pdf_Invoice extends Mage_Sales_Model_Order_Pdf_Invoice
{
	public $euro = " ";
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
			$tempYstartTotals = $this->y;
			$this->insertTotals($page, $invoice);
			if ($invoice->getStoreId()) {
				Mage::app()->getLocale()->revert();
			}
			$tempYstopTotals = $this->y;
			
			//Add invoice comments to pdf
			//use drawlineblock for this
			$clineBlock = array(
					'lines'  => array(),
					'height' => 15
			);
			$this->_setFontRegular($page, 10);
			$page->setFillColor(new Zend_Pdf_Color_GrayScale(0.25));//0.25
			$order = $invoice->getOrder();
			$orderComment = null;
			if(!empty($order->getPatientName()))
				$orderComment ='Patiëntgegevens'.': '. $order->getPatientFirstname().' '.$order->getPatientName() . "<br>";
			if(!empty($order->getPatientBirthDate())){
				$birthdatetext = Mage::helper('sales')->__('BirthDate Patient:').Mage::helper('core')->formatDate($order->getPatientBirthDate(), 'medium', false);
				$orderComment = $orderComment.$birthdatetext.'<br>';
			}
			foreach ($invoice->getCommentsCollection() as $comment) {
				$orderComment = $orderComment . $comment->getComment();
				$orderComment = $orderComment . "<br>";
			}
			if(!empty($orderComment)){
				$comments = explode("<br>",$orderComment);
				foreach($comments as $c){
					$clineBlock['lines'][] = array(array('text'      => $c,
									'feed'      => 50,
									'align'     => 'left')	
					);
				}		
				//put comment on same heigth as totals
				$this->y=$tempYstartTotals - 20;
				$page = $this->drawLineBlocks($page, array($clineBlock));
			}
			//make sure the next block doesn't overlap totals or comments
			// higher number is lower on the page
			if($tempYstopTotals < $this->y){
				$this->y = $tempYstopTotals;
			}
			$this->y -= 20;
			//end add comment
			/*SHE add hearedfrom */
			$seller = Mage::getModel("hearedfrom/salesSeller")->loadByOrderId($order->getIncrementId());
			$sellerName = Mage::getModel("hearedfrom/salesForce")->load($seller['user_id'])->getData("user_nm");
							
			
			/*SHE add footer*/
	        //use drawlineblock for this
	        $ogm = $invoice->getOgm();
	        if(empty($ogm)){
	        	$ogm = $invoice->getIncrementId()-100000000;
	        }
			$this->insertFooter($page, $invoice->getStore(),$ogm,$sellerName);
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
		$last_key = count($totals)-1;
		foreach ($totals as $key=>$total) {
			$total->setOrder($order)
			->setSource($source);
	
			if ($total->canDisplay()) {
				$total->setFontSize(10);
				//hierin komt label BE-04(6.0000%): t array 7 title BE-06 percent 6.0000 amount en label bE06(6.0000%)
				//Mage_Sales_Model_Order_Pdf_Total_Default
				//public function getFullTaxInfo()
				
				$size;
				$last_key_2 = count($total->getTotalsForDisplay())-1;
				$label = "";
				foreach ($total->getTotalsForDisplay() as $key2=>$totalData) {
					$size = $totalData['font_size'];
					if($last_key === $key && $last_key_2 === $key2){
						$size = 14;
						$this->euro = $totalData['amount'].' ';
					}
					$label = $totalData['label'];
					if($label == "Subtotaal:"){
						continue;
					}elseif ($label=="Verzending:"){
						$label = $order->getShippingDescription().':';
					}
					$this->totalsh += 15;
					$lineBlock['lines'][] = array(
							array(
									'text'      => $label,
									'feed'      => 475,
									'align'     => 'right',
									'font_size' => $size,
									'font'      => 'bold'
							),
							array(
									'text'      => $totalData['amount'],
									'feed'      => 565,
									'align'     => 'right',
									'font_size' => $size,
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
		$page->drawText($text, 443, $docHeader[1] - 15, 'UTF-8');
	}
	/**
	 * Insert footer
	 */
	private function insertFooter(&$page, $store = null, $invnr = "#", $sellerNm = null) {
		$flineBlock = array(
				'lines'  => array(),
				'height' => 15
		);
		$linesContent = array();
		
		$this->_setFontRegular($page, 10);
	
		$name = Mage::getStoreConfig('general/store_information/name');
		// Footer title and rist block*******************************************************
		$page->setFillColor(new Zend_Pdf_Color_GrayScale(0.25));//0.25
		
		$this->_setFontBold($page,10);
		$linesContent[]='TOTAAL van '.$this->euro.' te betalen binnen 10 dagen na ontvangst van de factuur.';
		$iban =  Mage::getModel('core/variable')->setStoreId(Mage::app()->getStore()->getId())->loadByCode('IBAN')->getValue('text');
		$bicc =  Mage::getModel('core/variable')->setStoreId(Mage::app()->getStore()->getId())->loadByCode('BICC')->getValue('text');		
		$linesContent[]='op rekening '.$iban.' '.$bicc;
		$linesContent[]='met mededeling: '.$invnr;
		//write first block 
		foreach($linesContent as $c){
			$flineBlock['lines'][] = array(array('text'      => $c,
					'feed'      => 50,
					'align'     => 'left',
					'font' 		=> 'bold'
			)
			);
		}
		$this->y -= 20;
		$page = $this->drawLineBlocks($page, array($flineBlock));
		
		
		//footnote *******************************************************************
		$flineBlock = array(
				'lines'  => array(),
				'height' => 15
		);
		$linesContent = array();
		if($sellerNm != null && $sellerNm != 'Zorgpunt'){
			$sellerNm = ' '.$sellerNm.'.';
		}else{
			$sellerNm = '.';
		}
		
		$linesContent[] = Mage::helper('sales')->__('Thank you for trusting ').$name.$sellerNm;
		foreach($linesContent as $c){
			$flineBlock['lines'][] = array(array('text'      => $c,
					'feed'      => 50,
					'align'     => 'center')
			);
		}
		$this->y -= 30;//20->30 update 3/5/2017
		$page = $this->drawLineBlocks($page, array($flineBlock));
		//end footnote
		
		
		
		//legal footnote *******************************************************************
		// update 3/5/2017
		$this->y -= 25;
		
		$flineBlock = array(
				'lines'  => array(),
				'height' => 10,
		);
		$linesContent = array();
		
		$linesContent[]=Mage::helper('rental')->__('Onze facturen zijn contant betaalbaar.');
		$linesContent[]=Mage::helper('rental')->__('In geval van laattijdige betaling zijn van rechtswege en zonder ingebrekestelling verwijlintresten van 12%');
		$linesContent[]=Mage::helper('rental')->__('vanaf factuurdatum en een forfaitaire schadevergoeding van 15% met een minimum van 50,00 euro verschuldigd.');
		$linesContent[]=Mage::helper('rental')->__('Bovendien vervallen dan alle betalingstermijnen ook voor alle andere facturen.');
		$linesContent[]=Mage::helper('rental')->__('Alle betwistingen omtrent onze facturen dienen binnen de 8 dagen na ontvangst van de factuur');
		$linesContent[]=Mage::helper('rental')->__('schriftelijk ter kennis gebracht te worden.');
		$linesContent[]=Mage::helper('rental')->__('Voor elke geschil zijn allen de rechtbanken van Leuven bevoegd.');
		
		foreach($linesContent as $c){
			$flineBlock['lines'][] = array(array('text'      => $c,
					'feed'      => 50,
					'font_size' => 8,
					'font'		=> 'italic'
			)
			);
		}
		$page = $this->drawLineBlocks($page, array($flineBlock));
		
		// write first column second block********************************************************
		//init
		
		$this->insertFooterLogo($page);
		
		//end footnote
		
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
		$patientBirthdate = $order->getPatientBirthDate();
	
		/* Billing Address */
		$billingAddress = $this->_formatAddress($order->getBillingAddress()->format('pdf'));
	
// 		/* Payment */
// 		$paymentInfo = Mage::helper('payment')->getInfoBlock($order->getPayment())
// 		->setIsSecureMode(true)
// 		->toPdf();
// 		$paymentInfo = htmlspecialchars_decode($paymentInfo, ENT_QUOTES);
// 		$payment = explode('{{pdf_row_separator}}', $paymentInfo);
// 		foreach ($payment as $key=>$value){
// 			if (strip_tags(trim($value)) == '') {
// 				unset($payment[$key]);
// 			}
// 		}
// 		reset($payment);
	
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
		} //else {
// 			$page->drawText(Mage::helper('sales')->__('Payment Method:'), 285, ($top - 15), 'UTF-8');
// 		}
	
		$addressesHeight = $this->_calcAddressHeight($billingAddress);
		if (isset($shippingAddress)) {
			$addressesHeight = max($addressesHeight, $this->_calcAddressHeight($shippingAddress));
			//add one line for birthdate
			if(!empty($patientBirthdate)){
				$addressesHeight += 15;
			}
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
					if($_value == ','){
						continue;
					}
					$text[] = $_value;
				}
				foreach ($text as $part) {
					//switched postition on pdf from 35 to 285
					$page->drawText(strip_tags(ltrim($part)), 285, $this->y, 'UTF-8');
					$this->y -= 15;
				}
			}
		}
		//draw patient birth date if available
// 		if(!empty($patientBirthdate)){
// 			$birthdatetext = Mage::helper('sales')->__('BirthDate Patient:').Mage::helper('core')->formatDate($patientBirthdate, 'medium', false);
// 			$page->drawText(strip_tags(ltrim(utf8_encode($birthdatetext))), 285, $this->y, 'UTF-8');
// 			$this->y -= 15;
// 		}
	
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
						if($part == ','){
							continue;
						}
						//switched postition on pdf from 285 to 35
						$page->drawText(strip_tags(ltrim($part)), 35, $this->y, 'UTF-8');
						$this->y -= 15;
					}
				}
			}
	
			$addressesEndY = min($addressesEndY, $this->y);
			$this->y = $addressesEndY;
		}
	}
	/**
	 * Insert logo to pdf page
	 *
	 * @param Zend_Pdf_Page $page
	 * @param null $store
	 */
	protected function insertLogo(&$page, $store = null)
	{
		$this->y = $this->y ? $this->y : 815;
		$image = Mage::getStoreConfig('sales/identity/logo', $store);
		if ($image) {
			$image = Mage::getBaseDir('media') . '/sales/store/logo/' . $image;
			if (is_file($image)) {
				$image       = Zend_Pdf_Image::imageWithPath($image);
				$top         = 830; //top border of the page
				$widthLimit  = 160; //SHE update so logo is smaller and invoice address get higher
				$heightLimit = 160; //SHE update so logo is smaller and invoice address get higher
				$width       = $image->getPixelWidth();
				$height      = $image->getPixelHeight();
	
				//preserving aspect ratio (proportions)
				$ratio = $width / $height;
				if ($ratio > 1 && $width > $widthLimit) {
					$width  = $widthLimit;
					$height = $width / $ratio;
				} elseif ($ratio < 1 && $height > $heightLimit) {
					$height = $heightLimit;
					$width  = $height * $ratio;
				} elseif ($ratio == 1 && $height > $heightLimit) {
					$height = $heightLimit;
					$width  = $widthLimit;
				}
	
				$y1 = $top - $height;
				$y2 = $top;
				$x1 = 25;
				$x2 = $x1 + $width;
	
				//coordinates after transformation are rounded by Zend
				$page->drawImage($image, $x1, $y1, $x2, $y2);
	
				$this->y = $y1 - 10;
			}
		}
	}
	/**
	 * Draw header for item table
	 *
	 * @param Zend_Pdf_Page $page
	 * @return void
	 */
	protected function _drawHeader(Zend_Pdf_Page $page)
	{
		/* Add table head */
		$this->_setFontRegular($page, 10);
		$page->setFillColor(new Zend_Pdf_Color_RGB(0.93, 0.92, 0.92));
		$page->setLineColor(new Zend_Pdf_Color_GrayScale(0.5));
		$page->setLineWidth(0.5);
		$page->drawRectangle(25, $this->y, 570, $this->y -15);
		$this->y -= 10;
		$page->setFillColor(new Zend_Pdf_Color_RGB(0, 0, 0));
	
		//columns headers
		$lines[0][] = array(
				'text' => Mage::helper('sales')->__('Products'),
				'feed' => 35
		);
	
		$lines[0][] = array(
				'text'  => Mage::helper('sales')->__('SKU'),
				'feed'  => 300,
				'align' => 'right'
		);
	
		$lines[0][] = array(
				'text'  => Mage::helper('sales')->__('Qty'),
				'feed'  => 455,
				'align' => 'right'
		);
	
		$lines[0][] = array(
				'text'  => Mage::helper('sales')->__('Price'),
				'feed'  => 390,
				'align' => 'right'
		);
	
		$lines[0][] = array(
				'text'  => Mage::helper('sales')->__('Tax'),
				'feed'  => 490,
				'align' => 'right'
		);
	
		$lines[0][] = array(
				'text'  => Mage::helper('sales')->__('Subtotal'),//Subtotal
				'feed'  => 565,
				'align' => 'right'
		);
	
		$lineBlock = array(
				'lines'  => $lines,
				'height' => 5
		);
	
		$this->drawLineBlocks($page, array($lineBlock), array('table_header' => true));
		$page->setFillColor(new Zend_Pdf_Color_GrayScale(0));
		$this->y -= 20;
	}
	/**
	 * Insert footer logo to pdf page
	 *
	 * @param Zend_Pdf_Page $page
	 * @param null $store
	 */
	protected function insertFooterLogo(&$page)
	{
		
		$image = Mage::getBaseDir('media') . '/sales/store/logo/footer_invoice_zorgpunt.png';
		if (is_file($image)) {
			$image       = Zend_Pdf_Image::imageWithPath($image);
			$top         = 90; //bottom border of the page
			$widthLimit  = 494; //SHE update so logo is smaller and invoice address get higher
			$heightLimit = 36; //SHE update so logo is smaller and invoice address get higher
			$width       = $image->getPixelWidth();
			$height      = $image->getPixelHeight();

			//preserving aspect ratio (proportions)
			$ratio = $width / $height;
			if ($ratio > 1 && $width > $widthLimit) {
				$width  = $widthLimit;
				$height = $width / $ratio;
			} elseif ($ratio < 1 && $height > $heightLimit) {
				$height = $heightLimit;
				$width  = $height * $ratio;
			} elseif ($ratio == 1 && $height > $heightLimit) {
				$height = $heightLimit;
				$width  = $widthLimit;
			}
			$y1 = $top - $height;
			$y2 = $top;
			$x1 = 50;
			$x2 = $x1 + $width;

			//coordinates after transformation are rounded by Zend
			$page->drawImage($image, $x1, $y1, $x2, $y2);
		}
	}
	
}
