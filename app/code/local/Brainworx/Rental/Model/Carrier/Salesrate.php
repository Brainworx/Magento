<?php
/**
 * Magento
 *
 * 
 * @category    Mage
 * @package     Brainworx_Rental based on Mage_Shipping
 * @copyright  Copyright (c) 2006-2014 X.commerce, Inc. (http://www.magento.com)
 * @license    http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

/*
 * Class Brainworx_Rental_Model_Carrier_Salesrate based on Mage_Shipping_Model_Carrier_Tablerate
 */
class Brainworx_Rental_Model_Carrier_Salesrate
    extends Mage_Shipping_Model_Carrier_Abstract
    implements Mage_Shipping_Model_Carrier_Interface
{

    /**
     * code name
     *
     * @var string
     */
    protected $_code = 'salesrate';

    /**
     * boolean isFixed
     *
     * @var boolean
     */
    protected $_isFixed = true;

    /*
     * Constructor
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Collect and get rates
     *
     * @param Mage_Shipping_Model_Rate_Request $request
     * @return Mage_Shipping_Model_Rate_Result
     */
    public function collectRates(Mage_Shipping_Model_Rate_Request $request)
    {
    	if(Mage::getSingleton('core/session')->getVaphOrder()){
    		return false;
    	}
    	$allowed = true;
    	$catrental = Mage::getModel('core/variable')->setStoreId(Mage::app()->getStore()->getId())->loadByCode('CAT_RENT')->getValue('text');
    	 
    	if (!empty($catrental)) {
    		// check items for rental - if 1 rental in basket --> salesrate not allowed
    		$items = $request->getAllItems();
    		foreach($items as $item){
	    		if(in_array($catrental,$item->getProduct()->getCategoryIds())){
					$allowed = false;
					break;
				}    			
    		}
    	}
        if (!$allowed || !$this->getConfigFlag('active')) {
            return false;
        }
        

        $result = $this->_getModel('shipping/rate_result');

        //option1
        $method = $this->_getModel('shipping/rate_result_method');

        $method->setCarrier('salesrate');
        $method->setCarrierTitle($this->getConfigData('title'));
		$method->setMethod('flatrate');
        $method->setMethodTitle($this->getConfigData('name'));
	    
	if ($request->getFreeShipping() === true) {
            /**
             * was applied promotion rule for whole cart
             * we must show method with 0$ price, 
             */
		$method->setPrice(0);
        }else{
        	$method->setPrice($this->getConfigData('price'));
	}
        
        $method->setCost(0);

        $result->append($method);        

        return $result;
    }

    /**
     * Get Model
     *
     * @param string $modelName
     *
     * @return Mage_Core_Model_Abstract
     */
    protected function _getModel($modelName)
    {
        return Mage::getModel($modelName);
    }

    /**
     * Get Rate
     *
     * @param Mage_Shipping_Model_Rate_Request $request
     *
     * @return Mage_Core_Model_Abstract
     */
    public function getRate(Mage_Shipping_Model_Rate_Request $request)
    {
        return Mage::getResourceModel('rental/carrier_salesrate')->getRate($request);
    }

    /**
     * Get code
     *
     * @param string $type
     * @param string $code
     *
     * @return array
     */
    public function getCode($type, $code = '')
    {
        return 'salesrate';
    }

    /**
     * Get allowed shipping methods
     *
     * @return array
     */
    public function getAllowedMethods()
    {
        return array('flatrate' => $this->getConfigData('name'));
    }

}
