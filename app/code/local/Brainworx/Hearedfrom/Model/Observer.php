<?php

class Brainworx_Hearedfrom_Model_Observer
{
	
	const ORDER_ATTRIBUTE_FHC_ID = 'hearedfrom';
	/**
	 * XML configuration paths
	 */
	const XML_PATH_EMAIL_IDENTITY               = 'sales_email/order/identity';
	const XML_PATH_EMAIL_COPY_TO                = 'sales_email/order/copy_to';
	const XML_PATH_EMAIL_COPY_METHOD            = 'sales_email/order/copy_method';
	
		
    /**
     * Event Hook: checkout_type_onepage_save_order
     * 
     * Save who brougth the order to save the commission later when invoiced.
     * Exception for articles invoiced by supplier: already save commission record for lines delivered and invoiced
     * by the supplier.
     * @author Stijn Heylen
     * @param $observer Varien_Event_Observer
     */
	public function hookToOrderSaveEvent()
	{
		/**
		* NOTE:
		* Order has already been saved, now we simply add some stuff to it,
		* that will be saved to database. We add the stuff to Order object property
		* called "hearedfrom"
		*/
		$order = new Mage_Sales_Model_Order();
		$incrementId = Mage::getSingleton('checkout/session')->getLastRealOrderId();
		$order->loadByIncrementId($incrementId);
		
		//Fetch the data from select box and throw it here- added to session in OnePageController
		$_hearedfrom_salesforce = null;
		$_hearedfrom_salesforce = Mage::getSingleton('core/session')->getBrainworxHearedfrom();

		//Create new salesCommission
		$newsalesseller = Mage::getModel('hearedfrom/salesSeller');
		$newsalesseller->setData("order_id",$order->getIncrementId());
		$newsalesseller->setData("user_id",$_hearedfrom_salesforce["entity_id"]);
		$newsalesseller->save();

		$shippinglist = null;
		$zorgpunt_shippinglist = null;
		$delivery_to_report = false;
		
		//check shipment method
		//helpers for shipping lists
		$preferredDT=Mage::getSingleton('core/session')->getPreferredDeliveryDate();
		$comment=Mage::getSingleton('core/session')->getOrigCommentToZorgpunt();
		$shippinglist = array();
		if($order->getShippingInclTax()>0){
			//need to create excel to send to external delivery party
			$delivery_to_report = true;
		}
		//TODO add to transaction
		//save commission for articles invoiced and delivered by the supplier - marked invoiced false
		$items = $order->getAllItems();
		foreach($items as $item){
			if(!empty($item->getSupplierinvoice())&&$item->getSupplierinvoice()>0){
				$type = Mage::getModel('core/variable')->setStoreId(Mage::app()->getStore()->getId())->loadByCode('TYPE_SALE')->getValue('text');
				self::saveCommission($_hearedfrom_salesforce["entity_id"],$order->getEntityId(),$item->getItemId(),
				$type,($item->getOriginalPrice()*$item->getQtyOrdered()),
						($item->getOriginalPrice()*$item->getQtyOrdered()*(1+$item->getTaxPercent()/100))
						,$item->getRistorno()*$item->getQtyOrdered(),false);
			}else{
				$shippingitem = array();
				//items not supplied by supplier
				$shippingitem['Bestelling #']=$order->getIncrementId();
				//Added in OnePageController
				$shippingitem['Leverdatum']=$preferredDT; //nog leeg
				$shippingitem['Naam']=$order->getShippingAddress()->getFirstname().' '.$order->getShippingAddress()->getLastname();
				$shippingitem['Adres (straat + nr)']=$order->getShippingAddress()->getStreetFull();
				$shippingitem['Gemeente']=$order->getShippingAddress()->getCity();
				$shippingitem['Postcode']=$order->getShippingAddress()->getPostcode();
				$shippingitem['Land']=$order->getShippingAddress()->getCountry();
				$shippingitem['Telefoon']=$order->getShippingAddress()->getTelephone();
				$shippingitem['Artikel']=$item->getName();
				$shippingitem['Aantal']=$item->getQtyOrdered();
				$shippingitem['Artikelnr.']=$item->getSku();
				$shippingitem['Info aan Zorgpunt']=$comment;
				$shippingitem['Gewicht']=$item->getWeight();
				$shippinglist[]=$shippingitem;
				unset($shippingitem);
			}		
		}
		if(!empty($shippinglist)){
			self::createShipmentsExcel($shippinglist,$order,$delivery_to_report);
		}	
		
	}
	/**
	 * Create an excel with items to be shipped + send it to transporter via email
	 */
	public function createShipmentsExcel($list,$order,$to_external)
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
			->setCellValue('B'.$line, 'Leverdatum')
			->setCellValue('C'.$line, 'Naam')
			->setCellValue('D'.$line, 'Adres (straat + nr)')
			->setCellValue('E'.$line, 'Gemeente')
			->setCellValue('F'.$line, 'Postcode')
			->setCellValue('G'.$line, 'Land')
			->setCellValue('H'.$line, 'Telefoon')
			->setCellValue('I'.$line, 'Artikel')
			->setCellValue('J'.$line, 'Aantal')
			->setCellValue('K'.$line, 'Artikelnr.')
			->setCellValue('L'.$line, 'Gewicht')
			->setCellValue('M'.$line, 'Info aan Zorgpunt');
			$objPHPExcel->getActiveSheet()->getStyle("A1:M1")->getFont()->setBold(true);
			foreach(range('A','M') as $columnID) {
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
				->setCellValue('M'.$line, $item['Info aan Zorgpunt']);
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
				$email_to = Mage::getModel('core/variable')->setStoreId(Mage::app()->getStore()->getId())->loadByCode('DELIVERY_EMAIL')->getValue('text');
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
			$sender_email = Mage::getStoreConfig('trans_email/ident_general/email');
			$email_template->setSenderName($sender_name);
			$email_template->setSenderEmail($sender_email);
			$email_template->addBcc(Mage::getStoreConfig('trans_email/ident_custom1/email'));
			
			//Add attachement
			$fileContents = file_get_contents($file); 
			$attachment = $email_template->getMail()->createAttachment($fileContents);
			$attachment->filename = $filename;
				
			//Send the email!
			$email_template->send($email_to, Mage::helper('hearedfrom')->__('Deliveries'), $email_template_variables);
			
			Mage::log('Email for delivery sent: '.$filename.' from '.$sender_email.' ('.$sender_name.') to '.$email_to);
			
		}catch(Exception $e){
			Mage::log('Fout create lever excel: ' . $e->getMessage());
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
						'info'        => 'Probleem creatie lever excel '.$order->getIncrementId().' - '.$e->getMessage()
				);
				
				// I'm using the Store Name as sender name here.
				$sender_name = Mage::getStoreConfig(Mage_Core_Model_Store::XML_PATH_STORE_STORE_NAME);
				// I'm using the general store contact here as the sender email.
				$sender_email = Mage::getStoreConfig('trans_email/ident_general/email');
				$email_template->setSenderName($sender_name);
				$email_template->setSenderEmail($sender_email);
				
				//Send the email!
				$email_template->send($email_to, Mage::helper('hearedfrom')->__('Deliveries'), $email_template_variables);
					
			}catch(Exception $e){
				Mage::log('fout bij verzenden problem mail: '.$e->getMessage());
			}
		}
	}
	
	/**
	 * Hook to sales_order_invoice_register
	 *
	 * Save invoice amount to seller commission record using info stored at order time
	 * This hook will be triggered for each invoice - rental as sale or other if they would be created
	 *
	 * Magento passes a Varien_Event_Observer object as
	 * the first parameter of dispatched events.
	 */
	public function hookToInvoiceEvent(Varien_Event_Observer $observer)
	{
		try{
			//Mage::Log('sales_order_invoice_register');
			
			$invoice = $observer->getEvent()->getInvoice();
			$order = $observer->getEvent()->getOrder();
			
			$invitems = $invoice->getItemsCollection();
			
			foreach($invitems as $item){
				if($item->getPrice() > 0){
					$seller = Mage::getModel('hearedfrom/salesSeller')->loadByOrderId($order->getIncrementId());
					if(isset($seller) && $seller != false){
						//Create new salesCommission
						$type = Mage::getModel('core/variable')->setStoreId(Mage::app()->getStore()->getId())->loadByCode('TYPE_SALE')->getValue('text');
						$orderitem = Mage::getModel('sales/order_item')->load($item->getOrderItemId());
						//rentalitems attribute on order set when adding quote item
						if(!empty($orderitem->getRentalitem())&& $orderitem->getRentalitem()==true){
							$type = Mage::getModel('core/variable')->setStoreId(Mage::app()->getStore()->getId())->loadByCode('TYPE_RENT')->getValue('text');
						}else{	
							//TODO remove this check as not needed		
							//check for orders made before storing rentalitem on orderitems
							$product = Mage::getModel('catalog/product')->load($item->getProductId());
							foreach($product->getCategoryIds() as $cat){
								if($cat == 
								Mage::getModel('core/variable')->setStoreId(Mage::app()->getStore()->getId())->loadByCode('CAT_RENT')->getValue('text')){
									Mage::Log("Hooktoinvoice found rental cat for line without rentalitem identifier:" .$cat);
									$orderitem->setRentalitem(true);//update item for next time
									$type = Mage::getModel('core/variable')->setStoreId(Mage::app()->getStore()->getId())->loadByCode('TYPE_RENT')->getValue('text');
									break;
								}
							}
						}
						self::saveCommission($seller['user_id'],$order->getEntityId(),
						$item->getOrderItemId(),$type,$item->getRowTotal(),
						$item->getRowTotalInclTax(),$orderitem->getRistorno()*$item->getQty(),true);
						
					}
				}		
			
			}
			
		}catch(Exception $e){
			Mage::log($e->getMessage());
			//set error message in session
// 			Mage::getSingleton('core/session')->addError('Sorry, er gebeurde een fout tijdens het wegschrijven van je bestelling.');
// 			die;
		}
	}
	public function hookToQuoteSetProductEvent($observer)
	{
		$quoteItem = $observer->getQuoteItem();
		$product = $observer->getProduct();
		$quoteItem->setRistorno($product->getRistorno());
	}
	public function hookToOrderPlaceAfterEvent($observer){
		//save here the comment in the order
		$_comment_to_zorgpunt = Mage::getSingleton('core/session')->getCommentToZorgpunt();
		$_preferred_delivery_DT = Mage::getSingleton('core/session')->getPreferredDeliveryDT();
		$order = $observer->getEvent()->getOrder();
		$order->setCommentToZorgpunt($_comment_to_zorgpunt);
		$order->setPreferredDeliveryDt($_preferred_delivery_DT);
		$order->save();		
	}
	private function saveCommission($sellerid,$orderid,$orderitemid,$type,$netamt,$brutamt,$rst,$invoiced ){
		$newsalescomm = Mage::getModel('hearedfrom/salesCommission');
		$newsalescomm->setData('user_id',$sellerid);
		$newsalescomm->setData('orig_order_id',$orderid);
		//$newsalescomm->setData('inv_id', $invoice->getIncrementId()); //doesn't exist at this point
		$newsalescomm->setData('order_item_id', $orderitemid);
		$newsalescomm->setData('type',$type);
		$newsalescomm->setData('net_amount',$netamt);
		$newsalescomm->setData('brut_amount',$brutamt);
		$newsalescomm->setData('ristorno',$rst);
		$newsalescomm->setData('invoiced',$invoiced);
		$newsalescomm->save();
	}
}