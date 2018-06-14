<?php
 
class Brainworx_Hearedfrom_Block_Adminhtml_Requesttype_Edit_Form extends Mage_Adminhtml_Block_Widget_Form
{
    protected function _prepareForm()
    {
    	
    	$newrecord = true;
    	//Check for model data in the registry
    	Mage::Log(print_r(Mage::registry('requesttype_data'),true));
        if (Mage::registry('requesttype_data'))
        {
            $data = Mage::registry('requesttype_data')->getData();   
            if(isset($data['entity_id']))
            	$newrecord = false;
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
        
        $this->setForm($form);
        # add a fieldset, this returns a Varien_Data_Form_Element_Fieldset object
        $fieldset = $form->addFieldset('request_form', array(
        'legend' =>Mage::helper('hearedfrom')->__('Zorgpunt Contact Request Type')
        ));
        
        if(! $newrecord){
        	$fieldset->addField('entity_id', 'text', array(
        			'label'     => Mage::helper('hearedfrom')->__('ID'),
        			'class'     => 'readonly-entry',
        			'readonly' => true,
        			'name'      => 'entity_id',
        			'after_element_html' => '<small>(autogenerated)</small>',
        	));
        }
        $fieldset->addField('type', 'text', array(
        		'label'     => Mage::helper('hearedfrom')->__('type'),
        		'class'     => 'required-entry',
        		'name'      => 'type',
        		'after_element_html' => '<small>(gebruikt in cms om juiste form te laden)</small>',
        ));
        $fieldset->addField('description', 'text', array(
        		'label'     => Mage::helper('hearedfrom')->__('Description'),
        		'class'     => 'required-entry',
        		'name'      => 'description',
        		'style'	=> 'width:450px'
        ));
        $fieldset->addField('partner_name', 'text', array(
        		'label'     => Mage::helper('hearedfrom')->__('Partner name'),
        		'class'     => 'required-entry',
        		'name'      => 'partner_name'
        ));
        $fieldset->addField('partner_email', 'text', array(
        		'label'     => Mage::helper('hearedfrom')->__('Partner email'),
        		'class'     => 'required-entry',
        		'name'      => 'partner_email'
        ));
        $fieldset->addField('created_at', 'date', array(
        		'label'     => Mage::helper('hearedfrom')->__('Create date'),
        		'format' => Mage::app()->getLocale()->getDateFormat(Mage_Core_Model_Locale::FORMAT_TYPE_SHORT),
        		'readonly' => true,
        		'name'      => 'created_at',
        		'after_element_html' => '<small>(autogenerated)</small>',
        ));
        $fieldset->addField('updated_at', 'date', array(
        		'label'     => Mage::helper('hearedfrom')->__('Updated on'),
        		'format' => Mage::app()->getLocale()->getDateFormat(Mage_Core_Model_Locale::FORMAT_TYPE_SHORT),
        		'readonly' => true,
        		'name'      => 'updated_at',
        		'after_element_html' => '<small>(autogenerated)</small>',
        ));
        $fieldset->addField('end_dt', 'date', array(
        		'label'     => Mage::helper('hearedfrom')->__('Ended on'),
        		//'image' => $this->getSkinUrl('images/grid-cal.gif'),
        		'format' => Mage::app()->getLocale()->getDateFormat(Mage_Core_Model_Locale::FORMAT_TYPE_SHORT),
        		'readonly' => true,
        		'name'      => 'end_dt',        		
        		'after_element_html' => '<small>(disabled - contact Brainworx)</small>',
        ));
        
        //use addValues and not setValues to allow default values
        $form->addValues($data);
 
        return parent::_prepareForm();
    }
    
}