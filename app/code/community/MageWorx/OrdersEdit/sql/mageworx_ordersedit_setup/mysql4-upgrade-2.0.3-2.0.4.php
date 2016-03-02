<?php
/**
 * MageWorx
 * Admin Order Editor extension
 *
 * @category   MageWorx
 * @package    MageWorx_OrdersEdit
 * @copyright  Copyright (c) 2015 MageWorx (http://www.mageworx.com/)
 */

$installer = $this;
$installer->startSetup();

$table = $installer->getConnection()
    ->newTable($installer->getTable('mageworx_ordersedit/order_status_history'))
    ->addColumn('entity_id', Varien_Db_Ddl_Table::TYPE_INTEGER, null, array(
        'identity'  => true,
        'unsigned'  => true,
        'nullable'  => false,
        'primary'   => true,
    ), 'Entity ID')
    ->addColumn('history_id', Varien_Db_Ddl_Table::TYPE_INTEGER, null, array(
        'unsigned'  => true,
        'nullable'  => false,
    ), 'History ID')
    ->addColumn('creator_admin_user_id', Varien_Db_Ddl_Table::TYPE_INTEGER, null, array(
        'unsigned'  => true,
        'nullable'  => false,
    ), 'Creator Admin User ID')
    ->addColumn('creator_firstname', Varien_Db_Ddl_Table::TYPE_VARCHAR, '32', array(
        'nullable'  => false,
    ), 'Creator First Name')
    ->addColumn('creator_lastname', Varien_Db_Ddl_Table::TYPE_VARCHAR, '32', array(
        'nullable'  => false,
    ), 'Creator Last Name')
    ->addColumn('creator_username', Varien_Db_Ddl_Table::TYPE_VARCHAR, '40', array(
        'nullable'  => false,
    ), 'Creator User Name')
    ->addIndex($installer->getIdxName(
            'mageworx_ordersedit/order_status_history',
            array('history_id'),
            Varien_Db_Adapter_Interface::INDEX_TYPE_UNIQUE
        ),
        array('history_id'),
        array('type' => Varien_Db_Adapter_Interface::INDEX_TYPE_UNIQUE)
    )
    ->addForeignKey(
        $installer->getFkName('mageworx_ordersedit/order_status_history', 'history_id', 'sales/order_status_history','history_id'),
        'history_id',
        $installer->getTable('sales/order_status_history'),
        'entity_id',
        Varien_Db_Ddl_Table::ACTION_CASCADE,
        Varien_Db_Ddl_Table::ACTION_CASCADE
    )
    ->setComment('OrdersEdit Order Status History');
$installer->getConnection()->createTable($table);

$installer->endSetup();