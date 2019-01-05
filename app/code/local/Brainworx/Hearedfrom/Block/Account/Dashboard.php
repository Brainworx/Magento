<?php
/**
 * Magento
 *
 */

/**
 * Customer dashboard block
 *
 * @category   Mage
 * @package    Mage_Customer
 * @author      Magento Core Team <core@magentocommerce.com>
 */
class Brainworx_Hearedfrom_Block_Account_Dashboard extends Mage_Customer_Block_Account_Dashboard
{
	function getOrders(){
		/*query
		 * */
		$customer = Mage::getSingleton('customer/session')->getCustomer();
		if(!empty($customer)){
			$salesforce = Mage::getModel('hearedfrom/salesForce')->loadByCustid($customer->getEntityId());
			if(!empty($salesforce)){
	
				$collection = Mage::getModel('sales/order')->getCollection();
					
				// add joined data to the collection
					
				$select = $collection->getSelect();
				$resource = Mage::getSingleton('core/resource');
			  
				$select->join(array('seller' => $resource->getTableName('hearedfrom/salesSeller')),
						'main_table.increment_id = seller.order_id',
						array('user_id'));
			  
				$collection->addFieldToFilter('user_id',
						Mage::getModel('hearedfrom/salesForce')->loadByCustid($customer->getEntityId())['entity_id']);
				//fiter out cancelled orders
				$collection->addFieldToFilter('status', array('nlike' => 'canceled'));
	
				$collection->setOrder('increment_id');
			}else{
				 
				//normal customer
				$collection = Mage::getResourceModel('sales/order_collection')
				->addFieldToSelect('*')
				->addFieldToFilter('customer_id', Mage::getSingleton('customer/session')->getCustomer()->getId())
				->addFieldToFilter('state', array('in' => Mage::getSingleton('sales/order_config')->getVisibleOnFrontStates()))
				->setOrder('created_at', 'desc')
				;
			}
		}
		return $collection;
	}
}
