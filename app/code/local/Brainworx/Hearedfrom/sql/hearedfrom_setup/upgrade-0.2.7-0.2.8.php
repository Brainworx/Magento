<?php

$installer = $this;
$installer->startSetup();

$table = $installer->getConnection()->newTable($installer->getTable('hearedfrom/requesttype'))
->addColumn('entity_id', Varien_Db_Ddl_Table::TYPE_INTEGER, null, array(
		'unsigned' => true,
		'nullable' => false,
		'primary' => true,
		'identity' => true,
), 'ID')
->addColumn('type', Varien_Db_Ddl_Table::TYPE_VARCHAR, null, array(
		'nullable' => false,
), 'Type Id')
->addColumn('description', Varien_Db_Ddl_Table::TYPE_VARCHAR, null, array(
		'nullable' => false,
), 'Description')
->addColumn('partner_email', Varien_Db_Ddl_Table::TYPE_VARCHAR, null, array(
		'nullable' => false,
), 'Partner email')
->addColumn('partner_name', Varien_Db_Ddl_Table::TYPE_VARCHAR, null, array(
		'nullable' => false,
), 'Partner name')
->addColumn('created_at', Varien_Db_Ddl_Table::TYPE_TIMESTAMP, null, array(
		'nullable' => false,"default" => Varien_Db_Ddl_Table::TIMESTAMP_INIT,
), 'Create date')
->addColumn('updated_at', Varien_Db_Ddl_Table::TYPE_TIMESTAMP, null, array(
		'nullable' => false,"default" => Varien_Db_Ddl_Table::TIMESTAMP_INIT_UPDATE,
), 'Update date')
->addColumn('end_dt', Varien_Db_Ddl_Table::TYPE_TIMESTAMP, null, array(
		'nullable' => false,
), 'Create date');

$table1 = $installer->getConnection()->newTable($installer->getTable('hearedfrom/requestform'))
->addColumn('entity_id', Varien_Db_Ddl_Table::TYPE_INTEGER, null, array(
		'unsigned' => true,
		'nullable' => false,
		'primary' => true,
		'identity' => true,
), 'ID')
->addColumn('type_id', Varien_Db_Ddl_Table::TYPE_INTEGER, null, array(
		'nullable' => false,
), 'Type Id')
->addColumn('request', Varien_Db_Ddl_Table::TYPE_VARCHAR, null, array(
		'nullable' => false,
), 'Request info')
->addColumn('cust_id', Varien_Db_Ddl_Table::TYPE_INTEGER, null, array(
		'nullable' => false,
), 'Customer Id')
->addColumn('name', Varien_Db_Ddl_Table::TYPE_VARCHAR, null, array(
		'nullable' => false,
), 'First name')
->addColumn('address', Varien_Db_Ddl_Table::TYPE_VARCHAR, null, array(
		'nullable' => false,
), 'Address')
->addColumn('phone', Varien_Db_Ddl_Table::TYPE_VARCHAR, null, array(
		'nullable' => false,
), 'Telephone')
->addColumn('email', Varien_Db_Ddl_Table::TYPE_VARCHAR, null, array(
		'nullable' => false,
), 'Email')
->addColumn('comment', Varien_Db_Ddl_Table::TYPE_VARCHAR, null, array(
		'nullable' => false,
), 'Comment')
->addColumn('salesforce_id', Varien_Db_Ddl_Table::TYPE_INTEGER, null, array(
		'nullable' => true,
), 'Seller Id')
->addColumn('created_at', Varien_Db_Ddl_Table::TYPE_TIMESTAMP, null, array(
		'nullable' => false,"default" => Varien_Db_Ddl_Table::TIMESTAMP_INIT,
), 'Create date')
->addColumn('updated_at', Varien_Db_Ddl_Table::TYPE_TIMESTAMP, null, array(
		'nullable' => false,"default" => Varien_Db_Ddl_Table::TIMESTAMP_INIT_UPDATE,
), 'Update date')
->addColumn('completed_at', Varien_Db_Ddl_Table::TYPE_TIMESTAMP, null, array(
		'nullable' => false,
), 'Completed date');
 
$installer->getConnection()->createTable($table);
$installer->getConnection()->createTable($table1);
$installer->endSetup();