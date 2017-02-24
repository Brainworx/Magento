<?php

$installer = $this;
$installer->startSetup();

$table = $installer->getConnection()->newTable($installer->getTable('rental/mederi'))
->addColumn('entity_id', Varien_Db_Ddl_Table::TYPE_INTEGER, null, array(
		'unsigned' => true,
		'nullable' => false,
		'primary' => true,
		'identity' => true,
), 'ID')
->addColumn('mederi_id', Varien_Db_Ddl_Table::TYPE_INTEGER, null, array(
		'unsigned' => true,'nullable' => false,
), 'Mederi Id')
->addColumn('email', Varien_Db_Ddl_Table::TYPE_VARCHAR, null, array(
		'unsigned' => true,'nullable' => true,
), 'Email')
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

$installer->endSetup();