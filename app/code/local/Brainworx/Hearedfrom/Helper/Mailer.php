<?php
class Brainworx_Hearedfrom_Helper_Mailer extends Mage_Core_Helper_Abstract{
	
	/**
	 * 
	 * @param $emails_to (, separated list of email addresses to send to)
	 * @param $names_to (, separated list of email addresses to send to - optional)
	 * @param $template_id (from etc/config.xml
	 * @param $email_template_variables (array of variables as required for the template) 
	 * @param $sender_name (optional)
	 * @param $sender_email (optional)
	 * @param $emails_bcc (optional, komma separated list of email addresses to send to)
	 * @param string $file (optional to attache)
	 * @param string $filename (optional to attache)
	 */
	public function sendMail($emails_to,$template_id,$email_template_variables,$names_to=null,$sender_name=null,$sender_email=null,$emails_bcc=null,$file = null, $filename=null){
		try{
			//send email
			// This is the template name from your etc/config.xml
			
			$storeId = Mage::app()->getStore()->getId();
			
			//send new shipment email to supplier
			
			// Who were sending to...
			$email_to = explode(",",$emails_to);
			
			// Load our template by template_id
			$email_template  = Mage::getModel('core/email_template')->loadDefault($template_id);
						
			if(empty($sender_name)){
				// I'm using the Store Name as sender name here.
				$sender_name = Mage::getStoreConfig(Mage_Core_Model_Store::XML_PATH_STORE_STORE_NAME);
				
			}
			if(empty($sender_email)){
				// I'm using the general store contact here as the sender email.
				$sender_email = Mage::getStoreConfig('trans_email/ident_sales/email');
			}
			$email_template->setSenderName($sender_name);
			$email_template->setSenderEmail($sender_email);

			$bcc = explode(",",$emails_bcc);
			foreach ($bcc as $bccmail ){
				$email_template->addBcc($bccmail);
			}
			
			//Add attachement
			if(!empty($file)){
				$fileContents = file_get_contents($file);
				$attachment = $email_template->getMail()->createAttachment($fileContents);
				$attachment->filename = $filename;
			}
			
			//Send the email!
			$email_template->send($email_to, $names_to, $email_template_variables);
			
			$log = 'Email '.$template_id.' sent: from '.$sender_email.' ('.$sender_name.') to '.$emails_to;
			if(!empty($emails_bcc)){
				$log = $log.' bcc '.$emails_bcc;
			}
			if(!empty($file)){
				$log = $log.' - with attachement '.$filename;
			}
			Mage::log($log, null, 'email.log');
		
		
		}catch(Exception $e){
			Mage::log('Fout create mail: ' . $e->getMessage(), null, 'email.log');
		
			Mage::helper("hearedfrom/error")->sendErrorMail('Probleem creatie mail('.$template_id.' - '.$e->getMessage());
		}
	}
}
