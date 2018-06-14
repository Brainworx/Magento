<?php
 
class Brainworx_Hearedfrom_Block_Adminhtml_Requestformoverview extends Mage_Adminhtml_Block_Widget_Grid_Container
{
    public function __construct()
    {	
		/*controller points to block element as in config.xml*/
        $this->_controller = 'adminhtml_requestformoverview';
		$this->_blockGroup = 'requestformoverview';
        $this->_headerText = Mage::helper('hearedfrom')->__('Requestform Manager');
        
        parent::__construct();
        
        //remove the Add button as by default it is usually visible
        $this->_removeButton('add');
        

    }
	//protected function _prepareLayout(){echo get_class($this->getLayout()->createBlock( $this->_blockGroup.'/' . $this->_controller . '_grid'); exit;}
	//protected function _prepareLayout(){echo get_class($this->getLayout()->createBlock( $this->_blockGroup.'/' . $this->_controller . '_grid'); }
}