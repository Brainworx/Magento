<?php
 
class Brainworx_Hearedfrom_Model_Resource_SalesForceStockRequest extends Mage_Core_Model_Resource_Db_Abstract
{
    protected function _construct()
    {
        $this->_init('hearedfrom/salesForceStockRequest', 'entity_id');
    }
}