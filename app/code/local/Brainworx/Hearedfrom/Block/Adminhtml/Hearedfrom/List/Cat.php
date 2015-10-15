<?php
class Brainworx_Hearedfrom_Block_Adminhtml_Hearedfrom_List_Cat extends Mage_Adminhtml_Block_Widget_Grid_Column_Renderer_Abstract
{
	/**
	 * Render the hearedfrom/salesCommission row column category
	 * (non-PHPdoc)
	 * @see Mage_Adminhtml_Block_Widget_Grid_Column_Renderer_Abstract::render()
	 */
	public function render(Varien_Object $row)
	{
		$order_item = Mage::getModel('sales/order_item')->load($row->getOrderItemId());
		$cats = $order_item->getProduct()->getCategoryIds();		
		$allCats = '';
		if(!empty($cats)){
			foreach($cats as $key => $cat)
			{
				$_category = Mage::getModel('catalog/category')->load($cat);
				$allCats.= $_category->getName();
				if($key < count($cats)-1)
					$allCats.= ' ,';
			}
		}
		return $allCats;
	}

}