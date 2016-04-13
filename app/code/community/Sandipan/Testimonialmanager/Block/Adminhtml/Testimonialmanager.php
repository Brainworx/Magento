<?php  
class Sandipan_Testimonialmanager_Block_Adminhtml_Testimonialmanager extends Mage_Adminhtml_Block_Widget_Grid_Container {
  public function __construct()
  {
    $this->_controller = 'adminhtml_testimonialmanager';
    $this->_blockGroup = 'testimonialmanager';
    $this->_headerText = Mage::helper('testimonialmanager')->__('Testimonial Manager');
    $this->_addButtonLabel = Mage::helper('testimonialmanager')->__('Add Testimonial');
    parent::__construct();
  }
}