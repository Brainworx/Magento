<?php
 
class Brainworx_Hearedfrom_Model_SalesCommission extends Mage_Core_Model_Abstract
{
    protected function _construct()
    {
    	parent::_construct();
        $this->_init('hearedfrom/salesCommission');
    }
    /**
     * Prepare list of available types for dropdown filter options for the grid
     * @return multitype:NULL
     */
    public function getTypes() {
    
    	$array = array();
    	foreach($this->getCollection()->addFieldToSelect("type")->distinct(true) as $comm){
    		$array[$comm->getType()] = $comm->getType();
    	}
    	return $array;
    }
    public function formatPrice($price, $addBrackets = false)
    {
    	return $this->formatPricePrecision($price, 2, $addBrackets);
    }
    
    public function formatPricePrecision($price, $precision, $addBrackets = false)
    {
    	$currency = Mage::getModel('directory/currency')->load(Mage::app()->getStore()->getCurrentCurrencyCode());
    	return $currency->formatPrecision($price, $precision, array(), true, $addBrackets);
    }
    /*
    * Optional method to load 1 entity by parameters
    * @param int $id
    * @return multitype:salescommission  array of salescommission data with column as keys
    */
    public function loadByLastCommission($order_id,$item_id)
    {
        return $this->_getResource()->loadByLastCommission($order_id,$item_id);
    }
}
