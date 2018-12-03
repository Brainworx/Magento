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
		
		//For a mederi user, commission goes to him/her
		$groupId = explode(",",Mage::getSingleton('customer/session')->getCustomerGroupId());
		$seller_custid = 0;
		if(in_array(Mage::getModel('core/variable')->setStoreId(Mage::app()->getStore()->getId())->loadByCode('MEDERI_GID')->getValue('text'),$groupId)) {
			$mederisellerid = Mage::getModel('core/variable')->setStoreId(Mage::app()->getStore()->getId())->loadByCode('MEDERI_FORCE_ID')->getValue('text');
			$_hearedfrom_salesforce = Mage::getModel('hearedfrom/salesForce')->load($mederisellerid);
			$seller_custid = Mage::getSingleton('customer/session')->getCustomerId();
		}else{		
			//Fetch the data from select box and throw it here- added to session in OnePageController
			$_hearedfrom_salesforce = null;
			$_hearedfrom_salesforce = Mage::getSingleton('core/session')->getBrainworxHearedfrom();
			$seller_custid = $_hearedfrom_salesforce["cust_id"];
		}

		//Create new salesCommission		
		$newsalesseller = Mage::getModel('hearedfrom/salesSeller');
		$newsalesseller->setData("order_id",$order->getIncrementId());
		$newsalesseller->setData("user_id",$_hearedfrom_salesforce["entity_id"]);
		$newsalesseller->setData("seller_cust_id",$seller_custid);		
		$newsalesseller->save();			
		
		$check = Mage::getModel('hearedfrom/salesSeller')->load($newsalesseller['entity_id']);
		if($check->getSellerCustId() != $seller_custid){
			Mage::log('seller cust not save correctly for '.$newsalesseller['entity_id'].'  - correcting to '.$seller_custid);
			Mage::getModel('hearedfrom/salesSeller')->updateSellerDetails($newsalesseller['entity_id'],$seller_custid);
		}else{
			Mage::log('seller cust saved correctly for '.$newsalesseller['entity_id'].'  - to '.$seller_custid);
		}
		

		$shippinglist = null;
		$zorgpunt_shippinglist = null;
		$delivery_to_report = false;
		
		//check shipment method
		//helpers for shipping lists
		$deliveryBefore=Mage::getSingleton('core/session')->getDeliveryBefore();
		$comment=Mage::getSingleton('core/session')->getOrigCommentToZorgpunt();
		$shippinglist = array();
		//$order->getShippingInclTax()>0
		if(  $order->getShippingMethod()=='tablerate_bestway'
				||  $order->getShippingMethod()=='tablerate_express'
				||  $order->getShippingMethod()=='tablerate_weekend'
				||  $order->getShippingMethod()=='specialrate_flatrate'
				||  $order->getShippingMethod()=='specialrate_free'							
				||  $order->getShippingMethod()=='specialrate_urgent'		
				||  $order->getShippingMethod()=='salesrate_flatrate'	
				||  $order->getShippingMethod()=='flatrate_flatrate'){
			//need to create excel to send to external delivery party
			$delivery_to_report = true;
		}
		 
		//TODO add to transaction
		//save commission for articles invoiced and delivered by the supplier - marked invoiced false
		$items = $order->getAllItems();
		$catvaph = Mage::getModel('core/variable')->setStoreId(Mage::app()->getStore()->getId())->loadByCode('CAT_VAPH')->getValue('text');	
		$catouderenzorg = Mage::getModel('core/variable')->setStoreId(Mage::app()->getStore()->getId())->loadByCode('CAT_OUDERENZORG')->getValue('text');	
		
		foreach($items as $item){
			//Check request ouderenzorg
			if(in_array($catouderenzorg,$item->getProduct()->getCategoryIds())){
				//Load sellername
				$seller = Mage::getSingleton('core/session')->getBrainworxHearedfrom();
				try{
					$sellerName = $seller['user_nm'];
					if($sellerName != null && $sellerName != 'Zorgpunt' && $sellerName != ''&& $sellerName != 'Selecteer'){//add translation
						$sellerName = 'Zorgpunt '.$sellerName.'.';
					}else{
						$sellerName = 'Zorgpunt';
					}
				}catch(Exception $e){
					$sellerName = 'Zorgpunt';
					Mage::log('No hearedfrom set when sending supplier order - exception on order '.$order->getId());
				}
				$emails_to = Mage::getModel('core/variable')->setStoreId(Mage::app()->getStore()->getId())->loadByCode('OUDERENZORG_MAILS')->getValue('text');	
				$template_id = 'ouderenzorg_order_new';
				
				$email_template_variables= array(
							 'order'        => $order,
							 'seller'	=> $sellerName
					);		
				Mage::helper("hearedfrom/mailer")->sendMail($emails_to,$template_id,$email_template_variables);
							
			}
			// Checking VAPH
			elseif(in_array($catvaph,$item->getProduct()->getCategoryIds())){
				//Load sellername
				$seller = Mage::getSingleton('core/session')->getBrainworxHearedfrom();
				try{
					$sellerName = $seller['user_nm'];
					if($sellerName != null && $sellerName != 'Zorgpunt' && $sellerName != ''&& $sellerName != 'Selecteer'){//add translation
						$sellerName = 'Zorgpunt '.$sellerName.'.';
					}else{
						$sellerName = 'Zorgpunt';
					}
				}catch(Exception $e){
					$sellerName = 'Zorgpunt';
					Mage::log('No hearedfrom set when sending supplier order - exception on order '.$order->getId());
				}
				$emails_to = Mage::getModel('core/variable')->setStoreId(Mage::app()->getStore()->getId())->loadByCode('VAPH_MAILS')->getValue('text');	
				$template_id = 'vaph_order_new';
				$vaph = $order->getVaphDocNr();
				if(empty($vaph)){
					$vaph=Mage::helper('checkout')->__('Not provided');
				}
				$email_template_variables= array(
							 'order'        => $order,
							 'seller'		=> $sellerName,
							 'vaph_doc_nr'	=> $vaph
					);		
				Mage::helper("hearedfrom/mailer")->sendMail($emails_to,$template_id,$email_template_variables);
							
			}else{
				if(!empty($item->getSupplierinvoice())&&$item->getSupplierinvoice()>0){
					Mage::log("No need to send shipment exl as shipment + invoice done by ".$item->getSupplierneworderemail().' for '.$order->getIncrementId().' item '.$item->getSku());
					
					$type = Mage::getModel('core/variable')->setStoreId(Mage::app()->getStore()->getId())->loadByCode('TYPE_SALE')->getValue('text');
					self::saveCommission($_hearedfrom_salesforce,$order->getEntityId(),$item->getItemId(),
					$type,($item->getOriginalPrice()*$item->getQtyOrdered()),
							($item->getOriginalPrice()*$item->getQtyOrdered()*(1+$item->getTaxPercent()/100))
							,$item->getRistorno()*$item->getQtyOrdered(),false,$seller_custid);
				}elseif (!empty($item->getSupplierneworderemail())){
					Mage::log("No need to send shipment exl as shipment done by ".$item->getSupplierneworderemail().' for '.$order->getIncrementId().' item '.$item->getSku());
				}elseif($item->getSku()=='ADM-rein'){
					Mage::log("No need to send shipment for cleaning item ".$order->getIncrementId().' item '.$item->getSku());
				}
				else{
					$shippingitem = array();
					//items not supplied by supplier
					$shippingitem['Bestelling #']=$order->getIncrementId();
					//Added in OnePageController
					$shippingitem['Leverdatum']=$deliveryBefore; 
					//$shippingitem['Leverdatum tot']=$deliveryBefore;
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
					$shippingitem['Type']=(!empty($item->getRentalitem())&&$item->getRentalitem() == true)? Mage::helper('hearedfrom')->__('Verhuur'): Mage::helper('hearedfrom')->__('Verkoop');
					$shippinglist[]=$shippingitem;
					unset($shippingitem);
				}		
			}
		}
		if(!empty($shippinglist)){
			Mage::helper("hearedfrom/delivery")->createShipmentsExcel($shippinglist,$order,$delivery_to_report,$_hearedfrom_salesforce["entity_id"]);
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
			
			//Add ogm to invoice
			$ogm = date("yz");
			$digits = 0;
			$oid = $order->getEntityId();
			$digits = strlen($oid+"");
			if ($digits > 5){
				$oid = substr($oid,$digits-5);
			}
			$ogm = $ogm.$oid;
			$digits = 0;
			$check = $ogm%97;
			if($check == 0 ){
				$check = 97;
			}			
			$digits = strlen($ogm+"");
			while ($digits < 10){
				$ogm = "0".$ogm;
				$digits++;
			}
			if($check < 10){
				$ogm = $ogm."0".$check;
			}else{
				$ogm = $ogm.$check;
			}
			$fogm = '+++'.substr($ogm,0,3).'/'.substr($ogm,3,4).'/'.substr($ogm,7).'+++';
			$invoice->setOgm($fogm);
			
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
						
						$sales_force = Mage::getModel("hearedfrom/salesForce")->load($seller['user_id']);
						self::saveCommission($sales_force,$order->getEntityId(),
						$item->getOrderItemId(),$type,$item->getRowTotal(),
						$item->getRowTotalInclTax(),$orderitem->getRistorno()*$item->getQty(),true,$seller['seller_cust_id']);
						
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
		$order = $observer->getEvent()->getOrder();
		$order->setCommentToZorgpunt(Mage::getSingleton('core/session')->getCommentToZorgpunt());
		$order->setPreferredDeliveryDt(Mage::getSingleton('core/session')->getDeliveryBefore());
		$order->setDeliveryUntilDt(Mage::getSingleton('core/session')->getDeliveryBefore());
		$birthdate=Mage::getSingleton('core/session')->getPatientBirthDate();
		if(!empty($birthdate)){
			$order->setPatientBirthDate($birthdate);
		}
		$order->setPatientName(Mage::getSingleton('core/session')->getPatientName());
		$order->setPatientFirstname(Mage::getSingleton('core/session')->getPatientFirstname());
		$order->setVaphDocNr(Mage::getSingleton('core/session')->getVaphDocNr());
		$order->save();		
	}
	private function saveCommission($seller,$orderid,$orderitemid,$type,$netamt,$brutamt,$rst,$invoiced,$sellercustid ){
		$sellerid = $seller["entity_id"];
		$linkedtosellerid = $seller['linked_to'];
		$_perc = $seller['ristorno_split_perc'];			
		$ristorno = $rst;
		$ristorno = $rst * ($_perc/100);
		$sellerdetails = "";
		//mederi
		if(!empty($sellercustid)){
			$cust = Mage::getModel('customer/customer')->load($sellercustid);
			if(!empty($cust)&&
					(Mage::getModel('core/variable')->setStoreId(
							Mage::app()->getStore()->getId())->loadByCode('MEDERI_GID')->getValue('text'))==$cust->getGroupId()){
				$sellerdetails = $cust->getFirstname().' '.$cust->getLastname();
			}
		}
		//todo insert if linked
		if(!empty($linkedtosellerid) && $linkedtosellerid>0){			
			$ristorno2 = $rst - $ristorno;
			$newsalescomm = Mage::getModel('hearedfrom/salesCommission');
			$newsalescomm->setData('user_id',$linkedtosellerid);
			$newsalescomm->setData('orig_order_id',$orderid);
			$newsalescomm->setData('order_item_id', $orderitemid);
			$newsalescomm->setData('type',$type);
			$newsalescomm->setData('net_amount',$netamt);
			$newsalescomm->setData('brut_amount',$brutamt);
			$newsalescomm->setData('ristorno',$ristorno2);
			$newsalescomm->setData('invoiced',$invoiced);
			if(!empty($sellerdetails)){
				$newsalescomm->setData('sold_by',$sellerdetails);
			}
			$newsalescomm->save();
			Mage::log("Saving ristono for linked Zorgpunt ".$linkedtosellerid." - seller ".$sellerid);
		}
		$newsalescomm = Mage::getModel('hearedfrom/salesCommission');
		$newsalescomm->setData('user_id',$sellerid);
		$newsalescomm->setData('orig_order_id',$orderid);
		//$newsalescomm->setData('inv_id', $invoice->getIncrementId()); //doesn't exist at this point
		$newsalescomm->setData('order_item_id', $orderitemid);
		$newsalescomm->setData('type',$type);
		$newsalescomm->setData('net_amount',$netamt);
		$newsalescomm->setData('brut_amount',$brutamt);
		$newsalescomm->setData('ristorno',$ristorno);
		$newsalescomm->setData('invoiced',$invoiced);
		if(!empty($sellerdetails)){
			$newsalescomm->setData('sold_by',$sellerdetails);
		}
		$newsalescomm->save();
	}
	public function login()
	{
		$_perc = 100;
		$groupId = explode(",",Mage::getSingleton('customer/session')->getCustomerGroupId());
		if(in_array(Mage::getModel('core/variable')->setStoreId(Mage::app()->getStore()->getId())->loadByCode('MEDERI_GID')->getValue('text'),$groupId)) {
			$mederisellerid = Mage::getModel('core/variable')->setStoreId(Mage::app()->getStore()->getId())->loadByCode('MEDERI_FORCE_ID')->getValue('text');
			$seller = Mage::getModel('hearedfrom/salesForce')->load($mederisellerid);
			
		}else{
			$seller = Mage::getModel('hearedfrom/salesForce')->loadByCustid(Mage::getSingleton('customer/session')->getId());
		}
		if(!empty($seller)){
			$_perc = $seller['ristorno_split_perc'];
		}
		Mage::getSingleton('customer/session')->setRistornoPerc($_perc);
	}
	
	public function logout()
	{
		Mage::getSingleton('customer/session')->unsRistornoPerc();
	}
	/**
	 * Observer method configured for sales_order_place_after
	 *
	 * Update stock quantity and in rent quantity for salesforcestock.
	 *
	 * Magento passes a Varien_Event_Observer object as
	 * the first parameter of dispatched events.
	 */
	public function checkNewConsignation(Varien_Event_Observer $observer)
	{
		try{
			$order = $observer->getEvent()->getOrder();
			
			if($order->getShippingMethod()=='tablerate_bestway'
					||  $order->getShippingMethod()=='tablerate_express'
					||  $order->getShippingMethod()=='tablerate_weekend'
					||  $order->getShippingMethod()=='specialrate_flatrate'
					||  $order->getShippingMethod()=='specialrate_free'						
					||  $order->getShippingMethod()=='specialrate_urgent'			
					||  $order->getShippingMethod()=='salesrate_flatrate'	
					||  $order->getShippingMethod()=='flatrate_flatrate'
					||  $order->getShippingInclTax()>0){
				Mage::log("items delivered at home so never stockupdate. ".$order->getIncrementId());
				return;
			}
			
			$items=$order->getAllVisibleItems();
			$cat = Mage::getModel('core/variable')->setStoreId(Mage::app()->getStore()->getId())->loadByCode('CAT_CONSIG')->getValue('text');
			$_hearedfrom_salesforce = Mage::getSingleton('core/session')->getBrainworxHearedfrom();
	
			foreach ($items as  $item)
			{
				if(in_array($cat, $item->getProduct()->getCategoryIds())){
					//update stock record as the order is to add stock item(s)
					$stock = Mage::getModel('hearedfrom/salesForceStock');
					$stockrow = Mage::getModel('hearedfrom/salesForceStock')->loadByProdCodeAndSalesForce($item->getProduct()->getSku(),$_hearedfrom_salesforce["entity_id"]);
					$qstock=0;
					$qinrent = $item->getQtyOrdered();
					if(!empty($stockrow) && !empty($stockrow['entity_id'])){
						$stock->load($stockrow['entity_id']);
						$qstock = $stockrow['stock_quantity'];
						$oldrent = $stockrow['inrent_quantity'];
						if($qstock >= $qinrent){
							$qstock -= $qinrent;
						}else{
							$qstock = 0;
						}
						$qinrent += $oldrent;
					}else{
						$stock->setData('force_id',$_hearedfrom_salesforce["entity_id"]);
						$stock->setData('article_pcd',$item->getProduct()->getSku());
						$stock->setData('article',$item->getProduct()->getName());
						$stock->setData('enabled',1);
					}
					$stock->setData("stock_quantity",$qstock);
					if(!empty($item->getRentalitem())&& $item->getRentalitem() == true){
						$stock->setData("inrent_quantity",$qinrent);
					}
					$stock->setData('update_dt',date('d-m-Y H:i:s', strtotime('now')));
					 
					$stock->save();
					unset($stock);
					unset($stockrow);
				}
			}
				
		}catch(Exception $e){
			Mage::log($e->getMessage());
			//set error message in session
			Mage::getSingleton('core/session')->addError('Sorry, er gebeurde een fout tijdens het wegschrijven van je bestelling.');
			die;
		}
	}
}
