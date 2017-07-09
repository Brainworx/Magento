<?php
 /*referenced in hearedfrom.xml layout file*/
class Brainworx_Hearedfrom_Block_Adminhtml_Commissionview extends Mage_Adminhtml_Block_Widget_Grid_Container
{
    public function __construct()
    {	
		/*controller points to block element as in config.xml*/
        $this->_controller = 'adminhtml_commissionview';
		$this->_blockGroup = 'commissionview';
        $this->_headerText = Mage::helper('hearedfrom')->__('Commission Evolution Report');
              
        parent::__construct();

        //remove the Add button as by default it is usually visible
        $this->_removeButton('add');
    }
}