<?php
 
class Brainworx_Hearedfrom_Model_Resource_CommissionView extends Mage_Core_Model_Resource_Db_Abstract
{
    protected function _construct()
    {
        $this->_init('hearedfrom/commissionView', 'entity_id');
    }
}