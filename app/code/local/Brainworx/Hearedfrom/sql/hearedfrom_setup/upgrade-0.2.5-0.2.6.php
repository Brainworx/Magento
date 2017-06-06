<?php
$installer = new Mage_Sales_Model_Resource_Setup('core_setup');
/**
 * Add 'custom_attribute' attribute for entities
*/
$entity = 'order';
$options = array(
		'type'     => Varien_Db_Ddl_Table::TYPE_VARCHAR,
		'visible'  => true,
		'required' => false,
		'nullable' => true,
		'default'	=> null
);
$installer->addAttribute($entity, 'vaph_doc_nr', $options);
$installer->endSetup();