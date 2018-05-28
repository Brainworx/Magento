<?php

$installer = $this;
$installer->startSetup();

$installer->getConnection()
->addColumn(
		$installer->getTable('hearedfrom/salesForce'),
		'unique_link',
		array(
				'type'     => Varien_Db_Ddl_Table::TYPE_TEXT,
				'visible'  => true,
				'required' => false,
				'comment'	=> 'unique_link'
		)
);
$installer->endSetup();