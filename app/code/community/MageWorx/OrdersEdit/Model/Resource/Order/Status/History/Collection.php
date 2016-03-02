<?php
/**
 * MageWorx
 * Admin Order Editor extension
 *
 * @category   MageWorx
 * @package    MageWorx_OrdersEdit
 * @copyright  Copyright (c) 2015 MageWorx (http://www.mageworx.com/)
 */

class MageWorx_OrdersEdit_Model_Resource_Order_Status_History_Collection extends Mage_Sales_Model_Mysql4_Order_Status_History_Collection
{                  
    
    public function __construct($resource=null)
    {
        parent::__construct();        
        
        if (Mage::helper('mageworx_ordersedit')->isEnabled() && $this->getSelect()!==null) {
                                    
            $this->getSelect()->joinLeft(array('upload_files_tbl'=>$this->getTable('mageworx_ordersedit/upload_files')),
                    'upload_files_tbl.history_id = main_table.entity_id',                    
                    array('file_id'=>'entity_id', 'file_name', 'file_size')
            );     

            $this->getSelect()->joinLeft(array('order_status_history_tbl'=>$this->getTable('mageworx_ordersedit/order_status_history')),
                'order_status_history_tbl.history_id = main_table.entity_id',
                array('status_history_id'=>'entity_id', 'creator_admin_user_id', 'creator_firstname', 'creator_lastname','creator_username')
            );

            $this->getSelect()->joinLeft(array('admin_user_tbl'=>$this->getTable('admin/user')),
                'admin_user_tbl.user_id = order_status_history_tbl.creator_admin_user_id',
                array('admin_firstname'=>'firstname', 'admin_lastname'=>'lastname')
            );       
        }            
    }
    
}
