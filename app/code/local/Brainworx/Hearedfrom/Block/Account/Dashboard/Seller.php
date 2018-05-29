<?php
/**
 * Magento
 *
 * 
 *
 * @category    Brainworx
 * @package     Brainworx_Hearedfrom
 * @copyright  Copyright (c) Brainworx
 */

/**
 * Dashboard Customer Info
 *
 */

class Brainworx_Hearedfrom_Block_Account_Dashboard_Seller extends Mage_Core_Block_Template
{
    public function getCustomer()
    {
        return Mage::getSingleton('customer/session')->getCustomer();
    }
    public function getSeller(){
        return Mage::getModel('hearedfrom/salesforce')->loadByCustid($this->getCustomer()->getEntityId());
    }

    /**
     *  Newsletter module availability
     *
     *  @return	  boolean
     */
    public function isSeller()
    {        
        $showLink = Mage::getModel('core/variable')->setStoreId(Mage::app()->getStore()->getId())->loadByCode('UNIQUE_LINK_FRONTEND')->getValue('text');
        return $showLink && !empty($this->getSeller());
    }
    public function getSellerName(){
        $seller = $this->getSeller();
        if(!empty($seller)){
            return $seller['user_nm'];
        }
        return '';
    }
    public function getSellerUniqueLink(){
         $seller = $this->getSeller();
        if(!empty($seller)){
            return $seller['unique_link'];
        }
        return '';
    }
    public function getExtraSellerInfo(){
         
        return  Mage::getModel('core/variable')->setStoreId(Mage::app()->getStore()->getId())->loadByCode('UNIQUE_LINK_EXTRA_INFO')->getValue('html');
    }
}
