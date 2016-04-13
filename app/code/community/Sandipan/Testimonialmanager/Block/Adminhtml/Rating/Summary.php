<?php
class Sandipan_Testimonialmanager_Block_Adminhtml_Rating_Summary extends Mage_Adminhtml_Block_Template
{
    public function __construct()
    {
        $this->setTemplate('testimonialmanager/rating/stars/summary.phtml');
    }

    public function getRating()
    {
		$id = $this->getRequest()->getParam('id');
		
		$resource = Mage::getSingleton('core/resource');
		$connection = $resource->getConnection('core_write');
		$table = $resource->getTableName('testimonialmanager/testimonialmanager');
		// select query
		$where = $connection->quoteInto("testimonial_id = ?", $id);
		$sql = $connection->select()->from($table,array('rating_summary'))->where($where);
		$rating = $connection->fetchOne($sql);
		
		return $rating;
    }

}
