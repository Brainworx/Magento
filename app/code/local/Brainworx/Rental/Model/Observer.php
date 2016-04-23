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
						$newrentalitem->setData('quantity',$item->getQtyOrdered());// nr of items - not days
						$newrentalitem->setStartDt(date("Y-m-d"));
						
						$newrentalitem->Save();
						
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
			Mage::log('Need to send '.count($suppliersToEmail).' supplier emails.');
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
					$sender_email = Mage::getStoreConfig('trans_email/ident_general/email');
					$email_template->setSenderName($sender_name);
					$email_template->setSenderEmail($sender_email);
					$email_template->addBcc($sender_email);
					
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
					Mage::log('Email to supplier '.$email.' sent. order '.$order->getEntityId());
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
			//Mage::log('sales_quote_product_add_after event occurred');
			
			$items = $observer->getItems();
			$item = $items[0];
			if ($item->getParentItem()) {
				$item = $item->getParentItem();
			}
			$rnotice = 0;
			$catrental = Mage::getModel('core/variable')->setStoreId(Mage::app()->getStore()->getId())->loadByCode('CAT_RENT')->getValue('text');
			$sinotice = 0;
			$catssuppplinvl = explode(",",Mage::getModel('core/variable')->setStoreId(Mage::app()->getStore()->getId())->loadByCode('CATS_SUPPL_INV')->getValue('text'));
			
			if(in_array($catrental,$item->getProduct()->getCategoryIds())){
				$rnotice = 1;
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
				Mage::getSingleton('core/session')->addNotice(Mage::helper('sales')->__('The price of your rental article has been set to 0, you will pay for this article within 10 days after receiving the monthly invoice.'));
				$item->setRentalitem(true);
			}else if($sinotice > 0){
				Mage::getSingleton('core/session')->addNotice(Mage::helper('sales')->__('The price of your article has been set to 0, you will receive the invoice directly from the supplier.'));
				$item->setSupplierinvoice(true);
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
			if($block->getInvoice()->getState() == Mage_Sales_Model_Order_Invoice::STATE_OPEN){
				$message = Mage::helper('rental')->__('Factuur '.$block->getInvoice()->getIncrementId()
						.' van ' . round($block->getInvoice()->getGrandTotal(),2).' euro werd betaald?');
				//$url = Mage::getModel('adminhtml/url')->getUrl('*/*/markpaid', array('invoice_id'=>$block->getInvoice()->getId()));
				$url = Mage::helper("adminhtml")->getUrl("adminhtml/rental/markpaid/",array("iid"=>$block->getInvoice()->getId()));
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
}