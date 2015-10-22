<?php

$installer = $this;
$installer->startSetup();
$installer->getConnection()
->addColumn(
		//Mage::getModel('sales/order_shipment_track')
		$installer->getTable('sales/shipment_track'),
		'delivery',
		array(
				'type'      => Varien_Db_Ddl_Table::TYPE_DATETIME,
				'unsigned'  => true,
				'nullable'  => true,
				'comment'	=> 'delivery'
		)
)
;
$installer->getConnection()
->addColumn(
		//Mage::getModel('sales/order_shipment_track')
		$installer->getTable('sales/shipment_track'),
		'comment',
		array(
				'type'      => Varien_Db_Ddl_Table::TYPE_TEXT,
				'unsigned'  => true,
				'nullable'  => true,
				'comment'	=> 'comment'
		)
)
;
$installer->getConnection()
->addColumn(
		$installer->getTable('sales/shipment_track'),
		'delivered',
		array(
				'type'      => Varien_Db_Ddl_Table::TYPE_BOOLEAN,
				'default'  => false,
				'comment'	=> 'delivered'
		)
);

$installer->endSetup();