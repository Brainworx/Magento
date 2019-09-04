<?php

class Brainworx_Hearedfrom_Helper_Error extends Mage_Core_Helper_Abstract{

	function sendErrorMail($info){
		try{
			// This is the template name from your etc/config.xml
			$template_id = 'problem_zorgpunt';
			$storeId = Mage::app()->getStore()->getId();
		
			// Who were sending to...
			$email_to = 'info@brainworx.be';
			// Load our template by template_id
			$email_template  = Mage::getModel('core/email_template')->loadDefault($template_id);
		
			// Here is where we can define custom variables to go in our email template!
			$email_template_variables = array(
					'info'        => $info
			);
		
			// I'm using the Store Name as sender name here.
			$sender_name = Mage::getStoreConfig(Mage_Core_Model_Store::XML_PATH_STORE_STORE_NAME);
			// I'm using the general store contact here as the sender email.
			$sender_email = Mage::getStoreConfig('trans_email/ident_sales/email');
			$email_template->setSenderName($sender_name);
			$email_template->setSenderEmail($sender_email);
		
			//Send the email!
			$email_template->send($email_to, Mage::helper('hearedfrom')->__('Technicalsupport'), $email_template_variables);
				
		}catch(Exception $e){
			Mage::log('fout bij verzenden problem mail: '.$e->getMessage());
		}
	}
}
