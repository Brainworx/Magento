<?php
 /*referenced in financial.xml layout file*/
class Brainworx_Hearedfrom_Block_Adminhtml_Financial extends Mage_Adminhtml_Block_Widget_Grid_Container
{
    public function __construct()
    {	
		/*controller points to location of grid element*/
        $this->_controller = 'adminhtml_financial';
        /*blockgroup points to block as in config.xml*/
		$this->_blockGroup = 'financial';
        $this->_headerText = Mage::helper('financial')->__('Financial Manager');
              
        parent::__construct();

        //remove the Add button as by default it is usually visible
        $this->_removeButton('add');
    }
	//protected function _prepareLayout(){echo get_class($this->getLayout()->createBlock( $this->_blockGroup.'/' . $this->_controller . '_grid'); exit;}
	//protected function _prepareLayout(){echo get_class($this->getLayout()->createBlock( $this->_blockGroup.'/' . $this->_controller . '_grid'); }
}