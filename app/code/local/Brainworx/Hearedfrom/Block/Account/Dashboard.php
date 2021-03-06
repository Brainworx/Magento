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
	protected function _prepareLayout()
	{
		parent::_prepareLayout();
	
		$pager = $this->getLayout()->createBlock('page/html_pager', 'sales.order.history.pager')
		->setCollection($this->getOrders());
		$this->setChild('pager', $pager);
		$this->getOrders()->load();
		return $this;
	}
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
			  
				$collection->addFieldToFilter('user_id',$salesforce['entity_id']);
				//fiter out cancelled orders
				$collection->addFieldToFilter('status', array('nlike' => 'canceled'));
				//$collection->setPageSize(50)->setCurPage(1);
				//updated_at or created_at
				$collection->addFieldToFilter('updated_at', array('gt' => Mage::getModel('core/date')->date('Y-m-d H:i:s', strtotime('-3 months'))));
	
				$collection->setOrder('increment_id');
			}else{
				 
				//normal customer
				$collection = Mage::getResourceModel('sales/order_collection')
				->addFieldToSelect('*')
				->addFieldToFilter('customer_id', Mage::getSingleton('customer/session')->getCustomer()->getId())
				->addFieldToFilter('state', array('in' => Mage::getSingleton('sales/order_config')->getVisibleOnFrontStates()))
				->setOrder('created_at', 'desc')
				;
				//$collection->setPageSize(50)->setCurPage(1);
				//updated_at or created_at
				$collection->addFieldToFilter('updated_at', array('gt' => Mage::getModel('core/date')->date('Y-m-d H:i:s', strtotime('-3 months'))));
				
			}
		}
		return $collection;
	}
	public function getPagerHtml()
	{
		return $this->getChildHtml('pager');
	}
	public function getViewOrderUrl($order)
	{
		return $this->getUrl('sales/order/view', array('order_id' => $order->getId()));
	}
}
