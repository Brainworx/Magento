<?php
/**
 * Magento
 *
 *
 * @category    Mage
 * @package     Mage_Checkout
 * @copyright  Copyright (c) 2006-2014 X.commerce, Inc. (http://www.magento.com)
 * @license    http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

/**
 * One page checkout status
 *
 * @category   Mage
 * @package    Mage_Checkout
 * @author      Magento Core Team <core@magentocommerce.com>
 */
class Brainworx_Hearedfrom_Block_Onepage_Progress extends Mage_Checkout_Block_Onepage_Progress
{
	public function getPatient()
	{
// 		$text = Mage::getSingleton('core/session')->getPatientFirstname()
// 		.' '.Mage::getSingleton('core/session')->getPatientName()
// 		.'<br>'.Mage::getSingleton('core/session')->getPatientBirthDate();
		
		return $this->getQuote()->getPatientAddress();;
	}
	public function getPatientBirthDate()
	{
		// 		$text = Mage::getSingleton('core/session')->getPatientFirstname()
		// 		.' '.Mage::getSingleton('core/session')->getPatientName()
		// 		.'<br>'.Mage::getSingleton('core/session')->getPatientBirthDate();
	
		return Mage::getSingleton('core/session')->getPatientBirthDate();
	}
}
