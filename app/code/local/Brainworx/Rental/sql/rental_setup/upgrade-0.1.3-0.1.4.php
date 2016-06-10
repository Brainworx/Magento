<?php

/**
 * Add 'custom_attribute' attribute for entities
*/
$installer = $this;
$installer->startSetup();

$installer->getConnection()
->addColumn($installer->getTable('rental/rentedItem'),'pickup_dt', array(
		'type'     => Varien_Db_Ddl_Table::TYPE_DATE,
		'visible'  => true,
		'required' => false,
		'nullable' => true,
		'default'	=> null,
		'comment'   => 'Pickup date'
));
$installer->endSetup();