<?php
 
class Brainworx_hearedfrom_Block_Adminhtml_Hearedfrom_Edit extends Mage_Adminhtml_Block_Widget_Form_Container
{
    public function __construct()
    {
    	Mage::Log('constuct edit form');
        parent::__construct();
 
        $this->_objectId = 'entity_id';
        $this->_blockGroup = 'hearedfrom';
        $this->_controller = 'adminhtml_hearedfrom';
        $this->_mode = 'edit';
 
        $this->_addButton('save_and_continue', array(
                  'label' => Mage::helper('adminhtml')->__('Save And Continue Edit'),
                  'onclick' => 'saveAndContinueEdit()',
                  'class' => 'save',
        ), -100);
        $this->_updateButton('save', 'label', Mage::helper('hearedfrom')->__('Save SalesCommission Item'));
 
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
        
        Mage::Log('constuct edit form end');
    }
 
    public function getHeaderText()
    {
        if (Mage::registry('hearedfrom_data') && Mage::registry('hearedfrom_data')->getEntityId()) 
        {
            return Mage::helper('hearedfrom')->__('Edit SalesCommission Item "%s"', $this->htmlEscape(Mage::registry('hearedfrom_data')->getEntity_id()));
        } else {
            return Mage::helper('hearedfrom')->__('New SalesCommission Item');
        }
    }
}