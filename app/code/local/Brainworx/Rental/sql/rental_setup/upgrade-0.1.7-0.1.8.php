<?php
/**
 * Add 'custom_attribute' attribute for entities
*/
$installer = $this;
$installer->startSetup();
$installer->getConnection()
->addColumn($installer->getTable('rental/rentedItem'),'rentalinterval', array(
		'type'     => Varien_Db_Ddl_Table::TYPE_TEXT,
		'visible'  => true,
		'required' => false,
		'nullable' => false,
		'default'	=> 'd',
		'comment'   => 'Interval for rental invoice'
));
$installer->endSetup();
