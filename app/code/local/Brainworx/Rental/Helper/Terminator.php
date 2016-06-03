<?php
class Brainworx_Rental_Helper_Terminator extends Mage_Core_Helper_Abstract{
	function TerminateRentals($pickupDT, $rentalids, $order=null){
		$shippinglistZP = array();
		$shippinglistEXT = array();
		$error = false;
		foreach($rentalids as $rentalId){
			try {
				$rentalModel = Mage::getModel ( 'rental/rentedItem' )->load($rentalId);
				if(isset($order)){
					$orderModel = $order;
				}else{					
					$orderModel = Mage::getModel('sales/order')->load($rentalModel->getOrigOrderId());
				}
				$itemModel = Mage::getModel('sales/order_item')->load($rentalModel->getOrderItemId());
				
				$shippingitem = self::terminateOneRental($rentalModel,$pickupDT,$orderModel,$itemModel);
				if($orderModel->getShippingInclTax()>0){
					$shippinglistEXT[]=$shippingitem;
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
		if(!empty($shippinglistEXT)){
			//need to create excel to send to external delivery party
			self::createPickupShipmentsExcel($shippinglistEXT,true);
		}
		if(!empty($shippinglistZP)){
			self::createPickupShipmentsExcel($shippinglistZP,false);
		}
		return !$error;
	}
	function terminateOneRental($rental,$preferredDT,$order,$item ){
		$rental->setEndDt(date("Y-m-d"));
		$rental->save();
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
		$shippingitem['Aantal']=$item->getQtyOrdered();
		$shippingitem['Artikelnr.']=$item->getSku();
		$shippingitem['Gewicht']=$item->getWeight();
		return $shippingitem;
	}
	/**
	 * Create an excel with items to be shipped + send it to transporter via email
	 */
	public function createPickupShipmentsExcel($list,$to_external)
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
				$emails = Mage::getModel('core/variable')->setStoreId(Mage::app()->getStore()->getId())->loadByCode('DELIVERY_EMAIL')->getValue('text');
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
			$sender_email = Mage::getStoreConfig('trans_email/ident_general/email');
			$email_template->setSenderName($sender_name);
			$email_template->setSenderEmail($sender_email);
			$email_template->addBcc(Mage::getStoreConfig('trans_email/ident_custom1/email'));
	
			//Add attachement
			$fileContents = file_get_contents($file);
			$attachment = $email_template->getMail()->createAttachment($fileContents);
			$attachment->filename = $filename;
	
			//Send the email!
			$email_template->send($email_to, Mage::helper('hearedfrom')->__('Retrieval'), $email_template_variables);
	
			Mage::log('Email for retrieval sent: '.$filename);
	
		}catch(Exception $e){
			Mage::log('Fout create ophaal excel: ' . $e->getMessage());
				
			self::sendErrorMail('Probleem creatie ophaal excel '.$filename.' - '.$e->getMessage());
				
		}
	}
	/**
	 * Sending error mail to webmaster
	 * @param unknown $info
	 */
	function sendErrorMail($info){
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
			$sender_email = Mage::getStoreConfig('trans_email/ident_general/email');
			$email_template->setSenderName($sender_name);
			$email_template->setSenderEmail($sender_email);
	
			//Send the email!
			$email_template->send($email_to, Mage::helper('hearedfrom')->__('Problems Zorgpunt'), $email_template_variables);
	
		}catch(Exception $e){
			Mage::log('fout bij verzenden problem mail: '.$e->getMessage());
		}
	}
	
}