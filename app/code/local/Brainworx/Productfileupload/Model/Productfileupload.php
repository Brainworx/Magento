<?php

class Brainworx_Productfileupload_Model_Productfileupload extends Mage_Core_Model_Abstract
{
    public function _construct()
    {
        parent::_construct();
        $this->_init('productfileupload/productfileupload');
    }

	public function getFilesByAttr($prodId, $place=1) {
        $data = array();
		$collection = $this->getCollection();       
		$collection->addFieldToFilter('productid', $prodId);
		$collection->addFieldToFilter('fileplace', $place);
        $collection->getSelect()->order('created_time');
        return $collection->toArray();
    }

}