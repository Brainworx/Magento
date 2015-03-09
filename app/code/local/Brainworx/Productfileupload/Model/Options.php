<?php
class Brainworx_Productfileupload_Model_Options
{
	public function toOptionArray()
	{
		return array(
				array('value'=>1, 'label'=>Mage::helper('productfileupload')->__('Yes')),
				array('value'=>2, 'label'=>Mage::helper('productfileupload')->__('No'))
		);
	}

}