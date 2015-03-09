<?php
 /*referenced in rental.xml layout file*/
class Brainworx_Rental_Block_Adminhtml_Rental extends Mage_Adminhtml_Block_Widget_Grid_Container
{
    public function __construct()
    {	
		/*controller points to block element as in config.xml*/
        $this->_controller = 'adminhtml_rental';
		$this->_blockGroup = 'rental';
        $this->_headerText = Mage::helper('rental')->__('Rental Manager');
        $this->_addButton('invoicer', array(
        		'label' => Mage::helper('rental')->__('Maak maandfacturen.'),
        		'onclick'   => "confirmSetLocation('De maandelijkse facturen aanmaken?', '{$this->getUrl('*/*/createInvoices')}')",
        ));
        parent::__construct();

        //remove the Add button as by default it is usually visible
        $this->_removeButton('add');
    }
	//protected function _prepareLayout(){echo get_class($this->getLayout()->createBlock( $this->_blockGroup.'/' . $this->_controller . '_grid'); exit;}
	//protected function _prepareLayout(){echo get_class($this->getLayout()->createBlock( $this->_blockGroup.'/' . $this->_controller . '_grid'); }
}