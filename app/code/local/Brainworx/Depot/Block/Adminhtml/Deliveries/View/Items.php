<?php
/**
 * Magento
 * Brainworx delivery item renderer
 * 
 */


/**
 * Adminhtml delivery item renderer
 *
 * @category   Brainworx
 * @package    Depot_Adminhtml
 * @author     Stijn Heylen
 */
class Brainworx_Depot_Block_Adminhtml_Deliveries_View_Items extends Mage_Adminhtml_Block_Sales_Items_Abstract
{
	private $order;
	public function _construct()
	{
		parent::_construct();
		 
		$this->setTemplate('deliveries/info.phtml');
	}
    /**
     * Retrieve order object
     *
     * @return Mage_Sales_Model_Order
     */
    public function getOrder()
    {
    	$data = Mage::registry('shipment_data');
        return Mage::getModel('sales/order')->load($data->getOrderId());
    }
    /**
     * retrieve shipment object
     * @return Mage_Sales_Order_Shipment
     */
    public function getShipment()
    {
    	$data = Mage::registry('shipment_data');
    	return Mage::getModel('sales/order_shipment')->load($data->getEntityId());
    }
 
   
}
