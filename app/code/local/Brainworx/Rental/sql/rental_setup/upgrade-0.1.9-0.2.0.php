<?php

/**
 * Add 'custom_attribute' attribute for entities
*/
$installer = $this;
$installer->startSetup();

$installer->getConnection()
->addColumn($installer->getTable('rental/rentedItem'),'closed_for_invoice', array(
		'type'     => Varien_Db_Ddl_Table::TYPE_BOOLEAN,
		'visible'  => true,
		'required' => false,
		'default'	=> false,
		'comment'   => 'Closed for invoicing'
));
$installer->endSetup();