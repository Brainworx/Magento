<?php
/**
 * Magento
 * 
 * Mage_Adminhtml_Block_Sales_Order_Shipment_Create_Tracking
 *
 * 
 * @category   Brainworx
 * @package    Depot_Adminhtml
 * @author     Stijn.Heylen@brainworx.be
 */
class Brainworx_Depot_Block_Adminhtml_Sales_Order_Shipment_Create_Tracking extends Mage_Adminhtml_Block_Sales_Order_Shipment_Create_Tracking
{
       

    /**
     * Retrieve
     *
     * @return unknown
     */
    public function getCarriers()
    {
        return Mage::getModel('depot/depot')->getAllCarriers();
    }
}
