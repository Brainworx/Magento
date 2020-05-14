<?php

class Brainworx_Hearedfrom_Helper_Error extends Mage_Core_Helper_Abstract{
	/** Identifier for history item
	 */
	const ENTITY_ERROR                              = 'error';	
	/**
	 * Event type names for order emails
	 */
	const EMAIL_EVENT_ERROR    = 'new_error';

	function sendErrorMail($info){
		try{
			// This is the template name from your etc/config.xml
			$template_id = 'problem_zorgpunt';
			$storeId = Mage::app()->getStore()->getId();
		
			// Who were sending to...
			$email_to = array('info@brainworx.be');
			
			// Here is where we can define custom variables to go in our email template!
			$email_template_variables = array(
					'info'        => $info
			);
			
			Mage::helper("hearedfrom/mailer")->sendMailViaQueue($email_to,$storeId,$template_id,$email_template_variables,self::ENTITY_ERROR, null,self::EMAIL_EVENT_ERROR,null,true);
			
				
		}catch(Exception $e){
			Mage::log('fout bij verzenden problem mail: '.$e->getMessage());
		}
	}
}
