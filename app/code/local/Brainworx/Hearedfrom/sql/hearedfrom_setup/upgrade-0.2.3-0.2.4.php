<?php
$installer = $this;
$installer->startSetup();

$installer->getConnection()
->addColumn(
		$installer->getTable('hearedfrom/salesForce'),
		'street_nr',
		array(
				'type'     => Varien_Db_Ddl_Table::TYPE_TEXT,
				'visible'  => true,
				'required' => false,
				'comment'	=> 'Street and number' 
		)
);
$installer->getConnection()
->addColumn(
		$installer->getTable('hearedfrom/salesForce'),
		'zip_cd',
		array(
				'type'     => Varien_Db_Ddl_Table::TYPE_TEXT,
				'visible'  => true,
				'required' => false,
				'comment'	=> 'Zip Code'
		)
);
$installer->getConnection()
->addColumn(
		$installer->getTable('hearedfrom/salesForce'),
		'city',
		array(
				'type'     => Varien_Db_Ddl_Table::TYPE_TEXT,
				'visible'  => true,
				'required' => false,
				'comment'	=> 'City'
		)
);
$installer->getConnection()
->addColumn(
		$installer->getTable('hearedfrom/salesForce'),
		'country',
		array(
				'type'     => Varien_Db_Ddl_Table::TYPE_TEXT,
				'visible'  => true,
				'required' => false,
				'comment'	=> 'Country'
		)
)
;
$installer->getConnection()
->addColumn(
		$installer->getTable('hearedfrom/salesForce'),
		'phone',
		array(
				'type'     => Varien_Db_Ddl_Table::TYPE_TEXT,
				'visible'  => true,
				'required' => false,
				'comment'	=> 'Phone'
		)
)
;

$installer->endSetup();