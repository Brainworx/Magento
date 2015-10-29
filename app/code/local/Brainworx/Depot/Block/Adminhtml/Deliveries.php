<?php
 /*referenced in hearedfrom.xml layout file*/
class Brainworx_Depot_Block_Adminhtml_Deliveries extends Mage_Adminhtml_Block_Widget_Grid_Container
{
    public function __construct()
    {	/*controller points to location of grid element inside the blockgroup as configured in config.xml
		 * example blockgroup financial maps to hearedfrom/block/ and is extended with adminhtml/financial
		 * ==> result: hearedfrom/block/adminhtml/financial.php*/

    	$this->_blockGroup = 'depot'; /*as in config.xml blocks tag*/
        $this->_controller = 'adminhtml_deliveries'; /*real path from block on*/
        $this->_headerText = Mage::helper('depot')->__('Delivery Manager');
              
        parent::__construct();

        //remove the Add button as by default it is usually visible
        $this->_removeButton('add');
    }
	//protected function _prepareLayout(){echo get_class($this->getLayout()->createBlock( $this->_blockGroup.'/' . $this->_controller . '_grid'); exit;}
	//protected function _prepareLayout(){echo get_class($this->getLayout()->createBlock( $this->_blockGroup.'/' . $this->_controller . '_grid'); }
}