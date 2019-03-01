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
    /*
     * Bepaal leveropties op basis van categorieën
     * in design frontend/checkout/onepage/shipment_method/available wordt dit gebruikt
     * custom var nodig
     */
    private function determineDeliveryOptions($customerAddressId){
    	$consigfound=false;
    	$afhfound=false;
    	$levfound=false;
    	$excllevfound=false;
		$nodeloptionfound=false;
		//per item to use in loop
		$i_consigfound=false;
    	$i_afhfound=false;
    	$i_levfound=false;
    	$i_excllevfound=false;
		$i_nodeloptionfound=false;
    	
    	$catconsig = Mage::getModel('core/variable')->setStoreId(Mage::app()->getStore()->getId())->loadByCode('CAT_CONSIG')->getValue('text');
    	$catafh = Mage::getModel('core/variable')->setStoreId(Mage::app()->getStore()->getId())->loadByCode('CAT_AFH')->getValue('text');
    	$catlev = Mage::getModel('core/variable')->setStoreId(Mage::app()->getStore()->getId())->loadByCode('CAT_LEV')->getValue('text');
    	
    	if(isset($catconsig)){
    		$items=$this->getOnepage()->getQuote()->getAllVisibleItems();
    		foreach ($items as  $item)
    		{
    			if(in_array($catconsig,$item->getProduct()->getCategoryIds())){
    				$i_consigfound=true;
    			}
    			if(in_array($catafh,$item->getProduct()->getCategoryIds())){
    				$i_afhfound = true;
    			}
    			if(in_array($catlev,$item->getProduct()->getCategoryIds())){
    				$i_levfound = true;
    				if(!($i_consigfound || $i_afhfound)){
    					$i_excllevfound=true;
    				}
    			}
			$i_nodeloptionfound=(!($i_consigfound||$i_afhfound||$i_levfound));
			
			$consigfound=$consigfound?$consigfound:$i_consigfound;
    		$afhfound=$afhfound?$afhfound:$i_afhfound;
    		$levfound=$levfound?$levfound:$i_levfound;
    		$excllevfound=$excllevfound?$excllevfound:$i_excllevfound;
			$nodeloptionfound=$nodeloptionfound?$nodeloptionfound:$i_nodeloptionfound;
			
			//reset
			$i_consigfound=false;
			$i_afhfound=false;
			$i_levfound=false;
			$i_excllevfound=false;
			$i_nodeloptionfound=false;
    		}
    	}
	   
    	if($excllevfound || $nodeloptionfound){
    		//develivery at home rules over other options when 1 art is delivery at home only OR when an article has no delivery option set to home or pickup
    		Mage::getSingleton('core/session')->setPickupPossible(false);
    		Mage::getSingleton('core/session')->setDeliveryPossible(true);
    	}elseif($afhfound||$levfound||$consigfound){
    		Mage::getSingleton('core/session')->setPickupPossible($afhfound || $consigfound);
    		Mage::getSingleton('core/session')->setDeliveryPossible($levfound);
    	}else{
    		//Default only delivery at home -- should not occur
    		//Mage:log('Default delivery only - should not occur!! --  ');
    		Mage::getSingleton('core/session')->setPickupPossible(false);
    		Mage::getSingleton('core/session')->setDeliveryPossible(true);
    	}
    	return false;
    }
    /**
     * Our custom hearedfrom save + route to next action
     */
    public function savePatientAction()
    {
    	$this->_expireAjax();
    	if ($this->getRequest()->isPost()) {
    		$data = $this->getRequest()->getPost('patient', array());
    		
    		//Grab the submited value heared from who and comment value
    		$_brainworx_hearedfrom = $this->getRequest()->getPost('getvoice');
    		//set Zorgpunt as default if no selection was made
    		if($_brainworx_hearedfrom == Mage::helper('checkout')->__('Select')){
    			$_brainworx_hearedfrom = "Zorgpunt";
    		}
    		//Add the seller and comment to the session
    		$seller = Mage::getModel("hearedfrom/salesForce")->loadByUsername($_brainworx_hearedfrom);
    		Mage::getSingleton('core/session')->setBrainworxHearedfrom($seller);
    		 
    		//for VAPH optional input
    		$_vaph_nr = $this->getRequest()->getPost('vaph_doc_nr');
    		if(isset($_vaph_nr))
    			Mage::getSingleton('core/session')->setVaphDocNr($_vaph_nr);
    		
            $customerAddressId = $this->getRequest()->getPost('patient_address_id', false);
            
            if (isset($data['email'])) {
    			$data['email'] = trim($data['email']);
    		}
    		if (isset($data['email2'])) {
    			$data['fax'] = trim($data['email2']);
    		}
            
            $result = $this->getOnepage()->savePatient($data, $customerAddressId);
            
            $error = $this->determineDeliveryOptions($customerAddressId);
            if($error){
            	$result['error']=$error;
            }
    
    		
    		//birthdate patient
    		if(empty($data['dob'])){
    			Mage::getSingleton('core/session')->setPatientBirthDate((new Datetime($this->getOnepage()->getQuote()->getCustomerDob()))->format("d-m-Y"));
    		}else{
    			Mage::getSingleton('core/session')->setPatientBirthDate($data['dob']);
    		}
    		Mage::getSingleton('core/session')->setPatientName($data['lastname']);
    		Mage::getSingleton('core/session')->setPatientFirstname($data['firstname']);
    		
    		$this->getOnepage()->getCheckout()->setStepData('patient', 'complete', true);
    		
    		
    		if (!isset($result['error'])) {
    			if(empty($data['use_for_billing'])){
    				$result['goto_section'] = 'billing';
    			}else{
    				$result['goto_section'] = 'shipping_method';
    				$result['duplicatePatientInfo'] = 'true';
    				$result['allow_sections'] = array('billing');
    				$result['update_section'] = array(
    						'name' => 'shipping-method',
    						'html' => $this->_getShippingMethodsHtml()
    				);
    			}
    		}
    
    		$this->getResponse()->setBody(Zend_Json::encode($result));
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
    		if (isset($data['email2'])) {
    			$data['fax'] = trim($data['email2']);
    		}
    		$result = $this->getOnepage()->saveBilling($data, $customerAddressId);
    		
    		
//     		//birthdate patient
//     		if (isset($data['day'])) {
//     			Mage::getSingleton('core/session')->setPatientBirthDate($data['day']."-".$data['month']."-".$data['year']);
//     		}    		
    		
    		//set payment info -- always payment[method]=free
//     		$paymentdata[][]= array('method'=>'free');
//     		$result = $this->getOnepage()->savePayment($paymentdata);
    		
    
    		if (!isset($result['error'])) {
    			if ($this->getOnepage()->getQuote()->isVirtual()) {
//     				$result['goto_section'] = 'payment';
//     				$result['update_section'] = array(
//     						'name' => 'payment-method',
//     						'html' => $this->_getPaymentMethodsHtml()
//     				);
    				$this->loadLayout('checkout_onepage_review');
    				$result['goto_section'] = 'review';
    				$result['update_section'] = array(
    						'name' => 'review',
    						'html' => $this->_getReviewHtml()
    				);
    			} else {
    				$result['goto_section'] = 'shipping_method';
    				$result['update_section'] = array(
    						'name' => 'shipping-method',
    						'html' => $this->_getShippingMethodsHtml()
    				);    				
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

    		//For VAPH we can skip shipping method and payment
    		if(!isset($result['error'])) {
//     			if(Mage::getSingleton('core/session')->getVAPH()){
//     				$result['goto_section'] = 'hearedfrom';
//     			}else{
// 	    			$result['goto_section'] = 'shipping_method';
    			//}
//     			$this->loadLayout('checkout_onepage_hearedfrom');
//     			$result['goto_section'] = 'hearedfrom';
    			
//     			$result['update_section'] = array(
//     					'name' => 'shipping-method',
//     					'html' => $this->_getShippingMethodsHtml()
//     			);
//     			$this->loadLayout('checkout_onepage_payment');
//     			$result['goto_section'] = 'payment';
//     			$result['update_section'] = array(
// 	            	'name' => 'payment-method',
// 	                'html' => $this->_getPaymentMethodsHtml()
// 	            );
    			$this->loadLayout('checkout_onepage_review');
    			$result['goto_section'] = 'review';
    			$result['update_section'] = array(
    					'name' => 'review',
    					'html' => $this->_getReviewHtml()
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
    		
    		$method = $this->getRequest()->getPost('shipping_method', '');
    		
    		$shipping = explode("_",$method);
    		$_usepatientaddress = $this->getRequest()->getPost($shipping[0].'_use_for_shipping');
    		$_sameasBillingaddress = $this->getRequest()->getPost('shipping_same_as_billing');
    		
    		Mage::getSingleton('customer/session')->setSeletedShipping($method);
    		
    		$result = $this->getOnepage()->saveShippingMethod($method,$_usepatientaddress,$_sameasBillingaddress);
    		
    		if (!$result) {
    			//preferred delivery date is selected from datepicker
    			//$_preferred_delivery_date = $this->getRequest()->getPost('pddate');
    			//delivery before is the date generated after picking or 24hrs or within 3 days
    			if(!empty($method))
    				$_delivery_before = $this->getRequest()->getPost($method.'_delrange');
    			$_comment_tozorgpunt = $this->getRequest()->getPost('myCustomerOrderComment');
				if(!empty($_comment_tozorgpunt)){
					Mage::getSingleton('core/session')->setOrigCommentToZorgpunt($_comment_tozorgpunt);
				}else{
					Mage::getSingleton('core/session')->setOrigCommentToZorgpunt('');
				}
    			$_vaph_nr = Mage::getSingleton('core/session')->getVaphDocNr();
    			$cmt = false;
    			if(!isset($_vaph_nr)){
    				if(!empty($_delivery_before) && strpos($_delivery_before, 'NaN') === false){
    					if(!empty($_comment_tozorgpunt)){
    						$cmt = $_comment_tozorgpunt;
    					}
    					$_comment_tozorgpunt = Mage::helper('checkout')->__('Delivery on %s',$_delivery_before);
    					if(!empty($cmt)){
    						$_comment_tozorgpunt = $_comment_tozorgpunt.' - '.$cmt;
    					}
    				}else{
    					//default delivery date = next day
    					Mage::log("DELIVERY NOT SET in frontend ".$_comment_tozorgpunt);
    					$_delivery_before=date('d-m-Y', strtotime('+1 weekday'));
    				}
    			}
    			
    			Mage::getSingleton('core/session')->setCommentToZorgpunt($_comment_tozorgpunt);
    			//set preferred delivery day with selection as made in radio buttons
    			Mage::getSingleton('core/session')->setPreferredDeliveryDate($_delivery_before);
    			Mage::getSingleton('core/session')->setDeliveryBefore($_delivery_before);
    			
    			$paymentdata= array('method'=>'banktransfer');
    			$result = $this->getOnepage()->savePayment($paymentdata);
    			
    			Mage::dispatchEvent(
    			'checkout_controller_onepage_save_shipping_method',
    			array(
    			'request' => $this->getRequest(),
    			'quote'   => $this->getOnepage()->getQuote()));
    			
    			$this->getOnepage()->getQuote()->collectTotals();
    			
    			
    			if (isset($_usepatientaddress) && $_usepatientaddress == 1){
//     				$this->loadLayout('checkout_onepage_payment');
//     				$result['goto_section'] = 'payment';
//     				$result['update_section'] = array(
// 		            	'name' => 'payment-method',
// 		                'html' => $this->_getPaymentMethodsHtml()
// 		            );
    				$this->loadLayout('checkout_onepage_review');
    				$result['goto_section'] = 'review';
    				$result['update_section'] = array(
    						'name' => 'review',
    						'html' => $this->_getReviewHtml()
    				);
    				$result['allow_sections'] = array('shipping'); //used in oppcheckout.js from skin folder
    				$result['shippingDuplicatePatientInfo'] = 'true';
    			}else{
    				$result['goto_section'] = 'shipping';
    				
    			}

    			$this->getResponse()->setBody(Mage::helper('core')->jsonEncode($result));
    		}    		
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
//         	if(empty($this->getRequest()->getPost('getvoice'))||
// 		   ((!empty($this->getRequest()->getPost('vaph_order_id')) && $this->getRequest()->getPost('vaph_order_id')!=1)
// 		     && empty($this->getRequest()->getPost('delrange')))){
//         		//	empty($this->getRequest()->getPost('pddate')||
//         		//			empty($this->getRequest()->getPost('delrange'))){
//         		$this->loadLayout('checkout_onepage_hearedfrom');
//         		$result['error'] = $this->__('Please complete all fields.');
//         		$result['goto_section'] = 'hearedfrom';
        		
//         		$this->getResponse()->setBody(Mage::helper('core')->jsonEncode($result));
//         	}
            
        	
        	
//         	$_comment_tozorgpunt = $this->getRequest()->getPost('myCustomerOrderComment');
        	
// 			$cmt = false;
// 			$_delivery_before = Mage::getSingleton('core/session')->getDeliveryBefore();
// 			if(empty($_vaph_nr)){				
// 				if(!empty($_delivery_before)){
// 					if(!empty($_comment_tozorgpunt)){
// 						Mage::getSingleton('core/session')->setOrigCommentToZorgpunt($_comment_tozorgpunt);
// 						$cmt = $_comment_tozorgpunt;
// 					}else{
// 						Mage::getSingleton('core/session')->setOrigCommentToZorgpunt('');						
// 					}
// 					$_comment_tozorgpunt = Mage::helper('checkout')->__('Delivery on %s',$_delivery_before);
// 					if(!empty($cmt)){
// 							$_comment_tozorgpunt = $_comment_tozorgpunt.' - '.$cmt;
// 					}
// 				}else{
// 					//default delivery date = next day
// 					Mage::log("ERROR -- DELIVERY NOT SET".$_comment_tozorgpunt);
// 					Mage::getSingleton('core/session')->setDeliveryBefore(date('d-m-Y', strtotime('+1 weekday')));
// 					Mage::getSingleton('core/session')->setOrigCommentToZorgpunt($_comment_tozorgpunt);
// 				}
// 			}
// 			$_comment_tozorgpunt = $this->getRequest()->getPost('myCustomerOrderComment');
			 
// 			Mage::getSingleton('core/session')->setCommentToZorgpunt($_comment_tozorgpunt);
			

			$result = array();
			$result['goto_section'] = 'payment';
			
            $result['update_section'] = array(
            	'name' => 'payment-method',
                'html' => $this->_getPaymentMethodsHtml()
            );

            $this->getResponse()->setBody(Zend_Json::encode($result));
        }
    }    
    /**
     * Create order action
     */
    public function saveOrderAction()
    {
    	if (!$this->_validateFormKey()) {
    		$this->_redirect('*/*');
    		return;
    	}
    
    	if ($this->_expireAjax()) {
    		return;
    	}
    
    	$result = array();
    	try {
    		 
    		$requiredAgreements = Mage::helper('checkout')->getRequiredAgreementIds();
    		if ($requiredAgreements) {
    			$postedAgreements = array_keys($this->getRequest()->getPost('agreement', array()));
    			$diff = array_diff($requiredAgreements, $postedAgreements);
    			if ($diff) {
    				$result['success'] = false;
    				$result['error'] = true;
    				$result['error_messages'] = $this->__('Please agree to all the terms and conditions before placing the order.');
    				$this->getResponse()->setBody(Mage::helper('core')->jsonEncode($result));
    				return;
    			}
    		}
    		$this->getOnepage()->getQuote()->collectTotals();
    		
    		$this->getOnepage()->getQuote()->setTotalsCollectedFlag(true);
    
    		$this->getOnepage()->saveOrder();
    
    		$redirectUrl = $this->getOnepage()->getCheckout()->getRedirectUrl();
    		$result['success'] = true;
    		$result['error']   = false;
    	} catch (Mage_Payment_Model_Info_Exception $e) {
    		$message = $e->getMessage();
    		if (!empty($message)) {
    			$result['error_messages'] = $message;
    		}
    		$result['goto_section'] = 'payment';
    		$result['update_section'] = array(
    				'name' => 'payment-method',
    				'html' => $this->_getPaymentMethodsHtml()
    		);
    	} catch (Mage_Core_Exception $e) {
    		Mage::logException($e);
    		Mage::helper('checkout')->sendPaymentFailedEmail($this->getOnepage()->getQuote(), $e->getMessage());
    		$result['success'] = false;
    		$result['error'] = true;
    		$result['error_messages'] = $e->getMessage();
    
    		$gotoSection = $this->getOnepage()->getCheckout()->getGotoSection();
    		if ($gotoSection) {
    			$result['goto_section'] = $gotoSection;
    			$this->getOnepage()->getCheckout()->setGotoSection(null);
    		}
    		$updateSection = $this->getOnepage()->getCheckout()->getUpdateSection();
    		if ($updateSection) {
    			if (isset($this->_sectionUpdateFunctions[$updateSection])) {
    				$updateSectionFunction = $this->_sectionUpdateFunctions[$updateSection];
    				$result['update_section'] = array(
    						'name' => $updateSection,
    						'html' => $this->$updateSectionFunction()
    				);
    			}
    			$this->getOnepage()->getCheckout()->setUpdateSection(null);
    		}
    	} catch (Exception $e) {
    		Mage::logException($e);
    		Mage::helper('checkout')->sendPaymentFailedEmail($this->getOnepage()->getQuote(), $e->getMessage());
    		$result['success']  = false;
    		$result['error']    = true;
    		$result['error_messages'] = $this->__('There was an error processing your order. Please contact us or try again later.');
    	}
    	$this->getOnepage()->getQuote()->save();
    	/**
    	 * when there is redirect to third party, we don't want to save order yet.
    	 * we will save the order in return action.
    	*/
    	if (isset($redirectUrl)) {
    		$result['redirect'] = $redirectUrl;
    	}
    
    	$this->getResponse()->setBody(Mage::helper('core')->jsonEncode($result));
    }
}
