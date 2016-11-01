<?php

$installer = $this;
$installer->startSetup();

$table = $installer->getConnection()->newTable($installer->getTable('hearedfrom/salesForceLink'))
->addColumn('entity_id', Varien_Db_Ddl_Table::TYPE_INTEGER, null, array(
		'unsigned' => true,
		'nullable' => false,
		'primary' => true,
		'identity' => true,
), 'ID')
->addColumn('force_id', Varien_Db_Ddl_Table::TYPE_INTEGER, null, array(
		'unsigned' => true,'nullable' => false,
), 'SalesForce')
->addColumn('linked_cust_id', Varien_Db_Ddl_Table::TYPE_INTEGER, null, array(
		'unsigned' => true,'nullable' => true,
), 'Linked Cust ID')
->addColumn('info', Varien_Db_Ddl_Table::TYPE_INTEGER, null, array(
		'unsigned' => true,'nullable' => true,
), 'Info')
->addColumn('create_dt', Varien_Db_Ddl_Table::TYPE_TIMESTAMP, null, array(
		"default" => Varien_Db_Ddl_Table::TIMESTAMP_INIT
), "Creation time")
->addColumn('end_dt', Varien_Db_Ddl_Table::TYPE_TIMESTAMP, null, array(
		"default" => null
), "End time")
;
 
$installer->getConnection()->createTable($table);

$installer->getConnection()
->addColumn(
		$installer->getTable('hearedfrom/salesForce'),
		'end_dt',
		array(
				'type'      =>  Varien_Db_Ddl_Table::TYPE_TIMESTAMP,
				'default'  => null,
				'comment'	=> 'End time'
		)
);
$installer->getConnection()->addColumn(
		$installer->getTable('hearedfrom/salesForce'),
		'linked_to',
		array(
				'type'      =>  Varien_Db_Ddl_Table::TYPE_INTEGER,
				'default'  => 0,
				'comment'	=> 'Linked to'
		)
);
$installer->getConnection()->addColumn(
		$installer->getTable('hearedfrom/salesForce'),
		'ristorno_split_perc',
		array(
				'type'      =>  Varien_Db_Ddl_Table::TYPE_INTEGER,
				'default'  => 100,
				'comment'	=> 'Ristorno split % to keep'
		)
);
$installer->getConnection()->addColumn(
		$installer->getTable('hearedfrom/salesForce'),
		'comment',
		array(
				'type'      =>  Varien_Db_Ddl_Table::TYPE_TEXT,
				'comment'	=> 'Comment'
		)
)
;

$installer->endSetup();