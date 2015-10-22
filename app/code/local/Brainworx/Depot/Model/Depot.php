<?php
 
class Brainworx_Depot_Model_Depot extends Mage_Core_Model_Abstract
{
    protected function _construct()
    {
    	parent::_construct();
        $this->_init('depot/depot');
    }
    /**
     * Prepare the list of increment id's for the admin grid filter <bestelling #>
     * @return multitype:mixed
     */
    public function getCarriers() {
    
    	$collection = Mage::getModel('sales/order_shipment_track')->getCollection();
    	$select = $collection->getSelect();
    		
    	 
    	$carrierArray = array();
    	foreach($collection as $carrier){
    		$ctitle = $carrier->getData('title');
    		if(! in_array($ctitle,$carrierArray))
    			$carrierArray[$carrier->getData('carrier_code')] = $ctitle;
    
    	}
    	return $carrierArray;
    
    }
    public function getAllCarriers(){
    	$carriers = array();
    	$carriers['Custom'] = 'Custom';
    	$carriers['Bierbeek'] = Mage::helper('sales')->__('Depot Bierbeek');
    	$carriers['Galmaarden'] = Mage::helper('sales')->__('Depot Galmaarden');
    	return $carriers;
    }
   
}