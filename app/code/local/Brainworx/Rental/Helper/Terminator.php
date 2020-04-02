<?php
class Brainworx_Rental_Helper_Terminator extends Mage_Core_Helper_Abstract{
	/**
	 * public function to end rentals provided
	 * @param date (d-m-Y) $pickupDT
	 * @param array $rentalids
	 * @param Order $order
	 * @param date (d-m-Y) $end_date rental
	 * @return boolean
	 */
	function TerminateRentals($pickupDT, $rentalids, $order=null, $endDT=null, $overrule=false){
		if(empty($endDT)){
			$endDT = date("d-m-Y");
		}
		$shippinglistZP = array();
		$shippinglistEXT = array();
		$shippinglistEXT2 = array();
		$shippinglistEXT3 = array();
		$shippinglistSupplier = array();
		$error = false;
		$sellerids = Mage::getModel('core/variable')->setStoreId(Mage::app()->getStore()->getId())->loadByCode('DELIVERY_SPECIALSELLER')->getValue('text');
		$specialsellerids = explode(',',$sellerids);
		
			
		foreach($rentalids as $rentalId){
			try {
				$rentalModel = Mage::getModel ( 'rental/rentedItem' )->load($rentalId);
				if(isset($order)){
					$orderModel = $order;
				}else{					
					$orderModel = Mage::getModel('sales/order')->load($rentalModel->getOrigOrderId());
				}
				$itemModel = Mage::getModel('sales/order_item')->load($rentalModel->getOrderItemId());
				
				$shippingitem = self::terminateOneRental($rentalModel,$pickupDT,$orderModel,$itemModel,$endDT,$overrule);
				if(!empty($itemModel->getSupplierneworderemail())){
					$shippinglistSupplier[$itemModel->getSupplierneworderemail()][]=$shippingitem;
				}elseif($orderModel->getShippingMethod()=='tablerate_bestway'
						||  $orderModel->getShippingMethod()=='tablerate_express'
						||  $orderModel->getShippingMethod()=='tablerate_weekend'
						||  $orderModel->getShippingMethod()=='specialrate_flatrate'
						||  $orderModel->getShippingMethod()=='specialrate_free'	
						||  $orderModel->getShippingMethod()=='specialrate_urgent1'
						||  $orderModel->getShippingMethod()=='specialrate_weekend'
						||  $orderModel->getShippingMethod()=='specialrate_standard'						
						||  $orderModel->getShippingMethod()=='specialrate_urgent'				
						||  $orderModel->getShippingMethod()=='salesrate_flatrate'										
						||  $orderModel->getShippingMethod()=='salesrate_urgent'	
						||  $orderModel->getShippingMethod()=='flatrate_flatrate'
						||  $orderModel->getShippingMethod()=='normalrate2_flatrate'
						|| $orderModel->getShippingInclTax()>0){ //from other rates or earlier orders flatrate_flatrate can be found with shipping cost so pickup required
					if($orderModel->getShippingMethod()=='normalrate2_flatrate'){
						$shippinglistEXT3[]=$shippingitem;
					}else{
						//check seller 
						$seller = Mage::getModel('hearedfrom/salesSeller')->loadByOrderId($orderModel->getIncrementId());
						if(isset($seller) && $seller != false && in_array($seller['user_id'],$specialsellerids)){
							$shippinglistEXT2[]=$shippingitem;
						}else{
							$shippinglistEXT[]=$shippingitem;
						}
					}
				}else{
					$shippinglistZP[]=$shippingitem;
				}
				unset($shippingitem);
				unset($orderModel);
				unset($itemModel);
				
			} catch ( Exception $e ) {
				Mage::log('Error while ending rental for rental '.$rentalId.' error:'.$e->getMessage());
				self::sendErrorMail('Probleem ending rental '.$rentalId .' - '.$e->getMessage());
				$error = true;
			}
		}
		//send mail with excel
		foreach ($shippinglistSupplier as $supplier => $list){
			$email = $supplier;
			self::createPickupShipmentsExcel($list, true,$email);
		}
		if(!empty($shippinglistEXT)){
			//need to create excel to send to external delivery party
			$emails = Mage::getModel('core/variable')->setStoreId(Mage::app()->getStore()->getId())->loadByCode('DELIVERY_EMAIL')->getValue('text');
			self::createPickupShipmentsExcel($shippinglistEXT,true,$emails);
		}
		if(!empty($shippinglistZP)){
			self::createPickupShipmentsExcel($shippinglistZP,false);
		}
		if(!empty($shippinglistEXT2)){
			//need to create excel to send to external delivery party
			$emails = Mage::getModel('core/variable')->setStoreId(Mage::app()->getStore()->getId())->loadByCode('DELIVERY_SPECIAL_EMAIL')->getValue('text');
			self::createPickupShipmentsExcel($shippinglistEXT2,true,$emails);
		}
		if(!empty($shippinglistEXT3)){
			//need to create excel to send to external delivery party
			$emails = Mage::getModel('core/variable')->setStoreId(Mage::app()->getStore()->getId())->loadByCode('DELIVERY_NORMAL2_EMAIL')->getValue('text');
			self::createPickupShipmentsExcel($shippinglistEXT3,true,$emails);
		}
		return !$error;
	}
	
	/**
	 * private function to end one rental record and update stock
	 * @param RentedItem $rental
	 * @param date (d-m-Y) $preferredDT
	 * @param Order $order
	 * @param Order_item $item
	 * @return multitype:string pickuprecord for excel
	 */
	private function terminateOneRental($rental,$preferredDT,$order,$item,$endDT,$overrule=false ){
		self::updateSalesForceStock($order, $item, $rental,$overrule);
		
		$rental->setEndDt($endDT);
		$rental->setPickupDt($preferredDT);
		$rental->save();
		
		$order->addStatusHistoryComment("Einde huur item ".$item->getSku()."-".$item->getName()." ophaling op ".$preferredDT,false)->save();
			
		$rental->updateStock();
			
		//prepare excel
		$shippingitem = array();
		//items not supplied by supplier
		$shippingitem['Bestelling #']=$order->getIncrementId();
		//Added in OnePageController
		$shippingitem['Ophaaldatum']=$preferredDT;
		$shippingitem['Naam']=$order->getShippingAddress()->getFirstname().' '.$order->getShippingAddress()->getLastname();
		$shippingitem['Adres (straat + nr)']=$order->getShippingAddress()->getStreetFull();
		$shippingitem['Gemeente']=$order->getShippingAddress()->getCity();
		$shippingitem['Postcode']=$order->getShippingAddress()->getPostcode();
		$shippingitem['Land']=$order->getShippingAddress()->getCountry();
		$shippingitem['Telefoon']=$order->getShippingAddress()->getTelephone();
		$shippingitem['Artikel']=$item->getName();
		$shippingitem['Aantal']=$rental->getQuantity();
		$shippingitem['Artikelnr.']=$item->getSku();
		$shippingitem['Gewicht']=$item->getWeight();
		return $shippingitem;
	}
	/**
	 * Update the salesforce stock as the rented item has been returned.
	 * - will only be for consignation items (sku)
	 * @param  $order
	 * @param  $item
	 * @param  $overrule (update stock even when delivered at home)
	 */
	private function updateSalesForceStock($order,$item, $rental, $overrule = false){
		try{
			$catconsig = Mage::getModel('core/variable')->setStoreId(Mage::app()->getStore()->getId())->loadByCode('CAT_CONSIG')->getValue('text');
			//check if overrule is possible - can only be done for consignation items
			if($overrule && !in_array($catconsig,$item->getProduct()->getCategoryIds())){
				$overrule=false;
			}
			if(!$overrule && ($order->getShippingMethod()=='tablerate_bestway'
					||  $order->getShippingMethod()=='tablerate_express'
					||  $order->getShippingMethod()=='tablerate_weekend'
					||  $order->getShippingMethod()=='specialrate_flatrate'
					||  $order->getShippingMethod()=='specialrate_free'					
					||  $order->getShippingMethod()=='specialrate_urgent1'
					||  $order->getShippingMethod()=='specialrate_weekend'
					||  $order->getShippingMethod()=='specialrate_standard'				
					||  $order->getShippingMethod()=='specialrate_urgent'			
					||  $order->getShippingMethod()=='salesrate_flatrate'								
					||  $order->getShippingMethod()=='salesrate_urgent'		
					||  $order->getShippingMethod()=='flatrate_flatrate'
					||  $order->getShippingMethod()=='normalrate2_flatrate'
					||  $order->getShippingInclTax()>0)){
				Mage::log("items delivered at home so never stockupdate. ".$order->getIncrementId());
				return;
			}
			$seller = Mage::getModel('hearedfrom/salesSeller')->loadByOrderId($order->getIncrementId());
			//update stock record
			$stock = Mage::getModel('hearedfrom/salesForceStock');
			$stockrow = Mage::getModel('hearedfrom/salesForceStock')->loadByProdCodeAndSalesForce($item->getProduct()->getSku(),$seller["user_id"]);
			if(!empty($stockrow) && !empty($stockrow['entity_id'])){
				$stock->load($stockrow['entity_id']);
				$qstock = $stockrow['stock_quantity'];
				$qinrent = $stockrow['inrent_quantity'];
				if($qinrent >= $rental->getQuantity()){
					$qinrent -= $rental->getQuantity();
				}else{
					$qinrent = 0;
				}				
				$qstock += $rental->getQuantity();
				
				
			}else{
				$stock->setData('force_id',$seller["user_id"]);
				$stock->setData('article_pcd',$item->getProduct()->getSku());
				$stock->setData('article',$item->getProduct()->getName());
				$stock->setData('enabled',1);
				$qinrent = 0;
				$qstock = $rental->getQuantity();
			}
			
			$stock->setData('inrent_quantity',$qinrent);
			$stock->setData('stock_quantity',$qstock);
			$stock->save();
			unset($stock);
			unset($stockrow);
			
		}catch(Exception $e){
			Mage::log($e->getMessage());
			Mage::log('error while updated salesforcestock after return for '.$order->getIncrementId());
			self::sendErrorMail('Probleem update stock record '.$order->getIncrementId() .' - '.$e->getMessage());
			
		}
	}
	/**
	 * private functions
	 * Create an excel with items to be shipped + send it to transporter via email
	 * @param array with pickuplines $list
	 * @param boolean $to_external (send to emails in DELIVERY_EMAIL)
	 */
	private function createPickupShipmentsExcel($list,$to_external,$emails=null)
	{
		try{
			require_once Mage::getBaseDir('lib').'/Excel/PHPExcel.php';
	
			$objPHPExcel = new PHPExcel();
			$objPHPExcel->getProperties()->setCreator("Brainworx for Zorgpunt")
			->setLastModifiedBy("Zorgpunt")
			->setTitle("Zorgpunt ophaalnota");
			//header
			$line=1;
			$objPHPExcel->setActiveSheetIndex(0)
			->setCellValue('A'.$line, 'Bestelling #')
			->setCellValue('B'.$line, 'Ophaaldatum')
			->setCellValue('C'.$line, 'Naam')
			->setCellValue('D'.$line, 'Adres (straat + nr)')
			->setCellValue('E'.$line, 'Gemeente')
			->setCellValue('F'.$line, 'Postcode')
			->setCellValue('G'.$line, 'Land')
			->setCellValue('H'.$line, 'Telefoon')
			->setCellValue('I'.$line, 'Artikel')
			->setCellValue('J'.$line, 'Aantal')
			->setCellValue('K'.$line, 'Artikelnr.')
			->setCellValue('L'.$line, 'Gewicht');
			$objPHPExcel->getActiveSheet()->getStyle("A1:L1")->getFont()->setBold(true);
			foreach(range('A','L') as $columnID) {
				$objPHPExcel->getActiveSheet()->getColumnDimension($columnID)
				->setAutoSize(true);
			}
			//lines
			foreach($list as $item){
				$line +=1;
				$objPHPExcel->setActiveSheetIndex(0)
				->setCellValue('A'.$line, $item['Bestelling #'])
				->setCellValue('B'.$line, $item['Ophaaldatum'])
				->setCellValue('C'.$line, $item['Naam'])
				->setCellValue('D'.$line, $item['Adres (straat + nr)'])
				->setCellValue('E'.$line, $item['Gemeente'])
				->setCellValue('F'.$line, $item['Postcode'])
				->setCellValue('G'.$line, $item['Land'])
				->setCellValue('H'.$line, $item['Telefoon'])
				->setCellValue('I'.$line, $item['Artikel'])
				->setCellValue('J'.$line, $item['Aantal'])
				->setCellValue('K'.$line, $item['Artikelnr.'])
				->setCellValue('L'.$line, $item['Gewicht']);
			}
			//write file
			$objWriter = PHPExcel_IOFactory::createWriter($objPHPExcel, 'Excel2007');
			$date =  DateTime::createFromFormat('U.u', microtime(true));
			if($to_external){
				$filename = 'ophaling_ext_'.$date->format('YmdHisu').'.xlsx';
			}else{
				$filename = 'ophaling_zp_'.$date->format('YmdHisu').'.xlsx';
			}
			$file = Mage::getBaseDir('export').'/'.$filename;
			$objWriter->save($file);
			Mage::log('file written '.$file);
	
			//send email
			// This is the template name from your etc/config.xml
			$template_id = 'supplier_new_pickup';
			$storeId = Mage::app()->getStore()->getId();
	
			//send new shipment email to supplier
	
			// Who were sending to...
			if($to_external){				
				$email_to = explode(",",$emails);
			}else{
				$email_to = Mage::getStoreConfig('trans_email/ident_general/email');
			}
			// Load our template by template_id
			$email_template  = Mage::getModel('core/email_template')->loadDefault($template_id);
	
			// Here is where we can define custom variables to go in our email template!
			$email_template_variables = array(
			);
	
			// I'm using the Store Name as sender name here.
			$sender_name = Mage::getStoreConfig(Mage_Core_Model_Store::XML_PATH_STORE_STORE_NAME);
			// I'm using the general store contact here as the sender email.
			$sender_email = Mage::getStoreConfig('trans_email/ident_sales/email');
			$email_template->setSenderName($sender_name);
			$email_template->setSenderEmail($sender_email);
			$email_template->addBcc(Mage::getStoreConfig('trans_email/ident_custom1/email'));
			$email_template->addBcc(Mage::getStoreConfig('trans_email/ident_general/email'));
			$extramail =  Mage::getModel('core/variable')->setStoreId(Mage::app()->getStore()->getId())->loadByCode('EXTRA_MAIL')->getValue('text');
			if(!empty($extramail)){
				$email_template->addBcc($extramail);
			}
	
			//Add attachement
			$fileContents = file_get_contents($file);
			$attachment = $email_template->getMail()->createAttachment($fileContents);
			$attachment->filename = $filename;
	
			//Send the email!
			$email_template->send($email_to, Mage::helper('hearedfrom')->__('Retrieval'), $email_template_variables);
	
			Mage::log('Email for retrieval sent: '.$filename, null, 'email.log');
	
		}catch(Exception $e){
			Mage::log('Fout create ophaal excel: ' . $e->getMessage());
				
			self::sendErrorMail('Probleem creatie ophaal excel '.$filename.' - '.$e->getMessage());
				
		}
	}
	/**
	 * Sending error mail to webmaster
	 * @param unknown $info
	 */
	public function sendErrorMail($info){
		try{
			// This is the template name from your etc/config.xml
			$template_id = 'problem_zorgpunt';
			$storeId = Mage::app()->getStore()->getId();
	
			// Who were sending to...
			$email_to = 'info@brainworx.be';
			// Load our template by template_id
			$email_template  = Mage::getModel('core/email_template')->loadDefault($template_id);
	
			// Here is where we can define custom variables to go in our email template!
			$email_template_variables = array(
					'info'        => $info
			);
	
			// I'm using the Store Name as sender name here.
			$sender_name = Mage::getStoreConfig(Mage_Core_Model_Store::XML_PATH_STORE_STORE_NAME);
			// I'm using the general store contact here as the sender email.
			$sender_email = Mage::getStoreConfig('trans_email/ident_sales/email');
			$email_template->setSenderName($sender_name);
			$email_template->setSenderEmail($sender_email);
	
			//Send the email!
			$email_template->send($email_to, Mage::helper('hearedfrom')->__('Problems Zorgpunt'), $email_template_variables);
	
		}catch(Exception $e){
			Mage::log('fout bij verzenden problem mail: '.$e->getMessage());
		}
	}
	
}
