<?php
class Brainworx_Hearedfrom_RequestformController extends Mage_Core_Controller_Front_Action  {
	const XML_PATH_EMAIL_RECIPIENT  = 'contacts/email/recipient_email';
	const XML_PATH_EMAIL_SENDER     = 'contacts/email/sender_email_identity';
	const XML_PATH_EMAIL_TEMPLATE   = 'contacts/email/email_template';
	const XML_PATH_ENABLED          = 'contacts/contacts/enabled';
	
	public function preDispatch()
	{
		parent::preDispatch();
	
		if( !Mage::getStoreConfigFlag(self::XML_PATH_ENABLED) ) {
			$this->norouteAction();
		}
	}
 
    public function indexAction() { // landing page
    	
        $this->loadLayout();        
        $this->renderLayout(); 
    }
    public function requestAction() { // landing page
    	$this->loadLayout();
    	$this->renderLayout();
    }
    public function postAction()
    {
    	Mage::log("in post action request form");
    	$post = $this->getRequest()->isPost();
    	// Save data
    	if ($post) {
    		try {
    			$error = false;
    			
    			if (!Zend_Validate::is(trim($_POST['name']) , 'NotEmpty')) {
    				$error = true;
    			}
    			if (!Zend_Validate::is(trim($_POST['type_id']) , 'NotEmpty')) {
    				$error = true;
    			}
    			if (!Zend_Validate::is(trim($_POST['phone']) , 'NotEmpty')) {
    				$error = true;
    			}
    			
    			if (!Zend_Validate::is(trim($_POST['seller']) , 'NotEmpty')) {
    				$error = true;
    			}
    			    			
    			if ($error) {
    				throw new Exception();
    			}
    			
	    		//$customer = $this->_getSession()->getCustomer();
	    		$salesforce = Mage::getModel("hearedfrom/salesForce")->loadByUsername($_POST['seller']);
	    		
	    		$type = Mage::getModel('hearedfrom/requesttype')->load($_POST['type_id']);
	    		$model = Mage::getModel ( 'hearedfrom/requestform' );
// 	    		if(!empty($customer)){
// 	    			$model->setData('cust_id',$customer->getEntityId());
// 	    		}
	    		$model->setData('type_id',$_POST['type_id']);
	    		$model->setData('request',$type->getType().' - '.$type->getDescription());
	    		$model->setData('name',$_POST['name']);
	    		$model->setData('phone',$_POST['phone']);
	    		$model->setData('email',$type->getPartnerEmail());
	    		
	    		if(isset($_POST['extra'])){
	    			$model->setData('comment',$_POST['extrafieldnm'].': '.$_POST['extra'].' - '.$_POST['comment']);
	    		}else{
	    			$model->setData('comment',$_POST['comment']);
	    		}
	    		$model->setData('salesforce_id',$salesforce['entity_id']);
	    		
	    		$model->save();
	    		
	    		
	    		$postObject = new Varien_Object();
	    		$postObject->setData($_POST);
	    		
	    		// Who were sending to...
	    		$emailto = $type->getPartnerEmail();
	    		
	    		// Load our template by template_id
	    		$email_template  = Mage::getModel('core/email_template')->loadDefault('contact_requestform');
	    			
	    		// Here is where we can define custom variables to go in our email template!
	    		$email_template_variables = array(
	    				'data' => $postObject, 'request' => $type->getType().' - '.$type->getDescription(),'id' => $model->getEntityId()
	    		);
	    			
	    		$sender_name = Mage::getStoreConfig(Mage_Core_Model_Store::XML_PATH_STORE_STORE_NAME);
	    		// I'm using the general store contact here as the sender email.
	    		$sender_email = Mage::getStoreConfig('trans_email/ident_sales/email');
	    		$email_template->setSenderName($sender_name);
	    		$email_template->setSenderEmail($sender_email);
	    		$extramails =  Mage::getModel('core/variable')->setStoreId(Mage::app()->getStore()->getId())->loadByCode('REQUESTFORM_RECIPIENTS')->getValue('text');
	    		if(!empty($extramails)){
	    			$emails = explode(",",$extramails);
	    			foreach($emails as $m){
	    				$email_template->addBcc($m);
	    			}
	    		}
	    			
	    		//Send the email!
	    		$email_template->send($emailto, Mage::helper('rental')->__('Partner'), $email_template_variables);
	    			
	    		Mage::getSingleton('customer/session')->addSuccess(Mage::helper('contacts')->__('Your inquiry was submitted and will be responded to as soon as possible. Thank you for contacting us.'));
	    		
	    		$this->_redirectSuccess(Mage::getUrl('', array('_secure'=>true)));
	    		return;
    			
    		} catch (Mage_Core_Exception $e) {
    			//$this->_getSession()->addException($e, $this->__('Cannot save the form.'));
    			Mage::log("Error saving stock: ". $e->getMessage());
    			Mage::helper("hearedfrom/error")->sendErrorMail("Error saving the contact form: ". $e->getMessage());
    		} catch (Exception $e) {
    			//$this->_getSession()->addException($e, $this->__('Cannot save the form.'));
    			Mage::log("Error saving stock: ". $e->getMessage());
    			Mage::helper("hearedfrom/error")->sendErrorMail("Error saving the contact form: ". $e->getMessage());
    		}
    	}
    
    	return $this->_redirectError(Mage::getUrl('', array('_secure'=>true)));
    }
}
