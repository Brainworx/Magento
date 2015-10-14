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
		'type'     => Varien_Db_Ddl_Table::TYPE_BOOLEAN,
		'visible'  => true,
		'required' => false,
		'default'	=> false
);
foreach ($entities as $entity) {
	$installer->addAttribute($entity, 'supplierinvoice', $options);
	$installer->addAttribute($entity, 'rentalitem', $options);
}
$installer->endSetup();