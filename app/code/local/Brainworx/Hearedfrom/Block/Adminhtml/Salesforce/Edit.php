<?php
 
class Brainworx_Hearedfrom_Block_Adminhtml_Salesforce_Edit extends Mage_Adminhtml_Block_Widget_Form_Container
{
    public function __construct()
    {
        parent::__construct();
 
        $this->_objectId = 'entity_id';
        $this->_blockGroup = 'salesforce';
        $this->_controller = 'adminhtml_salesforce';
        $this->_mode = 'edit';
 
        $this->_addButton('save_and_continue', array(
                  'label' => Mage::helper('adminhtml')->__('Save And Continue Edit'),
                  'onclick' => 'saveAndContinueEdit()',
                  'class' => 'save',
        ), -100);
        $this->_updateButton('save', 'label', Mage::helper('hearedfrom')->__('Save Zorgpunt'));
        
        $this->_removeButton('delete');
 
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
        if (Mage::registry('salesforce_data') && Mage::registry('salesforce_data')->getEntityId()) 
        {
            return Mage::helper('hearedfrom')->__('Edit salesforce_data Item "%s"', $this->htmlEscape(Mage::registry('salesforce_data')->getEntity_id()));
        } else {
            return Mage::helper('hearedfrom')->__('New salesforce_data Item');
        }
    }
}