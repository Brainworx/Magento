<?php
 
$installer = $this;
$installer->startSetup();
 
$table = $installer->getConnection()->newTable($installer->getTable('rental/rentedItem'))
    ->addColumn('entity_id', Varien_Db_Ddl_Table::TYPE_INTEGER, null, array(
        'unsigned' => true,
        'nullable' => false,
        'primary' => true,
        'identity' => true,
        ), 'ID')
    ->addColumn('descr', Varien_Db_Ddl_Table::TYPE_TEXT, null, array(
        'nullable' => false,
        ), 'Description')
	->addColumn('product_id', Varien_Db_Ddl_Table::TYPE_INTEGER, null, array(
        'nullable' => false,
        ), 'Product	Id')
	->addColumn('cust_id', Varien_Db_Ddl_Table::TYPE_INTEGER, null, array(
        'nullable' => true,
        ), 'Customer Id')
    ->addColumn('cust_nm', Varien_Db_Ddl_Table::TYPE_TEXT, null, array(
        'nullable' => true,
        ), 'Customer Name')
	->addColumn('del_adr', Varien_Db_Ddl_Table::TYPE_TEXT, null, array(
        'nullable' => true,
        ), 'Delivery Address')
	->addColumn('start_dt', Varien_Db_Ddl_Table::TYPE_TIMESTAMP, null, array(
        'nullable' => false,
        ), 'Start Rental')
	->addColumn('last_inv_dt', Varien_Db_Ddl_Table::TYPE_TIMESTAMP, null, array(
        'nullable' => true,
        ), 'Date Last Invoice')
	->addColumn('end_dt', Varien_Db_Ddl_Table::TYPE_TIMESTAMP, null, array(
        'nullable' => true,
        ), 'End Rental')
    ->addColumn('isactive', Varien_Db_Ddl_Table::TYPE_SMALLINT, null, array(
        'nullable' => false,
        ), 'Active');
         
$installer->getConnection()->createTable($table);
$installer->endSetup();