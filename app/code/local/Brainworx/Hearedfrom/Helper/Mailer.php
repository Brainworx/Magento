<?php
class Brainworx_Hearedfrom_Helper_Mailer extends Mage_Core_Helper_Abstract{
	
	/**
	 * @return success
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
		$success = false;
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
			$success = $email_template->send($email_to, $names_to, $email_template_variables);
			
			$log = 'Email '.$template_id.' sent: from '.$sender_email.' ('.$sender_name.') to '.$emails_to;
			if(!empty($emails_bcc)){
				$log = $log.' bcc '.$emails_bcc;
			}
			if(!empty($file)){
				$log = $log.' - with attachement '.$filename;
			}
			$log .= ' - success: '.$success;
			Mage::log($log, null, 'email.log');
		
		
		}catch(Exception $e){
			Mage::log('Fout create mail: ' . $e->getMessage(), null, 'email.log');		
			Mage::helper("hearedfrom/error")->sendErrorMail('Probleem creatie mail('.$template_id.' - '.$e->getMessage());
		}
		
		return $success;
	}
	/**
	 *
	 * @param $emails_to (array of email addresses to send to)
	 * @param $storeId 
	 * @param $template_id (from etc/config.xml
	 * @param $email_template_variables (array of variables as required for the template)
	 * @param $entity (type of entity to link email to  example order)store in table core_email_queue
	 * @param $entityobject (may be NULL or object to link email to)store in table core_email_queue
	 * @param $eventtype (type of event to link email to example new_delivery, new_order,...) store in table core_email_queue
	 * @param $emails_bcc (optional, komma separated list of email addresses to send to)
	 * @param $errormail (optional, indicate this is an error mail to prevent error mail on failure)
	 */
	public function sendMailViaQueue($emails_to,$storeId,$template_id,$email_template_variables,$entity, $entityobject,$eventtype,$emails_bcc=null,$errormail=false){
		try{
			$forceMode=false;
			$log = 'Prepare '.$eventtype.': add email '.$template_id.' sent to '.implode(",",$emails_to).' to queue';
			if(!empty($entityobject)){
				$log.=' for '.$entity.' '.$entityobject->getId();
			}
			if(!empty($emails_bcc)){
				$log = $log.' bcc '.$emails_bcc;
			}
			Mage::log($log, null, 'email.log');
			
			/** @var $mailer Mage_Core_Model_Email_Template_Mailer */
			$mailer = Mage::getModel('core/email_template_mailer');
			
			$count = 0;
			foreach ($emails_to as $email) {
				$emailInfo = Mage::getModel('core/email_info');
				$emailInfo->addTo($email);
				if($count == 0){
					//addd bcc with first email
					if(!empty($emails_bcc)){
						$bcc = is_array($emails_bcc) ? $emails_bcc : array($emails_bcc);					
						foreach ($bcc as $emailcc) {
		    				$emailInfo->addBcc($emailcc);
		    			}
					}
				}
				$mailer->addEmailInfo($emailInfo);
				$count++;
			}
			
			// Set all required params and send emails
			$mailer->setSender(Mage::getStoreConfig('sales_email/order/identity', $storeId));
			$mailer->setStoreId($storeId);
			$mailer->setTemplateId($template_id);
			$mailer->setTemplateParams($email_template_variables);
			
			/** @var $emailQueue Mage_Core_Model_Email_Queue */
			$emailQueue = Mage::getModel('core/email_queue');
			if(!empty($entityobject)){
				$emailQueue->setEntityId($entityobject->getId());
			}
			$emailQueue->setEntityType($entity)
			->setEventType($eventtype)
			->setIsForceCheck(!$forceMode);
			
			$mailer->setQueue($emailQueue)->send();
			
			$log = 'Email '.$eventtype.' '.$template_id.' sent to '.implode(",",$emails_to).' added to queue';
			if(!empty($entityobject)){
				$log.=' for '.$entity.' '.$entityobject->getId();
			}
			if(!empty($emails_bcc)){
				$log = $log.' - bcc '.$emails_bcc;
			}
			Mage::log($log, null, 'email.log');

		}catch(Exception $e){
			Mage::log('Fout create mail for '.$entity.' '.$entityobject->getId().': ' . $e->getMessage(), null, 'email.log');
			if(!$errormail)			
				Mage::helper("hearedfrom/error")->sendErrorMail('Probleem creatie mail for '.$entity.' ('.$template_id.' - '.$e->getMessage());
		}
	}
}
