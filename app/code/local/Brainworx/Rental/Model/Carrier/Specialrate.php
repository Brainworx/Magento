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
 * Class Brainworx_Rental_Model_Carrier_Specialrate based on Mage_Shipping_Model_Carrier_Tablerate
 */
class Brainworx_Rental_Model_Carrier_Specialrate
    extends Mage_Shipping_Model_Carrier_Abstract
    implements Mage_Shipping_Model_Carrier_Interface
{

    /**
     * code name
     *
     * @var string
     */
    protected $_code = 'specialrate';

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
    	$allowed = false;
    	$user_special_shipping = $this->getConfigData('availablefor');
    	
    	if (!empty($user_special_shipping) && Mage::getSingleton('customer/session')->isLoggedIn()) {
    		// Load the customer's data
    		$customer = Mage::getSingleton('customer/session')->getCustomer();
    		if(in_array($customer->getID(),explode(',',$user_special_shipping))){
    			$allowed = true;
    		}
    	}
        if (!$allowed || !$this->getConfigFlag('active')) {
            return false;
        }

        $result = $this->_getModel('shipping/rate_result');

        //option1
        $method = $this->_getModel('shipping/rate_result_method');

        $method->setCarrier('specialrate');
        $method->setCarrierTitle($this->getConfigData('title'));

        $method->setMethod('flatrate');
        $method->setMethodTitle($this->getConfigData('name'));
        
        $method->setPrice($this->getConfigData('price'));
        $method->setCost(0);

        $result->append($method);
        
        //option2
        if($this->getConfigFlag('freeoption')){
	        $method = $this->_getModel('shipping/rate_result_method');
	        
	        $method->setCarrier('specialrate');
	        $method->setCarrierTitle($this->getConfigData('title'));
	        
	        $method->setMethod('free');
	        $method->setMethodTitle('Gratis');
	        
	        $method->setPrice(0);
	        $method->setCost(0);
	        
	        $result->append($method);
        }

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
        return Mage::getResourceModel('rental/carrier_specialrate')->getRate($request);
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
        return 'specialrate';
    }

    /**
     * Get allowed shipping methods
     *
     * @return array
     */
    public function getAllowedMethods()
    {
        return array('flatrate' => $this->getConfigData('name'), 'free' => $this->getConfigData('optiontitle'));
    }

}