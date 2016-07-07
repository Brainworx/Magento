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
        			empty($this->getRequest()->getPost('pddate')||
        					empty($this->getRequest()->getPost('delrange')))){
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
        	$_preferred_delivery_date = $this->getRequest()->getPost('pddate');
        	$_delivery_before = $this->getRequest()->getPost('delrange');
        	Mage::getSingleton('core/session')->setDeliveryBefore($_delivery_before);
        	$_comment_tozorgpunt = $this->getRequest()->getPost('myCustomerOrderComment');
        	//Add the seller and comment to the session
        	$seller = Mage::getModel("hearedfrom/salesForce")->loadByUsername($_brainworx_hearedfrom);
			Mage::getSingleton('core/session')->setBrainworxHearedfrom($seller);
			$cmt = false;
			if(!empty($_preferred_delivery_date)){
				if(!empty($_comment_tozorgpunt)){
					Mage::getSingleton('core/session')->setOrigCommentToZorgpunt($_comment_tozorgpunt);
					$cmt = $_comment_tozorgpunt;
				}else{
					Mage::getSingleton('core/session')->setOrigCommentToZorgpunt('');						
				}
				Mage::getSingleton('core/session')->setPreferredDeliveryDate($_preferred_delivery_date);
				//
				if($_preferred_delivery_date != $_delivery_before)
					$_comment_tozorgpunt = Mage::helper('checkout')->__('Delivered between %s and %s',$_preferred_delivery_date,$_delivery_before);
				else 
					$_comment_tozorgpunt = Mage::helper('checkout')->__('Preferred delivery date:').' '.$_preferred_delivery_date;
				if(!empty($cmt)){
						$_comment_tozorgpunt = $_comment_tozorgpunt.' - '.$cmt;
				}
			}else{
				//default delivery date = next day
				Mage::getSingleton('core/session')->setPreferredDeliveryDate(date('d-m-Y', strtotime('+1 day')));
				Mage::getSingleton('core/session')->setOrigCommentToZorgpunt($_comment_tozorgpunt);
			}
			Mage::getSingleton('core/session')->setCommentToZorgpunt($_comment_tozorgpunt);

			$result = array();
            
//             $redirectUrl = $this->getOnePage()->getQuote()->getPayment()->getCheckoutRedirectUrl();
//             if (!$redirectUrl) {
//                 $this->loadLayout('checkout_onepage_review');

//                 $result['goto_section'] = 'review';
//                 $result['update_section'] = array(
//                     'name' => 'review',
//                     'html' => $this->_getReviewHtml()
//                 );
                $result['goto_section'] = 'payment';
                $result['update_section'] = array(
                		'name' => 'payment-method',
                		'html' => $this->_getPaymentMethodsHtml()
                );

//             }

//             if ($redirectUrl) {
//                 $result['redirect'] = $redirectUrl;
//             }

            $this->getResponse()->setBody(Zend_Json::encode($result));
        }
    }    
}
