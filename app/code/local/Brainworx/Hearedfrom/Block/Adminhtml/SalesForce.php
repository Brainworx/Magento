<?php
 /*referenced in rental.xml layout file*/
class Brainworx_Hearedfrom_Block_Adminhtml_SalesForce extends Mage_Adminhtml_Block_Widget_Grid_Container
{
    public function __construct()
    {	
		/*controller points to block element as in config.xml*/
        $this->_controller = 'adminhtml_salesforce';
		$this->_blockGroup = 'salesforce';
        $this->_headerText = Mage::helper('hearedfrom')->__('SalesForce Manager');
        
        parent::__construct();

    }
	//protected function _prepareLayout(){echo get_class($this->getLayout()->createBlock( $this->_blockGroup.'/' . $this->_controller . '_grid'); exit;}
	//protected function _prepareLayout(){echo get_class($this->getLayout()->createBlock( $this->_blockGroup.'/' . $this->_controller . '_grid'); }
}