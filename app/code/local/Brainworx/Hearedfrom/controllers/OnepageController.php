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

    public function savePaymentAction()
    {
        $this->_expireAjax();
        if ($this->getRequest()->isPost()) {
            $data = $this->getRequest()->getPost('payment', array());
            /*
            * first to check payment information entered is correct or not
            */

            try {
                $result = $this->getOnepage()->savePayment($data);
            }
            catch (Mage_Payment_Exception $e) {
                if ($e->getFields()) {
                    $result['fields'] = $e->getFields();
                }
                $result['error'] = $e->getMessage();
            }
            catch (Exception $e) {
                $result['error'] = $e->getMessage();
            }
            $redirectUrl = $this->getOnePage()->getQuote()->getPayment()->getCheckoutRedirectUrl();
            if (empty($result['error']) && !$redirectUrl) {
				$this->loadLayout('checkout_onepage_hearedfrom');

                $result['goto_section'] = 'hearedfrom';
            }

            if ($redirectUrl) {
                $result['redirect'] = $redirectUrl;
            }

            $this->getResponse()->setBody(Zend_Json::encode($result));
        }
    }

    public function saveHearedfromAction()
    {
        $this->_expireAjax();
        if ($this->getRequest()->isPost()) {
            
        	//Grab the submited value heared from who and comment value
        	$_brainworx_hearedfrom = $this->getRequest()->getPost('getvoice');
        	//set Zorgpunt as default if no selection was made
        	if($_brainworx_hearedfrom == Mage::helper('checkout')->__('Select')){
        		$_brainworx_hearedfrom = "Zorgpunt";
        	}
        	$_preferred_delivery_date = $this->getRequest()->getPost('pddate');
        	$_comment_tozorgpunt = $this->getRequest()->getPost('myCustomerOrderComment');
        	//Add the seller and comment to the session
        	$seller = Mage::getModel("hearedfrom/salesForce")->loadByUsername($_brainworx_hearedfrom);
			Mage::getSingleton('core/session')->setBrainworxHearedfrom($seller);
			$cmt = false;
			if(!empty($_preferred_delivery_date)){
				Mage::getSingleton('core/session')->setPreferredDeliveryDT($_preferred_delivery_date);
				if(!empty($_comment_tozorgpunt)){
					Mage::getSingleton('core/session')->setOrigCommentToZorgpunt($_comment_tozorgpunt);
					$cmt = $_comment_tozorgpunt;
				}else{
					Mage::getSingleton('core/session')->setOrigCommentToZorgpunt('');						
				}
				Mage::getSingleton('core/session')->setPreferredDeliveryDate($_preferred_delivery_date);
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
            
            $redirectUrl = $this->getOnePage()->getQuote()->getPayment()->getCheckoutRedirectUrl();
            if (!$redirectUrl) {
                $this->loadLayout('checkout_onepage_review');

                $result['goto_section'] = 'review';
                $result['update_section'] = array(
                    'name' => 'review',
                    'html' => $this->_getReviewHtml()
                );

            }

            if ($redirectUrl) {
                $result['redirect'] = $redirectUrl;
            }

            $this->getResponse()->setBody(Zend_Json::encode($result));
        }
    }    
}
