<?php
$installer = new Mage_Sales_Model_Resource_Setup('core_setup');
/**
 * Add 'custom_attribute' attribute for entities
*/
$entities = array(
		'quote_item',
		'order_item'
);
$options = array(
		'type'     => Varien_Db_Ddl_Table::TYPE_DECIMAL, '12,4',
		'visible'  => true,
		'required' => false
);
foreach ($entities as $entity) {
	$installer->addAttribute($entity, 'ristorno', $options);
}
$installer->endSetup();