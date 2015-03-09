<?php
$installer = $this;
$installer->startSetup();

$table = $installer->getConnection()->newTable($installer->getTable('productfileupload/productfileupload'))
->addColumn('fid', Varien_Db_Ddl_Table::TYPE_INTEGER, null, array(
		'unsigned' => true,
		'nullable' => false,
		'primary' => true,
		'identity' => true,
), 'ID')
->addColumn('filename', Varien_Db_Ddl_Table::TYPE_VARCHAR, null, array(
		'nullable' => false,
), 'Filename')
->addColumn('productid', Varien_Db_Ddl_Table::TYPE_INTEGER, null, array(
		'nullable' => false,
), 'Product ID')
->addColumn('fileplace', Varien_Db_Ddl_Table::TYPE_SMALLINT, null, array(
		'nullable' => false,
), 'File Place')
->addColumn('created_time', Varien_Db_Ddl_Table::TYPE_DATETIME, null, array(
		'nullable' => true,
), 'Create date')
->addColumn('update_time', Varien_Db_Ddl_Table::TYPE_DATETIME, null, array(
		'nullable' => true,
), 'Update date');
$installer->getConnection()->createTable($table);

$installer->endSetup(); 