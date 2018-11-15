<?php
/**
 * This class will verify the new sale for rental items and insert a new rental record per item.
 * Magento local
 * @author Stijn
 *
*/
class Brainworx_Rental_Model_Observer
{
	/**
	 * XML configuration paths
	 */
	const XML_PATH_EMAIL_IDENTITY               = 'sales_email/order/identity';
	const XML_PATH_EMAIL_COPY_TO                = 'sales_email/order/copy_to';
	const XML_PATH_EMAIL_COPY_METHOD            = 'sales_email/order/copy_method';
	/**
	 * Observer method configured for sales_order_place_after
	 * 
	 * Save new rental items to be invoiced monthly.
	 * 
	 * Magento passes a Varien_Event_Observer object as
	 * the first parameter of dispatched events.
	 */
	public function checkNewRental(Varien_Event_Observer $observer)
	{
		try{
			
			// Retrieve the product being updated from the event observer
			$order = $observer->getEvent()->getOrder();
			// array_keys($order->getData() prints teh data keys in the order - can later be used to getData($key);
							
			//Check tax record		
			$items=$order->getAllVisibleItems();
			$count = 0;
			$process = 0;
			$rentaltosave = false;
			$suppliersToEmail = array();
			foreach ($items as  $item)
			{
				if(!empty($item->getSupplierneworderemail())
						&&!in_array($item->getSupplierneworderemail(),$suppliersToEmail)){
					$suppliersToEmail[]=$item->getSupplierneworderemail();
				}
	
				$rentaltosave = false;
				
				if(!empty($item->getRentalitem())&&$item->getRentalitem() == true){
					$rentaltosave = true;
				}else{
					//TODO remove this check as not needed
					foreach($item->getProduct()->getCategoryIds() as $cat){
						if($cat ==
						  Mage::getModel('core/variable')->setStoreId(Mage::app()->getStore()->getId())->loadByCode('CAT_RENT')->getValue('text')){
							$rentaltosave = true;						
							break;
						}
					}
				}
				
				if($rentaltosave){
					$count++;
					//saving new rental line to be invoiced monthly
					try{
						$newrentalitem = Mage::getModel('rental/rentedItem');
					
						$newrentalitem->setData('orig_order_id',$order->getEntityId());
						$newrentalitem->setData('order_item_id',$item->getItemId());
                        $itv = $item->getRentalinterval();
                        if(!empty($itv)){
                            $newrentalitem->setData('rentalinterval',substr($itv,0,1));
                        }
						$newrentalitem->setData('quantity',$item->getQtyOrdered());// nr of items - not days
						//old rule: start verhuur bij levering is leverdatum, bij afhaling ingave order
						//ticket 144 new rule: always delivery date
						$start_date = Mage::getSingleton('core/session')->getDeliveryBefore();
						if(! isset($start_date)){
							$start_date = date("Y-m-d");
							Mage::log('Startdate set to today as delivery date was empty! '.$order->getIncrementId());
						}
						$newrentalitem->setStartDt($start_date);
						
						$newrentalitem->save();
						
						//Check/add sale_tax_item record
						if(0==count(Mage::getModel('tax/sales_order_tax_item')->getCollection()->addFieldToFilter(
								array('item_id'),
								array(array('eq'=> $item->getItemId()))))){
							$taxCollection = Mage::getModel('tax/calculation')->getCollection();
							// add joined data to the collection
							$taxCollection->getSelect()->join(
									array('rate' => 'tax_calculation_rate'),
									'main_table.tax_calculation_rate_id = rate.tax_calculation_rate_id',
									array('code','rate')
							)->join(
									array('rule' => 'tax_calculation_rule'),
									'main_table.tax_calculation_rule_id = rule.tax_calculation_rule_id',
									array('priority','position')
							);
							$custTaxClassID = Mage::getModel('core/variable')->setStoreId(Mage::app()->getStore()->getId())->loadByCode('CUST_TAX_ID')->getValue('text');
							$taxCollection->addFieldToFilter(
									array('customer_tax_class_id'),
									array(
											array('eq'=>$custTaxClassID)) //TODO update customer tax class from real customer
							)->addFieldToFilter(
									array('product_tax_class_id'),
									array(
											array('eq'=>$item->getProduct()->getData('tax_class_id')))
							);
							foreach ( $taxCollection as $taxR ) {
								$code = $taxR->getData("code");
								//Check add sale_tax
								$saleTaxes = Mage::getModel('tax/sales_order_tax')->getCollection()
								->addFieldToFilter(
										array('order_id'),
										array(array('eq'=> $order->getEntityId())))
										->addFieldToFilter(
												array('code'),
												array(array('eq'=> $code)));
								if(0==count($saleTaxes)){
									$data = array(
											'order_id'          => $order->getId(),
											'code'              => $code,
											'title'             => $code, // here you could check tax_calculation_rate_title for overloaded title-if multiple stores exist
											'hidden'            => 0,
											'percent'           => $taxR->getData('rate'),
											'priority'          => $taxR->getData('priority'),
											'position'          => $taxR->getData('position'),
											'amount'            => 0,
											'base_amount'       => 0,
											'process'           => $process,
											'base_real_amount'  => 0,
									);
									$saleTax = Mage::getModel('tax/sales_order_tax')->setData($data)->save();
									//process = used to sort tax rates per order
									$process++;
								}else{
									foreach($saleTaxes as $st){
										$saleTax = $st;
										break;
									}
								}
							}
			
							//load data for sale tax item
							$data = array(
									'item_id'       => $item->getItemId(),
									'tax_id'        => $saleTax->getTaxId(),
									'tax_percent'   => $saleTax->getPercent()
							);
							//save the sale tax item
							Mage::getModel('tax/sales_order_tax_item')->setData($data)->save();
						}
					}catch(Exception $e){
						Mage::log($e->getMessage());
						//set error message in session
						Mage::getSingleton('core/session')->addError('Sorry, er gebeurde een fout tijdens het wegschrijven van je verhuur order.');
						die;
					}
				}
				 
			}
			if($count > 0){
				//add comment which will be sent to the customer
				//TODO add translation Mage::helper('sales')->__('Invoice Date ')
				$order->addStatusToHistory($order->getStatus(), 'Verhuurartikels worden aangerekend per dag en maandelijks gefactureerd.', true);
				$order->save();
			}
			if(count($suppliersToEmail)>0){
				Mage::log($order->getEntityId().' Need to send '.count($suppliersToEmail).' supplier emails.');
			}
			if(!empty($suppliersToEmail)){
				// This is the template name from your etc/config.xml
				$template_id = 'supplier_order_new';
				$storeId = Mage::app()->getStore()->getId();
				
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
				foreach($suppliersToEmail as $email){
					//send new order email to supplier
					
					// Who were sending to...
					$email_to = $email;				
					// Load our template by template_id
					$email_template  = Mage::getModel('core/email_template')->loadDefault($template_id);
					
					// Here is where we can define custom variables to go in our email template!
					$email_template_variables = array(
							 'order'        => $order,
							 'supplieremail'=> $email,
							 'seller'		=> $sellerName
							 //'companyname' 	=> $order->getBillingAddress()->getCompanyname()
							// Other variables for our email template.
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
					
					//Send the email!
					$email_template->send($email_to, Mage::helper('rental')->__('Supplier'), $email_template_variables);
					
					//via queu
					
					// Get the destination email addresses to send copies to
					//$copyTo = array(Mage::getStoreConfig(self::XML_PATH_EMAIL_IDENTITY, $storeId));
// 					$copyTo = array(Mage::getStoreConfig('trans_email/ident_general/email'));
// 					$copyMethod = Mage::getStoreConfig(self::XML_PATH_EMAIL_COPY_METHOD, $storeId);
										
// 					/** @var $mailer Mage_Core_Model_Email_Template_Mailer */
// 					$mailer = Mage::getModel('core/email_template_mailer');
// 					/** @var $emailInfo Mage_Core_Model_Email_Info */
// 					$emailInfo = Mage::getModel('core/email_info');
// 					$emailInfo->addTo($email, Mage::helper('rental')->__('Supplier'));
// 					if ($copyTo && $copyMethod == 'bcc') {
// 						// Add bcc to customer email
// 						foreach ($copyTo as $emailc) {
// 							$emailInfo->addBcc($emailc);
// 							Mage::log('copy email to '.$emailc);
// 						}
// 					}
// 					$mailer->addEmailInfo($emailInfo);
					
					
// 					// Email copies are sent as separated emails if their copy method is
// 					// 'copy' or a customer should not be notified
// 					if ($copyTo && ($copyMethod == 'copy')) {
// 						foreach ($copyTo as $emailc) {
// 							$emailInfo = Mage::getModel('core/email_info');
// 							$emailInfo->addTo($emailc);
// 							$mailer->addEmailInfo($emailInfo);
// 							Mage::log('copy email to '.$emailc);
// 						}
// 					}
					
// 					// Set all required params and send emails
// 					$mailer->setSender(Mage::getStoreConfig('trans_email/ident_general/email'));
// 	       			$mailer->setStoreId($storeId);
// 					$mailer->setTemplateId($template_id);
// 					$mailer->setTemplateParams(array(
// 							 'order'        => $order,
// 							 'supplieremail'=> $email
// 					)
// 					);			
// 					/** @var $emailQueue Mage_Core_Model_Email_Queue */
// 					$emailQueue = Mage::getModel('core/email_queue');
// 					$emailQueue->setEntityId($order->getEntityId())
// 					->setEntityType('order')
// 					->setEventType('new_supplier_order')
// 					->setIsForceCheck(false);

// 					$mailer->setQueue($emailQueue);
// 					$mailer->send();
					Mage::log('Email to supplier '.$email.' sent. order '.$order->getEntityId(), null, 'email.log');
				}
			}		
			Mage::Log("Sale done: nr rental items : " . $count . " for order ".$order->getEntityId());
		
		}catch(Exception $e){
			Mage::log($e->getMessage());
			//set error message in session
			Mage::getSingleton('core/session')->addError('Sorry, er gebeurde een fout tijdens het wegschrijven van je bestelling.');
			die;
		}
		
	}
	public function checkVAPH(Varien_Event_Observer $observer){
		try{
			if ($observer->getEvent()->getControllerAction()->getFullActionName() == 'checkout_cart_add') {

				Mage::getSingleton('core/session')->setVaphOrder(0);
				
				$productId = Mage::app()->getRequest()->getParam('product');
				$product = Mage::getModel('catalog/product')->load($productId);
			
				$cartHelper = Mage::helper('checkout/cart');
				$cart = $cartHelper->getCart();
				$cartitems = $cart->getItems();
				$nr_items = sizeof($cartitems);
			
				$vaphnotice = 0;
				$catvaph = Mage::getModel('core/variable')->setStoreId(Mage::app()->getStore()->getId())->loadByCode('CAT_VAPH')->getValue('text');
					
				// Checking VAPH
				if(in_array($catvaph,$product->getCategoryIds())){
					$vaphnotice = 1;
					//new is VAPH, no other products allowed
					if($nr_items == 0){
						//first item is VAPH
						Mage::log("VAPH item added");
						Mage::getSingleton('core/session')->setVaphOrder(1);
						return;
					}else{
						Mage::getSingleton('core/session')->setVaphOrder(0);
						//earlier added non-vaph item -- remove vaph
						Mage::app()->getRequest()->setParam('product', false);
						Mage::getSingleton('core/session')->addNotice(Mage::helper('sales')->__('Combo not allowed.'));
						Mage::app()->getResponse()->setRedirect('checkout/cart');
					}
				}else{
					//check other article for VAPH
					foreach($cartitems as $citem){
						if(in_array($catvaph,$citem->getProduct()->getCategoryIds())){
							Mage::getSingleton('core/session')->setVaphOrder(1);
							//removing the newly added one as the other one is a vaph
							Mage::app()->getRequest()->setParam('product', false);
							Mage::getSingleton('core/session')->addNotice(Mage::helper('sales')->__('Combo with unique not allowed.'));
							Mage::app()->getResponse()->setRedirect('checkout/cart');
						}
					}
				}
			}
			
			
			//end check VAPH
		}catch(Exception $e){
			Mage::log($e->getMessage());
			//set error message in session
			Mage::getSingleton('core/session')->addError('Sorry, er gebeurde een fout tijdens het aanpassen van je bestelling.');
			die;
		}
	}
	
	/**
	 * Observer method configured for sales_quote_product_add_after
	 * After adding a rental item or item invoiced by the supplier
	 * the price will be set to 0 as rental items will be monthly invoiced after the first month.
	 * There will also be an indicator on the orderitem for rentals (rentalitem)
	 *  and/or invoiced by supplier (Supplierinvoice).
	 */
	public function addDiscountToRental(Varien_Event_Observer $observer)
	{
		try{
			//check additional cleaning required
			$skuswithextratoadd = explode(',',Mage::getModel('core/variable')->setStoreId(Mage::app()->getStore()->getId())->loadByCode('SKU_EXTRA_REIN')->getValue('text'));
			$skustoaddextra = explode(',',Mage::getModel('core/variable')->setStoreId(Mage::app()->getStore()->getId())->loadByCode('SKU_EXTRA_TOADD')->getValue('text'));
				
			Mage::getSingleton('core/session')->setVAPH(false);
			//Mage::log('sales_quote_product_add_after event occurred');
			$catvaph = Mage::getModel('core/variable')->setStoreId(Mage::app()->getStore()->getId())->loadByCode('CAT_VAPH')->getValue('text');
			
			$items = $observer->getItems();
			$item = $items[0];
			if ($item->getParentItem()) {
				$item = $item->getParentItem();
			}
			//when we're adding auto products -- no need for further process expect for adding supplier email when required
			if(in_array($item->getSku(),$skustoaddextra)){
				if(!empty($item->getProduct()->getSupplierOrderEmail())){
					$item->setSupplierneworderemail($item->getProduct()->getSupplierOrderEmail());
				}
				return;
			}
			
			//directly move to checkout after adding the VAPH article
			if(in_array($catvaph,$item->getProduct()->getCategoryIds())){
				Mage::getSingleton('core/session')->setVAPH(true);
				Mage::app()->getFrontController()->getResponse()->setRedirect(Mage::getUrl('checkout/onepage'));
				Mage::getSingleton('checkout/session')->setNoCartRedirect(true);
			}
			
			//check to add additional productse
			if(!empty($skuswithextratoadd)&&in_array($item->getSku(),$skuswithextratoadd)){
				$counter=0;
				foreach($skuswithextratoadd as $s){
					if($s == $item->getSku()){
						break;
					}else{
						$counter++;
					}
				}
				
				// additiona product required
				$quoteitems = Mage::getModel('checkout/cart')->getQuote()->getAllItems();
				$product = Mage::getModel('catalog/product')->loadByAttribute('sku',$skustoaddextra[$counter]);
				$cart = Mage::getModel('checkout/cart');
				
				//check extra item present
				$found = false;
				$reinitem='';
				$qty=0;
				foreach ($quoteitems as  $qitem)
				{
					if(!empty($qitem->getSku()==$skustoaddextra[$counter])){
						$found=true;
						$reinitem=$qitem->getId();
						break;
					}
				}

				//product to clean already in basket
				if($found){
					// update extra item
					$cart->updateItem($reinitem, $item->getQty());					
				}else{					
					// add extra item
			        $cart->addProduct($product->getEntityId());
				}
				Mage::getSingleton('checkout/session')->setCartWasUpdated(true);
			}
			
			$rnotice = 0;
			$catrental = Mage::getModel('core/variable')->setStoreId(Mage::app()->getStore()->getId())->loadByCode('CAT_RENT')->getValue('text');
			$sinotice = 0;
			$catssuppplinvl = explode(",",Mage::getModel('core/variable')->setStoreId(Mage::app()->getStore()->getId())->loadByCode('CATS_SUPPL_INV')->getValue('text'));
			$dsnotice = 0;
			
			if(!empty($item->getProduct()->getSupplierOrderEmail())){
				$item->setSupplierneworderemail($item->getProduct()->getSupplierOrderEmail());
				$dsnotice = 1;
			}
			
			if(in_array($catrental,$item->getProduct()->getCategoryIds())){
				$rnotice = 1;
				$item->setRentalinterval($item->getProduct()->getAttributeText('rental_interval'));
				Mage::log("rental interval set ".$item->getRentalinterval());
				
			}
			if($rnotice == 0){
				foreach($item->getProduct()->getCategoryIds() as $cat){	
					if( in_array($cat,$catssuppplinvl))	{	
						if(!empty($item->getProduct()->getSupplierOrderEmail())){
							$item->setSupplierneworderemail($item->getProduct()->getSupplierOrderEmail());
						}else{
							Mage::log('Article invoiced by supplier but no email '.$item->getProduct()->getSku());
							$item->setSupplierneworderemail(Mage::getStoreConfig('trans_email/ident_general/email'));
						}
						Mage::log('Item with Product '.$item->getProduct()->getSku().' supplier email '.$item->getSupplierneworderemail());
						
						$sinotice = 1;
						break;
					}
				}
			}
			if($rnotice > 0 || $sinotice > 0){
				$item->setCustomPrice(0);
				$item->setOriginalCustomPrice(0);
				$item->getProduct()->setIsSuperMode(true);
			}			
			if($rnotice > 0){
				$item->setRentalitem(true);
			}elseif($sinotice > 0){
				$item->setSupplierinvoice(true);
			}
			if ($sinotice == 0 && $dsnotice > 0){
				Mage::getSingleton('core/session')->addNotice(Mage::helper('sales')->__('This article will be shipped directly from the supplier.'));
			}
		}catch(Exception $e){
			Mage::log($e->getMessage());
			//set error message in session
			Mage::getSingleton('core/session')->addError('Sorry, er gebeurde een fout tijdens het aanpassen van je bestelling.');
			die;
		}
	}	 
	/**
	 * Triggered when an adminmodule is loaded
	 * @param unknown $event
	 */
	public function adminhtmlWidgetContainerHtmlBefore( $event){
		$block = $event->getBlock();
		//Add button to mark invoices as paid on the invoice page
		if ($block instanceof Mage_Adminhtml_Block_Sales_Order_Invoice_View) {
			//only add markpaid button when invoice state is open
			if($block->getInvoice()->getState() == Mage_Sales_Model_Order_Invoice::STATE_OPEN
					|| $block->getInvoice()->getState() == Brainworx_Rental_Model_Order_Invoice::STATE_INCASSO){
				$message = Mage::helper('rental')->__('Factuur '.$block->getInvoice()->getIncrementId()
						.' van ' . round($block->getInvoice()->getGrandTotal(),2).' euro werd betaald?');
				//$url = Mage::getModel('adminhtml/url')->getUrl('*/*/markpaid', array('invoice_id'=>$block->getInvoice()->getId()));
				$url = Mage::helper("adminhtml")->getUrl("adminhtml/rental/markpaid/",array("iid"=>$block->getInvoice()->getId()));
				$block->removeButton('capture');
				$block->addButton('mark_paid', array(
						'label'     => Mage::helper('rental')->__('Markeer als betaald'),
						//'onclick'   => "confirmSetLocation('De maandelijkse facturen aanmaken?', '{$this->getUrl('*/*/createInvoices')}')",
	    				//'onclick' => "confirmSetLocation('De maandelijkse facturen aanmaken?','{$url}')",
						'onclick' => "confirmSetLocation('$message','$url')",
	    				'class'     => 'go' //geeft een ander uitzicht
								));
			}
		}
	}
	/**
	 * Function to check customer session, when not logged in and bitcheck cookie exists we need to log him in as it's for Mederi
	 */
	public function checkcustomer(){
		try{
			$cookies = Mage::getSingleton('core/cookie')->get();
			//test
			$csession = Mage::getSingleton('customer/session');
			if (!$csession->isLoggedIn()) {
				if(array_key_exists('bitcheck',$cookies)){
					$sess = $cookies['bitcheck'];
					if(!empty($sess)){
						Mage::log('Customer not logged in by api cookie -- login in by id');
						$pieces = explode('|',$sess);
						//$session = Mage::getSingleton('customer/session');
						if(count($pieces > 1)){
						$csession->loginById($pieces[1]);
						if ($csession->isLoggedIn()) {
							Mage::log('Customer from API logged in by id');
						}else{
							Mage::log('FAIL: Customer from API not logged in by id');
						}
						}else{
							Mage::log('Bitcheck message nok - no pipe, only 1 element');
						}
					}
				}
			}
		}catch(Exception $e){
			Mage::log($e->getMessage());
			Mage::helper("rental/error")->sendErrorMail("Error login session from api: ". $e->getMessage());
		}
	
	}
	/**
	 * remove cookie from api login when customer logs out
	 */
	public function logout()
	{
		Mage::log('deleting cookie bitcheck');
		Mage::getModel('core/cookie')->delete('bitcheck');
		setcookie('bitcheck', '', time() - 3600,'/');
		if (isset($_COOKIE['bitcheck'])) {
			unset($_COOKIE['bitcheck']);
		}
	}
	/**
	 * Observer method configured for sales_quote_remove_item
	 * Add reiniging when required // prevent removal of reiniging
	 */
	public function hookToRemoveItem(Varien_Event_Observer $observer)
	{
		try{
			
			//check additional cleaning products removed from basket
			$skuswithextratoadd = explode(',',Mage::getModel('core/variable')->setStoreId(Mage::app()->getStore()->getId())->loadByCode('SKU_EXTRA_REIN ')->getValue('text'));
			$skustoaddextra = explode(',',Mage::getModel('core/variable')->setStoreId(Mage::app()->getStore()->getId())->loadByCode('SKU_EXTRA_TOADD')->getValue('text'));
				
			$item = $observer->getEvent()->getQuoteItem();
			
			//check sku with extra is being removed
			if(!empty($skuswithextratoadd)&&in_array($item->getSku(),$skuswithextratoadd)){
				$counter=0;
				foreach($skuswithextratoadd as $s){
					if($s == $item->getSku()){
						break;
					}else{
						$counter++;
					}
				}
				//check item is here
				$extraitemfound=false;
				$extraitemid;
				$items = Mage::getModel('checkout/cart')->getQuote()->getAllItems();
				foreach ($items as  $iitem)
				{
					if($iitem->getSku()==$skustoaddextra[$counter]){
						$extraitemfound=true;
						$extraitemid=$iitem->getId();
						break;
					}
				}
				if($extraitemfound){
					$cart = Mage::getModel('checkout/cart');
					$cart->removeItem($extraitemid)->save();
					Mage::getSingleton('checkout/session')->setCartWasUpdated(true);
				}
			}
			
			
		}catch(Exception $e){
			Mage::log($e->getMessage());
			Mage::helper("rental/error")->sendErrorMail("Error remove item");
		}
			
	}
	function hookToCartUpdateItems(Varien_Event_Observer $observer){
		try{
			$skuswithextratoadd = explode(',',Mage::getModel('core/variable')->setStoreId(Mage::app()->getStore()->getId())->loadByCode('SKU_EXTRA_REIN ')->getValue('text'));
			$skustoaddextra = explode(',',Mage::getModel('core/variable')->setStoreId(Mage::app()->getStore()->getId())->loadByCode('SKU_EXTRA_TOADD')->getValue('text'));
			
			$cart=$observer->getCart();
			$quoteItems = $cart->getQuote()->getAllVisibleItems();
			
			$qty=[];
			$cnt=0;
			$rqty=[];
			$extraitemid=[];
			foreach ($quoteItems as $item) {
				if(!empty($skuswithextratoadd)&&in_array($item->getSku(),$skuswithextratoadd)){
					$cnt=0;
					foreach($skuswithextratoadd as $s){
						if($s == $item->getSku()){
							$qty[$cnt]=$item->getQty();
							break;
						}
						$cnt++;
					}
				}elseif(!empty($skustoaddextra)&&in_array($item->getSku(),$skustoaddextra)){
					$cnt=0;
					foreach($skustoaddextra as $s){
						if($s == $item->getSku()){
							$rqty[$cnt]=$item->getQty();
							$extraitemid[$cnt]=$item->getId();
							break;
						}
						$cnt++;
					}
				}
			}
			//update the quantity of the auto added product if needed
			for($i=0; $i < count($skuswithextratoadd);$i++){
				if(!empty($qty[$i])&&$qty[$i]!=$rqty[$i]){
					$cart->updateItem($extraitemid[$i], $qty[$i]);
					Mage::getSingleton('checkout/session')->setCartWasUpdated(true);
				}
			}
			
		}catch(Exception $e){
			Mage::log($e->getMessage());
			Mage::helper("rental/error")->sendErrorMail("Error update item: ".$e->getMessage());
		}
	}
}
