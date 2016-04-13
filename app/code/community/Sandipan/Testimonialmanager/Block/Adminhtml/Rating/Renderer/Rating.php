<?php
class Sandipan_Testimonialmanager_Block_Adminhtml_Rating_Renderer_Rating extends Mage_Adminhtml_Block_Widget_Grid_Column_Renderer_Abstract
{
    public function render(Varien_Object $row)
    {
        $html = '<span class="rating-box">';
        $html .= '<span style="width:'. $row->getData($this->getColumn()->getIndex()) * 20 .'%;" class="rating"></span>';
        $html .= '</span>';
        return $html;
    }
}
