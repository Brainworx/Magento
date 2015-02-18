<?php
 /*referenced in rental.xml layout file*/
class Brainworx_Rental_Block_Adminhtml_Hearedfrom extends Mage_Adminhtml_Block_Widget_Grid_Container
{
    public function __construct()
    {	
		/*controller points to block element as in config.xml*/
        $this->_controller = 'adminhtml_hearedfrom';
		$this->_blockGroup = 'hearedfrom';
        $this->_headerText = Mage::helper('hearedfrom')->__('Hearedfrom Manager');
              
        parent::__construct();

        //remove the Add button as by default it is usually visible
        $this->_removeButton('add');
    }
	//protected function _prepareLayout(){echo get_class($this->getLayout()->createBlock( $this->_blockGroup.'/' . $this->_controller . '_grid'); exit;}
	//protected function _prepareLayout(){echo get_class($this->getLayout()->createBlock( $this->_blockGroup.'/' . $this->_controller . '_grid'); }
}