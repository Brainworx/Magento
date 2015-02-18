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
		Mage::Log('sales_order_place_after');
		// Retrieve the product being updated from the event observer
		$order = $observer->getEvent()->getOrder();
		// array_keys($order->getData() prints teh data keys in the order - can later be used to getData($key);
		
		if($order->getCustomerId() === NULL){
			Mage::Log("Guest: " .$order->getBillingAddress()->getLastname());
		}
		else {
			//$customer = Mage::getModel('customer/customer')->load($order->getCustomerId());
			Mage::Log("Known customer: " .$order->getCustomerId());
		}
		//Check tax record
		
		$items=$order->getAllVisibleItems();
		Mage::Log("nr items : " . count($items));
		$count = 0;
		$process = 0;
		
		foreach ($items as  $item)
		{
			foreach($item->getProduct()->getCategoryIds() as $cat){
				Mage::Log("cat:" .$cat);
				if($cat == 56){
					$count++;
					//saving new rental line to be invoiced monthly
					try{
						$newrentalitem = Mage::getModel('rental/rentedItem');
		
						$newrentalitem->setData('orig_order_id',$order->getEntityId());
// 							Mage::Log('order:' . $order->getEntityId());
// 							Mage::Log($newrentalitem->getData('orig_order_id'));
						$newrentalitem->setData('order_item_id',$item->getItemId());
						$newrentalitem->setData('quantity',$item->getQtyOrdered());// nr of items - not days
						$newrentalitem->setStartDt(date("Y-m-d"));
						Mage::Log('date:'.Mage::getModel('core/date')->date('Y-m-d'));
		
						$newrentalitem->Save();			
		
						Mage::Log("new rental (product:".$item->getName()." - Q ".$item->getQtyOrdered()." after saved for order " . $order->getEntityId());
						 
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
							$taxCollection->addFieldToFilter(
									array('customer_tax_class_id'),
									array(
											array('eq'=>9)) //TODO update customer tax class from real customer
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
					break;
				}
			}
			 
		}
		if($count > 0){
			//add comment which will be sent to the customer
			$order->addStatusToHistory($order->getStatus(), 'Verhuurartikels zullen vanaf de 1ste maand gefactureerd worden.', true);
			$order->save();
		}
		Mage::Log("Sale done: nr rental items : " . $count);
		
		}catch(Exception $e){
			Mage::log($e->getMessage());
			//set error message in session
			Mage::getSingleton('core/session')->addError('Sorry, er gebeurde een fout tijdens het wegschrijven van je bestelling.');
			die;
		}
		
	}
	/**
	 * Observer method configured for checkout_cart_product_add_after
	 * After adding a rental item the price will be set to 0 as rental items will be monthly invoiced after the first month.
	 */
	public function addDiscountToRental(Varien_Event_Observer $observer)
	{
		try{
			Mage::log('Checkout add product after event occurred');
			
			/* @var $item Mage_Sales_Model_Quote_Item */
			$item = $observer->getQuoteItem();
			if ($item->getParentItem()) {
				$item = $item->getParentItem();
			}
			$notice = 0;
			foreach($observer->getProduct()->getCategoryIds() as $cat){
				if($cat == 56){
					$item->setCustomPrice(0);
					$item->setOriginalCustomPrice(0);
					$item->getProduct()->setIsSuperMode(true);
					Mage::Log("Custom price set for rental " . $item->getProduct()->getName() . " -" . $item->getProduct()->getSku());
					$notice = 1;
					break;
				}
			}
			if($notice > 0){
				Mage::getSingleton('core/session')->addNotice('De prijs van je verhuurartikel werd op 0 gezet, je betaald deze pas na 1 maand.');
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