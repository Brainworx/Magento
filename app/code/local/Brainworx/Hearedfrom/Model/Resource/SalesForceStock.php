<?php
 
class Brainworx_Hearedfrom_Model_Resource_SalesForceStock extends Mage_Core_Model_Resource_Db_Abstract
{
    protected function _construct()
    {
        $this->_init('hearedfrom/salesForceStock', 'entity_id');
    }
    /**
     * Optional method to load entity by sales force
     * @param varchar $username
     * @return multitype:salesforce array of salesforce with column as keys
     */
    public function loadBySalesForce($id)
    {
       $select = $adapter->select()
    	->from($this->getMainTable())
    	->where($this->getMainTable().'.'.'force_id = :id');
    	
    	$binds=array('id' => $id);
    
    	return $adapter->fetchAll($select, $binds);
    }
    /**
     * Optional method to load entity by product code
     * @param int $id
     * @return multitype:salesforce  array of salesforce with column as keys
     */
    public function loadByProdCode($code)
    {
    	$select = $adapter->select()
    	->from($this->getMainTable())
    	->where($this->getMainTable().'.'.'article_pcd = :pcode');
    	
    	$binds=array('pcode'=>$code);
    
    	return $adapter->fetchAll($select, $binds);
    }
    /**
     * Optional method to load entity by product code and sales force
     * @param int $id
     * $param string $code is product code - stock has cons+real product code
     * @return multitype:salesforce  array of salesforce with column as keys
     */
    public function loadByProdCodeAndSalesForce($code, $id)
    {
    	$adapter = $this->_getReadAdapter();
    	
    	//All stock codes have 'cons'
    
    	$select = $adapter->select()
    	->from($this->getMainTable())
    	->where($this->getMainTable().'.'.'article_pcd = :pcode')
    	->where($this->getMainTable().'.'.'force_id = :id')
    	->where($this->getMainTable().'.'.'enabled = 1');
    	
    	$binds=array('pcode'=>$code,'id' => $id);
    
    	return $adapter->fetchRow($select, $binds);
    }
}