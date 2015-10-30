<?php
 /*referenced in financial.xml layout file*/
class Brainworx_Hearedfrom_Block_Adminhtml_Financialssupplier extends Mage_Adminhtml_Block_Widget_Grid_Container
{
    public function __construct()
    {	
		/*controller points to location of grid element inside the blockgroup as configured in config.xml
		 * example blockgroup financial maps to hearedfrom/block/ and is extended with adminhtml/financial
		 * ==> result: hearedfrom/block/adminhtml/financial.php*/
        $this->_controller = 'adminhtml_financialssupplier';
        /*blockgroup points to block as in config.xml*/
		$this->_blockGroup = 'financialssupplier';
        $this->_headerText = Mage::helper('hearedfrom')->__('Financials Supplier Manager');
              
        parent::__construct();

        //remove the Add button as by default it is usually visible
        $this->_removeButton('add');
    }
	//protected function _prepareLayout(){echo get_class($this->getLayout()->createBlock( $this->_blockGroup.'/' . $this->_controller . '_grid'); exit;}
	//protected function _prepareLayout(){echo get_class($this->getLayout()->createBlock( $this->_blockGroup.'/' . $this->_controller . '_grid'); }
}