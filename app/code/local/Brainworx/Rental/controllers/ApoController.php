<?php
class Brainworx_Rental_ApoController extends Mage_Core_Controller_Front_Action
{
	//Make sure the user is logged in and redirect to login page first if required
	public function preDispatch()
	{
		parent::preDispatch();
		$action = $this->getRequest()->getActionName();
		$loginUrl = Mage::helper('customer')->getLoginUrl();
	
		if (!Mage::getSingleton('customer/session')->authenticate($this, $loginUrl)) {
			$this->setFlag('', self::FLAG_NO_DISPATCH, true);
		}
	}
	/**
	 * Action to call via mysite/rental/apo/terminate
	 */
	public function terminateAction(){
		$error = false;
		//double check login
		if(Mage::getSingleton('customer/session')->isLoggedIn()){

			$input = $this->getRequest()->getPost('items');
			$orderid = $this->getRequest()->getPost('realorderid');
			$order = Mage::getModel('sales/order')->loadByIncrementId($orderid);
			$items = explode("-",$input);
			$preferredDT = date('d-m-Y', strtotime('+3 Weekdays'));
			
			$rentalstoend=array();
			//add loop over input elements
			foreach($items as $itemid){
				$rental = Mage::getModel("rental/rentedItem")->loadByOrderItem($itemid);
				$rentalstoend[]= $rental["entity_id"];
			}

			$success = Mage::helper('rental/terminator')->TerminateRentals($preferredDT,$rentalstoend,$order,null,true);
			
			$response = array();
			$response['success'] = $success;
			
			$response['message'] = Mage::helper('rental')->__('De verhuur van de geselecteerde items werd beeindigd.');
			if (!$success) {
				Mage::log("ending rentals but some error occurred - ".$orderid);
				$response['message'] = Mage::helper('rental')->__('Er liep iets fout, gelieve het resultaat te controleren of contact op te nemen met Zorgpunt.');
				try{
					Mage::helper('rental/error')->sendErrorMail('Probleem end rental from account order '.$orderid);
				}catch (Exception $e){Mage::log("Error sending error mail - account end rental ".$orderid);}
			}
			Mage::log("ending rentals from account finished - ".$orderid);
			
			$order->addStatusHistoryComment("Verhuur beeindigd vanuit account voor verhuuritems: ".implode(",",$rentalstoend)." ophaling op ".$preferredDT,false)->save();
				
			$this->getResponse()->setBody(Mage::helper('core')->jsonEncode($response));
		}
	}	
}