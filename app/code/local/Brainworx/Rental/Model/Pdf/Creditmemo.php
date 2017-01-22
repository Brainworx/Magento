<?php

/**
 * Override Sales Order Creditmemo PDF model
 *
 * @category   Mage
 * @package    Brainworx_rental
 * @author     Stijn Heylen
 */
class Brainworx_Rental_Model_Pdf_Creditmemo extends Mage_Sales_Model_Order_Pdf_Creditmemo
{
    
    /**
     * Return PDF document
     *
     * @param  array $creditmemos
     * @return Zend_Pdf
     */
    public function getPdf($creditmemos = array())
    {
        $this->_beforeGetPdf();
        $this->_initRenderer('creditmemo');

        $pdf = new Zend_Pdf();
        $this->_setPdf($pdf);
        $style = new Zend_Pdf_Style();
        $this->_setFontBold($style, 10);

        foreach ($creditmemos as $creditmemo) {
            if ($creditmemo->getStoreId()) {
                Mage::app()->getLocale()->emulate($creditmemo->getStoreId());
                Mage::app()->setCurrentStore($creditmemo->getStoreId());
            }
            $page  = $this->newPage();
            $order = $creditmemo->getOrder();
            /* Add image */
            $this->insertLogo($page, $creditmemo->getStore());
            /* Add address */
            $this->insertAddress($page, $creditmemo->getStore());
            /* Add head */
            $this->insertOrder(
                $page,
                $order,
                Mage::getStoreConfigFlag(self::XML_PATH_SALES_PDF_CREDITMEMO_PUT_ORDER_ID, $order->getStoreId())
            );
            /* Add document text and number */
            $this->insertDocumentNumber(
                $page,
                Mage::helper('sales')->__('Credit Memo # ') . $creditmemo->getIncrementId()
            );
            /* Add CM date*/
            $this->insertCreditmemoDate($page,
            		Mage::helper('sales')->__('Datum Credit Memo : ') .
            		Mage::helper('core')->formatDate($creditmemo->getCreatedAt(), 'medium', false));
            /* Add table head */
            $this->_drawHeader($page);
            /* Add body */
            foreach ($creditmemo->getAllItems() as $item){
                if ($item->getOrderItem()->getParentItem()) {
                    continue;
                }
                /* Draw item */
                $this->_drawItem($item, $page, $order);
                $page = end($pdf->pages);
            }
            /* Add totals */
            $this->insertTotals($page, $creditmemo);
        
	        /*SHE add footer*/
	        //use drawlineblock for this
	        $this->insertFooter($page, $creditmemo->getStore());
        }
        
        $this->_afterGetPdf();
        if ($creditmemo->getStoreId()) {
            Mage::app()->getLocale()->revert();
        }
        return $pdf;
    }

    /**
     * insert the creditmemo date in at the top of the page
     * @param Zend_Pdf_Page $page
     * @param unknown $text
     */
    public function insertCreditmemoDate(Zend_Pdf_Page $page, $text)
    {
    	$page->setFillColor(new Zend_Pdf_Color_GrayScale(1));
    	$this->_setFontRegular($page, 10);
    	$docHeader = $this->getDocHeaderCoordinates();
    	$page->drawText($text, 412, $docHeader[1] - 15, 'UTF-8');
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
    				//switched postition on pdf from 35 to 285
    				$page->drawText(strip_tags(ltrim($part)), 285, $this->y, 'UTF-8');
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
    					//switched postition on pdf from 285 to 35
    					$page->drawText(strip_tags(ltrim($part)), 35, $this->y, 'UTF-8');
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
     * Insert footer
     */
    private function insertFooter(&$page, $store = null) {
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
    	$linesContent[]='Het saldo storten we terug op je bankrekening.';
    	
    	//write first block
    	foreach($linesContent as $c){
    		$flineBlock['lines'][] = array(array('text'      => $c,
    				'feed'      => 50,
    				'align'     => 'left',
    				'font' 		=> 'bold'
    		)
    		);
    	}
    	$this->y -= 80;
    	$page = $this->drawLineBlocks($page, array($flineBlock));
    
    	//footnote *******************************************************************
    	$flineBlock = array(
    			'lines'  => array(),
    			'height' => 15
    	);
    	$linesContent = array();
    	
    	$linesContent[] = Mage::helper('sales')->__('Thank you for trusting ').'Zorgpunt.';
    	foreach($linesContent as $c){
    		$flineBlock['lines'][] = array(array('text'      => $c,
    				'feed'      => 50,
    				'align'     => 'center')
    		);
    	}
    	$this->y -= 20;
    	$page = $this->drawLineBlocks($page, array($flineBlock));
    	//end footnote
    
    	// write first column second block********************************************************
    	//init
    	$flineBlock = array(
    			'lines'  => array(),
    			'height' => 15
    	);
    	$linesContent = array();
    	//end init
    	$linesContent[]=$name;
    	$linesContent[]=Mage::getStoreConfig('general/store_information/address');
    	$linesContent[]='BTW '.Mage::getStoreConfig('general/store_information/merchant_vat_number');
    	//write first column second block *********************************************
    	foreach($linesContent as $c){
    		$flineBlock['lines'][] = array(array('text'      => $c,
    				'feed'      => 50,
    				'align'     => 'left')
    		);
    	}
    	$this->y -= 10;
    	$page = $this->drawLineBlocks($page, array($flineBlock));
    
    	// Column 2 second block *******************************************************
    	//init
    	$this->y += 65;
    
    	$flineBlock = array(
    			'lines'  => array(),
    			'height' => 15
    	);
    	$linesContent = array();
    	//end init
    	$linesContent[] = Mage::getStoreConfig('general/store_information/phone');
    	$linesContent[] = Mage::getStoreConfig('trans_email/ident_general/email');
    	$linesContent[] = Mage::getStoreConfig('web/secure/base_url');
    
    	//write
    	foreach($linesContent as $c){
    		$flineBlock['lines'][] = array(array('text'      => $c,
    				'feed'      => 350,
    				'align'     => 'left')
    		);
    	}
    	$this->y -= 20;
    	$page = $this->drawLineBlocks($page, array($flineBlock));
    
    }
}
