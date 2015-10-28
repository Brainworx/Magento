<?php
/**
 * Magento
 *
 * Override to allow view order of patients (other custsomers)
 * @category    Mage
 * @package     Mage_Sales
 * @copyright  Copyright (c) 2006-2014 X.commerce, Inc. (http://www.magento.com)
 * @license    http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

/**
 * Sales orders controller
 *
 * @category   Brainworx
 * @package    Hearedfrom_controllers
 * @author     Stijn Heylen
 */
require_once(Mage::getModuleDir('controllers','Mage_Sales').DS.'OrderController.php');
class Brainworx_Hearedfrom_OrderController extends Mage_Sales_OrderController
{
	protected function _canViewOrder($order)
	{
		$customerId = Mage::getSingleton('customer/session')->getCustomerId();
		$availableStates = Mage::getSingleton('sales/order_config')->getVisibleOnFrontStates();
		if ($order->getId() && $order->getCustomerId() && ($order->getCustomerId() == $customerId)
				&& in_array($order->getState(), $availableStates, $strict = true)
		) {
			return true;
		}
		//Check if order van be viewed by zorgpuntuser
		$customer = Mage::getSingleton('customer/session')->getCustomer();
		if(!empty($customer)){
			$salesforce = Mage::getModel('hearedfrom/salesForce')->loadByCustid($customer->getEntityId());
			if(!empty($salesforce)){
				if(Mage::getModel('hearedfrom/salesSeller')->loadByOrderId($order->getIncrementId())['user_id']==$salesforce['entity_id'])
					return true;
			}
		}
		return false;
	}

   
}
