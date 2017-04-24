<?php

$installer = $this;
$installer->startSetup();

$table = $installer->getConnection()->newTable($installer->getTable('hearedfrom/salesForceStock'))
->addColumn('entity_id', Varien_Db_Ddl_Table::TYPE_INTEGER, null, array(
		'unsigned' => true,
		'nullable' => false,
		'primary' => true,
		'identity' => true,
), 'ID')
->addColumn('force_id', Varien_Db_Ddl_Table::TYPE_INTEGER, null, array(
		'unsigned' => true,'nullable' => false,
), 'SalesForce')
->addColumn('article_pcd', Varien_Db_Ddl_Table::TYPE_VARCHAR, null, array(
		'unsigned' => true,'nullable' => true,
), 'Product Code')
->addColumn('article', Varien_Db_Ddl_Table::TYPE_VARCHAR, null, array(
		'unsigned' => true,'nullable' => true,
), 'Product Name')
->addColumn('stock_quantity', Varien_Db_Ddl_Table::TYPE_INTEGER, null, array(
		'unsigned' => true,'nullable' => true,
), 'Stock Qty')
->addColumn('inrent_quantity', Varien_Db_Ddl_Table::TYPE_INTEGER, null, array(
		'unsigned' => true,'nullable' => true,
), 'Qty in rent')
->addColumn('enabled', Varien_Db_Ddl_Table::TYPE_BOOLEAN, null, array(
		"default" => true
), "Enabled")
->addColumn('create_dt', Varien_Db_Ddl_Table::TYPE_TIMESTAMP, null, array(
		"default" => Varien_Db_Ddl_Table::TIMESTAMP_INIT
), "Creation time")
->addColumn('update_dt', Varien_Db_Ddl_Table::TYPE_TIMESTAMP, null, array(
		"default" => Varien_Db_Ddl_Table::TIMESTAMP_INIT_UPDATE
), "Update time")
;
 
$installer->getConnection()->createTable($table);

$table2 = $installer->getConnection()->newTable($installer->getTable('hearedfrom/salesForceStockRequest'))
->addColumn('entity_id', Varien_Db_Ddl_Table::TYPE_INTEGER, null, array(
		'unsigned' => true,
		'nullable' => false,
		'primary' => true,
		'identity' => true,
), 'ID')
->addColumn('force_id', Varien_Db_Ddl_Table::TYPE_INTEGER, null, array(
		'unsigned' => true,'nullable' => false,
), 'SalesForce')
->addColumn('article_pcd', Varien_Db_Ddl_Table::TYPE_VARCHAR, null, array(
		'unsigned' => true,'nullable' => true,
), 'Product Code')
->addColumn('article', Varien_Db_Ddl_Table::TYPE_VARCHAR, null, array(
		'unsigned' => true,'nullable' => true,
), 'Product Name')
->addColumn('inrequest_quantity', Varien_Db_Ddl_Table::TYPE_INTEGER, null, array(
		'unsigned' => true,'nullable' => true,
), 'Qty in request')
->addColumn('create_dt', Varien_Db_Ddl_Table::TYPE_TIMESTAMP, null, array(
		"default" => Varien_Db_Ddl_Table::TIMESTAMP_INIT
), "Creation time")
;

$installer->getConnection()->createTable($table2);

$installer->endSetup();