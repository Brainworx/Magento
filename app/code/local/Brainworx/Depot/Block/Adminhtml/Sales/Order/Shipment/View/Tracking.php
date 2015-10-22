<?php
/**
 * Magento
 *
 * 

 */

/**
 * Shipment tracking control form
 *
 * @category    Brainworx
 * @package     Depot_Adminhtml
 * @author      Stijn Heylen
 */
class Brainworx_Depot_Block_Adminhtml_Sales_Order_Shipment_View_Tracking extends Mage_Adminhtml_Block_Sales_Order_Shipment_View_Tracking
{

    /**
     * Retrieve remove url
     *
     * @return string
     */
    public function getTrackInfoUrl($track)
    {
        return $this->getUrl('*/*/viewTrack/', array(
            'shipment_id' => $this->getShipment()->getId(),
            'track_id' => $track->getId()
        ));
    }

    public function getDeliveryUrl($track){
    	return $this->getUrl('*/deliveries/edit',array('id'=>$track->getId()));
    }
    /**
     * Retrieve
     *
     * @return unknown
     */
    public function getCarriers()
    {
       return Mage::getModel('depot/depot')->getAllCarriers();
    }

    public function getCarrierTitle($code)
    {
        if ($carrier = Mage::getSingleton('shipping/config')->getCarrierInstance($code)) {
            return $carrier->getConfigData('title');
        }
        else {
        	//BW update 'custom name' to $code as I'll use the name as code
            return Mage::helper('sales')->__($code);
        }
        return false;
    }
}
