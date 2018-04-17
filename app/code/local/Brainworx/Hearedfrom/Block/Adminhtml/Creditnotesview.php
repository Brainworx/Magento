<?php
 /*referenced in hearedfrom.xml layout file*/
class Brainworx_Hearedfrom_Block_Adminhtml_Creditnotesview extends Mage_Adminhtml_Block_Widget_Grid_Container
{
    public function __construct()
    {	
		/*controller points to block element as in config.xml*/
        $this->_controller = 'adminhtml_creditnotesview';
		$this->_blockGroup = 'creditnotesview';
        $this->_headerText = Mage::helper('hearedfrom')->__('Creditnotes Report Admisol');
              
        parent::__construct();

        //remove the Add button as by default it is usually visible
        $this->_removeButton('add');
    }
}