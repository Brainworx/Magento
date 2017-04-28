<?php
 
class Brainworx_Hearedfrom_Model_Resource_SalesForce extends Mage_Core_Model_Resource_Db_Abstract
{
    protected function _construct()
    {
        $this->_init('hearedfrom/salesForce', 'entity_id');
    }
    /**
     * Optional method to load entity by username
     * @param varchar $username
     * @return multitype:salesforce array of salesforce with column as keys
     */
    public function loadByUsername($username)
    {
        $adapter = $this->_getReadAdapter();
    
        $select = $adapter->select()
            ->from($this->getMainTable())
            ->where($this->getMainTable().'.'.'user_nm'.'=?',$username);

        return $adapter->fetchRow($select);
    }
    /**
     * Optional method to load entity by customerid
     * @param int $id
     * @return multitype:salesforce  array of salesforce with column as keys
     */
    public function loadByCustomerid($id)
    {
    	$adapter = $this->_getReadAdapter();
    
    	$select = $adapter->select()
    	->from($this->getMainTable())
    	->where($this->getMainTable().'.'.'cust_id'.'=?',$id);
    
    	return $adapter->fetchRow($select);
    }
   
    /**
     * Load foreign key related data to include in the model when selected
     * (showed in admin form)
     * (non-PHPdoc)
     * @see Mage_Core_Model_Resource_Db_Abstract::_getLoadSelect()
     */
//     protected function _getLoadSelect($field, $value, $object)
//     {
//     	$select = parent::_getLoadSelect($field, $value, $object);
    	
//     	$resource = Mage::getSingleton('core/resource');
    
    	//TODO update
//     	$select->join(
// 					array('order' => $resource->getTableName('sales/order')),
// 					'orig_order_id = order.entity_id',
// 					array('customer_id','increment_id',
// 							'customer'=>'concat(customer_firstname," ",customer_lastname)'
// 					)
// 		);
//     	$select->join(
//     			array('baddress' => $resource->getTableName('sales_flat_order_address')),
//     			'billing_address_id = baddress.entity_id',
//     			array('billing_address' => 'concat(baddress.street,", ",baddress.city," ", baddress.postcode)' )
//     	);
//     	$select->join(
//     			array('saddress' => $resource->getTableName('sales_flat_order_address')),
//     			'shipping_address_id = saddress.entity_id',
//     			array('shipping_address' => 'concat(saddress.street,", ",saddress.city," ", saddress.postcode)' )
//     	);
//         $select->join(
// 	        		array('item' => $resource->getTableName('sales/order_item')),
// 	        		'order_item_id = item.item_id',
// 	        		array('product' => 'name', 'sku') //select name as product
//         );
//     	return $select;
//     }
}