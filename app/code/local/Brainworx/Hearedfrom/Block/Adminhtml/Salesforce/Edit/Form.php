<?php
 
class Brainworx_Hearedfrom_Block_Adminhtml_Salesforce_Edit_Form extends Mage_Adminhtml_Block_Widget_Form
{
	private function getAllCustomerOptions(){
	
		$zpgroups = Mage::getModel('core/variable')->setStoreId(Mage::app()->getStore()->getId())->loadByCode('ZORGP_HEAREDFROM_GIDS')->getValue('text');
		$collection = Mage::getModel('customer/customer')
		->getCollection()
		->addAttributeToSelect('*')
		->addFieldToFilter('group_id', array("in" => explode(',', $zpgroups)));
	
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
        
        $fieldset->addField('user_nm', 'text', array(
        		'label'     => Mage::helper('hearedfrom')->__('User name*'),
        		'class'     => 'required-entry',
        		'name'      => 'user_nm'
        ));
        $fieldset->addField('cust_id', 'select', array(
        		'label'     => Mage::helper('hearedfrom')->__('Main Customer #'),
        		'values'    => $this->getAllCustomerOptions(),
        		'name'      => 'cust_id'
        		//TODO check options
        ));
        $fieldset->addField('street_nr', 'text', array(
        		'label'     => Mage::helper('hearedfrom')->__('Street and number'),
        		'name'      => 'street_nr'
        ));
        $fieldset->addField('zip_cd', 'text', array(
        		'label'     => Mage::helper('hearedfrom')->__('ZipCode'),
        		'name'      => 'zip_cd'
        ));
        $fieldset->addField('city', 'text', array(
        		'label'     => Mage::helper('hearedfrom')->__('City_'),
        		'name'      => 'city'
        ));
        /*
        $fieldset->addField('country', 'text', array(
        		'label'     => Mage::helper('hearedfrom')->__('Country_'),
        		'name'      => 'country',
        		'value'		=> 'BE',
        ));
        */
        $fieldset->addField('phone', 'text', array(
        		'label'     => Mage::helper('hearedfrom')->__('Phone'),
        		'name'      => 'phone',
        ));
        $fieldset->addField('linked_to', 'select', array(
        		'label'     => Mage::helper('hearedfrom')->__('Linked to Seller'),
        		'values'	=> Mage::getModel('hearedfrom/salesForce')->getUserNamesOptions(),
        		'name'      => 'linked_to'
        ));
        $fieldset->addField('ristorno_split_perc', 'text', array(
        		'label'     => Mage::helper('hearedfrom')->__('Ristorno % to keep'),
        		'name'      => 'ristorno_split_perc',
        		'value'		=> 100
        ));
        $fieldset->addField('comment', 'text', array(
        		'label'     => Mage::helper('hearedfrom')->__('Comment'),
        		'name'      => 'comment'
        ));
        $fieldset->addField('entity_id', 'text', array(
        		'label'     => Mage::helper('hearedfrom')->__('SalesForce #'),
        		'readonly' => true,
        		'name'      => 'entity_id'
        ));
        $fieldset->addField('create_dt', 'date', array(
        		'label'     => Mage::helper('hearedfrom')->__('Created on'),
        		'readonly' => false,
        		'image' => $this->getSkinUrl('images/grid-cal.gif'),
        		'format' => Mage::app()->getLocale()->getDateFormat(Mage_Core_Model_Locale::FORMAT_TYPE_SHORT),
        		'name'      => 'create_dt',
        		'value'		=>  date( Mage::app()->getLocale()->getDateStrFormat(Mage_Core_Model_Locale::FORMAT_TYPE_SHORT),
                                  strtotime('next weekday') )
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