<?php
/**
 * MageWorx
 * Admin Order Editor extension
 *
 * @category   MageWorx
 * @package    MageWorx_OrdersEdit
 * @copyright  Copyright (c) 2015 MageWorx (http://www.mageworx.com/)
 */

class MageWorx_OrdersEdit_Model_Paypal_Direct extends MageWorx_OrdersEdit_Model_Paypal_Direct_Abstract
{
    /**
     * @return $this|Mage_Payment_Model_Abstract
     */
    public function validate() {
        if (Mage::app()->getRequest()->getControllerName()=='ordersedit_order_edit') {
            $paymentData = Mage::app()->getRequest()->getPost('payment');
            // if method=='paypal_direct' - must be card number to validate payment data
            if ($paymentData && isset($paymentData['method']) && $paymentData['method']=='paypal_direct' && !isset($paymentData['cc_number'])) {
                return $this;
            }
        }        
        return parent::validate();
    }
}
