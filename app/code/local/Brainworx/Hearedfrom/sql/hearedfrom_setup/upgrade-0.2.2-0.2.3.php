<?php
$installer = $this;
$installer->startSetup();
$installer->getConnection()
->addColumn(
		$installer->getTable('hearedfrom/salesCommission'),
		'sold_by',
		array(
				'type'     => Varien_Db_Ddl_Table::TYPE_TEXT,
				'visible'  => true,
				'required' => false,
				'comment'	=> 'Sold By' 
		)
)
;
$installer->getConnection()
->addColumn(
		$installer->getTable('hearedfrom/salesSeller'),
		'seller_cust_id',
		array(
				'type'      => Varien_Db_Ddl_Table::TYPE_INTEGER,
				'visible'  => true,
				'default'  => 0,
				'comment'	=> 'Seller Cust Id'
		)
)
;

$installer->endSetup();