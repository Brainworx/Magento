<?php
 
$installer = $this;
$installer->startSetup();
 
$table = $installer->getConnection()->newTable($installer->getTable('rental/rentedItem'))
    ->addColumn('entity_id', Varien_Db_Ddl_Table::TYPE_INTEGER, null, array(
        'unsigned' => true,
        'nullable' => false,
        'primary' => true,
        'identity' => true,
        ), 'ID')
    ->addColumn('orig_order_id', Varien_Db_Ddl_Table::TYPE_INTEGER, null, array(
        		'unsigned' => true,'nullable' => true,
        ), 'Original Order Id')
        /*
         *  ->addColumn('state', Varien_Db_Ddl_Table::TYPE_TEXT, 32, array(
        ), 'State')
         */
        //sales_flat_order_item
    ->addColumn('order_item_id', Varien_Db_Ddl_Table::TYPE_INTEGER, null, array(
    		'unsigned' => true,'nullable' => true,
    	), 'Item Id')
    	//initial quantity - not nr of day but nr of items
    ->addColumn('quantity', Varien_Db_Ddl_Table::TYPE_DECIMAL, '12,4', array(
    		'unsigned' => true,'nullable' => true,
    	), 'Quantity')
	->addColumn('start_dt', Varien_Db_Ddl_Table::TYPE_DATE, null, array(
        'nullable' => false,
        ), 'Start Rental')
	->addColumn('last_inv_dt', Varien_Db_Ddl_Table::TYPE_DATE, null, array(
        'nullable' => true,
        ), 'Date Last Invoice')
    ->addColumn('last_order_id', Varien_Db_Ddl_Table::TYPE_INTEGER, null, array(
        		'unsigned' => true,'nullable' => true,
        ), 'Last Order Id')
	->addColumn('end_dt', Varien_Db_Ddl_Table::TYPE_DATE, null, array(
        'nullable' => true,
        ), 'End Rental')
     ->addColumn('create_dt', Varien_Db_Ddl_Table::TYPE_TIMESTAMP, null, array(
        		"default" => Varien_Db_Ddl_Table::TIMESTAMP_INIT
        ), "Creation time")
        /*
    ->addForeignKey('fk_rental_orig_order','orig_order_id','sales_flat_order','entity_id', Varien_Db_Ddl_Table::ACTION_SET_NULL, Varien_Db_Ddl_Table::ACTION_CASCADE)
    ->addForeignKey('fk_rental_last_order','last_order_id','sales_flat_order','entity_id', Varien_Db_Ddl_Table::ACTION_SET_NULL, Varien_Db_Ddl_Table::ACTION_CASCADE)
    ->addForeignKey('fk_rental_order_item','order_item_id','sales_flat_order_item','item_id', Varien_Db_Ddl_Table::ACTION_SET_NULL, Varien_Db_Ddl_Table::ACTION_CASCADE)
    */;
         
$installer->getConnection()->createTable($table);
$installer->endSetup();