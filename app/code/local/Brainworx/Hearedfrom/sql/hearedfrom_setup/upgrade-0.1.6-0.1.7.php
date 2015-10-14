<?php
$installer = $this;
$installer->startSetup();
$installer->getConnection()
->addColumn(
		$installer->getTable('hearedfrom/salesCommission'),
		'ristorno',
		array(
				'type'      => Varien_Db_Ddl_Table::TYPE_DECIMAL, 
				'length'	=> '12,4',
				'unsigned'  => true,
				'nullable'  => true,
				'comment'	=> 'ristorno'
		)
)
;
$installer->getConnection()
->addColumn(
		$installer->getTable('hearedfrom/salesCommission'),
		'invoiced',
		array(
				'type'      => Varien_Db_Ddl_Table::TYPE_BOOLEAN,
				'default'  => true,
				'comment'	=> 'invoiced'
		)
);

$installer->endSetup();