<?php
 
class Brainworx_Rental_Block_Adminhtml_Rental_Edit_Form extends Mage_Adminhtml_Block_Widget_Form
{
    protected function _prepareForm()
    {
    	//check callcenter
    	
    	$adminuserId = Mage::getSingleton('admin/session')->getUser()->getUserId();
    	$role_data = Mage::getModel('admin/user')->load($adminuserId)->getRole()->getData();
    	$callcenter = ($role_data["role_name"] == "Callcenter");
    	
    	//Check for model data in the registry
    	//Mage::Log(print_r(Mage::registry('rental_data'),true));
    	$patient = "";
        if (Mage::registry('rental_data'))
        {
            $data = Mage::registry('rental_data')->getData();
            $order = Mage::getModel("sales/order")->load($data['orig_order_id']);
            $patient = $order->getBillingAddress()->getFirstname().' '.$order->getBillingAddress()->getLastname();
            
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
        $fieldset = $form->addFieldset('rental_form', array(
             'legend' =>Mage::helper('rental')->__('Rented item Information')
        ));
        # now add fields on to the fieldset object, for more detailed info
        # see https://makandracards.com/magento/12737-admin-form-field-types
        $fieldset->addField('entity_id', 'text', array(
        		'label'     => Mage::helper('rental')->__('Rental #'),
        		'class'     => 'required-entry',
        		'readonly' => true,
        		'name'      => 'entity_id'
        ));
        $fieldset->addField('increment_id', 'text', array(
        		'label'     => Mage::helper('rental')->__('Order #'),
        		'class'     => 'required-entry',
        		'readonly' => true,
        		'name'      => 'increment_id'
        ));
        $fieldset->addField('customer_id', 'text', array(
        		'label'     => Mage::helper('rental')->__('Customer #'),
        		'readonly' => true,
        		'name'      => 'customer_id'
        ));
        $fieldset->addField('customer', 'text', array(
        		'label'     => Mage::helper('rental')->__('Ordered by'),
        		'class'     => 'required-entry',
        		'readonly' => true,
        		'name'      => 'customer'
        ));
        $fieldset->addField('patient', 'text', array(
        		'label'     => Mage::helper('rental')->__('Invoiced to patient'),
        		'class'     => 'required-entry',
        		'readonly' => true,
        		'value'      => $patient
        ));
        $fieldset->addField('billing_address', 'text', array(
        		'label'     => Mage::helper('rental')->__('Invoice address'),
        		'class'     => 'required-entry',
        		'readonly' => true,
        		'name'      => 'billing_address'
        ));
        $fieldset->addField('shipping_address', 'text', array(
        		'label'     => Mage::helper('rental')->__('Delivery Address'),
        		'class'     => 'required-entry',
        		'readonly' => true,
        		'name'      => 'shipping_address'
        ));        
        $fieldset->addField('orig_order_id', 'text', array(
        		'label'     => Mage::helper('rental')->__('Original Order Id'),
        		'class'     => 'required-entry',
        		'readonly' => true,
        		'name'      => 'orig_order_id'
        ));
        $fieldset->addField('order_item_id', 'text', array(
        		'label'     => Mage::helper('rental')->__('Orderitem id'),
        		'class'     => 'required-entry',
        		'readonly' => true,
        		'name'      => 'order_item_id'
        ));
        $fieldset->addField('sku', 'text', array(
        		'label'     => Mage::helper('rental')->__('Product id'),
        		'class'     => 'required-entry',
        		'readonly' => true,
        		'name'      => 'sku'
        ));
        $fieldset->addField('product', 'text', array(
        		'label'     => Mage::helper('rental')->__('Product'),
        		'class'     => 'required-entry',
        		'readonly' => true,
        		'name'      => 'product'
        ));
        $fieldset->addField('quantity', 'text', array(
        		'label'     => Mage::helper('rental')->__('QTY'),
        		'class'     => 'required-entry',
        		'readonly' => true,
        		'name'      => 'quantity'
        ));
        if($callcenter){
	        $fieldset->addField('last_inv_dt', 'text', array(
	        		'label'     => Mage::helper('rental')->__('Date last invoice'),
	//         		'image' => $this->getSkinUrl('images/grid-cal.gif'),
	        	 	'format' => Mage::app()->getLocale()->getDateFormat(Mage_Core_Model_Locale::FORMAT_TYPE_SHORT),
	        		'readonly' => true,
	        		'name'      => 'last_inv_dt'
	        ));
        }else{
        	$fieldset->addField('last_inv_dt', 'date', array(
        			'label'     => Mage::helper('rental')->__('Date last invoice'),
        			'image' => $this->getSkinUrl('images/grid-cal.gif'),
        			'format' => Mage::app()->getLocale()->getDateFormat(Mage_Core_Model_Locale::FORMAT_TYPE_SHORT),
        			'readonly' => false,
        			'name'      => 'last_inv_dt'
        	));
        }
//TODO remove last_order_id field
//         $fieldset->addField('last_order_id', 'text', array(
//         		'label'     => Mage::helper('rental')->__('ID laatste bestelling'),
//         		//'class'     => 'required-entry',
//         		'readonly' => true,
//         		'required' => false,
//         		'name'      => 'last_order_id'
//         ));
		if($callcenter){
        $fieldset->addField('start_dt', 'text', array(
        		'label'     => Mage::helper('rental')->__('Start Rental'),
        		'image' => $this->getSkinUrl('images/grid-cal.gif'),
        		'format' => Mage::app()->getLocale()->getDateFormat(Mage_Core_Model_Locale::FORMAT_TYPE_SHORT),
        		'class'     => 'required-entry',
        		'readonly' => true,
        		'name'      => 'start_dt'
        ));
		}else{
			$fieldset->addField('start_dt', 'date', array(
					'label'     => Mage::helper('rental')->__('Start Rental'),
					'image' => $this->getSkinUrl('images/grid-cal.gif'),
					'format' => Mage::app()->getLocale()->getDateFormat(Mage_Core_Model_Locale::FORMAT_TYPE_SHORT),
					'class'     => 'required-entry',
					'readonly' => false,
					'name'      => 'start_dt'
			));
		}
        $fieldset->addField('end_dt', 'date', array(
        		'label'     => Mage::helper('rental')->__('End Rental'),
        		'image' => $this->getSkinUrl('images/grid-cal.gif'),
        	 	'format' => Mage::app()->getLocale()->getDateFormat(Mage_Core_Model_Locale::FORMAT_TYPE_SHORT),
        		'name'      => 'end_dt'
        ));
        
        //use addValues and not setValues to allow default values
        $form->addValues($data);
        
 
        return parent::_prepareForm();
    }
}