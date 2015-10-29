<?php
 
class Brainworx_Rental_Block_Adminhtml_Hearedfrom_Edit_Form extends Mage_Adminhtml_Block_Widget_Form
{
    protected function _prepareForm()
    {
    	//Check for model data in the registry
        if (Mage::registry('hearedfrom_data'))
        {
            $data = Mage::registry('hearedfrom_data')->getData();
        }
        else
        {
            $data = array();
        }
 
        $form = new Varien_Data_Form(array(
                'id' => 'edit_form',
                'action' => $this->getUrl('*/*/save', array('id' => $this->getRequest()->getParam('id'))),
                'method' => 'post',
                'enctype' => 'multipart/form-data',
        ));
 
        $form->setUseContainer(true);
        
 
        return parent::_prepareForm();
    }
}