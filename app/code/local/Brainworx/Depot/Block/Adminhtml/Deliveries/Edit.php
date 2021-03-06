<?php
 
class Brainworx_Depot_Block_Adminhtml_Deliveries_Edit extends Mage_Adminhtml_Block_Widget_Form_Container
{
    public function __construct()
    {
        parent::__construct();
 
        $this->_objectId = 'entity_id';
        $this->_blockGroup = 'depot';
        $this->_controller = 'adminhtml_deliveries';
        $this->_mode = 'edit';
 
        $this->_addButton('save_and_continue', array(
                  'label' => Mage::helper('adminhtml')->__('Save And Continue Edit'),
                  'onclick' => 'saveAndContinueEdit()',
                  'class' => 'save',
        ), -100);
        $this->_updateButton('save', 'label', Mage::helper('depot')->__('Save Delivery'));
        
        $this->_removeButton('delete');
        $this->_removeButton('reset');
 
        $this->_formScripts[] = "
            function toggleEditor() {
                if (tinyMCE.getInstanceById('form_content') == null) {
                    tinyMCE.execCommand('mceAddControl', false, 'edit_form');
                } else {
                    tinyMCE.execCommand('mceRemoveControl', false, 'edit_form');
                }
            }
 
            function saveAndContinueEdit(){
                editForm.submit($('edit_form').action+'back/edit/');
            }
        ";
        
    }
 
    public function getHeaderText()
    {
        if (Mage::registry('shipment_data') && Mage::registry('shipment_data')->getEntityId()) 
        {
            return Mage::helper('depot')->__('Edit Delivery Item', $this->htmlEscape(Mage::registry('shipment_data')->getEntity_id()));
        } else {
            return Mage::helper('depot')->__('New Delivery Item');
        }
    }
}