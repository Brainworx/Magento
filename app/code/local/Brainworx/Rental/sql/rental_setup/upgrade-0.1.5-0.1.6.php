<?php
$installer = new Mage_Sales_Model_Resource_Setup('core_setup');
/**
 * Add 'custom_attribute' attribute for entities
*/

$options = array(
		'type'     => Varien_Db_Ddl_Table::TYPE_BOOLEAN,
		'visible'  => true,
		'required' => false,
		'default'	=> false
);
$installer->addAttribute('invoice', 'incasso', $options);

$installer->endSetup();