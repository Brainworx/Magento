<?php
 
class Brainworx_Hearedfrom_Block_Adminhtml_SalesForce_Edit_Form extends Mage_Adminhtml_Block_Widget_Form
{
    protected function _prepareForm()
    {
    	//Check for model data in the registry
    	//Mage::Log(print_r(Mage::registry('rental_data'),true));
        if (Mage::registry('salesforce_data'))
        {
            $data = Mage::registry('salesforce_data')->getData();   
        }
        else
        {
            $data = array();
        }
        //Mage::Log(print_r($data,true));
 
        $form = new Varien_Data_Form(array(
                'id' => 'edit_form',
                'action' => $this->getUrl('*/*/save', array('id' => $this->getRequest()->getParam('id'))),
                'method' => 'post',
                'enctype' => 'multipart/form-data',
        ));
 
        $form->setUseContainer(true);
 
        $this->setForm($form);
        # add a fieldset, this returns a Varien_Data_Form_Element_Fieldset object
        $fieldset = $form->addFieldset('salesforce_form', array(
             'legend' =>Mage::helper('hearedfrom')->__('Zorgpunt Information')
        ));
        # now add fields on to the fieldset object, for more detailed info
        # see https://makandracards.com/magento/12737-admin-form-field-types
        $fieldset->addField('entity_id', 'text', array(
        		'label'     => Mage::helper('hearedfrom')->__('SalesForce #'),
        		'class'     => 'required-entry',
        		'readonly' => true,
        		'name'      => 'entity_id'
        ));
        $fieldset->addField('create_dt', 'date', array(
        		'label'     => Mage::helper('hearedfrom')->__('Created on'),
        		'readonly' => true,
        		'format' => Mage::app()->getLocale()->getDateFormat(Mage_Core_Model_Locale::FORMAT_TYPE_SHORT),
        		'name'      => 'create_dt'
        ));
        $fieldset->addField('user_nm', 'text', array(
        		'label'     => Mage::helper('hearedfrom')->__('User name'),
        		'class'     => 'required-entry',
        		'name'      => 'user_nm'
        ));
        $fieldset->addField('cust_id', 'text', array(
        		'label'     => Mage::helper('hearedfrom')->__('Main Customer #'),
        		'name'      => 'cust_id'
        		//TODO check options
        ));
        $fieldset->addField('linked_to', 'text', array(
        		'label'     => Mage::helper('hearedfrom')->__('Linked to Seller'),
        		'name'      => 'linked_to'
        ));
        $fieldset->addField('ristorno_split_perc', 'text', array(
        		'label'     => Mage::helper('hearedfrom')->__('Ristorno % to keep'),
        		'name'      => 'ristorno_split_perc'
        ));
        $fieldset->addField('comment', 'text', array(
        		'label'     => Mage::helper('hearedfrom')->__('Comment'),
        		'name'      => 'comment'
        ));
        $fieldset->addField('end_dt', 'date', array(
        		'label'     => Mage::helper('hearedfrom')->__('Ended on'),
        		'image' => $this->getSkinUrl('images/grid-cal.gif'),
        		'format' => Mage::app()->getLocale()->getDateFormat(Mage_Core_Model_Locale::FORMAT_TYPE_SHORT),
        		'readonly' => false,
        		'name'      => 'end_dt'
        ));
        
        //use addValues and not setValues to allow default values
        $form->addValues($data);
        
 
        return parent::_prepareForm();
    }
}