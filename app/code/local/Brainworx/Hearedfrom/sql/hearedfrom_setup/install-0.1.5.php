<?php

$installer = $this;
$installer->startSetup();
//Add user table
$table = $installer->getConnection()->newTable($installer->getTable('hearedfrom/salesForce'))
->addColumn('entity_id', Varien_Db_Ddl_Table::TYPE_INTEGER, null, array(
		'unsigned' => true,
		'nullable' => false,
		'primary' => true,
		'identity' => true,
), 'ID')
->addColumn('user_nm', Varien_Db_Ddl_Table::TYPE_VARCHAR, null, array(
		'unsigned' => true,'nullable' => true,
), 'Name')
->addColumn('vat_id', Varien_Db_Ddl_Table::TYPE_VARCHAR, null, array(
		'unsigned' => true,'nullable' => true,
), 'Vat ID')
->addColumn('create_dt', Varien_Db_Ddl_Table::TYPE_TIMESTAMP, null, array(
		"default" => Varien_Db_Ddl_Table::TIMESTAMP_INIT
), "Creation time")
;
//Add order vs seller table
$table1 = $installer->getConnection()->newTable($installer->getTable('hearedfrom/salesSeller'))
->addColumn('entity_id', Varien_Db_Ddl_Table::TYPE_INTEGER, null, array(
		'unsigned' => true,
		'nullable' => false,
		'primary' => true,
		'identity' => true,
), 'ID')
->addColumn('user_id', Varien_Db_Ddl_Table::TYPE_INTEGER, null, array(
		'unsigned' => true,'nullable' => true,
), 'Seller ID')
->addColumn('order_id', Varien_Db_Ddl_Table::TYPE_INTEGER, null, array(
		'unsigned' => true,'nullable' => true,
), 'Order ID')
;
//Add table to track sales / rentals for commission calculation
$table2 = $installer->getConnection()->newTable($installer->getTable('hearedfrom/salesCommission'))
->addColumn('entity_id', Varien_Db_Ddl_Table::TYPE_INTEGER, null, array(
		'unsigned' => true,
		'nullable' => false,
		'primary' => true,
		'identity' => true,
), 'ID')
->addColumn('user_id', Varien_Db_Ddl_Table::TYPE_INTEGER, null, array(
		'unsigned' => true,'nullable' => true,
), 'User Id') 
->addColumn('orig_order_id', Varien_Db_Ddl_Table::TYPE_INTEGER, null, array(
		'unsigned' => true,'nullable' => true,
), 'Original Order Id')
->addColumn('order_item_id', Varien_Db_Ddl_Table::TYPE_INTEGER, null, array(
		'unsigned' => true,'nullable' => true,
), 'Item Id')
->addColumn('type', Varien_Db_Ddl_Table::TYPE_VARCHAR, null, array(
		'unsigned' => true,
), 'Sale Type') //Sale = S Rental = R
->addColumn('net_amount', Varien_Db_Ddl_Table::TYPE_DECIMAL, '12,4', array(
		'unsigned' => true,'nullable' => true,
), 'Amount Ex Vat')
->addColumn('brut_amount', Varien_Db_Ddl_Table::TYPE_DECIMAL, '12,4', array(
		'unsigned' => true,'nullable' => true,
), 'Amount Incl Vat')
->addColumn('create_dt', Varien_Db_Ddl_Table::TYPE_TIMESTAMP, null, array(
		"default" => Varien_Db_Ddl_Table::TIMESTAMP_INIT
), "Creation time")
;
 
$installer->getConnection()->createTable($table);
$installer->getConnection()->createTable($table1);
$installer->getConnection()->createTable($table2);

$installer->endSetup();