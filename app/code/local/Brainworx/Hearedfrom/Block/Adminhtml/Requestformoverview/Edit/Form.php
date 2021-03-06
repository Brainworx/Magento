<?php
 
class Brainworx_Hearedfrom_Block_Adminhtml_Requestformoverview_Edit_Form extends Mage_Adminhtml_Block_Widget_Form
{
	private function getAllCustomerOptions(){
	
		$zpgroups = Mage::getModel('core/variable')->setStoreId(Mage::app()->getStore()->getId())->loadByCode('ZORGP_HEAREDFROM_GIDS')->getValue('text');
		$collection = Mage::getModel('customer/customer')
		->getCollection()
		->addAttributeToSelect('*');
	
		$options[] = array(
				'value' => null,
				'label' => Mage::helper('hearedfrom')->__('Not Selected')
		);
		foreach($collection as $customer){
			$options[] = array(
					'value' => $customer->getID(),
					'label' => $customer->getName().' (klantnr.:'.$customer->getID().')'
			);
		}
	
		$this->_options = $options;
	
		return $this->_options;
	}
    protected function _prepareForm()
    {
    	
    	$newrecord = true;
    	//Check for model data in the registry
    	Mage::Log(print_r(Mage::registry('requestform_data'),true));
        if (Mage::registry('requestform_data'))
        {
            $data = Mage::registry('requestform_data')->getData();   
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
        'legend' =>Mage::helper('hearedfrom')->__('Zorgpunt Contact Requestform')
        ));
        
        if(!$newrecord){
        	$fieldset->addField('entity_id', 'text', array(
        			'label'     => Mage::helper('hearedfrom')->__('ID'),
        			'class'     => 'readonly-entry',
        			'readonly' => true,
        			'name'      => 'entity_id',
        			'after_element_html' => '<small>(autogenerated)</small>',
        	));
        }
        $fieldset->addField('type_id', 'select', array(
        		'label'     => Mage::helper('hearedfrom')->__('Type'),
        		'values'    => Mage::getModel('hearedfrom/requesttype')->getAllOptions(),
        		'name'      => 'type_id'
        ));
        $fieldset->addField('request', 'text', array(
        		'label'     => Mage::helper('hearedfrom')->__('Request'),
        		'class'     => 'required-entry',
        		'name'      => 'request',
        		'style'	=> 'width:450px'
        ));
        $fieldset->addField('comment', 'text', array(
        		'label'     => Mage::helper('hearedfrom')->__('Comment'),
        		'class'     => 'required-entry',
        		'name'      => 'comment',
        		'style'	=> 'width:450px'
        ));
        $fieldset->addField('cust_id', 'select', array(
        		'label'     => Mage::helper('hearedfrom')->__('Customer'),
        		'values'    => $this->getAllCustomerOptions(),
        		'name'      => 'cust_id'
        ));
        $fieldset->addField('name', 'text', array(
        		'label'     => Mage::helper('hearedfrom')->__('Name'),
        		'class'     => 'required-entry',
        		'name'      => 'name'
        ));
        $fieldset->addField('address', 'text', array(
        		'label'     => Mage::helper('hearedfrom')->__('Address'),
        		'class'     => 'required-entry',
        		'name'      => 'address'
        ));
        $fieldset->addField('phone', 'text', array(
        		'label'     => Mage::helper('hearedfrom')->__('Phone'),
        		'class'     => 'required-entry',
        		'name'      => 'phone'
        ));
        $fieldset->addField('email', 'text', array(
        		'label'     => Mage::helper('hearedfrom')->__('Email'),
        		'class'     => 'required-entry',
        		'name'      => 'email'
        ));
        
        $fieldset->addField('salesforce_id', 'select', array(
        		'label'     => Mage::helper('hearedfrom')->__('Zorgpunt'),
        		'values'    => Mage::getModel('hearedfrom/salesForce')->getAllUserNamesOptions(),
        		'name'      => 'salesforce_id'
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
        		'image' => $this->getSkinUrl('images/grid-cal.gif'),
        		'format' => Mage::app()->getLocale()->getDateFormat(Mage_Core_Model_Locale::FORMAT_TYPE_SHORT),
        		'readonly' => false,
        		'name'      => 'end_dt',        		
        		'after_element_html' => '<small>(In te vullen zodra het contact afgehandeld werd.)</small>',
        ));
        
        //use addValues and not setValues to allow default values
        $form->addValues($data);
 
        return parent::_prepareForm();
    }
    
}