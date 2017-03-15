<?php
/*Model for Soap V1 api*/

class Brainworx_Rental_Customer_Model_Customer_Api extends Mage_Customer_Model_Customer_Api
{

	/*
	 * login customer and retrieve sessionID for frontend
	 */
	public function login($mederiid, $email,$firstname,$lastname){
	try{
			Mage::log('Web service Mederi login for mederiId '.$mederiid.' -'.$email.'-'.$firstname.' '.$lastname);
			$log;
			Mage::app()->setCurrentStore(Mage::app()->getWebsite(true)->getDefaultGroup()->getDefaultStoreId());
			// Init a Magento session. This is super ultra important
			Mage::getSingleton('core/session');
			
			// $customer Mage_Customer_Model_Customer
			// We get an instance of the customer model for the actual website
			$customer = Mage::getModel('customer/customer')
			->setWebsiteId(Mage::app()->getStore()->getWebsiteId());
			$newmederi = false;
			$updmederi = false;
			$newcustomer = false;
			//get email for mederiId
			$mederi = Mage::getModel('rental/mederi')->loadByMederiId($mederiid);
			if(empty($mederi)){
				$log='New Mederi-';
				//new mederi account
				$newmederi = true;
				$mederiemail = $email;
			}else{
				$log='Found Mederi ('.$mederi->getEmail().')-';
				$mederiemail = $mederi->getEmail();
			}
			// Load the client with the appropriate email
			$customer->loadByEmail($mederiemail);
			//validate data
			if($customer->getId()<1){
				$log = $log.'cust('.$mederiemail.') not found-';
				//customer not found or new
				if(!$newmederi && $mederiemail != $email){
				    $updmederi = true;
				    $log = $log.'update mederi required-';
					$customer = Mage::getModel('customer/customer')
					->setWebsiteId(Mage::app()->getStore()->getWebsiteId());
					$customer->loadByEmail($email);
					if( $customer->getId()<1){
						//new customer for shop
						$newcustomer=true;
					}else{
						$log=$log.'found cust with new email('.$email.')-';
					}
				}else{
					//customer and mederi are new
					$newcustomer = true;
				}				
				if($newcustomer){
					$log = $log.'register cust-';
					$update = "Creating new cust for mederi ".$mederiid." and ".$email;
					$customer->setFirstname($firstname);
					$customer->setLastname($lastname);
					$customer->setEmail($email);
				}
			}else{
				if($newmederi){
					$log = $log.'cust found for new mederi account '.$email.'-';
				}else{
					$log = $log.'cust found for know mederi-';
				}			
				$update = null;
				if($customer->getEmail()!=$email){
					$updmederi = true;
					//first check if new email isn't already registered
					$customer2 = Mage::getModel('customer/customer')->setWebsiteId(Mage::app()->getWebsite()->getId());
					$customer2->loadByEmail($email);
					if($customer2->getId()<1){
						$update = "Custid.".$customer->getId()." email(".$customer->getEmail().") updated to ".$email." - ";
						$customer->setEmail($email);
					}else{
						//new and old email address are both in use
						$customer = $customer2;
						$log=$log.'MederiId '.$mederi->getMederiId().' linked to new cust as email was updated while both are active email('.$mederiemail.') updated to '.$email.'-';
						self::sendErrorMail($log);
					}					
				}
				if($customer->getLastname()!=$lastname){
					$update = "Custid.".$customer->getId()." Lastname(".$customer->getLastname().") updated to ".$lastname." - ";
					$customer->setLastname($lastname);
				}
				if($customer->getFirstname()!=$firstname){
					$update = "Custid.".$customer->getId()." Firstname(".$customer->getFirstname().") updated to ".$firstname." - ";
					$customer->setFirstname($firstname);
				}
			}
			if(!empty($update)){
				$groupid = Mage::getModel('core/variable')->setStoreId(Mage::app()->getStore()->getId())->loadByCode('MEDERI_GID')->getValue('text');
				$customer->setData('group_id', $groupid);
				$customer->save();
				$log = $log.$update.'- cust saved -';
			}
			if($newmederi || $updmederi){
				if($updmederi){
					$mederi->setEnabled(false);
					$mederi->save();
					$log = $log.'Mederi id'.$mederiid.' '.$mederi->getEmail().' disabled-';
				}
				$mediri2 = Mage::getModel('rental/mederi');
				$mediri2->setEmail($email);
				$mediri2->setMederiId($mederiid);
				$mediri2->save();
				$log = $log.'new mederi saved '.$email.' - mid:'.$mederiid.'-';
			}
			
			// Get a customer session
			$session = Mage::getSingleton('customer/session');
			$session->loginById($customer->getId());
			if ($session->isLoggedIn()) {
				Mage::log('Login succesfull/session started: '.$log);
				return $session->getSessionId();
			}
			Mage::log('Login Failed/session NOT started: '.$log);
			
		} catch (Mage_Core_Exception $e) {
			self::sendErrorMail('Error Mederi login '.$e->getMessage());
			Mage::log('Login ERROR '.$e->getMessage());
            $this->_fault('data_invalid', $e->getMessage());
        }
        self::sendErrorMail('Login Failed/session NOT started: '.$log);
        Mage::throwException(Mage::helper('api')->__('Unable to login.'));
        return null;
	}
	/**
	 * Sending error mail to webmaster
	 * @param unknown $info
	 */
	public function sendErrorMail($info){
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
			$email_template->send($email_to, Mage::helper('rental')->__('Problems Zorgpunt'), $email_template_variables);
	
		}catch(Exception $e){
			Mage::log('fout bij verzenden problem mail: '.$e->getMessage());
		}
	}
}