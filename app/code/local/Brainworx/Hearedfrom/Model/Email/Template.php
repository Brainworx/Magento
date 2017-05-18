<?php
/**
 * Magento
 *
 * Override of Mage_Core_Model_Email_Template model
 */

/**
 * Template model
 *
 * Example:
 *
 * // Loading of template
 * $emailTemplate  = Mage::getModel('core/email_template')
 *    ->load(Mage::getStoreConfig('path_to_email_template_id_config'));
 * $variables = array(
 *    'someObject' => Mage::getSingleton('some_model')
 *    'someString' => 'Some string value'
 * );
 * $emailTemplate->send('some@domain.com', 'Name Of User', $variables);
 *
 * @method Mage_Core_Model_Resource_Email_Template _getResource()
 * @method Mage_Core_Model_Resource_Email_Template getResource()
 * @method string getTemplateCode()
 * @method Mage_Core_Model_Email_Template setTemplateCode(string $value)
 * @method string getTemplateText()
 * @method Mage_Core_Model_Email_Template setTemplateText(string $value)
 * @method string getTemplateStyles()
 * @method Mage_Core_Model_Email_Template setTemplateStyles(string $value)
 * @method int getTemplateType()
 * @method Mage_Core_Model_Email_Template setTemplateType(int $value)
 * @method string getTemplateSubject()
 * @method Mage_Core_Model_Email_Template setTemplateSubject(string $value)
 * @method string getTemplateSenderName()
 * @method Mage_Core_Model_Email_Template setTemplateSenderName(string $value)
 * @method string getTemplateSenderEmail()
 * @method Mage_Core_Model_Email_Template setTemplateSenderEmail(string $value)
 * @method string getAddedAt()
 * @method Mage_Core_Model_Email_Template setAddedAt(string $value)
 * @method string getModifiedAt()
 * @method Mage_Core_Model_Email_Template setModifiedAt(string $value)
 * @method string getOrigTemplateCode()
 * @method Mage_Core_Model_Email_Template setOrigTemplateCode(string $value)
 * @method string getOrigTemplateVariables()
 * @method Mage_Core_Model_Email_Template setOrigTemplateVariables(string $value)
 * @method Mage_Core_Model_Email_Template setQueue(Mage_Core_Model_Abstract $value)
 * @method Mage_Core_Model_Email_Queue getQueue()
 * @method int hasQueue()
 *
 * @category    Mage
 * @package     Brainworx_Hearedfrom
 * @author      Stijn Heylen
 */
class Brainworx_Hearedfrom_Model_Email_Template extends Mage_Core_Model_Email_Template
{
    
    /**
     * Send mail to recipient
     *
     * @param   array|string       $email        E-mail(s)
     * @param   array|string|null  $name         receiver name(s)
     * @param   array              $variables    template variables
     * @return  boolean
     **/
    public function send($email, $name = null, array $variables = array())
    {
        if (!$this->isValidForSend()) {
            Mage::logException(new Exception('This letter cannot be sent.')); // translation is intentionally omitted
            return false;
        }
        
        /* Set up mail transport to Email Hosting Provider SMTP Server via SSL/TLS */
        $port= Mage::getModel('core/variable')->setStoreId(Mage::app()->getStore()->getId())->loadByCode('MAIL_PORT')->getValue('text');
        $smtp= Mage::getModel('core/variable')->setStoreId(Mage::app()->getStore()->getId())->loadByCode('MAIL_SMTP')->getValue('text');
        $ssl= Mage::getModel('core/variable')->setStoreId(Mage::app()->getStore()->getId())->loadByCode('MAIL_SSL')->getValue('text');
        $auth= Mage::getModel('core/variable')->setStoreId(Mage::app()->getStore()->getId())->loadByCode('MAIL_AUTH')->getValue('text');
        $user= Mage::getModel('core/variable')->setStoreId(Mage::app()->getStore()->getId())->loadByCode('MAIL_USER')->getValue('text');
        $passw= Mage::getModel('core/variable')->setStoreId(Mage::app()->getStore()->getId())->loadByCode('MAIL_PASSW')->getValue('text');
         
        Mage::log('Preparing mail via:'.$smtp.':'.$port.'/'.$ssl.'/auth: '.$auth.'/usr: '.$user, null, 'email.log');
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

        $emails = array_values((array)$email);
        $names = is_array($name) ? $name : (array)$name;
        $names = array_values($names);
        foreach ($emails as $key => $email) {
            if (!isset($names[$key])) {
                $names[$key] = substr($email, 0, strpos($email, '@'));
            }
        }

        $variables['email'] = reset($emails);
        $variables['name'] = reset($names);

        $this->setUseAbsoluteLinks(true);
        $text = $this->getProcessedTemplate($variables, true);
        $subject = $this->getProcessedTemplateSubject($variables);

        $setReturnPath = Mage::getStoreConfig(self::XML_PATH_SENDING_SET_RETURN_PATH);
        switch ($setReturnPath) {
            case 1:
                $returnPathEmail = $this->getSenderEmail();
                break;
            case 2:
                $returnPathEmail = Mage::getStoreConfig(self::XML_PATH_SENDING_RETURN_PATH_EMAIL);
                break;
            default:
                $returnPathEmail = null;
                break;
        }

        if ($this->hasQueue() && $this->getQueue() instanceof Mage_Core_Model_Email_Queue) {
            /** @var $emailQueue Mage_Core_Model_Email_Queue */
            $emailQueue = $this->getQueue();
            $emailQueue->setMessageBody($text);
            $emailQueue->setMessageParameters(array(
                    'subject'           => $subject,
                    'return_path_email' => $returnPathEmail,
                    'is_plain'          => $this->isPlain(),
                    'from_email'        => $this->getSenderEmail(),
                    'from_name'         => $this->getSenderName(),
                    'reply_to'          => $this->getMail()->getReplyTo(),
                    'return_to'         => $this->getMail()->getReturnPath(),
                ))
                ->addRecipients($emails, $names, Mage_Core_Model_Email_Queue::EMAIL_TYPE_TO)
                ->addRecipients($this->_bccEmails, array(), Mage_Core_Model_Email_Queue::EMAIL_TYPE_BCC);
            $emailQueue->addMessageToQueue();
            
            Mage::log('Mail added to queue from: ' . $this->getSenderEmail() . ' to:' . implode(" ",$emails) . ' ' .$this->getProcessedTemplateSubject($variables), null, 'email.log');
            

            return true;
        }

//         ini_set('SMTP', Mage::getStoreConfig('system/smtp/host'));
//         ini_set('smtp_port', Mage::getStoreConfig('system/smtp/port'));

        $mail = $this->getMail();

        if ($returnPathEmail !== null) {
            $mailTransport = new Zend_Mail_Transport_Sendmail("-f".$returnPathEmail);
            Zend_Mail::setDefaultTransport($mailTransport);
        }

        foreach ($emails as $key => $email) {
            $mail->addTo($email, '=?utf-8?B?' . base64_encode($names[$key]) . '?=');
        }

        if ($this->isPlain()) {
            $mail->setBodyText($text);
        } else {
            $mail->setBodyHTML($text);
        }

        $mail->setSubject('=?utf-8?B?' . base64_encode($subject) . '?=');
        $mail->setFrom($this->getSenderEmail(), $this->getSenderName());

        try {
        	/*Add transport object for smtp*/
            $mail->send($transport);
            $this->_mail = null;
            Mage::log('Mailed from: ' . $this->getSenderEmail() . ' to:' . implode(" ",$emails) . ' ' .$this->getProcessedTemplateSubject($variables), null, 'email.log');
        }
        catch (Exception $e) {
            $this->_mail = null;
            Mage::logException($e);
            Mage::helper("hearedfrom/error")->sendErrorMail('Probleem versturen mail - '.$e->getMessage());
            Mage::log('Mail error '.$e->getMessage(), null, 'email.log');
             
            return false;
        }
        Mage::log('Mail end ', null, 'email.log');
        
        return true;
    }
}