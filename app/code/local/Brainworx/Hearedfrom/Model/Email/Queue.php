<?php
/**
 * Magento
 *
 * Override of Core_Email_Queue to add smtp
 * 
 * @category    Mage
 * @package     Brainworx_Hearedfrom
 */

/**
 * Email Template Mailer Model
 *
 * @method Mage_Core_Model_Email_Queue setEntityId(int $value)
 * @method Mage_Core_Model_Email_Queue setEntityType(string $value)
 * @method Mage_Core_Model_Email_Queue setEventType(string $value)
 * @method Mage_Core_Model_Email_Queue setIsForceCheck(int $value)
 * @method int getIsForceCheck()
 * @method int getEntityId()
 * @method string getEntityType()
 * @method string getEventType()
 * @method string getMessageBodyHash()
 * @method string getMessageBody()
 * @method Mage_Core_Model_Email_Queue setMessageBody(string $value)
 * @method Mage_Core_Model_Email_Queue setMessageParameters(array $value)
 * @method Mage_Core_Model_Email_Queue setProcessedAt(string $value)
 * @method array getMessageParameters()
 *
 */
class Brainworx_Hearedfrom_Model_Email_Queue extends Mage_Core_Model_Email_Queue
{
    /**
     * Send all messages in a queue
     *
     * @return Mage_Core_Model_Email_Queue
     */
    public function send()
    {
        /** @var $collection Mage_Core_Model_Resource_Email_Queue_Collection */
        $collection = Mage::getModel('core/email_queue')->getCollection()
            ->addOnlyForSendingFilter()
            ->setPageSize(self::MESSAGES_LIMIT_PER_CRON_RUN)
            ->setCurPage(1)
            ->load();
        
        /* Set up mail transport to Email Hosting Provider SMTP Server via SSL/TLS */
        $port= Mage::getModel('core/variable')->setStoreId(Mage::app()->getStore()->getId())->loadByCode('MAIL_PORT')->getValue('text');
        $smtp= Mage::getModel('core/variable')->setStoreId(Mage::app()->getStore()->getId())->loadByCode('MAIL_SMTP')->getValue('text');
        $ssl= Mage::getModel('core/variable')->setStoreId(Mage::app()->getStore()->getId())->loadByCode('MAIL_SSL')->getValue('text');
        $auth= Mage::getModel('core/variable')->setStoreId(Mage::app()->getStore()->getId())->loadByCode('MAIL_AUTH')->getValue('text');
        $user= Mage::getModel('core/variable')->setStoreId(Mage::app()->getStore()->getId())->loadByCode('MAIL_USER')->getValue('text');
        $passw= Mage::getModel('core/variable')->setStoreId(Mage::app()->getStore()->getId())->loadByCode('MAIL_PASSW')->getValue('text');
         
        $config = array(
        		'ssl'      => $ssl,      // option of none, ssl or tls
        		'port'     => $port,     // TLS 587 - SSL 465 - default 25
        		'auth'     => $auth,     // Auth type none, login, plain, CRAM-MD5
        		'username' => $user,
        		'password' => $passw
        );
        
        /* Set up transport package to host */
        $transport = new Zend_Mail_Transport_Smtp($smtp, $config);
        /* End transport setup */


//         ini_set('SMTP', Mage::getStoreConfig('system/smtp/host'));
//         ini_set('smtp_port', Mage::getStoreConfig('system/smtp/port'));

        /** @var $message Mage_Core_Model_Email_Queue */
        foreach ($collection as $message) {
        	
            if ($message->getId()) {
            	Mage::log('Preparing mail for queue via:'.$smtp.':'.$port.'/'.$ssl.'/auth: '.$auth.'/usr: '.$user, null, 'email.log');
            	 
                $parameters = new Varien_Object($message->getMessageParameters());
                if ($parameters->getReturnPathEmail() !== null) {
                    $mailTransport = new Zend_Mail_Transport_Sendmail("-f" . $parameters->getReturnPathEmail());
                    Zend_Mail::setDefaultTransport($mailTransport);
                }

                $mailer = new Zend_Mail('utf-8');
                $to = "";
                foreach ($message->getRecipients() as $recipient) {
                    list($email, $name, $type) = $recipient;
                    $to = $email.' '.$to;
                    switch ($type) {
                        case self::EMAIL_TYPE_BCC:
                            $mailer->addBcc($email, '=?utf-8?B?' . base64_encode($name) . '?=');
                            break;
                        case self::EMAIL_TYPE_TO:
                        case self::EMAIL_TYPE_CC:
                        default:
                            $mailer->addTo($email, '=?utf-8?B?' . base64_encode($name) . '?=');
                            break;
                    }
                }

                if ($parameters->getIsPlain()) {
                    $mailer->setBodyText($message->getMessageBody());
                } else {
                    $mailer->setBodyHTML($message->getMessageBody());
                }

                $mailer->setSubject('=?utf-8?B?' . base64_encode($parameters->getSubject()) . '?=');
                $mailer->setFrom($parameters->getFromEmail(), $parameters->getFromName());
                if ($parameters->getReplyTo() !== null) {
                    $mailer->setReplyTo($parameters->getReplyTo());
                }
                if ($parameters->getReturnTo() !== null) {
                    $mailer->setReturnPath($parameters->getReturnTo());
                }

                try {
                	/*Add transport object for smtp*/
                	$mailer->send($transport);
                    unset($mailer);
                    $message->setProcessedAt(Varien_Date::formatDate(true));
                    $message->save();
                    Mage::log('Mailed via queue from: ' . $parameters->getFromEmail() . ' to:' . $to . ' ' .$parameters->getSubject(), null, 'email.log');
                }
                catch (Exception $e) {
                    unset($mailer);
                    $oldDevMode = Mage::getIsDeveloperMode();
                    Mage::setIsDeveloperMode(true);
                    Mage::logException($e);
                    Mage::setIsDeveloperMode($oldDevMode);

                    Mage::helper("hearedfrom/error")->sendErrorMail('Probleem versturen mail van queue - '.$e->getMessage());
                    Mage::log('Mailed via queue error ', null, 'email.log');
                     
                    return false;
                }

                Mage::log('Mailed via queue end',null, 'email.log');
            }
        }
         
        return $this;
    }
}
