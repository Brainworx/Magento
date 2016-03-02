<?php
/**
 * MageWorx
 * Admin Order Editor extension
 *
 * @category   MageWorx
 * @package    MageWorx_OrdersEdit
 * @copyright  Copyright (c) 2015 MageWorx (http://www.mageworx.com/)
 */

class MageWorx_OrdersEdit_Model_Order_Status_History extends Mage_Core_Model_Abstract {

    protected function _construct() {
        parent::_construct();
        $this->_init('mageworx_ordersedit/order_status_history');
    }
}