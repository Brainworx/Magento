<?php
class Brainworx_Hearedfrom_Model_Observer
{
	
	const ORDER_ATTRIBUTE_FHC_ID = 'hearedfrom';
		
    /**
     * Event Hook: checkout_type_onepage_save_order
     * 
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
		//$this->_getRequest()->getPost(‘myCustomerOrderComment’, false);
		Mage::Log("Order brought by: ".$_hearedfrom_salesforce["user_nm"]." id ".$_hearedfrom_salesforce["entity_id"]);
		//Create new salesCommission
		$newsalesseller = Mage::getModel('hearedfrom/salesSeller');
		$newsalesseller->setData("order_id",$order->getIncrementId());
		$newsalesseller->setData("user_id",$_hearedfrom_salesforce["entity_id"]);
		$newsalesseller->save();
		//TODO add to transaction
		//save commission for articles invoiced by the supplier - marked invoiced false
		$items = $order->getAllItems();
		foreach($items as $item){
			if(!empty($item->getSupplierinvoice())&&$item->getSupplierinvoice()>0){
				$type = Mage::getModel('core/variable')->setStoreId(Mage::app()->getStore()->getId())->loadByCode('TYPE_SALE')->getValue('text');
				self::saveCommission($_hearedfrom_salesforce["entity_id"],$order->getEntityId(),$item->getItemId(),
				$type,($item->getOriginalPrice()*$item->getQtyOrdered()),
						($item->getOriginalPrice()*$item->getQtyOrdered()*(1+$item->getTaxPercent()/100))
						,$item->getRistorno()*$item->getQtyOrdered(),false);
			}
		
		}
		
	}
	
	/**
	 * Hook to sales_order_invoice_register
	 *
	 * Save invoice amount to seller record
	 * This hook will be triggered for each invoice - rental as sale or other if they would be created
	 *
	 * Magento passes a Varien_Event_Observer object as
	 * the first parameter of dispatched events.
	 */
	public function hookToInvoiceEvent(Varien_Event_Observer $observer)
	{
		try{
			Mage::Log('sales_order_invoice_register');
			
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
		Mage::log('Ristorno set in quote '.$quoteItem->getEntityId());
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