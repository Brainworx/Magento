<?php
 
$installer = $this;
$installer->startSetup();

$installer->run("
		DROP TABLE IF EXISTS {$this->getTable('rental/rentedItem')};
		CREATE TABLE {$this->getTable('rental/rentedItem')} (
		'entity_id' int(11) unsigned NOT NULL AUTO_INCREMENT,
		'orig_order_id' int(11) NOT NULL,
		'order_item_id' int(11) NOT NULL,
		'last_order_id' int(11) NOT NULL,
		'quantity' int(11) DEFAULT '1',
		'start_dt' date DEFAULT NULL,
		'last_inv_dt' date DEFAULT NULL,
		'end_dt' date DEFAULT NULL,
		'create_time' timestamp DEFAULT CURRENTTIMESTAMP,
		PRIMARY KEY ('entity_id'),	
		FOREIGN KEY ('orig_order_id') REFERENCES sales_flat_order('entity_id') ON DELETE SET NULL ON UPDATE CASCADE,
		FOREIGN KEY ('last_order_id') REFERENCES sales_flat_order('entity_id')ON DELETE SET NULL ON UPDATE CASCADE,
		FOREIGN KEY ('order_item_id') REFERENCES sales_flat_order_item('item_id') ON DELETE SET NULL ON UPDATE CASCADE
		) ENGINE=InnoDB  DEFAULT CHARSET=utf8 AUTO_INCREMENT=1 ;
");

$installer->endSetup();