<?php   
class Sandipan_Testimonialmanager_Block_Testimonials extends Mage_Core_Block_Template{   

    protected function _getSubmitUrl()
    {
        return $this->getUrl('testimonialmanager/index/submit');
    }
    protected function _getWriteUrl()
    {
        return $this->getUrl('testimonialmanager/index/form');
    }
	public function getResizedImage($_imageName, $_width, $_height) {
		$_imageUrl = Mage::getBaseUrl(Mage_Core_Model_Store::URL_TYPE_MEDIA) . $_imageName;
		$_resizedImageUrl = Mage::getBaseUrl() . 'image.php?width='. $_width .'&height='. $_height .'&image='. $_imageUrl;
		return $_resizedImageUrl;
	}
    protected function _getCollection()
    {
        $collection = Mage::getModel("testimonialmanager/testimonialmanager")->getCollection();
		$collection->addFieldToFilter('status', '2');
        $collection->setOrder('testimonial_position', 'ASC')
				->load();
        return $collection;
    }
    protected function _getSidebarCollection()
    {
        $collection = Mage::getModel("testimonialmanager/testimonialmanager")->getCollection();
		$collection->addFieldToFilter('status', '2');
		$collection->addFieldToFilter('testimonial_sidebar', '1');
        $collection->setOrder('testimonial_position', 'ASC')
				->load();
        return $collection;
    }
}