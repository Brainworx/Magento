<?php
class Brainworx_Hearedfrom_Helper_Delivery extends Mage_Core_Helper_Abstract{
	/**
	 * Identifier for history item
	 */
	const ENTITY_ORDER                              = 'order';
	const ENTITY_STOCKREQ                          = 'stockrequest';
	
	/**
	 * Event type names for order emails
	 */
	const EMAIL_EVENT_SHIPMENT    = 'new_shipment';
	const EMAIL_EVENT_STOCKSHIPMENT = 'new_stock_shipment';
	/**
	 * Create an excel with items to be shipped + send it to transporter via email
	 */
	public function createStockShipmentsExcel($list,$stockrequestids)
	{
		try{
			require_once Mage::getBaseDir('lib').'/Excel/PHPExcel.php';
	
			$objPHPExcel = new PHPExcel();
			$objPHPExcel->getProperties()->setCreator("Brainworx for Zorgpunt")
			->setLastModifiedBy("Zorgpunt")
			->setTitle("Zorgpunt levernota voorraad");
			//header
			$line=1;
			$objPHPExcel->setActiveSheetIndex(0)
			->setCellValue('A'.$line, 'Stockrequest #')
			->setCellValue('B'.$line, 'Leverdatum ')
			->setCellValue('c'.$line, 'Naam')
			->setCellValue('D'.$line, 'Adres (straat + nr)')
			->setCellValue('E'.$line, 'Gemeente')
			->setCellValue('F'.$line, 'Postcode')
			->setCellValue('G'.$line, 'Land')
			->setCellValue('H'.$line, 'Telefoon')
			->setCellValue('I'.$line, 'Artikel')
			->setCellValue('J'.$line, 'Aantal')
			->setCellValue('K'.$line, 'Artikelnr.')
			->setCellValue('L'.$line, 'Gewicht')
			;
			$objPHPExcel->getActiveSheet()->getStyle("A1:L1")->getFont()->setBold(true);
			foreach(range('A','L') as $columnID) {
				$objPHPExcel->getActiveSheet()->getColumnDimension($columnID)
				->setAutoSize(true);
			}
			//lines
			foreach($list as $item){
				$line +=1;
				$objPHPExcel->setActiveSheetIndex(0)
				->setCellValue('A'.$line, $item['Stockrequest #'])
				->setCellValue('B'.$line, $item['Leverdatum'])
				->setCellValue('C'.$line, $item['Naam'])
				->setCellValue('D'.$line, $item['Adres (straat + nr)'])
				->setCellValue('E'.$line, $item['Gemeente'])
				->setCellValue('F'.$line, $item['Postcode'])
				->setCellValue('G'.$line, $item['Land'])
				->setCellValue('H'.$line, $item['Telefoon'])
				->setCellValue('I'.$line, $item['Artikel'])
				->setCellValue('J'.$line, $item['Aantal'])
				->setCellValue('K'.$line, $item['Artikelnr.'])
				->setCellValue('L'.$line, $item['Gewicht'])
				;
			}
			//write file
			$objWriter = PHPExcel_IOFactory::createWriter($objPHPExcel, 'Excel2007');
			$filename = 'stocklevering_ext_'.$stockrequestids.'.xlsx';
			
			$file = Mage::getBaseDir('export').'/'.$filename;
			$objWriter->save($file);
			Mage::log('file written '.$file);
	
			//send email
			// This is the template name from your etc/config.xml
			$template_id = 'supplier_new_stockshipment';
			$storeId = Mage::app()->getStore()->getId();
	
			//send new shipment email to supplier
	
			// Who were sending to...
			$emails = Mage::getModel('core/variable')->setStoreId(Mage::app()->getStore()->getId())->loadByCode('DELIVERY_EMAIL')->getValue('text');
			$email_to = explode(",",$emails);
				
			// Load our template by template_id
			$email_template  = Mage::getModel('core/email_template')->loadDefault($template_id);
	
			// Here is where we can define custom variables to go in our email template!
			$email_template_variables = array(
					'stockrequest'        => $stockrequestids
			);
	
			// I'm using the Store Name as sender name here.
			$sender_name = Mage::getStoreConfig(Mage_Core_Model_Store::XML_PATH_STORE_STORE_NAME);
			// I'm using the general store contact here as the sender email.
			$sender_email = Mage::getStoreConfig('trans_email/ident_sales/email');
			$email_template->setSenderName($sender_name);
			$email_template->setSenderEmail($sender_email);
			$email_template->addBcc(Mage::getStoreConfig('trans_email/ident_general/email'));
			$email_template->addBcc(Mage::getStoreConfig('trans_email/ident_custom1/email'));
	
			//Add attachement
			$fileContents = file_get_contents($file);
			$attachment = $email_template->getMail()->createAttachment($fileContents);
			$attachment->filename = $filename;
	
			$email_template->send($email_to, Mage::helper('hearedfrom')->__('Deliveries'), $email_template_variables);
	
			Mage::log('Email for stockdelivery sent: '.$filename.' from '.$sender_email.' ('.$sender_name.')', null, 'email.log');
				
	
		}catch(Exception $e){
			Mage::log('Fout create stocklever excel: ' . $e->getMessage());

			Mage::helper("hearedfrom/error")->sendErrorMail('Probleem creatie stock lever excel '.$stockrequestids.' - '.$e->getMessage());
		}
	}
	/**
	 * Create an mail with items to be shipped + send it to transporter via email
	 */
	public function createStockShipmentsSimpleReport($list,$stockrequestids)
	{
		try{
			$items="";
			$deliverydate ="";
			$deliveryaddress="";
			$phone="";
			
			//header
			$line=1;
			//lines
			foreach($list as $item){
				$deliverydate = $item['Leverdatum'];
				$deliveryaddress = $item['Naam'].', '.$item['Adres (straat + nr)'].', '.$item['Postcode'].' '.$item['Gemeente'];
				$phone=$item['Telefoon'];
				$items=$items.$line.".".$item['Type'].": ".$item['Aantal']." x ".$item['Artikel']." (".$item['Artikelnr.'].")<br>";
				$line +=1;
			}
	
			//send email
			// This is the template name from your etc/config.xml
			$template_id = 'supplier_new_stockshipment_simple';
			$storeId = Mage::app()->getStore()->getId();
	
			//send new shipment email to supplier
	
			// Who were sending to...
			$emails = Mage::getModel('core/variable')->setStoreId(Mage::app()->getStore()->getId())->loadByCode('DELIVERY_EMAIL')->getValue('text');
			$email_to = explode(",",$emails);
	
			// Here is where we can define custom variables to go in our email template!
			$email_template_variables = array(
					'stockrequest'        => $stockrequestids,
					'deliverydate' => $deliverydate,
					'deliveryaddress' => $deliveryaddress,
					'phone' => $phone,
					'items' => $items
			);
	
			$emails_bcc = array(Mage::getStoreConfig('trans_email/ident_custom1/email'));
			
			Mage::helper("hearedfrom/mailer")->sendMailViaQueue($email_to,$storeId,$template_id,$email_template_variables,self::ENTITY_STOCKREQ, null,self::EMAIL_EVENT_STOCKSHIPMENT,$emails_bcc);
	
	
		}catch(Exception $e){
			Mage::log('Fout create stocklever nota: ' . $e->getMessage());
	
			Mage::helper("hearedfrom/error")->sendErrorMail('Probleem creatie stock lever nota '.$stockrequestids.' - '.$e->getMessage());
		}
	}
	/**
	 * Create an report with items to be shipped + send it to transporter via email
	 */
	public function createStockShipmentsReport($list,$stockrequestids)
	{
		$reportexcel = Mage::getModel('core/variable')->setStoreId(Mage::app()->getStore()->getId())->loadByCode('DeliveryReport_excel')->getValue('text');
		if(empty($reportexcel)||$reportexcel == 'Y'){
			self::createStockShipmentsExcel($list, $stockrequestids);
		}else{
			self::createStockShipmentsSimpleReport($list,$stockrequestids);
		}
	}
	/**
	 * Create an report with items to be shipped + send it to transporter via email
	 */
	public function createShipmentsReport($list,$order,$to_external,$seller=null)
	{
		$reportexcel = Mage::getModel('core/variable')->setStoreId(Mage::app()->getStore()->getId())->loadByCode('DeliveryReport_excel')->getValue('text');
		if(empty($reportexcel)||$reportexcel == 'Y'){
			self::createShipmentsExcel($list, $order, $to_external,$seller);
		}else{
			self::createShipmentsSimpleReport($list, $order, $to_external,$seller);
		}
	}
	/**
	 * Create an excel with items to be shipped + send it to transporter via email
	 */
	public function createShipmentsExcel($list,$order,$to_external,$seller=null)
	{
		try{
			require_once Mage::getBaseDir('lib').'/Excel/PHPExcel.php';
				
			$objPHPExcel = new PHPExcel();
			$objPHPExcel->getProperties()->setCreator("Brainworx for Zorgpunt")
			->setLastModifiedBy("Zorgpunt")
			->setTitle("Zorgpunt levernota");
			//header
			$line=1;
			$objPHPExcel->setActiveSheetIndex(0)
			->setCellValue('A'.$line, 'Bestelling #')
			->setCellValue('B'.$line, 'Leverdatum ')
			->setCellValue('c'.$line, 'Naam')
			->setCellValue('D'.$line, 'Adres (straat + nr)')
			->setCellValue('E'.$line, 'Gemeente')
			->setCellValue('F'.$line, 'Postcode')
			->setCellValue('G'.$line, 'Land')
			->setCellValue('H'.$line, 'Telefoon')
			->setCellValue('I'.$line, 'Artikel')
			->setCellValue('J'.$line, 'Aantal')
			->setCellValue('K'.$line, 'Artikelnr.')
			->setCellValue('L'.$line, 'Gewicht')
			->setCellValue('M'.$line, 'Info aan Zorgpunt')
			->setCellValue('N'.$line, 'Type');
			$objPHPExcel->getActiveSheet()->getStyle("A1:O1")->getFont()->setBold(true);
			foreach(range('A','N') as $columnID) {
				$objPHPExcel->getActiveSheet()->getColumnDimension($columnID)
				->setAutoSize(true);
			}
			//lines
			foreach($list as $item){
				$line +=1;
				$objPHPExcel->setActiveSheetIndex(0)
				->setCellValue('A'.$line, $item['Bestelling #'])
				->setCellValue('B'.$line, $item['Leverdatum'])
				->setCellValue('C'.$line, $item['Naam'])
				->setCellValue('D'.$line, $item['Adres (straat + nr)'])
				->setCellValue('E'.$line, $item['Gemeente'])
				->setCellValue('F'.$line, $item['Postcode'])
				->setCellValue('G'.$line, $item['Land'])
				->setCellValue('H'.$line, $item['Telefoon'])
				->setCellValue('I'.$line, $item['Artikel'])
				->setCellValue('J'.$line, $item['Aantal'])
				->setCellValue('K'.$line, $item['Artikelnr.'])
				->setCellValue('L'.$line, $item['Gewicht'])
				->setCellValue('M'.$line, $item['Info aan Zorgpunt'])
				->setCellValue('N'.$line, $item['Type']);
			}
			//write file
			$objWriter = PHPExcel_IOFactory::createWriter($objPHPExcel, 'Excel2007');
			if($to_external){
				$filename = 'levering_ext_'.$order->getIncrementId().'.xlsx';
			}else{
				$filename = 'levering_zp_'.$order->getIncrementId().'.xlsx';
			}
			$file = Mage::getBaseDir('export').'/'.$filename;
			$objWriter->save($file);
			Mage::log('file written '.$file);
				
			//send email
			// This is the template name from your etc/config.xml
			$template_id = 'supplier_new_shipment';
			$storeId = Mage::app()->getStore()->getId();
	
			//send new shipment email to supplier
	
			// Who were sending to...
			if($to_external){
				
				//check who is shipping the order
				if( $order->getShippingMethod()=='normalrate2_flatrate'){
					$emails = Mage::getModel('core/variable')->setStoreId(Mage::app()->getStore()->getId())->loadByCode('DELIVERY_NORMAL2_EMAIL')->getValue('text');
				}else{
					$emails = Mage::getModel('core/variable')->setStoreId(Mage::app()->getStore()->getId())->loadByCode('DELIVERY_EMAIL')->getValue('text');
				}
				$email_to = explode(",",$emails);
				//check external shipment or Bruno
				if($seller!=null){
					$sellerids = Mage::getModel('core/variable')->setStoreId(Mage::app()->getStore()->getId())->loadByCode('DELIVERY_SPECIALSELLER')->getValue('text');
					$ids = explode(',',$sellerids);
					if(in_array($seller,$ids)){
						$emails = Mage::getModel('core/variable')->setStoreId(Mage::app()->getStore()->getId())->loadByCode('DELIVERY_SPECIAL_EMAIL')->getValue('text');
						$email_to = explode(",",$emails);
					}
				}
			}else{
				$email_to = Mage::getStoreConfig('trans_email/ident_general/email');
			}
			// Load our template by template_id
			$email_template  = Mage::getModel('core/email_template')->loadDefault($template_id);
	
			// Here is where we can define custom variables to go in our email template!
			$email_template_variables = array(
					'order'        => $order
			);
	
			// I'm using the Store Name as sender name here.
			$sender_name = Mage::getStoreConfig(Mage_Core_Model_Store::XML_PATH_STORE_STORE_NAME);
			// I'm using the general store contact here as the sender email.
			$sender_email = Mage::getStoreConfig('trans_email/ident_sales/email');
			$email_template->setSenderName($sender_name);
			$email_template->setSenderEmail($sender_email);
			$email_template->addBcc(Mage::getStoreConfig('trans_email/ident_general/email'));
			$email_template->addBcc(Mage::getStoreConfig('trans_email/ident_custom1/email'));
				
			//Add attachement
			$fileContents = file_get_contents($file);
			$attachment = $email_template->getMail()->createAttachment($fileContents);
			$attachment->filename = $filename;
	
			//Send the email!
			$email_template->send($email_to, Mage::helper('hearedfrom')->__('Deliveries'), $email_template_variables);
				
			Mage::log('Email for delivery sent: '.$filename.' from '.$sender_email.' ('.$sender_name.')', null, 'email.log');
				
		}catch(Exception $e){
			Mage::log('Fout create lever excel: ' . $e->getMessage());
			Mage::helper("hearedfrom/error")->sendErrorMail('Probleem creatie lever excel '.$order->getIncrementId().' - '.$e->getMessage());
		}
	}
	/**
	 * Create an excel with items to be shipped + send it to transporter via email
	 */
	public function createShipmentsSimpleReport($list,$order,$to_external,$seller=null)
	{
		try{
			$sellername="";
			$phone="";
			if(!empty($seller)){
				$salesforce = Mage::getModel('hearedfrom/salesForce')->load($seller);
				$sellername = $salesforce["user_nm"];
			}else{
				$sellername = "Zorgpunt";
			}
			$items="";
			$deliverydate ="";
			$deliveryaddress="";
			$phone="";
	
			//header
			$line=1;
			//lines
			foreach($list as $item){
				$deliverydate = $item['Leverdatum'];
				$deliveryaddress = $item['Naam'].', '.$item['Adres (straat + nr)'].', '.$item['Postcode'].' '.$item['Gemeente'];
				$phone=$item['Telefoon'];
				$extrainfo = $item['Info aan Zorgpunt'];
				$items=$items.$line.".".$item['Type'].": ".$item['Aantal']." x ".$item['Artikel']." (".$item['Artikelnr.'].")<br>";
				$line +=1;
			}
	
			//send email
			// This is the template name from your etc/config.xml
			$template_id = 'supplier_new_shipment_simple';
			$storeId = Mage::app()->getStore()->getId();
	
			//send new shipment email to supplier
	
			// Who were sending to...
			if($to_external){
	
				//check who is shipping the order
				if( $order->getShippingMethod()=='normalrate2_flatrate'){
					$emails = Mage::getModel('core/variable')->setStoreId(Mage::app()->getStore()->getId())->loadByCode('DELIVERY_NORMAL2_EMAIL')->getValue('text');
				}else{
					$emails = Mage::getModel('core/variable')->setStoreId(Mage::app()->getStore()->getId())->loadByCode('DELIVERY_EMAIL')->getValue('text');
				}
				$email_to = explode(",",$emails);
				//check external shipment or Bruno
				if($seller!=null){
					$sellerids = Mage::getModel('core/variable')->setStoreId(Mage::app()->getStore()->getId())->loadByCode('DELIVERY_SPECIALSELLER')->getValue('text');
					$ids = explode(',',$sellerids);
					if(in_array($seller,$ids)){
						$emails = Mage::getModel('core/variable')->setStoreId(Mage::app()->getStore()->getId())->loadByCode('DELIVERY_SPECIAL_EMAIL')->getValue('text');
						$email_to = explode(",",$emails);
					}
				}
				$emails_bcc = array(Mage::getStoreConfig('trans_email/ident_general/email'),
						Mage::getStoreConfig('trans_email/ident_custom1/email'));
			}else{
				$email_to = array(Mage::getStoreConfig('trans_email/ident_general/email'));
				$emails_bcc = array(Mage::getStoreConfig('trans_email/ident_custom1/email'));
			}
			
			// Here is where we can define custom variables to go in our email template!
			$email_template_variables = array(
					'order'        => $order,
					'deliverydate' => $deliverydate,
					'deliveryaddress' => $deliveryaddress,
					'phone' => $phone,
					'items' => $items,
					'seller' => $sellername,
					'extrainfo' => $extrainfo
			);
			
			Mage::helper("hearedfrom/mailer")->sendMailViaQueue($email_to,$storeId,$template_id,$email_template_variables,self::ENTITY_ORDER, $order,self::EMAIL_EVENT_SHIPMENT,$emails_bcc);
			
	
		}catch(Exception $e){
			Mage::log('Fout create lever simple report: ' . $e->getMessage());
			Mage::helper("hearedfrom/error")->sendErrorMail('Probleem creatie lever excel '.$order->getIncrementId().' - '.$e->getMessage());
		}
	}
	
	function increaseWorkDay($dayToAdd){
		$workDayToProcess = date("w");
		
		$days=0;
		if($workDayToProcess ==6){
			$days = 2;
		}else if ($workDayToProcess == 7){
			$days = 1;
		}
		
		if($workDayToProcess+$days >= 5 - $dayToAdd){
			$days += (5-$dayToAdd) - ($workDayToProcess+$days);
		}
		$days += $dayToAdd;
		
		if($workDayToProcess % 6 == 0){
			$days += 2;
		}else if ($workDayToProcess % 7 == 0){
			$days += 1;
		}
		
		return date('d-m-Y', strtotime('+'.$days.' day'));
	}	
}
