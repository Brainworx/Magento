<?php
class Sandipan_Testimonialmanager_Block_Adminhtml_Testimonialmanager_Edit extends Mage_Adminhtml_Block_Widget_Form_Container
{

    public function __construct()
    {
        parent::__construct();

        $this->_objectId = 'id';
        $this->_blockGroup = 'testimonialmanager';
        $this->_controller = 'adminhtml_testimonialmanager';

        $this->_updateButton('save', 'label', Mage::helper('testimonialmanager')->__('Save Testimonial'));
        $this->_updateButton('delete', 'label', Mage::helper('testimonialmanager')->__('Delete Testimonial'));

        if( $this->getRequest()->getParam($this->_objectId) ) {
            $model = Mage::getModel('testimonialmanager/testimonialmanager')->load($this->getRequest()->getParam($this->_objectId));
            Mage::register('testimonialmanager', $model);
        }
    }

    public function getHeaderText()
    {
        if( Mage::registry('testimonialmanager') && Mage::registry('testimonialmanager')->getId() ) {
            return Mage::helper('testimonialmanager')->__('Edit Testimonial');
        } else {
            return Mage::helper('testimonialmanager')->__('Add Testimonial');
        }
    }
}
