<?php

/**
 * Override Sales Order Creditmemo PDF model
 *
 * @category   Mage
 * @package    Brainworx_rental
 * @author     Stijn Heylen
 */
class Brainworx_Rental_Model_Pdf_Creditmemo extends Mage_Sales_Model_Order_Pdf_Creditmemo
{
    
    /**
     * Return PDF document
     *
     * @param  array $creditmemos
     * @return Zend_Pdf
     */
    public function getPdf($creditmemos = array())
    {
        $this->_beforeGetPdf();
        $this->_initRenderer('creditmemo');

        $pdf = new Zend_Pdf();
        $this->_setPdf($pdf);
        $style = new Zend_Pdf_Style();
        $this->_setFontBold($style, 10);

        foreach ($creditmemos as $creditmemo) {
            if ($creditmemo->getStoreId()) {
                Mage::app()->getLocale()->emulate($creditmemo->getStoreId());
                Mage::app()->setCurrentStore($creditmemo->getStoreId());
            }
            $page  = $this->newPage();
            $order = $creditmemo->getOrder();
            /* Add image */
            $this->insertLogo($page, $creditmemo->getStore());
            /* Add address */
            $this->insertAddress($page, $creditmemo->getStore());
            /* Add head */
            $this->insertOrder(
                $page,
                $order,
                Mage::getStoreConfigFlag(self::XML_PATH_SALES_PDF_CREDITMEMO_PUT_ORDER_ID, $order->getStoreId())
            );
            /* Add document text and number */
            $this->insertDocumentNumber(
                $page,
                Mage::helper('sales')->__('Credit Memo # ') . $creditmemo->getIncrementId()
            );
            /* Add CM date*/
            $this->insertCreditmemoDate($page,
            		Mage::helper('sales')->__('Datum Credit Memo : ') .
            		Mage::helper('core')->formatDate($creditmemo->getCreatedAt(), 'medium', false));
            /* Add table head */
            $this->_drawHeader($page);
            /* Add body */
            foreach ($creditmemo->getAllItems() as $item){
                if ($item->getOrderItem()->getParentItem()) {
                    continue;
                }
                /* Draw item */
                $this->_drawItem($item, $page, $order);
                $page = end($pdf->pages);
            }
            /* Add totals */
            $this->insertTotals($page, $creditmemo);
        }
        $this->_afterGetPdf();
        if ($creditmemo->getStoreId()) {
            Mage::app()->getLocale()->revert();
        }
        return $pdf;
    }

    /**
     * insert the creditmemo date in at the top of the page
     * @param Zend_Pdf_Page $page
     * @param unknown $text
     */
    public function insertCreditmemoDate(Zend_Pdf_Page $page, $text)
    {
    	$page->setFillColor(new Zend_Pdf_Color_GrayScale(1));
    	$this->_setFontRegular($page, 10);
    	$docHeader = $this->getDocHeaderCoordinates();
    	$page->drawText($text, 400, $docHeader[1] - 15, 'UTF-8');
    }
}
