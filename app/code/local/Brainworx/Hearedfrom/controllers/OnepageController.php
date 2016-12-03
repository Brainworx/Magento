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
    		
    		//verify stocksupply is possible
    		//todo check order contents - only rental allowed for stock supply
    		Mage::getSingleton('core/session')->setStockSupplyPossible(false);
    		$salesforce = Mage::getModel('hearedfrom/salesForce')->loadByCustid($this->getOnepage()->getQuote()->getCustomer()->getEntityId());
	    	if(!empty($salesforce)){
	    		$defaultbillingaddress = $this->getOnepage()->getQuote()->getCustomer()->getDefaultBillingAddress();
	    		if($defaultbillingaddress){
	    			if($defaultbillingaddress->getId() == $customerAddressId){
	    				//stock supply possible
	    				Mage::getSingleton('core/session')->setStockSupplyPossible(true);
	    				$supplyAll = Mage::getModel('core/variable')->setStoreId(Mage::app()->getStore()->getId())->loadByCode('STOCK_SUPPLY_ALL')->getValue('text');
	    				if(isset($supplyAll)&&$supplyAll==0){
		    				$items=$this->getOnepage()->getQuote()->getAllVisibleItems();
		    				foreach ($items as  $item)
		    				{
		    					if(!empty($item->getRentalitem())&&$item->getRentalitem() == true){
		    						$rentaltosave = true;
		    					}else{
		    						//item in basket which isn't rental
		    						Mage::log('Stocksupply not possible due to sale item in basket:'.$item->getItemId());
		    						Mage::getSingleton('core/session')->setStockSupplyPossible(false);
		    					}
		    				}
	    				}
	    			}
	    		}
    		}
    		//verify delivery pickup allowed
    		//if only items from cat consig >> pickup possible, 
    		//if minimal 1 non consignation rental item >> no pickup
    		Mage::getSingleton('core/session')->setPickupPossible(true);
    		$catconsig = Mage::getModel('core/variable')->setStoreId(Mage::app()->getStore()->getId())->loadByCode('CAT_CONSIG')->getValue('text');
    		if(isset($catconsig)){
    			$items=$this->getOnepage()->getQuote()->getAllVisibleItems();
    			foreach ($items as  $item)
    			{
    				if(!empty($item->getRentalitem())&&$item->getRentalitem() == true){
    					if(!in_array($catconsig,$item->getProduct()->getCategoryIds())){
    						Mage::getSingleton('core/session')->setPickupPossible(false);
    						Mage::log("Found rental without consignation cat >> pickup not possible - product ".$item->getProduct()->getSku());
    					}
    				}
    			}
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
    		
    		//If shipment addres is different from billing address, pickup isn't possible
    		$defaultbillingaddress = $this->getOnepage()->getQuote()->getCustomer()->getDefaultBillingAddress();
    		if($defaultbillingaddress){
    			if($defaultbillingaddress->getId() != $customerAddressId){
    				 Mage::getSingleton('core/session')->setPickupPossible(false);
    				 Mage::log("Selected other delivery address >> no pickup possible - address:".$customerAddressId);
    			}else{
    				Mage::getSingleton('core/session')->setPickupPossible(true);    				
    			}
    		}
    
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
