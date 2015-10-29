<?php
 
class Brainworx_Depot_Block_Adminhtml_Deliveries_Edit_Form extends Mage_Adminhtml_Block_Widget_Form
{
    protected function _prepareForm()
    {
    	//Check for model data in the registry
    	//Mage::Log(print_r(Mage::registry('shipment_data'),true));
        if (Mage::registry('shipment_data'))
        {
            $data = Mage::registry('shipment_data')->getData();
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
        $fieldset = $form->addFieldset('delivery_form', array(
             'legend' =>Mage::helper('depot')->__('Delivery item Information')
        ));
        # now add fields on to the fieldset object, for more detailed info
        # see https://makandracards.com/magento/12737-admin-form-field-types
        $fieldset->addField('entity_id', 'text', array(
        		'label'     => Mage::helper('depot')->__('Levering #'),
        		'class'     => 'required-entry',
        		'readonly' => true,
        		'name'      => 'entity_id',
        		'required'	=> true,
        ));
        $fieldset->addField('track_number', 'text', array(
        		'label'     => Mage::helper('depot')->__('Tracking Number'),
        		'class'     => 'required-entry',
        		'readonly' => true,
        		'name'      => 'track_number',
        		'required'	=> true,
        ));
      
        $fieldset->addField('title', 'select',array(
        		'label'    => Mage::helper('depot')->__('Carrier'),
        		'value'		=> 'carrier_code',
        		'values'	=>  Mage::getModel('depot/depot')->getCarriers(),
        		'required'	=> true,
        ));
       
        $fieldset->addField('delivery', 'datetime', array(
        		'label'     => Mage::helper('depot')->__('Delivery date'),
        		'image' => $this->getSkinUrl('images/grid-cal.gif'),
        	 	'readonly' => false,
        		'name'      => 'delivery',
        		'format' => 'dd-MM-yyyy HH:mm',
        		'input_format' =>  'dd-MM-yyyy HH:mm',
        		'time' => true,
        		'type' => 'datetime',
        		'input'	=> 'datetime',
        		'required'	=> true,
        ));
        
        $fieldset->addField('comment', 'text', array(
        		'label'     => Mage::helper('depot')->__('Additional Comment'),
        		'name'      => 'comment',
        ));
        
        $fieldset->addField('delivered', 'select', array(
        		'label'     => Mage::helper('depot')->__('Delivered'),
        		'name'      => 'delivered',
        		'values'	=> array('1' => Mage::helper('depot')->__('Y'),'0' => Mage::helper('depot')->__('N')),
        		'value'  	=> $this->getDelivered() == true ? '1':'0',
        ));
        
        $form->setValues($data);
        
 
        return parent::_prepareForm();
    }
}