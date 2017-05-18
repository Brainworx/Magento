<?php
 
class Brainworx_Hearedfrom_Block_Adminhtml_Salesforcestock_Edit_Form extends Mage_Adminhtml_Block_Widget_Form
{
	private function getAllStockProductOptions(){
	
		/**
		 * If you want to display products from any specific category
		*/
		$categoryId = Mage::getModel('core/variable')->setStoreId(Mage::app()->getStore()->getId())->loadByCode('CAT_CONSIG')->getValue('text');
		$category = Mage::getModel('catalog/category')->load($categoryId);
		
		/**
		 * Getting product collection for a particular category
		*/
		$prodCollection = Mage::getResourceModel('catalog/product_collection')
		->addCategoryFilter($category)
		->addAttributeToSelect('*');
		
		/**
		 * Applying status and visibility filter to the product collection
		 * i.e. only fetching visible and enabled products
 		*/
		Mage::getSingleton('catalog/product_status')
		->addVisibleFilterToCollection($prodCollection);
		
	
		$options[] = array(
				'value' => null,
				'label' => Mage::helper('hearedfrom')->__('Not Selected')
		);
		$type = "";
		$catstock = Mage::getModel('core/variable')->setStoreId(Mage::app()->getStore()->getId())->loadByCode('CAT_RENT')->getValue('text');
		
		foreach ($prodCollection as $val) {
			$type = (in_array($catstock, $val->getCategoryIds()))?
			Mage::helper('hearedfrom')->__('Verhuur'): Mage::helper('hearedfrom')->__('Verkoop');
			$options[] = array(
					'value' => $val->getSku(),
					'label' => $type.' - '.$val->getName()
			);
		}
	
		$this->_options = $options;
	
		return $this->_options;
	}
	
    protected function _prepareForm()
    {
    	$newrecord = true;
    	//Check for model data in the registry
    	//Mage::Log(print_r(Mage::registry('rental_data'),true));
        if (Mage::registry('salesforcestock_data'))
        {
            $data = Mage::registry('salesforcestock_data')->getData();   
            if(isset($data['entity_id']))
            	$newrecord = false;
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
        $fieldset = $form->addFieldset('salesforcestock_form', array(
             'legend' =>Mage::helper('hearedfrom')->__('Zorgpunt Stock Information')
        ));
        # now add fields on to the fieldset object, for more detailed info
        # see https://makandracards.com/magento/12737-admin-form-field-types
        
        if(! $newrecord){
	        $fieldset->addField('entity_id', 'text', array(
	        		'label'     => Mage::helper('hearedfrom')->__('ID'),
	        		'class'     => 'readonly-entry',
	        		'readonly' => true,
	        		'name'      => 'user_nm'
	        ));
        }
        $fieldset->addField('force_id', 'select', array(
        		'label'     => Mage::helper('hearedfrom')->__('Zorgpunt'),
        		'values'    => Mage::getModel('hearedfrom/salesForce')->getAllUserNamesOptions(),
        		'name'      => 'force_id'
        ));
        $fieldset->addField('article_pcd', 'select', array(
        		'label'     => Mage::helper('hearedfrom')->__('Article'),
        		'values'	=> $this->getAllStockProductOptions(),
        		'name'      => 'article_pcd'
        ));
        $fieldset->addField('stock_quantity', 'text', array(
        		'label'     => Mage::helper('hearedfrom')->__('Stock Quantity'),
        		'name'      => 'stock_quantity',
        		'value'		=> 0
        ));
        $fieldset->addField('inrent_quantity', 'text', array(
        		'label'     => Mage::helper('hearedfrom')->__('Rented Quantity'),
        		'name'      => 'inrent_quantity',
        		'value'		=> 0
        ));
        if(!$newrecord){
	        $fieldset->addField('enabled', 'checkbox', array(
	        		'label'     => Mage::helper('hearedfrom')->__('Active'),
	        		'name'      => 'enabled',
	        		'checked'    => $data['enabled']==1 ,
	        		'onclick'   => 'this.value = this.checked ? 1 : 0;',
	        ));
	        $fieldset->addField('create_dt', 'date', array(
	        		'label'     => Mage::helper('hearedfrom')->__('Created on'),
	        		'readonly' => true,
	        		'format' => Mage::app()->getLocale()->getDateFormat(Mage_Core_Model_Locale::FORMAT_TYPE_SHORT),
	        		'name'      => 'create_dt'
	        ));
	        /*
	        $fieldset->addField('update_dt', 'date', array(
	        		'label'     => Mage::helper('hearedfrom')->__('Updated on'),
	        		'format' => Mage::app()->getLocale()->getDateFormat(Mage_Core_Model_Locale::FORMAT_TYPE_SHORT),
	        		'readonly' => true,
	        		'name'      => 'update_dt',
	        		'value'		=>  date('d-m-Y H:i:s', strtotime('now'))
	        ));
	        */
        }
        
        //use addValues and not setValues to allow default values
        $form->addValues($data);
        
 
        return parent::_prepareForm();
    }
}