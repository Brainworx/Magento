<?php

require_once 'Mage/Checkout/controllers/OnepageController.php';

class Brainworx_Hearedfrom_OnepageController extends Mage_Checkout_OnepageController
{
    public function doSomestuffAction()
    {
		if(true) {
			$result['update_section'] = array(
            	'name' => 'payment-method',
                'html' => $this->_getPaymentMethodsHtml()
			);					
		}
    	else {
			$result['goto_section'] = 'shipping';
		}		
    }
    private function determineDeliveryOptions($customerAddressId){
    	$consigfound=false;
    	$afhfound=false;
    	$levfound=false;
    	$excllevfound=false;
    	
    	$catconsig = Mage::getModel('core/variable')->setStoreId(Mage::app()->getStore()->getId())->loadByCode('CAT_CONSIG')->getValue('text');
    	$catafh = Mage::getModel('core/variable')->setStoreId(Mage::app()->getStore()->getId())->loadByCode('CAT_AFH')->getValue('text');
    	$catlev = Mage::getModel('core/variable')->setStoreId(Mage::app()->getStore()->getId())->loadByCode('CAT_LEV')->getValue('text');
    	
    	if(isset($catconsig)){
    		$items=$this->getOnepage()->getQuote()->getAllVisibleItems();
    		foreach ($items as  $item)
    		{
    			if(in_array($catconsig,$item->getProduct()->getCategoryIds())){
    				$consigfound=true;
    			}
    			if(in_array($catafh,$item->getProduct()->getCategoryIds())){
    				$afhfound = true;
    			}
    			if(in_array($catlev,$item->getProduct()->getCategoryIds())){
    				$levfound = true;
    				if(!in_array($catafh,$item->getProduct()->getCategoryIds())){
    					$excllevfound=true;
    				}
    			}
    		}
    	}
    	if($excllevfound){
    		//develivery at home rules over other options when 1 art is delivery only
    		Mage::getSingleton('core/session')->setPickupPossible(false);
    		Mage::getSingleton('core/session')->setStockSupplyPossible(false);
    		Mage::getSingleton('core/session')->setDeliveryPossible(true);
    	}elseif ($consigfound && !($levfound || $afhfound)){
    		//Stocksupply is only possible when all articles are from consig
    		//verify stocksupply is possible
    		//todo check order contents - only rental allowed for stock supply
    		Mage::getSingleton('core/session')->setStockSupplyPossible(false);
    		Mage::getSingleton('core/session')->setPickupPossible(false);
    		Mage::getSingleton('core/session')->setDeliveryPossible(false);
    		$salesforce = Mage::getModel('hearedfrom/salesForce')->loadByCustid($this->getOnepage()->getQuote()->getCustomer()->getEntityId());
    		if(!empty($salesforce)){
    			Mage::getSingleton('core/session')->setStockSupplyPossible(true);
    			//check on billing address not required
    			$defaultbillingaddress = $this->getOnepage()->getQuote()->getCustomer()->getDefaultBillingAddress();
//     			if($defaultbillingaddress){
//     				if($defaultbillingaddress->getId() == $customerAddressId){
//     					//stock supply possible for sales or consig rentals
//     					Mage::getSingleton('core/session')->setStockSupplyPossible($consigfound);
//     					// 		    				$supplyAll = Mage::getModel('core/variable')->setStoreId(Mage::app()->getStore()->getId())->loadByCode('STOCK_SUPPLY_ALL')->getValue('text');
//     					// 		    				if(isset($supplyAll)&&$supplyAll==0){
//     					// 		    					//limit stocksupply to (consig) rental
//     					// 			    				$items=$this->getOnepage()->getQuote()->getAllVisibleItems();
//     					// 			    				foreach ($items as  $item)
//     						// 			    				{
//     						// 			    					if(!empty($item->getRentalitem())&&$item->getRentalitem() == true){
//     						// 			    						$rentaltosave = true;
//     						// 			    					}else{
//     						// 			    						//item in basket which isn't rental
//     						// 			    						Mage::log('Stocksupply not possible due to sale item in basket:'.$item->getItemId());
//     						// 			    						Mage::getSingleton('core/session')->setStockSupplyPossible(false);
//     						// 			    					}
//     						// 			    				}
//     					// 		    				}
//     				}else{
//     					return 'Bevoorrading kan enkel op eigen adres';
//     				}
//     			}
    		}else{
    			//TODO emp workarround if consignatie would be selected 
    			Mage::getSingleton('core/session')->setDeliveryPossible(true);
    		}
    	}elseif($afhfound||$levfound){
    		Mage::getSingleton('core/session')->setPickupPossible($afhfound);
    		Mage::getSingleton('core/session')->setStockSupplyPossible(false);
    		Mage::getSingleton('core/session')->setDeliveryPossible($levfound);
    	}else{
    		//Default only delivery at home -- should not occur
    		//Mage:log('Default delivery only - should not occur!! --  ');
    		Mage::getSingleton('core/session')->setPickupPossible(false);
    		Mage::getSingleton('core/session')->setStockSupplyPossible(false);
    		Mage::getSingleton('core/session')->setDeliveryPossible(true);
    	}
    	return false;
    }
    /**
     * Save checkout billing address
     */
    public function saveBillingAction()
    {
    	if ($this->_expireAjax()) {
    		return;
    	}
    	if ($this->getRequest()->isPost()) {
    		$data = $this->getRequest()->getPost('billing', array());
    		$customerAddressId = $this->getRequest()->getPost('billing_address_id', false);
    
    		if (isset($data['email'])) {
    			$data['email'] = trim($data['email']);
    		}
    		$result = $this->getOnepage()->saveBilling($data, $customerAddressId);
    		
    		$error = $this->determineDeliveryOptions($customerAddressId);
    		if($error){
    			$result['error']=$error;
    		}
    		
    
    		if (!isset($result['error'])) {
    			if ($this->getOnepage()->getQuote()->isVirtual()) {
    				$result['goto_section'] = 'payment';
    				$result['update_section'] = array(
    						'name' => 'payment-method',
    						'html' => $this->_getPaymentMethodsHtml()
    				);
    			} elseif (isset($data['use_for_shipping']) && $data['use_for_shipping'] == 1) {
    				$result['goto_section'] = 'shipping_method';
    				$result['update_section'] = array(
    						'name' => 'shipping-method',
    						'html' => $this->_getShippingMethodsHtml()
    				);
    
    				$result['allow_sections'] = array('shipping');
    				$result['duplicateBillingInfo'] = 'true';
    				
    			} else {
    				$result['goto_section'] = 'shipping';
    			}
    		}
    
    		$this->getResponse()->setBody(Mage::helper('core')->jsonEncode($result));
    	}
    }
    /**
     * Shipping address save action
     */
    public function saveShippingAction()
    {
    	if ($this->_expireAjax()) {
    		return;
    	}
    	if ($this->getRequest()->isPost()) {
    		$data = $this->getRequest()->getPost('shipping', array());
    		$customerAddressId = $this->getRequest()->getPost('shipping_address_id', false);
    		$result = $this->getOnepage()->saveShipping($data, $customerAddressId);
    		
    		//niet nodig -- TODO voor consignatie terug inbouwen --  enkel op eigen adres
//     		$error = $this->determineDeliveryOptions($customerAddressId);
//     		if($error){
//     			$result['error']=$error;
//     		}
    		
//     		//check if pickup is possible - could be no depending basket which is checked in previous actions
//     		if(Mage::getSingleton('core/session')->getPickupPossible()){
// 	    		//default no pickup as other delivery address was selected
// 	    		Mage::getSingleton('core/session')->setPickupPossible(false);
// 	    		//If shipment addres is different from billing address, pickup isn't possible
// 	    		$billingaddress = $this->getOnepage()->getQuote()->getBillingAddress();
// 	    		if((isset($data['same_as_billing']) && $data['same_as_billing']==1)
// 	    				 || ($customerAddressId && $billingaddress->getCustomerAddressId() == $customerAddressId)){
// 	    			Mage::getSingleton('core/session')->setPickupPossible(true);
// 	    		}else{    	
// 	    			if(Mage::getSingleton('core/session')->getDeliveryPossible()){
// 	    				Mage::getSingleton('core/session')->setPickupPossible(false);
// 	    				Mage::log("Selected other delivery address >> no pickup possible - address:".$customerAddressId);
// 	    			}else{
// 	    				Mage::getSingleton('core/session')->setPickupPossible(true);	    				 
// 	    				Mage::log("Selected other delivery address >> no pickup possible - but artile has no delivery options delivery address:".$customerAddressId);
// 	    			}
// 	    		}
//     		}
    		
    
    		if (!isset($result['error'])) {
    			$result['goto_section'] = 'shipping_method';
    			$result['update_section'] = array(
    					'name' => 'shipping-method',
    					'html' => $this->_getShippingMethodsHtml()
    			);
    		}
    		$this->getResponse()->setBody(Mage::helper('core')->jsonEncode($result));
    	}
    }
    
//     public function saveShippingAction()
//     {
// 	    if ($this->_expireAjax()) {
// 	            return;
//         }
//         if ($this->getRequest()->isPost()) {
//             $data = $this->getRequest()->getPost('shipping', array());
//             $customerAddressId = $this->getRequest()->getPost('shipping_address_id', false);
//             $result = $this->getOnepage()->saveShipping($data, $customerAddressId);
 
//             /*if we pass via this step - other shipping address was selected*/

//             Mage::getSingleton('core/session')->setStockSupplyPossible(false);
//             $salesforce = Mage::getModel('hearedfrom/salesForce')->loadByCustid($this->getOnepage()->getQuote()->getCustomer()->getEntityId());
//             if(!empty($salesforce)){
//             	$defaultbillingaddress = $this->getOnepage()->getQuote()->getCustomer()->getDefaultBillingAddress();
//             	if($defaultbillingaddress){
//             		if($defaultbillingaddress->getId() == $customerAddressId){
//             			//stock supply possible
//             			Mage::getSingleton('core/session')->setStockSupplyPossible(true);
//             		}
//             	}
//             }
            
//             if (!isset($result['error'])) {
                
//                 if (!isset($result['error'])) {
//                     $result['goto_section'] = 'shipping_method';
//                 }
//             }
//             $this->getResponse()->setBody(Mage::helper('core')->jsonEncode($result));
//         }
	    
// 	}
    
    /**
     * Shipping method save action
     * SHE: route next step to additional info step
     */
    public function saveShippingMethodAction()
    {
    	if ($this->_expireAjax()) {
    		return;
    	}
    	if ($this->getRequest()->isPost()) {
    		$data = $this->getRequest()->getPost('shipping_method', '');
    		Mage::getSingleton('customer/session')->setSeletedShipping($data);
    		
    		$result = $this->getOnepage()->saveShippingMethod($data);
    		// $result will contain error data if shipping method is empty
    		if (!$result) {
    			Mage::dispatchEvent(
    			'checkout_controller_onepage_save_shipping_method',
    			array(
    			'request' => $this->getRequest(),
    			'quote'   => $this->getOnepage()->getQuote()));
    			$this->getOnepage()->getQuote()->collectTotals();
    			$this->getResponse()->setBody(Mage::helper('core')->jsonEncode($result));
    
    			$this->loadLayout('checkout_onepage_hearedfrom');

                $result['goto_section'] = 'hearedfrom';
    		}
    		$this->getOnepage()->getQuote()->collectTotals()->save();
    		$this->getResponse()->setBody(Mage::helper('core')->jsonEncode($result));
    	}
    }
    
    
	/**
	 * route action to hearedfrom page after saving the payment
	 * (non-PHPdoc)
	 * @see Mage_Checkout_OnepageController::savePaymentAction()
	 */
//     public function savePaymentAction()
//     {
//         $this->_expireAjax();
//         if ($this->getRequest()->isPost()) {
//             $data = $this->getRequest()->getPost('payment', array());
//             /*
//             * first to check payment information entered is correct or not
//             */

//             try {
//                 $result = $this->getOnepage()->savePayment($data);
//             }
//             catch (Mage_Payment_Exception $e) {
//                 if ($e->getFields()) {
//                     $result['fields'] = $e->getFields();
//                 }
//                 $result['error'] = $e->getMessage();
//             }
//             catch (Exception $e) {
//                 $result['error'] = $e->getMessage();
//             }
//             $redirectUrl = $this->getOnePage()->getQuote()->getPayment()->getCheckoutRedirectUrl();
//             if (empty($result['error']) && !$redirectUrl) {
// 				$this->loadLayout('checkout_onepage_hearedfrom');

//                 $result['goto_section'] = 'hearedfrom';
//             }

//             if ($redirectUrl) {
//                 $result['redirect'] = $redirectUrl;
//             }

//             $this->getResponse()->setBody(Zend_Json::encode($result));
//         }
//     }
	/**
	 * Our custom hearedfrom save + route to next action
	 */
    public function saveHearedfromAction()
    {
        $this->_expireAjax();
        if ($this->getRequest()->isPost()) {
        	if(empty($this->getRequest()->getPost('getvoice'))||
        		//	empty($this->getRequest()->getPost('pddate')||
        					empty($this->getRequest()->getPost('delrange'))){
        		$this->loadLayout('checkout_onepage_hearedfrom');
        		$result['error'] = $this->__('Please complete all fields.');
        		$result['goto_section'] = 'hearedfrom';
        		
        		$this->getResponse()->setBody(Mage::helper('core')->jsonEncode($result));
        	}
            
        	//Grab the submited value heared from who and comment value
        	$_brainworx_hearedfrom = $this->getRequest()->getPost('getvoice');
        	//set Zorgpunt as default if no selection was made
        	if($_brainworx_hearedfrom == Mage::helper('checkout')->__('Select')){
        		$_brainworx_hearedfrom = "Zorgpunt";
        	}
        	//preferred delivery date is selected from datepicker
        	//$_preferred_delivery_date = $this->getRequest()->getPost('pddate');
        	//delivery before is the date generated after picking or 24hrs or within 3 days
        	$_delivery_before = $this->getRequest()->getPost('delrange');
        	
        	//set preferred delivery day with selection as made in radio buttons
        	Mage::getSingleton('core/session')->setPreferredDeliveryDate($_delivery_before);
        	Mage::getSingleton('core/session')->setDeliveryBefore($_delivery_before);
        	$_comment_tozorgpunt = $this->getRequest()->getPost('myCustomerOrderComment');
        	//Add the seller and comment to the session
        	$seller = Mage::getModel("hearedfrom/salesForce")->loadByUsername($_brainworx_hearedfrom);
			Mage::getSingleton('core/session')->setBrainworxHearedfrom($seller);
			$cmt = false;
			if(!empty($_delivery_before)){
				if(!empty($_comment_tozorgpunt)){
					Mage::getSingleton('core/session')->setOrigCommentToZorgpunt($_comment_tozorgpunt);
					$cmt = $_comment_tozorgpunt;
				}else{
					Mage::getSingleton('core/session')->setOrigCommentToZorgpunt('');						
				}
				$_comment_tozorgpunt = Mage::helper('checkout')->__('Delivery on %s',$_delivery_before);
				if(!empty($cmt)){
						$_comment_tozorgpunt = $_comment_tozorgpunt.' - '.$cmt;
				}
			}else{
				//default delivery date = next day
				Mage::log("ERROR -- DELIVERY NOT SET".$_comment_tozorgpunt);
				Mage::getSingleton('core/session')->setDeliveryBefore(date('d-m-Y', strtotime('+1 day')));
				Mage::getSingleton('core/session')->setOrigCommentToZorgpunt($_comment_tozorgpunt);
			}
			Mage::getSingleton('core/session')->setCommentToZorgpunt($_comment_tozorgpunt);

			$result = array();
            
                $result['goto_section'] = 'payment';
                $result['update_section'] = array(
                		'name' => 'payment-method',
                		'html' => $this->_getPaymentMethodsHtml()
                );

            $this->getResponse()->setBody(Zend_Json::encode($result));
        }
    }    
}
