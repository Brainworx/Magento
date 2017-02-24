<?php
 
class Brainworx_Rental_Model_Mederi extends Mage_Core_Model_Abstract
{
    protected function _construct()
    {
    	parent::_construct();
        $this->_init('rental/mederi');
    }

   	/**
   	 * Load mederi object
   	 * @param integer $id
   	 */
   	public function loadByMederiId($id){
   		
   		$mederi = $this->_getResource()->loadByMederiId($id);
   		if(!empty($mederi) && $mederi['entity_id']>0){
   			return Mage::getModel('rental/mederi')->load($mederi['entity_id']);
   		}
   		return null;
   	}
}